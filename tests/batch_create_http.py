#!/usr/bin/env python3
"""UNIT-014: disposable HTTP/PHP/MariaDB batch creation A/B + rollback."""
import json
import os
from pathlib import Path
import secrets
import shutil
import subprocess
import sys
import tempfile
import urllib.error
import urllib.parse
import urllib.request
import user_actions_http as h
from acct_maintenance_http import Forms

ROOT=Path(__file__).resolve().parents[1]
BASELINE=os.environ.get('BATCH_CREATE_BASELINE')=='1'
DB,WEB,NETWORK=h.DB,h.WEB,h.NETWORK
run,sql,wait_for=h.run,h.sql,h.wait_for


def main():
    scratch=Path.home()/'.hermes/cache/scratch'
    scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch,prefix='dalo-batch-create-') as tmp:
        fixture=Path(tmp)
        shutil.copytree(ROOT/'app',fixture/'app',symlinks=True)
        if BASELINE:
            old=subprocess.check_output(['git','show','28154153c:app/operators/mng-batch-add.php'],cwd=ROOT)
            (fixture/'app/operators/mng-batch-add.php').write_bytes(old)
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql',
                         'mariadb-daloradius-dictionaries.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            sql("""INSERT INTO operators_acl (operator_id,file,access) VALUES
                   (9001,'mng_batch_add',1),(9002,'mng_batch_add',0);
                   INSERT INTO hotspots (id,name) VALUES (7,'Sample hotspot');
                   INSERT INTO radgroupcheck (groupname,attribute,op,value)
                       VALUES ('gold','Auth-Type',':=','Accept');
                   INSERT INTO billing_plans (planName,planId,planCost,planActive)
                       VALUES ('Basic plan','basic','12.30','yes'),
                              ('Plan 50% O''Reilly','quoted','12.30','yes');""")
            config=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,val in {'CONFIG_DB_HOST':DB,'CONFIG_DB_USER':'root',
                            'CONFIG_DB_PASS':'','CONFIG_DB_NAME':'radius'}.items():
                config+='\n$configValues['+repr(key)+'] = '+repr(val)+';\n'
            config+=("\n$configValues['CONFIG_LOCATIONS']['named'] = ["
                     f"'Engine'=>'mysqli','Hostname'=>{DB!r},'Port'=>'3306',"
                     "'Database'=>'radius','Username'=>'root','Password'=>''];\n")
            (fixture/'app/common/includes/daloradius.conf.php').write_text(config)
            (fixture/'session.php').write_text('''<?php
session_name('daloradius_operator_sid'); session_id($argv[1]); session_start();
$_SESSION = ['daloradius_logged_in'=>true,'operator_id'=>(int)$argv[2],
'operator_user'=>'batch-fixture','location_name'=>$argv[3],'time'=>time()];
session_write_close();
''')
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{fixture}:/fixtures','-w','/fixtures/app/operators',
                '--entrypoint','php','lirantal/daloradius','-d','display_errors=0',
                '-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/mng-batch-add.php'
            wait_for(lambda: urllib.request.urlopen(base,timeout=10),'PHP HTTP')
            sid=secrets.token_hex(16)
            def session(operator=9001,location='default'):
                run('docker','exec',WEB,'php','/fixtures/session.php',sid,str(operator),location)
            def request(data=None,authenticated=True):
                req=urllib.request.Request(base,
                    data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),
                    headers={'Cookie':'daloradius_operator_sid='+sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req,timeout=25) as response:
                        return response.url,response.read().decode()
                except urllib.error.HTTPError as error:
                    return error.url,error.read().decode()
            def csrf():
                url,html=request()
                forms=Forms(html).forms
                matches=[f['csrf_token'] for f in forms if 'csrf_token' in f]
                if not matches:raise AssertionError(('missing csrf',url,'csrf in html', 'csrf_token' in html, 'forms',len(forms),[list(f) for f in forms],html[-1000:]))
                return matches[0]
            def submit(name='Fixture batch',prefix='unit-',start='1000',extra=None,token=None):
                data={'csrf_token':csrf() if token is None else token,
                    'batch_name':name,'batch_description':'Sample batch',
                    'hotspot_id':'hotspot-7','accountType':'incremental_user_random_password',
                    'username_prefix':prefix,'startingIndex':start,'number':'4',
                    'passwordType':'SHA2-Password','length_pass':'8',
                    'group':'gold','group_priority':'2','planName':'Basic plan',
                    'firstname':'Batch Person','bi_contactperson':'Billing Contact',
                    'reply1[]':['Reply-Message','Hi team',':=','reply']}
                if extra:data.update(extra)
                return request(data)[1]
            def state():
                return {key:sql(query) for key,query in {
                    'batch':'SELECT id,batch_name,batch_description,hotspot_id FROM batch_history ORDER BY id',
                    'check':"SELECT username,attribute,op FROM radcheck WHERE username LIKE 'unit-%' ORDER BY username,id",
                    'reply':"SELECT username,attribute,op,value FROM radreply WHERE username LIKE 'unit-%' ORDER BY username,id",
                    'groups':"SELECT username,groupname,priority FROM radusergroup WHERE username LIKE 'unit-%' ORDER BY username,id",
                    'info':"SELECT username,firstname,changeuserinfo,enableportallogin FROM userinfo WHERE username LIKE 'unit-%' ORDER BY username,id",
                    'billing':"SELECT username,planName,hotspot_id,batch_id,contactperson FROM userbillinfo WHERE username LIKE 'unit-%' ORDER BY username,id",
                }.items()}
            session()
            before=state()
            assert request(authenticated=False)[0].endswith('/login.php')
            session(9002)
            assert request()[0].endswith('/home-error.php')
            session()
            assert 'Created 4 user(s)' not in submit(token='invalid')
            assert state()==before
            print('PASS auth, ACL and CSRF',file=sys.stderr)
            response=submit()
            assert 'Created 4 user(s)' in response,response[:750]
            after=state()
            assert all(f'unit-{i}\tSHA2-Password\t:=' in after['check'] for i in range(1000,1004)),after
            assert after['groups'].count('\tgold\t2')==4,after['groups']
            assert after['reply'].count('\tReply-Message\t:=\tHi team')==4,after['reply']
            assert all(f'unit-{i}\tBasic plan\t7\t1\tBilling Contact' in after['billing'] for i in range(1000,1004)),after
            if not BASELINE:
                reference=os.environ.get('BATCH_CREATE_BASELINE_JSON')
                if reference:
                    baseline=json.loads(Path(reference).read_text())
                    assert after==baseline['after'],(after,baseline['after'])
                    print('PASS generated account, RADIUS, billing and history match PEAR',file=sys.stderr)
                snapshot=state()
                assert 'Created 4 user(s)' not in submit()
                assert state()==snapshot
                assert 'Created 4 user(s)' not in submit(name='Other batch',start='1000')
                assert state()==snapshot
                print('PASS duplicate batch and existing generated account reject whole request',file=sys.stderr)
                sql("DELIMITER //\nCREATE TRIGGER fixture_fail_second BEFORE INSERT ON userinfo "
                    "FOR EACH ROW BEGIN IF NEW.username='unit-2001' THEN "
                    "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='forced fixture failure'; "
                    "END IF; END//\nDELIMITER ;\n")
                snapshot=state()
                failed=submit(name='Rollback batch',start='2000',extra={'firstname':'Rollback Person'})
                assert 'no accounts were created' in failed and 'forced fixture failure' not in failed
                assert state()==snapshot
                sql('DROP TRIGGER fixture_fail_second')
                print('PASS failure on second user rolls back history and all first-user writes',file=sys.stderr)
                for bad in ({'firstname[]':['nested']},
                            {'reply1[0][nested]':'x'},
                            {'group':'missing'},
                            {'planName':'missing'},
                            {'accountType':'invalid'},
                            {'passwordType':'invalid'},
                            {'hotspot_id':'hotspot-999'}):
                    submit(name='Invalid batch',start='3000',extra=bad)
                    assert state()==snapshot,bad
                print('PASS malformed fields, unknown group and plan cannot create batch',file=sys.stderr)
                session(9001,'named')
                quoted=submit(name='Named batch',start='4000',extra={'planName':"Plan 50% O'Reilly"})
                assert 'Created 4 user(s)' in quoted
                assert "unit-4000\tPlan 50% O'Reilly\t" in state()['billing']
                print('PASS named connection and quoted/percent plan',file=sys.stderr)
                pin=submit(name='PIN batch',prefix='pin-',extra={
                    'accountType':'random_pincode_no_password',
                    'length_user':'8','planName':'','group':''})
                assert 'Created 4 user(s)' in pin
                pins=sql("SELECT username,attribute,op,value FROM radcheck WHERE username LIKE 'pin-%' ORDER BY username")
                assert len(pins.splitlines())==4 and all('\tAuth-Type\t:=\tAccept' in line for line in pins.splitlines()),pins
                portal=submit(name='Portal batch',start='5000',extra={
                    'portalLoginPassword':'fixture-portal-secret','changeUserInfo':'1',
                    'enableUserPortalLogin':'1','bi_changeuserbillinfo':'1'})
                assert 'Created 4 user(s)' in portal
                hashes=sql("SELECT username,portalloginpassword,changeuserinfo,enableportallogin FROM userinfo WHERE username LIKE 'unit-500%' ORDER BY username")
                stored=[line.split('\t') for line in hashes.splitlines()]
                assert len(stored)==4 and all(x[1].startswith('$dalo$portal$v1$') and x[2:]==['1','1'] for x in stored)
                assert len({x[1] for x in stored})==4 and 'fixture-portal-secret' not in hashes
                portal_before=state()
                submit(name='No portal batch',start='6000',extra={'enableUserPortalLogin':'1'})
                assert state()==portal_before
                print('PASS PIN mode, per-user portal hash and portal password gate',file=sys.stderr)
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            assert 'PHP Fatal error' not in logs.stderr,'\n'.join(x for x in logs.stderr.splitlines() if 'PHP Fatal error' in x)
            print(json.dumps({'after':after}))
        finally:
            for name in (WEB,DB):run('docker','rm','-f','-v',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            run('docker','run','--rm','-v',f'{fixture}:/fixtures','--entrypoint','sh',
                'lirantal/daloradius','-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')

if __name__=='__main__':main()
