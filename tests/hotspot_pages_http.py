#!/usr/bin/env python3
"""R09: native pinned PEAR/PDO hotspot CRUD, JSON/charts, atomicity and isolation."""
import concurrent.futures,hashlib,json,os,re,secrets,shutil,subprocess,tempfile,time,urllib.parse,urllib.request,urllib.error
from html.parser import HTMLParser
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap
import user_actions_http as h
from acct_maintenance_http import Forms as InputForms
ROOT=Path(__file__).resolve().parents[1]
BASE='493027ebb4ab078d83eac303ffebf80d14199c40'
SUFFIX=secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=['pdo-r09-'+k+'-'+SUFFIX for k in ('db','web','net')]
run,sql,wait_for=h.run,h.sql,h.wait_for
PAGES=['mng-hs-'+kind+'.php' for kind in ('new','edit','del','list')]
ENDPOINTS=['library/ajax/hotspot_info.php','library/graphs/hotspot_details.php']
class Forms(InputForms):
    """Capture textareas too: the native hotspot form stores address there."""
    def __init__(self,html):
        self.textarea=None;self.text=[];super().__init__(html)
    def handle_starttag(self,tag,attrs):
        super().handle_starttag(tag,attrs);a=dict(attrs)
        if tag=='textarea' and self.current is not None:
            self.textarea=a.get('name');self.text=[]
    def handle_data(self,data):
        if self.textarea is not None:self.text.append(data)
    def handle_endtag(self,tag):
        if tag=='textarea':
            if self.current is not None and self.textarea is not None:self.current[self.textarea]=''.join(self.text)
            self.textarea=None;self.text=[]
        super().handle_endtag(tag)
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
            if tag=='input' and a.get('name')=='name[]':self.row['selected']=a.get('value')
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
    with tempfile.TemporaryDirectory(prefix='pdo-r09-',dir=scratch) as tmp:
        f=Path(tmp)
        for version in ('base','candidate'):
            shutil.copytree(ROOT/'app',f/version/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if version=='base':
                restore_pear_bootstrap((f / version / 'app').parent, BASE)
                for rel in PAGES+ENDPOINTS:
                    (f/version/'app/operators'/rel).write_bytes(subprocess.check_output(['git','show',BASE+':app/operators/'+rel],cwd=ROOT))
            conf=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,value in {'CONFIG_DB_HOST':h.DB,'CONFIG_DB_USER':'root','CONFIG_DB_PASS':'','CONFIG_DB_NAME':version,'CONFIG_IFACE_TABLES_LISTING':'2'}.items():
                conf+='\n$configValues['+repr(key)+']='+repr(value)+';\n'
            conf+="\n$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>'"+h.DB+"','Username'=>'root','Password'=>'','Database'=>'"+version+"_other','Port'=>'3306');\n"
            (f/version/'app/common/includes/daloradius.conf.php').write_text(conf)
        (f/'session.php').write_text("<?php session_name('daloradius_operator_sid');session_id($argv[1]);session_start();$_SESSION=['daloradius_logged_in'=>true,'operator_id'=>(int)($argv[3]??9001),'operator_user'=>bin2hex(random_bytes(12)),'location_name'=>($argv[2]??'default'),'time'=>time()-(int)($argv[4]??0)];session_write_close();")
        (f/'candidate/app/operators/borrowed.php').write_text("<?php include 'library/checklogin.php';include_once '../common/includes/config_read.php';require_once 'library/hotspot_pages_pdo.php';$logDebugSQL='';$pdo=dalo_pdo_connect($configValues,$_SESSION['location_name']??'default');$pdo->beginTransaction();$pdo->exec(\"INSERT INTO hotspots(name,mac) VALUES ('Caller','02:00:00:00:00:FE')\");$rejected=false;try {dalo_hotspot_mutate($pdo,$configValues,function(){return true;});}catch(LogicException $e){$rejected=true;}$active=$pdo->inTransaction();$pdo->rollBack();echo json_encode(['rejected'=>$rejected,'active'=>$active]);")
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
                acl=','.join("(9001,'mng_hs_"+kind+"',1)" for kind in ('new','edit','del','list'))
                db('INSERT INTO operators_acl(operator_id,file,access) VALUES '+acl+"; INSERT INTO operators_acl(operator_id,file,access) SELECT 9002,file,0 FROM operators_acl WHERE operator_id=9001;",version)
                db("INSERT INTO operators_acl(operator_id,file,access) VALUES (9001,'acct_hotspot_accounting',1),(9001,'acct_hotspot_compare',1),(9002,'acct_hotspot_accounting',0),(9002,'acct_hotspot_compare',0)",version)
                names=['Alpha','Beta','Gamma','Delta','Epsilon','Zeta','Eta','Theta']
                for i,name in enumerate(names):
                    mac='02:00:00:00:00:'+str(10+i)
                    db("INSERT INTO hotspots(id,name,mac,owner,company,type,creationdate,creationby) VALUES ("+str(10+i)+",'"+name+"','"+mac+"','Owner"+str(i)+"','Company"+str(i)+"','Type"+str(i)+"','2000-01-01',NULL)",version)
                    for j in range(i+1):
                        db("INSERT INTO radacct(username,acctsessionid,acctuniqueid,nasipaddress,calledstationid,acctsessiontime,acctinputoctets,acctoutputoctets,acctstarttime) VALUES ('Client"+str(i)+'_'+str(j)+"','Session"+str(i)+'_'+str(j)+"','Unique"+str(i)+'_'+str(j)+"','198.51.100.1','"+mac+"',"+str(i*100+j+1)+','+str((i+1)*1000)+','+str((i+1)*2000)+",'2020-01-01')",version)
                db("INSERT INTO userbillinfo(id,hotspot_id,planName) VALUES (201,10,'FixturePlan'),(202,11,'FixturePlan')",version)
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
            def fields(name='Created',mac='02:00:00:00:00:80',**extra):
                return dict({'name':name,'macaddress':mac,'geocode':'10,20','ownername':'Owner','emailowner':'owner@example.invalid','managername':'Manager','emailmanager':'manager@example.invalid','address':'Address','company':'Company','phone1':'Phone1','phone2':'Phone2','hotspot_type':'TypeEdited','companywebsite':'https://example.invalid','companyemail':'company@example.invalid','companycontact':'Contact','companyphone':'CompanyPhone'},**extra)
            def create(name='Created',mac='02:00:00:00:00:80',version='candidate',session_id=None,**extra):
                return post(new,fields(name,mac,**extra),version,session_id)
            def update(name='Alpha',mac='02:00:00:00:00:81',version='candidate',session_id=None,**extra):
                return post(edit,fields(name,mac,**extra),version,session_id)
            columns='id,name,mac,geocode,owner,email_owner,manager,email_manager,address,company,phone1,phone2,type,companywebsite,companyemail,companycontact,companyphone'
            def state(version='candidate',table='hotspots',parity=False):
                audit=',creationdate,creationby,updatedate,updateby' if not parity else ',creationdate IS NOT NULL,creationby IS NOT NULL,updatedate IS NOT NULL,updateby IS NOT NULL'
                return db('SELECT '+columns+audit+' FROM '+table+' ORDER BY id',version)
            def accounting(version='candidate'):
                return db('SELECT radacctid,username,calledstationid,acctsessiontime,acctinputoctets,acctoutputoctets FROM radacct ORDER BY radacctid',version)
            def billing(version='candidate'):
                return db('SELECT id,hotspot_id,planName FROM userbillinfo ORDER BY id',version)
            acct_before=accounting()
            for version in ('base','candidate'):
                html=create(version=version);assert 'Successfully added' in html
                links=[u for u in Rows(html).links if u.startswith('mng-hs-edit.php?name=')]
                assert len(links)==1 and urllib.parse.parse_qs(urllib.parse.urlsplit(links[0]).query)=={'name':['Created']}
                assert any(form.get('name')=='Created' for form in Forms(req(links[0],version=version)[1]).forms)
                created_id=db("SELECT id FROM hotspots WHERE name='Created'",version)
                db("INSERT INTO userbillinfo(id,hotspot_id,planName) VALUES (203,"+created_id+",'FixturePlan')",version)
                assert 'Updated hotspot' in update(version=version)
                assert 'Deleted hotspot(s)' in post(delete,{'name[]':['Created']},version)
            assert state('base',parity=True)==state(parity=True),'ordinary full business and audit-presence parity'
            assert accounting('base')==accounting()==acct_before
            billing_before=billing();assert billing('base')==billing_before
            assert db('SELECT COUNT(*) FROM userbillinfo AS b LEFT JOIN hotspots AS hs ON hs.id=b.hotspot_id WHERE b.id=203 AND hs.id IS NULL')=='1','historical no-cascade delete contract'
            # Creation metadata stays intact, existing name/ID stay immutable on edit.
            assert db("SELECT id=10 AND name='Alpha' AND creationdate='2000-01-01' AND creationby IS NULL AND updateby IS NOT NULL FROM hotspots WHERE id=10")=='1'
            comparisons=0
            for order in ('id','name','owner','company','type'):
                for direction in ('asc','desc'):
                    for page_num in (1,2,3,4):
                        q={'orderBy':order,'orderType':direction,'page':str(page_num)}
                        assert Rows(req(listpage,version='base',query=q)[1]).rows==Rows(req(listpage,query=q)[1]).rows,(order,direction,page_num);comparisons+=1
            left=Forms(req(edit,version='base',query={'name':'Alpha'})[1]).forms
            right=Forms(req(edit,query={'name':'Alpha'})[1]).forms
            l=next(x for x in left if 'csrf_token' in x);rr=next(x for x in right if 'csrf_token' in x)
            assert l.get('hotspot_type')=='' and rr.get('hotspot_type')=='TypeEdited','characterized existing type renderer defect'
            for form in (l,rr):
                for key in ('csrf_token','hotspot_type','creationdate','creationby','updatedate','updateby'):form.pop(key,None)
            assert l==rr;comparisons+=1
            for hotspot in ('Alpha','Beta','Gamma','missing'):
                a=req(ENDPOINTS[0],version='base',query={'hotspot':hotspot});b=req(ENDPOINTS[0],query={'hotspot':hotspot})
                assert a[0]==b[0]==200 and json.loads(a[1])==json.loads(b[1]);comparisons+=1
            for category in ('avg_session_time','total_session_time','login_hits','unique_users','invalid'):
                a=req(ENDPOINTS[1],version='base',query={'category':category});b=req(ENDPOINTS[1],query={'category':category})
                assert a[0]==b[0]==200 and json.loads(a[1])==json.loads(b[1]),category;comparisons+=1
            for version in ('base','candidate'):
                assert 'Successfully added' in create('NullStats','192.0.2.199',version=version)
                db("INSERT INTO radacct(username,acctsessionid,acctuniqueid,nasipaddress,calledstationid,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES ('NullClient','NullSession','NullUnique','198.51.100.1','192.0.2.199',NULL,NULL,NULL)",version)
            a=req(ENDPOINTS[0],version='base',query={'hotspot':'NullStats'});b=req(ENDPOINTS[0],query={'hotspot':'NullStats'})
            assert json.loads(a[1])==json.loads(b[1])=={'upload':'(n/a)','download':'(n/a)','hits':1};comparisons+=1
            for category in ('avg_session_time','total_session_time','login_hits','unique_users'):
                a=req(ENDPOINTS[1],version='base',query={'category':category});b=req(ENDPOINTS[1],query={'category':category})
                assert a[0]==b[0]==200 and json.loads(a[1])==json.loads(b[1]);comparisons+=1
            for version in ('base','candidate'):
                assert 'Deleted hotspot(s)' in post(delete,{'name':'NullStats'},version)
                db("DELETE FROM radacct WHERE acctuniqueid='NullUnique'",version)
            assert accounting()==accounting('base')==acct_before and billing()==billing_before
            print('PASS native PEAR/PDO CRUD/full business state, audit presence, unchanged accounting/billing and',comparisons,'list/form/JSON/chart comparisons',flush=True)
            base_rows=state('base').splitlines()
            assert 'Deleted hotspot(s)' in post(delete,{'name[]':['Alpha','missing']},'base')
            assert state('base').splitlines()==[row for row in base_rows if row.split('\t')[1]!='Alpha']
            base_rows=state('base').splitlines()
            db("\nDELIMITER $$\nCREATE TRIGGER fail_legacy_delete BEFORE DELETE ON hotspots FOR EACH ROW BEGIN IF OLD.name='Gamma' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='legacy rollback characterization'; END IF; END$$\nDELIMITER ;\n",'base')
            post(delete,{'name[]':['Beta','Gamma']},'base')
            assert state('base').splitlines()==[row for row in base_rows if row.split('\t')[1]!='Beta']
            db('DROP TRIGGER fail_legacy_delete','base')
            assert 'Successfully added' in create('LegacyPrefix','001122334455junk',version='base')
            assert db("SELECT mac FROM hotspots WHERE name='LegacyPrefix'",'base')=='001122334455junk'
            print('PASS pinned legacy ignored-stale/partial-delete and malformed MAC-prefix/type renderer characterizations',flush=True)
            before=state()
            for value in ({'name[]':['bad'],'macaddress':'192.0.2.80'},{'name':'Bad','macaddress[]':['192.0.2.80']},{'name':'Bad','macaddress':'001122334455junk'},{'name':'Bad','macaddress':'192x0x2x80'}, {'name':'X'*201,'macaddress':'192.0.2.80'}, {'name':'Bad','macaddress':'192.0.2.80','ownername[]':['Bad']},{'name':'Bad\0Name','macaddress':'192.0.2.80'}):
                assert 'Unable to create' in post(new,value);assert state()==before
            assert 'already exist' in create('Alpha','02:00:00:00:00:90');assert state()==before
            assert 'already exist' in create('Other','02:00:00:00:00:11');assert state()==before
            assert 'already used' in update(mac='02:00:00:00:00:11');assert state()==before
            assert 'Unable to load or update' in update(name='alpha');assert state()==before
            assert 'Unable to load or update' in update(name='missing');assert state()==before
            # No-op business updates may refresh audit timestamps; creation metadata must not change.
            before_form=state(parity=True)
            form=next(x for x in Forms(req(edit,query={'name':'Alpha'})[1]).forms if 'csrf_token' in x)
            assert form['hotspot_type']=='TypeEdited'
            assert 'Updated hotspot' in req(edit,form)[1]
            assert state(parity=True)==before_form and billing()==billing_before
            before=state()
            for controls in ({'name[]':['Alpha','missing']},{'name[0]':'Alpha','name[1][]':['nested']},{'name[]':['alpha']},{}, {'name[]':[]}):
                assert 'Unable to delete' in post(delete,controls);assert state()==before
            assert 'Unable to delete' in req(delete,{'csrf_token':token(),'name[]':['Alpha']*1100})[1];assert state()==before
            preview=req(delete,query={'name[]':['Alpha','Beta']})[1];tok=next(form['csrf_token'] for form in Forms(preview).forms if 'csrf_token' in form)
            beta_id=db("SELECT id FROM hotspots WHERE name='Beta'")
            db("UPDATE hotspots SET name='BetaMoved' WHERE id="+beta_id);stale_before=state()
            assert 'Unable to delete' in req(delete,{'csrf_token':tok,'name[]':['Alpha','Beta']})[1];assert state()==stale_before
            db("UPDATE hotspots SET name='Beta' WHERE id="+beta_id);assert state()==before
            db("\nDELIMITER $$\nCREATE TRIGGER fail_delete BEFORE DELETE ON hotspots FOR EACH ROW BEGIN IF OLD.name='Gamma' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='private fixture marker'; END IF; END$$\nDELIMITER ;\n")
            html=post(delete,{'name[]':['Beta','Gamma']});assert 'Unable to delete' in html and 'private fixture marker' not in html;assert state()==before
            db('DROP TRIGGER fail_delete')
            for event,call,msg in (('INSERT',lambda:create(),'Unable to create'),('UPDATE',lambda:update(ownername='Changed'),'Unable to load or update')):
                db("CREATE TRIGGER fail_write BEFORE "+event+" ON hotspots FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='private fixture marker'")
                html=call();assert msg in html and 'private fixture marker' not in html;assert state()==before;db('DROP TRIGGER fail_write')
            assert json.loads(req('borrowed.php')[1])=={'rejected':True,'active':True};assert state()==before
            db('ALTER TABLE hotspots ENGINE=MyISAM')
            assert 'Unable to create' in create();assert 'Unable to load or update' in update();assert 'Unable to delete' in post(delete,{'name[]':['Beta']});assert state()==before
            db('ALTER TABLE hotspots ENGINE=InnoDB')
            # Duplicate/collation-equivalent records cannot be edited or deleted by an ambiguous name.
            db("INSERT INTO hotspots(name,mac) VALUES ('Alpha','02:00:00:00:00:FE')");ambiguous=state()
            assert 'Unable to load or update' in update();assert 'Unable to delete' in post(delete,{'name[]':['Alpha']});assert state()==ambiguous
            db("DELETE FROM hotspots WHERE mac='02:00:00:00:00:FE'");assert state()==before
            rows_before=state().splitlines()
            assert 'Deleted hotspot(s)' in post(delete,{'name[]':['Eta','Theta','Eta']})
            assert state().splitlines()==[row for row in rows_before if row.split('\t')[1] not in ('Eta','Theta')]
            assert accounting()==acct_before
            before=state()
            print('PASS invalid controls/MAC/IP/lengths, name-or-MAC duplicates, exact/ambiguous identity, stale preview and full INSERT/UPDATE/later DELETE rollback; other hotspots/accounting invariant',flush=True)
            db('ALTER TABLE hotspots CONVERT TO CHARACTER SET latin1')
            assert 'Unable to create' in create('Emoji😀');assert state()==before
            db('ALTER TABLE hotspots CONVERT TO CHARACTER SET utf8mb4')
            for i,name in enumerate(('HS &+é',"HS%'Quote",'0','é'*200)):
                html=create(name,'192.0.2.'+str(90+i));assert 'Successfully added' in html
                links=[u for u in Rows(html).links if u.startswith('mng-hs-edit.php?name=')]
                assert len(links)==1 and urllib.parse.parse_qs(urllib.parse.urlsplit(links[0]).query)=={'name':[name]}
                form=next(x for x in Forms(req(links[0])[1]).forms if 'csrf_token' in x)
                assert form['name']==name and form['name_presentation']==name and form['hotspot_type']=='TypeEdited'
                html=update(name=name,mac='192.0.2.'+str(90+i));assert 'Updated hotspot' in html
                row=Rows(req(listpage,query={'orderBy':'id','orderType':'desc'})[1]);assert row.rows[0][0]==name
                assert any(urllib.parse.parse_qs(urllib.parse.urlsplit(u).query).get('name')==[name] for u in row.links if u.startswith('mng-hs-edit.php?name='))
                assert any(o.get('value')==name for o in Rows(req(delete)[1]).options)
                # Actual accounting relation by the newly stored address, including percent name lookup.
                db("UPDATE radacct SET calledstationid='192.0.2."+str(90+i)+"' WHERE radacctid=1")
                status,text=req(ENDPOINTS[0],query={'hotspot':name});assert status==200 and json.loads(text)['hits']==1
                db("UPDATE radacct SET calledstationid='02:00:00:00:00:10' WHERE radacctid=1")
                edge=state();assert 'Unable to load or update' in update(name=name,mac='192.0.2.'+str(90+i),ownername='X'*201);assert state()==edge
                assert 'Deleted hotspot(s)' in post(delete,{'name[]':[name,name]})
            assert state()==before and accounting()==acct_before
            assert 'Successfully added' in create('ZeroControls','192.0.2.198',ownername='0',hotspot_type='0',phone1='0')
            assert db("SELECT owner='0' AND type='0' AND phone1='0' FROM hotspots WHERE name='ZeroControls'")=='1'
            assert 'Deleted hotspot(s)' in post(delete,{'name':'ZeroControls'})
            assert state()==before and billing()==billing_before
            print('PASS immutable creation metadata, full contact/type form, raw special/zero/200-character names across producers/JSON, capacity and charset readback rollback',flush=True)
            sid2=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',sid2)
            for scenario in ('same-name','same-mac'):
                tokens={s:token(session_id=s) for s in (sid,sid2)}
                def race_create(s):
                    name='Concurrent' if scenario=='same-name' or s==sid else 'ConcurrentOther'
                    mac='192.0.2.130' if scenario=='same-mac' or s==sid else '192.0.2.131'
                    return req(new,dict(fields(name,mac),csrf_token=tokens[s]),session_id=s)[1]
                with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:out=list(pool.map(race_create,(sid,sid2)))
                assert sum('Successfully added' in x for x in out)==1 and sum('already exist' in x for x in out)==1
                name=db("SELECT name FROM hotspots WHERE name IN ('Concurrent','ConcurrentOther')")
                assert 'Deleted hotspot(s)' in post(delete,{'name':name})
            assert 'Successfully added' in create('Concurrent','192.0.2.130')
            tokens={s:token(session_id=s) for s in (sid,sid2)}
            def race_edit(s):return req(edit,dict(fields('Concurrent','192.0.2.131' if s==sid else '192.0.2.132',ownername='First' if s==sid else 'Second'),csrf_token=tokens[s]),session_id=s)[1]
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:out=list(pool.map(race_edit,(sid,sid2)))
            assert all('Updated hotspot' in x for x in out)
            assert db("SELECT mac,owner FROM hotspots WHERE name='Concurrent'") in ('192.0.2.131\tFirst','192.0.2.132\tSecond')
            tokens={s:token(session_id=s) for s in (sid,sid2)}
            def race_delete(s):return req(delete,{'name[]':['Concurrent'],'csrf_token':tokens[s]},session_id=s)[1]
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:out=list(pool.map(race_delete,(sid,sid2)))
            assert sum('Deleted hotspot(s)' in x for x in out)==1 and sum('Unable to delete' in x for x in out)==1
            lockname='dalo_hotspot_'+hashlib.sha256(b'candidate:`hotspots`').hexdigest()[:64-len('dalo_hotspot_')]
            tok=token();holder=subprocess.Popen(['docker','exec',h.DB,'mariadb','-uroot','-N','-B','candidate','-e',"SELECT GET_LOCK('"+lockname+"',0); SELECT SLEEP(2); SELECT RELEASE_LOCK('"+lockname+"');"],stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
            try:
                wait_for(lambda:db("SELECT IS_USED_LOCK('"+lockname+"')")!='NULL','independent lock')
                with concurrent.futures.ThreadPoolExecutor(max_workers=1) as pool:
                    waiting=pool.submit(req,new,dict(fields('LockContention','192.0.2.145'),csrf_token=tok))
                    time.sleep(.1);assert not waiting.done();assert 'Successfully added' in waiting.result(timeout=15)[1]
                assert holder.wait(timeout=10)==0
            finally:
                if holder.poll() is None:holder.kill();holder.wait()
                holder.communicate()
            assert 'Deleted hotspot(s)' in post(delete,{'name':'LockContention'})
            before=state()
            for page,data in ((new,fields()),(edit,fields('Alpha','02:00:00:00:00:81')),(delete,{'name[]':['Beta']})):
                assert 'CSRF token error' in req(page,dict(data,**{'csrf_token[]':['bad']}))[1];assert state()==before
            denied=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',denied,'default','9002')
            for page in PAGES:
                assert not Rows(req(page,session_id=denied)[1]).rows
            for endpoint in ENDPOINTS:
                assert req(endpoint,query={'hotspot':'Beta'},session_id=denied)[0]==403
                assert req(endpoint,{},query={'hotspot':'Beta'})[0]==405
            assert req(ENDPOINTS[0],query={'hotspot[]':['Beta']})[0]==400
            assert req(ENDPOINTS[0],query={'hotspot':"' OR 1=1 --"})[0]==200
            assert req(ENDPOINTS[1],query={'category[]':['login_hits']})[0]==200
            anonymous=secrets.token_hex(16);expired=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',expired,'default','9001','7200')
            for page in PAGES+ENDPOINTS:
                assert req(page,session_id=anonymous,follow_redirects=False)[0]==302
                assert req(page,session_id=expired,follow_redirects=False)[0]==302
            assert state()==before and accounting()==acct_before
            assert req('library/hotspot_pages_pdo.php')[0]==404
            print('PASS native create/name-or-MAC races, edit/delete races, actual advisory lock, seeded session/expiry, CSRF and JSON/producer ACL contracts',flush=True)
            default_rows=Rows(req(listpage)[1]).rows
            for q in ({'orderBy[]':['name']},{'orderType[]':['desc']},{'orderBy':'id;invalid','orderType':'desc;invalid','page[]':['1']}):
                assert Rows(req(listpage,query=q)[1]).rows==default_rows
            configfile=f/'candidate/app/common/includes/daloradius.conf.php';saved_config=configfile.read_text()
            reader='reader_'+secrets.token_hex(6)
            db("CREATE USER '"+reader+"'@'%' IDENTIFIED BY ''; GRANT SELECT ON candidate.* TO '"+reader+"'@'%'")
            configfile.write_text(saved_config+"\n$configValues['CONFIG_DB_USER']='"+reader+"';\n")
            try:
                assert Rows(req(listpage)[1]).rows==default_rows
                assert any(form.get('name')=='Alpha' for form in Forms(req(edit,query={'name':'Alpha'})[1]).forms)
                assert any(o.get('value')=='Beta' for o in Rows(req(delete)[1]).options)
                for endpoint in ENDPOINTS:assert req(endpoint,query={'hotspot':'Beta'})[0]==200
                assert 'Unable to create' in create()
                assert 'Unable to load or update' in update(ownername='Changed')
                assert 'Unable to delete' in post(delete,{'name':'Beta'})
                assert state()==before and billing()==billing_before
            finally:
                configfile.write_text(saved_config);db("DROP USER '"+reader+"'@'%'")
            print('PASS malformed sorting, real SELECT-only page/aggregate reads and denied writes with full-state invariance',flush=True)
            other=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',other,'other')
            assert 'Successfully added' in create('OtherOnly','192.0.2.160',session_id=other)
            assert 'Updated hotspot' in update('OtherOnly','192.0.2.161',session_id=other)
            assert Rows(req(listpage,query={'orderBy':'id','orderType':'desc'},session_id=other)[1]).rows[0][0]=='OtherOnly'
            assert any(o.get('value')=='OtherOnly' for o in Rows(req(delete,session_id=other)[1]).options)
            db("INSERT INTO radacct(username,acctsessionid,acctuniqueid,nasipaddress,calledstationid,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES ('OtherClient','OtherSession','OtherUnique','198.51.100.1','192.0.2.161',321,3100,4700)",'candidate_other')
            assert json.loads(req(ENDPOINTS[0],query={'hotspot':'OtherOnly'},session_id=other)[1])['hits']==1
            assert json.loads(req(ENDPOINTS[0],query={'hotspot':'OtherOnly'})[1])['hits']=='(n/a)'
            assert any('OtherOnly' in label for label in json.loads(req(ENDPOINTS[1],session_id=other)[1])['data']['labels'])
            assert not any('OtherOnly' in label for label in json.loads(req(ENDPOINTS[1])[1])['data']['labels'])
            assert 'Deleted hotspot(s)' in post(delete,{'name':'OtherOnly'},session_id=other);assert state()==before
            configfile=f/'candidate/app/common/includes/daloradius.conf.php';conf=configfile.read_text()
            db('RENAME TABLE hotspots TO custom_hs,radacct TO custom_acct')
            conf+="\n$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']='custom_hs';\n$configValues['CONFIG_DB_TBL_RADACCT']='custom_acct';\n";configfile.write_text(conf)
            assert 'Successfully added' in create('Configured','192.0.2.170')
            assert 'Updated hotspot' in update('Configured','192.0.2.171')
            assert Rows(req(listpage,query={'orderBy':'id','orderType':'desc'})[1]).rows[0][0]=='Configured'
            assert any(o.get('value')=='Configured' for o in Rows(req(delete)[1]).options)
            for endpoint in ENDPOINTS:assert req(endpoint,query={'hotspot':'Beta'})[0]==200
            assert 'Deleted hotspot(s)' in post(delete,{'name':'Configured'})
            configfile.write_text(conf+"\n$configValues['CONFIG_DEBUG_SQL_ONPAGE']='yes';\n")
            html=create('DebugSentinel','192.0.2.180');debug=html.split('Debugging SQL Queries:',1)[1]
            assert 'DebugSentinel' not in debug and '192.0.2.180' not in debug and 'owner@example.invalid' not in debug
            configfile.write_text(conf)
            before=state(table='custom_hs')
            configfile.write_text(conf+"\n$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']='custom_hs;invalid';\n")
            assert 'Unable to create' in create();assert state(table='custom_hs')==before
            for endpoint in ENDPOINTS:
                status,text=req(endpoint,query={'hotspot':'Beta'});assert status==500 and 'SQLSTATE' not in text and 'error' in json.loads(text)
            configfile.write_text(conf)
            db('ALTER TABLE custom_hs RENAME COLUMN owner TO absent_owner')
            assert 'Unable to load hotspots' in req(listpage)[1]
            db('ALTER TABLE custom_hs RENAME COLUMN absent_owner TO owner');assert state(table='custom_hs')==before
            db('RENAME TABLE custom_acct TO absent_acct')
            for endpoint in ENDPOINTS:
                status,text=req(endpoint,query={'hotspot':'Beta'});assert status==500 and 'SQLSTATE' not in text and 'error' in json.loads(text)
            db('RENAME TABLE absent_acct TO custom_acct')
            db('RENAME TABLE custom_hs TO absent_hs')
            assert 'Unable to load hotspots' in req(listpage)[1] and 'Unable to load hotspot options' in req(delete)[1]
            assert 'Unable to create' in create()
            db('RENAME TABLE absent_hs TO custom_hs')
            # Both aggregate contracts handle an empty matching data set.
            db('CREATE TABLE empty_hs LIKE custom_hs')
            configfile.write_text(conf+"\n$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']='empty_hs';\n")
            assert 'Nothing to display' in req(listpage)[1]
            assert json.loads(req(ENDPOINTS[0],query={'hotspot':'Beta'})[1])=={'upload':'(n/a)','download':'(n/a)','hits':'(n/a)'}
            assert json.loads(req(ENDPOINTS[1])[1])['data']['labels']==[]
            configfile.write_text(conf)
            assert billing()==billing_before
            diagnostics=subprocess.run(['docker','logs',h.WEB],text=True,capture_output=True,check=True);logs=diagnostics.stdout+diagnostics.stderr
            assert not any('/fixtures/candidate/' in line and any(mark in line for mark in ('PHP Warning:','PHP Fatal error:','PHP Deprecated:')) for line in logs.splitlines()),'Candidate PHP diagnostics (details suppressed)'
            assert 'private fixture marker' not in logs and 'SQLSTATE' not in logs
            print('PASS selected/configured backends and accounting join, missing/invalid/late/empty reads, sanitized JSON/debug, no legacy-open/close and clean candidate diagnostics',flush=True)
        finally:
            run('docker','rm','-f',h.WEB,h.DB,check=False);run('docker','network','rm',h.NETWORK,check=False)
            subprocess.run(['sudo','-n','chown','-R',str(os.getuid())+':'+str(os.getgid()),str(f)],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
            assert not [n for n in run('docker','ps','-a','--format','{{.Names}}').splitlines() if n in (h.WEB,h.DB)]
            assert h.NETWORK not in run('docker','network','ls','--format','{{.Name}}').splitlines()
            print('CLEANUP R09 fixture containers/network removed',flush=True)
if __name__=='__main__':main()
