#!/usr/bin/env python3
"""R15: native isolated HTTP/PHP/MariaDB invoice reads against pinned PEAR.
No live configuration, data, credential snapshots or persistent services.
"""
import csv,io,json,os,re,secrets,shutil,subprocess,tempfile,urllib.parse,urllib.request,urllib.error
from html.parser import HTMLParser
from pathlib import Path
import user_actions_http as h
from operator_reports_http import Rows
from acct_maintenance_http import Forms
ROOT=Path(__file__).resolve().parents[1]
BASE='63a7a88eb6f5d5a66c34e793e47df73528c832c5'
PREFIX='pdo-r15-'+secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=[PREFIX+'-'+k for k in ('db','web','net')]
_original=h.run
def run(*a,**kw):
    try:return _original(*a,**kw)
    except RuntimeError:raise RuntimeError('Isolated fixture failure; details suppressed') from None
h.run=run
sql,wait_for=h.sql,h.wait_for
PAGES=['bill-invoice-'+s+'.php' for s in ('list','report','new','edit','del')]
DEFAULT={'bill-invoice-report.php':{'startdate':'2020-01-01','enddate':'2020-12-31'},'bill-invoice-new.php':{'user_id':'10'},'bill-invoice-edit.php':{'invoice_id':'50'}}
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
        tables={'CONFIG_DB_TBL_DALOBILLINGINVOICE':'custom_invoice','CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS':'custom_items','CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS':'custom_status','CONFIG_DB_TBL_DALOBILLINGINVOICETYPE':'custom_type','CONFIG_DB_TBL_DALOBILLINGPLANS':'custom_plans','CONFIG_DB_TBL_DALOPAYMENTS':'custom_payment','CONFIG_DB_TBL_DALOUSERINFO':'custom_info','CONFIG_DB_TBL_DALOUSERBILLINFO':'custom_bill'}
        for v in ('base','candidate'):
            shutil.copytree(ROOT/'app',f/v/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if v=='base':
                for p in PAGES:(f/v/'app/operators'/p).write_bytes(subprocess.check_output(['git','show',BASE+':app/operators/'+p],cwd=ROOT))
                p='app/operators/library/report_export_batch.php';(f/v/p).write_bytes(subprocess.check_output(['git','show',BASE+':'+p],cwd=ROOT))
            conf=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for k,value in dict(tables,CONFIG_DB_HOST=h.DB,CONFIG_DB_USER='root',CONFIG_DB_PASS='',CONFIG_DB_NAME=v,CONFIG_IFACE_TABLES_LISTING='2',CONFIG_IFACE_DEBUG='0',CONFIG_MAIL_ENABLED='no').items():conf+='\n$configValues['+repr(k)+']='+repr(value)+';\n'
            conf+="\n$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>'"+h.DB+"','Username'=>'root','Password'=>'','Database'=>'"+v+"_other','Port'=>'3306');\n"
            configs[v]=conf;(f/v/'app/common/includes/daloradius.conf.php').write_text(conf)
        (f/'session.php').write_text("<?php session_name('daloradius_operator_sid');session_id($argv[1]);session_start();$_SESSION=['daloradius_logged_in'=>true,'operator_id'=>(int)($argv[3]??9001),'operator_user'=>bin2hex(random_bytes(12)),'location_name'=>($argv[2]??'default'),'time'=>time()];session_write_close();")
        (f/'candidate/app/operators/descriptor.php').write_text("<?php include 'library/checklogin.php';echo json_encode($_SESSION['reportExport']??null);")
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
                for n in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):db((ROOT/'contrib/db'/n).read_text(),v)
                db('RENAME TABLE invoice TO custom_invoice,invoice_items TO custom_items,invoice_status TO custom_status,invoice_type TO custom_type,billing_plans TO custom_plans,payment TO custom_payment,userinfo TO custom_info,userbillinfo TO custom_bill',v)
                acl=','.join("(9001,'"+p[:-4].replace('-','_')+"',1),(9002,'"+p[:-4].replace('-','_')+"',0)" for p in PAGES)
                db('INSERT INTO operators_acl(operator_id,file,access) VALUES '+acl,v)
                db("INSERT INTO custom_info(id,username) VALUES (10,'Alice'),(11,'Bob'),(12,'NoBill'),(13,'0');INSERT INTO custom_bill(id,username,contactperson,city,state) VALUES (10,'Alice','Alice Customer','Town','Region'),(11,'Bob','Bob Customer','City','State'),(13,'0','Zero Customer','','');INSERT INTO custom_plans(id,planName,planActive) VALUES (41,'First plan','yes'),(42,'Other plan','yes'),(43,'Inactive','no');",v)
                for i in range(9):
                    name='OtherOnly' if v.endswith('_other') else 'Invoice '+str(i)
                    db("INSERT INTO custom_invoice(id,user_id,date,status_id,type_id,notes) VALUES ("+str(50+i)+","+str(10+i%2)+",'2020-01-"+str(i+2).zfill(2)+"',"+str(1+i%4)+",1,'"+name+"');INSERT INTO custom_items(invoice_id,plan_id,amount,tax_amount,notes) VALUES ("+str(50+i)+",42,"+str(2+i)+",0.25,'Item "+str(i)+"'),("+str(50+i)+",41,1.50,0.50,'Second item');INSERT INTO custom_payment(invoice_id,amount,date,notes) VALUES ("+str(50+i)+","+str(i)+",'2020-01-02','First payment'),("+str(50+i)+",0.25,'2020-01-02','Second payment')",v)
                db("INSERT INTO custom_invoice(id,user_id,date,status_id,type_id,notes) VALUES (70,13,'2020-02-01',1,1,'Zero'),(71,10,'2020-03-01',1,1,'Empty'),(72,999,'2020-03-01',1,1,'Orphan'),(73,10,'2020-03-01',999,1,'No status')",v)
            urls={};sid=secrets.token_hex(16);other=secrets.token_hex(16);denied=secrets.token_hex(16)
            for v in ('base','candidate'):
                web=h.WEB+'-'+v
                run('docker','run','-d','--name',web,'--network',h.NETWORK,'-v',str(f)+':/fixtures','-w','/fixtures/'+v+'/app/operators','--entrypoint','php',h.IMAGE,'-d','display_errors=0','-d','opcache.enable=0','-d','opcache.enable_cli=0','-S','0.0.0.0:8080','-t','.')
                ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',web);urls[v]='http://'+ip+':8080/'
                for session,loc,op in ((sid,'default',9001),(other,'other',9001),(denied,'default',9002)):run('docker','exec',web,'php','/fixtures/session.php',session,loc,str(op))
            def req(page,q=None,v='candidate',session=sid,raw=False,data=None):
                request=urllib.request.Request(urls[v]+page+('?' +urllib.parse.urlencode(q,doseq=True) if q else ''),data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),headers={'Cookie':'daloradius_operator_sid='+session} if session else {})
                try:
                    with urllib.request.urlopen(request,timeout=30) as r:data=r.read();r.headers['X-Fixture-Final-URL']=r.url;return r.status,data if raw else data.decode(),r.headers
                except urllib.error.HTTPError as e:
                    data=e.read();return e.code,data if raw else data.decode(),e.headers
            wait_for(lambda:req(PAGES[0]),'PHP HTTP')
            def compare(page,q=None,session=sid,sort=False):
                nonlocal comparisons
                data=[]
                for v in ('base','candidate'):
                    status,text,_=req(page,q or DEFAULT.get(page),v,session);assert status==200 and 'Unable to read invoice data' not in text,(page,'parity request')
                    parser=Rows(text);Controls.new=page=='bill-invoice-new.php';controls=Controls(text).controls
                    # Random legacy default item names/row IDs do not carry identity.
                    projection=parser.projection()
                    if sort:
                        index={'contactperson':2,'status_id':7}[q['orderBy']]
                        keys=[row[index] for row in projection[0]]
                        if q['orderBy']=='contactperson':
                            names={'Alice':'Alice Customer','Bob':'Bob Customer','0':'Zero Customer'};ordered=[names[x.removesuffix(' Edit User')] for x in keys];assert ordered==sorted(ordered,reverse=q['orderType']=='desc')
                        else:
                            ids={'open':1,'disputed':2,'draft':3,'sent':4,'paid':5,'partial':6};ordered=[ids[x] for x in keys];assert ordered==sorted(ordered,reverse=q['orderType']=='desc')
                        projection=(sorted(projection[0]),projection[1])
                    data.append((projection,controls))
                assert data[0]==data[1],(page,'complete row/control mismatch',q,data)
                comparisons+=1
            for p in PAGES:compare(p)
            for v in ('base','candidate'):
                (f/v/'app/common/includes/daloradius.conf.php').write_text(configs[v]+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='100';")
            for p in PAGES[:2]:
                for sort in ('id','contactperson','date','totalbilled','totalpayed','status_id'):
                    for direction in ('asc','desc'):
                        q=dict(DEFAULT.get(p,{}),orderBy=sort,orderType=direction,**{'per-page':'100'})
                        compare(p,q,sort=sort in ('contactperson','status_id'))
            for v in ('base','candidate'):(f/v/'app/common/includes/daloradius.conf.php').write_text(configs[v])
            for p in PAGES[:2]:
                for page in (1,2,3,4):compare(p,dict(DEFAULT.get(p,{}),page=str(page)))
            for q in ({'username':'Alice'},{'username':'Bob'},{'invoice_status_id':'1'},{'user_id':'10'},{'username':'Alice','invoice_status_id':'2'}):compare(PAGES[0],q)
            for q in ({'username':'Alice'},{'invoice_status':'1'},{'startdate':'2020-01-04','enddate':'2020-01-07'},{'username':'Bob','invoice_status':'2'}):compare(PAGES[1],dict(DEFAULT[PAGES[1]],**q))
            for id in ('50','51','70','71','999','0'):compare('bill-invoice-edit.php',{'invoice_id':id})
            for id in ('10','11','13','999'):compare('bill-invoice-new.php',{'user_id':id})
            for invoice in ('72','73'):
                assert 'Unable to read invoice data' in req('bill-invoice-edit.php',{'invoice_id':invoice})[1]
            export='include/management/fileExport.php'
            for query in ({},{'username':'Alice'},{'invoice_status':'2'}):
                data=[]
                for v in ('base','candidate'):
                    req(PAGES[1],dict(DEFAULT[PAGES[1]],**query),v)
                    status,text,_=req(export,{'reportFormat':'csv','reportType':'reportsInvoiceList'},v)
                    assert status==200;data.append(list(csv.reader(io.StringIO(text))))
                assert data[0]==data[1];comparisons+=1
            for p in PAGES:compare(p,DEFAULT.get(p),other)
            assert 'OtherOnly' in req('bill-invoice-edit.php',{'invoice_id':'50'},session=other)[1]
            # Missing explicit username was historically an unfiltered list; characterize and repair.
            assert Rows(req(PAGES[0],{'username':'Absent'},'base')[1]).rows
            assert not Rows(req(PAGES[0],{'username':'Absent'})[1]).rows
            assert len(Rows(req(PAGES[0],{'username':'0'})[1]).rows)==1
            req(PAGES[1],dict(DEFAULT[PAGES[1]],username='0'))
            assert len(Rows(req(PAGES[1],dict(DEFAULT[PAGES[1]],username='0'))[1]).rows)==1
            assert json.loads(req('descriptor.php')[1])['filters']['username']=='0'
            zero_csv=list(csv.reader(io.StringIO(req(export,{'reportFormat':'csv','reportType':'reportsInvoiceList'})[1])));assert len(zero_csv)==2 and 'Zero Customer' in str(zero_csv)
            for p in PAGES:
                assert req(p,DEFAULT.get(p),session=denied)[2]['X-Fixture-Final-URL'].endswith('/home-error.php')
            for p in PAGES[:2]:
                for field in ('username','orderType','orderBy','invoice_status_id'):
                    status,text,_=req(p,dict(DEFAULT.get(p,{}),**{field+'[]':'bad'}));assert 'Unable to read invoice data' in text and 'SQLSTATE' not in text
            for p in PAGES[:2]:
                assert not Rows(req(p,dict(DEFAULT.get(p,{}),username="' OR 1=1 --"))[1]).rows
            for p in PAGES[:2]:
                query=dict(DEFAULT.get(p,{}),username='Alice')
                text=req(p,query)[1]
                link=next(link for link in Rows(text).links if 'orderBy=date' in link)
                q=urllib.parse.parse_qs(urllib.parse.urlsplit(link).query);assert q['username']==['Alice']
                assert Rows(req(p,{k:v[0] for k,v in q.items()})[1]).rows
            # All reads leave complete noncredential invoice relationships unchanged.
            def state():return tuple(db('SELECT * FROM '+table+' ORDER BY id') for table in ('custom_invoice','custom_items','custom_payment'))
            before=state()
            for p in PAGES:req(p,DEFAULT.get(p))
            assert state()==before
            for p in PAGES:
                assert req(p,DEFAULT.get(p),session='')[2]['X-Fixture-Final-URL'].endswith('/login.php')
            # Missing mandatory bill/status joins fail closed, without rendering a phantom edit.
            assert 'name="invoice_id" type="hidden"' not in req('bill-invoice-edit.php',{'invoice_id':'72'})[1]
            assert 'Unable to read invoice data' in req('bill-invoice-new.php',{'user_id':'12'})[1]
            # Empty/NULL aggregate and label contracts on equivalent native schemas.
            for v in ('base','candidate'):
                db("UPDATE custom_invoice SET notes=NULL WHERE id=71;UPDATE custom_bill SET contactperson=NULL WHERE id=10;UPDATE custom_items SET amount=NULL,tax_amount=NULL,notes=NULL WHERE invoice_id=50",v)
            for p in ('bill-invoice-list.php','bill-invoice-report.php','bill-invoice-edit.php'):compare(p,DEFAULT.get(p))
            for v in ('base','candidate'):
                db("UPDATE custom_bill SET contactperson='Alice Customer' WHERE id=10;UPDATE custom_items SET amount=2.00,tax_amount=0.25,notes='restored' WHERE invoice_id=50",v)
            # Raw special identities survive the real customer and filtered sort links.
            special="O'Reilly % + &é"
            db("INSERT INTO custom_info(id,username) VALUES (20,'O''Reilly % + &é');INSERT INTO custom_bill(id,username,contactperson) VALUES (20,'O''Reilly % + &é','Special');INSERT INTO custom_invoice(id,user_id,date,status_id,type_id,notes) VALUES (80,20,'2020-01-02',1,1,'Special')")
            for p,q in [('bill-invoice-new.php',{'user_id':'20'}),('bill-invoice-edit.php',{'invoice_id':'80'}),(PAGES[0],{'username':special}),(PAGES[1],dict(DEFAULT[PAGES[1]],username=special))]:
                text=req(p,q)[1];customer=next(link for link in Rows(text).links if 'bill-pos-edit.php?username=' in link)
                assert urllib.parse.parse_qs(urllib.parse.urlsplit(customer).query)['username']==[special]
                if p in PAGES[:2]:
                    link=next(link for link in Rows(text).links if 'orderBy=date' in link)
                    parsed=urllib.parse.parse_qs(urllib.parse.urlsplit(link).query);assert parsed['username']==[special]
                    assert len(Rows(req(p,{k:v[0] for k,v in parsed.items()})[1]).rows)==1
            db("UPDATE custom_plans SET planName='Quote''s + & plan' WHERE id=41")
            for p,q in [('bill-invoice-new.php',{'user_id':'20'}),('bill-invoice-edit.php',{'invoice_id':'80'})]:
                text=req(p,q)[1];match=re.search(r'plansSelect = (.+),',text);assert match
                select=json.loads(match.group(1));assert 'Quote' in select and 'itemXXXXXXX[plan]' in select
            db("DELETE FROM custom_invoice WHERE id=80;DELETE FROM custom_bill WHERE id=20;DELETE FROM custom_info WHERE id=20;UPDATE custom_plans SET planName='First plan' WHERE id=41")
            # Preserve real PDF actions outside the R15 migration boundary (R20 remains PEAR).
            for action in ('preview','download'):
                for v in ('base','candidate'):
                    status,pdf,headers=req('include/common/notifications.php',{'type':'user-invoice','action':action,'invoice_id':'50'},v,raw=True)
                    assert status==200 and pdf.startswith(b'%PDF-') and 'application/pdf' in headers.get('Content-Type',''),('PDF',action,status,pdf[:70])
            # Real late SELECT failure in each form/list family, fixture-only synchronization hook.
            provider=f/'candidate/app/operators/library/invoice_reads_pdo.php';original=provider.read_text()
            hook="""    $hook='/fixtures/fail-read.json';
    if (is_file($hook)) {
        $test=json_decode(file_get_contents($hook),true);
        if (strpos($sql,$test['match'])!==false) {unlink($hook);$pdo->exec($test['sql']);}
    }
"""
            provider.write_text(original.replace('    $statement = $pdo->prepare($sql);',hook+'    $statement = $pdo->prepare($sql);'))
            for p,match,table,column in [(PAGES[0],' LIMIT ','custom_bill','contactperson'),(PAGES[1],' LIMIT ','custom_bill','contactperson'),('bill-invoice-edit.php','WHERE a.invoice_id=','custom_items','tax_amount'),('bill-invoice-new.php','SELECT contactperson,','custom_bill','contactperson'),('bill-invoice-del.php','SELECT id FROM','custom_invoice','id')]:
                (f/'fail-read.json').write_text(json.dumps({'match':match,'sql':f'ALTER TABLE {table} RENAME COLUMN {column} TO hidden_column'}))
                status,text,_=req(p,DEFAULT.get(p));assert not (f/'fail-read.json').exists(),(p,'hook reached')
                assert status==200 and 'Unable to read invoice data' in text and 'SQLSTATE' not in text and 'fileExport.php' not in text,(p,'late SQL envelope')
                assert json.loads(req('descriptor.php')[1]) is None
                db(f'ALTER TABLE {table} RENAME COLUMN hidden_column TO {column}')
            # A display failure AFTER a committed create is not a mutation rollback.
            form=Forms(req('bill-invoice-new.php',{'user_id':'10'})[1]);token=next(x['csrf_token'] for x in form.forms if 'csrf_token' in x)
            before_count=int(db('SELECT COUNT(*) FROM custom_invoice'))
            (f/'fail-read.json').write_text(json.dumps({'match':'SELECT contactperson,','sql':'ALTER TABLE custom_bill RENAME COLUMN contactperson TO hidden_column'}))
            text=req('bill-invoice-new.php',data={'csrf_token':token,'user_id':'10','invoice_type_id':'1','invoice_status_id':'1','invoice_date':'2020-01-02','invoice_notes':'Committed display error'})[1]
            assert 'Successfully added new invoice' in text and 'Unable to read invoice data' in text
            assert int(db('SELECT COUNT(*) FROM custom_invoice'))==before_count+1
            db('ALTER TABLE custom_bill RENAME COLUMN hidden_column TO contactperson');db("DELETE FROM custom_invoice WHERE notes='Committed display error'")
            # And a successful delete stays successful when its remaining-options read fails.
            db("INSERT INTO custom_invoice(id,user_id,date,status_id,type_id,notes) VALUES (81,10,'2020-01-02',1,1,'Temporary delete')")
            form=Forms(req('bill-invoice-del.php')[1]);token=next(x['csrf_token'] for x in form.forms if 'csrf_token' in x)
            (f/'fail-read.json').write_text(json.dumps({'match':'SELECT id FROM','sql':'ALTER TABLE custom_invoice RENAME COLUMN id TO hidden_column'}))
            text=req('bill-invoice-del.php',data={'csrf_token':token,'invoice_id[]':['81']})[1]
            assert 'Deleted 1 invoice id(s)' in text and 'Unable to read invoice data' in text
            db('ALTER TABLE custom_invoice RENAME COLUMN hidden_column TO id');assert db('SELECT COUNT(*) FROM custom_invoice WHERE id=81')=='0'
            provider.write_text(original)
            # Stale descriptors are never exported from nonexporting/empty pages.
            for p in ('bill-invoice-list.php','bill-invoice-report.php'):
                req(PAGES[1],DEFAULT[PAGES[1]]);req(p,dict(DEFAULT.get(p,{}),username='Absent'))
                assert json.loads(req('descriptor.php')[1]) is None
            # Borrowed PDO owns neither a successful nor a failed transaction.
            (f/'candidate/app/operators/borrowed.php').write_text("<?php include '../common/includes/config_read.php';include 'library/checklogin.php';require 'library/invoice_reads_pdo.php';$pdo=dalo_invoice_read_open($configValues);$pdo->beginTransaction();$rows=dalo_invoice_read_rows($pdo,'SELECT 1');try{dalo_invoice_read_rows($pdo,'SELECT missing FROM missing_table');}catch(Throwable $e){}echo json_encode([$rows==[[1]],$pdo->inTransaction()]);$pdo->rollBack();")
            assert json.loads(req('borrowed.php')[1])==[True,True]
            # Throwing PEAR tripwires on all five pages, including remaining shared sidebar.
            for name in ('db_open.php','db_close.php'):(f/'candidate/app/common/includes'/name).write_text("<?php throw new RuntimeException('Legacy connection tripwire');")
            for p in PAGES:
                status,text,_=req(p,DEFAULT.get(p));assert status==200 and 'Unable to read invoice data' not in text
            account='r'+secrets.token_hex(10);factor=secrets.token_hex(24)
            sql("CREATE USER '"+account+"'@'%' IDENTIFIED BY '"+factor+"';GRANT SELECT ON candidate.* TO '"+account+"'@'%';GRANT SELECT ON candidate_other.* TO '"+account+"'@'%';")
            conf=f/'candidate/app/common/includes/daloradius.conf.php'
            readonly=configs['candidate']+"\n$configValues['CONFIG_DB_USER']='"+account+"';$configValues['CONFIG_DB_PASS']='"+factor+"';$configValues['CONFIG_LOCATIONS']['other']['Username']='"+account+"';$configValues['CONFIG_LOCATIONS']['other']['Password']='"+factor+"';"
            conf.write_text(readonly)
            for p in PAGES:
                status,text,_=req(p,DEFAULT.get(p));assert status==200 and 'Unable to read invoice data' not in text,(p,'SELECT-only native page')
            assert 'OtherOnly' in req('bill-invoice-edit.php',{'invoice_id':'50'},session=other)[1]
            conf.write_text(configs['candidate']);sql("DROP USER '"+account+"'@'%'");factor=None;account=None;readonly=None
            # Configurable identifiers rejected without SQL/error details.
            conf=f/'candidate/app/common/includes/daloradius.conf.php'
            conf.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE']='invalid-table';")
            for p in ('bill-invoice-list.php','bill-invoice-edit.php','bill-invoice-del.php'):
                assert 'Unable to read invoice data' in req(p,DEFAULT.get(p))[1]
            conf.write_text(configs['candidate'])
            for v in ('base','candidate'):db('DELETE FROM custom_payment;DELETE FROM custom_items;DELETE FROM custom_invoice',v)
            for p in ('bill-invoice-list.php','bill-invoice-report.php','bill-invoice-edit.php','bill-invoice-del.php'):compare(p,DEFAULT.get(p))
            logs=subprocess.run(['docker','logs',h.WEB+'-candidate'],capture_output=True,text=True)
            assert not any(k in logs.stdout+logs.stderr for k in ('PHP Warning:','PHP Fatal error:','PHP Deprecated:','SQLSTATE[')), 'Candidate PHP log error'
            print('PASS R15 complete PEAR/PDO comparisons',comparisons)
            print('PASS native PDF preview/download, CSV, configured/named reads, ACL, borrowed handles, late SQL failures, PEAR tripwires and empty datasets')
        finally:
            for n in (h.WEB+'-base',h.WEB+'-candidate',h.DB):run('docker','rm','-f','-v',n,check=False)
            run('docker','network','rm',h.NETWORK,check=False)
            run('docker','run','--rm','-v',str(f)+':/fixtures','--entrypoint','sh',h.IMAGE,'-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')
    assert not any(n.startswith(PREFIX) for n in run('docker','ps','-a','--format','{{.Names}}').splitlines())
    assert PREFIX not in run('docker','network','ls','--format','{{.Name}}')
    print('PASS fixture resources removed')
if __name__=='__main__':main()
