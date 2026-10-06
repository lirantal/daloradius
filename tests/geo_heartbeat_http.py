#!/usr/bin/env python3
"""R10: native pinned PEAR/PDO hotspot CRUD, JSON/charts, atomicity and isolation."""
import concurrent.futures,hashlib,json,os,re,secrets,shutil,subprocess,tempfile,time,urllib.parse,urllib.request,urllib.error
from html.parser import HTMLParser
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap
import user_actions_http as h
from acct_maintenance_http import Forms as InputForms
ROOT=Path(__file__).resolve().parents[1]
BASE='95f58a27e9136353358dc1a65761cdf2ad0c4fb2'
SUFFIX=secrets.token_hex(5)
h.DB,h.WEB,h.NETWORK=['pdo-r10-'+k+'-'+SUFFIX for k in ('db','web','net')]
run,sql,wait_for=h.run,h.sql,h.wait_for
PAGES=['gis-editmap.php','gis-viewmap.php']
ENDPOINTS=['heartbeat.php']
class Forms(InputForms):
    """Capture textareas too: the native hotspot form stores address there."""
    def __init__(self,html):
        self.textarea=None;self.text=[];super().__init__(html)
    def handle_starttag(self,tag,attrs):
        super().handle_starttag(tag,attrs);a=dict(attrs)
        if tag=='textarea' and self.current is not None:
            self.textarea=a.get('name');self.text=[]
    def handle_data(self,data):
        if self.textarea is not None:self.text.append(data)
    def handle_endtag(self,tag):
        if tag=='textarea':
            if self.current is not None and self.textarea is not None:self.current[self.textarea]=''.join(self.text)
            self.textarea=None;self.text=[]
        super().handle_endtag(tag)
