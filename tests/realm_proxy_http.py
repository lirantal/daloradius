#!/usr/bin/env python3
"""UNIT-035 isolated HTTP/PHP/MariaDB proxy/realm and generated-file test."""
import os
import re
import secrets
import shutil
import subprocess
import tempfile
import time
import urllib.parse
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap

import operator_login_http as auth
from operator_login_http import Client, FormParser, hash_password, login, quote, run, sql, wait_for

ROOT=Path(__file__).resolve().parents[1]
BASELINE=os.environ.get('REALM_PROXY_BASELINE')=='1'
BASE_COMMIT='9cf49ec788a9cd3f85f13c17dae935d997a21db3'
DB,WEB,NETWORK=auth.DB,auth.WEB,auth.NETWORK


def csrf(client,url):
    status,_,_,page=client.request(url)
    assert status==200,(url,status)
    parser=FormParser();parser.feed(page)
    assert parser.csrf,(url,page[:200])
    return parser.csrf


def post(client,url,data):
    status,_,_,page=client.request(url,list(data))
    assert status==200,(url,status)
    return page


def state():
    # Secret columns are deliberately absent from recorded checkpoints.
    return (sql('SELECT id,HEX(proxyname),retry_delay,retry_count,dead_time,default_fallback '
                'FROM proxys ORDER BY id'),
            sql('SELECT id,HEX(realmname),HEX(type),HEX(authhost),HEX(accthost),'
                'LENGTH(secret),ldflag,nostrip,hints,notrealm FROM realms ORDER BY id'))


def proxy_create(client,name):
    token=csrf(client,'mng-rad-proxys-new.php')
    page=post(client,'mng-rad-proxys-new.php',[
        ('csrf_token',token),('proxyname',name),('retry_delay','4'),('retry_count','3'),
        ('dead_time','5'),('default_fallback','1')])
    return page


def realm_create(client,name,secret):
    token=csrf(client,'mng-rad-realms-new.php')
    page=post(client,'mng-rad-realms-new.php',[
        ('csrf_token',token),('realmname',name),('type','fail-over'),('nostrip','yes'),
        ('authhost','auth.example:1812'),('accthost','acct.example:1813'),
        ('secret',secret),('ldflag',''),('hints','0'),('notrealm','0')])
    return page


def proxy_edit(client,pid,name):
    token=csrf(client,'mng-rad-proxys-edit.php?item=proxy-'+str(pid))
    return post(client,'mng-rad-proxys-edit.php',[
        ('csrf_token',token),('item','proxy-'+str(pid)),('proxyname',name),
        ('retry_delay','9'),('retry_count','2'),('dead_time','6'),('default_fallback','1')])


def realm_edit(client,name,secret):
    token=csrf(client,'mng-rad-realms-edit.php?realmname='+urllib.parse.quote(name))
    return post(client,'mng-rad-realms-edit.php',[
        ('csrf_token',token),('realmname',name),('type','fail-over'),('nostrip','yes'),
        ('authhost','auth2.example:1812'),('accthost','acct.example:1813'),
        ('secret',secret),('ldflag',''),('hints','0'),('notrealm','0')])


def delete(client,kind,names):
    plural='proxys' if kind=='proxy' else 'realms'
    key='proxyname' if kind=='proxy' else 'realmname'
    token=csrf(client,'mng-rad-'+plural+'-del.php')
    return post(client,'mng-rad-'+plural+'-del.php',
                [('csrf_token',token)]+[(key+'[]',name) for name in names])


