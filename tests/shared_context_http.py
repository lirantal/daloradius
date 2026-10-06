#!/usr/bin/env python3
"""R20 native isolated PEAR/PDO contexts, reports, PDFs, SMTP and ticket reads.
No live config/data. Captured documents and generated cards stay in memory.
"""
import concurrent.futures, email, email.policy, json, os, re, secrets, shutil, socketserver
import subprocess, tempfile, threading, urllib.parse, urllib.request, urllib.error
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap
import user_actions_http as h
ROOT=Path(__file__).resolve().parents[1]
BASE="9c2cd027308391b11c8d58b2ab35068f8e7323ef"
PREFIX='pdo-r20-'+secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=[PREFIX+'-'+k for k in ('db','web','net')]
_original=h.run
def run(*a,**kw):
    try:return _original(*a,**kw)
    except RuntimeError:raise RuntimeError('Isolated fixture failure; details suppressed') from None
h.run=run
sql,wait_for=h.sql,h.wait_for
FILES=['include/common/notifications.php','include/common/printTickets.php',
       'include/management/userBilling.php','include/management/userReports.php','notifications/context.php']
class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self,*a,**kw):return None
OPENER=urllib.request.build_opener(NoRedirect)
class SMTP(socketserver.StreamRequestHandler):
    def handle(self):
        self.wfile.write(b'220 isolated fixture\r\n');recipient=''
        while True:
            line=self.rfile.readline()
            if not line:return
            command=line.split(b' ',1)[0].strip().upper()
            if command in (b'EHLO',b'HELO'):reply=b'250 isolated fixture\r\n'
            elif command==b'RCPT':
                recipient=line.decode().strip()[8:].strip('<>');reply=b'550 refused\r\n' if self.server.reject else b'250 OK\r\n'
            elif command==b'DATA':
                self.wfile.write(b'354 data\r\n');parts=[]
                while True:
                    part=self.rfile.readline()
                    if not part:return
                    if part==b'.\r\n':break
                    parts.append(part[1:] if part.startswith(b'..') else part)
                self.server.messages.append((recipient,b''.join(parts)));reply=b'250 accepted\r\n'
            elif command==b'QUIT':self.wfile.write(b'221 bye\r\n');return
            else:reply=b'250 OK\r\n'
            self.wfile.write(reply)
class Server(socketserver.ThreadingTCPServer):
    allow_reuse_address=True
    daemon_threads=True

