#!/usr/bin/env python3
"""Real HTTP/PHP/MariaDB checks for operator CSV exports on disposable data.

The temporary database lives on tmpfs on an isolated Docker network. Sessions
are synthetic; no live operator credentials or accounting rows are touched.
"""
import csv
from html.parser import HTMLParser
import io
import os
from pathlib import Path
import secrets
import shutil
import tempfile
import urllib.error
import urllib.parse
import urllib.request
import user_actions_http as harness

ROOT = Path(__file__).resolve().parents[1]
run, sql, wait_for = harness.run, harness.sql, harness.wait_for
DB, WEB, NETWORK = harness.DB, harness.WEB, harness.NETWORK
IMAGE = os.environ.get('REPORT_EXPORT_WEB_IMAGE', 'lirantal/daloradius')


def main():
    with tempfile.TemporaryDirectory(prefix='dalo-export-') as directory:
        fixture = Path(directory)
        shutil.copytree(ROOT / 'app', fixture / 'app', symlinks=True)
        try:
            run('docker', 'network', 'create', '--internal', NETWORK)
            run('docker', 'run', '-d', '--name', DB, '--network', NETWORK,
                '--tmpfs', '/var/lib/mysql', '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e', 'MARIADB_DATABASE=radius', 'mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'), 'MariaDB')
            for name in ['fr3-mariadb-freeradius.sql', 'mariadb-daloradius.sql']:
                sql((ROOT / 'contrib/db' / name).read_text())
            config = (ROOT / 'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>', '')
            for key, value in {'CONFIG_DB_HOST': DB, 'CONFIG_DB_USER': 'root', 'CONFIG_DB_PASS': '',
                               'CONFIG_DB_NAME': 'radius'}.items():
                config += '\n$configValues[' + repr(key) + '] = ' + repr(value) + ';\n'
            (fixture / 'app/common/includes/daloradius.conf.php').write_text(config)
            (fixture / 'session.php').write_text('''<?php
session_name('daloradius_operator_sid'); session_id($argv[1]); session_start();
$_SESSION = ['daloradius_logged_in'=>true, 'operator_id'=>9001,
'operator_user'=>'export-fixture', 'location_name'=>'default', 'time'=>time()];
session_write_close();
''')
            # The ACL is checked by the source pages and again by new exports.
            acl = ['acct_all', 'acct_username', 'mng_list_all', 'mng_search',
                   'mng_rad_profiles_list', 'rep_online', 'rep_lastconnect',
                   'rep_topusers', 'rep_history', 'rep_batch_list', 'mng_batch_list',
                   'rep_batch_details', 'bill_invoice_report', 'acct_plans_usage',
                   'acct_date', 'acct_hotspot_accounting', 'acct_ipaddress',
                   'acct_nasipaddress']
            sql('INSERT INTO operators_acl (operator_id,file,access) VALUES ' +
                ','.join("(9001,'" + key + "',1)" for key in acl))
            sql("""SET sql_mode='';
INSERT INTO radcheck (username,attribute,op,value) VALUES
 ('alice','Cleartext-Password',':=','alice-secret'),
 ('bob','Cleartext-Password',':=','bob-secret'),
 ('outside','Cleartext-Password',':=','outside-secret');
INSERT INTO userinfo (username,email,firstname,lastname) VALUES
 ('alice','alice@example.invalid','Alice','Smith'),
 ('bob','bob@example.invalid','Bob','Jones'),
 ('outside','outside@example.invalid','Outside','User');
INSERT INTO radusergroup (username,groupname,priority) VALUES
 ('alice','fixture-group',1),('bob','fixture-group',1);
INSERT INTO radacct (username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime,
 acctstoptime,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES
 ('alice','a1','fixture-a1','192.0.2.1','2020-01-01 12:00:00',NULL,11,12,13),
 ('bob','b1','fixture-b1','192.0.2.2','2020-01-02 12:00:00',NULL,21,22,23),
 ('outside','o1','fixture-o1','192.0.2.3','2020-01-03 12:00:00',NULL,31,32,33);
""")
            run('docker', 'run', '-d', '--name', WEB, '--network', NETWORK,
                '-v', f'{fixture}:/fixtures', '-w', '/fixtures/app/operators',
                '--entrypoint', 'php', IMAGE, '-d', 'display_errors=0',
                '-S', '0.0.0.0:8080', '-t', '.')
            address = run('docker', 'inspect', '-f',
                          '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}', WEB)
            base = 'http://' + address + ':8080/'
            wait_for(lambda: urllib.request.urlopen(base + 'login.php', timeout=10), 'PHP HTTP server')
            session = secrets.token_hex(16)
            run('docker', 'exec', WEB, 'php', '/fixtures/session.php', session)

            def request(path, logged_in=True):
                req = urllib.request.Request(base + path,
                    headers={'Cookie': 'daloradius_operator_sid=' + session} if logged_in else {})
                try:
                    with urllib.request.urlopen(req, timeout=15) as response:
                        return response.status, response.read().decode('utf-8')
                except urllib.error.HTTPError as error:
                    return error.code, error.read().decode('utf-8')

            export_path = 'include/management/fileExport.php?reportFormat=csv'
            # Exact formatting is legacy-compatible; the security/ACL probes
            # below run only on the PDO candidate, never on the PEAR baseline.
            status, page = request('acct-all.php')
            assert status == 200 and 'PHP Fatal' not in page
            status, content = request(export_path + '&reportType=accountingGeneric')
            assert status == 200 and content.startswith('Id,NAS/Hotspot,UserName,')
            assert 'alice' in content and 'bob' in content and 'outside' in content
            assert content.count('\n') >= 4
            print('PASS: accounting CSV includes full unpaginated result')

            status, page = request('mng-list-all.php')
            assert status == 200 and 'PHP Fatal' not in page
            status, content = request(export_path + '&reportType=usernameListGeneric')
            assert status == 200 and content.startswith('username,password,email,')
            rows = list(csv.DictReader(io.StringIO(content)))
            assert {'alice', 'bob', 'outside'} <= {row['username'] for row in rows}
            assert next(row for row in rows if row['username'] == 'alice')['email'] == 'alice@example.invalid'
            print('PASS: user CSV uses import-compatible column order')

            status, content = request(export_path + '&reportType=usernameListByGroup&groupname=fixture-group')
            assert status == 200 and content.startswith('username,password,email,')
            assert {'alice', 'bob'} == {row['username'] for row in csv.DictReader(io.StringIO(content))}
            print('PASS: direct group export uses a bound group name')

            if os.environ.get('REPORT_EXPORT_EXPECT_PDO'):
                # Alter only the private session state, never the live DB.
                (fixture / 'tamper.php').write_text('''<?php
session_name('daloradius_operator_sid'); session_id($argv[1]); session_start();
$_SESSION['reportTable'] = 'radacct';
$_SESSION['reportQuery'] = ' WHERE 1=0';
session_write_close();
''')
                run('docker', 'exec', WEB, 'php', '/fixtures/tamper.php', session)
                status, content = request(export_path + '&reportType=usernameListGeneric')
                assert status == 200 and {'alice', 'bob', 'outside'} <= {
                    row['username'] for row in csv.DictReader(io.StringIO(content))}
                assert request(export_path + '&reportType=TopUsers')[0] == 400
                print('PASS: raw session SQL ignored; mismatched report type rejected')
                sql("UPDATE operators_acl SET access=0 WHERE operator_id=9001 AND file='mng_list_all'")
                assert request(export_path + '&reportType=usernameListGeneric')[0] == 403
                print('PASS: authorization is rechecked at export time')
                sql("UPDATE operators_acl SET access=0 WHERE operator_id=9001 AND file='mng_rad_profiles_list'")
                assert request(export_path + '&reportType=usernameListByGroup&groupname=fixture-group')[0] == 403
                print('PASS: direct group export checks its own source ACL')
                sql("INSERT INTO radcheck (username,attribute,op,value) VALUES ('orphan','Cleartext-Password',':=','secret')")
                status, page = request('mng-search.php?username=orphan')
                assert status == 200
                status, content = request(export_path + '&reportType=usernameListGeneric')
                assert status == 200 and 'orphan' not in content
                print('PASS: search export excludes users without userinfo like the source page')
                status, page = request('rep-history.php')
                assert status == 200 and 'get_csv_export_control' not in page
                history_export_status, history_export_body = request(export_path + '&reportType=usernameListGeneric')
                assert history_export_status == 400, (history_export_status, page[:250], history_export_body[:250])
                print('PASS: history clears stale export state')
            import subprocess
            result = subprocess.run(['docker', 'logs', WEB], capture_output=True, text=True, check=True)
            logs = result.stdout + result.stderr
            assert 'PHP Fatal error' not in logs and 'PHP Warning' not in logs, logs[-3000:]
            print('PASS: no PHP warnings or fatal errors')
        finally:
            for container in (WEB, DB):
                run('docker', 'rm', '-f', '-v', container, check=False)
            run('docker', 'network', 'rm', NETWORK, check=False)
            assert not run('docker', 'ps', '-aq', '--filter', 'name=' + harness.PREFIX)
            print('CLEANUP: disposable containers/data removed')

if __name__ == '__main__':
    main()
