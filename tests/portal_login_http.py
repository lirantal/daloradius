#!/usr/bin/env python3
"""UNIT-030: differential user portal login over isolated HTTP/PHP/MariaDB."""
import os
import secrets
import shutil
import subprocess
import tempfile
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path

import operator_login_http as fixture_helpers
from operator_login_http import FormParser, NoRedirect, hash_password, quote, run, sql, wait_for

ROOT = Path(__file__).resolve().parents[1]
BASELINE = os.environ.get('PORTAL_LOGIN_BASELINE') == '1'
CHANGE_TEST = os.environ.get('PORTAL_CHANGE_TEST') == '1'
CHANGE_BASELINE = os.environ.get('PORTAL_CHANGE_BASELINE') == '1'
CHANGE_BASE_COMMIT = 'a9721356bb41b89d002dba2f143ac81b531b892d'
AUTH_TEST = os.environ.get('RADIUS_CHANGE_TEST') == '1'
AUTH_BASELINE = os.environ.get('RADIUS_CHANGE_BASELINE') == '1'
AUTH_BASE_COMMIT = '894e04924040f90c355982cf1eff5e3bcb51fd33'
BASE_COMMIT = '6eea7640feef13e69ec3b35e03e11c16e5d87ba4'
IMAGE = os.environ.get('PORTAL_LOGIN_WEB_IMAGE', 'lirantal/daloradius')
DB, WEB, NETWORK = fixture_helpers.DB, fixture_helpers.WEB, fixture_helpers.NETWORK


class Client:
    def __init__(self, base):
        self.base, self.sid = base, None
        self.opener = urllib.request.build_opener(NoRedirect())

    def request(self, path, fields=None):
        data = urllib.parse.urlencode(fields, doseq=True).encode() if fields is not None else None
        request = urllib.request.Request(self.base + path, data=data)
        if self.sid:
            request.add_header('Cookie', 'daloradius_user_sid=' + self.sid)
        try:
            response = self.opener.open(request, timeout=25)
        except urllib.error.HTTPError as error:
            response = error
        for cookie in response.headers.get_all('Set-Cookie', []):
            marker = 'daloradius_user_sid='
            if marker in cookie:
                value = cookie.split(marker, 1)[1].split(';', 1)[0]
                if value: self.sid = value
        return response.status, response.headers, response.read().decode('utf-8', 'replace')

    def csrf(self):
        status, _, body = self.request('login.php')
        assert status == 200
        form = FormParser(); form.feed(body)
        assert form.csrf
        self.last_csrf = form.csrf
        return form.csrf

    def login(self, username, password, language='en'):
        return self.request('dologin.php', [('csrf_token', self.csrf()),
            ('login_user', username), ('login_pass', password), ('language', language)])

    def probe(self):
        import json
        status, _, body = self.request('session_probe.php')
        assert status == 200
        return json.loads(body)


def portal_hash(password):
    return run('docker', 'exec', '-i', WEB, 'php', '-r',
        "require '/fixtures/app/common/includes/portal_password.php';"
        "echo dalo_portal_password_hash(stream_get_contents(STDIN));", input=password)


def verify_stored(username, password):
    code = '''
require '/fixtures/app/common/includes/portal_password.php';
$p=new PDO('mysql:host=' . $argv[1] . ';dbname=radius;charset=utf8mb4','root','');
$s=$p->prepare('SELECT portalloginpassword FROM userinfo WHERE username=?');
$s->execute([$argv[2]]); $h=$s->fetchColumn();
echo dalo_portal_password_verify(stream_get_contents(STDIN), $h)['verified'] ? 'yes' : 'no';
'''
    return run('docker','exec','-i',WEB,'php','-r',code,DB,username,input=password)


def add_user(username, password, enabled=1):
    sql('INSERT INTO userinfo (username,enableportallogin,portalloginpassword) VALUES (%s,%d,%s)' %
        (quote(username), enabled, 'NULL' if password is None else quote(password)))


def state():
    return sql('SELECT username,enableportallogin,portalloginpassword IS NULL,'
               'portalloginpassword IS NOT NULL AND portalloginpassword<>\'\' '
               'FROM userinfo ORDER BY username,id')


