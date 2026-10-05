#!/usr/bin/env python3
"""UNIT-041: retired 2Checkout routes, SDK/provider fail-closed and legacy proof.

Real isolated HTTP/PHP/MariaDB, no remote gateway or real payment. Signing words
are generated at runtime, held in memory/container environment only and removed
with the disposable container. No secret/signature/payload snapshots or logs.
"""
import concurrent.futures
import hashlib
import json
import os
import re
from pathlib import Path
import secrets
import shutil
import subprocess
import tempfile
import urllib.error
import urllib.parse
import urllib.request

from operator_login_http import run, wait_for

ROOT=Path(__file__).resolve().parents[1]
BASE='352b3cb788435e6cdd22af5dbcf752a41f5cc179'
BASELINE=os.environ.get('CHILLI_2CHECKOUT_BASELINE')=='1'
TAG='u41-'+secrets.token_hex(6)
DB,WEB,NET=(TAG+'-'+x for x in ('db','web','net'))
FAMILY='contrib/chilli/portal2/signup-2checkout'
MESSAGE=b'Legacy 2Checkout signup and payment callbacks are retired. No account activation is available.'


def sql(query):
    p=subprocess.run(['docker','exec','-i',DB,'mariadb','-uroot','-N','-B','radius'],input=query,
                     capture_output=True,text=True,timeout=30)
    if p.returncode:
        code=re.search(r'ERROR\s+(\d+)',p.stderr)
        raise RuntimeError('Fixture SQL failed; error code '+(code.group(1) if code else 'unavailable')+'; driver details omitted')
    return p.stdout.strip()


def state():
    # Fixtures contain no authentication passwords or card data.
    return [sql('SELECT * FROM '+table+' ORDER BY id') for table in
            ['billing_merchant','userbillinfo','userinfo','radcheck','radusergroup','billing_history']]


def request(base,path,method='POST',data=b'',content_type='application/x-www-form-urlencoded'):
    if isinstance(data,dict):data=urllib.parse.urlencode(data,doseq=True).encode()
    req=urllib.request.Request(base+path,method=method,data=None if method in ('GET','HEAD') else data,
                              headers={'Content-Type':content_type})
    try:r=urllib.request.urlopen(req,timeout=20)
    except urllib.error.HTTPError as e:r=e
    return r.status,r.read(),{k.lower():v for k,v in r.headers.items()}


SDK_PROBE=r'''<?php
error_reporting(E_ALL & ~E_DEPRECATED);
chdir(__DIR__ . '/contrib/chilli/portal2/signup-2checkout');
require_once 'include/merchant/TwoCo.php';
$gateway=new TwoCo();$gateway->demo='N';$gateway->logIpn=false;
$word=bin2hex(random_bytes(32));$gateway->setSecret($word);
$_POST=array('vendor_number'=>'700001','sid'=>'700001','order_number'=>'900001','total'=>'10.00',
    'demo'=>'N','custom'=>'local-order-a','cart_order_id'=>'plan-a','credit_card_processed'=>'Y','x_MD5_Hash'=>'');
$_POST['key']=strtoupper(md5($word.$_POST['sid'].$_POST['order_number'].$_POST['total']));
$baseline=getenv('FIXTURE_BASELINE')==='1';$rejected=0;$accepted=array();
for($i=0;$i<2;$i++) {
    if($i===1) {$_POST['custom']='local-order-b';$_POST['cart_order_id']='plan-b';$_POST['credit_card_processed']='N';}
    try {$accepted[]=$gateway->validateIpn();} catch(LogicException $error) {$rejected++;}
}
if($baseline) {
    if($accepted!==array(true,true)||$rejected!==0)exit(1);
    echo json_encode(array('legacy_valid_and_tampered_both_accepted'=>true));
} else {
    if($accepted!==array()||$rejected!==2||!empty($gateway->ipnData))exit(1);
    require_once 'include/common/provisionUser.php';
    class FixtureSocket {public $calls=0;function __call($name,$args){$this->calls++;throw new RuntimeException('Unexpected SQL');}}
    $socket=new FixtureSocket();$blocked=false;
    try {provisionUser($socket,'synthetic-order');}catch(LogicException $error){$blocked=true;}
    if(!$blocked||$socket->calls!==0)exit(1);
    echo json_encode(array('validator_rejected_both'=>true,'provider_rejected_without_sql'=>true));
}
'''


