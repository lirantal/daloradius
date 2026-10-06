#!/usr/bin/env python3
"""R11: pinned PEAR/PDO reports through native isolated HTTP/PHP/MariaDB.
No live database, browser or RADIUS server is used. Fixture sessions are synthetic.
"""
import csv,io,json,os,re,secrets,shutil,subprocess,tempfile,urllib.parse,urllib.request,urllib.error
from html.parser import HTMLParser
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap
import user_actions_http as h
ROOT=Path(__file__).resolve().parents[1]
BASE='1160e4bfb6f1744c6eb5c3b8ac0fae8d154dc898'
SUFFIX=secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=['pdo-r11-'+kind+'-'+SUFFIX for kind in ('db','web','net')]
run,sql,wait_for=h.run,h.sql,h.wait_for
PAGES=['acct-'+kind+'.php' for kind in ('active','all','date','hotspot-accounting','hotspot-compare','ipaddress','nasipaddress','username')]
GENERIC=[page for page in PAGES if page not in ('acct-active.php','acct-hotspot-compare.php')]
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
    with tempfile.TemporaryDirectory(prefix='pdo-r11-',dir=scratch) as tmp:
        f=Path(tmp);configs={}
        for version in ('base','candidate'):
            shutil.copytree(ROOT/'app',f/version/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if version=='base':
                restore_pear_bootstrap((f / version / 'app').parent, BASE)
                for page in PAGES:
                    (f/version/'app/operators'/page).write_bytes(subprocess.check_output(['git','show',BASE+':app/operators/'+page],cwd=ROOT))
            conf=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,value in {'CONFIG_DB_HOST':h.DB,'CONFIG_DB_USER':'root','CONFIG_DB_PASS':'','CONFIG_DB_NAME':version,'CONFIG_IFACE_TABLES_LISTING':'2','CONFIG_DB_TBL_RADACCT':'custom_acct','CONFIG_DB_TBL_DALOHOTSPOTS':'custom_hs','CONFIG_DB_TBL_RADCHECK':'custom_check','CONFIG_DB_TBL_DALOUSERBILLINFO':'custom_bill','CONFIG_IFACE_DEBUG':'0'}.items():
                conf+='\n$configValues['+repr(key)+']='+repr(value)+';\n'
            conf+="\n$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>'"+h.DB+"','Username'=>'root','Password'=>'','Database'=>'"+version+"_other','Port'=>'3306');\n"
            configs[version]=conf;(f/version/'app/common/includes/daloradius.conf.php').write_text(conf)
        (f/'session.php').write_text("<?php session_name('daloradius_operator_sid');session_id($argv[1]);session_start();$_SESSION=['daloradius_logged_in'=>true,'operator_id'=>(int)($argv[3]??9001),'operator_user'=>bin2hex(random_bytes(12)),'location_name'=>($argv[2]??'default'),'time'=>time()];session_write_close();")
        (f/'candidate/app/operators/descriptor.php').write_text("<?php include 'library/checklogin.php';echo json_encode($_SESSION['reportExport']??null);")
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
                for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql','mariadb-daloradius-dictionaries.sql'):db((ROOT/'contrib/db'/name).read_text(),version)
                db('RENAME TABLE radacct TO custom_acct, hotspots TO custom_hs, radcheck TO custom_check, userbillinfo TO custom_bill',version)
                db('ALTER TABLE custom_acct MODIFY acctterminatecause VARCHAR(32) NULL',version)
                acl=','.join("(9001,'"+page[:-4].replace('-','_')+"',1),(9002,'"+page[:-4].replace('-','_')+"',0)" for page in PAGES)
                db('INSERT INTO operators_acl(operator_id,file,access) VALUES '+acl,version)
                db("INSERT INTO custom_hs(id,name,mac) VALUES (10,'Alpha','02:00:00:00:00:10'),(11,'Beta','02:00:00:00:00:11'),(12,'Empty','02:00:00:00:00:12')",version)
                accounts=[('Alice','2020-01-01 00:00:00',None,'192.0.2.1','198.51.100.1','02:00:00:00:00:10','0',10),('Alice','2020-01-02 23:59:59','2020-01-03 00:00:00','192.0.2.2','198.51.100.2','02:00:00:00:00:11','User-Request',20),('Bob','2020-01-03 00:00:00',None,'192.0.2.3','198.51.100.1','02:00:00:00:00:10','Lost-Carrier',30),('Bob','2020-01-01 12:00:00','2020-01-02','192.0.2.4','198.51.100.3','unknown','',40),('NoMatch','2020-01-01',None,'192.0.2.5','198.51.100.4','unknown',None,0),('External','2020-01-04',None,'192.0.2.6','198.51.100.1','02:00:00:00:00:11','0',50)]
                if version.endswith('_other'):accounts=[('OtherOnly','2020-01-01',None,'192.0.2.7','198.51.100.5','02:00:00:00:00:10','0',60)]
                def literal(value):return 'NULL' if value is None else "'"+str(value).replace("'","''")+"'"
                for i,(user,start,stop,ip,nas,mac,cause,duration) in enumerate(accounts):
                    db('INSERT INTO custom_acct(radacctid,username,acctsessionid,acctuniqueid,acctstarttime,acctstoptime,framedipaddress,nasipaddress,calledstationid,acctterminatecause,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES ('+','.join(map(literal,[i+1,user,'S'+str(i),'U'+str(i),start,stop,ip,nas,mac,cause,duration,duration*100,duration*200]))+')',version)
                db("INSERT INTO custom_check(username,attribute,op,value) VALUES ('Alice','Max-All-Session',':=','100'),('Bob','Expiration',':=','2030-01-01'),('External','Max-All-Session',':=','1'),('OtherOnly','Max-All-Session',':=','100')",version)
            run('docker','run','-d','--name',h.WEB,'--network',h.NETWORK,'-v',str(f)+':/fixtures','-w','/fixtures','--entrypoint','php','lirantal/daloradius','-d','opcache.enable_cli=0','-d','opcache.enable=0','-d','display_errors=0','-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',h.WEB)
            sid=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',sid)
            def req(page,query=None,version='candidate',session=None):
                url='http://'+ip+':8080/'+version+'/app/operators/'+page
                if query:url+='?'+urllib.parse.urlencode(query,doseq=True)
                request=urllib.request.Request(url,headers={'Cookie':'daloradius_operator_sid='+(session or sid)})
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
            default={'acct-date.php':{'username':'Alice','startdate':'2020-01-01','enddate':'2020-01-02'},'acct-username.php':{'username':'Alice'}}
            comparisons=0
            def compare(page,query=None,session=None,export_csv=True):
                nonlocal comparisons
                q=dict(default.get(page,{}));q.update(query or {})
                a=req(page,q,'base',session);b=req(page,q,'candidate',session)
                assert a[0]==b[0]==200,(page,'HTTP parity')
                pa,pb=Rows(a[1]).projection(),Rows(b[1]).projection()
                if pa!=pb:
                    # SQL gives no order within ties. Compare complete result sets
                    # plus ordered sort-key sequences, not arbitrary tie order.
                    key=q.get('orderBy','radacctid')
                    indices={'radacctid':0,'name':1,'username':2,'framedipaddress':3,'acctstarttime':4,'acctstoptime':5,'acctsessiontime':6,'acctinputoctets':7,'acctoutputoctets':8,'acctterminatecause':9,'nasipaddress':10}
                    if page=='acct-active.php':indices={'username':0,'attribute':1,'maxtimeexpiration':2,'usedtime':3}
                    if page=='acct-hotspot-compare.php':indices={'hotspot':0,'uniqueusers':1,'totalhits':2,'avgsessiontime':3,'totaltime':4,'sumInputOctets':5,'sumOutputOctets':6}
                    assert pa[1]==pb[1] and sorted(pa[0])==sorted(pb[0]) and [r[indices[key]] for r in pa[0]]==[r[indices[key]] for r in pb[0]],(page,q,'display/sort parity')
                assert not any(x in b[1] for x in ('SQLSTATE','PHP Fatal','PHP Warning'))
                if export_csv and page in GENERIC and 'fileExport.php' in b[1]:
                    ea=req(export,{'reportFormat':'csv','reportType':'accountingGeneric'},'base',session)
                    eb=req(export,{'reportFormat':'csv','reportType':'accountingGeneric'},'candidate',session)
                    assert ea==eb and ea[0]==200,(page,'full unpaginated CSV parity')
                comparisons+=1
                return a,b
            def snapshot(version='candidate'):
                return tuple(db('SELECT '+columns+' FROM '+table+' ORDER BY '+order,version) for table,columns,order in [('custom_acct','radacctid,username,acctsessionid,acctuniqueid,acctstarttime,acctstoptime,acctsessiontime,acctinputoctets,acctoutputoctets,acctterminatecause,framedipaddress,nasipaddress,calledstationid','radacctid'),('custom_hs','id,name,mac','id'),('custom_check','id,username,attribute,op,value','id'),('custom_bill','id,username,planName','id')])
            original=snapshot()
            sorts=['radacctid','name','username','framedipaddress','acctstarttime','acctstoptime','acctsessiontime','acctinputoctets','acctoutputoctets','acctterminatecause','nasipaddress']
            for page in PAGES:
                keys=['username','attribute','maxtimeexpiration','usedtime'] if page=='acct-active.php' else ['hotspot','uniqueusers','totalhits','avgsessiontime','totaltime','sumInputOctets','sumOutputOctets'] if page=='acct-hotspot-compare.php' else sorts
                for version in ('base','candidate'):
                    (f/version/'app/common/includes/daloradius.conf.php').write_text(configs[version]+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='100';\n")
                for key in keys:
                    for direction in ('asc','desc'):compare(page,{'orderBy':key,'orderType':direction})
                for version in ('base','candidate'):(f/version/'app/common/includes/daloradius.conf.php').write_text(configs[version])
                for page_num in ('1','2','999','0','-1','abc'):compare(page,{'page':page_num})
            for page,query in [('acct-date.php',{'startdate':'2020-01-02','enddate':'2020-01-02'}),('acct-date.php',{'startdate':'2020-01-03','enddate':'2020-01-02'}),('acct-ipaddress.php',{'ipaddress':'192.0.2.1'}),('acct-ipaddress.php',{'ipaddress':'192.0.2'}),('acct-nasipaddress.php',{'nasipaddress':'198.51.100.1'}),('acct-nasipaddress.php',{'nasipaddress':'198.51.100.1','only-active':'1'}),('acct-hotspot-accounting.php',{'hotspot':['Alpha','Beta']}),('acct-hotspot-accounting.php',{'hotspot':'Alpha'}),('acct-hotspot-accounting.php',{'hotspot':'Empty'}),('acct-username.php',{'username':'Absent'}),('acct-active.php',{'username':'Absent','startdate':'2020-01-01','enddate':'2020-01-02'})]:compare(page,query)
            # End day is inclusive, next midnight is excluded; raw CSV stop/NULL stays unchanged.
            req('acct-date.php',default['acct-date.php']);status,text=req(export,{'reportFormat':'csv','reportType':'accountingGeneric'})
            rows=list(csv.reader(io.StringIO(text)));assert status==200 and {r[0] for r in rows[1:]}=={'1','2'}
            other=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',other,'other')
            for page in PAGES:
                q={'username':'OtherOnly','startdate':'2020-01-01','enddate':'2020-01-02'} if page in ('acct-date.php','acct-username.php') else {}
                a,b=compare(page,q,other);assert 'OtherOnly' in b[1] or page=='acct-hotspot-compare.php'
                assert 'External' not in ' '.join(map(str,Rows(b[1]).rows))
            assert snapshot()==original,'Accounting read mutated data'
            # New validation: nested/array controls and stale export descriptors are refused.
            for page in PAGES:
                req('acct-all.php')
                for key in ('username','startdate','enddate','orderBy','orderType','page','ipaddress','nasipaddress'):
                    status,text=req(page,{key+'[]':'invalid'})
                    assert status==400 and text=='Invalid accounting filters',(page,key,'scalar validation')
                assert json.loads(req('descriptor.php')[1]) is None
            assert req('acct-hotspot-accounting.php',{'hotspot[0][]':'bad'})[0]==400
            for page in ('acct-active.php','acct-hotspot-compare.php','acct-username.php'):
                req('acct-all.php');req(page);assert json.loads(req('descriptor.php')[1]) is None
            for page,q in [('acct-username.php',{'username':'Absent'}),('acct-date.php',{'username':'Absent','startdate':'2020-01-01','enddate':'2020-01-02'}),('acct-hotspot-accounting.php',{'hotspot':'Empty'})]:
                req('acct-all.php');req(page,q);assert json.loads(req('descriptor.php')[1]) is None
            denied=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',denied,'default','9002')
            for page in PAGES:
                assert req(page,session=denied)[0] in (302,403),'ACL bypass'
            # Literal zero/percent/quote/Unicode identities are bound, not rewritten.
            for user in ('0',"O'Reilly%雪",'Amp&Plus+Percent%'):
                safe=user.replace("'","''")
                db("INSERT INTO custom_acct(username,acctsessionid,acctuniqueid,nasipaddress,calledstationid,acctstarttime) VALUES ('"+safe+"','Special','Special"+secrets.token_hex(4)+"','198.51.100.1','unknown','2020-01-01')")
                req('acct-username.php',{'username':user});status,text=req(export,{'reportFormat':'csv','reportType':'accountingGeneric'})
                rows=list(csv.reader(io.StringIO(text)));assert status==200 and len(rows)==2 and rows[1][2]==user
                html=req('acct-username.php',{'username':user})[1]
                for link in Rows(html).links:
                    if 'acct-username.php?username=' in link or 'mng-edit.php?username=' in link or link.startswith('?page='):
                        parsed=urllib.parse.parse_qs(urllib.parse.urlsplit(link).query)
                        if 'username' in parsed:assert parsed['username']==[user],'Identity link was double escaped'
            for hotspot in ('0',"HS'&+%雪"):
                safe=hotspot.replace("'","''");mac='02:01:00:00:00:'+secrets.token_hex(1)
                db("INSERT INTO custom_hs(name,mac) VALUES ('"+safe+"','"+mac+"'); INSERT INTO custom_acct(username,acctsessionid,acctuniqueid,nasipaddress,calledstationid,acctstarttime) VALUES ('SpecialHS','HS','HS"+secrets.token_hex(4)+"','198.51.100.1','"+mac+"','2020-01-01')")
                body=req('acct-hotspot-accounting.php',{'hotspot':hotspot})[1]
                assert 'fileExport.php' in body
                status,text=req(export,{'reportFormat':'csv','reportType':'accountingGeneric'});rows=list(csv.reader(io.StringIO(text)))
                assert status==200 and len(rows)==2 and rows[1][1]==hotspot
                assert any(urllib.parse.parse_qs(urllib.parse.urlsplit(link).query).get('hotspot[]')==[hotspot] for link in Rows(body).links if link.startswith('?orderBy='))
                db("DELETE FROM custom_acct WHERE calledstationid='"+mac+"'; DELETE FROM custom_hs WHERE name='"+safe+"'")
            # Requests containing quotes remain literal LIKE filters, never SQL fragments.
            for page,key in [('acct-ipaddress.php','ipaddress'),('acct-nasipaddress.php','nasipaddress')]:
                status,text=req(page,{key:"192.0.2.1' OR 1=1 --"});assert status==200 and 'Nothing to display' in text
                assert json.loads(req('descriptor.php')[1]) is None
            # BIGINT is preserved in HTML/CSV and ordering, not cast through a float.
            db("INSERT INTO custom_acct(radacctid,username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime) VALUES (9007199254740993,'BigID','Big','Big','198.51.100.1','2020-01-01')")
            body=req('acct-all.php')[1];assert Rows(body).rows[0][0]=='9007199254740993'
            status,text=req(export,{'reportFormat':'csv','reportType':'accountingGeneric'});assert list(csv.reader(io.StringIO(text)))[1][0]=='9007199254740993'
            db('DELETE FROM custom_acct WHERE radacctid=9007199254740993')
            # Count query reflects the exact joined rows even if two hotspots share one MAC.
            db("INSERT INTO custom_hs(name,mac) VALUES ('DuplicateMAC','02:00:00:00:00:10')")
            body=req('acct-all.php')[1];assert 'out of <strong>11</strong>' in body
            db("DELETE FROM custom_hs WHERE name='DuplicateMAC'")
            # Real read-only grants prove reports need neither writes nor SQL transactions.
            reader='reader_'+secrets.token_hex(6);password=secrets.token_hex(24)
            sql("CREATE USER '"+reader+"'@'%' IDENTIFIED BY '"+password+"'; GRANT SELECT ON candidate.* TO '"+reader+"'@'%'")
            configfile=f/'candidate/app/common/includes/daloradius.conf.php'
            configfile.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_USER']='"+reader+"';\n$configValues['CONFIG_DB_PASS']='"+password+"';\n")
            before=snapshot()
            for page in PAGES:
                status,text=req(page,default.get(page,{}));assert status==200 and 'Unable to read' not in text and Rows(text).rows
            assert snapshot()==before
            configfile.write_text(configs['candidate']);sql("DROP USER '"+reader+"'@'%'");reader=password=None
            # Fixture-only synchronization: fail the real data SELECT after the
            # native count succeeded. No production SQL driver or response is mocked.
            helperfile=f/'candidate/app/operators/library/accounting_pages_pdo.php'
            originalhelper=helperfile.read_text()
            needle="return (int)dalo_accounting_execute($pdo, 'SELECT COUNT(*) FROM (' . $sql . ') AS accounting_rows', $bindings)->fetchColumn();"
            hook="""$count = (int)dalo_accounting_execute($pdo, 'SELECT COUNT(*) FROM (' . $sql . ') AS accounting_rows', $bindings)->fetchColumn();
    if (isset($_GET['fixture_late_error'])) {
        if (basename($_SERVER['PHP_SELF']) === 'acct-active.php') {
            $pdo->exec('ALTER TABLE custom_check RENAME COLUMN value TO absent_value');
        } elseif (basename($_SERVER['PHP_SELF']) === 'acct-hotspot-compare.php') {
            $pdo->exec('ALTER TABLE custom_acct RENAME COLUMN calledstationid TO absent_station');
        } else {
            $pdo->exec('ALTER TABLE custom_acct RENAME COLUMN acctterminatecause TO absent_cause');
        }
    }
    return $count;"""
            assert originalhelper.count(needle)==1
            helperfile.write_text(originalhelper.replace(needle,hook));before=snapshot()
            for page in PAGES:
                q=dict(default.get(page,{}),fixture_late_error='1')
                status,text=req(page,q)
                if page=='acct-active.php':db('ALTER TABLE custom_check RENAME COLUMN absent_value TO value')
                elif page=='acct-hotspot-compare.php':db('ALTER TABLE custom_acct RENAME COLUMN absent_station TO calledstationid')
                else:db('ALTER TABLE custom_acct RENAME COLUMN absent_cause TO acctterminatecause')
                assert status==200 and 'Unable to read accounting records' in text and 'SQLSTATE' not in text,(page,'late read error')
                assert json.loads(req('descriptor.php')[1]) is None and 'fileExport.php' not in text
                assert snapshot()==before
            helperfile.write_text(originalhelper)
            # Candidate direct reports never invoke PEAR; R20 summaries remain independent.
            for rel in ('db_open.php','db_close.php'):(f/'candidate/app/common/includes'/rel).write_text("<?php throw new RuntimeException('Legacy database tripwire');")
            for page in PAGES:
                if page not in ('acct-date.php','acct-username.php'):
                    status,text=req(page);assert status==200 and 'Nothing to display' not in text and Rows(text).rows
            # No data and missing tables clear descriptors; exceptions never show SQL/values.
            db('RENAME TABLE custom_acct TO hidden_acct')
            for page in ('acct-all.php','acct-ipaddress.php','acct-nasipaddress.php','acct-hotspot-accounting.php','acct-active.php','acct-hotspot-compare.php'):
                status,text=req(page);assert status==200 and 'Unable to read accounting records' in text
                assert 'SQLSTATE' not in text and json.loads(req('descriptor.php')[1]) is None
            db('RENAME TABLE hidden_acct TO custom_acct')
            db('CREATE TABLE empty_acct LIKE custom_acct')
            configfile=f/'candidate/app/common/includes/daloradius.conf.php'
            configfile.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_TBL_RADACCT']='empty_acct';\n")
            for page in ('acct-all.php','acct-ipaddress.php','acct-nasipaddress.php','acct-hotspot-accounting.php','acct-active.php','acct-hotspot-compare.php'):
                status,text=req(page);assert status==200 and 'Nothing to display' in text
                assert json.loads(req('descriptor.php')[1]) is None
            for value in ('bad;table','0','-1'):
                key='CONFIG_DB_TBL_RADACCT' if value=='bad;table' else 'CONFIG_IFACE_TABLES_LISTING'
                configfile.write_text(configs['candidate']+'\n$configValues['+repr(key)+']='+repr(value)+';\n')
                status,text=req('acct-all.php');assert status==200 and 'Unable to read accounting records' in text and 'SQLSTATE' not in text
            configfile.write_text(configs['candidate'])
            logs=subprocess.run(['docker','logs',h.WEB],text=True,capture_output=True,check=True);text=logs.stdout+logs.stderr
            assert not any('/fixtures/candidate/' in line and any(mark in line for mark in ('PHP Fatal error:','PHP Warning:','PHP Deprecated:')) for line in text.splitlines()),'Candidate PHP diagnostics (details suppressed)'
            print('PASS '+str(comparisons)+' PEAR/PDO display/full-CSV comparisons; all eight reports, date boundaries, NULL, empty, configured tables, named routing, pagination and every sort',flush=True)
            print('PASS malformed filters, ACL, exact special identities, stale-export clearing, joined counts, late/missing/invalid/empty reads, SELECT-only grants, no direct PEAR calls and unchanged accounting state',flush=True)
        finally:
            run('docker','rm','-f',h.WEB,h.DB,check=False);run('docker','network','rm',h.NETWORK,check=False)
            subprocess.run(['sudo','-n','chown','-R',str(os.getuid())+':'+str(os.getgid()),str(f)],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
            assert not [n for n in run('docker','ps','-a','--format','{{.Names}}').splitlines() if n in (h.WEB,h.DB)]
            assert h.NETWORK not in run('docker','network','ls','--format','{{.Name}}').splitlines()
            print('CLEANUP R11 fixtures removed',flush=True)
if __name__=='__main__':main()
