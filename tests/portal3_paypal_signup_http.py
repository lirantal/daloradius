#!/usr/bin/env python3
"""R25: native paired Portal3 pending signup/receipt and unchanged IPN via local TLS.
No real provider, payment, RADIUS, live configuration or persistent secrets used.
"""
import concurrent.futures, hashlib, html, http.cookiejar, json, re, secrets, shutil, subprocess, tempfile, time
import urllib.error, urllib.parse, urllib.request
from pathlib import Path
from html.parser import HTMLParser
import chilli_paypal_http as ipn
ROOT=Path(__file__).resolve().parents[1]
BASE='218393e0fafa07b7f335abdf8802052300dd29b2'
PREFIX='pdo-r25-'+secrets.token_hex(5)
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
        fields={'submit':'submit','firstName':'Fixture','lastName':'Person','address':'Street','city':'Town','state':'State','planId':'legacy-1'}
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
            keys=['DALOBILLINGPLANS','DALOUSERINFO','DALOBILLINGPAYPAL','RADCHECK','RADREPLY','RADGROUPCHECK','RADGROUPREPLY','RADUSERGROUP']
            tables={'CONFIG_DB_TBL_'+k:'custom_'+defaults['CONFIG_DB_TBL_'+k] for k in keys}
            settings=dict(tables,CONFIG_DB_ENGINE='mysqli',CONFIG_DB_HOST=DB,CONFIG_DB_PORT='3306',
                CONFIG_DB_USER='root',CONFIG_DB_PASS='',CONFIG_USER_ALLOWEDRANDOMCHARS='abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
                CONFIG_PAYPAL_RECEIVER_EMAIL='merchant@example.invalid',CONFIG_MERCHANT_BUSINESS_ID='merchant@example.invalid',CONFIG_PAYPAL_SANDBOX=False,
                CONFIG_PASSWORD_LENGTH='8',CONFIG_USERNAME_LENGTH='5',CONFIG_MERCHANT_IPN_URL_ROOT='https://portal.example.invalid/paypal',
                CONFIG_MERCHANT_WEB_PAYMENT='https://www.paypal.com/cgi-bin/webscr',
                CONFIG_MERCHANT_IPN_URL_RELATIVE_SUCCESS='success.php',CONFIG_MERCHANT_IPN_URL_RELATIVE_FAILURE='index.php',CONFIG_MERCHANT_IPN_URL_RELATIVE_DIR='paypal-ipn.php',
                CONFIG_PAYPAL_CA_FILE='/fixtures/tls/cert.pem',CONFIG_PAYPAL_SUCCESS_MSG_HEADER='<h1>Fixture receipt</h1>',
                CONFIG_PAYPAL_SUCCESS_MSG_PRE='<p>Waiting for payment</p>',CONFIG_PAYPAL_SUCCESS_MSG_POST='<p>Confirmed</p>')
            configs={}
            def configure(v='candidate',updates=None):
                cfg=dict(settings,CONFIG_DB_NAME=v);cfg.update(updates or {})
                path=f/v/'contrib/chilli/portal3/signup-paypal/library/daloradius.conf.php'
                text='<?php\n'+''.join('$configValues['+ipn.php(k)+']='+ipn.php(value)+';\n' for k,value in cfg.items())
                path.write_text(text);path.chmod(0o600);configs[v]=text
            for v in ('base','candidate','other'):
                run('docker','exec',DB,'mariadb','-uroot','-e','CREATE DATABASE '+v)
                for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):db((ROOT/'contrib/db'/name).read_text(),v)
                db('RENAME TABLE '+','.join(defaults[k]+' TO '+t for k,t in tables.items()),v)
                db("INSERT INTO custom_billing_plans(planId,planName,planType,planCost,planTax,planCurrency,planGroup,planTimeType,planTimeBank) VALUES"
                    "('legacy-1','Plan','PayPal','12.34','0.62','USD','FixtureGroup','Accumulative','600'),"
                    "('0','Zero plan','PayPal','0','0','EUR','','Accumulative','0'),"
                    "('not-paypal','Other','Other','1','0','USD','','','');"
                    "INSERT INTO custom_radgroupcheck(groupname,attribute,op,value) VALUES('FixtureGroup','Filter-Id','=','fixture');"
                    "INSERT INTO custom_userinfo(username,firstname,lastname,creationby) VALUES('untouched','Other','User','fixture');",v)
            for v in ('base','candidate'):
                shutil.copytree(ROOT/'contrib/chilli',f/v/'contrib/chilli',ignore=shutil.ignore_patterns('daloradius.conf.php'))
                common=f/v/'app/common/includes';common.mkdir(parents=True)
                shutil.copy2(ROOT/'app/common/includes/pdo_connection.php',common/'pdo_connection.php')
                if v=='base':
                    for page in ('index.php','success.php'):
                        path='contrib/chilli/portal3/signup-paypal/'+page
                        (f/v/path).write_bytes(subprocess.check_output(['git','show',BASE+':'+path],cwd=ROOT))
                else:
                    for file in ('opendb.php','closedb.php'):
                        (f/v/'contrib/chilli/portal3/signup-paypal/library'/file).write_text("<?php throw new RuntimeException('Legacy registration connection reached');")
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
            clients={v:Client(url+v+'/contrib/chilli/portal3/signup-paypal/') for v in ('base','candidate')}
            def state(v='candidate'):
                return tuple(db(query,v) for query in (
                    'SELECT id,firstname,lastname,address,city,state,creationby FROM custom_userinfo ORDER BY id',
                    'SELECT id,planName,planId,payment_status,mc_gross,tax FROM custom_billing_paypal ORDER BY id',
                    'SELECT id,attribute,op FROM custom_radcheck ORDER BY id',
                    'SELECT id,attribute,op FROM custom_radreply ORDER BY id',
                    'SELECT id,groupname,priority FROM custom_radusergroup ORDER BY id',
                    'SELECT event_key,event_status FROM chilli_paypal_events ORDER BY event_key'))
            def registered(response,v='candidate'):
                status,body,_=response;assert status==200 and b'</html>' in body,'complete signup response'
                form=Form(body)
                match=re.search(rb'PIN Code: <b>\s*(.*?)\s*</b>',body,re.S);assert match,'PIN absent (body suppressed)'
                pin=html.unescape(match[1].decode());token=form.fields['os0']
                assert len(pin)==8 and len(token)==64
                assert db('SELECT COUNT(*) FROM custom_userinfo i INNER JOIN custom_billing_paypal p ON i.username=p.username WHERE i.username='+q(pin)+' AND p.txnId='+q(token),v)=='1'
                return pin,token,form
            a=clients['base'].req();b=clients['candidate'].req()
            assert a[0]==b[0]==200 and Form(a[1]).options==Form(b[1]).options==['legacy-1','0']
            comparisons['initial plans/form']=True
            # Ordinary pending signup: compare complete non-sensitive SQL projection.
            for name,values in [('ordinary',{}),('zero',{'planId':'0'}),('quotes',{'firstName':"Raw%+é'&",'lastName':'Quoted'})]:
                result=[]
                for v in ('base','candidate'):
                    response,posted=clients[v].submit(values);pin,token,form=registered(response,v)
                    projection=db('SELECT firstname,lastname,address,city,state,creationby FROM custom_userinfo WHERE username='+q(pin),v)
                    plan=db('SELECT planName,planId,payment_status,mc_gross,tax,pin,password FROM custom_billing_paypal WHERE txnId='+q(token),v)
                    assert db('SELECT COUNT(*) FROM custom_radcheck WHERE username='+q(pin),v)=='0'
                    assert db('SELECT COUNT(*) FROM chilli_paypal_events',v)=='0'
                    checkout={key:form.fields[key] for key in ('cmd','business','amount','item_name','quantity','tax','item_number','no_note','lc','on0','cancel_return','notify_url')}
                    checkout['return']=urllib.parse.urlsplit(form.fields['return'])._replace(query='').geturl()
                    result.append((projection,plan,checkout))
                    if v=='base':assert 'currency_code' not in form.fields  # malformed historical name attribute, now repaired

                    if v=='candidate':
                        assert form.fields['currency_code'] in ('USD','EUR')
                        assert form.forms[0]['action']=='https://www.paypal.com/cgi-bin/webscr'
                        assert form.fields['business']=='merchant@example.invalid'
                        assert dict(urllib.parse.parse_qsl(urllib.parse.urlsplit(form.fields['return']).query))=={'txnId':token}
                        assert form.fields['notify_url'].endswith('/paypal-ipn.php') and form.fields['cancel_return'].endswith('/index.php')
                        assert response[2]['Cache-Control']=='no-store' and response[2]['Referrer-Policy']=='no-referrer'
                assert result[0]==result[1],('Pending projection parity',name);comparisons['signup:'+name]=True
            # Configured PASSWORD_LENGTH, not USERNAME_LENGTH, chooses the PIN.
            configure(updates={'CONFIG_PASSWORD_LENGTH':'12','CONFIG_USERNAME_LENGTH':'2'})
            response,fields=clients['candidate'].submit();status,body,_=response;form=Form(body)
            match=re.search(rb'PIN Code: <b>(.*?)</b>',body,re.S);assert status==200 and len(match[1])==12
            configure()
            configure(updates={'CONFIG_PAYPAL_RECEIVER_ID':'FixtureMerchantId'})
            response,_=clients['candidate'].submit();assert response[0]==200 and Form(response[1]).fields['business']=='merchant@example.invalid'
            configure()
            checks.append('PASSWORD_LENGTH independently configured, valid currency_code repairs malformed legacy attribute, and single pending registration projection; no RADIUS activation or event before IPN; valid complete checkout fields')
            # Correlation receipt states: pin only on exact Completed, never trust query status.
            for status in ('Pending','Completed','Denied','Failed',''):
                for v in ('base','candidate'):
                    db('INSERT INTO custom_billing_paypal(username,txnId,planId,planName,payment_status) VALUES('+q('receipt-user')+','+q('receipt-'+status)+",'legacy-1','Plan',"+q(status)+')',v)
                page='success.php?'+urllib.parse.urlencode({'txnId':'receipt-'+status,'payment_status':'Completed'})
                a=clients['base'].req(page);b=clients['candidate'].req(page)
                assert a[0]==b[0]==200
                def visible(body):
                    body=body.split(b'<body>',1)[1].split(b'</body>',1)[0]
                    return re.sub(rb'\s+',b' ',re.sub(rb'<[^>]*>',b' ',body)).strip()
                assert visible(a[1])==visible(b[1]);assert (b'http-equiv="refresh"' in b[1])==(status!='Completed')
                comparisons['receipt:'+status]=True
            checks.append('success receipt only observes persisted Completed; pending/denied/failed polling and injected query status ignored')
            # Native late INSERT error: baseline reports stage2 and keeps the earlier info;
            # candidate rolls both writes back and emits neither PIN nor Buy Now.
            for v in ('base','candidate'):
                db("CREATE TRIGGER r25_late BEFORE INSERT ON custom_billing_paypal FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture later insert failure'",v)
                before=state(v);count=int(db('SELECT COUNT(*) FROM custom_userinfo',v))
                response,_=clients[v].submit()
                if v=='base':
                    assert int(db('SELECT COUNT(*) FROM custom_userinfo',v))==count+1 and b'PIN Code:' in response[1]
                else:assert response[0]==503 and state(v)==before and b'PIN Code:' not in response[1] and b'Buy Now' not in response[1]
                db('DROP TRIGGER r25_late',v)
            checks.append('characterized PEAR partial write/false checkout; PDO later-order rollback with full non-sensitive state invariance')
            c=clients['candidate']
            def reject(updates,expected=400):
                before=state();response,fields=c.submit(updates)
                assert response[0]==expected and state()==before and b'PIN Code:' not in response[1] and b'SQLSTATE' not in response[1],('Rejected signup',expected,response[0])
            for fields in [{'planId':'absent'},{'planId':'not-paypal'},{'planId[]':'legacy-1','planId':None},
                {'firstName':'x'*201},{'firstName[]':'invalid','firstName':None},{'submit[]':'submit','submit':None},
                {'csrf_token':'invalid'},{'csrf_token[]':'invalid','csrf_token':None}]:
                fields={k:v for k,v in fields.items() if v is not None};reject(fields,403 if any(k.startswith(('csrf_token','submit')) for k in fields) else 400)
            db("CREATE TRIGGER r25_early BEFORE INSERT ON custom_userinfo FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture first insert failure'")
            reject({},503);db('DROP TRIGGER r25_early')
            for field in ('firstName','lastName','address','city','state','planId'):
                before=state();base_before=state('base')
                response,_=clients['base'].submit({field:''});assert response[0]==200 and state('base')==base_before
                response,_=clients['candidate'].submit({field:''});assert response[0]==400 and state()==before
                comparisons['presence:'+field]=True

            for badlength in ('0','65','08',[],None):
                configure(updates={'CONFIG_PASSWORD_LENGTH':badlength});reject({},400)
            configure()
            # Presence validation on real direct POST; replay consumes CSRF, no duplicate account.
            response,fields=c.submit();assert response[0]==200;before=state()
            assert c.req(data=fields)[0]==403 and state()==before
            get=c.req();token=Form(get[1]).fields['csrf_token']
            assert c.req(data={'submit':'submit','csrf_token':token,'planId':'legacy-1'})[0]==400 and state()==before
            assert c.req(method='PUT')[0]==405 and state()==before
            for field in ('firstName','lastName','address','city','state','planId'):
                get=c.req();fields={'submit':'submit','csrf_token':Form(get[1]).fields['csrf_token'],'firstName':'Fixture','lastName':'Person','address':'Street','city':'Town','state':'State','planId':'legacy-1'}
                fields[field]=['invalid'];raw=urllib.parse.urlencode([(k+'[]' if isinstance(v,list) else k, v[0] if isinstance(v,list) else v) for k,v in fields.items()]).encode()
                assert c.req(data=raw)[0]==400 and state()==before
            checks.append('all scalar fields, method, fresh session CSRF, missing controls and one-use replay gates')
            # Physical capacity/engine protections and exact plan selection.
            for table in ('custom_userinfo','custom_billing_paypal','custom_billing_plans','custom_radcheck','custom_radreply','custom_radusergroup'):
                db('ALTER TABLE '+table+' ENGINE=MyISAM');reject({},503);db('ALTER TABLE '+table+' ENGINE=InnoDB')
            db("ALTER TABLE custom_userinfo MODIFY firstname VARCHAR(3)");reject({'firstName':'Long name'});db('ALTER TABLE custom_userinfo MODIFY firstname VARCHAR(200)')
            db("ALTER TABLE custom_billing_paypal MODIFY planName VARCHAR(2)");reject({});db('ALTER TABLE custom_billing_paypal MODIFY planName VARCHAR(128)')
            db("INSERT INTO custom_billing_plans(planId,planName,planType,planCost,planTax,planCurrency) VALUES('legacy-1','Ambiguous','PayPal','1','0','USD')")
            reject({});db("DELETE FROM custom_billing_plans WHERE planName='Ambiguous'")
            special="quote%+é'&"
            db("INSERT INTO custom_billing_plans(planId,planName,planType,planCost,planTax,planCurrency,planGroup) VALUES("+q(special)+','+q('<b>Plan & Quote</b>')+",'PayPal','1.23','0.10','EUR','FixtureGroup')")
            response,fields=c.submit({'planId':special,'firstName':'<script>bad()</script>'});pin,token,form=registered(response)
            assert form.fields['item_name']=='<b>Plan & Quote</b>' and form.fields['item_number']==special
            assert b'<script>bad()</script>' not in response[1] and b'&lt;b&gt;Plan &amp; Quote&lt;/b&gt;' in response[1]
            before=state();response,_=c.submit({'firstName':'<script>bad()</script>','city':''})
            assert response[0]==400 and state()==before and b'<script>bad()</script>' not in response[1] and b'&lt;script&gt;' in response[1]
            for table,column in (('custom_userinfo','firstname'),('custom_billing_paypal','planId')):
                db('CREATE TRIGGER r25_mutation BEFORE INSERT ON '+table+" FOR EACH ROW SET NEW."+column+"='Mutated'")
                reject({},503);db('DROP TRIGGER r25_mutation')
            checks.append('configured metadata capacities, InnoDB, ambiguous/foreign plan rejection and quoted/Unicode/XSS form identities')
            # Existing identity cannot be reused by the PIN allocator; bounded retry.
            configure(updates={'CONFIG_USER_ALLOWEDRANDOMCHARS':'a'})
            for table in ('custom_userinfo','custom_billing_paypal','custom_radcheck','custom_radreply','custom_radusergroup'):
                if table=='custom_userinfo':insert="INSERT INTO custom_userinfo(username) VALUES('aaaaaaaa')"
                elif table=='custom_billing_paypal':insert="INSERT INTO custom_billing_paypal(username) VALUES('aaaaaaaa')"
                elif table=='custom_radcheck':insert="INSERT INTO custom_radcheck(username,attribute,op,value) VALUES('aaaaaaaa','Auth-Type',':=','Reject')"
                elif table=='custom_radreply':insert="INSERT INTO custom_radreply(username,attribute,op,value) VALUES('aaaaaaaa','Filter-Id','=','Fixture')"
                else:insert="INSERT INTO custom_radusergroup(username,groupname,priority) VALUES('aaaaaaaa','Existing',0)"
                db(insert);reject({},503);db('DELETE FROM '+table+" WHERE username='aaaaaaaa'")
            configure()
            checks.append('bounded secure allocation refuses collisions in info/order/RADIUS check/reply/mapping sources')
            # Actual two workers and independent sessions on the same deterministic PIN.
            configure(updates={'CONFIG_USER_ALLOWEDRANDOMCHARS':'b'})
            independent=[]
            # Separate HTTP server processes avoid CLI accept/backlog scheduling
            # making a two-request contention assertion accidentally sequential.
            for worker in WORKERS:
                run('docker','run','-d','--name',worker,'--network',NET,'-v',str(f)+':/fixtures',
                    '--entrypoint','php',IMAGE,'-d','display_errors=0','-d','opcache.enable=0',
                    '-d','opcache.enable_cli=0','-S','0.0.0.0:8080','-t','/fixtures')
                worker_ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',worker)
                worker_url='http://'+worker_ip+':8080/'
                wait(lambda:urllib.request.urlopen(worker_url+'health.php',timeout=5).status==200)
                independent.append(Client(worker_url+'candidate/contrib/chilli/portal3/signup-paypal/'))
            prepared=[]
            for client in independent:
                form=client.req();prepared.append({'submit':'submit','csrf_token':Form(form[1]).fields['csrf_token'],
                    'firstName':'Concurrent','lastName':'Person','address':'Street','city':'Town','state':'State','planId':'legacy-1'})
            lockname=db("SELECT CONCAT('dalo-chilli-signup-',LEFT(SHA2(DATABASE(),256),40))")
            blocker=subprocess.Popen(['docker','exec','-i',DB,'mariadb','-uroot','-N','-B','--unbuffered','candidate'],stdin=subprocess.PIPE,stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
            blocker.stdin.write('SELECT GET_LOCK('+q(lockname)+',10);\n');blocker.stdin.flush();assert blocker.stdout.readline().strip()=='1'
            with concurrent.futures.ThreadPoolExecutor(2) as pool:
                pending=[pool.submit(client.req,data=fields) for client,fields in zip(independent,prepared)]
                end=time.monotonic()+5
                peak=0
                while time.monotonic()<end:
                    peak=max(peak,int(db("SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE STATE='User lock' AND INFO LIKE '%GET_LOCK%'")))
                    if peak>=2:break
                    time.sleep(.05)
                assert peak>=2,('Native concurrent lock waits',peak,db("SELECT STATE,COUNT(*) FROM information_schema.PROCESSLIST GROUP BY STATE"))
                blocker.stdin.write('SELECT RELEASE_LOCK('+q(lockname)+');\n');blocker.stdin.flush();blocker.communicate(timeout=10);blocker=None
                results=[future.result() for future in pending]
            assert sorted(r[0] for r in results)==[200,503]
            assert db("SELECT COUNT(*) FROM custom_userinfo WHERE username='bbbbbbbb'")=='1'
            assert db("SELECT COUNT(*) FROM custom_billing_paypal WHERE username='bbbbbbbb'")=='1'
            for worker in WORKERS:run('docker','rm','-f',worker)
            configure();checks.append('observed two native advisory-lock waits and exactly one deterministic PIN allocation')
            # Complete signup-produced checkout correlation through unchanged IPN.
            response,fields=c.submit();pin,token,form=registered(response)
            data={'option_selection1':form.fields['os0'],'item_number':form.fields['item_number'],'item_name':form.fields['item_name'],
                'txn_type':'web_accept','txn_id':'FIXTURE-'+secrets.token_hex(10),'payment_status':'Completed','receiver_email':form.fields['business'],
                'mc_gross':'12.96','tax':form.fields['tax'],'mc_currency':form.fields['currency_code'],'quantity':form.fields['quantity'],
                'payment_date':'12:00:00 Sep 30, 2026 PDT','test_ipn':'0','first_name':'Fixture','last_name':'Person'}
            before=state();invalid=dict(data,verify_sign='invalid');assert c.req('paypal-ipn.php',data=invalid)[0]==400 and state()==before
            assert c.req('success.php?txnId='+urllib.parse.quote(token,safe=''))[1].find(b'http-equiv="refresh"')!=-1
            raw=urllib.parse.urlencode(data).encode();processed=c.req('paypal-ipn.php',data=raw)
            assert processed[0]==200 and processed[1]==b'processed'
            assert json.loads((tls/'metrics.json').read_text())['last_hash']==hashlib.sha256(b'cmd=_notify-validate&'+raw).hexdigest()
            assert db('SELECT value FROM custom_radcheck WHERE username='+q(pin)+" AND attribute='Auth-Type'")=='Accept'
            assert db('SELECT groupname FROM custom_radusergroup WHERE username='+q(pin))=='FixtureGroup'
            success=c.req('success.php?txnId='+urllib.parse.quote(token,safe=''));assert success[0]==200 and pin.encode() in success[1] and b'http-equiv="refresh"' not in success[1]
            before=state();repeat=c.req('paypal-ipn.php',data=raw);assert repeat[1]==b'duplicate' and state()==before
            checks.append('actual signup-generated order, trusted local TLS/raw verification, rejected invalid, completed activation/receipt and duplicate callback')
            # Read failure/missing row is not successful payment, and no POST/SQL marks it completed.
            before=state()
            response=c.req('success.php',data={'txnId':'receipt-Completed','payment_status':'Completed'})
            assert response[0]==200 and b'Your user PIN' not in response[1] and state()==before
            for value in ('absent',"' OR 1=1 --",'0'):
                response=c.req('success.php?'+urllib.parse.urlencode({'txnId':value,'payment_status':'Completed'}));assert response[0]==200 and b'Your user PIN' not in response[1]
            for query in ('txnId[]=x','txnId=','txnId='+('x'*201)):
                response=c.req('success.php?'+query);assert response[0]==400 and b'Your user PIN' not in response[1]
            db("INSERT INTO custom_billing_paypal(username,txnId,payment_status) VALUES('Duplicate','receipt-Completed','Completed')")
            assert c.req('success.php?txnId=receipt-Completed')[0]==400;db("DELETE FROM custom_billing_paypal WHERE username='Duplicate'")
            assert state()==before
            db("INSERT INTO custom_billing_paypal(username,txnId,payment_status) VALUES('<script>receipt()</script>','receipt-xss','Completed')")
            response=c.req('success.php?txnId=receipt-xss');assert response[0]==200 and b'<script>receipt()</script>' not in response[1] and b'&lt;script&gt;receipt()' in response[1]
            db("DELETE FROM custom_billing_paypal WHERE txnId='receipt-xss'")
            path=f/'candidate/contrib/chilli/portal3/signup-paypal/library/daloradius.conf.php'
            path.write_text(configs['candidate']+"unset($configValues['CONFIG_PAYPAL_SUCCESS_MSG_HEADER']);")
            response=c.req('success.php?txnId=receipt-Completed');assert response[0]==200 and b'Your user PIN' in response[1] and state()==before
            configure()
            reader='reader'+secrets.token_hex(4)
            db('CREATE USER '+q(reader)+"@'%' IDENTIFIED BY '';GRANT SELECT ON candidate.* TO "+q(reader)+"@'%';")
            configure(updates={'CONFIG_DB_USER':reader});assert c.req()[0]==200 and c.req('success.php?txnId=receipt-Completed')[0]==200
            reject({},503);configure();db('DROP USER '+q(reader)+"@'%'")
            for updates in [{'CONFIG_DB_TBL_DALOBILLINGPAYPAL':'missing_table'},{'CONFIG_DB_TBL_DALOBILLINGPAYPAL':'bad;table'}]:
                configure(updates=updates);response=c.req('success.php?txnId=receipt-Completed');assert response[0] in (400,503) and b'Your user PIN' not in response[1] and b'SQLSTATE' not in response[1]
            configure()
            for updates in [{'CONFIG_PAYPAL_RECEIVER_EMAIL':''},{'CONFIG_MERCHANT_BUSINESS_ID':'different@example.invalid'},{'CONFIG_MERCHANT_WEB_PAYMENT':'https://www.sandbox.paypal.com/cgi-bin/webscr'},{'CONFIG_MERCHANT_IPN_URL_RELATIVE_SUCCESS':'https://foreign.invalid/success.php'},{'CONFIG_MERCHANT_IPN_URL_ROOT':'http://portal.example.invalid'},
                {'CONFIG_MERCHANT_IPN_URL_ROOT':'https://user:pass@portal.example.invalid'}, {'CONFIG_PAYPAL_SANDBOX':'true'},
                {'CONFIG_DB_TBL_DALOUSERINFO':'bad;identifier'},{'CONFIG_USER_ALLOWEDRANDOMCHARS':'\"'}]:
                configure(updates=updates);reject({},400 if 'CONFIG_USER_ALLOWEDRANDOMCHARS' in updates or 'CONFIG_DB_TBL_DALOUSERINFO' in updates else 503)
            configure();assert state()==before
            # Config-selected schema isolates all reads and both pending writes.
            before=state();configure(updates={'CONFIG_DB_NAME':'other','CONFIG_PAYPAL_SANDBOX':True,'CONFIG_MERCHANT_WEB_PAYMENT':'https://www.sandbox.paypal.com/cgi-bin/webscr'})
            response,fields=c.submit();pin,token,form=registered(response,'other')
            assert form.forms[0]['action']=='https://www.sandbox.paypal.com/cgi-bin/webscr' and state()==before
            configure();checks.append('read-only receipt/SELECT grants, missing/invalid backend, merchant configuration and alternate schema isolation')
            # A read helper cannot take ownership of a caller transaction.
            probe=f/'candidate/contrib/chilli/portal3/signup-paypal/probe.php'
            probe.write_text("""<?php
require __DIR__.'/library/config_read.php';require dirname(__DIR__,2).'/common/portal3Paypal.php';
$pdo=dalo_chilli_pdo_open($configValues);$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_SILENT);$pdo->beginTransaction();
$pdo->exec("UPDATE custom_userinfo SET firstname='Borrowed' WHERE username='untouched'");
if(($_GET['case']??'')==='bad'){$configValues['CONFIG_DB_TBL_DALOBILLINGPAYPAL']='missing_table';}
try {
if(($_GET['case']??'')==='register'){dalo_portal3_paypal_register($pdo,$configValues,[]);}
else{dalo_portal3_paypal_receipt($pdo,$configValues,'receipt-Completed');}
$failed=false;}catch(Throwable $e){$failed=true;}
$active=$pdo->inTransaction();$prior=$pdo->query("SELECT firstname FROM custom_userinfo WHERE username='untouched'")->fetchColumn()==='Borrowed';
$pdo->rollBack();$rolled=$pdo->query("SELECT firstname FROM custom_userinfo WHERE username='untouched'")->fetchColumn()!=='Borrowed';
echo json_encode([$failed,$active,$prior,$rolled]);
""")
            for case in ('good','bad','register'):assert json.loads(c.req('probe.php?case='+case)[1])==[case!='good',True,True,True]
            assert state()==before;checks.append('borrowed silent-mode receipt preserves caller write/transaction on both successful and failed reads')
            silent=f/'candidate/contrib/chilli/portal3/signup-paypal/silent.php'
            silent.write_text("""<?php
require __DIR__.'/library/config_read.php';require dirname(__DIR__,2).'/common/portal3Paypal.php';
$pdo=dalo_chilli_pdo_open($configValues);$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_SILENT);
try {dalo_portal3_paypal_register($pdo,$configValues,array('firstName'=>'Silent','lastName'=>'Fixture',
'address'=>'Street','city'=>'Town','state'=>'State','planId'=>'legacy-1'));$failed=false;}
catch(Throwable $error){$failed=true;}
echo json_encode(array($failed,$pdo->inTransaction()));
""")
            for table in ('custom_userinfo','custom_billing_paypal'):
                db('CREATE TRIGGER r25_silent BEFORE INSERT ON '+table+" FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture silent fault'")
                before=state();assert json.loads(c.req('silent.php')[1])==[True,False] and state()==before
                db('DROP TRIGGER r25_silent')
            silent.unlink();checks.append('owned silent-mode first/last native execution failures release the transaction and roll back every row')
            # HTTPError must be consumed rather than permitting an authenticated provider execution.
            try:urllib.request.urlopen(url+'candidate/contrib/chilli/common/portal3Paypal.php');assert False
            except urllib.error.HTTPError as e:assert e.code==404
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True);logs=logs.stdout+logs.stderr
            failures=re.findall(r'PHP (?:Warning|Fatal error|Notice|Deprecated):[^\n]*',logs)
            candidate=[p for p in failures if '/fixtures/candidate/' in p]
            assert not candidate,('Candidate PHP diagnostics',len(candidate))
            # Baseline missing identity generates a known warning only when explicitly characterized.
            def expected_legacy(line):
                return ('PHP Deprecated:  Implicit conversion from float' in line and '/fixtures/base/' in line and '/include/common/common.php' in line) or \
                    ('Trying to access array offset on' in line and '/fixtures/base/contrib/chilli/portal3/signup-paypal/success.php' in line)
            unexpected=[line for line in failures if not expected_legacy(line)]
            assert not unexpected,('Unknown fixture diagnostics (details suppressed)',len(unexpected))
            assert comparisons and all(comparisons.values())
            print('PASS R25',len(comparisons),'paired PEAR/PDO comparisons;',len(checks),'candidate/characterization families; full native signup/receipt/IPN',flush=True)
        finally:
            if blocker is not None and blocker.poll() is None:
                try:blocker.communicate('SELECT RELEASE_ALL_LOCKS();\n',timeout=10)
                except subprocess.TimeoutExpired:blocker.kill();blocker.communicate()
            for container in (WEB,TLS,DB,*WORKERS):run('docker','rm','-f',container,check=False)
            run('docker','network','rm',NET,check=False)
            if f.exists():run('docker','run','--rm','--network','none','-v',str(f)+':/fixtures','--entrypoint','sh',IMAGE,'-c','chmod -R a+rwX /fixtures',check=False)
    assert not any(PREFIX in n for n in run('docker','ps','-a','--format','{{.Names}}').splitlines())
    assert not any(PREFIX in n for n in run('docker','network','ls','--format','{{.Name}}').splitlines())
    print('PASS R25 fixture/config/TLS cleanup',flush=True)
if __name__=='__main__':main()