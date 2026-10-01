#!/usr/bin/env python3
"""UNIT-045: isolated real PHP/PEAR/PDO/MariaDB and native PHPMailer SMTP.

Synthetic node/accounting rows only. SMTP captured in memory on the disposable
internal bridge, never delivered externally. No real configuration or secrets.
"""
from email import policy
from email.parser import BytesParser
import json
import os
from pathlib import Path
import secrets
import shutil
import socketserver
import subprocess
import tempfile
import threading
import urllib.error
import urllib.request

from expired_accounts_retirement import run, wait_for

ROOT = Path(__file__).resolve().parents[1]
BASE = '02dda793228333a45e63dc757f4869b4842cf4e3'
BASELINE = os.environ.get('READONLY_MONITORS_BASELINE') == '1'
TAG = 'u45-' + secrets.token_hex(6)
DB, WEB, NET = (TAG + '-' + suffix for suffix in ('db', 'web', 'net'))
PREFIX = 'contrib/scripts/maintenance/monitor/'
NODE, TRAFFIC = 'node-status-monitor.php', 'user-traffic-monitor.php'
NT, RT = 'u45_node', 'u45_radacct'
FIELDS = ('radacctid', 'acctsessionid', 'username', 'nasipaddress', 'nasportid', 'acctstarttime',
          'acctsessiontime', 'acctinputoctets', 'acctoutputoctets', 'calledstationid', 'callingstationid', 'framedipaddress')
POST = {'CONFIG_NODE_STATUS_MONITOR_HARD_DELAY': 15,
        'CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT': 1000, 'CONFIG_USER_TRAFFIC_MONITOR_SOFTLIMIT': 500}


def sql(query):
    p = run('docker', 'exec', '-i', DB, 'mariadb', '-uroot', '--default-character-set=utf8mb4',
            '-N', '-B', 'fixture', input="SET SESSION sql_mode=''; SET timestamp=1700000000;\n" + query, check=False)
    if p.returncode:
        raise RuntimeError('Fixture SQL failed; details omitted')
    return p.stdout.rstrip('\n')


def state():
    # All these columns contain only synthetic metadata, never credentials.
    return (sql('SELECT * FROM ' + NT + ' ORDER BY id'), sql('SELECT * FROM ' + RT + ' ORDER BY radacctid'))


class SMTP(socketserver.StreamRequestHandler):
    def handle(self):
        self.request.settimeout(20)
        self.wfile.write(b'220 u45.test SMTP fixture\r\n')
        data = []
        while True:
            line = self.rfile.readline(65536)
            if not line:
                return
            verb = line.split(b' ', 1)[0].strip().upper()
            if verb in (b'EHLO', b'HELO'):
                self.wfile.write(b'250-u45.test\r\n250-8BITMIME\r\n250 SIZE 10485760\r\n')
            elif verb == b'MAIL':
                data = []
                self.wfile.write(b'250 sender accepted\r\n')
            elif verb == b'RCPT':
                # Even inside this internal-only fixture, reject non-test routing.
                self.wfile.write(b'250 recipient accepted\r\n' if b'admin@example.test' in line else b'550 invalid fixture recipient\r\n')
            elif verb == b'DATA':
                with self.server.lock:
                    mode = self.server.mode
                if mode in ('reject', 'reject-first'):
                    if mode == 'reject-first':
                        with self.server.lock:
                            self.server.mode = ''
                    self.wfile.write(b'550 fixture delivery rejected\r\n')
                    continue
                self.wfile.write(b'354 send data\r\n')
                while True:
                    chunk = self.rfile.readline(65536)
                    if not chunk or chunk == b'.\r\n':
                        break
                    data.append(chunk[1:] if chunk.startswith(b'..') else chunk)
                message = BytesParser(policy=policy.default).parsebytes(b''.join(data))
                body = message.get_content().replace('\r\n', '\n').rstrip('\n')
                with self.server.lock:
                    self.server.messages.append({'subject': str(message['Subject']), 'body': body,
                                                 'to': str(message['To']), 'type': message.get_content_type()})
                    if mode == 'late-select-failure':
                        self.server.mode = ''
                        sql('RENAME TABLE ' + RT + ' TO u45_backup')
                self.wfile.write(b'250 accepted\r\n')
            elif verb == b'QUIT':
                self.wfile.write(b'221 closing\r\n')
                return
            elif verb in (b'RSET', b'NOOP'):
                self.wfile.write(b'250 ok\r\n')
            else:
                self.wfile.write(b'502 unsupported fixture command\r\n')


