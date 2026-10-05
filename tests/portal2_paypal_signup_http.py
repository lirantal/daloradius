#!/usr/bin/env python3
"""R24: native paired Portal2 pending signup/resume/receipt and unchanged IPN via local TLS.
No real provider, payment, RADIUS, live configuration or persistent secrets used.
"""
import concurrent.futures, hashlib, html, http.cookiejar, json, re, secrets, shutil, subprocess, tempfile, time
import urllib.error, urllib.parse, urllib.request
from pathlib import Path
from html.parser import HTMLParser
import chilli_paypal_http as ipn
ROOT=Path(__file__).resolve().parents[1]
BASE='9c2925b88f5cfe3678dac62a3656f58acaab8612'
PREFIX='pdo-r24-'+secrets.token_hex(5)
DB,WEB,TLS,NET=[PREFIX+'-'+k for k in ('db','web','tls','net')]
WORKERS=[PREFIX+'-worker-'+str(i) for i in range(2)]
IMAGE='lirantal/daloradius'

def run(*args,input=None,check=True):
    p=subprocess.run(args,input=input,text=True,capture_output=True,timeout=180)
    if check and p.returncode:raise RuntimeError('Disposable fixture operation failed; details suppressed')
    return p.stdout.strip()
def db(query,v='candidate'):
    p=subprocess.run(['docker','exec','-i',DB,'mariadb','-uroot','-N','-B',v],
        input="SET SESSION sql_mode='';\n"+query,capture_output=True,text=True,timeout=40)
    if p.returncode:raise RuntimeError('Disposable SQL failed; codes '+repr(re.findall(r'ERROR (\d+)',p.stderr)))
    return p.stdout.rstrip('\n')
def q(value):return "'"+value.replace('\\','\\\\').replace("'","''")+"'"
def wait(fn):
    end=time.monotonic()+60
    while time.monotonic()<end:
        try:
            result=fn()
            if result:return result
        except (OSError,RuntimeError,urllib.error.URLError):pass
        time.sleep(.2)
    raise RuntimeError('Disposable fixture readiness timed out')
class Form(HTMLParser):
    def __init__(self,body):
        super().__init__();self.fields={};self.options=[];self.forms=[];self.feed(body.decode())
    def handle_starttag(self,tag,attrs):
        a=dict(attrs)
        if tag=='input' and a.get('name'):self.fields[a['name']]=a.get('value','')
        if tag=='option':self.options.append(a.get('value',''))
        if tag=='form':self.forms.append(a)
class Client:
    def __init__(self,base):
        self.base=base;self.jar=http.cookiejar.CookieJar()
        self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.jar))
    def req(self,page='index.php',data=None,method=None):
        raw=urllib.parse.urlencode(data,doseq=True).encode() if isinstance(data,dict) else data
        request=urllib.request.Request(self.base+page,data=raw,method=method)
        try:r=self.opener.open(request,timeout=50)
        except urllib.error.HTTPError as e:r=e
        return r.status,r.read(),r.headers
    def submit(self,updates=None):
        status,body,_=self.req();assert status==200,'signup GET'
        fields={'submit':'submit','firstName':'Fixture','lastName':'Person','address':'Street','city':'Town','state':'State','planId':'1'}
        token=Form(body).fields.get('csrf_token')
        if token:fields['csrf_token']=token
        fields.update(updates or {})
        return self.req(data=fields),fields