class Rows(HTMLParser):
    def __init__(self,html):
        super().__init__(convert_charrefs=True);self.rows=[];self.row=None;self.cell=None;self.links=[];self.options=[];self.feed(html)
    def handle_starttag(self,tag,attrs):
        a=dict(attrs)
        if tag=='a' and 'href' in a:self.links.append(a['href'])
        if tag=='option':self.options.append(a)
        if tag=='tr':self.row={'selected':None,'cells':[]}
        if self.row is not None:
            if tag=='td':self.cell=[]
            if tag=='input' and a.get('name')=='name[]':self.row['selected']=a.get('value')
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
    with tempfile.TemporaryDirectory(prefix='pdo-r10-',dir=scratch) as tmp:
        f=Path(tmp);hbkey=secrets.token_hex(32)
        for version in ('base','candidate'):
            shutil.copytree(ROOT/'app',f/version/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
            if version=='base':
                restore_pear_bootstrap((f / version / 'app').parent, BASE)
                for rel in PAGES+ENDPOINTS:
                    (f/version/'app/operators'/rel).write_bytes(subprocess.check_output(['git','show',BASE+':app/operators/'+rel],cwd=ROOT))
            conf=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            for key,value in {'CONFIG_DB_HOST':h.DB,'CONFIG_DB_USER':'root','CONFIG_DB_PASS':'','CONFIG_DB_NAME':version,'CONFIG_IFACE_TABLES_LISTING':'2'}.items():
                conf+='\n$configValues['+repr(key)+']='+repr(value)+';\n'
            conf+="\n$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>'"+h.DB+"','Username'=>'root','Password'=>'','Database'=>'"+version+"_other','Port'=>'3306');\n"
            conf+="\n$configValues['CONFIG_DASHBOARD_DALO_SECRETKEY']='"+hbkey+"';\n"
            (f/version/'app/common/includes/daloradius.conf.php').write_text(conf)
        (f/'session.php').write_text("<?php session_name('daloradius_operator_sid');session_id($argv[1]);session_start();$_SESSION=['daloradius_logged_in'=>true,'operator_id'=>(int)($argv[3]??9001),'operator_user'=>bin2hex(random_bytes(12)),'location_name'=>($argv[2]??'default'),'time'=>time()-(int)($argv[4]??0)];session_write_close();")
        (f/'candidate/app/operators/borrowed.php').write_text("<?php include 'library/checklogin.php';include_once '../common/includes/config_read.php';require_once 'library/geo_heartbeat_pdo.php';$logDebugSQL='';$pdo=dalo_pdo_connect($configValues,$_SESSION['location_name']??'default');$pdo->beginTransaction();$pdo->exec(\"INSERT INTO hotspots(name,mac) VALUES ('Caller','02:00:00:00:00:FE')\");$rejected=false;try {dalo_hotspot_mutate($pdo,$configValues,function(){return true;});}catch(LogicException $e){$rejected=true;}$nodeRejected=false;try{dalo_heartbeat_mutate($pdo,$configValues,function(){return true;});}catch(LogicException $e){$nodeRejected=true;}$active=$pdo->inTransaction();$pdo->rollBack();echo json_encode(['rejected'=>$rejected,'heartbeat_rejected'=>$nodeRejected,'active'=>$active]);")
        try:
            run('docker','network','create','--internal',h.NETWORK)
            run('docker','run','-d','--name',h.DB,'--network',h.NETWORK,'--tmpfs','/var/lib/mysql','-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            def db(q,version='candidate'):
                p=subprocess.run(['docker','exec','-i',h.DB,'mariadb','-uroot','-N','-B',version],input="SET SESSION sql_mode=''; "+q,text=True,capture_output=True)
                if p.returncode:raise RuntimeError('Fixture SQL error codes '+repr(re.findall(r'ERROR (\d+)',p.stderr)))
                return p.stdout.rstrip('\n')
            for version in ('base','candidate','base_other','candidate_other'):
                sql('CREATE DATABASE '+version)
                for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                    db((ROOT/'contrib/db'/name).read_text(),version)
                db("INSERT INTO operators_acl(operator_id,file,access) VALUES (9001,'gis_editmap',1),(9001,'gis_viewmap',1),(9001,'mng_hs_edit',1),(9002,'gis_editmap',0),(9002,'gis_viewmap',0)",version)
                points=[('First','10.25,20.75'),('South','-12.5,-80'),('Zero','0,0'),('Empty',''),('Null',None),('Malformed','nope'),('Boundary','90,-180')]
                for i,(name,geo) in enumerate(points):
                    value='NULL' if geo is None else "'"+geo+"'"
                    db("INSERT INTO hotspots(id,name,mac,geocode,creationdate,creationby) VALUES ("+str(10+i)+",'"+name+"','02:00:00:00:00:"+str(10+i)+"',"+value+",'2000-01-01',NULL)",version)
                db("INSERT INTO userbillinfo(id,hotspot_id,planName) VALUES (201,10,'FixturePlan'); INSERT INTO radacct(username,nasipaddress,calledstationid) VALUES ('FixtureClient','198.51.100.1','02:00:00:00:00:10')",version)
            for rel in ('db_open.php','db_close.php'):
                (f/'candidate/app/common/includes'/rel).write_text("<?php throw new RuntimeException('Legacy database tripwire');")
            run('docker','run','-d','--name',h.WEB,'--network',h.NETWORK,'-v',str(f)+':/fixtures','-w','/fixtures','-e','PHP_CLI_SERVER_WORKERS=4','--entrypoint','php','lirantal/daloradius','-d','opcache.enable_cli=0','-d','opcache.enable=0','-d','display_errors=0','-S','0.0.0.0:8080','-t','.')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',h.WEB)
            sid=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',sid)
            def req(page,data=None,version='candidate',query=None,session_id=None,follow_redirects=True):
                url='http://'+ip+':8080/'+version+'/app/operators/'+page
                if query:url+='?'+urllib.parse.urlencode(query,doseq=True)
                request=urllib.request.Request(url,data=None if data is None else urllib.parse.urlencode(data,doseq=True).encode(),headers={'Cookie':'daloradius_operator_sid='+(session_id or sid)})
                class NoRedirect(urllib.request.HTTPRedirectHandler):
                    def redirect_request(self,*args,**kwargs):return None
                opener=urllib.request.build_opener() if follow_redirects else urllib.request.build_opener(NoRedirect)
                try:
                    with opener.open(request,timeout=30) as response:return response.status,response.read().decode()
                except urllib.error.HTTPError as e:return e.code,e.read().decode()
            edit,view=PAGES;new=edit
            wait_for(lambda:req(new),'PHP')
            print('RUNTIME PHP '+run('docker','exec',h.WEB,'php','-r','echo PHP_VERSION;')+'; MariaDB '+db('SELECT VERSION()'),flush=True)
            def token(version='candidate',session_id=None):
                return next(form['csrf_token'] for form in Forms(req(new,version=version,session_id=session_id)[1]).forms if 'csrf_token' in form)
            def post(page,fields,version='candidate',session_id=None):
                return req(page,dict(fields,csrf_token=token(version,session_id)),version,session_id=session_id)[1]
            def add(name='Added',geo='25.5,40.5',mac='02:00:00:00:00:80',version='candidate',session_id=None):
                return post(edit,{'type':'add','hotspotname':name,'hotspotmac':mac,'hotspotgeo':geo},version,session_id)
            def remove(id,version='candidate',session_id=None):
                return post(edit,{'type':'del','hotspotid':str(id)},version,session_id)
            def hsstate(version='candidate',table='hotspots',parity=False):
                names=[x for x in db("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='"+table+"' ORDER BY ORDINAL_POSITION",version).splitlines() if x not in ('creationdate','creationby','updatedate','updateby')]
                audit=',creationdate,creationby,updatedate,updateby' if not parity else ',creationdate IS NOT NULL,creationby IS NOT NULL,updatedate IS NOT NULL,updateby IS NOT NULL'
                return db('SELECT `'+ '`,`'.join(names)+'`'+audit+' FROM '+table+' ORDER BY id',version)
            def related(version='candidate'):
                return db('SELECT id,hotspot_id,planName FROM userbillinfo ORDER BY id',version)+'\n'+db('SELECT radacctid,username,nasipaddress,calledstationid FROM radacct ORDER BY radacctid',version)
            relations=related()
            def node_state(version='candidate',table='node',parity=False):
                cols=[x for x in db("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='"+table+"' ORDER BY ORDINAL_POSITION",version).splitlines() if x not in ('wifi_key','time')]
                clock=',time IS NOT NULL' if parity else ',time'
                return db('SELECT `'+ '`,`'.join(cols)+'`'+clock+",wifi_key IS NULL,wifi_key='' FROM "+table+' ORDER BY id',version)
            def beat(version='candidate',**extra):
                q=dict({'secret_key':hbkey,'nas_mac':'node-host','wan_iface':'eth0','wan_ip':'198.51.100.5','wifi_ssid':'FixtureNetwork','uptime':'100','memfree':'128','cpu':'12.5%'},**extra)
                return req('heartbeat.php',version=version,query=q)
            # Execute actual inline PHP-generated JavaScript with a synthetic Leaflet API.
            jsrunner=r"""const vm=require('vm');const x=JSON.parse(require('fs').readFileSync(0,'utf8'));let markers=[],center,fit=false,map;let fields={};for(const k of ['type','hotspotid','hotspotname','hotspotmac','hotspotgeo'])fields[k]={value:''};let submitted=null;fields.submit=()=>{submitted={};for(const k of Object.keys(fields))if(k!=='submit')submitted[k]=fields[k].value};const prompts=(x.prompts||[]).slice();const ctx={window:{},document:{editmaps:fields},confirm:()=>true,prompt:()=>prompts.shift(),L:{map:()=>map={handlers:{},setView(v,z){center=v;return this},on(k,f){this.handlers[k]=f;return this},fitBounds(){fit=true}},featureGroup:()=>({addTo(){return this},getBounds:()=>[]}),tileLayer:()=>({addTo(){return this}}),marker:(v,o)=>{let m={coords:v,options:o||{},handlers:{},addTo(){markers.push(this);return this},bindTooltip(v){this.tooltip=v;return this},bindPopup(v){this.popup=v;return this},on(k,f){this.handlers[k]=f;return this},remove(){}};return m}}};vm.createContext(ctx);vm.runInContext(x.code,ctx);ctx.window.onload();if(x.action==='add')map.handlers.click({latlng:{lat:-8.25,lng:-150.5}});if(x.action==='del'){let m=markers.find(m=>m.options.title===x.name);if(m)m.handlers.click({target:m})};process.stdout.write(JSON.stringify({center,fit,markers:markers.map(m=>({coords:m.coords,options:m.options,tooltip:m.tooltip,popup:m.popup})),submitted}));"""
            def leaflet(html,**options):
                script=re.search(r'(window.onload = function\(\) \{.*?\n\})\s*var tooltipTriggerList',html,re.S)
                assert script,'Actual map script missing'
                p=subprocess.run(['docker','run','--rm','--network','none','-i','node:22-alpine','node','-e',jsrunner],input=json.dumps(dict(options,code=script[1])),text=True,capture_output=True)
                assert p.returncode==0,'Generated JavaScript did not execute (details suppressed)'
                result=json.loads(p.stdout)
                for marker in result['markers']:
                    if 'id' in marker['options']:marker['options']['id']=str(marker['options']['id'])
                return result
            comparisons=0
            for page in PAGES:
                a=leaflet(req(page,version='base')[1]);b=leaflet(req(page)[1]);assert a==b and len(b['markers'])==4;comparisons+=1
                assert b['center']==[10.25,20.75] and b['fit']
            for version in ('base','candidate'):
                html=add(version=version);assert 'Added new geolocation' in html
                link=next(u for u in Rows(html).links if u.startswith('mng-hs-edit.php?name='))
                assert urllib.parse.parse_qs(urllib.parse.urlsplit(link).query)=={'name':['Added']}
                assert any(form.get('name')=='Added' for form in Forms(req(link,version=version)[1]).forms)
                id=db("SELECT id FROM hotspots WHERE name='Added'",version)
                assert 'Deleted geolocation' in remove(id,version)
            assert hsstate('base',parity=True)==hsstate(parity=True) and related()==related('base')==relations
            for geo in ('0,0','-90,180',' 10 , -20 ','90,-180'):
                assert 'Added new geolocation' in add('Coordinate',geo)
                id=db("SELECT id FROM hotspots WHERE name='Coordinate'")
                assert any(m['coords']==[float(x) for x in geo.split(',')] for m in leaflet(req(edit)[1])['markers'])
                assert 'Deleted geolocation' in remove(id)
            # Legacy GIS inserts duplicate names/MACs and deletes the complete selected row.
            for version in ('base','candidate'):
                assert 'Added new geolocation' in add('First','25,40','02:00:00:00:00:10',version)
                id=db("SELECT MAX(id) FROM hotspots WHERE name='First'",version);assert 'Deleted geolocation' in remove(id,version)
            assert hsstate('base',parity=True)==hsstate(parity=True)
            assert 'Added new geolocation' in add('InvalidLegacy','not-coordinates',version='base')
            assert db("SELECT geocode FROM hotspots WHERE name='InvalidLegacy'",'base')=='not-coordinates'
            legacyid=db("SELECT id FROM hotspots WHERE name='InvalidLegacy'",'base');remove(legacyid,'base')
            db("CREATE TRIGGER fail_legacy_add BEFORE INSERT ON hotspots FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='legacy error'",'base')
            html=add('FalseSuccess',version='base');assert 'Added new geolocation' in html and db("SELECT COUNT(*) FROM hotspots WHERE name='FalseSuccess'",'base')=='0'
            db('DROP TRIGGER fail_legacy_add','base')
            for version in ('base','candidate'):db("INSERT INTO hotspots(id,name,mac,geocode) VALUES (100,'Outside','02:00:00:00:00:FE','91,181')",version)
            assert any(m['coords']==[91,181] for m in leaflet(req(view,version='base')[1])['markers'])
            assert not any(m['coords']==[91,181] for m in leaflet(req(view)[1])['markers'])
            for version in ('base','candidate'):db('DELETE FROM hotspots WHERE id=100',version)
            print('PASS native GIS PEAR/PDO inserts/deletes/marker parity; ordinary/NULL/empty/malformed locations; pinned regex and false-success defects; retained duplicate and whole-row deletion policy',flush=True)
            before=hsstate()
            for geo in ('91,0','0,181','1e2,1','bad','1,2,3','1\0,2','0'*201+',1'):
                assert 'Unable to change' in add(geo=geo);assert hsstate()==before
            for data in ({'type[]':['add']},{'type':'add','hotspotname[]':['X'],'hotspotmac':'001122334455','hotspotgeo':'1,2'},{'type':'add','hotspotname':'X','hotspotmac[]':['001122334455'],'hotspotgeo':'1,2'},{'type':'add','hotspotname':'X','hotspotmac':'001122334455suffix','hotspotgeo':'1,2'},{'type':'add','hotspotname':'X','hotspotmac':'001122334455','hotspotgeo[]':['1,2']},{'type':'del','hotspotid[]':['10']}):
                assert 'Unable to change' in post(edit,data);assert hsstate()==before
            for id in ('missing','0','-1','01','10garbage','9223372036854775808','999'):
                assert 'Unable to change' in remove(id);assert hsstate()==before
            assert 'Unable to change' in add('X'*201);assert hsstate()==before
            for event,action in (('INSERT',lambda:add()),('DELETE',lambda:remove(10))):
                db('CREATE TRIGGER fail_write BEFORE '+event+" ON hotspots FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='private fixture marker'")
                html=action();assert 'Unable to change' in html and 'private fixture marker' not in html;assert hsstate()==before;db('DROP TRIGGER fail_write')
            db('ALTER TABLE hotspots ENGINE=MyISAM');assert 'Unable to change' in add();assert 'Unable to change' in remove(10);assert hsstate()==before;db('ALTER TABLE hotspots ENGINE=InnoDB')
            assert json.loads(req('borrowed.php')[1])=={'rejected':True,'heartbeat_rejected':True,'active':True};assert hsstate()==before
            db('ALTER TABLE hotspots CONVERT TO CHARACTER SET latin1');assert 'Unable to change' in add('Unicode😀');assert hsstate()==before;db('ALTER TABLE hotspots CONVERT TO CHARACTER SET utf8mb4')
            # Native generated JS drives the actual hidden form; Leaflet itself is stubbed.
            special="GIS &é% ' </script>"
            html=req(edit)[1];tok=next(f['csrf_token'] for f in Forms(html).forms if 'csrf_token' in f)
            rendered=leaflet(html,action='add',prompts=[special,'02:00:00:00:00:90']);controls=rendered['submitted'];assert controls['hotspotgeo']=='-8.25,-150.5'
            html=req(edit,dict(controls,csrf_token=tok))[1];assert 'Added new geolocation' in html
            assert any(urllib.parse.parse_qs(urllib.parse.urlsplit(u).query).get('name')==[special] for u in Rows(html).links if u.startswith('mng-hs-edit.php?name='))
            html=req(edit)[1];assert special not in html and '\\u003C' in html
            tok=next(f['csrf_token'] for f in Forms(html).forms if 'csrf_token' in f)
            rendered=leaflet(html,action='del',name=special,prompts=[special]);assert rendered['submitted'] and rendered['submitted']['hotspotid']
            assert 'Deleted geolocation' in req(edit,dict(rendered['submitted'],csrf_token=tok))[1];assert hsstate()==before and related()==relations
            for version in ('base','candidate'):
                assert beat(version=version)==(200,'success')
                assert beat(version=version,wan_iface='eth1',cpu='25.75%')==(200,'success')
                assert beat(version=version,nas_mac='missing-cpu',cpu='')==(200,'success')
            assert node_state('base',parity=True)==node_state(parity=True)
            assert db("SELECT cpu FROM node WHERE mac='node-host'")=='25.75'
            # On update, unrelated registration/owner/location fields must remain untouched.
            for version in ('base','candidate'):db("UPDATE node SET name='Registered',netid=42,owner_name='FixtureOwner',latitude='5',longitude='6' WHERE mac='node-host'",version)
            assert beat()==(200,'success');assert db("SELECT name,netid,owner_name,latitude,longitude FROM node WHERE mac='node-host'")=='Registered\t42\tFixtureOwner\t5\t6'
            assert beat(version='base')==(200,'success');assert node_state('base',parity=True)==node_state(parity=True)
            for cpu in ('0','0%','0.125%','-1.25','1e2%','12.34%'):
                for version in ('base','candidate'):assert beat(version=version,cpu=cpu)==(200,'success')
                assert node_state('base',parity=True)==node_state(parity=True);comparisons+=1
            for page in PAGES:
                assert leaflet(req(page,version='base')[1])==leaflet(req(page)[1]);comparisons+=1
            print('PASS native heartbeat INSERT/UPDATE/defaults, hostname NAS identity, CPU percent/float parity, preserved node registration fields;',comparisons,'direct GIS/CPU comparisons',flush=True)
            db("CREATE TRIGGER fail_legacy_node BEFORE UPDATE ON node FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='legacy error'",'base')
            legacy_nodes=node_state('base')
            legacy_reply=beat(version='base',wan_iface='Changed')
            assert legacy_reply[0]==200 and legacy_reply[1].endswith('success') and 'Database error' in legacy_reply[1] and 'DB Error' in legacy_reply[1] and node_state('base')==legacy_nodes
            db('DROP TRIGGER fail_legacy_node','base')
            nodes=node_state()
            assert req('heartbeat.php',{})==(200,'wrong HTTP method')
            assert req('heartbeat.php')==(200,'secret_key not provided')
            assert req('heartbeat.php',query={'secret_key[]':['bad']})==(200,'secret_key not provided')
            assert req('heartbeat.php',query={'secret_key':'bad'})==(200,'authorization denied')
            for extra in ({'nas_mac':''},{'nas_mac':'X'*21},{'nas_mac[]':['host'],'nas_mac':''},{'wan_iface[]':['bad']},{'wan_iface':'X'*129},{'wifi_ssid':'bad\0value'},{'cpu':'invalid'},{'cpu':'1e100'},{'cpu':'1e999'}):
                status,text=beat(**extra);assert status==400 or status==500;assert text in ('invalid input','unable to save heartbeat');assert node_state()==nodes
            for event,params in (('INSERT',{'nas_mac':'TriggerNew'}),('UPDATE',{'wan_iface':'Changed'})):
                db('CREATE TRIGGER fail_node BEFORE '+event+" ON node FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='private fixture marker'")
                assert beat(**params)==(500,'unable to save heartbeat');assert node_state()==nodes;db('DROP TRIGGER fail_node')
            # A late readback mismatch rolls back a successfully executed insert/update.
            db("CREATE TRIGGER coerce_node BEFORE UPDATE ON node FOR EACH ROW SET NEW.wan_iface='Forced'")
            assert beat(wan_iface='Changed')==(500,'unable to save heartbeat');assert node_state()==nodes;db('DROP TRIGGER coerce_node')
            db('ALTER TABLE node ENGINE=MyISAM');assert beat()==(500,'unable to save heartbeat');assert node_state()==nodes;db('ALTER TABLE node ENGINE=InnoDB')
            db('ALTER TABLE node DROP INDEX mac')
            db("INSERT INTO node(mac,time) VALUES ('node-host','2000-01-01')")
            duplicate=node_state();duplicate_id=db("SELECT MAX(id) FROM node WHERE mac='node-host'")
            assert beat()==(500,'unable to save heartbeat') and node_state()==duplicate
            db('DELETE FROM node WHERE id='+duplicate_id);db('ALTER TABLE node ADD UNIQUE KEY mac(mac)');assert node_state()==nodes
            # Case-insensitive lookup semantics remain those of the configured SQL collation.
            assert beat(nas_mac='NODE-HOST')==(200,'success')
            assert db("SELECT COUNT(*),MAX(mac) FROM node WHERE mac='node-host'")=='1\tNODE-HOST'
            assert beat(nas_mac='node-host')==(200,'success')
            literal="host'_%é"
            assert beat(nas_mac=literal)==(200,'success')
            assert db("SELECT COUNT(*) FROM node WHERE mac='host''_%é'")=='1'
            db("DELETE FROM node WHERE mac='host''_%é'")
            # Sensitive payloads and the authorization factor never appear in response/debug.
            ephemeral_wifi=secrets.token_hex(24)
            status,text=beat(wifi_key=ephemeral_wifi,CONFIG_DASHBOARD_DALO_DEBUG='1');assert status==200 and text.endswith('success') and hbkey not in text and ephemeral_wifi not in text
            assert db("SELECT wifi_key='' FROM node WHERE mac='node-host'")=='1'
            db("UPDATE node SET wifi_key='"+ephemeral_wifi+"' WHERE mac='node-host'")
            assert beat(wifi_key=ephemeral_wifi)==(200,'success') and db("SELECT wifi_key='' FROM node WHERE mac='node-host'")=='1'
            assert beat(**{'wifi_key[]':['ignored'],'uptime':'0','memfree':'0'})==(200,'success')
            assert db("SELECT uptime='0' AND memfree='0' FROM node WHERE mac='node-host'")=='1'
            print('PASS scalar authorization, invalid/overflow/column validation, native write/readback rollback and engine refusal; sanitized debug and cleared submitted Wi-Fi credentials',flush=True)
            sid2=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',sid2)
            tokens={s:token(session_id=s) for s in (sid,sid2)}
            id='10'
            def race_delete(s):return req(edit,{'type':'del','hotspotid':id,'csrf_token':tokens[s]},session_id=s)[1]
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:out=list(pool.map(race_delete,(sid,sid2)))
            assert sum('Deleted geolocation' in x for x in out)==1 and sum('Unable to change' in x for x in out)==1
            assert related()==relations and db('SELECT COUNT(*) FROM hotspots WHERE id=10')=='0'
            def race_beat(i):return beat(nas_mac='concurrent-node',wan_iface='iface'+str(i),uptime=str(i))
            with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:out=list(pool.map(race_beat,(1,2)))
            assert out==[(200,'success')]*2 and db("SELECT COUNT(*) FROM node WHERE mac='concurrent-node'")=='1'
            assert db("SELECT wan_iface,uptime FROM node WHERE mac='concurrent-node'") in ('iface1\t1','iface2\t2')
            lockname='dalo_heartbeat_'+hashlib.sha256(b'candidate:`node`').hexdigest()[:64-len('dalo_heartbeat_')]
            holder=subprocess.Popen(['docker','exec',h.DB,'mariadb','-uroot','-N','-B','candidate','-e',"SELECT GET_LOCK('"+lockname+"',0); SELECT SLEEP(2); SELECT RELEASE_LOCK('"+lockname+"');"],stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
            try:
                wait_for(lambda:db("SELECT IS_USED_LOCK('"+lockname+"')")!='NULL','heartbeat lock')
                with concurrent.futures.ThreadPoolExecutor(max_workers=1) as pool:
                    future=pool.submit(beat,nas_mac='lock-node');time.sleep(.1);assert not future.done();assert future.result(timeout=15)==(200,'success')
                assert holder.wait(timeout=10)==0
            finally:
                if holder.poll() is None:holder.kill();holder.wait()
                holder.communicate()
            before=hsstate();nodes=node_state()
            assert 'CSRF token error' in req(edit,{'type':'del','hotspotid':'11','csrf_token[]':['bad']})[1];assert hsstate()==before
            denied=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',denied,'default','9002')
            for page in PAGES:assert leaflet(req(page)[1])['markers'] and 'window.onload' not in req(page,session_id=denied)[1]
            anonymous=secrets.token_hex(16);expired=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',expired,'default','9001','7200')
            for page in PAGES:
                assert req(page,session_id=anonymous,follow_redirects=False)[0]==302 and req(page,session_id=expired,follow_redirects=False)[0]==302
            assert beat()==(200,'success') and hsstate()==before
            other=secrets.token_hex(16);run('docker','exec',h.WEB,'php','/fixtures/session.php',other,'other')
            assert 'Added new geolocation' in add('OtherOnly','22,44',session_id=other)
            for page in PAGES:
                assert any(m['coords']==[22,44] for m in leaflet(req(page,session_id=other)[1])['markers'])
                assert not any(m['coords']==[22,44] for m in leaflet(req(page)[1])['markers'])
            other_id=db("SELECT id FROM hotspots WHERE name='OtherOnly'",'candidate_other');assert 'Deleted geolocation' in remove(other_id,session_id=other)
            # No session selection is trusted by the machine heartbeat.
            assert req('heartbeat.php',query={'secret_key':hbkey,'nas_mac':'default-only'},session_id=other)==(200,'success')
            assert db("SELECT COUNT(*) FROM node WHERE mac='default-only'")=='1' and db("SELECT COUNT(*) FROM node WHERE mac='default-only'",'candidate_other')=='0'
            configfile=f/'candidate/app/common/includes/daloradius.conf.php';conf=configfile.read_text()
            reader='reader_'+secrets.token_hex(6);db("CREATE USER '"+reader+"'@'%' IDENTIFIED BY ''; GRANT SELECT ON candidate.* TO '"+reader+"'@'%'")
            configfile.write_text(conf+"\n$configValues['CONFIG_DB_USER']='"+reader+"';\n")
            try:
                for page in PAGES:assert leaflet(req(page)[1])['markers']
                before=hsstate();nodes=node_state()
                assert 'Unable to change' in add() and 'Unable to change' in remove(11);assert hsstate()==before
                assert beat()==(500,'unable to save heartbeat') and node_state()==nodes
            finally:configfile.write_text(conf);db("DROP USER '"+reader+"'@'%'")
            db('RENAME TABLE hotspots TO custom_hs,node TO custom_node')
            conf+="\n$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']='custom_hs';\n$configValues['CONFIG_DB_TBL_DALONODE']='custom_node';\n";configfile.write_text(conf)
            assert 'Added new geolocation' in add('Configured','25,45');id=db("SELECT id FROM custom_hs WHERE name='Configured'");assert 'Deleted geolocation' in remove(id)
            assert beat(nas_mac='configured-node')==(200,'success');assert db("SELECT COUNT(*) FROM custom_node WHERE mac='configured-node'")=='1'
            for key in ('CONFIG_DB_TBL_DALOHOTSPOTS','CONFIG_DB_TBL_DALONODE'):
                configfile.write_text(conf+"\n$configValues['"+key+"']='invalid;table';\n")
                if key.endswith('DALONODE'):assert beat()==(400,'invalid input')
                else:
                    for page in PAGES:assert 'Unable to load hotspot locations' in req(page)[1]
                    assert 'Unable to change' in add()
                configfile.write_text(conf)
            db('ALTER TABLE custom_hs RENAME COLUMN geocode TO absent_geocode')
            for page in PAGES:assert 'Unable to load hotspot locations' in req(page)[1]
            db('ALTER TABLE custom_hs RENAME COLUMN absent_geocode TO geocode')
            db('ALTER TABLE custom_node RENAME COLUMN cpu TO absent_cpu');assert beat()==(500,'unable to save heartbeat');db('ALTER TABLE custom_node RENAME COLUMN absent_cpu TO cpu')
            # Empty/single-point center and maximum positive signed BIGINT ID contract.
            db('CREATE TABLE empty_hs LIKE custom_hs');configfile.write_text(conf+"\n$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']='empty_hs';\n")
            for page in PAGES:
                result=leaflet(req(page)[1]);assert result['markers']==[] and result['center']==[43.71805,10.42284] and not result['fit']
            db("INSERT INTO empty_hs(id,name,mac,geocode) VALUES (9223372036854775807,'Maximum','001122334455','0,0')")
            for page in PAGES:
                result=leaflet(req(page)[1]);assert result['center']==[0,0] and not result['fit']
            # Producer's decimal ID is preserved exactly in native rendered JS text.
            html=req(edit)[1];assert 'id: "9223372036854775807",' in html
            tok=next(form['csrf_token'] for form in Forms(html).forms if 'csrf_token' in form)
            produced=leaflet(html,action='del',name='Maximum',prompts=['Maximum'])['submitted']
            assert produced['hotspotid']=='9223372036854775807'
            assert 'Deleted geolocation' in req(edit,dict(produced,csrf_token=tok))[1]
            configfile.write_text(conf)
            diagnostics=subprocess.run(['docker','logs',h.WEB],text=True,capture_output=True,check=True);logs=diagnostics.stdout+diagnostics.stderr
            assert not any('/fixtures/candidate/' in line and any(mark in line for mark in ('PHP Warning:','PHP Fatal error:','PHP Deprecated:')) for line in logs.splitlines()),'Candidate PHP diagnostics (details suppressed)'
            assert 'private fixture marker' not in logs
            print('PASS actual heartbeat race/lock, GIS delete race, seeded sessions/ACL/CSRF, selected/default/configured backends, SELECT-only reads, invalid/missing/empty sources and legacy-open/close tripwires',flush=True)
            print('NOTE map JS uses synthetic Leaflet/DOM; no CDN, tile service, browser or real heartbeat client/hardware was exercised',flush=True)
        finally:
            run('docker','rm','-f',h.WEB,h.DB,check=False);run('docker','network','rm',h.NETWORK,check=False)
            subprocess.run(['sudo','-n','chown','-R',str(os.getuid())+':'+str(os.getgid()),str(f)],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
            assert not [n for n in run('docker','ps','-a','--format','{{.Names}}').splitlines() if n in (h.WEB,h.DB)]
            assert h.NETWORK not in run('docker','network','ls','--format','{{.Name}}').splitlines()
            print('CLEANUP R10 fixture containers/network removed',flush=True)
if __name__=='__main__':main()
