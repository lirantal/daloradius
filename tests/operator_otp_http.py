#!/usr/bin/env python3
"""UNIT-029: isolated real HTTP/PHP/MariaDB OTP transaction comparison."""
import os
import secrets
import shutil
import subprocess
import tempfile
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap

import operator_login_http as login_fixture
from operator_login_http import Client, FormParser, add_operator, hash_password, login, quote, run, sql, wait_for

ROOT = Path(__file__).resolve().parents[1]
BASELINE = os.environ.get('OPERATOR_OTP_BASELINE') == '1'
BASE_COMMIT = '3da3c90ebeff36741ec238246778dd406ade4fc4'
DB, WEB, NETWORK = login_fixture.DB, login_fixture.WEB, login_fixture.NETWORK
IMAGE = os.environ.get('OPERATOR_OTP_WEB_IMAGE', 'lirantal/daloradius')


def php(expression, input_text=None):
    return run('docker', 'exec', '-i', WEB, 'php', '-r', expression, input=input_text)


def new_secret():
    return php("chdir('/fixtures/app/operators');require 'library/totp.php';echo dalo_totp_generate_secret();")


def code_for(secret):
    return php("chdir('/fixtures/app/operators');require 'library/totp.php';"
               "echo dalo_totp_new()->getCode(stream_get_contents(STDIN));", secret)


def recovery_hash(code):
    return php("chdir('/fixtures/app/operators');require 'library/totp.php';"
               "echo dalo_totp_hash_recovery_codes([stream_get_contents(STDIN)]);", code)


def probe(client):
    status, _, _, body = client.request('session_probe.php')
    assert status == 200
    import json
    return json.loads(body)


def otp_page(client):
    status, _, _, body = client.request('login-otp.php')
    assert status == 200
    form = FormParser(); form.feed(body)
    assert form.csrf
    return form.csrf


def verify(client, code):
    return client.request('login-otp.php', [('otp_code', code), ('csrf_token', otp_page(client))])


