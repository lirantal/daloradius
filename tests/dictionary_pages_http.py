#!/usr/bin/env python3
"""R06: actual pinned PEAR/PDO dictionary pages and JSON on isolated PHP/MariaDB."""
import concurrent.futures, json, os, re, secrets, shutil, subprocess, tempfile, urllib.parse, urllib.request, urllib.error
from html.parser import HTMLParser
from pathlib import Path
import user_actions_http as h
from acct_maintenance_http import Forms
ROOT=Path(__file__).resolve().parents[1]
BASE='603a33bdc24258d822d63d560d649c6881d16a5a'
SUFFIX=secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=['pdo-r06-'+k+'-'+SUFFIX for k in ('db','web','net')]
run,sql,wait_for=h.run,h.sql,h.wait_for
PAGES=['mng-rad-attributes-'+kind+'.php' for kind in ('new','edit','del','list','search')]+['library/ajax/attributes.php','library/ajax/vendor_attribute_info.php']
PERMS=['mng_rad_attributes_'+kind for kind in ('new','edit','del','list','search')]+['mng_new','mng_edit','mng_batch_add','mng_rad_profiles_new','mng_rad_profiles_edit','mng_rad_groupcheck_new','mng_rad_groupreply_new','mng_new_quick','mng_import_users','bill_pos_new','mng_rad_groupcheck_edit','mng_rad_groupreply_edit']

class Rows(HTMLParser):
    def __init__(self, html):
        super().__init__(convert_charrefs=True);self.rows=[];self.row=None;self.cell=None;self.links=[];self.feed(html)
    def handle_starttag(self,tag,attrs):
        a=dict(attrs)
        if tag=='a' and 'href' in a:self.links.append(a['href'])
        if tag=='tr':self.row={'selected':None,'cells':[]}
        if self.row is not None:
            if tag=='td':self.cell=[]
            if tag=='input' and a.get('name')=='vendor__attribute[]':self.row['selected']=a.get('value')
    def handle_data(self,data):
        if self.cell is not None:self.cell.append(data)
    def handle_endtag(self,tag):
        if tag=='td' and self.row is not None and self.cell is not None:
            self.row['cells'].append(' '.join(''.join(self.cell).split()));self.cell=None
        if tag=='tr' and self.row is not None:
            if self.row['selected'] is not None:self.rows.append((self.row['selected'],self.row['cells']))
            self.row=None

