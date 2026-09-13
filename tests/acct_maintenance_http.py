#!/usr/bin/env python3
"""Open-session maintenance integration tests: real PHP/HTTP/MariaDB.

Run: python3 tests/acct_maintenance_http.py
Requires Docker, mariadb:11.8 and lirantal/daloradius (or MAINTENANCE_WEB_IMAGE).
Uses a disposable internal network/database, synthetic sessions and minimal
fixtures; never reads live credentials or changes live accounting data.
"""
from html.parser import HTMLParser
import os
import re
from pathlib import Path
import secrets
import shutil
import tempfile
import urllib.parse
import urllib.request
import user_actions_http as harness

ROOT = Path(__file__).resolve().parents[1]
run, sql, wait_for = harness.run, harness.sql, harness.wait_for
DB, WEB, NETWORK = harness.DB, harness.WEB, harness.NETWORK
IMAGE = os.environ.get('MAINTENANCE_WEB_IMAGE', 'lirantal/daloradius')
HTTP_TIMEOUT = 10


class Forms(HTMLParser):
    def __init__(self, html):
        super().__init__()
        self.forms = []
        self.current = None
        self.feed(html)

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == 'form':
            self.current = {}
            self.forms.append(self.current)
        if tag == 'input' and self.current is not None and 'name' in attrs:
            self.current[attrs['name']] = attrs.get('value', '')

    def handle_endtag(self, tag):
        if tag == 'form':
            self.current = None


