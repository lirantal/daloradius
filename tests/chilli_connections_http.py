#!/usr/bin/env python3
"""UNIT-038 real PHP/PEAR/PDO/MariaDB connection-boundary validation."""
import json
import os
from pathlib import Path
import secrets
import shutil
import subprocess
import tempfile
import urllib.error
import urllib.request

from operator_login_http import run, wait_for, quote

ROOT = Path(__file__).resolve().parents[1]
BASE = 'eed0a3ae61b0fd908e28a9686b7eb8a7999ae561'
BASELINE = os.environ.get('CHILLI_CONNECTION_BASELINE') == '1'
FAMILIES = ('portal1/signup-paypal', 'portal2/signup-2checkout', 'portal2/signup-free',
            'portal2/signup-paypal', 'portal3/signup-free', 'portal3/signup-paypal')
TAG = 'u38-' + secrets.token_hex(6)
DB, WEB, NETWORK = TAG + '-db', TAG + '-web', TAG + '-net'

ENDPOINT = '''<?php
$family=__FAMILY__;
chdir('/fixtures'); // Includes must not depend on the calling working directory.
$lib=__DIR__.'/library/';
$action=$_GET['action'] ?? 'legacy';
if ($action==='legacy') {
    include $lib.'opendb.php';
    $v=$dbSocket->escapeSimple("sample-'-%");
    $r=$dbSocket->query("INSERT INTO u38_rows (value) VALUES ('$v')");
    if (DB::isError($r)) { throw new RuntimeException('Fixture insert failed'); }
    $r=$dbSocket->query('SELECT value FROM u38_rows ORDER BY id');
    $rows=array();while ($row=$r->fetchRow()) { $rows[]=$row[0]; }
    $r->free(); include $lib.'closedb.php';
    include $lib.'opendb.php';
    $r=$dbSocket->query('SELECT COUNT(*) FROM u38_rows');$count=(int)$r->fetchRow()[0];
    $r->free();include $lib.'closedb.php';
    echo json_encode(array('rows'=>$rows,'count'=>$count));exit;
}
require_once '/fixtures/contrib/chilli/common/database.php';
include $lib.'config_read.php';
try {
    if ($action==='failed') {
        $configValues['CONFIG_DB_NAME']='missing-u38';
        if (($_GET['client'] ?? '')==='pear') { $p=dalo_chilli_pear_open($configValues,true); }
        else { $p=dalo_chilli_pdo_open($configValues); }
        throw new LogicException('Unexpected connection');
    }
    if ($action==='invalid') {
        $configValues['CONFIG_DB_PORT']=array('bad');
        $p=dalo_chilli_pdo_open($configValues);
        throw new LogicException('Unexpected connection');
    }
    if ($action==='query_error') {
        $p=dalo_chilli_pear_open($configValues,true);
        ob_start();$r=$p->query("SELECT 'sensitive-bound-marker' FROM missing_u38");$text=ob_get_clean();
        $ok=DB::isError($r) && $text==='<br/><b>Database error</b><br>';
        dalo_chilli_database_close($p);echo json_encode(array('ok'=>$ok));exit;
    }
    $p=dalo_chilli_pdo_open($configValues);
    $ok=($p instanceof PDO) && !$p->inTransaction() &&
        $p->getAttribute(PDO::ATTR_ERRMODE)===PDO::ERRMODE_EXCEPTION &&
        !$p->getAttribute(PDO::ATTR_EMULATE_PREPARES) && !$p->getAttribute(PDO::ATTR_PERSISTENT) &&
        $p->query('SELECT @@character_set_connection')->fetchColumn()==='utf8mb4' &&
        $p->query('SELECT @@sql_mode')->fetchColumn()==='';
    if (!$ok) { throw new LogicException('Wrong connection options'); }
    if ($action==='pdo') {
        $p->beginTransaction();$s=$p->prepare('INSERT INTO u38_rows (value) VALUES (?)');
        $s->execute(array("unicode-é-%-'"));$s->execute(array('dependent'));
        $p->commit();dalo_chilli_database_close($p);$null=$p===null;
        $p=dalo_chilli_pdo_open($configValues);
        $rows=$p->query('SELECT value FROM u38_rows ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
        dalo_chilli_database_close($p);dalo_chilli_database_close($p);
        echo json_encode(array('ok'=>$null && $p===null,'rows'=>$rows));exit;
    }
    if ($action==='rollback') {
        $before=$p->query('SELECT id,value FROM u38_rows ORDER BY id')->fetchAll();
        $p->beginTransaction();$s=$p->prepare('INSERT INTO u38_rows (value) VALUES (?)');
        $s->execute(array('uncommitted'));
        dalo_chilli_database_close($p); // Keep $s alive to prove explicit rollback.
        $other=dalo_chilli_pdo_open($configValues);
        $after=$other->query('SELECT id,value FROM u38_rows ORDER BY id')->fetchAll();
        echo json_encode(array('ok'=>$p===null && $before===$after));
        dalo_chilli_database_close($other);exit;
    }
    throw new LogicException('Unknown fixture action');
} catch (RuntimeException $error) {
    echo json_encode(array('error'=>$error->getMessage(),'previous'=>$error->getPrevious()!==null));
}
'''


