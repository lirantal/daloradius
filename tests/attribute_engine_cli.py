#!/usr/bin/env python3
"""UNIT-023: shared PEAR/PDO attribute provider against disposable MariaDB."""
import hashlib
import json
import os
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap
import secrets
import shutil
import subprocess
import sys
import tempfile
import user_actions_http as h

ROOT=Path(__file__).resolve().parents[1]
BASELINE=os.environ.get('ATTRIBUTE_ENGINE_BASELINE')=='1'
DB,WEB,NETWORK=h.DB,h.WEB,h.NETWORK
run,sql,wait_for=h.run,h.sql,h.wait_for

DRIVER=r'''<?php
$_SERVER['PHP_SELF']='/operators/attribute-fixture.php';
chdir('/fixtures/app/operators');
function t($section,$key) { return $key; }
require_once '/fixtures/app/common/includes/config_read.php';
require_once '/fixtures/app/common/includes/validation.php';
require_once '/fixtures/app/operators/library/attributes.php';
$req=json_decode(file_get_contents('php://stdin'),true,512,JSON_THROW_ON_ERROR);
$_SESSION['location_name']='default';
$_POST=$req['fields'] ?? array();
if ($req['block_cleartext'] ?? false) $configValues['CONFIG_DB_PASSWORD_ENCRYPTION']='no';
$valid_ops=array(':=','==','=');
$logDebugSQL='';
$pdo=null;
try {
    if (($req['connection'] ?? '') === 'pdo') {
        require_once '/fixtures/app/common/includes/pdo_connection.php';
        $pdo=dalo_pdo_connect($configValues,'default');
        $handle=$pdo;
        if (($req['operation'] ?? 'mutate')==='mutate' && !($req['no_transaction'] ?? false)) {
            $pdo->beginTransaction();
        }
    } else {
        if (__BASELINE__) {
            require '/fixtures/app/common/includes/db_open.php';
            $handle=$dbSocket;
        } else { $handle=new stdClass(); }
    }
    if (($req['operation'] ?? 'mutate')==='lookup') {
        $found=is_attribute_already_present($handle,$req['table'],$req['param'],
                    $req['subject'],$req['attribute'],$req['op'],$req['value']);
        echo json_encode(array('ok'=>true,'found'=>$found),JSON_THROW_ON_ERROR);
    } else {
        if ($pdo && ($req['marker'] ?? false)) {
            $pdo->exec("INSERT INTO radreply (username,attribute,op,value) VALUES ('marker','Class',':=','pending')");
        }
        $count=handleAttributes($handle,$req['subject'] ?? 'user-a',
            array('submit','csrf_token','groups'),$req['insert_only'] ?? true,
            $req['user_or_group'] ?? 'user');
        $owned=$pdo ? $pdo->inTransaction() : false;
        if ($pdo && $pdo->inTransaction()) {
            if (($req['finish'] ?? '')==='rollback') $pdo->rollBack();
            else $pdo->commit();
        }
        echo json_encode(array('ok'=>true,'count'=>$count,'caller_owns_transaction'=>$owned),JSON_THROW_ON_ERROR);
    }
} catch (Throwable $exception) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(array('ok'=>false,'error'=>get_class($exception)),JSON_THROW_ON_ERROR);
}
'''


