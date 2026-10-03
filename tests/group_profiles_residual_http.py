#!/usr/bin/env python3
"""R04: native profile/group HTTP differential, reads and atomic failure tests."""
import concurrent.futures, os, re, secrets, shutil, subprocess, tempfile, urllib.parse, urllib.request, urllib.error
from html.parser import HTMLParser
from pathlib import Path
import user_actions_http as h
from acct_maintenance_http import Forms
ROOT=Path(__file__).resolve().parents[1]
BASE='527cebeeae351cbd074283e192419a3509e0a869'
SUFFIX=secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=['pdo-r04-'+k+'-'+SUFFIX for k in ('db','web','net')]
run,sql,wait_for=h.run,h.sql,h.wait_for
PAGES=['mng-rad-groupcheck-del.php', 'mng-rad-groupcheck-edit.php', 'mng-rad-groupcheck-list.php', 'mng-rad-groupcheck-search.php', 'mng-rad-groupreply-del.php', 'mng-rad-groupreply-edit.php', 'mng-rad-groupreply-list.php', 'mng-rad-groupreply-search.php', 'mng-rad-profiles-edit.php', 'mng-rad-profiles-list.php', 'mng-rad-profiles-new.php']

class Rows(HTMLParser):
    def __init__(self, html):
        super().__init__(convert_charrefs=True);self.rows=[];self.row=None;self.cell=None;self.feed(html)
    def handle_starttag(self, tag, attrs):
        a=dict(attrs)
        if tag=='tr':self.row={'selected':None,'cells':[]}
        if self.row is not None:
            if tag=='td':self.cell=[]
            if tag=='input' and a.get('name') in ('record_id[]','profile_names[]'):self.row['selected']=a.get('value')
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
    with tempfile.TemporaryDirectory(prefix='pdo-r04-',dir=scratch) as tmp:
        f=Path(tmp)
        for version in ('base','candidate'):
            shutil.copytree(ROOT/'app',f/version/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if version=='base':
                for rel in PAGES+['library/attributes.php']:
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
                db("INSERT INTO operators_acl(operator_id,file,access) VALUES (9001,'mng_rad_groupcheck_del',1),(9001,'mng_rad_groupcheck_edit',1),(9001,'mng_rad_groupcheck_list',1),(9001,'mng_rad_groupcheck_search',1),(9001,'mng_rad_groupreply_del',1),(9001,'mng_rad_groupreply_edit',1),(9001,'mng_rad_groupreply_list',1),(9001,'mng_rad_groupreply_search',1),(9001,'mng_rad_profiles_edit',1),(9001,'mng_rad_profiles_list',1),(9001,'mng_rad_profiles_new',1); INSERT INTO operators_acl(operator_id,file,access) SELECT 9002,file,0 FROM operators_acl WHERE operator_id=9001; INSERT INTO radgroupcheck(groupname,attribute,op,value) VALUES ('gold','Auth-Type',':=','Accept');",version)
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
            wait_for(lambda:req('mng-rad-profiles-new.php'),'PHP')
            def post(page,fields,version='candidate',session_id=None,query=None):
                html=req(page,version=version,session_id=session_id,query=query)[1]
                forms=Forms(html).forms
                token=next(form['csrf_token'] for form in forms if 'csrf_token' in form)
                return req(page,dict(fields,csrf_token=token),version,session_id=session_id)[1]
            def state(version='candidate'):
                return {t:db("SELECT id,groupname,attribute,op,CASE WHEN attribute LIKE '%-Password' THEN '[excluded]' ELSE value END FROM "+t+" ORDER BY id",version) for t in ('radgroupcheck','radgroupreply')}
            def create(name='ordinary',version='candidate',more=None):
                fields={'profile':name,'dictValues0[]':['Class','check-before',':=','check'],
                        'dictValues1[]':['Reply-Message','reply-before',':=','reply']}
                if more:fields.update(more)
                return post('mng-rad-profiles-new.php',fields,version)
            def edit(name='ordinary',version='candidate',more=None):
                check_id=db("SELECT id FROM radgroupcheck WHERE groupname='"+name+"' AND attribute='Class'",version)
                reply_id=db("SELECT id FROM radgroupreply WHERE groupname='"+name+"' AND attribute='Reply-Message'",version)
                fields={'profile_name':name,'editValues_radgroupcheck_'+check_id+'[]':[check_id+'__Class','check-after',':=','radgroupcheck'],
                    'editValues_radgroupreply_'+reply_id+'[]':[reply_id+'__Reply-Message','reply-after',':=','radgroupreply']}
                if more:fields.update(more)
                return post('mng-rad-profiles-edit.php',fields,version,query={'profile_name':name})
            for version in ('base','candidate'):
                assert 'Successfully added a new profile' in create(version=version),(version,'create')
                assert 'Updated attributes for' in edit(version=version),(version,'edit')
            assert state('base')==state(),'profile mutation parity'
            # Comparable render fixtures, groupcheck/reply overlap and usergroup-only entry.
            seed="INSERT INTO radgroupcheck(groupname,attribute,op,value) VALUES ('alpha','Class',':=','A'),('beta','Class',':=','B'),('quoted O\'Reilly','Class',':=','C');"
            # Use SQL quote doubling rather than Python backslash loss.
            seed=seed.replace("O'Reilly","O''Reilly")
            seed+="INSERT INTO radgroupreply(groupname,attribute,op,value) VALUES ('alpha','Reply-Message',':=','A'),('gamma','Class',':=','C'); INSERT INTO radusergroup(username,groupname,priority) VALUES ('one','alpha',0),('one','alpha',1),('two','alpha',0),('one','mapping-only',0);"
            for version in ('base','candidate'):db(seed,version)
            for version in ('base','candidate'):
                configfile=f/version/'app/common/includes/daloradius.conf.php'
                configfile.write_text(configfile.read_text()+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='2';\n")
            comparisons=0
            for page in [p for p in PAGES if p.endswith('-list.php') or p.endswith('-search.php')]:
                sorts=['groupname','users'] if 'profiles' in page else ['id','groupname','attribute','op','value']
                for order in sorts:
                    for direction in ('asc','desc'):
                        ordered_left=[];ordered_right=[]
                        for page_num in (1,2,3):
                            query={'orderBy':order,'orderType':direction,'page':str(page_num)}
                            if 'search' in page:query['groupname']='a'
                            left=Rows(req(page,version='base',query=query)[1]).rows
                            right=Rows(req(page,query=query)[1]).rows
                            if page=='mng-rad-profiles-list.php' and order=='users':
                                # Legacy ORDER BY users has no tiebreaker; tied identities may differ across LIMIT boundaries.
                                assert [r[1][-1] for r in left]==[r[1][-1] for r in right],(page,order,direction,page_num,'user-count ranks')
                            else:
                                assert left==right,(page,order,direction,page_num,'row parity')
                            ordered_left.extend(left);ordered_right.extend(right)
                            comparisons+=1
                        assert sorted(ordered_left)==sorted(ordered_right),(page,order,direction,'complete page membership')
            print('PASS native PEAR/PDO profile state and',comparisons,'list/search sort-page projections')
            for kind in ('check','reply'):
                page='mng-rad-group'+kind+'-search.php'
                for search in ('',"quoted O'Reilly","' OR 1=1 --",'not-present'):
                    query={'groupname':search,'orderBy':'id','orderType':'asc'}
                    assert Rows(req(page,version='base',query=query)[1]).rows==Rows(req(page,query=query)[1]).rows,(kind,'search binding')
                assert req(page,query={'groupname[]':'alpha','orderBy[]':'id','orderType[]':'asc'})[0]==200
            for kind in ('check','reply'):
                table='radgroup'+kind
                page='mng-rad-group'+kind+'-edit.php'
                ident=db("SELECT id FROM "+table+" WHERE groupname='ordinary'")
                query={'item':'group'+kind+'-'+ident}
                left=Forms(req(page,version='base',query=query)[1]).forms
                right=Forms(req(page,query=query)[1]).forms
                assert [(x.get('groupname'),x.get('attribute'),x.get('value')) for x in left]==[(x.get('groupname'),x.get('attribute'),x.get('value')) for x in right]
            # Late reply failures after the first check insert/update roll back both families.
            before=state();db("CREATE TRIGGER fail_reply BEFORE INSERT ON radgroupreply FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture failure'")
            assert 'Unable to create profile' in create('late-create');assert state()==before
            db('DROP TRIGGER fail_reply')
            db("CREATE TRIGGER fail_reply BEFORE UPDATE ON radgroupreply FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture failure'")
            ident=db("SELECT id FROM radgroupcheck WHERE groupname='ordinary' AND attribute='Class'")
            reply_id=db("SELECT id FROM radgroupreply WHERE groupname='ordinary' AND attribute='Reply-Message'")
            assert 'Unable to update profile' in edit(more={'editValues_radgroupcheck_'+ident+'[]':[ident+'__Class','late-change',':=','radgroupcheck'],'editValues_radgroupreply_'+reply_id+'[]':[reply_id+'__Reply-Message','late-reply',':=','radgroupreply']});assert state()==before
            db('DROP TRIGGER fail_reply')
            before=state()
            assert 'Unable to create profile' in create('bad-later',more={'dictValues2[]':['Class','bad','invalid','check']});assert state()==before
            assert 'Unable to create profile' in create();assert state()==before
            assert 'Unable to create profile' in create('empty',more={'dictValues0[]':['','',':=','check'],'dictValues1[]':['','',':=','reply']});assert state()==before
            assert 'Unable to create profile' in post('mng-rad-profiles-new.php',{'profile[]':['bad']});assert state()==before
            foreign=db("SELECT id FROM radgroupcheck WHERE groupname='alpha'")
            assert 'Unable to update profile' in edit(more={'foreign[]':[foreign+'__Class','bad',':=','radgroupcheck']});assert state()==before
            assert 'Successfully added a new profile' in create('literal%+é',more={'dictValues0[]':['Class','0',':=','check'],'dictValues1[]':['','',':=','reply']})
            assert db("SELECT value FROM radgroupcheck WHERE groupname='literal%+é'")=='0'
            assert 'Updated attributes for' in post('mng-rad-profiles-edit.php',{'profile_name':'literal%+é','new[]':['Reply-Message','0',':=','reply']},query={'profile_name':'literal%+é'})
            for name in ("quote O'Reilly &%+é",'0'):
                fields={'profile':name,'dictValues0[]':['Class','named',':=','check']}
                assert 'Successfully added a new profile' in post('mng-rad-profiles-new.php',fields)
                html=req('mng-rad-profiles-edit.php',query={'profile_name':name})[1]
                assert 'profile_name='+urllib.parse.quote_plus(name) in html,'Raw-name edit/delete URL'
                assert 'Updated attributes for' in post('mng-rad-profiles-edit.php',{'profile_name':name,'new[]':['Reply-Message','named-reply',':=','reply']},query={'profile_name':name})
            # Full deletion selection must be valid before deleting the first row.
            for kind in ('check','reply'):
                table='radgroup'+kind;page='mng-rad-group'+kind+'-del.php'
                ids=db('SELECT id FROM '+table+' ORDER BY id LIMIT 2').splitlines();before=state()
                assert 'Unable to delete group attributes' in post(page,{'record_id[]':['record-'+ids[0],'invalid']});assert state()==before
                assert 'Unable to delete group attributes' in post(page,{'record_id[]':['record-'+ids[0],'record-4294967295']});assert state()==before
                # A later-row trigger fails after an earlier DELETE, both must survive.
                db('CREATE TRIGGER fail_delete BEFORE DELETE ON '+table+' FOR EACH ROW SET @r04_marker=IF(OLD.id='+ids[1]+', (SELECT id FROM (SELECT 1 AS id UNION ALL SELECT 2) AS multiple_rows),0)')
                assert 'Unable to delete group attributes' in post(page,{'record_id[]':['record-'+i for i in ids]});assert state()==before
                db('DROP TRIGGER fail_delete')
                # Ordinary scalar deletion state parity against the pinned PEAR page.
                for version in ('base','candidate'):
                    ident=db("SELECT id FROM "+table+" WHERE groupname='ordinary'",version)
                    html=post(page,{'record_id':'record-'+ident},version)
                    assert 'Deleted' in html,(page,version,'delete')
                assert db("SELECT COUNT(*) FROM "+table+" WHERE groupname='ordinary'")=='0'
            print('PASS profile/attribute late rollback, malformed/stale IDs, ownership, zero/percent/Unicode and deletion parity')
            before=state();db('ALTER TABLE radgroupreply ENGINE=MyISAM')
            assert 'Unable to create profile' in create('nontransactional');assert state()==before
            assert 'Unable to delete group attributes' in post('mng-rad-groupreply-del.php',{'record_id':'record-1'});assert state()==before
            db('ALTER TABLE radgroupreply ENGINE=InnoDB')
            sessions=[secrets.token_hex(16),secrets.token_hex(16)]
            for token in sessions:run('docker','exec',h.WEB,'php','/fixtures/session.php',token)
            fields={'profile':'concurrent','dictValues0[]':['Class','same',':=','check']}
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:
                results=list(pool.map(lambda token:post('mng-rad-profiles-new.php',fields,session_id=token),sessions))
            assert sum('Successfully added a new profile' in html for html in results)==1
            assert db("SELECT COUNT(*) FROM radgroupcheck WHERE groupname='concurrent'")=='1'
            denied=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',denied,'default','9002')
            legacy_form=Forms(req('mng-rad-profiles-edit.php',version='base',session_id=denied,query={'profile_name':'alpha'})[1]).forms
            assert any(form.get('profile_name')=='alpha' for form in legacy_form),'Characterized legacy ACL omission'
            for page in PAGES:
                url='http://'+ip+':8080/candidate/app/operators/'+page
                for cookie,target in [(None,'login.php'),(denied,'home-error.php')]:
                    request=urllib.request.Request(url,headers={} if cookie is None else {'Cookie':'daloradius_operator_sid='+cookie})
                    with urllib.request.urlopen(request,timeout=30) as response:assert response.geturl().endswith(target),(page,'ACL/auth')
            before=state()
            for page,fields in [('mng-rad-profiles-new.php',{'profile':'csrf'}),('mng-rad-profiles-edit.php',{'profile_name':'alpha'}),('mng-rad-groupcheck-del.php',{'record_id':'record-1'}),('mng-rad-groupreply-del.php',{'record_id':'record-1'})]:
                assert 'CSRF token error' in req(page,dict(fields,csrf_token='invalid'))[1];assert state()==before
            other_sid=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',other_sid,'other')
            fields={'profile':'located','dictValues0[]':['Class','located',':=','check']}
            assert 'Successfully added a new profile' in post('mng-rad-profiles-new.php',fields,session_id=other_sid)
            assert db("SELECT COUNT(*) FROM radgroupcheck WHERE groupname='located'",'candidate_other')=='1'
            assert db("SELECT COUNT(*) FROM radgroupcheck WHERE groupname='located'")=='0'
            assert Rows(req('mng-rad-groupcheck-list.php',session_id=other_sid)[1]).rows
            assert 'located' in req('mng-rad-profiles-edit.php',query={'profile_name':'located'},session_id=other_sid)[1]
            # Configurable tables, clean late read failures, no legacy-open on migrated routes.
            configfile=f/'candidate/app/common/includes/daloradius.conf.php';conf=configfile.read_text()
            for key,old,new in [('RADGROUPCHECK','radgroupcheck','custom_check'),('RADGROUPREPLY','radgroupreply','custom_reply'),('RADUSERGROUP','radusergroup','custom_users'),('DALODICTIONARY','dictionary','custom_dictionary')]:
                db('RENAME TABLE '+old+' TO '+new);conf+="\n$configValues['CONFIG_DB_TBL_"+key+"']='"+new+"';\n"
            configfile.write_text(conf)
            assert 'Successfully added a new profile' in create('custom')
            assert db("SELECT COUNT(*) FROM custom_check WHERE groupname='custom'")=='1'
            ident=db("SELECT id FROM custom_check WHERE groupname='custom'")
            fields={'profile_name':'custom','existing[]':[ident+'__Class','custom-edit',':=','custom_check'],'new[]':['Reply-Message','custom-reply',':=','reply']}
            assert 'Updated attributes for' in post('mng-rad-profiles-edit.php',fields,query={'profile_name':'custom'})
            assert db("SELECT value FROM custom_check WHERE groupname='custom'")=='custom-edit'
            assert db("SELECT COUNT(*) FROM custom_reply WHERE groupname='custom' AND value='custom-reply'")=='1'
            for page in PAGES:
                query={'profile_name':'custom','item':'groupcheck-1','groupname':'custom'}
                assert req(page,query=query)[0]==200,(page,'custom read')
            db('RENAME TABLE custom_reply TO absent_reply')
            assert 'Unable to load profiles' in req('mng-rad-profiles-list.php')[1]
            db('RENAME TABLE absent_reply TO custom_reply')
            logs=subprocess.run(['docker','logs',h.WEB],text=True,capture_output=True);lines=(logs.stdout+logs.stderr).splitlines()
            assert not any('/fixtures/candidate/' in line and ('PHP Warning:' in line or 'PHP Fatal error:' in line) for line in lines),'Candidate PHP diagnostic detected (details suppressed)'
            print('PASS concurrent creation, non-InnoDB, ACL/CSRF, selected locations, configured tables, sanitized reads and clean candidate logs')
        finally:
            run('docker','rm','-f',h.WEB,h.DB,check=False);run('docker','network','rm',h.NETWORK,check=False)
            subprocess.run(['sudo','-n','chown','-R',str(os.getuid())+':'+str(os.getgid()),str(f)],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
if __name__=='__main__':main()
