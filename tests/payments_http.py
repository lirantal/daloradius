#!/usr/bin/env python3
"""R17: native isolated HTTP/PHP/MariaDB catalogue reads against pinned PEAR.
No live configuration, data, credential snapshots or persistent services.
"""
import json,os,re,secrets,shutil,subprocess,tempfile,urllib.parse,urllib.request,urllib.error
from html.parser import HTMLParser
from pathlib import Path
import user_actions_http as h
from operator_reports_http import Rows
from acct_maintenance_http import Forms
ROOT=Path(__file__).resolve().parents[1]
BASE='2c7dc3e603bfbced669ddaa32b3ec161f3fd5ff9'
PREFIX='pdo-r17-'+secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=[PREFIX+'-'+k for k in ('db','web','net')]
_original=h.run
def run(*a,**kw):
    try:return _original(*a,**kw)
    except RuntimeError:raise RuntimeError('Isolated fixture failure; details suppressed') from None
h.run=run
sql,wait_for=h.sql,h.wait_for
PAGES=['bill-payments-new.php','bill-payments-edit.php','bill-payments-del.php','bill-payments-list.php']
DEFAULT={'bill-payments-edit.php':{'payment_id':'30'}}
class Controls(HTMLParser):
    def __init__(self,t):
        super().__init__(convert_charrefs=True);self.controls=[];self.select=None;self.option=None;self.textarea=None;self.feed(t)
    def name(self,n):return re.sub(r'item\d+','itemNEW',n) if self.new else n
    new=False
    def handle_starttag(self,tag,attrs):
        a=dict(attrs);n=a.get('name','')
        if tag=='input' and n and n!='csrf_token':self.controls.append(('input',self.name(n),a.get('type',''),a.get('value',''),'disabled' in a))
        if tag=='select':self.select=[self.name(n),[],'disabled' in a,'multiple' in a]
        if tag=='option' and self.select is not None:self.option=[a.get('value',''),'selected' in a,[]]
        if tag=='textarea':self.textarea=[self.name(n),[]]
    def handle_data(self,t):
        if self.option is not None:self.option[2].append(t)
        if self.textarea is not None:self.textarea[1].append(t)
    def handle_endtag(self,tag):
        if tag=='option' and self.option is not None:
            self.select[1].append((self.option[0],self.option[1],''.join(self.option[2])));self.option=None
        if tag=='select' and self.select is not None:self.controls.append(('select',*self.select));self.select=None
        if tag=='textarea' and self.textarea is not None:self.controls.append(('textarea',self.textarea[0],''.join(self.textarea[1])));self.textarea=None


