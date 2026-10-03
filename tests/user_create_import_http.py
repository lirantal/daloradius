#!/usr/bin/env python3
"""R03: disposable native PHP/HTTP/MariaDB differential and rollback tests."""
import concurrent.futures, hashlib, json, os, re, secrets, shutil, subprocess, tempfile, urllib.parse, urllib.request, urllib.error
from pathlib import Path
import user_actions_http as h
from acct_maintenance_http import Forms
ROOT=Path(__file__).resolve().parents[1]
BASE='a4f2c6ef2973c9ea037f5720ae402870dfef4acb'
SUFFIX=secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=['pdo-r03-'+k+'-'+SUFFIX for k in ('db','web','net')]
run,sql,wait_for=h.run,h.sql,h.wait_for
PAGES=['mng-new.php','mng-new-quick.php','mng-import-users.php','mng-edit.php','library/ajax/json_api.php','library/ajax/user_info.php']
def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='pdo-r03-',dir=scratch) as tmp:
        f=Path(tmp)
        for version in ('base','candidate'):
            shutil.copytree(ROOT/'app',f/version/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if version=='base':
                for rel in PAGES+['include/management/functions.php','include/management/groups.php','include/management/attributes.php']:
                    (f/version/'app/operators'/rel).write_bytes(subprocess.check_output(['git','show',BASE+':app/operators/'+rel],cwd=ROOT))
            conf=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,value in {'CONFIG_DB_HOST':h.DB,'CONFIG_DB_USER':'root','CONFIG_DB_PASS':'','CONFIG_DB_NAME':version}.items():
                conf+='\n$configValues['+repr(key)+']='+repr(value)+';\n'
            conf+="\n$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>'"+h.DB+"','Username'=>'root','Password'=>'','Database'=>'"+version+"_other','Port'=>'3306');\n"
            (f/version/'app/common/includes/daloradius.conf.php').write_text(conf)
        (f/'session.php').write_text("<?php session_name('daloradius_operator_sid');session_id($argv[1]);session_start();$_SESSION=['daloradius_logged_in'=>true,'operator_id'=>(int)($argv[3]??9001),'operator_user'=>'fixture','location_name'=>($argv[2]??'default'),'time'=>time()];session_write_close();")
        try:
            run('docker','network','create','--internal',h.NETWORK)
            run('docker','run','-d','--name',h.DB,'--network',h.NETWORK,'--tmpfs','/var/lib/mysql','-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            def db(q,version='candidate'):
                # Capture SQL diagnostics without recording bound values.
                p=subprocess.run(['docker','exec','-i',h.DB,'mariadb','-uroot','-N','-B',version],input="SET SESSION sql_mode=''; "+q,text=True,capture_output=True)
                if p.returncode: raise RuntimeError('Fixture SQL failed: '+repr(re.findall(r'ERROR (\d+)',p.stderr)))
                return p.stdout.strip()
            for version in ('base','candidate','base_other','candidate_other'):
                sql('CREATE DATABASE '+version)
                for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql','mariadb-daloradius-dictionaries.sql'):
                    db((ROOT/'contrib/db'/name).read_text(),version)
                db("INSERT INTO operators_acl(operator_id,file,access) VALUES (9001,'mng_new',1),(9001,'mng_new_quick',1),(9001,'mng_import_users',1),(9001,'mng_edit',1),(9001,'mng_search',1),(9001,'acct_username',1); INSERT INTO operators_acl(operator_id,file,access) SELECT 9002,file,0 FROM operators_acl WHERE operator_id=9001; INSERT INTO radgroupcheck(groupname,attribute,op,value) VALUES ('gold','Auth-Type',':=','Accept');",version)
            run('docker','run','-d','--name',h.WEB,'--network',h.NETWORK,'-v',str(f)+':/fixtures','-w','/fixtures','-e','PHP_CLI_SERVER_WORKERS=4','--entrypoint','php','lirantal/daloradius','-d','opcache.enable_cli=0','-d','opcache.enable=0','-d','display_errors=0','-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',h.WEB)
            sid=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',sid)
            def req(page,data=None,version='candidate',query=None,session_id=None):
                url='http://'+ip+':8080/'+version+'/app/operators/'+page
                if query:url+='?'+urllib.parse.urlencode(query)
                request=urllib.request.Request(url,data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),headers={'Cookie':'daloradius_operator_sid='+(session_id or sid)})
                try:
                    with urllib.request.urlopen(request,timeout=30) as response:return response.status,response.read().decode()
                except urllib.error.HTTPError as e:return e.code,e.read().decode()
            wait_for(lambda:req('mng-new.php'),'PHP')
            def post(page,fields,version='candidate',session_id=None):
                html=req(page,version=version,session_id=session_id)[1]
                forms=Forms(html).forms
                token=next(form['csrf_token'] for form in forms if 'csrf_token' in form)
                return req(page,dict(fields,csrf_token=token),version,session_id=session_id)[1]
            def state(version='candidate'):
                # Credentials and payment data never enter snapshots.
                return {k:db(q,version) for k,q in {
                    'check':"SELECT username,attribute,op FROM radcheck ORDER BY username,attribute",
                    'reply':"SELECT username,attribute,op,value FROM radreply ORDER BY username,attribute",
                    'ui':"SELECT username,firstname,email,enableportallogin FROM userinfo ORDER BY username",
                    'bi':"SELECT username,contactperson,planName FROM userbillinfo ORDER BY username",
                    'group':"SELECT username,groupname,priority FROM radusergroup ORDER BY username,groupname"}.items()}
            def rollback_state():
                result={}
                for table in ('radcheck','radreply','userinfo','userbillinfo','radusergroup'):
                    cols=db("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='candidate' AND TABLE_NAME='"+table+"' ORDER BY ORDINAL_POSITION").splitlines()
                    excluded={'portalloginpassword','creditcardname','creditcardnumber','creditcardverification','creditcardtype','creditcardexp'}
                    fields=[]
                    for column in cols:
                        assert re.fullmatch('[A-Za-z_][A-Za-z0-9_]*',column)
                        if column in excluded or (column=='value' and table=='radcheck'):continue
                        if column=='value' and table=='radreply':fields.append("CASE WHEN attribute LIKE '%-Password' THEN '[excluded]' ELSE value END")
                        else:fields.append('`'+column+'`')
                    result[table]=db('SELECT '+','.join(fields)+' FROM '+table+' ORDER BY id')
                return result
            password=secrets.token_hex(12)
            ordinary=[('mng-new.php',{'username':'standard','password':password,'authType':'userAuth','passwordType':'SHA2-Password','firstname':'Alice','groups[]':['gold']}),
                ('mng-new-quick.php',{'username':'quick','password':password,'passwordType':'SHA2-Password','firstname':'Alice','sessiontimeout':'300','groups[]':['gold']}),
                ('mng-import-users.php',{'authType':'userAuth','passwordType':'SHA2-Password','csvdata':'imported,'+password+',alice@example.org,Alice,Smith,,,,,,,,,,,,300,40,600','groups[]':['gold']})]
            denied_sid=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',denied_sid,'default','9002')
            for page in PAGES:
                url='http://'+ip+':8080/candidate/app/operators/'+page
                query={'username':'standard','datatype':'usernames','action':'list'}
                url+='?'+urllib.parse.urlencode(query)
                for cookie,denied in [(None,False),(denied_sid,True)]:
                    request=urllib.request.Request(url,headers={} if cookie is None else {'Cookie':'daloradius_operator_sid='+cookie})
                    try:
                        with urllib.request.urlopen(request,timeout=30) as response:
                            assert response.geturl().endswith('home-error.php' if denied else 'login.php'),(page,'authorization gate')
                    except urllib.error.HTTPError as error:
                        assert denied and error.code==403,(page,'authorization status')
            before=rollback_state()
            for page,fields in ordinary:
                html=req(page,dict(fields,csrf_token='invalid'))[1]
                assert 'CSRF token error' in html;assert rollback_state()==before
            for page,fields in ordinary:
                for version in ('base','candidate'):
                    html=post(page,fields,version)
                    assert 'Inserted new' in html or 'Successfully imported' in html,(page,version,'creation unsuccessful')
                legacy,candidate=state('base'),state()
                if page=='mng-import-users.php':
                    # Legacy passed raw table names to a key-only helper, silently dropping reply attributes.
                    legacy.pop('reply'); candidate.pop('reply')
                    assert db("SELECT COUNT(*) FROM radreply WHERE username='imported' AND attribute IN ('Session-Timeout','Idle-Timeout')")== '2'
                assert legacy==candidate,(page,'differential state mismatch')
            for page in ('mng-new.php','mng-new-quick.php'):
                before=rollback_state();fields=dict(ordinary[0 if page=='mng-new.php' else 1][1]);fields['username']='late-'+page
                db("CREATE TRIGGER fail_billing BEFORE INSERT ON userbillinfo FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture failure'")
                html=post(page,fields);assert 'Unable to create or import users' in html;assert rollback_state()==before
                db('DROP TRIGGER fail_billing')
                fields['username']='invalid-'+page;fields['firstname[]']=['bad'];fields.pop('firstname',None)
                assert 'Unable to create or import users' in post(page,fields);assert rollback_state()==before
            # Real dynamic-attribute producer names and blank default row.
            fields=dict(ordinary[0][1],username='attributes')
            fields['dictValues0[]']=['Reply-Message','0',':=','reply']
            fields['dictValues1[]']=['','',':=','check']
            assert 'Inserted new' in post('mng-new.php',fields)
            assert db("SELECT value FROM radreply WHERE username='attributes' AND attribute='Reply-Message'")=='0'
            before=rollback_state();fields['username']='late-invalid-attribute'
            fields['dictValues2[]']=['Reply-Message','bad','bogus','reply']
            assert 'Unable to create or import users' in post('mng-new.php',fields);assert rollback_state()==before
            for page,fields in [('mng-new.php',dict(ordinary[0][1],username='literal%+name')),('mng-new-quick.php',dict(ordinary[1][1],username='zero-password',password='0'))]:
                assert 'Inserted new' in post(page,fields)
            for auth,name,key in [('macAuth','aa:bb:cc:dd:ee:ff','macaddress'),('pincodeAuth','123456','pincode')]:
                fields={'authType':auth,key:name}
                assert 'Inserted new' in post('mng-new.php',fields)
                assert db("SELECT value FROM radcheck WHERE username='"+name+"' AND attribute='Auth-Type'")=='Accept'
            before=rollback_state()
            bad={'authType':'userAuth','passwordType':'SHA2-Password','csvdata':'valid,'+password+',a@example.org,Alice,Smith\ninvalid'}
            assert 'Unable to create or import users' in post('mng-import-users.php',bad);assert rollback_state()==before
            # Two independent sessions and PHP workers race on the same missing account.
            sessions=[secrets.token_hex(16),secrets.token_hex(16)]
            for token in sessions:run('docker','exec',h.WEB,'php','/fixtures/session.php',token)
            fields=dict(ordinary[1][1],username='concurrent')
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:
                results=list(pool.map(lambda token:post('mng-new-quick.php',fields,session_id=token),sessions))
            assert sum('Inserted new' in result for result in results)==1
            assert db("SELECT COUNT(*) FROM radcheck WHERE username='concurrent'")=='1'
            before=rollback_state()
            db("CREATE TRIGGER fail_later BEFORE INSERT ON userinfo FOR EACH ROW SET NEW.id=IF(NEW.username='second',1,NEW.id)")
            fields={'authType':'userAuth','passwordType':'SHA2-Password','csvdata':'first,'+password+',a@example.org,Alice,Smith\nsecond,'+password+',b@example.org,Bob,Smith'}
            assert 'Unable to create or import users' in post('mng-import-users.php',fields);assert rollback_state()==before
            db('DROP TRIGGER fail_later')
            # A duplicate import is intentionally skipped, matching the legacy policy.
            assert 'No users have been imported' in post('mng-import-users.php',ordinary[2][1]);assert rollback_state()==before
            for page,fields in ordinary[:2]:
                before=rollback_state();post(page,fields);assert rollback_state()==before
            db('ALTER TABLE radreply ENGINE=MyISAM')
            fields=dict(ordinary[1][1],username='nontransactional');before=rollback_state()
            assert 'Unable to create or import users' in post('mng-new-quick.php',fields);assert rollback_state()==before
            db('ALTER TABLE radreply ENGINE=InnoDB')
            for page,query in [('library/ajax/json_api.php',{'datatype':'usernames','action':'list','username':'sta'}),('library/ajax/user_info.php',{'username':'standard'})]:
                status,body=req(page,query=query);assert status==200;data=json.loads(body)
                assert data==json.loads(req(page,version='base',query=query)[1])
            assert req('library/ajax/json_api.php',query={'datatype[]':'bad'})[0]==200
            assert req('library/ajax/user_info.php',query={'username[]':'bad'})[0]==400
            db('RENAME TABLE radacct TO absent_radacct')
            status,body=req('library/ajax/user_info.php',query={'username':'standard'});assert status==500 and json.loads(body).get('error')
            db('RENAME TABLE absent_radacct TO radacct')
            assert 'standard' in req('mng-edit.php',query={'username':'standard'})[1]
            # Simple-list PIN/MAC numeric keys must remain string account identities.
            fields={'authType':'otherAuth','simpleList':'456789,a@example.org,Alice,Smith','groups[]':['gold']}
            assert 'Successfully imported' in post('mng-import-users.php',fields)
            assert db("SELECT value FROM radcheck WHERE username='456789' AND attribute='Auth-Type'")=='Accept'
            # Portal and billing policy, hash verification without snapshotting credentials.
            portal=secrets.token_hex(12)
            fields=dict(ordinary[1][1],username='portal',portalLoginPassword=portal,enableUserPortalLogin='1',changeUserInfo='1')
            assert 'Inserted new' in post('mng-new-quick.php',fields)
            assert db("SELECT enableportallogin FROM userinfo WHERE username='portal'")=='1'
            before=rollback_state();fields=dict(ordinary[0][1],username='portal-invalid',enableUserPortalLogin='1')
            post('mng-new.php',fields);assert rollback_state()==before
            fields={'authType':'userAuth','passwordType':'SHA2-Password','generatepassword':'yes','csvdata':'generated,,a@example.org,Alice,Smith'}
            html=post('mng-import-users.php',fields)
            assert 'Successfully imported' in html and 'generated-passwords-export-form' in html
            assert db("SELECT COUNT(*) FROM radcheck WHERE username='generated'")=='1'
            before=rollback_state()
            db("CREATE TRIGGER fail_generated BEFORE INSERT ON userinfo FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture failure'")
            fields['csvdata']='generated-fail,,b@example.org,Bob,Smith'
            html=post('mng-import-users.php',fields)
            assert 'Unable to create or import users' in html and 'generated-passwords-export-form' not in html
            assert rollback_state()==before;db('DROP TRIGGER fail_generated')
            # All write/read endpoints must target the explicitly selected location.
            location_sid=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',location_sid,'other')
            for page,original in ordinary:
                fields=dict(original)
                if page=='mng-import-users.php':fields['csvdata']='located-import,'+password+',a@example.org,Alice,Smith'
                else:fields['username']='located-'+('quick' if 'quick' in page else 'standard')
                html=post(page,fields,session_id=location_sid)
                assert 'Inserted new' in html or 'Successfully imported' in html
            assert db("SELECT COUNT(*) FROM radcheck WHERE username LIKE 'located-%'",'candidate_other')=='3'
            assert db("SELECT COUNT(*) FROM radcheck WHERE username LIKE 'located-%'")=='0'
            status,body=req('library/ajax/json_api.php',query={'datatype':'usernames','action':'list','username':'located'},session_id=location_sid)
            assert status==200 and len(json.loads(body))==3
            assert 'located-standard' in req('mng-edit.php',query={'username':'located-standard'},session_id=location_sid)[1]
            (f/'probe.php').write_text('<?php\n$_SERVER[\'PHP_SELF\']=\'probe.php\';\nrequire \'/fixtures/candidate/app/common/includes/config_read.php\';\nrequire \'/fixtures/candidate/app/operators/include/management/functions.php\';\nrequire \'/fixtures/candidate/app/operators/library/user_create.php\';\n$pdo=dalo_pdo_connect($configValues);$pdo->beginTransaction();\n$pdo->exec("INSERT INTO radcheck(username,attribute,op,value) VALUES (\'borrowed\',\'Auth-Type\',\':=\',\'Accept\')");\nassert(add_user_info($pdo,\'borrowed\',array(\'firstname\'=>\'Zero\',\'enableportallogin\'=>\'0\'))===true);\nassert(update_user_info($pdo,\'borrowed\',array(\'firstname\'=>\'Updated\'))===true);\nassert(insert_single_attribute($pdo,\'borrowed\',\'Reply-Message\',\':=\',\'0\',\'CONFIG_DB_TBL_RADREPLY\')===true);\n$arr=prepare_fields_and_values($pdo,\'borrowed\',array(\'firstname\'=>"O\'Reilly"),array(\'firstname\'),array(),\'CONFIG_DB_TBL_DALOUSERINFO\');\nassert($arr[\'values\']===array("O\'Reilly"));\nassert($pdo->inTransaction());$pdo->rollBack();\nassert($pdo->query("SELECT COUNT(*) FROM radcheck WHERE username=\'borrowed\'")->fetchColumn()==0);\nassert($pdo->query("SELECT COUNT(*) FROM userinfo WHERE username=\'borrowed\'")->fetchColumn()==0);\necho \'BORROWED_PASS\';\n')
            assert run('docker','exec',h.WEB,'php','-d','zend.assertions=1','-d','assert.exception=1','/fixtures/probe.php')=='BORROWED_PASS'
            (f/'legacy-probe.php').write_text('<?php\n$_SERVER[\'PHP_SELF\']=\'probe.php\';\nrequire \'/fixtures/candidate/app/common/includes/db_open.php\';\nrequire \'/fixtures/candidate/app/operators/include/management/functions.php\';\n$dbSocket->query(\'START TRANSACTION\');\n$dbSocket->query("INSERT INTO radcheck(username,attribute,op,value) VALUES (\'legacy-borrowed\',\'Auth-Type\',\':=\',\'Accept\')");\nassert(add_user_info($dbSocket,\'legacy-borrowed\',array(\'firstname\'=>\'Before\'))===true);\nassert(update_user_info($dbSocket,\'legacy-borrowed\',array(\'firstname\'=>\'After\'))===true);\nassert(insert_single_attribute($dbSocket,\'legacy-borrowed\',\'Reply-Message\',\':=\',\'0\')===true);\n$dbSocket->query(\'ROLLBACK\');\nassert(!user_exists($dbSocket,\'legacy-borrowed\'));\necho \'LEGACY_PASS\';\n')
            assert run('docker','exec','-w','/fixtures/candidate/app/operators',h.WEB,'php','-d','zend.assertions=1','-d','assert.exception=1','/fixtures/legacy-probe.php')=='LEGACY_PASS'
            # Configured non-default table names for creation and AJAX.
            configfile=f/'candidate/app/common/includes/daloradius.conf.php'
            conf=configfile.read_text()
            for key,old,new in [('RADCHECK','radcheck','custom_check'),('RADREPLY','radreply','custom_reply'),('DALOUSERINFO','userinfo','custom_info'),('DALOUSERBILLINFO','userbillinfo','custom_bill'),('RADUSERGROUP','radusergroup','custom_group'),('RADACCT','radacct','custom_acct')]:
                db('RENAME TABLE '+old+' TO '+new)
                conf+="\n$configValues['CONFIG_DB_TBL_"+key+"']='"+new+"';\n"
            configfile.write_text(conf)
            fields=dict(ordinary[1][1],username='custom-tables')
            assert 'Inserted new' in post('mng-new-quick.php',fields)
            assert db("SELECT COUNT(*) FROM custom_check WHERE username='custom-tables'")=='1'
            status,body=req('library/ajax/json_api.php',query={'datatype':'usernames','action':'list','username':'custom'})
            assert status==200 and json.loads(body)==['custom-tables']
            assert req('library/ajax/user_info.php',query={'username':'custom-tables'})[0]==200

            log_result=subprocess.run(['docker','logs',h.WEB],text=True,capture_output=True)
            log_lines=(log_result.stdout+log_result.stderr).splitlines()
            assert password not in '\n'.join(log_lines) and portal not in '\n'.join(log_lines) and hashlib.sha256(password.encode()).hexdigest() not in '\n'.join(log_lines),'Credential diagnostic detected (details suppressed)'
            assert not any('/fixtures/candidate/' in line and ('PHP Warning:' in line or 'PHP Fatal error:' in line) for line in log_lines),'Candidate PHP diagnostic detected (details suppressed)'
            print('R03 native differential create/quick/import, caller rollback, duplicate, malformed, non-InnoDB, AJAX parity/errors and edit display: PASS')
        finally:
            run('docker','rm','-f',h.WEB,h.DB,check=False);run('docker','network','rm',h.NETWORK,check=False)
            subprocess.run(['sudo','-n','chown','-R',str(os.getuid())+':'+str(os.getgid()),str(f)],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
if __name__=='__main__':main()