class Server(socketserver.ThreadingTCPServer):
    daemon_threads = True
    allow_reuse_address = True

    def __init__(self, address):
        self.lock = threading.Lock()
        self.messages = []
        self.mode = ''
        super().__init__(address, SMTP)

    def reset(self, mode=''):
        with self.lock:
            self.messages.clear()
            self.mode = mode

    def captured(self):
        with self.lock:
            return list(self.messages)


def configure(common, **changes):
    values = {'CONFIG_MAIL_ENABLED': 'yes', 'CONFIG_NODE_STATUS_MONITOR_EMAIL_TO': 'admin@example.test',
              'CONFIG_USER_TRAFFIC_MONITOR_EMAIL_TO': 'admin@example.test',
              'CONFIG_NODE_STATUS_MONITOR_HARD_DELAY': '15', 'CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT': '1000',
              'CONFIG_USER_TRAFFIC_MONITOR_SOFTLIMIT': '500',
              'CONFIG_DB_TBL_DALONODE': NT, 'CONFIG_DB_TBL_RADACCT': RT,
              'FREERADIUS_VERSION': '3', 'CONFIG_MAIL_SMTP_SECURITY': '', 'CONFIG_MAIL_SMTP_USERNAME': '',
              'CONFIG_MAIL_SMTP_PASSWORD': '', 'CONFIG_MAIL_SMTPFROM': 'monitor@example.test',
              'CONFIG_MAIL_SMTP_SENDER_NAME': 'Fixture monitor', 'CONFIG_MAIL_CHARSET': 'UTF-8'}
    values.update(changes)
    text = '<?php\n$configValues=' + 'json_decode(' + json.dumps(json.dumps(values, ensure_ascii=True)) + ',true);\n'
    for field in ('ENGINE', 'HOST', 'PORT', 'NAME', 'USER', 'PASS'):
        text += "$configValues['CONFIG_DB_" + field + "']=getenv('FIXTURE_DB_" + field + "');\n"
    text += "$configValues['CONFIG_MAIL_SMTPADDR']=getenv('FIXTURE_SMTP_HOST');\n"
    text += "$configValues['CONFIG_MAIL_SMTPPORT']=getenv('FIXTURE_SMTP_PORT');\n"
    (common / 'daloradius.conf.php').write_text(text)


def cli(name, post=POST, cwd='/'):
    return run('docker', 'exec', '-w', cwd, '-e', 'FIXTURE_MONITOR=' + name,
               '-e', 'FIXTURE_POST=' + json.dumps(post), WEB, 'php', '-d', 'display_errors=0',
               '-d', 'error_reporting=8191', '/fixtures/runner.php', check=False, timeout=90)


def good(p, stdout=''):
    assert p.returncode == 0 and p.stderr == '' and p.stdout == stdout


def failed(p, name, stdout=''):
    label = 'node status' if name == NODE else 'user traffic'
    assert p.returncode == 1 and p.stdout == stdout and p.stderr == 'Unable to run ' + label + ' monitor.\n'


def seed_nodes():
    sql('TRUNCATE TABLE ' + NT)
    for i, age in enumerate((16, 15, 14, -1), 1):
        sql(f"INSERT INTO {NT}(id,time,mac,memfree,cpu,firmware,firmware_revision) VALUES "
            f"({i},NOW()-INTERVAL {age} SECOND,'fixture-mac-{i}','128',0.25,'fixture-fw','1')")
    sql(f"INSERT INTO {NT}(id,time,mac) VALUES (5,'0000-00-00 00:00:00','fixture-zero')")


def seed_traffic(quoted=False, soft_only=False):
    sql('TRUNCATE TABLE ' + RT)
    rows = [(1, "quote'-%-+-é" if quoted else 'hard-fixture', 1000, 'NULL'),
            (2, 'soft-%-+-é', 501, "'0000-00-00 00:00:00'"),
            (3, 'at-soft-fixture', 500, 'NULL'), (4, 'below-fixture', 499, 'NULL'),
            (5, 'stopped-fixture', 2000, 'NOW()'), (6, 'null-octets-fixture', None, 'NULL'),
            (7, 'hard-fixture' if not quoted else "quote'-%-+-é", 600, 'NULL')]
    if soft_only:
        rows = [rows[1], rows[2], rows[3]]
    for i, name, total, stop in rows:
        name = name.replace("'", "''")
        sql(f"INSERT INTO {RT}(radacctid,acctsessionid,acctuniqueid,username,nasipaddress,acctstarttime,"
            f"acctsessiontime,acctinputoctets,acctoutputoctets,acctstoptime) VALUES "
            f"({i},'session-{i}','unique-{i}','{name}','198.51.100.1',NOW()-INTERVAL 1 HOUR,3600,"
            f"{'NULL' if total is None else total},0,{stop})")


