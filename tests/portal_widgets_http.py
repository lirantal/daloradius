#!/usr/bin/env python3
"""R22 isolated native portal HTTP/PHP/MariaDB widget differential; no live data.
State, SQL projections and session values remain only in runtime memory.
"""
import concurrent.futures, csv, html, io, json, re, secrets, shutil, subprocess, tempfile, time
import urllib.error, urllib.parse, urllib.request
from pathlib import Path
from html.parser import HTMLParser
import user_actions_http as h
ROOT = Path(__file__).resolve().parents[1]
BASE = '41b2b0ee4c3a930aeb298adda53cad020a86ba46'
PREFIX = 'pdo-r22-' + secrets.token_hex(5)
h.DB, h.WEB, h.NETWORK = [PREFIX + '-' + k for k in ('db', 'web', 'net')]
_original = h.run

def run(*args, **kw):
    try: return _original(*args, **kw)
    except RuntimeError: raise RuntimeError('Fixture command failed; details suppressed') from None
h.run = run
FILES = ['include/management/userReports.php', 'library/graphs/overall_users_data.php', 'library/tables/overall_users_download.php', 'library/tables/overall_users_login.php', 'library/tables/overall_users_upload.php', 'graphs-overall_download.php', 'graphs-overall_logins.php', 'graphs-overall_upload.php']
class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kw): return None
OPENER = urllib.request.build_opener(NoRedirect)
class Forms(HTMLParser):
    def __init__(self, body):
        super().__init__(); self.inputs=[]; self.options=[]; self.links=[]; self.forms=[]; self.feed(body)
    def handle_starttag(self, tag, attrs):
        a=dict(attrs)
        if tag in ('input','select','button'): self.inputs.append(a)
        if tag=='option': self.options.append(a)
        if tag=='a' and 'href' in a: self.links.append(a['href'])
        if tag=='form': self.forms.append(a)
    def csrf(self): return next(a['value'] for a in self.inputs if a.get('name')=='csrf_token')

def normalize(body):
    if '<main class="app-content' in body:
        body=body[body.index('<main class="app-content'):body.index('</main>')+len('</main>')]
    body=re.sub(r'(name="csrf_token"[^>]*value=")[^"]*',r'\1TOKEN',body)
    body=re.sub(r'(?:modal_|key-|form-)[0-9]+','random-id',body)
    def link(m):
        value=html.unescape(m.group(1)); p=urllib.parse.urlsplit(value)
        if p.query:
            value=urllib.parse.urlunsplit((p.scheme,p.netloc,p.path,
                urllib.parse.urlencode(sorted(urllib.parse.parse_qsl(p.query))),p.fragment))
        return 'href="'+value+'"'
    body=re.sub(r'href="([^"]*)"',link,body)
    return re.sub(r'>\s+<','><',body).strip()

