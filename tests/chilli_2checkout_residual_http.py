#!/usr/bin/env python3
"""R26: retire the remaining 2Checkout public receipt; remove unreachable signup.

Native PHP HTTP/CLI + disposable MariaDB. Characterize the pinned historical
reader separately from intentional 410 retirement; no payment/provider call.
No live config/data is copied and no credentials or request snapshots are saved.
"""
import concurrent.futures
import json
import os
from pathlib import Path
import re
import secrets
import shutil
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request

ROOT=Path(__file__).resolve().parents[1]
BASE='7279daab04783a97aa8160dc69646d098f82f6f8'
FAMILY='contrib/chilli/portal2/signup-2checkout'
TAG='r26-'+secrets.token_hex(6)
DB,WEB,NET=(TAG+'-'+suffix for suffix in ('db','web','net'))
IMAGE='lirantal/daloradius'
MESSAGE=b'Legacy 2Checkout signup and payment callbacks are retired. No account activation is available.'
ROUTES=('index.php','success.php','2co_start.php','2co_ipn.php','include/common/provisionUser.php','include/common/retired.php')
TABLES=('history_merchant','userinfo','userbillinfo','radcheck','radreply','radusergroup','billing_history','billing_plans')

def run(*args,input=None,check=True):
    p=subprocess.run(args,input=input,capture_output=True,text=True,timeout=60)
    if check and p.returncode:raise RuntimeError('Fixture command failed: '+args[0]+'; details suppressed')
    return p

def sql(query):
    p=run('docker','exec','-i',DB,'mariadb','-uroot','--default-character-set=utf8mb4','-N','-B','fixture',input=query,check=False)
    if p.returncode:
        code=re.search(r'ERROR\s+(\d+)',p.stderr)
        raise RuntimeError('Fixture SQL error '+(code.group(1) if code else 'unavailable'))
    return p.stdout.rstrip('\n')

def state():return tuple(sql('SELECT * FROM '+table+' ORDER BY id') for table in TABLES)

def wait(fn):
    end=time.monotonic()+30
    while time.monotonic()<end:
        try:
            if fn():return
        except (RuntimeError,OSError,urllib.error.URLError):pass
        time.sleep(.25)
    raise RuntimeError('Fixture readiness failed')

def request(url,method='GET',data=None,content_type='application/x-www-form-urlencoded'):
    if isinstance(data,dict):data=urllib.parse.urlencode(data,doseq=True).encode()
    req=urllib.request.Request(url,method=method,data=None if method in ('GET','HEAD') else data,headers={'Content-Type':content_type})
    try:response=urllib.request.urlopen(req,timeout=20)
    except urllib.error.HTTPError as error:response=error
    return response.status,response.read(),{k.lower():v for k,v in response.headers.items()}

