#!/usr/bin/env python3
'''R27 pinned native NAS read parity; fake radclient only, no real NAS.
Secret-bearing outputs remain in memory, never written as snapshots.'''
import base64, html, json, os, re, secrets, shutil, subprocess, tempfile
from pathlib import Path
from html.parser import HTMLParser
import operator_login_http as auth
from operator_login_http import Client, FormParser, login, hash_password, quote, wait_for
ROOT=Path(__file__).resolve().parents[1]
BASE='a87b9a3ec29505a568192e1ff7e3e5fae9e433f9'
FILES=['config-maint-disconnect-user.php','config-maint-test-user.php','library/extensions/maintenance_radclient.php','mng-rad-nas-export.php','mng-rad-nas-list.php']
TAG='r27-'+secrets.token_hex(5)
DB,WEB,NET=TAG+'-db',TAG+'-web',TAG+'-net'
checks={}
def run(*args,input=None,check=True):
    p=subprocess.run(args,input=input,text=True,capture_output=True,timeout=180)
    if check and p.returncode:raise RuntimeError('fixture command failed')
    return p.stdout.strip()
def sql(q):return run('docker','exec','-i',DB,'mariadb','-uroot','-N','-B','radius',input=q)
class Table(HTMLParser):
    def __init__(self):super().__init__();self.active=False;self.parts=[];self.boxes=[]
    def handle_starttag(self,t,a):
        d=dict(a)
        if t=='tbody':self.active=True
        if self.active:
            if t=='input' and d.get('name')=='nasname[]':self.boxes.append(d.get('value'))
            if t=='a':self.parts.append(('href',d.get('href')))
    def handle_endtag(self,t):
        if t=='tbody':self.active=False
    def handle_data(self,s):
        if self.active and s.strip():self.parts.append(('text',s.strip()))
def req(c,path,fields=None):
    status,_,headers,body=c.request(path,fields)
    assert 'PHP Fatal' not in body
    return status,headers,body
def csrf(c,path):
    status,h,body=req(c,path);assert status==200 and '</html>' in body
    p=FormParser();p.feed(body);assert p.csrf;return p.csrf
def checkpoint(key,value,mode):
    if mode=='pear':checks[key]=value
    else:assert checks[key]==value,'differential mismatch: '+key

