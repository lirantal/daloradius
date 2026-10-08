#!/usr/bin/env python3
"""R01b MFA configuration: disposable native HTTP/PHP/MariaDB, no retained factors."""
import json
import os
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap
import re
import secrets
import shutil
import subprocess
import tempfile
from concurrent.futures import ThreadPoolExecutor
from html.parser import HTMLParser

import operator_login_http as harness
from operator_login_http import Client, quote, wait_for

ROOT = Path(__file__).resolve().parents[1]
BASE = 'a363ab69c8eda7a89dab0ba0155e292a236fa910'
BASELINE = os.environ.get('MFA_CONFIG_BASELINE') == '1'
DB, WEB, NETWORK = harness.DB, harness.WEB, harness.NETWORK
IMAGE = os.environ.get('MFA_CONFIG_WEB_IMAGE', 'lirantal/daloradius')


def run(*args, input=None, check=True, timeout=180):
    p = subprocess.run(args, input=input, text=True, capture_output=True, timeout=timeout)
    if check and p.returncode:
        raise RuntimeError('Isolated fixture command failed; sensitive details suppressed')
    return (p.stdout+p.stderr).strip() if args[:2]==('docker','logs') else p.stdout.strip()


def sql(query, database='radius'):
    return run('docker', 'exec', '-i', DB, 'mariadb', '-uroot', '-N', '-B', database, input=query)


class Form(HTMLParser):
    def __init__(self, body):
        super().__init__(); self.csrf = None; self.secret = None; self.pre = False; self.codes = []
        self.feed(body)

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == 'input' and attrs.get('name') == 'csrf_token':
            self.csrf = attrs.get('value')
        if tag == 'input' and 'readonly' in attrs and 'font-monospace' in attrs.get('class', ''):
            self.secret = attrs.get('value')
        if tag == 'pre': self.pre = True

    def handle_endtag(self, tag):
        if tag == 'pre': self.pre = False

    def handle_data(self, data):
        if self.pre:
            self.codes.extend(re.findall(r'(?m)^[A-F0-9]{5}-[A-F0-9]{5}$', data))


def php(code, value=None):
    return run('docker', 'exec', '-i', WEB, 'php', '-r', code,
               input=None if value is None else json.dumps(value))


def otp(secret):
    return php("require 'library/totp.php';echo dalo_totp_new()->getCode(json_decode(stream_get_contents(STDIN),true));", secret)


