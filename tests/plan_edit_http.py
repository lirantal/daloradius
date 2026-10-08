#!/usr/bin/env python3
"""UNIT-009: isolated HTTP/PHP/MariaDB plan edit and A/B database state."""
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

ROOT=Path(os.environ.get('PLAN_EDIT_ROOT',Path(__file__).resolve().parents[1]))
CANDIDATE=(ROOT/'app/operators/library/plan_edit.php').exists()
DB,WEB,NETWORK=h.DB,h.WEB,h.NETWORK
run,sql,wait_for=h.run,h.sql,h.wait_for


def main():
    scratch=Path.home()/'.hermes/cache/scratch'
    scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch,prefix='dalo-plan-edit-') as tmp:
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
                 (9001,'bill_plans_edit',1),(9002,'bill_plans_edit',0);
                 INSERT INTO radgroupcheck (groupname,attribute,op,value)
                   VALUES ('gold','Auth-Type',':=','Accept'),
                          ('O''Reilly','Auth-Type',':=','Accept');
                 INSERT INTO radgroupreply (groupname,attribute,op,value)
                   VALUES ('silver','Session-Timeout',':=','60');
                 INSERT INTO radusergroup (username,groupname,priority)
                   VALUES ('alice','member-only',0);
                 INSERT INTO billing_plans (planName,planId,planActive,creationby)
                   VALUES ('Existing plan','old-id','yes','seed'),
                          ('Hold 50% & O''Reilly','old-quoted','yes','seed');
                 INSERT INTO billing_plans_profiles (plan_name,profile_name)
                   VALUES ('Existing plan','member-only');""")
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
'operator_user'=>'plan-edit-fixture','location_name'=>$argv[3],'time'=>time()];
session_write_close();
''')
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{fixture}:/fixtures','-w','/fixtures/app/operators',
                '--entrypoint','php','lirantal/daloradius','-d','display_errors=0',
                '-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/bill-plans-edit.php'
            wait_for(lambda: urllib.request.urlopen(base,timeout=10),'PHP HTTP')
            sid=secrets.token_hex(16)
            def session(operator=9001,location='default'):
                run('docker','exec',WEB,'php','/fixtures/session.php',sid,str(operator),location)
            def request(data=None,name='Existing plan',authenticated=True):
                url=base+'?'+urllib.parse.urlencode({'planName':name})
                req=urllib.request.Request(url,
                    data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),
                    headers={'Cookie':'daloradius_operator_sid='+sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req,timeout=15) as response:
                        return response.url,response.read().decode()
                except urllib.error.HTTPError as error:
                    return error.url,error.read().decode()
            def csrf(name='Existing plan'):
                return next(f['csrf_token'] for f in Forms(request(name=name)[1]).forms if 'csrf_token' in f)
            def submit(name='Existing plan',groups=None,extra=None,token=None):
                values={'csrf_token':csrf(name) if token is None else token,
                        'planName':name,'planId':'updated-42','planType':'Prepaid',
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
                return request(values,name=name)[1]
            def state():
                return {'plans':sql('''SELECT planName,planId,planType,planTimeType,planTimeBank,
                    planTimeRefillCost,planBandwidthUp,planBandwidthDown,planTrafficTotal,
                    planTrafficDown,planTrafficUp,planTrafficRefillCost,planRecurring,
                    planRecurringPeriod,planRecurringBillingSchedule,planCost,planSetupCost,
                    planTax,planCurrency,planActive,planGroup,creationby,updateby
                    FROM billing_plans ORDER BY id'''),
                    'profiles':sql('SELECT plan_name,profile_name FROM billing_plans_profiles ORDER BY plan_name,profile_name')}
            session()
            before=state()
            assert request(authenticated=False)[0].endswith('/login.php')
            session(9002)
            assert request()[0].endswith('/home-error.php')
            session()
            assert 'successfully updated' not in submit(groups=['gold'],token='invalid')
            assert state()==before
            print('PASS authentication, ACL and CSRF prevent writes',file=sys.stderr)
            if CANDIDATE:
                html=request()[1]
                assert 'name="planActive"' in html
                assert 'name="planRecurring"' in html
            response=submit(groups=['gold','silver'])
            assert 'successfully updated' in response,response[:500]
            initial=state()
            assert 'Existing plan\tgold' in initial['profiles'] and 'Existing plan\tsilver' in initial['profiles']
            assert 'Existing plan\tmember-only' not in initial['profiles']
            assert 'updated-42\tPrepaid' in initial['plans']
            if CANDIDATE and os.environ.get('PLAN_EDIT_BASELINE_JSON'):
                baseline=json.loads(Path(os.environ['PLAN_EDIT_BASELINE_JSON']).read_text())
                assert initial==baseline['initial'],(initial,baseline)
                print('PASS all persisted fields and replacement mappings match PEAR baseline',file=sys.stderr)
            if CANDIDATE:
                for extra,groups,label in [({'planCost[]':'nested'},['gold'],'nested field'),
                                           ({'planName[]':'nested'},['gold'],'nested name'),
                                           ({'groups[0][nested]':'gold'},['gold'],'nested group'),
                                           ({'groups':'gold'},None,'scalar group'),
                                           ({},['unknown'],'unknown profile')]:
                    submit(extra=extra,groups=groups)
                    assert state()==initial,label
                missing=submit(name='Missing plan',groups=['gold'],token=csrf())
                assert 'successfully updated' not in missing and state()==initial
                print('PASS malformed fields, unknown profile and missing plan leave state intact',file=sys.stderr)
                # Force a failure after header UPDATE, DELETE old associations and first INSERT.
                sql("DELETE FROM billing_plans_profiles WHERE plan_name = 'Existing plan'")
                sql("INSERT INTO billing_plans_profiles (plan_name,profile_name) VALUES ('Existing plan','member-only'),('outside','silver')")
                sql('ALTER TABLE billing_plans_profiles ADD UNIQUE KEY fixture_unique_profile (profile_name)')
                before_failure=state()
                failed=submit(groups=['gold','silver'],extra={'planId':'must-not-stick'})
                assert 'Failed to update' in failed and 'Duplicate entry' not in failed
                assert state()==before_failure
                sql('ALTER TABLE billing_plans_profiles DROP KEY fixture_unique_profile')
                print('PASS second mapping failure restores old header and associations',file=sys.stderr)
                empty=submit()
                assert 'successfully updated' in empty
                assert 'Existing plan\t' not in state()['profiles']
                quoted=submit(name="Hold 50% & O'Reilly",groups=["O'Reilly"])
                assert 'successfully updated' in quoted
                assert "Hold 50% & O'Reilly\tO'Reilly" in state()['profiles']
                print('PASS empty selection and quoted percent-bearing names',file=sys.stderr)
                session(9001,'named')
                named=submit(groups=['member-only'])
                assert 'successfully updated' in named
                assert 'Existing plan\tmember-only' in state()['profiles']
                print('PASS named location',file=sys.stderr)
            logs=run('docker','logs',WEB,check=True)
            assert 'PHP Fatal error' not in logs and 'PHP Warning:' not in logs,logs[-1000:]
            print(json.dumps({'initial':initial}))
        finally:
            for name in (WEB,DB): run('docker','rm','-f','-v',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            run('docker','run','--rm','-v',f'{fixture}:/fixtures','--entrypoint','sh',
                'lirantal/daloradius','-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')

if __name__=='__main__': main()