def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    comparisons={};checks=[]
    with tempfile.TemporaryDirectory(prefix=PREFIX+'-',dir=scratch) as temp:
        f=Path(temp)
        sample=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
        names=dict(re.findall(r"\$configValues\['(CONFIG_DB_TBL_[^']+)'\]\s*=\s*'([^']+)'",sample))
        keys=['RADACCT','RADCHECK','RADREPLY','RADGROUPREPLY','RADUSERGROUP','DALOUSERINFO','DALOUSERBILLINFO',
            'DALOBILLINGINVOICE','DALOBILLINGINVOICEITEMS','DALOBILLINGINVOICESTATUS','DALOBILLINGINVOICETYPE',
            'DALOPAYMENTS','DALOBILLINGPLANS','DALOHOTSPOTS','DALOMESSAGES']
        tables={'CONFIG_DB_TBL_'+k:'custom_'+names['CONFIG_DB_TBL_'+k] for k in keys}
        fields=['firstname','lastname','email','department','company','workphone','homephone','mobilephone',
            'address','city','state','country','zip']
        usernames=['Alice','Bob','0',"Raw%+é'&",'Missing']
        (f/'session.php').write_text("""<?php
session_name('daloradius_user_sid');session_id($argv[1]);session_start();
$_SESSION=['logged_in'=>true,'login_user'=>$argv[2],'location_name'=>$argv[3],'time'=>time()];
require '/fixtures/'.$argv[4].'/app/users/library/sessions.php';
echo dalo_csrf_token();session_write_close();
""")
        probe='<?php\ninclude \'library/checklogin.php\';include \'../common/includes/config_read.php\';\ninclude \'../common/includes/layout.php\';include \'include/management/userReports.php\';\n$pdo=dalo_portal_handle($configValues);\n$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_SILENT);\n$pdo->beginTransaction();\n$pdo->exec("UPDATE custom_userinfo SET firstname=\'Borrowed\' WHERE id=10");\n$case=$_GET[\'case\']??\'\';\nif($case===\'bad\'){$configValues[\'CONFIG_DB_TBL_RADREPLY\']=\'missing_table\';}\nob_start();\nuserSubscriptionAnalysis($_SESSION[\'login_user\'],1,false,$pdo);\nuserPlanInformation($_SESSION[\'login_user\'],1,false,$pdo);\nuserConnectionStatus($_SESSION[\'login_user\'],1,false,$pdo);\n$body=ob_get_clean();\n$active=$pdo->inTransaction();\n$prior=$pdo->query("SELECT firstname FROM custom_userinfo WHERE id=10")->fetchColumn()===\'Borrowed\';\n$pdo->rollBack();\n$rolled=$pdo->query("SELECT firstname FROM custom_userinfo WHERE id=10")->fetchColumn()!==\'Borrowed\';\necho json_encode([$active,$prior,$rolled,strpos($body,\'Portal statistics unavailable\')!==false]);\n'
        web_names=[h.WEB+'-base',h.WEB+'-candidate',h.WEB+'-second']
        try:
            run('docker','network','create','--internal',h.NETWORK)
            run('docker','run','-d','--name',h.DB,'--network',h.NETWORK,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            h.wait_for(lambda:h.sql('SELECT 1'),'MariaDB')
            def db(query,v='candidate'):
                r=subprocess.run(['docker','exec','-i',h.DB,'mariadb','-uroot','-N','-B',v],
                    input="SET SESSION sql_mode='';\n"+query,text=True,capture_output=True)
                if r.returncode:raise RuntimeError('Fixture SQL codes '+repr(re.findall(r'ERROR (\d+)',r.stderr)))
                return r.stdout.rstrip('\n')
            quote=lambda s:"'"+s.replace('\\','\\\\').replace("'","''")+"'"
            for v in ('base','candidate','base_other','candidate_other'):
                h.sql('CREATE DATABASE '+v)
                for n in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):db((ROOT/'contrib/db'/n).read_text(),v)
                db('RENAME TABLE '+','.join(names[k]+' TO '+t for k,t in tables.items()),v)
                db("INSERT INTO custom_userinfo(id,username,firstname,lastname,changeuserinfo) VALUES"
                    "(10,'Alice','Alice','Customer',1),(11,'Bob','Bob','Customer',0),(12,'0','Zero','Customer',1),"
                    "(13,"+quote(usernames[3])+",'Raw','Customer',1)",v)
                db("INSERT INTO custom_hotspots(id,name,mac) VALUES(20,'Hotspot','aa:bb:cc:dd:ee:01');"
                    "INSERT INTO custom_billing_plans(id,planName,planCost,planTimeBank,planCurrency,planTimeType,"
                    "planBandwidthUp,planBandwidthDown,planTrafficTotal,planTrafficUp,planTrafficDown) VALUES"
                    "(1,'Plan',12.75,10000,'EUR','Accumulative','64','128',100000,50000,50000);"
                    "INSERT INTO custom_userbillinfo(id,username,planName,email,contactperson,address,city,state,phone) VALUES"
                    "(10,'Alice','Plan','alice@example.invalid','Alice Customer','Street','Town','State','123'),"
                    "(11,'Bob','Plan','bob@example.invalid','Bob Customer','Other','','',''),"
                    "(12,'0','Plan','zero@example.invalid','Zero Customer','','','',''),"
                    "(13,"+quote(usernames[3])+",'Plan','raw@example.invalid','Raw Customer','','','','');",v)
                db("INSERT INTO custom_invoice(id,user_id,date,status_id,type_id,notes) VALUES"
                    "(50,10,'2020-01-02',1,1,'First'),(51,10,'2020-01-03',2,1,'Second'),"
                    "(52,11,'2020-01-02',1,1,'Foreign'),(53,999,'2020-01-02',1,1,'Orphan'),"
                    "(54,10,'2020-01-04',1,1,'No items'),(57,10,'2020-01-05',3,1,'Numeric sorting'),(55,12,'2020-01-02',1,1,'Zero'),(56,13,'2020-01-02',1,1,'Raw');"
                    "INSERT INTO custom_invoice_items(id,invoice_id,plan_id,amount,tax_amount,notes) VALUES"
                    "(100,50,1,12.75,0.25,'Item A'),(101,50,999,1.5,0.5,'Item B'),"
                    "(102,51,1,0.1,0.2,'Fraction'),(103,52,1,99,0,'Foreign item'),(104,55,1,1,0,'Zero item'),"
                    "(105,56,1,2,0,'Raw item'),(106,57,1,9,0,'Numeric item');"
                    "INSERT INTO custom_payment(invoice_id,amount,date) VALUES(50,3,'2020-01-02'),(50,0.25,'2020-01-02'),(57,11,'2020-01-05');",v)
                db("INSERT INTO custom_radacct(radacctid,username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime,acctstoptime,"
                    "acctsessiontime,acctinputoctets,acctoutputoctets,calledstationid) VALUES"
                    "(100,'Alice','a0','a0','192.0.2.1','2020-01-01','2020-01-01 01:00:00',3600,100,200,'aa:bb:cc:dd:ee:01'),"
                    "(101,'Alice','a1','a1','192.0.2.1','2020-01-02','2020-01-02 00:01:00',60,10,20,'aa:bb:cc:dd:ee:01'),"
                    "(102,'Alice','a2','a2','192.0.2.1','2020-01-03',NULL,1800,11,21,'unmatched'),"
                    "(103,'Alice','a3','a3','192.0.2.1','2020-01-04','2020-01-04 00:02:00',120,12,22,'unmatched'),"
                    "(104,'Alice','a4','a4','192.0.2.1',CURRENT_DATE(),DATE_ADD(CURRENT_DATE(),INTERVAL 60 SECOND),60,25,50,'unmatched'),"
                    "(200,'Bob','b1','b1','192.0.2.2','2020-01-02',NULL,1,99,99,'unmatched'),"
                    "(300,'0','z1','z1','192.0.2.3','2020-01-02','2020-01-02 00:01:00',60,1,1,'unmatched'),"
                    "(400,"+quote(usernames[3])+",'r1','r1','192.0.2.4','2020-01-02','2020-01-02 00:01:00',60,2,2,'unmatched');"
                    "INSERT INTO custom_radcheck(username,attribute,op,value) VALUES('Alice','Expiration',':=','31 Dec 2030');"
                    "INSERT INTO custom_radreply(username,attribute,op,value) VALUES('Alice','Session-Timeout',':=','3600'),"
                    "('Alice','Idle-Timeout',':=','300');",v)
                db("UPDATE custom_messages SET content='<p>Fixture message <script>bad()</script></p>' WHERE type IN ('login','dashboard','support')",v)
                if v.endswith('_other'):db("UPDATE custom_userinfo SET firstname='Other';UPDATE custom_invoice SET notes='Other';"
                    "UPDATE custom_messages SET content='<p>Other backend</p>';UPDATE custom_radacct SET acctinputoctets=9999",v)
            for v in ('base','candidate','base_other','candidate_other'):
                db("INSERT INTO custom_radgroupreply(groupname,attribute,op,value) VALUES('FixtureGroup','Idle-Timeout',':=','120');"
                    "INSERT INTO custom_radusergroup(username,groupname,priority) VALUES('Bob','FixtureGroup',1);"
                    "INSERT INTO custom_radreply(username,attribute,op,value) VALUES('Bob','Session-Timeout',':=','0');",v)
                for n in range(42):
                    db("INSERT INTO custom_radacct(username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime,acctstoptime,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES"
                       "('Alice','duplicate',"+quote('boundary'+str(n))+",'192.0.2.9',DATE_SUB(CURRENT_DATE(),INTERVAL "+str(n*32)+" DAY),"
                       "DATE_ADD(DATE_SUB(CURRENT_DATE(),INTERVAL "+str(n*32)+" DAY),INTERVAL 60 SECOND),60,"+str(n*1048576)+","+str((n+1)*1048576)+")",v)
                db("INSERT INTO custom_radacct(username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime,acctstoptime,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES"
                    "('Missing','zero','zero','192.0.2.10',CURRENT_DATE(),'0000-00-00 00:00:00',0,0,0),"
                    "('Bob','null','null','192.0.2.11','2010-01-01','2010-01-01 00:01:00',NULL,NULL,NULL),"
                    "('Alice','zero','alice-zero','192.0.2.11','2009-01-01','0000-00-00 00:00:00',0,0,0)",v)
                for n,expression in enumerate([
                    "CURRENT_DATE()", "DATE_SUB(CURRENT_DATE(),INTERVAL 1 SECOND)",
                    "DATE_ADD(CURRENT_DATE(),INTERVAL 1 DAY)",
                    "CAST(DATE_FORMAT(CURRENT_DATE(),'%Y-%m-01') AS DATETIME)",
                    "DATE_SUB(CAST(DATE_FORMAT(CURRENT_DATE(),'%Y-%m-01') AS DATETIME),INTERVAL 1 SECOND)",
                    "DATE_ADD(LAST_DAY(CURRENT_DATE()),INTERVAL 1 DAY)",
                    "DATE_SUB(CURRENT_DATE(),INTERVAL WEEKDAY(CURRENT_DATE()) DAY)",
                    "DATE_ADD(DATE_SUB(CURRENT_DATE(),INTERVAL WEEKDAY(CURRENT_DATE()) DAY),INTERVAL 6 DAY)"]):
                    db("INSERT INTO custom_radacct(username,acctsessionid,acctuniqueid,nasipaddress,acctstarttime,acctstoptime,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES"
                       "('Alice',"+quote('bound'+str(n))+","+quote('bound'+str(n))+",'192.0.2.12',"+expression+",DATE_ADD("+expression+",INTERVAL 1 SECOND),1,1048576,2097152)",v)
                if v.endswith('_other'):db("UPDATE custom_radacct SET acctinputoctets=acctinputoctets+7654321,acctoutputoctets=acctoutputoctets+8765432 WHERE username='Alice'",v)
            configs={};urls={};sessions={}
            for v in ('base','candidate'):
                shutil.copytree(ROOT/'app',f/v/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
                if v=='base':
                    for p in FILES:(f/v/'app/users'/p).write_bytes(subprocess.check_output(['git','show',BASE+':app/users/'+p],cwd=ROOT))
                conf=sample
                values=dict(tables,CONFIG_DB_HOST=h.DB,CONFIG_DB_USER='root',CONFIG_DB_PASS='',CONFIG_DB_NAME=v,
                    CONFIG_IFACE_TABLES_LISTING='2',CONFIG_IFACE_DEBUG='0',CONFIG_LOG_PAGES='no',CONFIG_LOG_QUERIES='no',
                    CONFIG_LOG_ACTIONS='no',CONFIG_DEBUG_SQL='no',CONFIG_DEBUG_SQL_ONPAGE='no')
                for k,value in values.items():conf+='\n$configValues['+repr(k)+']='+repr(value)+';\n'
                conf+="\n$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>'"+h.DB+"',"
                conf+="'Username'=>'root','Password'=>'','Database'=>'"+v+"_other','Port'=>'3306');\n"
                configs[v]=conf;(f/v/'app/common/includes/daloradius.conf.php').write_text(conf)
                if v=='candidate':
                    # No remaining portal widget may silently reopen PEAR.
                    for name in ('db_open.php','db_close.php'):
                        (f/v/'app/common/includes'/name).write_text("<?php throw new RuntimeException('Legacy portal connection reached');")
                    (f/v/'app/users/probe.php').write_text(probe)
                widget=f/v/'app/users/include/management/userReports.php'
                widget.write_text(widget.read_text().replace('timestampdiff(SECOND,AcctStartTime,NOW())', "timestampdiff(SECOND,AcctStartTime,'2030-01-01')"))
                web=h.WEB+'-'+v
                run('docker','run','-d','--name',web,'--network',h.NETWORK,'-v',str(f)+':/fixtures',
                    '-w','/fixtures/'+v+'/app/users','--entrypoint','php',h.IMAGE,'-d','display_errors=0',
                    '-d','opcache.enable=0','-d','opcache.enable_cli=0','-S','0.0.0.0:8080','-t','.')
                ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',web)
                urls[v]='http://'+ip+':8080/'
                for i,user in enumerate(usernames):
                    for loc in ('default','other'):
                        sid=secrets.token_hex(16);sessions[v,i,loc]=sid
                        run('docker','exec',web,'php','/fixtures/session.php',sid,user,loc,v)
            def req(page,query=None,version='candidate',user=0,location='default',data=None,base=None):
                headers={} if user is None else {'Cookie':'daloradius_user_sid='+sessions[version,user,location]}
                url=(base or urls[version])+page+('?' +urllib.parse.urlencode(query,doseq=True) if query else '')
                request=urllib.request.Request(url,headers=headers,
                    data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode())
                try:
                    with OPENER.open(request,timeout=45) as r:return r.status,r.read(),r.headers
                except urllib.error.HTTPError as e:return e.code,e.read(),e.headers
            h.wait_for(lambda:req('login.php',user=None),'PHP HTTP server')
            def state():
                return tuple(db(query,v) for v in ('base','candidate','base_other','candidate_other') for query in (
                    'SELECT radacctid,username,acctstarttime,acctstoptime,acctsessionid,acctsessiontime,acctinputoctets,acctoutputoctets FROM custom_radacct ORDER BY radacctid',
                    'SELECT id,firstname,lastname FROM custom_userinfo ORDER BY id',
                    'SELECT id,username,attribute,op,value FROM custom_radreply ORDER BY id',
                    'SELECT id,groupname,attribute,op,value FROM custom_radgroupreply ORDER BY id',
                    'SELECT username,groupname,priority FROM custom_radusergroup ORDER BY username,groupname,priority'))
            initial_state=state()
            def compare(label,page,query=None,user=0,location='default',json_mode=False):
                a=req(page,query,version='base',user=user,location=location);b=req(page,query,user=user,location=location)
                assert a[0]==b[0]==200,('HTTP differential',label,a[0],b[0])
                if json_mode:
                    left,right=json.loads(a[1]),json.loads(b[1])
                else:
                    assert b'</html>' in a[1] and b'</html>' in b[1],('Incomplete page',label)
                    legacy=a[1].decode()
                    if user==3 and page in ('graphs-overall_download.php','graphs-overall_upload.php'):
                        # Legacy traffic titles interpolate the raw account; candidate escapes it.
                        legacy=legacy.replace('produced by user '+usernames[user]+'</h4>',
                            'produced by user '+html.escape(usernames[user],quote=True).replace('&#x27;','&#039;')+'</h4>')
                    left,right=normalize(legacy),normalize(b[1].decode())
                if left!=right:
                    if isinstance(left,str):
                        index=next((i for i,(x,y) in enumerate(zip(left,right)) if x!=y),min(len(left),len(right)))
                        raise AssertionError(('Differential mismatch',label,index,len(left),len(right)))
                    raise AssertionError(('JSON differential mismatch',label))
                assert label not in comparisons;comparisons[label]=True
                return b
            routes={'login':'graphs-overall_logins.php','download':'graphs-overall_download.php','upload':'graphs-overall_upload.php'}
            chart='library/graphs/overall_users_data.php'
            for loc in ('default','other'):
                for user in range(len(usernames)):
                    # NULL/zero formatter returns are a pinned baseline PHP8 issue,
                    # corrected by candidate scalar formatting, not a SQL mismatch.
                    compare(f'{loc}:{user}:home','home-main.php',user=user,location=loc)
                    for category,page in routes.items():
                        for typ,period in [('daily','day'),('monthly','month'),('yearly','year')]:
                            for size in ('megabytes','gigabytes'):
                                q={'type':typ,'size':size}
                                if user!=2:
                                    response=compare(f'{loc}:{user}:{category}:{typ}:{size}:chart',chart,dict(q,category=category),user,loc,True)
                                    envelope=json.loads(response[1]);assert len(envelope['data']['labels'])<=36
                                    if user==0 and typ in ('daily','monthly'):assert len(envelope['data']['labels'])==36
                                else:
                                    assert json.loads(req(chart,dict(q,category=category),version='base',user=2,location=loc)[1])['data']['labels']==[]
                                    assert json.loads(req(chart,dict(q,category=category),user=2,location=loc)[1])['data']['labels']
                                for order in ('asc','desc'):
                                    # Period is unique. Complete tie membership is tested below.
                                    compare(f'{loc}:{user}:{category}:{typ}:{size}:{order}',page,
                                        dict(q,orderBy=period,orderType=order),user,loc)
                            for pn in (2,999,0):
                                compare(f'{loc}:{user}:{category}:{typ}:page{pn}',page,
                                    {'type':typ,'orderBy':period,'page':pn},user,loc)
            checks.append('full native home widgets, table period sorts and chart envelopes for five identities/two backends')
            # Metric ties: full membership/ranks, no invented legacy tie ordering.
            for category,page in routes.items():
                metric={'login':'logins','download':'downloads','upload':'uploads'}[category]
                for typ in ('daily','monthly','yearly'):
                    for order in ('asc','desc'):
                        q={'type':typ,'orderBy':metric,'orderType':order}
                        cfgs={v:f/v/'app/common/includes/daloradius.conf.php' for v in ('base','candidate')}
                        full=[]
                        for v in ('base','candidate'):
                            cfgs[v].write_text(configs[v]+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='1000';\n")
                            response=req(page,q,version=v);assert response[0]==200
                            rows=re.findall(r'<tbody>(.*?)</tbody>',normalize(response[1].decode()),re.S)
                            assert len(rows)==1
                            full.append(re.findall(r'<tr>(.*?)</tr>',rows[0],re.S))
                            cfgs[v].write_text(configs[v])
                        assert sorted(full[0])==sorted(full[1]),('Tied membership',category,typ,order)
                        def value(row):return re.sub('<[^>]*>','',re.findall(r'<td>(.*?)</td>',row,re.S)[1])
                        assert [value(r) for r in full[0]]==[value(r) for r in full[1]],('Tied ranks',category,typ,order)
                        paged=[]
                        for pn in range(1,(len(full[1])+1)//2+1):
                            response=req(page,dict(q,page=pn));assert response[0]==200
                            parsed=re.findall(r'<tbody>(.*?)</tbody>',normalize(response[1].decode()),re.S);assert len(parsed)==1
                            paged+=re.findall(r'<tr>(.*?)</tr>',parsed[0],re.S)
                        assert paged==full[1] and len(paged)==len(set(paged)),('Stable complete pagination',category,typ,order)
                        comparisons[f'ties:{category}:{typ}:{order}']=True
            checks.append('all metric-sort ties and complete deterministic paginated membership')
            # Real producer URLs and filters, not just directly constructed endpoints.
            for category,page in routes.items():
                result=req(page,{'type':'monthly','size':'gigabytes','orderBy':'month','page':2})
                sources=re.findall(rb'data-chart-source="([^"]+)"',result[1]);assert len(sources)==1
                generated=html.unescape(sources[0].decode());response=req(generated)
                assert response[0]==200 and json.loads(response[1])['type']=='bar'
                for link in Forms(result[1].decode()).links:
                    if link.startswith('?') and 'orderBy=' in link:
                        decoded=dict(urllib.parse.parse_qsl(urllib.parse.urlsplit(link).query))
                        assert decoded['type']=='monthly'
                        assert req(page+html.unescape(link))[0]==200
                for q in [{'type[]':'daily','size[]':'megabytes','orderBy[]':'day','orderType[]':'asc'},
                    {'type':'bad','size':'bad','orderBy':'bad','orderType':'bad'}]:
                    assert req(page,q)[0]==200 and req(chart,dict(q,category=category))[0]==200
                # No HTTP account selector can switch the portal identity.
                assert normalize(req(page,{'username':'Bob'})[1].decode())==normalize(req(page)[1].decode())
                assert json.loads(req(chart,{'category':category,'username':'Bob'})[1])==json.loads(req(chart,{'category':category})[1])
                assert req(page,user=None)[0] in (302,303)
            assert req(chart,user=None)[0] in (302,303)
            for file in FILES:
                if file.startswith(('include/','library/tables/')):assert req(file)[0] in (302,303)
            assert req('library/portal_widgets_pdo.php')[0]==404
            checks.append('canvas sources, generated sort/page links, malformed arrays, account isolation and anonymous/direct access')
            # Routing must select measurably different accounting, not merely return 200.
            assert json.loads(req(chart,{'category':'upload'},location='default')[1])!=json.loads(req(chart,{'category':'upload'},location='other')[1])
            for case in ('good','bad'):
                response=req('probe.php',{'case':case});assert response[0]==200
                assert json.loads(response[1])==[True,True,True,case=='bad'],('Borrowed ownership',case)
            checks.append('named backend and borrowed silent-mode transaction survives native later read failure')
            confpath=f/'candidate/app/common/includes/daloradius.conf.php'
            for key,pages in [('CONFIG_DB_TBL_RADACCT',list(routes.values())+[chart,'home-main.php']),
                ('CONFIG_DB_TBL_RADREPLY',['home-main.php']),('CONFIG_DB_TBL_DALOBILLINGPLANS',['home-main.php'])]:
                confpath.write_text(configs['candidate']+"\n$configValues['"+key+"']='missing_table';\n")
                for page in pages:
                    response=req(page);assert b'Portal statistics unavailable' in response[1] and b'SQLSTATE' not in response[1]
                    if page==chart:assert response[0]==503 and list(json.loads(response[1]))==['error']
                confpath.write_text(configs['candidate'])
            confpath.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_TBL_RADACCT']='bad;identifier';\n")
            assert req(chart)[0]==503 and b'Portal statistics unavailable' in req(routes['login'])[1]
            confpath.write_text(configs['candidate'])
            # Late native LIMIT failure after full group query succeeded. Fixture-only hook.
            provider=f/'candidate/app/users/library/portal_widgets_pdo.php';original=provider.read_text()
            hook="if ($offset !== null) { $pdo->exec('ALTER TABLE custom_radacct CHANGE AcctStopTime broken_stop DATETIME NULL'); }\n"
            provider.write_text(original.replace('    return dalo_portal_rows($pdo, $sql, $bindings);',hook+'    return dalo_portal_rows($pdo, $sql, $bindings);'))
            for page in routes.values():
                response=req(page);assert b'Portal statistics unavailable' in response[1] and b'<tbody>' not in response[1]
                db('ALTER TABLE custom_radacct CHANGE broken_stop AcctStopTime DATETIME NULL')
            provider.write_text(original)
            checks.append('native missing/invalid tables, late LIMIT failure on every table route and redacted JSON errors')
            confpath.write_text(configs['candidate']+"\n$configValues['CONFIG_DEBUG_SQL_ONPAGE']='yes';\n")
            response=req(routes['login'])
            debug=response[1].split(b'Debugging SQL Queries:',1)[-1]
            assert b':username' in debug and b"username='Alice'" not in debug
            confpath.write_text(configs['candidate'])
            # SELECT-only reads on a generated disposable DB principal; never print it.
            reader='r'+secrets.token_hex(6)
            db('CREATE USER '+quote(reader)+"@'%' IDENTIFIED BY '';GRANT SELECT ON candidate.* TO "+quote(reader)+"@'%';")
            confpath.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_USER']="+repr(reader)+";\n")
            for page in list(routes.values())+[chart,'home-main.php']:assert req(page)[0]==200
            confpath.write_text(configs['candidate']);db('DROP USER '+quote(reader)+"@'%'")
            checks.append('all migrated routes usable with SELECT-only grants')
            for web in web_names[:2]:
                logs=run('docker','logs',web,check=False)
                result=subprocess.run(['docker','logs',web],capture_output=True,text=True);logs=result.stdout+result.stderr
                problems=re.findall(r'PHP (?:Warning|Fatal error|Notice|Deprecated):[^\n]*',logs)
                if web.endswith('-candidate'):assert not problems,('Candidate PHP log gate',len(problems))
                else:
                    allowed=('htmlspecialchars(): Passing null to parameter #1',)
                    assert all(any(x in problem for x in allowed) for problem in problems),('Unknown baseline log failures',len(problems))
                    if problems:print('CHARACTERIZED baseline NULL formatter deprecations',len(problems),flush=True)
            assert state()==initial_state, 'Read-only routes changed fixture state'
            assert all(comparisons.values());print('PASS R22',len(comparisons),'PEAR/PDO comparisons;',len(checks),'candidate families; native HTTP/SQL and logs',flush=True)
        finally:
            if f.exists():run('docker','run','--rm','--network','none','-v',str(f)+':/fixtures','--entrypoint','sh',h.IMAGE,
                '-c','chmod -R a+rwX /fixtures',check=False)
            for web in web_names:run('docker','rm','-f',web,check=False)
            run('docker','rm','-f',h.DB,check=False);run('docker','network','rm',h.NETWORK,check=False)
    assert not any(PREFIX in n for n in run('docker','ps','-a','--format','{{.Names}}').splitlines())
    assert not any(PREFIX in n for n in run('docker','network','ls','--format','{{.Name}}').splitlines())
    print('PASS R22 fixture cleanup',flush=True)
if __name__=='__main__':main()