def main():
    auth.DB=DB
    op='fixture-'+secrets.token_hex(5);pw=secrets.token_urlsafe(25)
    nassecret=secrets.token_urlsafe(20);userpw=secrets.token_urlsafe(20)
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(prefix=TAG+'-',dir=scratch) as folder:
        root=Path(folder);shutil.copytree(ROOT/'app',root/'app',ignore=shutil.ignore_patterns('daloradius.conf.php'))
        common=root/'app/common/includes';ops=root/'app/operators'
        candidate={f:(ops/f).read_bytes() for f in FILES}
        config=(common/'daloradius.conf.php.sample').read_text().replace('?>','')
        settings={'CONFIG_DB_ENGINE':'mysqli','CONFIG_DB_HOST':DB,'CONFIG_DB_PORT':'3306','CONFIG_DB_USER':'root','CONFIG_DB_PASS':'','CONFIG_DB_NAME':'radius','CONFIG_LOG_PAGES':'no','CONFIG_LOG_QUERIES':'no','CONFIG_LOG_ACTIONS':'no','CONFIG_DEBUG_SQL':'no','CONFIG_DEBUG_SQL_ONPAGE':'no','CONFIG_IFACE_PASSWORD_HIDDEN':'yes','CONFIG_IFACE_TABLES_LISTING':'2'}
        for k,v in settings.items():config+='\n$configValues['+quote(k)+']='+quote(v)+';'
        config+='\n$configValues["CONFIG_LOCATIONS"]["alternate"]=array("Engine"=>"mysqli","Hostname"=>'+quote(DB)+',"Port"=>"3306","Database"=>"alternate","Username"=>"root","Password"=>"");\n'
        (common/'daloradius.conf.php').write_text(config)
        # Retained config writer is replaced in both fixtures with an invocation
        # marker, so no posted shared secret is saved to a config file.
        (common/'config_write.php').write_text('<?php file_put_contents("/fixtures/config-writes", "write\\n", FILE_APPEND);')
        try:
            run('docker','network','create','--internal',NET)
            run('docker','run','-d','--name',DB,'--network',NET,'--tmpfs','/var/lib/mysql','-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'R27 MariaDB')
            for name in ['fr3-mariadb-freeradius.sql','mariadb-daloradius.sql']:sql((ROOT/'contrib/db'/name).read_text())
            sql('CREATE DATABASE alternate; CREATE TABLE alternate.nas LIKE radius.nas; CREATE TABLE alternate.radcheck LIKE radius.radcheck;')
            run('docker','run','-d','--name',WEB,'--network',NET,'--tmpfs','/tmp','-v',str(root)+':/fixtures','-w','/fixtures/app/operators','--entrypoint','php','lirantal/daloradius','-d','display_errors=0','-d','opcache.enable=0','-d','opcache.enable_cli=0','-S','0.0.0.0:8080','-t','.')
            stub='#!/bin/sh\ncat >/dev/null\necho call >>/fixtures/calls\necho fixture-success\n'
            run('docker','exec','-i',WEB,'sh','-c','mkdir -p /usr/share/freeradius; cat >/usr/local/bin/radclient; chmod +x /usr/local/bin/radclient',input=stub)
            address=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            url='http://'+address+':8080/'
            wait_for(lambda:Client(url).request('login.php')[0]==200,'R27 PHP HTTP')
            auth.add_operator(op,hash_password(WEB,pw))
            oid=sql('SELECT id FROM operators WHERE username='+quote(op))
            for perm in ['mng_rad_nas_list','config_maint_disconnect_user','config_maint_test_user']:
                sql('INSERT INTO operators_acl (operator_id,file,access) VALUES ('+oid+','+quote(perm)+',1)')
            sql("INSERT INTO radcheck(username,attribute,op,value) VALUES ('fixture-user','Cleartext-Password',':=',"+quote(userpw)+")")
            for mode in ['pear','pdo']:
                for f in FILES:(ops/f).write_bytes(subprocess.check_output(['git','show',BASE+':app/operators/'+f]) if mode=='pear' else candidate[f])
                c=Client(url);assert login(c,op,pw,'local','default')[0]==302
                for f in ['mng-rad-nas-list.php','mng-rad-nas-export.php',*FILES[:2]]:assert req(Client(url),f)[0]==302
                sql('DELETE FROM nas')
                status,h,body=req(c,'mng-rad-nas-export.php');doc=json.loads(body);doc.pop('exported_at');checkpoint('empty-export',doc,mode)
                status,h,body=req(c,'mng-rad-nas-list.php');assert 'Nothing to display' in body and '</html>' in body
                checkpoint('empty-list',(status,'Nothing to display' in body),mode)
                for i in range(5):
                    sql('INSERT INTO nas(id,nasname,shortname,type,ports,secret,server,community,description) VALUES ('+str(i+10)+','+','.join(map(quote,["nas-'é-&+%-"+str(i),'short-'+str(i),'other']))+','+str(i)+','+','.join(map(quote,[nassecret,'server-'+str(i),'community-'+str(i),'description-'+str(i)]))+')')
                status,h,body=req(c,'mng-rad-nas-export.php');doc=json.loads(body);doc.pop('exported_at');assert doc['count']==5 and doc['version']==1
                assert all(x['secret']==nassecret for x in doc['nas'])
                checkpoint('v1-export',(status,doc,h.get('Content-Type'),h.get('Cache-Control'),h.get('X-Content-Type-Options')),mode)
                for sort in ['id','nasname','shortname','type','ports','secret','server','community','description','bad-SQL']:
                    for direction in ['asc','desc']:
                        for page in [1,2,3,99]:
                            status,h,body=req(c,'mng-rad-nas-list.php?orderBy='+sort+'&orderType='+direction+'&page='+str(page))
                            assert status==200 and '</html>' in body
                            t=Table();t.feed(body);assert t.boxes and nassecret not in body
                            checkpoint('list-'+sort+'-'+direction+'-'+str(page),(t.parts,t.boxes),mode)
                for f in FILES[:2]:
                    status,h,body=req(c,f);assert status==200 and '</html>' in body
                    assert 'CSRF token error' in req(c,f,[('csrf_token','invalid')])[2]
                f=FILES[0];token=csrf(c,f)
                status,h,body=req(c,f,[('csrf_token',token),('username','fixture-user'),('nas_id','nas-10'),('simulate','on'),('port','1700')])
                assert 'Performed disconnect action' in body and ':1700' in body and '(not executed)' in body
                checkpoint('disconnect-simulated',html.unescape(re.search(r'<pre[^>]*>(.*?)</pre>',body,re.S)[1]),mode)
                actual_calls=(root/'calls').read_text().count('call') if (root/'calls').exists() else 0
                assert actual_calls==(0 if mode=='pear' else 1)
                token=csrf(c,f);status,h,body=req(c,f,[('csrf_token',token),('username','fixture-user'),('nas_id','nas-10'),('packetType','coa')])
                assert 'fixture-success' in body and ':3799' in body
                checkpoint('disconnect-executable',html.unescape(re.search(r'<pre[^>]*>(.*?)</pre>',body,re.S)[1]),mode)
                f=FILES[1];token=csrf(c,f)
                fields=[('csrf_token',token),('username','fixture-user'),('radius_addr','127.0.0.1'),('radius_port','1812'),('secret',nassecret),('password1',userpw),('password2',userpw),('simulate','on')]
                status,h,body=req(c,f,fields);assert 'Performed informative action' in body and '(not executed)' in body
                checkpoint('user-simulated',html.unescape(re.search(r'<pre[^>]*>(.*?)</pre>',body,re.S)[1]),mode)
                token=csrf(c,f);status,h,body=req(c,f,[('csrf_token',token),('username','absent')]);assert 'This user does not exist' in body
                checkpoint('absent-user','This user does not exist' in body,mode)
                token=csrf(c,f);status,h,body=req(c,f,[('csrf_token',token),('username','fixture-user'),('password1','a'),('password2','b')]);assert 'should match' in body
                checkpoint('mismatched-passwords','should match' in body,mode)
                sql('ALTER TABLE nas MODIFY description VARBINARY(200) NULL; ALTER TABLE nas MODIFY shortname VARBINARY(32) NULL;')
                sql("UPDATE nas SET description=UNHEX('00FF0A'),shortname=NULL,ports=NULL WHERE id=10")
                status,h,body=req(c,'mng-rad-nas-export.php');doc=json.loads(body);doc.pop('exported_at')
                assert doc['version']==2 and doc['nas'][0]['shortname'] is None
                assert base64.b64decode(doc['nas'][0]['description']['data'])==b'\x00\xff\n'
                checkpoint('v2-export',(status,doc),mode)
                sql('UPDATE nas SET description=NULL; ALTER TABLE nas MODIFY description VARCHAR(200) NULL; ALTER TABLE nas MODIFY shortname VARCHAR(32) NULL;')
                print('PASS: '+mode+' native list/export and simulated/executable maintenance')
            assert len(checks)==89,len(checks)
            (common/'db_open.php').write_text('<?php throw new RuntimeException("R27 legacy tripwire");')
            c=Client(url);assert login(c,op,pw,'local','default')[0]==302
            for f in ['mng-rad-nas-list.php','mng-rad-nas-export.php',*FILES[:2]]:
                status,h,body=req(c,f);assert status==200
                if f!='mng-rad-nas-export.php':assert '</html>' in body
            for f in FILES[:2]:
                token=csrf(c,f);status,h,body=req(c,f,[('csrf_token',token),('username[]','bad'),('nas_id[]','nas-10')])
                assert status==200 and 'CSRF token error' in body and '</html>' in body
            status,h,body=req(c,'mng-rad-nas-list.php?orderType[]=desc&orderBy[]=id');assert status==200 and '</html>' in body

            initial=sql('SELECT id,HEX(nasname),HEX(shortname),HEX(type),ports,LENGTH(secret),HEX(server),HEX(community),HEX(description) FROM nas ORDER BY id')
            calls=(root/'calls').read_text().count('call')
            # Actual helper/POST under tripwire, not just GET form rendering.
            f=FILES[0];token=csrf(c,f)
            status,h,body=req(c,f,[('csrf_token',token),('username',"' OR 1=1 --"),('nas_id','nas-11'),('simulate','on')])
            assert 'Performed disconnect action' in body and ':3799' in body
            token=csrf(c,f);status,h,body=req(c,f,[('csrf_token',token),('username','fixture-user'),('nas_id','nas-9999')])
            assert 'Empty or invalid required' in body and (root/'calls').read_text().count('call')==calls
            f=FILES[1];token=csrf(c,f)
            status,h,body=req(c,f,[('csrf_token',token),('username',"' OR 1=1 --"),('password1',userpw),('password2',userpw)])
            assert 'This user does not exist' in body
            token=csrf(c,f);status,h,body=req(c,f,[('csrf_token',token),('username','fixture-user'),('password1',userpw),('password2',userpw)])
            assert 'fixture-success' in body and (root/'calls').read_text().count('call')==calls+1
            for f in FILES[:2]:
                token=csrf(c,f);status,h,body=req(c,f,[('csrf_token[]',token),('username','fixture-user')])
                assert 'CSRF token error' in body and '</html>' in body
            assert sql('SELECT id,HEX(nasname),HEX(shortname),HEX(type),ports,LENGTH(secret),HEX(server),HEX(community),HEX(description) FROM nas ORDER BY id')==initial
            # Visible/hidden list policy remains distinct from the full privileged export.
            (common/'daloradius.conf.php').write_text(config+'\n$configValues["CONFIG_IFACE_PASSWORD_HIDDEN"]="no";\n')
            assert nassecret in req(c,'mng-rad-nas-list.php')[2]
            (common/'daloradius.conf.php').write_text(config)
            # Count succeeds but the later row projection fails: complete redacted render.
            sql('ALTER TABLE nas CHANGE description unavailable_description VARCHAR(200) NULL')
            status,h,body=req(c,'mng-rad-nas-list.php');assert 'Unable to read the NAS list' in body and '</html>' in body
            sql('ALTER TABLE nas CHANGE unavailable_description description VARCHAR(200) NULL')
            (common/'daloradius.conf.php').write_text(config+'\n$configValues["CONFIG_DB_TBL_RADNAS"]="nas; SELECT 1";\n')
            status,h,body=req(c,'mng-rad-nas-export.php');assert status==500 and 'nas;' not in body
            (common/'daloradius.conf.php').write_text(config)

            (ops/'r27-location.php').write_text('<?php require "library/checklogin.php"; $_SESSION["location_name"]="alternate"; echo "ok";')
            sql('INSERT INTO alternate.nas SELECT * FROM radius.nas WHERE id=11; INSERT INTO alternate.radcheck SELECT * FROM radius.radcheck; CREATE TABLE alternate.operators LIKE radius.operators; INSERT INTO alternate.operators SELECT * FROM radius.operators; CREATE TABLE alternate.operators_acl LIKE radius.operators_acl; INSERT INTO alternate.operators_acl SELECT * FROM radius.operators_acl;')
            req(c,'r27-location.php');status,h,body=req(c,'mng-rad-nas-export.php');assert json.loads(body)['count']==1
            assert 'nas-11' in req(c,FILES[0])[2]
            sql('RENAME TABLE alternate.nas TO alternate.fixture_nas')
            (common/'daloradius.conf.php').write_text(config+'\n$configValues["CONFIG_DB_TBL_RADNAS"]="fixture_nas";\n')
            status,h,body=req(c,'mng-rad-nas-export.php');assert status==200 and json.loads(body)['count']==1

            f=FILES[0];token=csrf(c,f)
            status,h,body=req(c,f,[('csrf_token',token),('username','fixture-user'),('nas_id','nas-11'),('simulate','on')])
            assert 'Performed disconnect action' in body and '(not executed)' in body
            f=FILES[1];token=csrf(c,f)
            status,h,body=req(c,f,[('csrf_token',token),('username','fixture-user'),('password1',userpw),('password2',userpw),('simulate','on')])
            assert 'Performed informative action' in body

            sql('RENAME TABLE alternate.fixture_nas TO alternate.unavailable_nas')
            status,h,body=req(c,'mng-rad-nas-export.php');assert status==500 and json.loads(body)=={'error':'Unable to export the NAS list'}
            status,h,body=req(c,'mng-rad-nas-list.php');assert status==200 and 'Unable to read the NAS list' in body and '</html>' in body
            status,h,body=req(c,FILES[0]);assert 'Unable to read NAS configuration' in body and '</html>' in body
            sql('DROP TABLE alternate.radcheck')
            f=FILES[1];token=csrf(c,f);before=(root/'config-writes').read_bytes()
            status,h,body=req(c,f,[('csrf_token',token),('username','fixture-user'),('password1',userpw),('password2',userpw)])
            assert 'Unable to check the user' in body and (root/'config-writes').read_bytes()==before
            (common/'daloradius.conf.php').write_text(config)
            sql("UPDATE alternate.operators_acl SET access=0 WHERE operator_id="+oid+" AND file='mng_rad_nas_list'")
            status,h,body=req(c,'mng-rad-nas-export.php');assert status==403 and nassecret not in body
            # Class-local lookup must leave caller-owned PDO and its write intact.
            probe='''$_SERVER['PHP_SELF']='r27-probe.php';require '../common/includes/config_read.php';require '../common/includes/pdo_connection.php';require 'library/extensions/maintenance_radclient.php';$pdo=dalo_pdo_connect($configValues);$pdo->beginTransaction();$pdo->exec("UPDATE nas SET ports=987 WHERE id=11");$r=new RadClient(array('simulate'=>true));$out=$r->disconnect(array('nas_id'=>11,'command'=>'disconnect','username'=>'fixture-user'));if($out['error']||!$pdo->inTransaction()||$pdo->query('SELECT ports FROM nas WHERE id=11')->fetchColumn()!=987)exit(2);$pdo->rollBack();$out=$r->disconnect(array('nas_id'=>9999,'command'=>'coa','username'=>'fixture-user'));if(!$out['error']||$out['output']!='NAS not found')exit(3);echo 'ownership-pass';'''
            assert run('docker','exec',WEB,'php','-r',probe)=='ownership-pass'
            assert sql('SELECT ports FROM nas WHERE id=11')=='1'
            failure=probe.split("$pdo=dalo_pdo_connect")[0]+'''$configValues['CONFIG_DB_TBL_RADNAS']='absent_nas';try{(new RadClient(array('simulate'=>true)))->disconnect(array('nas_id'=>11,'command'=>'coa','username'=>'fixture-user'));exit(2);}catch(RuntimeException $e){if($e->getMessage()!='Unable to read NAS configuration')exit(3);echo 'failure-pass';}'''
            assert run('docker','exec',WEB,'php','-r',failure)=='failure-pass'
            assert sql('SELECT COUNT(*) FROM nas WHERE secret='+quote(nassecret))=='5'
            (ops/'r27-log.php').write_text('<?php error_log("R27-log-marker"); echo "ok";')
            assert req(c,'r27-log.php')[2]=='ok'
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=True)
            text=logs.stdout+logs.stderr
            assert "R27-log-marker" in text
            assert all(s not in text for s in [nassecret,userpw,pw,'PHP Fatal error','PHP Warning','PHP Notice','PHP Deprecated','R27 legacy tripwire'])
            print('PASS: '+str(len(checks))+' keyed PEAR/PDO comparisons')
            print('PASS: legacy-open tripwire, malformed forms, location/table routing, redacted missing-table failures, ACL revocation, caller transaction')
        finally:
            run('docker','exec','-u','root',WEB,'chown','-R',str(os.getuid())+':'+str(os.getgid()),'/fixtures',check=False)
            for container in [WEB,DB]:run('docker','rm','-f','-v',container,check=False)
            run('docker','network','rm',NET,check=False)
            assert all(subprocess.run(['docker','inspect',x],capture_output=True).returncode!=0 for x in [WEB,DB,NET])
    assert not root.exists()
    print('CLEANUP: R27 fixture/config/session resources removed')
if __name__=='__main__':main()