def main():
    with tempfile.TemporaryDirectory(prefix='dalo-maintenance-') as directory:
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
            sql("INSERT INTO operators_acl (operator_id,file,access) VALUES (9001,'acct_maintenance_cleanup',1),(9002,'acct_maintenance_cleanup',0)")
            config = (ROOT / 'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>', '')
            for key, value in {'CONFIG_DB_HOST': DB, 'CONFIG_DB_USER': 'root', 'CONFIG_DB_PASS': '', 'CONFIG_DB_NAME': 'radius', 'CONFIG_LANG': 'fr'}.items():
                config += '\n$configValues[' + repr(key) + '] = ' + repr(value) + ';\n'
            (fixture / 'app/common/includes/daloradius.conf.php').write_text(config)
            (fixture / 'session.php').write_text('''<?php
session_name('daloradius_operator_sid'); session_id($argv[1]); session_start();
$_SESSION = ['daloradius_logged_in'=>true, 'operator_id'=>(int)$argv[2],
'operator_user'=>'maintenance-fixture', 'location_name'=>'default', 'time'=>time()];
session_write_close();
''')
            run('docker', 'run', '-d', '--name', WEB, '--network', NETWORK,
                '-v', f'{fixture}:/fixtures', '-w', '/fixtures/app/operators',
                '--entrypoint', 'php', IMAGE, '-d', 'display_errors=0', '-S', '0.0.0.0:8080', '-t', '.')
            address = run('docker', 'inspect', '-f', '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}', WEB)
            base = 'http://' + address + ':8080/'
            wait_for(lambda: urllib.request.urlopen(base + 'login.php', timeout=HTTP_TIMEOUT), 'PHP HTTP server')
            sessions = {}
            for operator in [9001, 9002]:
                sessions[operator] = secrets.token_hex(16)
                run('docker', 'exec', WEB, 'php', '/fixtures/session.php', sessions[operator], str(operator))

            def request(data=None, operator=9001, query=''):
                headers = {} if operator is None else {'Cookie': 'daloradius_operator_sid=' + sessions[operator]}
                req = urllib.request.Request(base + 'acct-maintenance-cleanup.php' + query,
                    data=None if data is None else urllib.parse.urlencode(data).encode(), headers=headers)
                with urllib.request.urlopen(req, timeout=HTTP_TIMEOUT) as response:
                    return response.url, response.read().decode()

            def csrf():
                return next(f['csrf_token'] for f in Forms(request()[1]).forms if 'csrf_token' in f)

            def preview(action='close', scope='username', value='fixture'):
                _, html = request(dict(step='preview', action=action, scope=scope, value=value, csrf_token=csrf()))
                return html, next((f for f in Forms(html).forms if f.get('step') == 'confirm'), None)

            def insert(stop='NULL', username='fixture', start='2020-01-01 12:00:00'):
                unique = secrets.token_hex(12)
                username = username.replace("'", "''")
                sql(f"SET sql_mode=''; INSERT INTO radacct (username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime,acctstoptime,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES ('{username}','{unique}','{unique}','192.0.2.1','{start}',{stop},123,456,789)")
                return sql(f"SELECT radacctid FROM radacct WHERE acctuniqueid='{unique}'")

            def reset():
                sql('DELETE FROM radacct')  # This database is disposable, never the live lab.

            assert request(operator=None)[0].endswith('/login.php')
            assert request(operator=9002)[0].endswith('/home-error.php')
            row = insert()
            token = csrf()
            for fields in [dict(action='unknown'), dict(scope='all'), dict(value=''),
                           dict(action=['delete']), dict(scope='date', value='2023-02-29'),
                           dict(scope='date', value='2020-13-01'), dict(scope='date', value='garbage'),
                           dict(scope='date', value='2020-01-01x'), dict(csrf_token='bad'),
                           dict(csrf_token=['bad']), {'csrf_token[]': 'bad'}, {'action[]': 'delete'},
                           {'scope[]': 'username'}, {'value[]': 'fixture'},
                           dict(step='confirm'), dict(step='unknown')]:
                data = dict(action='close', scope='username', value='fixture', step='preview', csrf_token=csrf())
                data.update(fields)
                assert 'Invalid request' in request(data)[1], fields
            assert sql('SELECT COUNT(*) FROM radacct WHERE acctstoptime IS NULL') == '1'
            assert request(dict(action='delete', scope='username', value='fixture', step='confirm', csrf_token=token), operator=9002)[0].endswith('/home-error.php')
            print('PASS: authentication, ACL on GET/POST, invalid action/scope/date/CSRF/confirmation; no mutation')

            odd = "O'Reilly & + é 50%"
            reset()
            insert(username=odd)
            html = request(query='?username=' + urllib.parse.quote(odd))[1]
            assert any(f.get('value') == odd for f in Forms(html).forms)
            assert sql('SELECT COUNT(*) FROM radacct WHERE acctstoptime IS NULL') == '1'
            assert 'Open-session maintenance' in html and 'Close sessions' in html and 'Delete records' in html
            assert 'Delete accounting history (all records)' in html
            html, confirm = preview(value=odd)
            if os.environ.get('MAINTENANCE_PREVIEW_HTML'):
                Path(os.environ['MAINTENANCE_PREVIEW_HTML']).write_text(html)
            assert confirm, 'Missing preview: ' + ('empty' if 'No matching open records' in html else 'error' if 'Cannot read accounting records' in html else 'invalid')
            response = request(confirm)[1]
            assert '1 actually affected' in response, 'Confirmation result: ' + re.findall(r'<div class="alert alert-info" role="status">(.*?)</div>', response).__repr__()
            print('PASS: GET prefill is read-only, special characters, new English fallback with French configured')

            for action in ['close', 'delete']:
                for scope in ['username', 'date']:
                    reset()
                    ids = [insert(), insert("'0000-00-00 00:00:00'")]
                    closed = insert("'2020-01-02 00:00:00'")
                    boundary = insert(username='other', start='2020-01-02 00:00:00')
                    before_closed = sql(f'SELECT * FROM radacct WHERE radacctid={closed}')
                    before_boundary = sql(f'SELECT * FROM radacct WHERE radacctid={boundary}')
                    html, confirm = preview(action, scope, 'fixture' if scope == 'username' else '2020-01-02')
                    assert '2 matching records' in html and confirm
                    assert sql('SELECT COUNT(*) FROM radacct') == '4'
                    response = request(confirm)[1]
                    assert '2 actually affected' in response and '0 failed' in response, response[-1000:]
                    if action == 'close':
                        assert sql(f"SELECT COUNT(*) FROM radacct WHERE radacctid IN ({','.join(ids)}) AND acctstoptime IS NOT NULL AND acctterminatecause='Admin-Reset' AND acctsessiontime=123 AND acctinputoctets=456 AND acctoutputoctets=789") == '2'
                    else:
                        assert sql(f"SELECT COUNT(*) FROM radacct WHERE radacctid IN ({','.join(ids)})") == '0'
                    assert before_closed == sql(f'SELECT * FROM radacct WHERE radacctid={closed}')
                    assert before_boundary == sql(f'SELECT * FROM radacct WHERE radacctid={boundary}')
                    assert 'Invalid request' in request(confirm)[1]
            print('PASS: close/delete × username/date; NULL/zero stops, exclusive midnight boundary, closed rows and counters retained, replay rejected')

            for action in ['close', 'delete']:
                reset()
                unchanged, interim, stopped = insert(), insert(), insert()
                _, confirm = preview(action)
                sql(f"UPDATE radacct SET acctinputoctets=999 WHERE radacctid={interim}; UPDATE radacct SET acctstoptime=NOW() WHERE radacctid={stopped}")
                new = insert()
                html = request(confirm)[1]
                assert '1 actually affected, 2 changed or ineligible records skipped, 0 failed' in html
                assert sql(f'SELECT COUNT(*) FROM radacct WHERE radacctid IN ({interim},{stopped},{new})') == '3'
                assert sql(f'SELECT acctinputoctets FROM radacct WHERE radacctid={interim}') == '999'
                _, confirm = preview(action)
                tampered = dict(confirm, value='other')
                assert 'Invalid request' in request(tampered)[1]
                assert 'Invalid request' in request(confirm)[1]
                _, confirm = preview(action)
                request()  # GET invalidates prior preview.
                assert 'Invalid request' in request(confirm)[1]
            print('PASS: concurrent Interim/Stop skip, new rows excluded, filter mismatch and GET invalidate confirmation')

            reset()
            html, confirm = preview()
            assert confirm is None and 'No matching open records' in html
            for _ in range(101):
                insert()
            html, confirm = preview('delete')
            assert '101 matching records' in html and '100 records in this preview' in html
            assert '100 actually affected' in request(confirm)[1]
            assert sql('SELECT COUNT(*) FROM radacct') == '1'
            _, confirm = preview()
            (fixture / 'expire.php').write_text("<?php session_name('daloradius_operator_sid'); session_id($argv[1]); session_start(); $_SESSION['acct_maintenance_preview']['created']=0; session_write_close();")
            run('docker', 'exec', WEB, 'php', '/fixtures/expire.php', sessions[9001])
            assert 'Invalid request' in request(confirm)[1]
            print('PASS: zero-match info, bounded 100-row snapshot, 101st row untouched, expiration')
            _, confirm = preview('delete')
            sql('RENAME TABLE radacct TO radacct_unavailable')
            try:
                assert '0 actually affected, 0 changed or ineligible records skipped, 1 failed' in request(confirm)[1]
            finally:
                sql('RENAME TABLE radacct_unavailable TO radacct')
            assert sql('SELECT COUNT(*) FROM radacct') == '1'
            print('PASS: SQL failure reports actual zero affected and one failed, retaining data')
            import subprocess
            result = subprocess.run(['docker', 'logs', WEB], capture_output=True, text=True, check=True)
            logs = result.stdout + result.stderr
            assert 'PHP Fatal error' not in logs and 'PHP Warning' not in logs, logs[-2000:]
            print('PASS: no PHP warnings or fatal errors')
        finally:
            for container in [WEB, DB]:
                run('docker', 'rm', '-f', '-v', container, check=False)
            run('docker', 'network', 'rm', NETWORK, check=False)
            assert not run('docker', 'ps', '-aq', '--filter', 'name=' + harness.PREFIX)
            print('CLEANUP: disposable containers/data removed; live lab accounting untouched')


if __name__ == '__main__':
    main()
