#!/usr/bin/env python3
"""UNIT-042: real isolated PHP CLI/HTTP + MariaDB retirement/legacy proof.

Uses only synthetic accounts without passwords, an internal Docker network and
an ephemeral database. No production config, cron, account or DB is accessed.
Connection settings come from disposable container environment, not config data.
"""
import concurrent.futures
import json
import os
from pathlib import Path
import re
import secrets
import shutil
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request

ROOT = Path(__file__).resolve().parents[1]
BASE = 'c86140442bc475e4b3de3404b8e61ff70ba9eb89'
BASELINE = os.environ.get('EXPIRED_ACCOUNTS_BASELINE') == '1'
TAG = 'u42-' + secrets.token_hex(6)
DB, WEB, NET = (TAG + '-' + suffix for suffix in ('db', 'web', 'net'))
PATH = 'contrib/scripts/maintenance/cleanExpiredAccounts.php'
MESSAGE = b'Legacy expired-account cleanup is retired. No accounts were deleted.'
TABLES = ('radacct', 'userbillinfo', 'userinfo', 'radcheck', 'radreply', 'radusergroup', 'billing_history')
USERS = ('fixture-ttf', 'fixture-recent', 'fixture-spent', 'fixture-overdue', 'fixture-keep', "fixture-%-+-é-'")
KEEP = set(USERS[-2:])


def run(*args, input=None, check=True, timeout=60):
    result = subprocess.run(args, input=input, capture_output=True, text=True, timeout=timeout)
    if check and result.returncode:
        # Do not surface driver logs, environment or arbitrary fixture output.
        raise RuntimeError('Isolated fixture command failed, details omitted: ' + args[0])
    return result


def wait_for(function, name):
    for _ in range(40):
        try:
            if function():
                return
        except (RuntimeError, OSError, urllib.error.URLError):
            pass
        time.sleep(0.5)
    raise RuntimeError('Isolated fixture readiness failed: ' + name)


def sql(query):
    p = run('docker', 'exec', '-i', DB, 'mariadb', '-uroot',
            '--default-character-set=utf8mb4', '-N', '-B', 'fixture', input=query, check=False)
    if p.returncode:
        match = re.search(r'ERROR\s+(\d+)', p.stderr)
        raise RuntimeError('Fixture SQL failed; code ' + (match.group(1) if match else 'unavailable'))
    return p.stdout.strip()


def state():
    return {table: sql('SELECT * FROM ' + table + ' ORDER BY ' +
                       ('radacctid' if table == 'radacct' else 'id')) for table in TABLES}


def request(base, method='POST', data=b'', content_type='application/x-www-form-urlencoded'):
    if isinstance(data, dict):
        data = urllib.parse.urlencode(data, doseq=True).encode()
    req = urllib.request.Request(base + PATH, method=method,
                                 data=None if method in ('GET', 'HEAD') else data,
                                 headers={'Content-Type': content_type})
    try:
        response = urllib.request.urlopen(req, timeout=20)
    except urllib.error.HTTPError as error:
        response = error
    return response.status, response.read(), {k.lower(): v for k, v in response.headers.items()}


def cli(cwd='/fixtures', args=()):
    return run('docker', 'exec', '-w', cwd, WEB, 'php', '-d', 'display_errors=0',
               '-d', 'log_errors=1', '-d', 'error_reporting=32767',
               '-d', 'include_path=/fixtures/mock:/usr/share/php', '/fixtures/' + PATH,
               *args, check=False)


def seed():
    for table in TABLES:
        sql('TRUNCATE TABLE ' + table)
    quote = lambda value: "'" + value.replace("'", "''") + "'"
    for user in USERS:
        plan = 'fixture-ttf-plan' if user == 'fixture-ttf' else 'fixture-acc-plan'
        sql('INSERT INTO userbillinfo(username,planName) VALUES (' + quote(user) + ',' + quote(plan) + ')')
        for table in ('userinfo', 'radcheck', 'radreply', 'radusergroup', 'billing_history'):
            # No authentication password/hash or real personal data is seeded.
            sql('INSERT INTO ' + table + '(username,note) VALUES (' + quote(user) + ",'fixture-note')")
        start = 'NOW()-INTERVAL 100 DAY' if user in ('fixture-recent', 'fixture-overdue') else 'NOW()-INTERVAL 20 MINUTE'
        session = 1200 if user == 'fixture-ttf' else 3000 if user == 'fixture-spent' else 10
        sql('INSERT INTO radacct(username,AcctStartTime,AcctSessionTime) VALUES (' +
            quote(user) + ',' + start + ',' + str(session) + ')')
        if user == 'fixture-recent':
            sql('INSERT INTO radacct(username,AcctStartTime,AcctSessionTime) VALUES (' +
                quote(user) + ',NOW()-INTERVAL 1 DAY,10)')


