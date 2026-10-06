#!/usr/bin/env python3
# UNIT-028 differential real HTTP/PHP/MariaDB operator authentication.
import json
import os
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap
import secrets
import shutil
import subprocess
import tempfile
from concurrent.futures import ThreadPoolExecutor
from html.parser import HTMLParser
import urllib.error
import urllib.parse
import urllib.request

import user_actions_http as harness

ROOT = Path(__file__).resolve().parents[1]
BASELINE = os.environ.get('OPERATOR_LOGIN_BASELINE') == '1'
IMAGE = os.environ.get('OPERATOR_LOGIN_WEB_IMAGE', 'lirantal/daloradius')
BASE_COMMIT = 'ec4662c8961cc3227cd93dfb34c44a234897359f'
DB, WEB, NETWORK = harness.DB, harness.WEB, harness.NETWORK
run_harness, wait_for = harness.run, harness.wait_for

class NoRedirect(urllib.request.HTTPRedirectHandler):
    def http_error_301(self, req, fp, code, msg, headers): return fp
    def http_error_302(self, req, fp, code, msg, headers): return fp
    def http_error_303(self, req, fp, code, msg, headers): return fp

class FormParser(HTMLParser):
    def __init__(self):
        super().__init__(); self.csrf = None
    def handle_starttag(self, tag, attrs):
        values = dict(attrs)
        if tag == 'input' and values.get('name') == 'csrf_token':
            self.csrf = values.get('value', '')

def run(*args, input=None, check=True, timeout=300):
    result = subprocess.run(args, input=input, text=True, capture_output=True, timeout=timeout)
    if check and result.returncode:
        raise RuntimeError('isolated fixture command failed: ' + result.stderr.strip())
    return result.stdout.strip()

def sql(query):
    try:
        return run('docker', 'exec', '-i', DB, 'mariadb', '-uroot', '-N', '-B', 'radius', input=query)
    except RuntimeError:
        raise RuntimeError('isolated fixture SQL operation failed') from None

def quote(value):
    return "'" + str(value).replace("'", "''") + "'"

def hash_password(web, password):
    return run('docker', 'exec', '-i', web, 'php', '-r',
               'echo password_hash(stream_get_contents(STDIN), PASSWORD_DEFAULT);', input=password)

def verify_password(web, username, password):
    code = '''
$p=new PDO("mysql:host=" . $argv[1] . ";dbname=radius;charset=utf8mb4", "root", "");
$s=$p->prepare("SELECT password FROM operators WHERE username=?");
$s->execute(array($argv[2])); $h=$s->fetchColumn();
echo password_verify(stream_get_contents(STDIN), $h) ? "yes" : "no";
'''
    return run('docker', 'exec', '-i', web, 'php', '-r', code, DB, username, input=password)

def add_operator(username, password, source='local', external_id=None,
                 totp_enabled=0, totp_secret=None, lastlogin='2000-01-01 00:00:00'):
    values = [quote(username), 'NULL' if password is None else quote(password), quote(source),
              'NULL' if external_id is None else quote(external_id), quote('Fixture'), quote('Operator')]
    values += [quote('')] * 10
    values += [quote(lastlogin), str(int(totp_enabled)),
               'NULL' if totp_secret is None else quote(totp_secret)]
    columns = ('username,password,auth_source,external_id,firstname,lastname,title,department,'
               'company,phone1,phone2,email1,email2,messenger1,messenger2,notes,lastlogin,'
               'totp_enabled,totp_secret')
    sql('INSERT INTO operators (' + columns + ') VALUES (' + ','.join(values) + ')')

def state():
    return {
        'operators': sql("SELECT username,auth_source,COALESCE(external_id,'<NULL>'),totp_enabled,lastlogin "
                         "FROM operators WHERE id<>1 ORDER BY username"),
        'acls': sql("SELECT o.username,a.file,a.access FROM operators o JOIN operators_acl a "
                    "ON a.operator_id=o.id WHERE o.id<>1 ORDER BY o.username,a.file"),
    }

