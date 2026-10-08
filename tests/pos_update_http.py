#!/usr/bin/env python3
"""UNIT-012: isolated HTTP/PHP/MariaDB POS edit differential and rollback."""
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

ROOT = Path(__file__).resolve().parents[1]
DB, WEB, NETWORK = h.DB, h.WEB, h.NETWORK
run, sql, wait_for = h.run, h.sql, h.wait_for
BASELINE = os.environ.get('POS_EDIT_BASELINE') == '1'


def main():
    scratch = Path.home()/'.hermes/cache/scratch'
    scratch.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch, prefix='dalo-pos-edit-') as tmp:
        fixture = Path(tmp)
        shutil.copytree(ROOT/'app', fixture/'app', symlinks=True)
        if BASELINE:
            old = subprocess.check_output(['git', 'show', 'f467c162c:app/operators/bill-pos-edit.php'], cwd=ROOT)
            (fixture/'app/operators/bill-pos-edit.php').write_bytes(old)
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'), 'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            sql("""INSERT INTO operators_acl (operator_id,file,access) VALUES
                     (9001,'bill_pos_edit',1),(9002,'bill_pos_edit',0);
                   INSERT INTO radcheck (username,attribute,op,value)
                     VALUES ('unit-alice','Cleartext-Password',':=','password');
                   INSERT INTO userinfo (username,firstname,lastname,creationdate,creationby)
                     VALUES ('unit-alice','Before','Before','2026-01-01','fixture');
                   INSERT INTO userbillinfo (username,planName,contactperson,company,creationdate,creationby)
                     VALUES ('unit-alice','Basic plan','Before','Old Co','2026-01-01','fixture');
                   INSERT INTO radgroupcheck (groupname,attribute,op,value)
                     VALUES ('gold','Auth-Type',':=','Accept'),('bronze','Auth-Type',':=','Accept');
                   INSERT INTO radgroupreply (groupname,attribute,op,value)
                     VALUES ('silver','Session-Timeout',':=','60');
                   INSERT INTO radusergroup (username,groupname,priority)
                     VALUES ('unit-alice','bronze',5);
                   INSERT INTO billing_plans (planName,planId,planCost,planActive)
                     VALUES ('Basic plan','basic','12.30','yes'),
                            ('Plan 50% O''Reilly','quoted','12.30','yes');
                   INSERT INTO billing_plans_profiles (plan_name,profile_name)
                     VALUES ('Plan 50% O''Reilly','gold'),('Basic plan','silver');""")
            config=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,val in {'CONFIG_DB_HOST':DB,'CONFIG_DB_USER':'root',
                            'CONFIG_DB_PASS':'','CONFIG_DB_NAME':'radius'}.items():
                config+='\n$configValues['+repr(key)+'] = '+repr(val)+';\n'
            (fixture/'app/common/includes/daloradius.conf.php').write_text(config)
            (fixture/'session.php').write_text('''<?php
session_name('daloradius_operator_sid'); session_id($argv[1]); session_start();
$_SESSION = ['daloradius_logged_in'=>true,'operator_id'=>(int)$argv[2],
'operator_user'=>'pos-fixture','location_name'=>'default','time'=>time()];
session_write_close();
''')
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{fixture}:/fixtures','-w','/fixtures/app/operators',
                '--entrypoint','php','lirantal/daloradius','-d','display_errors=0',
                '-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/bill-pos-edit.php'
            wait_for(lambda: urllib.request.urlopen(base,timeout=10),'PHP HTTP')
            sid=secrets.token_hex(16)
            def session(operator=9001):
                run('docker','exec',WEB,'php','/fixtures/session.php',sid,str(operator))
            def request(data=None,authenticated=True):
                req=urllib.request.Request(base+('?username=unit-alice' if data is None else ''),
                    data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),
                    headers={'Cookie':'daloradius_operator_sid='+sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req,timeout=25) as response:
                        return response.url,response.read().decode()
                except urllib.error.HTTPError as error:
                    return error.url,error.read().decode()
            def csrf():
                return next(f['csrf_token'] for f in Forms(request()[1]).forms if 'csrf_token' in f)
            def state():
                return {key:sql(query) for key,query in {
                    'info':"SELECT username,firstname,lastname,company,changeuserinfo,enableportallogin FROM userinfo WHERE username='unit-alice'",
                    'billing':"SELECT username,planName,contactperson,company,notes,changeuserbillinfo FROM userbillinfo WHERE username='unit-alice'",
                    'groups':"SELECT username,groupname,priority FROM radusergroup WHERE username='unit-alice' ORDER BY groupname,priority",
                }.items()}
            def submit(plan='Basic plan',groups=None,reassign=False,extra=None,token=None):
                data={'username':'unit-alice','csrf_token':csrf() if token is None else token,
                      'planName':plan,'oldplanName':'Basic plan',
                      'firstname':'Updated','lastname':"O'Reilly",'company':'Unit Co',
                      'bi_contactperson':'Updated Contact','bi_company':'Billing Co',
                      'bi_notes':'new note'}
                if groups is not None:
                    for i,(name,priority) in enumerate(groups):
                        data[f'groups[{i}][0]']=name
                        data[f'groups[{i}][1]']=priority
                if reassign: data['reassignplanprofiles']='1'
                if extra: data.update(extra)
                return request(data)[1]
            session()
            before=state()
            assert request(authenticated=False)[0].endswith('/login.php')
            session(9002)
            assert request()[0].endswith('/home-error.php')
            session()
            assert 'CSRF token error' in submit(token='invalid')
            assert state()==before
            print('PASS authentication, ACL and CSRF',file=sys.stderr)
            output=submit(groups=[('silver','7')])
            after=state()
            assert 'unit-alice\tUpdated\t' in after['info'],output[:500]
            assert 'unit-alice\tBasic plan\tUpdated Contact\tBilling Co\tnew note' in after['billing'],after
            assert 'unit-alice\tsilver\t7' in after['groups'],after
            if not BASELINE:
                baseline_file=os.environ.get('POS_EDIT_BASELINE_JSON')
                if baseline_file:
                    expected=json.loads(Path(baseline_file).read_text())
                    assert after==expected['after'],(after,expected['after'])
                    print('PASS PEAR parity for normal edit',file=sys.stderr)
                snapshot=state()
                submit(groups=[('unknown','0')])
                assert state()==snapshot
                submit(plan='Unknown plan',reassign=True)
                assert state()==snapshot
                submit(groups=[('silver','wrong')])
                assert state()==snapshot
                for malformed in ({'planName[]':['bad']},
                                  {'oldplanName[]':['bad']},
                                  {'reassignplanprofiles[]':['1']},
                                  {'bi_contactperson[]':['bad']},
                                  {'groups[0][0]':'silver','groups[0][1][bad]':'3'}):
                    submit(extra=malformed)
                    assert state()==snapshot,malformed
                print('PASS unknown profile/plan and malformed input rejected',file=sys.stderr)
                sql("INSERT INTO radusergroup (username,groupname,priority) VALUES ('other','gold',0)")
                sql('ALTER TABLE radusergroup ADD UNIQUE KEY fixture_group (groupname,priority)')
                snapshot=state()
                failed=submit(groups=[('silver','3'),('gold','0')])
                assert 'Duplicate entry' not in failed
                assert state()==snapshot,(state(),snapshot)
                sql('ALTER TABLE radusergroup DROP KEY fixture_group')
                print('PASS later profile failure rolls back user and billing updates and groups',file=sys.stderr)
                response=submit(plan="Plan 50% O'Reilly",reassign=True)
                assert 'unit-alice\tgold\t0' in state()['groups'],response[:500]
                assert "unit-alice\tPlan 50% O'Reilly\t" in state()['billing']
                print('PASS quoted plan reassignment',file=sys.stderr)
                # The existing portal password is preserved on a blank password field.
                submit(plan='',groups=[],extra={'portalLoginPassword':'fixture-new-password',
                      'enableUserPortalLogin':'1','changeUserInfo':'1','bi_changeuserbillinfo':'1'})
                secret=sql("SELECT portalloginpassword FROM userinfo WHERE username='unit-alice'")
                assert secret.startswith('$dalo$portal$v1$') and 'fixture-new-password' not in secret
                assert 'unit-alice\t\t' in state()['billing']
                assert not state()['groups']
                submit(plan='',groups=[],extra={'enableUserPortalLogin':'1',
                      'changeUserInfo':'1','bi_changeuserbillinfo':'1'})
                assert sql("SELECT portalloginpassword FROM userinfo WHERE username='unit-alice'")==secret
                print('PASS no-plan edit, empty groups and hashed portal password preservation',file=sys.stderr)
                token=csrf()
                sql("DELETE FROM userinfo WHERE username='unit-alice'")
                sql("DELETE FROM userbillinfo WHERE username='unit-alice'")
                submit(plan='',groups=[('gold','2')],token=token)
                assert 'unit-alice\tUpdated\t' in state()['info']
                assert 'unit-alice\t\tUpdated Contact\t' in state()['billing']
                assert 'unit-alice\tgold\t2' in state()['groups']
                print('PASS missing optional user and billing rows are created',file=sys.stderr)
                sql("INSERT INTO radcheck (username,attribute,op,value) VALUES ('unit-50%','Cleartext-Password',':=','password')")
                sql("INSERT INTO userinfo (username,firstname) VALUES ('unit-50%','Percent')")
                sql("INSERT INTO userbillinfo (username,planName) VALUES ('unit-50%','')")
                percent_req=urllib.request.Request(base+'?username=unit-50%25',
                    headers={'Cookie':'daloradius_operator_sid='+sid})
                with urllib.request.urlopen(percent_req,timeout=25) as response:
                    assert 'unit-50%' in response.read().decode()
                print('PASS percent-bearing username remains addressable',file=sys.stderr)
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            assert 'PHP Fatal error' not in logs.stderr, '\n'.join(x for x in logs.stderr.splitlines() if 'PHP Fatal error' in x or 'Stack trace:' in x or 'Uncaught ' in x)
            print(json.dumps({'after':after}))
        finally:
            for name in (WEB,DB): run('docker','rm','-f','-v',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            run('docker','run','--rm','-v',f'{fixture}:/fixtures','--entrypoint','sh',
                'lirantal/daloradius','-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')

if __name__=='__main__': main()
