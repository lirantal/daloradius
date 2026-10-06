#!/usr/bin/env python3
"""R14: pinned PEAR/PDO charts, tables and dashboard through native isolated HTTP/PHP/MariaDB.
No live database, browser or RADIUS server is used. Fixture sessions are synthetic.
"""
import concurrent.futures,csv,io,json,os,re,secrets,shutil,subprocess,tempfile,time,urllib.parse,urllib.request,urllib.error
from html.parser import HTMLParser
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap
import user_actions_http as h
ROOT=Path(__file__).resolve().parents[1]
BASE='d0a8a9d20016130e56ff723350004d395a64eddd'
SUFFIX=secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=['pdo-r14-'+kind+'-'+SUFFIX for kind in ('db','web','net')]
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
PAGES=['home-main.php', 'library/graphs/alltime_users_data.php', 'library/graphs/logged_users.php', 'library/graphs/new_users.php', 'library/graphs/online_nas.php', 'library/graphs/online_users.php', 'library/graphs/overall_users_data.php', 'library/graphs/total_users.php', 'graphs-alltime_logins.php', 'graphs-overall_logins.php', 'graphs-overall_upload.php', 'graphs-overall_download.php', 'rep-newusers.php', 'rep-online.php', 'graphs-alltime_traffic_compare.php', 'graphs-logged_users.php', 'mng-main.php', 'mng-users.php']

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
    with tempfile.TemporaryDirectory(prefix='pdo-r14-',dir=scratch) as tmp:
        f=Path(tmp);configs={}
        for version in ('base','candidate'):
            shutil.copytree(ROOT/'app',f/version/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if version=='base':
                restore_pear_bootstrap((f / version / 'app').parent, BASE)
                for source in ['app/common/includes/chart.php', 'app/operators/include/management/functions.php', 'app/operators/home-main.php', 'app/operators/library/graphs/alltime_users_data.php', 'app/operators/library/graphs/logged_users.php', 'app/operators/library/graphs/new_users.php', 'app/operators/library/graphs/online_nas.php', 'app/operators/library/graphs/online_users.php', 'app/operators/library/graphs/overall_users_data.php', 'app/operators/library/graphs/total_users.php', 'app/operators/library/tables/alltime_users_login.php', 'app/operators/library/tables/overall_users_download.php', 'app/operators/library/tables/overall_users_login.php', 'app/operators/library/tables/overall_users_upload.php', 'app/operators/graphs-alltime_logins.php', 'app/operators/graphs-overall_logins.php', 'app/operators/graphs-overall_upload.php', 'app/operators/graphs-overall_download.php']:
                    (f/version/source).write_bytes(subprocess.check_output(['git','show',BASE+':'+source],cwd=ROOT))
            # Historical PEAR consumer belongs only to the baseline source tree.
            if version=='base':
                (f/version/'app/users/library/graphs/overall_users_data.php').write_bytes(
                    subprocess.check_output(['git','show', BASE+':app/users/library/graphs/overall_users_data.php'],cwd=ROOT))
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
        (f/'portal-session.php').write_text("<?php session_name('daloradius_user_sid');session_id($argv[1]);session_start();$_SESSION=['logged_in'=>true,'login_user'=>'Alice','location_name'=>'default','time'=>time()];session_write_close();")
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
                acl=','.join("(9001,'"+Path(page).stem.replace('-','_')+"',1),(9002,'"+Path(page).stem.replace('-','_')+"',0)" for page in PAGES+['acct-all.php'])
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
            graphs=[p for p in PAGES if p.startswith('library/graphs/')]
            tables=[p.split('app/operators/')[1] for p in ['app/operators/graphs-alltime_logins.php', 'app/operators/graphs-overall_logins.php', 'app/operators/graphs-overall_upload.php', 'app/operators/graphs-overall_download.php']]
            for version in ('base','candidate','base_other','candidate_other'):
                db("INSERT INTO custom_acct(username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime,acctstoptime,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES ('"+('OtherOnly' if version.endswith('_other') else 'Alice')+"','Recent','Recent','198.51.100.1','2023-11-10','2023-11-11',120,1048576,2147483648)",version)
                if not version.endswith('_other'):
                    db("INSERT INTO custom_acct(username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime,acctstoptime,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES ('NullOnly','Null','Null','198.51.100.1',NULL,'2020-01-02',NULL,NULL,NULL)",version)
                    for i in range(40):
                        year=2010+i//12;month=1+i%12
                        db("INSERT INTO custom_acct(username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime,acctstoptime,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES ('Alice','Cap"+str(i)+"','Cap"+str(i)+"','198.51.100.1','"+str(year)+'-'+str(month)+"-01','"+str(year)+'-'+str(month)+"-02',1,1048576,1073741824)",version)
            def state(version='candidate'):
                return db('SELECT radacctid,username,acctstarttime,acctstoptime,acctsessiontime,acctinputoctets,acctoutputoctets FROM custom_acct ORDER BY radacctid',version)
            def compare(page,q=None):
                nonlocal comparisons
                data=[]
                for version in ('base','candidate'):
                    status,text=req(page,q,version);assert status==200,(page,'status',version,status)
                    if page in graphs:
                        value=json.loads(text);assert set(value)=={'type','data','options'},(page,'chart envelope')
                        data.append(value)
                    else:
                        rows=Rows(text);value=rows.projection()
                        if page=='home-main.php':
                            cards=re.findall(r'<p class="card-text">(.*?)</p>',text,re.S)
                            assert len(cards)==3;value=(value,cards)
                        data.append(value)
                assert data[0]==data[1],(page,'complete native projection mismatch',q)
                comparisons+=1
            before=state()
            for kind in ('login','upload','download'):
                for period in ('daily','monthly','yearly'):
                    for size in ('megabytes','gigabytes'):
                        q={'category':kind,'type':period,'size':size}
                        compare('library/graphs/alltime_users_data.php',q)
                        for user in ('Alice','Bob','NullOnly','Absent',''):
                            compare('library/graphs/overall_users_data.php',dict(q,user=user))
            for period in ('daily','monthly','yearly'):
                key={'daily':'day','monthly':'month','yearly':'year'}[period]
                for page in tables:
                    metric='uploads' if 'upload' in page else 'downloads' if 'download' in page else 'logins'
                    for order in (key,metric):
                        for direction in ('asc','desc'):
                            for number in ('1','2','999'):
                                query={'type':period,'username':'Alice','orderBy':order,'orderType':direction,'page':number}
                                compare(page,query)
                                if metric!='logins':compare(page,dict(query,size='gigabytes'))
            for query in ({},{'startdate':'2020-01-01'},{'enddate':'2020-02-29'},{'startdate':'2020-01-01','enddate':'2020-01-01'},{'startdate':'2040-01-01'},{'startdate':'invalid'}):compare('library/graphs/new_users.php',query)
            for query in ({'year':'2020','month':'1'},{'year':'2020','month':'2'},{'year':'2020','month':'1','day':'1'},{'year':'2020','month':'1','day':'2'},{'year':'2020','month':'2','day':'29'},{'year':'1970','month':'13','day':'32'}):compare('library/graphs/logged_users.php',query)
            for page in ('library/graphs/online_nas.php','library/graphs/online_users.php','library/graphs/total_users.php','home-main.php'):compare(page)
            for version in ('base','candidate'):
                db('ALTER TABLE custom_postauth CHANGE username user VARCHAR(64),CHANGE authdate date TIMESTAMP',version)
                (f/version/'app/common/includes/daloradius.conf.php').write_text(configs[version]+"\n$configValues['FREERADIUS_VERSION']='1';\n")
            compare('home-main.php')
            for version in ('base','candidate'):
                db('ALTER TABLE custom_postauth CHANGE user username VARCHAR(64),CHANGE date authdate TIMESTAMP',version)
                (f/version/'app/common/includes/daloradius.conf.php').write_text(configs[version])
            assert state()==before,'read-only accounting projection'
            cap=json.loads(req('library/graphs/overall_users_data.php',{'category':'login','type':'monthly','user':'Alice'})[1]);assert len(cap['data']['labels'])==36
            # Actual parent-produced chart URLs and table ordering links, not constructed substitutes.
            for user in ('0',"O'Reilly%雪",'Amp&Plus+','<b>Markup</b>'):
                safe=user.replace("'","''")
                db("INSERT INTO custom_acct(username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime,acctstoptime,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES ('"+safe+"','Special','"+secrets.token_hex(8)+"','198.51.100.1','2020-01-01','2020-01-02',1,1048576,2147483648)")
                for page in tables:
                    if 'alltime' in page:continue
                    status,text=req(page,{'username':user,'type':'daily'});assert status==200 and Rows(text).rows
                    if user.startswith('<'):assert '<b>Markup</b>' not in text and '&lt;b&gt;Markup&lt;/b&gt;' in text
                    sources=re.findall(r'data-chart-source="([^"]+)"',text);assert len(sources)==1
                    source=__import__('html').unescape(sources[0]);parsed=urllib.parse.urlsplit(source);query=urllib.parse.parse_qs(parsed.query)
                    assert query['user']==[user],(page,'exact identity in actual canvas source')
                    status,chart=req(parsed.path,{k:v[0] for k,v in query.items()});assert status==200 and json.loads(chart)['data']['datasets'][0]['data']
                    links=[link for link in Rows(text).links if link.startswith('?') and 'orderBy=' in link]
                    assert links
                    for link in links:
                        query=urllib.parse.parse_qs(urllib.parse.urlsplit(link).query);assert query['username']==[user]
                        assert req(page,{k:v[0] for k,v in query.items()})[0]==200
            for page in graphs+tables:
                for key in ('type','category','size','username','user','startdate','day','orderBy','page'):
                    assert req(page,{key+'[]':'invalid'})[0]==400,(page,'scalar input',key)
            for page in tables:
                if 'alltime' not in page:
                    status,text=req(page,{'username':"' OR 1=1 --"});assert status==200 and not Rows(text).rows
            status,chart=req('library/graphs/overall_users_data.php',{'user':"' OR 1=1 --"});assert status==200 and json.loads(chart)['data']['labels']==[]
            # Named routing uses a record not present on the default location.
            other=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',other,'other')
            status,chart=req('library/graphs/overall_users_data.php',{'user':'OtherOnly','category':'login'},session=other)
            assert status==200 and json.loads(chart)['data']['datasets'][0]['data']
            for page in tables:
                status,text=req(page,{'username':'OtherOnly'},session=other);assert status==200 and Rows(text).rows
                if 'alltime' not in page:assert 'OtherOnly' in text and 'Alice' not in text
            status,text=req('home-main.php',session=other);assert status==200 and 'OtherOnly' in text and 'Alice' not in text
            for page in graphs:
                query={'user':'OtherOnly','year':'2020','month':'1','category':'login'}
                status,text=req(page,query,session=other);assert status==200
                selected=json.loads(text)
                if page.endswith(('new_users.php','total_users.php','online_users.php')):
                    assert selected!=json.loads(req(page,query)[1]),(page,'distinct selected dataset')
            # SQL exceptions in both read-only contexts preserve the borrowed transaction.
            probe=f/'candidate/app/operators/borrowed-widget.php'
            probe.write_text("<?php include_once '../common/includes/config_read.php';include 'library/checklogin.php';require_once '../common/includes/chart.php';require_once 'library/widget_reads_pdo.php';$pdo=dalo_widget_open();$pdo->beginTransaction();$data=dalo_chart_overall_user_statistics($pdo,$configValues['CONFIG_DB_TBL_RADACCT'],'Alice','login','monthly','megabytes','%s %s',true);$active=$pdo->inTransaction();$failed=false;try{dalo_chart_rows($pdo,'SELECT absent FROM missing_widget_table');}catch(Throwable $e){$failed=true;}$active=$active && $pdo->inTransaction();$pdo->rollBack();echo json_encode([$active,$failed,count($data['labels'])>0,!$pdo->inTransaction()]);")
            assert json.loads(req('borrowed-widget.php')[1])==[True,True,True,True]
            # Every direct graph producer respects its existing parent page permission.
            denied=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',denied,'default','9002')
            for page in graphs:
                assert req(page,session=denied)[0]==403,(page,'producer ACL')
                request=urllib.request.Request('http://'+ip+':8080/candidate/app/operators/'+page)
                class NoRedirect(urllib.request.HTTPRedirectHandler):
                    def redirect_request(self,*args,**kwargs):return None
                try:urllib.request.build_opener(NoRedirect).open(request,timeout=15);assert False,'unauthenticated producer accepted'
                except urllib.error.HTTPError as e:assert e.code==302
            db("INSERT INTO operators_acl(operator_id,file,access) VALUES (9002,'graphs_overall_download',1)")
            duplicate=int(db('SELECT MAX(id) FROM operators_acl'))
            assert req('library/graphs/overall_users_data.php',{'user':'Alice','category':'download'},session=denied)[0]==403
            db('DELETE FROM operators_acl WHERE id='+str(duplicate))
            # Historical PEAR portal producer versus current PDO-only shared chart.
            portal=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/portal-session.php',portal)
            portal_outputs=[]
            for version in ('base','candidate'):
                request=urllib.request.Request('http://'+ip+':8080/'+version+'/app/users/library/graphs/overall_users_data.php?category=upload&type=monthly',headers={'Cookie':'daloradius_user_sid='+portal})
                with urllib.request.urlopen(request,timeout=15) as response:portal_outputs.append(json.loads(response.read()))
            assert portal_outputs[0]==portal_outputs[1] and portal_outputs[1]['data']['labels'],'historical PEAR/current PDO portal chart parity'
            # Direct extension/provider guards are still effective.
            for page in ['library/tables/'+Path(p).name for p in ['app/operators/library/tables/alltime_users_login.php', 'app/operators/library/tables/overall_users_download.php', 'app/operators/library/tables/overall_users_login.php', 'app/operators/library/tables/overall_users_upload.php']]+['library/widget_reads_pdo.php']:
                assert req(page)[0] in (302,404),(page,'direct access guard')
            # A SELECT-only fixture account exercises the application, not only connection setup.
            reader='reader_'+secrets.token_hex(6);password=secrets.token_hex(24)
            sql("CREATE USER '"+reader+"'@'%' IDENTIFIED BY '"+password+"';GRANT SELECT ON candidate.* TO '"+reader+"'@'%';GRANT SELECT ON candidate_other.* TO '"+reader+"'@'%'")
            cf=f/'candidate/app/common/includes/daloradius.conf.php'
            cf.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_USER']='"+reader+"';\n$configValues['CONFIG_DB_PASS']='"+password+"';\n$configValues['CONFIG_LOCATIONS']['other']['Username']='"+reader+"';\n$configValues['CONFIG_LOCATIONS']['other']['Password']='"+password+"';\n")
            for page in graphs+tables+['home-main.php']:
                status,text=req(page,{'username':'Alice','user':'Alice'});assert status==200 and 'Unable to read widget' not in text,(page,'SELECT-only')
            assert req('library/graphs/overall_users_data.php',{'user':'OtherOnly'},session=other)[0]==200
            cf.write_text(configs['candidate']);sql("DROP USER '"+reader+"'@'%'");reader=password=None
            # Legacy connection tripwires include parent tables and JSON endpoints.
            for name in ('db_open.php','db_close.php'):(f/'candidate/app/common/includes'/name).write_text("<?php throw new RuntimeException('Legacy widget tripwire');")
            for page in graphs+tables+['home-main.php']:
                status,text=req(page,{'username':'Alice','user':'Alice'});assert status==200 and 'Unable to read widget' not in text,(page,'legacy tripwire')
            # A real later query error after the full table dataset was fetched.
            provider=f/'candidate/app/operators/library/widget_reads_pdo.php';original=provider.read_text()
            marker='    return dalo_chart_rows($pdo,$sql,$bindings);';assert marker in original
            hook="    if (file_exists('/fixtures/late-widget') && strpos($sql,' LIMIT ')!==false) {unlink('/fixtures/late-widget');$pdo->exec('ALTER TABLE custom_acct CHANGE acctstarttime absent_start DATETIME');}\n"
            provider.write_text(original.replace(marker,hook+marker))
            for page in tables:
                (f/'late-widget').write_text('enabled');status,text=req(page,{'username':'Alice'});assert status==200 and 'Unable to read widget data' in text
                assert not (f/'late-widget').exists();db('ALTER TABLE custom_acct CHANGE absent_start acctstarttime DATETIME')
            provider.write_text(original)
            # Missing/invalid configured sources cannot leak SQL or yield success JSON.
            db('RENAME TABLE custom_acct TO missing_acct')
            for page in graphs:
                if page.endswith(('new_users.php','total_users.php')):continue
                status,text=req(page,{'user':'Alice','year':'2020','month':'1'});assert status==500 and json.loads(text)=={'error':'Unable to read widget data'},(page,'SQL error envelope')
            for page in tables+['home-main.php']:
                status,text=req(page,{'username':'Alice'});assert status==200 and 'Unable to read widget data' in text
            db('RENAME TABLE missing_acct TO custom_acct')
            cf.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_TBL_RADACCT']='bad;identifier';\n")
            for page in ['library/graphs/alltime_users_data.php','home-main.php']:
                status,text=req(page);assert 'Unable to read widget data' in text and 'SQLSTATE' not in text
            cf.write_text(configs['candidate'])
            for table,pages in [('custom_info',['library/graphs/new_users.php','library/graphs/total_users.php']),('custom_check',['library/graphs/total_users.php','library/graphs/online_users.php']),('custom_nas',['library/graphs/online_nas.php'])]:
                db('RENAME TABLE '+table+' TO missing_widget_table')
                for page in pages:
                    status,text=req(page);assert status==500 and json.loads(text)=={'error':'Unable to read widget data'},(page,'own missing source')
                db('RENAME TABLE missing_widget_table TO '+table)
            # Empty datasets have the same complete JSON and HTML contract.
            for version in ('base','candidate'):
                db('DELETE FROM custom_acct;DELETE FROM custom_info;DELETE FROM custom_check',version)
            for page in graphs:compare(page,{'year':'2020','month':'1','user':'Absent'})
            for page in tables:compare(page,{'username':'Absent'})
            compare('home-main.php')
            logs=subprocess.run(['docker','logs',h.WEB],capture_output=True,text=True,check=True);logs=logs.stdout+logs.stderr
            bad=[line for line in logs.splitlines() if '/fixtures/candidate/' in line and any(mark in line for mark in ('PHP Fatal error:','PHP Warning:','PHP Deprecated:'))]
            if bad:print('DIAGNOSTIC_LOCATIONS',[re.findall(r'/fixtures/candidate/[^ ]+|on line [0-9]+|PHP (?:Warning|Deprecated|Fatal error):',line) for line in bad],flush=True)
            assert not bad,'Candidate PHP diagnostics (details suppressed)'
            assert 'SQLSTATE' not in logs
            print('PASS '+str(comparisons)+' pinned PEAR/PDO complete JSON/HTML comparisons: periods, categories, units, every table sort/pagination, capped and empty results, and all dashboard sections',flush=True)
            print('PASS exact canvas/sort links, raw special identities, scalar/injection/ACL, configured/named reads, SELECT-only grants, historical PEAR/current PDO portal parity, native later failures and no-legacy tripwires',flush=True)
        finally:
            run('docker','rm','-f',h.WEB,h.DB,check=False);run('docker','network','rm',h.NETWORK,check=False)
            subprocess.run(['sudo','-n','chown','-R',str(os.getuid())+':'+str(os.getgid()),str(f)],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
            assert not [n for n in run('docker','ps','-a','--format','{{.Names}}').splitlines() if n in (h.WEB,h.DB)]
            assert h.NETWORK not in run('docker','network','ls','--format','{{.Name}}').splitlines()
            print('CLEANUP R14 fixtures removed',flush=True)
if __name__=='__main__':main()
