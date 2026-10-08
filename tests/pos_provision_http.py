#!/usr/bin/env python3
"""UNIT-011: isolated HTTP/PHP/MariaDB POS provisioning differential + rollback."""
import json
import os
from pathlib import Path
import secrets
import shutil
import sys
import tempfile
import urllib.error
import urllib.parse
import urllib.request
import user_actions_http as h
from acct_maintenance_http import Forms

ROOT=Path(os.environ.get('POS_ROOT',Path(__file__).resolve().parents[1]))
CANDIDATE=(ROOT/'app/operators/library/pos_provision.php').exists()
DB,WEB,NETWORK=h.DB,h.WEB,h.NETWORK
run,sql,wait_for=h.run,h.sql,h.wait_for


def main():
    scratch=Path.home()/'.hermes/cache/scratch'
    scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch,prefix='dalo-pos-provision-') as tmp:
        fixture=Path(tmp)
        shutil.copytree(ROOT/'app',fixture/'app',symlinks=True)
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            sql("""INSERT INTO operators_acl (operator_id,file,access) VALUES
                   (9001,'bill_pos_new',1),(9002,'bill_pos_new',0);
                   INSERT INTO radgroupcheck (groupname,attribute,op,value)
                     VALUES ('gold','Auth-Type',':=','Accept');
                   INSERT INTO radgroupreply (groupname,attribute,op,value)
                     VALUES ('silver','Session-Timeout',':=','60');
                   INSERT INTO billing_plans (planName,planId,planCost,planSetupCost,planTax,planActive)
                     VALUES ('Basic plan','basic-1','12.30','4.50','10','yes'),
                            ('Plan 50% O''Reilly','gold-1','12.30','4.50','10','yes'),
                            ('Inactive','disabled','2.00','','0','no');
                   INSERT INTO billing_plans_profiles (plan_name,profile_name)
                     VALUES ('Basic plan','gold'),('Basic plan','silver'),
                            ('Plan 50% O''Reilly','gold'),('Plan 50% O''Reilly','silver');""")
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
'operator_user'=>'pos-fixture','location_name'=>$argv[3],'time'=>time()];
session_write_close();
''')
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{fixture}:/fixtures','-w','/fixtures/app/operators',
                '--entrypoint','php','lirantal/daloradius','-d','display_errors=0',
                '-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/bill-pos-new.php'
            wait_for(lambda: urllib.request.urlopen(base,timeout=10),'PHP HTTP')
            sid=secrets.token_hex(16)
            def session(operator=9001,location='default'):
                run('docker','exec',WEB,'php','/fixtures/session.php',sid,str(operator),location)
            def request(data=None,authenticated=True):
                req=urllib.request.Request(base,
                    data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),
                    headers={'Cookie':'daloradius_operator_sid='+sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req,timeout=20) as response:
                        return response.url,response.read().decode()
                except urllib.error.HTTPError as error:
                    return error.url,error.read().decode()
            def csrf():
                return next(f['csrf_token'] for f in Forms(request()[1]).forms if 'csrf_token' in f)
            def submit(user='unit-alice',plan='Basic plan',profiles=None,extra=None,token=None):
                values={'csrf_token':csrf() if token is None else token,
                        'username':user,'password':'secret-for-fixture','passwordType':'SHA2-Password',
                        'planName':plan,'firstname':'Alice','lastname':'O\'Reilly',
                        'email':'alice@example.invalid','bi_contactperson':'Alice Contact',
                        'bi_company':'Example Co', 'bi_notes':'billing note'}
                if profiles is not None: values['profiles[]']=profiles
                if extra: values.update(extra)
                return request(values)[1]
            def state():
                return {
                    'check':sql("SELECT username,attribute,op,value FROM radcheck WHERE username LIKE 'unit-%' ORDER BY username,attribute,value"),
                    'reply':sql("SELECT username,attribute,op,value FROM radreply WHERE username LIKE 'unit-%' ORDER BY username,attribute,value"),
                    'groups':sql("SELECT username,groupname,priority FROM radusergroup WHERE username LIKE 'unit-%' ORDER BY username,groupname,priority"),
                    'info':sql("SELECT username,firstname,lastname,email,changeuserinfo,enableportallogin,portalloginpassword FROM userinfo WHERE username LIKE 'unit-%' ORDER BY username"),
                    'billing':sql("SELECT username,planName,contactperson,company,notes,lastbill,nextbill,changeuserbillinfo FROM userbillinfo WHERE username LIKE 'unit-%' ORDER BY username"),
                    'invoices':sql("SELECT b.username,DATE(i.date),i.status_id,i.type_id,i.notes FROM invoice i JOIN userbillinfo b ON b.id=i.user_id WHERE b.username LIKE 'unit-%' ORDER BY b.username,i.id"),
                    'items':sql("SELECT b.username,ii.plan_id,ii.amount,ii.tax_amount,ii.notes FROM invoice_items ii JOIN invoice i ON i.id=ii.invoice_id JOIN userbillinfo b ON b.id=i.user_id WHERE b.username LIKE 'unit-%' ORDER BY b.username,ii.id"),
                }
            session()
            before=state()
            assert request(authenticated=False)[0].endswith('/login.php')
            session(9002)
            assert request()[0].endswith('/home-error.php')
            assert request({'username':'unit-denied'})[0].endswith('/home-error.php')
            session()
            assert 'Inserted new user' not in submit(token='invalid')
            assert state()==before
            print('PASS authentication, ACL and CSRF prevent provisioning',file=sys.stderr)
            response=submit(profiles=['gold'],extra={'reply1[]':['Reply-Message',"Hi O'Reilly",':=','reply']})
            assert 'Inserted new user' in response,response[:1000]
            initial=state()
            assert 'unit-alice\tSHA2-Password\t:=' in initial['check']
            assert "unit-alice\tReply-Message\t:=\tHi O'Reilly" in initial['reply']
            assert initial['groups'].count('unit-alice\tgold\t0')==2,initial['groups']
            assert 'unit-alice\tsilver\t0' in initial['groups']
            assert 'unit-alice\t12.30\t1.23' not in initial['items']  # plan id precedes amount
            assert '\t12.30\t1.23\tcharge for plan service' in initial['items']
            assert '\t4.50\t0.45\tcharge for plan setup fee (one time)' in initial['items']
            if CANDIDATE and os.environ.get('POS_BASELINE_JSON'):
                baseline=json.loads(Path(os.environ['POS_BASELINE_JSON']).read_text())
                assert initial==baseline['initial'],(initial,baseline)
                print('PASS RADIUS, user, billing and invoice data match PEAR success path',file=sys.stderr)
            if CANDIDATE:
                assert 'Inserted new user' not in submit()
                assert state()==initial
                for user,plan,extra in [
                    ('unit-bad','Inactive',{}),('unit-bad','No such plan',{}),
                    ('unit-bad',"Plan 50% O'Reilly",{'firstname[]':['nested']}),
                    ('unit-bad',"Plan 50% O'Reilly",{'reply1[0][nested]':'x'})]:
                    submit(user=user,plan=plan,extra=extra)
                    assert state()==initial,(user,plan,extra)
                submit(user='unit-bad',profiles=['does-not-exist'])
                assert state()==initial
                print('PASS duplicate, inactive/unknown plan, invalid profiles and nested fields',file=sys.stderr)
                sql("DELETE FROM invoice_items WHERE notes = 'charge for plan setup fee (one time)'")
                sql("INSERT INTO invoice_items (invoice_id,plan_id,amount,tax_amount,notes) VALUES (999,999,0,0,'charge for plan setup fee (one time)')")
                sql('ALTER TABLE invoice_items ADD UNIQUE KEY fixture_unique_note (notes)')
                snapshot=state()
                invoice_rows=sql('SELECT invoice_id,notes FROM invoice_items ORDER BY id')
                failed=submit(user='unit-rollback')
                assert 'Failed to provision' in failed and 'Duplicate entry' not in failed
                assert state()==snapshot
                assert sql('SELECT invoice_id,notes FROM invoice_items ORDER BY id')==invoice_rows
                sql('ALTER TABLE invoice_items DROP KEY fixture_unique_note')
                sql('DELETE FROM invoice_items WHERE invoice_id=999')
                print('PASS second invoice item failure rolls back RADIUS, user, billing and invoice',file=sys.stderr)
                empty=submit(user='unit-no-plan',plan='',profiles=[])
                assert 'Inserted new user' in empty
                assert 'unit-no-plan\t\t' in state()['billing']
                assert 'unit-no-plan\t' not in state()['invoices']
                quoted=submit(user='unit-quoted',plan="Plan 50% O'Reilly")
                assert 'Inserted new user' in quoted
                assert 'unit-quoted\tgold\t0' in state()['groups']
                assert 'unit-quoted\tsilver\t0' in state()['groups']
                assert 'unit-quoted\t' in state()['invoices']
                invalid_portal=submit(user='unit-invalid-portal',plan='',
                                      extra={'enableUserPortalLogin':'1'})
                assert 'Inserted new user' not in invalid_portal
                assert 'unit-invalid-portal\t' not in state()['info']
                portal=submit(user='unit-portal',plan='',extra={
                    'portalLoginPassword':'fixture-portal-password',
                    'enableUserPortalLogin':'1','changeUserInfo':'1',
                    'bi_changeuserbillinfo':'1'})
                assert 'Inserted new user' in portal
                stored=sql("SELECT portalloginpassword FROM userinfo WHERE username='unit-portal'")
                assert stored.startswith('$dalo$portal$v1$') and 'fixture-portal-password' not in stored
                assert 'unit-portal\tAlice\t' in state()['info']
                assert '\t1\t1\t'+stored in state()['info']
                assert 'unit-portal\t\tAlice Contact\tExample Co\tbilling note\t0000-00-00\t0000-00-00\t1' in state()['billing']
                print('PASS no-plan, quoted name and hashed portal access',file=sys.stderr)
                session(9001,'named')
                named=submit(user='unit-named',profiles=['silver'])
                assert 'Inserted new user' in named
                assert 'unit-named\tsilver\t0' in state()['groups']
                print('PASS no-plan provisioning and named location',file=sys.stderr)
            import subprocess
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            warnings=[x for x in logs.stderr.splitlines() if 'PHP Warning:' in x]
            if not CANDIDATE:
                assert all('Undefined variable $groups' in warning for warning in warnings),warnings
            else:
                assert not warnings,'\n'.join(warnings[-5:])
            assert 'PHP Fatal error' not in logs.stderr,logs.stderr[-1000:]
            print(json.dumps({'initial':initial}))
        finally:
            for name in (WEB,DB): run('docker','rm','-f','-v',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            run('docker','run','--rm','-v',f'{fixture}:/fixtures','--entrypoint','sh',
                'lirantal/daloradius','-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')

if __name__=='__main__': main()
