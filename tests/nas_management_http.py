#!/usr/bin/env python3
"""UNIT-034 NAS CRUD differential test on disposable HTTP/PHP/MariaDB."""
import os
import secrets
import shutil
import subprocess
import tempfile
import time
import urllib.parse
from pathlib import Path

import operator_login_http as auth
from operator_login_http import Client, FormParser, hash_password, login, quote, run, sql, wait_for

ROOT=Path(__file__).resolve().parents[1]
BASELINE=os.environ.get('NAS_MANAGEMENT_BASELINE')=='1'
COMMIT='ebc21cd51ce43f83110090e502e996aebbec36cd'
DB,WEB,NETWORK=auth.DB,auth.WEB,auth.NETWORK


def form(client,page):
    status,_,_,html=client.request(page)
    assert status==200,(page,status)
    parser=FormParser();parser.feed(html);assert parser.csrf,(page,html[:150])
    return parser.csrf


def post(client,page,fields):
    status,_,_,html=client.request(page,fields)
    assert status==200,(page,status)
    return html


def state():
    # Keep secret bytes out of snapshots, output and logs.
    return sql('SELECT id,HEX(nasname),HEX(shortname),HEX(type),ports,LENGTH(secret),'
               'HEX(server),HEX(community),HEX(description) FROM nas ORDER BY id')


def create(client,name,secret,**kwargs):
    csrf=form(client,'mng-rad-nas-new.php')
    fields={'csrf_token':csrf,'nasname':name,'secret':secret,'nastype':'other',
            'shortname':'nas-short','ports':'4','server':'','community':'',
            'description':'test NAS'}
    fields.update(kwargs)
    return post(client,'mng-rad-nas-new.php',list(fields.items()))


def edit(client,name,secret,**kwargs):
    csrf=form(client,'mng-rad-nas-edit.php?nasname='+urllib.parse.quote(name))
    fields={'csrf_token':csrf,'nasname':name,'secret':secret,'type':'other',
            'shortname':'updated','ports':'8','server':'server',
            'community':'snmp','description':'updated NAS'}
    fields.update(kwargs)
    return post(client,'mng-rad-nas-edit.php',list(fields.items()))


def delete(client,names):
    csrf=form(client,'mng-rad-nas-del.php')
    return post(client,'mng-rad-nas-del.php',[('csrf_token',csrf)]+[
        ('nasname[]',name) for name in names])


def count(name):
    return sql('SELECT COUNT(*) FROM nas WHERE HEX(nasname)=%s' % quote(name.encode().hex().upper()))


