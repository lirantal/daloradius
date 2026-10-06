#!/usr/bin/env python3
"""UNIT-026 differential HTTP/PHP/MariaDB test on disposable internal containers.

Run baseline with OPERATOR_EDIT_BASELINE=1, then candidate. No live configuration,
credentials, or secrets are read; synthetic random test data is destroyed.
"""
import hashlib
from concurrent.futures import ThreadPoolExecutor
import json
import os
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap
import secrets
import shutil
import subprocess
import tempfile
import urllib.error
import urllib.parse
import urllib.request

import user_actions_http as harness
from acct_maintenance_http import Forms

ROOT = Path(__file__).resolve().parents[1]
BASELINE = os.environ.get('OPERATOR_EDIT_BASELINE') == '1'
DB, WEB, NETWORK = harness.DB, harness.WEB, harness.NETWORK
run, wait_for = harness.run, harness.wait_for
IMAGE = os.environ.get('OPERATOR_EDIT_WEB_IMAGE', 'lirantal/daloradius')
BASE_COMMIT = 'aceb81eaddf77947aec6a07af879520e5850ea62'


def sql(query):
    # The harness's default error embeds the full statement, including test factors.
    try:
        return harness.sql(query)
    except RuntimeError:
        raise RuntimeError('Synthetic SQL operation failed (statement redacted)') from None