def change_password(client, current, new, confirmation=None, csrf=None):
    status, _, page = client.request('pref-portal-password-edit.php')
    assert status == 200, ('change page HTTP', status)
    form = FormParser(); form.feed(page)
    assert form.csrf
    fields = [('csrf_token', form.csrf if csrf is None else csrf),
              ('current_password', current), ('new_password1', new),
              ('new_password2', new if confirmation is None else confirmation)]
    return client.request('pref-portal-password-edit.php', fields)


def test_change(base, names, passwords):
    client = Client(base)
    assert client.login(names['modern'],passwords['modern'])[0] == 302
    assert client.probe()['logged_in']
    previous = passwords['modern']
    replacement = secrets.token_urlsafe(18)
    status,_,body = change_password(client, previous,replacement)
    assert status == 200 and 'has been changed' in body
    assert verify_stored(names['modern'],replacement) == 'yes'
    assert verify_stored(names['modern'],previous) == 'no'
    fresh = Client(base)
    assert fresh.login(names['modern'],previous)[0] == 302 and not fresh.probe()['logged_in']
    fresh = Client(base)
    assert fresh.login(names['modern'],replacement)[0] == 302 and fresh.probe()['logged_in']
    assert client.probe()['logged_in']
    print('PASS: authenticated portal password change persists a new hash')

    for current,new,confirm,fragment in (
        (previous,secrets.token_urlsafe(18),None,'correctly provide your current password'),
        (replacement,secrets.token_urlsafe(18),'different','should match'),
        (replacement,'',None,'empty or invalid')):
        status,_,body = change_password(client,current,new,confirm)
        assert status == 200 and fragment in body
        assert verify_stored(names['modern'],replacement) == 'yes'
    print('PASS: invalid current password, confirmation and empty replacement leave the hash intact')

    other = Client(base)
    assert other.login(names['special'],passwords['special'])[0] == 302
    special_new = secrets.token_urlsafe(18)
    status,_,body = change_password(other,passwords['special'],special_new)
    assert status == 200 and 'has been changed' in body
    assert verify_stored(names['special'],special_new) == 'yes'
    client = Client(base)
    assert client.login(names['zero'],'0')[0] == 302
    status,_,body = change_password(client,'0','0')
    assert status == 200 and 'has been changed' in body
    assert verify_stored(names['zero'],'0') == 'yes'
    print('PASS: quoted Unicode username and password zero')

    client = Client(base)
    assert client.login(names['modern'],replacement)[0] == 302
    status,_,body = change_password(client,replacement,secrets.token_urlsafe(18),csrf='invalid')
    assert status == 200 and 'CSRF token error' in body
    assert verify_stored(names['modern'],replacement) == 'yes'
    print('PASS: invalid CSRF cannot change a password')

    if CHANGE_BASELINE:
        return

    token_current = replacement
    before = state()
    for malformed in ([('current_password[]',token_current),('new_password1','new'),('new_password2','new')],
                      [('current_password',token_current),('new_password1[]','new'),('new_password2','new')],
                      [('csrf_token[]','bad'),('current_password',token_current),
                       ('new_password1','new'),('new_password2','new')]):
        status,_,page = client.request('pref-portal-password-edit.php')
        assert status == 200
        form = FormParser(); form.feed(page)
        fields = malformed if malformed[0][0] == 'csrf_token[]' else [('csrf_token',form.csrf)] + malformed
        status,_,body = client.request('pref-portal-password-edit.php',fields)
        assert status == 200 and 'has been changed' not in body and state() == before
    print('PASS: array-typed controls fail without a password mutation')

    sql("CREATE TRIGGER reject_portal_change BEFORE UPDATE ON userinfo FOR EACH ROW "
        "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture portal failure'")
    try:
        status,_,body = change_password(client,replacement,secrets.token_urlsafe(18))
        assert status == 200 and 'Something went wrong while attempting to change' in body
        assert 'SQLSTATE' not in body and 'fixture portal failure' not in body
        assert verify_stored(names['modern'],replacement) == 'yes'
    finally:
        sql('DROP TRIGGER reject_portal_change')
    print('PASS: database write error is generic and preserves the previous hash')

    code = '''
require '/fixtures/app/common/includes/portal_password.php';
$p=new PDO('mysql:host=' . $argv[1] . ';dbname=radius;charset=utf8mb4','root','');
$p->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$q=$p->prepare('SELECT id,portalloginpassword FROM userinfo WHERE username=?');
$q->execute([$argv[2]]);$row=$q->fetch(PDO::FETCH_ASSOC);
$other=new PDO('mysql:host=' . $argv[1] . ';dbname=radius;charset=utf8mb4','root','');
$winner=dalo_portal_password_hash(bin2hex(random_bytes(16)));
$loser=dalo_portal_password_hash(bin2hex(random_bytes(16)));
$other->prepare('UPDATE userinfo SET portalloginpassword=? WHERE id=?')->execute([$winner,$row['id']]);
$n=dalo_portal_password_update($p,'`userinfo`',$row,$argv[2],$loser);
$q->execute([$argv[2]]);$current=$q->fetch(PDO::FETCH_ASSOC);
echo $n===0 && hash_equals($winner,$current['portalloginpassword']) ? 'ok' : 'no';
'''
    assert run('docker','exec',WEB,'php','-r',code,DB,names['cas']) == 'ok'
    print('PASS: PDO bytewise compare-and-swap cannot overwrite a concurrent password reset')