def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    checks=[];comparisons=[]
    with tempfile.TemporaryDirectory(prefix=TAG+'-',dir=scratch) as directory:
        fixture=Path(directory)
        try:
            run('docker','network','create','--internal',NET)
            run('docker','run','-d','--name',DB,'--network',NET,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=fixture','mariadb:11.8')
            # The entrypoint's temporary bootstrap server uses the Unix socket;
            # require TCP readiness so its shutdown cannot race schema imports.
            wait(lambda:run('docker','exec',DB,'mariadb','-h127.0.0.1','-uroot',
                            '-N','-B','fixture','-e','SELECT 1',check=False).returncode==0)
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):sql((ROOT/'contrib/db'/name).read_text())
            sql('RENAME TABLE billing_merchant TO history_merchant;')
            for status in ('Pending','Completed','Denied','Failed',''):
                sql("INSERT INTO history_merchant(username,txnId,planName,planId,vendor_type,payment_status) VALUES ('fixture-receipt','history-"+status+"','Fixture',101,'2Checkout','"+status+"');")
            sql("INSERT INTO history_merchant(username,txnId,planName,planId,vendor_type,payment_status) VALUES "
                "('fixture-other-vendor','history-other','Fixture',101,'PayPal','Completed'),"
                "('<script>fixture()</script>','history-markup','Fixture',101,'2Checkout','Completed');"
                "INSERT INTO userinfo(username,firstname,lastname) VALUES ('already-active','Fixture','Person');"
                "INSERT INTO userbillinfo(username,planname) VALUES ('already-active','Fixture');"
                "INSERT INTO radcheck(username,attribute,op,value) VALUES ('already-active','Auth-Type',':=','Accept');"
                "INSERT INTO radreply(username,attribute,op,value) VALUES ('already-active','Filter-Id','=','Fixture');"
                "INSERT INTO radusergroup(username,groupname,priority) VALUES ('already-active','Fixture',0);")
            initial=state()
            for version in ('base','candidate'):
                family=fixture/version/FAMILY
                shutil.copytree(ROOT/FAMILY,family,ignore=shutil.ignore_patterns('daloradius.conf.php','*.log'))
                shutil.copytree(ROOT/'contrib/chilli/common',fixture/version/'contrib/chilli/common')
                common=fixture/version/'app/common/includes';common.mkdir(parents=True)
                shutil.copy2(ROOT/'app/common/includes/pdo_connection.php',common/'pdo_connection.php')
                if version=='base':
                    # Keep historical receipt and its PEAR loader dependencies coherent.
                    provider='contrib/chilli/common/database.php'
                    (fixture/version/provider).write_text(run('git','show',BASE+':'+provider).stdout)
                    for name in ('index.php','success.php','library/opendb.php','library/closedb.php','library/config_read.php'):
                        (family/name).write_text(run('git','show',BASE+':'+FAMILY+'/'+name).stdout)
                # Only ephemeral settings; never read/copy the repository's actual config.
                (family/'library/daloradius.conf.php').write_text("""<?php
$configValues=array('CONFIG_DB_ENGINE'=>'mysqli','CONFIG_DB_HOST'=>getenv('FIXTURE_HOST'),
'CONFIG_DB_USER'=>'root','CONFIG_DB_PASS'=>'','CONFIG_DB_NAME'=>'fixture','CONFIG_DB_PORT'=>'3306',
'CONFIG_DB_TBL_DALOBILLINGMERCHANT'=>'history_merchant',
'CONFIG_MERCHANT_SUCCESS_MSG_PRE'=>'Waiting','CONFIG_MERCHANT_SUCCESS_MSG_POST'=>'Confirmed',
'CONFIG_MERCHANT_SUCCESS_MSG_HEADER'=>'Fixture receipt');
""")
                (family/'library/daloradius.conf.php').chmod(0o600)
            (fixture/'health.php').write_text('<?php echo "ready";')
            run('docker','run','-d','--name',WEB,'--network',NET,'-v',str(fixture)+':/fixtures:ro',
                '-e','FIXTURE_HOST='+DB,'-e','PHP_CLI_SERVER_WORKERS=4','--entrypoint','php',IMAGE,
                '-d','display_errors=0','-d','log_errors=1','-d','opcache.enable=0','-d','opcache.enable_cli=0',
                '-d','error_reporting=32767','-d','include_path=/fixtures/mock:/usr/share/php','-S','0.0.0.0:8080','-t','/fixtures')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB).stdout.strip()
            base='http://'+ip+':8080/';wait(lambda:request(base+'health.php')[0]==200)
            def endpoint(version,name):return base+version+'/'+FAMILY+'/'+name
            # Pinned read behavior is intentionally not carried forward as an active route.
            for status in ('Pending','Completed','Denied','Failed',''):
                query='?'+urllib.parse.urlencode({'txnId':'history-'+status,'payment_status':'Completed'})
                old=request(endpoint('base','success.php')+query);new=request(endpoint('candidate','success.php')+query)
                assert old[0]==200 and (b'Your user PIN' in old[1])==(status=='Completed')
                assert (b'http-equiv="refresh"' in old[1])==(status!='Completed')
                assert new[0]==410 and new[1]==MESSAGE
                comparisons.append('receipt:'+status)
            old=request(endpoint('base','success.php')+'?txnId=history-other')
            assert old[0]==200 and b'fixture-other-vendor' in old[1]  # no vendor filter
            assert request(endpoint('candidate','success.php')+'?txnId=history-other')[:2]==(410,MESSAGE)
            comparisons.append('cross-vendor historical disclosure')
            old=request(endpoint('base','success.php')+'?txnId=history-markup')
            assert old[0]==200 and b'<script>fixture()</script>' in old[1]  # unescaped stored identity
            assert request(endpoint('candidate','success.php')+'?txnId=history-markup')[:2]==(410,MESSAGE)
            comparisons.append('stored markup historical disclosure')
            old=request(endpoint('base','index.php'),method='POST',data={'submit':'submit','planId':'101'})
            assert old[:2]==request(endpoint('candidate','index.php'),method='POST',data={'submit':'submit','planId':'101'})[:2]==(410,MESSAGE)
            comparisons.append('signup retirement exact parity')
            assert state()==initial
            checks.append('pinned receipt states/cross-vendor/markup characterized; deliberate retirement; unchanged active accounts/history')
            marker='UNTRUSTED_R26_'+secrets.token_hex(12)
            def retired(route,method='GET',data=None,query='',content_type='application/x-www-form-urlencoded'):
                status,body,headers=request(endpoint('candidate',route)+query,method,data,content_type)
                assert status==410 and body==(b'' if method=='HEAD' else MESSAGE)
                assert headers.get('content-type')=='text/plain; charset=UTF-8'
                assert headers.get('cache-control')=='no-store' and headers.get('x-content-type-options')=='nosniff'
                assert not any(k in headers for k in ('location','set-cookie','refresh'))
            connections=int(sql("SHOW GLOBAL STATUS LIKE 'Connections'").split('\t')[1])
            for route in ROUTES:
                for method in ('GET','POST','HEAD','PUT','DELETE','OPTIONS'):
                    retired(route,method,data={'submit':'submit','payment_status':'Completed','force':'1','marker':marker})
                for query in ('', '?txnId=history-Completed&payment_status=Completed','?txnId[]=history-Completed',
                              '?txnId=', '?txnId='+urllib.parse.quote("' OR 1=1 --"), '?txnId='+('x'*201)):
                    retired(route,query=query)
                for payload,kind in (({'txnId[]':['history-Completed','invalid'],'force[]':['1']},'application/x-www-form-urlencoded'),
                    ({'txnId':"quote'-%-é-<script>",'marker':marker},'application/x-www-form-urlencoded'),
                    (b'x'*70000,'application/x-www-form-urlencoded'),
                    (json.dumps({'payment_status':'Completed','marker':marker}).encode(),'application/json')):
                    retired(route,'POST',data=payload,content_type=kind)
            with concurrent.futures.ThreadPoolExecutor(4) as pool:
                list(pool.map(lambda n:retired(ROUTES[n%len(ROUTES)],'POST',data={'force':'1','marker':marker}),range(12)))
            after=int(sql("SHOW GLOBAL STATUS LIKE 'Connections'").split('\t')[1])
            assert after==connections+1  # the second measuring CLI connection, no HTTP SQL connections
            assert state()==initial
            checks.append('six-route methods/query/malformed/Unicode/oversize/JSON/concurrent matrix; native zero-DB-connection measurement')
            candidate=fixture/'candidate'/FAMILY
            trap="<?php throw new RuntimeException('R26 fixture dependency tripwire reached');\n"
            for rel in ('library/config_read.php','library/opendb.php','library/closedb.php','library/daloradius.conf.php'):
                (candidate/rel).write_text(trap)
            (fixture/'mock').mkdir();(fixture/'mock/DB.php').write_text(trap)
            (fixture/'candidate/contrib/chilli/common/database.php').write_text(trap)
            (fixture/'candidate/app/common/includes/pdo_connection.php').write_text(trap)
            for route in ROUTES:retired(route,'POST',data={'txnId':'history-Completed','marker':marker})
            assert state()==initial
            checks.append('config/open/close/PEAR/PDO tripwires unreachable before request processing')
            # CLI preserves the existing retired endpoints' exit-0/body contract, no config.
            for route in ('index.php','success.php'):
                for cwd in ('/','/fixtures','/fixtures/candidate/'+FAMILY):
                    for args in ((),('--force',),('--txnId','history-Completed'),('--help',)):
                        p=run('docker','exec','-w',cwd,WEB,'php','-d','display_errors=0',
                            '/fixtures/candidate/'+FAMILY+'/'+route,*args)
                        assert p.returncode==0 and p.stdout.encode()==MESSAGE and p.stderr==''
            assert state()==initial;checks.append('native CLI from unrelated cwd and misleading flags; no SQL or payload reflection')
            run('docker','stop',DB)
            for rel in ('library','include/merchant'):shutil.rmtree(candidate/rel)
            shutil.rmtree(fixture/'candidate/contrib/chilli/common');shutil.rmtree(fixture/'candidate/app');shutil.rmtree(fixture/'mock')
            for route in ROUTES:retired(route,'POST',data={'txnId':'history-Completed','marker':marker})
            p=run('docker','exec',WEB,'php','/fixtures/candidate/'+FAMILY+'/success.php')
            assert p.stdout.encode()==MESSAGE and p.stderr==''
            checks.append('all routes stay retired with stopped DB and missing config/SDK/PEAR/PDO providers')
            logs=run('docker','logs',WEB);logs=logs.stdout+logs.stderr
            assert marker not in logs and not re.search(r'PHP (?:Warning|Fatal error|Notice|Deprecated):',logs)
            assert not list(fixture.rglob('*.log'))
            checks.append('clean PHP logs and no request/config/signature/payload artifacts')
            print('PASS R26',len(comparisons),'keyed baseline/candidate comparisons (retirement, not PDO parity);',len(checks),'native control families',flush=True)
        finally:
            for name in (WEB,DB):run('docker','rm','-f',name,check=False)
            run('docker','network','rm',NET,check=False)
            for name in (WEB,DB):assert run('docker','inspect',name,check=False).returncode!=0
            assert run('docker','network','inspect',NET,check=False).returncode!=0
    assert not fixture.exists();print('PASS R26 fixture/config/container/network cleanup',flush=True)

if __name__=='__main__':main()
