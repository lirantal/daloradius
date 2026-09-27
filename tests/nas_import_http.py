#!/usr/bin/env python3
"""UNIT-033 differential NAS import in disposable HTTP/PHP/MariaDB."""
import base64
import json
import os
import re
import secrets
import shutil
import subprocess
import tempfile
import urllib.error
import urllib.request
from pathlib import Path

import operator_login_http as auth
from operator_login_http import Client, FormParser, hash_password, login, quote, run, sql, wait_for

ROOT = Path(__file__).resolve().parents[1]
BASELINE = os.environ.get('NAS_IMPORT_BASELINE') == '1'
BASE_COMMIT = 'dc831aa4e677a3005c53ca290db3196faa830680'
IMAGE = os.environ.get('NAS_IMPORT_WEB_IMAGE','lirantal/daloradius')
DB, WEB, NETWORK = auth.DB, auth.WEB, auth.NETWORK


def request(client,path,fields=None):
    return client.request(path,fields)


def csrf(client):
    status,_,_,page=request(client,'mng-rad-nas-import.php')
    assert status == 200
    parser=FormParser(); parser.feed(page)
    assert parser.csrf
    return parser.csrf


def preview(client,document):
    token=csrf(client)
    boundary='dalo-'+secrets.token_hex(12)
    content=json.dumps(document,ensure_ascii=False).encode()
    fields=[('csrf_token',token),('nas_import_action','preview')]
    body=b''
    for key,value in fields:
        body += ('--%s\r\nContent-Disposition: form-data; name="%s"\r\n\r\n%s\r\n' %
                 (boundary,key,value)).encode()
    body += ('--%s\r\nContent-Disposition: form-data; name="nas_backup"; '
             'filename="backup.json"\r\nContent-Type: application/json\r\n\r\n' % boundary).encode()
    body += content + ('\r\n--%s--\r\n' % boundary).encode()
    req=urllib.request.Request(client.base+'mng-rad-nas-import.php',data=body,
        headers={'Content-Type':'multipart/form-data; boundary='+boundary})
    if client.sid: req.add_header('Cookie','daloradius_operator_sid='+client.sid)
    try: response=client.opener.open(req,timeout=45)
    except urllib.error.HTTPError as error: response=error
    page=response.read().decode('utf-8','replace')
    match=re.search(r'name="preview_token" value="([0-9a-f]+)"',page)
    return response.status,page,match.group(1) if match else None


def confirm(client,token,csrf_override=None):
    # The preview generated a fresh CSRF for the confirm form.
    status,_,_,page=request(client,'mng-rad-nas-import.php',
        [('csrf_token',csrf_override if csrf_override is not None else client.confirm_csrf),
         ('nas_import_action','confirm'),('preview_token',token)])
    return status,page


def preview_and_token(client,document):
    status,page,token=preview(client,document)
    if token:
        parser=FormParser(); parser.feed(page); client.confirm_csrf=parser.csrf
    return status,page,token


def document(entries,version=1):
    return {'format':'daloradius-nas-backup','version':version,'includes_secrets':True,
            'count':len(entries),'nas':entries}


def entry(name,secret,**fields):
    return dict({'nasname':name,'shortname':None,'type':'other','ports':None,
                 'secret':secret,'server':None,'community':None,'description':None},**fields)


def state():
    # Never retrieve the secret itself, or store password-bearing DB dumps.
    return sql('SELECT HEX(nasname),HEX(shortname),ports,HEX(type),LENGTH(secret),'
               'HEX(server),HEX(community),HEX(description) FROM nas ORDER BY id')


def insert_existing(name,secret):
    sql('INSERT INTO nas (nasname,secret) VALUES (%s,%s)' % (quote(name),quote(secret)))


