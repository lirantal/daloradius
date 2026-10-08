#!/usr/bin/env python3
"""UNIT-004: isolated real HTTP/PHP/MariaDB user CSV export differential.

Run once with USER_REPORT_ROOT pointing at a PEAR baseline and again at the PDO
candidate. An optional USER_REPORT_BASELINE_JSON checks equivalent safe rows.
"""
import csv
import io
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
import user_actions_http as harness

ROOT = Path(os.environ.get('USER_REPORT_ROOT', Path(__file__).resolve().parents[1]))
run, sql, wait_for = harness.run, harness.sql, harness.wait_for
DB, WEB, NETWORK = harness.DB, harness.WEB, harness.NETWORK
IS_CANDIDATE = bool(os.environ.get('USER_REPORT_BASELINE_JSON'))


def main():
    results = {}
    scratch = Path.home() / '.hermes/cache/scratch'
    scratch.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='dalo-user-export-', dir=scratch) as directory:
        fixture = Path(directory)
        shutil.copytree(ROOT / 'app', fixture / 'app', symlinks=True)
        try:
            run('docker', 'network', 'create', '--internal', NETWORK)
            run('docker', 'run', '-d', '--name', DB, '--network', NETWORK,
                '--tmpfs', '/var/lib/mysql', '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e', 'MARIADB_DATABASE=radius', 'mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'), 'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql', 'mariadb-daloradius.sql'):
                sql((ROOT / 'contrib/db' / name).read_text())
            sql("""SET sql_mode='';
INSERT INTO hotspots (id,name,mac) VALUES
 (21,'Lab','aa:bb:cc:dd:ee:01'),(22,'Lab, East','aa:bb:cc:dd:ee:02');
INSERT INTO radacct (radacctid,username,acctsessionid,acctuniqueid,nasipaddress,
 calledstationid,acctstarttime,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES
 (101,'alice','a1','a1','192.0.2.1','aa:bb:cc:dd:ee:01','2020-01-01 12:00:00',11,12,13),
 (102,'alice','a2','a2','192.0.2.1','aa:bb:cc:dd:ee:02','2020-01-01 00:00:00',21,22,23),
 (103,'alice','a3','a3','192.0.2.1','aa:bb:cc:dd:ee:02','2020-01-03 00:00:00',31,32,33),
 (201,'bob','b1','b1','192.0.2.2','aa:bb:cc:dd:ee:01','2020-01-02 12:00:00',41,42,43);
INSERT INTO userinfo (username) VALUES ('alice'),('bob');
INSERT INTO userbillinfo (id,username,contactperson) VALUES
 (10,'alice','Alice'),(11,'bob','Bob');
INSERT INTO invoice_status (id,value,notes) VALUES
 (10,'Paid','fixture'),(11,'=1+1','formula fixture');
INSERT INTO invoice (id,user_id,date,status_id,type_id,notes) VALUES
 (30,10,'2020-01-02 00:00:00',10,1,'fixture'),
 (31,10,'2020-01-03 12:00:00',11,1,'fixture'),
 (40,11,'2020-01-02 00:00:00',10,1,'fixture');
INSERT INTO invoice_items (invoice_id,amount,tax_amount,notes) VALUES
 (30,2.00,0.20,'fixture'),(40,7.00,0.00,'fixture');
INSERT INTO payment (invoice_id,amount,date,notes) VALUES
 (30,1.00,'2020-01-02 00:00:00','fixture');
""")
            config = (ROOT / 'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>', '')
            for key, value in {'CONFIG_DB_HOST': DB, 'CONFIG_DB_USER': 'root',
                               'CONFIG_DB_PASS': '', 'CONFIG_DB_NAME': 'radius'}.items():
                config += '\n$configValues[' + repr(key) + '] = ' + repr(value) + ';\n'
            (fixture / 'app/common/includes/daloradius.conf.php').write_text(config)
            (fixture / 'session.php').write_text('''<?php
session_name('daloradius_user_sid'); session_id($argv[1]); session_start();
$_SESSION = ['logged_in'=>true,'login_user'=>$argv[2],'location_name'=>'default','time'=>time()];
session_write_close();
''')
            (fixture / 'tamper.php').write_text('''<?php
session_name('daloradius_user_sid'); session_id($argv[1]); session_start();
$_SESSION['export_query'] = 'SELECT * FROM radacct';
$_SESSION['export_items'] = ['everything'];
$_SESSION['userReportExport'] = ['source'=>'acct-date','filters'=>['startdate'=>'2020-01-01','enddate'=>'2020-01-03']];
session_write_close();
''')
            (fixture / 'switch.php').write_text('''<?php
session_name('daloradius_user_sid'); session_id($argv[1]); session_start();
$_SESSION['login_user'] = $argv[2];
session_write_close();
''')
            (fixture / 'bad.php').write_text('''<?php
session_name('daloradius_user_sid'); session_id($argv[1]); session_start();
$_SESSION['userReportExport'] = ['source'=>'acct-date','filters'=>['username'=>'bob']];
session_write_close();
''')
            run('docker', 'run', '-d', '--name', WEB, '--network', NETWORK,
                '-v', f'{fixture}:/fixtures', '-w', '/fixtures/app/users',
                '--entrypoint', 'php', 'lirantal/daloradius', '-d', 'display_errors=0',
                '-S', '0.0.0.0:8080', '-t', '.')
            address = run('docker', 'inspect', '-f',
                          '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}', WEB)
            base = 'http://' + address + ':8080/'
            wait_for(lambda: urllib.request.urlopen(base + 'login.php', timeout=10), 'PHP HTTP server')
            sid = secrets.token_hex(16)
            run('docker', 'exec', WEB, 'php', '/fixtures/session.php', sid, 'alice')

            def request(path, authenticated=True):
                req = urllib.request.Request(base + path,
                    headers={'Cookie':'daloradius_user_sid=' + sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req, timeout=15) as response:
                        return response.status, response.read().decode('utf-8'), response.headers.get('Content-type','')
                except urllib.error.HTTPError as error:
                    return error.code, error.read().decode('utf-8'), error.headers.get('Content-type','')

            export = 'include/management/fileExport.php'
            cases = [
                ('accounting', 'acct-date.php', {'startdate':'2020-01-01','enddate':'2020-01-03'}),
                ('invoice', 'bill-invoice-report.php', {'startdate':'2020-01-02','enddate':'2020-01-03','invoice_status':'10'}),
            ]
            for name, page, params in cases:
                status, body, _ = request(page + '?' + urllib.parse.urlencode(params))
                assert status == 200 and '<strong>Database error</strong>' not in body, (name, status, body[:250])
                csv_status, csv_body, content_type = request(export)
                assert csv_status == 200, (name, csv_status, csv_body[:250])
                rows = list(csv.reader(io.StringIO(csv_body), skipinitialspace=True))
                results[name] = {'rows': rows, 'csv':csv_body, 'content_type':content_type}
                assert request(export)[1] == '' if not IS_CANDIDATE else request(export)[0] == 400

            if IS_CANDIDATE:
                baseline = json.loads(Path(os.environ['USER_REPORT_BASELINE_JSON']).read_text())
                for name in ('accounting','invoice'):
                    assert baseline[name]['rows'] == results[name]['rows'], name
                print('PASS: normal CSV rows match PEAR baseline for both user reports', file=sys.stderr)
                assert results['accounting']['rows'][1][0] == '101'
                assert '102' not in [r[0] for r in results['accounting']['rows'][1:]]
                assert '103' not in [r[0] for r in results['accounting']['rows'][1:]]
                assert results['invoice']['rows'][1][0] == '30'
                assert len(results['invoice']['rows']) == 2
                print('PASS: exclusive accounting and inclusive invoice date bounds preserved', file=sys.stderr)

                run('docker', 'exec', WEB, 'php', '/fixtures/tamper.php', sid)
                assert request(export)[0] == 200
                assert request(export)[0] == 400
                run('docker', 'exec', WEB, 'php', '/fixtures/bad.php', sid)
                bad_status, bad_body, _ = request(export)
                assert bad_status == 400 and 'SELECT' not in bad_body and 'password' not in bad_body
                print('PASS: raw session SQL ignored; unknown filters rejected; descriptors consumed once', file=sys.stderr)
                # A new page with zero rows clears a prior report's descriptor.
                assert request('acct-date.php?startdate=2030-01-01')[0] == 200
                assert request(export)[0] == 400
                print('PASS: no stale export after empty source page', file=sys.stderr)
                # Simulate a principal change after a report was prepared,
                # without replacing its descriptor. Export must use current user.
                assert request('acct-date.php')[0] == 200
                run('docker', 'exec', WEB, 'php', '/fixtures/switch.php', sid, 'bob')
                _, csv_body, _ = request(export)
                assert '201' in csv_body and '101' not in csv_body
                print('PASS: stale descriptor cannot export prior principal rows', file=sys.stderr)
                # A new page for another user must also remain user-scoped.
                run('docker', 'exec', WEB, 'php', '/fixtures/session.php', sid, 'bob')
                assert request('acct-date.php')[0] == 200
                _, csv_body, _ = request(export)
                assert '201' in csv_body and '101' not in csv_body
                assert request('bill-invoice-report.php')[0] == 200
                _, csv_body, _ = request(export)
                assert '40,' in csv_body and '30,' not in csv_body
                print('PASS: accounting and invoice exports bind current session principal', file=sys.stderr)
                unauth_status, unauth_body, unauth_type = request(export, authenticated=False)
                assert unauth_status == 200 and 'text/csv' not in unauth_type and '<form' in unauth_body
                print('PASS: unauthenticated export redirects to login, not CSV', file=sys.stderr)
                run('docker', 'exec', WEB, 'php', '/fixtures/session.php', sid, 'alice')
                assert request('acct-date.php')[0] == 200
                _, csv_body, _ = request(export)
                rows = list(csv.reader(io.StringIO(csv_body)))
                assert any(r[1] == 'Lab, East' and len(r) == 10 for r in rows[1:])
                assert request('bill-invoice-report.php')[0] == 200
                _, csv_body, _ = request(export)
                rows = list(csv.reader(io.StringIO(csv_body)))
                assert any(r[2].startswith("'=1+1") for r in rows[1:])
                print('PASS: comma in hotspot quoted; formula status neutralized', file=sys.stderr)
            import subprocess
            logs = subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            warnings = [line for line in logs.stderr.splitlines()
                        if 'PHP Warning:' in line and 'Constant ' not in line]
            assert 'PHP Fatal error' not in logs.stderr and not warnings, '\n'.join(warnings[-10:])
            print('PASS: no new PHP warnings/fatal errors (legacy duplicate constants excluded)',file=sys.stderr)
            print(json.dumps(results,indent=2))
        finally:
            for container in (WEB, DB):
                run('docker', 'rm', '-f', '-v', container, check=False)
            run('docker', 'network', 'rm', NETWORK, check=False)
            # HTMLPurifier creates root-owned cache files in the mounted copy.
            run('docker', 'run', '--rm', '-v', f'{fixture}:/fixtures',
                '--entrypoint', 'sh', 'lirantal/daloradius', '-c',
                f'chown -R {os.getuid()}:{os.getgid()} /fixtures')
            print('CLEANUP: disposable containers/data removed', file=sys.stderr)

if __name__ == '__main__':
    main()
