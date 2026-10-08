#!/usr/bin/env python3
"""UNIT-013: disposable HTTP/PHP/MariaDB POS user deletion A/B and rollback."""
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
BASELINE=os.environ.get('POS_DELETE_BASELINE')=='1'
DB,WEB,NETWORK=h.DB,h.WEB,h.NETWORK
run,sql,wait_for=h.run,h.sql,h.wait_for


def main():
    scratch=Path.home()/'.hermes/cache/scratch'
    scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch,prefix='dalo-pos-delete-') as tmp:
        fixture=Path(tmp)
        shutil.copytree(ROOT/'app',fixture/'app',symlinks=True)
        if BASELINE:
            old=subprocess.check_output(['git','show','c9c8ae386:app/operators/bill-pos-del.php'],cwd=ROOT)
            (fixture/'app/operators/bill-pos-del.php').write_bytes(old)
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            sql("""INSERT INTO operators_acl (operator_id,file,access) VALUES
                     (9001,'bill_pos_del',1),(9002,'bill_pos_del',0);
                   INSERT INTO radcheck (id,username,attribute,op,value) VALUES
                     (1,'unit-alice','Cleartext-Password',':=','secret'),
                     (2,'unit-keep','Cleartext-Password',':=','keep'),
                     (3,'unit-50% O''Reilly','Cleartext-Password',':=','quoted');
                   INSERT INTO radreply (username,attribute,op,value) VALUES
                     ('unit-alice','Reply-Message',':=','hi'),
                     ('unit-keep','Reply-Message',':=','stay'),
                     ('unit-50% O''Reilly','Reply-Message',':=','quoted');
                   INSERT INTO radusergroup (username,groupname,priority) VALUES
                     ('unit-alice','gold',0),('unit-keep','silver',1),
                     ('unit-50% O''Reilly','gold',0);
                   INSERT INTO userinfo (id,username,firstname) VALUES
                     (10,'unit-alice','Alice'),(11,'unit-keep','Keep'),
                     (12,'unit-50% O''Reilly','Quoted');
                   INSERT INTO userbillinfo (id,username,contactperson) VALUES
                     (20,'unit-alice','Alice'),(21,'unit-keep','Keep'),
                     (22,'unit-50% O''Reilly','Quoted'),(23,'unit-alice','Second billing');
                   INSERT INTO invoice (id,user_id,date,status_id,type_id,notes) VALUES
                     (50,20,'2020-01-02',1,1,'first'),
                     (51,23,'2020-01-02',1,1,'second'),
                     (52,21,'2020-01-02',1,1,'keep'),
                     (53,22,'2020-01-02',1,1,'quoted');
                   INSERT INTO invoice_items (invoice_id,plan_id,amount,tax_amount,notes) VALUES
                     (50,1,2.50,0.25,'first'),(51,1,3.50,0,'second'),
                     (52,1,4.50,0,'keep'),(53,1,5.50,0,'quoted');
                   INSERT INTO payment (invoice_id,amount,date,notes) VALUES
                     (50,1.00,'2020-01-02','first'),(51,2.00,'2020-01-02','second'),
                     (52,3.00,'2020-01-02','keep'),(53,4.00,'2020-01-02','quoted');
                   INSERT INTO radacct (username,acctsessionid,acctuniqueid) VALUES
                     ('unit-alice','one','unit-one'),('unit-keep','stay','unit-stay'),
                     ('unit-50% O''Reilly','quoted','unit-quoted');""")
            config=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,val in {'CONFIG_DB_HOST':DB,'CONFIG_DB_USER':'root',
                            'CONFIG_DB_PASS':'','CONFIG_DB_NAME':'radius'}.items():
                config+='\n$configValues['+repr(key)+'] = '+repr(val)+';\n'
            (fixture/'app/common/includes/daloradius.conf.php').write_text(config)
            (fixture/'session.php').write_text('''<?php
session_name('daloradius_operator_sid'); session_id($argv[1]); session_start();
$_SESSION = ['daloradius_logged_in'=>true,'operator_id'=>(int)$argv[2],
'operator_user'=>'delete-fixture','location_name'=>'default','time'=>time()];
session_write_close();
''')
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{fixture}:/fixtures','-w','/fixtures/app/operators',
                '--entrypoint','php','lirantal/daloradius','-d','display_errors=0',
                '-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/bill-pos-del.php'
            wait_for(lambda: urllib.request.urlopen(base,timeout=10),'PHP HTTP')
            sid=secrets.token_hex(16)
            def session(operator=9001):
                run('docker','exec',WEB,'php','/fixtures/session.php',sid,str(operator))
            def request(data=None,authenticated=True):
                req=urllib.request.Request(base,
                    data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),
                    headers={'Cookie':'daloradius_operator_sid='+sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req,timeout=20) as response:
                        return response.url,response.read().decode()
                except urllib.error.HTTPError as error:
                    return error.url,error.read().decode()
            def csrf():
                return next(f['csrf_token'] for f in Forms(request()[1]).forms if 'csrf_token' in f)
            def delete(username='unit-alice',accounting='no',token=None,extra=None):
                data={'username':username,'delradacct':accounting,
                      'csrf_token':csrf() if token is None else token}
                if extra: data.update(extra)
                return request(data)[1]
            def state():
                return {key:sql(query) for key,query in {
                    'check':'SELECT username,attribute FROM radcheck ORDER BY username,id',
                    'reply':'SELECT username,attribute FROM radreply ORDER BY username,id',
                    'groups':'SELECT username,groupname,priority FROM radusergroup ORDER BY username,id',
                    'user':'SELECT id,username,firstname FROM userinfo ORDER BY id',
                    'billing':'SELECT id,username,contactperson FROM userbillinfo ORDER BY id',
                    'invoice':'SELECT id,user_id,notes FROM invoice ORDER BY id',
                    'items':'SELECT invoice_id,notes FROM invoice_items ORDER BY invoice_id,id',
                    'payments':'SELECT invoice_id,notes FROM payment ORDER BY invoice_id,id',
                    'acct':'SELECT username,acctsessionid FROM radacct ORDER BY username,radacctid',
                }.items()}
            session()
            before=state()
            assert request(authenticated=False)[0].endswith('/login.php')
            session(9002)
            assert request()[0].endswith('/home-error.php')
            assert request({'username':'unit-alice','csrf_token':'x'})[0].endswith('/home-error.php')
            session()
            assert 'Deleted user' not in delete(token='invalid')
            assert state()==before
            print('PASS auth, ACL and CSRF prevent deletion',file=sys.stderr)
            if not BASELINE:
                for bad in ({'username[]':['unit-alice']},
                            {'username':'unit-missing'},
                            {'delradacct[]':['yes']},
                            {'delradacct':'wrong'}):
                    delete(extra=bad)
                    assert state()==before,bad
                sql('CREATE TABLE fixture_block (radcheck_id INT UNSIGNED NOT NULL, '
                    'FOREIGN KEY (radcheck_id) REFERENCES radcheck(id)) ENGINE=InnoDB')
                sql('INSERT INTO fixture_block VALUES (1)')
                failed=delete(accounting='yes')
                assert 'Failed to delete user' in failed and 'fixture_block' not in failed
                assert state()==before
                sql('DROP TABLE fixture_block')
                print('PASS late account failure rolls back billing, invoice children and RADIUS',file=sys.stderr)
            html=delete()
            assert 'Deleted user' in html,html[:600]
            after=state()
            for key in ('check','reply','groups','user','billing'):
                assert 'unit-alice' not in after[key],(key,after[key])
                assert 'unit-keep' in after[key],(key,after[key])
            assert 'unit-alice\tone' in after['acct']
            if not BASELINE:
                baseline=json.loads(Path(os.environ['POS_DELETE_BASELINE_JSON']).read_text()) if os.environ.get('POS_DELETE_BASELINE_JSON') else None
                if baseline:
                    for key in ('check','reply','groups','user','billing','acct'):
                        assert after[key]==baseline['after'][key],key
                    assert '50\t' in baseline['after']['invoice']
                    assert '51\t' in baseline['after']['invoice']
                for key in ('invoice','items','payments'):
                    assert not any(line.startswith(('50\t','51\t')) for line in after[key].splitlines()),(key,after[key])
                    assert any(line.startswith('52\t') for line in after[key].splitlines()),key
                print('PASS account parity, previously orphaned invoices/payments/items now deleted',file=sys.stderr)
                assert 'Deleted user' not in delete()
                assert state()==after
                quoted=delete(username="unit-50% O'Reilly",accounting='yes')
                assert 'Deleted user' in quoted and '&lt;' not in quoted
                final=state()
                assert "unit-50% O'Reilly" not in '\n'.join(final.values())
                assert 'unit-keep' in final['acct'] and '52\t' in final['invoice']
                print('PASS repeat deletion rejected; quoted/percent username and accounting option',file=sys.stderr)
                sql("INSERT INTO radcheck (username,attribute,op,value) VALUES ('unit-only','Cleartext-Password',':=','empty')")
                empty=delete(username='unit-only')
                assert 'Deleted user' in empty
                assert 'unit-only' not in '\n'.join(state().values())
                print('PASS account without billing or invoices',file=sys.stderr)
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            assert 'PHP Fatal error' not in logs.stderr, '\n'.join(x for x in logs.stderr.splitlines() if 'PHP Fatal error' in x)
            print(json.dumps({'after':after}))
        finally:
            for name in (WEB,DB): run('docker','rm','-f','-v',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            run('docker','run','--rm','-v',f'{fixture}:/fixtures','--entrypoint','sh',
                'lirantal/daloradius','-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')

if __name__=='__main__': main()