def auth_password_page(client, current, new, confirmation=None, csrf=None):
    status,_,page = client.request('pref-auth-password-edit.php')
    assert status == 200
    form = FormParser(); form.feed(page)
    assert form.csrf
    fields = [('csrf_token', form.csrf if csrf is None else csrf),
              ('current_password', current), ('new_password1', new),
              ('new_password2', new if confirmation is None else confirmation)]
    return client.request('pref-auth-password-edit.php',fields)


def test_radius_change(base,names,passwords):
    import hashlib
    original = secrets.token_urlsafe(18)
    changed = secrets.token_urlsafe(18)
    another = secrets.token_urlsafe(18)
    crypt_hash = run('docker','exec','-i',WEB,'php','-r',
        "echo crypt(stream_get_contents(STDIN), '$6$' . bin2hex(random_bytes(8)) . '$');",
        input=original)
    types = [
        ('Cleartext-Password',original),('MD5-Password',hashlib.md5(original.encode()).hexdigest().upper()),
        ('SHA1-Password',hashlib.sha1(original.encode()).hexdigest()),
        ('SHA2-Password',hashlib.sha256(original.encode()).hexdigest()),
        ('User-Password',original),('Crypt-Password',crypt_hash),
        ('Cleartext-Password',another),
    ]
    for attr,value in types:
        sql('INSERT INTO radcheck (username,attribute,op,value) VALUES (%s,%s,%s,%s)' %
            (quote(names['modern']),quote(attr),quote(':='),quote(value)))
    sql('INSERT INTO radcheck (username,attribute,op,value) VALUES (%s,%s,%s,%s)' %
        (quote(names['modern']),quote('Cleartext-Password'),quote('='),quote(original)))
    sql('INSERT INTO radcheck (username,attribute,op,value) VALUES (%s,%s,%s,%s)' %
        (quote(names['special']),quote('Cleartext-Password'),quote(':='),quote(original)))
    sql('INSERT INTO radcheck (username,attribute,op,value) VALUES (%s,%s,%s,%s)' %
        (quote(names['missing']),quote('Cleartext-Password'),quote(':='),quote(original)))
    def values(user):
        return sql('SELECT id,attribute,op,value FROM radcheck WHERE username=%s ORDER BY id' % quote(user)).splitlines()
    before_modern = values(names['modern'])
    before_special = values(names['special'])
    before_other = values(names['missing'])
    client = Client(base)
    assert client.login(names['modern'],passwords['modern'])[0] == 302 and client.probe()['logged_in']
    status,_,body = auth_password_page(client,original,changed)
    assert status == 200 and '6 auth password(s) have been changed' in body
    if not AUTH_BASELINE:
        assert original not in body and changed not in body
    after_modern = values(names['modern'])
    after_special = values(names['special'])
    assert after_modern != before_modern and after_special == before_special and values(names['missing']) == before_other
    # Only expected values are compared; neither password nor hash is emitted.
    assert after_modern[0].split('\t')[-1] == changed
    assert after_modern[1].split('\t')[-1] == hashlib.md5(changed.encode()).hexdigest().upper()
    assert after_modern[2].split('\t')[-1] == hashlib.sha1(changed.encode()).hexdigest()
    assert after_modern[3].split('\t')[-1] == hashlib.sha256(changed.encode()).hexdigest()
    assert after_modern[4].split('\t')[-1] == changed
    assert after_modern[5].split('\t')[-1] != crypt_hash
    assert after_modern[6].split('\t')[-1] == another
    assert after_modern[7].split('\t')[-1] == original
    print('PASS: all six matching password attributes change; mismatched/op/other user remain')

    for current,new,confirm,fragment in (
        (original,secrets.token_urlsafe(18),None,'Something went wrong'),
        (changed,'',None,'empty or invalid'),
        (changed,secrets.token_urlsafe(18),'different','should match'),
        (changed,'0',None,'empty or invalid')):
        status,_,body = auth_password_page(client,current,new,confirm)
        assert status == 200 and fragment in body and values(names['modern']) == after_modern
    status,_,body = auth_password_page(client,changed,secrets.token_urlsafe(18),csrf='invalid')
    assert status == 200 and 'CSRF token error' in body and values(names['modern']) == after_modern
    print('PASS: wrong current, invalid replacement and invalid CSRF do not write')

    other = Client(base)
    assert other.login(names['special'],passwords['special'])[0] == 302
    status,_,body = auth_password_page(other,original,changed)
    assert status == 200 and '1 auth password(s) have been changed' in body
    assert values(names['special']) != before_special
    absent = Client(base)
    assert absent.login(names['zero'],'0')[0] == 302
    status,_,body = auth_password_page(absent,original,changed)
    assert status == 200 and 'auth password(s) have been changed' not in body
    print('PASS: quoted Unicode username and no matching password attributes')

    if AUTH_BASELINE:
        return
    for bad in (
        [('current_password[]',changed),('new_password1',another),('new_password2',another)],
        [('current_password',changed),('new_password1[]',another),('new_password2',another)],
        [('csrf_token[]','bad'),('current_password',changed),('new_password1',another),('new_password2',another)],
    ):
        status,_,page = client.request('pref-auth-password-edit.php')
        assert status == 200
        form = FormParser(); form.feed(page)
        fields = bad if bad[0][0] == 'csrf_token[]' else [('csrf_token',form.csrf)] + bad
        status,_,body = client.request('pref-auth-password-edit.php',fields)
        assert status == 200 and 'auth password(s) have been changed' not in body
        assert values(names['modern']) == after_modern
    print('PASS: malformed controls cannot mutate RADIUS credentials')

    sql("DELIMITER //\nCREATE TRIGGER reject_later_radius BEFORE UPDATE ON radcheck FOR EACH ROW "
        "BEGIN IF OLD.attribute='SHA1-Password' THEN "
        "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture later failure'; END IF; END//\n"
        "DELIMITER ;\n")
    try:
        status,_,body = auth_password_page(client,changed,another)
        assert status == 200 and 'Something went wrong' in body
        assert 'fixture later failure' not in body and 'SQLSTATE' not in body
        assert values(names['modern']) == after_modern
    finally:
        sql('DROP TRIGGER reject_later_radius')
    print('PASS: failure on later attribute rolls back all earlier password updates')

    sql('ALTER TABLE radcheck ENGINE=MyISAM')
    try:
        status,_,body = auth_password_page(client,changed,another)
        assert status == 200 and 'Something went wrong' in body
        assert values(names['modern']) == after_modern
    finally:
        sql('ALTER TABLE radcheck ENGINE=InnoDB')
    print('PASS: nontransactional table rejected without changing credentials')

    # The stock FreeRADIUS column is NOT NULL; emulate a legacy nullable schema.
    sql('ALTER TABLE radcheck MODIFY value varchar(253) NULL DEFAULT NULL')
    try:
        sql('INSERT INTO radcheck (username,attribute,op,value) VALUES (%s,%s,%s,NULL)' %
            (quote(names['zero']),quote('Cleartext-Password'),quote(':=')))
        before_null = values(names['zero'])
        status,_,body = auth_password_page(absent,original,another)
        assert status == 200 and 'Something went wrong' in body and values(names['zero']) == before_null
    finally:
        sql('DELETE FROM radcheck WHERE username=%s' % quote(names['zero']))
        sql("ALTER TABLE radcheck MODIFY value varchar(253) NOT NULL DEFAULT ''")
    print('PASS: NULL password attributes on a nullable legacy schema cannot authorize an update')

    from concurrent.futures import ThreadPoolExecutor
    from threading import Barrier
    sessions = [Client(base),Client(base)]
    for session in sessions:
        assert session.login(names['modern'],passwords['modern'])[0] == 302
    prepared = []
    for session in sessions:
        status,_,page = session.request('pref-auth-password-edit.php')
        assert status == 200
        form = FormParser(); form.feed(page)
        prepared.append(form.csrf)
    barrier = Barrier(2)
    replacements = [secrets.token_urlsafe(18),secrets.token_urlsafe(18)]
    def race(index):
        barrier.wait(timeout=10)
        fields = [('csrf_token',prepared[index]),('current_password',changed),
                  ('new_password1',replacements[index]),('new_password2',replacements[index])]
        return sessions[index].request('pref-auth-password-edit.php',fields)
    with ThreadPoolExecutor(max_workers=2) as pool:
        results = list(pool.map(race,range(2)))
    winners = [i for i,(status,_,body) in enumerate(results)
               if status == 200 and '6 auth password(s) have been changed' in body]
    assert len(winners) == 1 and all(status == 200 for status,_,_ in results)
    final = values(names['modern'])
    assert final[0].split('\t')[-1] == replacements[winners[0]]
    assert final[1].split('\t')[-1] == hashlib.md5(replacements[winners[0]].encode()).hexdigest().upper()
    assert final[2].split('\t')[-1] == hashlib.sha1(replacements[winners[0]].encode()).hexdigest()
    assert final[3].split('\t')[-1] == hashlib.sha256(replacements[winners[0]].encode()).hexdigest()
    assert final[4].split('\t')[-1] == replacements[winners[0]]
    assert final[6] == after_modern[6] and final[7] == after_modern[7]
    print('PASS: two concurrent sessions yield one complete password change')


