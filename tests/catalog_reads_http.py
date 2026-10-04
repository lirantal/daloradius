#!/usr/bin/env python3
"""R16: native isolated HTTP/PHP/MariaDB catalogue reads against pinned PEAR.
No live configuration, data, credential snapshots or persistent services.
"""
import json,os,re,secrets,shutil,subprocess,tempfile,urllib.parse,urllib.request,urllib.error
from html.parser import HTMLParser
from pathlib import Path
import user_actions_http as h
from operator_reports_http import Rows
from acct_maintenance_http import Forms
ROOT=Path(__file__).resolve().parents[1]
BASE='2148769ec72bc42cfc4b179516d6f434b6a08639'
PREFIX='pdo-r16-'+secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=[PREFIX+'-'+k for k in ('db','web','net')]
_original=h.run
def run(*a,**kw):
    try:return _original(*a,**kw)
    except RuntimeError:raise RuntimeError('Isolated fixture failure; details suppressed') from None
h.run=run
sql,wait_for=h.sql,h.wait_for
PAGES=['bill-plans-del.php', 'bill-plans-edit.php', 'bill-plans-list.php', 'bill-pos-del.php', 'bill-pos-edit.php', 'bill-pos-list.php', 'mng-batch-add.php', 'mng-batch-del.php']
DEFAULT={'bill-plans-edit.php':{'planName':'Plan 0'},'bill-pos-edit.php':{'username':'User0'}}
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
        tables={'CONFIG_DB_TBL_DALOBILLINGPLANS':'custom_plans','CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES':'custom_profiles','CONFIG_DB_TBL_DALOUSERINFO':'custom_info','CONFIG_DB_TBL_DALOUSERBILLINFO':'custom_bill','CONFIG_DB_TBL_RADCHECK':'custom_check','CONFIG_DB_TBL_RADUSERGROUP':'custom_groups','CONFIG_DB_TBL_DALOBATCHHISTORY':'custom_batch','CONFIG_DB_TBL_DALOHOTSPOTS':'custom_hotspots'}
        for v in ('base','candidate'):
            shutil.copytree(ROOT/'app',f/v/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if v=='base':
                for p in PAGES:(f/v/'app/operators'/p).write_bytes(subprocess.check_output(['git','show',BASE+':app/operators/'+p],cwd=ROOT))
            conf=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for k,value in dict(tables,CONFIG_DB_HOST=h.DB,CONFIG_DB_USER='root',CONFIG_DB_PASS='',CONFIG_DB_NAME=v,CONFIG_IFACE_TABLES_LISTING='2',CONFIG_IFACE_PASSWORD_HIDDEN='yes',CONFIG_IFACE_DEBUG='0',CONFIG_MAIL_ENABLED='no').items():conf+='\n$configValues['+repr(k)+']='+repr(value)+';\n'
            conf+="\n$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>'"+h.DB+"','Username'=>'root','Password'=>'','Database'=>'"+v+"_other','Port'=>'3306');\n"
            # Config has real newlines; the source recipe never copies an ignored live config.
            conf=conf.replace('\n','\n')
            configs[v]=conf;(f/v/'app/common/includes/daloradius.conf.php').write_text(conf)
        (f/'session.php').write_text("<?php session_name('daloradius_operator_sid');session_id($argv[1]);session_start();$_SESSION=['daloradius_logged_in'=>true,'operator_id'=>(int)($argv[3]??9001),'operator_user'=>bin2hex(random_bytes(12)),'location_name'=>($argv[2]??'default'),'time'=>time()];session_write_close();")
        try:
            run('docker','network','create','--internal',h.NETWORK)
            run('docker','run','-d','--name',h.DB,'--network',h.NETWORK,'--tmpfs','/var/lib/mysql','-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            def db(q,v='candidate'):
                p=subprocess.run(['docker','exec','-i',h.DB,'mariadb','-uroot','-N','-B',v],input="SET SESSION sql_mode='';\n".replace('\n','\n')+q,text=True,capture_output=True)
                if p.returncode:raise RuntimeError('Fixture SQL error codes '+repr(re.findall(r'ERROR (\d+)',p.stderr)))
                return p.stdout.rstrip('\n')
            factor=secrets.token_hex(24)
            for v in ('base','candidate','base_other','candidate_other'):
                sql('CREATE DATABASE '+v)
                for n in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql','mariadb-daloradius-dictionaries.sql'):db((ROOT/'contrib/db'/n).read_text(),v)
                db('RENAME TABLE billing_plans TO custom_plans,billing_plans_profiles TO custom_profiles,userinfo TO custom_info,userbillinfo TO custom_bill,radcheck TO custom_check,radusergroup TO custom_groups,batch_history TO custom_batch,hotspots TO custom_hotspots',v)
                acl=','.join("(9001,'"+p[:-4].replace('-','_')+"',1),(9002,'"+p[:-4].replace('-','_')+"',0)" for p in PAGES)
                db('INSERT INTO operators_acl(operator_id,file,access) VALUES '+acl,v)
                for i in range(8):
                    name='Plan '+str(i);contact=('OtherOnly' if v.endswith('_other') else 'Contact ')+str(i)
                    db("INSERT INTO custom_plans(id,planName,planId,planType,planActive,planCost,planGroup,creationdate,creationby) VALUES ("+str(40+i)+",'"+name+"','P"+str(i)+"','"+('prepaid' if i%2 else 'postpaid')+"','"+('no' if i==7 else 'yes')+"','12.30','GroupA','2020-01-01','Fixture');INSERT INTO custom_info(id,username,firstname,lastname,creationdate,creationby) VALUES ("+str(10+i)+",'User"+str(i)+"','First "+str(i)+"','Last','2020-01-01','Fixture');INSERT INTO custom_bill(id,username,planName,contactperson,company,creationdate,creationby) VALUES ("+str(10+i)+",'User"+str(i)+"','"+name+"','"+contact+"','Company "+str(i)+"','2020-01-01','Fixture');INSERT INTO custom_check(username,attribute,op,value) VALUES ('User"+str(i)+"','Cleartext-Password',':=','"+factor+"');",v)
                db("INSERT INTO custom_profiles(plan_name,profile_name) VALUES ('Plan 0','GroupA'),('Plan 0','GroupB'),('Plan 0','GroupA');INSERT INTO radgroupcheck(groupname,attribute,op,value) VALUES ('GroupA','Auth-Type',':=','Accept');INSERT INTO radgroupreply(groupname,attribute,op,value) VALUES ('GroupB','Session-Timeout',':=','60');INSERT INTO custom_groups(username,groupname,priority) VALUES ('User0','GroupA',3),('User0','GroupB',6),('User0','GroupA',7),('User1','daloRADIUS-Disabled-Users',-1);INSERT INTO custom_batch(id,batch_name,batch_description) VALUES (60,'Batch A','First'),(61,'Batch B','Second');INSERT INTO custom_hotspots(id,name) VALUES (70,'Site A'),(71,'Site B');",v)
                db("INSERT INTO custom_check(username,attribute,op,value) VALUES ('NoInfo','Cleartext-Password',':=','"+factor+"'),('Optional','Auth-Type',':=','Accept');INSERT INTO custom_plans(id,planName,planActive) VALUES (80,'0','yes');INSERT INTO custom_info(id,username,firstname) VALUES (30,'0','Zero');INSERT INTO custom_bill(id,username,planName,contactperson) VALUES (30,'0','0','Zero Contact');INSERT INTO custom_check(username,attribute,op,value) VALUES ('0','Cleartext-Password',':=','"+factor+"');",v)
            urls={};sid=secrets.token_hex(16);other=secrets.token_hex(16);denied=secrets.token_hex(16)
            for v in ('base','candidate'):
                web=h.WEB+'-'+v
                run('docker','run','-d','--name',web,'--network',h.NETWORK,'-v',str(f)+':/fixtures','-w','/fixtures/'+v+'/app/operators','--entrypoint','php',h.IMAGE,'-d','display_errors=0','-d','opcache.enable=0','-d','opcache.enable_cli=0','-S','0.0.0.0:8080','-t','.')
                ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',web);urls[v]='http://'+ip+':8080/'
                for session,loc,op in ((sid,'default',9001),(other,'other',9001),(denied,'default',9002)):run('docker','exec',web,'php','/fixtures/session.php',session,loc,str(op))
            def req(page,q=None,v='candidate',session=sid,data=None):
                request=urllib.request.Request(urls[v]+page+('?' +urllib.parse.urlencode(q,doseq=True) if q else ''),data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),headers={'Cookie':'daloradius_operator_sid='+session} if session else {})
                try:
                    with urllib.request.urlopen(request,timeout=30) as r:return r.status,r.read().decode(),r.url
                except urllib.error.HTTPError as e:return e.code,e.read().decode(),e.url
            wait_for(lambda:req(PAGES[2]),'PHP HTTP')
            def compare(page,q=None,session=sid,sort=False):
                nonlocal comparisons
                data=[]
                for v in ('base','candidate'):
                    status,text,_=req(page,q or DEFAULT.get(page),v,session);assert status==200 and 'Unable to read billing catalogue data' not in text,(page,'parity request')
                    parser=Rows(text);controls=Controls(text).controls
                    if page=='bill-pos-edit.php' and (q or DEFAULT.get(page))=={'username':'User0'} and any(c[1]=='username_presentation' for c in controls):
                        assert any(c[0]=='input' and c[1]=='password' and c[3]==factor and c[4] for c in controls), 'Disabled authentication display contract'
                    # Authentication factors never become stored snapshots or assertion output.
                    controls=[c for c in controls if c[1] not in ('password','portalLoginPassword')]
                    data.append((parser.projection(),controls))
                assert data[0]==data[1],(page,'complete noncredential row/control mismatch',q)
                comparisons+=1
            for p in PAGES:compare(p)
            print('PASS R16 initial eight page comparisons',flush=True)
            for v in ('base','candidate'):(f/v/'app/common/includes/daloradius.conf.php').write_text(configs[v]+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='100';".replace('\n','\n'))
            for p,keys in [('bill-plans-list.php',['id','planName','planType','planActive']),('bill-pos-list.php',['id','contactperson','company','username','planname'])]:
                for sort in keys:
                    for direction in ('asc','desc'):
                        compare(p,{'orderBy':sort,'orderType':direction,'per-page':'100'})
            for v in ('base','candidate'):(f/v/'app/common/includes/daloradius.conf.php').write_text(configs[v])
            for p in ('bill-plans-list.php','bill-pos-list.php'):
                for page in (1,2,3,4,5):compare(p,{'page':str(page)})
            for name in ('Plan 0','Plan 1','Plan 7','Absent'):
                compare('bill-plans-edit.php',{'planName':name});compare('bill-plans-del.php',{'planName':name})
            for name in ('User0','User1','Absent','Optional'):
                compare('bill-pos-edit.php',{'username':name});compare('bill-pos-del.php',{'username':name})
            for name in ('Plan 0','Plan','Absent',"' OR 1=1 --"):
                compare('bill-pos-list.php',{'planname':name})
            for p in PAGES:compare(p,DEFAULT.get(p),other)
            assert 'OtherOnly' in req('bill-pos-edit.php',{'username':'User0'},session=other)[1]
            # Original batch handler keeps shared read-only selectors; hotspot belongs to R16.
            for p in PAGES:
                assert req(p,DEFAULT.get(p),session=denied)[2].endswith('/home-error.php')
                assert req(p,DEFAULT.get(p),session='')[2].endswith('/login.php')
            for p in ('bill-plans-list.php','bill-pos-list.php'):
                for field in ('orderBy','orderType','planname'):
                    assert 'Unable to read billing catalogue data' in req(p,{field+'[]':'bad'})[1]
            assert 'Unable to read billing catalogue data' in req('bill-plans-edit.php',{'planName[]':'bad'})[1]
            assert 'Unable to read billing catalogue data' in req('bill-pos-edit.php',{'username[]':'bad'})[1]
            # Read-only state includes relationships, but never any factor-bearing columns.
            def state(v='candidate'):
                return tuple(db(q,v) for q in ['SELECT id,planName,planType,planActive,planCost FROM custom_plans ORDER BY id','SELECT id,plan_name,profile_name FROM custom_profiles ORDER BY id','SELECT id,username,contactperson,planName,batch_id FROM custom_bill ORDER BY id','SELECT id,username,firstname,lastname FROM custom_info ORDER BY id','SELECT id,username,groupname,priority FROM custom_groups ORDER BY id','SELECT id,batch_name,batch_description FROM custom_batch ORDER BY id'])
            before=state()
            for p in PAGES:req(p,DEFAULT.get(p))
            assert state()==before
            assert 'name="planId"' not in req('bill-plans-edit.php',{'planName':'0'},v='base')[1]
            assert 'name="planId"' in req('bill-plans-edit.php',{'planName':'0'})[1]
            assert 'name="username_presentation"' not in req('bill-pos-edit.php',{'username':'0'},v='base')[1]
            assert 'name="username_presentation"' in req('bill-pos-edit.php',{'username':'0'})[1]
            # Zero filters and sort/page links retain the exact raw plan identity.
            assert len(Rows(req('bill-pos-list.php',{'planname':'0'})[1]).rows)==2
            for filter_value in ('Plan','0'):
                links=Rows(req('bill-pos-list.php',{'planname':filter_value})[1]).links
                actionable=[x for x in links if x.startswith('?') and ('orderBy=' in x or 'page=' in x)]
                assert actionable
                for link in actionable:
                    parsed=urllib.parse.parse_qs(urllib.parse.urlsplit(link).query)
                    assert parsed.get('planname')==[filter_value], 'Sort/top/bottom pagination filter identity'
                if filter_value=='Plan':
                    page_link=next(x for x in actionable if 'page=2' in x)
                    parsed=urllib.parse.parse_qs(urllib.parse.urlsplit(page_link).query)
                    rows=Rows(req('bill-pos-list.php',{k:v[0] for k,v in parsed.items()})[1]).rows
                    assert rows and all('Plan' in row[-1] for row in rows)

            assert 'value="0"' in req('bill-pos-edit.php',{'username':'0'})[1]
            special="O'Reilly % + &é"
            for v in ('base','candidate'):
                db("INSERT INTO custom_plans(id,planName,planActive) VALUES (81,'O''Reilly % + &é','yes');INSERT INTO custom_info(id,username) VALUES (31,'O''Reilly % + &é');INSERT INTO custom_bill(id,username,planName,contactperson) VALUES (31,'O''Reilly % + &é','O''Reilly % + &é','Special');INSERT INTO custom_check(username,attribute,op,value) VALUES ('O''Reilly % + &é','Cleartext-Password',':=','"+factor+"')",v)
            for p in ('bill-plans-edit.php','bill-plans-del.php'):compare(p,{'planName':special})
            # Dropdown option encoding fixes are scoped elsewhere; check raw action links here.
            text=req('bill-pos-list.php',{'planname':special})[1];parser=Rows(text)
            assert len(parser.rows)==1
            userlink=next(x for x in parser.links if x.startswith('bill-pos-edit.php?'))
            assert urllib.parse.parse_qs(urllib.parse.urlsplit(userlink).query)['username']==[special]
            sortlink=next(x for x in parser.links if 'orderBy=contactperson' in x)
            parsed=urllib.parse.parse_qs(urllib.parse.urlsplit(sortlink).query);assert parsed['planname']==[special]
            assert len(Rows(req('bill-pos-list.php',{k:v[0] for k,v in parsed.items()})[1]).rows)==1
            for v in ('base','candidate'):
                conf=f/v/'app/common/includes/daloradius.conf.php';conf.write_text(configs[v]+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='100';".replace('\n','\n'))
            text=req('bill-plans-list.php')[1]
            planlinks=[x for x in Rows(text).links if x.startswith('bill-plans-edit.php?')]
            assert special in [urllib.parse.parse_qs(urllib.parse.urlsplit(x).query)['planName'][0] for x in planlinks]
            # Duplicate names formerly understated rendered/pagination row count.
            for v in ('base','candidate'):db("INSERT INTO custom_plans(id,planName,planActive) VALUES (82,'Plan 0','yes')",v)
            candidate=Rows(req('bill-plans-list.php')[1]);baseline=Rows(req('bill-plans-list.php',v='base')[1])
            assert len(candidate.rows)==len(baseline.rows)==11
            for v in ('base','candidate'):(f/v/'app/common/includes/daloradius.conf.php').write_text(configs[v])
            assert Rows(req('bill-plans-list.php',{'page':'6'})[1]).rows != Rows(req('bill-plans-list.php',{'page':'6'},v='base')[1]).rows
            for v in ('base','candidate'):
                db('DELETE FROM custom_plans WHERE id IN (81,82);DELETE FROM custom_info WHERE id=31;DELETE FROM custom_bill WHERE id=31;DELETE FROM custom_check WHERE username="O\'Reilly % + &é"',v)
                (f/v/'app/common/includes/daloradius.conf.php').write_text(configs[v])
            # Real late SQL failures in all page-local paths, not mocked query returns.
            provider=f/'candidate/app/operators/library/catalog_reads_pdo.php';original=provider.read_text()
            hook="""    $hook='/fixtures/fail-read.json';
    if (is_file($hook)) {
        $test=json_decode(file_get_contents($hook),true);
        if (strpos($sql,$test['match'])!==false) {unlink($hook);$pdo->exec($test['sql']);}
    }
"""
            provider.write_text(original.replace('    $statement = $pdo->prepare($sql);',hook+'    $statement = $pdo->prepare($sql);'))
            cases=[('bill-plans-list.php',' LIMIT ','custom_plans','planType'),('bill-pos-list.php',' LIMIT ','custom_bill','contactperson'),('bill-plans-edit.php','SELECT DISTINCT(profile_name)','custom_profiles','profile_name'),('bill-pos-edit.php','SELECT id, planName,','custom_bill','planName'),('bill-plans-del.php','SELECT DISTINCT(planName)','custom_plans','planName'),('bill-pos-del.php','SELECT DISTINCT(username)','custom_check','username'),('mng-batch-del.php','SELECT DISTINCT(batch_name)','custom_batch','batch_name'),('mng-batch-add.php','SELECT id, name','custom_hotspots','name')]
            for p,match,table,column in cases:
                (f/'fail-read.json').write_text(json.dumps({'match':match,'sql':f'ALTER TABLE {table} RENAME COLUMN {column} TO hidden_column'}))
                status,text,_=req(p,DEFAULT.get(p));assert not (f/'fail-read.json').exists(),(p,'hook reached')
                assert status==200 and 'Unable to read billing catalogue data' in text and 'SQLSTATE' not in text,(p,'late SQL envelope')
                db(f'ALTER TABLE {table} RENAME COLUMN hidden_column TO {column}')
            # A committed deletion is not rolled back by a later selector failure.
            db("INSERT INTO custom_check(username,attribute,op,value) VALUES ('DeleteOnly','Auth-Type',':=','Accept')")
            token=next(x['csrf_token'] for x in Forms(req('bill-pos-del.php')[1]).forms if 'csrf_token' in x)
            (f/'fail-read.json').write_text(json.dumps({'match':'SELECT DISTINCT(username)','sql':'ALTER TABLE custom_check RENAME COLUMN username TO hidden_column'}))
            text=req('bill-pos-del.php',data={'username':'DeleteOnly','csrf_token':token})[1]
            assert 'Deleted user:' in text and 'Unable to read billing catalogue data' in text
            db('ALTER TABLE custom_check RENAME COLUMN hidden_column TO username');assert db("SELECT COUNT(*) FROM custom_check WHERE username='DeleteOnly'")=='0'
            db("INSERT INTO custom_plans(id,planName,planId,planType,planActive) VALUES (83,'Committed Plan','Temp','Prepaid','yes')")
            token=next(x['csrf_token'] for x in Forms(req('bill-plans-edit.php',{'planName':'Committed Plan'})[1]).forms if 'csrf_token' in x)
            (f/'fail-read.json').write_text(json.dumps({'match':'SELECT DISTINCT(profile_name)','sql':'ALTER TABLE custom_profiles RENAME COLUMN profile_name TO hidden_column'}))
            text=req('bill-plans-edit.php',data={'csrf_token':token,'planName':'Committed Plan','planId':'CommittedDisplay','planType':'Prepaid','planTimeType':'Accumulative','planCost':'12.30','planActive':'yes'})[1]
            assert 'has been successfully updated' in text and 'Unable to read billing catalogue data' in text and 'name="planId"' not in text
            assert db("SELECT planId FROM custom_plans WHERE id=83")=='CommittedDisplay'
            db('ALTER TABLE custom_profiles RENAME COLUMN hidden_column TO profile_name');db("DELETE FROM custom_plans WHERE id=83")
            token=next(x['csrf_token'] for x in Forms(req('mng-batch-add.php')[1]).forms if 'csrf_token' in x)
            snapshot=state();check_count=db('SELECT COUNT(*) FROM custom_check')
            (f/'fail-read.json').write_text(json.dumps({'match':'SELECT id, name','sql':'ALTER TABLE custom_hotspots RENAME COLUMN name TO hidden_column'}))
            text=req('mng-batch-add.php',data={'csrf_token':token,'batch_name':'NoCreate','accountType':'random_pincode_no_password','number':'1','length_user':'8'})[1]
            assert 'Unable to read billing catalogue data' in text and 'export-users-form' not in text and state()==snapshot and db('SELECT COUNT(*) FROM custom_check')==check_count
            db('ALTER TABLE custom_hotspots RENAME COLUMN hidden_column TO name')
            provider.write_text(original)
            groups_file=f/'candidate/app/operators/include/management/groups.php';groups_source=groups_file.read_text()
            groups_file.write_text(groups_source.replace('SELECT groupname,priority FROM', 'SELECT groupname,missing_priority FROM'))
            status,text,_=req('bill-pos-edit.php',{'username':'User0'})
            assert status==200 and 'Unable to read billing catalogue data' in text and '</html>' in text and 'groups[0][0]' not in text
            groups_file.write_text(groups_source)
            # PIN classification fixes the proven undefined numeric-row key, not authentication data.
            db("INSERT INTO custom_info(id,username) VALUES (90,'12345');INSERT INTO custom_bill(id,username,planName) VALUES (90,'12345','Plan 0');INSERT INTO custom_check(username,attribute,op,value) VALUES ('12345','Auth-Type',':=','Accept')")
            text=req('bill-pos-list.php',{'orderBy':'id','orderType':'desc'})[1]
            assert 'data-bs-title="pin"' in text and 'bi-123' in text and '</html>' in text
            db("DELETE FROM custom_info WHERE id=90;DELETE FROM custom_bill WHERE id=90;DELETE FROM custom_check WHERE username='12345'")
            # Preserve optional records and retained accordions with native full-page requests.
            compare('bill-pos-edit.php',{'username':'Optional'})
            text=req('bill-pos-edit.php',{'username':'User0'})[1]
            assert 'Associated' in text or 'already associated' in text.lower()
            assert 'Subscription' in text and 'Invoice' in text
            # Borrowed handle survives real success/failure while owning a transaction.
            (f/'candidate/app/operators/borrowed.php').write_text("<?php include '../common/includes/config_read.php';include 'library/checklogin.php';require 'library/catalog_reads_pdo.php';$pdo=dalo_catalog_read_open($configValues);$pdo->beginTransaction();$pdo->exec(\"UPDATE custom_plans SET planId='borrowed' WHERE id=40\");$rows=dalo_catalog_read_rows($pdo,'SELECT planId FROM custom_plans WHERE id=40');try{dalo_catalog_read_rows($pdo,'SELECT missing FROM missing_table');}catch(Throwable $e){}echo json_encode([$rows==[['borrowed']],$pdo->inTransaction()]);$pdo->rollBack();")
            assert json.loads(req('borrowed.php')[1])==[True,True]
            assert db('SELECT planId FROM custom_plans WHERE id=40')=='P0'
            # Real PDO-only seven routes; POS edit still invokes separately planned R20 reports.
            legacy=f/'candidate/app/common/includes/db_open.php';legacy_text=legacy.read_text()
            close=f/'candidate/app/common/includes/db_close.php';close_text=close.read_text()
            for file in (legacy,close):file.write_text("<?php throw new RuntimeException('Legacy connection tripwire');")
            for p in PAGES:
                if p!='bill-pos-edit.php':
                    status,text,_=req(p,DEFAULT.get(p));assert status==200 and '</html>' in text and 'Unable to read billing catalogue data' not in text,(p,'legacy tripwire')
            # Allow only existing R20 functions, not page-local opens, on full POS edit.
            legacy.write_text("<?php $frame=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2);$caller=$frame[1]['function']??'';if(!in_array($caller,['userInvoicesStatus','userPlanInformation','userSubscriptionAnalysis','userConnectionStatus','checkUserOnline'],true)){throw new RuntimeException('Unexpected legacy caller');}\n"+legacy_text.removeprefix('<?php'))
            close.write_text(close_text)
            status,text,_=req('bill-pos-edit.php',DEFAULT['bill-pos-edit.php']);assert status==200 and '</html>' in text
            legacy.write_text(legacy_text)
            # SQL SELECT-only grants prove these reads do not require business writes.
            account='r'+secrets.token_hex(10);connection_factor=secrets.token_hex(24)
            sql("CREATE USER '"+account+"'@'%' IDENTIFIED BY '"+connection_factor+"';GRANT SELECT ON candidate.* TO '"+account+"'@'%';GRANT SELECT ON candidate_other.* TO '"+account+"'@'%';")
            conf=f/'candidate/app/common/includes/daloradius.conf.php'
            readonly=configs['candidate']+"\n$configValues['CONFIG_DB_USER']='"+account+"';$configValues['CONFIG_DB_PASS']='"+connection_factor+"';$configValues['CONFIG_LOCATIONS']['other']['Username']='"+account+"';$configValues['CONFIG_LOCATIONS']['other']['Password']='"+connection_factor+"';"
            conf.write_text(readonly.replace('\n','\n'))
            for p in PAGES:
                status,text,_=req(p,DEFAULT.get(p));assert status==200 and 'Unable to read billing catalogue data' not in text,(p,'SELECT-only native page')
            assert 'OtherOnly' in req('bill-pos-edit.php',DEFAULT['bill-pos-edit.php'],session=other)[1]
            conf.write_text(configs['candidate']);sql("DROP USER '"+account+"'@'%'");connection_factor=None;account=None;readonly=None
            conf.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']='invalid-table';".replace('\n','\n'))
            for p in ('bill-plans-list.php','bill-plans-edit.php','bill-plans-del.php'):assert 'Unable to read billing catalogue data' in req(p,DEFAULT.get(p))[1]
            conf.write_text(configs['candidate'])
            # SQL NULL display labels are normalized, no PHP deprecation or lost options.
            for v in ('base','candidate'):db('UPDATE custom_bill SET contactperson=NULL,company=NULL WHERE id=10',v)
            compare('bill-pos-list.php');compare('bill-pos-edit.php',{'username':'User0'})
            for v in ('base','candidate'):
                db('DELETE FROM custom_plans;DELETE FROM custom_profiles;DELETE FROM custom_check;DELETE FROM custom_info;DELETE FROM custom_bill;DELETE FROM custom_groups;DELETE FROM custom_batch;DELETE FROM custom_hotspots',v)
            for p in PAGES:compare(p,DEFAULT.get(p))
            (f/'candidate/app/operators/log-marker.php').write_text("<?php error_log('R16_LOG_CHANNEL_MARKER');echo 'ok';")
            req('log-marker.php')
            logs=subprocess.run(['docker','logs',h.WEB+'-candidate'],capture_output=True,text=True);logs=logs.stdout+logs.stderr
            assert 'R16_LOG_CHANNEL_MARKER' in logs and factor not in logs
            # Retained R20 warning sites are characterized; page/provider warnings are not accepted.
            failures=[line for line in logs.splitlines() if any(k in line for k in ('PHP Warning:','PHP Fatal error:','PHP Deprecated:','SQLSTATE['))]
            baseline_logs=subprocess.run(['docker','logs',h.WEB+'-base'],capture_output=True,text=True)
            baseline_logs=baseline_logs.stdout+baseline_logs.stderr
            allowed_lines={'213','214','215','216'}
            for line in failures:
                site=re.search(r'in /fixtures/candidate/app/operators/include/management/userBilling.php on line (\d+)',line)
                assert site and site[1] in allowed_lines and 'Trying to access array offset on null' in line, 'Unexpected candidate PHP diagnostic; details suppressed'
                assert 'Trying to access array offset on null in /fixtures/base/app/operators/include/management/userBilling.php on line '+site[1] in baseline_logs, 'Uncharacterized R20 warning'
            print('PASS retained R20 no-invoice warning sites match pinned baseline; no other candidate PHP diagnostics')
            print('PASS R16 complete PEAR/PDO comparisons',comparisons)
            print('PASS configured/named/SELECT-only reads, eight page-local plus grouped-widget late SQL errors, transaction ownership, committed edit/delete, blocked pre-write failure, retained R20 reports, tripwires and empty datasets')
        finally:
            for n in (h.WEB+'-base',h.WEB+'-candidate',h.DB):run('docker','rm','-f','-v',n,check=False)
            run('docker','network','rm',h.NETWORK,check=False)
            run('docker','run','--rm','-v',str(f)+':/fixtures','--entrypoint','sh',h.IMAGE,'-c',f'chown -R {os.getuid()}:{os.getgid()} /fixtures')
    assert not any(n.startswith(PREFIX) for n in run('docker','ps','-a','--format','{{.Names}}').splitlines())
    assert PREFIX not in run('docker','network','ls','--format','{{.Name}}')
    print('PASS fixture resources removed')
if __name__=='__main__':main()