def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    comparisons={};checks=[]
    with tempfile.TemporaryDirectory(prefix=PREFIX+'-',dir=scratch) as directory:
        f=Path(directory);blocker=None
        try:
            run('docker','network','create','--internal',NET)
            run('docker','run','-d','--name',DB,'--network',NET,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','mariadb:11.8')
            wait(lambda:run('docker','exec',DB,'mariadb','--protocol=tcp','-h127.0.0.1','-uroot','-N','-B','-e','SELECT 1'))
            sample=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text()
            defaults=dict(re.findall(r"\$configValues\['(CONFIG_DB_TBL_[^']+)'\]\s*=\s*'([^']+)'",sample))
            keys=['DALOBILLINGPLANS','DALOUSERINFO','DALOBILLINGMERCHANT','DALOUSERBILLINFO','DALOBILLINGPLANSPROFILES','DALOBILLINGHISTORY','RADCHECK','RADREPLY','RADGROUPCHECK','RADGROUPREPLY','RADUSERGROUP']
            tables={'CONFIG_DB_TBL_'+k:'custom_'+defaults['CONFIG_DB_TBL_'+k] for k in keys}
            settings=dict(tables,CONFIG_DB_ENGINE='mysqli',CONFIG_DB_HOST=DB,CONFIG_DB_PORT='3306',
                CONFIG_DB_USER='root',CONFIG_DB_PASS='',CONFIG_USER_ALLOWEDRANDOMCHARS='abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
                CONFIG_PAYPAL_RECEIVER_EMAIL='merchant@example.invalid',CONFIG_MERCHANT_BUSINESS_ID='merchant@example.invalid',CONFIG_PAYPAL_SANDBOX=False,
                CONFIG_USERNAME_LENGTH='8',CONFIG_MERCHANT_IPN_URL_ROOT='https://portal.example.invalid/paypal',
                CONFIG_MERCHANT_WEB_PAYMENT='https://www.paypal.com/cgi-bin/webscr',
                CONFIG_MERCHANT_IPN_URL_RELATIVE_SUCCESS='success.php',CONFIG_MERCHANT_IPN_URL_RELATIVE_FAILURE='index.php',CONFIG_MERCHANT_IPN_URL_RELATIVE_DIR='paypal-ipn.php',
                CONFIG_PAYPAL_CA_FILE='/fixtures/tls/cert.pem',CONFIG_MERCHANT_SUCCESS_MSG_HEADER='<h1>Fixture receipt</h1>',
                CONFIG_MERCHANT_SUCCESS_MSG_PRE='<p>Waiting for payment</p>',CONFIG_MERCHANT_SUCCESS_MSG_POST='<p>Confirmed</p>')
            configs={}
            def configure(v='candidate',updates=None):
                cfg=dict(settings,CONFIG_DB_NAME=v);cfg.update(updates or {})
                path=f/v/'contrib/chilli/portal2/signup-paypal/library/daloradius.conf.php'
                text='<?php\n'+''.join('$configValues['+ipn.php(k)+']='+ipn.php(value)+';\n' for k,value in cfg.items())
                path.write_text(text);path.chmod(0o600);configs[v]=text
            for v in ('base','candidate','other'):
                run('docker','exec',DB,'mariadb','-uroot','-e','CREATE DATABASE '+v)
                for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):db((ROOT/'contrib/db'/name).read_text(),v)
                db('RENAME TABLE '+','.join(defaults[k]+' TO '+t for k,t in tables.items()),v)
                db("INSERT INTO custom_billing_plans(id,planId,planName,planType,planCost,planTax,planCurrency,planRecurring,planRecurringPeriod,planTimeType,planTimeBank) VALUES"
                    "(1,'logical-id','Plan','PayPal','12.34','5','USD','No','Never','Accumulative','600'),"
                    "(2,'not-paypal','Other','Other','1','0','USD','No','Never','','');"
                    "SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO';INSERT INTO custom_billing_plans(id,planId,planName,planType,planCost,planTax,planCurrency,planRecurring,planRecurringPeriod) VALUES"
                    "(0,'zero','Zero plan','PayPal','0','0','EUR','No','Never');SET SESSION sql_mode='';"
                    "INSERT INTO custom_radgroupcheck(groupname,attribute,op,value) VALUES('FixtureGroup','Filter-Id','=','fixture');"
                    "INSERT INTO custom_billing_plans_profiles(plan_name,profile_name) VALUES('Plan','FixtureGroup');"
                    "INSERT INTO custom_userinfo(username,firstname,lastname,creationby) VALUES('untouched','Other','User','fixture');",v)
                for i,period in enumerate(('Daily','Weekly','Monthly','Yearly'),3):
                    db("INSERT INTO custom_billing_plans(id,planId,planName,planType,planCost,planTax,planCurrency,planRecurring,planRecurringPeriod,planTimeType,planTimeBank) VALUES("+str(i)+","+q('logical-'+period)+","+q(period)+",'PayPal','12.34','5','USD','Yes',"+q(period)+",'Accumulative','600');INSERT INTO custom_billing_plans_profiles(plan_name,profile_name) VALUES("+q(period)+",'FixtureGroup')",v)
            for v in ('base','candidate'):
                shutil.copytree(ROOT/'contrib/chilli',f/v/'contrib/chilli',ignore=shutil.ignore_patterns('daloradius.conf.php'))
                common=f/v/'app/common/includes';common.mkdir(parents=True)
                shutil.copy2(ROOT/'app/common/includes/pdo_connection.php',common/'pdo_connection.php')
                if v=='base':
                    for page in ('index.php','success.php'):
                        path='contrib/chilli/portal2/signup-paypal/'+page
                        (f/v/path).write_bytes(subprocess.check_output(['git','show',BASE+':'+path],cwd=ROOT))
                else:
                    for file in ('opendb.php','closedb.php'):
                        (f/v/'contrib/chilli/portal2/signup-paypal/library'/file).write_text("<?php throw new RuntimeException('Legacy registration connection reached');")
                configure(v)
            tls=f/'tls';tls.mkdir();(tls/'server.py').write_text(ipn.EMULATOR)
            run('openssl','req','-x509','-newkey','rsa:2048','-nodes','-days','1','-keyout',str(tls/'key.pem'),
                '-out',str(tls/'cert.pem'),'-subj','/CN=ipnpb.paypal.com','-addext',
                'subjectAltName=DNS:ipnpb.paypal.com,DNS:ipnpb.sandbox.paypal.com,DNS:www.paypal.com,DNS:www.sandbox.paypal.com')
            (tls/'key.pem').chmod(0o600)
            run('docker','run','-d','--name',TLS,'--network',NET,'--network-alias','ipnpb.paypal.com',
                '--network-alias','ipnpb.sandbox.paypal.com','--network-alias','www.paypal.com','--network-alias','www.sandbox.paypal.com',
                '-v',str(f)+':/fixtures','--entrypoint','python','python:3.13-alpine','/fixtures/tls/server.py')
            (f/'health.php').write_text('<?php echo "ready";')
            run('docker','run','-d','--name',WEB,'--network',NET,'-v',str(f)+':/fixtures','-e','PHP_CLI_SERVER_WORKERS=4',
                '--entrypoint','php',IMAGE,'-d','display_errors=0','-d','opcache.enable=0','-d','opcache.enable_cli=0',
                '-d','openssl.cafile=/fixtures/tls/cert.pem','-S','0.0.0.0:8080','-t','/fixtures')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            url='http://'+ip+':8080/'
            wait(lambda:urllib.request.urlopen(url+'health.php',timeout=5).status==200)
            wait(lambda:run('docker','exec',WEB,'php','-d','openssl.cafile=/fixtures/tls/cert.pem','-r',
                "$s=fsockopen('ssl://ipnpb.paypal.com',443);if(!$s)exit(1);fclose($s);echo 'ready';"))
            def client(v='candidate'):
                return Client(url+v+'/contrib/chilli/portal2/signup-paypal/')
            def state(v='candidate'):
                return tuple(db(query,v) for query in (
                    'SELECT id,firstname,lastname,address,city,state,creationby FROM custom_userinfo ORDER BY id',
                    'SELECT id,planName,contactperson,address,city,state,lastbill,nextbill,creationby FROM custom_userbillinfo ORDER BY id',
                    'SELECT id,planId,planName,payment_status,txn_type,vendor_type,payment_cost,payment_tax FROM custom_billing_merchant ORDER BY id',
                    'SELECT id,attribute,op FROM custom_radcheck ORDER BY id',
                    'SELECT id,attribute,op FROM custom_radreply ORDER BY id',
                    'SELECT id,groupname,priority FROM custom_radusergroup ORDER BY id',
                    'SELECT event_key,event_status FROM chilli_paypal_events ORDER BY event_key',
                    'SELECT id,planId,billAmount,billReason FROM custom_billing_history ORDER BY id'))
            def registered(response,v='candidate',length=8):
                status,body,_=response;assert status==200 and b'</html>' in body,'complete signup response'
                form=Form(body);pin=form.fields['os1'];token=form.fields['os0']
                assert len(pin)==length and len(token)==64,'generated shape'
                assert db('SELECT COUNT(*) FROM custom_userinfo i INNER JOIN custom_billing_merchant p ON i.username=p.username INNER JOIN custom_userbillinfo b ON b.username=i.username WHERE i.username='+q(pin)+' AND p.txnId='+q(token),v)=='1'
                return pin,token,form
            a=client('base').req();b=client().req()
            assert a[0]==b[0]==200 and Form(a[1]).options==Form(b[1]).options==['0','1','3','4','5','6']
            comparisons['initial plans/form']=True
            for name,values in [('ordinary',{}),('zero',{'planId':'0'}),('quotes',{'firstName':"Raw%+é'&",'lastName':'Quoted'})]+[(p,{'planId':str(i)}) for i,p in enumerate(('Daily','Weekly','Monthly','Yearly'),3)]:
                result=[];projections=[]
                for v in ('base','candidate'):
                    response,posted=client(v).submit(values);pin,token,form=registered(response,v)
                    info=db('SELECT firstname,lastname,address,city,state,creationby FROM custom_userinfo WHERE username='+q(pin),v)
                    bill=db('SELECT planName,contactperson,address,city,state,creationby FROM custom_userbillinfo WHERE username='+q(pin),v)
                    order=db('SELECT planId,planName,vendor_type,payment_status,txn_type FROM custom_billing_merchant WHERE txnId='+q(token),v)
                    assert db('SELECT COUNT(*) FROM custom_radcheck WHERE username='+q(pin),v)=='0'
                    assert db('SELECT COUNT(*) FROM chilli_paypal_events',v)=='0'
                    result.append((info,bill,order));projections.append({k:form.fields[k] for k in ('cmd','business','currency_code','item_number','item_name','quantity','tax','no_shipping','lc')})
                    if v=='candidate':
                        assert form.forms[0]['action']=='https://www.paypal.com/cgi-bin/webscr'
                        assert dict(urllib.parse.parse_qsl(urllib.parse.urlsplit(form.fields['return']).query))=={'txnId':token}
                        assert form.fields['notify_url'].endswith('/paypal-ipn.php')
                        assert response[2]['Cache-Control']=='no-store' and response[2]['Referrer-Policy']=='no-referrer'
                        if name in ('Daily','Weekly','Monthly','Yearly'):
                            assert {k:form.fields[k] for k in ('a3','p3','t3','src','sra')}=={'a3':'12.34','p3':'1','t3':{'Daily':'D','Weekly':'W','Monthly':'M','Yearly':'Y'}[name],'src':'1','sra':'1'}
                        else:assert form.fields['amount']==('0.00' if name=='zero' else '12.34')
                assert result[0]==result[1],('Pending projection parity',name)
                assert projections[0]==projections[1],('Checkout projection parity',name,{k:(projections[0][k],projections[1][k]) if k in ('tax','cmd','quantity','no_shipping','lc','currency_code') else 'different' for k in projections[0] if projections[0][k]!=projections[1][k]})
                comparisons['signup:'+name]=True
            checks.append('all three pending rows, addresses/contact, canonical numeric item_number, cost/percentage tax and four subscription forms')
            for status in ('Pending','Completed','Denied','Failed',''):
                for v in ('base','candidate'):
                    db('INSERT INTO custom_billing_merchant(username,txnId,planId,payment_status,vendor_type) VALUES('+q('receipt-user')+','+q('receipt-'+status)+",1,"+q(status)+",'PayPal')",v)
                page='success.php?'+urllib.parse.urlencode({'txnId':'receipt-'+status,'payment_status':'Completed','username':'wrong'})
                a=client('base').req(page);b=client().req(page);assert a[0]==b[0]==200
                def visible(body):return re.sub(rb'>\s+<',b'><',body).split(b'<body>',1)[1].split(b'</body>',1)[0].strip()
                assert visible(a[1])==visible(b[1]);assert (b'http-equiv="refresh"' in b[1])==(status!='Completed')
                comparisons['receipt:'+status]=True
            checks.append('persisted receipt statuses and historical layout/link; query username/status never grant payment')
            # Paired characterization: the legacy third-insert error strands two rows.
            for v in ('base','candidate'):
                db("CREATE TRIGGER r24_late BEFORE INSERT ON custom_billing_merchant FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture later insert failure'",v)
                before=state(v);count=int(db('SELECT COUNT(*) FROM custom_userinfo',v));bill=int(db('SELECT COUNT(*) FROM custom_userbillinfo',v))
                response,_=client(v).submit()
                if v=='base':
                    assert int(db('SELECT COUNT(*) FROM custom_userinfo',v))==count+1 and int(db('SELECT COUNT(*) FROM custom_userbillinfo',v))==bill+1 and b'PIN Code:' in response[1]
                else:assert response[0]==503 and state(v)==before and b'PIN Code:' not in response[1] and b'Buy Now' not in response[1]
                db('DROP TRIGGER r24_late',v)
            checks.append('native PEAR two-row partial write/false checkout versus PDO complete late rollback')
            def reject(updates=None,expected=400):
                before=state();response,fields=client().submit(updates)
                assert response[0]==expected and state()==before and b'PIN Code:' not in response[1] and b'SQLSTATE' not in response[1],('Rejected signup',expected,response[0])
            for table in ('custom_userinfo','custom_userbillinfo','custom_billing_merchant'):
                db('CREATE TRIGGER r24_insert BEFORE INSERT ON '+table+" FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture insert failure'")
                reject({},503);db('DROP TRIGGER r24_insert')
            for fields in [{'planId':'absent'},{'planId':'2'},{'planId':'01'},{'planId':'-1'},{'planId':'2147483648'},{'planId':"1 OR 1=1"},
                {'firstName':''},{'lastName':''},{'address':''},{'city':''},{'state':''},{'firstName':'x'*201},
                {'firstName[]':'invalid'},{'planId[]':'1'},{'csrf_token':'invalid'},{'csrf_token[]':'invalid'},{'submit[]':'invalid'}]:
                reject(fields,403 if any(k.startswith(('csrf_token','submit')) for k in fields) else 400)
            c=client();response,fields=c.submit();pin,token,form=registered(response);before=state()
            assert c.req(data=fields)[0]==403 and state()==before
            resumed=c.req();rpin,rtoken,rform=registered(resumed)
            assert rpin==pin and rtoken==token and rform.fields==form.fields and state()==before
            stranger=client().req('index.php?'+urllib.parse.urlencode({'txnId':token,'username':pin,'pin':pin}));assert stranger[0]==200 and b'PIN Code:' not in stranger[1] and state()==before
            assert client().req(method='PUT')[0]==405 and state()==before
            fresh=client();get=fresh.req();nonce=Form(get[1]).fields['csrf_token'];assert fresh.req(data={'submit':'Submit','csrf_token':nonce,'planId':'1'})[0]==400 and state()==before
            for field in ('firstName','lastName','address','city','state','planId'):
                fresh=client();get=fresh.req();fields={'submit':'Submit','csrf_token':Form(get[1]).fields['csrf_token'],'firstName':'Fixture','lastName':'Person','address':'Street','city':'Town','state':'State','planId':'1'}
                raw=urllib.parse.urlencode([(k+'[]' if k==field else k,v) for k,v in fields.items()]).encode()
                assert fresh.req(data=raw)[0]==400 and state()==before
            billid=db('SELECT id FROM custom_userbillinfo WHERE username='+q(pin))
            db("UPDATE custom_userbillinfo SET username='fixture-disconnected' WHERE id="+billid)
            corrupt=state();failed_resume=c.req();assert failed_resume[0]==503 and b'PIN Code:' not in failed_resume[1] and state()==corrupt
            db('UPDATE custom_userbillinfo SET username='+q(pin)+' WHERE id='+billid);assert state()==before
            expire=f/'candidate/contrib/chilli/portal2/signup-paypal/expire.php';expire.write_text("<?php session_start();$_SESSION['portal2_paypal_pending']['expires']=1;echo 'ok';")
            assert c.req('expire.php')[1]==b'ok';assert b'PIN Code:' not in c.req()[1] and state()==before
            checks.append('all required scalar fields/CSRF/replay; session-bound reload resumes the original PIN/order without writes; stranger/expired session cannot resume')
            for table in ('custom_userinfo','custom_userbillinfo','custom_billing_merchant','custom_billing_plans','custom_radcheck','custom_radreply','custom_radusergroup'):
                db('ALTER TABLE '+table+' ENGINE=MyISAM');reject({},503);db('ALTER TABLE '+table+' ENGINE=InnoDB')
            db('ALTER TABLE custom_userbillinfo MODIFY address VARCHAR(3)');reject({'address':'Long address'});db('ALTER TABLE custom_userbillinfo MODIFY address VARCHAR(200)')
            reject({'firstName':'a'*110,'lastName':'b'*110})
            for key,value in [('planCost','invalid'),('planTax','invalid'),('planCurrency','XXX'),('planRecurring','invalid')]:
                old=db('SELECT '+key+' FROM custom_billing_plans WHERE id=1');db('UPDATE custom_billing_plans SET '+key+'='+q(value)+' WHERE id=1');reject({});db('UPDATE custom_billing_plans SET '+key+'='+q(old)+' WHERE id=1')
            db("UPDATE custom_billing_plans SET planRecurringPeriod='Quarterly' WHERE id=5");reject({'planId':'5'});db("UPDATE custom_billing_plans SET planRecurringPeriod='Monthly' WHERE id=5")
            special="quote%+é'&<b>Plan</b>"
            db("UPDATE custom_billing_plans SET planName="+q(special)+' WHERE id=1')
            response,fields=client().submit({'firstName':'<script>bad()</script>'});pin,token,form=registered(response)
            assert form.fields['item_name']==special and b'<b>Plan</b>' not in response[1] and b'&lt;b&gt;Plan' in response[1]
            assert db('SELECT firstname FROM custom_userinfo WHERE username='+q(pin))=='<script>bad()</script>'
            db("UPDATE custom_billing_plans SET planName='Plan' WHERE id=1")
            db("UPDATE custom_billing_plans SET planCost='0.01',planTax='50' WHERE id=1")
            response,fields=client().submit();_,_,midpoint=registered(response);assert midpoint.fields['amount']=='0.01' and midpoint.fields['tax']=='0.01'
            db("UPDATE custom_billing_plans SET planCost='12.34',planTax='5' WHERE id=1")
            reject({'planId':'99999'})
            configure(updates={'CONFIG_USERNAME_LENGTH':'12'});response,fields=client().submit();registered(response,length=12);configure()
            checks.append('all seven InnoDB sources, physical address/contact capacity, malformed plans, unsupported recurrence and exact quoted/Unicode names')
            configure(updates={'CONFIG_USER_ALLOWEDRANDOMCHARS':'a'})
            inserts={'custom_userinfo':"INSERT INTO custom_userinfo(username) VALUES('aaaaaaaa')",
                'custom_userbillinfo':"INSERT INTO custom_userbillinfo(username) VALUES('aaaaaaaa')",
                'custom_billing_merchant':"INSERT INTO custom_billing_merchant(username,planId) VALUES('aaaaaaaa',1)",
                'custom_radcheck':"INSERT INTO custom_radcheck(username,attribute,op,value) VALUES('aaaaaaaa','Auth-Type',':=','Reject')",
                'custom_radreply':"INSERT INTO custom_radreply(username,attribute,op,value) VALUES('aaaaaaaa','Filter-Id','=','Fixture')",
                'custom_radusergroup':"INSERT INTO custom_radusergroup(username,groupname,priority) VALUES('aaaaaaaa','Existing',0)"}
            for table,insert in inserts.items():db(insert);reject({},503);db('DELETE FROM '+table+" WHERE username='aaaaaaaa'")
            configure();checks.append('configured PIN length and bounded collision refusal across all six account/order sources')
            configure(updates={'CONFIG_USER_ALLOWEDRANDOMCHARS':'b'})
            independent=[]
            for worker in WORKERS:
                run('docker','run','-d','--name',worker,'--network',NET,'-v',str(f)+':/fixtures',
                    '--entrypoint','php',IMAGE,'-d','display_errors=0','-d','opcache.enable=0','-d','opcache.enable_cli=0','-S','0.0.0.0:8080','-t','/fixtures')
                worker_ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',worker)
                worker_url='http://'+worker_ip+':8080/';wait(lambda:urllib.request.urlopen(worker_url+'health.php',timeout=5).status==200)
                independent.append(Client(worker_url+'candidate/contrib/chilli/portal2/signup-paypal/'))
            prepared=[]
            for cli in independent:
                get=cli.req();prepared.append({'submit':'Submit','csrf_token':Form(get[1]).fields['csrf_token'],'firstName':'Concurrent','lastName':'Person','address':'Street','city':'Town','state':'State','planId':'1'})
            lockname=db("SELECT CONCAT('dalo-chilli-signup-',LEFT(SHA2(DATABASE(),256),40))")
            blocker=subprocess.Popen(['docker','exec','-i',DB,'mariadb','-uroot','-N','-B','--unbuffered','candidate'],stdin=subprocess.PIPE,stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
            blocker.stdin.write('SELECT GET_LOCK('+q(lockname)+',10);\n');blocker.stdin.flush();assert blocker.stdout.readline().strip()=='1'
            with concurrent.futures.ThreadPoolExecutor(2) as pool:
                pending=[pool.submit(cli.req,data=fields) for cli,fields in zip(independent,prepared)]
                end=time.monotonic()+5;peak=0
                while time.monotonic()<end:
                    peak=max(peak,int(db("SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE STATE='User lock' AND INFO LIKE '%GET_LOCK%'")))
                    if peak>=2:break
                    time.sleep(.05)
                assert peak>=2,('Native concurrent lock waits',peak)
                blocker.stdin.write('SELECT RELEASE_LOCK('+q(lockname)+');\n');blocker.stdin.flush();blocker.communicate(timeout=10);blocker=None
                results=[future.result() for future in pending]
            assert sorted(r[0] for r in results)==[200,503]
            for table in ('custom_userinfo','custom_userbillinfo','custom_billing_merchant'):assert db('SELECT COUNT(*) FROM '+table+" WHERE username='bbbbbbbb'")=='1'
            for worker in WORKERS:run('docker','rm','-f',worker)
            configure();checks.append('two independent native advisory-lock waits and exactly one complete three-row PIN allocation')
            def checkout_event(form,kind='web_accept'):
                return {'option_selection1':form.fields['os0'],'option_selection2':form.fields['os1'],'item_number':form.fields['item_number'],'item_name':form.fields['item_name'],
                    'txn_type':kind,'txn_id':'FIXTURE-'+secrets.token_hex(10),'payment_status':'Completed','receiver_email':form.fields['business'],
                    'mc_gross':'12.96','tax':form.fields['tax'],'mc_currency':form.fields['currency_code'],'quantity':form.fields['quantity'],
                    'payment_date':'12:00:00 Sep 30, 2026 PDT','test_ipn':'0','first_name':'Fixture','last_name':'Person'}
            # One-shot newly created order through actual unchanged modern callback.
            c=client();response,fields=c.submit();pin,token,form=registered(response);data=checkout_event(form)
            before=state();invalid=dict(data,verify_sign='invalid');assert c.req('paypal-ipn.php',data=invalid)[0]==400 and state()==before
            assert b'http-equiv="refresh"' in c.req('success.php?txnId='+token)[1]
            raw=urllib.parse.urlencode(data).encode();result=c.req('paypal-ipn.php',data=raw);assert result[0]==200 and result[1]==b'processed'
            assert json.loads((tls/'metrics.json').read_text())['last_hash']==hashlib.sha256(b'cmd=_notify-validate&'+raw).hexdigest()
            assert db('SELECT value FROM custom_radcheck WHERE username='+q(pin)+" AND attribute='Auth-Type'")=='Accept'
            assert db('SELECT groupname FROM custom_radusergroup WHERE username='+q(pin))=='FixtureGroup'
            assert db('SELECT COUNT(*) FROM custom_billing_history WHERE username='+q(pin))=='1'
            success=c.req('success.php?txnId='+token);assert success[0]==200 and pin.encode() in success[1] and b'http-equiv="refresh"' not in success[1]
            before=state();assert c.req('paypal-ipn.php',data=raw)[1]==b'duplicate' and state()==before
            checks.append('signup-produced one-shot order, exact trusted TLS/raw-body verification, invalid/Completed/duplicate callback, billing and receipt')
            # Subscription enrollment is not a completed payment; payment/cancel stay callback-owned.
            c=client();response,fields=c.submit({'planId':'5'});pin,token,form=registered(response)
            signup=checkout_event(form,'subscr_signup');signup.update(subscr_id='SUB-'+secrets.token_hex(10),subscr_date='12:00:00 Sep 30, 2026 PDT')
            for key in ('txn_id','payment_status','payment_date','mc_gross','mc_currency'):signup.pop(key)
            assert c.req('paypal-ipn.php',data=signup)[1]==b'processed'
            assert db('SELECT COUNT(*) FROM custom_radcheck WHERE username='+q(pin))=='0'
            assert b'http-equiv="refresh"' in c.req('success.php?txnId='+token)[1]
            payment=checkout_event(form,'subscr_payment');payment['subscr_id']=signup['subscr_id'];assert c.req('paypal-ipn.php',data=payment)[1]==b'processed'
            assert db('SELECT lastbill,nextbill FROM custom_userbillinfo WHERE username='+q(pin))=='2026-09-30\t2026-10-30'
            assert pin.encode() in c.req('success.php?txnId='+token)[1]
            cancel=dict(signup,txn_type='subscr_cancel',subscr_date='13:00:00 Sep 30, 2026 PDT');assert c.req('paypal-ipn.php',data=cancel)[1]==b'processed'
            assert db('SELECT value FROM custom_radcheck WHERE username='+q(pin)+" AND attribute='Auth-Type'")=='Reject'
            assert b'Your user PIN' in c.req('success.php?txnId='+token)[1], 'receipt does not claim current subscription activation'
            before=state();assert c.req('paypal-ipn.php',data=payment)[1]==b'duplicate' and state()==before
            checks.append('signup-generated subscription enrollment/payment/cancel, unchanged callback ownership and historical receipt versus current authorization')
            # Legitimate repeated rows: completed payment must not be hidden by older Pending.
            for v in ('base','candidate'):
                db("INSERT INTO custom_billing_merchant(username,txnId,planId,payment_status,vendor_type) VALUES('repeat-user','repeat-receipt',5,'Pending','PayPal'),('repeat-user','repeat-receipt',5,'Completed','PayPal')",v)
            assert b'http-equiv="refresh"' in client('base').req('success.php?txnId=repeat-receipt')[1]
            assert b'Your user PIN' in client().req('success.php?txnId=repeat-receipt')[1]
            db("INSERT INTO custom_billing_merchant(username,txnId,planId,payment_status,vendor_type) VALUES('foreign-user','repeat-receipt',5,'Completed','PayPal')")
            assert client().req('success.php?txnId=repeat-receipt')[0]==400;db("DELETE FROM custom_billing_merchant WHERE username='foreign-user'")
            db("INSERT INTO custom_billing_merchant(username,txnId,planId,payment_status,vendor_type) VALUES('<script>bad()</script>','escaped-receipt',1,'Completed','PayPal')")
            escaped=client().req('success.php?txnId=escaped-receipt');assert escaped[0]==200 and b'<script>bad()</script>' not in escaped[1] and b'&lt;script&gt;' in escaped[1]
            before=state()
            for value in ('absent',"' OR 1=1 --",'0'):
                response=client().req('success.php?'+urllib.parse.urlencode({'txnId':value,'payment_status':'Completed'}));assert response[0]==200 and b'Your user PIN' not in response[1]
            for query in ('txnId[]=x','txnId=','txnId='+('x'*201)):
                assert client().req('success.php?'+query)[0]==400 and state()==before
            reader='reader'+secrets.token_hex(4);db('CREATE USER '+q(reader)+"@'%' IDENTIFIED BY '';GRANT SELECT ON candidate.* TO "+q(reader)+"@'%';")
            configure(updates={'CONFIG_DB_USER':reader});assert client().req()[0]==200 and client().req('success.php?txnId=receipt-Completed')[0]==200
            reject({},503);configure();db('DROP USER '+q(reader)+"@'%'")
            header=settings.pop('CONFIG_MERCHANT_SUCCESS_MSG_HEADER')
            try:
                configure();assert client().req('success.php?txnId=receipt-Completed')[0]==200 and state()==before
            finally:settings['CONFIG_MERCHANT_SUCCESS_MSG_HEADER']=header;configure()
            for updates,expected in [({'CONFIG_DB_TBL_DALOBILLINGMERCHANT':'missing_table'},503),({'CONFIG_DB_TBL_DALOBILLINGMERCHANT':'bad;table'},400)]:
                configure(updates=updates);response=client().req('success.php?txnId=receipt-Completed');assert response[0]==expected and b'Your user PIN' not in response[1];configure()
            for updates in [{'CONFIG_PAYPAL_RECEIVER_EMAIL':''},{'CONFIG_MERCHANT_IPN_URL_ROOT':'http://portal.example.invalid'},
                {'CONFIG_MERCHANT_WEB_PAYMENT':'https://other.example.invalid/pay'},{'CONFIG_PAYPAL_SANDBOX':'true'},
                {'CONFIG_MERCHANT_IPN_URL_RELATIVE_DIR':'../endpoint.php'}]:
                configure(updates=updates);reject({},503);configure()
            for updates in [{'CONFIG_USERNAME_LENGTH':'0'},{'CONFIG_USERNAME_LENGTH':'65'},{'CONFIG_USER_ALLOWEDRANDOMCHARS':'\"'},
                {'CONFIG_DB_TBL_DALOUSERBILLINFO':'bad;table'}]:
                configure(updates=updates);reject({},400);configure()
            assert state()==before
            configure(updates={'CONFIG_DB_NAME':'other','CONFIG_PAYPAL_SANDBOX':True,'CONFIG_MERCHANT_WEB_PAYMENT':'https://www.sandbox.paypal.com/cgi-bin/webscr'})
            response,fields=client().submit();pin,token,form=registered(response,'other');assert form.forms[0]['action']=='https://www.sandbox.paypal.com/cgi-bin/webscr' and state()==before
            configure();checks.append('repeated payment rows with consistent ownership; malformed/read-only/failed reads and configured schema/merchant/environment isolation')
            probe=f/'candidate/contrib/chilli/portal2/signup-paypal/probe.php'
            probe.write_text("""<?php
require __DIR__.'/library/config_read.php';require dirname(__DIR__,2).'/common/portal2Paypal.php';
$pdo=dalo_chilli_pdo_open($configValues);$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_SILENT);$pdo->beginTransaction();
$pdo->exec("UPDATE custom_userinfo SET firstname='Borrowed' WHERE username='untouched'");
if(($_GET['case']??'')==='bad'){$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT']='missing_table';}
try {if(($_GET['case']??'')==='register'){dalo_portal2_paypal_register($pdo,$configValues,[]);}else{dalo_portal2_paypal_receipt($pdo,$configValues,'receipt-Completed');}$failed=false;}catch(Throwable $e){$failed=true;}
$active=$pdo->inTransaction();$prior=$pdo->query("SELECT firstname FROM custom_userinfo WHERE username='untouched'")->fetchColumn()==='Borrowed';$pdo->rollBack();
$rolled=$pdo->query("SELECT firstname FROM custom_userinfo WHERE username='untouched'")->fetchColumn()!=='Borrowed';echo json_encode([$failed,$active,$prior,$rolled]);
""")
            for case in ('good','bad','register'):assert json.loads(client().req('probe.php?case='+case)[1])==[case!='good',True,True,True]
            assert state()==before;checks.append('borrowed silent-mode receipt success/failure and registration refusal preserve caller write/transaction')
            try:urllib.request.urlopen(url+'candidate/contrib/chilli/common/portal2Paypal.php');assert False
            except urllib.error.HTTPError as e:assert e.code==404
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True);logs=logs.stdout+logs.stderr
            failures=re.findall(r'PHP (?:Warning|Fatal error|Notice|Deprecated):[^\n]*',logs)
            assert not [line for line in failures if '/fixtures/candidate/' in line],'Candidate PHP diagnostics (details suppressed)'
            def expected_legacy(line):
                return ('PHP Deprecated:  Implicit conversion from float' in line and '/include/common/common.php' in line) or \
                    ('Trying to access array offset on' in line and '/fixtures/base/contrib/chilli/portal2/signup-paypal/success.php' in line)
            assert not [line for line in failures if not expected_legacy(line)],('Unknown fixture diagnostics (details suppressed)',len([line for line in failures if not expected_legacy(line)]))
            print('PASS R24',len(comparisons),'paired PEAR/PDO comparisons;',len(checks),'candidate/characterization families; native pending signup/resume/receipt/unchanged IPN',flush=True)
        finally:
            if blocker is not None and blocker.poll() is None:
                try:blocker.communicate('SELECT RELEASE_ALL_LOCKS();\n',timeout=10)
                except subprocess.TimeoutExpired:blocker.kill();blocker.communicate()
            for container in (WEB,TLS,DB,*WORKERS):run('docker','rm','-f',container,check=False)
            run('docker','network','rm',NET,check=False)
            if f.exists():run('docker','run','--rm','--network','none','-v',str(f)+':/fixtures','--entrypoint','sh',IMAGE,'-c','chmod -R a+rwX /fixtures',check=False)
    assert not any(PREFIX in n for n in run('docker','ps','-a','--format','{{.Names}}').splitlines())
    assert not any(PREFIX in n for n in run('docker','network','ls','--format','{{.Name}}').splitlines())
    print('PASS R24 fixture/config/TLS cleanup',flush=True)
if __name__=='__main__':main()