#!/usr/bin/env python3
"""UNIT-027: differential real HTTP/PHP/MariaDB operator deletion.

Two runs use equivalent disposable tmpfs databases and an internal-only Docker
network. No real credentials or factors are read, retained or printed.
"""
import hashlib
import json
import os
from pathlib import Path
import secrets
import shutil
import subprocess
import tempfile
from concurrent.futures import ThreadPoolExecutor
import urllib.error
import urllib.parse
import urllib.request

import user_actions_http as harness
from acct_maintenance_http import Forms

ROOT = Path(__file__).resolve().parents[1]
BASELINE = os.environ.get('OPERATOR_DELETE_BASELINE') == '1'
DB, WEB, NETWORK = harness.DB, harness.WEB, harness.NETWORK
run, wait_for = harness.run, harness.wait_for
IMAGE = os.environ.get('OPERATOR_DELETE_WEB_IMAGE', 'lirantal/daloradius')
BASE_COMMIT = 'edc624db99321eaf26df108c89bda5f5eb15a392'


def sql(query):
    try:
        return harness.sql(query)
    except RuntimeError:
        # Never echo a statement containing fixture material to stdout/stderr.
        raise RuntimeError('Synthetic SQL operation failed (statement redacted)') from None


def main():
    scratch = Path.home() / '.hermes/cache/scratch'
    scratch.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='dalo-op-delete-', dir=scratch) as directory:
        fixture = Path(directory)
        shutil.copytree(ROOT / 'app', fixture / 'app', symlinks=True)
        if BASELINE:
            old = run('git', 'show', BASE_COMMIT + ':app/operators/config-operators-del.php')
            (fixture / 'app/operators/config-operators-del.php').write_text(old + '\n')
        try:
            run('docker', 'network', 'create', '--internal', NETWORK)
            run('docker', 'run', '-d', '--name', DB, '--network', NETWORK,
                '--tmpfs', '/var/lib/mysql', '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e', 'MARIADB_DATABASE=radius', 'mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'), 'MariaDB')
            for file in ('fr3-mariadb-freeradius.sql', 'mariadb-daloradius.sql'):
                sql((ROOT / 'contrib/db' / file).read_text())
            names = ('unit-alpha', 'unit-bravo', 'unit-charlie', 'unit-beta',
                     'unit-gamma', 'unit-keep', 'unit-race', 'unit-scalar', "unit-%plus+O'Reilly")
            columns = ('username,firstname,lastname,title,department,company,'
                       'phone1,phone2,email1,email2,messenger1,messenger2,notes,auth_source')
            selected = ('firstname,lastname,title,department,company,phone1,phone2,'
                        'email1,email2,messenger1,messenger2,notes,auth_source')
            for name in names:
                sql(f"INSERT INTO operators ({columns}) SELECT '{name.replace(chr(39),chr(39)*2)}',"
                    f'{selected} FROM operators WHERE id=1')
            sql("INSERT INTO operators_acl (operator_id,file,access) "
                "SELECT id,'mng_edit',1 FROM operators WHERE username LIKE 'unit-%'")
            sql("INSERT INTO operators_acl (operator_id,file,access) "
                "SELECT id,'config_operators_edit',1 FROM operators WHERE username LIKE 'unit-%'")
            sql("INSERT INTO operators_acl (operator_id,file,access) VALUES "
                "(9001,'config_operators_del',1),(9002,'config_operators_del',0),"
                "(9001,'config_operators_list',1)")
            config = (ROOT / 'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>', '')
            for key, value in {'CONFIG_DB_HOST': DB, 'CONFIG_DB_USER': 'root',
                               'CONFIG_DB_PASS': '', 'CONFIG_DB_NAME': 'radius',
                               'CONFIG_LOG_PAGES': 'no', 'CONFIG_LOG_QUERIES': 'no',
                               'CONFIG_LOG_ACTIONS': 'no', 'CONFIG_DEBUG_SQL': 'no',
                               'CONFIG_DEBUG_SQL_ONPAGE': 'no'}.items():
                config += '\n$configValues[' + repr(key) + '] = ' + repr(value) + ';\n'
            (fixture / 'app/common/includes/daloradius.conf.php').write_text(config)
            (fixture / 'session.php').write_text('''<?php
session_name('daloradius_operator_sid'); session_id($argv[1]); session_start();
$_SESSION = ['daloradius_logged_in'=>true, 'operator_id'=>(int)$argv[2],
    'operator_user'=>'fixture-admin', 'location_name'=>'default', 'time'=>time()];
session_write_close();
''')
            run('docker', 'run', '-d', '--name', WEB, '--network', NETWORK,
                '-v', f'{fixture}:/fixtures', '-w', '/fixtures/app/operators',
                '--entrypoint', 'php', IMAGE, '-d', 'display_errors=0',
                '-S', '0.0.0.0:8080', '-t', '.')
            address = run('docker', 'inspect', '-f',
                          '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}', WEB)
            base = 'http://' + address + ':8080/'
            wait_for(lambda: urllib.request.urlopen(base + 'login.php', timeout=10), 'PHP HTTP')
            sid = secrets.token_hex(16)

            def set_session(operator=9001):
                run('docker', 'exec', WEB, 'php', '/fixtures/session.php', sid, str(operator))

            def request(data=None, authenticated=True):
                req = urllib.request.Request(base + 'config-operators-del.php',
                    data=urllib.parse.urlencode(data).encode() if data is not None else None,
                    headers={'Cookie': 'daloradius_operator_sid=' + sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req, timeout=25) as res:
                        return res.status, res.url, res.read().decode()
                except urllib.error.HTTPError as err:
                    return err.code, err.url, err.read().decode()

            def token():
                forms = Forms(request()[2]).forms
                return next(f['csrf_token'] for f in forms if 'csrf_token' in f)

            def submit(names, csrf=None):
                controls = [('operator_username[]', name) for name in names]
                controls.append(('csrf_token', token() if csrf is None else csrf))
                return request(controls)[2]

            def state():
                return {'accounts': sql("SELECT id,username,firstname,lastname,notes,"
                    "auth_source,totp_enabled FROM operators WHERE username LIKE 'unit-%' "
                    "ORDER BY id"),
                    'acls': sql("SELECT o.username,a.id,a.file,a.access FROM operators o "
                    "JOIN operators_acl a ON a.operator_id=o.id "
                    "WHERE o.username LIKE 'unit-%' ORDER BY o.id,a.id")}

            set_session()
            if not BASELINE:
                try:
                    urllib.request.urlopen(base + 'library/operator_delete.php', timeout=10)
                    raise AssertionError('helper was served directly')
                except urllib.error.HTTPError as err:
                    assert err.code == 404
            assert request(authenticated=False)[1].endswith('/login.php')
            set_session(9002)
            assert request()[1].endswith('/home-error.php')
            set_session()
            before = state()
            assert 'Deleted operator(s):' not in submit(['unit-alpha'],csrf='invalid')
            assert state() == before
            assert 'unit-alpha' in request()[2]
            listing = urllib.request.Request(base + 'config-operators-list.php',
                headers={'Cookie': 'daloradius_operator_sid=' + sid})
            with urllib.request.urlopen(listing, timeout=25) as res:
                list_html = res.read().decode()
            assert 'config-operators-del.php' in list_html
            assert 'operator_username[]' in list_html and 'unit-alpha' in list_html
            print('PASS: login, ACL, CSRF and both available-operator/list forms')

            assert 'Deleted operator(s):' in submit(['unit-alpha'])
            assert sql("SELECT COUNT(*) FROM operators WHERE username='unit-alpha'") == '0'
            assert sql("SELECT COUNT(*) FROM operators_acl WHERE operator_id NOT IN "
                       "(SELECT id FROM operators) AND operator_id<>9001 AND operator_id<>9002") == '0'
            assert 'Deleted operator(s):' in submit(['unit-bravo','unit-charlie','unit-bravo'])
            assert sql("SELECT COUNT(*) FROM operators WHERE username IN "
                       "('unit-bravo','unit-charlie')") == '0'
            normal = state()
            print('NORMAL_STATE_SHA256=' + hashlib.sha256(
                json.dumps(normal,ensure_ascii=False,sort_keys=True).encode()).hexdigest())
            print('PASS: single and multi-delete remove operators and linked ACLs')

            if not BASELINE:
                scalar_page = request([
                    ('operator_username','unit-scalar'), ('csrf_token',token())])[2]
                assert 'Deleted operator(s):' in scalar_page
                assert sql("SELECT COUNT(*) FROM operators WHERE username='unit-scalar'") == '0'
                print('PASS: scalar selection also removes its operator and ACLs')
                for bad in (['unit-beta','unit-absent'], ['unit-beta',''],
                            ['unit-beta', ['nested']], ['unit-beta', 2],
                            ['unit-beta','z'*33], ['unit-beta','UNIT-GAMMA']):
                    unchanged = state()
                    page = submit(bad)
                    assert 'Deleted operator(s):' not in page, bad
                    assert state() == unchanged, bad
                assert 'Deleted operator(s):' not in submit([])
                unchanged = state()
                nested_page = request([('operator_username[]','unit-beta'),
                    ('operator_username[][]','nested'),('csrf_token',token())])[2]
                assert 'Deleted operator(s):' not in nested_page and state() == unchanged
                print('PASS: full selection preflight rejects malformed or stale later items')
                target_id = sql("SELECT id FROM operators WHERE username='unit-gamma'")
                sql("DELIMITER //\nCREATE TRIGGER block_later_acl BEFORE DELETE ON operators_acl "
                    "FOR EACH ROW BEGIN IF OLD.operator_id=" + target_id +
                    " THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture failure'; "
                    "END IF; END//\nDELIMITER ;")
                try:
                    unchanged = state()
                    page = submit(['unit-beta','unit-gamma'])
                    assert 'Deleted operator(s):' not in page and state() == unchanged
                    assert 'SQLSTATE' not in page and 'fixture failure' not in page
                finally:
                    sql('DROP TRIGGER block_later_acl')
                sql("DELIMITER //\nCREATE TRIGGER block_parent BEFORE DELETE ON operators "
                    "FOR EACH ROW BEGIN IF OLD.username='unit-gamma' "
                    "THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture failure'; "
                    "END IF; END//\nDELIMITER ;")
                try:
                    unchanged = state()
                    page = submit(['unit-beta','unit-gamma'])
                    assert 'Deleted operator(s):' not in page and state() == unchanged
                finally:
                    sql('DROP TRIGGER block_parent')
                sql('ALTER TABLE operators_acl ENGINE=MyISAM')
                try:
                    unchanged = state()
                    assert 'Deleted operator(s):' not in submit(['unit-beta'])
                    assert state() == unchanged
                finally:
                    sql('ALTER TABLE operators_acl ENGINE=InnoDB')
                sql('ALTER TABLE operators ENGINE=MyISAM')
                try:
                    unchanged = state()
                    assert 'Deleted operator(s):' not in submit(['unit-beta'])
                    assert state() == unchanged
                finally:
                    sql('ALTER TABLE operators ENGINE=InnoDB')
                print('PASS: late ACL/parent failures roll back prior deletes; MyISAM rejected')
                special = "unit-%plus+O'Reilly"
                special_page = submit([special])
                assert 'Deleted operator(s):' in special_page
                assert 'unit-%plus+O&#039;Reilly' in special_page
                assert sql("SELECT COUNT(*) FROM operators WHERE username='unit-%plus+O''Reilly'") == '0'
                assert sql("SELECT COUNT(*) FROM operators_acl WHERE operator_id NOT IN "
                           "(SELECT id FROM operators) AND operator_id<>9001 AND operator_id<>9002") == '0'
                print('PASS: literal percent, plus, and quote remain bound to the intended account')
                sql(f"INSERT INTO operators ({columns}) SELECT 'unit-beta',"
                    f'{selected} FROM operators WHERE username=\'unit-keep\' LIMIT 1')
                unchanged = state()
                assert 'Deleted operator(s):' not in submit(['unit-beta'])
                assert state() == unchanged
                sql("DELETE FROM operators WHERE username='unit-beta' AND id=(SELECT i FROM "
                    "(SELECT MAX(id) i FROM operators WHERE username='unit-beta') x)")
                print('PASS: ambiguous duplicate name refuses deletion')
                # Distinct PHP/PDO connections compete for the same operator.
                (fixture / 'delete_race.php').write_text('''<?php
require '/fixtures/app/operators/library/operator_delete.php';
$p=new PDO('mysql:host=' . $argv[1] . ';dbname=radius;charset=utf8mb4','root','',
    array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false));
$c=array('CONFIG_DB_TBL_DALOOPERATORS'=>'operators',
         'CONFIG_DB_TBL_DALOOPERATORS_ACL'=>'operators_acl');
try { dalo_operator_delete($p,$c,array('unit-race')); echo 'deleted'; }
catch (DomainException $e) { echo 'stale'; }
catch (Throwable $e) { echo 'unexpected'; }
''')
                command = ['docker','exec',WEB,'php','/fixtures/delete_race.php',DB]
                with ThreadPoolExecutor(max_workers=2) as pool:
                    outcomes = sorted(f.result() for f in [pool.submit(run,*command) for _ in range(2)])
                assert outcomes == ['deleted','stale'], outcomes
                assert sql("SELECT COUNT(*) FROM operators WHERE username='unit-race'") == '0'
                print('PASS: concurrent PDO deletes serialize; only one succeeds')
                self_id = sql("SELECT id FROM operators WHERE username='unit-keep'")
                sql("INSERT INTO operators_acl (operator_id,file,access) VALUES (" +
                    self_id + ",'config_operators_del',1)")
                set_session(int(self_id))
                assert 'Deleted operator(s):' in submit(['unit-keep'])
                assert request()[1].endswith('/home-error.php')
                print('PASS: deleting own account also revokes its ACL')
            logs = subprocess.run(['docker', 'logs', WEB], capture_output=True,
                                  text=True,timeout=30,check=True)
            assert 'PHP Fatal error' not in logs.stderr + logs.stdout
            assert 'PHP Warning' not in logs.stderr + logs.stdout
        finally:
            for name in (WEB,DB):
                run('docker','rm','-f','-v',name,check=False)
            run('docker','network','rm',NETWORK,check=False)


if __name__ == '__main__':
    main()
