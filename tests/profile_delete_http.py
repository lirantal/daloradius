#!/usr/bin/env python3
"""UNIT-020: disposable HTTP/PHP/MariaDB profile deletion A/B and rollback."""
import html
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
BASELINE=os.environ.get('PROFILE_DELETE_BASELINE')=='1'
DB,WEB,NETWORK=h.DB,h.WEB,h.NETWORK
run,sql,wait_for=h.run,h.sql,h.wait_for


def main():
    scratch=Path.home()/'.hermes/cache/scratch'
    scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(dir=scratch,prefix='dalo-profile-delete-') as tmp:
        fixture=Path(tmp)
        shutil.copytree(ROOT/'app',fixture/'app',symlinks=True)
        if BASELINE:
            old=subprocess.check_output(['git','show','ab3523b3e:app/operators/mng-rad-profiles-del.php'],cwd=ROOT)
            (fixture/'app/operators/mng-rad-profiles-del.php').write_bytes(old)
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,
                '--tmpfs','/var/lib/mysql','-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            sql("""INSERT INTO operators_acl (operator_id,file,access) VALUES
                     (9001,'mng_rad_profiles_del',1),(9002,'mng_rad_profiles_del',0);
                   INSERT INTO radgroupcheck (id,groupname,attribute,op,value) VALUES
                     (9001,'unit-gold','Auth-Type',':=','Accept'),
                     (9002,'unit-gold','Session-Timeout',':=','60'),
                     (9003,'unit-silver','Auth-Type',':=','Accept'),
                     (9004,'unit-keep','Auth-Type',':=','Accept'),
                     (9005,'unit-attr','Auth-Type',':=','Accept'),
                     (9006,'unit-attr','Session-Timeout',':=','90'),
                     (9007,'unit-50% O''Reilly__v','Auth-Type',':=','Accept'),
                     (9008,'unit-map-only','Auth-Type',':=','Accept'),
                     (9009,'unit-équipe','Auth-Type',':=','Accept');
                   INSERT INTO radgroupreply (id,groupname,attribute,op,value) VALUES
                     (9010,'unit-gold','Reply-Message',':=','gold'),
                     (9011,'unit-silver','Reply-Message',':=','silver'),
                     (9012,'unit-keep','Reply-Message',':=','keep'),
                     (9013,'unit-attr','Reply-Message',':=','attr'),
                     (9014,'unit-50% O''Reilly__v','Reply-Message',':=','special'),
                     (9015,'unit-map-only','Reply-Message',':=','mapping');
                   INSERT INTO radusergroup (username,groupname,priority) VALUES
                     ('alice','unit-gold',0),('bob','unit-silver',0),
                     ('keep','unit-keep',0),('attr','unit-attr',0),
                     ('special','unit-50% O''Reilly__v',0),
                     ('mapped','unit-map-only',0),('equipe','unit-équipe',0);
                   INSERT INTO billing_plans (id,planName,planActive) VALUES
                     (100,'plan-A','yes'),(101,'plan-B','yes');
                   INSERT INTO billing_plans_profiles (plan_name,profile_name) VALUES
                     ('plan-A','unit-gold'),('plan-A','unit-silver'),
                     ('plan-A','unit-keep'),('plan-A','unit-attr'),
                     ('plan-B','unit-50% O''Reilly__v'),
                     ('plan-B','unit-map-only'),('plan-B','unit-équipe');""")
            config=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,val in {'CONFIG_DB_HOST':DB,'CONFIG_DB_USER':'root',
                            'CONFIG_DB_PASS':'','CONFIG_DB_NAME':'radius'}.items():
                config+='\n$configValues['+repr(key)+'] = '+repr(val)+';\n'
            (fixture/'app/common/includes/daloradius.conf.php').write_text(config)
            (fixture/'session.php').write_text('''<?php
session_name('daloradius_operator_sid');session_id($argv[1]);session_start();
$_SESSION=['daloradius_logged_in'=>true,'operator_id'=>(int)$argv[2],
'operator_user'=>'profile-fixture','location_name'=>'default','time'=>time()];
session_write_close();
''')
            run('docker','run','-d','--name',WEB,'--network',NETWORK,
                '-v',f'{fixture}:/fixtures','-w','/fixtures/app/operators',
                '--entrypoint','php','lirantal/daloradius','-d','display_errors=0',
                '-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f',
                '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/mng-rad-profiles-del.php'
            wait_for(lambda:urllib.request.urlopen(base,timeout=10),'PHP HTTP')
            sid=secrets.token_hex(16)
            def session(operator=9001):
                run('docker','exec',WEB,'php','/fixtures/session.php',sid,str(operator))
            def request(data=None,query=None,authenticated=True):
                url=base+('?' + urllib.parse.urlencode(query) if query else '')
                req=urllib.request.Request(url,
                    data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),
                    headers={'Cookie':'daloradius_operator_sid='+sid} if authenticated else {})
                try:
                    with urllib.request.urlopen(req,timeout=30) as response:
                        return response.url,response.read().decode()
                except urllib.error.HTTPError as error:
                    return error.url,error.read().decode()
            def csrf():
                return next(f['csrf_token'] for f in Forms(request()[1]).forms if 'csrf_token' in f)
            def send(payload,token=None):
                data={'csrf_token':csrf() if token is None else token}
                data.update(payload)
                return request(data)[1]
            def state():
                return {key:sql(query) for key,query in {
                    'check':'SELECT id,groupname,attribute FROM radgroupcheck ORDER BY id',
                    'reply':'SELECT id,groupname,attribute FROM radgroupreply ORDER BY id',
                    'users':'SELECT username,groupname,priority FROM radusergroup ORDER BY username,id',
                    'plans':'SELECT plan_name,profile_name FROM billing_plans_profiles ORDER BY id',
                }.items()}
            session()
            initial=state()
            assert request(authenticated=False)[0].endswith('login.php')
            session(9002)
            assert request()[0].endswith('home-error.php')
            session()
            assert 'removed' not in send({'profile_names[]':['unit-gold']},token='invalid').lower()
            assert state()==initial
            print('PASS auth, ACL and CSRF',file=sys.stderr)
            if not BASELINE:
                for bad in ({'profile_names[]':['unit-gold','missing']},
                            {'profile_names[]':['unit-gold'],'profile_delete_assoc[]':'yes'},
                            {'profile_names[]':['unit-gold'],'profile__id__table[]':['unit-gold__9001__radgroupcheck']},
                            {'profile__id__table[]':['unit-silver__9010__radgroupreply']},
                            {'profile__id__table[]':['unit-gold__9001__radcheck']},
                            {'profile__id__table[]':['unit-gold__9001__radgroupcheck','unit-keep__9001__radgroupcheck']}):
                    response=send(bad)
                    assert 'no changes were saved' in response,response[:500]
                    assert state()==initial,bad
                sql("DELIMITER //\nCREATE TRIGGER fixture_second_profile BEFORE DELETE ON radgroupreply "
                    "FOR EACH ROW BEGIN IF OLD.groupname='unit-silver' THEN "
                    "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='later profile failed'; "
                    "END IF; END//\nDELIMITER ;\n")
                failed=send({'profile_names[]':['unit-gold','unit-silver']})
                assert 'no changes were saved' in failed and 'later profile failed' not in failed
                assert state()==initial
                sql('DROP TRIGGER fixture_second_profile')
                sql("DELIMITER //\nCREATE TRIGGER fixture_second_attribute BEFORE DELETE ON radgroupreply "
                    "FOR EACH ROW BEGIN IF OLD.groupname='unit-attr' THEN "
                    "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='later attribute failed'; "
                    "END IF; END//\nDELIMITER ;\n")
                failed=send({'profile__id__table[]':['unit-attr__9005__radgroupcheck',
                                                      'unit-attr__9013__radgroupreply']})
                assert 'no changes were saved' in failed and state()==initial
                sql('DROP TRIGGER fixture_second_attribute')
                print('PASS malformed/stale selections and late profile/attribute rollback',file=sys.stderr)
            # One attribute leaves the profile active and mapped.
            response=send({'profile__id__table[]':['unit-attr__9005__radgroupcheck']})
            assert 'profile(s) have been deleted/modified' in response,response[:600]
            attr_partial=state()
            assert '9005\tunit-attr\t' not in attr_partial['check']
            assert 'unit-attr' in attr_partial['users'] and 'unit-attr' in attr_partial['plans']
            # Delete remaining check/reply attributes; user and plan mappings are now orphaned.
            response=send({'profile__id__table[]':['unit-attr__9006__radgroupcheck',
                                                  'unit-attr__9013__radgroupreply']})
            assert 'profile(s) have been deleted/modified' in response
            attr_final=state()
            assert 'unit-attr' not in attr_final['check']+attr_final['reply']+attr_final['users']
            if not BASELINE:
                assert 'unit-attr' not in attr_final['plans']
            print('PASS attribute path with still-active and emptied profiles',file=sys.stderr)
            # Mapping-only retains both attributes and plan associations.
            response=send({'profile_names[]':['unit-map-only'],'profile_delete_assoc':'yes'})
            assert 'Removed all user mappings for' in response,response[:600]
            mapped=state()
            assert 'unit-map-only' not in mapped['users']
            assert 'unit-map-only' in mapped['check'] and 'unit-map-only' in mapped['reply']
            assert 'unit-map-only' in mapped['plans']
            if not BASELINE:
                assert '1 profile(s)' in response
            response=send({'profile_names[]':['unit-gold','unit-silver']})
            assert 'Completely removed attributes and user mappings for' in response,response[:600]
            after=state()
            for key in ('check','reply','users'):
                for name in ('unit-gold','unit-silver','unit-attr'):
                    assert name not in after[key],(key,name)
                assert 'unit-keep' in after[key],key
            reference=os.environ.get('PROFILE_DELETE_REFERENCE')
            if reference:
                projection={key:after[key] for key in ('check','reply','users')}
                if BASELINE:
                    Path(reference).write_text(json.dumps(projection,sort_keys=True))
                else:
                    assert projection==json.loads(Path(reference).read_text()),'PEAR/PDO state mismatch'
            if not BASELINE:
                for name in ('unit-gold','unit-silver','unit-attr'):
                    assert name not in after['plans']
                assert 'unit-keep' in after['plans']
                assert '2 profile(s)' in response
                assert 'no changes were saved' in send({'profile_names[]':['unit-gold']})
                assert state()==after
                special="unit-50% O'Reilly__v"
                get_page=request(query={'profile_name':special})[1]
                assert special in html.unescape(get_page)
                assert 'name="profile_names[]"' in get_page
                direct=request(query={'profile_name':special,'id':'9007','tablename':'radgroupcheck'})[1]
                assert 'profile__id__table[]' in direct
                special_attribute=send({'profile__id__table[]':[
                    special+'__9007__radgroupcheck']})
                assert '1 profile(s) have been deleted/modified' in special_attribute
                assert special not in state()['check'] and special in state()['reply']
                assert '1 profile(s)' in send({'profile_names[]':[special]})
                assert special not in '\n'.join(state().values())
                assert '1 profile(s)' in send({'profile_names[]':['unit-équipe']})
                assert 'unit-équipe' not in '\n'.join(state().values())
                before_engine=state()
                sql('ALTER TABLE billing_plans_profiles ENGINE=MyISAM')
                assert 'no changes were saved' in send({'profile_names[]':['unit-keep']})
                assert state()==before_engine
                sql('ALTER TABLE billing_plans_profiles ENGINE=InnoDB')
                print('PASS mapping cleanup, quoted/percent/Unicode identity and engine preflight',file=sys.stderr)
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            assert 'PHP Fatal error' not in logs.stderr
            print('PASS UNIT-020 '+('PEAR baseline' if BASELINE else 'PDO candidate'))
        finally:
            for item in (WEB,DB):run('docker','rm','-f','-v',item,check=False)
            run('docker','network','rm',NETWORK,check=False)
            run('docker','run','--rm','-v',f'{fixture}:/fixtures','--entrypoint','sh',
                'lirantal/daloradius','-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')

if __name__=='__main__':main()
