#!/usr/bin/env python3
"""R13: pinned PEAR/PDO operator report reads through native isolated HTTP/PHP/MariaDB.
No live database, browser or RADIUS server is used. Fixture sessions are synthetic.
"""
import concurrent.futures,csv,io,json,os,re,secrets,shutil,subprocess,tempfile,time,urllib.parse,urllib.request,urllib.error
from html.parser import HTMLParser
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap
import user_actions_http as h
ROOT=Path(__file__).resolve().parents[1]
BASE='9ca89687a94f4fd2a8392585d23f78be2495b915'
SUFFIX=secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=['pdo-r13-'+kind+'-'+SUFFIX for kind in ('db','web','net')]
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
PAGES=['mng-list-all.php', 'mng-search.php', 'mng-batch-list.php', 'rep-batch-details.php', 'rep-batch-list.php', 'rep-hb-dashboard.php', 'rep-history.php', 'rep-lastconnect.php', 'rep-newusers.php', 'rep-online.php', 'rep-topusers.php']

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
    with tempfile.TemporaryDirectory(prefix='pdo-r13-',dir=scratch) as tmp:
        f=Path(tmp);configs={}
        for version in ('base','candidate'):
            shutil.copytree(ROOT/'app',f/version/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if version=='base':
                restore_pear_bootstrap((f / version / 'app').parent, BASE)
                for page in PAGES:
                    (f/version/'app/operators'/page).write_bytes(subprocess.check_output(['git','show',BASE+':app/operators/'+page],cwd=ROOT))
            conf=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,value in {'CONFIG_DB_HOST':h.DB,'CONFIG_DB_USER':'root','CONFIG_DB_PASS':'','CONFIG_DB_NAME':version,'CONFIG_IFACE_TABLES_LISTING':'2','CONFIG_DB_TBL_RADACCT':'custom_acct','CONFIG_DB_TBL_DALOHOTSPOTS':'custom_hs','CONFIG_DB_TBL_RADCHECK':'custom_check','CONFIG_DB_TBL_DALOUSERBILLINFO':'custom_bill','CONFIG_DB_TBL_DALOBILLINGPLANS':'custom_plans','CONFIG_DB_TBL_DALOUSERINFO':'custom_info','CONFIG_DB_TBL_RADREPLY':'custom_reply','CONFIG_DB_TBL_RADUSERGROUP':'custom_groups','CONFIG_DB_TBL_RADPOSTAUTH':'custom_postauth','CONFIG_DB_TBL_RADNAS':'custom_nas','CONFIG_DB_TBL_DALOBATCHHISTORY':'custom_batches','CONFIG_DB_TBL_DALONODE':'custom_node','CONFIG_DB_TBL_DALOPROXYS':'custom_proxy','CONFIG_DB_TBL_DALOREALMS':'custom_realms','CONFIG_DB_TBL_DALOBILLINGINVOICE':'custom_invoice','CONFIG_DB_TBL_DALOPAYMENTS':'custom_payments','CONFIG_IFACE_PASSWORD_HIDDEN':'yes','CONFIG_IFACE_DEBUG':'0'}.items():
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
                db('RENAME TABLE radacct TO custom_acct, hotspots TO custom_hs, radcheck TO custom_check, userbillinfo TO custom_bill, billing_plans TO custom_plans, userinfo TO custom_info, radreply TO custom_reply, radusergroup TO custom_groups, radpostauth TO custom_postauth, nas TO custom_nas, batch_history TO custom_batches, node TO custom_node, proxys TO custom_proxy, realms TO custom_realms, invoice TO custom_invoice, payment TO custom_payments',version)
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
            for version in ('base','candidate','base_other','candidate_other'):
                db('UPDATE custom_acct SET acctinputoctets=COALESCE(acctinputoctets,0),acctoutputoctets=COALESCE(acctoutputoctets,0)',version)
                db("UPDATE operators SET creationdate='2020-01-01',creationby='fixture',updatedate='2020-01-02',updateby='fixture'",version)
                db("INSERT INTO custom_nas(id,nasname,shortname,type,secret) VALUES (10,'198.51.100.1','NAS Alpha','other',''),(11,'198.51.100.2','NAS Beta','other','')",version)
                db("INSERT INTO custom_batches(id,batch_name,batch_description,batch_status,hotspot_id,creationdate,creationby,updatedate,updateby) VALUES (20,'First','First batch','1',10,'2020-01-01','fixture','2020-01-02','fixture'),(21,'Second','Second batch','1',11,'2020-01-02','fixture','2020-01-03','fixture'),(22,'Empty','Empty batch','0',12,'2020-03-01','fixture',NULL,NULL)",version)
                db("UPDATE custom_bill SET batch_id=IF(username='Bob',21,20)",version)
                users=[('Alice','Anna','One','2020-01-01'),('Bob','Ben','Two','2020-02-29'),('NoMatch',None,None,'2020-01-31 23:59:59'),('External','Eve','Outside','2020-04-01'),('NoAuth','No','Auth','2020-03-01')]
                if version.endswith('_other'):users=[('OtherOnly','Named','Backend','2020-01-01')]
                for user,first,last,date in users:
                    db('INSERT INTO custom_info(username,firstname,lastname,email,homephone,workphone,mobilephone,creationdate,creationby,updatedate,updateby) VALUES ('+','.join(map(literal,[user,first,last,'','5550100','5550200','5550300',date,'fixture',None,None]))+')',version)
                    if user!='NoAuth':db("INSERT INTO custom_check(username,attribute,op,value) VALUES ('"+user+"','Auth-Type',':=','Accept')",version)
                    db("INSERT INTO custom_groups(username,groupname,priority) VALUES ('"+user+"','Users',1)",version)
                    db("INSERT INTO custom_reply(username,attribute,op,value) VALUES ('"+user+"','Framed-IP-Address',':=','192.0.2.99')",version)
                    for i,(reply,authdate) in enumerate([('Access-Accept','2020-01-01'),('Access-Reject','2020-03-31 23:59:59'),('Access-Challenge','2020-04-01')]):
                        db("INSERT INTO custom_postauth(username,pass,reply,authdate) VALUES ('"+user+"','','"+reply+"','"+authdate+"')",version)
                db("INSERT INTO custom_groups(username,groupname,priority) VALUES ('Bob','daloRADIUS-Disabled-Users',0);INSERT INTO custom_proxy(proxyname,creationdate,creationby,updatedate,updateby) VALUES ('P','2020-01-01','fixture','2020-01-02','fixture');INSERT INTO custom_realms(realmname,creationdate,creationby,updatedate,updateby) VALUES ('R','2020-01-01','fixture',NULL,NULL)",version)
                db("INSERT INTO custom_invoice(id,creationdate,creationby,updatedate,updateby) VALUES (40,'2020-01-02','fixture',NULL,NULL);INSERT INTO custom_payments(id,creationdate,creationby,updatedate,updateby) VALUES (50,'2020-01-03','fixture','2020-01-04','fixture')",version)
                db("INSERT INTO custom_node(id,mac,wan_iface,wan_ip,wan_mac,wan_gateway,wifi_iface,wifi_ip,wifi_mac,wifi_ssid,wifi_key,wifi_channel,lan_iface,lan_mac,lan_ip,uptime,memfree,cpu,wan_bup,wan_bdown,firmware,firmware_revision,time) VALUES (10,'02:00:00:00:00:10','eth0','198.51.100.1','','','wlan0','','','Fixture','',6,'eth1','','',3600,1024,'12.5%',100,200,'Fixture','1','2020-01-01'),(11,'unmatched',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL)",version)
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
            comparisons=0
            dates={'startdate':'2020-01-01','enddate':'2020-03-31'}
            defaults={page:dict(dates) for page in PAGES}
            defaults['rep-batch-details.php']={'batch_name':'First'}
            types={'mng-list-all.php':'usernameListGeneric','mng-search.php':'usernameListGeneric','mng-batch-list.php':'reportsBatchList','rep-batch-list.php':'reportsBatchList','rep-batch-details.php':'reportsBatchActiveUsers','rep-lastconnect.php':'reportsLastConnectionAttempts','rep-online.php':'reportsOnlineUsers','rep-topusers.php':'TopUsers'}
            sorts={'mng-list-all.php':['id','fullname','username','framedipaddress','lastlogin'],'mng-search.php':['id','fullname','username','framedipaddress','lastlogin'],'mng-batch-list.php':['bid','batch_name','creationdate','creationby'],'rep-batch-list.php':['id','creationdate','creationby'],'rep-batch-details.php':['username','status','acctstarttime'],'rep-lastconnect.php':['username','fullname','reply','authdate'],'rep-online.php':['username','framedipaddress','calledstationid','nasshortname','hotspot','acctstarttime','acctsessiontime'],'rep-topusers.php':['username','framedipaddress','nasshortname','acctstarttime','acctstoptime','Time','Upload','Download','acctterminatecause'],'rep-newusers.php':['month','users'],'rep-history.php':['section','item','creationdate','creationby','updatedate','updateby'],'rep-hb-dashboard.php':['id']}
            sort_cells={'mng-list-all.php': {'id': 0, 'fullname': 1, 'username': 2, 'framedipaddress': 3, 'lastlogin': 4}, 'mng-search.php': {'id': 0, 'fullname': 1, 'username': 2, 'framedipaddress': 3, 'lastlogin': 4}, 'mng-batch-list.php': {'bid': 0, 'batch_name': 1, 'creationdate': 8, 'creationby': 9}, 'rep-batch-list.php': {'id': 0, 'creationdate': 8, 'creationby': 9}, 'rep-batch-details.php': {'username': 1, 'acctstarttime': 3}, 'rep-lastconnect.php': {'username': 0, 'user': 0, 'fullname': 1, 'reply': 2, 'authdate': 3, 'date': 3}, 'rep-online.php': {'username': 1, 'framedipaddress': 3, 'calledstationid': 4, 'nasshortname': 5, 'hotspot': 6, 'acctstarttime': 7, 'acctsessiontime': 8}, 'rep-topusers.php': {'username': 0, 'framedipaddress': 1, 'nasshortname': 2, 'acctstarttime': 3, 'acctstoptime': 4, 'Time': 5, 'Upload': 6, 'Download': 7, 'acctterminatecause': 8}, 'rep-newusers.php': {'month': 0, 'users': 1}, 'rep-history.php': {'section': 0, 'item': 1, 'creationdate': 2, 'creationby': 3, 'updatedate': 4, 'updateby': 5}, 'rep-hb-dashboard.php': {'id': 0}}
            def state(version='candidate'):
                tables={'custom_acct':'radacctid,username,acctstarttime,acctstoptime,acctsessiontime,acctinputoctets,acctoutputoctets','custom_info':'id,username,firstname,lastname,creationdate,creationby,updatedate,updateby','custom_check':'id,username,attribute,op','custom_reply':'id,username,attribute,op','custom_groups':'id,username,groupname,priority','custom_bill':'id,username,planname,batch_id','custom_batches':'id,batch_name,batch_description,batch_status,hotspot_id,creationdate,updatedate','custom_node':'id,mac,uptime,cpu,memfree,time','custom_postauth':'id,username,reply,authdate'}
                return {table:db('SELECT '+columns+' FROM '+table+' ORDER BY id' if table!='custom_acct' else 'SELECT '+columns+' FROM '+table+' ORDER BY radacctid',version) for table,columns in tables.items()}
            def compare(page,q):
                nonlocal comparisons
                results=[];exports=[]
                for version in ('base','candidate'):
                    status,text=req(page,q,version);assert status==200,(page,'HTTP parity')
                    results.append(Rows(text).projection())
                    if page in types and 'fileExport.php' in text:
                        kinds=[types[page]]
                        if page=='rep-batch-details.php':
                            kinds=['reportsBatchTotalUsers']
                            if 'Active Users CSV Export' in text:kinds.append('reportsBatchActiveUsers')
                        payload={}
                        for kind in kinds:
                            req(page,q,version);status,csvtext=req(export,{'reportFormat':'csv','reportType':kind},version)
                            assert status==200,(page,'CSV HTTP',version,status,q,kind)
                            payload[kind]=list(csv.reader(io.StringIO(csvtext)))
                        exports.append(payload)
                a,b=results
                assert a[1]==b[1] and sorted(a[0])==sorted(b[0]),(page,'complete display/foot parity',q)
                key=q.get('orderBy');column=sort_cells[page].get(key)
                if column is not None:
                    project=lambda rows:[row[column] for row in rows if page!='rep-batch-details.php' or len(row)==4]
                    assert project(a[0])==project(b[0]),(page,'ordered sort-key parity',key)
                if len(exports)==2:assert exports[0]==exports[1],(page,'full CSV parity')
                assert len(exports) in (0,2),(page,'CSV control parity')
                comparisons+=1
            before=state()
            for version in ('base','candidate'):(f/version/'app/common/includes/daloradius.conf.php').write_text(configs[version]+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='100';\n")
            for page in PAGES:
                for key in sorts[page]:
                    for direction in ('asc','desc'):compare(page,dict(defaults[page],orderBy=key,orderType=direction))
            for version in ('base','candidate'):(f/version/'app/common/includes/daloradius.conf.php').write_text(configs[version]+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='100';\n$configValues['CONFIG_DASHBOARD_DALO_DELAYSOFT']=1;\n$configValues['CONFIG_DASHBOARD_DALO_DELAYHARD']=2;\n")
            now=int(db('SELECT UNIX_TIMESTAMP()'))
            for age,color in [(0,'green'),(90,'orange'),(180,'red')]:
                for version in ('base','candidate'):db('UPDATE custom_node SET time=FROM_UNIXTIME('+str(now-age)+') WHERE id=10',version)
                compare('rep-hb-dashboard.php',dict(dates,orderBy='id'))
                for version in ('base','candidate'):assert 'color: '+color in req('rep-hb-dashboard.php',dates,version)[1]
            for version in ('base','candidate'):db("UPDATE custom_node SET time='2020-01-01' WHERE id=10",version)
            for page in ('mng-search.php','rep-lastconnect.php','rep-online.php','rep-topusers.php'):
                for user in ('Alice','li','Absent','55501','192.0.2.99'):compare(page,dict(defaults[page],username=user))
            for page in ('mng-list-all.php','mng-search.php','rep-lastconnect.php'):
                for version in ('base','candidate'):(f/version/'app/common/includes/daloradius.conf.php').write_text(configs[version]+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='100';\n$configValues['CONFIG_IFACE_PASSWORD_HIDDEN']='no';\n")
                for direction in ('asc','desc'):compare(page,dict(defaults[page],orderBy='pass' if page=='rep-lastconnect.php' else 'auth',orderType=direction))
            for version in ('base','candidate'):(f/version/'app/common/includes/daloradius.conf.php').write_text(configs[version]+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='100';\n")
            for reply in ('Any','Access-Accept','Access-Reject','Access-Challenge'):compare('rep-lastconnect.php',dict(dates,radiusReply=reply))
            for batch in ('First','Second','Empty','Absent'):
                for user in ('','Alice','Absent'):compare('rep-batch-details.php',{'batch_name':batch,'username':user})
            for page in ('rep-newusers.php','rep-lastconnect.php','rep-topusers.php'):
                for period in ({'startdate':'2020-01-01','enddate':'2020-01-01'},{'startdate':'2020-01-01','enddate':'2020-02-29'},{'startdate':'2040-01-01','enddate':'2040-01-01'}):compare(page,period)
            for version in ('base','candidate'):(f/version/'app/common/includes/daloradius.conf.php').write_text(configs[version])
            for page in PAGES:
                # Stable unique/default keys only; do not require an arbitrary tie page.
                for number in ('1','999','0','abc'):compare(page,dict(defaults[page],page=number))
            assert state()==before,'Read-only domain projection invariant'
            # FR1 versioned postauth column names, with the same date/reply filters.
            for version in ('base','candidate'):
                db('ALTER TABLE custom_postauth CHANGE username user VARCHAR(64), CHANGE authdate date TIMESTAMP',version)
                (f/version/'app/common/includes/daloradius.conf.php').write_text(configs[version]+"\n$configValues['FREERADIUS_VERSION']='1';\n")
            compare('rep-lastconnect.php',dict(dates,radiusReply='Any',orderBy='user'))
            for version in ('base','candidate'):
                db('ALTER TABLE custom_postauth CHANGE user username VARCHAR(64), CHANGE date authdate TIMESTAMP',version)
                (f/version/'app/common/includes/daloradius.conf.php').write_text(configs[version])
            # Named backend contains only its synthetic identity; no default leakage.
            other=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',other,'other')
            for page in ('mng-list-all.php','mng-search.php','rep-lastconnect.php','rep-online.php','rep-topusers.php','rep-batch-details.php'):
                query=dict(defaults[page])
                if page=='rep-topusers.php':db("UPDATE custom_acct SET acctstoptime='2020-01-02'",'candidate_other')
                status,text=req(page,query,session=other);assert status==200
                text=' '.join(map(str,Rows(text).rows));assert 'OtherOnly' in text and 'Alice' not in text,(page,'named routing')
            # Malformed scalars rejected before PHP templates or query execution.
            for page in PAGES:
                for key in ('username','startdate','enddate','orderBy','orderType','page'):
                    assert req(page,{key+'[]':'bad'})[0]==400,(page,key)
            for key in ('batch_name','radiusReply'):assert req('rep-batch-details.php' if key=='batch_name' else 'rep-lastconnect.php',{key+'[]':'bad'})[0]==400
            before=state()
            for page in ('mng-search.php','rep-lastconnect.php','rep-online.php','rep-topusers.php'):
                status,text=req(page,dict(defaults[page],username="' OR 1=1 --"));assert status==200 and not Rows(text).rows
            assert state()==before
            # Literal special and zero identities, safe UI labels and raw links.
            (f/'candidate/app/common/includes/daloradius.conf.php').write_text(configs['candidate']+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='100';\n")
            for user in ('0',"O'Reilly%雪",'Amp&Plus+','<b>Markup</b>'):
                safe=user.replace("'","''")
                db("INSERT INTO custom_info(username,firstname,lastname,creationdate) VALUES ('"+safe+"','Fixture','User','2020-01-02');INSERT INTO custom_check(username,attribute,op,value) VALUES ('"+safe+"','Auth-Type',':=','Accept');INSERT INTO custom_bill(username,planName,batch_id) VALUES ('"+safe+"','Basic',20);INSERT INTO custom_groups(username,groupname,priority) VALUES ('"+safe+"','Users',1);INSERT INTO custom_acct(username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime,acctstoptime,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES ('"+safe+"','Special','"+secrets.token_hex(8)+"','198.51.100.1','2020-01-02',NULL,1,0,0)")
                for page in ('mng-search.php','rep-online.php','rep-batch-details.php'):
                    status,text=req(page,dict(defaults[page],username=user));assert status==200 and len(Rows(text).rows)>=1
                    if user.startswith('<'):assert '<b>Markup</b>' not in text and '&lt;b&gt;Markup&lt;/b&gt;' in text
                    links=Rows(text).links
                    assert any(urllib.parse.parse_qs(urllib.parse.urlsplit(link).query).get('username')==[user] for link in links if 'mng-edit.php?' in link),(page,'raw identity link')
            for page,user in [('rep-batch-details.php','Alice'),('rep-online.php','0'),('rep-lastconnect.php','Alice')]:
                status,text=req(page,dict(defaults[page],username=user));assert status==200
                links=[link for link in Rows(text).links if (link.startswith(page+'?') or link.startswith('?')) and 'orderBy=' in link]
                assert links,(page,'real sort links')
                for link in links:
                    parsed=urllib.parse.parse_qs(urllib.parse.urlsplit(link).query)
                    assert parsed.get('username')==[user],(page,'filter retained in link')
                    if page=='rep-batch-details.php':assert parsed.get('batch_name')==['First']
                    assert req(page,{key:value[0] for key,value in parsed.items()})[0]==200
            (f/'candidate/app/common/includes/daloradius.conf.php').write_text(configs['candidate'])
            # Empty/error sources must clear other pages' export descriptors.
            for page in ('rep-history.php','rep-newusers.php','rep-hb-dashboard.php'):
                req('acct-all.php');req(page,defaults[page]);assert json.loads(req('descriptor.php')[1]) is None
            for page in ('mng-search.php','rep-online.php','rep-lastconnect.php','rep-topusers.php'):
                req('acct-all.php');req(page,dict(defaults[page],username='Absent'));assert json.loads(req('descriptor.php')[1]) is None
            req('rep-batch-details.php',{'batch_name':'First','username':'Absent'})
            descriptor=json.loads(req('descriptor.php')[1]);assert descriptor['source']=='rep-batch-details' and descriptor['type']=='reportsBatchActiveUsers'
            assert req(export,{'reportFormat':'csv','reportType':'reportsBatchTotalUsers'})[0]==200
            # A real later SELECT failure after each page's successful native count.
            target=f/'candidate/app/operators/library/operator_reports_pdo.php';original=target.read_text()
            marker='    $bindings[\':operator_offset\']=$offset;'
            assert marker in original
            late_sources={'mng-list-all.php': ('custom_acct', 'acctstarttime'), 'mng-search.php': ('custom_acct', 'acctstarttime'), 'mng-batch-list.php': ('custom_bill', 'planname'), 'rep-batch-list.php': ('custom_acct', 'username'), 'rep-batch-details.php': ('custom_acct', 'acctstarttime'), 'rep-lastconnect.php': ('custom_postauth', 'authdate'), 'rep-online.php': ('custom_acct', 'acctinputoctets'), 'rep-topusers.php': ('custom_acct', 'acctinputoctets'), 'rep-history.php': ('custom_info', 'creationdate'), 'rep-newusers.php': ('custom_info', 'creationdate'), 'rep-hb-dashboard.php': ('custom_node', 'cpu')}
            for page,(table,column) in late_sources.items():
                column_type=db("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='candidate' AND TABLE_NAME='"+table+"' AND COLUMN_NAME='"+column+"'")
                assert re.fullmatch(r'[a-z0-9(), ]+',column_type)
                alteration='ALTER TABLE '+table+' CHANGE '+column+' absent_column '+column_type
                hook="    if (file_exists('/fixtures/fail-read')) {unlink('/fixtures/fail-read');$pdo->exec('"+alteration+"');}\n"
                target.write_text(original.replace(marker,hook+marker));(f/'fail-read').write_text('enabled')
                status,text=req(page,defaults[page]);assert not (f/'fail-read').exists(),(page,'late hook reached')
                assert status==200 and 'Unable to read accounting records' in text and 'fileExport.php' not in text and 'SQLSTATE' not in text,(page,'sanitized late error')
                assert json.loads(req('descriptor.php')[1]) is None
                db('ALTER TABLE '+table+' CHANGE absent_column '+column+' '+column_type)
            target.write_text(original)
            # Existing NULL traffic fatal is characterized independently from parity.
            db("UPDATE custom_acct SET acctinputoctets=NULL,acctoutputoctets=NULL WHERE radacctid=1",'base')
            def logs():
                result=subprocess.run(['docker','logs',h.WEB],capture_output=True,text=True,check=True);return result.stdout+result.stderr
            old=logs();req('rep-online.php',dict(dates,username='Alice'),'base');delta=logs()[len(old):]
            assert 'Unsupported operand types: string + string' in delta and '/fixtures/base/app/operators/rep-online.php' in delta
            db("UPDATE custom_acct SET acctinputoctets=NULL,acctoutputoctets=NULL WHERE radacctid=1")
            status,text=req('rep-online.php',dict(dates,username='Alice'));assert status==200 and Rows(text).rows and 'fileExport.php' in text
            # ACL denied both normal and malformed routes.
            denied=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',denied,'default','9002')
            for page in PAGES:
                assert req(page,defaults[page],session=denied)[0] in (302,403)
                assert req(page,{'username[]':'bad'},session=denied)[0] in (302,403)
            # Read-only generated SQL account on default and named backend.
            reader='reader_'+secrets.token_hex(6);password=secrets.token_hex(24)
            sql("CREATE USER '"+reader+"'@'%' IDENTIFIED BY '"+password+"';GRANT SELECT ON candidate.* TO '"+reader+"'@'%';GRANT SELECT ON candidate_other.* TO '"+reader+"'@'%'")
            cf=f/'candidate/app/common/includes/daloradius.conf.php'
            cf.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_USER']='"+reader+"';\n$configValues['CONFIG_DB_PASS']='"+password+"';\n")
            for page in PAGES:
                status,text=req(page,defaults[page]);assert status==200 and 'Unable to read accounting' not in text,(page,'SELECT-only')
            cf.write_text(configs['candidate']);sql("DROP USER '"+reader+"'@'%'");reader=password=None
            # Actual no-legacy-open/close tripwire for all eleven page paths.
            for name in ('db_open.php','db_close.php'):(f/'candidate/app/common/includes'/name).write_text("<?php throw new RuntimeException('Legacy database tripwire');")
            for page in PAGES:
                status,text=req(page,defaults[page]);assert status==200 and 'Unable to read accounting records' not in text
            before=state();db('RENAME TABLE custom_acct TO missing_acct')
            for page in ('mng-list-all.php','mng-search.php','rep-batch-list.php','rep-batch-details.php','rep-online.php','rep-topusers.php'):
                status,text=req(page,defaults[page]);assert status==200 and 'Unable to read accounting records' in text and 'SQLSTATE' not in text and 'fileExport.php' not in text
            db('RENAME TABLE missing_acct TO custom_acct');assert state()==before
            log=logs();assert 'SQLSTATE' not in log
            bad=[line for line in log.splitlines() if '/fixtures/candidate/' in line and any(mark in line for mark in ('PHP Fatal error:','PHP Warning:','PHP Deprecated:'))]
            if bad:print('DIAGNOSTIC_LOCATIONS',[re.findall(r'/fixtures/candidate/[^ ]+|on line [0-9]+|PHP (?:Warning|Deprecated|Fatal error):',line) for line in bad],flush=True)
            assert not bad,'Candidate PHP diagnostics (details suppressed)'
            print('PASS '+str(comparisons)+' pinned PEAR/PDO display comparisons, every offered sort, full CSV producers including both batch detail exports, periods/pagination and FR1 postauth columns',flush=True)
            print('PASS read-only domain projections, configured/named routing, SELECT-only grants, ACL/input/injection, exact raw identities, late/missing reads, stale CSV clearing and eleven legacy tripwires',flush=True)
        finally:
            run('docker','rm','-f',h.WEB,h.DB,check=False);run('docker','network','rm',h.NETWORK,check=False)
            subprocess.run(['sudo','-n','chown','-R',str(os.getuid())+':'+str(os.getgid()),str(f)],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
            assert not [n for n in run('docker','ps','-a','--format','{{.Names}}').splitlines() if n in (h.WEB,h.DB)]
            assert h.NETWORK not in run('docker','network','ls','--format','{{.Name}}').splitlines()
            print('CLEANUP R13 fixtures removed',flush=True)
if __name__=='__main__':main()
