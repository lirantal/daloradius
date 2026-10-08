#!/usr/bin/env python3
"""R02a: native independent selector parity and guarded identifier reads."""
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
BASE='d59b5c6c5019e2d8fa7e5bbffad872b1792d6d0d'
SUFFIX=secrets.token_hex(5)
DB,WEB,NETWORK=['pdo-r02a-'+kind+'-'+SUFFIX for kind in ('db','web','net')]
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


def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    user='selectors_'+secrets.token_hex(5);password=secrets.token_hex(24)
    sample=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text()
    provider=(ROOT/'app/operators/include/management/selectbox_read.php').read_text()
    keys=list(dict.fromkeys(re.findall(r"'(CONFIG_DB_TBL_[A-Z]+)'",provider)))
    originals={k:re.search(r"\$configValues\['"+k+r"'\]\s*=\s*['\"]([^'\"]+)['\"]",sample).group(1) for k in keys}
    mapping={k:'selector_'+str(i) for i,k in enumerate(keys)}
    with tempfile.TemporaryDirectory(prefix='pdo-r02a-',dir=scratch) as directory:
        fixture=Path(directory)
        for version in ('base','candidate'):
            dest=fixture/version
            shutil.copytree(ROOT/'app',dest/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if version=='base':
                restore_pear_bootstrap((dest / 'app').parent, BASE)
                rel='app/operators/include/management/populate_selectbox.php'
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
            conf+="if(isset($_GET['bad_table'])){$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']='selector_bad; DROP TABLE selector_bad';}\n"
            (dest/'app/common/includes/daloradius.conf.php').write_text(conf)
            (dest/'app/operators/selector-fixture.php').write_text('''<?php
include 'library/checklogin.php';include '../common/includes/config_read.php';include 'lang/main.php';
$operator_perm_file='selector_fixture';include 'library/check_operator_perm.php';
include 'include/management/populate_selectbox.php';require_once '../common/includes/pdo_connection.php';
$pdo=dalo_pdo_connect($configValues,$_SESSION['location_name'] ?? 'default');$pdo->beginTransaction();$id=spl_object_id($pdo);
$allowed=json_decode(getenv('SELECTOR_FUNCTIONS'),true);$name=$_GET['fn'] ?? '';
if($name !== 'list_from_db' && !in_array($name,$allowed,true)){http_response_code(400);exit;}
if($name === 'list_from_db') {
    $sql = isset($_GET['bad_sql']) ? 'DELETE FROM forbidden_selector_write' : 'SELECT DISTINCT(Vendor) FROM '.$configValues['CONFIG_DB_TBL_DALODICTIONARY'].' ORDER BY Vendor ASC';
    $value = list_from_db($sql);
} else {
    $value=$name==='get_users'?get_users($_GET['key'] ?? 'CONFIG_DB_TBL_RADCHECK'):$name();
}
$kept=$pdo->inTransaction() && spl_object_id($pdo)===$id && $pdo->query('SELECT 1')->fetchColumn()==1;
$pdo->rollBack();header('Content-Type: application/json');echo json_encode(array('value'=>$value,'caller_kept'=>$kept));
''')
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
            base,candidate=client('base'),client()
            def request(c,name,extra=''):
                r=c.request('selector-fixture.php?fn='+name+extra)
                assert r[0]==200,'Selector request failed: '+name
                value=json.loads(r[3]);assert value['caller_kept'],'Caller transaction changed'
                return value['value']
            checksum_query='CHECKSUM TABLE '+','.join(mapping.values())
            frozen_state=sql(checksum_query)
            for name in FUNCTIONS:
                a,b=request(base,name),request(candidate,name)
                assert a==b,'Selector parity failed: '+name
                if name not in ('get_online_users',):assert b,'Unexpected empty seeded selector: '+name
            for key in ('RADACCT','DALOUSERINFO','DALOUSERBILLINFO','RADCHECK'):
                assert request(base,'get_users','&key=CONFIG_DB_TBL_'+key)==request(candidate,'get_users','&key=CONFIG_DB_TBL_'+key)
            plans=request(candidate,'get_active_plans');assert len(plans)==3 and '13' not in plans
            groups=request(candidate,'get_groups');assert set(groups)=={'both','0','reply É%+','mapped-only'}
            assert len(request(candidate,'get_users'))==1
            assert request(candidate,'get_online_users')==['online','zero-date']
            assert request(candidate,'get_active_plans')!=request(client(location='other'),'get_active_plans')
            for name in FUNCTIONS:
                assert request(client('base','other'),name)==request(client(location='other'),name),'Named location parity failed'
            assert Client(origin+'candidate/app/operators/').request('selector-fixture.php?fn=get_groups')[0]==302
            assert client(identity=9002).request('selector-fixture.php?fn=get_groups')[0]==302
            assert sql(checksum_query)==frozen_state,'Selector reads changed database state'
            dictionary=table('DALODICTIONARY')
            sql('ALTER TABLE '+dictionary+' MODIFY Attribute varchar(64) NULL, MODIFY Vendor varchar(64) NULL; INSERT INTO '+dictionary+'(Attribute,Vendor,Type) VALUES(NULL,NULL,\'string\')')
            for name in ('get_attributes','get_vendors'):
                a,b=request(base,name),request(candidate,name)
                assert a==b and a[0] is None,'NULL selector parity failed'
            assert request(base,'list_from_db')==request(candidate,'list_from_db')
            assert request(candidate,'list_from_db','&bad_sql=1')==[]
            print('PASS: all 19 getters and four username-table selectors preserve PEAR values/keys/order, NULL, duplicates/group UNION, online filters, locations, unchanged SQL state and caller transaction; no legacy opens')
            assert request(candidate,'get_active_plans','&bad_table=1')==[]
            assert request(candidate,'get_users','&key[]=invalid')==[]
            assert request(candidate,'get_users','&key=CONFIG_DB_TBL_DALOBILLINGPLANS')==[]
            saved=table('DALOPROXYS');sql('RENAME TABLE '+saved+' TO saved_proxy')
            try:assert request(candidate,'get_proxies')==[]
            finally:sql('RENAME TABLE saved_proxy TO '+saved)
            for key in ('DALOBILLINGPLANS','RADGROUPCHECK','RADGROUPREPLY','RADUSERGROUP','RADHG','DALOBILLINGINVOICESTATUS','RADIPPOOL','DALOPROXYS'):
                sql('DELETE FROM '+table(key))
            for name in FUNCTIONS[:6]:assert request(base,name)==request(candidate,name)==[]
            assert candidate.request('log-channel.php')[0]==200
            logs=run('docker','logs',WEB)
            assert 'SELECTOR_LOG_CHANNEL' in logs,'PHP stderr capture missing'
            assert 'PHP Fatal error' not in logs and 'PHP Warning' not in logs
            assert password not in logs and user not in logs
            print('PASS: invalid table/key/missing table safely empty and redacted, empty selectors, SELECT-only grants, positively verified clean PHP logs')
        finally:
            for name in (WEB,DB):run('docker','rm','-f',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            assert not run('docker','ps','-a','-q','--filter','name=^/'+DB+'$')
            assert not run('docker','ps','-a','-q','--filter','name=^/'+WEB+'$')
            assert not run('docker','network','ls','-q','--filter','name=^'+NETWORK+'$')
            subprocess.run(['sudo','-n','chown','-R',str(os.getuid())+':'+str(os.getgid()),str(fixture)],capture_output=True,timeout=30)
    print('PASS: ephemeral SQL/config/session/connection/container/network resources removed; no deployment')


if __name__=='__main__':main()
