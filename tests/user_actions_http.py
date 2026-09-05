#!/usr/bin/env python3
"""Real PHP/MariaDB/PHPMailer regression test in disposable Docker containers.

Requires Docker, mariadb:11.8 and the project's built web image
(USER_ACTIONS_WEB_IMAGE, default lirantal/daloradius). No live config/data used.
The internal network prevents outbound mail; SMTP is captured on loopback.
"""
from html.parser import HTMLParser
import json
import os
from pathlib import Path
import secrets
import shutil
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request

ROOT = Path(__file__).resolve().parents[1]
PREFIX = 'dalo-actions-' + secrets.token_hex(4)
DB, WEB, NETWORK = PREFIX + '-db', PREFIX + '-web', PREFIX + '-net'
IMAGE = os.environ.get('USER_ACTIONS_WEB_IMAGE', 'lirantal/daloradius')


def run(*args, input=None, check=True):
    result = subprocess.run(args, input=input, text=True, capture_output=True)
    if check and result.returncode:
        raise RuntimeError(f'{args[0:3]} failed: {result.stderr}')
    return result.stdout.strip()


def sql(query):
    return run('docker', 'exec', '-i', DB, 'mariadb', '-uroot', '-N', '-B', 'radius', input=query)


def wait_for(check, description):
    deadline = time.monotonic() + 60
    while time.monotonic() < deadline:
        try:
            return check()
        except (RuntimeError, OSError, urllib.error.URLError):
            time.sleep(0.25)
    raise RuntimeError('Timed out waiting for ' + description)


