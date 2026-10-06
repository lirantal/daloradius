#!/usr/bin/env python3
"""R07: native pinned PEAR/PDO IP pool CRUD, lists, atomicity and isolation."""
import concurrent.futures,hashlib,json,os,re,secrets,shutil,subprocess,tempfile,time,urllib.parse,urllib.request,urllib.error
from html.parser import HTMLParser
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap
import user_actions_http as h
from acct_maintenance_http import Forms
ROOT=Path(__file__).resolve().parents[1]
BASE='8c9a749649806929eb1fc63d1a7048c7a1555f6e'
SUFFIX=secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=['pdo-r07-'+k+'-'+SUFFIX for k in ('db','web','net')]
run,sql,wait_for=h.run,h.sql,h.wait_for
PAGES=['mng-rad-ippool-'+kind+'.php' for kind in ('new','edit','del','list')]
class Rows(HTMLParser):
    def __init__(self,html):
        super().__init__(convert_charrefs=True);self.rows=[];self.row=None;self.cell=None;self.links=[];self.options=[];self.feed(html)
    def handle_starttag(self,tag,attrs):
        a=dict(attrs)
        if tag=='a' and 'href' in a:self.links.append(a['href'])
        if tag=='option':self.options.append(a)
        if tag=='tr':self.row={'selected':None,'cells':[]}
        if self.row is not None:
            if tag=='td':self.cell=[]
            if tag=='input' and a.get('name')=='item[]':self.row['selected']=a.get('value')
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
    with tempfile.TemporaryDirectory(prefix='pdo-r07-',dir=scratch) as tmp:
        f=Path(tmp)
        for version in ('base','candidate'):
            shutil.copytree(ROOT/'app',f/version/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if version=='base':
                restore_pear_bootstrap((f / version / 'app').parent, BASE)
                for rel in PAGES:
                    (f/version/'app/operators'/rel).write_bytes(subprocess.check_output(['git','show',BASE+':app/operators/'+rel],cwd=ROOT))
            conf=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,value in {'CONFIG_DB_HOST':h.DB,'CONFIG_DB_USER':'root','CONFIG_DB_PASS':'','CONFIG_DB_NAME':version,'CONFIG_IFACE_TABLES_LISTING':'2'}.items():
                conf+='\n$configValues['+repr(key)+']='+repr(value)+';\n'
            conf+="\n$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>'"+h.DB+"','Username'=>'root','Password'=>'','Database'=>'"+version+"_other','Port'=>'3306');\n"
            (f/version/'app/common/includes/daloradius.conf.php').write_text(conf)
        (f/'session.php').write_text("<?php session_name('daloradius_operator_sid');session_id($argv[1]);session_start();$_SESSION=['daloradius_logged_in'=>true,'operator_id'=>(int)($argv[3]??9001),'operator_user'=>bin2hex(random_bytes(12)),'location_name'=>($argv[2]??'default'),'time'=>time()-(int)($argv[4]??0)];session_write_close();")
        (f/'candidate/app/operators/borrowed.php').write_text("<?php include 'library/checklogin.php';include_once '../common/includes/config_read.php';require_once 'library/ip_pool_pages_pdo.php';$logDebugSQL='';$pdo=dalo_pdo_connect($configValues,$_SESSION['location_name']??'default');$pdo->beginTransaction();$pdo->exec(\"INSERT INTO radippool(pool_name,framedipaddress) VALUES ('Caller','192.0.2.200')\");$rejected=false;try {dalo_ippool_mutate($pdo,$configValues,function(){return true;});}catch(LogicException $e){$rejected=true;}$active=$pdo->inTransaction();$pdo->rollBack();echo json_encode(['rejected'=>$rejected,'active'=>$active]);")
        try:
            run('docker','network','create','--internal',h.NETWORK)
            run('docker','run','-d','--name',h.DB,'--network',h.NETWORK,'--tmpfs','/var/lib/mysql','-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            def db(q,version='candidate'):
                p=subprocess.run(['docker','exec','-i',h.DB,'mariadb','-uroot','-N','-B',version],input="SET SESSION sql_mode=''; "+q,text=True,capture_output=True)
                if p.returncode:raise RuntimeError('Fixture SQL error codes '+repr(re.findall(r'ERROR (\d+)',p.stderr)))
                return p.stdout.rstrip('\n')
            for version in ('base','candidate','base_other','candidate_other'):
                sql('CREATE DATABASE '+version)
                for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                    db((ROOT/'contrib/db'/name).read_text(),version)
                acl=','.join("(9001,'mng_rad_ippool_"+kind+"',1)" for kind in ('new','edit','del','list'))
                db('INSERT INTO operators_acl(operator_id,file,access) VALUES '+acl+"; INSERT INTO operators_acl(operator_id,file,access) SELECT 9002,file,0 FROM operators_acl WHERE operator_id=9001;",version)
                rows=[]
                for i in range(8):
                    values=[10+i,['Alpha','Beta','Gamma','Delta'][i//2], '192.0.2.'+str(10+i),'198.51.100.'+str(10+i),'called-'+str(i),'calling-'+str(i),'2020-01-01 00:00:00' if i%2==0 else '2099-01-01 00:00:00','LeaseOwner' if i==0 else '','key-'+str(i)]
                    rows.append('('+','.join(str(v) if isinstance(v,int) else "'"+v+"'" for v in values)+')')
                db('INSERT INTO radippool(id,pool_name,framedipaddress,nasipaddress,calledstationid,callingstationid,expiry_time,username,pool_key) VALUES '+','.join(rows)+"; INSERT INTO radcheck(username,attribute,op,value) VALUES ('LeaseOwner','Class',':=','fixture'); INSERT INTO radacct(username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime) VALUES ('LeaseOwner','fixture-session','fixture-unique','198.51.100.10','2020-01-01');",version)
            for rel in ('db_open.php','db_close.php'):
                (f/'candidate/app/common/includes'/rel).write_text("<?php throw new RuntimeException('Legacy database tripwire');")
            run('docker','run','-d','--name',h.WEB,'--network',h.NETWORK,'-v',str(f)+':/fixtures','-w','/fixtures','-e','PHP_CLI_SERVER_WORKERS=4','--entrypoint','php','lirantal/daloradius','-d','opcache.enable_cli=0','-d','opcache.enable=0','-d','display_errors=0','-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',h.WEB)
            sid=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',sid)
            def req(page,data=None,version='candidate',query=None,session_id=None,follow_redirects=True):
                url='http://'+ip+':8080/'+version+'/app/operators/'+page
                if query:url+='?'+urllib.parse.urlencode(query,doseq=True)
                request=urllib.request.Request(url,data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),headers={'Cookie':'daloradius_operator_sid='+(session_id or sid)})
                class NoRedirect(urllib.request.HTTPRedirectHandler):
                    def redirect_request(self,*args,**kwargs):return None
                opener=urllib.request.build_opener() if follow_redirects else urllib.request.build_opener(NoRedirect)
                try:
                    with opener.open(request,timeout=30) as response:return response.status,response.read().decode()
                except urllib.error.HTTPError as e:return e.code,e.read().decode()
            new,edit,delete,listpage=PAGES
            wait_for(lambda:req(new),'PHP')
            print('RUNTIME PHP '+run('docker','exec',h.WEB,'php','-r','echo PHP_VERSION;')+'; MariaDB '+db('SELECT VERSION()'),flush=True)
            def token(version='candidate',session_id=None):
                return next(form['csrf_token'] for form in Forms(req(new,version=version,session_id=session_id)[1]).forms if 'csrf_token' in form)
            def post(page,fields,version='candidate',session_id=None):
                return req(page,dict(fields,csrf_token=token(version,session_id)),version,session_id=session_id)[1]
            def create(pool='Created',address='192.0.2.80',version='candidate',session_id=None):
                return post(new,{'pool_name':pool,'framedipaddress':address},version,session_id)
            def update(item='ippool-10',pool='Edited',address='192.0.2.81',version='candidate',session_id=None):
                return post(edit,{'item':item,'pool_name':pool,'framedipaddress':address},version,session_id)
            def state(version='candidate',table='radippool'):
                return db('SELECT id,pool_name,framedipaddress,nasipaddress,calledstationid,callingstationid,expiry_time,username,pool_key FROM '+table+' ORDER BY id',version)
            for version in ('base','candidate'):
                assert 'Successfully added' in create(version=version)
                assert 'Successfully updated' in update(version=version)
                assert 'Deleted 1 ippool' in post(delete,{'item[]':['ippool-18']},version)
            assert state('base')==state(),'ordinary full physical CRUD parity, including lease fields'
            comparisons=0
            for order in ('id','pool_name','framedipaddress','nasipaddress','CalledStationId','CallingStationID','expiry_time','username','pool_key'):
                for direction in ('asc','desc'):
                    for page_num in (1,2,3,4):
                        q={'orderBy':order,'orderType':direction,'page':str(page_num)}
                        assert Rows(req(listpage,version='base',query=q)[1]).rows==Rows(req(listpage,query=q)[1]).rows,(order,direction,page_num,'row parity');comparisons+=1
            for q in ({'pool_name':'Alpha'},{'pool_name':'%Alpha%'},{'pool_name':'missing'}):
                assert Rows(req(listpage,version='base',query=q)[1]).rows==Rows(req(listpage,query=q)[1]).rows;comparisons+=1
            for page in (edit,delete):
                q={'item':'ippool-10'}
                left=Forms(req(page,version='base',query=q)[1]).forms;right=Forms(req(page,query=q)[1]).forms
                for forms in (left,right):
                    for form in forms:form.pop('csrf_token',None)
                assert left==right;comparisons+=1
            print('PASS native PEAR/PDO ordinary CRUD/full lease state and',comparisons,'sort/page/filter/form comparisons',flush=True)
            legacy_before=state('base').splitlines()
            assert 'Deleted 1 ippool' in post(delete,{'item[]':['ippool-10','ippool-999']},'base')
            assert state('base').splitlines()==[row for row in legacy_before if row.split('\t',1)[0]!='10']
            legacy_before=state('base').splitlines()
            db("\nDELIMITER $$\nCREATE TRIGGER fail_legacy_delete BEFORE DELETE ON radippool FOR EACH ROW BEGIN IF OLD.id=12 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='legacy rollback characterization'; END IF; END$$\nDELIMITER ;\n",'base')
            assert 'Deleted 1 ippool' in post(delete,{'item[]':['ippool-11','ippool-12']},'base')
            assert state('base').splitlines()==[row for row in legacy_before if row.split('\t',1)[0]!='11']
            db('DROP TRIGGER fail_legacy_delete','base')
            print('PASS unmodified PEAR characterization: stale later selection ignored and earlier deletion persists on later SQL failure',flush=True)

            before=state()
            for fields in ({'pool_name[]':['bad'],'framedipaddress':'192.0.2.80'}, {'pool_name':'Bad','framedipaddress[]':['192.0.2.80']}, {'pool_name':'Bad','framedipaddress':'not-an-ip'}, {'pool_name':'X'*31,'framedipaddress':'192.0.2.80'},{'pool_name':'Bad\0Name','framedipaddress':'192.0.2.80'}):
                assert 'Unable to create' in post(new,fields);assert state()==before
            assert 'already contained' in create('OtherPool','192.0.2.11');assert state()==before
            assert 'already contained' in update(address='192.0.2.11');assert state()==before
            for item in ('ippool-999','ippool-0','ippool-01','ippool-4294967296','ippool-1junk'):
                assert 'Unable to load or update' in update(item=item);assert state()==before
            assert 'Unable to load or update' in post(edit,{'item[]':['ippool-10'],'pool_name':'Bad','framedipaddress':'192.0.2.80'});assert state()==before
            assert 'Successfully updated' in update(pool='Edited',address='192.0.2.81');assert state()==before
            for selection in (['ippool-10','ippool-999'],['ippool-10','junk'],['ippool-10','ippool-01']):
                assert 'Unable to delete' in post(delete,{'item[]':selection});assert state()==before
            assert 'Unable to delete' in post(delete,{'item[0]':'ippool-10','item[1][bad]':'nested'});assert state()==before
            assert 'Unable to delete' in req(delete,{'csrf_token':token(),'item[]':['ippool-10']*1100})[1];assert state()==before
            # Preview a selection, then delete its later row independently: POST must recheck all rows.
            preview=req(delete,query={'item[]':['ippool-10','ippool-11']})[1]
            fresh=next(form['csrf_token'] for form in Forms(preview).forms if 'csrf_token' in form)
            db('DELETE FROM radippool WHERE id=11');after_external=state()
            assert 'Unable to delete' in req(delete,{'csrf_token':fresh,'item[]':['ippool-10','ippool-11']})[1];assert state()==after_external
            # Restore fixture row and compare actual late-error rollback of the entire selection.
            db("INSERT INTO radippool(id,pool_name,framedipaddress) VALUES (11,'Alpha','192.0.2.11')")
            before=state()
            db("\nDELIMITER $$\nCREATE TRIGGER fail_delete BEFORE DELETE ON radippool FOR EACH ROW BEGIN IF OLD.id=12 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='private fixture marker'; END IF; END$$\nDELIMITER ;\n")
            html=post(delete,{'item[]':['ippool-10','ippool-12']});assert 'Unable to delete' in html and 'SQLSTATE' not in html and 'private fixture marker' not in html;assert state()==before
            db('DROP TRIGGER fail_delete')
            db("CREATE TRIGGER fail_update BEFORE UPDATE ON radippool FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='private fixture marker'")
            assert 'Unable to load or update' in update();assert state()==before;db('DROP TRIGGER fail_update')
            db("CREATE TRIGGER fail_insert BEFORE INSERT ON radippool FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='private fixture marker'")
            assert 'Unable to create' in create();assert state()==before;db('DROP TRIGGER fail_insert')
            assert json.loads(req('borrowed.php')[1])=={'rejected':True,'active':True};assert state()==before
            db('ALTER TABLE radippool ENGINE=MyISAM')
            assert 'Unable to create' in create();assert 'Unable to load or update' in update();assert 'Unable to delete' in post(delete,{'item[]':['ippool-10']});assert state()==before
            db('ALTER TABLE radippool ENGINE=InnoDB')
            print('PASS malformed/overflow/stale IDs, global collisions, actual stale preview, late DELETE/UPDATE/INSERT invariance, truncated/nested selection and borrowed/nontransactional guards',flush=True)
            rows_before=state().splitlines()
            assert 'Deleted 2 ippool' in post(delete,{'item[]':['ippool-16','ippool-17','ippool-16']})
            assert state().splitlines()==[row for row in rows_before if row.split('\t',1)[0] not in ('16','17')]
            before=state()
            # The stock varchar(15) accepts an IPv6 POST but the old permissive insert truncates it.
            long_ip='2001:db8:abcd:1234::1'
            assert 'Successfully added' in create('LongIPv6',long_ip,'base')
            stored=db("SELECT framedipaddress FROM radippool WHERE pool_name='LongIPv6'",'base')
            assert stored==long_ip[:15] and stored!=long_ip
            assert 'Unable to create' in create('LongIPv6',long_ip);assert state()==before
            db('ALTER TABLE radippool MODIFY framedipaddress VARCHAR(45) NOT NULL DEFAULT \'\'')
            assert 'Successfully added' in create('LongIPv6',long_ip)
            item='ippool-'+db("SELECT id FROM radippool WHERE pool_name='LongIPv6'")
            assert 'Successfully updated' in update(item=item,pool='LongIPv6Edit',address='2001:db8:abcd:5678::1')
            assert db("SELECT framedipaddress FROM radippool WHERE pool_name='LongIPv6Edit'")=='2001:db8:abcd:5678::1'
            assert 'Deleted 1 ippool' in post(delete,{'item':item})
            db('ALTER TABLE radippool MODIFY framedipaddress VARCHAR(15) NOT NULL DEFAULT \'\'')
            assert 'Successfully added' in create('ShortIPv6','::1')
            item='ippool-'+db("SELECT id FROM radippool WHERE pool_name='ShortIPv6'");assert 'Deleted 1 ippool' in post(delete,{'item':item})
            # Charset coercion is detected by readback and rolled back, even with permissive SQL mode.
            db('ALTER TABLE radippool CONVERT TO CHARACTER SET latin1')
            before=state();assert 'Unable to create' in create('Pool😀','192.0.2.80');assert state()==before
            db('ALTER TABLE radippool CONVERT TO CHARACTER SET utf8mb4')
            # Existing transport preserves raw names and literal zero; only the LIKE search strips percent.
            for i,pool in enumerate(('Pool &+é','Pool%Name','0')):
                assert 'Successfully added' in create(pool,'192.0.2.'+str(90+i))
                item='ippool-'+db("SELECT id FROM radippool WHERE pool_name='"+pool+"'")
                html=req(edit,query={'item':item})[1];assert any(form.get('pool_name')==pool for form in Forms(html).forms)
                assert 'Successfully updated' in update(item=item,pool=pool,address='192.0.2.'+str(90+i))
                if '%' not in pool:
                    parsed=Rows(req(listpage,query={'pool_name':pool})[1]);assert len(parsed.rows)==1 and parsed.rows[0][0]==item
                    urls=[u for u in parsed.links if u.startswith('mng-rad-ippool-list.php?pool_name=')];assert urls
                    assert urllib.parse.parse_qs(urllib.parse.urlsplit(urls[0]).query)=={'pool_name':[pool]}
                assert 'Deleted 1 ippool' in post(delete,{'item[]':[item,item]})
            print('PASS characterized legacy IPv6 truncation, actual configured capacity, full IPv6 on widened schema, charset coercion rollback and special/zero pool names',flush=True)
            # Independent-session concurrency, with freshly rendered CSRF tokens for every operation.
            sid2=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',sid2)
            tokens={s:token(session_id=s) for s in (sid,sid2)}
            def concurrent_create(s):return req(new,{'pool_name':'ConcurrentA' if s==sid else 'ConcurrentB','framedipaddress':'192.0.2.130','csrf_token':tokens[s]},session_id=s)[1]
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:out=list(pool.map(concurrent_create,(sid,sid2)))
            assert sum('Successfully added' in x for x in out)==1 and sum('already contained' in x for x in out)==1
            item='ippool-'+db("SELECT id FROM radippool WHERE framedipaddress='192.0.2.130'")
            tokens={s:token(session_id=s) for s in (sid,sid2)}
            def concurrent_edit(s):return req(edit,{'item':item,'pool_name':'FinalA' if s==sid else 'FinalB','framedipaddress':'192.0.2.131' if s==sid else '192.0.2.132','csrf_token':tokens[s]},session_id=s)[1]
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:out=list(pool.map(concurrent_edit,(sid,sid2)))
            assert all('Successfully updated' in x for x in out)
            assert db("SELECT pool_name,framedipaddress FROM radippool WHERE id="+item[7:]) in ('FinalA\t192.0.2.131','FinalB\t192.0.2.132')
            tokens={s:token(session_id=s) for s in (sid,sid2)}
            def concurrent_delete(s):return req(delete,{'item[]':[item],'csrf_token':tokens[s]},session_id=s)[1]
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:out=list(pool.map(concurrent_delete,(sid,sid2)))
            assert sum('Deleted 1 ippool' in x for x in out)==1 and sum('Unable to delete' in x for x in out)==1
            # A real independent connection holds exactly the production advisory lock.
            lockname='dalo_ippool_'+hashlib.sha256(b'candidate:`radippool`').hexdigest()[:52]
            tok=token()
            holder=subprocess.Popen(['docker','exec',h.DB,'mariadb','-uroot','-N','-B','candidate','-e',"SELECT GET_LOCK('"+lockname+"',0); SELECT SLEEP(2); SELECT RELEASE_LOCK('"+lockname+"');"],stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
            try:
                wait_for(lambda:db("SELECT IS_USED_LOCK('"+lockname+"')")!='NULL','independent advisory lock')
                with concurrent.futures.ThreadPoolExecutor(max_workers=1) as pool:
                    waiting=pool.submit(req,new,{'pool_name':'LockContention','framedipaddress':'192.0.2.145','csrf_token':tok})
                    time.sleep(.1);assert not waiting.done(),'Page did not wait for its database-scoped lock'
                    assert 'Successfully added' in waiting.result(timeout=15)[1]
                assert holder.wait(timeout=10)==0
            finally:
                if holder.poll() is None:holder.kill();holder.wait()
                holder.communicate()
            item='ippool-'+db("SELECT id FROM radippool WHERE pool_name='LockContention'")
            assert 'Deleted 1 ippool' in post(delete,{'item':item})
            before=state()
            for page,fields in ((new,{'pool_name':'Denied','framedipaddress':'192.0.2.150'}),(edit,{'item':'ippool-10','pool_name':'Denied','framedipaddress':'192.0.2.150'}),(delete,{'item[]':['ippool-10']})):
                assert 'CSRF token error' in req(page,dict(fields,**{'csrf_token[]':['bad']}))[1];assert state()==before
            anonymous=secrets.token_hex(16)
            expired=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',expired,'default','9001','7200')
            for page in PAGES:
                assert req(page,session_id=anonymous,follow_redirects=False)[0]==302
                assert req(page,session_id=expired,follow_redirects=False)[0]==302
            assert req(new,{'pool_name':'Unauthenticated','framedipaddress':'192.0.2.150'},session_id=anonymous,follow_redirects=False)[0]==302
            assert state()==before
            assert req('library/ip_pool_pages_pdo.php')[0]==404
            denied=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',denied,'default','9002')
            for page in PAGES:
                html=req(page,session_id=denied)[1];assert not Rows(html).rows and 'name="pool_name"' not in html
            assert state()==before
            for q in ({'pool_name[]':['bad']},{'pool_name':"' OR 1=1 --"},{'orderBy[]':['id'],'orderType[]':['asc']}):
                status,html=req(listpage,query=q);assert status==200 and 'SQLSTATE' not in html
            print('PASS actual concurrent create/edit/delete, independent lock contention, authentication/expiry and ACL/CSRF/malformed filter gates',flush=True)
            other=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',other,'other')
            before=state();assert 'Successfully added' in create('OtherOnly','192.0.2.160',session_id=other)
            item='ippool-'+db("SELECT id FROM radippool WHERE pool_name='OtherOnly'",'candidate_other')
            assert 'Successfully updated' in update(item=item,pool='OtherEdited',address='192.0.2.161',session_id=other)
            assert Rows(req(listpage,query={'pool_name':'OtherEdited'},session_id=other)[1]).rows
            assert any(o.get('value')==item for o in Rows(req(delete,session_id=other)[1]).options)
            assert state()==before
            assert 'Deleted 1 ippool' in post(delete,{'item[]':[item]},session_id=other);assert state()==before
            configfile=f/'candidate/app/common/includes/daloradius.conf.php';conf=configfile.read_text()
            db('RENAME TABLE radippool TO custom_pool');conf+="\n$configValues['CONFIG_DB_TBL_RADIPPOOL']='custom_pool';\n";configfile.write_text(conf)
            assert 'Successfully added' in create('Configured','192.0.2.170')
            item='ippool-'+db("SELECT id FROM custom_pool WHERE pool_name='Configured'")
            assert 'Successfully updated' in update(item=item,pool='ConfiguredEdit',address='192.0.2.171')
            assert Rows(req(listpage,query={'pool_name':'ConfiguredEdit'})[1]).rows
            assert any(o.get('value')==item for o in Rows(req(delete)[1]).options)
            assert 'Deleted 1 ippool' in post(delete,{'item[]':[item]})
            configfile.write_text(conf+"\n$configValues['CONFIG_DEBUG_SQL_ONPAGE']='yes';\n")
            html=create('DebugSentinel','192.0.2.180');debug=html.split('Debugging SQL Queries:',1)[1]
            assert 'DebugSentinel' not in debug and '192.0.2.180' not in debug
            html=req(listpage,query={'pool_name':'DebugSentinel'})[1];debug=html.split('Debugging SQL Queries:',1)[1]
            assert 'DebugSentinel' not in debug and 'LIKE ?' in debug
            configfile.write_text(conf)
            db("INSERT INTO custom_pool(id,pool_name,framedipaddress) VALUES (4294967295,'UnsignedMax','192.0.2.190')")
            assert any(form.get('item')=='ippool-4294967295' for form in Forms(req(edit,query={'item':'ippool-4294967295'})[1]).forms)
            assert Rows(req(listpage,query={'pool_name':'UnsignedMax'})[1]).rows[0][0]=='ippool-4294967295'
            assert 'Deleted 1 ippool' in post(delete,{'item[]':['ippool-4294967295']})
            db('ALTER TABLE custom_pool AUTO_INCREMENT=1000')
            before=state(table='custom_pool')
            configfile.write_text(conf+"\n$configValues['CONFIG_DB_TBL_RADIPPOOL']='custom_pool;invalid';\n")
            assert 'Unable to create' in create();assert state(table='custom_pool')==before
            configfile.write_text(conf)
            db('RENAME TABLE custom_pool TO absent_pool')
            assert 'Unable to load IP pools' in req(listpage)[1]
            assert 'Unable to load IP pool options' in req(delete)[1]
            assert 'Unable to create' in create()
            db('RENAME TABLE absent_pool TO custom_pool')
            db('RENAME TABLE radcheck TO absent_auth')
            html=req(listpage,query={'pool_name':'Edited'})[1];assert 'Unable to load IP pool user links' in html and 'SQLSTATE' not in html
            db('RENAME TABLE absent_auth TO radcheck')
            diagnostics=subprocess.run(['docker','logs',h.WEB],text=True,capture_output=True,check=True);logs=diagnostics.stdout+diagnostics.stderr
            assert not any('/fixtures/candidate/' in line and any(mark in line for mark in ('PHP Warning:','PHP Fatal error:','PHP Deprecated:')) for line in logs.splitlines()),'Candidate PHP diagnostics (body suppressed)'
            assert 'private fixture marker' not in logs and 'SQLSTATE' not in logs
            print('PASS selected location, configured/invalid tables, sanitized late reads, SQL debug redaction, strict PDO catalog and shared user-helper reuse and no legacy-open/close; clean script logs',flush=True)
        finally:
            run('docker','rm','-f',h.WEB,h.DB,check=False);run('docker','network','rm',h.NETWORK,check=False)
            subprocess.run(['sudo','-n','chown','-R',str(os.getuid())+':'+str(os.getgid()),str(f)],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
            assert not [n for n in run('docker','ps','-a','--format','{{.Names}}').splitlines() if n in (h.WEB,h.DB)]
            assert h.NETWORK not in run('docker','network','ls','--format','{{.Name}}').splitlines()
            print('CLEANUP R07 fixture containers/network removed',flush=True)
if __name__=='__main__':main()
