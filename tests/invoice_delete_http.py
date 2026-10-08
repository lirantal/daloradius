#!/usr/bin/env python3
"""UNIT-007: disposable HTTP/PHP/MariaDB invoice deletion A/B and rollback."""
import json
import os
from pathlib import Path
import secrets
import shutil
import sys
import tempfile
import urllib.error
import urllib.parse
import urllib.request
import user_actions_http as h
from acct_maintenance_http import Forms

ROOT=Path(os.environ.get('INVOICE_DELETE_ROOT',Path(__file__).resolve().parents[1]))
CANDIDATE=(ROOT/'app/operators/library/invoice_delete.php').exists()
DB,WEB,NETWORK=h.DB,h.WEB,h.NETWORK
run,sql,wait_for=h.run,h.sql,h.wait_for

def main():
    scratch=Path.home()/'.hermes/cache/scratch'
    scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch,prefix='dalo-invoice-delete-') as tmp:
        fixture=Path(tmp)
        shutil.copytree(ROOT/'app',fixture/'app',symlinks=True)
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            sql("""SET sql_mode='';
                INSERT INTO operators_acl (operator_id,file,access) VALUES
                  (9001,'bill_invoice_del',1),(9002,'bill_invoice_del',0);
                INSERT INTO userinfo (id,username) VALUES (10,'alice');
                INSERT INTO userbillinfo (id,username,contactperson) VALUES (10,'alice','Alice');
                INSERT INTO invoice (id,user_id,date,status_id,type_id,notes) VALUES
                  (50,10,'2020-01-02',1,1,'first'),
                  (51,10,'2020-01-02',1,1,'second'),
                  (52,10,'2020-01-02',1,1,'third'),
                  (53,10,'2020-01-02',1,1,'unrelated');
                INSERT INTO invoice_items (invoice_id,plan_id,amount,tax_amount,notes) VALUES
                  (50,1,2.50,0.25,'first item'),(51,1,3.50,0.00,'second item'),
                  (52,1,4.50,0.00,'third item'),(53,1,5.50,0.00,'keep item');
                INSERT INTO payment (invoice_id,amount,date,notes) VALUES
                  (50,1.00,'2020-01-02','first payment'),
                  (51,2.00,'2020-01-02','second payment'),
                  (52,3.00,'2020-01-02','third payment'),
                  (53,4.00,'2020-01-02','keep payment');""")
            config=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,val in {'CONFIG_DB_HOST':DB,'CONFIG_DB_USER':'root',
                            'CONFIG_DB_PASS':'','CONFIG_DB_NAME':'radius'}.items():
                config+='\n$configValues['+repr(key)+'] = '+repr(val)+';\n'
            config+=("\n$configValues['CONFIG_LOCATIONS']['named'] = ["
                     f"'Engine'=>'mysqli','Hostname'=>{DB!r},'Port'=>'3306',"
                     "'Database'=>'radius','Username'=>'root','Password'=>''];\n")
            (fixture/'app/common/includes/daloradius.conf.php').write_text(config)
            (fixture/'session.php').write_text('''<?php
session_name('daloradius_operator_sid'); session_id($argv[1]); session_start();
$_SESSION = ['daloradius_logged_in'=>true,'operator_id'=>(int)$argv[2],
'operator_user'=>'delete-fixture','location_name'=>$argv[3],'time'=>time()];
session_write_close();
''')
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{fixture}:/fixtures','-w','/fixtures/app/operators',
                '--entrypoint','php','lirantal/daloradius','-d','display_errors=0',
                '-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/bill-invoice-del.php'
            wait_for(lambda: urllib.request.urlopen(base,timeout=10),'PHP HTTP')
            sid=secrets.token_hex(16)
            def session(operator=9001,location='default'):
                run('docker','exec',WEB,'php','/fixtures/session.php',sid,str(operator),location)
            def request(data=None,authenticated=True):
                req=urllib.request.Request(base,
                    data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),
                    headers={'Cookie':'daloradius_operator_sid='+sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req,timeout=15) as response:
                        return response.url,response.read().decode()
                except urllib.error.HTTPError as error:
                    return error.url,error.read().decode()
            def csrf():
                return next(f['csrf_token'] for f in Forms(request()[1]).forms if 'csrf_token' in f)
            def delete(ids,token=None,scalar=False):
                data={'csrf_token':csrf() if token is None else token,
                      'invoice_id' if scalar else 'invoice_id[]':ids}
                return request(data)[1]
            def state():
                return {'invoice':sql('SELECT id,notes FROM invoice ORDER BY id'),
                        'items':sql('SELECT invoice_id,amount,notes FROM invoice_items ORDER BY invoice_id,id'),
                        'payments':sql('SELECT invoice_id,amount,notes FROM payment ORDER BY invoice_id,id')}
            session()
            before=state()
            assert request(authenticated=False)[0].endswith('/login.php')
            session(9002)
            assert request()[0].endswith('/home-error.php')
            assert request({'csrf_token':'x','invoice_id[]':['50']})[0].endswith('/home-error.php')
            session()
            assert 'Deleted' not in delete(['50'],token='invalid')
            assert state()==before
            print('PASS authentication, ACL and CSRF prevent writes',file=sys.stderr)
            if CANDIDATE:
                for bad in ([],['0'],['-1'],['50','x'],['50','50 OR 1=1'],['50',''],
                            ['050'],['2147483648'],['50',{'nested':'51'}]):
                    if bad and isinstance(bad[-1],dict):
                        # Simulate PHP's nested array rather than urlencode(dict).
                        form={'csrf_token':csrf(),'invoice_id[]':['50'],
                              'invoice_id[1][nested]':'51'}
                        request(form)
                    else:
                        delete(bad)
                    assert state()==before,bad
                print('PASS entire malformed selection rejected before mutation',file=sys.stderr)
            html=delete('50',scalar=True)
            assert 'Deleted 1 invoice id(s)' in html,html[:350]
            single=state()
            assert all(not r.startswith('50\t') for r in single['invoice'].splitlines())
            assert all(not r.startswith('50\t') for r in single['items'].splitlines())
            assert '51\t' in single['invoice'] and '53\t' in single['invoice']
            if CANDIDATE:
                assert '50\t' not in single['payments']+'\n'
                baseline=json.loads(Path(os.environ['INVOICE_DELETE_BASELINE_JSON']).read_text()) if os.environ.get('INVOICE_DELETE_BASELINE_JSON') else None
                if baseline:
                    assert single['invoice']==baseline['single']['invoice']
                    assert single['items']==baseline['single']['items']
                    assert '50\t' in baseline['single']['payments']
                print('PASS single header/item parity; former orphan payment now removed',file=sys.stderr)
                # Already-deleted ID does not mutate unrelated rows.
                assert 'Deleted 0 invoice id(s)' in delete(['50'])
                assert state()==single
                # Reject a parent delete on the second invoice after deleting
                # the first invoice's payments, children, and parent.
                sql('CREATE TABLE fixture_block (invoice_id INT NOT NULL, '
                    'FOREIGN KEY (invoice_id) REFERENCES invoice(id)) ENGINE=InnoDB')
                sql('INSERT INTO fixture_block VALUES (52)')
                failed=delete(['51','52'])
                assert 'Failed to delete' in failed and 'fixture_block' not in failed
                assert state()==single
                sql('DROP TABLE fixture_block')
                print('PASS later parent failure rolls back earlier invoice + dependents',file=sys.stderr)
                html=delete(['52','51','51'])
                assert 'Deleted 2 invoice id(s), 2 item(s) and 2 payment(s)' in html,html[:350]
                assert state()['invoice']=='53\tunrelated'
                assert state()['items']=='53\t5.50\tkeep item'
                assert state()['payments']=='53\t4.00\tkeep payment'
                print('PASS multi-select deduplicated and all dependents removed',file=sys.stderr)
                session(9001,'named')
                assert 'Deleted 1 invoice id(s)' in delete(['53'])
                assert state()=={'invoice':'','items':'','payments':''}
                print('PASS named location',file=sys.stderr)
            else:
                assert any(r.startswith('50\t') for r in single['payments'].splitlines())
                multi=delete(['51','52'])
                after_multi=state()
                assert 'Deleted 1 invoice id(s)' in multi
                assert all(not r.startswith('51\t') for r in after_multi['invoice'].splitlines())
                assert any(r.startswith('52\t') for r in after_multi['invoice'].splitlines())
                assert any(r.startswith('51\t') for r in after_multi['payments'].splitlines())
                print('BASELINE: PEAR leaves orphan payments and batch IN string deletes only first ID',file=sys.stderr)
            import subprocess
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            warnings=[x for x in logs.stderr.splitlines() if 'PHP Warning:' in x]
            assert 'PHP Fatal error' not in logs.stderr and not warnings,'\n'.join(warnings[-5:])
            print(json.dumps({'single':single}))
        finally:
            for name in (WEB,DB): run('docker','rm','-f','-v',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            run('docker','run','--rm','-v',f'{fixture}:/fixtures','--entrypoint','sh',
                'lirantal/daloradius','-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')

if __name__=='__main__': main()