def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='chilli-2checkout-',dir=scratch) as directory:
        fixture=Path(directory);family=fixture/FAMILY
        shutil.copytree(ROOT/FAMILY,family,ignore=shutil.ignore_patterns('daloradius.conf.php','*.log'))
        shutil.copytree(ROOT/'contrib/chilli/common',fixture/'contrib/chilli/common')
        common=fixture/'app/common/includes';common.mkdir(parents=True)
        shutil.copy2(ROOT/'app/common/includes/pdo_connection.php',common/'pdo_connection.php')
        if BASELINE:
            for rel in ['2co_ipn.php','2co_start.php','index.php','include/common/provisionUser.php','include/merchant/TwoCo.php']:
                (family/rel).write_text(run('git','show',BASE+':'+FAMILY+'/'+rel))
        (fixture/'health.php').write_text('<?php echo "ready";')
        (fixture/'sdk_probe.php').write_text(SDK_PROBE)
        cfg={'CONFIG_DB_ENGINE':'mysqli','CONFIG_DB_HOST':DB,'CONFIG_DB_PORT':'3306','CONFIG_DB_USER':'root','CONFIG_DB_PASS':'',
             'CONFIG_DB_NAME':'radius','CONFIG_DB_TBL_RADCHECK':'radcheck','CONFIG_DB_TBL_RADUSERGROUP':'radusergroup',
             'CONFIG_DB_TBL_RADGROUPCHECK':'radgroupcheck','CONFIG_DB_TBL_RADGROUPREPLY':'radgroupreply',
             'CONFIG_DB_TBL_DALOBILLINGPLANS':'billing_plans','CONFIG_DB_TBL_DALOBILLINGMERCHANT':'billing_merchant',
             'CONFIG_DB_TBL_DALOUSERBILLINFO':'userbillinfo','CONFIG_DB_TBL_DALOUSERINFO':'userinfo',
             'CONFIG_LOG_MERCHANT_IPN_FILENAME':'/fixtures/legacy-payload.log','CONFIG_MERCHANT_IPN_URL_ROOT':'https://fixture.invalid',
             'CONFIG_MERCHANT_IPN_URL_RELATIVE_SUCCESS':'success.php'}
        php_quote=lambda x:"'"+str(x).replace('\\','\\\\').replace("'","\\'")+"'"
        config='<?php\n'+''.join('$configValues[%s]=%s;\n'%(php_quote(k),php_quote(v)) for k,v in cfg.items())
        config+="$configValues['CONFIG_MERCHANT_IPN_SECRET']=getenv('FIXTURE_2CO_WORD');\n"
        (family/'library/daloradius.conf.php').write_text(config)
        (family/'library/daloradius.conf.php').chmod(0o600)
        word=secrets.token_hex(32);os.environ['FIXTURE_2CO_WORD']=word
        sentinel='NO_PAYLOAD_LOG_'+secrets.token_hex(16)
        try:
            run('docker','network','create','--internal',NET)
            run('docker','run','-d','--name',DB,'--network',NET,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            for name in ['fr3-mariadb-freeradius.sql','mariadb-daloradius.sql']:sql((ROOT/'contrib/db'/name).read_text())
            sql("INSERT INTO billing_plans(planId,planName,planType,planCost,planTax,planCurrency,planGroup) VALUES ('101','Fixture Plan','2Checkout','10.00','0','USD','fixture-group')")
            sql("INSERT INTO billing_merchant(username,txnId,planId,planName,vendor_type,payment_status) VALUES ('fixture-user','local-order-a',101,'Fixture Plan','2Checkout','')")
            sql("INSERT INTO userbillinfo(username,email,paymentmethod,planname) VALUES ('fixture-user','initial@example.invalid','Initial','Fixture Plan')")
            sql("INSERT INTO userinfo(username,firstname,lastname) VALUES ('fixture-user','Fixture','Person')")
            sql("INSERT INTO radcheck(username,attribute,op,value) VALUES ('already-active','Auth-Type',':=','Accept')")
            sql("INSERT INTO radusergroup(username,groupname,priority) VALUES ('already-active','existing-profile',0)")
            run('docker','run','-d','--name',WEB,'--network',NET,'-v',f'{fixture}:/fixtures',
                '-e','FIXTURE_2CO_WORD','-e','FIXTURE_BASELINE='+('1' if BASELINE else '0'),
                '-e','PHP_CLI_SERVER_WORKERS=4','--entrypoint','php','lirantal/daloradius',
                '-d','display_errors=0','-d','opcache.enable=0','-d','opcache.enable_cli=0',
                '-S','0.0.0.0:8080','-t','/fixtures')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/'
            wait_for(lambda:request(base,'health.php',method='GET')[0]==200,'PHP HTTP')
            probe=json.loads(run('docker','exec',WEB,'php','/fixtures/sdk_probe.php'))
            assert all(probe.values()) and len(probe)==(1 if BASELINE else 2)
            initial=state()
            fields={'vendor_number':'700001','sid':'700001','order_number':'900001','total':'10.00','demo':'N',
                    'custom':'local-order-a','cart_order_id':'101','credit_card_processed':'Y',
                    'card_holder_name':'Fixture  Person','email':'fixture@example.invalid','phone':'0',
                    'street_address':'Fixture Street','country':'US','city':'Fixture City','state':'Fixture State',
                    'zip':'00000','pay_method':'CC','key':hashlib.md5((word+'70000190000110.00').encode()).hexdigest().upper(),
                    'x_MD5_Hash':'','marker':sentinel}
            root=FAMILY+'/'
            if BASELINE:
                status,body,headers=request(base,root+'2co_ipn.php',data=fields)
                assert status==500 and state()==initial
                p=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True);logs=p.stdout+p.stderr
                assert 'Path must not be empty' in logs and 'PaymentGateway.php:193' in logs
                assert not any(v in logs or v.encode() in body for v in [word,fields['key'],sentinel])
                print('PASS: native baseline SDK accepts changed unsigned order/plan/payment fields')
                print('PASS: unmodified baseline HTTP 500 at missing SDK log path; state unchanged, not a successful payment baseline')
            else:
                routes=['2co_ipn.php','2co_start.php','index.php','success.php','include/common/provisionUser.php','include/common/retired.php']
                def retired(path,method='POST',data=fields,content_type='application/x-www-form-urlencoded'):
                    status,body,headers=request(base,root+path,method,data,content_type)
                    assert status==410 and (body==MESSAGE or method=='HEAD' and body==b'')
                    assert headers.get('cache-control')=='no-store' and headers.get('x-content-type-options')=='nosniff'
                    assert headers.get('content-type')=='text/plain; charset=UTF-8'
                    assert not any(k.lower() in ['set-cookie','location'] for k in headers)
                    assert state()==initial
                for route in routes:
                    for method in ['GET','POST','HEAD','PUT']:retired(route,method)
                    for data,ct in [(b'', 'application/x-www-form-urlencoded'),
                                    ({'key[]':['bad'],'custom[]':['bad'],'total[]':['bad']},'application/x-www-form-urlencoded'),
                                    (dict(fields,key='invalid'),'application/x-www-form-urlencoded'),
                                    (dict(fields,custom="quote'-%-é-<script>"),'application/x-www-form-urlencoded'),
                                    (b'x'*70000,'application/x-www-form-urlencoded'),
                                    (b'{"untrusted":"'+sentinel.encode()+b'"}','application/json')]:
                        retired(route,data=data,content_type=ct)
                with concurrent.futures.ThreadPoolExecutor(4) as pool:
                    list(pool.map(lambda _:retired('2co_ipn.php'),range(8)))
                print('PASS: all entry points HTTP 410; signed/invalid/malformed/Unicode/oversized/concurrent inputs leave all tables unchanged')
                # Configuration must be unreachable, including the historical receipt after R26.
                (family/'library/config_read.php').write_text("<?php throw new RuntimeException('Fixture configuration must not be loaded');")
                for route in routes:retired(route)
                # Prove retirement still works without DB, provider, SDK or configuration.
                run('docker','stop',DB)
                for rel in ['library','include/merchant']:shutil.rmtree(family/rel)
                for route in routes:
                    status,body,headers=request(base,root+route,data=fields)
                    assert status==410 and body==MESSAGE
                print('PASS: SDK validator and provider throw before use; no configuration/SDK/DB required by retired routes')
                p=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True);logs=p.stdout+p.stderr
                assert not any(x in logs.lower() for x in ['warning:','fatal error:','sqlstate','pdoexception'])
                assert not any(v in logs for v in [word,fields['key'],sentinel])
                assert not any(p.exists() for p in [fixture/'legacy-payload.log',family/'2co.ipn_results.log'])
                print('PASS: combined PHP stdout/stderr clean; no request/signature/secret logging or reflected input')
        finally:
            os.environ.pop('FIXTURE_2CO_WORD',None)
            for name in [WEB,DB]:subprocess.run(['docker','rm','-f',name],capture_output=True)
            subprocess.run(['docker','network','rm',NET],capture_output=True)
            for name in [WEB,DB]:assert subprocess.run(['docker','inspect',name],capture_output=True).returncode!=0
            assert subprocess.run(['docker','network','inspect',NET],capture_output=True).returncode!=0
            subprocess.run(['docker','run','--rm','-v',f'{fixture}:/fixture','--entrypoint','sh','lirantal/daloradius',
                            '-c',f'chown -R {os.getuid()}:{os.getgid()} /fixture'],capture_output=True,check=True)
    assert not fixture.exists();print('PASS: isolated container/network/environment/fixture cleanup; no external provider call or payment')


if __name__=='__main__':main()
