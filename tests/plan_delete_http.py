#!/usr/bin/env python3
"""UNIT-010: disposable HTTP/PHP/MariaDB billing plan deletion A/B."""
import json
import os
import re
from pathlib import Path
import secrets
import shutil
import sys
import tempfile
import urllib.error
import urllib.parse
import urllib.request
import user_actions_http as h
from acct_maintenance_http import Forms

ROOT=Path(os.environ.get('PLAN_DELETE_ROOT',Path(__file__).resolve().parents[1]))
CANDIDATE=(ROOT/'app/operators/library/plan_delete.php').exists()
DB,WEB,NETWORK=h.DB,h.WEB,h.NETWORK
run,sql,wait_for=h.run,h.sql,h.wait_for


def main():
    scratch=Path.home()/'.hermes/cache/scratch'
    scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch,prefix='dalo-plan-delete-') as tmp:
        fixture=Path(tmp)
        shutil.copytree(ROOT/'app',fixture/'app',symlinks=True)
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            sql("""INSERT INTO operators_acl (operator_id,file,access) VALUES
                 (9001,'bill_plans_del',1),(9002,'bill_plans_del',0);
                 INSERT INTO billing_plans (planName,planId,planCost,creationby) VALUES
                   ('single','s','2.50','seed'),('batch-a','a','3.50','seed'),
                   ('batch-b','b','4.50','seed'),('keep','k','5.50','seed'),
                   ('O''Reilly 50%','q','6.50','seed');
                 INSERT INTO billing_plans_profiles (plan_name,profile_name) VALUES
                   ('single','gold'),('single','silver'),('batch-a','gold'),
                   ('batch-b','silver'),('keep','member-only'),('O''Reilly 50%','gold');""")
            config=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,val in {'CONFIG_DB_HOST':DB,'CONFIG_DB_USER':'root',
                            'CONFIG_DB_PASS':'','CONFIG_DB_NAME':'radius'}.items():
                config+='\n$configValues['+repr(key)+'] = '+repr(val)+';\n'
            config+=("\n$configValues['CONFIG_LOCATIONS']['named'] = ["
                     f"'Engine'=>'mysqli','Hostname'=>{DB!r},'Port'=>'3306',"
                     "'Database'=>'radius','Username'=>'root','Password'=>''];\n")
            (fixture/'app/common/includes/daloradius.conf.php').write_text(config)
            (fixture/'session.php').write_text('''<?php
session_name('daloradius_operator_sid'); session_id($argv[1]); session_start();
$_SESSION = ['daloradius_logged_in'=>true,'operator_id'=>(int)$argv[2],
'operator_user'=>'plan-delete-fixture','location_name'=>$argv[3],'time'=>time()];
session_write_close();
''')
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{fixture}:/fixtures','-w','/fixtures/app/operators',
                '--entrypoint','php','lirantal/daloradius','-d','display_errors=0',
                '-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/bill-plans-del.php'
            wait_for(lambda: urllib.request.urlopen(base,timeout=10),'PHP HTTP')
            sid=secrets.token_hex(16)
            def session(operator=9001,location='default'):
                run('docker','exec',WEB,'php','/fixtures/session.php',sid,str(operator),location)
            def request(data=None,name=None,authenticated=True):
                url=base+('?' + urllib.parse.urlencode({'planName':name}) if name else '')
                req=urllib.request.Request(url,
                    data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),
                    headers={'Cookie':'daloradius_operator_sid='+sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req,timeout=15) as response:
                        return response.url,response.read().decode()
                except urllib.error.HTTPError as error:
                    return error.url,error.read().decode()
            def csrf():
                return next(f['csrf_token'] for f in Forms(request()[1]).forms if 'csrf_token' in f)
            def delete(names,token=None,scalar=False):
                data={'csrf_token':csrf() if token is None else token,
                      'planName' if scalar else 'planName[]':names}
                return request(data)[1]
            def state():
                return {'plans':sql('SELECT planName,planId,planCost,creationby FROM billing_plans ORDER BY planName'),
                        'profiles':sql('SELECT plan_name,profile_name FROM billing_plans_profiles ORDER BY plan_name,profile_name')}
            session()
            before=state()
            assert request(authenticated=False)[0].endswith('/login.php')
            session(9002)
            assert request()[0].endswith('/home-error.php')
            assert request({'csrf_token':'bad','planName[]':['single']})[0].endswith('/home-error.php')
            session()
            assert 'Deleted' not in delete(['single'],token='invalid')
            assert state()==before
            print('PASS authentication, ACL and CSRF prevent writes',file=sys.stderr)
            if CANDIDATE:
                for names in ([],['single',''],['single',"missing"],
                              ['single','x OR 1=1'],['single','x'*129]):
                    delete(names)
                    assert state()==before,names
                request({'csrf_token':csrf(),'planName[]':['single'],'planName[1][nested]':'batch-a'})
                assert state()==before
                print('PASS malformed and stale multi-select rejected before mutation',file=sys.stderr)
            response=delete('single',scalar=True)
            assert 'Deleted ' in response,response[:400]
            single=state()
            assert not any(r.startswith('single\t') for r in single['plans'].splitlines())
            assert not any(r.startswith('single\t') for r in single['profiles'].splitlines())
            if CANDIDATE and os.environ.get('PLAN_DELETE_BASELINE_JSON'):
                baseline=json.loads(Path(os.environ['PLAN_DELETE_BASELINE_JSON']).read_text())
                assert single==baseline['single'],(single,baseline)
            print('PASS single plan and mappings removed',file=sys.stderr)
            if CANDIDATE:
                sql('CREATE TABLE fixture_block (plan_id INT(8) NOT NULL, '
                    'FOREIGN KEY (plan_id) REFERENCES billing_plans(id)) ENGINE=InnoDB')
                sql("INSERT INTO fixture_block SELECT id FROM billing_plans WHERE planName='batch-b'")
                locked=state()
                failed=delete(['batch-b','batch-a'])
                assert 'Failed to delete' in failed and 'fixture_block' not in failed
                assert state()==locked
                sql('DROP TABLE fixture_block')
                print('PASS later parent failure restores first plan and mappings',file=sys.stderr)
            multi=delete(['batch-b','batch-a','batch-a'])
            notice=re.search(r'Deleted [0-9]+ plan\(s\)(?: and [0-9]+ profile association\(s\))?',multi)
            assert notice,multi[:400]
            after_multi=state()
            assert not any(r.startswith('batch-') for r in after_multi['plans'].splitlines())
            assert not any(r.startswith('batch-') for r in after_multi['profiles'].splitlines())
            assert 'keep\t' in after_multi['plans'] and 'keep\tmember-only' in after_multi['profiles']
            if CANDIDATE:
                assert 'Deleted 2 plan(s) and 2 profile association(s)' in multi
                if os.environ.get('PLAN_DELETE_BASELINE_JSON'):
                    assert after_multi==baseline['multi']
                    assert baseline['notice']=='Deleted 1 plan(s)',baseline['notice']
                print('PASS A/B batch state matches PEAR; correct count replaces legacy resource cast',file=sys.stderr)
                html=request(name="O'Reilly 50%")[1]
                assert "O&#039;Reilly 50%" in html
                assert 'Deleted 1 plan(s)' in delete(["O'Reilly 50%"])
                assert not any(r.startswith("O'Reilly 50%\t") for r in state()['plans'].splitlines())
                session(9001,'named')
                assert 'Deleted 1 plan(s)' in delete(['keep'])
                assert state()=={'plans':'','profiles':''}
                print('PASS quoted/percent name and named location',file=sys.stderr)
            import subprocess
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            warnings=[x for x in logs.stderr.splitlines() if 'PHP Warning:' in x]
            assert 'PHP Fatal error' not in logs.stderr and not warnings,'\n'.join(warnings[-5:])
            print(json.dumps({'single':single,'multi':after_multi,'notice':notice.group(0)}))
        finally:
            for name in (WEB,DB): run('docker','rm','-f','-v',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            run('docker','run','--rm','-v',f'{fixture}:/fixtures','--entrypoint','sh',
                'lirantal/daloradius','-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')

if __name__=='__main__': main()
