#!/usr/bin/env python3
"""R05: native user-group page parity, atomicity and compatibility tests."""
import concurrent.futures, json, os, re, secrets, shutil, subprocess, tempfile, urllib.parse, urllib.request, urllib.error
from html.parser import HTMLParser
from pathlib import Path
import user_actions_http as h
from acct_maintenance_http import Forms
ROOT=Path(__file__).resolve().parents[1]
BASE='fb33d38a505bf8d3a1cfcd3d987b3f67d939932b'
SUFFIX=secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=['pdo-r05-'+k+'-'+SUFFIX for k in ('db','web','net')]
run,sql,wait_for=h.run,h.sql,h.wait_for
PAGES=['mng-rad-usergroup-list.php', 'mng-rad-usergroup-list-user.php', 'mng-rad-usergroup-del.php', 'mng-rad-usergroup-new.php', 'mng-rad-usergroup-edit.php']

class Rows(HTMLParser):
    def __init__(self, html):
        super().__init__(convert_charrefs=True);self.rows=[];self.row=None;self.cell=None;self.feed(html)
    def handle_starttag(self, tag, attrs):
        a=dict(attrs)
        if tag=='tr':self.row={'selected':None,'cells':[]}
        if self.row is not None:
            if tag=='td':self.cell=[]
            if tag=='input' and a.get('name') in ('usergroup[]',):self.row['selected']=a.get('value')
    def handle_data(self,data):
        if self.cell is not None:self.cell.append(data)
    def handle_endtag(self,tag):
        if tag=='td' and self.row is not None and self.cell is not None:
            self.row['cells'].append(' '.join(''.join(self.cell).split()));self.cell=None
        if tag=='tr' and self.row is not None:
            if self.row['selected'] is not None:self.rows.append((self.row['selected'],self.row['cells']))
            self.row=None

