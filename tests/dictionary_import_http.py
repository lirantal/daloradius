#!/usr/bin/env python3
"""UNIT-022: isolated PEAR/PDO dictionary imports over real PHP HTTP/MariaDB."""
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

ROOT=Path(__file__).resolve().parents[1]
BASELINE=os.environ.get('DICTIONARY_IMPORT_BASELINE')=='1'
DB,WEB,NETWORK=h.DB,h.WEB,h.NETWORK
run,sql,wait_for=h.run,h.sql,h.wait_for


def main():
    scratch=Path.home()/'.hermes/cache/scratch'
    scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch,prefix='dalo-dictionary-import-') as tmp:
        fixture=Path(tmp)
        shutil.copytree(ROOT/'app',fixture/'app',symlinks=True)
        if BASELINE:
            old=subprocess.check_output(['git','show',
                '39f307126:app/operators/mng-rad-attributes-import.php'],cwd=ROOT)
            (fixture/'app/operators/mng-rad-attributes-import.php').write_bytes(old)
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,
                '--tmpfs','/var/lib/mysql','-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            ddl=(ROOT/'contrib/db/mariadb-daloradius-dictionaries.sql').read_text().splitlines()
            sql('\n'.join(ddl[:21]))
            sql("""INSERT INTO operators_acl (operator_id,file,access) VALUES
                     (9001,'mng_rad_attributes_import',1),
                     (9002,'mng_rad_attributes_import',0);
                   INSERT INTO dictionary (id,Type,Attribute,Vendor,Value,RecommendedOP) VALUES
                     (8001,'string','Existing','UnitVendor',NULL,':='),
                     (8002,'integer','Stay','UnitVendor','choice',NULL),
                     (8003,'octets','Outside','OtherVendor',NULL,NULL);""")
            config=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,val in {'CONFIG_DB_HOST':DB,'CONFIG_DB_USER':'root',
                            'CONFIG_DB_PASS':'','CONFIG_DB_NAME':'radius'}.items():
                config+='\n$configValues['+repr(key)+'] = '+repr(val)+';\n'
            config+=("\n$configValues['CONFIG_LOCATIONS']['named'] = ["
                     f"'Engine'=>'mysqli','Hostname'=>{DB!r},'Port'=>'3306',"
                     "'Database'=>'radius','Username'=>'root','Password'=>''];\n")
            (fixture/'app/common/includes/daloradius.conf.php').write_text(config)
            (fixture/'session.php').write_text('''<?php
session_name('daloradius_operator_sid');session_id($argv[1]);session_start();
$_SESSION=['daloradius_logged_in'=>true,'operator_id'=>(int)$argv[2],
'operator_user'=>'dictionary-fixture','location_name'=>$argv[3],'time'=>time()];
session_write_close();
''')
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{fixture}:/fixtures','-w','/fixtures/app/operators',
                '--entrypoint','php','lirantal/daloradius','-d','display_errors=0',
                '-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f',
                '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/mng-rad-attributes-import.php'
            wait_for(lambda:urllib.request.urlopen(base,timeout=10),'PHP HTTP')
            sid=secrets.token_hex(16)
            def session(operator=9001,location='default'):
                run('docker','exec',WEB,'php','/fixtures/session.php',sid,str(operator),location)
            def request(data=None,authenticated=True):
                req=urllib.request.Request(base,
                    data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),
                    headers={'Cookie':'daloradius_operator_sid='+sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req,timeout=30) as response:
                        return response.url,response.read().decode()
                except urllib.error.HTTPError as error:
                    return error.url,error.read().decode()
            def csrf():
                return next(f['csrf_token'] for f in Forms(request()[1]).forms if 'csrf_token' in f)
            def submit(strategy='only_insert_new',dictionary=None,vendor='UnitVendor',detect=True,token=None,extras=None):
                if dictionary is None:
                    dictionary='VENDOR UnitVendor 42\nATTRIBUTE Existing 1 string\nATTRIBUTE Added 2 string\nATTRIBUTE NoType 3'
                data={'csrf_token':csrf() if token is None else token,
                      'importStrategy':strategy,'dictionary':dictionary,'vendor':vendor}
                if detect:
                    data['detectVendor']='on'
                if extras:
                    data.update(extras)
                return request(data)[1]
            def state():
                return sql('SELECT Vendor,Attribute,Type,Value,Format,RecommendedOP,RecommendedTable,RecommendedHelper,RecommendedTooltip FROM dictionary ORDER BY Vendor,Attribute,id')
            assert 'login.php' in request(authenticated=False)[0]
            session(9002)
            before=state()
            assert 'home-error.php' in request()[0]
            assert state()==before
            session()
            assert 'CSRF token error' in submit(token='invalid')
            assert state()==before
            print('PASS auth, ACL, CSRF',file=sys.stderr)
            first=submit()
            assert 'processed: 3, deleted: 0, inserted: 2, updated: 0' in first,first[-350:]
            after_first=state()
            assert 'UnitVendor\tNoType\tNULL' in after_first
            second=submit(strategy='insert_or_update',
                dictionary='VENDOR UnitVendor 42\nATTRIBUTE Existing 1 integer\nATTRIBUTE Fresh 2 ipaddr')
            assert 'processed: 2, deleted: 0, inserted: 1, updated: 1' in second,second[-350:]
            assert 'UnitVendor\tExisting\tinteger' in state()
            third=submit(strategy='delete_then_insert',
                dictionary='VENDOR UnitVendor 42\nATTRIBUTE Replacement 1 string\nATTRIBUTE Existing 2 octets')
            assert 'processed: 2, deleted: 5, inserted: 2, updated: 0' in third,(third[third.find('processed:')-80:third.find('processed:')+180],state())
            persisted=state()
            assert 'OtherVendor\tOutside\toctets' in persisted
            assert 'UnitVendor\tExisting\toctets' in persisted
            assert 'UnitVendor\tReplacement\tstring' in persisted
            assert 'UnitVendor\tStay\t' not in persisted
            print('PASS three strategies, nullable type, vendor isolation',file=sys.stderr)
            reference=os.environ.get('DICTIONARY_IMPORT_REFERENCE')
            if BASELINE:
                if reference:
                    Path(reference).write_text(json.dumps({'first':after_first,'last':persisted},ensure_ascii=False))
                print('PASS UNIT-022 PEAR baseline',file=sys.stderr)
            else:
                if reference:
                    assert json.loads(Path(reference).read_text())=={'first':after_first,'last':persisted}
                sql("INSERT INTO dictionary (Type,Attribute,Vendor,Value) VALUES "
                    "('integer','Choice','UnitVendor','one'),"
                    "('integer','Choice','UnitVendor','two')")
                choices='VENDOR UnitVendor 42\nATTRIBUTE Choice 5 string'
                assert 'processed: 1, deleted: 0, inserted: 0, updated: 2' in submit(
                    strategy='insert_or_update',dictionary=choices)
                assert 'processed: 1, deleted: 0, inserted: 0, updated: 0' in submit(
                    strategy='insert_or_update',dictionary=choices)
                assert sql("SELECT COUNT(*) FROM dictionary WHERE Attribute='Choice' AND Type='string'").strip()=='2'
                print('PASS duplicate-value rows updated together; counts reflect committed changes',file=sys.stderr)
                for changes in [
                    {'importStrategy':'unrecognized'},
                    {'importStrategy[]':['only_insert_new']},
                    {'dictionary[]':['ATTRIBUTE No 1 string']},
                    {'dictionary':'VENDOR UnitVendor 1'},
                    {'dictionary':'VENDOR UnitVendor 1\nVENDOR OtherVendor 2\nATTRIBUTE Invalid 1 string'},
                    {'dictionary':'VENDOR UnitVendor 1\nATTRIBUTE '+('a'*65)+' 1 string'},
                    {'dictionary':'VENDOR UnitVendor 1\nATTRIBUTE Attr 1 '+('a'*31)},
                    {'vendor[]':['broken'],'detectVendor':None},
                ]:
                    payload={'importStrategy':'delete_then_insert',
                             'dictionary':'VENDOR UnitVendor 1\nATTRIBUTE Existing 1 string',
                             'detectVendor':'on','vendor':'UnitVendor'}
                    if 'detectVendor' in changes and changes['detectVendor'] is None:
                        del payload['detectVendor']
                    for key,val in changes.items():
                        if key!='detectVendor':
                            if key.endswith('[]'):
                                payload.pop(key[:-2],None)
                            payload[key]=val
                    before=state()
                    body=request({'csrf_token':csrf(),**payload})[1]
                    assert 'Cannot import dictionary' in body,(changes,body[-250:])
                    assert state()==before,changes
                print('PASS invalid strategy, malformed controls, multi-vendor/empty/oversized input',file=sys.stderr)
                sql("DELIMITER //\nCREATE TRIGGER fixture_late_dictionary BEFORE INSERT ON dictionary "
                    "FOR EACH ROW BEGIN IF NEW.Attribute='Crash' THEN "
                    "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='late dictionary write failed'; "
                    "END IF; END//\nDELIMITER ;\n")
                before=state()
                failed=submit(strategy='delete_then_insert',
                    dictionary='VENDOR UnitVendor 42\nATTRIBUTE Replacement 1 string\nATTRIBUTE Crash 2 string')
                assert 'Cannot import dictionary' in failed and 'late dictionary write failed' not in failed
                assert state()==before,'delete and first insert persisted despite later insert failure'
                failed=submit(strategy='insert_or_update',
                    dictionary='VENDOR UnitVendor 42\nATTRIBUTE Existing 1 ipaddr\nATTRIBUTE Crash 2 string')
                assert 'Cannot import dictionary' in failed and state()==before
                sql('DROP TRIGGER fixture_late_dictionary')
                print('PASS late insert rollback after delete/insert and after update',file=sys.stderr)
                assert 'processed: 1, deleted: 0, inserted: 1, updated: 0' in submit(
                    dictionary="ATTRIBUTE 50%O'Reilly 1 string",vendor="Vendor% O'Reilly",detect=False)
                assert "Vendor% O'Reilly\t50%O'Reilly\tstring" in state()
                session(9001,'named')
                assert 'processed: 1, deleted: 0, inserted: 1, updated: 0' in submit(
                    dictionary='VENDOR Équipe 1\nATTRIBUTE Nom-équipe 1 string')
                assert 'Équipe\tNom-équipe\tstring' in state()
                session()
                sql('ALTER TABLE dictionary ENGINE=MyISAM')
                before=state()
                assert 'Cannot import dictionary' in submit(strategy='delete_then_insert')
                assert state()==before
                sql('ALTER TABLE dictionary ENGINE=InnoDB')
                print('PASS percent/quotes/Unicode, named location, non-InnoDB rejection',file=sys.stderr)
                print('PASS UNIT-022 PDO candidate',file=sys.stderr)
        finally:
            for name in (WEB,DB):
                subprocess.run(['docker','rm','-f',name],stdout=subprocess.DEVNULL,
                               stderr=subprocess.DEVNULL)
            subprocess.run(['docker','network','rm',NETWORK],stdout=subprocess.DEVNULL,
                           stderr=subprocess.DEVNULL)

if __name__=='__main__':main()