class Client:
    def __init__(self, base):
        self.base, self.sid = base, None
        self.opener = urllib.request.build_opener(NoRedirect())
    def request(self, path, fields=None):
        url = path if path.startswith('http://') else self.base + path.lstrip('/')
        data = urllib.parse.urlencode(fields, doseq=True).encode() if fields is not None else None
        request = urllib.request.Request(url, data=data)
        if self.sid: request.add_header('Cookie', 'daloradius_operator_sid=' + self.sid)
        try: response = self.opener.open(request, timeout=25)
        except urllib.error.HTTPError as error: response = error
        cookie = response.headers.get('Set-Cookie', '')
        marker = 'daloradius_operator_sid='
        if marker in cookie:
            value = cookie.split(marker, 1)[1].split(';', 1)[0]
            if value: self.sid = value
        return response.status, response.geturl(), response.headers, response.read().decode('utf-8', 'replace')
    def csrf(self):
        status, _, _, body = self.request('login.php')
        assert status == 200
        parser = FormParser(); parser.feed(body); assert parser.csrf
        return parser.csrf

def login(client, username, password, source, location):
    return client.request('dologin.php', [
        ('operator_user', username), ('operator_pass', password),
        ('operator_auth_source', source), ('location', location),
        ('csrf_token', client.csrf()),
    ])

def probe(client):
    status, _, _, body = client.request('session_probe.php')
    assert status == 200
    return json.loads(body)