def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='pdo-r05-',dir=scratch) as tmp:
        f=Path(tmp)
        for version in ('base','candidate'):
            shutil.copytree(ROOT/'app',f/version/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if version=='base':
                for rel in PAGES+['include/management/functions.php','include/management/groups.php']:
                    (f/version/'app/operators'/rel).write_bytes(subprocess.check_output(['git','show',BASE+':app/operators/'+rel],cwd=ROOT))
            conf=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,value in {'CONFIG_DB_HOST':h.DB,'CONFIG_DB_USER':'root','CONFIG_DB_PASS':'','CONFIG_DB_NAME':version}.items():
                conf+='\n$configValues['+repr(key)+']='+repr(value)+';\n'
            conf+="\n$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>'"+h.DB+"','Username'=>'root','Password'=>'','Database'=>'"+version+"_other','Port'=>'3306');\n"
            (f/version/'app/common/includes/daloradius.conf.php').write_text(conf)
        for version in ('base','candidate'):
            (f/version/'app/operators/widget.php').write_text("<?php\ninclude 'library/checklogin.php';\ninclude_once '../common/includes/config_read.php';\ninclude_once 'lang/main.php';\ninclude_once '../common/includes/validation.php';\ninclude_once '../common/includes/layout.php';\ninclude_once 'include/management/functions.php';\n$username=is_string($_GET['username']??null)?$_GET['username']:'';\n$logDebugSQL='';\nif (__BASE__) { include '../common/includes/db_open.php'; }\nelse { require_once '../common/includes/pdo_connection.php'; $dbSocket=dalo_pdo_connect($configValues,$_SESSION['location_name']??'default'); }\ninclude 'include/management/groups.php';\nif (__BASE__) { include '../common/includes/db_close.php'; }\n".replace('__BASE__','true' if version=='base' else 'false'))
        (f/'base/app/operators/compat_widget.php').write_text("<?php\ninclude 'library/checklogin.php';\ninclude_once '../common/includes/config_read.php';\ninclude_once 'lang/main.php';\ninclude_once '../common/includes/validation.php';\ninclude_once '../common/includes/layout.php';\ninclude_once 'include/management/functions.php';\n$username=is_string($_GET['username']??null)?$_GET['username']:'';\n$logDebugSQL='';\nif (true) { include '../common/includes/db_open.php'; }\nelse { require_once '../common/includes/pdo_connection.php'; $dbSocket=dalo_pdo_connect($configValues,$_SESSION['location_name']??'default'); }\ninclude '/fixtures/candidate/app/operators/include/management/groups.php';\nif (true) { include '../common/includes/db_close.php'; }\n")
        (f/'candidate/app/operators/borrowed.php').write_text('<?php\ninclude \'library/checklogin.php\';include_once \'../common/includes/config_read.php\';\ninclude_once \'lang/main.php\';include_once \'../common/includes/layout.php\';\ninclude_once \'include/management/functions.php\';require_once \'../common/includes/pdo_connection.php\';\n$logDebugSQL=\'\';$dbSocket=dalo_pdo_connect($configValues,$_SESSION[\'location_name\']??\'default\');\n$dbSocket->beginTransaction();$username=\'alice\';\n$dbSocket->exec("INSERT INTO radreply(username,attribute,op,value) VALUES (\'owned-caller\',\'Class\',\':=\',\'sentinel\')");\nif (($_GET[\'action\']??\'\')===\'delete\') { $ok=delete_user_group_mappings($dbSocket,$username); }\nelse { ob_start();include \'include/management/groups.php\';ob_end_clean();$ok=true; }\n$active=$dbSocket->inTransaction();$dbSocket->rollBack();\necho json_encode(array(\'ok\'=>$ok,\'active\'=>$active));\n')
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
                db("INSERT INTO operators_acl(operator_id,file,access) VALUES (9001,'mng_rad_usergroup_list',1),(9001,'mng_rad_usergroup_list_user',1),(9001,'mng_rad_usergroup_del',1),(9001,'mng_rad_usergroup_new',1),(9001,'mng_rad_usergroup_edit',1); INSERT INTO operators_acl(operator_id,file,access) SELECT 9002,file,0 FROM operators_acl WHERE operator_id=9001; INSERT INTO radgroupcheck(groupname,attribute,op,value) VALUES ('gold','Auth-Type',':=','Accept'),('bronze','Class',':=','bronze'),('amber','Class',':=','amber'),('copper','Class',':=','copper'),('daloRADIUS-Disabled-Users','Auth-Type',':=','Reject'); INSERT INTO radgroupreply(groupname,attribute,op,value) VALUES ('silver','Class',':=','silver'); INSERT INTO radcheck(username,attribute,op,value) VALUES ('alice','Class',':=','alice'),('bob','Class',':=','bob'),('charlie','Class',':=','charlie'),('delete-all','Class',':=','all'); INSERT INTO userinfo(username,firstname,lastname) VALUES ('alice','Alice','Alpha'),('bob','Bob','Beta'),('charlie','Charlie','Gamma');",version)
            for rel in ('db_open.php','db_close.php'):
                (f/'candidate/app/common/includes'/rel).write_text("<?php throw new RuntimeException('Legacy database tripwire');")
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
            wait_for(lambda:req('mng-rad-usergroup-new.php'),'PHP')
            def post(page,fields,version='candidate',session_id=None,query=None):
                html=req(page,version=version,session_id=session_id,query=query)[1]
                forms=Forms(html).forms
                if not any('csrf_token' in form for form in forms):
                    # A stale edit can lose its form; retain a valid session token from the real create form.
                    forms=Forms(req('mng-rad-usergroup-new.php',version=version,session_id=session_id)[1]).forms
                token=next(form['csrf_token'] for form in forms if 'csrf_token' in form)
                return req(page,dict(fields,csrf_token=token),version,session_id=session_id)[1]
            new='mng-rad-usergroup-new.php';edit='mng-rad-usergroup-edit.php';delete='mng-rad-usergroup-del.php'
            def state(version='candidate'):
                return db('SELECT id,username,groupname,priority FROM radusergroup ORDER BY id',version)
            def create(user='alice',group='gold',priority='0',version='candidate',session_id=None):
                return post(new,{'username':user,'group':group,'priority':priority},version,session_id)
            def update(user='alice',current='gold',group='gold',priority='0',version='candidate',session_id=None):
                return post(edit,{'username':user,'current_group':current,'group':group,'priority':priority},version,session_id,query={'username':user,'current_group':current})
            for version in ('base','candidate'):
                configfile=f/version/'app/common/includes/daloradius.conf.php'
                configfile.write_text(configfile.read_text()+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='2';\n")
                assert 'Added new user-group mapping' in create(version=version)
                assert 'Updated user-group mapping' in update(group='silver',priority='7',version=version)
                assert 'Added new user-group mapping' in create('alice','amber','10',version)
                assert 'Added new user-group mapping' in create('alice','copper','12',version)
                assert 'Added new user-group mapping' in create('bob','gold','2',version)
                assert 'Added new user-group mapping' in create('charlie','bronze','3',version)
            assert state('base')==state(),'ordinary mutation state'
            comparisons=0
            for page in ('mng-rad-usergroup-list.php','mng-rad-usergroup-list-user.php'):
                sorts=('username','fullname') if page.endswith('-list.php') else ('groupname','priority')
                for order in sorts:
                    for direction in ('asc','desc'):
                        for page_num in (1,2):
                            query={'orderBy':order,'orderType':direction,'page':str(page_num)}
                            if page.endswith('-list-user.php'):query['username']='alice'
                            left=Rows(req(page,version='base',query=query)[1]).rows
                            right=Rows(req(page,query=query)[1]).rows
                            assert left==right,(page,order,direction,page_num,'row parity');comparisons+=1
                for query in ({'username':'alice'},{'username':'not-found'}):
                    assert Rows(req(page,version='base',query=query)[1]).rows==Rows(req(page,query=query)[1]).rows
            base_form=Forms(req(edit,version='base',query={'username':'alice','current_group':'silver'})[1]).forms
            candidate_form=Forms(req(edit,query={'username':'alice','current_group':'silver'})[1]).forms
            assert [(x.get('username'),x.get('current_group'),x.get('priority')) for x in base_form]==[(x.get('username'),x.get('current_group'),x.get('priority')) for x in candidate_form]
            # Native widget parity, then preserve a real caller write/transaction through widget and provider.
            assert Forms(req('widget.php',version='base',query={'username':'alice'})[1]).forms==Forms(req('widget.php',query={'username':'alice'})[1]).forms
            assert Forms(req('compat_widget.php',version='base',query={'username':'alice'})[1]).forms==Forms(req('widget.php',query={'username':'alice'})[1]).forms
            before=state()
            for action in ('widget','delete'):
                status,html=req('borrowed.php',query={'action':action});assert status==200
                assert json.loads(html)=={'ok':True,'active':True};assert state()==before
                assert db("SELECT COUNT(*) FROM radreply WHERE username='owned-caller'")=='0'
            for version in ('base','candidate'):
                assert 'Deleted' in post(delete,{'usergroup[]':['charlie||bronze']},version)
            assert state('base')==state(),'ordinary deletion state'
            print('PASS native PEAR/PDO creation, rename, deletion,',comparisons,'list projections, edit controls and borrowed widget/provider rollback')
            before=state()
            for fields in ({'username[]':['alice'],'group':'gold'},{'username':'alice','group[]':['gold']},
                           {'username':'alice','group':'gold','priority[]':['1']},
                           {'username':'alice','group':'gold','priority':'1.5'},
                           {'username':'alice','group':'gold','priority':'2147483648'},
                           {'username':'missing','group':'gold'}, {'username':'alice','group':'missing'}):
                assert 'Unable to create user-group mapping' in post(new,fields);assert state()==before
            assert 'Unable to create user-group mapping' in create('alice','silver');assert state()==before
            assert 'Unable to update user-group mapping' in update(current='missing',group='gold');assert state()==before
            assert 'Unable to update user-group mapping' in update(current='silver',group='missing');assert state()==before
            assert 'Added new user-group mapping' in create('alice','gold','-10')
            assert db("SELECT priority FROM radusergroup WHERE username='alice' AND groupname='gold'")=='0'
            before=state()
            assert 'Unable to update user-group mapping' in update(current='silver',group='gold');assert state()==before
            # Both the page UPDATE and a later multi-delete fail with no partial mutation.
            db("CREATE TRIGGER fail_update BEFORE UPDATE ON radusergroup FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture failure'")
            assert 'Unable to update user-group mapping' in update(current='silver',group='bronze');assert state()==before
            db('DROP TRIGGER fail_update')
            db("CREATE TRIGGER fail_delete BEFORE DELETE ON radusergroup FOR EACH ROW SET @r05_marker=IF(OLD.username='bob',(SELECT id FROM (SELECT 1 AS id UNION ALL SELECT 2) AS multi),0)")
            assert 'Unable to delete user-group mappings' in post(delete,{'usergroup[]':['alice||silver','bob||gold']});assert state()==before
            db('DROP TRIGGER fail_delete')
            for selection in (['alice||silver','invalid'],['alice||silver','bob||missing'],['alice||silver',['nested']],['alice||silver','mapping:bad%ZZ||gold']):
                assert 'Unable to delete user-group mappings' in post(delete,{'usergroup[]':selection});assert state()==before
            # Put the valid CSRF first so a truncated tail cannot merely fail for a missing token.
            token=next(form['csrf_token'] for form in Forms(req(delete)[1]).forms if 'csrf_token' in form)
            assert 'Unable to delete user-group mappings' in req(delete,{'csrf_token':token,'usergroup[]':['alice||silver']*1100})[1]
            assert state()==before
            # UPDATE preserves duplicate rows, while the priority provider has its own collapse contract.
            db("INSERT INTO radusergroup(username,groupname,priority) VALUES ('alice','silver',9),('alice','silver',11)")
            assert 'Updated user-group mapping' in update(current='silver',group='bronze',priority='4')
            assert db("SELECT COUNT(*) FROM radusergroup WHERE username='alice' AND groupname='bronze' AND priority=4")=='3'
            assert 'Updated user-group mapping' in update(current='bronze',group='bronze',priority='4')
            assert 'Added new user-group mapping' in create('alice','daloRADIUS-Disabled-Users','90')
            assert db("SELECT priority FROM radusergroup WHERE username='alice' AND groupname='daloRADIUS-Disabled-Users'")=='-1'
            assert 'Updated user-group mapping' in update(current='gold',group='gold',priority='2147483647')
            before=state();db('ALTER TABLE radusergroup ENGINE=MyISAM')
            assert 'Unable to create user-group mapping' in create('charlie','gold');assert state()==before
            assert 'Unable to update user-group mapping' in update(current='gold',group='gold');assert state()==before
            assert 'Unable to delete user-group mappings' in post(delete,{'username':'alice'});assert state()==before
            db('ALTER TABLE radusergroup ENGINE=InnoDB')
            # Distinct-group count used by legacy pagination can hide duplicate rows; candidate counts physical rows.
            configfile=f/'candidate/app/common/includes/daloradius.conf.php'
            configfile.write_text(configfile.read_text()+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='2';\n")
            seen=[]
            expected_rows=int(db("SELECT COUNT(*) FROM radusergroup WHERE username='alice'"))
            for page_num in range(1,(expected_rows+1)//2+1):
                seen+=Rows(req('mng-rad-usergroup-list-user.php',query={'username':'alice','page':str(page_num)})[1]).rows
            assert len(seen)==expected_rows,'all duplicate mapping rows reachable'
            # Raw special-character names survive real producer tokens and URLs; no second URL decoding.
            names=[("name O'Reilly &%+é","group O'Reilly &%+é"),('user||part','group||part'),('mapping:literal','encoded'),'0']
            for item in names:
                user,group=item if isinstance(item,tuple) else (item,item)
                escaped_user=user.replace("'","''");escaped_group=group.replace("'","''")
                db("INSERT INTO radcheck(username,attribute,op,value) VALUES ('"+escaped_user+"','Class',':=','named'); INSERT INTO radgroupcheck(groupname,attribute,op,value) VALUES ('"+escaped_group+"','Class',':=','named')")
                assert 'Added new user-group mapping' in create(user,group)
                assert 'Updated user-group mapping' in update(user,group,group,'6')
                html=req('mng-rad-usergroup-list-user.php',query={'username':user})[1]
                selected=Rows(html).rows;assert len(selected)==1
                assert 'username='+urllib.parse.quote_plus(user)+'&current_group='+urllib.parse.quote_plus(group) in html,'raw-name URL'
                assert 'Deleted 1 group mapping(s)' in post(delete,{'usergroup[]':[selected[0][0]]})
                assert db("SELECT COUNT(*) FROM radusergroup WHERE username='"+escaped_user+"'")=='0'
            # Broken legacy username-only delete is characterized separately, not called parity.
            for version in ('base','candidate'):
                db("INSERT INTO radusergroup(username,groupname,priority) VALUES ('delete-all','gold',0),('delete-all','silver',1)",version)
            legacy_before=state('base');post(delete,{'username':'delete-all'},'base');assert state('base')==legacy_before
            assert 'Deleted 2 group mapping(s) for a total of 1 user(s)' in post(delete,{'username':'delete-all'})
            assert db("SELECT COUNT(*) FROM radusergroup WHERE username='delete-all'")=='0'
            db("INSERT INTO radusergroup(username,groupname,priority) VALUES ('bob','gold',3),('bob','gold',4)")
            assert 'Deleted 4 group mapping(s) for a total of 2 user(s)' in post(delete,{'usergroup[]':['bob||gold','alice||copper','bob||gold']})
            assert db("SELECT COUNT(*) FROM radusergroup WHERE username='bob' OR (username='alice' AND groupname='copper')")=='0'
            # Orphaned mappings can be cleaned without an existing authentication parent.
            db("INSERT INTO radusergroup(username,groupname,priority) VALUES ('orphan','gone',2)")
            assert 'Deleted 1 group mapping(s)' in post(delete,{'username':'orphan','group':'gone'})
            print('PASS malformed/stale batches, duplicate-preserving edit, later UPDATE/DELETE rollback, priorities, non-InnoDB, delimiter/zero/Unicode names and repaired delete-all')
            # Concurrent independent sessions serialize on the real shared parent lock.
            sessions=[secrets.token_hex(16),secrets.token_hex(16)]
            for token in sessions:run('docker','exec',h.WEB,'php','/fixtures/session.php',token)
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:
                results=list(pool.map(lambda token:create('charlie','gold','2',session_id=token),sessions))
            assert sum('Added new user-group mapping' in html for html in results)==1
            assert db("SELECT COUNT(*) FROM radusergroup WHERE username='charlie' AND groupname='gold'")=='1'
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:
                results=list(pool.map(lambda args:update('charlie','gold',args[1],'3',session_id=args[0]),zip(sessions,('silver','bronze'))))
            assert sum('Updated user-group mapping' in html for html in results)==1
            current=db("SELECT groupname FROM radusergroup WHERE username='charlie'")
            assert current in ('silver','bronze')
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:
                results=list(pool.map(lambda token:post(delete,{'usergroup[]':['charlie||'+current]},session_id=token),sessions))
            assert sum('Deleted 1 group mapping(s)' in html for html in results)==1
            assert db("SELECT COUNT(*) FROM radusergroup WHERE username='charlie'")=='0'
            denied=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',denied,'default','9002')
            for page in PAGES:
                url='http://'+ip+':8080/candidate/app/operators/'+page
                for cookie,target in [(None,'login.php'),(denied,'home-error.php')]:
                    request=urllib.request.Request(url,headers={} if cookie is None else {'Cookie':'daloradius_operator_sid='+cookie})
                    with urllib.request.urlopen(request,timeout=30) as response:assert response.geturl().endswith(target),(page,'ACL/auth')
            before=state()
            for page,fields in [(new,{'username':'alice','group':'gold'}),(edit,{'username':'alice','current_group':'bronze','group':'gold'}),(delete,{'username':'alice'})]:
                assert 'CSRF token error' in req(page,dict(fields,csrf_token='invalid'))[1];assert state()==before
                assert 'CSRF token error' in req(page,dict(fields,**{'csrf_token[]':['invalid']}))[1];assert state()==before
            for page in ('mng-rad-usergroup-list.php','mng-rad-usergroup-list-user.php'):
                assert req(page,query={'username[]':'bad','orderBy[]':'username','orderType[]':'asc'})[0]==200
            other=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',other,'other')
            assert 'Added new user-group mapping' in create('alice','gold',session_id=other)
            assert db("SELECT COUNT(*) FROM radusergroup WHERE username='alice' AND groupname='gold'",'candidate_other')=='1'
            assert state()==before
            assert 'Updated user-group mapping' in update('alice','gold','silver','8',session_id=other)
            assert Rows(req('mng-rad-usergroup-list-user.php',query={'username':'alice'},session_id=other)[1]).rows
            assert 'Deleted 1 group mapping(s)' in post(delete,{'username':'alice','group':'silver'},session_id=other)
            assert db('SELECT COUNT(*) FROM radusergroup','candidate_other')=='0';assert state()==before
            # Static PHP widget output does not inject raw quote-bearing names or closing script tags.
            group='quote \" </script><script>bad</script>'
            db("INSERT INTO radgroupcheck(groupname,attribute,op,value) VALUES ('"+group+"','Class',':=','safe'); INSERT INTO radusergroup(username,groupname,priority) VALUES ('alice','"+group+"',2)")
            html=req('widget.php',query={'username':'alice'})[1]
            assert '<script>bad</script>' not in html
            assert 'nextGroupId' in html and '.value = selected_group;' in html
            # Configured names on real create/edit/delete/catalog/widget paths.
            conf=configfile.read_text()
            for key,old,new_table in [('RADCHECK','radcheck','custom_auth'),('RADUSERGROUP','radusergroup','custom_mapping'),('RADGROUPCHECK','radgroupcheck','custom_check'),('RADGROUPREPLY','radgroupreply','custom_reply'),('DALOUSERINFO','userinfo','custom_info')]:
                db('RENAME TABLE '+old+' TO '+new_table);conf+="\n$configValues['CONFIG_DB_TBL_"+key+"']='"+new_table+"';\n"
            configfile.write_text(conf)
            assert 'Added new user-group mapping' in create('charlie','silver')
            assert 'Updated user-group mapping' in update('charlie','silver','bronze','9')
            assert 'Deleted 1 group mapping(s)' in post(delete,{'username':'charlie','group':'bronze'})
            assert req('widget.php',query={'username':'alice'})[0]==200
            assert Rows(req('mng-rad-usergroup-list.php',query={'username':'alice'})[1]).rows
            assert Rows(req('mng-rad-usergroup-list-user.php',query={'username':'alice'})[1]).rows
            db('RENAME TABLE custom_info TO absent_info')
            html=req('mng-rad-usergroup-list.php')[1];assert 'Unable to load user-group mappings' in html and 'SQLSTATE' not in html
            db('RENAME TABLE absent_info TO custom_info')
            logs=subprocess.run(['docker','logs',h.WEB],text=True,capture_output=True);lines=(logs.stdout+logs.stderr).splitlines()
            assert not any('/fixtures/candidate/' in line and ('PHP Warning:' in line or 'PHP Fatal error:' in line) for line in lines),'Candidate PHP diagnostic detected (details suppressed)'
            print('PASS concurrent create/edit/delete, ACL/CSRF, selected locations, configured tables, sanitized errors, widget encoding and legacy-open tripwire')
        finally:
            run('docker','rm','-f',h.WEB,h.DB,check=False);run('docker','network','rm',h.NETWORK,check=False)
            subprocess.run(['sudo','-n','chown','-R',str(os.getuid())+':'+str(os.getgid()),str(f)],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
if __name__=='__main__':main()