def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    operator='nas-op-'+secrets.token_hex(5)
    password=secrets.token_urlsafe(22)
    first='nas-é\'-'+secrets.token_hex(4)
    second='nas-later-'+secrets.token_hex(4)
    duplicate='nas-existing-'+secrets.token_hex(4)
    secret_values=[secrets.token_urlsafe(18) for _ in range(6)]
    with tempfile.TemporaryDirectory(prefix='dalo-nas-import-',dir=scratch) as directory:
        root=Path(directory);shutil.copytree(ROOT/'app',root/'app',symlinks=True)
        if BASELINE:
            old=run('git','show',BASE_COMMIT+':app/operators/mng-rad-nas-import.php')
            (root/'app/operators/mng-rad-nas-import.php').write_text(old+'\n')
            old=run('git','show',BASE_COMMIT+':app/operators/include/management/nasImportExport.php')
            (root/'app/operators/include/management/nasImportExport.php').write_text(old+'\n')
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
                '-e','PHP_CLI_SERVER_WORKERS=2','--entrypoint','php',IMAGE,
                '-d','display_errors=0','-S','0.0.0.0:8080','-t','.')
            address=run('docker','inspect','-f',
                '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+address+':8080/'
            wait_for(lambda:Client(base).request('login.php')[0]==200,'operators HTTP')
            auth.add_operator(operator,hash_password(WEB,password))
            operator_id=sql('SELECT id FROM operators WHERE username=%s' % quote(operator))
            sql("INSERT INTO operators_acl (operator_id,file,access) VALUES "
                "(%s,'mng_rad_nas_list',1),(%s,'mng_rad_nas_new',1)" %
                (operator_id,operator_id))
            client=Client(base)
            status,_,headers,_=login(client,operator,password,'local','default')
            assert status==302 and headers.get('Location','').endswith('index.php')
            insert_existing(duplicate,secret_values[0])
            original=state()

            bad=entry('nas-invalid-'+secrets.token_hex(4),'')
            doc=document([entry(first,secret_values[1],shortname='éxample',ports=0),
                          entry(duplicate,secret_values[2]),bad,
                          entry(second,secret_values[3],description='later')])
            status,page,token=preview_and_token(client,doc)
            assert status==200 and token and 'Ready to import' in page
            assert all(value not in page for value in secret_values)
            if not BASELINE:
                status,_,_,invalid=request(client,'mng-rad-nas-import.php',
                    [('csrf_token',client.confirm_csrf),('nas_import_action','confirm'),
                     ('preview_token[]',token)])
                assert status==200 and 'no longer valid' in invalid
                status,_,_,invalid=request(client,'mng-rad-nas-import.php',
                    [('csrf_token[]',client.confirm_csrf),('nas_import_action','confirm'),
                     ('preview_token',token)])
                assert status==200 and 'CSRF token error' in invalid
                form=FormParser();form.feed(invalid);assert form.csrf
                client.confirm_csrf=form.csrf
                # Invalid requests must not consume the valid preview.
            insert_existing(second,secret_values[4])
            status,page=confirm(client,token)
            assert status==200 and 'Imported 1 NAS entry; skipped 2' in page and 'rejected 1' in page
            assert sql('SELECT COUNT(*) FROM nas WHERE nasname=%s' % quote(first))=='1'
            assert sql('SELECT secret=%s FROM nas WHERE nasname=%s' %
                       (quote(secret_values[1]),quote(first)))=='1'
            assert sql('SELECT secret=%s FROM nas WHERE nasname=%s' %
                       (quote(secret_values[0]),quote(duplicate)))=='1'
            assert sql('SELECT secret=%s FROM nas WHERE nasname=%s' %
                       (quote(secret_values[4]),quote(second)))=='1'
            assert all(value not in page for value in secret_values)
            print('PASS: preview, import, existing and post-preview duplicate, invalid row, secret redaction')

            # Replay must not import a second copy or reuse the consumed preview.
            before=state()
            new_csrf=csrf(client)
            status,_,_,replay=request(client,'mng-rad-nas-import.php',
                [('csrf_token',new_csrf),('nas_import_action','confirm'),('preview_token',token)])
            assert status==200 and 'no longer valid' in replay and state()==before
            print('PASS: preview token is single use')

            # The shared lock helpers must still support untouched PEAR NAS pages.
            legacy_name='nas-pear-'+secrets.token_hex(4)
            status,_,_,form_page=request(client,'mng-rad-nas-new.php')
            assert status==200
            form=FormParser();form.feed(form_page);assert form.csrf
            status,_,_,new_page=request(client,'mng-rad-nas-new.php',
                [('csrf_token',form.csrf),('nasname',legacy_name),('secret',secret_values[5])])
            assert status==200 and 'Successfully added a new NAS' in new_page
            assert sql('SELECT secret=%s FROM nas WHERE nasname=%s' %
                       (quote(secret_values[5]),quote(legacy_name)))=='1'
            print('PASS: untouched PEAR NAS creation still acquires and releases shared lock')

            if not BASELINE:
                late1='nas-rollback-a-'+secrets.token_hex(4)
                late2='nas-rollback-b-'+secrets.token_hex(4)
                status,page,token=preview_and_token(client,document([
                    entry(late1,secret_values[1]),entry(late2,secret_values[2])]))
                assert status==200 and token
                before=state()
                sql("DELIMITER //\nCREATE TRIGGER reject_nas_late BEFORE INSERT ON nas FOR EACH ROW "
                    "BEGIN IF NEW.nasname="+quote(late2)+" THEN SIGNAL SQLSTATE '45000' "
                    "SET MESSAGE_TEXT='fixture late insert failure'; END IF; END//\nDELIMITER ;\n")
                try:
                    status,page=confirm(client,token)
                    assert status==200 and 'rolled back' in page
                    assert state()==before and 'fixture late insert failure' not in page
                finally:
                    sql('DROP TRIGGER reject_nas_late')
                lock_name=sql("SELECT CONCAT('daloradius:nas:',SUBSTRING(SHA2(CONCAT(DATABASE(),CHAR(0),'nas'),256),1,48))")
                assert sql('SELECT IS_FREE_LOCK(%s)' % quote(lock_name))=='1'
                print('PASS: second-insert SQL error rolls back first insert and releases lock')

                # A non-transactional target cannot satisfy the all-or-nothing promise.
                status,page,token=preview_and_token(client,document([entry(
                    'nas-myisam-'+secrets.token_hex(4),secret_values[1])]))
                assert status==200 and token
                before=state();sql('ALTER TABLE nas ENGINE=MyISAM')
                try:
                    status,page=confirm(client,token)
                    assert status==200 and 'failed' in page and state()==before
                finally:
                    sql('ALTER TABLE nas ENGINE=InnoDB')
                print('PASS: nontransactional table rejected without a write')

                # Binary version 2 is the export format for non-text fields.
                binary_name=b'nas-bin-\x00'+secrets.token_hex(4).encode()
                encoded={'encoding':'base64','data':base64.b64encode(binary_name).decode(),
                         'byte_length':len(binary_name)}
                status,page,token=preview_and_token(client,document([
                    entry(encoded,secret_values[5])],version=2))
                assert status==200 and token
                status,page=confirm(client,token)
                assert status==200 and 'Imported 1 NAS entry' in page
                assert sql('SELECT COUNT(*) FROM nas WHERE HEX(nasname)=%s' %
                           quote(binary_name.hex().upper()))=='1'
                print('PASS: version-2 binary NAS name round trips without text coercion')

                # The same lock name coordinates PEAR NAS changes and PDO import.
                lock_name=sql("SELECT CONCAT('daloradius:nas:',SUBSTRING(SHA2(CONCAT(DATABASE(),CHAR(0),'nas'),256),1,48))")
                guard_code="""$p=new PDO('mysql:host=' . $argv[1] . ';dbname=radius','root','');
$n=$argv[2];echo $p->query('SELECT GET_LOCK(' . $p->quote($n) . ', 0)')->fetchColumn();
flush();sleep(2);$p->query('SELECT RELEASE_LOCK(' . $p->quote($n) . ')');"""
                holder=subprocess.Popen(['docker','exec',WEB,'php','-r',guard_code,DB,lock_name],
                    stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
                assert holder.stdout.read(1)=='1'
                pending='nas-lock-'+secrets.token_hex(4)
                status,page,token=preview_and_token(client,document([entry(pending,secret_values[1])]))
                assert status==200 and token
                status,page=confirm(client,token)
                holder.communicate(timeout=20)
                assert status==200 and 'Imported 1 NAS entry' in page
                assert sql('SELECT COUNT(*) FROM nas WHERE nasname=%s' % quote(pending))=='1'
                print('PASS: PDO import waits for the shared NAS advisory lock')

            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,timeout=30,check=True)
            assert 'PHP Fatal error' not in logs.stdout+logs.stderr
            assert 'PHP Warning' not in logs.stdout+logs.stderr
            print('PASS: no PHP fatal errors or warnings')
        finally:
            run('docker','exec','-u','root',WEB,'chown','-R',
                f'{os.getuid()}:{os.getgid()}','/fixtures',check=False)
            for container in (WEB,DB):run('docker','rm','-f','-v',container,check=False)
            run('docker','network','rm',NETWORK,check=False)
            print('CLEANUP: disposable NAS import fixture removed')

if __name__=='__main__':main()
