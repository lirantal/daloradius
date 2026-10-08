#!/usr/bin/env python3
"""UNIT-040 real IPN HTTP/PHP/MariaDB + local HTTPS verifier integration.

No real PayPal call/payment: Docker network is internal and four official DNS
names resolve to an ephemeral TLS emulator. Candidate does NOT stub validation.
"""
import concurrent.futures
import hashlib
import json
import os
from pathlib import Path
import secrets
import shutil
import subprocess
import tempfile
import urllib.error
import urllib.parse
import urllib.request

from operator_login_http import run, wait_for, quote

ROOT=Path(__file__).resolve().parents[1]
BASE='58b410ab47f4b598dedd2564248d9c781ebd8f03'
BASELINE=os.environ.get('CHILLI_PAYPAL_BASELINE')=='1'
TAG='u40-'+secrets.token_hex(6)
DB,WEB,TLS,NET=(TAG+'-'+s for s in ('db','web','tls','net'))
MIGRATION='2026-09-30-chilli-paypal-events.sql'
TABLES=['billing_paypal','billing_merchant','chilli_paypal_events','radcheck','radusergroup','usergroup',
        'userbillinfo','billing_history']


def php(value):
    if isinstance(value,bool):return 'true' if value else 'false'
    return "'"+str(value).replace('\\','\\\\').replace("'","\\'")+"'"


def sql(query):
    p=subprocess.run(['docker','exec','-i',DB,'mariadb','-uroot','-N','-B','radius'],input=query,
                     capture_output=True,text=True,timeout=30)
    if p.returncode:raise RuntimeError('Fixture SQL failed (driver details omitted)')
    return p.stdout.strip()


def state():
    # Only synthetic profile/payment fields, never password/credential columns.
    return [sql('SELECT * FROM '+t+' ORDER BY '+('event_key' if t=='chilli_paypal_events' else
            ('username,groupname,priority' if t=='usergroup' else 'id'))) for t in TABLES]


def post(base,n,data):
    raw=urllib.parse.urlencode(data).encode() if isinstance(data,dict) else data
    request=urllib.request.Request(base+f'portal{n}/signup-paypal/paypal-ipn.php',data=raw,
                                  headers={'Content-Type':'application/x-www-form-urlencoded'})
    try:r=urllib.request.urlopen(request,timeout=50)
    except urllib.error.HTTPError as e:r=e
    return r.status,r.read().strip()


EMULATOR='''import hashlib,http.server,json,ssl,urllib.parse
from pathlib import Path
class Handler(http.server.BaseHTTPRequestHandler):
    def log_message(self,*args):pass
    def do_POST(self):
        body=self.rfile.read(int(self.headers.get('Content-Length','0')))
        fields=urllib.parse.parse_qs(body.decode('ascii'))
        flag=fields.get('verify_sign',[''])[0]
        reply={'invalid':'INVALID','near':'NOTVERIFIED','redirect':'VERIFIED','http-error':'VERIFIED'}.get(flag,'VERIFIED')
        code=503 if flag=='http-error' else 302 if flag=='redirect' else 200
        Path('/fixtures/tls/metrics.json').write_text(json.dumps({'last_hash':hashlib.sha256(body).hexdigest()}))
        self.send_response(code)
        if flag=='redirect':self.send_header('Location','https://untrusted.invalid/')
        self.send_header('Content-Length',str(len(reply)));self.send_header('Connection','close');self.end_headers()
        self.wfile.write(reply.encode())
server=http.server.ThreadingHTTPServer(('0.0.0.0',443),Handler)
context=ssl.SSLContext(ssl.PROTOCOL_TLS_SERVER);context.load_cert_chain('/fixtures/tls/cert.pem','/fixtures/tls/key.pem')
server.socket=context.wrap_socket(server.socket,server_side=True);server.serve_forever()
'''


