#!/usr/bin/env python3
"""UNIT-025: differential real HTTP/PHP/MariaDB operator creation.

Uses disposable MariaDB/PHP containers and a network without external egress.
No real credentials or password hashes are written to logs or test snapshots.
"""
import json
import os
from pathlib import Path
import secrets
import shutil
import subprocess
import sys
import tempfile
import urllib.error
import urllib.parse
import urllib.request

import user_actions_http as harness
from acct_maintenance_http import Forms

ROOT = Path(__file__).resolve().parents[1]
BASELINE = os.environ.get('OPERATOR_CREATE_BASELINE') == '1'
DB, WEB, NETWORK = harness.DB, harness.WEB, harness.NETWORK
run, sql, wait_for = harness.run, harness.sql, harness.wait_for
IMAGE = os.environ.get('OPERATOR_CREATE_WEB_IMAGE', 'lirantal/daloradius')
BASE_COMMIT = '3e727673fdbdc0de793cfa820cd13b1c3d7f22bd'


def main():
    scratch = Path.home() / '.hermes/cache/scratch'
    scratch.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='dalo-op-create-', dir=scratch) as directory:
        fixture = Path(directory)
        shutil.copytree(ROOT / 'app', fixture / 'app', symlinks=True)
        if BASELINE:
            old = run('git', 'show', BASE_COMMIT + ':app/operators/config-operators-new.php')
            (fixture / 'app/operators/config-operators-new.php').write_text(old + '\n')
        try:
            run('docker', 'network', 'create', '--internal', NETWORK)
            run('docker', 'run', '-d', '--name', DB, '--network', NETWORK,
                '--tmpfs', '/var/lib/mysql', '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e', 'MARIADB_DATABASE=radius', 'mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'), 'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql', 'mariadb-daloradius.sql'):
                sql((ROOT / 'contrib/db' / name).read_text())
            sql("INSERT INTO operators_acl (operator_id,file,access) VALUES "
                "(9001,'config_operators_new',1),(9002,'config_operators_new',0)")
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
            url = 'http://' + address + ':8080/config-operators-new.php'
            wait_for(lambda: urllib.request.urlopen('http://' + address + ':8080/login.php', timeout=10), 'PHP HTTP')
            sid = secrets.token_hex(16)
            def set_session(operator=9001):
                run('docker', 'exec', WEB, 'php', '/fixtures/session.php', sid, str(operator))

            def request(data=None, authenticated=True):
                headers = {'Cookie': 'daloradius_operator_sid=' + sid} if authenticated else {}
                req = urllib.request.Request(url,
                    data=urllib.parse.urlencode(data).encode() if data is not None else None,
                    headers=headers)
                try:
                    with urllib.request.urlopen(req, timeout=25) as res:
                        return res.status, res.url, res.read().decode()
                except urllib.error.HTTPError as err:
                    return err.code, err.url, err.read().decode()

            def token():
                forms = Forms(request()[2]).forms
                return next(form['csrf_token'] for form in forms if 'csrf_token' in form)

            local_password = secrets.token_urlsafe(24)
            def submit(username, extra=None, csrf=None):
                data = {'operator_username': username, 'operator_password': local_password,
                        'auth_source': 'local', 'csrf_token': token() if csrf is None else csrf,
                        'firstname': "Anne O'Reilly", 'lastname': 'Étoile',
                        'email1': 'operator@example.invalid', 'ACL_config_operators_edit': '1',
                        'ACL_mng_edit': '0'}
                if extra:
                    data.update(extra)
                return request(data)[2]

            def state():
                return {'operators': sql("SELECT username,auth_source,COALESCE(external_id,'<NULL>'),"
                    "firstname,lastname,email1,creationby FROM operators WHERE id<>1 "
                    "ORDER BY username"),
                    'acls': sql("SELECT o.username,a.file,a.access FROM operators o "
                    "JOIN operators_acl a ON a.operator_id=o.id WHERE o.id<>1 "
                    "ORDER BY o.username,a.file")}

            set_session()
            if not BASELINE:
                try:
                    urllib.request.urlopen('http://' + address + ':8080/library/operator_create.php', timeout=10)
                    raise AssertionError('helper was served directly')
                except urllib.error.HTTPError as error:
                    assert error.code == 404
            assert request(authenticated=False)[1].endswith('/login.php')
            set_session(9002)
            assert request()[1].endswith('/home-error.php')
            set_session()
            assert 'Successfully added new operator' not in submit('local-fixture', csrf='invalid')
            assert state() == {'operators': '', 'acls': ''}
            print('PASS: login, ACL and CSRF gates prevent unauthorized creation')

            html = submit('local-fixture')
            assert 'Successfully added new operator' in html, 'local create failed'
            assert sql("SELECT COUNT(*) FROM operators WHERE username='local-fixture'") == '1'
            assert sql("SELECT password IS NOT NULL, auth_source, external_id IS NULL "
                       "FROM operators WHERE username='local-fixture'") == '1\tlocal\t1'
            # Verify a real stored hash without exposing or snapshotting it.
            verify = '''$p=new PDO("mysql:host=%s;dbname=radius;charset=utf8mb4","root","");
            $s=$p->prepare("SELECT password FROM operators WHERE username=?");
            $s->execute(array("local-fixture")); $h=$s->fetchColumn();
            echo password_verify(stream_get_contents(STDIN),$h) ? "yes" : "no";''' % DB
            checked = subprocess.run(['docker','exec','-i',WEB,'php','-r',verify],
                input=local_password,text=True,capture_output=True,timeout=30,check=True)
            assert checked.stdout.strip() == 'yes', 'local stored hash not valid'
            # Authenticate through the real login route and use the new ACL.
            from http.cookiejar import CookieJar
            browser = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(CookieJar()))
            login_url = 'http://' + address + ':8080/login.php'
            with browser.open(login_url, timeout=20) as response:
                login_html = response.read().decode()
            login_token = next(form['csrf_token'] for form in Forms(login_html).forms if 'csrf_token' in form)
            login_data = urllib.parse.urlencode({'operator_user': 'local-fixture',
                'operator_pass': local_password, 'csrf_token': login_token}).encode()
            with browser.open(urllib.request.Request(
                    'http://' + address + ':8080/dologin.php', data=login_data), timeout=25) as response:
                destination = response.url
                response.read()
            assert not destination.endswith('/login.php') and not destination.endswith('/login-otp.php'), destination
            with browser.open('http://' + address + ':8080/config-operators-edit.php?operator_username=local-fixture', timeout=25) as response:
                assert response.url.endswith('operator_username=local-fixture') and response.status == 200, response.url
                response.read()
            print('PASS: newly created local identity authenticates and can use its granted ACL')
            assert sql("SELECT file,access FROM operators_acl WHERE operator_id="
                       "(SELECT id FROM operators WHERE username='local-fixture') ORDER BY file") == \
                   'config_operators_edit\t1\nmng_edit\t0'
            ldap = submit('ldap-fixture', {'auth_source': 'ldap', 'operator_password': '',
                                          'external_id': 'directory-fixture-1', 'ACL_mng_edit': '1'})
            assert 'Successfully added new operator' in ldap, 'LDAP create failed'
            assert sql("SELECT password IS NULL,auth_source,external_id FROM operators "
                       "WHERE username='ldap-fixture'") == '1\tldap\tdirectory-fixture-1'
            assert sql("SELECT COUNT(*) FROM operators_acl WHERE operator_id="
                       "(SELECT id FROM operators WHERE username='ldap-fixture')") == '2'
            assert 'operator already exists in database' in submit('local-fixture%')
            assert sql("SELECT COUNT(*) FROM operators WHERE username='local-fixture'") == '1'
            print('PASS: local hash verified, LDAP password NULL, ACLs and duplicate detection')
            print('NORMAL_STATE=' + json.dumps(state(), ensure_ascii=False, sort_keys=True))

            if not BASELINE:
                before = state()
                for bad in ({'ACL_mng_edit':'2'}, {'ACL_nonexistent':'1'},
                            {'ACL_mng_edit[]':'1'},
                            {'firstname':'x' * 33}, {'operator_username':'x' * 33},
                            {'operator_password[]':'array'}, {'external_id[]':'array'},
                            {'auth_source[]':'local'}):
                    page = submit('invalid-fixture', bad)
                    assert 'Successfully added new operator' not in page, bad
                    assert state() == before, bad
                empty_acl = {'operator_username':'empty-acl','operator_password':local_password,
                    'auth_source':'local','csrf_token':token()}
                assert 'Successfully added new operator' not in request(empty_acl)[2]
                assert state() == before
                print('PASS: bad ACLs, profile lengths, array fields and absent ACLs reject without writes')

                sql("DELIMITER //\nCREATE TRIGGER block_second_acl BEFORE INSERT ON operators_acl "
                    "FOR EACH ROW BEGIN IF NEW.file='mng_edit' THEN SIGNAL SQLSTATE '45000' "
                    "SET MESSAGE_TEXT='fixture error'; END IF; END//\nDELIMITER ;\n")
                try:
                    page = submit('late-failure')
                    assert 'Successfully added new operator' not in page
                    assert state() == before, 'operator/first ACL stranded after second ACL failure'
                finally:
                    sql('DROP TRIGGER block_second_acl')
                assert 'Successfully added new operator' not in submit('external-collision',
                    {'auth_source':'ldap','operator_password':'',
                     'external_id':'directory-fixture-1'})
                assert state() == before
                sql('ALTER TABLE operators_acl ENGINE=MyISAM')
                try:
                    assert 'Successfully added new operator' not in submit('nontransactional')
                    assert state() == before
                finally:
                    sql('ALTER TABLE operators_acl ENGINE=InnoDB')
                print('PASS: later ACL trigger, external ID conflict and non-InnoDB preflight roll back')

                # Two independent PHP connections race on the same username.
                (fixture / 'create_cli.php').write_text('''<?php
require '/fixtures/app/operators/include/management/operator_identity.php';
require '/fixtures/app/operators/library/operator_create.php';
require '/fixtures/app/common/includes/pdo_connection.php';
$p=new PDO('mysql:host=' . $argv[1] . ';dbname=radius;charset=utf8mb4','root','',
    array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false));
$p->exec("SET SESSION sql_mode=''");
$c=array('CONFIG_DB_TBL_DALOOPERATORS'=>'operators',
         'CONFIG_DB_TBL_DALOOPERATORS_ACL'=>'operators_acl',
         'CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES'=>'operators_acl_files');
$n=$argv[2]; $f=array('username'=>$n,'firstname'=>'','lastname'=>'','title'=>'',
'department'=>'','company'=>'','phone1'=>'','phone2'=>'','email1'=>'','email2'=>'',
'messenger1'=>'','messenger2'=>'','notes'=>'','acls'=>array('mng_edit'=>'0'));
$i=operator_prepare_create_identity('local',bin2hex(random_bytes(16)),null);
try { dalo_operator_create($p,$c,$f,$i,'fixture-admin'); echo 'created'; }
catch (DomainException $e) { echo 'duplicate'; }
''')
                cmd = ['docker','exec',WEB,'php','/fixtures/create_cli.php',DB,'racing-fixture']
                with __import__('concurrent.futures', fromlist=['ThreadPoolExecutor']).ThreadPoolExecutor(2) as pool:
                    futures = [pool.submit(run,*cmd) for _ in range(2)]
                    outcomes = sorted(f.result(timeout=60) for f in futures)
                assert outcomes == ['created','duplicate'], outcomes
                assert sql("SELECT COUNT(*) FROM operators WHERE username='racing-fixture'") == '1'
                assert sql("SELECT COUNT(*) FROM operators_acl WHERE operator_id="
                           "(SELECT id FROM operators WHERE username='racing-fixture')") == '1'
                print('PASS: concurrent PDO create requests serialize; only one identity/ACL persists')

            result = subprocess.run(['docker', 'logs', WEB], capture_output=True, text=True, check=True)
            logs = result.stdout + result.stderr
            assert 'PHP Fatal error' not in logs and 'PHP Warning' not in logs, logs[-1300:]
        finally:
            for container in (WEB, DB):
                run('docker', 'rm', '-f', '-v', container, check=False)
            run('docker', 'network', 'rm', NETWORK, check=False)
            assert not run('docker', 'ps', '-aq', '--filter', 'name=' + harness.PREFIX)
            print('CLEANUP: disposable containers and data removed; live lab untouched')


if __name__ == '__main__':
    main()
