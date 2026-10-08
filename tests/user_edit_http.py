#!/usr/bin/env python3
"""UNIT-018: disposable HTTP/PHP/MariaDB user-edit A/B and rollback."""
import hashlib
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
BASELINE=os.environ.get('USER_EDIT_BASELINE')=='1'
DB,WEB,NETWORK=h.DB,h.WEB,h.NETWORK
run,sql,wait_for=h.run,h.sql,h.wait_for


def main():
    scratch=Path.home()/'.hermes/cache/scratch'
    scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch,prefix='dalo-edit-') as tmp:
        fixture=Path(tmp)
        shutil.copytree(ROOT/'app',fixture/'app',symlinks=True)
        if BASELINE:
            page=fixture/'app/operators/mng-edit.php'
            page.write_bytes(subprocess.check_output(['git','show',
                'e3075ac0c:app/operators/mng-edit.php'],cwd=ROOT))
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,
                '--tmpfs','/var/lib/mysql','-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql',
                         'mariadb-daloradius-dictionaries.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            sql("""INSERT INTO operators_acl (operator_id,file,access) VALUES
                   (9001,'mng_edit',1),(9002,'mng_edit',0);
                   INSERT INTO radcheck (id,username,attribute,op,value) VALUES
                     (100,'fixture-a','Cleartext-Password',':=','old'),
                     (101,'fixture-b','Cleartext-Password',':=','other');
                   INSERT INTO radreply (id,username,attribute,op,value) VALUES
                     (200,'fixture-a','Reply-Message',':=','before');
                   INSERT INTO userinfo (id,username,firstname,creationby) VALUES
                     (100,'fixture-a','Before','original');
                   INSERT INTO userbillinfo (id,username,planName,contactperson,creationby) VALUES
                     (100,'fixture-a','plan-old','Before','original');
                   INSERT INTO billing_plans (id,planName,planActive) VALUES
                     (100,'plan-old','yes'),(101,'plan-new','yes');
                   INSERT INTO billing_plans_profiles (plan_name,profile_name) VALUES
                     ('plan-old','gold'),('plan-new','silver');
                   INSERT INTO radgroupcheck (groupname,attribute,op,value) VALUES
                     ('gold','Auth-Type',':=','Accept'),('silver','Auth-Type',':=','Accept');
                   INSERT INTO radusergroup (username,groupname,priority) VALUES
                     ('fixture-a','gold',0);""")
            config=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,val in {'CONFIG_DB_HOST':DB,'CONFIG_DB_USER':'root',
                            'CONFIG_DB_PASS':'','CONFIG_DB_NAME':'radius'}.items():
                config+='\n$configValues['+repr(key)+'] = '+repr(val)+';\n'
            (fixture/'app/common/includes/daloradius.conf.php').write_text(config)
            (fixture/'session.php').write_text('''<?php
session_name('daloradius_operator_sid');session_id($argv[1]);session_start();
$_SESSION=['daloradius_logged_in'=>true,'operator_id'=>(int)$argv[2],
'operator_user'=>'edit-fixture','location_name'=>'default','time'=>time()];
session_write_close();
''')
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{fixture}:/fixtures','-w','/fixtures/app/operators',
                '--entrypoint','php','lirantal/daloradius','-d','display_errors=0',
                '-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f',
                '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/mng-edit.php'
            wait_for(lambda:urllib.request.urlopen(base,timeout=10),'PHP HTTP')
            sid=secrets.token_hex(16)
            def session(op=9001):
                run('docker','exec',WEB,'php','/fixtures/session.php',sid,str(op))
            def request(data=None,username='fixture-a',authenticated=True):
                req=urllib.request.Request(base+('?'+urllib.parse.urlencode({'username':username}) if data is None else ''),
                    data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),
                    headers={'Cookie':'daloradius_operator_sid='+sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req,timeout=30) as response:
                        return response.url,response.read().decode()
                except urllib.error.HTTPError as error:
                    return error.url,error.read().decode()
            def csrf(username='fixture-a'):
                html=request(username=username)[1]
                assert 'name="csrf_token"' in html,html[:650]
                return next(f['csrf_token'] for f in Forms(html).forms
                            if 'csrf_token' in f and 'username' in f)
            def edit(extra=None,username='fixture-a',plan='plan-old',old='plan-old',groups=None):
                data={'username':username,'csrf_token':csrf(username),
                      'oldplanName':old,'planName':plan,
                      'firstname':'Alice','bi_contactperson':'Contact',
                      'editValues100[]':['100__Cleartext-Password','old',':=','radcheck'],
                      'editValues200[]':['200__Reply-Message','updated',':=','radreply']}
                if groups is not None:
                    for i,(g,p) in enumerate(groups):
                        data[f'groups[{i}][0]']=g
                        data[f'groups[{i}][1]']=str(p)
                if extra:data.update(extra)
                return request(data)[1]
            def state():
                return {key:sql('SELECT * FROM '+table+' ORDER BY '+ordering)
                        for key,table,ordering in (
                    ('check','radcheck','id'),('reply','radreply','id'),
                    ('ui','userinfo','username,id'),('bi','userbillinfo','username,id'),
                    ('group','radusergroup','username,groupname,priority'))}
            def projection():
                return {key:sql(query) for key,query in {
                    'check':'SELECT id,username,attribute,op,value FROM radcheck ORDER BY id',
                    'reply':'SELECT id,username,attribute,op,value FROM radreply ORDER BY id',
                    'ui':'SELECT username,firstname FROM userinfo ORDER BY username',
                    'bi':'SELECT username,planName,contactperson FROM userbillinfo ORDER BY username',
                    'group':'SELECT username,groupname,priority FROM radusergroup ORDER BY username,groupname',
                }.items()}
            session()
            before=state()
            before_projection=projection()
            assert request(authenticated=False)[0].endswith('login.php')
            session(9002)
            assert request()[0].endswith('home-error.php')
            session()
            assert 'Successfully updated user' not in edit({'csrf_token':'invalid'})
            assert state()==before
            response=edit(groups=[('gold',0)])
            assert 'Successfully updated user' in response,[x for x in response.splitlines()
                    if 'update' in x.lower() or 'error' in x.lower()][-8:]
            after=state()
            after_projection=projection()
            assert after['check']==before['check']
            assert 'updated' in after['reply'] and 'Alice' in after['ui'] and 'Contact' in after['bi']
            assert after_projection['group']==before_projection['group']
            print('PASS login, ACL, CSRF; attribute/info/billing/group state',file=sys.stderr)
            response=edit(plan='plan-new',old='plan-old',groups=[('gold',0)])
            assert 'Successfully updated user' in response
            switched=state()
            switched_projection=projection()
            assert 'silver' in switched['group'] and 'gold' not in switched['group']
            assert 'plan-new' in switched['bi']
            assert switched['check']==after['check']
            reference=os.environ.get('USER_EDIT_REFERENCE')
            if reference:
                comparable={'after':after_projection,'switched':switched_projection}
                if BASELINE:
                    Path(reference).write_text(json.dumps(comparable,sort_keys=True))
                else:
                    assert comparable==json.loads(Path(reference).read_text()), 'PEAR/PDO state mismatch'
            print('PASS plan mapping replacement with intact account',file=sys.stderr)
            if not BASELINE:
                before=state()
                assert 'no changes were saved' in edit(plan='plan-old',old='plan-old',groups=[('gold',0)])
                assert state()==before
                assert 'no changes were saved' in edit(plan='plan-new',old='plan-new',groups=[('gold',0)],
                    extra={'editValues101[]':['101__Cleartext-Password','malicious',':=','radcheck']})
                assert state()==before
                assert 'no changes were saved' in edit(plan='plan-new',old='plan-new',
                    groups=[('silver','bad')])
                assert state()==before
                malformed=edit(plan='plan-new',old='plan-new',groups=[('silver',0)],
                               extra={'enableUserPortalLogin[]':'1'})
                assert 'Invalid user edit input' in malformed
                assert state()==before
                sql("CREATE TRIGGER fixture_group_fail BEFORE INSERT ON radusergroup FOR EACH ROW "
                    "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='isolated failure'")
                assert 'no changes were saved' in edit(plan='plan-new',old='plan-new',
                    groups=[('silver',0)],extra={'bi_contactperson':'Rollback',
                    'editValues200[]':['200__Reply-Message','rollback',':=','radreply']})
                assert state()==before
                sql('DROP TRIGGER fixture_group_fail')
                print('PASS stale selection, foreign attribute, invalid group and late rollback',file=sys.stderr)
                assert sql("SELECT creationby FROM userbillinfo WHERE username='fixture-a'")=='original'
                response=edit(plan='plan-new',old='plan-new',groups=[('silver',0)],extra={
                    'editValues300[]':['0__SHA2-Password','synthetic-secret',':=','radcheck'],
                    'editValues301[]':['0__Reply-Message','0',':=','radreply']})
                assert 'Successfully updated user' in response,[x for x in response.splitlines() if 'alert-' in x or 'no changes' in x or 'error' in x.lower()][-12:]
                assert sql("SELECT value FROM radcheck WHERE username='fixture-a' "
                           "AND attribute='SHA2-Password'")==hashlib.sha256(b'synthetic-secret').hexdigest()
                assert sql("SELECT value FROM radreply WHERE username='fixture-a' "
                           "AND value='0'")=='0'
                first_count=sql("SELECT COUNT(*) FROM radcheck WHERE username='fixture-a' "
                                "AND attribute='SHA2-Password'")
                edit(plan='plan-new',old='plan-new',groups=[('silver',0)],extra={
                    'editValues300[]':['0__SHA2-Password','synthetic-secret',':=','radcheck']})
                assert sql("SELECT COUNT(*) FROM radcheck WHERE username='fixture-a' "
                           "AND attribute='SHA2-Password'")==first_count
                print('PASS hashed attribute insertion, duplicate suppression, literal zero',file=sys.stderr)
                quoted_name="quote%O'Reilly"
                quoted_plan="Plan 50% O'Reilly"
                sql("INSERT INTO radcheck (username,attribute,op,value) VALUES "
                    "('quote%O''Reilly','Cleartext-Password',':=','pw'); "
                    "INSERT INTO billing_plans (planName,planActive) VALUES "
                    "('Plan 50% O''Reilly','yes'); "
                    "INSERT INTO billing_plans_profiles (plan_name,profile_name) VALUES "
                    "('Plan 50% O''Reilly','silver')")
                data={'username':quoted_name,'csrf_token':csrf(quoted_name),
                      'oldplanName':'','planName':quoted_plan,'firstname':'New',
                      'bi_contactperson':'New billing','enableUserPortalLogin':'1',
                      'portalLoginPassword':'synthetic-portal-password'}
                response=request(data)[1]
                assert 'Successfully updated user' in response
                assert sql("SELECT firstname,creationby,enableportallogin FROM userinfo "
                           "WHERE username='quote%O''Reilly'")=='New\tedit-fixture\t1'
                assert sql("SELECT planName,contactperson FROM userbillinfo "
                           "WHERE username='quote%O''Reilly'")==quoted_plan+'\tNew billing'
                assert 'silver' in sql("SELECT groupname FROM radusergroup "
                           "WHERE username='quote%O''Reilly'")
                assert sql("SELECT portalloginpassword <> 'synthetic-portal-password' "
                           "FROM userinfo WHERE username='quote%O''Reilly'")=='1'
                stored_hash=sql("SELECT portalloginpassword FROM userinfo "
                                "WHERE username='quote%O''Reilly'")
                data={'username':quoted_name,'csrf_token':csrf(quoted_name),
                      'oldplanName':quoted_plan,'planName':quoted_plan,'firstname':'Retained',
                      'enableUserPortalLogin':'1',
                      'groups[0][0]':'silver','groups[0][1]':'0'}
                assert 'Successfully updated user' in request(data)[1]
                assert sql("SELECT portalloginpassword FROM userinfo "
                           "WHERE username='quote%O''Reilly'")==stored_hash
                print('PASS quoted/percent username and plan, first info/billing and portal hash preservation',file=sys.stderr)
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            assert 'PHP Fatal error' not in logs.stderr
            print('PASS UNIT-018 '+('PEAR baseline' if BASELINE else 'PDO candidate'))
        finally:
            for item in (WEB,DB):run('docker','rm','-f','-v',item,check=False)
            run('docker','network','rm',NETWORK,check=False)
            run('docker','run','--rm','-v',f'{fixture}:/fixtures','--entrypoint','sh',
                'lirantal/daloradius','-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')

if __name__=='__main__':main()
