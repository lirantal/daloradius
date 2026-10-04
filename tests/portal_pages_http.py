#!/usr/bin/env python3
"""R21 isolated native portal HTTP/PHP/MariaDB/PDF differential; no live data.
Documents, state, and session/CSRF values remain only in runtime memory.
"""
import concurrent.futures, csv, html, io, json, re, secrets, shutil, subprocess, tempfile, time
import urllib.error, urllib.parse, urllib.request
from pathlib import Path
from html.parser import HTMLParser
import user_actions_http as h
ROOT = Path(__file__).resolve().parents[1]
BASE = 'f41baed8eb6a086764cabe9991ef5814e8fab2c1'
PREFIX = 'pdo-r21-' + secrets.token_hex(5)
h.DB, h.WEB, h.NETWORK = [PREFIX + '-' + k for k in ('db', 'web', 'net')]
_original = h.run

def run(*args, **kw):
    try: return _original(*args, **kw)
    except RuntimeError: raise RuntimeError('Fixture command failed; details suppressed') from None
h.run = run
FILES = ['acct-date.php', 'bill-invoice-report.php', 'bill-invoice-show.php', 'help-main.php', 'home-main.php',
    'include/common/notificationsUserInvoice.php', 'include/menu/sidebar/bill/default.php', 'login.php', 'pref-userinfo-edit.php']
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
    # Characterized legacy invoice-link concatenation; candidate is checked separately.
    body=re.sub(r'(orderType=(?:asc|desc))username=',r'\1&username=',body)
    def link(m):
        value=html.unescape(m.group(1)); p=urllib.parse.urlsplit(value)
        if p.query:
            value=urllib.parse.urlunsplit((p.scheme,p.netloc,p.path,
                urllib.parse.urlencode(sorted((k,v) for k,v in urllib.parse.parse_qsl(p.query) if k!='username')),p.fragment))
        return 'href="'+value+'"'
    body=re.sub(r'href="([^"]*)"',link,body)
    return re.sub(r'>\s+<','><',body).strip()