LEGACY_CONFIG = r'''<?php
// Compatibility loader in the disposable baseline only; no persisted settings.
$configValues = array(
    'CONFIG_DB_ENGINE' => getenv('FIXTURE_DB_ENGINE'),
    'CONFIG_DB_USER' => getenv('FIXTURE_DB_USER'),
    'CONFIG_DB_PASS' => getenv('FIXTURE_DB_PASS'),
    'CONFIG_DB_HOST' => getenv('FIXTURE_DB_HOST'),
    'CONFIG_DB_PORT' => getenv('FIXTURE_DB_PORT'),
    'CONFIG_DB_NAME' => getenv('FIXTURE_DB_NAME'),
    'CONFIG_DB_TBL_RADACCT' => 'radacct',
    'CONFIG_DB_TBL_DALOBILLINGPLANS' => 'billing_plans',
    'CONFIG_DB_TBL_DALOUSERBILLINFO' => 'userbillinfo',
    'CONFIG_DB_TBL_DALOUSERINFO' => 'userinfo',
    'CONFIG_DB_TBL_RADCHECK' => 'radcheck',
    'CONFIG_DB_TBL_RADREPLY' => 'radreply'
);
'''
TRIPWIRE = "<?php throw new RuntimeException('Fixture configuration or PEAR must not be loaded');\n"


