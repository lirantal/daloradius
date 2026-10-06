#!/usr/bin/env python3
"""R01c: native PEAR/PDO catalog parity and AJAX legacy-open removal."""
import json
import os
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap
import secrets
import shutil
import subprocess
import tempfile
from html.parser import HTMLParser
from urllib.parse import urlencode
from operator_login_http import Client, wait_for

ROOT = Path(__file__).resolve().parents[1]
BASE = '523113375b234f56035b8e9dd92d2c6c3ffcf776'
SUFFIX = secrets.token_hex(5)
DB, WEB, NETWORK = ['pdo-r01c-' + label + '-' + SUFFIX for label in ('db','web','net')]
IMAGE = os.environ.get('CATALOG_WEB_IMAGE','lirantal/daloradius')


def run(*args, input=None, check=True):
    result = subprocess.run(args,input=input,text=True,capture_output=True,timeout=180)
    if check and result.returncode:
        raise RuntimeError('Fixture operation failed; details suppressed')
    return (result.stdout + result.stderr if args[:2]==('docker','logs') else result.stdout).strip()


def sql(query, database='radius'):
    return run('docker','exec','-i',DB,'mariadb','-uroot','-N','-B',database,input=query)


class Table(HTMLParser):
    def __init__(self, body):
        super().__init__(); self.rows=[]; self.row=None; self.cell=None; self.csrf=None; self.feed(body)
    def handle_starttag(self,tag,attrs):
        attrs=dict(attrs)
        if tag=='tr': self.row=[]
        if tag=='td': self.cell=''
        if tag=='input' and attrs.get('name')=='csrf_token': self.csrf=attrs.get('value')
    def handle_data(self,data):
        if self.cell is not None: self.cell+=data
    def handle_endtag(self,tag):
        if tag=='td' and self.row is not None:
            self.row.append(self.cell.strip()); self.cell=None
        if tag=='tr':
            if self.row and len(self.row)==6: self.rows.append(tuple(self.row))
            self.row=None


