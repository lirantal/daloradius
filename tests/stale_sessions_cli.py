#!/usr/bin/env python3
"""UNIT-043: isolated real PHP CLI/HTTP and MariaDB A/B + rollback tests.

No live cron, config or accounting DB. Generated fixture connection values are
held only in memory and disposable container environment, never written/logged.
"""
import concurrent.futures
from datetime import datetime, timezone
import os
from pathlib import Path
import secrets
import shutil
import tempfile
import urllib.error
import urllib.request

from expired_accounts_retirement import run, wait_for

ROOT = Path(__file__).resolve().parents[1]
BASE = '1707f4a2ff742de36465764e2e8ffc5ae504a5d5'
BASELINE = os.environ.get('STALE_SESSIONS_BASELINE') == '1'
TAG = 'u43-' + secrets.token_hex(6)
DB, WEB, NET = (TAG + '-' + suffix for suffix in ('db', 'web', 'net'))
PATH = 'contrib/scripts/maintenance/fix-stale-sessions.php'
TABLE = 'radacct_u43'
CLOCK = int(datetime(2026, 9, 30, 12, tzinfo=timezone.utc).timestamp())
FAILURE = 'Stale-session repair failed.\n'


def sql(query):
    result = run('docker', 'exec', '-i', DB, 'mariadb', '-uroot',
                 '--default-character-set=utf8mb4', '-N', '-B', 'fixture',
                 input="SET SESSION sql_mode=''; SET timestamp=" + str(CLOCK) + ';\n' + query,
                 check=False)
    if result.returncode:
        raise RuntimeError('Isolated SQL failed; driver details omitted')
    return result.stdout.strip()


def state():
    return sql('SELECT * FROM ' + TABLE + ' ORDER BY radacctid')


def seed(threshold=90, empty=False):
    sql('TRUNCATE TABLE ' + TABLE)
    if empty:
        return
    sql(f'''INSERT INTO {TABLE}(username,acctstarttime,acctsessiontime,acctstoptime,acctterminatecause) VALUES
        ('fixture-stale',FROM_UNIXTIME({CLOCK-threshold-601}),600,NULL,''),
        ('fixture-zero-stop',FROM_UNIXTIME({CLOCK-threshold-601}),600,'0000-00-00 00:00:00',''),
        ('fixture-boundary',FROM_UNIXTIME({CLOCK-threshold-600}),600,NULL,''),
        ('fixture-fresh',FROM_UNIXTIME({CLOCK-max(threshold-1,0)-600}),600,NULL,''),
        ('fixture-closed',FROM_UNIXTIME({CLOCK-10000}),20,FROM_UNIXTIME({CLOCK-1000}),'User-Request'),
        ('fixture-late-start',NULL,120,NULL,''),
        ('fixture-zero-start','0000-00-00 00:00:00',120,NULL,''),
        ('fixture-null-counter',NULL,NULL,NULL,''),
        ('fixture-empty-counter',NULL,0,NULL,''),
        ('fixture-future',FROM_UNIXTIME({CLOCK+1200}),20,NULL,''),
        ('fixture-%-+-é-''quote',FROM_UNIXTIME({CLOCK-threshold-601}),600,NULL,'')''')


def reference(threshold):
    # Native golden queries preserve the exact legacy expressions and order.
    sql(f'''UPDATE {TABLE} SET acctstoptime=NOW(),acctterminatecause='Stale-Session'
          WHERE (UNIX_TIMESTAMP(NOW())-(UNIX_TIMESTAMP(acctstarttime)+acctsessiontime))>{threshold}
            AND (acctstoptime='0000-00-00 00:00:00' OR acctstoptime IS NULL);
          UPDATE {TABLE} SET acctstarttime=DATE_ADD(NOW(), INTERVAL (acctsessiontime+{threshold}) SECOND)
          WHERE (acctstarttime='0000-00-00 00:00:00' OR acctstarttime IS NULL) AND acctsessiontime>0;''')
    return state()


def cli(cwd='/'):
    return run('docker', 'exec', '-w', cwd, WEB, 'php', '-d', 'display_errors=0',
               '-d', 'error_reporting=8191', '/fixtures/' + PATH, check=False)


