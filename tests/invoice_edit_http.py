#!/usr/bin/env python3
"""UNIT-006: invoice edit A/B on disposable PHP HTTP and MariaDB."""
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

ROOT=Path(os.environ.get('INVOICE_EDIT_ROOT', Path(__file__).resolve().parents[1]))
CANDIDATE=(ROOT/'app/operators/library/invoice_edit.php').exists()
DB,WEB,NETWORK=h.DB,h.WEB,h.NETWORK
run,sql,wait_for=h.run,h.sql,h.wait_for

def main():
    scratch=Path.home()/'.hermes/cache/scratch'
    scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch,prefix='dalo-invoice-edit-') as tmp:
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
                  (9001,'bill_invoice_edit',1),(9002,'bill_invoice_edit',0);
                INSERT INTO userinfo (id,username) VALUES (10,'alice'),(11,'bob');
                INSERT INTO userbillinfo (id,username,contactperson) VALUES
                  (10,'alice','Alice'),(11,'bob','Bob');
                INSERT INTO billing_plans (id,planName,planActive) VALUES
                  (41,'First plan','yes'),(42,'Other plan','yes'),(43,'Inactive','no');
                INSERT INTO invoice (id,user_id,date,status_id,type_id,notes,
                  creationdate,creationby,updatedate,updateby) VALUES
                  (50,10,'2020-01-02',1,1,'original','2020-01-01','original','2020-01-01','original');
                INSERT INTO invoice_items (id,invoice_id,plan_id,amount,tax_amount,notes,
                  creationdate,creationby) VALUES
                  (70,50,42,2.50,0.25,'old one','2020-01-01','original'),
                  (71,50,42,4.00,0.00,'old two','2020-01-01','original');
                INSERT INTO payment (invoice_id,amount,date,notes) VALUES
                  (50,1.00,'2020-01-01','old payment');""")
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
'operator_user'=>'edit-fixture','location_name'=>$argv[3],'time'=>time()];
session_write_close();
''')
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{fixture}:/fixtures','-w','/fixtures/app/operators',
                '--entrypoint','php','lirantal/daloradius','-d','display_errors=0',
                '-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/bill-invoice-edit.php'
            wait_for(lambda: urllib.request.urlopen(base,timeout=10),'PHP HTTP')
            sid=secrets.token_hex(16)
            def session(operator=9001,location='default'):
                run('docker','exec',WEB,'php','/fixtures/session.php',sid,str(operator),location)
            def request(data=None, authenticated=True, query='?invoice_id=50'):
                req=urllib.request.Request(base+query,
                    data=None if data is None else urllib.parse.urlencode(data).encode(),
                    headers={'Cookie':'daloradius_operator_sid='+sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req,timeout=15) as response:
                        return response.url,response.read().decode()
                except urllib.error.HTTPError as error:
                    return error.url,error.read().decode()
            def csrf():
                return next(f['csrf_token'] for f in Forms(request()[1]).forms if 'csrf_token' in f)
            def submit(extra=None,token=None):
                fields={'csrf_token':csrf() if token is None else token,'invoice_id':'50',
                        'user_id':'user-11','invoice_type_id':'type-2',
                        'invoice_status_id':'status-4','invoice_date':'2020-02-03',
                        'invoice_notes':"Changed O'Reilly",'item70[plan]':'42',
                        'item70[amount]':'5.50','item70[tax]':'0.50',
                        'item70[notes]':"New 'one'",'item72[plan]':'41',
                        'item72[amount]':'7.00','item72[tax]':'0',
                        'item72[notes]':'new two'}
                if extra: fields.update(extra)
                return request(fields)[1]
            def header():
                return sql('SELECT user_id,DATE(date),status_id,type_id,notes,updatedate,updateby FROM invoice WHERE id=50')
            def items():
                return sql('SELECT plan_id,amount,tax_amount,notes FROM invoice_items WHERE invoice_id=50 ORDER BY id')
            def payment():
                return sql('SELECT amount,DATE(date),notes FROM payment WHERE invoice_id=50')
            original=(header(),items(),payment())
            session()
            assert request(authenticated=False)[0].endswith('/login.php')
            session(9002)
            assert request()[0].endswith('/home-error.php')
            session()
            assert 'Successfully' not in submit(token='invalid')
            assert (header(),items(),payment())==original
            print('PASS login/ACL/CSRF never mutate',file=sys.stderr)
            get=request()[1]
            assert 'name="item70[plan]"' in get
            if CANDIDATE:
                # A pre-existing plan must be selected, not the first active plan.
                assert 'value="42" selected' in get
            edited=submit()
            assert 'Successfully' in edited,edited[:500]
            item_snapshot=items()
            assert item_snapshot=="42\t5.50\t0.50\tNew 'one'\n41\t7.00\t0.00\tnew two",item_snapshot
            assert payment()==original[2]
            if CANDIDATE:
                assert header().startswith("11\t2020-02-03\t4\t2\tChanged O'Reilly\t"),header()
                if os.environ.get('INVOICE_EDIT_BASELINE_JSON'):
                    baseline=json.loads(Path(os.environ['INVOICE_EDIT_BASELINE_JSON']).read_text())
                    assert item_snapshot==baseline['items'],(item_snapshot,baseline)
                print('PASS replaced items match PEAR; header now actually updates (legacy WHERE id=0 defect)',file=sys.stderr)
                before=(header(),items(),payment())
                for changed in ({'item72[amount]':'invalid'}, {'item72[plan]':'43'},
                                {'item72[plan]':'999'}, {'invoice_date':'2020-13-01'},
                                {'invoice_status_id':'status-999'}, {'invoice_notes[]':'array'},
                                {'invoice_id[]':'50'}):
                    submit(changed)
                    assert (header(),items(),payment())==before,changed
                print('PASS malformed header/item/plan/id preserve entire invoice',file=sys.stderr)
                sql('ALTER TABLE invoice_items ADD UNIQUE KEY fixture_unique_notes (notes)')
                failed=submit({'item70[notes]':'two duplicates',
                               'item72[notes]':'two duplicates'})
                assert 'Failed to update' in failed and 'Duplicate entry' not in failed
                assert (header(),items(),payment())==before
                sql('ALTER TABLE invoice_items DROP KEY fixture_unique_notes')
                print('PASS second item failure rolls back header, delete and first insert',file=sys.stderr)
                # Replace with zero children; preserve payment and header.
                token=csrf()
                empty=request({'csrf_token':token,'invoice_id':'50'})[1]
                assert 'with 0 item(s)' in empty
                assert items()=='' and payment()==original[2]
                print('PASS empty replacement deletes prior items atomically',file=sys.stderr)
                session(9001,'named')
                assert 'Successfully' in submit()
                assert items()==item_snapshot
                print('PASS named location',file=sys.stderr)
            import subprocess
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            warnings=[x for x in logs.stderr.splitlines() if 'PHP Warning:' in x]
            if not CANDIDATE:
                warnings=[x for x in warnings if 'Undefined variable $invoice ' not in x]
            assert 'PHP Fatal error' not in logs.stderr and not warnings, '\n'.join(warnings[-5:])
            print(json.dumps({'items':item_snapshot,'header':header()}))
        finally:
            for name in (WEB,DB): run('docker','rm','-f','-v',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            run('docker','run','--rm','-v',f'{fixture}:/fixtures','--entrypoint','sh',
                'lirantal/daloradius','-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')

if __name__=='__main__': main()
