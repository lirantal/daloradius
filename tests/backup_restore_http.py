#!/usr/bin/env python3
"""UNIT-017: isolated HTTP/PHP/MariaDB restore parity and atomic failure."""
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

ROOT=Path(__file__).resolve().parents[1]
BASELINE=os.environ.get('BACKUP_RESTORE_BASELINE')=='1'
DB,WEB,NETWORK=h.DB,h.WEB,h.NETWORK
run,sql,wait_for=h.run,h.sql,h.wait_for


def main():
    scratch=Path.home()/'.hermes/cache/scratch'
    scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch,prefix='dalo-restore-') as tmp:
        fixture=Path(tmp)
        shutil.copytree(ROOT/'app',fixture/'app',symlinks=True)
        backupdir=fixture/'data/backup'
        backupdir.mkdir(parents=True)
        if BASELINE:
            manager=fixture/'app/operators/config-backup-managebackups.php'
            manager.write_bytes(subprocess.check_output(['git','show',
                '876cca1a8:app/operators/config-backup-managebackups.php'],cwd=ROOT))
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,
                '--tmpfs','/var/lib/mysql','-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            sql("""INSERT INTO operators_acl (operator_id,file,access) VALUES
                   (9001,'config_backup_createbackups',1),
                   (9001,'config_backup_managebackups',1),
                   (9002,'config_backup_managebackups',0);
                   INSERT INTO radcheck (username,attribute,op,value) VALUES
                     ('fixture-a','Cleartext-Password',':=','one'),
                     ('fixture-b','Cleartext-Password',':=',CONCAT('slash',CHAR(92),'apostrophe ',CHAR(39),' newline',CHAR(10),'end'));
                   INSERT INTO radreply (username,attribute,op,value) VALUES
                     ('fixture-a','Reply-Message',':=','ok');
                   INSERT INTO userinfo (username,firstname) VALUES
                     ('fixture-a','Alice');""")
            config=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,val in {'CONFIG_DB_HOST':DB,'CONFIG_DB_USER':'root','CONFIG_DB_PASS':'',
                            'CONFIG_DB_NAME':'radius',
                            'CONFIG_PATH_DALO_VARIABLE_DATA':'/fixtures/data',
                            'CONFIG_DB_TBL_DALONODE':'fixture_binary'}.items():
                config+='\n$configValues['+repr(key)+'] = '+repr(val)+';\n'
            (fixture/'app/common/includes/daloradius.conf.php').write_text(config)
            (fixture/'session.php').write_text('''<?php
session_name('daloradius_operator_sid');session_id($argv[1]);session_start();
$_SESSION=['daloradius_logged_in'=>true,'operator_id'=>(int)$argv[2],
'operator_user'=>'restore-fixture','location_name'=>'default','time'=>time()];
session_write_close();
''')
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{fixture}:/fixtures','-w','/fixtures/app/operators',
                '--entrypoint','php','lirantal/daloradius','-d','display_errors=0',
                '-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f',
                '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/'
            wait_for(lambda:urllib.request.urlopen(base+'config-backup-createbackups.php',timeout=10),'PHP HTTP')
            sid=secrets.token_hex(16)
            def session(op=9001):
                run('docker','exec',WEB,'php','/fixtures/session.php',sid,str(op))
            def request(page,data=None,authenticated=True):
                req=urllib.request.Request(base+page,
                    data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),
                    headers={'Cookie':'daloradius_operator_sid='+sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req,timeout=30) as response:
                        return response.url,response.read().decode()
                except urllib.error.HTTPError as error:
                    return error.url,error.read().decode()
            create='config-backup-createbackups.php'
            manage='config-backup-managebackups.php'
            def csrf(page):
                return next(f['csrf_token'] for f in Forms(request(page)[1]).forms
                            if 'csrf_token' in f)
            def restore(file,token=None):
                return request(manage,{'file':file,'action':'rollback',
                        'csrf_token':csrf(manage) if token is None else token})[1]
            def state():
                return {name:sql(query) for name,query in {
                    'check':'SELECT id,username,attribute,op,value FROM radcheck ORDER BY id',
                    'reply':'SELECT id,username,attribute,op,value FROM radreply ORDER BY id',
                    'userinfo':'SELECT id,username,firstname,lastname FROM userinfo ORDER BY id',
                }.items()}
            session()
            created=request(create,{'csrf_token':csrf(create),
                                    'CONFIG_DB_TBL_RADCHECK':'yes',
                                    'CONFIG_DB_TBL_RADREPLY':'yes',
                                    'CONFIG_DB_TBL_DALOUSERINFO':'yes'})[1]
            assert 'Successfully created backup for 3 table(s)' in created
            files=list(backupdir.glob('backup-*.sql'))
            assert len(files)==1,files
            name=files[0].name
            original=state()
            sql("UPDATE radcheck SET value='changed' WHERE username='fixture-a';"
                "UPDATE radreply SET value='changed' WHERE username='fixture-a';"
                "UPDATE userinfo SET firstname='Changed' WHERE username='fixture-a'")
            changed=state()
            assert changed!=original
            assert request(manage,authenticated=False)[0].endswith('login.php')
            session(9002)
            assert request(manage)[0].endswith('home-error.php')
            assert request(manage,{'file':name,'action':'rollback','csrf_token':'invalid'})[0].endswith('home-error.php')
            session()
            assert 'Successfully performed rollback' not in restore(name,token='invalid')
            assert state()==changed
            success=restore(name)
            assert 'Successfully performed rollback of table(s)' in success,[
                x for x in success.splitlines() if 'rollback' in x.lower()][-8:]
            assert state()==original,(state(),original)
            print('PASS HTTP restore: auth, ACL, CSRF, matching three-table state',file=sys.stderr)
            if not BASELINE:
                def unchanged_failure(file,phrase='Cannot rollback'):
                    before=state()
                    response=restore(file)
                    assert phrase in response,[x for x in response.splitlines() if 'backup' in x.lower()][-5:]
                    assert state()==before
                # A valid first statement MUST NOT run if any later statement is bad.
                safe="INSERT INTO `radcheck` (`id`,`username`,`attribute`,`op`,`value`) VALUES (999,'unexpected','X',':=','no');\n\n\n"
                def inject(data,filename):
                    (backupdir/filename).write_text(data)
                    unchanged_failure(filename)
                    (backupdir/filename).unlink()
                inject(safe+"DELETE FROM `radreply`;",'backup-20260101-010101.sql')
                inject(safe+"INSERT INTO `not_allowed` (`id`) VALUES ('1');",'backup-20260101-010102.sql')
                inject(safe+safe,'backup-20260101-010103.sql')
                inject(safe+"INSERT INTO `radreply` (`id`) VALUES ('1');",'backup-20260101-010104.sql')
                assert 'Successfully performed rollback' not in restore('../'+name)
                assert state()==original
                alias=backupdir/'backup-20260101-010106.sql'
                alias.symlink_to(name)
                unchanged_failure(alias.name,phrase='The requested action cannot be performed')
                alias.unlink()
                print('PASS complete preflight rejects trailing SQL, unknown/duplicate tables and schema mismatch',file=sys.stderr)
                sql('ALTER TABLE radreply ENGINE=MyISAM')
                unchanged_failure(name)
                sql('ALTER TABLE radreply ENGINE=InnoDB')
                # The first table changes, the second raises on INSERT. Both
                # the first table and the second DELETE must roll back.
                sql("UPDATE radcheck SET value='changed again' WHERE username='fixture-a';"
                    "UPDATE radreply SET value='changed again' WHERE username='fixture-a';"
                    "CREATE TRIGGER fixture_restore_fail BEFORE INSERT ON radreply FOR EACH ROW "
                    "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='isolated failure'")
                before=state()
                unchanged_failure(name)
                assert state()==before
                sql('DROP TRIGGER fixture_restore_fail')
                assert 'Successfully performed rollback' in restore(name)
                assert state()==original
                oldname='backup-20260101-010105.sql'
                run('docker','exec',WEB,'php','-r',
                    "copy('/fixtures/data/backup/"+name+"','/fixtures/data/backup/"+oldname+"');")
                assert 'Successfully performed rollback' in restore(oldname)
                assert state()==original
                sql('CREATE TABLE fixture_binary (id INT PRIMARY KEY,payload BLOB,note TEXT) ENGINE=InnoDB;'
                    "INSERT INTO fixture_binary VALUES (1,0x00FF5C27,NULL)")
                binary=request(create,{'csrf_token':csrf(create),
                                       'CONFIG_DB_TBL_DALONODE':'yes'})[1]
                assert 'Successfully created backup for 1 table(s) [fixture_binary]' in binary
                binary_names={f.name for f in backupdir.glob('backup-*.sql')}-{name,oldname}
                assert len(binary_names)==1,binary_names
                sql("UPDATE fixture_binary SET payload=0xFFFF,note='changed'")
                assert 'Successfully performed rollback' in restore(binary_names.pop())
                assert sql('SELECT HEX(payload),note IS NULL FROM fixture_binary') == '00FF5C27\t1'
                print('PASS later insert failure rolls back prior table; legacy/new filenames; binary NULL',file=sys.stderr)
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            assert 'PHP Fatal error' not in logs.stderr
            print('PASS UNIT-017 '+('PEAR baseline' if BASELINE else 'PDO candidate'))
        finally:
            for item in (WEB,DB):run('docker','rm','-f','-v',item,check=False)
            run('docker','network','rm',NETWORK,check=False)
            run('docker','run','--rm','-v',f'{fixture}:/fixtures','--entrypoint','sh',
                'lirantal/daloradius','-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')

if __name__=='__main__':main()
