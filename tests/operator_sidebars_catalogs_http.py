#!/usr/bin/env python3
"""R02c: native catalog and sidebar parity, paging and independent ownership."""
from html.parser import HTMLParser
from urllib.parse import urlencode
import json
import os
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap
import re
import secrets
import shutil
import subprocess
import tempfile
from operator_login_http import Client, wait_for

ROOT=Path(__file__).resolve().parents[1]
BASE='451a633c92bd5530c98bf357b80ee49d115c95f0'
SUFFIX=secrets.token_hex(5)
DB,WEB,NETWORK=['pdo-r02c-'+kind+'-'+SUFFIX for kind in ('db','web','net')]
IMAGE=os.environ.get('SELECTOR_WEB_IMAGE','lirantal/daloradius')
FUNCTIONS=['get_invoice_status_id','get_active_plans','get_proxies','get_ippools','get_huntgroups',
'get_groups','get_payment_types','get_realms','get_online_users','get_users','get_nas_names','get_plans',
'get_ratenames','get_groups_that_have_users','get_users_that_have_groups','get_attributes','get_vendors',
'get_hotspots','get_batch_names']


def run(*args,input=None,check=True):
    p=subprocess.run(args,input=input,text=True,capture_output=True,timeout=180)
    if check and p.returncode:
        codes=re.findall(r'ERROR (\d+) \(([A-Z0-9]+)\)',p.stderr)
        raise RuntimeError('Isolated fixture operation failed; SQL categories='+repr(codes)+'; details suppressed')
    return (p.stdout+p.stderr if args[:2]==('docker','logs') else p.stdout).strip()


def sql(query,database='radius'):
    return run('docker','exec','-i',DB,'mariadb','-uroot','-N','-B',database,input="SET SESSION sql_mode=''; "+query)



class Rows(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True);self.in_body=False;self.cell=None;self.row=None;self.rows=[];self.controls=[];self.actions=[]
    def handle_starttag(self,tag,attrs):
        a=dict(attrs)
        if tag=='form' and a.get('name')=='listall':self.actions.append((a.get('action'),a.get('method')))
        if tag=='tbody':self.in_body=True
        if self.in_body:
            if tag=='tr':self.row=[]
            elif tag=='td':self.cell=[]
            elif tag=='input' and a.get('type')=='checkbox':self.controls.append((a.get('name'),a.get('value')))
    def handle_data(self,data):
        if self.cell is not None:self.cell.append(data)
    def handle_endtag(self,tag):
        if tag=='td' and self.cell is not None:
            self.row.append(' '.join(''.join(self.cell).split()));self.cell=None
        elif tag=='tr' and self.row is not None:
            self.rows.append(self.row);self.row=None
        elif tag=='tbody':self.in_body=False


