#!/usr/bin/env python3
"""UNIT-008: disposable HTTP/PHP/MariaDB billing plan + profiles A/B."""
import json
import os
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

ROOT=Path(os.environ.get('PLAN_CREATE_ROOT',Path(__file__).resolve().parents[1]))
CANDIDATE=(ROOT/'app/operators/library/plan_create.php').exists()
DB,WEB,NETWORK=h.DB,h.WEB,h.NETWORK
run,sql,wait_for=h.run,h.sql,h.wait_for

def main():
    scratch=Path.home()/'.hermes/cache/scratch'
    scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch,prefix='dalo-plan-create-') as tmp:
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
                   (9001,'bill_plans_new',1),(9002,'bill_plans_new',0);
                   INSERT INTO radgroupcheck (groupname,attribute,op,value)
                     VALUES ('gold','Auth-Type',':=','Accept'),
                            ('O''Reilly','Auth-Type',':=','Accept');
                   INSERT INTO radgroupreply (groupname,attribute,op,value)
                     VALUES ('silver','Session-Timeout',':=','60');
                   INSERT INTO radusergroup (username,groupname,priority)
                     VALUES ('alice','member-only',0);""")
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
'operator_user'=>'plan-fixture','location_name'=>$argv[3],'time'=>time()];
session_write_close();
''')
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{fixture}:/fixtures','-w','/fixtures/app/operators',
                '--entrypoint','php','lirantal/daloradius','-d','display_errors=0',
                '-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/bill-plans-new.php'
            wait_for(lambda: urllib.request.urlopen(base,timeout=10),'PHP HTTP')
            sid=secrets.token_hex(16)
            def session(operator=9001,location='default'):
                run('docker','exec',WEB,'php','/fixtures/session.php',sid,str(operator),location)
            def request(data=None,authenticated=True):
                req=urllib.request.Request(base,
                    data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),
                    headers={'Cookie':'daloradius_operator_sid='+sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req,timeout=15) as response:
                        return response.url,response.read().decode()
                except urllib.error.HTTPError as error:
                    return error.url,error.read().decode()
            def csrf():
                return next(f['csrf_token'] for f in Forms(request()[1]).forms if 'csrf_token' in f)
            def submit(extra=None,name="O'Reilly & 50%",groups=None,token=None):
                values={'csrf_token':csrf() if token is None else token,
                        'planName':name,'planId':'fixture-42','planType':'Prepaid',
                        'planTimeBank':'120','planTimeType':'Accumulative',
                        'planTimeRefillCost':'5.00','planBandwidthUp':'100',
                        'planBandwidthDown':'200','planTrafficTotal':'300',
                        'planTrafficUp':'400','planTrafficDown':'500',
                        'planTrafficRefillCost':'2.00','planRecurring':'no',
                        'planRecurringPeriod':'Monthly',
                        'planRecurringBillingSchedule':'Anniversary',
                        'planCost':'12.30','planSetupCost':'4.50','planTax':'0.25',
                        'planCurrency':'EUR','planGroup':'fixture','planActive':'no'}
                if groups is not None: values['groups[]']=groups
                if extra: values.update(extra)
                return request(values)[1]
            def state():
                return {'plans':sql('''SELECT planName,planId,planType,planTimeBank,planTimeType,
                  planTimeRefillCost,planBandwidthUp,planBandwidthDown,planTrafficTotal,
                  planTrafficUp,planTrafficDown,planTrafficRefillCost,planRecurring,
                  planRecurringPeriod,planRecurringBillingSchedule,planCost,planSetupCost,
                  planTax,planCurrency,planGroup,planActive,creationby
                  FROM billing_plans ORDER BY id'''),
                  'profiles':sql('SELECT plan_name,profile_name FROM billing_plans_profiles ORDER BY id')}
            session()
            original=state()
            assert request(authenticated=False)[0].endswith('/login.php')
            session(9002)
            assert request()[0].endswith('/home-error.php')
            session()
            assert 'successfully added' not in submit(groups=['gold'],token='invalid')
            assert state()==original
            print('PASS authentication, ACL and CSRF prevent writes',file=sys.stderr)
            if CANDIDATE:
                html=request()[1]
                assert 'name="planActive"' in html, 'PlanActive control must submit its own field'
            response=submit(groups=['gold','silver'])
            assert 'successfully added' in response,response[:500]
            initial=state()
            assert initial['profiles']==("O'Reilly & 50%\tgold\nO'Reilly & 50%\tsilver"),initial
            assert 'no\tMonthly\tAnniversary' in initial['plans'],initial
            if CANDIDATE and os.environ.get('PLAN_CREATE_BASELINE_JSON'):
                baseline=json.loads(Path(os.environ['PLAN_CREATE_BASELINE_JSON']).read_text())
                assert initial==baseline['initial'],(initial,baseline)
                print('PASS all persisted plan fields + mappings match PEAR baseline',file=sys.stderr)
            duplicate=submit(groups=['gold'])
            assert 'already exists' in duplicate and state()==initial
            print('PASS duplicate plan cannot create mappings',file=sys.stderr)
            if CANDIDATE:
                invalid=[({'planName[]':'nested'},'malformed name'),
                         ({'planCost[]':'nested'},'malformed field'),
                         ({'groups':'gold'},'scalar groups'),
                         ({'groups[0][nested]':'gold'},'nested groups'),
                         ({},'unknown profile'),
                         ({},'overlong name')]
                for extras,label in invalid:
                    name='Invalid-'+label
                    groups=['gold']
                    if label=='unknown profile': groups=['bogus']
                    if label=='overlong name': name='X'*129
                    submit(extras,name=name,groups=groups)
                    assert state()==initial,label
                print('PASS malformed fields, unknown groups and oversized name do not write',file=sys.stderr)
                # First mapping succeeds, second fails at the database: neither
                # the plan nor the first mapping may survive.
                sql("DELETE FROM billing_plans_profiles WHERE plan_name = 'O''Reilly & 50%'")
                sql("INSERT INTO billing_plans_profiles (plan_name,profile_name) VALUES ('existing','silver')")
                sql('ALTER TABLE billing_plans_profiles ADD UNIQUE KEY fixture_unique_profile (profile_name)')
                before=state()
                failed=submit(name='Must rollback',groups=['gold','silver'])
                assert 'Failed to insert' in failed and 'Duplicate entry' not in failed
                assert state()==before
                sql('ALTER TABLE billing_plans_profiles DROP KEY fixture_unique_profile')
                print('PASS second mapping SQL failure rolls back plan and first mapping',file=sys.stderr)
                one=submit(name='Member-only plan',groups=['member-only'])
                assert '1 ' in one and 'Member-only plan\tmember-only' in state()['profiles']
                quoted=submit(name='Quoted profile plan',groups=["O'Reilly"])
                assert 'successfully added' in quoted
                assert "Quoted profile plan\tO'Reilly" in state()['profiles']
                empty=submit(name='No-profile plan')
                assert 'successfully added' in empty
                assert 'No-profile plan' in state()['plans']
                assert 'No-profile plan' not in state()['profiles']
                print('PASS radusergroup-only and empty profile selection',file=sys.stderr)
                session(9001,'named')
                named=submit(name='Named-location plan',groups=['gold'])
                assert 'successfully added' in named
                assert 'Named-location plan\tgold' in state()['profiles']
                print('PASS named database location',file=sys.stderr)
            import subprocess
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            warnings=[x for x in logs.stderr.splitlines() if 'PHP Warning:' in x]
            assert 'PHP Fatal error' not in logs.stderr and not warnings,'\n'.join(warnings[-5:])
            print(json.dumps({'initial':initial}))
        finally:
            for name in (WEB,DB): run('docker','rm','-f','-v',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            run('docker','run','--rm','-v',f'{fixture}:/fixtures','--entrypoint','sh',
                'lirantal/daloradius','-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')

if __name__=='__main__': main()
