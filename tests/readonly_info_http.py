#!/usr/bin/env python3
"""HTTP contract tests: real PHP endpoints/session/ACL code, synthetic DB only.

Requires Python 3 and PHP CLI (or PHP_COMMAND pointing to a PHP container).
Creates and removes an isolated temporary application; never uses the lab DB.
"""
import http.client
import json
import os
from pathlib import Path
import shlex
import shutil
import socket
import subprocess
import tempfile
import time
import urllib.parse

ROOT = Path(__file__).resolve().parents[1]
PHP = shlex.split(os.environ.get('PHP_COMMAND', 'php'))
ENDPOINTS = {
    'user_info.php': ('username', 'acct_username'),
    'hotspot_info.php': ('hotspot', 'acct_hotspot_accounting'),
    'vendor_attribute_info.php': ('attribute', 'mng_rad_attributes_list'),
}


def run():
    checks = 0
    with tempfile.TemporaryDirectory(prefix='dalo-info-test-') as temp:
        base = Path(temp)
        app = base / 'app'
        for name in [*ENDPOINTS, 'json_info.php']:
            dest = app / 'operators/library/ajax' / name
            dest.parent.mkdir(parents=True, exist_ok=True)
            shutil.copyfile(ROOT / 'app/operators/library/ajax' / name, dest)
        for name in ['checklogin.php', 'check_operator_perm.php', 'sessions.php']:
            shutil.copyfile(ROOT / 'app/operators/library' / name, app / 'operators/library' / name)
        dest = app / 'operators/include/management/pages_common.php'
        dest.parent.mkdir(parents=True, exist_ok=True)
        shutil.copyfile(ROOT / 'app/operators/include/management/pages_common.php', dest)
        includes = app / 'common/includes'
        includes.mkdir(parents=True)
        (includes / 'db_close.php').write_text('<?php // Synthetic connection has nothing to close.\n')
        (includes / 'db_open.php').write_text('''<?php
require_once __DIR__ . '/fixture.php';
$configValues = [
    'CONFIG_DB_TBL_DALOOPERATORS_ACL' => 'operators_acl',
    'CONFIG_DB_TBL_RADACCT' => 'radacct',
    'CONFIG_DB_TBL_DALOHOTSPOTS' => 'hotspots',
    'CONFIG_DB_TBL_DALODICTIONARY' => 'dictionary',
];
$dbSocket = new FixtureDB();
''')
        (includes / 'fixture.php').write_text('''<?php
define('PEAR_ERROR_RETURN', 1);
class DB { static function isError($value) { return $value instanceof Exception; } }
class FixtureResult {
    function fetchRow() {
        if (isset($_GET['empty'])) return null;
        return basename($_SERVER['SCRIPT_NAME']) === 'hotspot_info.php' ? [3, 1024, 2048] : [1024, 2048];
    }
}
class FixtureDB {
    private $returnErrors = false;
    function setErrorHandling($mode) { $this->returnErrors = $mode === PEAR_ERROR_RETURN; }
    function escapeSimple($value) { return str_replace("'", "''", $value); }
    function query($sql) {
        if (!str_starts_with($sql, 'SELECT ')) throw new Exception('Unexpected mutation');
        if (isset($_GET['failure']) && !$this->returnErrors) echo '<div>Default PEAR error output</div>';
        return isset($_GET['failure']) ? new Exception('Synthetic DB error') : new FixtureResult();
    }
    function getOne($sql) {
        if (str_contains($sql, 'operators_acl')) {
            $expected = [
                'user_info.php' => 'acct_username',
                'hotspot_info.php' => 'acct_hotspot_accounting',
                'vendor_attribute_info.php' => 'mng_rad_attributes_list',
            ][basename($_SERVER['SCRIPT_NAME'])];
            if (!str_contains($sql, "file='$expected'")) throw new Exception('Wrong ACL');
            return $_SESSION['operator_id'] === 1 ? 1 : 0;
        }
        if (isset($_GET['failure'])) {
            if (!$this->returnErrors) echo '<div>Default PEAR error output</div>';
            return new Exception('Synthetic DB error');
        }
        return isset($_GET['empty']) ? null : "Description with 'quotes', & é <img src=x onerror=alert(1)>";
    }
}
''')
        sessions = base / 'sessions'
        sessions.mkdir(mode=0o777)
        sessions.chmod(0o777)
        # Seed local test sessions; this is not a test of password login.
        for name, operator, age in [('allowed', 1, 0), ('denied', 2, 0), ('expired', 1, 7200)]:
            script = (f'session_save_path({json.dumps(str(sessions))}); '
                      f'session_name("daloradius_operator_sid"); session_id("{name}"); session_start(); '
                      f'$_SESSION=["daloradius_logged_in"=>true,"operator_id"=>{operator},"time"=>time()-{age}]; '
                      'session_write_close();')
            subprocess.run(PHP + ['-r', script], check=True, capture_output=True)
        with socket.socket() as sock:
            sock.bind(('127.0.0.1', 0))
            port = sock.getsockname()[1]
        command = PHP + ['-d', f'session.save_path={sessions}', '-d', 'display_errors=1',
                         '-d', 'error_reporting=24575', '-S', f'127.0.0.1:{port}', '-t', str(app / 'operators')]
        with (base / 'server.log').open('w+') as log:
            server = subprocess.Popen(command, stdout=log, stderr=log)
            try:
                for _ in range(100):
                    try:
                        with socket.create_connection(('127.0.0.1', port), timeout=.2):
                            break
                    except OSError:
                        if server.poll() is not None:
                            raise RuntimeError('PHP server exited')
                        time.sleep(.05)
                else:
                    raise RuntimeError('PHP server did not start')

                def request(endpoint, query, session='allowed', method='GET'):
                    connection = http.client.HTTPConnection('127.0.0.1', port, timeout=5)
                    headers = {} if session is None else {'Cookie': 'daloradius_operator_sid=' + session}
                    connection.request(method, '/library/ajax/' + endpoint + '?' + query, headers=headers)
                    response = connection.getresponse()
                    result = response.status, dict(response.getheaders()), response.read().decode()
                    connection.close()
                    return result

                for endpoint, (parameter, _) in ENDPOINTS.items():
                    query = urllib.parse.urlencode({parameter: "é O'Reilly & + /"})
                    status, headers, body = request(endpoint, query)
                    assert status == 200, (endpoint, status, body)
                    assert headers['Content-Type'] == 'application/json; charset=UTF-8'
                    assert headers['Cache-Control'] == 'no-store'
                    data = json.loads(body)
                    if endpoint == 'vendor_attribute_info.php':
                        assert data == {'description': "Description with 'quotes', & é <img src=x onerror=alert(1)>"}
                    else:
                        assert data['upload'] == '1 KB' and data['download'] == '2 KB', data
                        if endpoint == 'hotspot_info.php': assert data['hits'] == 3
                    checks += 1
                    status, _, body = request(endpoint, query + '&empty=1')
                    assert status == 200 and all(x == '(n/a)' for x in json.loads(body).values()), body
                    checks += 1
                    for extra, session, method, expected in [
                        ('', None, 'GET', 302), ('', 'expired', 'GET', 302),
                        ('', 'denied', 'GET', 403), ('', 'allowed', 'POST', 405),
                        ('&failure=1', 'allowed', 'GET', 500),
                    ]:
                        status, headers, body = request(endpoint, query + extra, session, method)
                        assert status == expected, (endpoint, expected, status, body)
                        if expected in [405, 500]: assert 'error' in json.loads(body)
                        if expected == 405: assert headers['Allow'] == 'GET'
                        checks += 1
                    for invalid in ['', parameter + '[]=x', parameter + '=%20']:
                        status, _, body = request(endpoint, invalid)
                        assert status == 400 and 'error' in json.loads(body), (endpoint, status, body)
                        checks += 1
            finally:
                server.terminate()
                try: server.wait(timeout=10)
                except subprocess.TimeoutExpired:
                    server.kill()
                    server.wait()
                log.seek(0)
                output = log.read()
                assert 'Fatal error' not in output and 'Warning:' not in output, output
    print(f'PASS: {checks} HTTP contract scenarios (synthetic DB, real PHP/session/ACL code).')


if __name__ == '__main__':
    run()