def main():
    with tempfile.TemporaryDirectory(prefix=PREFIX) as directory:
        fixture = Path(directory)
        shutil.copytree(ROOT / 'app', fixture / 'app', symlinks=True)
        token = secrets.token_hex(32)
        try:
            run('docker', 'network', 'create', '--internal', NETWORK)
            run('docker', 'run', '-d', '--name', DB, '--network', NETWORK,
                '--tmpfs', '/var/lib/mysql', '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e', 'MARIADB_DATABASE=radius', 'mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'), 'MariaDB')
            for name in ['fr3-mariadb-freeradius.sql', 'mariadb-daloradius.sql']:
                sql((ROOT / 'contrib/db' / name).read_text())
            sql("""
                INSERT INTO operators_acl (operator_id, file, access) VALUES
                    (9001, 'mng_edit', 1), (9001, 'mng_search', 1),
                    (9002, 'mng_edit', 0), (9002, 'mng_search', 0);
                INSERT INTO radcheck (username, attribute, op, value) VALUES
                    ('alice', 'Cleartext-Password', ':=', 'fixture-alice'),
                    ('bob', 'Cleartext-Password', ':=', 'fixture-bob'),
                    ('bad-mail', 'Cleartext-Password', ':=', 'fixture-bad');
                INSERT INTO userinfo (username, email, firstname, lastname) VALUES
                    ('alice', 'alice@example.invalid', 'Alice', 'Fixture'),
                    ('bob', 'bob@example.invalid', 'Bob', 'Fixture'),
                    ('bad-mail', 'invalid-address', 'Invalid', 'Fixture');
                INSERT INTO billing_plans (id, planName, planTimeRefillCost, planTrafficRefillCost, planTax) VALUES
                    (9001, 'paid-fixture', '10', '20', '20'),
                    (9002, 'free-fixture', '0', '0', '0');
                INSERT INTO userbillinfo (id, username, planName) VALUES
                    (9001, 'alice', 'paid-fixture'), (9002, 'bob', 'free-fixture');
                INSERT INTO radacct (username, acctsessionid, acctuniqueid, nasipaddress,
                    acctsessiontime, acctinputoctets, acctoutputoctets) VALUES
                    ('alice', 'a1', 'a1', '127.0.0.1', 600, 1000, 2000),
                    ('alice', 'a2', 'a2', '127.0.0.1', 300, 3000, 4000),
                    ('bob', 'b1', 'b1', '127.0.0.1', 900, 5000, 6000),
                    ('no-plan', 'n1', 'n1', '127.0.0.1', 900, 5000, 6000),
                    ('untouched', 'u1', 'u1', '127.0.0.1', 123, 456, 789);
            """)
            config = (ROOT / 'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>', '')
            overrides = {
                'CONFIG_DB_HOST': DB, 'CONFIG_DB_USER': 'root', 'CONFIG_DB_PASS': '', 'CONFIG_DB_NAME': 'radius',
                'CONFIG_MAIL_SMTPADDR': '127.0.0.1', 'CONFIG_MAIL_SMTPPORT': '2525',
                'CONFIG_MAIL_SMTP_SECURITY': '', 'CONFIG_MAIL_SMTPFROM': 'fixture@example.invalid',
                'CONFIG_USER_VPN_SERVER': 'vpn.example.invalid',
            }
            for key, value in overrides.items():
                config += '\n$configValues[' + repr(key) + '] = ' + repr(value) + ';\n'
            (fixture / 'app/common/includes/daloradius.conf.php').write_text(config)
            (fixture / 'session.php').write_text('''<?php
session_name('daloradius_operator_sid');
session_id($argv[1]);
session_start();
$_SESSION = ['daloradius_logged_in' => true, 'operator_id' => intval($argv[2]),
    'operator_user' => 'fixture-operator', 'location_name' => 'default',
    'time' => time(), 'csrf_token' => $argv[3]];
session_write_close();
''')
            (fixture / 'smtp.php').write_text('''<?php
$server = stream_socket_server('tcp://127.0.0.1:2525');
while ($client = stream_socket_accept($server, -1)) {
    fwrite($client, "220 fixture SMTP\\r\\n");
    $data = false; $message = '';
    while (($line = fgets($client)) !== false) {
        if ($data) {
            if (rtrim($line) === '.') {
                file_put_contents('/fixtures/mail.jsonl', json_encode($message) . "\\n", FILE_APPEND);
                fwrite($client, "250 captured\\r\\n"); $data = false;
            } else { $message .= $line; }
        } elseif (str_starts_with($line, 'DATA')) {
            fwrite($client, "354 send data\\r\\n"); $data = true;
        } elseif (str_starts_with($line, 'QUIT')) {
            fwrite($client, "221 bye\\r\\n"); break;
        } else { fwrite($client, "250 OK\\r\\n"); }
    }
    fclose($client);
}
''')
            run('docker', 'run', '-d', '--name', WEB, '--network', NETWORK,
                '-v', f'{ROOT}:/repo:ro',
                '-v', f'{fixture}:/fixtures',
                '-v', f'{fixture}/app:/repo/app:ro',
                '-w', '/repo/app/operators', '--entrypoint', 'php', IMAGE,
                '-d', 'display_errors=0', '-S', '0.0.0.0:8080', '-t', '.')
            address = run('docker', 'inspect', '-f', '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}', WEB) + ':8080'
            url = 'http://' + address + '/library/ajax/user_actions.php'
            wait_for(lambda: urllib.request.urlopen('http://' + address + '/login.php'), 'PHP HTTP server')
            run('docker', 'exec', '-d', WEB, 'php', '/fixtures/smtp.php')
            sessions = {}
            for operator in [9001, 9002]:
                sid = secrets.token_hex(16)
                run('docker', 'exec', WEB, 'php', '/fixtures/session.php', sid, str(operator), token)
                sessions[operator] = sid

            def request(action=None, users=None, method='POST', operator=9001, csrf=token, extra=None):
                params = []
                if action is not None:
                    params.append(('action', action))
                if users is not None:
                    params += [('username[]', user) for user in users]
                if csrf is not None:
                    params.append(('csrf_token', csrf))
                if extra:
                    params += extra
                data = urllib.parse.urlencode(params).encode()
                dest = url + ('?' + data.decode() if method == 'GET' else '')
                headers = {'Accept': 'application/json'}
                if operator:
                    headers['Cookie'] = 'daloradius_operator_sid=' + sessions[operator]
                req = urllib.request.Request(dest, data=data if method != 'GET' else None, headers=headers, method=method)
                try:
                    response = urllib.request.urlopen(req)
                except urllib.error.HTTPError as error:
                    response = error
                raw = response.read().decode()
                if response.status == 403 and not raw.strip():
                    return response.status, None  # Existing ACL helper intentionally exits with 403.
                assert response.headers.get_content_type() == 'application/json', raw[:200]
                return response.status, json.loads(raw)

            anonymous = urllib.request.Request(url, data=b'action=userEnable&username=alice')
            with urllib.request.urlopen(anonymous) as response:
                assert response.url.endswith('/login.php'), response.url

            # Validate before dispatch: no absent/unknown/GET action may enable a user.
            sql("INSERT INTO radusergroup (username, groupname, priority) VALUES ('alice', 'daloRADIUS-Disabled-Users', -1)")
            for action in [None, 'unknown']:
                assert request(action, ['alice'])[0] == 400
            assert request(None, ['alice'], extra=[('action[]', 'userEnable')])[0] == 400
            assert request('userEnable', ['alice'], method='GET')[0] == 405
            assert request('userEnable', ['alice'], method='PUT')[0] == 405
            assert request('userEnable', ['alice'], csrf=None)[0] == 403
            assert request('userEnable', ['alice'], csrf='invalid')[0] == 403
            assert request('userEnable', ['alice'], csrf=None, extra=[('csrf_token[]', 'invalid')])[0] == 403
            for action in ['userEnable', 'userDisable', 'refillSessionTime', 'refillSessionTraffic', 'userMail']:
                assert request(action, ['alice'], operator=9002)[0] == 403
                assert request(action, ['alice'], csrf='invalid')[0] == 403
            assert request('checkDisabled', ['alice'], method='GET', operator=9002, csrf=None)[0] == 403
            assert request('userEnable', [])[0] == 400
            assert request('userEnable', None, extra=[('username[][]', 'alice')])[0] == 400
            assert sql('SELECT COUNT(*) FROM radusergroup WHERE username="alice"') == '1'
            print('PASS: method/action/input validation, CSRF and both ACL mappings; no unauthorized mutation')

            assert request('checkDisabled', ['alice'], method='GET', csrf=None)[1]['disabled'] is True
            assert request('userDisable', ['alice'])[1]['success'] is False
            assert request('userEnable', ['alice'])[1]['success'] is True
            assert request('checkDisabled', ['alice'], method='GET', csrf=None)[1]['disabled'] is False
            odd = "O'Reilly & + é 50%"
            assert request('userDisable', ['alice', 'bob', odd, odd])[1]['success'] is True
            assert sql('SELECT COUNT(*) FROM radusergroup') == '3'
            assert request('checkDisabled', [odd], method='GET', csrf=None)[1]['disabled'] is True
            assert request('userDisable', [odd])[1]['success'] is False
            assert request('userEnable', ['alice', 'bob', odd])[1]['success'] is True
            assert sql('SELECT COUNT(*) FROM radusergroup') == '0'
            print('PASS: individual/bulk activation, disabled status, already disabled, deduplication and special characters')

            for action, amount, tax in [('refillSessionTime', '10.00', '2.00'), ('refillSessionTraffic', '20.00', '4.00')]:
                assert request(action, ['alice', 'bob', 'no-plan'])[1]['success'] is True
                column = 'acctsessiontime' if action == 'refillSessionTime' else 'acctinputoctets + acctoutputoctets'
                assert sql(f"SELECT SUM({column}) FROM radacct WHERE username IN ('alice','bob','no-plan')") == '0'
                if action == 'refillSessionTime':
                    assert sql("SELECT SUM(acctinputoctets + acctoutputoctets) FROM radacct WHERE username='alice'") == '10000'
                assert sql('SELECT amount, tax_amount FROM invoice_items ORDER BY id DESC LIMIT 1') == amount + '\t' + tax
            assert sql('SELECT COUNT(*) FROM billing_history') == '4'
            assert sql("SELECT username, COUNT(*) FROM billing_history GROUP BY username ORDER BY username") == 'alice\t2\nbob\t2'
            assert sql('SELECT COUNT(*) FROM invoice') == '2'
            assert sql('SELECT COUNT(*) FROM invoice_items') == '2'
            assert sql("SELECT acctsessiontime, acctinputoctets, acctoutputoctets FROM radacct WHERE username='untouched'") == '123\t456\t789'
            assert request('refillSessionTime', ['alice'])[1]['success'] is True
            assert sql('SELECT COUNT(*) FROM invoice') == '3'
            print('PASS: real accounting resets, paid/free/no-plan billing history, invoice amounts/tax and individual refill')

            # Real PHPMailer transport terminates at the loopback capture, never outside the internal network.
            assert request('userMail', ['alice'])[1]['success'] is True
            assert request('userMail', ['alice', 'bob'])[1]['success'] is True
            mixed = request('userMail', ['bad-mail', 'bob'])[1]
            assert mixed['success'] is False and 'Failed: 1' in mixed['message'], mixed
            assert request('userMail', ['missing'])[1]['success'] is False
            captures = [json.loads(line) for line in (fixture / 'mail.jsonl').read_text().splitlines()]
            assert len(captures) == 4, len(captures)
            assert all('VPN Credentials' in message for message in captures)
            assert any('fixture-alice' in message for message in captures)
            assert any('fixture-bob' in message for message in captures)
            print('PASS: individual/bulk SMTP capture, mixed failure aggregation and no recipients (no external mail)')

            # Force failures at each sensitive step; JSON must never claim success after a partial mutation.
            for table, action in [('billing_history', 'refillSessionTime'), ('invoice', 'refillSessionTraffic'), ('invoice_items', 'refillSessionTime'), ('radusergroup', 'userDisable')]:
                sql(f'RENAME TABLE {table} TO {table}_unavailable')
                try:
                    status, result = request(action, ['alice'])
                    assert status == 500 and result['success'] is False, (table, status, result)
                    assert 'already have been applied' in result['message']
                finally:
                    sql(f'RENAME TABLE {table}_unavailable TO {table}')
            print('PASS: database failures at group/history/invoice/item stages return JSON without false success')
            log_result = subprocess.run(['docker', 'logs', WEB], capture_output=True, text=True, check=True)
            logs = log_result.stdout + log_result.stderr
            assert 'PHP Fatal error' not in logs and 'PHP Warning' not in logs, logs[-2000:]

            # Render real caller pages and use their actual generated CSRF fields.
            class Inputs(HTMLParser):
                def __init__(self):
                    super().__init__()
                    self.tokens = []
                def handle_starttag(self, tag, attributes):
                    attrs = dict(attributes)
                    if tag == 'input' and attrs.get('name') == 'csrf_token':
                        self.tokens.append(attrs.get('value', ''))
            pages = ['mng-edit', 'bill-pos-edit', 'mng-list-all', 'mng-search', 'bill-pos-list', 'rep-batch-details']
            for page in pages:
                if page not in ['mng-edit', 'mng-search']:
                    sql(f"INSERT INTO operators_acl (operator_id, file, access) VALUES (9001, '{page.replace('-', '_')}', 1)")
            sql("INSERT INTO batch_history (id, batch_name) VALUES (9001, 'fixture-batch'); UPDATE userbillinfo SET batch_id=9001")
            for page in pages:
                query = 'batch_name=fixture-batch' if page == 'rep-batch-details' else 'username=alice'
                req = urllib.request.Request('http://' + address + '/' + page + '.php?' + query,
                    headers={'Cookie': 'daloradius_operator_sid=' + sessions[9001]})
                with urllib.request.urlopen(req) as response:
                    html = response.read().decode()
                    assert response.status == 200 and page + '.php' in response.url
                assert 'id="returnMessages"' in html, page
                assert 'static/js/ajaxGeneric.js' not in html and 'static/js/ajax.js' not in html, page
                parser = Inputs()
                parser.feed(html)
                assert parser.tokens and parser.tokens[0], page
                assert request('userDisable', ['alice'], csrf=parser.tokens[0])[1]['success'] is True, page
                assert request('userEnable', ['alice'], csrf=parser.tokens[0])[1]['success'] is True, page
            print('PASS: six real PHP caller pages supply a working CSRF token; SACK removed')
        finally:
            for container in [WEB, DB]:
                run('docker', 'rm', '-f', '-v', container, check=False)
            run('docker', 'network', 'rm', NETWORK, check=False)
            assert not run('docker', 'ps', '-aq', '--filter', 'name=' + PREFIX)
            print('CLEANUP: disposable containers and data removed; live lab untouched')


if __name__ == '__main__':
    main()