def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    operator='rp-op-'+secrets.token_hex(5);password=secrets.token_urlsafe(22)
    realm_secret=secrets.token_urlsafe(24)
    proxy='proxy-'+secrets.token_hex(4);realm='realm-'+secrets.token_hex(4)
    with tempfile.TemporaryDirectory(prefix='dalo-realm-proxy-',dir=scratch) as directory:
        root=Path(directory);shutil.copytree(ROOT/'app',root/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
        configfile=root/'proxy.conf';configfile.write_text('# daloradius - initial\n\n')
        initial_stat=configfile.stat()
        if BASELINE:
            restore_pear_bootstrap((root / 'app').parent, BASE_COMMIT)
            for filename in ['mng-rad-'+kind+'-'+action+'.php' for kind in ('proxys','realms')
                             for action in ('new','edit','del')]:
                old=run('git','show',BASE_COMMIT+':app/operators/'+filename)
                (root/'app/operators'/filename).write_text(old+'\n')
            old=run('git','show',BASE_COMMIT+':app/operators/include/management/saveRealmsProxys.php')
            (root/'app/operators/include/management/saveRealmsProxys.php').write_text(old+'\n')
        else:
            # Fixture-only fault injection immediately after atomic file publication.
            helper=root/'app/operators/include/management/realmProxyPdo.php'
            source=helper.read_text()
            marker="$published = true;\n        $commitAttempted = true;"
            assert source.count(marker)==1
            hook=("$published = true;\n"
                  "        if (is_file('/fixtures/force-commit-failure')) { "
                  "@unlink('/fixtures/force-commit-failure'); "
                  "throw new RuntimeException('injected post-publication failure'); }\n"
                  "        $commitAttempted = true;")
            source=source.replace(marker,hook)
            marker="$commitAttempted = true;\n        if (!$pdo->commit())"
            assert source.count(marker)==1
            hook=("$commitAttempted = true;\n"
                  "        if (is_file('/fixtures/force-commit-uncertain')) { "
                  "@unlink('/fixtures/force-commit-uncertain'); "
                  "throw new RuntimeException('injected commit uncertainty'); }\n"
                  "        if (!$pdo->commit())")
            helper.write_text(source.replace(marker,hook))
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
                'CONFIG_FILE_RADIUS_PROXY':'/fixtures/proxy.conf',
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
            for kind in ('proxys','realms'):
                for action in ('new','edit','del'):
                    perm='mng_rad_'+kind+'_'+action
                    sql('INSERT INTO operators_acl (operator_id,file,access) VALUES (%s,%s,1)' %
                        (oid,quote(perm)))
            client=Client(base)
            status,_,headers,_=login(client,operator,password,'local','default')
            assert status==302 and headers.get('Location','').endswith('index.php')
            page=proxy_create(client,proxy)
            assert 'Successfully inserted new proxy' in page,(page[:2000])
            pid=sql('SELECT id FROM proxys WHERE proxyname=%s' % quote(proxy))
            assert pid.isdigit()
            page=realm_create(client,realm,realm_secret)
            assert 'Successfully inserted new realm' in page,(page[:2000])
            assert sql('SELECT secret=%s FROM realms WHERE realmname=%s' %
                       (quote(realm_secret),quote(realm)))=='1'
            content=configfile.read_text()
            assert ('proxy '+proxy+' {') in content and ('realm '+realm+' {') in content
            assert realm_secret in content and 'nostrip' in content
            print('PASS: proxy and realm creation persist to DB and generated config')

            page=proxy_edit(client,pid,proxy)
            assert 'Successfully updated proxy' in page,(page[:2000])
            assert sql('SELECT retry_delay FROM proxys WHERE id=%s' % pid)=='9'
            page=realm_edit(client,realm,realm_secret)
            assert 'Successfully updated realm' in page,(page[:2000])
            assert sql('SELECT authhost FROM realms WHERE realmname=%s' % quote(realm))=='auth2.example:1812'
            assert 'retry_delay = 9' in configfile.read_text() and 'auth2.example:1812' in configfile.read_text()
            print('PASS: edits update DB and regenerate both sections')

            page=delete(client,'proxy',[proxy]);assert 'Deleted proxy(s)' in page
            page=delete(client,'realm',[realm]);assert 'Deleted realm(s)' in page
            assert state()==('','') and realm_secret not in configfile.read_text()
            print('PASS: both deletions remove DB rows and generated entries')

            if not BASELINE:
                status,_,_,realm_form=client.request('mng-rad-realms-new.php')
                assert status==200 and re.search(r'name="nostrip"',realm_form)
                assert len(re.findall(r'name="type"',realm_form))==1
                print('PASS: realm form exposes separate type and nostrip controls')
                zero_name='realm-zero-'+secrets.token_hex(3)
                assert 'Successfully inserted' in realm_create(client,zero_name,'0')
                assert '\tsecret = 0\n' in configfile.read_text()
                assert 'Deleted realm(s)' in delete(client,'realm',[zero_name])
                print('PASS: literal zero realm secret is present in generated config')
                p1='proxy-a-'+secrets.token_hex(3);p2='proxy-b-'+secrets.token_hex(3)
                assert 'Successfully inserted' in proxy_create(client,p1)
                assert 'Successfully inserted' in proxy_create(client,p2)
                before=state();old=configfile.read_bytes()
                sql("DELIMITER //\nCREATE TRIGGER reject_proxy_del BEFORE DELETE ON proxys FOR EACH ROW "
                    "BEGIN IF OLD.proxyname="+quote(p2)+" THEN SIGNAL SQLSTATE '45000' "
                    "SET MESSAGE_TEXT='fixture later delete'; END IF; END//\nDELIMITER ;\n")
                try:
                    page=delete(client,'proxy',[p1,p2])
                    assert 'no change applied' in page and state()==before and configfile.read_bytes()==old
                    assert 'fixture later delete' not in page
                finally:sql('DROP TRIGGER reject_proxy_del')
                print('PASS: later delete failure rolls back all DB rows and preserves file')

                pid=sql('SELECT id FROM proxys WHERE proxyname=%s' % quote(p1))
                renamed='proxy-%-'+secrets.token_hex(3)
                page=proxy_edit(client,pid,renamed)
                assert 'Successfully updated proxy' in page
                assert sql('SELECT COUNT(*) FROM proxys WHERE proxyname=%s' % quote(p1))=='0'
                assert sql('SELECT COUNT(*) FROM proxys WHERE proxyname=%s' % quote(renamed))=='1'
                assert ('proxy '+renamed+' {') in configfile.read_text()
                print('PASS: proxy edit renames the locked identity with literal percent')

                # All six entry points must enforce CSRF independently of helper validation.
                before=state();old=configfile.read_bytes()
                for kind in ('proxys','realms'):
                    for action in ('new','edit','del'):
                        url='mng-rad-'+kind+'-'+action+'.php'
                        data=[('csrf_token','invalid-token')]
                        if kind=='proxys':
                            data += [('item','proxy-'+pid),('proxyname',renamed)]
                        else:
                            data += [('realmname','missing-realm')]
                        page=post(client,url,data)
                        assert 'CSRF token error' in page
                        assert state()==before and configfile.read_bytes()==old
                print('PASS: invalid CSRF rejected by all six mutation pages')

                # Real SQL failures on both creation and edit must not publish a file.
                test_realm='realm-errors-'+secrets.token_hex(3)
                assert 'Successfully inserted' in realm_create(client,test_realm,realm_secret)
                for table in ('proxys','realms'):
                    for event in ('INSERT','UPDATE'):
                        before=state();old=configfile.read_bytes()
                        trigger='reject_'+table+'_'+event.lower()
                        sql('CREATE TRIGGER '+trigger+' BEFORE '+event+' ON '+table+
                            " FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture mutation rejected'")
                        try:
                            if table=='proxys':
                                page=proxy_create(client,'proxy-trigger-'+secrets.token_hex(3)) if event=='INSERT' \
                                    else proxy_edit(client,pid,renamed)
                            else:
                                page=realm_create(client,'realm-trigger-'+secrets.token_hex(3),realm_secret) if event=='INSERT' \
                                    else realm_edit(client,test_realm,realm_secret)
                            assert 'no change applied' in page
                            assert state()==before and configfile.read_bytes()==old
                            assert 'fixture mutation rejected' not in page
                        finally:
                            sql('DROP TRIGGER '+trigger)
                assert 'Deleted realm(s)' in delete(client,'realm',[test_realm])
                print('PASS: SQL insert/update failures preserve DB and config in both families')

                r1='realm-a-'+secrets.token_hex(3);r2='realm-b-'+secrets.token_hex(3)
                assert 'Successfully inserted' in realm_create(client,r1,realm_secret)
                assert 'Successfully inserted' in realm_create(client,r2,realm_secret)
                before=state();old=configfile.read_bytes()
                sql("DELIMITER //\nCREATE TRIGGER reject_realm_del BEFORE DELETE ON realms FOR EACH ROW "
                    "BEGIN IF OLD.realmname="+quote(r2)+" THEN SIGNAL SQLSTATE '45000' "
                    "SET MESSAGE_TEXT='fixture later realm delete'; END IF; END//\nDELIMITER ;\n")
                try:
                    page=delete(client,'realm',[r1,r2])
                    assert 'no change applied' in page and state()==before and configfile.read_bytes()==old
                finally:sql('DROP TRIGGER reject_realm_del')
                assert 'Deleted realm(s)' in delete(client,'realm',[r1,r2])
                print('PASS: later realm delete failure rolls back earlier deletion and file')

                # Synthetic fixture fault between atomic rename and database commit.
                before=state();old=configfile.read_bytes()
                (root/'force-commit-failure').write_text('')
                page=proxy_create(client,'proxy-publish-failure-'+secrets.token_hex(3))
                assert 'no change applied' in page and state()==before and configfile.read_bytes()==old
                assert not (root/'force-commit-failure').exists()
                assert not list(root.glob('.dalo-proxy-*'))
                print('PASS: injected post-publication error restores file and rolls back DB')

                before=state();old=configfile.read_bytes()
                (root/'force-commit-uncertain').write_text('')
                page=proxy_create(client,'proxy-uncertain-'+secrets.token_hex(3))
                assert 'state uncertain; verify file and database' in page
                assert state()==before and configfile.read_bytes()==old
                assert not (root/'force-commit-uncertain').exists()
                print('PASS: injected ambiguous commit warning requires manual verification')

                before=state();old=configfile.read_bytes()
                token=csrf(client,'mng-rad-realms-new.php')
                page=post(client,'mng-rad-realms-new.php',[
                    ('csrf_token',token),('realmname','realm-invalid-'+secrets.token_hex(3)),
                    ('secret[]',realm_secret)])
                assert 'no change applied' in page and state()==before and configfile.read_bytes()==old
                print('PASS: malformed realm controls cannot alter DB or generated file')

                before=state();old=configfile.read_bytes()
                missing=root/'missing.conf'
                configfile.rename(missing)
                try:
                    page=proxy_create(client,'proxy-failure-'+secrets.token_hex(3))
                    assert 'no change applied' in page and state()==before and not configfile.exists()
                finally:missing.rename(configfile)
                assert configfile.read_bytes()==old
                print('PASS: missing generated-file target prevents DB mutation')

                before=state();old=configfile.read_bytes()
                configfile.write_text('# administrator-owned proxy config\n')
                try:
                    page=proxy_create(client,'proxy-unmanaged-'+secrets.token_hex(3))
                    assert 'no change applied' in page and state()==before
                    assert configfile.read_text()=='# administrator-owned proxy config\n'
                    assert not list(root.glob('proxy.conf.orig-*'))
                finally:configfile.write_bytes(old)
                print('PASS: unsigned administrator file is not overwritten or backed up')

                before=state();old=configfile.read_bytes()
                target=root/'symlink-target.conf'
                configfile.rename(target)
                configfile.symlink_to(target)
                try:
                    page=proxy_create(client,'proxy-symlink-'+secrets.token_hex(3))
                    assert 'no change applied' in page and state()==before
                    assert configfile.is_symlink() and target.read_bytes()==old
                    assert not list(root.glob('.dalo-proxy-*'))
                finally:
                    configfile.unlink()
                    target.rename(configfile)
                print('PASS: symlink target is not replaced and its contents stay unchanged')

                before=state();old=configfile.read_bytes()
                stale='proxy-stale-'+secrets.token_hex(3)
                page=delete(client,'proxy',[renamed,stale])
                assert 'no change applied' in page and state()==before and configfile.read_bytes()==old
                print('PASS: stale later deletion rejects the whole selection')

                before=state();old=configfile.read_bytes()
                sql('ALTER TABLE realms ENGINE=MyISAM')
                try:
                    page=proxy_create(client,'proxy-nontrans-'+secrets.token_hex(3))
                    assert 'no change applied' in page and state()==before and configfile.read_bytes()==old
                finally:sql('ALTER TABLE realms ENGINE=InnoDB')
                print('PASS: nontransactional dependency rejected before write')

                lock_name=sql("SELECT CONCAT('daloradius:realm-proxy:',SUBSTRING(SHA2(DATABASE(),256),1,36))")
                token=csrf(client,'mng-rad-proxys-new.php')
                guard="$p=new PDO('mysql:host='.$argv[1].';dbname=radius','root','');" \
                      "$n=$argv[2];echo $p->query('SELECT GET_LOCK('.$p->quote($n).',0)')->fetchColumn();" \
                      "flush();sleep(2);$p->query('SELECT RELEASE_LOCK('.$p->quote($n).')');"
                holder=subprocess.Popen(['docker','exec',WEB,'php','-r',guard,DB,lock_name],
                    stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
                assert holder.stdout.read(1)=='1'
                start=time.monotonic();pending='proxy-lock-'+secrets.token_hex(3)
                page=post(client,'mng-rad-proxys-new.php',[
                    ('csrf_token',token),('proxyname',pending)])
                elapsed=time.monotonic()-start
                holder.communicate(timeout=20)
                assert 'Successfully inserted' in page and elapsed>=1.0
                assert sql('SELECT COUNT(*) FROM proxys WHERE proxyname=%s' % quote(pending))=='1'
                print('PASS: DB-scoped lock serializes configuration changes')

            observed=configfile.stat()
            assert (observed.st_uid,observed.st_gid,observed.st_mode & 0o777)==(
                initial_stat.st_uid,initial_stat.st_gid,initial_stat.st_mode & 0o777)
            assert not list(root.glob('.dalo-proxy-*'))
            print('PASS: file ownership/mode retained and no staged copies remain')
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,timeout=30,check=True)
            assert 'PHP Fatal error' not in logs.stdout+logs.stderr
            assert 'PHP Warning' not in logs.stdout+logs.stderr
            assert realm_secret not in logs.stdout+logs.stderr
            print('PASS: no PHP warnings, fatal errors or logged secret')
        finally:
            run('docker','exec','-u','root',WEB,'chown','-R',
                f'{os.getuid()}:{os.getgid()}','/fixtures',check=False)
            for container in (WEB,DB):run('docker','rm','-f','-v',container,check=False)
            run('docker','network','rm',NETWORK,check=False)
            print('CLEANUP: disposable realm/proxy fixture removed')

if __name__=='__main__':main()