def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    comparisons=0;smtp=None
    with tempfile.TemporaryDirectory(prefix=PREFIX+'-',dir=scratch) as temp:
        f=Path(temp)
        sample=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
        original_tables=dict(re.findall(r"\$configValues\['(CONFIG_DB_TBL_[^']+)'\]\s*=\s*'([^']+)'",sample))
        keys=['RADACCT','RADCHECK','RADREPLY','RADGROUPREPLY','RADUSERGROUP','DALOUSERINFO','DALOUSERBILLINFO',
              'DALOBILLINGINVOICE','DALOBILLINGINVOICEITEMS','DALOBILLINGINVOICESTATUS','DALOBILLINGINVOICETYPE',
              'DALOPAYMENTS','DALOBILLINGRATES','DALOBILLINGMERCHANT','DALOBILLINGPLANS','DALOBATCHHISTORY','DALOHOTSPOTS']
        tables={"CONFIG_DB_TBL_"+k:'custom_'+original_tables['CONFIG_DB_TBL_'+k] for k in keys}
        probe=r"""<?php
include '../common/includes/config_read.php';
include $configValues['OPERATORS_LIBRARY'].'/checklogin.php';
include $configValues['OPERATORS_LANG'].'/main.php';
include_once 'include/management/pages_common.php';
include_once '../common/includes/layout.php';
include_once 'include/management/userBilling.php';
include_once 'include/management/userReports.php';
include_once 'notifications/render.php';
include_once 'notifications/context.php';
mt_srand(11);
$case=$_GET['case']??'';$user=$_GET['username']??'Alice';$draw=(int)($_GET['draw']??1);
$pdo=null;
if (isset($_GET['borrow'])) {
    $pdo=dalo_shared_handle($configValues);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $pdo->beginTransaction();
    $pdo->exec("UPDATE custom_billing_rates SET rateCost=19 WHERE id=40");
}
ob_start();
try {
 switch($case) {
 case 'welcome':case 'batch':case 'invoice':
    if (function_exists('dalo_shared_handle')) { $handle=$pdo??dalo_shared_handle($configValues); }
    else { include '../common/includes/db_open.php';$handle=$dbSocket; }
    $params=json_decode($_GET['params']??'{}',true);
    $type=['welcome'=>'user-welcome','batch'=>'batch-details','invoice'=>'user-invoice'][$case];
    $result=notification_build($type,$configValues,$handle,$params);
    if (!function_exists('dalo_shared_handle')) { include '../common/includes/db_close.php'; }
    break;
 case 'subscription':$result=isset($_GET['borrow'])?userSubscriptionAnalysis($user,$draw,$pdo):userSubscriptionAnalysis($user,$draw);break;
 case 'plan':$result=isset($_GET['borrow'])?userPlanInformation($user,$draw,$pdo):userPlanInformation($user,$draw);break;
 case 'connection':$result=isset($_GET['borrow'])?userConnectionStatus($user,$draw,$pdo):userConnectionStatus($user,$draw);break;
 case 'online':$result=isset($_GET['borrow'])?checkUserOnline($user,$pdo):checkUserOnline($user);break;
 case 'invoices':$result=isset($_GET['borrow'])?userInvoicesStatus($_GET['id']??10,$draw,$pdo):userInvoicesStatus($_GET['id']??10,$draw);break;
 case 'rates':
    $args=[$user,'2020-01-01','2020-01-05',$_GET['rate']??'Rate0',$draw];if(isset($_GET['borrow']))$args[]=$pdo;
    $result=userBillingRatesSummary(...$args);break;
 case 'merchant':
    $args=[ '2020-01-01','2020-01-05',$_GET['email']??'',$_GET['address']??'',$_GET['payer']??'',$_GET['status']??'',$_GET['vendor']??'',$draw ];
    if(isset($_GET['borrow']))$args[]=$pdo;$result=userBillingPayPalSummary(...$args);break;
 case 'create':
    $args=[$_POST['user']??'Alice',json_decode($_POST['info']??'{}',true),json_decode($_POST['items']??'[]',true)];
    if(isset($_GET['borrow'])){$args[]=null;$args[]=$pdo;}
    $result=userInvoiceAdd(...$args);break;
 default:throw new RuntimeException('Unknown fixture case');
 }
 $output=ob_get_clean();$transaction=null;
 if($pdo){$transaction=$pdo->inTransaction();$changed=$pdo->query('SELECT rateCost FROM custom_billing_rates WHERE id=40')->fetchColumn();$pdo->rollBack();$after=$pdo->query('SELECT rateCost FROM custom_billing_rates WHERE id=40')->fetchColumn();$transaction=[$transaction,(int)$changed===19,(int)$after!==19];}
 header('Content-Type: application/json');echo json_encode(['result'=>$result,'output'=>$output,'transaction'=>$transaction]);
} catch(Throwable $e) {ob_end_clean();http_response_code(503);echo 'Fixture context failed: '.get_class($e).' at '.basename($e->getFile()).':'.$e->getLine().' calls '.implode(',',array_column($e->getTrace(),'function'));}
"""
        (f/'session.php').write_text(r"""<?php
session_name('daloradius_operator_sid');session_id($argv[1]);session_start();
$_SESSION=['daloradius_logged_in'=>true,'operator_id'=>(int)$argv[3],'operator_user'=>bin2hex(random_bytes(8)),
 'location_name'=>$argv[2],'time'=>time()];
require '/fixtures/'.$argv[4].'/app/operators/library/sessions.php';
echo dalo_csrf_token();
if(isset($argv[5]))$_SESSION['notification']=json_decode($argv[5],true);
session_write_close();
""")
        for v in ('base','candidate'):
            shutil.copytree(ROOT/'app',f/v/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if v=='base':
                restore_pear_bootstrap((f / v / 'app').parent, BASE)
                for p in FILES:(f/v/'app/operators'/p).write_bytes(subprocess.check_output(['git','show',BASE+':app/operators/'+p],cwd=ROOT))
            (f/v/'app/operators/probe.php').write_text(probe)
        try:
            run('docker','network','create','--internal',h.NETWORK)
            gateway=run('docker','network','inspect','-f','{{(index .IPAM.Config 0).Gateway}}',h.NETWORK)
            smtp=Server((gateway,0),SMTP);smtp.messages=[];smtp.reject=False
            threading.Thread(target=smtp.serve_forever,daemon=True).start()
            configs={}
            for v in ('base','candidate'):
                conf=sample
                overrides=dict(tables,CONFIG_DB_HOST=h.DB,CONFIG_DB_USER='root',CONFIG_DB_PASS='',CONFIG_DB_NAME=v,
                    CONFIG_IFACE_DEBUG='0',CONFIG_MAIL_ENABLED='yes',CONFIG_MAIL_SMTPADDR=gateway,
                    CONFIG_MAIL_SMTPPORT=str(smtp.server_address[1]),CONFIG_MAIL_SMTP_SECURITY='',
                    CONFIG_MAIL_SMTPFROM='sender@example.invalid',CONFIG_MAIL_SMTP_USERNAME='',CONFIG_MAIL_SMTP_PASSWORD='')
                for k,value in overrides.items():conf+='\n$configValues['+repr(k)+']='+repr(value)+';\n'
                conf+="\n$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>'"+h.DB+"','Username'=>'root','Password'=>'','Database'=>'"+v+"_other','Port'=>'3306');\n"
                configs[v]=conf;(f/v/'app/common/includes/daloradius.conf.php').write_text(conf)
            run('docker','run','-d','--name',h.DB,'--network',h.NETWORK,'--tmpfs','/var/lib/mysql','-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            def db(q,v='candidate'):
                p=subprocess.run(['docker','exec','-i',h.DB,'mariadb','-uroot','-N','-B',v],input="SET SESSION sql_mode='';\n"+q,text=True,capture_output=True)
                if p.returncode:raise RuntimeError('Fixture SQL error codes '+repr(re.findall(r'ERROR (\d+)',p.stderr)))
                return p.stdout.rstrip('\n')
            for v in ('base','candidate','base_other','candidate_other'):
                sql('CREATE DATABASE '+v)
                for n in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql','mariadb-daloradius-dictionaries.sql'):db((ROOT/'contrib/db'/n).read_text(),v)
                db('RENAME TABLE '+','.join(original_tables[k]+' TO '+name for k,name in tables.items()),v)
                db("INSERT INTO operators_acl(operator_id,file,access) VALUES(9001,'mng_new',1),(9001,'bill_invoice_edit',1),(9001,'rep_batch_details',1),(9001,'mng_batch_add',1),(9003,'bill_pos_new',1)",v)
                db("INSERT INTO custom_userinfo(id,username,firstname,lastname,email,address,city,state,zip,mobilephone) VALUES(10,'Alice','Alice','Customer','alice@example.invalid','Street','Town','State','12345','123'),(11,'Bob','Bob','Customer','bob@example.invalid','','','','',''),(12,'0','Zero','Customer','zero@example.invalid','','','','',''),(13,'Raw%+é','Raw','Customer','raw@example.invalid','','','','','');",v)
                db("INSERT INTO custom_hotspots(id,name,owner,address,companyphone,companyemail,companywebsite) VALUES(20,'Hotspot','Owner','Street','123','hotspot@example.invalid','https://example.invalid');INSERT INTO custom_batch_history(id,batch_name,batch_status,hotspot_id,creationdate,creationby) VALUES(30,'Batch','active',20,'2020-01-01','Fixture'),(31,'Empty','active',0,'2020-01-01','Fixture'),(32,'Raw%+é','active',20,'2020-01-01','Fixture');",v)
                db("INSERT INTO custom_billing_plans(id,planName,planActive,planCost,planTimeBank,planCurrency,planTimeType,planBandwidthUp,planBandwidthDown,planTrafficTotal,planTrafficUp,planTrafficDown,planRecurringPeriod) VALUES(1,'Plan','yes',12.75,10000,'EUR','Accumulative','64','128',100000,50000,50000,'Never'),(2,'Other','yes',8,5000,'USD','Time-To-Finish','32','64',0,0,0,'Monthly'),(3,'Uncoded','yes',1,60,'','Accumulative','','',0,0,0,'Never');",v)
                db("INSERT INTO custom_userbillinfo(id,username,planName,batch_id,email,emailinvoice,contactperson,company,phone,address,city,state,zip,country) VALUES(10,'Alice','Plan',30,'invoice@example.invalid','yes','Alice Customer','Company','123','Street','Town','State','12345','Country'),(11,'Bob','Plan',30,'bob@example.invalid','no','Bob Customer','','','','','','',''),(12,'0','Other',32,'zero@example.invalid','yes','Zero Customer','','','','','','','');",v)
                db("INSERT INTO custom_invoice(id,user_id,date,status_id,type_id,notes) VALUES(50,10,'2020-01-02',1,1,'First'),(51,10,'2020-01-03',2,1,'Mixed currency'),(52,11,'2020-01-02',1,1,'Empty'),(53,999,'2020-01-02',1,1,'Orphan'),(54,10,'2020-01-02',1,1,'Uncoded');INSERT INTO custom_invoice_items(invoice_id,plan_id,amount,tax_amount,notes) VALUES(50,1,12.75,0.25,'Item A'),(50,1,1.50,0.50,'Item B'),(51,1,5,0.50,'Euro'),(51,2,5,0.50,'Dollar'),(54,3,1,0,'No currency');INSERT INTO custom_payment(invoice_id,amount,date) VALUES(50,3,'2020-01-02'),(50,0.25,'2020-01-02');",v)
                db("INSERT INTO custom_billing_rates(id,rateName,rateType,rateCost) VALUES(40,'Rate0','1/hour',12),(41,'0','1/second',1),(42,'Invalid','0/hour',10);",v)
                db("INSERT INTO custom_radacct(radacctid,username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime,acctstoptime,acctsessiontime,acctinputoctets,acctoutputoctets,framedipaddress,calledstationid,callingstationid) VALUES(10,'Alice','a1','a1','192.0.2.1','2020-01-01','2020-01-01 01:00:00',3600,100,200,'192.0.2.2','Called','Calling'),(11,'Alice','a2','a2','192.0.2.1','2020-01-05','2020-01-05 02:00:00',7200,200,300,'192.0.2.2','Called','Calling'),(12,'Bob','b1','b1','192.0.2.1','2020-01-03',NULL,1800,10,20,'192.0.2.3','Called','Calling'),(13,'0','z1','z1','192.0.2.1','2020-01-03','2020-01-03 01:00:00',3600,50,50,'192.0.2.4','Called','Calling');",v)
                # Current periods have nonempty closed-session aggregates; latest Alice remains deterministic.
                db("INSERT INTO custom_radacct(radacctid,username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime,acctstoptime,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES(5,'Alice','today','today','192.0.2.1',CURRENT_DATE(),NOW(),60,25,50);INSERT INTO custom_radcheck(username,attribute,op,value) VALUES('Alice','Expiration',':=','31 Dec 2030');INSERT INTO custom_radreply(username,attribute,op,value) VALUES('Alice','Session-Timeout',':=','3600'),('Alice','Idle-Timeout',':=','300');INSERT INTO custom_radusergroup(username,groupname,priority) VALUES('0','Group',1);INSERT INTO custom_radgroupreply(groupname,attribute,op,value) VALUES('Group','Idle-Timeout',':=','600');",v)
                db("INSERT INTO custom_billing_merchant(id,username,planId,business_email,payer_email,payment_date,payment_total,payment_fee,payment_tax,payment_currency,vendor_type,payment_status,payment_address_status,payer_status) VALUES(70,'Alice',1,'business@example.invalid','alice@example.invalid','2020-01-02',12.75,0.10,0.25,'EUR','PayPal','Completed','confirmed','verified'),(71,'Alice',1,'business@example.invalid','alice@example.invalid','2020-01-05 23:59:59',1.25,0.05,0.10,'EUR','PayPal','Pending','unconfirmed','unverified'),(72,'Alice',1,'business@example.invalid','alice@example.invalid','2020-01-06',99,0,0,'EUR','PayPal','Completed','confirmed','verified');",v)
                if v.endswith('_other'):db("UPDATE custom_userinfo SET firstname='Other';UPDATE custom_invoice SET notes='Other';UPDATE custom_billing_rates SET rateCost=99 WHERE id=40;UPDATE custom_billing_plans SET planCost=99;UPDATE custom_radacct SET acctinputoctets=9999",v)
            urls={};sessions={};tokens={}
            for v in ('base','candidate'):
                web=h.WEB+'-'+v
                run('docker','run','-d','--name',web,'--network',h.NETWORK,'-v',str(f)+':/fixtures','-w','/fixtures/'+v+'/app/operators','--entrypoint','php',h.IMAGE,'-d','display_errors=0','-d','opcache.enable=0','-d','opcache.enable_cli=0','-S','0.0.0.0:8080','-t','.')
                ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',web);urls[v]='http://'+ip+':8080/'
                for label,loc,op in [('normal','default',9001),('other','other',9001),('denied','default',9002),('alternate','default',9003)]:
                    sid=secrets.token_hex(16);sessions[v,label]=sid
                    tokens[v,label]=run('docker','exec',web,'php','/fixtures/session.php',sid,loc,str(op),v)
            def req(page='probe.php',q=None,v='candidate',label='normal',data=None):
                headers={} if label is None else {'Cookie':'daloradius_operator_sid='+sessions[v,label]}
                request=urllib.request.Request(urls[v]+page+('?' +urllib.parse.urlencode(q,doseq=True) if q else ''),data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),headers=headers)
                try:
                    with OPENER.open(request,timeout=45) as r:return r.status,r.read(),r.headers
                except urllib.error.HTTPError as e:return e.code,e.read(),e.headers
            wait_for(lambda:req(q={'case':'online'}),'PHP HTTP')
            def probe(case,q=None,**kwargs):
                status,body,_=req(q=dict(q or {},case=case),**kwargs)
                assert status==200,('Probe status',case,status,body.decode() if body.startswith(b'Fixture context failed: ') else 'Body suppressed')
                return json.loads(body)
            def compare(case,q=None,label='normal'):
                nonlocal comparisons
                a=probe(case,q,v='base',label=label);b=probe(case,q,label=label)

                assert a==b,('Context parity',case,label,q)
                comparisons+=1
                return b
            for label in ('normal','other'):
                for case,params in [('welcome',{'username':'Alice'}),('welcome',{'username':'0'}),('welcome',{'username':'NoUser'}),('batch',{'batch_name':'Batch'}),('batch',{'batch_name':'Empty'}),('batch',{'batch_name':'Missing'}),('invoice',{'invoice_id':50}),('invoice',{'invoice_id':51}),('invoice',{'invoice_id':52}),('invoice',{'invoice_id':53}),('invoice',{'invoice_id':54}),('invoice',{'invoice_id':999})]:compare(case,{'params':json.dumps(params)},label)
                for case in ('subscription','plan','connection','online','rates'):
                    for user in ('Alice','0','NoUser'):
                        for draw in (0,1):compare(case,{'username':user,'draw':draw},label)
                for user_id in (10,11,999):
                    for draw in (0,1):compare('invoices',{'id':user_id,'draw':draw},label)
                for case in ({},{'email':'alice'},{'status':'Completed'},{'vendor':'PayPal'},{'address':'confirmed'},{'payer':'verified'},{'email':'Absent'}):compare('merchant',case,label)
            for unit in ('second','minute','hour','day','week','month'):
                for v in ('base','candidate'):db("UPDATE custom_billing_rates SET rateType='2/"+unit+"' WHERE id=40",v)
                compare('rates')
            for v in ('base','candidate'):db("UPDATE custom_billing_rates SET rateType='1/hour' WHERE id=40",v)
            # Raw identities no longer delete percent signs or display escaped SQL literals.
            result=probe('welcome',{'params':json.dumps({'username':'Raw%+é'})})['result'];assert result['recipient_email']=='raw@example.invalid'
            for case,params in [('welcome',{'username':[]}),('batch',{'batch_name':[]}),('invoice',{'invoice_id':[]})]:assert req(q={'case':case,'params':json.dumps(params)})[0]==503,('Malformed notification field',case)
            for case in ('subscription','plan','connection','rates'):assert probe(case,{'username[]':'Alice'})['result'] is False
            assert 'Unable to load user summary' in probe('rates',{'rate':'Invalid'})['output']
            # Native PDF endpoints: complete documents and deterministic content except creation metadata/IDs.
            def pdf_norm(pdf):
                assert pdf.startswith(b'%PDF-') and b'%%EOF' in pdf
                pdf=re.sub(rb'/(CreationDate|ModDate) \(.*?\)',b'/Date (normalized)',pdf)
                return re.sub(rb'/ID\s*\[.*?\]',b'/ID [normalized]',pdf,flags=re.S)
            notification='include/common/notifications.php'
            notification_cases=[('user-welcome',{'username':'Alice'}),('batch-details',{'batch_name':'Batch'}),('user-invoice',{'invoice_id':50})]
            for kind,params in notification_cases:
                for action in ('preview','download'):
                    values=[]
                    for v in ('base','candidate'):
                        status,pdf,headers=req(notification,dict(params,type=kind,action=action),v=v)
                        assert status==200 and headers['Content-Type']=='application/pdf',(kind,action,status)
                        assert ('attachment' if action=='download' else 'inline') in headers['Content-Disposition']
                        values.append(pdf_norm(pdf))
                    assert values[0]==values[1],('PDF parity',kind,action);comparisons+=1
                mails=[]
                for v in ('base','candidate'):
                    before=len(smtp.messages);status,body,_=req(notification,dict(params,type=kind,action='email'),v=v)
                    assert status==200 and b'Email sent successfully' in body and len(smtp.messages)==before+1,(kind,'SMTP native delivery')
                    recipient,raw=smtp.messages[-1];mail=email.message_from_bytes(raw,policy=email.policy.default)
                    attachment=next(mail.iter_attachments());html=mail.get_body(preferencelist=('html',)).get_content()
                    mails.append((recipient,str(mail['To']),str(mail['Subject']),html,attachment.get_filename(),pdf_norm(attachment.get_payload(decode=True))))
                assert mails[0]==mails[1],('SMTP payload parity',kind);comparisons+=1
            for kind,params in notification_cases:
                left=req(notification,dict(params,type=kind),v='base',label='other')
                right=req(notification,dict(params,type=kind),label='other')
                assert left[0]==right[0]==200 and pdf_norm(left[1])==pdf_norm(right[1]);comparisons+=1
                if kind!='batch-details':assert pdf_norm(right[1])!=pdf_norm(req(notification,dict(params,type=kind))[1])
            # SMTP refusal is an external-delivery failure, not an SQL rollback.
            before=len(smtp.messages);snapshot=db('SELECT id,notes FROM custom_invoice ORDER BY id')
            smtp.reject=True;status,body,_=req(notification,{'type':'user-invoice','invoice_id':50,'action':'email'});smtp.reject=False
            assert status==200 and b'Email sent successfully' not in body and len(smtp.messages)==before and db('SELECT id,notes FROM custom_invoice ORDER BY id')==snapshot
            for kind,params in notification_cases:assert req(notification,dict(params,type=kind),label='denied')[0]==403
            assert req(notification,{'type':'user-welcome','username':'Alice'},label='alternate')[0]==200
            assert req(notification,{'type':'user-welcome','username':'Alice'},label=None)[0]==302
            assert req(notification,{'type[]':'user-welcome'})[0]==400 and req(notification,{'type':'user-welcome','action[]':'email'})[0]==400
            # Session payload and GET priority through the actual producer-owned endpoint.
            sid=sessions['candidate','normal'];run('docker','exec',h.WEB+'-candidate','php','/fixtures/session.php',sid,'default','9001','candidate',json.dumps({'type':'user-welcome','username':'Bob'}))
            status,pdf,_=req(notification,{'username':'Alice'});assert status==200 and pdf_norm(pdf)==pdf_norm(req(notification,{'type':'user-welcome','username':'Alice'})[1])
            tokens['candidate','normal']=run('docker','exec',h.WEB+'-candidate','php','/fixtures/session.php',sid,'default','9001','candidate')
            ticket='include/common/printTickets.php'
            # Runtime-generated display-only pairs: never written to disk or printed.
            pairs=[(secrets.token_hex(8),secrets.token_hex(10)) for _ in range(5)]
            def cards(v='candidate',plan='Plan',label='normal'):
                fields={'type':'batch','batch_name':'Cards','ticketInformation':'Line A\nLine B','plan':plan,'csrf_token':tokens[v,label]}
                for i,pair in enumerate([('Username','Password')]+pairs):
                    fields['accounts['+str(i)+'][0]']=pair[0];fields['accounts['+str(i)+'][1]']=pair[1]
                return fields
            for plan in ('Plan','Other',''):
                rendered=[]
                for v in ('base','candidate'):
                    status,body,_=req(ticket,v=v,data=cards(v,plan));assert status==200 and body.count(b'class="card"')==5 and b'</html>' in body
                    rendered.append(body)
                assert rendered[0]==rendered[1],('Ticket parity',plan);comparisons+=1
            assert req(ticket,label='denied',data=cards(label='denied'))[0]==403
            fields=cards();fields['csrf_token']='invalid';assert req(ticket,data=fields)[0]==302
            fields=cards();fields['csrf_token[]']='invalid';del fields['csrf_token'];assert req(ticket,data=fields)[0]==400
            fields=cards();fields['plan[]']='Plan';del fields['plan'];assert req(ticket,data=fields)[0]==400
            assert req(ticket,data=cards(plan='Missing'))[0]==400
            configpath=f/'candidate/app/common/includes/daloradius.conf.php'
            configpath.write_text(configs['candidate']+"\n$configValues['CONFIG_MAIL_ENABLED']='no';\n")
            before=len(smtp.messages);status,body,_=req(notification,{'type':'user-invoice','invoice_id':50,'action':'email'})
            assert status==200 and b'E-mail delivery is disabled' in body and len(smtp.messages)==before
            configpath.write_text(configs['candidate'])
            before=len(smtp.messages);status,body,_=req(notification,{'type':'user-welcome','username':'NoUser','action':'email'})
            assert status==200 and b'No recipient e-mail address' in body and len(smtp.messages)==before
            db('ALTER TABLE custom_billing_plans RENAME COLUMN planCost TO hidden_column')
            status,body,_=req(ticket,data=cards());assert status==503 and b'class="card"' not in body and b'SQLSTATE' not in body
            db('ALTER TABLE custom_billing_plans RENAME COLUMN hidden_column TO planCost')

            for case,q in [('subscription',{}),('plan',{}),('connection',{}),('online',{}),('rates',{}),('merchant',{}),('invoices',{'id':10}),('invoice',{'params':json.dumps({'invoice_id':50})})]:
                result=probe(case,dict(q,borrow=1));assert result['transaction']==[True,True,True] and result['result'] is not False,('Borrowed ownership',case)
            print('PASS R20 ordinary native contexts, PDFs, real isolated SMTP and cards',flush=True)
            # Atomic callable invoice helper: parity excludes generated ids and audit actors.
            info={'date':'2020-02-01','notes':'New invoice','status_id':1,'type_id':1}
            items=[{'plan_id':1,'amount':'12.75','tax':'0.25','notes':'First'},{'plan_id':2,'amount':'1.50','tax':'0.50','notes':'Second'}]
            for user in ('Alice','10'):
                for v in ('base','candidate'):
                    result=probe('create',v=v,data={'user':user,'info':json.dumps(info),'items':json.dumps(items)});assert result['result'] is True
                query="SELECT user_id,date,status_id,type_id,notes FROM custom_invoice WHERE notes='New invoice' ORDER BY id;SELECT plan_id,amount,tax_amount,notes FROM custom_invoice_items WHERE invoice_id IN (SELECT id FROM custom_invoice WHERE notes='New invoice') ORDER BY id"
                assert db(query,'base')==db(query);comparisons+=1
            result=probe('create',{'borrow':1},data={'user':'Alice','info':json.dumps(info),'items':json.dumps(items)})
            assert result['result'] is False and result['transaction']==[True,True,True]
            def physical():return db('SELECT * FROM custom_invoice ORDER BY id;SELECT * FROM custom_invoice_items ORDER BY id')
            before=physical()
            db('CREATE TABLE fixture_visits(n INT) ENGINE=MyISAM')
            db("DELIMITER //\nCREATE TRIGGER fail_second BEFORE INSERT ON custom_invoice_items FOR EACH ROW BEGIN INSERT INTO fixture_visits VALUES(1);IF NEW.notes='Second' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Fixture';END IF;END//\nDELIMITER ;")
            assert probe('create',data={'user':'Alice','info':json.dumps(info),'items':json.dumps(items)})['result'] is False and physical()==before
            assert db('SELECT COUNT(*) FROM fixture_visits')=='2'
            db('DROP TRIGGER fail_second;DROP TABLE fixture_visits')
            for data in [{'user':'Missing'},{'user':'Alice','items':json.dumps([items[0],dict(items[1],amount='invalid')])},{'user':'Alice','info':json.dumps({'notes':[]})},{'user':'Alice','info':json.dumps({'date':'2020-02-31'})}]:
                assert probe('create',data=data)['result'] is False and physical()==before
            db('ALTER TABLE custom_invoice_items ENGINE=MyISAM')
            assert probe('create',data={'user':'Alice','items':json.dumps(items)})['result'] is False and physical()==before
            db('ALTER TABLE custom_invoice_items ENGINE=InnoDB')
            db('ALTER TABLE custom_invoice_items MODIFY notes VARCHAR(4)')
            shortened=physical();assert probe('create',data={'user':'Alice','items':json.dumps(items)})['result'] is False and physical()==shortened
            db('ALTER TABLE custom_invoice_items MODIFY notes VARCHAR(200)')
            # Named-location invoice mutation must never modify default schema.
            default_before=physical();other_before=db('SELECT COUNT(*) FROM custom_invoice','candidate_other')
            result=probe('create',label='other',data={'user':'Alice','info':json.dumps(info),'items':json.dumps(items)})
            assert result['result'] is True and physical()==default_before
            assert int(db('SELECT COUNT(*) FROM custom_invoice','candidate_other'))==int(other_before)+1
            # Independent native workers share no connection-local last-insert identity.
            (f/'candidate/app/operators/parallel.php').write_text(r"""<?php
$_SERVER['PHP_SELF']='/fixture';include '../common/includes/config_read.php';
$_SESSION=['operator_user'=>bin2hex(random_bytes(8)),'location_name'=>'default'];
include 'include/management/userBilling.php';
$ok=userInvoiceAdd('Alice', ['date'=>'2020-02-01','notes'=>'Concurrent'],
 [['plan_id'=>1,'amount'=>'1.25','tax'=>'0.25','notes'=>'Concurrent item']]);
echo json_encode($ok);
""")
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:
                outcomes=list(pool.map(lambda _:run('docker','exec',h.WEB+'-candidate','php','parallel.php'),range(2)))
            assert outcomes==['true','true']
            assert db("SELECT COUNT(DISTINCT a.id),COUNT(i.id) FROM custom_invoice a INNER JOIN custom_invoice_items i ON i.invoice_id=a.id WHERE a.notes='Concurrent' AND i.notes='Concurrent item'")=='2\t2'
            # Silent-mode borrowed error preserves the caller's write/transaction too.
            db('ALTER TABLE custom_radreply RENAME COLUMN value TO hidden_column')
            result=probe('subscription',{'borrow':1});assert result['result'] is False and result['transaction']==[True,True,True]
            db('ALTER TABLE custom_radreply RENAME COLUMN hidden_column TO value')
            print('PASS R20 invoice parity/late rollback, named writes, two native creators and borrowed ownership',flush=True)
            # SELECT-only access, unsafe identifiers and independently configured selected schemas.
            db("CREATE USER 'fixture_reader'@'%';GRANT SELECT ON candidate.* TO 'fixture_reader'@'%';GRANT SELECT ON candidate_other.* TO 'fixture_reader'@'%'")
            configpath=f/'candidate/app/common/includes/daloradius.conf.php'
            configpath.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_USER']='fixture_reader';\n")
            for kind,params in notification_cases:assert req(notification,dict(params,type=kind))[0]==200
            for case in ('subscription','plan','connection','online','rates','merchant','invoices'):assert probe(case)['result'] is not False
            assert req(ticket,data=cards())[0]==200
            configpath.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_TBL_DALOUSERINFO']='bad;table';\n")
            assert req(notification,{'type':'user-welcome','username':'Alice'})[0]==400
            configpath.write_text(configs['candidate'])
            # Late read faults after prior successful context queries; no mail/partial documents.
            for table,column,kind,params in [('custom_billing_plans','planCurrency','batch-details',{'batch_name':'Batch'}),('custom_invoice_items','notes','user-invoice',{'invoice_id':50})]:
                db('ALTER TABLE '+table+' RENAME COLUMN '+column+' TO hidden_column');before=len(smtp.messages)
                status,body,_=req(notification,dict(params,type=kind,action='email'))
                assert status==503 and len(smtp.messages)==before and b'%PDF' not in body and b'SQLSTATE' not in body
                db('ALTER TABLE '+table+' RENAME COLUMN hidden_column TO '+column)
            db('ALTER TABLE custom_radreply RENAME COLUMN value TO hidden_column')
            result=probe('subscription');assert result['result'] is False and '<table' not in result['output']
            db('ALTER TABLE custom_radreply RENAME COLUMN hidden_column TO value')
            db('ALTER TABLE custom_radacct RENAME COLUMN acctstoptime TO hidden_column')
            assert probe('online')['result']=='User status unavailable'
            result=probe('connection');assert result['result'] is False and '<table' not in result['output']
            db('ALTER TABLE custom_radacct RENAME COLUMN hidden_column TO acctstoptime')
            for legacy in ('db_open.php','db_close.php'):(f/'candidate/app/common/includes'/legacy).write_text("<?php throw new RuntimeException('Legacy shared handle reached');")
            for case in ('subscription','plan','connection','online','rates','merchant','invoices'):assert probe(case)['result'] is not False
            for kind,params in notification_cases:assert req(notification,dict(params,type=kind))[0]==200
            assert req(ticket,data=cards())[0]==200
            assert probe('create',data={'user':'Alice','items':json.dumps(items)})['result'] is True
            print('PASS R20 selected/read-only handles, late-read atomic rendering and PEAR tripwires',flush=True)
            (f/'candidate/app/operators/log-marker.php').write_text("<?php error_log('R20_LOG_CHANNEL_MARKER');echo 'ok';")
            req('log-marker.php')
            logs=subprocess.run(['docker','logs',h.WEB+'-candidate'],capture_output=True,text=True);text=logs.stdout+logs.stderr
            assert 'R20_LOG_CHANNEL_MARKER' in text
            markers=[x for x in ('PHP Fatal','PHP Warning','PHP Notice','PHP Deprecated','Uncaught','SQLSTATE') if x in text]
            assert not markers,('Candidate PHP log gate',markers)
            print('PASS R20',comparisons,'PEAR/PDO comparisons; native PDF/SMTP/tickets and candidate log gate',flush=True)
        finally:
            if smtp:smtp.shutdown();smtp.server_close();smtp.messages.clear()
            for v in ('base','candidate'):run('docker','rm','-f',h.WEB+'-'+v,check=False)
            # PDF font caches may be owned by root in disposable mounted copies.
            run('docker','run','--rm','--network','none','-v',str(f)+':/fixtures','--entrypoint','sh',h.IMAGE,'-c','chmod -R ugo+rwX /fixtures',check=False)
            run('docker','rm','-f',h.DB,check=False);run('docker','network','rm',h.NETWORK,check=False)
if __name__=='__main__':main()