def sql(query, database=DB):
    result = subprocess.run(['docker','exec','-i',database,'mariadb','-uroot','-N','-B','radius'],
                            input=query,text=True,capture_output=True,timeout=30)
    if result.returncode: raise RuntimeError('Fixture SQL failed (details omitted)')
    return result.stdout.strip()


def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='chilli-connections-',dir=scratch) as directory:
        fixture=Path(directory)
        for family in FAMILIES:
            target=fixture/'contrib/chilli'/family/'library';target.mkdir(parents=True)
            for name in ('opendb.php','closedb.php','config_read.php','errorHandling.php'):
                source='contrib/chilli/'+family+'/library/'+name
                content=run('git','show',BASE+':'+source) if BASELINE else (ROOT/source).read_text()
                (target/name).write_text(content+'\n')
            (target.parent/'probe.php').write_text(ENDPOINT.replace('__FAMILY__',quote(family)))
        if not BASELINE:
            target=fixture/'contrib/chilli/common';target.mkdir()
            shutil.copy2(ROOT/'contrib/chilli/common/database.php',target/'database.php')
        common=fixture/'app/common/includes';common.mkdir(parents=True)
        shutil.copy2(ROOT/'app/common/includes/pdo_connection.php',common/'pdo_connection.php')
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            sql('CREATE TABLE u38_rows (id INT PRIMARY KEY AUTO_INCREMENT,value VARCHAR(100)) ENGINE=InnoDB')
            # Ephemeral restricted identity, credentials never printed or persisted beyond fixture cleanup.
            username='u38_'+secrets.token_hex(5);password=secrets.token_urlsafe(30)
            sql('CREATE USER '+quote(username)+"@'%' IDENTIFIED BY "+quote(password)+
                '; GRANT ALL ON radius.* TO '+quote(username)+"@'%'")
            settings={'CONFIG_DB_ENGINE':'mysqli','CONFIG_DB_HOST':DB,'CONFIG_DB_PORT':'3306',
                      'CONFIG_DB_NAME':'radius','CONFIG_DB_USER':username,'CONFIG_DB_PASS':password}
            config='<?php\n'+''.join('$configValues[%s]=%s;\n'%(quote(k),quote(v)) for k,v in settings.items())
            for family in FAMILIES:
                path=fixture/'contrib/chilli'/family/'library/daloradius.conf.php'
                path.write_text(config);path.chmod(0o600)
            run('docker','run','-d','--name',WEB,'--network',NETWORK,'-v',f'{fixture}:/fixtures',
                '--entrypoint','php','lirantal/daloradius','-d','display_errors=0',
                '-S','0.0.0.0:8080','-t','/fixtures/contrib/chilli')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/'
            def fetch(path):
                try: response=urllib.request.urlopen(base+path,timeout=30)
                except urllib.error.HTTPError as error: response=error
                return response.status,response.read().decode()
            wait_for(lambda:fetch(FAMILIES[0]+'/probe.php')[0]==200,'PHP HTTP')
            for family in FAMILIES:
                sql('TRUNCATE u38_rows')
                status,body=fetch(family+'/probe.php');assert status==200
                assert json.loads(body)=={'rows':["sample-'-%"],'count':1}
                assert sql('SELECT value FROM u38_rows')=="sample-'-%"
                if not BASELINE:
                    status,body=fetch(family+'/probe.php?action=pdo');assert status==200
                    assert json.loads(body)=={'ok':True,'rows':["sample-'-%","unicode-é-%-'",'dependent']}
                    assert json.loads(fetch(family+'/probe.php?action=rollback')[1])=={'ok':True}
                    for action in ('failed&client=pear','failed&client=pdo','invalid'):
                        status,body=fetch(family+'/probe.php?action='+action);assert status==200
                        assert json.loads(body)=={'error':'Database connection failed','previous':False}
                        assert password not in body and username not in body
                    assert json.loads(fetch(family+'/probe.php?action=query_error')[1])=={'ok':True}
                print('PASS:',family,'PEAR open/query/close/reopen'+(' and opt-in PDO commit/close/rollback/redaction' if not BASELINE else ' (pinned baseline)'))
            if not BASELINE:
                assert fetch('common/database.php')[0]==404
                sequence=''
                for family in FAMILIES:
                    lib='/fixtures/contrib/chilli/'+family+'/library/'
                    sequence += 'include '+quote(lib+'opendb.php')+';'
                    sequence += "$r=$dbSocket->query('SELECT 1');if(DB::isError($r)||$r->fetchRow()[0]!=1){exit(1);}$r->free();"
                    sequence += 'include '+quote(lib+'closedb.php')+';if($dbSocket!==null){exit(1);}'
                assert run('docker','exec',WEB,'php','-r',sequence+"echo 'ok';")=='ok'
                print('PASS: all six wrappers coexist in one PHP process without callback redeclaration')
                # Defaults, invalid configuration and array-DSN special credentials are checked without logging them.
                code='''require '/fixtures/contrib/chilli/common/database.php';
$c=array('CONFIG_DB_ENGINE'=>'mysqli','CONFIG_DB_HOST'=>'localhost','CONFIG_DB_NAME'=>'radius',
'CONFIG_DB_USER'=>'user@:/','CONFIG_DB_PASS'=>'secret@:/');
assert(dalo_chilli_database_settings($c,true)['CONFIG_DB_PORT']==='3306');
$c['CONFIG_DB_ENGINE']='pgsql';assert(dalo_chilli_database_settings($c,true)['CONFIG_DB_PORT']==='5432');
foreach (array('0','65536','3306;bad',array('bad')) as $port) {
 $c['CONFIG_DB_PORT']=$port;$failed=false;try { dalo_chilli_database_settings($c,true); }
 catch (RuntimeException $e) {$failed=true;} if (!$failed) {exit(1);}
}
$c['CONFIG_DB_PORT']='invalid';if(dalo_chilli_database_settings($c,false)['CONFIG_DB_PORT']!=='5432'){exit(1);}
$c['CONFIG_DB_PORT']='3306';
foreach (array('ENGINE','HOST','NAME','USER','PASS') as $key) {
 $bad=$c;$bad['CONFIG_DB_'.$key]=array('bad');$failed=false;
 try {dalo_chilli_pdo_open($bad);} catch (RuntimeException $e) {
  $failed=$e->getMessage()==='Database connection failed' && $e->getPrevious()===null;
 } if(!$failed){exit(1);}
}
$c['CONFIG_DB_ENGINE']='unsupported';$failed=false;
try {dalo_chilli_pdo_open($c);}catch(RuntimeException $e){$failed=$e->getMessage()==='Database connection failed';}
if(!$failed){exit(1);}echo 'ok';'''
                assert run('docker','exec',WEB,'php','-d','zend.assertions=1','-d','assert.exception=1','-r',code)=='ok'
                print('PASS: direct helper denied, default ports, invalid ports and legacy ignored-port policy')

                # Real nondefault-port server and URI-delimiter-bearing credentials.
                alternate=TAG+'-alternate'
                run('docker','run','-d','--name',alternate,'--network',NETWORK,'--tmpfs','/var/lib/mysql',
                    '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius',
                    'mariadb:11.8','--port=3307')
                wait_for(lambda:sql('SELECT 1',alternate),'alternate MariaDB')
                special_user=username+'@:/';special_password=password+'@:/'
                sql('CREATE TABLE u38_rows (id INT PRIMARY KEY AUTO_INCREMENT,value VARCHAR(100)) ENGINE=InnoDB;'
                    'CREATE USER '+quote(special_user)+"@'%' IDENTIFIED BY "+quote(special_password)+
                    '; GRANT ALL ON radius.* TO '+quote(special_user)+"@'%'",alternate)
                settings.update(CONFIG_DB_HOST=alternate,CONFIG_DB_PORT='3307',
                                CONFIG_DB_USER=special_user,CONFIG_DB_PASS=special_password)
                config='<?php\n'+''.join('$configValues[%s]=%s;\n'%(quote(k),quote(v)) for k,v in settings.items())
                for family in FAMILIES:
                    (fixture/'contrib/chilli'/family/'library/daloradius.conf.php').write_text(config)
                    sql('TRUNCATE u38_rows',alternate)
                    status,body=fetch(family+'/probe.php');assert status==200
                    if family in ('portal2/signup-free','portal2/signup-paypal'):
                        assert json.loads(body)=={'rows':["sample-'-%"],'count':1}
                    else:
                        assert body.strip()=='<b>Database connection error</b><br/>', 'Legacy connection error must be generic'
                        assert special_password not in body and special_user not in body
                        assert sql('SELECT COUNT(*) FROM u38_rows',alternate)=='0'
                    status,body=fetch(family+'/probe.php?action=pdo');assert status==200
                    expected=["sample-'-%"] if family in ('portal2/signup-free','portal2/signup-paypal') else []
                    assert json.loads(body)=={'ok':True,'rows':expected+["unicode-é-%-'",'dependent']}
                print('PASS: actual port 3307 routing and delimiter-bearing credentials on PEAR/PDO')
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,timeout=30)
            text=logs.stdout+logs.stderr
            assert password not in text and 'sensitive-bound-marker' not in text
            assert 'PHP Warning' not in text and 'PHP Fatal error' not in text
            print('PASS: clean PHP logs, no leaked fixture credentials or query values')
        finally:
            for name in (WEB,DB,TAG+'-alternate'):run('docker','rm','-f','-v',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            for kind,name in (('container',WEB),('container',DB),('container',TAG+'-alternate'),('network',NETWORK)):
                r=subprocess.run(['docker',kind,'inspect',name],capture_output=True,timeout=30)
                assert r.returncode!=0,'Fixture resource remains'
            print('CLEANUP VERIFIED: disposable containers and network removed')

if __name__=='__main__':main()