def pdf_norm(pdf):
    assert pdf.startswith(b'%PDF-') and b'%%EOF' in pdf
    pdf=re.sub(rb'/(CreationDate|ModDate) \(.*?\)',b'/Date (normalized)',pdf)
    return re.sub(rb'/ID\s*\[.*?\]',b'/ID [normalized]',pdf,flags=re.S)

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
        probe="""<?php
include 'library/checklogin.php';include '../common/includes/config_read.php';include 'library/portal_pages_pdo.php';
$pdo=dalo_portal_handle($configValues);$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_SILENT);
$pdo->beginTransaction();$pdo->exec("UPDATE custom_userinfo SET firstname='Borrowed' WHERE id=10");
$before=$pdo->inTransaction();
try {
 if(($_GET['case']??'')==='borrow'){
  $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']='missing_table';
  dalo_portal_invoice($pdo,$configValues,$_SESSION['login_user'],50);
 }else{dalo_portal_update_userinfo($pdo,$configValues,$_SESSION['login_user'],$_POST);}
 $failed=false;
}catch(Throwable $e){$failed=true;}
$owned=$pdo->inTransaction();$changed=$pdo->query("SELECT firstname FROM custom_userinfo WHERE id=10")->fetchColumn()==='Borrowed';
$pdo->rollBack();$rolled=$pdo->query("SELECT firstname FROM custom_userinfo WHERE id=10")->fetchColumn()!=='Borrowed';
echo json_encode([$before,$failed,$owned,$changed,$rolled]);
"""
        web_names=[h.WEB+'-base',h.WEB+'-candidate',h.WEB+'-second']
        blocker=None
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
            configs={};urls={};sessions={}
            for v in ('base','candidate'):
                shutil.copytree(ROOT/'app',f/v/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
                if v=='base':
                    for p in FILES:(f/v/'app/users'/p).write_bytes(subprocess.check_output(['git','show',BASE+':app/users/'+p],cwd=ROOT))
                    for p in ('app/common/includes/functions.php','app/users/include/management/userReports.php'):
                        (f/v/p).write_bytes(subprocess.check_output(['git','show',BASE+':'+p],cwd=ROOT))
                conf=sample
                values=dict(tables,CONFIG_DB_HOST=h.DB,CONFIG_DB_USER='root',CONFIG_DB_PASS='',CONFIG_DB_NAME=v,
                    CONFIG_IFACE_TABLES_LISTING='2',CONFIG_IFACE_DEBUG='0',CONFIG_LOG_PAGES='no',CONFIG_LOG_QUERIES='no',
                    CONFIG_LOG_ACTIONS='no',CONFIG_DEBUG_SQL='no',CONFIG_DEBUG_SQL_ONPAGE='no')
                for k,value in values.items():conf+='\n$configValues['+repr(k)+']='+repr(value)+';\n'
                conf+="\n$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>'"+h.DB+"',"
                conf+="'Username'=>'root','Password'=>'','Database'=>'"+v+"_other','Port'=>'3306');\n"
                configs[v]=conf;(f/v/'app/common/includes/daloradius.conf.php').write_text(conf)
                if v=='candidate':
                    # R22 finished independent widgets: the candidate portal must
                    # no longer open or close a legacy connection on these routes.
                    for name in ('db_open.php','db_close.php'):
                        (f/v/'app/common/includes'/name).write_text("<?php throw new RuntimeException('Legacy portal connection reached');")
                    (f/v/'app/users/probe.php').write_text(probe)
                endpoint=(f/v/'app/users/include/common/notificationsUserInvoice.php').read_text()
                context=endpoint[endpoint.index('function getInvoiceDetails('):].replace('?>','')
                (f/v/'app/users/include/common/context_probe.php').write_text("""<?php
require_once __DIR__.'/../../library/checklogin.php';require_once __DIR__.'/../../../common/includes/config_read.php';
require_once __DIR__.'/../../library/portal_pages_pdo.php';
header('Content-type: application/json');echo json_encode(getInvoiceDetails($_GET['invoice_id'],$_SESSION['login_user']));
"""+context)
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
            def compare(label,page,query=None,user=0,location='default',mode='html'):
                a=req(page,query,version='base',user=user,location=location);b=req(page,query,user=user,location=location)
                assert a[0]==b[0]==200,('Differential HTTP',label,a[0],b[0])
                if mode=='pdf':left,right=pdf_norm(a[1]),pdf_norm(b[1])
                elif mode=='json':left,right=json.loads(a[1]),json.loads(b[1])
                else:
                    assert b'</html>' in a[1] and b'</html>' in b[1],('Incomplete native page',label)
                    left,right=normalize(a[1].decode()),normalize(b[1].decode())
                # Native LIMIT queries have no secondary key. Verify all pages, complete
                # row membership and ordered SQL-key ranks instead of inventing a tie order.
                if mode=='html' and page in ('acct-date.php','bill-invoice-report.php') and query and 'orderBy' in query:
                    sort=query['orderBy'];direction=query['orderType'];raw=[];rendered=[]
                    for v,first in (('base',left),('candidate',right)):
                        if page=='acct-date.php':
                            key='dhs.name' if sort=='hotspot' else 'ra.'+sort
                            ranks=db('SELECT ra.radacctid,'+key+' FROM custom_radacct ra LEFT JOIN custom_hotspots dhs ON ra.calledstationid=dhs.mac WHERE ra.username='+quote(usernames[user])+' ORDER BY '+key+' '+direction,v+('_other' if location=='other' else ''))
                        else:
                            key={'id':'a.id','date':'a.date','status_id':'a.status_id',
                                'totalbilled':'COALESCE((SELECT SUM(amount+tax_amount) FROM custom_invoice_items WHERE invoice_id=a.id),0)',
                                'totalpayed':'COALESCE((SELECT SUM(amount) FROM custom_payment WHERE invoice_id=a.id),0)'}[sort]
                            ranks=db('SELECT a.id,'+key+' FROM custom_invoice a INNER JOIN custom_userbillinfo b ON a.user_id=b.id INNER JOIN custom_invoice_status c ON a.status_id=c.id WHERE b.username='+quote(usernames[user])+' ORDER BY '+key+' '+direction,v+('_other' if location=='other' else ''))
                        pairs=[line.split('\t') for line in ranks.splitlines()];values=dict(pairs)
                        pages=[first]
                        for n in range(2,(len(pairs)+1)//2+1):
                            result=req(page,dict(query,page=n),version=v,user=user,location=location)
                            assert result[0]==200 and b'</html>' in result[1];pages.append(normalize(result[1].decode()))
                        rows=[]
                        for body in pages:
                            match=re.search(r'<tbody>(.*?)</tbody>',body,re.S);assert match
                            rows.extend(re.findall(r'<tr>(.*?)</tr>',match[1],re.S))
                        ids=[html.unescape(re.sub('<[^>]+>','',re.match(r'<td>(.*?)</td>',r,re.S)[1])) for r in rows]
                        assert [values[i] for i in ids]==[p[1] for p in pairs],('SQL rank and pagination',label,v)
                        if v=='candidate':
                            assert len(ids)==len(set(ids))==len(pairs),('Complete pagination cardinality',label,v)
                        else:
                            # Characterize historical tied LIMIT duplication without changing its SQL.
                            # The complete unpaginated native baseline still supplies every rendered row.
                            cfg=f/'base/app/common/includes/daloradius.conf.php'
                            cfg.write_text(configs['base']+"\n$configValues['CONFIG_IFACE_TABLES_LISTING']='100';\n")
                            try:
                                full=req(page,query,version='base',user=user,location=location)
                                assert full[0]==200 and b'</html>' in full[1]
                                match=re.search(r'<tbody>(.*?)</tbody>',normalize(full[1].decode()),re.S);assert match
                                rows=re.findall(r'<tr>(.*?)</tr>',match[1],re.S)
                                assert len(rows)==len(pairs)
                            finally:cfg.write_text(configs['base'])
                        raw.append(sorted(rows));rendered.append(re.sub(r'<tbody>.*?</tbody>','<tbody>ROWS</tbody>',first,flags=re.S))
                    assert raw[0]==raw[1] and rendered[0]==rendered[1],('Complete native page/membership',label)
                    left=right=rendered[0]
                if left!=right:
                    if isinstance(left,str):
                        index=next((i for i,(x,y) in enumerate(zip(left,right)) if x!=y),min(len(left),len(right)))
                        # Structural location only: do not print documents, identities or fields.
                        raise AssertionError(('Differential mismatch',label,'offset',index,'lengths',len(left),len(right)))
                    raise AssertionError(('Differential mismatch',label,'content suppressed'))
                assert label not in comparisons;comparisons[label]=True
                return b
            for loc in ('default','other'):
                for p in ('pref-userinfo-edit.php','home-main.php','help-main.php'):compare(loc+':'+p,p,location=loc)
                compare(loc+':login','login.php',user=None)
                for user in (0,2,3):
                    for page,sorts in [('acct-date.php',['radacctid','hotspot','nasipaddress','framedipaddress','acctstarttime',
                        'acctstoptime','acctsessiontime','acctinputoctets','acctoutputoctets','acctterminatecause']),
                        ('bill-invoice-report.php',['id','date','totalbilled','totalpayed','status_id'])]:
                        for direction in ('asc','desc'):
                            for sort in sorts:compare(f'{loc}:{user}:{page}:{sort}:{direction}',page,
                                {'orderBy':sort,'orderType':direction},user,loc)
                        for name,q in [('page2',{'page':2}),('empty',{'startdate':'2035-01-01'}),
                            ('dates',{'startdate':'2020-01-01','enddate':'2020-01-04'})]:
                            compare(f'{loc}:{user}:{page}:{name}',page,q,user,loc)
                        for v in ('base','candidate'):req(page,version=v,user=user,location=loc)
                        a=req('include/management/fileExport.php',version='base',user=user,location=loc)
                        b=req('include/management/fileExport.php',user=user,location=loc)
                        assert a[0]==b[0]==200 and a[1]==b[1],('CSV parity',page,user,loc)
                        comparisons[f'{loc}:{user}:{page}:csv']=True
                        rows=list(csv.reader(io.StringIO(b[1].decode()),skipinitialspace=True))
                        allowed={'acct-date.php':{0:{'100','101','102','103','104'},2:{'300'},3:{'400'}},
                            'bill-invoice-report.php':{0:{'50','51','54','57'},2:{'55'},3:{'56'}}}
                        assert {r[0] for r in rows[1:]}==allowed[page][user],('CSV isolation',page,user)
                        assert req('include/management/fileExport.php',user=user,location=loc)[0]==400
                for invoice,user in ((50,0),(51,0),(54,0),(57,0),(55,2),(56,3)):
                    q={'invoice_id':invoice}
                    compare(f'{loc}:{invoice}:show','bill-invoice-show.php',q,user,loc)
                    compare(f'{loc}:{invoice}:context','include/common/context_probe.php',q,user,loc,'json')
                    if user==2:
                        # Legacy uses PHP falsiness and silently refuses the valid session name '0'.
                        old=req('include/common/notificationsUserInvoice.php',q,version='base',user=user,location=loc)
                        new=req('include/common/notificationsUserInvoice.php',q,user=user,location=loc)
                        assert old[0]==new[0]==200 and old[1].strip()==b''
                        pdf_norm(new[1]);checks.append('legacy zero-identity PDF repaired:'+loc)
                    else:
                        compare(f'{loc}:{invoice}:pdf','include/common/notificationsUserInvoice.php',q,user,loc,'pdf')
            print('PASS R21 ordinary native pages/contexts/PDFs/CSV',len(comparisons),flush=True)
            for page in FILES:
                if page=='login.php' or 'sidebar/' in page:continue
                status,body,headers=req(page,{'invoice_id':50},user=None)
                assert status==302 and 'login.php' in headers.get('Location',''),('Authentication',page)
            for page in ('bill-invoice-show.php','include/common/notificationsUserInvoice.php'):
                for value in ('52','53','999'):
                    status,body,_=req(page,{'invoice_id':value})
                    assert b'Foreign item' not in body and b'%PDF' not in body
                    assert (status==404 if 'notifications' in page else b'this invoice has no details' in body)
            for value in ('0','-1','50junk','92233720368547758080','',['50']):
                q={'invoice_id[]':value} if isinstance(value,list) else {'invoice_id':value}
                status,body,_=req('include/common/notificationsUserInvoice.php',q)
                assert status==400 and b'%PDF' not in body
                assert b'invalid or empty invoice id' in req('bill-invoice-show.php',q)[1]
            for value in (['download'],'email',''):
                key='destination[]' if isinstance(value,list) else 'destination'
                assert req('include/common/notificationsUserInvoice.php',{'invoice_id':50,key:value})[0]==400
            body=req('bill-invoice-report.php',{'invoice_status':'1'})[1];form=Forms(body.decode())
            assert any(x.get('name')=='invoice_status' for x in form.inputs)
            assert not any(x.get('name')=='invoice_status_id' for x in form.inputs)
            assert any(x.get('value')=='1' and 'selected' in x for x in form.options)
            assert b'>51</td>' not in body and b'>50</td>' in body
            for page in ('acct-date.php','bill-invoice-report.php'):
                status,body,_=req(page,{'orderBy[]':['id'],'orderType[]':['desc'],'startdate[]':['x'],
                    'enddate':'2020-02-30','username':'Bob','invoice_status[]':['1'],'page[]':['2']})
                assert status==200 and b'</html>' in body and b'>200</td>' not in body and b'>52</td>' not in body
                q={'startdate':'2020-01-01','enddate':'2020-01-04'}
                if 'invoice' in page:q['invoice_status']='1'
                for link in Forms(req(page,q)[1].decode()).links:
                    if not link.startswith('?') or 'orderBy=' not in link:continue
                    query=dict(urllib.parse.parse_qsl(urllib.parse.urlsplit(link).query))
                    assert all(query.get(k)==value for k,value in q.items()),('Filtered link',page)
                    assert req(page,query)[0]==200
            checks.append('authentication, invoice ownership/exact IDs, sidebar producer, malformed filters and links')
            def state(v='candidate'):return db('SELECT id,username,changeuserinfo,'+','.join(fields)+',notes,enableportallogin,creationdate,creationby,updatedate,updateby FROM custom_userinfo ORDER BY id',v)
            def post(data,user=0,location='default',base=None):
                token=Forms(req('pref-userinfo-edit.php',user=user,location=location,base=base)[1].decode()).csrf()
                return req('pref-userinfo-edit.php',user=user,location=location,data=dict(data,csrf_token=token),base=base)
            for user in (0,2,3):
                values={field:('Value '+field+" %+'é&") for field in fields};values['email']='fixture@example.invalid'
                for v in ('base','candidate'):
                    token=Forms(req('pref-userinfo-edit.php',version=v,user=user)[1].decode()).csrf()
                    assert b'User info have been updated' in req('pref-userinfo-edit.php',version=v,user=user,
                        data=dict(values,csrf_token=token,username='Bob'))[1]
                assert state('base')==state(),('Mutation parity',user);comparisons[f'userinfo:update:{user}']=True
            for v in ('base','candidate'):
                token=Forms(req('pref-userinfo-edit.php',version=v)[1].decode()).csrf()
                assert b'User info have been updated' in req('pref-userinfo-edit.php',version=v,
                    data={'firstname':'Only first','csrf_token':token})[1]
            assert state('base')==state();comparisons['userinfo:missing-fields-clear']=True
            before=state()
            for data in ({'firstname':'Forbidden','csrf_token':'wrong'},{'firstname':'Forbidden','csrf_token[]':['x']}):
                assert b'CSRF token error' in req('pref-userinfo-edit.php',data=data)[1] and state()==before
            for user in (1,4):assert b'not allowed' in post({'firstname':'Forbidden'},user=user)[1] and state()==before
            for field in fields:assert b'Something went wrong' in post({field+'[]':['malformed']})[1] and state()==before
            assert b'Something went wrong' in post({'firstname':'x'*300})[1] and state()==before
            db("INSERT INTO custom_userinfo(id,username,firstname,changeuserinfo) VALUES(99,'Alice','Duplicate',1)")
            db("DELIMITER //\nCREATE TRIGGER late_fail BEFORE UPDATE ON custom_userinfo FOR EACH ROW BEGIN IF NEW.id=99 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture'; END IF; END//\nDELIMITER ;")
            before=state();assert b'Something went wrong' in post({'firstname':'Both changed'})[1] and state()==before
            db('DROP TRIGGER late_fail')
            db("CREATE TRIGGER coerce_value BEFORE UPDATE ON custom_userinfo FOR EACH ROW SET NEW.firstname='Coerced'")
            assert b'Something went wrong' in post({'firstname':'Exact required'})[1] and state()==before
            db('DROP TRIGGER coerce_value')
            assert b'User info have been updated' in post({'firstname':'Both changed'})[1]
            assert db("SELECT COUNT(*) FROM custom_userinfo WHERE username='Alice' AND firstname='Both changed'")=='2'
            db('UPDATE custom_userinfo SET changeuserinfo=0 WHERE id=99');before=state()
            assert b'not allowed' in post({'firstname':'Forbidden'})[1] and state()==before
            db('DELETE FROM custom_userinfo WHERE id=99')
            for case in ('borrow','write'):
                status,body,_=req('probe.php',{'case':case});assert status==200 and json.loads(body)==[True]*5
            checks.append('state parity, CSRF, literal/zero identity, scalar bounds, duplicate rollback and borrowed handles')
            print('PASS R21 mutation and ownership negatives',flush=True)
            before=state();other=state('candidate_other')
            assert b'User info have been updated' in post({'firstname':'Named update'},location='other')[1]
            assert state()==before and state('candidate_other')!=other
            run('docker','run','-d','--name',web_names[2],'--network',h.NETWORK,'-v',str(f)+':/fixtures',
                '-w','/fixtures/candidate/app/users','--entrypoint','php',h.IMAGE,'-d','display_errors=0',
                '-d','opcache.enable=0','-d','opcache.enable_cli=0','-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',web_names[2]);second='http://'+ip+':8080/'
            lock="""require '/fixtures/candidate/app/common/includes/config_read.php';require '/fixtures/candidate/app/users/library/portal_pages_pdo.php';
$p=dalo_portal_handle($configValues);$p->beginTransaction();$p->exec('UPDATE custom_userinfo SET changeuserinfo=0 WHERE id=10');
echo "LOCKED\n";flush();fgets(STDIN);$p->commit();"""
            blocker=subprocess.Popen(['docker','exec','-i',web_names[2],'php','-r',lock],stdin=subprocess.PIPE,
                stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
            assert blocker.stdout.readline().strip()=='LOCKED'
            run('docker','exec',web_names[2],'php','/fixtures/session.php',sessions['candidate',0,'default'],'Alice','default','candidate')
            token=Forms(req('pref-userinfo-edit.php',base=second)[1].decode()).csrf()
            with concurrent.futures.ThreadPoolExecutor(1) as pool:
                future=pool.submit(req,'pref-userinfo-edit.php',data={'firstname':'Stale permission','csrf_token':token},base=second)
                deadline=time.monotonic()+20;observed=False
                while time.monotonic()<deadline:
                    if int(db('SELECT COUNT(*) FROM INFORMATION_SCHEMA.INNODB_LOCK_WAITS'))>0:observed=True;break
                    if future.done():break
                    time.sleep(.1)
                if not observed:
                    blocker.communicate('\n',timeout=20)
                assert observed and not future.done(),'Expected blocked portal writer'
                blocker.communicate('\n',timeout=20);assert blocker.returncode==0
                assert b'not allowed' in future.result(timeout=30)[1]
            assert db('SELECT firstname FROM custom_userinfo WHERE id=10')=='Both changed'
            db('UPDATE custom_userinfo SET changeuserinfo=1 WHERE id=10')
            checks.append('named update and observed blocked permission revocation')
            preference=f/'candidate/app/users/pref-userinfo-edit.php';original=preference.read_text()
            preference.write_text(original.replace('dalo_portal_update_userinfo($portalPdo, $configValues, $login_user, $_POST);',
                "dalo_portal_update_userinfo($portalPdo, $configValues, $login_user, $_POST); $configValues['CONFIG_DB_TBL_DALOUSERINFO']='missing_table';"))
            try:
                response=post({'firstname':'Committed'})
                assert b'User info updated; display is unavailable' in response[1]
                assert db('SELECT firstname FROM custom_userinfo WHERE id=10')=='Committed'
            finally:preference.write_text(original)
            before=state()
            assert b'Something went wrong' in post({'firstname':'NUL\x00value'})[1] and state()==before
            db('ALTER TABLE custom_userinfo MODIFY country VARCHAR(3) DEFAULT NULL');before=state()
            assert b'Something went wrong' in post({'country':'overflow'})[1] and state()==before
            assert b'User info have been updated' in post({'country':'OK'})[1]
            db('ALTER TABLE custom_userinfo MODIFY country VARCHAR(100) DEFAULT NULL')
            db('ALTER TABLE custom_userinfo ENGINE=MyISAM');before=state()
            assert b'Something went wrong' in post({'firstname':'Unsupported engine'})[1] and state()==before
            db('ALTER TABLE custom_userinfo ENGINE=InnoDB')
            db('DELETE FROM custom_messages')
            assert req('login.php',user=None)[0]==200 and req('help-main.php')[0]==200
            confpath=f/'candidate/app/common/includes/daloradius.conf.php'
            db("CREATE USER 'fixture_reader'@'%' IDENTIFIED BY '';GRANT SELECT ON candidate.* TO 'fixture_reader'@'%'")
            confpath.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_USER']='fixture_reader';\n")
            for page,q in [('pref-userinfo-edit.php',None),('acct-date.php',None),('bill-invoice-report.php',None),
                ('bill-invoice-show.php',{'invoice_id':50}),('include/common/notificationsUserInvoice.php',{'invoice_id':50}),
                ('login.php',None),('help-main.php',None),('home-main.php',None)]:
                status,body,_=req(page,q,user=None if page=='login.php' else 0)
                assert status==200 and b'SQLSTATE' not in body and (b'%PDF' in body if 'notifications' in page else b'</html>' in body),('SELECT-only',page,status)
            before=state();assert b'Something went wrong' in post({'firstname':'Denied write'})[1] and state()==before
            confpath.write_text(configs['candidate'])
            for key,page,q,marker in [('CONFIG_DB_TBL_DALOHOTSPOTS','acct-date.php',None,b'Report unavailable'),
                ('CONFIG_DB_TBL_DALOPAYMENTS','bill-invoice-report.php',None,b'Report unavailable'),
                ('CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS','bill-invoice-show.php',{'invoice_id':50},b'Invoice unavailable'),
                ('CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS','include/common/notificationsUserInvoice.php',{'invoice_id':50},b'Invoice unavailable'),
                ('CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS','bill-invoice-report.php',None,b'Report unavailable'),
                ('CONFIG_DB_TBL_DALOUSERINFO','pref-userinfo-edit.php',None,b'Something went wrong'),
                ('CONFIG_DB_TBL_DALOMESSAGES','login.php',None,b'Portal message unavailable'),
                ('CONFIG_DB_TBL_DALOMESSAGES','help-main.php',None,b'Portal message unavailable'),
                ('CONFIG_DB_TBL_DALOMESSAGES','home-main.php',None,b'Portal message unavailable')]:
                confpath.write_text(configs['candidate']+"\n$configValues['"+key+"']='missing_table';\n")
                status,body,_=req(page,q,user=None if page=='login.php' else 0)
                assert marker in body and b'SQLSTATE' not in body and b'%PDF' not in body,('Sanitized SQL error',page,status)
                if page in ('acct-date.php','bill-invoice-report.php'):assert req('include/management/fileExport.php')[0]==400
            confpath.write_text(configs['candidate']+"\n$configValues['CONFIG_DB_TBL_DALOUSERINFO']='bad;identifier';\n")
            assert b'Something went wrong' in req('pref-userinfo-edit.php')[1];confpath.write_text(configs['candidate'])
            confpath.write_text(configs['candidate']+"\n$configValues['CONFIG_DEBUG_SQL_ONPAGE']='yes';\n")
            response=post({'firstname':'private-bound-value'})
            assert b'User info have been updated' in response[1]
            debug=response[1].split(b'Debugging SQL Queries:',1)[-1]
            assert b'private-bound-value' not in debug and b':username' in debug and b"username='Alice'" not in debug
            confpath.write_text(configs['candidate'])
            checks.append('engine refusal, missing messages, SELECT-only grants and sanitized late SQL/config errors')
            for web in web_names:
                r=subprocess.run(['docker','logs',web],capture_output=True,text=True,check=True);logs=r.stdout+r.stderr
                problems=re.findall(r'PHP (?:Warning|Fatal error|Notice|Deprecated):[^\n]*',logs)
                validation=subprocess.check_output(['git','show',BASE+':app/common/includes/validation.php'],cwd=ROOT,text=True)
                known=set()
                for number,line in enumerate(validation.splitlines(),1):
                    match=re.search(r"define\(['\"]([A-Z_]+)['\"]",line)
                    if match:known.add('PHP Warning:  Constant '+match[1]+' already defined in /fixtures/base/app/common/includes/validation.php on line '+str(number))
                unexpected=[p for p in problems if not (web.endswith('-base') and p in known)]
                assert not unexpected,('PHP log gate',web,len(unexpected))
                if problems:print('CHARACTERIZED baseline validation redeclarations',len(problems),flush=True)
            assert comparisons and all(comparisons.values())
            print('PASS R21',len(comparisons),'PEAR/PDO comparisons;',len(checks),'candidate families; PDF and log gate',flush=True)
        finally:
            if blocker is not None and blocker.poll() is None:
                try:blocker.communicate('\n',timeout=10)
                except subprocess.TimeoutExpired:blocker.kill();blocker.communicate()
            if f.exists():run('docker','run','--rm','--network','none','-v',str(f)+':/fixtures','--entrypoint','sh',h.IMAGE,
                '-c','chmod -R a+rwX /fixtures',check=False)
            for web in web_names:run('docker','rm','-f',web,check=False)
            run('docker','rm','-f',h.DB,check=False);run('docker','network','rm',h.NETWORK,check=False)
    assert not any(PREFIX in n for n in run('docker','ps','-a','--format','{{.Names}}').splitlines())
    assert not any(PREFIX in n for n in run('docker','network','ls','--format','{{.Name}}').splitlines())
    print('PASS R21 fixture cleanup',flush=True)
if __name__=='__main__':main()
