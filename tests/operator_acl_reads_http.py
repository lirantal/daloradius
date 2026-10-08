#!/usr/bin/env python3
"""R01a: native differential ACL HTTP/PHP/MariaDB reads, no live config/data."""
import collections
import json
import os
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap
import re
import secrets
import shutil
import subprocess
import tempfile
import urllib.error
import urllib.parse
import urllib.request
from html.parser import HTMLParser

import user_actions_http as harness

ROOT = Path(__file__).resolve().parents[1]
BASE = 'eaecd81773c95603824825e399b83cbe833e233f'
DB, WEB, NETWORK = harness.DB, harness.WEB, harness.NETWORK
run, wait_for = harness.run, harness.wait_for


def sql(query):
    result = subprocess.run(['docker', 'exec', '-i', DB, 'mariadb', '-uroot', '-N', '-B', 'radius'],
                            input=query, text=True, capture_output=True, timeout=180)
    if result.returncode:
        # SQL setup may include disposable account credentials: never echo driver input/errors.
        raise RuntimeError('Isolated SQL operation failed (details intentionally suppressed)')
    return result.stdout.strip()


IMAGE = os.environ.get('ACL_READ_WEB_IMAGE', 'lirantal/daloradius')


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


class ACLRows(HTMLParser):
    def __init__(self):
        super().__init__()
        self.rows, self.current, self.cell = [], None, None
        self.select, self.options = None, []

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == 'tr':
            self.current = []
        elif tag == 'td':
            self.cell = ''
        elif tag == 'select':
            self.select = attrs.get('name')
            self.options = []
        elif tag == 'option':
            self.options.append((attrs.get('value'), 'selected' in attrs))

    def handle_data(self, data):
        if self.cell is not None:
            self.cell += data

    def handle_endtag(self, tag):
        if tag == 'td' and self.current is not None:
            self.current.append(self.cell)
            self.cell = None
        elif tag == 'select' and self.current is not None:
            self.current.append((self.select, tuple(self.options)))
        elif tag == 'tr' and self.current:
            self.rows.append(tuple(self.current))
            self.current = None


