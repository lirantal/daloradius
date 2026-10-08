#!/usr/bin/env python3
"""UNIT-021: isolated PEAR/PDO profile duplication and rollback over HTTP."""
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
BASELINE=os.environ.get('PROFILE_DUPLICATE_BASELINE')=='1'
DB,WEB,NETWORK=h.DB,h.WEB,h.NETWORK
run,sql,wait_for=h.run,h.sql,h.wait_for

def main():
    scratch=Path.home()/'.hermes/cache/scratch'
    scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch,prefix='dalo-profile-duplicate-') as tmp:
        fixture=Path(tmp)
        shutil.copytree(ROOT/'app',fixture/'app',symlinks=True)
        if BASELINE:
            old=subprocess.check_output(['git','show',
                'f18aa37b3:app/operators/mng-rad-profiles-duplicate.php'],cwd=ROOT)
            (fixture/'app/operators/mng-rad-profiles-duplicate.php').write_bytes(old)
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,
                '--tmpfs','/var/lib/mysql','-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            sql("""INSERT INTO operators_acl (operator_id,file,access) VALUES
                     (9001,'mng_rad_profiles_duplicate',1),
                     (9002,'mng_rad_profiles_duplicate',0);
                   INSERT INTO radgroupcheck (id,groupname,attribute,op,value) VALUES
                     (9101,'unit-gold','Auth-Type',':=','Accept'),
                     (9102,'unit-gold','Filter-Id','==','A% O''Brien'),
                     (9103,'unit-check','Auth-Type',':=','Accept'),
                     (9104,'unit-existing','Auth-Type',':=','Accept'),
                     (9105,'unit-50% O''Reilly__v','Auth-Type',':=','Accept'),
                     (9106,'unit-équipe','Auth-Type',':=','Accept');
                   INSERT INTO radgroupreply (id,groupname,attribute,op,value) VALUES
                     (9201,'unit-gold','Reply-Message',':=','Équipe'),
                     (9202,'unit-reply','Reply-Message',':=','reply'),
                     (9203,'unit-50% O''Reilly__v','Reply-Message',':=','special');
                   INSERT INTO radusergroup (username,groupname,priority) VALUES
                     ('alice','unit-gold',0),('only','unit-mapping-only',0),
                     ('target','unit-target-map',0);
                   INSERT INTO billing_plans (id,planName,planActive) VALUES
                     (100,'plan-fixture','yes');
                   INSERT INTO billing_plans_profiles (plan_name,profile_name) VALUES
                     ('plan-fixture','unit-gold');""")
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
'operator_user'=>'profile-fixture','location_name'=>$argv[3],'time'=>time()];
session_write_close();
''')
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{fixture}:/fixtures','-w','/fixtures/app/operators',
                '--entrypoint','php','lirantal/daloradius','-d','display_errors=0',
                '-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f',
                '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/mng-rad-profiles-duplicate.php'
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
            def submit(source='unit-gold',target='unit-clone',token=None):
                data={'csrf_token':csrf() if token is None else token,
                      'sourceProfile[]' if isinstance(source,list) else 'sourceProfile':source,
                      'targetProfile[]' if isinstance(target,list) else 'targetProfile':target}
                return request(data)[1]
            def state():
                return {'check':sql('SELECT groupname,attribute,op,value FROM radgroupcheck ORDER BY groupname,id'),
                        'reply':sql('SELECT groupname,attribute,op,value FROM radgroupreply ORDER BY groupname,id'),
                        'users':sql('SELECT username,groupname,priority FROM radusergroup ORDER BY username,groupname'),
                        'plans':sql('SELECT plan_name,profile_name FROM billing_plans_profiles ORDER BY plan_name,profile_name')}
            def projection(name):
                return {'check':sql("SELECT attribute,op,value FROM radgroupcheck WHERE groupname='"+name+"' ORDER BY id"),
                        'reply':sql("SELECT attribute,op,value FROM radgroupreply WHERE groupname='"+name+"' ORDER BY id")}
            assert 'login.php' in request(authenticated=False)[0]
            session(9002)
            denied=state()
            denied_url,denied_body=request()
            assert 'home-error.php' in denied_url,(denied_url,denied_body[-350:])
            assert state()==denied
            session()
            assert 'CSRF token error' in submit(target='unit-csrf',token='invalid')
            assert state()==denied
            print('PASS authentication, ACL and CSRF',file=sys.stderr)
            assert 'successfully cloned' in submit(target='unit-clone')
            assert projection('unit-clone')==projection('unit-gold')
            assert 'successfully cloned' in submit(source='unit-check',target='unit-only-check')
            assert projection('unit-only-check')==projection('unit-check')
            assert 'successfully cloned' in submit(source='unit-reply',target='unit-only-reply')
            assert projection('unit-only-reply')==projection('unit-reply')
            persisted=state()
            assert 'unit-clone' not in persisted['users']+persisted['plans']
            print('PASS check/reply only and combined profile cloning',file=sys.stderr)
            if BASELINE:
                empty_copy=submit(source='unit-mapping-only',target='unit-empty-copy')
                assert projection('unit-empty-copy')=={'check':'','reply':''}
                assert 'successfully cloned' in empty_copy,empty_copy[-450:]
                reference=os.environ.get('PROFILE_DUPLICATE_REFERENCE')
                if reference:
                    Path(reference).write_text(json.dumps(persisted,ensure_ascii=False,sort_keys=True))
                print('PASS UNIT-021 PEAR baseline',file=sys.stderr)
            else:
                reference=os.environ.get('PROFILE_DUPLICATE_REFERENCE')
                if reference:
                    assert json.loads(Path(reference).read_text())==persisted, 'PEAR/PDO ordinary state mismatch'
                invalid=[('unit-missing','unit-new'),('unit-mapping-only','unit-new'),
                         ('unit-gold','unit-existing'),('unit-gold','unit-target-map'),
                         ('unit-gold','unit-gold'),('unit-gold','UNIT-GOLD'),
                         ('unit-gold','unit-clone'),
                         ('unit-gold','x'*65),(['unit-gold'],'unit-array'),
                         ('unit-gold',['unit-array']),('','unit-blank')]
                for source,target in invalid:
                    before=state()
                    body=submit(source,target)
                    assert 'Cannot clone profile' in body,(source,target,body[-250:])
                    assert state()==before,(source,target)
                print('PASS malformed/unknown/colliding/no-attribute profiles',file=sys.stderr)
                sql("DELIMITER //\nCREATE TRIGGER fixture_reply_insert BEFORE INSERT ON radgroupreply "
                    "FOR EACH ROW BEGIN IF NEW.groupname='unit-late' THEN "
                    "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='later reply insert failed'; "
                    "END IF; END//\nDELIMITER ;\n")
                before=state()
                failed=submit(target='unit-late')
                assert 'Cannot clone profile' in failed
                assert 'later reply insert failed' not in failed
                assert state()==before,'first check INSERT persisted despite later reply error'
                sql('DROP TRIGGER fixture_reply_insert')
                print('PASS late reply failure rolls back earlier check rows',file=sys.stderr)
                special="unit-50% O'Reilly__v"
                assert 'successfully cloned' in submit(source=special,target="clone-50% O'Reilly__v")
                assert projection('clone-50% O''Reilly__v')==projection('unit-50% O''Reilly__v')
                assert 'successfully cloned' in submit(source='unit-équipe',target='clone-équipe')
                assert projection('clone-équipe')==projection('unit-équipe')
                session(9001,'named')
                assert 'successfully cloned' in submit(target='unit-named')
                assert projection('unit-named')==projection('unit-gold')
                session()
                sql('ALTER TABLE radgroupreply ENGINE=MyISAM')
                before=state()
                assert 'Cannot clone profile' in submit(target='unit-engine')
                assert state()==before
                sql('ALTER TABLE radgroupreply ENGINE=InnoDB')
                print('PASS quoted/percent/Unicode, named location, non-InnoDB refusal',file=sys.stderr)
                print('PASS UNIT-021 PDO candidate',file=sys.stderr)
        finally:
            for name in (WEB,DB):
                subprocess.run(['docker','rm','-f',name],stdout=subprocess.DEVNULL,
                               stderr=subprocess.DEVNULL)
            subprocess.run(['docker','network','rm',NETWORK],stdout=subprocess.DEVNULL,
                           stderr=subprocess.DEVNULL)

if __name__=='__main__':main()
