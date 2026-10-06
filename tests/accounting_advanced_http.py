#!/usr/bin/env python3
"""R12: pinned PEAR/PDO advanced reports and purge through native isolated HTTP/PHP/MariaDB.
No live database, browser or RADIUS server is used. Fixture sessions are synthetic.
"""
import concurrent.futures,csv,io,json,os,re,secrets,shutil,subprocess,tempfile,time,urllib.parse,urllib.request,urllib.error
from html.parser import HTMLParser
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap
import user_actions_http as h
ROOT=Path(__file__).resolve().parents[1]
BASE='64c46d949c1bf1e8e4f927c7bf06a0d96a7196b9'
SUFFIX=secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=['pdo-r12-'+kind+'-'+SUFFIX for kind in ('db','web','net')]
_harness_run=h.run
def run(*args,**kwargs):
    try:return _harness_run(*args,**kwargs)
    except RuntimeError:raise RuntimeError('Isolated fixture operation failed; details suppressed') from None
h.run=run
sql,wait_for=h.sql,h.wait_for
def wait_condition(predicate, description):
    def check():
        if not predicate():raise RuntimeError('Condition not ready')
        return True
    return wait_for(check,description)
PAGES=['acct-custom-query.php','acct-maintenance-delete.php','acct-plans-usage.php']
GENERIC=['acct-plans-usage.php']
class Rows(HTMLParser):
    def __init__(self,text):
        super().__init__(convert_charrefs=True);self.rows=[];self.row=None;self.cell=None;self.links=[];self.footer=[];self.tfoot=False;self.feed(text)
    def handle_starttag(self,tag,attrs):
        a=dict(attrs)
        if tag=='a' and 'href' in a:self.links.append(a['href'])
        if tag=='tfoot':self.tfoot=True
        if tag=='tr':self.row=[]
        if tag=='td' and self.row is not None:self.cell=[]
    def handle_data(self,text):
        if self.cell is not None:self.cell.append(text)
        if self.tfoot:self.footer.append(text)
    def handle_endtag(self,tag):
        if tag=='td' and self.cell is not None:
            self.row.append(' '.join(''.join(self.cell).split()));self.cell=None
        if tag=='tr':
            if self.row:self.rows.append(self.row)
            self.row=None
        if tag=='tfoot':self.tfoot=False
    def projection(self):return self.rows,' '.join(''.join(self.footer).split())

