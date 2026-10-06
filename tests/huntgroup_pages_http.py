#!/usr/bin/env python3
"""R08: native pinned PEAR/PDO huntgroup CRUD, lists, atomicity and isolation."""
import concurrent.futures,hashlib,json,os,re,secrets,shutil,subprocess,tempfile,time,urllib.parse,urllib.request,urllib.error
from html.parser import HTMLParser
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap
import user_actions_http as h
from acct_maintenance_http import Forms
ROOT=Path(__file__).resolve().parents[1]
BASE='71bb1985b1b449525b93197fb6a71657e0bab2d0'
SUFFIX=secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=['pdo-r08-'+k+'-'+SUFFIX for k in ('db','web','net')]
run,sql,wait_for=h.run,h.sql,h.wait_for
PAGES=['mng-rad-hunt-'+kind+'.php' for kind in ('new','edit','del','list')]
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
    with tempfile.TemporaryDirectory(prefix='pdo-r08-',dir=scratch) as tmp:
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
        (f/'candidate/app/operators/borrowed.php').write_text("<?php include 'library/checklogin.php';include_once '../common/includes/config_read.php';require_once 'library/huntgroup_pages_pdo.php';$logDebugSQL='';$pdo=dalo_pdo_connect($configValues,$_SESSION['location_name']??'default');$pdo->beginTransaction();$pdo->exec(\"INSERT INTO radhuntgroup(groupname,nasipaddress) VALUES ('Caller','192.0.2.200')\");$rejected=false;try {dalo_hunt_mutate($pdo,$configValues,function(){return true;});}catch(LogicException $e){$rejected=true;}$active=$pdo->inTransaction();$pdo->rollBack();echo json_encode(['rejected'=>$rejected,'active'=>$active]);")
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
                acl=','.join("(9001,'mng_rad_hunt_"+kind+"',1)" for kind in ('new','edit','del','list'))
                db('INSERT INTO operators_acl(operator_id,file,access) VALUES '+acl+"; INSERT INTO operators_acl(operator_id,file,access) SELECT 9002,file,0 FROM operators_acl WHERE operator_id=9001;",version)
                rows=[]
                for i in range(8):
                    values=[str(10+i),"'"+['Alpha','Beta','Gamma','Delta'][i//2]+"'","'192.0.2."+str(10+i)+"'",'NULL' if i==7 else "'"+str(10+i)+"'"]
                    rows.append('('+','.join(values)+')')
                db('INSERT INTO radhuntgroup(id,groupname,nasipaddress,nasportid) VALUES '+','.join(rows),version)
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
            def create(group='Created',address='192.0.2.80',port='80',version='candidate',session_id=None):
                return post(new,{'groupname':group,'nasipaddress':address,'nasportid':port},version,session_id)
            def update(item='huntgroup-10',group='Edited',address='192.0.2.81',port='81',version='candidate',session_id=None):
                return post(edit,{'item':item,'groupname':group,'nasipaddress':address,'nasportid':port},version,session_id)
            def state(version='candidate',table='radhuntgroup'):
                return db('SELECT id,groupname,nasipaddress,nasportid FROM '+table+' ORDER BY id',version)
            for version in ('base','candidate'):
                created=create(version=version);assert 'Successfully added' in created
                edit_links=[u for u in Rows(created).links if u.startswith('mng-rad-hunt-edit.php?item=')]
                assert len(edit_links)==1 and urllib.parse.parse_qs(urllib.parse.urlsplit(edit_links[0]).query)=={'item':['huntgroup-18']}
                assert any(form.get('item')=='huntgroup-18' and form.get('groupname')=='Created' for form in Forms(req(edit_links[0],version=version)[1]).forms)
                assert 'Successfully updated' in update(version=version)
                assert 'Deleted 1 huntgroup' in post(delete,{'item[]':['huntgroup-18']},version)
            assert state('base')==state(),'ordinary complete CRUD state parity'
            comparisons=0
            for order in ('id','groupname','nasipaddress','nasportid'):
                for direction in ('asc','desc'):
                    for page_num in (1,2,3,4):
                        q={'orderBy':order,'orderType':direction,'page':str(page_num)}
                        assert Rows(req(listpage,version='base',query=q)[1]).rows==Rows(req(listpage,query=q)[1]).rows,(order,direction,page_num);comparisons+=1
            for page in (edit,delete):
                left=Forms(req(page,version='base',query={'item':'huntgroup-10'})[1]).forms
                right=Forms(req(page,query={'item':'huntgroup-10'})[1]).forms
                for forms in (left,right):
                    for form in forms:form.pop('csrf_token',None)
                assert left==right;comparisons+=1
            q={'orderBy':'missing','orderType':'bad','page':'bad'}
            assert Rows(req(listpage,version='base',query=q)[1]).rows==Rows(req(listpage,query=q)[1]).rows;comparisons+=1
            print('PASS native ordinary PEAR/PDO CRUD/full state and',comparisons,'sort/page/form/default comparisons',flush=True)
            before=state()
            assert 'already contained' in update(version='base') and 'Successfully updated' in update()
            assert state()==before,'unchanged edit must succeed without mutation'
            assert 'already contained' in update(group='RenameOnly',version='base')
            assert 'Successfully updated' in update(group='RenameOnly');assert 'RenameOnly' in state()
            assert 'Successfully updated' in update(group='Edited')
            assert state()==before
            base_rows=state('base').splitlines()
            assert 'Deleted 1 huntgroup' in post(delete,{'item[]':['huntgroup-10','huntgroup-999']},'base')
            assert state('base').splitlines()==[row for row in base_rows if row.split('\t',1)[0]!='10']
            base_rows=state('base').splitlines()
            db("\nDELIMITER $$\nCREATE TRIGGER fail_legacy_delete BEFORE DELETE ON radhuntgroup FOR EACH ROW BEGIN IF OLD.id=12 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='legacy rollback characterization'; END IF; END$$\nDELIMITER ;\n",'base')
            assert 'Deleted 1 huntgroup' in post(delete,{'item[]':['huntgroup-11','huntgroup-12']},'base')
            assert state('base').splitlines()==[row for row in base_rows if row.split('\t',1)[0]!='11']
            db('DROP TRIGGER fail_legacy_delete','base')
            print('PASS unmodified legacy self-collision, ignored stale selection and partial late-delete characterization',flush=True)
            for fields in ({'groupname[]':['bad'],'nasipaddress':'192.0.2.80'}, {'groupname':'Bad','nasipaddress[]':['192.0.2.80']}, {'groupname':'Bad','nasipaddress':'not-ip'}, {'groupname':'X'*65,'nasipaddress':'192.0.2.80'},{'groupname':'Bad\0Name','nasipaddress':'192.0.2.80'}, {'groupname':'Bad','nasipaddress':'192.0.2.80','nasportid[]':['1']}):
                assert 'Unable to create' in post(new,fields);assert state()==before
            for port in ('1junk','-1','+1','1.2','1e3','9'*16):
                assert 'Unable to create' in create(port=port);assert state()==before
            assert 'Successfully added' in create('LegacyCoercion','192.0.2.202','12junk',version='base')
            assert db("SELECT nasportid FROM radhuntgroup WHERE groupname='LegacyCoercion'",'base')=='12'
            assert 'Unable to create' in create('LegacyCoercion','192.0.2.202','12junk');assert state()==before
            assert 'already contained' in create('OtherGroup','192.0.2.11','11');assert state()==before
            assert 'already contained' in update(address='192.0.2.11',port='11');assert state()==before
            # The same address on a different port, and same port on a different address, remain legal.
            for group,address,port in (('OtherPort','192.0.2.11','12'),('OtherAddress','192.0.2.85','11'),('ZeroPort','192.0.2.86',''),('LeadingZeros','192.0.2.87','00012'),('MaxPort','192.0.2.88','999999999999999')):
                assert 'Successfully added' in create(group,address,port)
                value=db("SELECT nasportid FROM radhuntgroup WHERE groupname='"+group+"'")
                assert value==('0' if port=='' else str(int(port)))
                item='huntgroup-'+db("SELECT id FROM radhuntgroup WHERE groupname='"+group+"'")
                assert 'Deleted 1 huntgroup' in post(delete,{'item':item})
            assert state()==before
            assert 'Successfully added' in create('NullDoesNotMatchZero','192.0.2.17','0')
            item='huntgroup-'+db("SELECT id FROM radhuntgroup WHERE nasipaddress='192.0.2.17' AND nasportid='0'")
            assert db('SELECT nasportid IS NULL FROM radhuntgroup WHERE id=17')=='1'
            assert 'Deleted 1 huntgroup' in post(delete,{'item':item});assert state()==before
            db("UPDATE radhuntgroup SET nasportid='00011' WHERE id=11")
            variant_before=state();assert 'already contained' in create('NumericCollision','192.0.2.11','11');assert state()==variant_before
            db("UPDATE radhuntgroup SET nasportid='11' WHERE id=11");assert state()==before
            for item in ('huntgroup-999','huntgroup-0','huntgroup-01','huntgroup-4294967296','huntgroup-1junk'):
                assert 'Unable to load or update' in update(item=item);assert state()==before
            assert 'Unable to load or update' in post(edit,{'item[]':['huntgroup-10'],'groupname':'Bad','nasipaddress':'192.0.2.80'});assert state()==before
            for selection in (['huntgroup-10','huntgroup-999'],['huntgroup-10','junk'],['huntgroup-10','huntgroup-01']):
                assert 'Unable to delete' in post(delete,{'item[]':selection});assert state()==before
            assert 'Unable to delete' in post(delete,{'item[0]':'huntgroup-10','item[1][bad]':'nested'});assert state()==before
            assert 'Unable to delete' in req(delete,{'csrf_token':token(),'item[]':['huntgroup-10']*1100})[1];assert state()==before
            preview=req(delete,query={'item[]':['huntgroup-10','huntgroup-11']})[1]
            tok=next(form['csrf_token'] for form in Forms(preview).forms if 'csrf_token' in form)
            db('DELETE FROM radhuntgroup WHERE id=11');stale_before=state()
            assert 'Unable to delete' in req(delete,{'csrf_token':tok,'item[]':['huntgroup-10','huntgroup-11']})[1];assert state()==stale_before
            db("INSERT INTO radhuntgroup(id,groupname,nasipaddress,nasportid) VALUES (11,'Alpha','192.0.2.11','11')");assert state()==before
            db("\nDELIMITER $$\nCREATE TRIGGER fail_delete BEFORE DELETE ON radhuntgroup FOR EACH ROW BEGIN IF OLD.id=12 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='private fixture marker'; END IF; END$$\nDELIMITER ;\n")
            html=post(delete,{'item[]':['huntgroup-10','huntgroup-12']});assert 'Unable to delete' in html and 'SQLSTATE' not in html and 'private fixture marker' not in html;assert state()==before
            db('DROP TRIGGER fail_delete')
            for action,event,call,message in (('update','UPDATE',lambda:update(group='Failure'),'Unable to load or update'),('insert','INSERT',lambda:create(),'Unable to create')):
                db("CREATE TRIGGER fail_"+action+" BEFORE "+event+" ON radhuntgroup FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='private fixture marker'")
                html=call();assert message in html and 'private fixture marker' not in html;assert state()==before
                db('DROP TRIGGER fail_'+action)
            assert json.loads(req('borrowed.php')[1])=={'rejected':True,'active':True};assert state()==before
            db('ALTER TABLE radhuntgroup ENGINE=MyISAM')
            assert 'Unable to create' in create();assert 'Unable to load or update' in update();assert 'Unable to delete' in post(delete,{'item[]':['huntgroup-10']});assert state()==before
            db('ALTER TABLE radhuntgroup ENGINE=InnoDB')
            rows_before=state().splitlines()
            assert 'Deleted 2 huntgroup' in post(delete,{'item[]':['huntgroup-16','huntgroup-17','huntgroup-16']})
            assert state().splitlines()==[row for row in rows_before if row.split('\t',1)[0] not in ('16','17')]
            before=state()
            print('PASS pair/port contracts, malformed/overflow/truncated IDs, actual stale preview, INSERT/UPDATE/later DELETE rollback, other groups invariance and ownership/engine guards',flush=True)
            long_ip='2001:db8:abcd:1234::1'
            assert 'Successfully added' in create('LongIPv6',long_ip,version='base')
            assert db("SELECT nasipaddress FROM radhuntgroup WHERE groupname='LongIPv6'",'base')==long_ip[:15]
            assert 'Unable to create' in create('LongIPv6',long_ip);assert state()==before
            db("ALTER TABLE radhuntgroup MODIFY nasipaddress VARCHAR(45) NOT NULL DEFAULT ''")
            assert 'Successfully added' in create('LongIPv6',long_ip)
            item='huntgroup-'+db("SELECT id FROM radhuntgroup WHERE groupname='LongIPv6'")
            assert 'Successfully updated' in update(item=item,group='LongIPv6Edit',address='2001:db8:abcd:5678::1')
            assert db("SELECT nasipaddress FROM radhuntgroup WHERE groupname='LongIPv6Edit'")=='2001:db8:abcd:5678::1'
            assert 'Deleted 1 huntgroup' in post(delete,{'item':item})
            db("ALTER TABLE radhuntgroup MODIFY nasipaddress VARCHAR(15) NOT NULL DEFAULT ''")
            db('ALTER TABLE radhuntgroup CONVERT TO CHARACTER SET latin1')
            assert 'Unable to create' in create('Group😀');assert state()==before
            db('ALTER TABLE radhuntgroup CONVERT TO CHARACTER SET utf8mb4')
            unicode_name='é'*64
            assert 'Successfully added' in create(unicode_name,'192.0.2.89')
            item='huntgroup-'+db("SELECT id FROM radhuntgroup WHERE nasipaddress='192.0.2.89'")
            assert any(form.get('groupname')==unicode_name for form in Forms(req(edit,query={'item':item})[1]).forms)
            edge_before=state()
            assert 'Unable to load or update' in update(item=item,group='é'*65,address='192.0.2.89');assert state()==edge_before
            assert 'Deleted 1 huntgroup' in post(delete,{'item':item});assert state()==before
            for i,group in enumerate(('Group &+é',"Group%'Quote",'0')):
                assert 'Successfully added' in create(group,'192.0.2.'+str(90+i))
                # Lookup by address avoids SQL quoting fixture names.
                item='huntgroup-'+db("SELECT id FROM radhuntgroup WHERE nasipaddress='192.0.2."+str(90+i)+"'")
                assert any(form.get('groupname')==group for form in Forms(req(edit,query={'item':item})[1]).forms)
                assert 'Successfully updated' in update(item=item,group=group,address='192.0.2.'+str(90+i))
                q={'orderBy':'id','orderType':'desc'}
                parsed=Rows(req(listpage,query=q)[1]);assert parsed.rows[0][0]==item and parsed.rows[0][1][1].startswith(group), (parsed.rows,item,group)
                assert any(urllib.parse.parse_qs(urllib.parse.urlsplit(u).query).get('item')==[item] for u in parsed.links if u.startswith('mng-rad-hunt-edit.php?'))
                assert any(o.get('value')==item for o in Rows(req(delete)[1]).options)
                assert 'Deleted 1 huntgroup' in post(delete,{'item[]':[item,item]})
            assert state()==before
            print('PASS legacy IPv6 truncation, actual capacity/readback, widened native IPv6 and charset rollback, special/zero names through real controls',flush=True)
            sid2=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',sid2)
            tokens={s:token(session_id=s) for s in (sid,sid2)}
            def concurrent_create(s):return req(new,{'groupname':'ConcurrentA' if s==sid else 'ConcurrentB','nasipaddress':'192.0.2.130','nasportid':'130','csrf_token':tokens[s]},session_id=s)[1]
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:out=list(pool.map(concurrent_create,(sid,sid2)))
            assert sum('Successfully added' in x for x in out)==1 and sum('already contained' in x for x in out)==1
            item='huntgroup-'+db("SELECT id FROM radhuntgroup WHERE nasipaddress='192.0.2.130'")
            tokens={s:token(session_id=s) for s in (sid,sid2)}
            def concurrent_edit(s):return req(edit,{'item':item,'groupname':'FinalA' if s==sid else 'FinalB','nasipaddress':'192.0.2.131' if s==sid else '192.0.2.132','nasportid':'131' if s==sid else '132','csrf_token':tokens[s]},session_id=s)[1]
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:out=list(pool.map(concurrent_edit,(sid,sid2)))
            assert all('Successfully updated' in x for x in out)
            assert db('SELECT groupname,nasipaddress,nasportid FROM radhuntgroup WHERE id='+item[len('huntgroup-'):]) in ('FinalA\t192.0.2.131\t131','FinalB\t192.0.2.132\t132')
            tokens={s:token(session_id=s) for s in (sid,sid2)}
            def concurrent_delete(s):return req(delete,{'item[]':[item],'csrf_token':tokens[s]},session_id=s)[1]
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:out=list(pool.map(concurrent_delete,(sid,sid2)))
            assert sum('Deleted 1 huntgroup' in x for x in out)==1 and sum('Unable to delete' in x for x in out)==1
            lockname='dalo_hunt_'+hashlib.sha256(b'candidate:`radhuntgroup`').hexdigest()[:52]
            tok=token()
            holder=subprocess.Popen(['docker','exec',h.DB,'mariadb','-uroot','-N','-B','candidate','-e',"SELECT GET_LOCK('"+lockname+"',0); SELECT SLEEP(2); SELECT RELEASE_LOCK('"+lockname+"');"],stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
            try:
                wait_for(lambda:db("SELECT IS_USED_LOCK('"+lockname+"')")!='NULL','independent advisory lock')
                with concurrent.futures.ThreadPoolExecutor(max_workers=1) as pool:
                    waiting=pool.submit(req,new,{'groupname':'LockContention','nasipaddress':'192.0.2.145','nasportid':'145','csrf_token':tok})
                    time.sleep(.1);assert not waiting.done()
                    assert 'Successfully added' in waiting.result(timeout=15)[1]
                assert holder.wait(timeout=10)==0
            finally:
                if holder.poll() is None:holder.kill();holder.wait()
                holder.communicate()
            item='huntgroup-'+db("SELECT id FROM radhuntgroup WHERE groupname='LockContention'");assert 'Deleted 1 huntgroup' in post(delete,{'item':item})
            before=state()
            for page,fields in ((new,{'groupname':'Denied','nasipaddress':'192.0.2.150'}),(edit,{'item':'huntgroup-10','groupname':'Denied','nasipaddress':'192.0.2.150'}),(delete,{'item[]':['huntgroup-10']})):
                assert 'CSRF token error' in req(page,dict(fields,**{'csrf_token[]':['bad']}))[1];assert state()==before
            anonymous=secrets.token_hex(16);expired=secrets.token_hex(16)
            run('docker','exec',h.WEB,'php','/fixtures/session.php',expired,'default','9001','7200')
            for page in PAGES:
                assert req(page,session_id=anonymous,follow_redirects=False)[0]==302
                assert req(page,session_id=expired,follow_redirects=False)[0]==302
            assert req(new,{'groupname':'Unauthenticated','nasipaddress':'192.0.2.150'},session_id=anonymous,follow_redirects=False)[0]==302
            assert state()==before and req('library/huntgroup_pages_pdo.php')[0]==404
            denied=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',denied,'default','9002')
            for page in PAGES:
                html=req(page,session_id=denied)[1];assert not Rows(html).rows and 'name="groupname"' not in html
            assert state()==before
            status,html=req(listpage,query={'orderBy[]':['id'],'orderType[]':['asc']});assert status==200 and 'SQLSTATE' not in html
            print('PASS independent-session create/edit/delete races, actual lock contention, authentication/expiry, ACL/CSRF and malformed sorts',flush=True)
            other=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',other,'other')
            assert 'Successfully added' in create('OtherOnly','192.0.2.160','160',session_id=other)
            item='huntgroup-'+db("SELECT id FROM radhuntgroup WHERE groupname='OtherOnly'",'candidate_other')
            assert 'Successfully updated' in update(item=item,group='OtherEdited',address='192.0.2.161',port='161',session_id=other)
            assert any(o.get('value')==item for o in Rows(req(delete,session_id=other)[1]).options)
            assert item in [x[0] for x in Rows(req(listpage,query={'orderBy':'id','orderType':'desc'},session_id=other)[1]).rows]
            assert state()==before
            assert 'Deleted 1 huntgroup' in post(delete,{'item[]':[item]},session_id=other);assert state()==before
            configfile=f/'candidate/app/common/includes/daloradius.conf.php';conf=configfile.read_text()
            db('RENAME TABLE radhuntgroup TO custom_hunt');conf+="\n$configValues['CONFIG_DB_TBL_RADHG']='custom_hunt';\n";configfile.write_text(conf)
            assert 'Successfully added' in create('Configured','192.0.2.170','170')
            item='huntgroup-'+db("SELECT id FROM custom_hunt WHERE groupname='Configured'")
            assert 'Successfully updated' in update(item=item,group='ConfiguredEdit',address='192.0.2.171',port='171')
            assert item in [x[0] for x in Rows(req(listpage,query={'orderBy':'id','orderType':'desc'})[1]).rows]
            assert any(o.get('value')==item for o in Rows(req(delete)[1]).options)
            assert 'Deleted 1 huntgroup' in post(delete,{'item[]':[item]})
            configfile.write_text(conf+"\n$configValues['CONFIG_DEBUG_SQL_ONPAGE']='yes';\n")
            html=create('DebugSentinel','192.0.2.180','180');debug=html.split('Debugging SQL Queries:',1)[1]
            assert 'DebugSentinel' not in debug and '192.0.2.180' not in debug
            configfile.write_text(conf)
            db("INSERT INTO custom_hunt(id,groupname,nasipaddress,nasportid) VALUES (4294967295,'UnsignedMax','192.0.2.190','190')")
            assert any(form.get('item')=='huntgroup-4294967295' for form in Forms(req(edit,query={'item':'huntgroup-4294967295'})[1]).forms)
            assert Rows(req(listpage,query={'orderBy':'id','orderType':'desc'})[1]).rows[0][0]=='huntgroup-4294967295'
            assert 'Deleted 1 huntgroup' in post(delete,{'item[]':['huntgroup-4294967295']})
            db('ALTER TABLE custom_hunt AUTO_INCREMENT=1000')
            before=state(table='custom_hunt')
            configfile.write_text(conf+"\n$configValues['CONFIG_DB_TBL_RADHG']='custom_hunt;invalid';\n")
            assert 'Unable to create' in create();assert state(table='custom_hunt')==before
            configfile.write_text(conf)
            db('ALTER TABLE custom_hunt RENAME COLUMN groupname TO absent_group')
            assert 'Unable to load huntgroups' in req(listpage)[1]
            db('ALTER TABLE custom_hunt RENAME COLUMN absent_group TO groupname')
            assert state(table='custom_hunt')==before
            db('RENAME TABLE custom_hunt TO absent_hunt')
            assert 'Unable to load huntgroups' in req(listpage)[1]
            assert 'Unable to load Huntgroup options' in req(delete)[1]
            assert 'Unable to create' in create()
            db('RENAME TABLE absent_hunt TO custom_hunt')
            configfile.write_text(conf+"\n$configValues['CONFIG_DB_TBL_RADHG']='empty_hunt';\n")
            db('CREATE TABLE empty_hunt LIKE custom_hunt')
            html=req(listpage)[1];assert 'Nothing to display' in html and not Rows(html).rows
            assert 'Unable to load' not in req(delete)[1]
            configfile.write_text(conf)
            diagnostics=subprocess.run(['docker','logs',h.WEB],text=True,capture_output=True,check=True);logs=diagnostics.stdout+diagnostics.stderr
            assert not any('/fixtures/candidate/' in line and any(mark in line for mark in ('PHP Warning:','PHP Fatal error:','PHP Deprecated:')) for line in logs.splitlines()),'Candidate PHP diagnostics (details suppressed)'
            assert 'private fixture marker' not in logs and 'SQLSTATE' not in logs
            print('PASS configured/invalid/missing/late-read/empty tables, selected backend, maximum unsigned IDs, SQL debug redaction, legacy-open/close tripwires and clean candidate diagnostics',flush=True)
        finally:
            run('docker','rm','-f',h.WEB,h.DB,check=False);run('docker','network','rm',h.NETWORK,check=False)
            subprocess.run(['sudo','-n','chown','-R',str(os.getuid())+':'+str(os.getgid()),str(f)],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
            assert not [n for n in run('docker','ps','-a','--format','{{.Names}}').splitlines() if n in (h.WEB,h.DB)]
            assert h.NETWORK not in run('docker','network','ls','--format','{{.Name}}').splitlines()
            print('CLEANUP R08 fixture containers/network removed',flush=True)
if __name__=='__main__':main()