def main():
    scratch = Path.home() / '.hermes/cache/scratch'; scratch.mkdir(parents=True, exist_ok=True)
    names = {k:'portal-' + k + '-' + secrets.token_hex(5)
             for k in ('modern','legacy','zero','missing','disabled','empty','duplicate','special','cas')}
    passwords = {k:secrets.token_urlsafe(18) for k in ('modern','legacy','disabled','duplicate','special','cas')}
    passwords['zero'] = '0'
    names['special'] = "é'" + secrets.token_hex(4)
    with tempfile.TemporaryDirectory(prefix='dalo-portal-login-', dir=scratch) as directory:
        root = Path(directory)
        shutil.copytree(ROOT / 'app', root / 'app', symlinks=True)
        if BASELINE:
            old = run('git','show',BASE_COMMIT + ':app/users/dologin.php')
            (root/'app/users/dologin.php').write_text(old + '\n')
        if CHANGE_BASELINE:
            old = run('git','show',CHANGE_BASE_COMMIT + ':app/users/pref-portal-password-edit.php')
            (root/'app/users/pref-portal-password-edit.php').write_text(old + '\n')
        if AUTH_BASELINE:
            old = run('git','show',AUTH_BASE_COMMIT + ':app/users/pref-auth-password-edit.php')
            (root/'app/users/pref-auth-password-edit.php').write_text(old + '\n')
        (root/'app/users/session_probe.php').write_text('''<?php
session_name('daloradius_user_sid');session_start();
echo json_encode(['logged_in'=>!empty($_SESSION['logged_in']),
'username'=>isset($_SESSION['login_user'])?(string)$_SESSION['login_user']:'']);
''')
        if not BASELINE:
            (root/'app/users/rehash_race.php').write_text('''<?php
define('DALORADIUS_PORTAL_LOGIN_TEST_ONLY',true);
chdir('/fixtures/app/users');require '/fixtures/app/users/dologin.php';
require_once '/fixtures/app/common/includes/pdo_connection.php';
try {
  $pdo=dalo_pdo_connect($configValues,'default');
  $table=dalo_portal_login_table($configValues);
  $q=$pdo->prepare("SELECT id,username,portalloginpassword FROM $table WHERE username=?");
  $q->execute([$argv[1]]);$row=$q->fetch(PDO::FETCH_ASSOC);
  if (!$row) { echo 'no'; exit; }
  $old=$row['portalloginpassword'];
  $replacement=dalo_portal_password_hash(bin2hex(random_bytes(16)));
  $candidate=dalo_portal_password_hash(bin2hex(random_bytes(16)));
  $other=dalo_pdo_connect($configValues,'default');
  $w=$other->prepare("UPDATE $table SET portalloginpassword=? WHERE id=?");
  $w->execute([$replacement,$row['id']]);
  $changed=dalo_portal_login_rehash($pdo,$table,$row,$old,$candidate);
  $recheck=dalo_portal_login_current($pdo,$table,$row,$old);
  $q->execute([$argv[1]]);$current=$q->fetch(PDO::FETCH_ASSOC);
  $other->prepare("UPDATE $table SET enableportallogin=0 WHERE id=?")->execute([$row['id']]);
  $locked=$current && !dalo_portal_login_current($pdo,$table,$current,$replacement);
  echo (!$changed && !$recheck && $locked && $current
      && hash_equals($replacement,$current['portalloginpassword'])) ? 'ok' : 'no';
} catch (Throwable $e) { echo 'no'; }
''')
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'), 'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            config = (ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,value in {'CONFIG_DB_ENGINE':'mysqli','CONFIG_DB_HOST':DB,'CONFIG_DB_PORT':'3306',
                              'CONFIG_DB_USER':'root','CONFIG_DB_PASS':'','CONFIG_DB_NAME':'radius',
                              'CONFIG_LOG_PAGES':'no','CONFIG_LOG_QUERIES':'no','CONFIG_LOG_ACTIONS':'no',
                              'CONFIG_DEBUG_SQL':'no',
                              'CONFIG_DEBUG_SQL_ONPAGE':'yes' if AUTH_TEST and not AUTH_BASELINE else 'no'}.items():
                config += '\n$configValues[' + quote(key) + '] = ' + quote(value) + ';\n'
            (root/'app/common/includes/daloradius.conf.php').write_text(config)
            web_workers = ['-e','PHP_CLI_SERVER_WORKERS=2'] if AUTH_TEST and not AUTH_BASELINE else []
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{root}:/fixtures','-w','/fixtures/app/users',
                *web_workers,'--entrypoint','php',IMAGE,'-d','display_errors=0',
                '-S','0.0.0.0:8080','-t','.')
            address = run('docker','inspect','-f',
                          '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base = 'http://' + address + ':8080/'
            wait_for(lambda: Client(base).request('login.php')[0] == 200,'portal HTTP')
            add_user(names['modern'], portal_hash(passwords['modern']))
            add_user(names['legacy'], passwords['legacy'])
            add_user(names['zero'], '0')
            add_user(names['disabled'], portal_hash(passwords['disabled']), enabled=0)
            add_user(names['empty'], '')
            add_user(names['duplicate'], portal_hash(passwords['duplicate']))
            add_user(names['duplicate'], portal_hash(passwords['duplicate']))
            add_user(names['special'], portal_hash(passwords['special']))
            add_user(names['cas'], portal_hash(passwords['cas']))

            client = Client(base)
            status, headers, _ = client.login(names['modern'],passwords['modern'])
            assert status == 302 and headers.get('Location','').endswith('index.php')
            assert client.probe() == {'logged_in':True,'username':names['modern']}
            status,headers,_ = client.request('dologin.php')
            assert status == 302 and headers.get('Location','').endswith('index.php')
            assert client.probe()['logged_in']
            status,headers,_ = client.request('dologin.php', [('csrf_token',client.last_csrf),
                ('login_user',names['modern']),('login_pass',secrets.token_urlsafe(18)),
                ('language','en')])
            assert status == 302 and headers.get('Location','').endswith('login.php')
            assert not client.probe()['logged_in']
            print('PASS: current hash login, non-authenticating request and failed retry session reset')

            for key in ('modern','disabled','empty','duplicate','missing'):
                client = Client(base)
                credential = secrets.token_urlsafe(18) if key == 'modern' else passwords.get(key,'not-used')
                status,headers,_ = client.login(names[key],credential)
                assert status == 302 and headers.get('Location','').endswith('login.php')
                assert client.probe()['logged_in'] is False
            print('PASS: wrong, missing, disabled, blank and duplicate accounts fail closed')

            client = Client(base)
            status,headers,_ = client.login(names['legacy'],passwords['legacy'])
            assert status == 302 and headers.get('Location','').endswith('index.php')
            assert verify_stored(names['legacy'],passwords['legacy']) == 'yes'
            assert sql('SELECT portalloginpassword LIKE %s FROM userinfo WHERE username=%s' %
                       (quote('$dalo$portal$v1$%'),quote(names['legacy']))) == '1'
            client = Client(base)
            status,headers,_ = client.login(names['zero'],'0')
            assert status == 302 and headers.get('Location','').endswith('index.php')
            assert verify_stored(names['zero'],'0') == 'yes'
            print('PASS: legacy plaintext and password zero rehash without exposing credentials')

            client = Client(base)
            status,headers,_ = client.login(names['special'],passwords['special'])
            assert status == 302 and headers.get('Location','').endswith('index.php')
            assert client.probe()['username'] == names['special']
            print('PASS: quote-bearing Unicode username is bound, not interpolated')

            if not BASELINE:
                original_state = state()
                for bad in ([('login_user[]',names['modern']),('login_pass',passwords['modern'])],
                            [('login_user',names['modern']),('login_pass[]',passwords['modern'])],
                            [('login_user',names['modern']),('login_pass',passwords['modern']),('language[]','en')]):
                    client = Client(base); token = client.csrf()
                    status,headers,_ = client.request('dologin.php',
                        [('csrf_token',token),('language','en')] + bad)
                    assert status == 302 and headers.get('Location','').endswith('login.php')
                    assert not client.probe()['logged_in'] and state() == original_state
                print('PASS: malformed fields fail closed without a database mutation')

                assert run('docker','exec',WEB,'php','/fixtures/app/users/rehash_race.php',names['cas']) == 'ok'
                print('PASS: old-hash compare-and-swap cannot overwrite a concurrent reset')

                add_user('portal-trigger-'+secrets.token_hex(4), portal_hash(secrets.token_urlsafe(18)))
                failure_user = 'portal-failure-'+secrets.token_hex(4)
                failure_password = secrets.token_urlsafe(18)
                add_user(failure_user, failure_password)
                before = state()
                sql("CREATE TRIGGER reject_portal_rehash BEFORE UPDATE ON userinfo FOR EACH ROW "
                    "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture portal error'")
                try:
                    client = Client(base)
                    status,headers,body = client.login(failure_user,failure_password)
                    assert status == 302 and headers.get('Location','').endswith('login.php')
                    assert not client.probe()['logged_in'] and state() == before
                    assert 'SQLSTATE' not in body and 'fixture portal error' not in body
                finally:
                    sql('DROP TRIGGER reject_portal_rehash')
                client = Client(base)
                status,headers,_ = client.login(failure_user,failure_password)
                assert status == 302 and headers.get('Location','').endswith('index.php')
                print('PASS: rehash database error cannot authenticate or expose SQL details')

                sql('ALTER TABLE userinfo ENGINE=MyISAM')
                try:
                    client = Client(base)
                    status,headers,_ = client.login(names['modern'],passwords['modern'])
                    assert status == 302 and headers.get('Location','').endswith('login.php')
                    assert not client.probe()['logged_in']
                finally:
                    sql('ALTER TABLE userinfo ENGINE=InnoDB')
                print('PASS: nontransactional table rejected before authentication')

            if CHANGE_TEST:
                test_change(base,names,passwords)
            if AUTH_TEST:
                test_radius_change(base,names,passwords)

            logs = subprocess.run(['docker','logs',WEB],text=True,capture_output=True,timeout=30,check=True)
            assert 'PHP Fatal error' not in logs.stdout + logs.stderr
            assert 'PHP Warning' not in logs.stdout + logs.stderr
            print('PASS: disposable web server has no PHP fatal errors or warnings')
        finally:
            # The PHP image may create root-owned HTMLPurifier cache in this
            # disposable fixture; return ownership before removing the tree.
            run('docker','exec','-u','root',WEB,'chown','-R',
                f'{os.getuid()}:{os.getgid()}','/fixtures',check=False)
            for container in (WEB,DB):run('docker','rm','-f','-v',container,check=False)
            run('docker','network','rm',NETWORK,check=False)
            assert not run('docker','ps','-aq','--filter','name=' + fixture_helpers.harness.PREFIX)
            print('CLEANUP: disposable portal database, containers, sessions and fixture data removed')

if __name__=='__main__':
    main()
