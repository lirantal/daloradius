#!/usr/bin/env python3
"""UNIT-016: isolated baseline/candidate HTTP backup and PDO snapshot tests."""
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
import user_actions_http as h
from acct_maintenance_http import Forms

ROOT = Path(__file__).resolve().parents[1]
BASELINE = os.environ.get('BACKUP_BASELINE') == '1'
DB, WEB, NETWORK = h.DB, h.WEB, h.NETWORK
run, sql, wait_for = h.run, h.sql, h.wait_for


def main():
    scratch = Path.home() / '.hermes/cache/scratch'
    scratch.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch, prefix='dalo-backup-') as tmp:
        fixture = Path(tmp)
        shutil.copytree(ROOT / 'app', fixture / 'app', symlinks=True)
        backupdir = fixture / 'data/backup'
        backupdir.mkdir(parents=True)
        if BASELINE:
            path = fixture / 'app/operators/config-backup-createbackups.php'
            path.write_bytes(subprocess.check_output(['git', 'show',
                             '2ad352af0:app/operators/config-backup-createbackups.php'], cwd=ROOT))
            path = fixture / 'app/operators/config-backup-managebackups.php'
            path.write_bytes(subprocess.check_output(['git', 'show',
                             '2ad352af0:app/operators/config-backup-managebackups.php'], cwd=ROOT))
        try:
            run('docker', 'network', 'create', '--internal', NETWORK)
            run('docker', 'run', '-d', '--name', DB, '--network', NETWORK,
                '--tmpfs', '/var/lib/mysql', '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e', 'MARIADB_DATABASE=radius', 'mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'), 'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql', 'mariadb-daloradius.sql'):
                sql((ROOT / 'contrib/db' / name).read_text())
            sql("""INSERT INTO operators_acl (operator_id,file,access) VALUES
                   (9001,'config_backup_createbackups',1),
                   (9002,'config_backup_createbackups',0),
                   (9001,'config_backup_managebackups',1);
                   INSERT INTO radcheck (username,attribute,op,value) VALUES
                   ('fixture-a','Cleartext-Password',':=','alpha'),
                   ('fixture-b','Cleartext-Password',':=','quote O''Reilly'),
                   ('fixture-c','Cleartext-Password',':=',CONCAT('slash',CHAR(92),'next',CHAR(10),'newline'));
                   INSERT INTO radreply (username,attribute,op,value) VALUES
                   ('fixture-a','Reply-Message',':=','welcome');
                   INSERT INTO userinfo (username,firstname) VALUES
                   ('fixture-a','Alice'),('fixture-b','Bob');""")
            config = (ROOT / 'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>', '')
            for key, value in {'CONFIG_DB_HOST': DB, 'CONFIG_DB_USER': 'root',
                               'CONFIG_DB_PASS': '', 'CONFIG_DB_NAME': 'radius',
                               'CONFIG_PATH_DALO_VARIABLE_DATA': '/fixtures/data'}.items():
                config += '\n$configValues[' + repr(key) + '] = ' + repr(value) + ';\n'
            configpath = fixture / 'app/common/includes/daloradius.conf.php'
            configpath.write_text(config)
            (fixture / 'session.php').write_text('''<?php
session_name('daloradius_operator_sid'); session_id($argv[1]); session_start();
$_SESSION = ['daloradius_logged_in'=>true,'operator_id'=>(int)$argv[2],
'operator_user'=>'backup-fixture','location_name'=>'default','time'=>time()];
session_write_close();
''')
            run('docker', 'run', '-d', '--name', WEB, '--network', NETWORK,
                '-v', f'{fixture}:/fixtures', '-w', '/fixtures/app/operators',
                '--entrypoint', 'php', 'lirantal/daloradius', '-d', 'display_errors=0',
                '-S', '0.0.0.0:8080', '-t', '.')
            ip = run('docker', 'inspect', '-f',
                     '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}', WEB)
            base = 'http://' + ip + ':8080/'
            wait_for(lambda: urllib.request.urlopen(base + 'config-backup-createbackups.php', timeout=10), 'PHP HTTP')
            sid = secrets.token_hex(16)

            def session(operator=9001):
                run('docker', 'exec', WEB, 'php', '/fixtures/session.php', sid, str(operator))

            def request(page, data=None, authenticated=True):
                req = urllib.request.Request(base + page,
                    data=None if data is None else urllib.parse.urlencode(data, doseq=True).encode(),
                    headers={'Cookie': 'daloradius_operator_sid=' + sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req, timeout=30) as response:
                        return response.url, response.read().decode()
                except urllib.error.HTTPError as error:
                    return error.url, error.read().decode()

            page = 'config-backup-createbackups.php'
            session()
            assert request(page, authenticated=False)[0].endswith('login.php')
            session(9002)
            assert request(page)[0].endswith('home-error.php')
            session()
            def csrf():
                return next(form['csrf_token'] for form in Forms(request(page)[1]).forms
                            if 'csrf_token' in form)
            token = csrf()
            selected = {'CONFIG_DB_TBL_RADCHECK': 'yes',
                        'CONFIG_DB_TBL_RADREPLY': 'yes',
                        'CONFIG_DB_TBL_RADGROUPREPLY': 'yes', # empty table is skipped
                        'CONFIG_DB_TBL_DALOUSERINFO': 'yes'}
            assert 'Successfully created backup' not in request(page,
                   {'csrf_token':'invalid', **selected})[1]
            assert not list(backupdir.iterdir())
            token = csrf()
            result = request(page, {'csrf_token': token, **selected})[1]
            assert 'Successfully created backup for 3 table(s)' in result, [
                x for x in result.splitlines() if any(y in x.lower() for y in
                ('fail', 'invalid', 'csrf', 'alert', 'success', 'error', 'no table'))][-18:]
            files = list(backupdir.glob('backup-*.sql'))
            assert len(files) == 1 and files[0].stat().st_size > 0, files
            name = files[0].name
            contents = run('docker', 'exec', WEB, 'php', '-r',
                           "echo file_get_contents('/fixtures/data/backup/"+name+"');")
            assert contents.count('INSERT INTO ') == 3, contents[:500]
            for table in ('radcheck', 'radreply', 'userinfo'):
                assert 'INSERT INTO `'+table+'`' in contents
            # A separate disposable copy can execute every generated INSERT.
            # Preserve quoted values and compare all copied rows to source.
            for table in ('radcheck', 'radreply', 'userinfo'):
                sql('CREATE TABLE `test_backup_'+table+'` LIKE `'+table+'`')
                inserts = [line for line in contents.split('\n\n\n')
                           if line.startswith('INSERT INTO `'+table+'`')]
                assert len(inserts) == 1
                sql(inserts[0].replace('INSERT INTO `'+table+'`',
                                      'INSERT INTO `test_backup_'+table+'`', 1))
                original = sql('SELECT * FROM `'+table+'` ORDER BY 1,2')
                restored = sql('SELECT * FROM `test_backup_'+table+'` ORDER BY 1,2')
                if BASELINE and table == 'userinfo':
                    # PEAR serializes NULL as '', an existing backup defect.
                    assert original != restored
                    assert sql('SELECT username,firstname FROM `'+table+'` ORDER BY 1,2') == sql(
                        'SELECT username,firstname FROM `test_backup_'+table+'` ORDER BY 1,2')
                else:
                    assert original == restored, table
            print('PASS HTTP login, ACL, CSRF; 3 exports import (PEAR NULL divergence)' if BASELINE
                  else 'PASS HTTP login, ACL, CSRF; 3 table exports import identically', file=sys.stderr)
            # Exercise the actual legacy rollback consumer, not just the SQL CLI.
            sql("UPDATE radcheck SET value='changed' WHERE username='fixture-a';"
                "UPDATE radreply SET value='changed' WHERE username='fixture-a';"
                "UPDATE userinfo SET firstname='Changed' WHERE username='fixture-a'")
            manage = 'config-backup-managebackups.php'
            manage_token = next(form['csrf_token'] for form in Forms(request(manage)[1]).forms
                                if 'csrf_token' in form)
            restore = request(manage, {'file':name, 'action':'rollback',
                                       'csrf_token':manage_token})[1]
            assert 'Successfully performed rollback' in restore, [
                x for x in restore.splitlines() if 'rollback' in x.lower()][-8:]
            assert 'alpha' in sql("SELECT value FROM radcheck WHERE username='fixture-a'")
            assert 'welcome' in sql("SELECT value FROM radreply WHERE username='fixture-a'")
            assert 'Alice' in sql("SELECT firstname FROM userinfo WHERE username='fixture-a'")
            if not BASELINE:
                assert sql("SELECT lastname IS NULL FROM userinfo WHERE username='fixture-a'") == '1'
            print('PASS HTTP backup manager rollback consumed published file',file=sys.stderr)
            if not BASELINE:
                assert files[0].stat().st_mode & 0o777 == 0o600
                previous = set(backupdir.iterdir())
                for data in ({}, {'CONFIG_DB_TBL_RADCHECK[]': 'yes'}):
                    invalid = request(page, {'csrf_token': csrf(), **data})[1]
                    assert 'Invalid backup selection or destination' in invalid
                    assert set(backupdir.iterdir()) == previous
                empty = request(page, {'csrf_token':csrf(),
                    'CONFIG_DB_TBL_RADGROUPREPLY':'yes'})[1]
                assert 'Failed creating backup' in empty
                assert set(backupdir.iterdir()) == previous
                sql('RENAME TABLE radreply TO fixture_radreply')
                result = request(page, {'csrf_token':csrf(), **selected})[1]
                sql('RENAME TABLE fixture_radreply TO radreply')
                assert 'Failed creating backup' in result and 'fixture_radreply' not in result, [
                    x for x in result.splitlines() if any(y in x.lower() for y in
                    ('fail', 'invalid', 'csrf', 'alert', 'success', 'error'))][-18:]
                assert set(backupdir.iterdir()) == previous
                staging = backupdir / '.backup-staging-test'
                staging.write_text('partial private data')
                listed = request('config-backup-managebackups.php')[1]
                assert name in listed and staging.name not in listed and 'partial private data' not in listed
                staging.unlink()
                # The callback writes via a distinct connection after the first table
                # was read; the second table must still reflect the first MVCC view.
                (fixture/'concurrency.php').write_text('''<?php
require '/fixtures/app/common/includes/daloradius.conf.php';
require '/fixtures/app/common/includes/pdo_connection.php';
require '/fixtures/app/operators/library/backup_snapshot.php';
$pdo=dalo_pdo_connect($configValues);
list($name)=dalo_create_backup_snapshot($pdo,['radcheck','radreply'],
    '/fixtures/data/backup',function($table) use ($configValues) {
        if ($table === 'radcheck') {
            $other=dalo_pdo_connect($configValues);
            $other->exec("INSERT INTO radreply (username,attribute,op,value) VALUES ('late-fixture','Reply-Message',':=','late')");
        }
    });
echo basename($name);
''')
                second = run('docker','exec',WEB,'php','/fixtures/concurrency.php')
                concurrent_dump = run('docker','exec',WEB,'php','-r',
                     "echo file_get_contents('/fixtures/data/backup/"+second+"');")
                assert 'late-fixture' not in concurrent_dump
                assert 'late-fixture' in sql('SELECT username FROM radreply')
                assert set(backupdir.glob('.backup-staging-*')) == set()
                sql('CREATE TABLE fixture_binary (id INT PRIMARY KEY,payload BLOB,note TEXT) ENGINE=InnoDB;'
                    "INSERT INTO fixture_binary VALUES (1,0x00FF5C27,NULL)")
                (fixture/'binary.php').write_text('''<?php
require '/fixtures/app/common/includes/daloradius.conf.php';
require '/fixtures/app/common/includes/pdo_connection.php';
require '/fixtures/app/operators/library/backup_snapshot.php';
list($name)=dalo_create_backup_snapshot(dalo_pdo_connect($configValues),
                                        ['fixture_binary'],'/fixtures/data/backup');
echo basename($name);
''')
                binary_name = run('docker','exec',WEB,'php','/fixtures/binary.php')
                binary_sql = run('docker','exec',WEB,'php','-r',
                    "echo file_get_contents('/fixtures/data/backup/"+binary_name+"');")
                sql('CREATE TABLE fixture_binary_copy LIKE fixture_binary;'+binary_sql.replace(
                    'INSERT INTO `fixture_binary`','INSERT INTO `fixture_binary_copy`',1))
                assert sql('SELECT HEX(payload),note IS NULL FROM fixture_binary') == sql(
                    'SELECT HEX(payload),note IS NULL FROM fixture_binary_copy')
                print('PASS binary and NULL fields survive snapshot/import',file=sys.stderr)
                print('PASS errors leave no partial file; restrictive mode; consistent MVCC snapshot', file=sys.stderr)
            logs = subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            assert 'PHP Fatal error' not in logs.stderr
            print(json.dumps({'tables': ['radcheck','radreply','userinfo'],
                              'inserts': contents.count('INSERT INTO ')}))
        finally:
            for name in (WEB,DB): run('docker','rm','-f','-v',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            run('docker','run','--rm','-v',f'{fixture}:/fixtures','--entrypoint','sh',
                'lirantal/daloradius','-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')

if __name__ == '__main__': main()