def main():
    scratch = Path.home() / '.hermes/cache/scratch'
    scratch.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='dalo-op-edit-', dir=scratch) as directory:
        fixture = Path(directory)
        shutil.copytree(ROOT / 'app', fixture / 'app', symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
        if BASELINE:
            restore_pear_bootstrap((fixture / 'app').parent, BASE_COMMIT)
            original = run('git', 'show', BASE_COMMIT + ':app/operators/config-operators-edit.php')
            (fixture / 'app/operators/config-operators-edit.php').write_text(original + '\n')
        try:
            run('docker', 'network', 'create', '--internal', NETWORK)
            run('docker', 'run', '-d', '--name', DB, '--network', NETWORK,
                '--tmpfs', '/var/lib/mysql', '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e', 'MARIADB_DATABASE=radius', 'mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'), 'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql', 'mariadb-daloradius.sql'):
                sql((ROOT / 'contrib/db' / name).read_text())
            sql("INSERT INTO operators_acl (operator_id,file,access) VALUES "
                "(9001,'config_operators_edit',1),(9002,'config_operators_edit',0)")
            # Both factors are synthetic and never printed or saved outside this tmpfs DB.
            synthetic_secret = secrets.token_hex(16)
            synthetic_recovery = secrets.token_hex(16)
            sql("INSERT INTO operators (username,firstname,lastname,title,department,company,"
                "phone1,phone2,email1,email2,messenger1,messenger2,notes,auth_source,"
                "totp_enabled,totp_secret,totp_last_counter,totp_confirmed_at,totp_recovery_codes) "
                "VALUES ('fixture-local','Before','Local','','','','','','','','','','Original','local',1,'"
                + synthetic_secret + "',42,'2025-01-01 00:00:00','" + synthetic_recovery + "'),"
                "('fixture-ldap','Before','Directory','','','','','','','','','','Original','ldap',0,NULL,NULL,NULL,NULL)")
            sql("INSERT INTO operators_acl (operator_id,file,access) VALUES "
                "((SELECT id FROM operators WHERE username='fixture-local'),'mng_edit',1),"
                "((SELECT id FROM operators WHERE username='fixture-ldap'),'mng_edit',0)")
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

            def request(data=None, username='fixture-local', authenticated=True):
                headers = {'Cookie': 'daloradius_operator_sid=' + sid} if authenticated else {}
                url = base + 'config-operators-edit.php'
                if data is None:
                    url += '?operator_username=' + urllib.parse.quote(username)
                req = urllib.request.Request(url,
                    data=urllib.parse.urlencode(data).encode() if data is not None else None,
                    headers=headers)
                try:
                    with urllib.request.urlopen(req, timeout=25) as res:
                        return res.status, res.url, res.read().decode()
                except urllib.error.HTTPError as err:
                    return err.code, err.url, err.read().decode()

            def form(username='fixture-local'):
                html = request(username=username)[2]
                matches = [f for f in Forms(html).forms if 'csrf_token' in f]
                assert len(matches) == 1, (username, 'edit form not displayed')
                return matches[0]

            def post(username='fixture-local', extra=None, fields=None):
                visible = dict(form(username) if fields is None else fields)
                data = {key: visible[key] for key in ('operator_username','identity_auth_source',
                    'identity_external_id','csrf_token') if key in visible}
                if 'identity_operator_id' in visible:
                    data['identity_operator_id'] = visible['identity_operator_id']
                data.update({'auth_source': visible['identity_auth_source'],
                             'external_id': visible['identity_external_id'],
                             'firstname':'Updated', 'lastname':'Person',
                             'notes':'Updated note', 'ACL_mng_edit':'0'})
                if extra:
                    data.update(extra)
                return request(data)[2]

            def state():
                return {'operator': sql("SELECT username,firstname,lastname,notes,auth_source,"
                    "COALESCE(external_id,'<none>'),password IS NULL,totp_enabled,"
                    "totp_secret IS NULL,totp_last_counter IS NULL,totp_confirmed_at IS NULL,"
                    "totp_recovery_codes IS NULL FROM operators WHERE username LIKE 'fixture-%' "
                    "ORDER BY username"),
                    'acl': sql("SELECT o.username,a.file,a.access FROM operators o "
                    "JOIN operators_acl a ON a.operator_id=o.id "
                    "WHERE o.username LIKE 'fixture-%' ORDER BY o.username,a.file,a.id")}

            set_session()
            if not BASELINE:
                try:
                    urllib.request.urlopen(base + 'library/operator_edit.php', timeout=10)
                    raise AssertionError('helper was served directly')
                except urllib.error.HTTPError as err:
                    assert err.code == 404
            assert request(authenticated=False)[1].endswith('/login.php')
            set_session(9002)
            assert request()[1].endswith('/home-error.php')
            set_session()
            before = state()
            assert 'Updated settings for' not in post(extra={'csrf_token':'invalid'})
            assert state() == before
            print('PASS: login, ACL and CSRF gates')

            assert 'Updated settings for' in post(extra={'firstname':"Anne O'Reilly",
                'lastname':'Étoile', 'ACL_mng_edit':'1', 'ACL_mng_search':'0'})
            assert sql("SELECT firstname,lastname,notes,auth_source,totp_enabled,"
                       "totp_secret IS NOT NULL,totp_recovery_codes IS NOT NULL "
                       "FROM operators WHERE username='fixture-local'") == \
                   "Anne O'Reilly\tÉtoile\tUpdated note\tlocal\t1\t1\t1"
            assert sql("SELECT file,access FROM operators_acl WHERE operator_id="
                       "(SELECT id FROM operators WHERE username='fixture-local') "
                       "AND file IN ('mng_edit','mng_search') ORDER BY file") == \
                   'mng_edit\t1\nmng_search\t0'
            assert 'Updated settings for' in post(extra={'firstname':"Anne O'Reilly",
                'lastname':'Étoile', 'ACL_mng_edit':'1'})
            print('PASS: profile update, ACL update/insert, MFA preserved, idempotent save')
            initial_hash = sql("SELECT COALESCE(SHA2(password,256),'<none>') FROM operators "
                               "WHERE username='fixture-local'")
            new_password = secrets.token_urlsafe(24)
            assert 'Updated settings for' in post(extra={'operator_password':new_password})
            stored_hash = sql("SELECT COALESCE(SHA2(password,256),'<none>') FROM operators "
                              "WHERE username='fixture-local'")
            assert stored_hash != initial_hash and stored_hash != '<none>'
            verifier = '''$p=new PDO("mysql:host=%s;dbname=radius;charset=utf8mb4","root","");
            $s=$p->prepare("SELECT password FROM operators WHERE username=?");
            $s->execute(array("fixture-local"));
            echo password_verify(stream_get_contents(STDIN),$s->fetchColumn()) ? "yes" : "no";''' % DB
            checked = subprocess.run(['docker','exec','-i',WEB,'php','-r',verifier],
                input=new_password,text=True,capture_output=True,timeout=30,check=True)
            assert checked.stdout.strip() == 'yes'
            assert 'Updated settings for' in post(extra={'operator_password':''})
            assert sql("SELECT SHA2(password,256) FROM operators WHERE username='fixture-local'") == stored_hash
            print('PASS: local password replacement verified, blank preserves hash')

            stale = form()
            prior = state()
            assert 'Updated settings for' not in post(extra={'auth_source':'ldap',
                'external_id':'fixture-directory-id', 'operator_password':''})
            assert state() == prior
            assert 'Updated settings for' in post(extra={'auth_source':'ldap',
                'external_id':'fixture-directory-id', 'confirm_auth_source_change':'1',
                'operator_password':''})
            assert sql("SELECT auth_source,external_id,password IS NULL,totp_enabled "
                       "FROM operators WHERE username='fixture-local'") == \
                   'ldap\tfixture-directory-id\t1\t1'
            converted = state()
            assert 'Updated settings for' not in post(fields=stale, extra={
                'auth_source':'local','operator_password':secrets.token_urlsafe(24),
                'confirm_auth_source_change':'1'})
            assert state() == converted
            assert 'Updated settings for' not in post(extra={'auth_source':'local',
                'confirm_auth_source_change':'1'})
            assert state() == converted
            assert 'Updated settings for' in post(extra={'auth_source':'local',
                'confirm_auth_source_change':'1', 'operator_password':secrets.token_urlsafe(24),
                'external_id':''})
            assert sql("SELECT auth_source,external_id IS NULL,password IS NOT NULL "
                       "FROM operators WHERE username='fixture-local'") == 'local\t1\t1'
            print('PASS: Local/LDAP conversions, confirmation, stale snapshot, password rules')

            assert 'Updated settings for' in post(extra={'reset_totp':'1'})
            assert sql("SELECT totp_enabled,totp_secret IS NULL,totp_last_counter IS NULL,"
                       "totp_confirmed_at IS NULL,totp_recovery_codes IS NULL "
                       "FROM operators WHERE username='fixture-local'") == '0\t1\t1\t1\t1'
            print('PASS: explicit MFA reset clears all factors')
            normal = state()
            print('NORMAL_STATE_SHA256=' + hashlib.sha256(
                json.dumps(normal,ensure_ascii=False,sort_keys=True).encode()).hexdigest())
            if not BASELINE:
                for bad in ({'ACL_mng_edit':'2'}, {'ACL_not_catalogued':'1'},
                            {'ACL_mng_edit[]':'1'}, {'firstname':'z'*33},
                            {'auth_source':'invalid'}, {'auth_source[]':'ldap'},
                            {'reset_totp[]':'1'}, {'operator_password[]':'value'},
                            {'identity_operator_id':'12345678'},
                            {'identity_auth_source[]':'ldap'}, {'external_id[]':'array'}):
                    unchanged = state()
                    page = post(extra=bad)
                    assert 'Updated settings for' not in page, bad
                    assert state() == unchanged, bad
                print('PASS: malformed controls, stale/replaced ID, unknown ACL reject atomically')
                sql("UPDATE operators SET external_id='fixture-collision' "
                    "WHERE username='fixture-ldap'")
                unchanged = state()
                page = post(extra={'auth_source':'ldap', 'external_id':'fixture-collision',
                                   'confirm_auth_source_change':'1'})
                assert 'Updated settings for' not in page and state() == unchanged
                sql("UPDATE operators SET external_id=NULL WHERE username='fixture-ldap'")
                print('PASS: external identity uniqueness failure leaves account unchanged')
                # A later ACL update failure must roll back earlier profile changes.
                sql("DELIMITER //\nCREATE TRIGGER block_acl_edit BEFORE UPDATE ON operators_acl "
                    "FOR EACH ROW BEGIN IF NEW.file='mng_edit' THEN SIGNAL SQLSTATE '45000' "
                    "SET MESSAGE_TEXT='fixture failure'; END IF; END//\nDELIMITER ;")
                try:
                    unchanged = state()
                    previous_hash = sql("SELECT SHA2(password,256) FROM operators "
                                        "WHERE username='fixture-local'")
                    page = post(extra={'firstname':'MustNotPersist','ACL_mng_edit':'1',
                                       'operator_password':secrets.token_urlsafe(24)})
                    assert 'Updated settings for' not in page and state() == unchanged
                    assert sql("SELECT SHA2(password,256) FROM operators "
                               "WHERE username='fixture-local'") == previous_hash
                finally:
                    sql('DROP TRIGGER block_acl_edit')
                sql("DELIMITER //\nCREATE TRIGGER block_new_acl BEFORE INSERT ON operators_acl "
                    "FOR EACH ROW BEGIN IF NEW.file='mng_search' THEN SIGNAL SQLSTATE '45000' "
                    "SET MESSAGE_TEXT='fixture failure'; END IF; END//\nDELIMITER ;")
                try:
                    unchanged = state()
                    # Use a second operator without an existing mng_search row.
                    page = post('fixture-ldap', {'firstname':'MustNotPersist',
                        'ACL_mng_edit':'1','ACL_mng_search':'0'})
                    assert 'Updated settings for' not in page and state() == unchanged
                finally:
                    sql('DROP TRIGGER block_new_acl')
                sql('ALTER TABLE operators_acl ENGINE=MyISAM')
                try:
                    unchanged = state()
                    assert 'Updated settings for' not in post(extra={'firstname':'NoEngine'})
                    assert state() == unchanged
                finally:
                    sql('ALTER TABLE operators_acl ENGINE=InnoDB')
                print('PASS: later ACL failures and nontransactional schema leave prior state intact')
                sql("UPDATE operators SET totp_enabled=1,totp_secret='" + synthetic_secret +
                    "',totp_last_counter=42,totp_confirmed_at='2025-01-01 00:00:00',"
                    "totp_recovery_codes='" + synthetic_recovery + "' "
                    "WHERE username='fixture-local'")
                sql("DELIMITER //\nCREATE TRIGGER block_factor_acl BEFORE UPDATE ON operators_acl "
                    "FOR EACH ROW BEGIN IF NEW.file='mng_edit' THEN SIGNAL SQLSTATE '45000' "
                    "SET MESSAGE_TEXT='fixture failure'; END IF; END//\nDELIMITER ;")
                try:
                    unchanged = state()
                    page = post(extra={'reset_totp':'1','firstname':'MustNotPersist',
                                       'ACL_mng_edit':'1'})
                    assert 'Updated settings for' not in page and state() == unchanged
                finally:
                    sql('DROP TRIGGER block_factor_acl')
                assert sql("SELECT totp_secret IS NOT NULL,totp_recovery_codes IS NOT NULL "
                           "FROM operators WHERE username='fixture-local'") == '1\t1'
                print('PASS: later ACL error also rolls back MFA reset')
                # Two independent PDO transactions compete for the same identity snapshot.
                (fixture / 'edit_race.php').write_text('''<?php
require '/fixtures/app/operators/library/operator_edit.php';
$p=new PDO('mysql:host=' . $argv[1] . ';dbname=radius;charset=utf8mb4','root','',
    array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false));
$p->exec("SET SESSION sql_mode=''");
$c=array('CONFIG_DB_TBL_DALOOPERATORS'=>'operators',
         'CONFIG_DB_TBL_DALOOPERATORS_ACL'=>'operators_acl',
         'CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES'=>'operators_acl_files');
$fields=array('identity_operator_id'=>$argv[2], 'identity_auth_source'=>'local',
    'identity_external_id'=>'', 'auth_source'=>'ldap', 'external_id'=>$argv[3],
    'confirm_auth_source_change'=>'1', 'ACL_mng_edit'=>'0');
try { dalo_operator_edit_apply($p,$c,'fixture-local',
      dalo_operator_edit_input($fields),'fixture-admin'); echo 'converted'; }
catch (DomainException $e) { echo 'stale'; }
catch (Throwable $e) { echo 'unexpected'; }
''')
                target = sql("SELECT id FROM operators WHERE username='fixture-local'")
                def competing_edit(which):
                    return run('docker','exec',WEB,'php','/fixtures/edit_race.php',DB,
                               target,'fixture-race-' + which)
                with ThreadPoolExecutor(max_workers=2) as pool:
                    outcomes = sorted(pool.map(competing_edit, ('a','b')))
                assert outcomes == ['converted','stale'], outcomes
                assert sql("SELECT auth_source,external_id FROM operators "
                           "WHERE username='fixture-local'") in (
                    'ldap\tfixture-race-a','ldap\tfixture-race-b')
                print('PASS: concurrent PDO identity changes serialize; one stale edit is rejected')
                sql("INSERT INTO operators (username,firstname,lastname,title,department,company,"
                    "phone1,phone2,email1,email2,messenger1,messenger2,notes,auth_source) "
                    "SELECT 'fixture&name',firstname,lastname,title,department,company,phone1,phone2,"
                    "email1,email2,messenger1,messenger2,notes,auth_source FROM operators "
                    "WHERE username='fixture-ldap' LIMIT 1")
                special = form('fixture&name')
                assert special['operator_username'] == 'fixture&name'
                assert 'Updated settings for' in post('fixture&name', {'firstname':'Literal'}, special)
                assert sql("SELECT firstname FROM operators WHERE username='fixture&name'") == 'Literal'
                print('PASS: special-character username round-trips through escaped hidden field')
                sql("INSERT INTO operators (username,firstname,lastname,title,department,company,"
                    "phone1,phone2,email1,email2,messenger1,messenger2,notes,auth_source) "
                    "SELECT username,firstname,lastname,title,department,company,phone1,phone2,"
                    "email1,email2,messenger1,messenger2,notes,auth_source FROM operators "
                    "WHERE username='fixture-ldap' LIMIT 1")
                assert 'csrf_token' not in request(username='fixture-ldap')[2] or not any(
                    'csrf_token' in f for f in Forms(request(username='fixture-ldap')[2]).forms)
                print('PASS: ambiguous duplicate username is not editable')
            result = subprocess.run(['docker', 'logs', WEB], capture_output=True, text=True, check=True)
            logs = result.stdout + result.stderr
            assert 'PHP Fatal error' not in logs and 'PHP Warning' not in logs, logs[-1300:]
        finally:
            for container in (WEB, DB):
                run('docker', 'rm', '-f', '-v', container, check=False)
            run('docker', 'network', 'rm', NETWORK, check=False)


if __name__ == '__main__':
    main()
