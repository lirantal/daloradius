#!/usr/bin/env python3
"""R19: native isolated HTTP/PHP/MariaDB catalogue reads against pinned PEAR.
No live configuration, data, credential snapshots or persistent services.
"""
import json,os,re,secrets,shutil,subprocess,tempfile,urllib.parse,urllib.request,urllib.error
from html.parser import HTMLParser
from pathlib import Path
import user_actions_http as h
from operator_reports_http import Rows
from acct_maintenance_http import Forms
ROOT=Path(__file__).resolve().parents[1]
BASE='7f06b3fcc03d2ab40a9240c49ffb5edd23bc307e'
PREFIX='pdo-r19-'+secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=[PREFIX+'-'+k for k in ('db','web','net')]
_original=h.run
def run(*a,**kw):
    try:return _original(*a,**kw)
    except RuntimeError:raise RuntimeError('Isolated fixture failure; details suppressed') from None
h.run=run
sql,wait_for=h.sql,h.wait_for
PAGES=['bill-rates-new.php', 'bill-rates-edit.php', 'bill-rates-del.php', 'bill-rates-list.php', 'bill-rates-date.php', 'bill-history-query.php', 'bill-merchant-transactions.php']
DEFAULT={'bill-rates-edit.php':{'ratename':'Rate0'},'bill-rates-date.php':{'ratename':'Rate0','username':'Alice','startdate':'2020-01-01','enddate':'2020-01-05'},'bill-merchant-transactions.php':{'startdate':'2020-01-01','enddate':'2020-01-05'}}
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
        tables={'CONFIG_DB_TBL_DALOBILLINGRATES': 'custom_rates', 'CONFIG_DB_TBL_DALOBILLINGHISTORY': 'custom_history', 'CONFIG_DB_TBL_DALOBILLINGMERCHANT': 'custom_merchant', 'CONFIG_DB_TBL_RADACCT': 'custom_acct', 'CONFIG_DB_TBL_DALOBILLINGPLANS': 'custom_plans'}
        for v in ('base','candidate'):
            shutil.copytree(ROOT/'app',f/v/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if v=='base':
                for p in PAGES:
                    raw=subprocess.check_output(['git','show',BASE+':app/operators/'+p],cwd=ROOT)
                    # Scalarize only baseline rendered selection after unchanged PEAR actions.
                    if p=='bill-rates-del.php':raw=raw.replace(b'    print_html_prologue($title, $langCode);',b"    if (is_array($ratename)) { $ratename = ''; }\n    print_html_prologue($title, $langCode);")
                    (f/v/'app/operators'/p).write_bytes(raw)
            if v=='base':
                for sidebar in ('app/operators/include/menu/sidebar/bill/rates.php','app/operators/include/menu/sidebar/bill/merchant.php','app/operators/include/management/userBilling.php'):
                    (f/v/sidebar).write_bytes(subprocess.check_output(['git','show',BASE+':'+sidebar],cwd=ROOT))
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
                db('RENAME TABLE billing_rates TO custom_rates,billing_history TO custom_history,billing_merchant TO custom_merchant,radacct TO custom_acct,billing_plans TO custom_plans',v)
                acl=','.join("(9001,'"+p[:-4].replace('-','_')+"',1),(9002,'"+p[:-4].replace('-','_')+"',0)" for p in PAGES)
                db('INSERT INTO operators_acl(operator_id,file,access) VALUES '+acl,v)
                for i in range(8):
                    db(f"INSERT INTO custom_rates(id,rateName,rateType,rateCost,creationdate,creationby) VALUES ({40+i},'Rate{i}','{1+i}/hour',{10+i},'2020-01-01','Fixture');INSERT INTO custom_history(id,username,planId,billAmount,billAction,billPerformer,paymentmethod) VALUES ({30+i},'User{i}',{i},'{i}.25','Refill','Fixture','cash');INSERT INTO custom_merchant(id,username,planName,planId,payment_date,payer_email,payment_total,payment_fee,payment_tax,payment_currency,vendor_type,payment_status) VALUES ({30+i},'User{i}','Plan',1,'2020-01-02','user{i}@example.invalid','{i}.25','0.10','0.05','EUR','PayPal','Completed')",v)
                db("INSERT INTO custom_plans(id,planName) VALUES(1,'Plan');INSERT INTO custom_rates(id,rateName,rateType,rateCost) VALUES(55,'0','1/second',1),(56,'77','1/minute',5);INSERT INTO custom_acct(radacctid,username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES(10,'Alice','a1','a1','192.0.2.1','2020-01-01',3600,100,200),(11,'Alice','a2','a2','192.0.2.2','2020-01-02',7200,200,300),(12,'Alice','a3','a3','192.0.2.3','2020-01-05',3600,50,50),(13,'Bob','b1','b1','192.0.2.4','2020-01-03',1800,30,50);",v)
                db("INSERT INTO custom_history(id,username,billAmount,billAction) VALUES(70,'0','0.25','Refill');INSERT INTO custom_merchant(id,username,planId,payment_date,payer_email,payment_total,payment_fee,payment_tax) VALUES(70,'0',1,'2020-01-05 23:59:59','0','0.25','0.01','0.00'),(71,'NextDay',1,'2020-01-06','next@example.invalid','1.25','0.05','0.10')",v)
                if v.endswith('_other'):
                    db("UPDATE custom_rates SET rateCost=99 WHERE id=40;UPDATE custom_history SET billPerformer='OtherOnly';UPDATE custom_merchant SET payment_total='99.75'",v)
            urls={};sid=secrets.token_hex(16);other=secrets.token_hex(16);denied=secrets.token_hex(16)
            for v in ('base','candidate'):
                web=h.WEB+'-'+v
                run('docker','run','-d','--name',web,'--network',h.NETWORK,'-v',str(f)+':/fixtures','-w','/fixtures/'+v+'/app/operators','--entrypoint','php',h.IMAGE,'-d','display_errors=0','-d','opcache.enable=0','-d','opcache.enable_cli=0','-S','0.0.0.0:8080','-t','.')
                ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',web);urls[v]='http://'+ip+':8080/'
                for session,loc,op in ((sid,'default',9001),(other,'other',9001),(denied,'default',9002)):run('docker','exec',web,'php','/fixtures/session.php',session,loc,str(op))
            def req(page,q=None,v='candidate',session=sid,data=None):
                if q is not None:q={(k if not isinstance(value,list) or k.endswith('[]') else k+'[]'):value for k,value in q.items()}
                if data is not None:data={(k if not isinstance(value,list) or k.endswith('[]') else k+'[]'):value for k,value in data.items()}
                request=urllib.request.Request(urls[v]+page+('?' +urllib.parse.urlencode(q,doseq=True) if q else ''),data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),headers={'Cookie':'daloradius_operator_sid='+session} if session else {})
                try:
                    with urllib.request.urlopen(request,timeout=30) as r:return r.status,r.read().decode(),r.url
                except urllib.error.HTTPError as e:return e.code,e.read().decode(),e.url
            wait_for(lambda:req(PAGES[3]),'PHP HTTP')

            # Characterize untouched legacy rate/date page before fixing only its duplicate include in the copied fixture.
            status,text,_=req(PAGES[4],DEFAULT[PAGES[4]],'base')
            assert status==200 and '</html>' not in text
            logs=subprocess.run(['docker','logs',h.WEB+'-base'],capture_output=True,text=True)
            assert 'Cannot redeclare function printLinks()' in logs.stdout+logs.stderr
            old=f/'base/app/operators/bill-rates-date.php'
            old.write_text(old.read_text().replace("include 'include/management/pages_numbering.php';", "include_once 'include/management/pages_numbering.php';").replace("include('include/management/pages_numbering.php');", "include_once('include/management/pages_numbering.php');"))
            def compare(page,q=None,session=sid,ordered=True):
                nonlocal comparisons
                data=[]
                for v in ('base','candidate'):
                    status,text,_=req(page,q if q is not None else DEFAULT.get(page),v,session)
                    assert status==200 and '</html>' in text and 'Unable to read billing rate data' not in text,(v,page,'complete page')
                    controls=[c for c in Controls(text).controls if c[1] not in ('creationdate','creationby','updatedate','updateby')]
                    # Baseline list has undefined checkbox value and nonexistent action routes.
                    if page==PAGES[3]:controls=[c for c in controls if c[1]!='ratename[]']
                    if page==PAGES[6]:controls=[c for c in controls if c[1] not in ('vendor_type','payer_email','payment_status')]
                    projection=Rows(text).projection()
                    if not ordered:projection=(sorted(projection[0]),projection[1])
                    data.append((projection,controls))
                assert data[0]==data[1],(page,'row/control parity mismatch',q,data)
                comparisons+=1
            for p in PAGES:compare(p)
            # Follow actual sidebar deletion producer; it must never route to plan deletion.
            text=req(PAGES[3])[1];links=Rows(text).links
            assert 'bill-rates-del.php' in links and 'bill-plans-del.php' not in links
            assert 'bill-plans-del.php' in Rows(req(PAGES[3],v='base')[1]).links
            q=dict(DEFAULT[PAGES[6]],payer_email='user1',vendor_type='PayPal',payment_status='Completed')
            controls=Controls(req(PAGES[6],q)[1]).controls
            assert ('input','payer_email','email','user1',False) in controls
            for name,value in [('vendor_type','PayPal'),('payment_status','Completed')]:
                assert any(c[0]=='select' and c[1]==name and any(o[0]==value and o[1] for o in c[2]) for c in controls),(name,[c for c in controls if c[0]=='select' and c[1]==name])
            base_controls=Controls(req(PAGES[6],q,v='base')[1]).controls
            assert ('input','payer_email','email','user1',False) not in base_controls
            for v in ('base','candidate'):(f/v/'app/common/includes/daloradius.conf.php').write_text(configs[v]+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='100';")
            # All offered non-secret columns, both directions; ties preserve rank sequence and row multiset.
            import collections
            def sorted_compare(page,q,index):
                nonlocal comparisons
                sets=[];ranks=[];foot=[]
                for v in ('base','candidate'):
                    status,text,_=req(page,q,v);assert status==200 and '</html>' in text and 'Unable to read billing rate data' not in text,(v,page,q['orderBy'],status,'complete sorted page')
                    projection=Rows(text).projection();rows=[r for r in projection[0] if len(r)==len(q['sqlfields'])]
                    sets.append(collections.Counter(tuple(r) for r in rows));ranks.append([r[index] for r in rows]);foot.append(projection[1])
                assert sets[0]==sets[1] and ranks[0]==ranks[1] and foot[0]==foot[1],(page,'sort ranks/membership',q['orderBy'])
                comparisons+=1
            fields={PAGES[5]:['id','username','planId','billAmount','billAction','billPerformer','billReason','paymentmethod','cash','coupon','discount','notes','creationdate','creationby','updatedate','updateby'],PAGES[6]:['id','username','txnId','planName','planId','quantity','business_email','business_id','payment_tax','payment_cost','payment_fee','payment_total','payment_currency','first_name','last_name','payer_email','payer_address_name','payer_address_street','payer_address_country','payer_address_country_code','payer_address_city','payer_address_state','payer_address_zip','payment_date','payment_status','payer_status','payment_address_status']}
            for page,columns in fields.items():
                for column in columns:
                    for direction in ('asc','desc'):
                        q=dict(DEFAULT.get(page,{}),sqlfields=columns,orderBy=column,orderType=direction)
                        sorted_compare(page,q,columns.index(column))
            for column in ('id','ratename','ratetype','ratecost'):
                for direction in ('asc','desc'):compare(PAGES[3],{'orderBy':column,'orderType':direction})
            for column in ('username','nasipaddress','acctstarttime','acctsessiontime'):
                for direction in ('asc','desc'):compare(PAGES[4],dict(DEFAULT[PAGES[4]],orderBy=column,orderType=direction))
            for user in ('User1','User','Absent'):
                compare(PAGES[5],{'username':user,'sqlfields':['id','username','billAmount'],'billaction':'Refill'})
            for email in ('user1','user','absent'):
                compare(PAGES[6],dict(DEFAULT[PAGES[6]],payer_email=email,vendor_type='PayPal',payment_status='Completed'))
            for dates in [('2020-01-01','2020-01-02'),('2020-01-05','2020-01-05'),('2020-01-06','2020-01-01'),('2020-01-01','2020-01-06')]:
                compare(PAGES[6],{'startdate':dates[0],'enddate':dates[1],'sqlfields':['id','username','payment_date','payment_total']})
                compare(PAGES[4],dict(DEFAULT[PAGES[4]],startdate=dates[0],enddate=dates[1]))
            # Each unit is interpreted with the original month coefficient, not silently corrected.
            for unit in ('second','minute','hour','day','week','month'):
                for v in ('base','candidate'):db("UPDATE custom_rates SET rateType='2/"+unit+"' WHERE id=40",v)
                compare(PAGES[4])
            for v in ('base','candidate'):db("UPDATE custom_rates SET rateType='1/hour' WHERE id=40",v)
            for page,q in [(PAGES[1],{'ratename':'Missing'}),(PAGES[4],dict(DEFAULT[PAGES[4]],ratename='Missing'))]:compare(page,q)
            for v in ('base','candidate'):(f/v/'app/common/includes/daloradius.conf.php').write_text(configs[v])
            for page in (PAGES[3],PAGES[5],PAGES[6]):
                for n in range(1,7):compare(page,dict(DEFAULT.get(page,{}),page=str(n)))
            for p in PAGES:compare(p,session=other)
            # Named routing must expose different data, not merely return 200 with identical seeds.
            assert '99' in req(PAGES[1],DEFAULT[PAGES[1]],session=other)[1]
            assert 'OtherOnly' in req(PAGES[5],{'sqlfields':['id','billPerformer']},session=other)[1]
            assert '99.75' in req(PAGES[6],dict(DEFAULT[PAGES[6]],sqlfields=['id','payment_total']),session=other)[1]
            def state(v='candidate'):return db('SELECT id,rateName,rateType,rateCost,creationdate,creationby,updatedate,updateby FROM custom_rates ORDER BY id',v)
            def business(v='candidate'):return db('SELECT id,rateName,rateType,rateCost FROM custom_rates ORDER BY id',v)
            def unrelated():return tuple(db(q) for q in ('SELECT radacctid,username,acctstarttime,acctsessiontime FROM custom_acct ORDER BY radacctid','SELECT id,username,billAmount FROM custom_history ORDER BY id','SELECT id,username,payment_date,payment_total,payment_fee,payment_tax FROM custom_merchant ORDER BY id'))
            def post(page,data,q=None,v='candidate',session=sid):
                token=next(x['csrf_token'] for x in Forms(req(page,q,v,session)[1]).forms if 'csrf_token' in x)
                return req(page,q,v,session,dict(data,csrf_token=token))[1]
            values={'ratecost':'12','ratetypenum':'2','ratetypetime':'hour'}
            untouched=unrelated()
            for name,cost,number in [('Created','12','2'),('Decimal','12.75','2.50'),("O'Reilly &é",'5','3')]:
                for v in ('base','candidate'):
                    text=post(PAGES[0],dict(values,ratename=name,ratecost=cost,ratetypenum=number),v=v)
                    assert 'Successfully inserted new rate' in text
                assert business('base')==business();comparisons+=1
            for data in [values,dict(values,ratecost='7.75'),dict(values,ratecost='',ratetypetime=''),dict(values,ratecost='0',ratetypenum='0'),values]:
                for v in ('base','candidate'):assert 'Successfully updated rate' in post(PAGES[1],dict(data,ratename='Rate0'),DEFAULT[PAGES[1]],v)
                assert business('base')==business();comparisons+=1
            for v in ('base','candidate'):assert 'Deleted' in post(PAGES[2],{'ratename':['Rate6','Rate7']},v=v)
            assert business('base')==business();comparisons+=1
            # Legacy PEAR DELETE result cast does not report the number of deleted rows.
            db("INSERT INTO custom_rates(id,rateName,rateType,rateCost) VALUES(90,'DeleteA','1/hour',1),(91,'DeleteB','1/hour',1)")
            before=state()
            for selection in (['DeleteA','Missing'],['DeleteA',['bad']],[],['DeleteA']*501):
                text=post(PAGES[2],{'ratename':selection});assert 'Failed deleting rate(s)' in text and '</html>' in text and state()==before
            for case,data in enumerate([dict(values,ratename=['Bad']),dict(values,ratename=''),dict(values,ratename='Rate0'),dict(values,ratename='rate0'),dict(values,ratename='Bad',ratecost=['1']),dict(values,ratename='Bad',ratecost='12garbage'),dict(values,ratename='Bad',ratecost='2147483648'),dict(values,ratename='Bad',ratetypetime=['hour']),dict(values,ratename='x'*129)]):
                text=post(PAGES[0],data);assert 'Failed to insert rate' in text and '</html>' in text and state()==before,('bad create',case,'Failed to insert rate' in text,'</html>' in text,state()==before)
            for data in [dict(values,ratename=['Rate0']),dict(values,ratename='rate0'),dict(values,ratename='Rate0',ratetypenum=['bad']),dict(values,ratename='Rate0',ratecost='999999999999999999999')]:
                text=post(PAGES[1],data,DEFAULT[PAGES[1]]);assert 'Successfully updated rate' not in text and '</html>' in text and state()==before
            for page,data in [(PAGES[0],dict(values,ratename='Secure')),(PAGES[1],dict(values,ratename='Rate0')),(PAGES[2],{'ratename':['DeleteA']})]:
                for token in (None,'invalid',['bad']):
                    text=req(page,data=dict(data,**({} if token is None else {'csrf_token':token})))[1]
                    assert 'CSRF token error' in text and '</html>' in text and state()==before
                assert req(page,session=denied,data=data)[2].endswith('/home-error.php') and state()==before
                assert req(page,session='')[2].endswith('/login.php')
            for page in PAGES:
                assert req(page,session=denied)[2].endswith('/home-error.php')
                assert req(page,session='')[2].endswith('/login.php')
            for kind,page,data in [('INSERT',PAGES[0],dict(values,ratename='Failure')),('UPDATE',PAGES[1],dict(values,ratename='Rate0'))]:
                db(f"CREATE TRIGGER fail_rate BEFORE {kind} ON custom_rates FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture failure'")
                before=state();text=post(page,data,DEFAULT[PAGES[1]] if kind=='UPDATE' else None)
                assert 'before retrying' in text and 'SQLSTATE' not in text and state()==before
                db('DROP TRIGGER fail_rate')
            # Nontransactional recorder proves an earlier DELETE actually ran before a later rejection.
            db("CREATE TABLE visits(id INT) ENGINE=MyISAM;\nDELIMITER $$\nCREATE TRIGGER fail_rate_delete BEFORE DELETE ON custom_rates FOR EACH ROW BEGIN INSERT INTO visits VALUES(OLD.id); IF OLD.id=91 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture failure'; END IF; END$$\nDELIMITER ;\n")
            before=state();text=post(PAGES[2],{'ratename':['DeleteA','DeleteB']})
            assert 'Failed deleting rate(s)' in text and state()==before and db('SELECT id FROM visits ORDER BY id')=='90\n91'
            db('DROP TRIGGER fail_rate_delete;DROP TABLE visits')
            assert 'Deleted 2 rate(s)' in post(PAGES[2],{'ratename':['DeleteA','DeleteB','DeleteA']})
            # Ambiguous identities cannot create a third duplicate or silently update/delete all rows.
            for v in ('base','candidate'):db("INSERT INTO custom_rates(id,rateName,rateType,rateCost) VALUES(80,'Duplicate','1/hour',1),(81,'Duplicate','2/hour',2)",v)
            assert 'Successfully inserted new rate' in post(PAGES[0],dict(values,ratename='Duplicate'),v='base')
            assert db("SELECT COUNT(*) FROM custom_rates WHERE rateName='Duplicate'",'base')=='3'
            before=state()
            for page,data in [(PAGES[0],dict(values,ratename='Duplicate')),(PAGES[1],dict(values,ratename='Duplicate')),(PAGES[2],{'ratename':['Rate1','Duplicate']})]:
                assert 'Successfully' not in post(page,data,DEFAULT[PAGES[1]] if page==PAGES[1] else None) and state()==before
            db('DELETE FROM custom_rates WHERE id IN (80,81)')
            # Raw identities and actual produced links/checkbox values, including zero and literal percent.
            special="Rate % + O'Reilly &é"
            for name in ('0',special):
                if name!='0':assert 'Successfully inserted new rate' in post(PAGES[0],dict(values,ratename=name))
                text=req(PAGES[1],{'ratename':name})[1]
                assert ('input','ratename','text',name,True) in Controls(text).controls
                assert 'Successfully updated rate' in post(PAGES[1],dict(values,ratename=name),{'ratename':name})
                assert any(c[0]=='select' and any(o[0]==name and o[1] for o in c[2]) for c in Controls(req(PAGES[2],{'ratename':name})[1]).controls)
            text=post(PAGES[0],dict(values,ratename='A & B'))
            link=next(x for x in Rows(text).links if x.startswith('bill-rates-edit.php?') and urllib.parse.parse_qs(urllib.parse.urlsplit(x).query).get('ratename')==['A & B'])
            assert 'Successfully' not in req(link)[1] and any(c[1]=='ratename' and c[3]=='A & B' for c in Controls(req(link)[1]).controls if c[0]=='input')
            config=f/'candidate/app/common/includes/daloradius.conf.php'
            config.write_text(configs['candidate']+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='100';")
            text=req(PAGES[3])[1]
            assert any(c[1]=='ratename[]' and c[3]==special for c in Controls(text).controls if c[0]=='input')
            links=[x for x in Rows(text).links if x.startswith('bill-rates-edit.php?')]
            assert special in [urllib.parse.parse_qs(urllib.parse.urlsplit(x).query)['ratename'][0] for x in links]
            assert not any('mng-rad-rates-' in x for x in Rows(text).links)
            config.write_text(configs['candidate'])
            for page,key in [(PAGES[4],'ratename'),(PAGES[4],'startdate'),(PAGES[5],'username'),(PAGES[5],'sqlfields'),(PAGES[6],'payer_email'),(PAGES[6],'startdate')]:
                text=req(page,dict(DEFAULT.get(page,{}),**{key:['bad']}))[1]
                assert '</html>' in text and ('Unable to read billing rate data' in text or 'Invalid billing report input' in text)
            # Invalid scalar dates retain the defaults without notices; exact valid boundaries were compared above.
            for page in (PAGES[4],PAGES[6]):
                text=req(page,dict(DEFAULT[page],startdate='invalid',enddate='2020-02-31'))[1]
                assert '</html>' in text and 'Unable to read billing rate data' not in text
            # Preserve payment decimals/NULL as returned; do not write transaction/accounting/history rows.
            assert unrelated()==untouched
            db("INSERT INTO custom_history(id,username,billAmount) VALUES(100,'zero%_name','1.25');INSERT INTO custom_merchant(id,username,planId,payer_email,payment_date,payment_total) VALUES(100,'Special',1,'zero%_name','2020-01-05','1.25')")
            for page,key in [(PAGES[5],'username'),(PAGES[6],'payer_email')]:
                q=dict(DEFAULT.get(page,{}),sqlfields=['id',key],**{key:'zero%_name'})
                assert ['100','zero%_name'] in Rows(req(page,q)[1]).rows
                q[key]='0';assert any('0' in row for row in Rows(req(page,q)[1]).rows)
                # Generated sort links preserve raw filter + field selection.
                q[key]='zero%_name';text=req(page,q)[1]
                link=next(x for x in Rows(text).links if urllib.parse.parse_qs(urllib.parse.urlsplit(x).query).get(key)==['zero%_name'] and 'orderBy=' in x)
                assert ['100','zero%_name'] in Rows(req(page+link if link.startswith('?') else link)[1]).rows
            # Stored invalid divisor fails closed before the retained summary can divide by zero.
            db("UPDATE custom_rates SET rateType='0/hour' WHERE id=40")
            text=req(PAGES[4],DEFAULT[PAGES[4]])[1];assert 'invalid stored type' in text and '</html>' in text
            db("UPDATE custom_rates SET rateType='1/hour' WHERE id=40")
            db('ALTER TABLE custom_rates ENGINE=MyISAM');before=state()
            for page,data in [(PAGES[0],dict(values,ratename='Engine')),(PAGES[1],dict(values,ratename='Rate0')),(PAGES[2],{'ratename':['Rate1']})]:
                assert 'Successfully' not in post(page,data,DEFAULT[PAGES[1]] if page==PAGES[1] else None) and state()==before
            db('ALTER TABLE custom_rates ENGINE=InnoDB')
            # Permissive narrow physical columns cannot commit silently truncated data.
            db('ALTER TABLE custom_rates MODIFY rateName VARCHAR(32) NOT NULL');before=state()
            assert 'Failed to insert rate' in post(PAGES[0],dict(values,ratename='x'*40)) and state()==before
            db('ALTER TABLE custom_rates MODIFY rateName VARCHAR(128) NOT NULL')
            db('ALTER TABLE custom_rates MODIFY rateCost TINYINT NOT NULL');before=state()
            assert 'Failed to insert rate' in post(PAGES[0],dict(values,ratename='CostOverflow',ratecost='200')) and state()==before
            db('ALTER TABLE custom_rates MODIFY rateCost INT NOT NULL')
            before=state();assert 'Successfully inserted new rate' in post(PAGES[0],dict(values,ratename='NamedOnly'),session=other) and state()==before
            config.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_HOST']='not-a-fixture-host';")
            for page in PAGES:
                text=req(page,DEFAULT.get(page),session=other)[1];assert '</html>' in text and 'Unable to read billing rate data' not in text
            assert 'Successfully inserted new rate' in post(PAGES[0],dict(values,ratename='NamedBrokenDefault'),session=other)
            config.write_text(configs['candidate'])
            readonly='r'+secrets.token_hex(8);sql("CREATE USER '"+readonly+"'@'%';GRANT SELECT ON candidate.* TO '"+readonly+"'@'%'")
            config.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_USER']='"+readonly+"';")
            for page in PAGES:
                text=req(page,DEFAULT.get(page))[1];assert '</html>' in text and 'Unable to read billing rate data' not in text
            before=state();assert 'Failed to insert rate' in post(PAGES[0],dict(values,ratename='DeniedWrite')) and state()==before
            config.write_text(configs['candidate'])
            before=state()
            config.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_TBL_DALOBILLINGRATES']='custom_rates;DROP TABLE custom_history';")
            assert 'Failed to insert rate' in post(PAGES[0],dict(values,ratename='UnsafeTable')) and state()==before
            config.write_text(configs['candidate'])
            for page in PAGES[3:]:
                for key in ('orderBy','orderType'):
                    text=req(page,dict(DEFAULT.get(page,{}),**{key:['invalid']}))[1]
                    assert '</html>' in text and 'Unable to read billing rate data' not in text
            for page in (PAGES[5],PAGES[6]):
                text=req(page,dict(DEFAULT.get(page,{}),sqlfields=['id','username;DROP TABLE custom_rates']))[1]
                assert 'Unable to read billing rate data' in text and '</html>' in text and state()==before
            text=req(PAGES[4],dict(DEFAULT[PAGES[4]],ratename='0',username=''))[1]
            assert '</html>' in text and 'Rate name is required' not in text and Rows(text).rows
            # Restore exact state and verify inherited schema without changing repository DDL.
            print('PASS R19 PEAR/PDO comparisons',comparisons,flush=True)
            print('PASS R19 CRUD, dates/decimals, raw identities, security, native rollback and selected backends',flush=True)
            # Borrowed reads and mutation ownership: real caller transaction must remain untouched.
            (f/'worker.php').write_text("""<?php
chdir('/fixtures/candidate/app/operators');require '../common/includes/daloradius.conf.php';require 'library/billing_rates_pdo.php';
$_SESSION=['location_name'=>'default'];
try {
 $job=json_decode(stream_get_contents(STDIN),true);$pdo=dalo_catalog_read_open($configValues);
 if($job['kind']==='owner'){
  $pdo->beginTransaction();$pdo->exec("INSERT INTO custom_rates(id,rateName,rateType,rateCost) VALUES(999,'Owner','1/hour',1)");
  dalo_rate_read($pdo,$configValues,'Owner');
  try{dalo_rate_mutate($pdo,$configValues,'del','Owner',[],'Fixture');throw new Exception('Ownership rejection missing');}
  catch(RuntimeException $e){if(!$pdo->inTransaction()){throw new Exception('Caller transaction lost');}}
  $pdo->rollBack();echo 'OWNER_OK';exit;
 }
 if($job['kind']==='lock'){
  $lock='dalo-rate:'.substr(hash('sha256',$pdo->query('SELECT DATABASE()')->fetchColumn().'\\0'.$configValues['CONFIG_DB_TBL_DALOBILLINGRATES']),0,40);
  if(strlen($lock)>64){throw new Exception('Long lock name');}
  $q=$pdo->prepare('SELECT GET_LOCK(?,10)');$q->execute([$lock]);if((int)$q->fetchColumn()!==1){throw new Exception('Lock failed');}$q->closeCursor();
  file_put_contents('/fixtures/locked','1');$deadline=microtime(true)+10;
  while(!is_file('/fixtures/release')&&microtime(true)<$deadline){usleep(10000);}
  if(!is_file('/fixtures/release')){throw new Exception('Rendezvous timeout');}
  $pdo->prepare('SELECT RELEASE_LOCK(?)')->execute([$lock]);echo 'RELEASED';exit;
 }
 echo 'OK:'.dalo_rate_mutate($pdo,$configValues,$job['mode'],$job['name'],$job['fields']??[],'Fixture');
}catch(Throwable $e){if(isset($pdo)&&$pdo->inTransaction()){$pdo->rollBack();}echo 'REJECTED:'.get_class($e);}
""")
            def worker(job):
                child=subprocess.Popen(['docker','exec','-i',h.WEB+'-candidate','php','/fixtures/worker.php'],stdin=subprocess.PIPE,stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
                child.stdin.write(json.dumps(job));child.stdin.close();return child
            def result(child):
                child.wait(timeout=20);out=child.stdout.read();err=child.stderr.read();assert child.returncode==0 and not err,'Worker PHP log gate';return out
            def wait_advisory(count):
                def check():
                    if int(db("SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE STATE='User lock'"))<count:raise RuntimeError('Awaiting native advisory waits')
                    return True
                return wait_for(check,'Observed blocked workers')
            def clear():
                for name in ('locked','release','pause-write'):
                    if (f/name).exists():(f/name).unlink()
            assert result(worker({'kind':'owner'}))=='OWNER_OK' and db('SELECT COUNT(*) FROM custom_rates WHERE id=999')=='0'
            holder=worker({'kind':'lock'});wait_for(lambda:(f/'locked').read_text(),'Advisory lock acquired')
            jobs=[worker({'kind':'rate','mode':'new','name':'RaceDuplicate','fields':values}) for _ in range(2)]
            wait_advisory(2);(f/'release').write_text('1');assert result(holder)=='RELEASED'
            results=[result(c) for c in jobs]
            assert sum(x.startswith('OK:') for x in results)==1 and sum(x=='REJECTED:DomainException' for x in results)==1
            assert db("SELECT COUNT(*) FROM custom_rates WHERE rateName='RaceDuplicate'")=='1';clear()
            holder=worker({'kind':'lock'});wait_for(lambda:(f/'locked').read_text(),'Advisory lock acquired')
            jobs=[worker({'kind':'rate','mode':'del','name':'RaceDuplicate'}) for _ in range(2)]
            wait_advisory(2);(f/'release').write_text('1');assert result(holder)=='RELEASED'
            results=[result(c) for c in jobs];assert sorted(results)==['OK:1','REJECTED:DomainException']
            assert db("SELECT COUNT(*) FROM custom_rates WHERE rateName='RaceDuplicate'")=='0';clear()
            rate_provider=f/'candidate/app/operators/library/billing_rates_pdo.php';rate_original=rate_provider.read_text()
            pause="""        if (is_file('/fixtures/pause-write')) {
            file_put_contents('/fixtures/locked','1');$deadline=microtime(true)+10;
            while(!is_file('/fixtures/release')&&microtime(true)<$deadline){usleep(10000);}
            if(!is_file('/fixtures/release')){throw new RuntimeException('Fixture rendezvous timeout');}
        }
"""
            rate_provider.write_text(rate_original.replace('        if (!$pdo->commit())',pause+'        if (!$pdo->commit())'))
            for first,second,name in [('edit','del','RaceEditFirst'),('del','edit','RaceDeleteFirst')]:
                db("INSERT INTO custom_rates(rateName,rateType,rateCost) VALUES('"+name+"','1/hour',1)")
                (f/'pause-write').write_text('1');a=worker({'kind':'rate','mode':first,'name':name,'fields':values})
                wait_for(lambda:(f/'locked').read_text(),'First mutation before commit')
                b=worker({'kind':'rate','mode':second,'name':name,'fields':values});wait_advisory(1)
                (f/'release').write_text('1');ra,rb=result(a),result(b)
                assert ra.startswith('OK:') and (rb=='OK:1' if second=='del' else rb=='REJECTED:DomainException')
                assert db("SELECT COUNT(*) FROM custom_rates WHERE rateName='"+name+"'")=='0';clear()
            rate_provider.write_text(rate_original)
            print('PASS R19 caller ownership and four observed native concurrency scenarios',flush=True)
            # Force actual late reads after successful count/commit; no hooks enter production source.
            provider=f/'candidate/app/operators/library/catalog_reads_pdo.php';original=provider.read_text()
            hook="""    $hook='/fixtures/fail-read.json';
    if (is_file($hook) && !$pdo->inTransaction()) {
        $test=json_decode(file_get_contents($hook),true);
        if(strpos($sql,$test['match'])!==false){unlink($hook);$pdo->exec($test['sql']);}
    }
"""
            provider.write_text(original.replace('    $statement = $pdo->prepare($sql);',hook+'    $statement = $pdo->prepare($sql);'))
            for page,match,table,column in [(PAGES[1],'SELECT id,rateName','custom_rates','rateCost'),(PAGES[2],'SELECT DISTINCT(rateName)','custom_rates','rateName'),(PAGES[3],' LIMIT :offset','custom_rates','rateType'),(PAGES[4],' LIMIT :offset','custom_acct','acctsessiontime'),(PAGES[5],' LIMIT :offset','custom_history','billAmount'),(PAGES[6],' LIMIT :offset','custom_merchant','payment_total')]:
                (f/'fail-read.json').write_text(json.dumps({'match':match,'sql':f'ALTER TABLE {table} RENAME COLUMN {column} TO hidden_column'}))
                status,text,_=req(page,DEFAULT.get(page));assert not (f/'fail-read.json').exists()
                assert status==200 and '</html>' in text and 'Unable to read billing rate data' in text and 'SQLSTATE' not in text
                # No partially rendered result table after an already successful COUNT.
                assert not any('Showing' in x for x in Rows(text).footer)
                db(f'ALTER TABLE {table} RENAME COLUMN hidden_column TO {column}')
            token=next(x['csrf_token'] for x in Forms(req(PAGES[1],DEFAULT[PAGES[1]])[1]).forms if 'csrf_token' in x)
            (f/'fail-read.json').write_text(json.dumps({'match':'SELECT id,rateName','sql':'ALTER TABLE custom_rates RENAME COLUMN rateCost TO hidden_column'}))
            text=req(PAGES[1],data=dict(values,ratename='Rate0',ratecost='27',csrf_token=token))[1]
            assert 'Successfully updated rate' in text and 'Unable to read billing rate data' in text
            db('ALTER TABLE custom_rates RENAME COLUMN hidden_column TO rateCost');assert db('SELECT rateCost FROM custom_rates WHERE id=40')=='27'
            db("INSERT INTO custom_rates(rateName,rateType,rateCost) VALUES('CommittedDelete','1/hour',1)")
            token=next(x['csrf_token'] for x in Forms(req(PAGES[2])[1]).forms if 'csrf_token' in x)
            (f/'fail-read.json').write_text(json.dumps({'match':'SELECT DISTINCT(rateName)','sql':'ALTER TABLE custom_rates RENAME COLUMN rateName TO hidden_column'}))
            text=req(PAGES[2],data={'ratename':['CommittedDelete'],'csrf_token':token})[1]
            assert 'Deleted 1 rate(s)' in text and 'Unable to read billing rate data' in text
            db('ALTER TABLE custom_rates RENAME COLUMN hidden_column TO rateName');assert db("SELECT COUNT(*) FROM custom_rates WHERE rateName='CommittedDelete'")=='0'
            provider.write_text(original)
            for legacy in ('db_open.php','db_close.php'):(f/'candidate/app/common/includes'/legacy).write_text("<?php throw new RuntimeException('Legacy rate handle reached');")
            for page in PAGES[:4]+[PAGES[5]]:
                text=req(page,DEFAULT.get(page))[1];assert '</html>' in text and 'Unable to read billing rate data' not in text
            assert 'Successfully inserted new rate' in post(PAGES[0],dict(values,ratename='Tripwire'))
            assert 'Successfully updated rate' in post(PAGES[1],dict(values,ratename='Tripwire'),{'ratename':'Tripwire'})
            assert 'Deleted 1 rate(s)' in post(PAGES[2],{'ratename':['Tripwire']})
            # R20 closes the independent summaries too: all report calls must remain PDO-only.
            for page in PAGES[4:5]+PAGES[6:]:
                text=req(page,DEFAULT[page])[1]
                assert '</html>' in text and 'Unable to read billing rate data' not in text and 'Unable to load user summary' not in text and Rows(text).rows
            print('PASS R19 six late read routes, committed-write/display distinction and complete PEAR tripwires',flush=True)
            logs=subprocess.run(['docker','logs',h.WEB+'-candidate'],capture_output=True,text=True);text=logs.stdout+logs.stderr
            for marker in ('PHP Fatal','PHP Warning','PHP Notice','Uncaught','SQLSTATE'):assert marker not in text,('Candidate log gate',marker)
            print('PASS R19 candidate PHP log gate',flush=True)
        finally:
            for v in ('base','candidate'):run('docker','rm','-f',h.WEB+'-'+v,check=False)
            run('docker','rm','-f',h.DB,check=False);run('docker','network','rm',h.NETWORK,check=False)
if __name__=='__main__':main()