def main():
    scratch=Path.home()/'.hermes/cache/scratch'
    scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch,prefix='dalo-attribute-engine-') as tmp:
        fixture=Path(tmp)
        shutil.copytree(ROOT/'app',fixture/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
        if BASELINE:
            restore_pear_bootstrap((fixture / 'app').parent, 'd73639c1d')
            old=subprocess.check_output(['git','show',
                'd73639c1d:app/operators/library/attributes.php'],cwd=ROOT)
            (fixture/'app/operators/library/attributes.php').write_bytes(old)
        (fixture/'driver.php').write_text(DRIVER.replace('__BASELINE__','true' if BASELINE else 'false'))
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,
                '--tmpfs','/var/lib/mysql','-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            sql("""INSERT INTO radcheck (id,username,attribute,op,value) VALUES
                     (100,'user-a','Session-Timeout',':=','30'),
                     (101,'user-b','Session-Timeout',':=','other');
                   INSERT INTO radreply (id,username,attribute,op,value) VALUES
                     (200,'user-a','Reply-Message',':=','before');
                   INSERT INTO radgroupcheck (id,groupname,attribute,op,value) VALUES
                     (300,'gold','Auth-Type',':=','Accept');
                   INSERT INTO radgroupreply (id,groupname,attribute,op,value) VALUES
                     (400,'gold','Reply-Message',':=','before');""")
            config=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,val in {'CONFIG_DB_HOST':DB,'CONFIG_DB_USER':'root',
                            'CONFIG_DB_PASS':'','CONFIG_DB_NAME':'radius'}.items():
                config+='\n$configValues['+repr(key)+'] = '+repr(val)+';\n'
            (fixture/'app/common/includes/daloradius.conf.php').write_text(config)
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{fixture}:/fixtures','-w','/fixtures/app/operators',
                '--entrypoint','sleep','lirantal/daloradius','3600')
            def invoke(fields=None,connection=None,**options):
                req={'connection':connection or ('pear' if BASELINE else 'pdo'),
                     'fields':fields or {},**options}
                p=subprocess.run(['docker','exec','-i',WEB,'php','/fixtures/driver.php'],
                    input=json.dumps(req,ensure_ascii=False),capture_output=True,text=True)
                assert p.returncode==0,(p.stdout[-500:],p.stderr[-500:])
                assert 'Fatal error' not in p.stdout+p.stderr,(p.stdout[-500:],p.stderr[-500:])
                try:
                    result=json.loads(p.stdout)
                except json.JSONDecodeError as exc:
                    raise AssertionError((p.stdout[-500:],p.stderr[-500:])) from exc
                return result
            def state():
                return {'check':sql('SELECT username,attribute,op,value FROM radcheck ORDER BY username,attribute,id'),
                        'reply':sql('SELECT username,attribute,op,value FROM radreply ORDER BY username,attribute,id'),
                        'groupcheck':sql('SELECT groupname,attribute,op,value FROM radgroupcheck ORDER BY groupname,attribute,id'),
                        'groupreply':sql('SELECT groupname,attribute,op,value FROM radgroupreply ORDER BY groupname,attribute,id')}
            lookup=dict(operation='lookup',table='radcheck',param='username',
                        subject='user-a',attribute='Session-Timeout',op=':=',value='30')
            assert invoke(**lookup)=={'ok':True,'found':True}
            assert invoke(**{**lookup,'value':'missing'})=={'ok':True,'found':False}
            inserted=invoke({'a':['Session-Timeout','60',':=','check'],
                             'b':['Reply-Message','Café',':=','reply'],
                             'c':['Reply-Message','Café',':=','reply'],
                             'csrf_token':'ignored'},subject='user-a')
            assert inserted['ok'] and inserted['count']==2,inserted
            groups=invoke({'a':['Filter-Id','A 50%',':=','check'],
                           'b':['Reply-Message',"O'Reilly",':=','reply']},
                          subject='gold',user_or_group='group')
            assert groups['ok'] and groups['count']==2,groups
            edited=invoke({'a':['100__Session-Timeout','120',':=','check'],
                           'b':['200__Reply-Message','after',':=','reply']},
                          subject='user-a',insert_only=False)
            assert edited['ok'] and edited['count']==2,edited
            group_edit=invoke({'a':['300__Auth-Type','Reject',':=','check'],
                               'b':['400__Reply-Message','after',':=','reply']},
                              subject='gold',user_or_group='group',insert_only=False)
            assert group_edit['ok'] and group_edit['count']==2,group_edit
            persisted=state()
            assert 'user-a\tSession-Timeout\t:=\t120' in persisted['check']
            assert 'gold\tReply-Message\t:=\tO\'Reilly' in persisted['groupreply']
            print('PASS user/group insert, duplicate suppression, update and exact lookup',file=sys.stderr)
            ref=os.environ.get('ATTRIBUTE_ENGINE_REFERENCE')
            if BASELINE:
                if ref: Path(ref).write_text(json.dumps(persisted,ensure_ascii=False))
                print('PASS UNIT-023 PEAR baseline',file=sys.stderr)
            else:
                if ref: assert json.loads(Path(ref).read_text())==persisted
                assert invoke(connection='pear', **lookup)=={'ok':False,'error':'TypeError'}
                print('PASS PDO-only candidate rejects legacy connection mode',file=sys.stderr)
                synthetic=secrets.token_hex(16)
                hashed=invoke({'password':['MD5-Password',synthetic,':=','check']})
                assert hashed['ok'] and hashed['count']==1
                stored=sql("SELECT id,value FROM radcheck WHERE username='user-a' AND attribute='MD5-Password'").split('\t')
                assert len(stored)==2 and stored[1].strip()==hashlib.md5(synthetic.encode()).hexdigest().upper()
                unchanged=invoke({'password':[stored[0]+'__MD5-Password',stored[1].strip(),'=','check']},
                                 insert_only=False)
                assert unchanged['ok'] and unchanged['count']==1
                assert sql("SELECT value,op FROM radcheck WHERE id="+stored[0]).strip()==stored[1].strip()+'\t='
                again=invoke({'password':[stored[0]+'__MD5-Password',stored[1].strip(),'=','check']},
                             insert_only=False)
                assert again['ok'] and again['count']==0
                before_cleartext=state()
                denied=invoke({'password':['Cleartext-Password',synthetic,':=','check']},
                              block_cleartext=True)
                assert not denied['ok'] and state()==before_cleartext
                print('PASS hashed password and unchanged-hash operator update',file=sys.stderr)
                bad=[
                    ({'a':['Valid-Attr','ok',':=','check'],
                      'z':['Invalid-Attr','x',':=','bad-reply']},'user-a',True),
                    ({'a':['Valid-Attr','ok',':=','check'],
                      'z':['101__Session-Timeout','x',':=','check']},'user-a',False),
                    ({'a':['Valid-Attr','ok',':=','check'],
                      'z':['100__Wrong-Attribute','x',':=','check']},'user-a',False),
                    ({'a':['Valid-Attr','ok',':=','check'],
                      'z':['Valid-Attr',[],':=','check']},'user-a',True),
                    ({'a':['Valid-Attr','ok','not-an-op','check']},'user-a',True),
                    ({'a':['100__Session-Timeout','x',':=','check'],
                      'z':['100__Session-Timeout','y',':=','check']},'user-a',False),
                ]
                for fields,subject,insert_only in bad:
                    before=state()
                    outcome=invoke(fields,subject=subject,insert_only=insert_only)
                    assert not outcome['ok'],(fields,outcome)
                    assert state()==before,fields
                assert invoke(connection='pdo',operation='lookup',table='radcheck` WHERE 1=1 --',
                              param='username',subject='user-a',attribute='Session-Timeout',
                              op=':=',value='30')['ok'] is False
                print('PASS malformed selector, stale/foreign IDs and invalid later entry',file=sys.stderr)
                before=state()
                no_tx=invoke({'a':['Uncommitted','x',':=','check']},no_transaction=True)
                assert not no_tx['ok'] and state()==before
                rolled=invoke({'a':['Rollback-Attr','x',':=','check']},finish='rollback',marker=True)
                assert rolled['ok'] and rolled['caller_owns_transaction'] and state()==before
                sql("DELIMITER //\nCREATE TRIGGER fixture_second_attribute BEFORE INSERT ON radreply "
                    "FOR EACH ROW BEGIN IF NEW.attribute='Break-Insert' THEN "
                    "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='later attribute failed'; "
                    "END IF; END//\nDELIMITER ;\n")
                failed=invoke({'a':['First-Insert','x',':=','check'],
                               'b':['Break-Insert','x',':=','reply']},marker=True)
                assert not failed['ok'] and state()==before
                sql('DROP TRIGGER fixture_second_attribute')
                print('PASS caller-owned rollback and later SQL failure propagation',file=sys.stderr)
                sql('ALTER TABLE radreply ENGINE=MyISAM')
                assert not invoke({'a':['First-Insert','x',':=','check'],
                                   'b':['Second-Insert','x',':=','reply']})['ok']
                assert state()==before
                sql('ALTER TABLE radreply ENGINE=InnoDB')
                assert invoke({'a':['Literal-Zero','0',':=','check']})['count']==1
                assert 'user-a\tLiteral-Zero\t:=\t0' in state()['check']
                print('PASS non-InnoDB preflight and literal zero',file=sys.stderr)
                print('PASS UNIT-023 PDO candidate',file=sys.stderr)
        finally:
            for name in (WEB,DB):
                subprocess.run(['docker','rm','-f',name],stdout=subprocess.DEVNULL,
                               stderr=subprocess.DEVNULL)
            subprocess.run(['docker','network','rm',NETWORK],stdout=subprocess.DEVNULL,
                           stderr=subprocess.DEVNULL)

if __name__=='__main__':main()
