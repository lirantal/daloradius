#!/usr/bin/env python3
"""UNIT-005: isolated HTTP/PHP/MariaDB invoice creation and rollback."""
import json
import os
from pathlib import Path
import re
import secrets
import shutil
import sys
import tempfile
import urllib.error
import urllib.parse
import urllib.request
import user_actions_http as h
from acct_maintenance_http import Forms

ROOT = Path(os.environ.get('INVOICE_ROOT', Path(__file__).resolve().parents[1]))
CANDIDATE = (ROOT/'app/operators/library/invoice_create.php').exists()
DB, WEB, NETWORK = h.DB, h.WEB, h.NETWORK
run, sql, wait_for = h.run, h.sql, h.wait_for

def main():
    scratch = Path.home() / '.hermes/cache/scratch'
    scratch.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch, prefix='dalo-invoice-') as tmp:
        fixture = Path(tmp)
        shutil.copytree(ROOT/'app', fixture/'app', symlinks=True)
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'), 'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            sql("""INSERT INTO operators_acl (operator_id,file,access) VALUES
                (9001,'bill_invoice_new',1),(9002,'bill_invoice_new',0);
                INSERT INTO userinfo (id,username) VALUES (10,'alice'),(11,'bob');
                INSERT INTO userbillinfo (id,username,contactperson) VALUES (10,'alice','Alice');
                INSERT INTO billing_plans (id,planName,planActive) VALUES
                  (41,'Lab plan','yes'),(42,'Old plan','no');""")
            config = (ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,val in {'CONFIG_DB_HOST':DB,'CONFIG_DB_USER':'root','CONFIG_DB_PASS':'',
                            'CONFIG_DB_NAME':'radius'}.items():
                config += '\n$configValues['+repr(key)+'] = '+repr(val)+';\n'
            config += ("\n$configValues['CONFIG_LOCATIONS']['named'] = ["
                       f"'Engine'=>'mysqli','Hostname'=>{DB!r},'Port'=>'3306',"
                       "'Database'=>'radius','Username'=>'root','Password'=>''];\n")
            (fixture/'app/common/includes/daloradius.conf.php').write_text(config)
            (fixture/'session.php').write_text('''<?php
session_name('daloradius_operator_sid'); session_id($argv[1]); session_start();
$_SESSION = ['daloradius_logged_in'=>true,'operator_id'=>(int)$argv[2],
'operator_user'=>'invoice-fixture','location_name'=>$argv[3],'time'=>time()];
session_write_close();
''')
            run('docker','run','-d','--name',WEB,'--network',NETWORK,'-v',f'{fixture}:/fixtures',
                '-w','/fixtures/app/operators','--entrypoint','php','lirantal/daloradius',
                '-d','display_errors=0','-S','0.0.0.0:8080','-t','.')
            ip = run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base = 'http://'+ip+':8080/bill-invoice-new.php'
            wait_for(lambda: urllib.request.urlopen(base,timeout=10), 'PHP HTTP')
            sid = secrets.token_hex(16)
            def set_session(operator=9001, location='default'):
                run('docker','exec',WEB,'php','/fixtures/session.php',sid,str(operator),location)
            def request(data=None, authenticated=True):
                req = urllib.request.Request(base + '?user_id=10',
                    data=None if data is None else urllib.parse.urlencode(data).encode(),
                    headers={'Cookie':'daloradius_operator_sid='+sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req,timeout=15) as res:
                        return res.url,res.read().decode()
                except urllib.error.HTTPError as err:
                    return err.url,err.read().decode()
            def token():
                return next(f['csrf_token'] for f in Forms(request()[1]).forms if 'csrf_token' in f)
            def submit(extra=None, csrf=None):
                data={'csrf_token':token() if csrf is None else csrf,'user_id':'10',
                      'invoice_type_id':'1','invoice_status_id':'1','invoice_date':'2020-01-02',
                      'invoice_notes':"O'Reilly & test",
                      'item123[plan]':'41','item123[amount]':'2.50',
                      'item123[tax]':'0.25','item123[notes]':"It's a test"}
                if extra: data.update(extra)
                return request(data)[1]
            def rows():
                return sql("""SELECT i.user_id,DATE(i.date),i.status_id,i.type_id,i.notes,
                    COALESCE(ii.plan_id,0),COALESCE(ii.amount,0),COALESCE(ii.tax_amount,0),
                    COALESCE(ii.notes,'') FROM invoice i LEFT JOIN invoice_items ii
                    ON ii.invoice_id=i.id ORDER BY i.id,ii.id""")
            set_session()
            assert request(authenticated=False)[0].endswith('/login.php')
            set_session(9002)
            assert request()[0].endswith('/home-error.php')
            set_session()
            assert 'Successfully added' not in submit(csrf='bad')
            assert rows() == ''
            print('PASS unauthenticated, denied ACL and invalid CSRF do not write',file=sys.stderr)
            assert 'Successfully added new invoice' in submit(), 'valid create failed'
            initial = rows()
            if CANDIDATE and os.environ.get('INVOICE_BASELINE_JSON'):
                baseline = json.loads(Path(os.environ['INVOICE_BASELINE_JSON']).read_text())
                assert initial == baseline['initial'], (initial,baseline)
                print('PASS: normalized invoice and child rows match PEAR baseline',file=sys.stderr)
            assert "O'Reilly" in initial and "It's a test" in initial, initial
            assert sql('SELECT COUNT(*) FROM invoice_items') == '1'
            assert sql('SELECT COUNT(DISTINCT invoice_id) FROM invoice_items') == '1'
            print('PASS header + quoted child written with linked ID',file=sys.stderr)
            # Blank default row followed by two valid items: no partial early return.
            if CANDIDATE:
                html = submit({'item123[amount]':'','item123[tax]':'','item123[notes]':'',
                               'item124[plan]':'41','item124[amount]':'3.00',
                               'item124[tax]':'0','item124[notes]':'second',
                               'item125[plan]':'41','item125[amount]':'4.00',
                               'item125[tax]':'0','item125[notes]':'third'})
                assert 'with 2 item(s)' in html, html[:350]
                assert sql('SELECT COUNT(*) FROM invoice_items') == '3'
                before = rows()
                assert 'Failed to add' in submit({'item123[amount]':'not-number'})
                assert rows() == before
                for tampered in ({'invoice_type_id':'999'}, {'user_id':'999'},
                                 {'invoice_status_id':'999'}, {'invoice_date':'2020-13-42'},
                                 {'invoice_notes[]':'array instead of scalar'},
                                 {'invoice_status_id[]':'array instead of scalar'},
                                 {'invoice_date[]':'array instead of scalar'},
                                 {'item123[plan]':'999'}, {'item123[plan]':'42'},
                                 {'item123[amount]':"1' OR 1=1 --"},
                                 {'item123[notes][]':'array instead of scalar'}):
                    submit(tampered)
                    assert rows() == before, tampered
                assert 'Successfully added new invoice' in submit({'item123[amount]':'',
                    'item123[tax]':'', 'item123[notes]':''})
                assert sql('SELECT COUNT(*) FROM invoice_items') == '3'
                assert sql('SELECT COUNT(*) FROM invoice') == '3'
                print('PASS invalid type/user/item rejected; empty invoice allowed',file=sys.stderr)
                before = rows()
                print('PASS blank row skipped, malformed item rejects all writes',file=sys.stderr)
                # A database exception after the first child must undo header and child.
                sql('ALTER TABLE invoice_items ADD UNIQUE KEY fixture_unique_notes (notes)')
                bad = submit({'item123[notes]':'duplicated in request',
                              'item124[plan]':'41','item124[amount]':'1.00',
                              'item124[tax]':'0','item124[notes]':'duplicated in request'})
                assert 'Failed to add' in bad and 'Duplicate entry' not in bad, bad[:350]
                assert rows() == before
                sql('ALTER TABLE invoice_items DROP KEY fixture_unique_notes')
                print('PASS later child SQL failure rolls back header and earlier child',file=sys.stderr)
                set_session(9001,'named')
                assert 'Successfully added new invoice' in submit()
                assert sql('SELECT COUNT(*) FROM invoice_items') == '4'
                print('PASS named location',file=sys.stderr)
            print(json.dumps({'initial':initial,'count':sql('SELECT COUNT(*) FROM invoice')}))
            import subprocess
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            fatal=[x for x in logs.stderr.splitlines() if 'PHP Fatal error' in x]
            allowed_warnings = {'invoice_date','invoice_notes','invoice_type_id',
                'this_amount','this_notes','this_tax_amount'}  # Confirmed on PEAR baseline.
            warnings = [x for x in logs.stderr.splitlines() if 'PHP Warning:' in x]
            new_warnings = [x for x in warnings if not any(
                'Undefined variable $'+name+' ' in x and 'bill-invoice-new.php' in x
                for name in allowed_warnings)]
            assert not fatal and not new_warnings, '\n'.join((fatal+new_warnings)[-5:])
        finally:
            for name in (WEB,DB): run('docker','rm','-f','-v',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            run('docker','run','--rm','-v',f'{fixture}:/fixtures','--entrypoint','sh',
                'lirantal/daloradius','-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')

if __name__=='__main__': main()