def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    operator='nas-crud-'+secrets.token_hex(5);password=secrets.token_urlsafe(24)
    secret1=secrets.token_urlsafe(20);secret2=secrets.token_urlsafe(20)
    first="nas-'é-"+secrets.token_hex(3)
    second='nas-second-'+secrets.token_hex(3)
    with tempfile.TemporaryDirectory(prefix='dalo-nas-crud-',dir=scratch) as directory:
        root=Path(directory);shutil.copytree(ROOT/'app',root/'app',symlinks=True)
        if BASELINE:
            for filename in ('mng-rad-nas-new.php','mng-rad-nas-edit.php','mng-rad-nas-del.php'):
                old=run('git','show',COMMIT+':app/operators/'+filename)
                (root/'app/operators'/filename).write_text(old+'\n')
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            for filename in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/filename).read_text())
            config=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,value in {'CONFIG_DB_ENGINE':'mysqli','CONFIG_DB_HOST':DB,'CONFIG_DB_PORT':'3306',
                'CONFIG_DB_USER':'root','CONFIG_DB_PASS':'','CONFIG_DB_NAME':'radius',
                'CONFIG_LOG_PAGES':'no','CONFIG_LOG_QUERIES':'no','CONFIG_LOG_ACTIONS':'no',
                'CONFIG_DEBUG_SQL':'no','CONFIG_DEBUG_SQL_ONPAGE':'no'}.items():
                config+='\n$configValues['+quote(key)+'] = '+quote(value)+';\n'
            (root/'app/common/includes/daloradius.conf.php').write_text(config)
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{root}:/fixtures','-w','/fixtures/app/operators',
                '-e','PHP_CLI_SERVER_WORKERS=2','--entrypoint','php','lirantal/daloradius',
                '-d','display_errors=0','-S','0.0.0.0:8080','-t','.')
            address=run('docker','inspect','-f',
                '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+address+':8080/'
            wait_for(lambda:Client(base).request('login.php')[0]==200,'operators HTTP')
            auth.add_operator(operator,hash_password(WEB,password))
            oid=sql('SELECT id FROM operators WHERE username=%s' % quote(operator))
            for perm in ('mng_rad_nas_list','mng_rad_nas_new','mng_rad_nas_edit','mng_rad_nas_del'):
                sql('INSERT INTO operators_acl (operator_id,file,access) VALUES (%s,%s,1)' %
                    (oid,quote(perm)))
            client=Client(base)
            status,_,headers,_=login(client,operator,password,'local','default')
            assert status==302 and headers.get('Location','').endswith('index.php')
            page=create(client,first,secret1)
            assert 'Successfully added a new NAS' in page and count(first)=='1'
            assert sql('SELECT secret=%s FROM nas WHERE nasname=%s' %
                       (quote(secret1),quote(first)))=='1'
            checkpoint_create=state()
            page=create(client,first,secret2)
            assert 'already exists' in page and state()==checkpoint_create
            page=edit(client,first,secret2)
            assert 'Edited NAS' in page
            assert sql('SELECT secret=%s,shortname,ports FROM nas WHERE nasname=%s' %
                       (quote(secret2),quote(first)))=='1\tupdated\t8'
            page=create(client,second,secret1)
            assert 'Successfully added a new NAS' in page
            page=delete(client,[first,second])
            assert 'Successfully deleted 2 NAS devices' in page and count(first)=='0' and count(second)=='0'
            assert all(secret not in page for secret in (secret1,secret2))
            print('PASS: create/duplicate/edit/multi-delete with SQL state and secret redaction')
            legacy='nas-legacy-'+secrets.token_hex(3)
            sql('INSERT INTO nas (nasname,secret,type) VALUES (%s,%s,%s)' %
                (quote(legacy),quote(secret1),quote('legacy-custom')))
            page=edit(client,legacy,secret1,type='legacy-custom')
            assert 'Edited NAS' in page
            page=edit(client,legacy,secret1,type='legacy-custom')
            assert 'Edited NAS' in page
            assert sql('SELECT type FROM nas WHERE nasname=%s' % quote(legacy))=='legacy-custom'
            page=delete(client,[legacy]);assert count(legacy)=='0'
            print('PASS: legacy NAS type preserved and repeated no-op edit succeeds')

            if not BASELINE:
                # A stale later selection must not delete an earlier valid NAS.
                page=create(client,first,secret1)
                assert 'Successfully added' in page
                before=state(); page=delete(client,[first,'missing-'+secrets.token_hex(4)])
                assert 'nothing was deleted' in page and state()==before
                print('PASS: stale later selection rejected before any deletion')

                page=create(client,second,secret2)
                assert 'Successfully added' in page
                before=state()
                sql("DELIMITER //\nCREATE TRIGGER reject_nas_delete BEFORE DELETE ON nas FOR EACH ROW "
                    "BEGIN IF OLD.nasname="+quote(second)+" THEN SIGNAL SQLSTATE '45000' "
                    "SET MESSAGE_TEXT='fixture late delete failure'; END IF; END//\nDELIMITER ;\n")
                try:
                    page=delete(client,[first,second])
                    assert 'Unable to delete NAS' in page and state()==before
                    assert 'fixture late delete failure' not in page
                finally:sql('DROP TRIGGER reject_nas_delete')
                print('PASS: late delete failure rolls back earlier deletion')

                before=state()
                sql("DELIMITER //\nCREATE TRIGGER reject_nas_edit BEFORE UPDATE ON nas FOR EACH ROW "
                    "BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture edit failure'; END//\nDELIMITER ;\n")
                try:
                    page=edit(client,first,secret2)
                    assert 'Unable to edit NAS' in page and state()==before
                    assert 'fixture edit failure' not in page
                finally:sql('DROP TRIGGER reject_nas_edit')
                print('PASS: failed edit retains all fields and hides driver details')

                before=state()
                sql("DELIMITER //\nCREATE TRIGGER reject_nas_create BEFORE INSERT ON nas FOR EACH ROW "
                    "BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture create failure'; END//\nDELIMITER ;\n")
                try:
                    page=create(client,'nas-error-'+secrets.token_hex(3),secret1)
                    assert 'Unable to add NAS' in page and state()==before
                    assert 'fixture create failure' not in page and secret1 not in page
                finally:sql('DROP TRIGGER reject_nas_create')
                print('PASS: failed insert leaves data unchanged without leaking driver/secret')

                percent='nas-%-'+secrets.token_hex(3)
                page=create(client,percent,secret1)
                assert 'Successfully added' in page and count(percent)=='1'
                assert count(percent.replace('%',''))=='0'
                page=edit(client,percent,secret2)
                assert 'Edited NAS' in page
                page=delete(client,[percent]);assert 'Successfully deleted 1' in page and count(percent)=='0'
                print('PASS: literal percent survives create, edit and delete')

                # A malformed scalar must fail before any database mutation.
                before=state(); csrf=form(client,'mng-rad-nas-new.php')
                page=post(client,'mng-rad-nas-new.php',[
                    ('csrf_token',csrf),('nasname[]',first),('secret',secret1)])
                assert 'NAS fields are empty or invalid' in page and state()==before
                csrf=form(client,'mng-rad-nas-new.php')
                page=post(client,'mng-rad-nas-new.php',[
                    ('csrf_token',csrf),('nasname','nas-too-long-'+secrets.token_hex(3)),
                    ('secret',secrets.token_urlsafe(100))])
                assert 'NAS fields are empty or invalid' in page and state()==before
                csrf=form(client,'mng-rad-nas-edit.php?nasname='+urllib.parse.quote(first))
                page=post(client,'mng-rad-nas-edit.php',[
                    ('csrf_token',csrf),('nasname',first),('secret[]',secret1)])
                assert 'NAS fields or hostname are invalid' in page and state()==before
                csrf=form(client,'mng-rad-nas-del.php')
                page=post(client,'mng-rad-nas-del.php',[
                    ('csrf_token',csrf),('nasname[][]',first)])
                assert state()==before
                print('PASS: malformed array controls cannot mutate NAS data')

                sql('ALTER TABLE nas ENGINE=MyISAM')
                try:
                    before=state();page=create(client,'nontrans-'+secrets.token_hex(4),secret1)
                    assert 'Unable to add NAS' in page and state()==before
                    page=edit(client,first,secret2)
                    assert 'Unable to edit NAS' in page and state()==before
                    page=delete(client,[first]);assert 'Unable to delete NAS' in page and state()==before
                finally:sql('ALTER TABLE nas ENGINE=InnoDB')
                print('PASS: all three mutations reject nontransactional table')

                # Cooperating writers use the same DB-scoped advisory lock.
                pending='nas-lock-'+secrets.token_hex(4)
                lock_name=sql("SELECT CONCAT('daloradius:nas:',SUBSTRING(SHA2(CONCAT(DATABASE(),CHAR(0),'nas'),256),1,48))")
                csrf=form(client,'mng-rad-nas-new.php')
                guard="$p=new PDO('mysql:host='.$argv[1].';dbname=radius','root','');" \
                      "$n=$argv[2];echo $p->query('SELECT GET_LOCK('.$p->quote($n).',0)')->fetchColumn();" \
                      "flush();sleep(2);$p->query('SELECT RELEASE_LOCK('.$p->quote($n).')');"
                holder=subprocess.Popen(['docker','exec',WEB,'php','-r',guard,DB,lock_name],
                    stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
                assert holder.stdout.read(1)=='1'
                start=time.monotonic()
                page=post(client,'mng-rad-nas-new.php',[
                    ('csrf_token',csrf),('nasname',pending),('secret',secret1)])
                elapsed=time.monotonic()-start
                holder.communicate(timeout=20)
                assert 'Successfully added' in page and elapsed>=1.0 and count(pending)=='1'
                assert sql('SELECT IS_FREE_LOCK(%s)' % quote(lock_name))=='1'
                print('PASS: create waits for shared NAS advisory lock')

            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,timeout=30,check=True)
            assert 'PHP Fatal error' not in logs.stdout+logs.stderr
            assert 'PHP Warning' not in logs.stdout+logs.stderr
            print('PASS: no PHP fatal errors or warnings')
        finally:
            run('docker','exec','-u','root',WEB,'chown','-R',
                f'{os.getuid()}:{os.getgid()}','/fixtures',check=False)
            for container in (WEB,DB):run('docker','rm','-f','-v',container,check=False)
            run('docker','network','rm',NETWORK,check=False)
            print('CLEANUP: disposable NAS CRUD fixture removed')

if __name__=='__main__':main()