def main():
    scratch = Path.home() / '.hermes/cache/scratch'
    scratch.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='dalo-acl-read-', dir=scratch) as directory:
        fixture = Path(directory)
        sid = secrets.token_hex(16)
        readonly_user = 'acl_' + secrets.token_hex(4)
        readonly_password = secrets.token_hex(24)
        try:
            for version in ('base', 'candidate'):
                dest = fixture / version
                shutil.copytree(ROOT / 'app', dest / 'app', symlinks=True,
                                ignore=shutil.ignore_patterns('daloradius.conf.php'))
                if version == 'base':
                    restore_pear_bootstrap((dest / 'app').parent, BASE)
                    for rel in ('app/operators/library/check_operator_perm.php',
                                'app/operators/include/management/operator_acls.php'):
                        (dest / rel).write_text(run('git', 'show', BASE + ':' + rel) + '\n')
                config = (ROOT / 'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>', '')
                config += '''
$configValues['CONFIG_DB_HOST'] = getenv('ACL_TEST_DB');
$configValues['CONFIG_DB_USER'] = getenv('ACL_TEST_USER');
$configValues['CONFIG_DB_PASS'] = getenv('ACL_TEST_PASSWORD');
$configValues['CONFIG_DB_NAME'] = 'radius';
$configValues['CONFIG_DB_ENGINE'] = 'mysqli';
$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'] = 'acl_custom';
$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES'] = 'acl_catalog_custom';
$configValues['CONFIG_LOCATIONS']['other'] = array('Engine'=>'mysqli', 'Hostname'=>getenv('ACL_TEST_DB'),
'Username'=>getenv('ACL_TEST_USER'), 'Password'=>getenv('ACL_TEST_PASSWORD'), 'Port'=>'3306', 'Database'=>'radius_other');
if (isset($_GET['bad_table'])) { $configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'] = 'acl_custom; SELECT 1'; }
if (isset($_GET['unavailable'])) { $configValues['CONFIG_DB_HOST'] = '127.0.0.1'; $configValues['CONFIG_DB_PORT'] = '1'; }
'''
                (dest / 'app/common/includes/daloradius.conf.php').write_text(config)
                (dest / 'app/operators/acl-fixture.php').write_text('''<?php
include __DIR__ . '/library/checklogin.php';
if (array_key_exists('alias', $_GET)) { $operator_perm_file = $_GET['alias']; }
if (isset($_GET['http_deny'])) { $operator_perm_deny_http_status = 403; }
if (isset($_GET['callback'])) {
    $db_error_handler = function ($error) {
        http_response_code(500); header('Content-Type: application/json');
        echo json_encode(array('error'=>'Permission check failed')); exit;
    };
}
$pdo = null; $caller_id = null;
if (isset($_GET['caller'])) {
    include __DIR__ . '/../common/includes/config_read.php';
    require_once __DIR__ . '/../common/includes/pdo_connection.php';
    $pdo = dalo_pdo_connect($configValues); $pdo->beginTransaction(); $caller_id = spl_object_id($pdo);
}
include __DIR__ . '/library/check_operator_perm.php';
if (($_GET['mode'] ?? '') === 'render') {
    include __DIR__ . '/include/management/operator_acls.php';
    drawOperatorACLs($_GET['id'] ?? '');
} else {
    $kept = $caller_id === null || ($pdo instanceof PDO && spl_object_id($pdo) === $caller_id &&
                                  $pdo->inTransaction() && $pdo->query('SELECT 1')->fetchColumn() == 1);
    echo json_encode(array('authorized'=>true, 'caller_kept'=>$kept));
}
if ($pdo instanceof PDO && $pdo->inTransaction()) { $pdo->rollBack(); }
''')
            (fixture / 'session.php').write_text('''<?php
session_name('daloradius_operator_sid'); session_id($argv[1]); session_start();
$_SESSION = array('daloradius_logged_in'=>true, 'operator_id'=>json_decode($argv[2],true),
'operator_user'=>'fixture-admin', 'location_name'=>$argv[3], 'time'=>time()); session_write_close();
''')
            # Tripwire: candidate reads must not open the legacy provider, even indirectly.
            (fixture / 'candidate/app/common/includes/db_open.php').write_text(
                '<?php throw new RuntimeException("Legacy provider tripwire");')
            run('docker', 'network', 'create', '--internal', NETWORK)
            run('docker', 'run', '-d', '--name', DB, '--network', NETWORK,
                '--tmpfs', '/var/lib/mysql', '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e', 'MARIADB_DATABASE=radius', 'mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'), 'MariaDB')
            for schema in ('fr3-mariadb-freeradius.sql', 'mariadb-daloradius.sql'):
                sql((ROOT / 'contrib/db' / schema).read_text())
            sql('CREATE TABLE acl_custom LIKE operators_acl; '
                'CREATE TABLE acl_catalog_custom LIKE operators_acl_files; '
                "INSERT INTO acl_catalog_custom (file,category,section) VALUES "
                "('acl_fixture','<Category&>','Section'),('unused_page','<Category&>','Section'),"
                "('odd&section','Special','Étoile'); "
                "INSERT INTO acl_custom (operator_id,file,access) VALUES "
                "(9001,'acl_fixture',1),(9002,'acl_fixture',0),(9001,'odd&section',1),"
                "(9001,'odd'' é%+',1),(9005,'acl_fixture',0),(9005,'acl_fixture',1); "
                'CREATE DATABASE radius_other; '
                'CREATE TABLE radius_other.acl_custom LIKE acl_custom; '
                'CREATE TABLE radius_other.acl_catalog_custom LIKE acl_catalog_custom; '
                'INSERT INTO radius_other.acl_custom SELECT * FROM acl_custom; '
                'INSERT INTO radius_other.acl_catalog_custom SELECT * FROM acl_catalog_custom; '
                "UPDATE radius_other.acl_custom SET access=0 WHERE operator_id=9001; "
                f"CREATE USER '{readonly_user}'@'%' IDENTIFIED BY '{readonly_password}'; "
                f"GRANT SELECT ON radius.* TO '{readonly_user}'@'%'; "
                f"GRANT SELECT ON radius_other.* TO '{readonly_user}'@'%';")
            environment = os.environ.copy()
            environment.update(ACL_TEST_DB=DB, ACL_TEST_USER=readonly_user,
                               ACL_TEST_PASSWORD=readonly_password)
            command = ['docker', 'run', '-d', '--name', WEB, '--network', NETWORK,
                       '-e', 'ACL_TEST_DB', '-e', 'ACL_TEST_USER', '-e', 'ACL_TEST_PASSWORD',
                       '-v', f'{fixture}:/fixtures', '-w', '/fixtures', '--entrypoint', 'php',
                       IMAGE, '-d', 'display_errors=0', '-d', 'opcache.enable=0',
                       '-S', '0.0.0.0:8080', '-t', '/fixtures']
            proc = subprocess.run(command, env=environment, text=True, capture_output=True, timeout=60)
            assert proc.returncode == 0, 'PHP fixture startup failed'
            address = run('docker', 'inspect', '-f',
                          '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}', WEB)
            origin = 'http://' + address + ':8080/'
            wait_for(lambda: urllib.request.urlopen(origin + 'candidate/app/operators/login.php', timeout=10), 'PHP HTTP')
            print('RUNTIME: PHP ' + run('docker', 'exec', WEB, 'php', '-r', 'echo PHP_VERSION;')
                  + '; MariaDB ' + sql('SELECT VERSION()'))
            browser = urllib.request.build_opener(NoRedirect())

            def session(operator=9001, location='default'):
                run('docker', 'exec', WEB, 'php', '/fixtures/session.php', sid,
                    json.dumps(operator), location)

            def request(version='candidate', params=None, authenticated=True, route='acl-fixture.php'):
                params = params or {}
                url = origin + version + '/app/operators/' + route
                if params:
                    url += '?' + urllib.parse.urlencode(params)
                headers = {'Cookie': 'daloradius_operator_sid=' + sid} if authenticated else {}
                req = urllib.request.Request(url, headers=headers)
                try:
                    response = browser.open(req, timeout=20)
                except urllib.error.HTTPError as error:
                    response = error
                return response.status, response.headers, response.read().decode()

            def state():
                return tuple(sql(f'SELECT operator_id,file,access FROM {schema}.acl_custom ORDER BY id')
                             + sql(f'SELECT file,category,section FROM {schema}.acl_catalog_custom ORDER BY id')
                             for schema in ('radius', 'radius_other'))

            initial = state()
            for operator, location, params in ((9001, 'default', {}), (9002, 'default', {}),
                                               (9005, 'default', {'http_deny': 1}),
                                               (9003, 'default', {'http_deny': 1}),
                                               (9001, 'other', {'http_deny': 1}),
                                               (9001, 'default', {'alias': 'missing', 'http_deny': 1})):
                session(operator, location)
                a, b = request('base', params), request('candidate', params)
                assert (a[0], a[1].get('Location'), a[2]) == (b[0], b[1].get('Location'), b[2]), 'permission parity failed'
            session()
            sql("UPDATE acl_custom SET access=0 WHERE operator_id=9001 AND file='acl_fixture'")
            try:
                assert request(params={'http_deny': 1})[0] == 403
            finally:
                sql("UPDATE acl_custom SET access=1 WHERE operator_id=9001 AND file='acl_fixture'")
            assert request()[0] == 200
            assert request(authenticated=False)[0] == 302
            assert request(params={'caller': 1})[2] == '{"authorized":true,"caller_kept":true}'
            print('PASS: PEAR/PDO allow/deny/missing ACL parity, redirects/403, selected location, login and caller-owned PDO preserved')

            for target in ('', '0', '9001', '9003'):
                params = {'mode': 'render', 'id': target}
                a, b = request('base', params), request('candidate', params)
                assert a[0] == b[0] == 200
                left, right = ACLRows(), ACLRows()
                left.feed(a[2]); right.feed(b[2])
                assert collections.Counter(left.rows) == collections.Counter(right.rows), 'render parity failed'
                if target == '9001':
                    assert '&lt;Category&amp;&gt;' in b[2] and 'ACL_odd&amp;section' in b[2]
                if target == '9003':
                    assert not right.rows
            sql('CREATE TABLE catalog_empty LIKE acl_catalog_custom; '
                'RENAME TABLE acl_catalog_custom TO catalog_saved, catalog_empty TO acl_catalog_custom')
            try:
                for version in ('base', 'candidate'):
                    response = request(version, {'mode': 'render'})
                    parsed = ACLRows()
                    parsed.feed(response[2])
                    assert response[0] == 200 and not parsed.rows and '<table' in response[2]
            finally:
                sql('DROP TABLE acl_catalog_custom; RENAME TABLE catalog_saved TO acl_catalog_custom')
            print('PASS: creation/edit ACL controls match PEAR, empty catalog/unfiltered joins, first-row duplicate policy and HTML escaping preserved')

            session(9002)
            injected = "acl_fixture' OR '1'='1"
            assert request('base', {'alias': injected, 'http_deny': 1})[0] == 200, 'baseline injection not characterized'
            assert request(params={'alias': injected, 'http_deny': 1})[0] == 403
            session()
            assert request(params={'alias': "odd' é%+", 'http_deny': 1})[0] == 200
            assert request(params={'alias[]': 'acl_fixture', 'http_deny': 1})[0] == 403
            for operator in (None, [], '9001suffix', -1):
                session(operator)
                assert request(params={'http_deny': 1})[0] == 403
            session(location='unknown')
            assert request(params={'http_deny': 1})[0] == 403
            session()
            assert request(params={'bad_table': 1, 'http_deny': 1})[0] == 403
            assert request(params={'mode': 'render', 'id[]': '9001'})[2] == '<div class="failure">Unable to load operator permissions.</div>'
            print('PASS: quoted/Unicode aliases bound, baseline SQL-injection characterized, malformed identity/page/table rejected')

            for params in ({'unavailable': 1, 'http_deny': 1}, {'unavailable': 1, 'callback': 1}):
                response = request(params=params)
                if 'callback' in params:
                    assert response[0] == 500 and json.loads(response[2]) == {'error': 'Permission check failed'}
                else:
                    assert response[0] == 503 and response[2] == 'Unable to check operator permissions.'
            sql('RENAME TABLE acl_custom TO acl_unavailable')
            try:
                response = request(params={'http_deny': 1})
                assert response[0] == 503 and response[2] == 'Unable to check operator permissions.'
                response = request(params={'callback': 1})
                assert response[0] == 500 and json.loads(response[2]) == {'error': 'Permission check failed'}
            finally:
                sql('RENAME TABLE acl_unavailable TO acl_custom')
            sql('RENAME TABLE acl_catalog_custom TO catalog_unavailable')
            try:
                response = request(params={'mode': 'render'})
                assert response[2] == '<div class="failure">Unable to load operator permissions.</div>'
            finally:
                sql('RENAME TABLE catalog_unavailable TO acl_catalog_custom')
            assert request(route='library/operator_acl_read.php')[0] == 404
            assert request(route='library/check_operator_perm.php')[0] == 302
            assert request(route='include/management/operator_acls.php')[0] == 302
            assert state() == initial, 'read-only ACL test changed rows'
            logs = run('docker', 'logs', WEB)
            assert readonly_password not in logs and readonly_user not in logs
            assert 'PHP Fatal error' not in logs and 'PHP Warning' not in logs
            print('PASS: SQL errors fail closed/redacted, callback contract, no partial form, direct guards, SELECT-only grants and unchanged exact ACL state')
        finally:
            for container in (WEB, DB):
                run('docker', 'rm', '-f', container, check=False)
            run('docker', 'network', 'rm', NETWORK, check=False)
            for container in (WEB, DB):
                assert not run('docker', 'ps', '-a', '-q', '--filter', 'name=^/' + container + '$'), 'fixture container remained'
            assert not run('docker', 'network', 'ls', '-q', '--filter', 'name=^' + NETWORK + '$'), 'fixture network remained'
    print('PASS: disposable containers, network, config/session fixture and credentials removed; no lab deployment')


if __name__ == '__main__':
    main()