def main():
    scratch = Path.home() / '.hermes/cache/scratch'
    scratch.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='expired-accounts-', dir=scratch) as directory:
        fixture = Path(directory)
        script = fixture / PATH
        script.parent.mkdir(parents=True)
        if BASELINE:
            script.write_text(run('git', 'show', BASE + ':' + PATH).stdout)
        else:
            shutil.copy2(ROOT / PATH, script)
        (fixture / 'health.php').write_text('<?php echo "ready";')
        for rel in ('library', 'app/common/includes', 'mock'):
            (fixture / rel).mkdir(parents=True)
        try:
            run('docker', 'network', 'create', '--internal', NET)
            run('docker', 'run', '-d', '--name', DB, '--network', NET, '--tmpfs', '/var/lib/mysql',
                '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1', '-e', 'MARIADB_DATABASE=fixture', 'mariadb:11.8')
            wait_for(lambda: sql('SELECT 1') == '1', 'MariaDB')
            sql('''CREATE TABLE radacct(radacctid BIGINT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(128),AcctStartTime DATETIME,AcctSessionTime INT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
                CREATE TABLE billing_plans(planName VARCHAR(128),planTimeType VARCHAR(128),planTimeBank INT) ENGINE=InnoDB;
                CREATE TABLE userbillinfo(id BIGINT AUTO_INCREMENT PRIMARY KEY,username VARCHAR(128),planName VARCHAR(128)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
                INSERT INTO billing_plans VALUES ('fixture-ttf-plan','Time-To-Finish',3600),('fixture-acc-plan','Accumulative',3600);''')
            for table in TABLES[2:]:
                sql('CREATE TABLE ' + table + '(id BIGINT AUTO_INCREMENT PRIMARY KEY,username VARCHAR(128),note VARCHAR(128)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin')
            seed()
            run('docker', 'run', '-d', '--name', WEB, '--network', NET, '-v', f'{fixture}:/fixtures:ro',
                '-e', 'FIXTURE_DB_ENGINE=mysqli', '-e', 'FIXTURE_DB_USER=root', '-e', 'FIXTURE_DB_PASS=',
                '-e', 'FIXTURE_DB_HOST=' + DB, '-e', 'FIXTURE_DB_PORT=3306', '-e', 'FIXTURE_DB_NAME=fixture',
                '-e', 'PHP_CLI_SERVER_WORKERS=4', '--entrypoint', 'php', 'lirantal/daloradius',
                '-d', 'display_errors=0', '-d', 'log_errors=1', '-d', 'opcache.enable=0',
                '-d', 'opcache.enable_cli=0', '-d', 'include_path=/fixtures/mock:/usr/share/php',
                '-S', '0.0.0.0:8080', '-t', '/fixtures')
            ip = run('docker', 'inspect', '-f', '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}', WEB).stdout.strip()
            base = 'http://' + ip + ':8080/'
            wait_for(lambda: urllib.request.urlopen(base + 'health.php', timeout=5).read() == b'ready', 'PHP HTTP')
            initial = state()
            if BASELINE:
                missing = cli()
                assert missing.returncode == 0 and missing.stdout.startswith('DB connection error.\nMessage: ')
                assert 'config_read.php' in missing.stderr and 'Failed to open stream' in missing.stderr
                assert state() == initial
                print('PASS baseline: missing legacy loader produces connection error with exit 0; no deletion, not a working baseline')
                (fixture / 'library/config_read.php').write_text(LEGACY_CONFIG)
                success = cli()
                assert success.returncode == 0 and success.stdout == ''
                for table in TABLES[:5]:
                    assert set(sql('SELECT DISTINCT username FROM ' + table).splitlines()) == KEEP
                for table in TABLES[5:]:
                    assert state()[table] == initial[table]
                print('PASS native baseline CLI with fixture-only compatibility loader: deletes unexpired Time-To-Finish and recently logged-in accounts')
                seed()
                code, body, _ = request(base)
                assert code == 200 and body == b''
                for table in TABLES[:5]:
                    assert set(sql('SELECT DISTINCT username FROM ' + table).splitlines()) == KEEP
                print('PASS native baseline HTTP: same destructive selection is reachable without authentication; fixture only')
            else:
                for rel in ('library/config_read.php', 'app/common/includes/config_read.php', 'mock/DB.php'):
                    (fixture / rel).write_text(TRIPWIRE)
                def refuse_cli(cwd='/fixtures', args=()):
                    p = cli(cwd, args)
                    assert p.returncode == 1 and p.stdout == '' and p.stderr == MESSAGE.decode() + '\n'
                for cwd in ('/', '/fixtures', '/fixtures/contrib/scripts/maintenance'):
                    for args in ((), ('--force',), ('--dry-run',), ('--help',), ('--execute', "quote-%-é-<script>")):
                        refuse_cli(cwd, args)
                assert state() == initial
                print('PASS candidate CLI: fixed stderr/exit 1 from all working directories and with misleading flags; seven tables unchanged')
                marker = 'UNTRUSTED_FIXTURE_MARKER_' + secrets.token_hex(8)
                def refuse_http(method='POST', data=b'', content_type='application/x-www-form-urlencoded'):
                    code, body, headers = request(base, method, data, content_type)
                    assert code == 410 and body == (b'' if method == 'HEAD' else MESSAGE)
                    assert headers.get('content-type') == 'text/plain; charset=UTF-8'
                    assert headers.get('cache-control') == 'no-store'
                    assert headers.get('x-content-type-options') == 'nosniff'
                    assert 'set-cookie' not in headers and 'location' not in headers
                for method in ('GET', 'POST', 'HEAD', 'PUT', 'DELETE', 'OPTIONS'):
                    refuse_http(method, {'execute': '1', 'marker': marker})
                for payload, content_type in (
                    ({'username[]': ['bad', 'é'], 'force[]': ['1']}, 'application/x-www-form-urlencoded'),
                    ({'username': "quote'-%-é-<script>", 'marker': marker}, 'application/x-www-form-urlencoded'),
                    (b'x' * 70000, 'application/x-www-form-urlencoded'),
                    (json.dumps({'execute': True, 'marker': marker}).encode(), 'application/json'),
                ):
                    refuse_http(data=payload, content_type=content_type)
                with concurrent.futures.ThreadPoolExecutor(4) as pool:
                    list(pool.map(lambda _: refuse_http(data={'force': '1', 'marker': marker}), range(8)))
                assert state() == initial
                print('PASS candidate HTTP: fixed 410/no-store/nosniff, malformed/Unicode/large/JSON/concurrent requests; seven tables unchanged')
                for rel in ('library', 'app', 'mock'):
                    shutil.rmtree(fixture / rel)
                refuse_cli()
                refuse_http()
                assert state() == initial
                run('docker', 'stop', DB)
                refuse_cli(args=('--force',))
                refuse_http(data={'execute': '1'})
                print('PASS candidate: tripwires never load; CLI/HTTP still refuse without config, PEAR or running DB')
                logs = run('docker', 'logs', WEB)
                combined = logs.stdout + logs.stderr
                assert marker not in combined
                assert not any(value in combined.lower() for value in ('warning:', 'fatal error:', 'sqlstate', 'pdoexception', 'must not be loaded'))
                print('PASS candidate: clean PHP stdout/stderr, no payload reflection or application logging')
        finally:
            for name in (WEB, DB):
                run('docker', 'rm', '-f', name, check=False)
            run('docker', 'network', 'rm', NET, check=False)
            for name in (WEB, DB):
                assert run('docker', 'inspect', name, check=False).returncode != 0
            assert run('docker', 'network', 'inspect', NET, check=False).returncode != 0
    assert not fixture.exists()
    print('PASS cleanup: isolated fixture, database, containers and internal network removed; no production account or DB accessed')


if __name__ == '__main__':
    main()