def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='chilli-paypal-',dir=scratch) as directory:
        fixture=Path(directory)
        shutil.copytree(ROOT/'contrib/chilli',fixture/'contrib/chilli')
        common=fixture/'app/common/includes';common.mkdir(parents=True)
        shutil.copy2(ROOT/'app/common/includes/pdo_connection.php',common/'pdo_connection.php')
        if BASELINE:
            files=[f'contrib/chilli/portal{n}/signup-paypal/paypal-ipn.php' for n in (1,2,3)]
            files+=['contrib/chilli/portal2/signup-paypal/include/common/provisionUser.php']
            for f in files:(fixture/f).write_text(run('git','show',BASE+':'+f)+'\n')
        tls=fixture/'tls';tls.mkdir();(tls/'server.py').write_text(EMULATOR)
        run('openssl','req','-x509','-newkey','rsa:2048','-nodes','-days','1',
            '-keyout',str(tls/'key.pem'),'-out',str(tls/'cert.pem'),'-subj','/CN=ipnpb.paypal.com',
            '-addext','subjectAltName=DNS:ipnpb.paypal.com,DNS:ipnpb.sandbox.paypal.com,DNS:www.paypal.com,DNS:www.sandbox.paypal.com')
        (tls/'key.pem').chmod(0o600)
        settings={'CONFIG_DB_ENGINE':'mysqli','CONFIG_DB_HOST':DB,'CONFIG_DB_PORT':'3306','CONFIG_DB_NAME':'radius',
                  'CONFIG_DB_USER':'root','CONFIG_DB_PASS':'','CONFIG_PAYPAL_CA_FILE':'/fixtures/tls/cert.pem',
                  'CONFIG_LOG_PAYPAL_IPN_FILENAME':'/fixtures/tls/baseline-ipn.log',
                  'CONFIG_LOG_MERCHANT_IPN_FILENAME':'/fixtures/tls/baseline-merchant-ipn.log',
                  'CONFIG_PAYPAL_RECEIVER_EMAIL':'merchant@example.invalid','CONFIG_MERCHANT_BUSINESS_ID':'merchant@example.invalid',
                  'CONFIG_DB_TBL_RADCHECK':'radcheck','CONFIG_DB_TBL_RADGROUPCHECK':'radgroupcheck',
                  'CONFIG_DB_TBL_RADGROUPREPLY':'radgroupreply','CONFIG_DB_TBL_DALOBILLINGPAYPAL':'billing_paypal',
                  'CONFIG_DB_TBL_DALOBILLINGMERCHANT':'billing_merchant','CONFIG_DB_TBL_DALOBILLINGPLANS':'billing_plans',
                  'CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES':'billing_plans_profiles','CONFIG_DB_TBL_DALOUSERBILLINFO':'userbillinfo',
                  'CONFIG_DB_TBL_DALOBILLINGHISTORY':'billing_history'}
        def configure(n,updates=None):
            cfg=dict(settings);cfg['CONFIG_DB_TBL_RADUSERGROUP']='radusergroup' if n==2 else 'usergroup';cfg.update(updates or {})
            path=fixture/f'contrib/chilli/portal{n}/signup-paypal/library/daloradius.conf.php'
            path.write_text('<?php\n'+''.join('$configValues[%s]=%s;\n'%(php(k),php(v)) for k,v in cfg.items()));path.chmod(0o600)
        counter=0
        def order(n,group=None,recurring=False,time_type='Accumulative',period='Never',special=False,pin=True):
            nonlocal counter
            counter+=1
            user=('identity-\'-%-é-' if special else 'identity-')+str(counter)
            correlation=('order-\'-%-é-' if special else 'order-')+str(counter)
            name=('plan-\'-%-é-' if special else 'plan-')+str(counter)
            planid='legacy-'+str(counter)
            sql("INSERT INTO billing_plans(planId,planName,planCost,planTax,planCurrency,planTimeType,planTimeBank,planRecurring,planRecurringPeriod,planGroup) VALUES (%s,%s,'10.00','0','USD',%s,'600',%s,%s,%s)"%
                (quote(planid),quote(name),quote(time_type),quote('Yes' if recurring else 'No'),quote(period),quote(group or '')))
            numeric=sql('SELECT id FROM billing_plans WHERE planName='+quote(name));target=numeric if n==2 else planid
            if n==2:
                sql("INSERT INTO billing_merchant(username,txnId,planId,txn_type,vendor_type) VALUES (%s,%s,%s,'','PayPal')"%(quote(user),quote(correlation),quote(target)))
                sql('INSERT INTO userbillinfo(username,planname,lastbill,nextbill) VALUES (%s,%s,\'0000-00-00\',\'0000-00-00\')'%(quote(user),quote(name)))
                if group:sql('INSERT INTO billing_plans_profiles(plan_name,profile_name) VALUES (%s,%s)'%(quote(name),quote(group)))
            else:
                sql('INSERT INTO billing_paypal(username,pin,txnId,planId,planName) VALUES (%s,%s,%s,%s,%s)'%
                    (quote(user),quote(user) if pin else 'NULL',quote(correlation),quote(target),quote(name)))
            data={'option_selection1':correlation,'option_selection2':user,'item_number':target,'item_name':name,
                  'txn_type':'web_accept','txn_id':'PAYMENT-'+str(counter),'payment_status':'Completed',
                  'receiver_email':'merchant@example.invalid','business':'merchant@example.invalid','mc_gross':'10.00',
                  'mc_fee':'0.50','tax':'0','mc_currency':'USD','quantity':'1','payment_date':'12:00:00 Sep 30, 2026 PDT',
                  'first_name':'Fixture','last_name':'Person','payer_email':'payer@example.invalid','payer_status':'verified',
                  'address_name':'Fixture Person','address_street':'Fixture Street','address_country':'Fixture Country',
                  'address_country_code':'US','address_city':'Fixture City','address_state':'Fixture State','address_zip':'00000',
                  'payment_address_status':'confirmed','test_ipn':'0' if n==1 else '1'}
            return user,data
        try:
            run('docker','network','create','--internal',NET)
            run('docker','run','-d','--name',DB,'--network',NET,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):sql((ROOT/'contrib/db'/name).read_text())
            fresh_journal=sql('SHOW CREATE TABLE chilli_paypal_events')
            assert sql('SELECT COUNT(*) FROM chilli_paypal_events')=='0'
            # This table belongs to this empty, tmpfs-only fixture database.
            sql('DROP TABLE chilli_paypal_events')
            sql((ROOT/'contrib/db/migrations'/MIGRATION).read_text())
            assert sql('SHOW CREATE TABLE chilli_paypal_events')==fresh_journal
            print('PASS: fresh and additive-upgrade journal definitions match')
            sql('CREATE TABLE usergroup(username VARCHAR(64),groupname VARCHAR(64),priority INT) ENGINE=InnoDB')
            for group in ['fixture-group',"group-'-%-é",'second-profile']:
                sql('INSERT INTO radgroupcheck(groupname,attribute,op,value) VALUES (%s,\'Filter-Id\',\'=\',\'fixture\')'%quote(group))
            for n in (1,2,3):configure(n)
            run('docker','run','-d','--name',TLS,'--network',NET,
                '--network-alias','ipnpb.paypal.com','--network-alias','ipnpb.sandbox.paypal.com',
                '--network-alias','www.paypal.com','--network-alias','www.sandbox.paypal.com',
                '-v',f'{fixture}:/fixtures','--entrypoint','python','python:3.13-alpine','/fixtures/tls/server.py')
            run('docker','run','-d','--name',WEB,'--network',NET,'-v',f'{fixture}:/fixtures',
                '-e','PHP_CLI_SERVER_WORKERS=4','--entrypoint','php','lirantal/daloradius',
                '-d','display_errors=0','-d','opcache.enable=0','-d','opcache.enable_cli=0',
                '-d','openssl.cafile=/fixtures/tls/cert.pem','-S','0.0.0.0:8080','-t','/fixtures/contrib/chilli')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/'
            (fixture/'contrib/chilli/health.php').write_text('<?php echo "ready";')
            wait_for(lambda:urllib.request.urlopen(base+'health.php',timeout=5).status==200,'IPN PHP HTTP')
            # Ensure TLS server is accepting before any business-state assertion.
            wait_for(lambda:run('docker','exec',WEB,'php','-d','openssl.cafile=/fixtures/tls/cert.pem','-r',"$s=fsockopen('ssl://ipnpb.paypal.com',443);if(!$s)exit(1);fclose($s);"),'TLS verifier')
            def accepted(n,fields):
                status,body=post(base,n,fields)
                if status!=200:
                    import re
                    process=subprocess.run(['docker','logs',WEB],capture_output=True,text=True)
                    logs=process.stdout+process.stderr
                    kinds=re.findall(r'Call to (?:undefined function|a member function) [A-Za-z_][A-Za-z_0-9]*\([^)]*\)(?: on [A-Za-z_]+)?|Uncaught [A-Za-z_]+|[A-Za-z_][A-Za-z_0-9]*\(\): Argument #[0-9]+',logs)
                    places=re.findall(r'/fixtures/[A-Za-z0-9_/.-]+(?:[: ](?:on line )?[0-9]+)',logs)
                    if BASELINE and n==2 and status==500:
                        assert 'Uncaught ValueError' in kinds and 'mysqli_query(): Argument #2' in kinds
                        assert any('/include/common/provisionUser.php on line 167' in p for p in places)
                        return # Unmodified baseline fails after committed activation/payment rows.
                    print('SANITIZED_DIAGNOSTIC',kinds,places[-8:])
                assert status==200 and (BASELINE or body==b'processed'),('notification failed',n,status,body[:100])
            def rejected(n,fields,status=400):
                before=state();actual,body=post(base,n,fields)
                assert actual==status and state()==before,('rejection/state mismatch',n,status,actual,body[:100])
                assert body in (b'Notification rejected',b'Notification processing unavailable')
            for n in (1,2,3):
                user,fields=order(n,'fixture-group');accepted(n,fields)
                mapping='radusergroup' if n==2 else 'usergroup'
                assert sql('SELECT attribute,op,value FROM radcheck WHERE username='+quote(user)+" AND attribute='Auth-Type'")=='Auth-Type\t:=\tAccept'
                expected_group='planGroup' if BASELINE and n==1 else 'fixture-group'
                assert sql('SELECT groupname,priority FROM '+mapping+' WHERE username='+quote(user))==expected_group+'\t0'
                table='billing_merchant' if n==2 else 'billing_paypal';cost='payment_cost' if n==2 else 'mc_gross'
                assert sql('SELECT '+cost+',payment_status,first_name,last_name FROM '+table+' WHERE username='+quote(user))=='10.00\tCompleted\tFixture\tPerson'
                if n==2:
                    if BASELINE:assert sql('SELECT COUNT(*) FROM billing_history WHERE username='+quote(user))=='0'
                    else:assert sql('SELECT billAmount,billReason FROM billing_history WHERE username='+quote(user))=='10.00\tweb_accept'
                    if not BASELINE:assert sql('SELECT lastbill,nextbill FROM userbillinfo WHERE username='+quote(user))=='2026-09-30\t0000-00-00'
                print('PASS:',n,'payment/profile projection checked; known baseline defects classified separately')
                before=state()
                if BASELINE:
                    accepted(n,fields)
                    assert state()!=before,'Expected baseline duplicate-side-effect defect'
                    if n==2:assert sql('SELECT lastbill,nextbill FROM userbillinfo WHERE username='+quote(user))=='0000-00-00\t0000-00-00'
                    print('PASS: baseline repeated side effects'+('; billing ValueError/HTTP 500 with committed earlier writes' if n==2 else ''))
                    continue
                accepted_status,body=post(base,n,fields)
                assert accepted_status==200 and body==b'duplicate' and state()==before
                # Actual raw-body retransmission including noncanonical percent/space encoding.
                user,fields=order(n,'fixture-group');fields['first_name']="Fixture %'é"
                raw=urllib.parse.urlencode(fields).encode().replace(b'Fixture+%25',b'Fixture%20%25')
                accepted(n,raw)
                metrics=json.loads((tls/'metrics.json').read_text())
                assert metrics['last_hash']==hashlib.sha256(b'cmd=_notify-validate&'+raw).hexdigest()
                for flag,code in [('invalid',400),('near',503),('http-error',503),('redirect',503)]:
                    _,fresh=order(n,'fixture-group');fresh['verify_sign']=flag;rejected(n,fresh,code)
                for change in [{'receiver_email':'other@example.invalid'},{'mc_gross':'9.99'},{'mc_currency':'EUR'},
                               {'item_number':'wrong'},{'option_selection2':'foreign-user'},{'quantity':'2'},
                               {'txn_id':''},{'payment_date':'not a date'},{'first_name':'x'*201},
                               {'option_selection1':'absent-order'},{'test_ipn':'1' if n==1 else '0'}]:
                    _,fresh=order(n,'fixture-group');fresh.update(change);rejected(n,fresh)
                _,fresh=order(n,'fixture-group');encoded=urllib.parse.urlencode(fresh).encode()
                for raw in [encoded+b'&txn_id=extra',encoded.replace(b'quantity=1',b'quantity%5B%5D=1'),
                            encoded+b'&cmd=other',encoded+b'&malformed=%GG',b'x'*65537]:rejected(n,raw)
                user,fresh=order(n,'fixture-group');fresh['payment_status']='Pending';accepted(n,fresh)
                assert sql('SELECT COUNT(*) FROM radcheck WHERE username='+quote(user))=='0'
                assert sql('SELECT COUNT(*) FROM billing_history WHERE username='+quote(user))=='0'
                fresh['payment_status']='Completed';accepted(n,fresh)
                before=state();fresh['payment_status']='Pending';status,body=post(base,n,fresh)
                assert status==200 and body==b'duplicate' and state()==before
                # A new, late Pending notification cannot overwrite a completed projection.
                late=dict(fresh,txn_id=fresh['txn_id']+'-late');accepted(n,late)
                assert sql('SELECT payment_status FROM '+table+' WHERE username='+quote(user))=='Completed'
                for engine in (['radcheck',mapping,'billing_merchant','userbillinfo','billing_history','billing_plans_profiles',
                                'radgroupcheck','radgroupreply','billing_plans','chilli_paypal_events'] if n==2 else
                               ['radcheck',mapping,'billing_paypal','billing_plans','radgroupcheck','radgroupreply','chilli_paypal_events']):
                    sql('ALTER TABLE '+engine+' ENGINE=MyISAM')
                    try:_,fresh=order(n,'fixture-group');rejected(n,fresh,503)
                    finally:sql('ALTER TABLE '+engine+' ENGINE=InnoDB')
                for updates in [{'CONFIG_DB_TBL_RADCHECK':'radcheck;bad'},{'CONFIG_DB_NAME':'missing_database'},
                                {'CONFIG_PAYPAL_RECEIVER_EMAIL':'','CONFIG_MERCHANT_BUSINESS_ID':''},
                                {'CONFIG_PAYPAL_CA_FILE':'/etc/ssl/certs/ca-certificates.crt'},
                                {'CONFIG_PAYPAL_CA_FILE':'/fixtures/tls/absent.pem'}]:
                    configure(n,updates)
                    try:_,fresh=order(n,'fixture-group');rejected(n,fresh,400 if 'CONFIG_DB_TBL_RADCHECK' in updates else 503)
                    finally:configure(n)
                # Late errors after the event/order write and authorization insertion.
                for failure in ([mapping,'billing_history','userbillinfo'] if n==2 else [mapping]):
                    verb='UPDATE' if failure=='userbillinfo' else 'INSERT'
                    sql('CREATE TRIGGER u40_late BEFORE '+verb+' ON '+failure+" FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture late failure'")
                    try:_,fresh=order(n,'fixture-group');rejected(n,fresh,503)
                    finally:sql('DROP TRIGGER u40_late')
                    accepted(n,fresh) # Retriable: failed transaction did not consume ledger key.
                user,fresh=order(n,"group-'-%-é",special=True);accepted(n,fresh)
                assert sql('SELECT groupname FROM '+mapping+' WHERE username='+quote(user))=="group-'-%-é"
                _,fresh=order(n,'not-defined');rejected(n,fresh)
                user,fresh=order(n,None,time_type='Time-To-Finish');accepted(n,fresh)
                assert sql('SELECT value FROM radcheck WHERE username='+quote(user)+" AND attribute='Access-Period'")=='600'
                # Actual parallel PHP workers, same verified event twice.
                user,fresh=order(n,'fixture-group')
                with concurrent.futures.ThreadPoolExecutor(2) as pool:results=list(pool.map(lambda _:post(base,n,fresh),range(2)))
                assert sorted(results)==[(200,b'duplicate'),(200,b'processed')],results
                assert sql('SELECT COUNT(*) FROM '+mapping+' WHERE username='+quote(user))=='1'
                # Reuse of the same PayPal transaction for another legitimate order fails closed.
                _,foreign=order(n,'fixture-group');foreign['txn_id']=fresh['txn_id'];rejected(n,foreign)
                # An existing pre-journal callback record cannot safely be replayed.
                user,legacy=order(n,'fixture-group')
                sql('UPDATE '+table+" SET payment_status='Completed' WHERE username="+quote(user))
                rejected(n,legacy,503)
                user,taxed=order(n,'fixture-group')
                sql("UPDATE billing_plans SET planCost='12.34',planTax="+quote('5' if n==2 else '0.62')+' WHERE planName='+quote(taxed['item_name']))
                taxed.update(mc_gross='12.96',tax='0.62');accepted(n,taxed)
                print('PASS:',n,'legacy journal-gap rejection and exact nonzero-tax amount')
                print('PASS:',n,'exact TLS/body verification, replay/concurrency, gates, engines, full rollback and retry')
            if not BASELINE:
                # Portal1's actual checkout stores username, not the obsolete pin column.
                user,fresh=order(1,'fixture-group',pin=False);accepted(1,fresh)
                assert sql('SELECT COUNT(*) FROM radcheck WHERE username='+quote(user)+" AND value='Accept'")=='1'
                # Profile fetch must insert ALL profile names using explicit mapping columns.
                user,fresh=order(2,'fixture-group');planname=fresh['item_name']
                sql('INSERT INTO billing_plans_profiles(plan_name,profile_name) VALUES (%s,\'second-profile\')'%quote(planname));accepted(2,fresh)
                assert sql('SELECT COUNT(*) FROM radusergroup WHERE username='+quote(user))=='2'
                user,multi=order(2,'fixture-group')
                sql('INSERT INTO billing_plans_profiles(plan_name,profile_name) VALUES (%s,\'second-profile\')'%quote(multi['item_name']))
                sql("DELIMITER //\nCREATE TRIGGER u40_second BEFORE INSERT ON radusergroup FOR EACH ROW BEGIN IF NEW.groupname='second-profile' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture second failure'; END IF; END//\nDELIMITER ;")
                try:rejected(2,multi,503)
                finally:sql('DROP TRIGGER u40_second')
                accepted(2,multi)
                user,fresh=order(2,None,recurring=True,period='Monthly')
                fresh.update(txn_type='subscr_signup',subscr_id='SUBSCRIPTION-ONE',subscr_date='12:00:00 Sep 30, 2026 PDT')
                for key in ['txn_id','payment_status','payment_date','mc_gross','mc_currency']:fresh.pop(key,None)
                accepted(2,fresh);assert sql('SELECT COUNT(*) FROM radcheck WHERE username='+quote(user))=='0'
                signup=dict(fresh)
                payment=dict(fresh,txn_type='subscr_payment',txn_id='SUB-PAYMENT-ONE',payment_status='Completed',
                             payment_date='12:00:00 Sep 30, 2026 PDT',mc_gross='10.00',mc_currency='USD')
                accepted(2,payment)
                assert sql('SELECT lastbill,nextbill FROM userbillinfo WHERE username='+quote(user))=='2026-09-30\t2026-10-30'
                assert sql('SELECT COUNT(*) FROM billing_merchant WHERE username='+quote(user))=='2'
                cancel=dict(signup,txn_type='subscr_cancel',subscr_date='11:00:00 Sep 30, 2026 PDT')
                sql("CREATE TRIGGER u40_cancel BEFORE UPDATE ON radcheck FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture cancel failure'")
                try:rejected(2,cancel,503)
                finally:sql('DROP TRIGGER u40_cancel')
                accepted(2,cancel)
                assert sql('SELECT value FROM radcheck WHERE username='+quote(user)+" AND attribute='Auth-Type'")=='Reject'
                before=state();assert post(base,2,payment)==(200,b'duplicate') and state()==before
                # A distinct older payment is booked once, but cannot reactivate or move bill dates backward.
                older=dict(payment,txn_id='SUB-PAYMENT-OLDER',payment_date='12:00:00 Sep 29, 2026 PDT');accepted(2,older)
                assert sql('SELECT value FROM radcheck WHERE username='+quote(user)+" AND attribute='Auth-Type'")=='Reject'
                assert sql('SELECT lastbill,nextbill FROM userbillinfo WHERE username='+quote(user))=='2026-09-30\t2026-10-30'
                failed=dict(signup,txn_type='subscr_failed');accepted(2,failed)
                later=dict(payment,txn_id='SUB-PAYMENT-AFTER-CANCEL',payment_date='12:00:00 Oct 1, 2026 PDT');accepted(2,later)
                assert sql('SELECT value FROM radcheck WHERE username='+quote(user)+" AND attribute='Auth-Type'")=='Reject'
                assert sql('SELECT lastbill,nextbill FROM userbillinfo WHERE username='+quote(user))=='2026-10-01\t2026-11-01'
                rejected(2,dict(signup,subscr_id='OTHER-SUBSCRIPTION'))
                missing=dict(payment,txn_id='SUB-MISSING-IDENTITY');missing.pop('subscr_id');rejected(2,missing)
                before=state();assert post(base,2,cancel)==(200,b'duplicate') and state()==before
                eot=dict(signup,txn_type='subscr_eot');accepted(2,eot)
                unknown=dict(signup,txn_type='unknown_event');before=state();assert post(base,2,unknown)==(200,b'ignored') and state()==before
                _,different=order(2,None,recurring=True,period='Monthly')
                different.update(txn_type='subscr_cancel',subscr_id='SUBSCRIPTION-ONE',subscr_date='13:00:00 Sep 30, 2026 PDT')
                for key in ['txn_id','payment_status','payment_date','mc_gross','mc_currency']:different.pop(key,None)
                rejected(2,different)
                # Replay migration preserves a nonempty ledger and its unique-key contract.
                before=state();sql((ROOT/'contrib/db/migrations'/MIGRATION).read_text());assert state()==before
                print('PASS: recurring signup/payment/cancel, stale payment, enrollment binding, billing dates and migration replay')
                # Explicit configured receiver ID and environment routing are never POST-selected.
                configure(1,{'CONFIG_PAYPAL_SANDBOX':True,'CONFIG_PAYPAL_RECEIVER_ID':'FIXTURE-RECEIVER'})
                try:
                    _,bound=order(1,'fixture-group');bound.update(test_ipn='1',receiver_id='FIXTURE-RECEIVER');accepted(1,bound)
                    _,wrong=order(1,'fixture-group');wrong.update(test_ipn='1',receiver_id='WRONG-RECEIVER');rejected(1,wrong)
                finally:configure(1)
                try:r=urllib.request.urlopen(base+'portal1/signup-paypal/paypal-ipn.php')
                except urllib.error.HTTPError as e:r=e
                assert r.status==405
                try:r=urllib.request.urlopen(base+'common/paypalPdo.php')
                except urllib.error.HTTPError as e:r=e
                assert r.status==404
                run('docker','exec',WEB,'php','-r',"if(!is_file('/etc/ssl/certs/ca-certificates.crt'))exit(1);")
                run('docker','stop',TLS)
                _,unavailable=order(1,'fixture-group');rejected(1,unavailable,503)
                assert not (tls/'baseline-ipn.log').exists() and not (tls/'baseline-merchant-ipn.log').exists()
                print('PASS: untrusted CA/unavailable verifier, receiver-ID pinning, explicit sandbox, endpoint gates and no raw IPN logs')
            logprocess=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            logs=logprocess.stdout+logprocess.stderr
            if not BASELINE:assert not any(x in logs.lower() for x in ['warning:','fatal error:','pdoexception','sqlstate','merchant@example.invalid'])
            # Old callbacks write raw IPNs to cwd. Such fixture files are ephemeral and never compared or printed.
            print('PASS:',('pinned PEAR baseline' if BASELINE else 'PDO candidate'),'real HTTP/PHP/MariaDB; verifier is local HTTPS emulator, not PayPal')
        finally:
            for name in (WEB,TLS,DB):subprocess.run(['docker','rm','-f',name],capture_output=True)
            subprocess.run(['docker','network','rm',NET],capture_output=True)
            for name in (WEB,TLS,DB):assert subprocess.run(['docker','inspect',name],capture_output=True).returncode!=0
            assert subprocess.run(['docker','network','inspect',NET],capture_output=True).returncode!=0
            # Temporary bind tree belongs to this test only; restore ownership of root-created emulator/log files.
            subprocess.run(['docker','run','--rm','-v',f'{fixture}:/fixture','--entrypoint','sh','lirantal/daloradius',
                            '-c',f'chown -R {os.getuid()}:{os.getgid()} /fixture'],capture_output=True,check=True)
    assert not fixture.exists();print('PASS: all disposable resources and fixture files removed')


if __name__=='__main__':main()