def main():
    scratch=Path.home()/'.hermes/cache/scratch'; scratch.mkdir(parents=True,exist_ok=True)
    username='catalog_'+secrets.token_hex(5); password=secrets.token_hex(24)
    with tempfile.TemporaryDirectory(prefix='pdo-r01c-',dir=scratch) as directory:
        fixture=Path(directory)
        for version in ('base','candidate'):
            dest=fixture/version
            shutil.copytree(ROOT/'app',dest/'app',symlinks=True,
                            ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if version=='base':
                restore_pear_bootstrap((dest / 'app').parent, BASE)
                for rel in ('app/operators/config-operators-list.php','app/operators/library/ajax/user_actions.php',
                            'app/operators/include/management/populate_selectbox.php'):
                    (dest/rel).write_text(run('git','show',BASE+':'+rel)+'\n')
            else:
                (dest/'app/common/includes/db_open.php').write_text(
                    "<?php throw new RuntimeException('Legacy provider tripwire');")
            config=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            config+='''
$configValues['CONFIG_DB_HOST']=getenv('CATALOG_DB');$configValues['CONFIG_DB_USER']=getenv('CATALOG_USER');
$configValues['CONFIG_DB_PASS']=getenv('CATALOG_PASS');$configValues['CONFIG_DB_NAME']='radius';
$configValues['CONFIG_DB_ENGINE']='mysqli';$configValues['CONFIG_DB_TBL_DALOOPERATORS']='catalog_custom';
$configValues['CONFIG_IFACE_TABLES_LISTING']=2;$configValues['CONFIG_IFACE_TABLES_LISTING_NUM']='yes';
$configValues['CONFIG_LOG_PAGES']='no';$configValues['CONFIG_LOG_QUERIES']='no';$configValues['CONFIG_LOG_ACTIONS']='no';
$configValues['CONFIG_DEBUG_SQL']='no';$configValues['CONFIG_DEBUG_SQL_ONPAGE']='no';
$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>getenv('CATALOG_DB'),
'Username'=>getenv('CATALOG_USER'),'Password'=>getenv('CATALOG_PASS'),'Database'=>'radius_other','Port'=>'3306');
if(isset($_GET['bad_table'])){$configValues['CONFIG_DB_TBL_DALOOPERATORS']='catalog_custom; SELECT 1';}
if(isset($_GET['bad_size'])){$configValues['CONFIG_IFACE_TABLES_LISTING']=0;}
'''
            (dest/'app/common/includes/daloradius.conf.php').write_text(config)
            (dest/'app/operators/log-channel.php').write_text("<?php error_log('CATALOG_LOG_CHANNEL');echo 'ok';")
            (dest/'app/operators/catalog-selector.php').write_text('''<?php
include 'library/checklogin.php';include '../common/includes/config_read.php';include 'lang/main.php';
include 'include/management/populate_selectbox.php';require_once '../common/includes/pdo_connection.php';
$pdo=dalo_pdo_connect($configValues,$_SESSION['location_name'] ?? 'default');$pdo->beginTransaction();
$before=spl_object_id($pdo);$names=get_operators();
echo json_encode(array('names'=>$names,'caller_kept'=>spl_object_id($pdo)===$before && $pdo->inTransaction() && $pdo->query('SELECT 1')->fetchColumn()==1));
$pdo->rollBack();
''')
        (fixture/'session.php').write_text('''<?php
$p=json_decode(stream_get_contents(STDIN),true);session_name('daloradius_operator_sid');session_id($p['sid']);session_start();
$_SESSION=array('time'=>time(),'daloradius_logged_in'=>true,'operator_id'=>$p['id'],
'operator_user'=>'fixture-actor','location_name'=>$p['location'],'csrf_token'=>$p['csrf']);session_write_close();
''')
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            sql("CREATE TABLE catalog_custom(id INT PRIMARY KEY,username VARCHAR(128),auth_source VARCHAR(16),external_id VARCHAR(128),firstname VARCHAR(128),lastname VARCHAR(128),title VARCHAR(128)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; "
                "INSERT INTO catalog_custom VALUES (1,'alpha','local',NULL,'Alice','Able','A'),"
                "(2,'bravo','ldap','','Bob','Baker','B'),(3,'O''Reilly%+É','ldap','linked','Étoile','C','C'),"
                "(4,'<script>marker</script>','local','linked','<b>Dave</b>','D','<img src=x>'),"
                "(5,'echo','local',NULL,'Eve','E','E'); "
                "INSERT INTO operators_acl(operator_id,file,access) VALUES(9001,'config_operators_list',1),"
                "(9002,'config_operators_list',0),(9001,'mng_search',1),(9001,'mng_edit',1); "
                "CREATE DATABASE radius_other; CREATE TABLE radius_other.catalog_custom LIKE catalog_custom; "
                "INSERT INTO radius_other.catalog_custom VALUES(99,'other-site','ldap','linked','Other','Location','Z'); "
                "CREATE TABLE radius_other.operators_acl LIKE operators_acl; INSERT INTO radius_other.operators_acl SELECT * FROM operators_acl; "
                "INSERT INTO radusergroup(username,groupname,priority) VALUES('sample','daloRADIUS-Disabled-Users',-1); "
                f"CREATE USER '{username}'@'%' IDENTIFIED BY '{password}'; GRANT SELECT ON radius.* TO '{username}'@'%'; "
                f"GRANT SELECT ON radius_other.* TO '{username}'@'%';")
            environment=os.environ.copy(); environment.update(CATALOG_DB=DB,CATALOG_USER=username,CATALOG_PASS=password)
            result=subprocess.run(['docker','run','-d','--name',WEB,'--network',NETWORK,
                '-e','CATALOG_DB','-e','CATALOG_USER','-e','CATALOG_PASS','-v',f'{fixture}:/fixtures',
                '-w','/fixtures','--entrypoint','php',IMAGE,'-d','display_errors=0','-d','opcache.enable=0',
                '-S','0.0.0.0:8080','-t','/fixtures'],env=environment,text=True,capture_output=True,timeout=60)
            assert result.returncode==0,'PHP fixture startup failed'
            address=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            origin='http://'+address+':8080/'
            wait_for(lambda:Client(origin+'candidate/app/operators/').request('login.php')[0]==200,'PHP HTTP')
            print('RUNTIME: PHP '+run('docker','exec',WEB,'php','-r','echo PHP_VERSION;')+'; MariaDB '+sql('SELECT VERSION()'))
            csrf=secrets.token_hex(32)
            def client(version='candidate',identity=9001,location='default'):
                c=Client(origin+version+'/app/operators/');c.sid=secrets.token_hex(16)
                run('docker','exec','-i',WEB,'php','/fixtures/session.php',input=json.dumps(
                    {'sid':c.sid,'id':identity,'location':location,'csrf':csrf}))
                return c
            base,candidate=client('base'),client()
            def get(c,params=None):
                return c.request('config-operators-list.php'+('?' + urlencode(params) if params else ''))
            initial=sql('SELECT * FROM catalog_custom ORDER BY id')
            for sort in ('id','username','auth_source','identity_status','fullname','title'):
                for direction in ('asc','desc'):
                    allrows=[]
                    for page in (1,2,3):
                        params={'orderBy':sort,'orderType':direction,'page':page}
                        a,b=get(base,params),get(candidate,params)
                        assert a[0]==b[0]==200,'Catalog status mismatch'
                        left,right=Table(a[3]),Table(b[3])
                        assert left.rows==right.rows, ('Catalog parity failed: '+str((sort,direction,page,len(left.rows),len(right.rows)))+' cells '+str([[a==b for a,b in zip(l,r)] for l,r in zip(left.rows,right.rows)]))
                        assert bool(left.csrf)==bool(right.csrf),'Deletion form CSRF parity differs'
                        assert 'config-operators-del.php' in b[3] and 'operator_username[]' in b[3]
                        assert len(right.rows)==(1 if page==3 else 2),'Page row count incorrect'
                        allrows+=right.rows
                    assert len(allrows)==5,'Incomplete catalog pagination'
            assert Table(get(candidate)[3]).rows==Table(get(base)[3]).rows
            for params in ({'page':999},{'orderBy':'unknown','orderType':'unknown'},{'page':-1}):
                assert Table(get(candidate,params)[3]).rows==Table(get(base,params)[3]).rows
            response=get(candidate,{'page':2,'orderBy':'id','orderType':'desc'})
            assert '<script>marker</script>' not in response[3] and '&lt;script&gt;marker&lt;/script&gt;' in response[3]
            assert Table(get(client(location='other'))[3]).rows==Table(get(client('base',location='other'))[3]).rows
            assert 'other-site' in get(client(location='other'))[3]
            assert get(Client(origin+'candidate/app/operators/'))[0]==302
            assert get(client(identity=9002))[0]==302
            print('PASS: six sort keys/both directions, exact PEAR/PDO row parity, count/pagination, HTML escaping, CSRF/delete controls, auth/ACL and named location')
            for location in ('default','other'):
                a=client('base',location=location).request('catalog-selector.php')
                b=client(location=location).request('catalog-selector.php')
                assert a[0]==b[0]==200 and json.loads(a[3])==json.loads(b[3])
                assert json.loads(b[3])['caller_kept'] and len(json.loads(b[3])['names'])==(5 if location=='default' else 1)
            print('PASS: independent operator selector parity and caller-owned PDO transaction preserved')
            ajax='library/ajax/user_actions.php?action=checkDisabled&username=sample'
            a,b=base.request(ajax),candidate.request(ajax)
            assert a[0]==b[0]==200 and json.loads(a[3])==json.loads(b[3])
            assert json.loads(b[3])['disabled'] is True
            assert candidate.request('library/ajax/user_actions.php?action=unknown&username=sample')[0]==400
            assert client(identity=9002).request(ajax)[0]==403
            assert candidate.request('library/ajax/user_actions.php',
                [('action','userDisable'),('username','sample'),('csrf_token','invalid')])[0]==403
            print('PASS: real AJAX JSON/permission/CSRF contracts without any legacy open; SELECT-only database grants')
            # Existing NULL rendering and malformed-sort failures are separate baseline defects.
            sql('UPDATE catalog_custom SET firstname=NULL,title=NULL WHERE id=5')
            response=get(candidate)
            assert response[0]==200 and len(Table(response[3]).rows)==2
            sql("UPDATE catalog_custom SET firstname='Eve',title='E' WHERE id=5")
            assert get(candidate,{'orderType[]':'asc','orderBy[]':'id'})[0]==200
            sql('RENAME TABLE catalog_custom TO catalog_saved; CREATE TABLE catalog_custom LIKE catalog_saved')
            try:
                for c in (base,candidate):
                    assert 'Nothing to display' in get(c)[3] and not Table(get(c)[3]).rows
            finally:
                sql('DROP TABLE catalog_custom; RENAME TABLE catalog_saved TO catalog_custom')
            for params in ({'bad_table':1},{'bad_size':1}):
                response=get(candidate,params)
                assert response[0]==200 and 'Unable to load operators.' in response[3] and not Table(response[3]).rows
                assert 'SQLSTATE' not in response[3] and password not in response[3] and username not in response[3]
            sql('RENAME TABLE catalog_custom TO catalog_saved')
            try:
                response=get(candidate)
                assert 'Unable to load operators.' in response[3] and not Table(response[3]).rows
            finally:
                sql('RENAME TABLE catalog_saved TO catalog_custom')
            assert sql('SELECT * FROM catalog_custom ORDER BY id')==initial,'Catalog read modified rows'
            assert candidate.request('log-channel.php')[0]==200
            logs=run('docker','logs',WEB)
            assert 'CATALOG_LOG_CHANNEL' in logs,'PHP stderr not captured'
            assert 'PHP Fatal error' not in logs and 'PHP Warning' not in logs,'Unexpected PHP failure before baseline characterization'
            assert password not in logs and username not in logs,'Connection material reached logs'
            assert get(base,{'orderType[]':'asc'})[0]==500,'Baseline array type failure not reproduced'
            logs=run('docker','logs',WEB)
            assert 'strtolower(): Argument' in logs,'Baseline array failure signature missing'
            assert password not in logs and username not in logs
            print('PASS: NULL-safe candidate, characterized baseline sort-array failure, empty catalog, invalid config/SQL redaction and unchanged database state')
        finally:
            for container in (WEB,DB):run('docker','rm','-f',container,check=False)
            run('docker','network','rm',NETWORK,check=False)
            for container in (WEB,DB):
                assert not run('docker','ps','-a','-q','--filter','name=^/'+container+'$'),'Fixture container remained'
            assert not run('docker','network','ls','-q','--filter','name=^'+NETWORK+'$'),'Fixture network remained'
            # Only this generated copy can contain root-owned PHP cache files.
            if fixture.exists():
                subprocess.run(['sudo','-n','chown','-R',str(os.getuid())+':'+str(os.getgid()),str(fixture)],
                               text=True,capture_output=True,check=False,timeout=30)
    print('PASS: disposable config/session/database/container/network teardown; no live deployment')


if __name__=='__main__':
    main()