def main():
    scratch = Path.home() / '.hermes/cache/scratch'; scratch.mkdir(parents=True, exist_ok=True)
    names = {k: 'u28-' + k + '-' + secrets.token_hex(6)
             for k in ('local', 'mfa', 'legacy', 'mismatch', 'link')}
    passwords = {k: secrets.token_urlsafe(24) for k in ('local', 'mfa', 'legacy')}
    external = {k: 'directory-' + secrets.token_hex(12) for k in ('mismatch', 'link')}
    location = 'fixture-location-' + secrets.token_hex(6)
    old_lastlogin = '2000-01-01 00:00:00'
    with tempfile.TemporaryDirectory(prefix='dalo-op-login-', dir=scratch) as directory:
        fixture = Path(directory); shutil.copytree(ROOT / 'app', fixture / 'app', symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
        if BASELINE:
            restore_pear_bootstrap((fixture / 'app').parent, BASE_COMMIT)
            old = run_harness('git', 'show', BASE_COMMIT + ':app/operators/dologin.php')
            (fixture / 'app/operators/dologin.php').write_text(old + '\n')
        (fixture / 'app/operators/session_probe.php').write_text('''<?php
session_name('daloradius_operator_sid'); session_start();
echo json_encode(array(
  'logged_in'=>!empty($_SESSION['daloradius_logged_in']),
  'pending'=>!empty($_SESSION['operator_2fa_pending']),
  'location'=>isset($_SESSION['location_name'])?(string)$_SESSION['location_name']:'',
  'source'=>isset($_SESSION['operator_auth_source'])?(string)$_SESSION['operator_auth_source']:'',
  'pending_source'=>isset($_SESSION['operator_2fa_auth_source'])?(string)$_SESSION['operator_2fa_auth_source']:''
));
''')
        (fixture / 'app/operators/link_helper.php').write_text('''<?php
define('DALORADIUS_OPERATOR_LOGIN_TEST_ONLY', true);
chdir('/fixtures/app/operators'); require '/fixtures/app/operators/dologin.php';
include '/fixtures/app/common/includes/db_open.php';
try {
  $s=$dbSocket->prepare('SELECT id FROM operators WHERE username=?');
  $r=$dbSocket->execute($s,array($argv[2])); $dbSocket->freePrepared($s);
  if (DB::isError($r) || $r->numRows() !== 1) { echo 'no'; }
  else { $row=$r->fetchRow(DB_FETCHMODE_ASSOC); $r->free();
    echo dalo_operator_ldap_link_external_id($dbSocket,'operators',$row['id'],$argv[3]) ? 'ok' : 'no'; }
} catch (Throwable $e) { echo 'no'; }
include '/fixtures/app/common/includes/db_close.php';
''')
        if not BASELINE:
            (fixture / 'app/operators/link_helper.php').write_text("""<?php
define('DALORADIUS_OPERATOR_LOGIN_TEST_ONLY', true);
chdir('/fixtures/app/operators'); require '/fixtures/app/operators/dologin.php';
require_once '/fixtures/app/common/includes/pdo_connection.php';
try {
  $pdo=dalo_pdo_connect($configValues, 'default');
  $s=$pdo->prepare('SELECT id FROM `operators` WHERE username=?');
  $s->execute(array($argv[2])); $row=$s->fetch(PDO::FETCH_ASSOC);
  if (!$row) { echo 'no'; }
  else { $table=dalo_operator_auth_table($configValues);
    echo dalo_operator_ldap_link_external_id($pdo,$table,$row['id'],$argv[3]) ? 'ok' : 'no'; }
} catch (Throwable $e) { echo 'no'; }
""")
        if not BASELINE:
            (fixture / 'app/operators/rehash_race.php').write_text('''<?php
define('DALORADIUS_OPERATOR_LOGIN_TEST_ONLY', true);
chdir('/fixtures/app/operators'); require '/fixtures/app/operators/dologin.php';
require_once '/fixtures/app/common/includes/pdo_connection.php';
try {
  $pdo=dalo_pdo_connect($configValues, 'default');
  $table=dalo_operator_auth_table($configValues);
  $s=$pdo->prepare("SELECT id,username,password,auth_source FROM $table WHERE username=?");
  $s->execute(array($argv[1])); $row=$s->fetch(PDO::FETCH_ASSOC);
  if (!$row || $row['auth_source'] !== 'local') { echo 'no'; exit; }
  $replacement=password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
  $candidate=password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
  $other=dalo_pdo_connect($configValues, 'default');
  $u=$other->prepare("UPDATE $table SET password=? WHERE id=?");
  $u->execute(array($replacement, $row['id']));
  $changed=dalo_operator_auth_rehash($pdo, $table, $row, $row['username'], $candidate);
  $s->execute(array($argv[1])); $current=$s->fetch(PDO::FETCH_ASSOC);
  echo (!$changed && $current && hash_equals($replacement, $current['password'])) ? 'ok' : 'no';
} catch (Throwable $e) { echo 'no'; }
''')
        try:
            run('docker', 'network', 'create', '--internal', NETWORK)
            run('docker', 'run', '-d', '--name', DB, '--network', NETWORK, '--tmpfs', '/var/lib/mysql',
                '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1', '-e', 'MARIADB_DATABASE=radius', 'mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'), 'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql', 'mariadb-daloradius.sql'):
                sql((ROOT / 'contrib/db' / name).read_text())
            config = (ROOT / 'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>', '')
            overrides = {
                'CONFIG_DB_ENGINE': 'mysqli', 'CONFIG_DB_HOST': DB, 'CONFIG_DB_PORT': '3306', 'CONFIG_DB_USER': 'root', 'CONFIG_DB_PASS': '', 'CONFIG_DB_NAME': 'radius',
                'CONFIG_LOG_PAGES': 'no', 'CONFIG_LOG_QUERIES': 'no', 'CONFIG_LOG_ACTIONS': 'no',
                'CONFIG_DEBUG_SQL': 'no', 'CONFIG_DEBUG_SQL_ONPAGE': 'no',
                'CONFIG_OPERATOR_AUTH_LOCAL_ENABLED': 'true', 'CONFIG_OPERATOR_AUTH_LDAP_ENABLED': 'true',
                'CONFIG_OPERATOR_AUTH_DEFAULT': 'local', 'CONFIG_OPERATOR_AUTH_LDAP_URI': "array('ldap://127.0.0.1:389')",
                'CONFIG_OPERATOR_AUTH_LDAP_SECURITY': 'plain', 'CONFIG_OPERATOR_AUTH_LDAP_BASE_DN': "'dc=fixture'",
                'CONFIG_OPERATOR_AUTH_LDAP_USER_BASE_DN': "'dc=fixture'",
                'CONFIG_OPERATOR_AUTH_LDAP_EXTERNAL_ID_ATTRIBUTE': "'uid'",
                'CONFIG_LOCATIONS': "array(" + quote(location) + "=>array('Engine'=>'mysqli','Username'=>'root',"
                                  "'Password'=>'','Database'=>'radius','Hostname'=>" + quote(DB) + ",'Port'=>'3306'))",
            }
            for key, value in overrides.items():
                php_value = value if value in ('true', 'false') or value.startswith('array(') or value.startswith("'") else quote(value)
                config += "\n$configValues[%s] = %s;\n" % (quote(key), php_value)
            (fixture / 'app/common/includes/daloradius.conf.php').write_text(config)
            run('docker', 'run', '-d', '--name', WEB, '--network', NETWORK, '-v', f'{fixture}:/fixtures',
                '-w', '/fixtures/app/operators', '--entrypoint', 'php', IMAGE, '-d', 'display_errors=0',
                '-S', '0.0.0.0:8080', '-t', '.')
            address = run('docker', 'inspect', '-f', '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}', WEB)
            base = 'http://' + address + ':8080/'
            wait_for(lambda: urllib.request.urlopen(base + 'login.php', timeout=10), 'PHP HTTP')
            add_operator(names['local'], hash_password(WEB, passwords['local']))
            add_operator(names['mfa'], hash_password(WEB, passwords['mfa']), totp_enabled=1,
                         totp_secret=secrets.token_urlsafe(24))
            add_operator(names['legacy'], passwords['legacy'])
            add_operator(names['mismatch'], None, source='ldap', external_id=external['mismatch'])
            add_operator(names['link'], None, source='ldap')

            client = Client(base)
            status, _, headers, _ = login(client, names['local'], passwords['local'], 'local', location)
            assert status == 302 and headers.get('Location', '').endswith('index.php')
            assert probe(client) == {'logged_in': True, 'pending': False, 'location': location,
                                     'source': 'local', 'pending_source': ''}
            assert sql("SELECT lastlogin<>%s FROM operators WHERE username=%s" %
                       (quote(old_lastlogin), quote(names['local']))) == '1'
            print('PASS: local success records location, provider, session auth, and lastlogin')

            client = Client(base)
            status, _, headers, _ = login(client, names['local'], secrets.token_urlsafe(24), 'local', location)
            assert status == 302 and headers.get('Location', '').endswith('login.php')
            assert probe(client)['logged_in'] is False
            print('PASS: invalid local password stays unauthenticated')

            client = Client(base)
            status, _, headers, _ = login(client, names['mfa'], passwords['mfa'], 'local', location)
            assert status == 302 and headers.get('Location', '').endswith('login-otp.php')
            assert probe(client) == {'logged_in': False, 'pending': True, 'location': location,
                                     'source': '', 'pending_source': 'local'}
            assert sql("SELECT lastlogin=%s FROM operators WHERE username=%s" %
                       (quote(old_lastlogin), quote(names['mfa']))) == '1'
            print('PASS: local MFA redirects to OTP without logged-in state or lastlogin')

            client = Client(base)
            status, _, headers, _ = login(client, names['legacy'], passwords['legacy'], 'local', location)
            assert status == 302 and headers.get('Location', '').endswith('index.php')
            assert sql("SELECT password LIKE '$2%%' FROM operators WHERE username=%s" % quote(names['legacy'])) == '1'
            assert verify_password(WEB, names['legacy'], passwords['legacy']) == 'yes'
            print('PASS: legacy plaintext login succeeds and rehashes to a valid password hash')

            client = Client(base)
            status, _, headers, _ = login(client, names['mismatch'], secrets.token_urlsafe(24), 'local', location)
            assert status == 302 and headers.get('Location', '').endswith('login.php')
            assert probe(client)['logged_in'] is False
            assert sql("SELECT auth_source,external_id IS NOT NULL FROM operators WHERE username=%s" %
                       quote(names['mismatch'])) == 'ldap\t1'
            print('PASS: explicit local/LDAP source mismatch fails closed without linking')

            before = state()
            if not BASELINE:
                cases = (
                    [('operator_user[]', names['local']), ('operator_pass', passwords['local'])],
                    [('operator_user', names['local']), ('operator_pass[]', passwords['local'])],
                    [('operator_user', names['local']), ('operator_pass', passwords['local']), ('operator_auth_source[]', 'local')],
                    [('operator_user', names['local']), ('operator_pass', passwords['local']), ('csrf_token[]', 'invalid')],
                )
                for malformed in cases:
                    client = Client(base); csrf = client.csrf()
                    status, _, headers, _ = client.request('dologin.php', [('location', location), ('csrf_token', csrf)] + malformed)
                    assert status == 302 and headers.get('Location', '').endswith('login.php')
                    assert probe(client)['logged_in'] is False and state() == before
                print('PASS: malformed scalar/array inputs fail closed without a database mutation')
                assert run('docker', 'exec', WEB, 'php', '/fixtures/app/operators/rehash_race.php',
                           names['legacy']) == 'ok'
                print('PASS: password reset between read and rehash cannot be overwritten')
                sql("UPDATE operators SET lastlogin=%s WHERE username=%s" %
                    (quote(old_lastlogin), quote(names['local'])))
                sql("CREATE TRIGGER reject_login_timestamp BEFORE UPDATE ON operators "
                    "FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture lastlogin error'")
                try:
                    client = Client(base)
                    status, _, headers, body = login(client, names['local'], passwords['local'], 'local', location)
                    assert status == 302 and headers.get('Location', '').endswith('login.php')
                    assert probe(client) == {'logged_in': False, 'pending': False, 'location': '',
                                              'source': '', 'pending_source': ''}
                    assert sql("SELECT lastlogin=%s FROM operators WHERE username=%s" %
                               (quote(old_lastlogin), quote(names['local']))) == '1'
                    assert 'SQLSTATE' not in body and 'fixture lastlogin error' not in body
                finally:
                    sql('DROP TRIGGER reject_login_timestamp')
                print('PASS: failed lastlogin update never opens a session or leaks SQL details')

            same_id = 'directory-' + secrets.token_hex(12)
            command = ['docker', 'exec', WEB, 'php', '/fixtures/app/operators/link_helper.php', DB, names['link'], same_id]
            with ThreadPoolExecutor(max_workers=2) as pool:
                outcomes = sorted(f.result() for f in [pool.submit(run, *command) for _ in range(2)])
            assert outcomes == ['ok', 'ok']
            assert sql("SELECT COUNT(*) FROM operators WHERE username=%s AND external_id=%s" %
                       (quote(names['link']), quote(same_id))) == '1'
            sql("UPDATE operators SET external_id=NULL WHERE username=%s" % quote(names['link']))
            other_ids = ('directory-' + secrets.token_hex(12), 'directory-' + secrets.token_hex(12))
            commands = [['docker', 'exec', WEB, 'php', '/fixtures/app/operators/link_helper.php', DB, names['link'], value]
                        for value in other_ids]
            with ThreadPoolExecutor(max_workers=2) as pool:
                outcomes = sorted(f.result() for f in [pool.submit(run, *command) for command in commands])
            assert outcomes == ['no', 'ok']
            assert sql("SELECT COUNT(*) FROM operators WHERE username=%s AND external_id IN (%s,%s)" %
                       (quote(names['link']), quote(other_ids[0]), quote(other_ids[1]))) == '1'
            print('PASS: concurrent same-identity links converge; different identities fail closed')

            logs = subprocess.run(['docker', 'logs', WEB], capture_output=True, text=True, timeout=30, check=True)
            combined = logs.stdout + logs.stderr
            assert 'PHP Fatal error' not in combined and 'PHP Warning' not in combined
            print('PASS: isolated PHP logs contain no fatal errors or warnings')
        finally:
            for container in (WEB, DB): run('docker', 'rm', '-f', '-v', container, check=False)
            run('docker', 'network', 'rm', NETWORK, check=False)
            assert not run('docker', 'ps', '-aq', '--filter', 'name=' + harness.PREFIX)
            print('CLEANUP: disposable containers, network, database, cookies, and fixture data removed')

if __name__ == '__main__':
    main()
