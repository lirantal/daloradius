#!/usr/bin/env python3
"""UNIT-019: disposable HTTP/PHP/MariaDB operator deletion A/B and rollback."""
import json
import os
from pathlib import Path
import secrets
import shutil
import subprocess
import sys
import tempfile
import urllib.error
import urllib.parse
import urllib.request
import user_actions_http as h
from acct_maintenance_http import Forms

ROOT=Path(__file__).resolve().parents[1]
BASELINE=os.environ.get('USER_DELETE_BASELINE')=='1'
FR1=os.environ.get('USER_DELETE_FR1')=='1'
DB,WEB,NETWORK=h.DB,h.WEB,h.NETWORK
run,sql,wait_for=h.run,h.sql,h.wait_for


def main():
    scratch=Path.home()/'.hermes/cache/scratch'
    scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch,prefix='dalo-user-delete-') as tmp:
        fixture=Path(tmp)
        shutil.copytree(ROOT/'app',fixture/'app',symlinks=True)
        if BASELINE:
            old=subprocess.check_output(['git','show','df1b6b3b7:app/operators/mng-del.php'],cwd=ROOT)
            (fixture/'app/operators/mng-del.php').write_bytes(old)
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,
                '--tmpfs','/var/lib/mysql','-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            sql("""INSERT INTO operators_acl (operator_id,file,access) VALUES
                     (9001,'mng_del',1),(9002,'mng_del',0);
                   INSERT INTO radcheck (id,username,attribute,op,value) VALUES
                     (1,'unit-a','Cleartext-Password',':=','a'),
                     (2,'unit-b','Cleartext-Password',':=','b'),
                     (3,'unit-keep','Cleartext-Password',':=','keep'),
                     (4,'unit-attr','Cleartext-Password',':=','attr'),
                     (5,'unit-attr','Reply-Message',':=','extra'),
                     (6,'unit-50% O''Reilly','Cleartext-Password',':=','special');
                   INSERT INTO radreply (id,username,attribute,op,value) VALUES
                     (10,'unit-a','Reply-Message',':=','a'),
                     (11,'unit-b','Reply-Message',':=','b'),
                     (12,'unit-keep','Reply-Message',':=','keep'),
                     (13,'unit-attr','Reply-Message',':=','attr'),
                     (14,'unit-50% O''Reilly','Reply-Message',':=','special');
                   INSERT INTO radusergroup (username,groupname,priority) VALUES
                     ('unit-a','gold',0),('unit-b','gold',0),
                     ('unit-keep','silver',0),('unit-50% O''Reilly','gold',0);
                   INSERT INTO userinfo (id,username,firstname) VALUES
                     (10,'unit-a','A'),(11,'unit-b','B'),(12,'unit-keep','Keep'),
                     (13,'unit-50% O''Reilly','Special');
                   INSERT INTO userbillinfo (id,username,contactperson) VALUES
                     (20,'unit-a','A'),(21,'unit-b','B'),(22,'unit-keep','Keep'),
                     (23,'unit-a','Second'),(24,'unit-50% O''Reilly','Special');
                   INSERT INTO invoice (id,user_id,date,status_id,type_id,notes) VALUES
                     (50,20,'2020-01-02',1,1,'a'),(51,23,'2020-01-02',1,1,'a second'),
                     (52,22,'2020-01-02',1,1,'keep'),(53,21,'2020-01-02',1,1,'b'),
                     (54,24,'2020-01-02',1,1,'special');
                   INSERT INTO invoice_items (invoice_id,plan_id,amount,tax_amount,notes) VALUES
                     (50,1,2.50,0,'a'),(51,1,3.50,0,'a second'),
                     (52,1,4.50,0,'keep'),(53,1,5.50,0,'b'),(54,1,6.50,0,'special');
                   INSERT INTO payment (invoice_id,amount,date,notes) VALUES
                     (50,1.00,'2020-01-02','a'),(51,2.00,'2020-01-02','a second'),
                     (52,3.00,'2020-01-02','keep'),(53,4.00,'2020-01-02','b'),
                     (54,5.00,'2020-01-02','special');
                   INSERT INTO radpostauth (username,reply) VALUES
                     ('unit-a','Access-Accept'),('unit-b','Access-Accept'),
                     ('unit-keep','Access-Accept'),('unit-50% O''Reilly','Access-Accept');
                   INSERT INTO radacct (username,acctsessionid,acctuniqueid,AcctStartTime,AcctStopTime) VALUES
                     ('unit-a','a1','fixture-a','2020-01-01 01:02:03',NULL),
                     ('unit-b','b1','fixture-b','2020-01-01 01:02:04',NULL),
                     ('unit-keep','k1','fixture-k','2020-01-01 01:02:05',NULL),
                     ('unit-50% O''Reilly','s1','fixture-s','2020-01-01 01:02:06',NULL);""")
            if FR1:
                sql("ALTER TABLE radpostauth CHANGE COLUMN username `user` VARCHAR(64) "
                    "NOT NULL DEFAULT ''")
            config=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,val in {'CONFIG_DB_HOST':DB,'CONFIG_DB_USER':'root',
                            'CONFIG_DB_PASS':'','CONFIG_DB_NAME':'radius'}.items():
                config+='\n$configValues['+repr(key)+'] = '+repr(val)+';\n'
            if FR1:
                config+="\n$configValues['FREERADIUS_VERSION'] = '1';\n"
            (fixture/'app/common/includes/daloradius.conf.php').write_text(config)
            (fixture/'session.php').write_text('''<?php
session_name('daloradius_operator_sid');session_id($argv[1]);session_start();
$_SESSION=['daloradius_logged_in'=>true,'operator_id'=>(int)$argv[2],
'operator_user'=>'delete-fixture','location_name'=>'default','time'=>time()];
session_write_close();
''')
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{fixture}:/fixtures','-w','/fixtures/app/operators',
                '--entrypoint','php','lirantal/daloradius','-d','display_errors=0',
                '-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f',
                '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/mng-del.php'
            wait_for(lambda:urllib.request.urlopen(base,timeout=10),'PHP HTTP')
            sid=secrets.token_hex(16)
            def session(operator=9001):
                run('docker','exec',WEB,'php','/fixtures/session.php',sid,str(operator))
            def request(data=None,authenticated=True):
                req=urllib.request.Request(base,
                    data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),
                    headers={'Cookie':'daloradius_operator_sid='+sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req,timeout=30) as response:
                        return response.url,response.read().decode()
                except urllib.error.HTTPError as error:
                    return error.url,error.read().decode()
            def csrf():
                return next(f['csrf_token'] for f in Forms(request()[1]).forms if 'csrf_token' in f)
            def send(payload,token=None):
                data={'csrf_token':csrf() if token is None else token}
                data.update(payload)
                return request(data)[1]
            def state():
                return {key:sql(query) for key,query in {
                    'check':'SELECT id,username,attribute FROM radcheck ORDER BY id',
                    'reply':'SELECT id,username,attribute FROM radreply ORDER BY id',
                    'groups':'SELECT username,groupname,priority FROM radusergroup ORDER BY username,id',
                    'user':'SELECT id,username,firstname FROM userinfo ORDER BY id',
                    'bill':'SELECT id,username,contactperson FROM userbillinfo ORDER BY id',
                    'invoice':'SELECT id,user_id FROM invoice ORDER BY id',
                    'items':'SELECT invoice_id,notes FROM invoice_items ORDER BY invoice_id,id',
                    'payments':'SELECT invoice_id,notes FROM payment ORDER BY invoice_id,id',
                    'postauth':f"SELECT `{'user' if FR1 else 'username'}` AS username,reply FROM radpostauth ORDER BY username,id",
                    'acct':'SELECT username,acctsessionid,AcctStartTime FROM radacct ORDER BY username,radacctid',
                }.items()}
            session()
            original=state()
            assert request(authenticated=False)[0].endswith('login.php')
            session(9002)
            assert request()[0].endswith('home-error.php')
            session()
            assert 'deleted' not in send({'username':'unit-a'},token='invalid').lower()
            assert state()==original
            print('PASS auth, ACL, CSRF',file=sys.stderr)
            if not BASELINE:
                for payload in ({'username[]':['unit-a','missing']},
                                {'username[]':['unit-a'],'delradacct[]':'yes'},
                                {'username[]':['unit-a'],'attribute':'10__Reply-Message','tablename':'radgroupcheck'},
                                {'username[]':['unit-a'],'attribute':'13__Reply-Message','tablename':'radreply'},
                                {'clearSessionsUsers[]':['unit-a||2020-01-01 01:02:03','bad']},
                                {'username[]':['unit-a'],'clearSessionsUsers[]':['unit-b||2020-01-01 01:02:04']}):
                    response=send(payload)
                    assert any(mark in response for mark in ('no changes were saved', 'no longer belongs', 'selected user is missing')), (payload,[x for x in response.splitlines() if 'alert-' in x or 'selected' in x][-8:])
                    assert state()==original,payload
                sql("CREATE TRIGGER fixture_late_failure BEFORE DELETE ON radcheck FOR EACH ROW "
                    "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='late failure'")
                failed=send({'username[]':['unit-a','unit-b'],'delradacct':'yes'})
                assert 'no changes were saved' in failed and 'late failure' not in failed
                assert state()==original
                sql('DROP TRIGGER fixture_late_failure')
                print('PASS invalid/stale selection and late rollback across all tables',file=sys.stderr)
            # The two display-backed attribute actions are independent of account deletion.
            response=send({'username':'unit-attr','attribute':'13__Reply-Message','tablename':'radreply'})
            assert 'Deleted attribute' in response,response[:600]
            response=send({'username':'unit-attr','attribute':'5__Reply-Message','tablename':'radcheck'})
            assert 'Deleted attribute' in response,response[:600]
            attr_state=state()
            assert 'unit-attr' not in attr_state['reply'] and '5\t' not in attr_state['check']
            if not BASELINE:
                assert 'Cannot delete the last check' in send(
                    {'username':'unit-attr','attribute':'4__Cleartext-Password','tablename':'radcheck'})
                assert state()==attr_state
                print('PASS last-auth-attribute guard scoped to selected user',file=sys.stderr)
            response=send({'clearSessionsUsers[]':['unit-keep||2020-01-01 01:02:05']})
            assert 'session(s) have been cleaned' in response,response[:600]
            sessions_state=state()
            assert 'unit-keep\tk1' not in sessions_state['acct']
            if not BASELINE:
                assert 'no longer exists' in send({'clearSessionsUsers[]':['unit-keep||2020-01-01 01:02:05']})
                assert state()==sessions_state
            response=send({'username[]':['unit-a','unit-b'],'delradacct':'no'})
            assert '2 user(s) have been deleted' in response,response[:600]
            after=state()
            for key in ('check','reply','groups','user','bill','postauth'):
                assert 'unit-a\t' not in after[key] and 'unit-b\t' not in after[key],key
                assert 'unit-keep' in after[key],key
            assert 'unit-a\ta1' in after['acct'] and 'unit-b\tb1' in after['acct']
            reference=os.environ.get('USER_DELETE_REFERENCE')
            if reference:
                common=('check','reply','groups','user','bill','postauth','acct')
                projected={key:after[key] for key in common}
                if BASELINE:
                    Path(reference).write_text(json.dumps(projected,sort_keys=True))
                else:
                    assert projected==json.loads(Path(reference).read_text()), 'PEAR/PDO state mismatch'
            if not BASELINE:
                for key in ('invoice','items','payments'):
                    assert not any(line.startswith(('50\t','51\t','53\t')) for line in after[key].splitlines()),key
                    assert any(line.startswith('52\t') for line in after[key].splitlines()),key
                assert state()==after
                stale=send({'clearSessionsUsers[]':[
                    'unit-b||2020-01-01 01:02:04','missing||2020-01-01 01:02:05']})
                assert 'no longer exists' in stale
                assert state()==after
                assert 'selected user is missing' in send({'username':'unit-a'})
                assert state()==after
                special=send({'username':"unit-50% O'Reilly",'delradacct':'yes'})
                assert '1 user(s) have been deleted' in special
                final=state()
                assert "unit-50% O'Reilly" not in '\n'.join(final.values())
                assert 'unit-a\ta1' in final['acct'] and 'unit-b\tb1' in final['acct']
                assert '52\t' in final['invoice'] and 'unit-keep' in final['check']
                sql("INSERT INTO radcheck (username,attribute,op,value) VALUES "
                    "('unit-only','Cleartext-Password',':=','fixture')")
                assert '1 user(s) have been deleted' in send({'username':'unit-only'})
                assert 'unit-only' not in '\n'.join(state().values())
                sql("INSERT INTO radacct (username,acctsessionid,acctuniqueid,AcctStartTime,AcctStopTime) "
                    "VALUES ('unit%+session||part','sx','fixture-sx','2020-01-01 03:04:05',NULL)")
                assert 'session(s) have been cleaned' in send({
                    'clearSessionsUsers[]':['unit%+session||part||2020-01-01 03:04:05']})
                assert 'unit%+session||part' not in state()['acct']
                before_engine=state()
                sql('ALTER TABLE radpostauth ENGINE=MyISAM')
                assert 'no changes were saved' in send({'username':'unit-keep'})
                assert state()==before_engine
                sql('ALTER TABLE radpostauth ENGINE=InnoDB')
                print('PASS orphan invoice cleanup, exact special name, empty billing, session identity and engine preflight',file=sys.stderr)
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            assert 'PHP Fatal error' not in logs.stderr
            print('PASS UNIT-019 '+('PEAR baseline' if BASELINE else 'PDO candidate'))
        finally:
            for item in (WEB,DB):run('docker','rm','-f','-v',item,check=False)
            run('docker','network','rm',NETWORK,check=False)
            run('docker','run','--rm','-v',f'{fixture}:/fixtures','--entrypoint','sh',
                'lirantal/daloradius','-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')

if __name__=='__main__':main()