def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(prefix=PREFIX+'-',dir=scratch) as tmp:
        f=Path(tmp);configs={};comparisons=0
        tables={'CONFIG_DB_TBL_DALOPAYMENTS':'custom_payments','CONFIG_DB_TBL_DALOPAYMENTTYPES':'custom_types','CONFIG_DB_TBL_DALOBILLINGINVOICE':'custom_invoice','CONFIG_DB_TBL_DALOUSERBILLINFO':'custom_bill'}
        for v in ('base','candidate'):
            shutil.copytree(ROOT/'app',f/v/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if v=='base':
                for p in PAGES:
                    raw=subprocess.check_output(['git','show',BASE+':app/operators/'+p],cwd=ROOT)
                    # Baseline PHP 8 sidebar trim(array) fatals before the selector.
                    # Scalarize only after PEAR action logic; SQL remains pinned and unmodified.
                    if p=='bill-payments-del.php':raw=raw.replace(b'    print_html_prologue($title, $langCode);',b"    $payment_id = '';\n    print_html_prologue($title, $langCode);")
                    (f/v/'app/operators'/p).write_bytes(raw)
            conf=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for k,value in dict(tables,CONFIG_DB_HOST=h.DB,CONFIG_DB_USER='root',CONFIG_DB_PASS='',CONFIG_DB_NAME=v,CONFIG_IFACE_TABLES_LISTING='2',CONFIG_IFACE_DEBUG='0',CONFIG_MAIL_ENABLED='no').items():conf+='\n$configValues['+repr(k)+']='+repr(value)+';\n'
            conf+="\n$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>'"+h.DB+"','Username'=>'root','Password'=>'','Database'=>'"+v+"_other','Port'=>'3306');\n"
            configs[v]=conf;(f/v/'app/common/includes/daloradius.conf.php').write_text(conf)
        (f/'session.php').write_text("<?php session_name('daloradius_operator_sid');session_id($argv[1]);session_start();$_SESSION=['daloradius_logged_in'=>true,'operator_id'=>(int)($argv[3]??9001),'operator_user'=>bin2hex(random_bytes(12)),'location_name'=>($argv[2]??'default'),'time'=>time()];session_write_close();")
        try:
            run('docker','network','create','--internal',h.NETWORK)
            run('docker','run','-d','--name',h.DB,'--network',h.NETWORK,'--tmpfs','/var/lib/mysql','-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            def db(q,v='candidate'):
                p=subprocess.run(['docker','exec','-i',h.DB,'mariadb','-uroot','-N','-B',v],input="SET SESSION sql_mode='';\n"+q,text=True,capture_output=True)
                if p.returncode:raise RuntimeError('Fixture SQL error codes '+repr(re.findall(r'ERROR (\d+)',p.stderr)))
                return p.stdout.rstrip('\n')
            for v in ('base','candidate','base_other','candidate_other'):
                sql('CREATE DATABASE '+v)
                for n in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql','mariadb-daloradius-dictionaries.sql'):db((ROOT/'contrib/db'/n).read_text(),v)
                db('RENAME TABLE payment TO custom_payments,payment_type TO custom_types,invoice TO custom_invoice,userbillinfo TO custom_bill',v)
                acl=','.join("(9001,'"+p[:-4].replace('-','_')+"',1),(9002,'"+p[:-4].replace('-','_')+"',0)" for p in PAGES)
                db('INSERT INTO operators_acl(operator_id,file,access) VALUES '+acl,v)
                db("INSERT INTO custom_bill(id,username) VALUES (10,'User A'),(11,'0'),(12,'Other User');INSERT INTO custom_invoice(id,user_id,date) VALUES (20,10,'2020-01-01'),(21,11,'2020-01-01'),(22,12,'2020-01-01');",v)
                for i in range(8):
                    db(f"INSERT INTO custom_payments(id,invoice_id,amount,date,type_id,notes,creationdate,creationby) VALUES ({30+i},{20+i%3},{i+1}.25,'2020-01-0{i+1}',{1+i%3},'"+('OtherOnly ' if v.endswith('_other') else 'Note ')+str(i)+"','2020-01-01','Fixture')",v)
                db("INSERT INTO custom_payments(id,invoice_id,amount,date,type_id,notes) VALUES (40,999,1.00,'2020-01-01',999,'')",v)
            urls={};sid=secrets.token_hex(16);other=secrets.token_hex(16);denied=secrets.token_hex(16)
            for v in ('base','candidate'):
                web=h.WEB+'-'+v
                run('docker','run','-d','--name',web,'--network',h.NETWORK,'-v',str(f)+':/fixtures','-w','/fixtures/'+v+'/app/operators','--entrypoint','php',h.IMAGE,'-d','display_errors=0','-d','opcache.enable=0','-d','opcache.enable_cli=0','-S','0.0.0.0:8080','-t','.')
                ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',web);urls[v]='http://'+ip+':8080/'
                for session,loc,op in ((sid,'default',9001),(other,'other',9001),(denied,'default',9002)):run('docker','exec',web,'php','/fixtures/session.php',session,loc,str(op))
            def req(page,q=None,v='candidate',session=sid,data=None):
                if data is not None:data={(k if not isinstance(value,list) or k.endswith('[]') else k+'[]'):value for k,value in data.items()}
                request=urllib.request.Request(urls[v]+page+('?' +urllib.parse.urlencode(q,doseq=True) if q else ''),data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),headers={'Cookie':'daloradius_operator_sid='+session} if session else {})
                try:
                    with urllib.request.urlopen(request,timeout=30) as r:return r.status,r.read().decode(),r.url
                except urllib.error.HTTPError as e:return e.code,e.read().decode(),e.url
            wait_for(lambda:req(PAGES[3]),'PHP HTTP')
            def compare(page,q=None,session=sid):
                nonlocal comparisons
                data=[]
                for v in ('base','candidate'):
                    status,text,_=req(page,q or DEFAULT.get(page),v,session);assert status==200 and '</html>' in text and 'Unable to read payment data' not in text,(v,page,'complete parity page',status, re.findall(r'(?:PHP (?:Fatal error|Warning|Notice)): ([^\n]+)',subprocess.run(['docker','logs',h.WEB+'-'+v],capture_output=True,text=True).stderr))
                    parser=Rows(text)
                    # Hidden deletion checkbox was undefined in PEAR; verify repaired IDs separately.
                    controls=[c for c in Controls(text).controls if not c[1].startswith('payment_id') or c[2]!='checkbox']
                    data.append((parser.projection(),controls))
                assert data[0]==data[1],(page,'row/control parity mismatch',q)
                comparisons+=1
            for p in PAGES:compare(p)
            for v in ('base','candidate'):(f/v/'app/common/includes/daloradius.conf.php').write_text(configs[v]+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='100';")
            for column in ('id','invoice_id','amount','date'):
                for order in ('asc','desc'):compare(PAGES[3],{'orderBy':column,'orderType':order})
            for q in ({'username':'User A'},{'user_id':'10'},{'invoice_id':'21'},{'invoice_id':'20','user_id':'10'},{'username':'User A','invoice_id':'21'}):compare(PAGES[3],q)
            for p,q in [(PAGES[0],{'payment_invoice_id':'20','payment_date':'2020-02-29'}),(PAGES[1],{'payment_id':'40'}),(PAGES[1],{'payment_id':'999'}),(PAGES[2],{'payment_id':['30','32']})]:compare(p,q)
            for v in ('base','candidate'):(f/v/'app/common/includes/daloradius.conf.php').write_text(configs[v])
            for page in range(1,6):compare(PAGES[3],{'page':str(page)})
            for p in PAGES:compare(p,DEFAULT.get(p),other)
            assert 'OtherOnly' in req(PAGES[1],DEFAULT[PAGES[1]],session=other)[1]
            assert not Rows(req(PAGES[3],{'invoice_id':'0'})[1]).rows
            assert Rows(req(PAGES[3],{'username':'0'})[1]).rows != Rows(req(PAGES[3],{'username':'0'},v='base')[1]).rows
            assert not Rows(req(PAGES[3],{'username':'Absent'})[1]).rows,'Unknown user cannot unfilter payments'
            assert not Rows(req(PAGES[3],{'username':"' OR 1=1 --"})[1]).rows
            for key in ('username','invoice_id','user_id','orderBy','orderType'):
                assert 'Unable to read payment data' in req(PAGES[3],{key+'[]':'bad'})[1]
            for p in PAGES:
                assert req(p,DEFAULT.get(p),session=denied)[2].endswith('/home-error.php')
                assert req(p,DEFAULT.get(p),session='')[2].endswith('/login.php')
            # Every row has a real deletion ID; both pagination controls retain filters.
            controls=Controls(req(PAGES[3],{'invoice_id':'20'})[1]).controls
            checked=[x[3] for x in controls if x[0]=='input' and x[2]=='checkbox' and x[1]=='payment_id[]'];assert checked and all(x.isdigit() for x in checked)
            for key,value in [('invoice_id','20'),('username','User A'),('user_id','10')]:
                links=[x for x in Rows(req(PAGES[3],{key:value})[1]).links if x.startswith('?') and ('page=' in x or 'orderBy=' in x)]
                assert links and all(urllib.parse.parse_qs(urllib.parse.urlsplit(x).query).get(key)==[value] for x in links)
                assert sum('page=2' in x for x in links)>=2,'Top and bottom pagination'
            def state(v='candidate'):
                return db('SELECT id,invoice_id,amount,date,type_id,IFNULL(notes,"<NULL>"),creationdate,creationby,updatedate,updateby FROM custom_payments ORDER BY id',v)
            def business(v='candidate'):
                return db('SELECT id,invoice_id,amount,date,type_id,IFNULL(notes,"<NULL>") FROM custom_payments ORDER BY id',v)
            def post(page,data,q=None,v='candidate',session=sid):
                token=next(x['csrf_token'] for x in Forms(req(page,q,v,session)[1]).forms if 'csrf_token' in x)
                return req(page,q,v,session,dict(data,csrf_token=token))[1]
            # Native HTTP mutations compare business rows to actual PEAR SQL results.
            for amount,kind,notes in [('12.30','paymentType-1',"O'Reilly &é"),('-2.50','','Refund'),('99999999.99','paymentType-2','maximum')]:
                for v in ('base','candidate'):
                    text=post(PAGES[0],{'payment_invoice_id':'20','payment_amount':amount,'payment_date':'2020-02-29','payment_type_id':kind,'payment_notes':notes},v=v)
                    assert 'Inserted new payment' in text,(v,'create')
                assert business('base')==business();comparisons+=1
                assert db('SELECT (creationdate IS NOT NULL AND creationby<>"" AND updatedate IS NULL AND updateby IS NULL) FROM custom_payments ORDER BY id DESC LIMIT 1')=='1'
            original_audit=db('SELECT creationdate,creationby FROM custom_payments WHERE id=30')
            for fields in [dict(payment_amount='5.50',payment_invoice_id='21',payment_type_id='paymentType-2',payment_notes="Edited ' &é",payment_date='2020-03-01'),dict(payment_amount='0',payment_notes='',payment_date='',payment_type_id=''),dict(payment_amount='-1.50'),dict(payment_amount='5.50')]:
                for v in ('base','candidate'):
                    text=post(PAGES[1],dict(fields,payment_id='30'),{'payment_id':'30'},v=v);assert 'Successfully updated payment' in text,(v,'edit')
                # Audit timestamps can cross a second boundary, compare stable business values.
                q='SELECT id,invoice_id,amount,date,type_id,IFNULL(notes,"<NULL>") FROM custom_payments ORDER BY id'
                assert db(q,'base')==db(q);comparisons+=1
                assert db('SELECT creationdate,creationby FROM custom_payments WHERE id=30')==original_audit
                assert db('SELECT (updatedate IS NOT NULL AND updateby<>"") FROM custom_payments WHERE id=30')=='1'
            for v in ('base','candidate'):
                text=post(PAGES[2],{'payment_id':['31']},v=v);assert 'Deleted ' in text
            assert db(q,'base')==db(q);comparisons+=1
            for v in ('base','candidate'):
                text=post(PAGES[2],{'payment_id':['34','35']},v=v);assert 'Deleted ' in text
            assert db('SELECT COUNT(*) FROM custom_payments WHERE id IN (34,35)','base')=='1'
            assert db('SELECT COUNT(*) FROM custom_payments WHERE id IN (34,35)')=='0', (db('SELECT id FROM custom_payments WHERE id IN (34,35)'), 'Failed to delete payments' in text, re.findall(r'(?:Failed[^<]*|Deleted[^<]*)',text))
            print('PASS R17 PEAR/PDO comparisons',comparisons,flush=True)
            # Scalar/decimal/date/notes/ID validation, CSRF and complete-selection validation.
            valid={'payment_invoice_id':'20','payment_amount':'3.30','payment_date':'2020-01-01','payment_type_id':'paymentType-1','payment_notes':'Valid'}
            before=state()
            for field,value in [('payment_invoice_id','999'),('payment_invoice_id','0'),('payment_invoice_id','20 OR 1=1'),('payment_invoice_id',['20']),('payment_type_id','paymentType-999'),('payment_type_id','paymentType-1 OR 1=1'),('payment_type_id',['paymentType-1']),('payment_amount','0'),('payment_amount','1.001'),('payment_amount','1e2'),('payment_amount','1,20'),('payment_amount','100000000.00'),('payment_amount',['1']),('payment_date','2020-02-30'),('payment_date','2020-01-01suffix'),('payment_date',['2020-01-01']),('payment_notes','x'*129),('payment_notes',['bad'])]:
                assert 'Failed to insert payment' in post(PAGES[0],dict(valid,**{field:value})),'Invalid create rejected'
                assert state()==before
            for value in ('999',['30','999'],['30','bad'],['30',['35']],[],['30']*501):
                assert 'Failed to delete payments' in post(PAGES[2],{'payment_id':value});assert state()==before
            for fields in [dict(payment_id='999'),dict(payment_id=['30']),dict(payment_id='30',payment_invoice_id='999'),dict(payment_id='30',payment_amount='1.001'),dict(payment_id='30',payment_notes='x'*129),dict(payment_id='30',payment_type_id='paymentType-999')]:
                text=post(PAGES[1],fields,{'payment_id':'30'});assert 'Successfully updated payment' not in text;assert state()==before
            for p,data in [(PAGES[0],valid),(PAGES[1],dict(valid,payment_id='30')),(PAGES[2],{'payment_id':['30','36']})]:
                for token in (None,'invalid',['bad']):
                    text=req(p,data=dict(data,**({} if token is None else {'csrf_token':token})))[1];assert 'CSRF token error' in text;assert state()==before
            # Duplicate IDs collapse; historical orphan deletion remains supported.
            assert 'Deleted 1 payment(s)' in post(PAGES[2],{'payment_id':['40','40']})
            # Named backend mutation cannot leak into default DB.
            before=state();text=post(PAGES[0],valid,session=other);assert 'Inserted new payment' in text and state()==before
            assert db('SELECT COUNT(*) FROM custom_payments','candidate_other')=='10'
            # Default date fallback, exact cents/leading zeros and explicit zero notes.
            assert 'Inserted new payment' in post(PAGES[0],dict(valid,payment_amount='00001.2',payment_date='',payment_notes='0'))
            assert db('SELECT amount,notes,(date=CURRENT_DATE()) FROM custom_payments ORDER BY id DESC LIMIT 1')=='1.20\t0\t1'
            # The runtime report SQL uses literal business identities and configurable names.
            db("INSERT INTO custom_bill(id,username) VALUES (13,'O''Reilly % + &é');INSERT INTO custom_invoice(id,user_id,date) VALUES (23,13,'2020-01-01');INSERT INTO custom_payments(id,invoice_id,amount,date,type_id,notes) VALUES (79,23,1,'2020-01-01',999,'special')")
            special="O'Reilly % + &é";text=req(PAGES[3],{'username':special})[1]
            assert len(Rows(text).rows)==1
            for link in Rows(text).links:
                if link.startswith('?') and 'orderBy=' in link:assert urllib.parse.parse_qs(urllib.parse.urlsplit(link).query)['username']==[special]
            # Real denied ACL applies to POST as well, before any payment write.
            before=state()
            for page,data in [(PAGES[0],valid),(PAGES[1],dict(valid,payment_id='30')),(PAGES[2],{'payment_id':['30','36']})]:
                token=next(x['csrf_token'] for x in Forms(req(page,{'payment_id':'30'} if page==PAGES[1] else None)[1]).forms if 'csrf_token' in x)
                assert req(page,session=denied,data=dict(data,csrf_token=token))[2].endswith('/home-error.php') and state()==before
            # Unknown selected backend and malicious configured identifiers never fall back.
            config=f/'candidate/app/common/includes/daloradius.conf.php'
            missing=secrets.token_hex(16);run('docker','exec',h.WEB+'-candidate','php','/fixtures/session.php',missing,'missing','9001')
            missing_status,missing_text,missing_url=req(PAGES[3],session=missing)
            assert missing_url.endswith('/home-error.php') and state()==before,(missing_status,urllib.parse.urlsplit(missing_url).path)
            config.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_TBL_DALOPAYMENTS']='custom_payments;DROP TABLE custom_invoice';")
            assert 'verify its state before retrying' in post(PAGES[0],valid) and state()==before
            config.write_text(configs['candidate'])
            for table in ('custom_invoice','custom_types'):
                db(f'ALTER TABLE {table} ENGINE=MyISAM');before=state()
                assert 'verify its state before retrying' in post(PAGES[0],valid) and state()==before
                db(f'ALTER TABLE {table} ENGINE=InnoDB')
            # Force a real late server-side DELETE error after an earlier row was removed.
            db("INSERT INTO custom_payments(id,invoice_id,amount,date,type_id) VALUES (80,20,1,'2020-01-01',1),(81,20,2,'2020-01-01',1);\nDELIMITER $$\nCREATE TRIGGER fail_payment_delete BEFORE DELETE ON custom_payments FOR EACH ROW BEGIN IF OLD.id=81 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture failure'; END IF; END$$\nDELIMITER ;\n")
            before=state();assert 'Failed to delete payments' in post(PAGES[2],{'payment_id':['80','81']});assert state()==before
            db('DROP TRIGGER fail_payment_delete')
            # Native INSERT/UPDATE trigger errors and nontransactional preflight are fail-closed.
            for mode,page,data in [('INSERT',PAGES[0],valid),('UPDATE',PAGES[1],dict(valid,payment_id='30'))]:
                db(f"CREATE TRIGGER fail_payment BEFORE {mode} ON custom_payments FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture failure'")
                before=state();text=post(page,data,{'payment_id':'30'} if mode=='UPDATE' else None)
                assert 'verify its state before retrying' in text and 'SQLSTATE' not in text and state()==before;db('DROP TRIGGER fail_payment')
            db('ALTER TABLE custom_payments ENGINE=MyISAM');before=state()
            assert 'verify its state before retrying' in post(PAGES[0],valid) and state()==before
            assert 'Failed to delete payments' in post(PAGES[2],{'payment_id':['80','81']}) and state()==before
            db('ALTER TABLE custom_payments ENGINE=InnoDB')
            print('PASS R17 CRUD validation, rollback, named backend and engine preflight',flush=True)

            # CLI workers exercise native PDO providers; rendezvous files are fixture-only.
            (f/'worker.php').write_text("""<?php
chdir('/fixtures/candidate/app/operators');
require '../common/includes/daloradius.conf.php';
require 'library/payments_pdo.php'; require 'library/invoice_delete.php';
$_SESSION=['location_name'=>'default'];
try {
    $job=json_decode(stream_get_contents(STDIN),true);$pdo=dalo_payment_open($configValues);
    if ($job['kind']==='owner') {
        $pdo->beginTransaction();$pdo->exec("INSERT INTO custom_payments(id,invoice_id,amount,date,type_id) VALUES(96,20,1,'2020-01-01',1)");
        try {dalo_payment_mutate($pdo,$configValues,'del',[96],[],'Fixture');throw new Exception('Expected ownership rejection');}
        catch(RuntimeException $e) {if (!$pdo->inTransaction()) {throw new Exception('Caller transaction lost');}}
        $pdo->rollBack();echo 'OWNERSHIP_OK';exit;
    }
    if ($job['kind']==='invoice-delete') {
        $pdo->beginTransaction();$q=$pdo->prepare('SELECT id FROM custom_invoice WHERE id=? FOR UPDATE');$q->execute([$job['id']]);$q->closeCursor();
        file_put_contents('/fixtures/locked','1');
        $deadline=microtime(true)+10;while(!is_file('/fixtures/release') && microtime(true)<$deadline){usleep(10000);}
        if (!is_file('/fixtures/release')) {throw new Exception('Worker rendezvous timeout');}
        $pdo->prepare('DELETE FROM custom_payments WHERE invoice_id=?')->execute([$job['id']]);
        $pdo->prepare('DELETE FROM custom_invoice WHERE id=?')->execute([$job['id']]);$pdo->commit();echo 'DELETED';exit;
    }
    if ($job['kind']==='invoice-provider') {
        dalo_delete_invoices($pdo,$configValues,[$job['id']]);echo 'DELETED';exit;
    }
    $ids=($job['mode']==='new')?[]:dalo_payment_ids($job['ids']);
    $values=($job['mode']==='del')?[]:dalo_payment_fields($job['fields'],$job['mode']==='new');
    $result=dalo_payment_mutate($pdo,$configValues,$job['mode'],$ids,$values,'Fixture');echo 'OK:'.$result;
} catch(Throwable $e) {if (isset($pdo)&&$pdo->inTransaction()){$pdo->rollBack();}echo 'REJECTED:'.get_class($e).':'.$e->getCode().':'.($e instanceof PDOException ? ($e->errorInfo[1]??'') : '');}
""")
            def worker(job):
                child=subprocess.Popen(['docker','exec','-i',h.WEB+'-candidate','php','/fixtures/worker.php'],stdin=subprocess.PIPE,stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
                child.stdin.write(json.dumps(job));child.stdin.close();return child
            def result(child):
                child.wait(timeout=20);text=child.stdout.read();err=child.stderr.read();assert child.returncode==0 and not err,'Native worker log gate';return text
            assert result(worker({'kind':'owner'}))=='OWNERSHIP_OK' and db('SELECT COUNT(*) FROM custom_payments WHERE id=96')=='0'
            # Existing invoice/POS/batch delete ordering: payment creation loses if parent deletion commits first.
            db("INSERT INTO custom_invoice(id,user_id,date) VALUES(90,10,'2020-01-01')")
            holder=worker({'kind':'invoice-delete','id':90})
            wait_for(lambda:(f/'locked').read_text(),'Invoice lock acquired')
            loser=worker({'kind':'payment','mode':'new','fields':dict(valid,payment_invoice_id='90')})
            def blocked():
                value=db("SELECT COUNT(*) FROM information_schema.INNODB_LOCK_WAITS")
                if value=='0':raise RuntimeError('Waiting for native lock contention')
                return True
            wait_for(blocked,'Payment waits on invoice lock');(f/'release').write_text('1')
            assert result(holder)=='DELETED' and result(loser).startswith('REJECTED:')
            assert db('SELECT COUNT(*) FROM custom_payments WHERE invoice_id=90')=='0'
            (f/'locked').unlink();(f/'release').unlink()
            # Reverse order: pause real payment provider after INSERT but before COMMIT;
            # actual existing invoice_delete provider must wait, then remove the new child.
            payment_provider=f/'candidate/app/operators/library/payments_pdo.php';payment_original=payment_provider.read_text()
            pause="""        if (is_file('/fixtures/pause-write')) {
            file_put_contents('/fixtures/locked','1');$deadline=microtime(true)+10;
            while (!is_file('/fixtures/release') && microtime(true)<$deadline) {usleep(10000);}
            if (!is_file('/fixtures/release')) {throw new RuntimeException('Fixture rendezvous timeout');}
        }
"""
            payment_provider.write_text(payment_original.replace('        if (!$pdo->commit())',pause+'        if (!$pdo->commit())'))
            (f/'pause-write').write_text('1');db("INSERT INTO custom_invoice(id,user_id,date) VALUES(91,10,'2020-01-01')")
            winner=worker({'kind':'payment','mode':'new','fields':dict(valid,payment_invoice_id='91')})
            wait_for(lambda:(f/'locked').read_text(),'Payment holds invoice lock')
            delete=worker({'kind':'invoice-provider','id':91});wait_for(blocked,'Invoice deletion waits on payment');(f/'release').write_text('1')
            assert result(winner).startswith('OK:') and result(delete)=='DELETED'
            assert db('SELECT COUNT(*) FROM custom_payments WHERE invoice_id=91')=='0' and db('SELECT COUNT(*) FROM custom_invoice WHERE id=91')=='0'
            for name in ('pause-write','locked','release'):(f/name).unlink()
            # Concurrent edit/delete: snapshot-isolation conflicts must fail closed.
            for snapshot in ('ON','OFF'):
                sql('SET GLOBAL innodb_snapshot_isolation='+snapshot)
                (f/'pause-write').write_text('1')
                editor=worker({'kind':'payment','mode':'edit','ids':['80'],'fields':{'payment_amount':'4.44'}})
                wait_for(lambda:(f/'locked').read_text(),'Payment edit before commit')
                deleter=worker({'kind':'payment','mode':'del','ids':['80']});wait_for(blocked,'Delete waits on edit');(f/'release').write_text('1')
                editor_result=result(editor);delete_result=result(deleter)
                assert editor_result=='OK:80',editor_result
                for name in ('pause-write','locked','release'):(f/name).unlink()
                if snapshot=='ON':
                    assert delete_result=='REJECTED:PDOException:HY000:1020',delete_result
                    assert db('SELECT amount FROM custom_payments WHERE id=80')=='4.44'
                    # A fresh request succeeds; no automatic replay of an uncertain write.
                    assert result(worker({'kind':'payment','mode':'del','ids':['80']}))=='OK:1'
                    db("INSERT INTO custom_payments(id,invoice_id,amount,date,type_id) VALUES(80,20,1,'2020-01-01',1)")
                else:
                    assert delete_result=='OK:1',delete_result
                    assert db('SELECT COUNT(*) FROM custom_payments WHERE id=80')=='0'
            sql('SET GLOBAL innodb_snapshot_isolation=ON')
            payment_provider.write_text(payment_original)
            print('PASS R17 caller ownership, four observed concurrent interleavings and fresh conflict retry',flush=True)
            # Default connection is deliberately made unusable while named location remains valid.
            config=f/'candidate/app/common/includes/daloradius.conf.php'
            config.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_HOST']='not-a-fixture-host';")
            for page in PAGES:assert '</html>' in req(page,DEFAULT.get(page),session=other)[1]
            assert 'Inserted new payment' in post(PAGES[0],valid,session=other)
            config.write_text(configs['candidate'])
            # SELECT-only account: reads pass, native writes fail without any business change.
            readonly='r'+secrets.token_hex(8)
            sql("CREATE USER '"+readonly+"'@'%';GRANT SELECT ON candidate.* TO '"+readonly+"'@'%'")
            config.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_USER']='"+readonly+"';")
            for page in PAGES:
                text=req(page,DEFAULT.get(page))[1];assert '</html>' in text and 'Unable to read payment data' not in text
            before=state();assert 'verify its state before retrying' in post(PAGES[0],valid) and state()==before
            config.write_text(configs['candidate'])
            print('PASS R17 selected-backend isolation and SELECT-only reads',flush=True)

            # Real read failures after count/options, and committed mutation followed by failure.
            provider=f/'candidate/app/operators/library/catalog_reads_pdo.php';original=provider.read_text()
            hook="""    $hook='/fixtures/fail-read.json';
    if (is_file($hook)) {
        $test=json_decode(file_get_contents($hook),true);
        if (strpos($sql,$test['match'])!==false) {unlink($hook);$pdo->exec($test['sql']);}
    }
"""
            provider.write_text(original.replace('    $statement = $pdo->prepare($sql);',hook+'    $statement = $pdo->prepare($sql);'))
            for p,match,table,column in [(PAGES[0],'SELECT id, value','custom_types','value'),(PAGES[1],'SELECT dp.id','custom_payments','notes'),(PAGES[2],'SELECT id FROM','custom_payments','id'),(PAGES[3],' LIMIT ','custom_payments','notes')]:
                (f/'fail-read.json').write_text(json.dumps({'match':match,'sql':f'ALTER TABLE {table} RENAME COLUMN {column} TO hidden_column'}))
                status,text,_=req(p,DEFAULT.get(p));assert not (f/'fail-read.json').exists()
                assert status==200 and '</html>' in text and 'Unable to read payment data' in text and 'SQLSTATE' not in text
                db(f'ALTER TABLE {table} RENAME COLUMN hidden_column TO {column}')
            token=next(x['csrf_token'] for x in Forms(req(PAGES[1],{'payment_id':'30'})[1]).forms if 'csrf_token' in x)
            (f/'fail-read.json').write_text(json.dumps({'match':'SELECT dp.id','sql':'ALTER TABLE custom_payments RENAME COLUMN notes TO hidden_column'}))
            text=req(PAGES[1],data={'payment_id':'30','payment_amount':'7.77','csrf_token':token})[1]
            assert 'Successfully updated payment' in text and 'Unable to read payment data' in text
            db('ALTER TABLE custom_payments RENAME COLUMN hidden_column TO notes');assert db('SELECT amount FROM custom_payments WHERE id=30')=='7.77'
            provider.write_text(original)
            # Fail-fast forbidden PEAR handles on every migrated page, including mutations.
            for legacy_file in ('db_open.php','db_close.php'):(f/'candidate/app/common/includes'/legacy_file).write_text("<?php throw new RuntimeException('Legacy payment handle reached');")
            for p in PAGES:assert '</html>' in req(p,DEFAULT.get(p))[1]
            assert 'Inserted new payment' in post(PAGES[0],valid)
            assert 'Successfully updated payment' in post(PAGES[1],{'payment_id':'30','payment_amount':'7.77'},{'payment_id':'30'})
            assert 'Deleted 1 payment(s)' in post(PAGES[2],{'payment_id':['81']})
            assert db('SELECT COUNT(*) FROM custom_payments WHERE id=81')=='0'
            print('PASS R17 late reads, committed writes and legacy tripwires',flush=True)
            log_result=subprocess.run(['docker','logs',h.WEB+'-candidate'],capture_output=True,text=True);logs=log_result.stdout+log_result.stderr
            for marker in ('PHP Fatal','PHP Warning','PHP Notice','Uncaught','SQLSTATE'):
                assert marker not in logs,('Candidate log gate',marker)
            print('PASS R17 candidate PHP log gate',flush=True)
        finally:
            for n in (h.WEB+'-base',h.WEB+'-candidate',h.DB):run('docker','rm','-f',n,check=False)
            run('docker','network','rm',h.NETWORK,check=False)
if __name__=='__main__':main()