def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='pdo-r06-',dir=scratch) as tmp:
        f=Path(tmp)
        for version in ('base','candidate'):
            shutil.copytree(ROOT/'app',f/version/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if version=='base':
                for rel in PAGES:
                    (f/version/'app/operators'/rel).write_bytes(subprocess.check_output(['git','show',BASE+':app/operators/'+rel],cwd=ROOT))
            conf=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,value in {'CONFIG_DB_HOST':h.DB,'CONFIG_DB_USER':'root','CONFIG_DB_PASS':'','CONFIG_DB_NAME':version,'CONFIG_IFACE_TABLES_LISTING':'2','CONFIG_IFACE_AUTO_COMPLETE':'yes'}.items():
                conf+='\n$configValues['+repr(key)+']='+repr(value)+';\n'
            conf+="\n$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>'"+h.DB+"','Username'=>'root','Password'=>'','Database'=>'"+version+"_other','Port'=>'3306');\n"
            (f/version/'app/common/includes/daloradius.conf.php').write_text(conf)
            (f/version/'app/operators/widget.php').write_text("<?php include 'library/checklogin.php';include_once '../common/includes/config_read.php';include_once 'lang/main.php';include_once '../common/includes/validation.php';include_once '../common/includes/layout.php';$logDebugSQL='';include 'include/management/attributes.php';")
        # Session fixture contains no persisted login material; the identifier is generated in memory.
        (f/'session.php').write_text("<?php session_name('daloradius_operator_sid');session_id($argv[1]);session_start();$_SESSION=['daloradius_logged_in'=>true,'operator_id'=>(int)($argv[3]??9001),'operator_user'=>bin2hex(random_bytes(12)),'location_name'=>($argv[2]??'default'),'time'=>time()];session_write_close();")
        (f/'candidate/app/operators/borrowed.php').write_text("<?php include 'library/checklogin.php';include_once '../common/includes/config_read.php';require_once 'library/dictionary_pages_pdo.php';$pdo=dalo_pdo_connect($configValues,$_SESSION['location_name']??'default');$pdo->beginTransaction();$pdo->exec(\"INSERT INTO dictionary (Type,Vendor,Attribute) VALUES ('string','Caller','EarlierWrite')\");$rejected=false;try {dalo_dictionary_mutate($pdo,$configValues,function(){return true;});}catch(LogicException $e){$rejected=true;}$active=$pdo->inTransaction();$pdo->rollBack();echo json_encode(['rejected'=>$rejected,'active'=>$active]);")
        try:
            run('docker','network','create','--internal',h.NETWORK)
            run('docker','run','-d','--name',h.DB,'--network',h.NETWORK,'--tmpfs','/var/lib/mysql','-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            def db(q,version='candidate'):
                p=subprocess.run(['docker','exec','-i',h.DB,'mariadb','-uroot','-N','-B',version],input="SET SESSION sql_mode=''; "+q,text=True,capture_output=True)
                if p.returncode:raise RuntimeError('Fixture SQL error codes '+repr(re.findall(r'ERROR (\d+)',p.stderr)))
                return p.stdout.strip()
            seed="""INSERT INTO dictionary(id,Type,Vendor,Attribute,Value,Format,RecommendedOP,RecommendedTable,RecommendedHelper,RecommendedTooltip) VALUES
              (100,'string','VendorA','Alpha','','',':=','check','','alpha description'),
              (101,'integer','VendorA','Enum','','',':=','reply','','enum description'),
              (102,'integer','VendorA','Enum','1','one',NULL,NULL,NULL,NULL),
              (103,'integer','VendorA','Enum','2','two',NULL,NULL,NULL,NULL),
              (104,'string','VendorB','Beta',NULL,NULL,'==','check','authtype','beta description'),
              (105,'date','VendorB','Calendar','','',':=','reply','date','calendar'),
              (106,'string','VendorB','Choice','','',':=','reply','servicetype','choice'),
              (107,'','VendorB','Empty','','',NULL,NULL,NULL,NULL),
              (108,NULL,'VendorB','Structural','','',NULL,NULL,NULL,NULL);"""
            for version in ('base','candidate','base_other','candidate_other'):
                sql('CREATE DATABASE '+version)
                for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                    db((ROOT/'contrib/db'/name).read_text(),version)
                db('\n'.join((ROOT/'contrib/db/mariadb-daloradius-dictionaries.sql').read_text().splitlines()[:21]),version)
                acl=','.join("(9001,'"+p+"',1)" for p in PERMS)
                db('INSERT INTO operators_acl(operator_id,file,access) VALUES '+acl+"; INSERT INTO operators_acl(operator_id,file,access) SELECT 9002,file,0 FROM operators_acl WHERE operator_id=9001;"+seed,version)
            for rel in ('db_open.php','db_close.php'):
                (f/'candidate/app/common/includes'/rel).write_text("<?php throw new RuntimeException('Legacy database tripwire');")
            run('docker','run','-d','--name',h.WEB,'--network',h.NETWORK,'-v',str(f)+':/fixtures','-w','/fixtures','-e','PHP_CLI_SERVER_WORKERS=4','--entrypoint','php','lirantal/daloradius','-d','opcache.enable_cli=0','-d','opcache.enable=0','-d','display_errors=0','-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',h.WEB)
            sid=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',sid)
            def req(page,data=None,version='candidate',query=None,session_id=None):
                url='http://'+ip+':8080/'+version+'/app/operators/'+page
                if query:url+='?'+urllib.parse.urlencode(query,doseq=True)
                request=urllib.request.Request(url,data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),headers={'Cookie':'daloradius_operator_sid='+(session_id or sid)})
                try:
                    with urllib.request.urlopen(request,timeout=30) as response:return response.status,response.read().decode()
                except urllib.error.HTTPError as e:return e.code,e.read().decode()
            new,edit,delete,listpage,search=PAGES[:5];ajax,tooltip=PAGES[5:]
            wait_for(lambda:req(new),'PHP')
            def token(version='candidate',session_id=None):
                return next(form['csrf_token'] for form in Forms(req(new,version=version,session_id=session_id)[1]).forms if 'csrf_token' in form)
            def post(page,fields,version='candidate',session_id=None):
                return req(page,dict(fields,csrf_token=token(version,session_id)),version,session_id=session_id)[1]
            def fields(attribute='Created',vendor='VendorC',**extra):
                return dict({'vendor':vendor,'attribute':attribute,'type':'string encrypt=1','RecommendedOP':':=','RecommendedTable':'reply','RecommendedHelper':'date','RecommendedTooltip':'created description'},**extra)
            def state(version='candidate',table='dictionary'):
                return db('SELECT id,Type,Vendor,Attribute,Value,Format,RecommendedOP,RecommendedTable,RecommendedHelper,RecommendedTooltip FROM '+table+' ORDER BY id',version)
            def js(query,version='candidate',page=None,session_id=None):
                status,body=req(page or ajax,version=version,query=query,session_id=session_id)
                return status,json.loads(body)
            for version in ('base','candidate'):
                assert 'has been inserted' in post(new,fields(),version)
                assert 'has been updated' in post(edit,fields(type='integer'),version)
                # Plain tokens characterize a genuinely functioning PEAR delete path.
                assert 'Deleted' in post(delete,{'vendor__attribute[]':['VendorC__Created']},version)
            assert state('base')==state(),'ordinary full physical CRUD parity'
            comparisons=0
            for page in (listpage,search):
                for order in ('id','vendor','attribute'):
                    for direction in ('asc','desc'):
                        for page_num in (1,2,3,4):
                            q={'orderBy':order,'orderType':direction,'page':str(page_num)}
                            left=Rows(req(page,version='base',query=q)[1]).rows
                            right=Rows(req(page,query=q)[1]).rows
                            assert left==right,(page,order,direction,page_num,'row parity');comparisons+=1
                for q in ({'vendor':'VendorA'} if page==listpage else {'attribute':'Enum'}, {'vendor':'notfound'} if page==listpage else {'attribute':'notfound'}):
                    assert Rows(req(page,version='base',query=q)[1]).rows==Rows(req(page,query=q)[1]).rows;comparisons+=1
            formkeys=('vendor','attribute','type','recommendedOP','recommendedTable','recommendedHelper','recommendedTooltip')
            lf=Forms(req(edit,version='base',query={'vendor':'VendorA','attribute':'Alpha'})[1]).forms
            rf=Forms(req(edit,query={'vendor':'VendorA','attribute':'Alpha'})[1]).forms
            assert [{k:x.get(k) for k in formkeys} for x in lf]==[{k:x.get(k) for k in formkeys} for x in rf];comparisons+=1
            for q in [{'getVendorsList':'yes','parentPage':'mng-new'}, {'vendorAttributes':'VendorA','parentPage':'mng-new'}, {'vendorAttributes':'','parentPage':'mng-new'}]+[{'getValuesForAttribute':a,'parentPage':'mng-new'} for a in ('Alpha','Enum','Beta','Calendar','Choice','Structural','Absent')]:
                assert js(q,'base')==js(q);comparisons+=1
            for a in ('Alpha','Absent'):
                assert js({'attribute':a},'base',tooltip)==js({'attribute':a},page=tooltip);comparisons+=1
            assert Forms(req('widget.php',version='base')[1]).forms==Forms(req('widget.php')[1]).forms;comparisons+=1
            print('PASS native PEAR/PDO ordinary CRUD and',comparisons,'list/form/JSON/widget comparisons',flush=True)
            before=state()
            for bad in ({'vendor[]':['VendorC'],'attribute':'Bad'}, {'vendor':'VendorC','attribute[]':['Bad']}, fields(attribute='X'*65), fields(vendor='V'*33),dict(fields(), **{'type[]':['string']}),fields(RecommendedTooltip='X'*513)):
                assert 'Could not add' in post(new,bad);assert state()==before
            # Creation keeps the historical global attribute-name collision rule, even across vendors.
            assert 'already present' in post(new,fields(attribute='Alpha',vendor='DifferentVendor'));assert state()==before
            assert 'no longer exists' in post(edit,fields(attribute='Absent'));assert state()==before
            print('PASS invalid and oversize values, global collisions and stale edits',flush=True)
            db("CREATE TRIGGER fail_insert BEFORE INSERT ON dictionary FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='private fixture marker'")
            html=post(new,fields(attribute='InsertFailure'))
            assert 'Could not add' in html and 'SQLSTATE' not in html and 'private fixture marker' not in html
            assert state()==before
            db('DROP TRIGGER fail_insert')
            assert 'has been inserted' in post(new,fields(attribute='InsertRetry'))
            assert 'Deleted' in post(delete,{'vendor__attribute[]':['VendorC__InsertRetry']})
            # The actual edit form emits lower-case recommendation names; old POST parsed upper-case only.
            for version in ('base','candidate'):
                assert 'has been updated' in post(edit,{'vendor':'VendorA','attribute':'Alpha','type':'integer','recommendedOP':'==','recommendedTable':'reply','recommendedHelper':'date','recommendedTooltip':'lowercase producer'},version)
            assert db("SELECT RecommendedOP FROM dictionary WHERE id=100",'base')==''
            assert db("SELECT RecommendedOP FROM dictionary WHERE id=100")=='=='
            assert 'has been updated' in post(edit,{'vendor':'VendorA','attribute':'Alpha','type':'integer','recommendedOP':'==','recommendedTable':'reply','recommendedHelper':'date','recommendedTooltip':'lowercase producer'})
            enum_before=db("SELECT id,Value,Format FROM dictionary WHERE Attribute='Enum' ORDER BY id")
            assert 'has been updated' in post(edit,fields(attribute='Enum',vendor='VendorA'))
            assert db("SELECT id,Value,Format FROM dictionary WHERE Attribute='Enum' ORDER BY id")==enum_before
            print('PASS characterized lower-case form repair, idempotent edit and preserved enumeration Value/Format',flush=True)
            # Whole multi-row UPDATE and whole deletion selection must roll back on a later trigger failure.
            before=state()
            db("\nDELIMITER $$\nCREATE TRIGGER fail_update BEFORE UPDATE ON dictionary FOR EACH ROW BEGIN IF OLD.id=103 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='private fixture marker'; END IF; END$$\nDELIMITER ;\n")
            assert 'Could not' in post(edit,fields(attribute='Enum',vendor='VendorA',type='octets'));assert state()==before
            db('DROP TRIGGER fail_update')
            db("\nDELIMITER $$\nCREATE TRIGGER fail_delete BEFORE DELETE ON dictionary FOR EACH ROW BEGIN IF OLD.id=104 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='private fixture marker'; END IF; END$$\nDELIMITER ;\n")
            assert 'Could not' in post(delete,{'vendor__attribute[]':['VendorA__Alpha','VendorB__Beta']});assert state()==before
            db('DROP TRIGGER fail_delete')
            for selection in (['VendorA__Alpha','Broken__Too__Many'],['VendorA__Alpha','VendorZ__Stale'],['VendorA__Alpha',{'bad':'shape'}],['VendorA__Alpha','bad%xx__attr']):
                assert 'Could not' in post(delete,{'vendor__attribute[]':selection});assert state()==before
            assert 'Could not' in post(delete,{'vendor__attribute[0]':'VendorA__Alpha','vendor__attribute[1][bad]':'nested'});assert state()==before
            # Real PHP truncation with CSRF before the oversized selection.
            payload={'csrf_token':token(),'vendor__attribute[]':['VendorA__Alpha']*1100}
            assert 'Could not' in req(delete,payload)[1];assert state()==before
            db('ALTER TABLE dictionary ENGINE=MyISAM')
            assert 'Could not' in post(new,fields());assert state()==before
            db('ALTER TABLE dictionary ENGINE=InnoDB')
            assert js({},page='borrowed.php')==(200,{'rejected':True,'active':True});assert state()==before
            print('PASS full-state late UPDATE/DELETE rollback, stale/malformed/truncated selection, engine preflight and borrowed transaction protection',flush=True)
            # Values are raw identities in URL/checkbox transports, not HTML-escaped names.
            special=[('V &"%+é','A <>&"%+é'),('Vendor__Part','Attribute__Part'),('0','0'),('dict:Vendor','DictAttribute')]
            for vendor,attribute in special:
                html=post(new,fields(attribute=attribute,vendor=vendor,RecommendedTooltip='0'))
                assert 'has been inserted' in html
                links=[u for u in Rows(html).links if u.startswith('mng-rad-attributes-edit.php?')]
                assert len(links)==1
                q=urllib.parse.parse_qs(urllib.parse.urlsplit(links[0]).query);assert q=={'vendor':[vendor],'attribute':[attribute]}
                assert req(edit,query={'vendor':vendor,'attribute':attribute})[0]==200
                if vendor=='0':assert Forms(req(edit,query={'vendor':vendor,'attribute':attribute})[1]).forms
                html=req(search,query={'attribute':attribute,'orderBy':'id'})[1]
                rows=Rows(html).rows
                # Search strips percent historically; use complete vendor selector for a percent-bearing raw identity.
                if '%' in attribute:rows=Rows(req(delete)[1]).rows
                if '%' not in attribute:
                    picked=[x for x in rows if x[1][-1]==attribute];assert picked
                    selected=picked[0][0]
                else:
                    # Pull the real deletion option value from the HTML, no hand-invented encoding.
                    class Options(HTMLParser):
                        def __init__(self,s):super().__init__(convert_charrefs=True);self.options=[];self.feed(s)
                        def handle_starttag(self,tag,attrs):
                            if tag=='option':self.options.append(dict(attrs).get('value'))
                    opts=Options(req(delete)[1]).options
                    selected=next(o for o in opts if o==urllib.parse.quote_plus(vendor)+'__'+urllib.parse.quote_plus(attribute))
                assert 'Deleted 1 dictionary row' in post(delete,{'vendor__attribute[]':[selected]})
                assert db("SELECT COUNT(*) FROM dictionary WHERE Attribute='"+attribute.replace("'","''")+"'")=='0'
            # Deleting one key with duplicate/enum physical rows removes all, with accurate physical count.
            assert 'Deleted 3 dictionary row(s) in 1 vendor/attribute(s)' in post(delete,{'vendor__attribute[]':['VendorA__Enum','VendorA__Enum']})
            print('PASS raw special/delimiter/zero identities through real links/options, metadata zero and actual duplicate deletion counts',flush=True)
            # Separate sessions let PHP worker concurrency reach the database rather than the PHP session mutex.
            sid2=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',sid2)
            tokens={s:token(session_id=s) for s in (sid,sid2)}
            def concurrent_create(s):return req(new,dict(fields(attribute='Concurrent'),csrf_token=tokens[s]),session_id=s)[1]
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:results=list(pool.map(concurrent_create,(sid,sid2)))
            assert sum('has been inserted' in x for x in results)==1
            assert sum('already present' in x for x in results)==1
            assert db("SELECT COUNT(*) FROM dictionary WHERE Attribute='Concurrent'")=='1'
            tokens={s:token(session_id=s) for s in (sid,sid2)}
            def concurrent_delete(s):return req(delete,{'vendor__attribute[]':['VendorC__Concurrent'],'csrf_token':tokens[s]},session_id=s)[1]
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:results=list(pool.map(concurrent_delete,(sid,sid2)))
            assert sum('Deleted 1 dictionary row' in x for x in results)==1
            assert sum('Could not delete dictionary selection' in x for x in results)==1
            assert db("SELECT COUNT(*) FROM dictionary WHERE Attribute='Concurrent'")=='0'
            assert 'has been inserted' in post(new,fields(attribute='ConcurrentEdit'))
            db("INSERT INTO dictionary(Type,Vendor,Attribute,Value,Format) VALUES ('string','VendorC','ConcurrentEdit','unchanged-enum','unchanged-format')")
            immutable=db("SELECT id,Value,Format FROM dictionary WHERE Attribute='ConcurrentEdit' ORDER BY id")
            tokens={s:token(session_id=s) for s in (sid,sid2)}
            def concurrent_edit(s):
                data=fields(attribute='ConcurrentEdit',type='byte' if s==sid else 'short',RecommendedTooltip='first' if s==sid else 'second')
                return req(edit,dict(data,csrf_token=tokens[s]),session_id=s)[1]
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:results=list(pool.map(concurrent_edit,(sid,sid2)))
            assert all('has been updated' in result for result in results), [('csrf' if 'CSRF' in x else 'missing' if 'no longer exists' in x else 'database' if 'Could not' in x else 'other') for x in results]
            final=db("SELECT DISTINCT Type,RecommendedTooltip FROM dictionary WHERE Attribute='ConcurrentEdit'")
            assert final in ('byte\tfirst','short\tsecond')
            assert db("SELECT id,Value,Format FROM dictionary WHERE Attribute='ConcurrentEdit' ORDER BY id")==immutable
            before=state()
            for page,body in [(new,fields()),(edit,fields(attribute='Alpha',vendor='VendorA')),(delete,{'vendor__attribute[]':['VendorA__Alpha']})]:
                assert 'CSRF token error' in req(page,dict(body, **{'csrf_token[]':['bad']}))[1];assert state()==before
            denied=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',denied,'default','9002')
            for page in PAGES[:5]:
                html=req(page,session_id=denied)[1];assert 'csrf_token' not in html and not Rows(html).rows
            assert req(ajax,query={'parentPage':'mng-new','getVendorsList':'yes'},session_id=denied)[0]==403
            assert req(tooltip,query={'attribute':'Alpha'},session_id=denied)[0]==403
            assert js({'parentPage':'bogus','getVendorsList':'yes'})[0]==400
            assert js({'parentPage':'mng-new','getValuesForAttribute[]':['Alpha']})[0]==400
            for parent in ('mng-new-quick','mng-import-users','bill-pos-new','mng-rad-groupcheck-edit','mng-rad-groupreply-edit'):
                assert js({'parentPage':parent,'getVendorsList':'yes'})[0]==200
                assert req(ajax,query={'parentPage':parent,'getVendorsList':'yes'},session_id=denied)[0]==403
            assert req(ajax,{'parentPage':'mng-new'})[0] in (400,405)
            print('PASS real concurrent creation/edit/deletion, ACL/CSRF and extended existing-widget parent authorization',flush=True)
            other=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',other,'other')
            default_before=state()
            assert 'has been inserted' in post(new,fields(attribute='OtherOnly'),session_id=other)
            assert 'has been updated' in post(edit,fields(attribute='OtherOnly',type='integer'),session_id=other)
            assert js({'parentPage':'mng-new','getValuesForAttribute':'OtherOnly'},session_id=other)[1]['type']=='integer'
            assert state()==default_before
            assert 'OtherOnly' in req('widget.php',session_id=other)[1]
            assert 'OtherOnly' not in req('widget.php')[1]
            assert Rows(req(search,query={'attribute':'OtherOnly'},session_id=other)[1]).rows
            assert js({'attribute':'OtherOnly'},page=tooltip,session_id=other)[1]['description']=='created description'
            assert 'Deleted' in post(delete,{'vendor__attribute[]':['VendorC__OtherOnly']},session_id=other)
            configfile=f/'candidate/app/common/includes/daloradius.conf.php';conf=configfile.read_text()
            db('RENAME TABLE dictionary TO custom_dictionary');conf+="\n$configValues['CONFIG_DB_TBL_DALODICTIONARY']='custom_dictionary';\n";configfile.write_text(conf)
            assert 'has been inserted' in post(new,fields(attribute='Renamed'))
            assert 'has been updated' in post(edit,fields(attribute='Renamed',type='octets'))
            assert Rows(req(listpage)[1]).rows and Rows(req(search,query={'attribute':'Renamed'})[1]).rows
            assert js({'parentPage':'mng-new','getValuesForAttribute':'Renamed'})[1]['type']=='octets'
            assert js({'attribute':'Renamed'},page=tooltip)[1]['description']=='created description'
            assert req('widget.php')[0]==200
            assert 'Deleted' in post(delete,{'vendor__attribute[]':['VendorC__Renamed']})
            configfile.write_text(conf+"\n$configValues['CONFIG_DEBUG_SQL_ONPAGE']='yes';\n")
            html=post(new,fields(attribute='DebugSentinel'))
            assert 'has been inserted' in html
            debug=html.split('Debugging SQL Queries:',1)[1]
            assert 'DebugSentinel' not in debug and 'created description' not in debug
            assert 'bound values' in debug
            html=req(search,query={'attribute':'DebugSentinel'})[1]
            debug=html.split('Debugging SQL Queries:',1)[1]
            assert 'DebugSentinel' not in debug and ':filter' in debug
            assert 'Deleted' in post(delete,{'vendor__attribute[]':['VendorC__DebugSentinel']})
            before=state(table='custom_dictionary')
            configfile.write_text(conf+"\n$configValues['CONFIG_DB_TBL_DALODICTIONARY']='custom_dictionary;invalid';\n")
            assert 'Could not' in post(new,fields());assert state(table='custom_dictionary')==before
            assert js({'parentPage':'mng-new','getVendorsList':'yes'})[0]==400
            configfile.write_text(conf)
            db('RENAME TABLE custom_dictionary TO missing_dictionary')
            for page in (listpage,search):
                html=req(page)[1];assert 'Could not load' in html and 'SQLSTATE' not in html
            for page,q in [(ajax,{'parentPage':'mng-new','getValuesForAttribute':'Alpha'}),(tooltip,{'attribute':'Alpha'})]:
                status,data=js(q,page=page);assert status==500 and data=={'error':'Unable to load attribute information.'}
            db('RENAME TABLE missing_dictionary TO custom_dictionary')
            diagnostics=subprocess.run(['docker','logs',h.WEB],text=True,capture_output=True,check=True)
            logs=diagnostics.stdout+diagnostics.stderr
            assert not any('/fixtures/candidate/' in line and any(mark in line for mark in ('PHP Warning:','PHP Fatal error:','PHP Deprecated:')) for line in logs.splitlines()),'Candidate PHP diagnostic (details suppressed)'
            assert 'private fixture marker' not in logs and 'SQLSTATE' not in logs
            print('PASS selected location, configured/invalid tables, sanitized failures, native widget and legacy-open/close tripwires; clean script diagnostics',flush=True)
        finally:
            run('docker','rm','-f',h.WEB,h.DB,check=False);run('docker','network','rm',h.NETWORK,check=False)
            subprocess.run(['sudo','-n','chown','-R',str(os.getuid())+':'+str(os.getgid()),str(f)],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
            assert not [n for n in run('docker','ps','-a','--format','{{.Names}}').splitlines() if n in (h.WEB,h.DB)]
            assert h.NETWORK not in run('docker','network','ls','--format','{{.Name}}').splitlines()
            print('CLEANUP R06 fixture containers/network removed',flush=True)
if __name__=='__main__':main()