def expected_traffic(hard=True, quoted=False):
    # Actual native database row formatting, same projection/order as PHP.
    predicate = '>= 1000' if hard else '> 500'
    name = "quote''-%-+-é" if quoted else 'hard-fixture'
    where = '' if hard else " AND username NOT IN ('" + name + "')"
    rows = sql('SELECT ' + ','.join(FIELDS) + ' FROM ' + RT +
               " WHERE (acctstoptime='0000-00-00 00:00:00' OR acctstoptime IS NULL)"
               ' AND (CAST(acctinputoctets AS UNSIGNED)+CAST(acctoutputoctets AS UNSIGNED)) ' + predicate + where)
    title = 'hard' if hard else 'soft'
    threshold = 1000 if hard else 500
    head = f'Dear system administrator,\nthe following users seem to have exceeded the traffic monitor {title} limit threshold ({threshold} bytes):\n'
    lines = [', '.join('' if x == 'NULL' else x for x in row.split('\t')) for row in rows.splitlines()]
    return (head + ', '.join(FIELDS) + '\n' + '\n'.join(lines)).rstrip('\n')


def main():
    scratch = Path.home() / '.hermes/cache/scratch'
    scratch.mkdir(parents=True, exist_ok=True)
    server = None
    with tempfile.TemporaryDirectory(prefix='readonly-monitors-', dir=scratch) as directory:
        fixture = Path(directory)
        common = fixture / 'app/common/includes'
        common.mkdir(parents=True)
        for name in ('config_read.php', 'version.php', 'pdo_connection.php', 'mail.php',
                     'db_open.php', 'db_close.php', 'db_error_handler.php', 'db_table_conventions.php'):
            shutil.copy2(ROOT / 'app/common/includes' / name, common / name)
        library = fixture / 'app/common/library/phpmailer'
        library.mkdir(parents=True)
        for name in ('Exception.php', 'SMTP.php', 'PHPMailer.php'):
            shutil.copy2(ROOT / 'app/common/library/phpmailer' / name, library / name)
        for name in (NODE, TRAFFIC):
            script = fixture / (PREFIX + name)
            script.parent.mkdir(parents=True, exist_ok=True)
            if BASELINE:
                script.write_text(run('git', 'show', BASE + ':' + PREFIX + name).stdout)
            else:
                shutil.copy2(ROOT / (PREFIX + name), script)
        (fixture / 'runner.php').write_text("<?php $_POST=json_decode(getenv('FIXTURE_POST'),true);require __DIR__.'/" + PREFIX + "'.getenv('FIXTURE_MONITOR');")
        (fixture / 'health.php').write_text('<?php echo "ready";')
        configure(common)
        user, password = 'fixture_' + secrets.token_hex(5), secrets.token_hex(32)
        try:
            run('docker', 'network', 'create', '--internal', NET)
            info = json.loads(run('docker', 'network', 'inspect', NET).stdout)[0]
            gateway = info['IPAM']['Config'][0]['Gateway']
            server = Server((gateway, 0))
            worker = threading.Thread(target=server.serve_forever, daemon=True)
            worker.start()
            run('docker', 'run', '-d', '--name', DB, '--network', NET, '--tmpfs', '/var/lib/mysql',
                '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1', '-e', 'MARIADB_DATABASE=fixture', 'mariadb:11.8')
            wait_for(lambda: sql('SELECT 1') == '1', 'MariaDB')
            s = (ROOT / 'contrib/db/mariadb-daloradius.sql').read_text()
            begin = s.index('CREATE TABLE `node` (')
            sql(s[begin:s.index(';', begin) + 1].replace('`node`', '`' + NT + '`', 1))
            s = (ROOT / 'contrib/db/fr3-mariadb-freeradius.sql').read_text()
            begin = s.index('CREATE TABLE IF NOT EXISTS radacct (')
            sql(s[begin:s.index(';', begin) + 1].replace('radacct (', RT + ' (', 1))
            sql(f"CREATE USER '{user}'@'%' IDENTIFIED BY '{password}'; GRANT SELECT ON fixture.* TO '{user}'@'%';"
                "SET GLOBAL init_connect='SET timestamp=1700000000';")
            environment = {'FIXTURE_DB_ENGINE': 'mysqli', 'FIXTURE_DB_HOST': DB, 'FIXTURE_DB_PORT': '3306',
                           'FIXTURE_DB_NAME': 'fixture', 'FIXTURE_DB_USER': user, 'FIXTURE_DB_PASS': password,
                           'FIXTURE_SMTP_HOST': gateway, 'FIXTURE_SMTP_PORT': str(server.server_address[1])}
            args = ['docker', 'run', '-d', '--name', WEB, '--network', NET, '-v', f'{fixture}:/fixtures:ro']
            for key in environment:
                args += ['-e', key]
            args += ['--entrypoint', 'php', 'lirantal/daloradius', '-d', 'display_errors=0', '-d', 'opcache.enable=0',
                     '-d', 'opcache.enable_cli=0', '-S', '0.0.0.0:8080', '-t', '/fixtures']
            p = subprocess.run(args, env=dict(os.environ, **environment), capture_output=True, text=True, timeout=60)
            assert p.returncode == 0
            environment.clear()
            ip = run('docker', 'inspect', '-f', '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}', WEB).stdout.strip()
            base = 'http://' + ip + ':8080/'
            wait_for(lambda: urllib.request.urlopen(base + 'health.php', timeout=5).read() == b'ready', 'PHP HTTP')
            if BASELINE:
                for name in (NODE, TRAFFIC):
                    p = cli(name)
                    assert p.returncode == 0 and p.stdout == 'SMTP Server not configured' and 'Failed to open stream' in p.stderr and not server.captured()
                shim = fixture / 'contrib/app/common/includes/config_read.php'
                shim.parent.mkdir(parents=True)
                shim.write_text("<?php require '/fixtures/app/common/includes/config_read.php';")
                print('PASS baseline characterization: broken configuration include; fixture-only compatibility loader enables unmodified entry points')
            seed_nodes()
            initial = state()
            server.reset()
            good(cli(NODE, cwd='/fixtures/' + PREFIX), 'SUCCESS: Email sent successfully')
            messages = server.captured()
            assert len(messages) == 1 and messages[0]['subject'] == 'daloRADIUS node status monitor'
            assert messages[0]['body'] == ('Dear system administrator,\nthe following nodes seem to be offline:\n\n'
                                         '`mac`, `memfree`, `cpu`, `wan_ip`, `wan_gateway`, `lan_mac`, `firmware`, `firmware_revision`\n'
                                         'fixture-mac-11280.25fixture-fw1')
            assert messages[0]['type'] == 'text/html' and 'admin@example.test' in messages[0]['to']
            assert state() == initial
            print('PASS A/B native node SQL+SMTP: strict delay boundary, NULL fields, future/zero date exclusion, unchanged exact mail body and SELECT-only table invariance')
            if BASELINE:
                server.reset()
                response = urllib.request.urlopen(base + PREFIX + NODE, timeout=20)
                assert response.status == 200 and response.read() == b'SUCCESS: Email sent successfully'
                assert len(server.captured()) == 1 and state() == initial
                print('PASS baseline characterization: unauthenticated fixture HTTP can trigger an actual node email')
            sql('TRUNCATE TABLE ' + NT)
            server.reset()
            good(cli(NODE))
            assert not server.captured()
            for key, changes, output in [('disabled', {'CONFIG_MAIL_ENABLED': 'no'}, 'SMTP Server not configured'),
                                         ('recipient', {'CONFIG_NODE_STATUS_MONITOR_EMAIL_TO': 'bad', 'CONFIG_USER_TRAFFIC_MONITOR_EMAIL_TO': 'bad'}, 'Email not valid')]:
                configure(common, **changes)
                for name in (NODE, TRAFFIC):
                    good(cli(name), output)
                    assert not server.captured()
            configure(common)
            seed_traffic()
            initial = state()
            server.reset()
            output = 'HARD LIMIT TRAFFIC MONITOR => SUCCESS: Email sent successfullySOFT LIMIT TRAFFIC MONITOR => SUCCESS: Email sent successfully'
            good(cli(TRAFFIC), output)
            messages = server.captured()
            assert len(messages) == 2 and messages[0]['body'] == expected_traffic()
            assert messages[1]['body'] == (messages[0]['body'] if BASELINE else expected_traffic(False))
            assert all(m['subject'] == 'daloRADIUS user traffic monitor' for m in messages)
            assert state() == initial
            print('PASS A/B hard SQL+native SMTP: hard >= / soft >, stopped/NULL counters excluded, same-name soft session excluded, no SQL writes')
            print('PASS baseline wrong-soft-body characterization' if BASELINE else 'PASS candidate corrected soft body verified over native SMTP')
            seed_traffic()
            sql('DELETE FROM ' + RT + ' WHERE radacctid=2')
            initial = state()
            server.reset()
            good(cli(TRAFFIC), 'HARD LIMIT TRAFFIC MONITOR => SUCCESS: Email sent successfully')
            assert len(server.captured()) == 1 and server.captured()[0]['body'] == expected_traffic() and state() == initial
            print('PASS A/B soft boundary: exact threshold and a same-name soft session do not produce a second message')
            seed_traffic(soft_only=True)
            sql(f"INSERT INTO {RT}(radacctid,acctuniqueid,username,acctinputoctets,acctoutputoctets) VALUES (8,'unique-8','just-below-hard',999,0)")
            server.reset()
            good(cli(TRAFFIC))
            assert not server.captured()
            sql('TRUNCATE TABLE ' + RT)
            good(cli(TRAFFIC))
            assert not server.captured()
            print('PASS A/B: empty traffic and soft-only behavior preserved (no soft query/alert without hard matches)')
            if BASELINE:
                seed_traffic(quoted=True)
                initial = state()
                server.reset()
                p = cli(TRAFFIC)
                assert p.returncode != 0 and 'numRows' in p.stderr
                assert len(server.captured()) == 1 and server.captured()[0]['body'] == expected_traffic(quoted=True)
                assert state() == initial
                print('PASS baseline characterization: quoted hard username breaks interpolated NOT IN after actual hard email')
            else:
                seed_traffic(quoted=True)
                initial = state()
                server.reset()
                good(cli(TRAFFIC), output)
                messages = server.captured()
                assert len(messages) == 2 and messages[0]['body'] == expected_traffic(quoted=True)
                assert messages[1]['body'] == expected_traffic(False, quoted=True) and state() == initial
                print('PASS candidate: bound quoted/Unicode/literal-percent/plus NOT IN and correct soft mail; exact row invariance')
                server.reset()
                # Saved configuration works when cron supplies no POST overrides.
                configure(common, CONFIG_NODE_STATUS_MONITOR_HARD_DELAY='60',
                          CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT='2000', CONFIG_USER_TRAFFIC_MONITOR_SOFTLIMIT='1000')
                good(cli(TRAFFIC, post={}))
                assert not server.captured()
                seed_nodes()
                good(cli(NODE, post={}))
                assert not server.captured()
                configure(common, CONFIG_NODE_STATUS_MONITOR_HARD_DELAY='1',
                          CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT='1000', CONFIG_USER_TRAFFIC_MONITOR_SOFTLIMIT='500')
                good(cli(NODE, post={}), 'SUCCESS: Email sent successfully')
                assert len(server.captured()) == 1
                server.reset()
                good(cli(TRAFFIC, post={}), output)
                assert len(server.captured()) == 2
                configure(common)
                print('PASS candidate cron-style CLI: saved nondefault limits/delay honored, default POST-free invocation and alternate cwd exercised')
                for name, table_key in ((NODE, 'CONFIG_DB_TBL_DALONODE'), (TRAFFIC, 'CONFIG_DB_TBL_RADACCT')):
                    for table in ('bad;DROP_TABLE', '', 'x' * 65):
                        configure(common, **{table_key: table})
                        server.reset()
                        failed(cli(name), name)
                        assert not server.captured()
                configure(common)
                for name, key in ((NODE, 'CONFIG_NODE_STATUS_MONITOR_HARD_DELAY'), (TRAFFIC, 'CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT'),
                                  (TRAFFIC, 'CONFIG_USER_TRAFFIC_MONITOR_SOFTLIMIT')):
                    server.reset()
                    failed(cli(name, post={key: []}), name)
                    assert not server.captured()
                initial = state()
                sql(f"REVOKE SELECT ON fixture.* FROM '{user}'@'%'; GRANT SELECT ON fixture.{RT} TO '{user}'@'%'")
                failed(cli(NODE), NODE)
                assert not server.captured()
                sql(f"REVOKE SELECT ON fixture.{RT} FROM '{user}'@'%'; GRANT SELECT ON fixture.{NT} TO '{user}'@'%'")
                failed(cli(TRAFFIC), TRAFFIC)
                assert not server.captured()
                sql(f"REVOKE SELECT ON fixture.{NT} FROM '{user}'@'%'; GRANT SELECT ON fixture.* TO '{user}'@'%'")
                assert state() == initial
                print('PASS candidate: malformed threshold/table and denied SELECT rejected with generic failure, no SMTP and unchanged rows')
                server.reset('reject')
                rejection = cli(NODE)
                assert rejection.returncode == 1 and rejection.stderr == '' and rejection.stdout == 'FAILURE: Email delivery failed'
                assert not server.captured() and state() == initial
                server.reset('reject-first')
                partial = cli(TRAFFIC)
                assert partial.returncode == 1 and partial.stderr == '' and partial.stdout == (
                    'HARD LIMIT TRAFFIC MONITOR => FAILURE: Email delivery failed'
                    'SOFT LIMIT TRAFFIC MONITOR => SUCCESS: Email sent successfully')
                assert len(server.captured()) == 1 and server.captured()[0]['body'] == expected_traffic(False, quoted=True)
                assert state() == initial
                server.reset('late-select-failure')
                failed(cli(TRAFFIC), TRAFFIC, 'HARD LIMIT TRAFFIC MONITOR => SUCCESS: Email sent successfully')
                assert len(server.captured()) == 1
                sql('RENAME TABLE u45_backup TO ' + RT)
                assert state() == initial
                print('PASS candidate: SMTP rejection redacted; late soft SELECT failure cannot undo prior hard delivery, tables unchanged')
                server.reset()
                (common / 'config_read.php').write_text("<?php throw new RuntimeException('config-tripwire');")
                for name in (NODE, TRAFFIC):
                    for method in ('GET', 'POST', 'HEAD', 'PUT'):
                        req = urllib.request.Request(base + PREFIX + name, method=method,
                                                     data=b'CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT=1' if method in ('POST', 'PUT') else None)
                        try:
                            response = urllib.request.urlopen(req, timeout=10)
                        except urllib.error.HTTPError as error:
                            response = error
                        assert response.status == 403 and response.read() == b''
                    failed(cli(name), name)
                assert not server.captured() and state() == initial
                shutil.copy2(ROOT / 'app/common/includes/config_read.php', common / 'config_read.php')
                conf = common / 'daloradius.conf.php'
                saved_conf = conf.read_bytes()
                conf.unlink()
                for name in (NODE, TRAFFIC):
                    failed(cli(name), name)
                conf.write_bytes(saved_conf)
                assert not server.captured() and state() == initial
                run('docker', 'stop', DB)
                for changes, message in (({'CONFIG_MAIL_ENABLED': 'no'}, 'SMTP Server not configured'),
                                         ({'CONFIG_NODE_STATUS_MONITOR_EMAIL_TO': 'bad', 'CONFIG_USER_TRAFFIC_MONITOR_EMAIL_TO': 'bad'}, 'Email not valid')):
                    configure(common, **changes)
                    for name in (NODE, TRAFFIC):
                        good(cli(name), message)
                configure(common)
                for name in (NODE, TRAFFIC):
                    failed(cli(name), name)
                logs = run('docker', 'logs', WEB)
                assert all(value not in logs.stdout + logs.stderr for value in (password, user, 'config-tripwire'))
                print('PASS candidate: HTTP 403 before config/SQL/SMTP; bootstrap and unavailable DB failures redacted')
                cron = (ROOT / 'app/operators/config-crontab.php').read_text()
                for name in (NODE, TRAFFIC):
                    assert "'file' => 'maintenance/monitor/" + name + "'" in cron
                    assert (ROOT / ('contrib/scripts/maintenance/monitor/' + name)).is_file()
                print('PASS static producer: both generated cron paths point to real tracked CLI scripts; no live cron changed')
        finally:
            if server:
                server.shutdown()
                server.server_close()
                worker.join(timeout=5)
                assert not worker.is_alive()
                server.messages.clear()
            for name in (WEB, DB):
                run('docker', 'rm', '-f', name, check=False)
            run('docker', 'network', 'rm', NET, check=False)
            for name in (WEB, DB):
                assert run('docker', 'inspect', name, check=False).returncode != 0
            assert run('docker', 'network', 'inspect', NET, check=False).returncode != 0
    assert not fixture.exists()
    print('PASS cleanup: isolated tmpfs DB, PHP fixture, internal network and SMTP listener removed; no live database, cron or external delivery')


if __name__ == '__main__':
    main()