def main():
    scratch = Path.home() / '.hermes/cache/scratch'; scratch.mkdir(parents=True, exist_ok=True)
    password = secrets.token_urlsafe(24)
    username = 'otp-' + secrets.token_hex(6)
    secret = None
    with tempfile.TemporaryDirectory(prefix='dalo-op-otp-', dir=scratch) as directory:
        fixture = Path(directory)
        shutil.copytree(ROOT / 'app', fixture / 'app', symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
        if BASELINE:
            restore_pear_bootstrap((fixture / 'app').parent, BASE_COMMIT)
            old = run('git', 'show', BASE_COMMIT + ':app/operators/login-otp.php')
            (fixture / 'app/operators/login-otp.php').write_text(old + '\n')
        (fixture / 'app/operators/session_probe.php').write_text('''<?php
session_name('daloradius_operator_sid'); session_start();
echo json_encode(['logged_in'=>!empty($_SESSION['daloradius_logged_in']),
  'pending'=>!empty($_SESSION['operator_2fa_pending']),
  'location'=>isset($_SESSION['location_name'])?(string)$_SESSION['location_name']:'',
  'source'=>isset($_SESSION['operator_auth_source'])?(string)$_SESSION['operator_auth_source']:'']);
''')
        try:
            run('docker', 'network', 'create', '--internal', NETWORK)
            run('docker', 'run', '-d', '--name', DB, '--network', NETWORK,
                '--tmpfs', '/var/lib/mysql', '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e', 'MARIADB_DATABASE=radius', 'mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'), 'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql', 'mariadb-daloradius.sql'):
                sql((ROOT / 'contrib/db' / name).read_text())
            config = (ROOT / 'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>', '')
            for key, value in {'CONFIG_DB_ENGINE':'mysqli','CONFIG_DB_HOST':DB,
                               'CONFIG_DB_PORT':'3306','CONFIG_DB_USER':'root',
                               'CONFIG_DB_PASS':'','CONFIG_DB_NAME':'radius',
                               'CONFIG_LOG_PAGES':'no','CONFIG_LOG_QUERIES':'no',
                               'CONFIG_LOG_ACTIONS':'no','CONFIG_DEBUG_SQL':'no',
                               'CONFIG_DEBUG_SQL_ONPAGE':'no'}.items():
                config += '\n$configValues[' + quote(key) + '] = ' + quote(value) + ';\n'
            (fixture / 'app/common/includes/daloradius.conf.php').write_text(config)
            run('docker', 'run', '-d', '--name', WEB, '--network', NETWORK,
                '-e', 'PHP_CLI_SERVER_WORKERS=2',
                '-v', f'{fixture}:/fixtures', '-w', '/fixtures/app/operators',
                '--entrypoint', 'php', IMAGE, '-d', 'display_errors=0',
                '-S', '0.0.0.0:8080', '-t', '.')
            address = run('docker', 'inspect', '-f',
                          '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}', WEB)
            base = 'http://' + address + ':8080/'
            wait_for(lambda: Client(base).request('login.php')[0] == 200, 'PHP HTTP')
            secret = new_secret()
            add_operator(username, hash_password(WEB, password), totp_enabled=1,
                         totp_secret=secret)
            recovery = secrets.token_hex(5).upper()
            recovery = recovery[:5] + '-' + recovery[5:]
            sql('UPDATE operators SET totp_recovery_codes=%s WHERE username=%s' %
                (quote(recovery_hash(recovery)), quote(username)))
            def pending():
                client = Client(base)
                status, _, headers, _ = login(client, username, password, 'local', 'default')
                assert status == 302 and headers.get('Location', '').endswith('login-otp.php')
                assert probe(client) == {'logged_in': False, 'pending': True,
                                         'location': 'default', 'source': ''}
                return client
            client = pending()
            valid_code = code_for(secret)
            status, _, headers, _ = verify(client, valid_code)
            assert status == 302 and headers.get('Location', '').endswith('index.php')
            assert probe(client) == {'logged_in': True, 'pending': False,
                                     'location': 'default', 'source': 'local'}
            assert sql('SELECT totp_last_counter IS NOT NULL,lastlogin<>%s FROM operators WHERE username=%s' %
                       (quote('2000-01-01 00:00:00'), quote(username))) == '1\t1'
            counter = sql('SELECT totp_last_counter FROM operators WHERE username=%s' % quote(username))
            print('PASS: TOTP consumes one counter and commits before the session opens')

            client = pending()
            status, _, _, body = verify(client, valid_code)
            assert status == 200 and 'Invalid verification code' in body
            assert not probe(client)['logged_in']
            assert sql('SELECT totp_last_counter FROM operators WHERE username=%s' % quote(username)) == counter
            print('PASS: replay of the same TOTP counter is rejected')

            status, _, headers, _ = verify(client, recovery)
            assert status == 302 and headers.get('Location', '').endswith('index.php')
            assert probe(client)['logged_in'] and not probe(client)['pending']
            assert sql('SELECT totp_recovery_codes FROM operators WHERE username=%s' % quote(username)) == '[]'
            print('PASS: recovery code is consumed exactly once')

            client = pending()
            status, _, _, body = verify(client, recovery)
            assert status == 200 and 'Invalid verification code' in body
            assert not probe(client)['logged_in']
            print('PASS: used recovery code is rejected')

            if not BASELINE:
                # The old handler crashes on nested controls; the PDO handler must fail closed.
                csrf = otp_page(client)
                status, _, _, body = client.request('login-otp.php',
                    [('otp_code[]', valid_code), ('csrf_token', csrf)])
                assert status == 200 and 'Invalid verification code' in body
                assert not probe(client)['logged_in']
                csrf = otp_page(client)
                status, _, _, body = client.request('login-otp.php',
                    [('otp_code', valid_code), ('csrf_token[]', csrf)])
                assert status == 200 and 'CSRF token error' in body
                assert not probe(client)['logged_in']
                print('PASS: malformed OTP and CSRF arrays fail closed')

                sql('UPDATE operators SET lastlogin=%s WHERE username=%s' %
                    (quote('2000-01-01 00:00:00'), quote(username)))
                old_counter = sql('SELECT totp_last_counter FROM operators WHERE username=%s' % quote(username))
                unused = secrets.token_hex(5).upper()
                unused = unused[:5] + '-' + unused[5:]
                sql('UPDATE operators SET totp_recovery_codes=%s WHERE username=%s' %
                    (quote(recovery_hash(unused)), quote(username)))
                failure_client = pending()
                sql("CREATE TRIGGER reject_otp_write BEFORE UPDATE ON operators FOR EACH ROW "
                    "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture OTP error'")
                try:
                    status, _, _, body = verify(failure_client, unused)
                    assert status == 200 and 'Invalid verification code' in body
                    assert 'SQLSTATE' not in body and 'fixture OTP error' not in body
                    assert not probe(failure_client)['logged_in']
                    assert sql('SELECT JSON_LENGTH(totp_recovery_codes),lastlogin=%s,totp_last_counter=%s '
                               'FROM operators WHERE username=%s' %
                               (quote('2000-01-01 00:00:00'), old_counter, quote(username))) == '1\t1\t1'
                finally:
                    sql('DROP TRIGGER reject_otp_write')
                status, _, headers, _ = verify(failure_client, unused)
                assert status == 302 and headers.get('Location', '').endswith('index.php')
                print('PASS: failed OTP write rolls back; unused recovery code remains valid')

                late_user = 'otp-late-' + secrets.token_hex(6)
                late_secret = new_secret()
                add_operator(late_user, hash_password(WEB, password),
                             totp_enabled=1, totp_secret=late_secret)
                late_client = Client(base)
                status, _, headers, _ = login(late_client, late_user, password, 'local', 'default')
                assert status == 302 and headers.get('Location','').endswith('login-otp.php')
                late_code = code_for(late_secret)
                sql("CREATE TRIGGER reject_otp_write BEFORE UPDATE ON operators FOR EACH ROW "
                    "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture OTP error'")
                try:
                    status, _, _, body = verify(late_client, late_code)
                    assert status == 200 and 'Invalid verification code' in body
                    assert not probe(late_client)['logged_in']
                    assert sql('SELECT totp_last_counter IS NULL,lastlogin=%s FROM operators WHERE username=%s' %
                               (quote('2000-01-01 00:00:00'), quote(late_user))) == '1\t1'
                finally:
                    sql('DROP TRIGGER reject_otp_write')
                status, _, headers, _ = verify(late_client, late_code)
                assert status == 302 and headers.get('Location','').endswith('index.php')
                print('PASS: failed TOTP counter write does not consume the valid code')

            ldap_user = 'otp-ldap-' + secrets.token_hex(6)
            ldap_identity = 'directory-' + secrets.token_hex(12)
            ldap_secret = new_secret()
            add_operator(ldap_user, None, source='ldap', external_id=ldap_identity,
                         totp_enabled=1, totp_secret=ldap_secret)
            (fixture / 'app/operators/seed_pending.php').write_text('''<?php
session_name('daloradius_operator_sid'); session_id($argv[1]); session_start();
$_SESSION = ['operator_2fa_pending'=>true, 'operator_2fa_id'=>(int)$argv[2],
  'operator_2fa_user'=>$argv[3], 'operator_2fa_auth_source'=>'ldap',
  'operator_2fa_external_id'=>$argv[4], 'operator_2fa_attempts'=>0,
  'daloradius_logged_in'=>false, 'location_name'=>'default', 'time'=>time()];
session_write_close();
''')
            def ldap_pending(identity):
                client = Client(base)
                client.sid = secrets.token_hex(16)
                run('docker','exec',WEB,'php','/fixtures/app/operators/seed_pending.php',
                    client.sid, sql('SELECT id FROM operators WHERE username=%s' % quote(ldap_user)),
                    ldap_user, identity)
                assert probe(client)['pending'] and not probe(client)['logged_in']
                return client
            changed = ldap_pending('other-' + secrets.token_hex(8))
            status, _, _, body = verify(changed, code_for(ldap_secret))
            assert status == 200 and 'Invalid verification code' in body
            assert not probe(changed)['logged_in']
            assert sql('SELECT totp_last_counter IS NULL FROM operators WHERE username=%s' % quote(ldap_user)) == '1'
            allowed = ldap_pending(ldap_identity)
            status, _, headers, _ = verify(allowed, code_for(ldap_secret))
            assert status == 302 and headers.get('Location','').endswith('index.php')
            assert probe(allowed)['logged_in']
            print('PASS: LDAP pending factor requires the same external identity')

            parallel_user = 'otp-parallel-' + secrets.token_hex(6)
            parallel_secret = new_secret()
            add_operator(parallel_user, hash_password(WEB, password), totp_enabled=1,
                         totp_secret=parallel_secret)
            def parallel_pending():
                client = Client(base)
                status, _, headers, _ = login(client, parallel_user, password, 'local', 'default')
                assert status == 302 and headers.get('Location','').endswith('login-otp.php')
                return client
            first, second = parallel_pending(), parallel_pending()
            parallel_code = code_for(parallel_secret)
            with ThreadPoolExecutor(max_workers=2) as pool:
                results = [future.result() for future in
                           [pool.submit(verify, first, parallel_code),
                            pool.submit(verify, second, parallel_code)]]
            assert sorted(result[0] for result in results) == [200,302]
            assert sorted((probe(first)['logged_in'], probe(second)['logged_in'])) == [False,True]
            print('PASS: concurrent use of one TOTP counter authenticates only once')

            client = pending()
            for attempt in range(5):
                status, _, headers, _ = verify(client, secrets.token_hex(9))
                if attempt < 4: assert status == 200
                else: assert status == 302 and headers.get('Location', '').endswith('login.php')
            assert not probe(client)['pending'] and not probe(client)['logged_in']
            print('PASS: five invalid attempts clear pending MFA state')

            if not BASELINE:
                engine_client = pending()
                sql('ALTER TABLE operators ENGINE=MyISAM')
                try:
                    status, _, _, body = verify(engine_client, secrets.token_hex(9))
                    assert status == 200 and 'Invalid verification code' in body
                    assert not probe(engine_client)['logged_in']
                finally:
                    sql('ALTER TABLE operators ENGINE=InnoDB')
                print('PASS: nontransactional operator table is rejected before OTP mutation')

            preupgrade_user = 'otp-preupgrade-' + secrets.token_hex(6)
            preupgrade_secret = new_secret()
            add_operator(preupgrade_user, hash_password(WEB, password),
                         totp_enabled=1, totp_secret=preupgrade_secret)
            sql('ALTER TABLE operators DROP COLUMN external_id, DROP COLUMN auth_source')
            preupgrade = Client(base)
            status, _, headers, _ = login(preupgrade, preupgrade_user, password, 'local', 'default')
            assert status == 302 and headers.get('Location','').endswith('login-otp.php')
            status, _, headers, _ = verify(preupgrade, code_for(preupgrade_secret))
            assert status == 302 and headers.get('Location','').endswith('index.php')
            assert probe(preupgrade)['logged_in']
            print('PASS: pre-provider-migration local pending session completes MFA')

            logs = subprocess.run(['docker','logs', WEB], text=True, capture_output=True, timeout=30, check=True)
            assert 'PHP Fatal error' not in logs.stdout + logs.stderr
            assert 'PHP Warning' not in logs.stdout + logs.stderr
            print('PASS: isolated PHP logs contain no fatal errors or warnings')
        finally:
            for container in (WEB, DB): run('docker', 'rm', '-f', '-v', container, check=False)
            run('docker', 'network', 'rm', NETWORK, check=False)
            assert not run('docker','ps','-aq','--filter','name=' + login_fixture.harness.PREFIX)
            print('CLEANUP: disposable OTP containers, network, and fixture data removed')

if __name__ == '__main__':
    main()