def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    user='selectors_'+secrets.token_hex(5);password=secrets.token_hex(24)
    sample=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text()
    provider=(ROOT/'app/operators/include/management/selectbox_read.php').read_text()
    keys=list(dict.fromkeys(re.findall(r"'(CONFIG_DB_TBL_[A-Z]+)'",provider)))
    originals={k:re.search(r"\$configValues\['"+k+r"'\]\s*=\s*['\"]([^'\"]+)['\"]",sample).group(1) for k in keys}
    mapping={k:'selector_'+str(i) for i,k in enumerate(keys)}
    with tempfile.TemporaryDirectory(prefix='pdo-r02c-',dir=scratch) as directory:
        fixture=Path(directory)
        for version in ('base','candidate'):
            dest=fixture/version
            shutil.copytree(ROOT/'app',dest/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if version=='base':
                restore_pear_bootstrap((dest / 'app').parent, BASE)
                rel='app/operators/include/management/populate_selectbox.php'
                (dest/rel).write_text(run('git','show',BASE+':'+rel)+'\n')
                for rel in ('app/operators/mng-rad-proxys-list.php','app/operators/mng-rad-realms-list.php','app/operators/include/menu/sidebar/bill/invoice.php','app/operators/include/menu/sidebar/mng/rad-groups.php'):
                    (dest/rel).write_text(run('git','show',BASE+':'+rel)+'\n')
            else:
                (dest/'app/common/includes/db_open.php').write_text("<?php throw new RuntimeException('Legacy selector tripwire');")
            conf=sample.replace('?>','')+'''
$configValues['CONFIG_DB_HOST']=getenv('SELECTOR_DB');$configValues['CONFIG_DB_USER']=getenv('SELECTOR_USER');
$configValues['CONFIG_DB_PASS']=getenv('SELECTOR_PASS');$configValues['CONFIG_DB_ENGINE']='mysqli';$configValues['CONFIG_DB_NAME']='radius';
$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>getenv('SELECTOR_DB'),
'Username'=>getenv('SELECTOR_USER'),'Password'=>getenv('SELECTOR_PASS'),'Database'=>'radius_other','Port'=>'3306');
'''
            for key,value in mapping.items():conf+="$configValues['"+key+"']='"+value+"';\n"
            conf+="$configValues['CONFIG_IFACE_TABLES_LISTING']=3; $configValues['CONFIG_IFACE_TABLES_LISTING_NUM']='yes'; if(isset($_GET['bad_page_size'])){$configValues['CONFIG_IFACE_TABLES_LISTING']=0;} if(isset($_GET['bad_table'])){$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']='selector_bad; DROP TABLE selector_bad';}\n"
            conf+="if(isset($_GET['bad_catalog'])){$configValues['CONFIG_DB_TBL_DALOPROXYS']='unsafe;table';$configValues['CONFIG_DB_TBL_DALOREALMS']='unsafe;table';}\n"
            (dest/'app/common/includes/daloradius.conf.php').write_text(conf)
            (dest/'app/operators/selector-fixture.php').write_text("<?php\ninclude 'library/checklogin.php';include '../common/includes/config_read.php';include 'lang/main.php';\n$operator_perm_file='selector_fixture';include 'library/check_operator_perm.php';\nrequire_once '../common/includes/pdo_connection.php';\n$pdo=dalo_pdo_connect($configValues,$_SESSION['location_name'] ?? 'default');$pdo->beginTransaction();$id=spl_object_id($pdo);$caller_pdo=$pdo;\n$sidebar=$_GET['sidebar'] ?? 'invoice';\nif($sidebar==='invoice') {include 'include/menu/sidebar/bill/invoice.php';$values=array($menu_users,$menu_invoice_status_id,$menu);}\nelse {include 'include/menu/sidebar/mng/rad-groups.php';$values=array($menu_groups,$radgroupcheck_options,$radgroupreply_options,$menu);}\n$kept=$pdo instanceof PDO && $pdo->inTransaction() && spl_object_id($pdo)===$id && $pdo->query('SELECT 1')->fetchColumn()==1;\nif($caller_pdo->inTransaction()){$caller_pdo->rollBack();}header('Content-Type: application/json');echo json_encode(array('value'=>$values,'caller_kept'=>$kept));\n")
            (dest/'app/operators/log-channel.php').write_text("<?php error_log('SELECTOR_LOG_CHANNEL');echo 'ok';")
        (fixture/'session.php').write_text('''<?php
$p=json_decode(stream_get_contents(STDIN),true);session_name('daloradius_operator_sid');session_id($p['sid']);session_start();
$_SESSION=array('time'=>time(),'daloradius_logged_in'=>true,'operator_id'=>$p['id'],'operator_user'=>'fixture-actor','location_name'=>$p['location']);session_write_close();
''')
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql','mariadb-daloradius-dictionaries.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            for key,original in originals.items():
                sql('CREATE TABLE '+mapping[key]+' LIKE '+original)
            def table(key):return mapping['CONFIG_DB_TBL_'+key]
            sql("INSERT INTO "+table('DALOBILLINGPLANS')+"(id,planName,planActive) VALUES(10,'active É%+','yes'),(11,'duplicate','yes'),(12,'duplicate','yes'),(13,'inactive','no'); "+
                "INSERT INTO "+table('DALOBILLINGINVOICESTATUS')+"(id,value) VALUES(7,'paid'),(8,'<draft>'); "+
                "INSERT INTO "+table('DALOPROXYS')+"(id,proxyname) VALUES(9,'proxy O''Reilly%+'); "+
                "INSERT INTO "+table('RADIPPOOL')+"(id,pool_name,framedipaddress) VALUES(1,'pool','10.0.0.2'),(2,'pool','10.0.0.1'); "+
                "INSERT INTO "+table('RADHG')+"(id,groupname,nasipaddress,nasportid) VALUES(1,'hunt','10.0.0.3','1'); "+
                "INSERT INTO "+table('RADGROUPCHECK')+"(groupname,attribute,op,value) VALUES('both','Auth-Type',':=','Accept'),('0','Auth-Type',':=','Accept'); "+
                "INSERT INTO "+table('RADGROUPREPLY')+"(groupname,attribute,op,value) VALUES('both','Reply-Message',':=','welcome'),('reply É%+','Reply-Message',':=','welcome'); "+
                "INSERT INTO "+table('RADUSERGROUP')+"(username,groupname,priority) VALUES('mapped','mapped-only',1),('mapped','both',2); "+
                "INSERT INTO "+table('RADCHECK')+"(username,attribute,op,value) VALUES('account O''Reilly%+','Auth-Type',':=','Accept'),('account O''Reilly%+','Session-Timeout',':=','100'); "+
                "INSERT INTO "+table('RADACCT')+"(acctsessionid,acctuniqueid,username,nasipaddress,acctstarttime,acctstoptime) VALUES('s1','one','online','10.0.0.4','2026-01-01',NULL),('s2','two','zero-date','10.0.0.4','2026-01-01','0000-00-00 00:00:00'),('s3','three','online','10.0.0.4','2026-01-01',NULL),('s4','four','offline','10.0.0.4','2026-01-01','2026-01-02'); "+
                "INSERT INTO "+table('DALOUSERINFO')+"(username) VALUES('info-only'); "+
                "INSERT INTO "+table('DALOUSERBILLINFO')+"(username) VALUES('billing-only'); "+
                "INSERT INTO "+table('DALOREALMS')+"(realmname) VALUES('realm É%+'); "+
                "INSERT INTO "+table('RADNAS')+"(nasname,shortname) VALUES('10.0.0.4','fixture'); "+
                "INSERT INTO "+table('DALOPAYMENTTYPES')+"(value) VALUES('card'),('cash'); "+
                "INSERT INTO "+table('DALOBILLINGRATES')+"(rateName) VALUES('rate A'); "+
                "INSERT INTO "+table('DALODICTIONARY')+"(Vendor,Attribute,Type) VALUES('vendor','Example','string'),('vendor','Other','string'); "+
                "INSERT INTO "+table('DALOHOTSPOTS')+"(name) VALUES('site A'); "+
                "INSERT INTO "+table('DALOBATCHHISTORY')+"(batch_name) VALUES('batch A'); "+
                "INSERT INTO operators_acl(operator_id,file,access) VALUES(9001,'selector_fixture',1),(9002,'selector_fixture',0); CREATE DATABASE radius_other; ")
            for key,original in originals.items():
                sql('CREATE TABLE radius_other.'+mapping[key]+' LIKE '+mapping[key])
            sql('CREATE TABLE radius_other.operators_acl LIKE operators_acl; INSERT INTO radius_other.operators_acl SELECT * FROM operators_acl; '+
                "INSERT INTO radius_other."+table('DALOBILLINGPLANS')+"(id,planName,planActive) VALUES(99,'other backend','yes'); "+
                f"CREATE USER '{user}'@'%' IDENTIFIED BY '{password}'; GRANT SELECT ON radius.* TO '{user}'@'%'; GRANT SELECT ON radius_other.* TO '{user}'@'%';")
            sql("INSERT INTO operators_acl(operator_id,file,access) VALUES(9001,'mng_rad_proxys_list',1),(9001,'mng_rad_realms_list',1),(9002,'mng_rad_proxys_list',0),(9002,'mng_rad_realms_list',0); INSERT INTO radius_other.operators_acl SELECT * FROM operators_acl WHERE operator_id IN (9001,9002) AND file IN ('mng_rad_proxys_list','mng_rad_realms_list');")
            for key,column in (('DALOPROXYS','proxyname'),('DALOREALMS','realmname')):
                sql('DELETE FROM '+table(key))
                for i,name in enumerate(['plain A',"O'Reilly & 50%+",'Unicode É','<b>display</b>','plain E','plain F','plain G'],1):
                    label=name.replace("'","''")
                    sql("INSERT INTO "+table(key)+"(id,"+column+",creationdate,creationby,updatedate,updateby) VALUES("+str(i)+",'"+label+"','2026-01-0"+str(i)+"','creator "+str(i)+"','2026-02-0"+str(i)+"','updater "+str(i)+"')")
            env=os.environ.copy();env.update(SELECTOR_DB=DB,SELECTOR_USER=user,SELECTOR_PASS=password,SELECTOR_FUNCTIONS=json.dumps(FUNCTIONS))
            p=subprocess.run(['docker','run','-d','--name',WEB,'--network',NETWORK,'-e','SELECTOR_DB','-e','SELECTOR_USER','-e','SELECTOR_PASS','-e','SELECTOR_FUNCTIONS',
                '-v',f'{fixture}:/fixtures','-w','/fixtures','--entrypoint','php',IMAGE,'-d','display_errors=0','-d','opcache.enable=0','-S','0.0.0.0:8080','-t','/fixtures'],
                env=env,text=True,capture_output=True,timeout=60)
            assert p.returncode==0,'PHP startup failed'
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            origin='http://'+ip+':8080/'
            wait_for(lambda:Client(origin+'candidate/app/operators/').request('login.php')[0]==200,'PHP HTTP')
            print('RUNTIME: PHP '+run('docker','exec',WEB,'php','-r','echo PHP_VERSION;')+'; MariaDB '+sql('SELECT VERSION()'))
            def client(version='candidate',location='default',identity=9001):
                c=Client(origin+version+'/app/operators/');c.sid=secrets.token_hex(16)
                run('docker','exec','-i',WEB,'php','/fixtures/session.php',input=json.dumps({'sid':c.sid,'id':identity,'location':location}))
                return c
            baseline,candidate=client('base'),client()
            def sidebar(c,name,expected_kept=True):
                r=c.request('selector-fixture.php?sidebar='+name)
                assert r[0]==200,'Sidebar failed: '+name+' status='+str(r[0])
                v=json.loads(r[3])
                assert v['caller_kept']==expected_kept,'Unexpected caller preservation/disposal contract'
                return v['value']
            frozen=sql('CHECKSUM TABLE '+','.join(mapping.values()))
            for location in ('default','other'):
                old,current=client('base',location),client(location=location)
                for name in ('invoice','groups'):
                    assert sidebar(old,name,name=='groups')==sidebar(current,name),'Sidebar values/descriptors mismatch'
            for entity,columns in (('proxys',['id','proxyname','creationdate','creationby','updatedate','updateby']),('realms',['realmname','creationdate','creationby','updatedate','updateby'])):
                page='mng-rad-'+entity+'-list.php'
                for column in columns:
                    for direction in ('asc','desc'):
                        coverage=[]
                        for n in (1,2,3):
                            query=urlencode({'orderBy':column,'orderType':direction,'page':n})
                            a=baseline.request(page+'?'+query);b=candidate.request(page+'?'+query)
                            assert a[0]==b[0]==200,'Catalog failed: '+entity+'/'+column+' statuses='+str((a[0],b[0]))
                            x,y=Rows(),Rows();x.feed(a[3]);y.feed(b[3])
                            assert x.rows==y.rows and x.controls==y.controls,'Catalog row/control parity failed: '+entity+'/'+column
                            assert len(y.rows)==(3 if n<3 else 1),'Wrong page size'
                            assert y.actions==x.actions and len(y.actions)==1,'Deletion form changed or missing'
                            assert y.actions[0][0]=='mng-rad-'+entity+'-del.php' and y.actions[0][1].lower()=='post'
                            expected_field='item[]' if entity=='proxys' else 'realmname[]'
                            assert all(field==expected_field for field,value in y.controls),'Selection field changed'
                            coverage.extend(y.controls)
                        assert len(coverage)==len(set(coverage))==7,'Paging omitted or repeated records'
                for query in ('','?page=999','?page=0','?page=-1','?orderBy=invalid&orderType=INVALID'):
                    a=baseline.request(page+query);b=candidate.request(page+query)
                    x,y=Rows(),Rows();x.feed(a[3]);y.feed(b[3]);assert x.rows==y.rows
                old,current=client('base','other'),client(location='other')
                assert 'Nothing to display' in old.request(page)[3] and 'Nothing to display' in current.request(page)[3]
                assert Client(origin+'candidate/app/operators/').request(page)[0]==302
                assert client(identity=9002).request(page)[0]==302
                assert candidate.request(page+'?orderType[]=bad')[0]==200
                for query in ('?bad_catalog=1','?bad_page_size=1'):
                    r=candidate.request(page+query);assert r[0]==200 and 'Unable to load catalog' in r[3]
                    parser=Rows();parser.feed(r[3]);assert not parser.rows,'Failed catalog partially rendered'
                name=table('DALOPROXYS' if entity=='proxys' else 'DALOREALMS')
                sql('RENAME TABLE '+name+' TO saved_catalog')
                try:
                    assert 'Unable to load catalog' in candidate.request(page)[3]
                    sql('CREATE TABLE '+name+'(id int PRIMARY KEY) ENGINE=InnoDB; INSERT INTO '+name+' VALUES(1)')
                    try:
                        r=candidate.request(page);assert 'Unable to load catalog' in r[3]
                        parser=Rows();parser.feed(r[3]);assert not parser.rows,'Late fetch failure partially rendered'
                    finally:sql('DROP TABLE '+name)
                finally:sql('RENAME TABLE saved_catalog TO '+name)
            assert sql('CHECKSUM TABLE '+','.join(mapping.values()))==frozen,'Catalog/sidebar reads mutated data'
            for key,page in (('DALOPROXYS','mng-rad-proxys-list.php'),('DALOREALMS','mng-rad-realms-list.php')):
                sql('UPDATE '+table(key)+' SET creationdate=NULL,creationby=NULL,updatedate=NULL,updateby=NULL')
                r=candidate.request(page);assert r[0]==200
                parser=Rows();parser.feed(r[3]);assert len(parser.rows)==3
                assert all(row[2:]==['']*4 for row in parser.rows),'NULL display values not normalized'
            print('PASS: real invoice/group sidebar descriptors and options match PEAR; caller connection/transaction preserved; populated/empty named backends')
            print('PASS: both real catalog pages match six/five sort columns, both directions and three pages; all seven controls covered once; deletion actions, special-character labels, defaults and invalid navigation preserved')
            print('PASS: auth/ACL, array sort, invalid config/missing SQL table are safe; failure before table output, unchanged SQL state and unconditional legacy-open tripwire')
            assert candidate.request('log-channel.php')[0]==200
            logs=run('docker','logs',WEB)
            assert 'SELECTOR_LOG_CHANNEL' in logs,'PHP stderr capture missing'
            assert not re.search(r'(?:Fatal error|Parse error|Warning:|Uncaught (?:Error|TypeError|PDOException|RuntimeException))',logs), 'Unexpected native PHP diagnostic'
            assert password not in logs and user not in logs
            print('PASS: SELECT-only grants and positively verified clean PHP stdout/stderr')
        finally:
            for name in (WEB,DB):run('docker','rm','-f',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            assert not run('docker','ps','-a','-q','--filter','name=^/'+DB+'$')
            assert not run('docker','ps','-a','-q','--filter','name=^/'+WEB+'$')
            assert not run('docker','network','ls','-q','--filter','name=^'+NETWORK+'$')
            subprocess.run(['sudo','-n','chown','-R',str(os.getuid())+':'+str(os.getgid()),str(fixture)],capture_output=True,timeout=30)
    print('PASS: ephemeral SQL/config/session/connection/container/network resources removed; no deployment')


if __name__=='__main__':main()
