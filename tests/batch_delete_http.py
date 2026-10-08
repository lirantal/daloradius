#!/usr/bin/env python3
"""UNIT-015: disposable HTTP/PHP/MariaDB batch deletion A/B + rollback."""
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
BASELINE=os.environ.get('BATCH_DELETE_BASELINE')=='1'
DB,WEB,NETWORK=h.DB,h.WEB,h.NETWORK
run,sql,wait_for=h.run,h.sql,h.wait_for


def main():
    scratch=Path.home()/'.hermes/cache/scratch'
    scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch,prefix='dalo-batch-delete-') as tmp:
        fixture=Path(tmp)
        shutil.copytree(ROOT/'app',fixture/'app',symlinks=True)
        if BASELINE:
            old=subprocess.check_output(['git','show','40902bd30:app/operators/mng-batch-del.php'],cwd=ROOT)
            (fixture/'app/operators/mng-batch-del.php').write_bytes(old)
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            sql("""INSERT INTO operators_acl (operator_id,file,access) VALUES
                   (9001,'mng_batch_del',1),(9002,'mng_batch_del',0);
                   INSERT INTO batch_history (id,batch_name) VALUES
                     (1,'First batch'),(2,'Second batch'),(3,'Keep batch'),
                     (4,'Plan 50% O''Reilly'),(5,'Empty batch');
                   INSERT INTO userbillinfo (id,username,batch_id,contactperson) VALUES
                     (10,'unit-alice',1,'Alice'),(11,'unit-alice2',1,'Alice 2'),
                     (20,'unit-bob',2,'Bob'),(30,'unit-keep',3,'Keep'),
                     (40,'unit-dave',4,'Dave');
                   INSERT INTO userinfo (username,firstname) VALUES
                     ('unit-alice','Alice'),('unit-alice2','Alice 2'),
                     ('unit-bob','Bob'),('unit-keep','Keep'),('unit-dave','Dave');
                   INSERT INTO radcheck (username,attribute,op,value) VALUES
                     ('unit-alice','Cleartext-Password',':=','one'),
                     ('unit-alice2','Cleartext-Password',':=','two'),
                     ('unit-bob','Cleartext-Password',':=','bob'),
                     ('unit-keep','Cleartext-Password',':=','keep'),
                     ('unit-dave','Cleartext-Password',':=','dave');
                   INSERT INTO radreply (username,attribute,op,value) VALUES
                     ('unit-alice','Reply-Message',':=','hi'),
                     ('unit-alice2','Reply-Message',':=','hi'),
                     ('unit-bob','Reply-Message',':=','hi'),
                     ('unit-keep','Reply-Message',':=','hi'),
                     ('unit-dave','Reply-Message',':=','hi');
                   INSERT INTO radusergroup (username,groupname,priority) VALUES
                     ('unit-alice','gold',0),('unit-alice2','gold',0),
                     ('unit-bob','gold',0),('unit-keep','gold',0),('unit-dave','gold',0);
                   INSERT INTO radacct (username,acctsessionid,acctuniqueid) VALUES
                     ('unit-alice','one','one'),('unit-alice2','two','two'),
                     ('unit-bob','bob','bob'),('unit-keep','keep','keep'),
                     ('unit-dave','dave','dave');
                   INSERT INTO radpostauth (username,pass,reply) VALUES
                     ('unit-alice','one','ok'),('unit-alice2','two','ok'),
                     ('unit-bob','bob','ok'),('unit-keep','keep','ok'),
                     ('unit-dave','dave','ok');
                   INSERT INTO invoice (id,user_id,date,status_id,type_id,notes) VALUES
                     (50,10,'2020-01-02',1,1,'first'),
                     (51,11,'2020-01-02',1,1,'second'),
                     (52,20,'2020-01-02',1,1,'bob'),
                     (53,30,'2020-01-02',1,1,'keep'),
                     (54,40,'2020-01-02',1,1,'dave');
                   INSERT INTO invoice_items (invoice_id,plan_id,amount,tax_amount,notes) VALUES
                     (50,1,2.50,0.25,'first'),(51,1,3.50,0,'second'),
                     (52,1,4.50,0,'bob'),(53,1,5.50,0,'keep'),
                     (54,1,6.50,0,'dave');
                   INSERT INTO payment (invoice_id,amount,date,notes) VALUES
                     (50,1.00,'2020-01-02','first'),(51,2.00,'2020-01-02','second'),
                     (52,3.00,'2020-01-02','bob'),(53,4.00,'2020-01-02','keep'),
                     (54,5.00,'2020-01-02','dave');""")
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
            base='http://'+ip+':8080/mng-batch-del.php'
            wait_for(lambda:urllib.request.urlopen(base,timeout=10),'PHP HTTP')
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
            def delete(ids=None,name='',token=None,extra=None):
                data={'csrf_token':csrf() if token is None else token}
                if ids is not None:data['batch_id[]' if isinstance(ids,list) else 'batch_id']=ids
                if name:data['batch_name']=name
                if extra:data.update(extra)
                return request(data)[1]
            def state():
                return {key:sql(query) for key,query in {
                    'batch':'SELECT id,batch_name FROM batch_history ORDER BY id',
                    'billing':'SELECT id,username,batch_id FROM userbillinfo ORDER BY id',
                    'user':'SELECT username FROM userinfo ORDER BY username,id',
                    'check':'SELECT username FROM radcheck ORDER BY username,id',
                    'reply':'SELECT username FROM radreply ORDER BY username,id',
                    'groups':'SELECT username FROM radusergroup ORDER BY username,id',
                    'acct':'SELECT username FROM radacct ORDER BY username,radacctid',
                    'postauth':'SELECT username FROM radpostauth ORDER BY username,id',
                    'invoice':'SELECT id,user_id FROM invoice ORDER BY id',
                    'items':'SELECT invoice_id FROM invoice_items ORDER BY invoice_id,id',
                    'payment':'SELECT invoice_id FROM payment ORDER BY invoice_id,id',
                }.items()}
            session()
            before=state()
            assert request(authenticated=False)[0].endswith('/login.php')
            session(9002)
            assert request()[0].endswith('/home-error.php')
            assert request({'batch_id':'1','csrf_token':'x'})[0].endswith('/home-error.php')
            session()
            assert 'Successfully deleted' not in delete('1',token='invalid')
            assert state()==before
            print('PASS login, ACL, CSRF prevent deletion',file=sys.stderr)
            if not BASELINE:
                for bad in (['1','bad'],['1','0'],['1','01'],['1',{'nested':'2'}]):
                    if isinstance(bad[-1],dict):
                        delete(extra={'batch_id[]':['1'],'batch_id[1][nested]':'2'})
                    else:delete(bad)
                    assert state()==before,bad
                delete(name='Missing batch')
                delete(['1','999'])
                assert state()==before
                print('PASS entire invalid/stale selection rejected before mutation',file=sys.stderr)
            result=delete('1')
            assert 'Successfully deleted 1 batch(es) [2 user(s)]' in result,result[:650]
            after=state()
            for key in ('batch','billing','user','check','reply','groups','acct','postauth'):
                if key in ('batch','billing'):
                    assert not any(line.startswith(('1\t','10\t','11\t')) for line in after[key].splitlines()),(key,after[key])
                else:
                    assert 'unit-alice' not in after[key],(key,after[key])
                assert 'unit-keep' in after[key] if key not in ('batch','billing') else True
            if not BASELINE:
                reference=os.environ.get('BATCH_DELETE_BASELINE_JSON')
                if reference:
                    baseline=json.loads(Path(reference).read_text())
                    for key in ('batch','billing','user','check','reply','groups','acct','postauth'):
                        assert after[key]==baseline['after'][key],(key,after[key],baseline['after'][key])
                    assert '50\t' in baseline['after']['invoice'] and '51\t' in baseline['after']['invoice']
                for key in ('invoice','items','payment'):
                    assert not any(line.startswith(('50\t','51\t')) if key=='invoice'
                                   else line in ('50','51') for line in after[key].splitlines()),(key,after[key])
                    assert any(line.startswith('53\t') if key=='invoice' else line=='53'
                               for line in after[key].splitlines()),key
                print('PASS account parity; formerly orphaned invoices and dependents removed',file=sys.stderr)
                sql("INSERT INTO userbillinfo (username,batch_id,contactperson) VALUES ('unit-bob',3,'Shared')")
                shared=state()
                assert 'no rows were deleted' in delete('2')
                assert state()==shared
                sql("DELETE FROM userbillinfo WHERE username='unit-bob' AND contactperson='Shared'")
                assert state()==after
                print('PASS username shared with unselected billing record rejected',file=sys.stderr)
                snapshot=state()
                sql('CREATE TABLE fixture_block (batch_id INT NOT NULL, '
                    'FOREIGN KEY (batch_id) REFERENCES batch_history(id)) ENGINE=InnoDB')
                sql('INSERT INTO fixture_block VALUES (4)')
                failed=delete(['2','4'])
                assert 'no rows were deleted' in failed and 'fixture_block' not in failed
                assert state()==snapshot
                sql('DROP TABLE fixture_block')
                print('PASS later batch failure rolls back first batch and both users',file=sys.stderr)
                result=delete(['2','2'],name="Plan 50% O'Reilly")
                assert 'Successfully deleted 2 batch(es) [2 user(s)]' in result,result[:650]
                final=state()
                for key in ('batch','billing','user','check','reply','groups','acct','postauth'):
                    assert 'unit-bob' not in final[key] and 'unit-dave' not in final[key],(key,final[key])
                assert '52\t' not in final['invoice'] and '54\t' not in final['invoice']
                assert '53\t' in final['invoice'] and '5\tEmpty batch' in final['batch']
                print('PASS multi selection and quoted/percent batch name; unselected preserved',file=sys.stderr)
                empty=delete('5')
                assert 'Successfully deleted 1 batch(es) [0 user(s)]' in empty
                print('PASS empty batch deletion',file=sys.stderr)
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            assert 'PHP Fatal error' not in logs.stderr,'\n'.join(x for x in logs.stderr.splitlines() if 'PHP Fatal error' in x)
            print(json.dumps({'after':after}))
        finally:
            for name in (WEB,DB):run('docker','rm','-f','-v',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            run('docker','run','--rm','-v',f'{fixture}:/fixtures','--entrypoint','sh',
                'lirantal/daloradius','-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')

if __name__=='__main__':main()