def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='pdo-r12-',dir=scratch) as tmp:
        f=Path(tmp);configs={}
        for version in ('base','candidate'):
            shutil.copytree(ROOT/'app',f/version/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if version=='base':
                restore_pear_bootstrap((f / version / 'app').parent, BASE)
                for page in PAGES:
                    (f/version/'app/operators'/page).write_bytes(subprocess.check_output(['git','show',BASE+':app/operators/'+page],cwd=ROOT))
            conf=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,value in {'CONFIG_DB_HOST':h.DB,'CONFIG_DB_USER':'root','CONFIG_DB_PASS':'','CONFIG_DB_NAME':version,'CONFIG_IFACE_TABLES_LISTING':'2','CONFIG_DB_TBL_RADACCT':'custom_acct','CONFIG_DB_TBL_DALOHOTSPOTS':'custom_hs','CONFIG_DB_TBL_RADCHECK':'custom_check','CONFIG_DB_TBL_DALOUSERBILLINFO':'custom_bill','CONFIG_DB_TBL_DALOBILLINGPLANS':'custom_plans','CONFIG_IFACE_DEBUG':'0'}.items():
                conf+='\n$configValues['+repr(key)+']='+repr(value)+';\n'
            conf+="\n$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>'"+h.DB+"','Username'=>'root','Password'=>'','Database'=>'"+version+"_other','Port'=>'3306');\n"
            configs[version]=conf;(f/version/'app/common/includes/daloradius.conf.php').write_text(conf)
            # Freeze SQL NOW() only in copied fixtures: R20 live summaries vary by second.
            old=f/version/'app/common/includes/db_open.php'
            if version=='base':
                old.write_text(old.read_text()+"\n$dbSocket->query('SET timestamp=1700000000');\n")
            pc=f/version/'app/common/includes/pdo_connection.php'
            pc.write_text(pc.read_text().replace("$pdo->exec(\"SET SESSION sql_mode = ''\");","$pdo->exec(\"SET SESSION sql_mode = ''\"); $pdo->exec('SET timestamp=1700000000');"))
        (f/'session.php').write_text("<?php session_name('daloradius_operator_sid');session_id($argv[1]);session_start();$_SESSION=['daloradius_logged_in'=>true,'operator_id'=>(int)($argv[3]??9001),'operator_user'=>bin2hex(random_bytes(12)),'location_name'=>($argv[2]??'default'),'time'=>time()];session_write_close();")
        (f/'candidate/app/operators/descriptor.php').write_text("<?php include 'library/checklogin.php';echo json_encode($_SESSION['reportExport']??null);")
        try:
            run('docker','network','create','--internal',h.NETWORK)
            run('docker','run','-d','--name',h.DB,'--network',h.NETWORK,'--tmpfs','/var/lib/mysql','-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            def db(q,version='candidate'):
                p=subprocess.run(['docker','exec','-i',h.DB,'mariadb','-uroot','-N','-B',version],input="SET SESSION sql_mode='';\n"+q,text=True,capture_output=True)
                if p.returncode:raise RuntimeError('Fixture SQL error codes '+repr(re.findall(r'ERROR (\d+)',p.stderr)))
                return p.stdout.rstrip('\n')
            for version in ('base','candidate','base_other','candidate_other'):
                sql('CREATE DATABASE '+version)
                for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql','mariadb-daloradius-dictionaries.sql'):db((ROOT/'contrib/db'/name).read_text(),version)
                db('RENAME TABLE radacct TO custom_acct, hotspots TO custom_hs, radcheck TO custom_check, userbillinfo TO custom_bill, billing_plans TO custom_plans',version)
                db('ALTER TABLE custom_acct MODIFY acctterminatecause VARCHAR(32) NULL',version)
                acl=','.join("(9001,'"+page[:-4].replace('-','_')+"',1),(9002,'"+page[:-4].replace('-','_')+"',0)" for page in PAGES+['acct-all.php'])
                db('INSERT INTO operators_acl(operator_id,file,access) VALUES '+acl,version)
                db("INSERT INTO custom_hs(id,name,mac) VALUES (10,'Alpha','02:00:00:00:00:10'),(11,'Beta','02:00:00:00:00:11'),(12,'Empty','02:00:00:00:00:12')",version)
                accounts=[('Alice','2020-01-01 00:00:00',None,'192.0.2.1','198.51.100.1','02:00:00:00:00:10','0',10),('Alice','2020-01-02 23:59:59','2020-01-03 00:00:00','192.0.2.2','198.51.100.2','02:00:00:00:00:11','User-Request',20),('Bob','2020-01-03 00:00:00',None,'192.0.2.3','198.51.100.1','02:00:00:00:00:10','Lost-Carrier',30),('Bob','2020-01-01 12:00:00','2020-01-02','192.0.2.4','198.51.100.3','unknown','',40),('NoMatch','2020-01-01',None,'192.0.2.5','198.51.100.4','unknown',None,0),('External','2020-01-04',None,'192.0.2.6','198.51.100.1','02:00:00:00:00:11','0',50)]
                if version.endswith('_other'):accounts=[('OtherOnly','2020-01-01',None,'192.0.2.7','198.51.100.5','02:00:00:00:00:10','0',60)]
                def literal(value):return 'NULL' if value is None else "'"+str(value).replace("'","''")+"'"
                for i,(user,start,stop,ip,nas,mac,cause,duration) in enumerate(accounts):
                    db('INSERT INTO custom_acct(radacctid,username,acctsessionid,acctuniqueid,acctstarttime,acctstoptime,framedipaddress,nasipaddress,calledstationid,acctterminatecause,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES ('+','.join(map(literal,[i+1,user,'S'+str(i),'U'+str(i),start,stop,ip,nas,mac,cause,duration,duration*100,duration*200]))+')',version)
                db("INSERT INTO custom_check(username,attribute,op,value) VALUES ('Alice','Max-All-Session',':=','100'),('Bob','Expiration',':=','2030-01-01'),('External','Max-All-Session',':=','1'),('OtherOnly','Max-All-Session',':=','100')",version)
            for version in ('base','candidate','base_other','candidate_other'):
                db("INSERT INTO custom_plans(id,planName,planTimeBank,planTimeType,planCost) VALUES (10,'Basic',1000,'Seconds','1.25'),(11,'Other',2000,'Seconds','2.50'),(12,'Unused',3000,'Seconds','3.75')",version)
                for user,plan in ([('OtherOnly','Other')] if version.endswith('_other') else [('Alice','Basic'),('Bob','Other'),('External','Basic')]):
                    db("INSERT INTO custom_bill(username,planName) VALUES ('"+user+"','"+plan+"')",version)
                # Distinct exact start/end midnight and whole-end-day rows.
                if not version.endswith('_other'):db("INSERT INTO custom_acct(radacctid,username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime,acctstoptime,acctsessiontime) VALUES (40,'Alice','B1','B1','198.51.100.1','2020-01-01 00:00:01',NULL,1),(41,'Alice','B2','B2','198.51.100.1','2020-01-03 23:59:59','2020-01-04',2),(42,'Alice','B3','B3','198.51.100.1','2020-01-04 00:00:00',NULL,3),(43,'Alice','Before','Before','198.51.100.1','2019-12-31 23:59:59',NULL,4)",version)
            run('docker','run','-d','--name',h.WEB,'--network',h.NETWORK,'-v',str(f)+':/fixtures','-w','/fixtures','-e','PHP_CLI_SERVER_WORKERS=4','--entrypoint','php','lirantal/daloradius','-d','opcache.enable_cli=0','-d','opcache.enable=0','-d','display_errors=0','-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',h.WEB)
            sid=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',sid)
            def req(page,query=None,version='candidate',session=None,data=None):
                url='http://'+ip+':8080/'+version+'/app/operators/'+page
                if query:url+='?'+urllib.parse.urlencode(query,doseq=True)
                encoded_data=None if data is None else {(key+'[]' if isinstance(value,list) and not key.endswith('[]') else key):value for key,value in data.items()}
                request=urllib.request.Request(url,data=None if encoded_data is None else urllib.parse.urlencode(encoded_data,doseq=True).encode(),headers={'Cookie':'daloradius_operator_sid='+(session or sid)})
                class NoRedirect(urllib.request.HTTPRedirectHandler):
                    def redirect_request(self,*args,**kwargs):return None
                opener=urllib.request.build_opener(NoRedirect)
                try:
                    with opener.open(request,timeout=30) as response:return response.status,response.read().decode()
                except urllib.error.HTTPError as error:return error.code,error.read().decode()
            wait_for(lambda:req('acct-all.php'),'PHP')
            print('RUNTIME PHP '+run('docker','exec',h.WEB,'php','-r','echo PHP_VERSION;')+'; MariaDB '+db('SELECT VERSION()'),flush=True)
            for page in PAGES+['library/accounting_pages_pdo.php']:
                run('docker','exec',h.WEB,'php','-l','/fixtures/candidate/app/operators/'+page)
            export='include/management/fileExport.php'
            default={}
            from acct_maintenance_http import Forms
            custom,delete,plans=PAGES
            comparisons=0
            dates={'startdate':'2020-01-01','enddate':'2020-01-03'}
            columns=['radacctid','username','framedipaddress','nasipaddress','acctstarttime','acctstoptime','acctsessiontime','acctinputoctets','acctoutputoctets','acctterminatecause']
            def snapshot(version='candidate'):
                return db('SELECT radacctid,username,acctsessionid,acctuniqueid,nasipaddress,framedipaddress,acctstarttime,acctstoptime,acctsessiontime,acctinputoctets,acctoutputoctets,acctterminatecause,calledstationid FROM custom_acct ORDER BY radacctid',version)
            def compare(page,q):
                nonlocal comparisons
                a,b=req(page,q,'base'),req(page,q)
                assert a[0]==b[0]==200,(page,'HTTP parity')
                pa,pb=Rows(a[1]).projection(),Rows(b[1]).projection()
                if pa!=pb:
                    # Complete data sets and ordered keys: unspecified ties may reorder.
                    selected=q.get('sqlfields[]',columns)
                    indices={key:i for i,key in enumerate(selected)} if page==custom else {'username':0,'planname':1,'sessiontime':2,'plantimebank':3}
                    key=q.get('orderBy','radacctid' if page==custom else 'username')
                    assert pa[1]==pb[1] and sorted(pa[0])==sorted(pb[0]) and [r[indices[key]] for r in pa[0]]==[r[indices[key]] for r in pb[0]],(page,q,'display parity')
                if page==plans and 'fileExport.php' in b[1]:
                    ea=req(export,{'reportFormat':'csv','reportType':'reportsPlansUsage'},'base')
                    eb=req(export,{'reportFormat':'csv','reportType':'reportsPlansUsage'})
                    assert ea==eb and ea[0]==200,'Plan full CSV parity'
                comparisons+=1
                return a,b
            before=snapshot()
            for version in ('base','candidate'):
                (f/version/'app/common/includes/daloradius.conf.php').write_text(configs[version]+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='100';\n")
            for key in columns:
                for direction in ('asc','desc'):compare(custom,dict(dates,**{'sqlfields[]':columns,'orderBy':key,'orderType':direction}))
            for key in ('username','planname','sessiontime','plantimebank'):
                for direction in ('asc','desc'):compare(plans,dict(dates,orderBy=key,orderType=direction))
            for field,op,value in [('username','equals','Alice'),('username','contains','li'),('nasipaddress','equals','198.51.100.1'),('acctsessiontime','equals','20'),('acctterminatecause','equals','User-Request')]:
                compare(custom,dict(dates,**{'sqlfields[]':columns,'where_field':field,'where_operator':op,'where_value':value}))
            for q in (dict(dates,username='Alice'),dict(dates,planname='Basic'),dict(dates,username='Bob',planname='Other'),dict(dates,username='Absent'),dict(dates,planname='Unused'),{'startdate':'2020-01-02','enddate':'2020-01-02'}):compare(plans,q)
            # Narrow projection and order by a non-selected but actual column.
            compare(custom,dict(dates,**{'sqlfields[]':['username','acctsessiontime'],'orderBy':'radacctid'}))
            for version in ('base','candidate'):(f/version/'app/common/includes/daloradius.conf.php').write_text(configs[version])
            for page in (custom,plans):
                for number in ('1','2','3','999','0','abc'):
                    q=dict(dates,page=number)
                    if page==custom:q['sqlfields[]']=columns
                    compare(page,q)
            assert snapshot()==before
            # Exact historical custom bounds exclude start midnight and end midnight.
            q=dict(dates,**{'sqlfields[]':['radacctid'],'orderBy':'radacctid'})
            for version in ('base','candidate'):
                (f/version/'app/common/includes/daloradius.conf.php').write_text(configs[version]+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='100';\n")
            body=req(custom,q)[1];assert {r[0] for r in Rows(body).rows}=={'2','4','40'}
            for version in ('base','candidate'):(f/version/'app/common/includes/daloradius.conf.php').write_text(configs[version])
            # Plan CSV covers the inclusive end day, not just the visible page.
            req(plans,dates);status,text=req(export,{'reportFormat':'csv','reportType':'reportsPlansUsage'})
            assert status==200 and len(list(csv.reader(io.StringIO(text))))==3
            other=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',other,'other')
            for page in (custom,plans):
                body=req(page,dict(dates,startdate='2019-12-31'),session=other)[1];assert 'OtherOnly' in ' '.join(map(str,Rows(body).rows)) and 'Alice' not in ' '.join(map(str,Rows(body).rows))
            def post(fields,version='candidate',session=None):
                form=req(delete,version=version,session=session)[1]
                token=next(form['csrf_token'] for form in Forms(form).forms if 'csrf_token' in form)
                return req(delete,version=version,session=session,data=dict(fields,csrf_token=token))
            # The same complete purge is applied to equivalent disposable schemas.
            fields=dict(dates,username='Alice')
            for version in ('base','candidate'):
                status,text=post(fields,version);assert status==200 and 'Deleted accounting records for user' in text
            assert snapshot('base')==snapshot(),'Purge complete state parity'
            ids={row.split('\t')[0] for row in snapshot().splitlines()}
            assert {'1','42','43'}<=ids and not {'2','40','41'}&ids,'Purge boundary/retention policy'
            before=snapshot()
            for fields in ({'username':'Absent',**dates},{'username':'Alice','startdate':'2020-01-03','enddate':'2020-01-01'},{'username':'Alice','startdate':'2020-02-30','enddate':'2020-03-01'},{'username':['Alice'],**dates},{'username':'Alice','startdate':['2020-01-01'],'enddate':'2020-01-03'}):
                status,text=post(fields)
                assert status==200 and ('invalid required' in text or 'Unable to delete' in text)
                assert snapshot()==before
            for factor in (None,'bad',['invalid']):
                req(delete);data=dict(dates,username='Alice')
                if factor is not None:data['csrf_token']=factor
                status,text=req(delete,data=data);assert status==200 and 'CSRF token error' in text and snapshot()==before
            # Native trigger fails on a later row after an earlier DELETE was attempted.
            db("INSERT INTO custom_acct(radacctid,username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime) VALUES (50,'Rollback','R1','R1','198.51.100.1','2020-01-02'),(51,'Rollback','R2','R2','198.51.100.1','2020-01-02')")
            before=snapshot()
            db('CREATE TABLE delete_visits(sequence_id INT AUTO_INCREMENT PRIMARY KEY,row_id BIGINT) ENGINE=MEMORY')
            db("DELIMITER //\nCREATE TRIGGER refuse_later BEFORE DELETE ON custom_acct FOR EACH ROW BEGIN INSERT INTO delete_visits(row_id) VALUES (OLD.radacctid); IF OLD.radacctid=51 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='private fixture marker'; END IF; END//\nDELIMITER ;")
            status,text=post(dict(dates,username='Rollback'));assert status==200 and 'Unable to delete accounting records' in text
            assert snapshot()==before and 'private fixture marker' not in text and 'SQLSTATE' not in text
            assert db('SELECT row_id FROM delete_visits ORDER BY sequence_id').splitlines()==['50','51'],'Failure did not follow the earlier row'
            db('DROP TRIGGER refuse_later; DROP TABLE delete_visits')
            db('ALTER TABLE custom_acct ENGINE=MyISAM');before=snapshot()
            assert 'Unable to delete accounting records' in post(dict(dates,username='Rollback'))[1] and snapshot()==before
            db('ALTER TABLE custom_acct ENGINE=InnoDB')
            # Two actual HTTP workers wait on the same native accounting lock.
            db("INSERT INTO custom_acct(radacctid,username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime) VALUES (60,'Concurrent','C1','C1','198.51.100.1','2020-01-02'),(61,'Concurrent','C2','C2','198.51.100.1','2020-01-02')")
            before=snapshot();sessions=[];payloads=[]
            for _ in range(2):
                csid=secrets.token_hex(16);sessions.append(csid);run('docker','exec',h.WEB,'php','/fixtures/session.php',csid)
                token=next(form['csrf_token'] for form in Forms(req(delete,session=csid)[1]).forms if 'csrf_token' in form)
                payloads.append(dict(dates,username='Concurrent',csrf_token=token))
            holderfile=f/'hold.php'
            holderfile.write_text("<?php include '/fixtures/candidate/app/common/includes/config_read.php';$_SERVER['PHP_SELF']='hold';require_once '/fixtures/candidate/app/operators/library/accounting_advanced_pdo.php';$logDebugSQL='';$pdo=dalo_pdo_connect($configValues,'default');$pdo->beginTransaction();$pdo->query('SELECT radacctid FROM custom_acct WHERE radacctid=60 FOR UPDATE')->fetchAll();file_put_contents('/fixtures/locked','ready');$deadline=microtime(true)+30;while(!file_exists('/fixtures/release')&&microtime(true)<$deadline){usleep(20000);}$pdo->rollBack();")
            holder=subprocess.Popen(['docker','exec',h.WEB,'php','/fixtures/hold.php'],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
            try:
                wait_condition(lambda:(f/'locked').exists(),'native lock holder')
                with concurrent.futures.ThreadPoolExecutor(max_workers=2) as workers:
                    futures=[workers.submit(req,delete,None,'candidate',session,payload) for session,payload in zip(sessions,payloads)]
                    try:
                        wait_condition(lambda:int(db('SELECT COUNT(*) FROM INFORMATION_SCHEMA.INNODB_LOCK_WAITS'))>=2,'both real purge transactions waiting')
                        assert snapshot()==before and not any(future.done() for future in futures)
                    finally:(f/'release').write_text('release')
                    responses=[future.result(timeout=35) for future in futures]
                assert all(status==200 for status,_ in responses)
                assert sum('Deleted accounting records for user' in text for _,text in responses)==1
                assert sum('Unable to delete accounting records' in text for _,text in responses)==1
                assert db("SELECT COUNT(*) FROM custom_acct WHERE username='Concurrent'")=='0'
                assert snapshot()=='\n'.join(line for line in before.splitlines() if line.split('\t')[0] not in ('60','61'))
            finally:
                (f/'release').write_text('release');holder.wait(timeout=35)
            # Named-location purge cannot mutate the default backend.
            before=snapshot();status,text=post(dict(dates,username='OtherOnly'),session=other)
            assert status==200 and 'Deleted accounting records' in text and snapshot()==before
            # Borrowed transaction is rejected without committing or undoing the caller.
            (f/'candidate/app/operators/borrowed.php').write_text("<?php include 'library/checklogin.php';include_once '../common/includes/config_read.php';require_once 'library/accounting_advanced_pdo.php';$logDebugSQL='';$pdo=dalo_pdo_connect($configValues,'default');$pdo->beginTransaction();$ok=false;try {dalo_advanced_purge($pdo,$configValues,'Rollback','2020-01-01','2020-01-03');}catch(LogicException $e){$ok=$pdo->inTransaction();}$pdo->rollBack();echo json_encode($ok);")
            assert json.loads(req('borrowed.php')[1]) is True and snapshot()==before
            # Stale identity between options and the actual write connection is refused.
            (f/'candidate/app/operators/stale.php').write_text("<?php include 'library/checklogin.php';include_once '../common/includes/config_read.php';require_once 'library/accounting_advanced_pdo.php';$logDebugSQL='';$pdo=dalo_pdo_connect($configValues,'default');$ok=false;try {dalo_advanced_purge($pdo,$configValues,'Absent','2020-01-01','2020-01-03');}catch(InvalidArgumentException $e){$ok=!$pdo->inTransaction();}echo json_encode($ok);")
            assert json.loads(req('stale.php')[1]) is True and snapshot()==before
            # Full validation, schema-derived allowlist and SQL injection controls.
            for page in (custom,plans):
                for key in ('username','startdate','enddate','orderBy','orderType','page'):
                    assert req(page,{key+'[]':'bad'})[0]==400
            for q in ({'sqlfields[]':['username','fake']},{'sqlfields[]':['username','username); DELETE FROM custom_acct;--']},{'sqlfields[0][]':'username'},{'sqlfields':'username'}):
                assert req(custom,dict(dates,**q))[0]==400 and snapshot()==before
            for q in ({'where_field':'username OR 1=1','where_operator':'equals','where_value':'Alice'},{'where_field':'username','where_operator':'equals','where_value':"' OR 1=1 --"}):
                text=req(custom,dict(dates,**q))[1];assert not Rows(text).rows and snapshot()==before
            # Exact special identities, literal zero, safe HTML labels and raw links.
            for user in ('0',"O'Reilly%雪",'Amp&Plus+'):
                safe=user.replace("'","''")
                db("INSERT INTO custom_acct(username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime) VALUES ('"+safe+"','Special','"+secrets.token_hex(6)+"','198.51.100.1','2020-01-02'); INSERT INTO custom_bill(username,planName) VALUES ('"+safe+"','Basic')")
                q=dict(dates,**{'sqlfields[]':['username','radacctid'],'where_field':'username','where_operator':'equals','where_value':user})
                body=req(custom,q)[1];assert len(Rows(body).rows)==1 and user in Rows(body).rows[0][0]
                assert any(urllib.parse.parse_qs(urllib.parse.urlsplit(link).query).get('where_value')==[user] for link in Rows(body).links if link.startswith('?page=') or link.startswith('?orderBy='))
                req(plans,dict(dates,username=user));status,text=req(export,{'reportFormat':'csv','reportType':'reportsPlansUsage'})
                assert status==200 and list(csv.reader(io.StringIO(text)))[1][0]==user
                assert 'Deleted accounting records' in post(dict(dates,username=user))[1]
                assert db("SELECT COUNT(*) FROM custom_acct WHERE BINARY username='"+safe+"'")=='0'
            # Native late read failure after a successful COUNT: never expose a stale CSV.
            provider=f/'candidate/app/operators/library/accounting_advanced_pdo.php';original_provider=provider.read_text()
            marker='function dalo_advanced_rows(PDO $pdo, $sql, $bindings, $order, $direction, $allowed, $offset, $limit, $associative = false) {'
            assert marker in original_provider
            provider.write_text(original_provider.replace(marker,marker+"\nif(file_exists('/fixtures/late-schema')) { unlink('/fixtures/late-schema');$pdo->exec('ALTER TABLE custom_acct CHANGE acctinputoctets missing_input BIGINT');}\n"))
            for page in (custom,plans):
                req('acct-all.php');(f/'late-schema').write_text('enabled')
                status,text=req(page,dates)
                assert status==200 and 'Unable to read accounting records' in text and not Rows(text).rows and 'fileExport.php' not in text and 'SQLSTATE' not in text
                assert json.loads(req('descriptor.php')[1]) is None
                db('ALTER TABLE custom_acct CHANGE missing_input acctinputoctets BIGINT')
            provider.write_text(original_provider)
            # Actual additional schema column is available, unlike injected names.
            db('ALTER TABLE custom_acct ADD custom_metric INT DEFAULT 7')
            status,text=req(custom,dict(dates,**{'sqlfields[]':['radacctid','custom_metric']}));assert status==200 and Rows(text).rows[0][1]=='7'
            db('ALTER TABLE custom_acct ADD MixedMetric INT DEFAULT 8')
            status,text=req(custom,dict(dates,**{'sqlfields[]':['radacctid','MixedMetric']}));assert status==200 and Rows(text).rows[0][1]=='8'
            # Zero plan time is valid stored data: its unused ratio must not crash HTML.
            db("UPDATE custom_plans SET planTimeBank='0' WHERE planName='Basic'")
            status,text=req(plans,dates);assert status==200 and '0 seconds' in text and 'fileExport.php' in text
            # Characterize precise baseline render defects, separately from parity.
            def log_text():
                log=subprocess.run(['docker','logs',h.WEB],text=True,capture_output=True,check=True)
                return log.stdout+log.stderr
            prior=log_text();db("UPDATE custom_plans SET planTimeBank='0' WHERE planName='Basic'",'base')
            req(plans,dates,'base')
            delta=log_text()[len(prior):]
            assert 'DivisionByZeroError' in delta and '/fixtures/base/app/operators/acct-plans-usage.php' in delta
            for version in ('base','candidate'):
                db("UPDATE custom_plans SET planTimeBank='1000' WHERE planName='Basic';INSERT INTO custom_bill(username,planName) VALUES ('NullTotals','Basic');INSERT INTO custom_acct(username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES ('NullTotals','NULL','NULL','198.51.100.1','2020-01-02',30,NULL,NULL)",version)
            prior=log_text();req(plans,dict(dates,username='NullTotals'),'base');delta=log_text()[len(prior):]
            assert 'Unsupported operand types: string + string' in delta and '/fixtures/base/app/operators/acct-plans-usage.php' in delta
            status,text=req(plans,dict(dates,username='NullTotals'))
            assert status==200 and 'NullTotals' in ' '.join(map(str,Rows(text).rows)) and 'fileExport.php' in text and 'Unable to read accounting' not in text
            # Replayed exports are cleared by custom/purge and empty plans.
            for page,q in ((custom,dates),(delete,None),(plans,dict(dates,username='Absent'))):
                req('acct-all.php');req(page,q);assert json.loads(req('descriptor.php')[1]) is None
            denied=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',denied,'default','9002')
            for page in PAGES:assert req(page,session=denied)[0] in (302,403)
            # SELECT-only roles permit reports/form but cannot purge.
            reader='reader_'+secrets.token_hex(6);password=secrets.token_hex(24)
            sql("CREATE USER '"+reader+"'@'%' IDENTIFIED BY '"+password+"'; GRANT SELECT ON candidate.* TO '"+reader+"'@'%'")
            cf=f/'candidate/app/common/includes/daloradius.conf.php'
            cf.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_USER']='"+reader+"';\n$configValues['CONFIG_DB_PASS']='"+password+"';\n")
            for page in PAGES:
                status,text=req(page,dates if page!=delete else None);assert status==200 and 'Unable to read accounting' not in text
            before=snapshot();assert 'Unable to delete accounting' in post(dict(dates,username='Rollback'))[1] and snapshot()==before
            cf.write_text(configs['candidate']);sql("DROP USER '"+reader+"'@'%'");reader=password=None
            # No whole-page PEAR for custom/purge or an unfiltered plan report.
            for rel in ('db_open.php','db_close.php'):(f/'candidate/app/common/includes'/rel).write_text("<?php throw new RuntimeException('Legacy database tripwire');")
            for page in PAGES:
                status,text=req(page,dates if page!=delete else None);assert status==200 and ('Unable to read' not in text)
            # Missing schemas fail without driver SQL or credential-bearing errors.
            db('RENAME TABLE custom_acct TO absent_acct')
            for page in PAGES:
                status,text=req(page,dates if page!=delete else None);assert status==200 and 'Unable to read accounting records' in text and 'SQLSTATE' not in text
            db('RENAME TABLE absent_acct TO custom_acct')
            logs=subprocess.run(['docker','logs',h.WEB],text=True,capture_output=True,check=True);log=logs.stdout+logs.stderr
            assert 'private fixture marker' not in log and 'SQLSTATE' not in log
            assert not any('/fixtures/candidate/' in line and any(mark in line for mark in ('PHP Fatal error:','PHP Warning:','PHP Deprecated:')) for line in log.splitlines()),'Candidate PHP diagnostics (details suppressed)'
            print('PASS '+str(comparisons)+' pinned PEAR/PDO display comparisons, full plan CSV, strict/inclusive date policies, schema columns, sorting/pagination and named routing',flush=True)
            print('PASS native purge state parity, complete later-row rollback, two native blocked concurrent purges, MyISAM refusal, stale/borrowed ownership, SQL injection/ACL/CSRF, SELECT-only roles, exact special identities, sanitized errors and legacy tripwires',flush=True)
        finally:
            run('docker','rm','-f',h.WEB,h.DB,check=False);run('docker','network','rm',h.NETWORK,check=False)
            subprocess.run(['sudo','-n','chown','-R',str(os.getuid())+':'+str(os.getgid()),str(f)],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
            assert not [n for n in run('docker','ps','-a','--format','{{.Names}}').splitlines() if n in (h.WEB,h.DB)]
            assert h.NETWORK not in run('docker','network','ls','--format','{{.Name}}').splitlines()
            print('CLEANUP R12 fixtures removed',flush=True)
if __name__=='__main__':main()
