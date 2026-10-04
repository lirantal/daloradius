#!/usr/bin/env python3
"""R18: native isolated HTTP/PHP/MariaDB catalogue reads against pinned PEAR.
No live configuration, data, credential snapshots or persistent services.
"""
import json,os,re,secrets,shutil,subprocess,tempfile,urllib.parse,urllib.request,urllib.error
from html.parser import HTMLParser
from pathlib import Path
import user_actions_http as h
from operator_reports_http import Rows
from acct_maintenance_http import Forms
ROOT=Path(__file__).resolve().parents[1]
BASE='f4c7aa4a941fcaa4c77d2235bece06ede89b62db'
PREFIX='pdo-r18-'+secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=[PREFIX+'-'+k for k in ('db','web','net')]
_original=h.run
def run(*a,**kw):
    try:return _original(*a,**kw)
    except RuntimeError:raise RuntimeError('Isolated fixture failure; details suppressed') from None
h.run=run
sql,wait_for=h.sql,h.wait_for
PAGES=['bill-payment-types-new.php','bill-payment-types-edit.php','bill-payment-types-del.php','bill-payment-types-list.php']
DEFAULT={'bill-payment-types-edit.php':{'paymentname':'Type0'}}
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
                    if p=='bill-payment-types-del.php':raw=raw.replace(b'    print_html_prologue($title, $langCode);',b"    if (is_array($paymentname)) { $paymentname = ''; }\n    print_html_prologue($title, $langCode);")
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
                    db(f"INSERT INTO custom_types(id,value,notes,creationdate,creationby) VALUES ({40+i},'Type{i}','','2020-01-01','Fixture');INSERT INTO custom_payments(id,invoice_id,amount,date,type_id,notes) VALUES ({30+i},20,1.25,'2020-01-01',{1+i%3},'Fixture')",v)
                db("INSERT INTO custom_types(id,value,notes) VALUES (55,'0',''),(56,'77','')",v)
                if v.endswith('_other'):db("UPDATE custom_types SET notes='OtherOnly' WHERE id=41",v)
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
                    status,text,_=req(page,q or DEFAULT.get(page),v,session)
                    assert status==200 and '</html>' in text and 'Unable to read payment type data' not in text,(v,page,'complete page')
                    controls=[c for c in Controls(text).controls if c[1] not in ('creationdate','creationby','updatedate','updateby')]
                    # Legacy edit does not feed the stored notes into its textarea.
                    if page==PAGES[1]:controls=[c for c in controls if c[1]!='paymentnotes']
                    data.append((Rows(text).projection(),controls))
                assert data[0]==data[1],(page,'row/control parity mismatch',q)
                comparisons+=1
            for p in PAGES:compare(p)
            for v in ('base','candidate'):(f/v/'app/common/includes/daloradius.conf.php').write_text(configs[v]+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='100';")
            for column in ('id','paymentname','unsupported'):
                for direction in ('asc','desc'):compare(PAGES[3],{'orderBy':column,'orderType':direction})
            for name in ('Type0','Cash','Absent'):
                for page in (PAGES[1],PAGES[2]):compare(page,{'paymentname':name})
            for v in ('base','candidate'):(f/v/'app/common/includes/daloradius.conf.php').write_text(configs[v])
            for n in range(1,8):compare(PAGES[3],{'page':str(n)})
            for p in PAGES:compare(p,DEFAULT.get(p),other)
            def state(v='candidate'):
                return db('SELECT id,value,notes,creationdate,creationby,updatedate,updateby FROM custom_types ORDER BY id',v)
            def business(v='candidate'):return db('SELECT id,value,notes FROM custom_types ORDER BY id',v)
            def post(page,data,q=None,v='candidate',session=sid):
                token=next(x['csrf_token'] for x in Forms(req(page,q,v,session)[1]).forms if 'csrf_token' in x)
                return req(page,q,v,session,dict(data,csrf_token=token))[1]
            for name,notes in [('Created','note'),('Quoted',"O'Reilly &é")]:
                for v in ('base','candidate'):
                    text=post(PAGES[0],{'paymentname':name,'paymentnotes':notes},v=v);assert 'Successfully inserted new payment type' in text
                assert business('base')==business();comparisons+=1
            for notes in ('Edited','',"Quoted ' &é", "Quoted ' &é"):
                for v in ('base','candidate'):
                    text=post(PAGES[1],{'paymentname':'Type0','paymentnotes':notes},{'paymentname':'Type0'},v=v);assert 'Successfully updated payment type' in text
                assert business('base')==business();comparisons+=1
            for v in ('base','candidate'):assert 'Deleted ' in post(PAGES[2],{'paymentname':['77']},v=v)
            assert business('base')==business();comparisons+=1
            print('PASS R18 PEAR/PDO comparisons',comparisons,flush=True)
            # Explicit characterization: text names were coerced to zero, deleting the wrong type.
            baseline=post(PAGES[2],{'paymentname':['Type1','Type2']},v='base')
            assert db("SELECT COUNT(*) FROM custom_types WHERE value='0'",'base')=='0'
            assert db("SELECT COUNT(*) FROM custom_types WHERE value IN ('Type1','Type2')",'base')=='2'
            assert 'Deleted 2 payment type(s)' in post(PAGES[2],{'paymentname':['Type1','Type2']})
            assert db("SELECT COUNT(*) FROM custom_types WHERE value IN ('Type1','Type2')")=='0'
            assert db("SELECT COUNT(*) FROM custom_types WHERE value='0'")=='1'
            # Stored notes must survive an unchanged complete form submission.
            text=req(PAGES[1],{'paymentname':'Type0'})[1]
            assert ('textarea','paymentnotes',"Quoted ' &é") in Controls(text).controls
            assert ('textarea','paymentnotes',"Quoted ' &é") not in Controls(req(PAGES[1],{'paymentname':'Type0'},v='base')[1]).controls
            audit=db('SELECT creationdate,creationby FROM custom_types WHERE value="Type0"')
            assert 'Successfully updated payment type' in post(PAGES[1],{'paymentname':'Type0','paymentnotes':"Quoted ' &é"},{'paymentname':'Type0'})
            assert db('SELECT creationdate,creationby FROM custom_types WHERE value="Type0"')==audit
            special="O'Reilly % + &é"
            for name in ('0',special):
                if name!= '0':assert 'Successfully inserted new payment type' in post(PAGES[0],{'paymentname':name,'paymentnotes':'special'})
                text=req(PAGES[1],{'paymentname':name})[1];assert ('input','paymentname-presentation','text',name,True) in Controls(text).controls
                assert 'Successfully updated payment type' in post(PAGES[1],{'paymentname':name,'paymentnotes':'0'},{'paymentname':name})
                assert ('textarea','paymentnotes','0') in Controls(req(PAGES[1],{'paymentname':name})[1]).controls
                assert any(c[0]=='select' and any(o[0]==name and o[1] for o in c[2]) for c in Controls(req(PAGES[2],{'paymentname':name})[1]).controls)
            # Success and list links carry raw names, not URL-encoded HTML labels.
            text=post(PAGES[0],{'paymentname':'A & B','paymentnotes':'Link'})
            link=next(x for x in Rows(text).links if x.startswith('bill-payment-types-edit.php?') and urllib.parse.parse_qs(urllib.parse.urlsplit(x).query).get('paymentname')==['A & B'])
            assert 'paymentname-presentation' in req(link)[1]
            (f/'candidate/app/common/includes/daloradius.conf.php').write_text(configs['candidate']+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='100';")
            text=req(PAGES[3])[1]
            assert special in [urllib.parse.parse_qs(urllib.parse.urlsplit(x).query)['paymentname'][0] for x in Rows(text).links if x.startswith('bill-payment-types-edit.php?')]
            assert any(c[0]=='input' and c[1]=='paymentname[]' and c[3]==special for c in Controls(text).controls)
            (f/'candidate/app/common/includes/daloradius.conf.php').write_text(configs['candidate'])
            before=state();payment_before=db('SELECT * FROM custom_payments ORDER BY id')
            for name,notes in [('Type0',''),('type0',''),('x'*33,''),(['Bad'],''),('',''),('Bad','x'*129),('Bad',['array'])]:
                text=post(PAGES[0],{'paymentname':name,'paymentnotes':notes});assert 'Successfully inserted new payment type' not in text and state()==before
            for value in (['Type3','Absent'],['Type3',['bad']],[],['Type3']*501,['Type3','Cash']):
                text=post(PAGES[2],{'paymentname':value});assert 'Failed to delete payment types' in text and state()==before
            for data in ({'paymentname':'Type0','paymentnotes':['bad']},{'paymentname':['Type0']},{'paymentname':'type0'},{'paymentname':'Type0','paymentnotes':'x'*129}):
                text=post(PAGES[1],data,{'paymentname':'Type0'});assert 'Successfully updated payment type' not in text and state()==before
            for p,data in [(PAGES[0],{'paymentname':'Secure'}),(PAGES[1],{'paymentname':'Type0'}),(PAGES[2],{'paymentname':['Type3']})]:
                for token in (None,'invalid',['bad']):
                    text=req(p,data=dict(data,**({} if token is None else {'csrf_token':token})))[1];assert 'CSRF token error' in text and state()==before
                assert req(p,session=denied,data=data)[2].endswith('/home-error.php') and state()==before
                assert req(p,session='')[2].endswith('/login.php')
            # Ambiguous duplicate identities are rejected, not edited/deleted in bulk accidentally.
            for v in ('base','candidate'):db("INSERT INTO custom_types(id,value,notes) VALUES(70,'Duplicate','a'),(71,'Duplicate','b')",v)
            assert 'Successfully inserted new payment type' in post(PAGES[0],{'paymentname':'Duplicate'},v='base')
            assert db("SELECT COUNT(*) FROM custom_types WHERE value='Duplicate'",'base')=='3'
            before=state()
            for page,data in [(PAGES[0],{'paymentname':'Duplicate'}),(PAGES[1],{'paymentname':'Duplicate','paymentnotes':'changed'}),(PAGES[2],{'paymentname':['Type3','Duplicate']})]:
                text=post(page,data,{'paymentname':'Type0'} if page==PAGES[1] else None);assert state()==before and 'Successfully' not in text
            db('DELETE FROM custom_types WHERE id IN (70,71)')
            # Forced later deletion failure proves full physical rollback.
            db("INSERT INTO custom_types(id,value,notes) VALUES(80,'DeleteA','a'),(81,'DeleteB','b');\nDELIMITER $$\nCREATE TRIGGER fail_type_delete BEFORE DELETE ON custom_types FOR EACH ROW BEGIN IF OLD.id=81 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture failure'; END IF; END$$\nDELIMITER ;\n")
            before=state();assert 'Failed to delete payment types' in post(PAGES[2],{'paymentname':['DeleteA','DeleteB']}) and state()==before;db('DROP TRIGGER fail_type_delete')
            for mode,page,data in [('INSERT',PAGES[0],{'paymentname':'Failure'}),('UPDATE',PAGES[1],{'paymentname':'Type0','paymentnotes':'failure'})]:
                db(f"CREATE TRIGGER fail_type BEFORE {mode} ON custom_types FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture failure'")
                before=state();text=post(page,data,{'paymentname':'Type0'} if mode=='UPDATE' else None)
                assert 'verify its state before retrying' in text and 'SQLSTATE' not in text and state()==before;db('DROP TRIGGER fail_type')
            for table in ('custom_types','custom_payments'):
                db(f'ALTER TABLE {table} ENGINE=MyISAM');before=state()
                text=post(PAGES[2],{'paymentname':['DeleteA','DeleteB']});assert 'Failed to delete payment types' in text and state()==before
                db(f'ALTER TABLE {table} ENGINE=InnoDB')
            assert db('SELECT * FROM custom_payments ORDER BY id')==payment_before
            assert 'Deleted 2 payment type(s)' in post(PAGES[2],{'paymentname':['DeleteA','DeleteB','DeleteA']})
            # Configured physical capacity narrower than stock cannot silently truncate a value.
            db('ALTER TABLE custom_types MODIFY value VARCHAR(16) NOT NULL');before=state()
            assert 'Failed to insert payment type' in post(PAGES[0],{'paymentname':'x'*20}) and state()==before
            db('ALTER TABLE custom_types MODIFY value VARCHAR(32) NOT NULL')
            # Named mutation/default-unavailable and SELECT-only reads.
            before=state();assert 'Successfully inserted new payment type' in post(PAGES[0],{'paymentname':'NamedOnly'},session=other) and state()==before
            config=f/'candidate/app/common/includes/daloradius.conf.php'
            config.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_HOST']='not-a-fixture-host';")
            for page in PAGES:assert '</html>' in req(page,DEFAULT.get(page),session=other)[1]
            assert 'Successfully inserted new payment type' in post(PAGES[0],{'paymentname':'NamedBrokenDefault'},session=other)
            config.write_text(configs['candidate'])
            readonly='r'+secrets.token_hex(8);sql("CREATE USER '"+readonly+"'@'%';GRANT SELECT ON candidate.* TO '"+readonly+"'@'%'")
            config.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_USER']='"+readonly+"';")
            for page in PAGES:
                text=req(page,DEFAULT.get(page))[1];assert '</html>' in text and 'Unable to read payment type data' not in text
            before=state();assert 'Failed to insert payment type' in post(PAGES[0],{'paymentname':'DeniedWrite'}) and state()==before
            config.write_text(configs['candidate'])
            for key in ('orderBy','orderType'):assert 'Unable to read payment type data' in req(PAGES[3],{key+'[]':'bad'})[1]
            before=state()
            config.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES']='custom_types;DROP TABLE custom_payments';")
            assert 'Failed to insert payment type' in post(PAGES[0],{'paymentname':'UnsafeTable'}) and state()==before
            config.write_text(configs['candidate'])
            missing=secrets.token_hex(16);run('docker','exec',h.WEB+'-candidate','php','/fixtures/session.php',missing,'missing','9001')
            assert req(PAGES[3],session=missing)[2].endswith('/home-error.php') and state()==before
            print('PASS R18 CRUD, raw identities, references, security, rollback and backends',flush=True)
            # Real independent PHP workers coordinate with the existing R17 type-parent locks.
            (f/'worker.php').write_text("""<?php
chdir('/fixtures/candidate/app/operators');require '../common/includes/daloradius.conf.php';require 'library/payment_types_pdo.php';
$_SESSION=['location_name'=>'default'];
try {
    $job=json_decode(stream_get_contents(STDIN),true);$pdo=dalo_payment_open($configValues);
    if ($job['kind']==='owner') {
        $pdo->beginTransaction();$pdo->exec("INSERT INTO custom_types(id,value,notes) VALUES(96,'Owner','prior')");
        try { dalo_payment_type_mutate($pdo,$configValues,'del','Owner','','Fixture');throw new Exception('Expected ownership rejection'); }
        catch(RuntimeException $e) { if (!$pdo->inTransaction()) {throw new Exception('Caller transaction lost');} }
        $pdo->rollBack();echo 'OWNER_OK';exit;
    }
    if ($job['kind']==='lock') {
        $lock=dalo_payment_type_lock_name($pdo,$configValues);if(strlen($lock)>64){throw new Exception('Long lock name');}
        $q=$pdo->prepare('SELECT GET_LOCK(?,10)');$q->execute([$lock]);if((int)$q->fetchColumn()!==1){throw new Exception('Lock failed');}$q->closeCursor();
        file_put_contents('/fixtures/locked','1');$deadline=microtime(true)+10;
        while(!is_file('/fixtures/release')&&microtime(true)<$deadline){usleep(10000);}
        if(!is_file('/fixtures/release')){throw new Exception('Rendezvous timeout');}
        $pdo->prepare('SELECT RELEASE_LOCK(?)')->execute([$lock]);echo 'RELEASED';exit;
    }
    if ($job['kind']==='type') {echo 'OK:'.dalo_payment_type_mutate($pdo,$configValues,$job['mode'],$job['name'],$job['notes']??'','Fixture');}
    else {
        $ids=$job['mode']==='new'?[]:dalo_payment_ids($job['ids']);
        $values=dalo_payment_fields($job['fields'],$job['mode']==='new');
        echo 'OK:'.dalo_payment_mutate($pdo,$configValues,$job['mode'],$ids,$values,'Fixture');
    }
} catch(Throwable $e) {if(isset($pdo)&&$pdo->inTransaction()){$pdo->rollBack();}echo 'REJECTED:'.get_class($e);}
""")
            def worker(job):
                child=subprocess.Popen(['docker','exec','-i',h.WEB+'-candidate','php','/fixtures/worker.php'],stdin=subprocess.PIPE,stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
                child.stdin.write(json.dumps(job));child.stdin.close();return child
            def result(child):
                child.wait(timeout=20);out=child.stdout.read();err=child.stderr.read();assert child.returncode==0 and not err,'Worker PHP log gate';return out
            def blocked():
                if db('SELECT COUNT(*) FROM information_schema.INNODB_LOCK_WAITS')=='0':raise RuntimeError('Awaiting native lock wait')
                return True
            def clear():
                for name in ('locked','release','pause-write','pause-before-type'):
                    if (f/name).exists():(f/name).unlink()
            assert result(worker({'kind':'owner'}))=='OWNER_OK' and db('SELECT COUNT(*) FROM custom_types WHERE id=96')=='0'
            holder=worker({'kind':'lock'});wait_for(lambda:(f/'locked').read_text(),'Advisory lock acquired')
            jobs=[worker({'kind':'type','mode':'new','name':'RaceDuplicate'}) for _ in range(2)]
            def advisory_wait():
                if int(db("SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE STATE='User lock'"))<2:raise RuntimeError('Awaiting both creators')
                return True
            wait_for(advisory_wait,'Both type creators actually blocked');(f/'release').write_text('1');assert result(holder)=='RELEASED'
            results=[result(c) for c in jobs];assert sum(x.startswith('OK:') for x in results)==1 and sum(x=='REJECTED:DomainException' for x in results)==1
            assert db("SELECT COUNT(*) FROM custom_types WHERE value='RaceDuplicate'")=='1';clear()
            pause="""        if (is_file('/fixtures/pause-write')) {
            file_put_contents('/fixtures/locked','1');$deadline=microtime(true)+10;
            while(!is_file('/fixtures/release')&&microtime(true)<$deadline){usleep(10000);}
            if(!is_file('/fixtures/release')){throw new RuntimeException('Fixture rendezvous timeout');}
        }
"""
            type_provider=f/'candidate/app/operators/library/payment_types_pdo.php';type_original=type_provider.read_text()
            pay_provider=f/'candidate/app/operators/library/payments_pdo.php';pay_original=pay_provider.read_text()
            db("INSERT INTO custom_types(id,value,notes) VALUES(83,'RacePaymentFirst',''),(84,'RaceTypeFirst',''),(85,'RaceEdit','')")
            fields={'payment_invoice_id':'20','payment_amount':'3.30','payment_date':'2020-01-01','payment_type_id':'paymentType-83'}
            pay_provider.write_text(pay_original.replace('        if (!$pdo->commit())',pause+'        if (!$pdo->commit())'))
            (f/'pause-write').write_text('1')
            creator=worker({'kind':'payment','mode':'new','fields':fields});wait_for(lambda:(f/'locked').read_text(),'Payment before commit')
            delete=worker({'kind':'type','mode':'del','name':'RacePaymentFirst'});wait_for(blocked,'Type deletion waits on payment type lock')
            (f/'release').write_text('1');assert result(creator).startswith('OK:') and result(delete)=='REJECTED:DomainException'
            assert db('SELECT COUNT(*) FROM custom_payments WHERE type_id=83')=='1' and db('SELECT COUNT(*) FROM custom_types WHERE id=83')=='1';clear();pay_provider.write_text(pay_original)
            # Reverse: type deletion owns the parent; late payment creation rejects missing type.
            type_provider.write_text(type_original.replace('        if (!$pdo->commit())',pause+'        if (!$pdo->commit())'))
            (f/'pause-write').write_text('1');delete=worker({'kind':'type','mode':'del','name':'RaceTypeFirst'})
            wait_for(lambda:(f/'locked').read_text(),'Type deletion before commit')
            creator=worker({'kind':'payment','mode':'new','fields':dict(fields,payment_type_id='paymentType-84')})
            wait_for(blocked,'Payment creation waits on type deletion');(f/'release').write_text('1')
            assert result(delete)=='OK:1' and result(creator)=='REJECTED:DomainException'
            assert db('SELECT COUNT(*) FROM custom_types WHERE id=84')=='0' and db('SELECT COUNT(*) FROM custom_payments WHERE type_id=84')=='0';clear();type_provider.write_text(type_original)
            # A payment edit can own the child before acquiring its type parent.
            # Type deletion must detect its committed reference without locking that child.
            db("INSERT INTO custom_payments(id,invoice_id,amount,date,type_id,notes) VALUES(92,20,1,'2020-01-01',85,'prior')")
            before_type=pause.replace("'/fixtures/pause-write'","'/fixtures/pause-before-type'")
            pay_provider.write_text(pay_original.replace("        if (!empty($values['type_id']))",before_type+"        if (!empty($values['type_id']))"))
            (f/'pause-before-type').write_text('1');edit=worker({'kind':'payment','mode':'edit','ids':['92'],'fields':dict(fields,payment_type_id='paymentType-85')})
            wait_for(lambda:(f/'locked').read_text(),'Edit owns payment before type lock')
            deletion=result(worker({'kind':'type','mode':'del','name':'RaceEdit'}));assert deletion=='REJECTED:DomainException'
            (f/'release').write_text('1');assert result(edit)=='OK:92' and db('SELECT amount FROM custom_payments WHERE id=92')=='3.30'
            clear();pay_provider.write_text(pay_original)
            print('PASS R18 ownership, duplicate-creator contention and three R17 reference races',flush=True)
            # Actual late read faults after count/locked mutation; hooks exist only in copied fixtures.
            provider=f/'candidate/app/operators/library/catalog_reads_pdo.php';original=provider.read_text()
            hook="""    $hook='/fixtures/fail-read.json';
    if (is_file($hook)) {
        $test=json_decode(file_get_contents($hook),true);
        if (strpos($sql,$test['match'])!==false) {unlink($hook);$pdo->exec($test['sql']);}
    }
"""
            provider.write_text(original.replace('    $statement = $pdo->prepare($sql);',hook+'    $statement = $pdo->prepare($sql);'))
            for page,match,column in [(PAGES[1],'SELECT id,value,notes','notes'),(PAGES[2],'SELECT DISTINCT(value)','value'),(PAGES[3],' LIMIT ','notes')]:
                (f/'fail-read.json').write_text(json.dumps({'match':match,'sql':f'ALTER TABLE custom_types RENAME COLUMN {column} TO hidden_column'}))
                status,text,_=req(page,DEFAULT.get(page));assert not (f/'fail-read.json').exists()
                assert status==200 and '</html>' in text and 'Unable to read payment type data' in text and 'SQLSTATE' not in text
                db(f'ALTER TABLE custom_types RENAME COLUMN hidden_column TO {column}')
            token=next(x['csrf_token'] for x in Forms(req(PAGES[1],{'paymentname':'Type0'})[1]).forms if 'csrf_token' in x)
            (f/'fail-read.json').write_text(json.dumps({'match':'SELECT id,value,notes','sql':'ALTER TABLE custom_types RENAME COLUMN notes TO hidden_column'}))
            text=req(PAGES[1],data={'paymentname':'Type0','paymentnotes':'Committed','csrf_token':token})[1]
            assert 'Successfully updated payment type' in text and 'Unable to read payment type data' in text
            db('ALTER TABLE custom_types RENAME COLUMN hidden_column TO notes');assert db('SELECT notes FROM custom_types WHERE id=40')=='Committed'
            db("INSERT INTO custom_types(id,value,notes) VALUES(10000,'CommittedDelete','')")
            token=next(x['csrf_token'] for x in Forms(req(PAGES[2])[1]).forms if 'csrf_token' in x)
            (f/'fail-read.json').write_text(json.dumps({'match':'SELECT DISTINCT(value)','sql':'ALTER TABLE custom_types RENAME COLUMN value TO hidden_column'}))
            text=req(PAGES[2],data={'paymentname':['CommittedDelete'],'csrf_token':token})[1]
            assert 'Deleted 1 payment type(s)' in text and 'Unable to read payment type data' in text
            db('ALTER TABLE custom_types RENAME COLUMN hidden_column TO value');assert db('SELECT COUNT(*) FROM custom_types WHERE id=10000')=='0'
            provider.write_text(original)
            for legacy in ('db_open.php','db_close.php'):(f/'candidate/app/common/includes'/legacy).write_text("<?php throw new RuntimeException('Legacy type handle reached');")
            for page in PAGES:assert '</html>' in req(page,DEFAULT.get(page))[1]
            assert 'Successfully inserted new payment type' in post(PAGES[0],{'paymentname':'Tripwire'})
            assert 'Successfully updated payment type' in post(PAGES[1],{'paymentname':'Tripwire','paymentnotes':'changed'},{'paymentname':'Tripwire'})
            assert 'Deleted 1 payment type(s)' in post(PAGES[2],{'paymentname':['Tripwire']})
            print('PASS R18 late reads, committed edit and PEAR open/close tripwires',flush=True)
            logs=subprocess.run(['docker','logs',h.WEB+'-candidate'],capture_output=True,text=True);text=logs.stdout+logs.stderr
            for marker in ('PHP Fatal','PHP Warning','PHP Notice','Uncaught','SQLSTATE'):assert marker not in text,('Candidate log gate',marker)
            print('PASS R18 candidate PHP log gate',flush=True)
        finally:
            for n in (h.WEB+'-base',h.WEB+'-candidate',h.DB):run('docker','rm','-f',n,check=False)
            run('docker','network','rm',h.NETWORK,check=False)
if __name__=='__main__':main()