def config(common, interval=None, grace=None, table=TABLE):
    def quote(value):
        return "'" + str(value).replace('\\', '\\\\').replace("'", "\\'") + "'"
    text = '<?php\n$configValues = array();\n'
    for key, env in [('ENGINE', 'ENGINE'), ('HOST', 'HOST'), ('PORT', 'PORT'),
                     ('NAME', 'NAME'), ('USER', 'USER'), ('PASS', 'PASS')]:
        text += "$configValues['CONFIG_DB_" + key + "']=getenv('FIXTURE_DB_" + env + "');\n"
    text += "$configValues['FREERADIUS_VERSION']='3';\n"
    text += "$configValues['CONFIG_DB_TBL_RADACCT']=" + quote(table) + ';\n'
    for key, value in [('INTERVAL', interval), ('GRACE', grace)]:
        if value is not None:
            text += "$configValues['CONFIG_FIX_STALE_" + key + "']=" + (
                'array(1)' if isinstance(value, list) else quote(value)) + ';\n'
    (common / 'daloradius.conf.php').write_text(text)


def main():
    scratch = Path.home() / '.hermes/cache/scratch'
    scratch.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='stale-sessions-', dir=scratch) as directory:
        fixture = Path(directory)
        script = fixture / PATH
        script.parent.mkdir(parents=True)
        script.write_text(run('git', 'show', BASE + ':' + PATH).stdout) if BASELINE else shutil.copy2(ROOT / PATH, script)
        common = fixture / 'app/common/includes'
        common.mkdir(parents=True)
        for name in ('config_read.php', 'version.php', 'db_open.php', 'db_close.php',
                     'db_table_conventions.php', 'db_error_handler.php', 'pdo_connection.php'):
            shutil.copy2(ROOT / 'app/common/includes' / name, common / name)
        (fixture / 'health.php').write_text('<?php echo "ready";')
        user, password = 'fixture_' + secrets.token_hex(5), secrets.token_hex(32)
        config(common)
        try:
            run('docker', 'network', 'create', '--internal', NET)
            run('docker', 'run', '-d', '--name', DB, '--network', NET, '--tmpfs', '/var/lib/mysql',
                '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1', '-e', 'MARIADB_DATABASE=fixture', 'mariadb:11.8')
            wait_for(lambda: sql('SELECT 1') == '1', 'MariaDB')
            sql(f'''CREATE TABLE {TABLE}(radacctid BIGINT AUTO_INCREMENT PRIMARY KEY,
                 username VARCHAR(128),acctstarttime DATETIME NULL,acctsessiontime BIGINT NULL,
                 acctstoptime DATETIME NULL,acctterminatecause VARCHAR(64)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                 CREATE USER '{user}'@'%' IDENTIFIED BY '{password}';
                 GRANT SELECT,UPDATE ON fixture.* TO '{user}'@'%';
                 SET GLOBAL init_connect='SET timestamp={CLOCK}';''')
            environment = {'FIXTURE_DB_ENGINE': 'mysqli', 'FIXTURE_DB_HOST': DB, 'FIXTURE_DB_PORT': '3306',
                           'FIXTURE_DB_NAME': 'fixture', 'FIXTURE_DB_USER': user, 'FIXTURE_DB_PASS': password}
            # Docker reads these values from the child environment; never put them in files/argv.
            import subprocess
            args = ['docker', 'run', '-d', '--name', WEB, '--network', NET, '-v', f'{fixture}:/fixtures:ro']
            for key in environment:
                args += ['-e', key]
            args += ['--entrypoint', 'php', 'lirantal/daloradius', '-d', 'display_errors=0',
                     '-d', 'opcache.enable=0', '-d', 'opcache.enable_cli=0', '-S', '0.0.0.0:8080', '-t', '/fixtures']
            result = subprocess.run(args, env=dict(os.environ, **environment), capture_output=True, text=True, timeout=60)
            assert result.returncode == 0
            environment.clear()
            ip = run('docker', 'inspect', '-f', '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}', WEB).stdout.strip()
            base = 'http://' + ip + ':8080/'
            wait_for(lambda: urllib.request.urlopen(base + 'health.php', timeout=5).read() == b'ready', 'PHP HTTP')
            for interval, grace, threshold in [(None, None, 90), ('30', '10', 40), ('30', '31', 45),
                                                ('1', '0', 1), ('0', '10', 70), ('-5', 'bogus', 90),
                                                ('60suffix', '30suffix', 90)]:
                config(common, interval, grace)
                seed(threshold)
                expected = reference(threshold)
                seed(threshold)
                p = cli()
                assert p.returncode == 0 and p.stdout == '' and p.stderr == ''
                assert state() == expected
                assert sql(f"SELECT acctstoptime IS NULL FROM {TABLE} WHERE username='fixture-boundary'") == '1'
                assert sql(f"SELECT UNIX_TIMESTAMP(acctstarttime) FROM {TABLE} WHERE username='fixture-late-start'") == str(CLOCK+120+threshold)
            config(common)
            seed(empty=True)
            p = cli('/fixtures/contrib/scripts/maintenance')
            assert p.returncode == 0 and p.stdout == '' and p.stderr == '' and state() == ''
            print('PASS A/B: unmodified PEAR baseline' if BASELINE else 'PASS A/B: PDO candidate',
                  'matches native golden state for threshold defaults/conversions, strict boundary, NULL/zero dates/counters, closed/future sessions, custom table and empty table')
            seed()
            before = state()
            sql(f'''DELIMITER //
              CREATE TRIGGER fixture_late_error BEFORE UPDATE ON {TABLE} FOR EACH ROW BEGIN
                IF OLD.username='fixture-late-start' AND OLD.acctstarttime IS NULL AND NEW.acctstarttime IS NOT NULL THEN
                  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture-late-update';
                END IF;
              END//
              DELIMITER ;''')
            p = cli()
            if BASELINE:
                assert p.returncode == 0 and 'fixture-late-update' in p.stdout
                assert state() != before
                assert sql(f"SELECT acctterminatecause FROM {TABLE} WHERE username='fixture-stale'") == 'Stale-Session'
                print('PASS baseline characterization: second UPDATE error leaves first UPDATE committed and exits 0')
            else:
                assert p.returncode == 1 and p.stdout == '' and p.stderr == FAILURE and state() == before
                print('PASS candidate: forced second UPDATE failure rolls back first UPDATE; nonzero status, fixed redacted error')
            sql('DROP TRIGGER fixture_late_error')
            if not BASELINE:
                config(common)
                seed()
                before = state()
                sql(f'''CREATE TRIGGER fixture_first_error BEFORE UPDATE ON {TABLE} FOR EACH ROW
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture-first-update';''')
                p = cli()
                assert p.returncode == 1 and p.stdout == '' and p.stderr == FAILURE and state() == before
                sql('DROP TRIGGER fixture_first_error')
                print('PASS candidate: first UPDATE failure leaves all accounting rows unchanged')
                seed()
                expected = reference(90)
                seed()
                with concurrent.futures.ThreadPoolExecutor(2) as pool:
                    results = list(pool.map(lambda _: cli(), range(2)))
                assert all(p.returncode == 0 and p.stdout == '' and p.stderr == '' for p in results)
                assert state() == expected
                print('PASS candidate: two independent concurrent CLI repairs converge to expected state')
                seed()
                with concurrent.futures.ThreadPoolExecutor(2) as pool:
                    writer = pool.submit(sql, f'''START TRANSACTION;
                        UPDATE {TABLE} SET acctsessiontime=691 WHERE username='fixture-stale';
                        DO SLEEP(3) /*u43-accounting*/; COMMIT;''')
                    wait_for(lambda: int(sql("SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE INFO LIKE 'DO SLEEP(3)%'")) > 0, 'accounting writer lock')
                    p = cli()
                    writer.result()
                assert p.returncode == 0 and p.stderr == ''
                assert sql(f"SELECT acctstoptime IS NULL FROM {TABLE} WHERE username='fixture-stale'") == '1'
                print('PASS candidate: UPDATE rechecks freshness after a concurrent accounting writer releases its row lock')
                # A native targeted probe verifies that even LIMIT 0 retains MDL
                # across preflight; no application synchronization hook is added.
                (fixture / 'metadata_probe.php').write_text('''<?php
                    require __DIR__ . '/app/common/includes/config_read.php';
                    require __DIR__ . '/app/common/includes/pdo_connection.php';
                    $pdo=dalo_pdo_connect($configValues);
                    $pdo->beginTransaction();
                    $pdo->query('SELECT 1 FROM radacct_u43 LIMIT 0')->closeCursor();
                    $pdo->query('DO SLEEP(3)')->closeCursor();
                    $pdo->rollBack();
                ''')
                before = state()
                with concurrent.futures.ThreadPoolExecutor(2) as pool:
                    probe = pool.submit(run, 'docker', 'exec', WEB, 'php', '/fixtures/metadata_probe.php')
                    wait_for(lambda: int(sql("SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE INFO LIKE 'DO SLEEP(3)%'")) > 0, 'metadata probe')
                    ddl = pool.submit(sql, 'ALTER TABLE ' + TABLE + ' ENGINE=MyISAM')
                    wait_for(lambda: int(sql("SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE STATE='Waiting for table metadata lock' AND INFO LIKE 'ALTER TABLE " + TABLE + "%'")) > 0, 'blocked engine DDL')
                    assert not ddl.done()
                    p = probe.result()
                    assert p.returncode == 0 and p.stdout == '' and p.stderr == ''
                    ddl.result()
                assert state() == before
                sql('ALTER TABLE ' + TABLE + ' ENGINE=InnoDB')
                print('PASS native PDO preflight probe: zero-row SELECT retains metadata lock and blocks concurrent engine-changing DDL')
                seed()
                before = state()
                for table in ('bad;DROP_TABLE', 'x' * 65, ''):
                    config(common, table=table)
                    p = cli()
                    assert p.returncode == 1 and p.stdout == '' and p.stderr == FAILURE and state() == before
                config(common, interval=[1])
                p = cli()
                assert p.returncode == 1 and p.stderr == FAILURE and state() == before
                config(common, interval=str(2**63-1), grace='1')
                p = cli()
                assert p.returncode == 1 and p.stderr == FAILURE and state() == before
                config(common)
                sql('ALTER TABLE ' + TABLE + ' ENGINE=MyISAM')
                before = state()
                p = cli()
                assert p.returncode == 1 and p.stderr == FAILURE and state() == before
                sql('ALTER TABLE ' + TABLE + ' ENGINE=InnoDB')
                sql('RENAME TABLE ' + TABLE + ' TO fixture_backup; CREATE VIEW ' + TABLE + ' AS SELECT * FROM fixture_backup')
                p = cli()
                assert p.returncode == 1 and p.stderr == FAILURE and state() == before
                sql('DROP VIEW ' + TABLE + '; RENAME TABLE fixture_backup TO ' + TABLE)
                print('PASS candidate: malformed/overlong identifiers, malformed/overflow thresholds, MyISAM and views rejected before writes')
                (common / 'config_read.php').write_text("<?php throw new RuntimeException('config-tripwire');")
                for method in ('GET', 'POST', 'HEAD', 'PUT'):
                    req = urllib.request.Request(base + PATH, method=method,
                                                 data=b'force=1' if method in ('POST', 'PUT') else None)
                    try:
                        response = urllib.request.urlopen(req, timeout=10)
                    except urllib.error.HTTPError as error:
                        response = error
                    assert response.status == 403
                    body = response.read()
                    assert body == (b'' if method == 'HEAD' else b'Stale-session repair is available only through PHP CLI.')
                    assert response.headers['Cache-Control'] == 'no-store'
                assert state() == before
                shutil.copy2(ROOT / 'app/common/includes/config_read.php', common / 'config_read.php')
                (common / 'daloradius.conf.php').unlink()
                p = cli()
                assert p.returncode == 1 and p.stdout == '' and p.stderr == FAILURE
                config(common)
                run('docker', 'stop', DB)
                p = cli()
                assert p.returncode == 1 and p.stdout == '' and p.stderr == FAILURE
                logs = run('docker', 'logs', WEB)
                assert password not in p.stdout + p.stderr + logs.stdout + logs.stderr
                assert user not in p.stdout + p.stderr + logs.stdout + logs.stderr
                assert 'config-tripwire' not in logs.stdout + logs.stderr
                print('PASS candidate: HTTP rejected before configuration; missing config and unavailable DB fail safely without connection details')
        finally:
            for name in (WEB, DB):
                run('docker', 'rm', '-f', name, check=False)
            run('docker', 'network', 'rm', NET, check=False)
            for name in (WEB, DB):
                assert run('docker', 'inspect', name, check=False).returncode != 0
            assert run('docker', 'network', 'inspect', NET, check=False).returncode != 0
    assert not fixture.exists()
    print('PASS cleanup: fixture, containers, DB and internal network removed; no live maintenance or RADIUS call')


if __name__ == '__main__':
    main()