def main():
    scratch = Path.home() / '.hermes/cache/scratch'; scratch.mkdir(parents=True, exist_ok=True)
    actor = "mfa-O'Reilly%+É"
    password = secrets.token_urlsafe(24)
    factor_values = []
    with tempfile.TemporaryDirectory(prefix='dalo-mfa-config-', dir=scratch) as directory:
        fixture = Path(directory)
        shutil.copytree(ROOT / 'app', fixture / 'app', symlinks=True,
                        ignore=shutil.ignore_patterns('daloradius.conf.php'))
        if BASELINE:
            restore_pear_bootstrap((fixture / 'app').parent, BASE)
            (fixture / 'app/operators/config-operator-2fa.php').write_text(
                run('git', 'show', BASE + ':app/operators/config-operator-2fa.php') + '\n')
        else:
            (fixture / 'app/common/includes/db_open.php').write_text(
                '<?php throw new RuntimeException("Legacy provider tripwire");')
        (fixture / 'session.php').write_text('''<?php
session_name('daloradius_operator_sid'); $p=json_decode(stream_get_contents(STDIN),true);
session_id($p['sid']);session_start();
$_SESSION['daloradius_logged_in']=true;$_SESSION['operator_id']=$p['id'];
$_SESSION['operator_user']=$p['operator'];$_SESSION['location_name']=$p['location'];$_SESSION['time']=time();
if (!empty($p['clear'])) {unset($_SESSION['operator_totp_pending_secret'],$_SESSION['operator_totp_pending_context']);}
session_write_close();
''')
        (fixture / 'app/operators/log-channel.php').write_text("<?php error_log('FIXTURE_LOG_CHANNEL'); echo 'ok';")
        try:
            run('docker', 'network', 'create', '--internal', NETWORK)
            run('docker', 'run', '-d', '--name', DB, '--network', NETWORK,
                '--tmpfs', '/var/lib/mysql', '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e', 'MARIADB_DATABASE=radius', 'mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'), 'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql', 'mariadb-daloradius.sql'):
                sql((ROOT / 'contrib/db' / name).read_text())
            sql('CREATE TABLE mfa_custom LIKE operators; INSERT INTO mfa_custom SELECT * FROM operators WHERE id=1; '
                f'UPDATE mfa_custom SET id=9001,username={quote(actor)},totp_enabled=0,totp_secret=NULL,'
                "totp_last_counter=NULL,totp_confirmed_at=NULL,totp_recovery_codes=NULL; "
                "INSERT INTO operators_acl(operator_id,file,access) VALUES(9001,'config_operator_2fa',1),"
                "(9002,'config_operator_2fa',0); CREATE DATABASE radius_other; "
                'CREATE TABLE radius_other.mfa_custom LIKE mfa_custom; '
                'INSERT INTO radius_other.mfa_custom SELECT * FROM mfa_custom; '
                'CREATE TABLE radius_other.operators_acl LIKE operators_acl; '
                'INSERT INTO radius_other.operators_acl SELECT * FROM operators_acl;')
            config = (ROOT / 'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>', '')
            config += '''
$configValues['CONFIG_DB_HOST']=getenv('MFA_TEST_DB');
$configValues['CONFIG_DB_USER']=getenv('MFA_TEST_USER');$configValues['CONFIG_DB_PASS']='';
$configValues['CONFIG_DB_NAME']='radius';$configValues['CONFIG_DB_ENGINE']='mysqli';
$configValues['CONFIG_DB_TBL_DALOOPERATORS']='mfa_custom';
$configValues['CONFIG_LOG_PAGES']='no';$configValues['CONFIG_LOG_QUERIES']='no';
$configValues['CONFIG_LOG_ACTIONS']='no';$configValues['CONFIG_DEBUG_SQL']='no';
$configValues['CONFIG_DEBUG_SQL_ONPAGE']='yes';
$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>getenv('MFA_TEST_DB'),
'Username'=>getenv('MFA_TEST_USER'),'Password'=>'','Database'=>'radius_other','Port'=>'3306');
'''
            (fixture / 'app/common/includes/daloradius.conf.php').write_text(config)
            environment = os.environ.copy(); environment.update(MFA_TEST_DB=DB, MFA_TEST_USER='root')
            p = subprocess.run(['docker', 'run', '-d', '--name', WEB, '--network', NETWORK,
                '-e', 'MFA_TEST_DB', '-e', 'MFA_TEST_USER', '-e', 'PHP_CLI_SERVER_WORKERS=4',
                '-v', f'{fixture}:/fixtures', '-w', '/fixtures/app/operators', '--entrypoint', 'php',
                IMAGE, '-d', 'display_errors=0', '-d', 'opcache.enable=0', '-S', '0.0.0.0:8080', '-t', '.'],
                env=environment, text=True, capture_output=True, timeout=60)
            assert p.returncode == 0, 'PHP fixture startup failed'
            address = run('docker', 'inspect', '-f', '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}', WEB)
            origin = 'http://' + address + ':8080/'
            wait_for(lambda: Client(origin).request('login.php')[0] == 200, 'PHP HTTP')
            hashed = run('docker', 'exec', '-i', WEB, 'php', '-r',
                         'echo password_hash(stream_get_contents(STDIN), PASSWORD_DEFAULT);', input=password)
            sql('UPDATE mfa_custom SET password=' + quote(hashed))
            print('RUNTIME: PHP ' + run('docker', 'exec', WEB, 'php', '-r', 'echo PHP_VERSION;') + '; MariaDB ' + sql('SELECT VERSION()'))

            def authenticated(identity=9001, location='default', client=None, clear=True):
                client = client or Client(origin)
                client.sid = client.sid or secrets.token_hex(16)
                run('docker', 'exec', '-i', WEB, 'php', '/fixtures/session.php', input=json.dumps(
                    {'sid':client.sid, 'id':identity, 'operator':actor, 'location':location, 'clear':clear}))
                return client

            def get(client):
                status, _, _, body = client.request('config-operator-2fa.php')
                assert status == 200, 'MFA GET failed'
                form = Form(body); assert form.csrf, 'MFA form token absent'
                return form, body

            def post(client, action, extra=()):
                form, _ = get(client)
                return client.request('config-operator-2fa.php', [('action', action), ('csrf_token',form.csrf), *extra])

            def snapshot():
                sql('DROP TABLE IF EXISTS mfa_before; CREATE TABLE mfa_before LIKE mfa_custom; '
                    'INSERT INTO mfa_before SELECT * FROM mfa_custom')

            def unchanged():
                fields = ('username','totp_enabled','totp_secret','totp_last_counter','totp_confirmed_at','totp_recovery_codes','password')
                predicate = ' AND '.join(f'a.{f}<=>b.{f}' for f in fields)
                assert sql(f'SELECT COUNT(*)=(SELECT COUNT(*) FROM mfa_before) FROM mfa_custom a JOIN mfa_before b '
                           f'ON a.id=b.id AND ({predicate})') == '1', 'MFA state changed unexpectedly'

            def check_factors(secret, codes):
                data = php('''require 'library/totp.php';require '../common/includes/config_read.php';
require '../common/includes/pdo_connection.php';$p=dalo_pdo_connect($configValues);
$s=$p->query('SELECT totp_enabled,totp_secret,totp_last_counter,totp_confirmed_at,totp_recovery_codes FROM mfa_custom WHERE id=9001');
$r=$s->fetch(PDO::FETCH_ASSOC);$expected=json_decode(stream_get_contents(STDIN),true);
$ok=(int)$r['totp_enabled']===1 && hash_equals($r['totp_secret'],$expected['secret']) &&
$r['totp_confirmed_at']!==null && count(json_decode($r['totp_recovery_codes'],true))===count($expected['codes']);
foreach($expected['codes'] as $code) {$ok=$ok && dalo_totp_verify_recovery_code($r['totp_recovery_codes'],$code)[0];}
echo $ok?'yes':'no';''', {'secret':secret,'codes':codes})
                assert data == 'yes', 'Stored factors do not verify'

            client = authenticated()
            snapshot()
            assert Client(origin).request('config-operator-2fa.php')[0] == 302
            assert authenticated(9002).request('config-operator-2fa.php')[0] == 302
            form, _ = get(client)
            response = client.request('config-operator-2fa.php', [('action','start_enable'),('csrf_token','invalid')])
            assert 'CSRF token error' in response[3]; unchanged()
            assert 'setup cancelled' in post(client, 'cancel_enable')[3]
            assert 'Scan or enter' in post(client, 'start_enable')[3]
            form, body = get(client); assert form.secret
            secret = form.secret; factor_values.append(secret)
            snapshot()
            assert 'Invalid verification code' in post(client, 'confirm_enable', [('otp_code','not-a-code')])[3]
            unchanged()
            code = otp(secret); factor_values.append(code)
            response = post(client, 'confirm_enable', [('otp_code',code)])
            assert response[0] == 200 and 'has been enabled' in response[3]
            codes = Form(response[3]).codes; assert len(codes) == 8; factor_values.extend(codes)
            check_factors(secret, codes)
            assert sql('SELECT totp_last_counter IS NULL FROM mfa_custom WHERE id=9001') == '1'
            assert not get(client)[0].codes and not get(client)[0].secret
            # Baseline rendered factor-bearing SQL in debug output; candidate renders placeholders only.
            debug = response[3].split('Debugging SQL Queries:',1)[-1]
            if BASELINE:
                assert secret in debug and "totp_secret='" in debug and '$2y$' in debug
            else:
                assert secret not in debug and '$2y$' not in debug and 'totp_secret=:secret' in debug
            print('PASS: login/ACL/CSRF gates, start/cancel/invalid/confirm, native TOTP/recovery verification, one-time code display and SQL logging characterization')

            sql('UPDATE mfa_custom SET totp_last_counter=123 WHERE id=9001')
            response = post(client,'regenerate_recovery')
            regenerated = Form(response[3]).codes; assert len(regenerated) == 8; factor_values.extend(regenerated)
            check_factors(secret, regenerated)
            assert sql('SELECT totp_last_counter=123 FROM mfa_custom WHERE id=9001') == '1'
            assert 'has been disabled' in post(client,'disable')[3]
            assert sql('SELECT totp_enabled=0 AND totp_secret IS NULL AND totp_last_counter IS NULL '
                       'AND totp_confirmed_at IS NULL AND totp_recovery_codes IS NULL FROM mfa_custom WHERE id=9001') == '1'
            snapshot()
            response = post(client,'regenerate_recovery')
            if BASELINE:
                assert len(Form(response[3]).codes) == 8 and 'New recovery codes generated' in response[3]
            else:
                assert not Form(response[3]).codes and 'not enabled' in response[3]
            unchanged()
            print('PASS: regeneration preserves consumed counter, disable clears all factors; disabled-regeneration false success characterized')

            if not BASELINE:
                for fields in ([('action[]','start_enable')], [('action','start_enable'),('csrf_token[]','invalid')],
                               [('action','confirm_enable'),('otp_code[]','bad')]):
                    form, _ = get(client)
                    payload = list(fields)
                    if not any(key.startswith('csrf_token') for key,value in payload): payload.append(('csrf_token',form.csrf))
                    response = client.request('config-operator-2fa.php',payload)
                    assert response[0] == 200 and not Form(response[3]).codes
                    unchanged()
                post(client,'start_enable'); pending = get(client)[0].secret; assert pending
                factor_values.append(pending)
                sql("CREATE TRIGGER reject_mfa BEFORE UPDATE ON mfa_custom FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture failure'")
                try:
                    response = post(client,'confirm_enable',[('otp_code',otp(pending))])
                    assert 'Unable to update' in response[3] and not Form(response[3]).codes
                    assert 'SQLSTATE' not in response[3] and 'fixture failure' not in response[3]
                    unchanged()
                finally: sql('DROP TRIGGER reject_mfa')
                authenticated(location='other',client=client,clear=False)
                response = post(client,'confirm_enable',[('otp_code',otp(pending))])
                assert 'Invalid verification code' in response[3] and not Form(response[3]).codes
                assert sql('SELECT totp_enabled=0 FROM mfa_custom WHERE id=9001','radius_other') == '1'
                client=authenticated(client=client)
                sql("UPDATE mfa_custom SET username='replaced' WHERE id=9001")
                try:
                    assert 'Operator changed' in post(client,'disable')[3]
                finally: sql('UPDATE mfa_custom SET username='+quote(actor)+' WHERE id=9001')
                unchanged()
                sql('ALTER TABLE mfa_custom ENGINE=MyISAM')
                try:
                    response = post(client,'disable'); assert 'Unable to update' in response[3]
                    unchanged()
                finally: sql('ALTER TABLE mfa_custom ENGINE=InnoDB')
                post(client,'start_enable'); pending = get(client)[0].secret; factor_values.append(pending)
                sql('ALTER TABLE mfa_custom MODIFY totp_recovery_codes VARCHAR(64) NULL')
                try:
                    response=post(client,'confirm_enable',[('otp_code',otp(pending))])
                    assert not Form(response[3]).codes and 'Unable to update' in response[3]
                    unchanged()
                finally: sql('ALTER TABLE mfa_custom MODIFY totp_recovery_codes TEXT NULL')
                print('PASS: malformed controls, SQL error/rollback, backend-bound enrollment, replaced identity, MyISAM and insufficient factor storage fail without publishing codes')

                racers=[authenticated(),authenticated()]
                secrets_for=[]; fields_for=[]
                for racer in racers:
                    post(racer,'start_enable'); form,_=get(racer); assert form.secret
                    secrets_for.append(form.secret); factor_values.append(form.secret)
                    fields_for.append([('action','confirm_enable'),('csrf_token',form.csrf),('otp_code',otp(form.secret))])
                with ThreadPoolExecutor(2) as pool:
                    results=list(pool.map(lambda pair: pair[0].request('config-operator-2fa.php',pair[1]),zip(racers,fields_for)))
                winners=[i for i,r in enumerate(results) if 'has been enabled' in r[3]]
                assert len(winners)==1 and all(r[0]==200 for r in results)
                winner=winners[0]; final_codes=Form(results[winner][3]).codes; factor_values.extend(final_codes)
                check_factors(secrets_for[winner],final_codes)
                assert not Form(results[1-winner][3]).codes
                print('PASS: two independent enrollment sessions/workers cannot replace an enabled factor; only the committed winner publishes recovery codes')

            assert client.request('log-channel.php')[0] == 200
            logs=run('docker','logs',WEB)
            assert 'FIXTURE_LOG_CHANNEL' in logs, 'PHP stderr log channel was not captured'
            assert 'PHP Fatal error' not in logs and 'PHP Warning' not in logs
            assert all(value not in logs for value in factor_values)
            print('PASS: native PHP logs contain no factors, raw hashes, fatal errors or warnings')
        finally:
            # Only the disposable copied app can have root-owned HTMLPurifier cache files.
            run('docker','exec',WEB,'chown','-R',f'{os.getuid()}:{os.getgid()}','/fixtures',check=False)
            for name in (WEB,DB): run('docker','rm','-f',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            for name in (WEB,DB):
                assert not run('docker','ps','-a','-q','--filter','name=^/'+name+'$'), 'Fixture container remained'
            assert not run('docker','network','ls','-q','--filter','name=^'+NETWORK+'$'), 'Fixture network remained'
    print('PASS: disposable database/config/session/factor resources removed; live lab untouched')


if __name__=='__main__': main()
