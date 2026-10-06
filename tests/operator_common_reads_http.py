#!/usr/bin/env python3
"""R02b: native borrowed-handle readers, PEAR compatibility and rollback ownership."""
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
DB,WEB,NETWORK=['pdo-r02b-'+kind+'-'+SUFFIX for kind in ('db','web','net')]
IMAGE=os.environ.get('SELECTOR_WEB_IMAGE','lirantal/daloradius')
FUNCTIONS=['count_sql','get_numrows','get_table_column_names','get_user_group_mappings','hotspots_exists','user_exists','user_portal_password_is_set']


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
    with tempfile.TemporaryDirectory(prefix='pdo-r02b-',dir=scratch) as directory:
        fixture=Path(directory)
        for version in ('base','candidate'):
            dest=fixture/version
            shutil.copytree(ROOT/'app',dest/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if version=='base':
                restore_pear_bootstrap((dest / 'app').parent, BASE)
                rel='app/operators/include/management/functions.php'
                (dest/rel).write_text(run('git','show',BASE+':'+rel)+'\n')

            conf=sample.replace('?>','')+'''
$configValues['CONFIG_DB_HOST']=getenv('SELECTOR_DB');$configValues['CONFIG_DB_USER']=getenv('SELECTOR_USER');
$configValues['CONFIG_DB_PASS']=getenv('SELECTOR_PASS');$configValues['CONFIG_DB_ENGINE']='mysqli';$configValues['CONFIG_DB_NAME']='radius';
$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>getenv('SELECTOR_DB'),
'Username'=>getenv('SELECTOR_USER'),'Password'=>getenv('SELECTOR_PASS'),'Database'=>'radius_other','Port'=>'3306');
'''
            for key,value in mapping.items():conf+="$configValues['"+key+"']='"+value+"';\n"
            conf+="if(isset($_GET['bad_table'])){$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']='selector_bad; DROP TABLE selector_bad';}\n"
            (dest/'app/common/includes/daloradius.conf.php').write_text(conf)
            (dest/'app/operators/selector-fixture.php').write_text('<?php\ninclude \'library/checklogin.php\';include \'../common/includes/config_read.php\';include \'lang/main.php\';\n$operator_perm_file=\'selector_fixture\';include \'library/check_operator_perm.php\';\ninclude \'include/management/functions.php\';require_once \'../common/includes/pdo_connection.php\';\n$mode=$_GET[\'mode\'] ?? \'pdo\';\nif($mode===\'pdo\'){$dbSocket=dalo_pdo_connect($configValues,$_SESSION[\'location_name\'] ?? \'default\');$dbSocket->beginTransaction();}\nelse {include \'../common/includes/db_open.php\';$dbSocket->autoCommit(false);}\n$id=spl_object_id($dbSocket);$dbSocket->query(\'INSERT INTO read_probe(id) VALUES(1)\');\n$name=$_GET[\'fn\'];$logDebugSQL=\'\';$value=null;$error=null;\ntry {\n switch($name) {\n case \'sensitive_error\':$value=dalo_portal_db_sensitive_call($dbSocket,function() use($dbSocket){return $dbSocket->query(\'SELECT * FROM fixture_missing_sensitive_table\');});break;\n case \'count_sql\':$value=count_sql($dbSocket,\'SELECT COUNT(*) FROM \'.$configValues[\'CONFIG_DB_TBL_RADUSERGROUP\']);break;\n case \'get_numrows\':$value=(int)get_numrows($dbSocket,\'SELECT COUNT(*) FROM \'.$configValues[\'CONFIG_DB_TBL_RADUSERGROUP\']);break;\n case \'get_table_column_names\':$value=get_table_column_names($dbSocket,isset($_GET[\'invalid\'])?\'bad;table\':$configValues[\'CONFIG_DB_TBL_RADCHECK\'],array(\'fallback\'));break;\n case \'get_user_group_mappings\':$value=get_user_group_mappings($dbSocket,\' mapped \');break;\n case \'hotspots_exists\':$value=hotspots_exists($dbSocket,\'site A\');break;\n case \'user_exists\':$value=user_exists($dbSocket," account O\'Reilly%+ ",isset($_GET[\'invalid\'])?\'not-a-table\':\'CONFIG_DB_TBL_RADCHECK\');break;\n case \'user_portal_password_is_set\':$value=user_portal_password_is_set($dbSocket,\'info-only\');break;\n default:throw new InvalidArgumentException(\'Unknown fixture read\');\n }\n} catch(Throwable $e){$error=get_class($e);if($name===\'sensitive_error\'){$value=$e->getMessage()===\'Database operation failed\' && $e->getPrevious()===null;}}\n$same=spl_object_id($dbSocket)===$id;$pdo=$dbSocket instanceof PDO;\n$res=$dbSocket->query(\'SELECT COUNT(*) FROM read_probe\');$visible=$pdo?$res->fetchColumn():$res->fetchRow()[0];\n$kept=$same && $visible==1 && (!$pdo || $dbSocket->inTransaction());\nif($pdo){$dbSocket->rollBack();}else{$dbSocket->rollback();}\nheader(\'Content-Type: application/json\');echo json_encode(array(\'value\'=>$value,\'error\'=>$error,\'caller_kept\'=>$kept,\'redacted\'=>strpos($logDebugSQL,\'Reilly\')===false));\n')
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
                "INSERT INTO operators_acl(operator_id,file,access) VALUES(9001,'selector_fixture',1),(9002,'selector_fixture',0); CREATE TABLE read_probe(id int PRIMARY KEY) ENGINE=InnoDB; CREATE DATABASE radius_other; ")
            for key,original in originals.items():
                sql('CREATE TABLE radius_other.'+mapping[key]+' LIKE '+mapping[key])
            sql('CREATE TABLE radius_other.read_probe LIKE read_probe; CREATE TABLE radius_other.operators_acl LIKE operators_acl; INSERT INTO radius_other.operators_acl SELECT * FROM operators_acl; '+
                "INSERT INTO radius_other."+table('DALOBILLINGPLANS')+"(id,planName,planActive) VALUES(99,'other backend','yes'); "+
                f"CREATE USER '{user}'@'%' IDENTIFIED BY '{password}'; GRANT SELECT ON radius.* TO '{user}'@'%'; GRANT SELECT ON radius_other.* TO '{user}'@'%'; GRANT INSERT ON radius.read_probe TO '{user}'@'%'; GRANT INSERT ON radius_other.read_probe TO '{user}'@'%';")
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
            def request(c,name,mode='pear',extra=''):
                r=c.request('selector-fixture.php?fn='+name+'&mode='+mode+extra)
                assert r[0]==200,'Read fixture failed: '+name
                v=json.loads(r[3]);assert v['caller_kept'],'Reader changed caller write/transaction: '+name
                assert sql('SELECT COUNT(*) FROM read_probe')=='0','Reader committed caller write'
                assert sql('SELECT COUNT(*) FROM radius_other.read_probe')=='0','Reader committed named caller write'
                return v
            sql('UPDATE '+table('DALOUSERINFO')+' SET portalloginpassword=NULL')
            frozen=sql('CHECKSUM TABLE '+','.join(mapping.values()))
            for location in ('default','other'):
                old,current=client('base',location),client(location=location)
                for name in FUNCTIONS:
                    a=request(old,name)
                    for mode in ('pdo',):
                        b=request(current,name,mode)
                        assert a['value']==b['value'] and a['error']==b['error'],'Read parity failed: '+name
                        if mode=='pdo':assert b['redacted'],'Bound data appears in SQL diagnostics'
            sensitive=request(candidate,'sensitive_error','pdo')
            assert sensitive['error']=='RuntimeException' and sensitive['value'] is True, 'Real PDO driver failure was not expurgated'
            assert request(candidate,'user_exists','pdo')['value'] is True
            assert request(candidate,'get_user_group_mappings','pdo')['value']==['both','mapped-only']
            assert request(candidate,'hotspots_exists','pdo')['value'] is True
            for marker in (None,'','fixture-presence-marker'):
                update='NULL' if marker is None else "'"+marker+"'"
                sql('UPDATE '+table('DALOUSERINFO')+' SET portalloginpassword='+update)
                a=request(baseline,'user_portal_password_is_set')['value']
                assert a==request(candidate,'user_portal_password_is_set','pdo')['value']==bool(marker)
            sql("INSERT INTO "+table('DALOUSERINFO')+"(id,username,portalloginpassword) VALUES(9901,'info-only','fixture-presence-marker')")
            assert request(baseline,'user_portal_password_is_set')['value'] is False
            assert request(candidate,'user_portal_password_is_set','pdo')['value'] is False
            sql('DELETE FROM '+table('DALOUSERINFO')+' WHERE id=9901')
            assert request(candidate,'get_table_column_names','pdo','&invalid=1')['value']==['fallback']
            assert request(candidate,'user_exists','pdo','&invalid=1')['error']=='InvalidArgumentException'
            saved=table('RADUSERGROUP');sql('RENAME TABLE '+saved+' TO saved_mappings')
            try:
                assert request(candidate,'get_user_group_mappings','pdo')['value']==[]
                assert request(candidate,'count_sql','pdo')['error']=='RuntimeException'
                assert request(candidate,'get_numrows','pdo')['error']=='RuntimeException'
            finally:sql('RENAME TABLE saved_mappings TO '+saved)
            sql('UPDATE '+table('DALOUSERINFO')+' SET portalloginpassword=NULL')
            assert sql('CHECKSUM TABLE '+','.join(mapping.values()))==frozen,'Reads changed non-probe tables'
            assert Client(origin+'candidate/app/operators/').request('selector-fixture.php?fn=count_sql')[0]==302
            assert client(identity=9002).request('selector-fixture.php?fn=count_sql')[0]==302
            print('PASS: seven borrowed-handle readers match pinned PEAR on default/named locations; literal identities, columns, distinct mappings and portal NULL/empty/set states')
            print('PASS: actual caller INSERT remains uncommitted and is rolled back by caller; invalid metadata fallback, later SQL failures redacted, no extra connection or implicit commit')
            assert candidate.request('log-channel.php')[0]==200
            logs=run('docker','logs',WEB)
            assert 'SELECTOR_LOG_CHANNEL' in logs,'PHP stderr capture missing'
            assert not re.search(r'(?:Fatal error|Parse error|Warning:|Uncaught (?:Error|TypeError|PDOException|RuntimeException))',logs), 'Unexpected native PHP diagnostic'
            assert password not in logs and user not in logs
            print('PASS: SELECT-only domain tables, auth/ACL and positively verified clean PHP logs')
        finally:
            for name in (WEB,DB):run('docker','rm','-f',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            assert not run('docker','ps','-a','-q','--filter','name=^/'+DB+'$')
            assert not run('docker','ps','-a','-q','--filter','name=^/'+WEB+'$')
            assert not run('docker','network','ls','-q','--filter','name=^'+NETWORK+'$')
            subprocess.run(['sudo','-n','chown','-R',str(os.getuid())+':'+str(os.getgid()),str(fixture)],capture_output=True,timeout=30)
    print('PASS: ephemeral SQL/config/session/connection/container/network resources removed; no deployment')


if __name__=='__main__':main()
