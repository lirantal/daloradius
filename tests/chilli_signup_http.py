#!/usr/bin/env python3
"""UNIT-039 disposable real-page PHP/PEAR/PDO/MariaDB signup tests."""
import concurrent.futures
import http.cookiejar
import json
import os
from pathlib import Path
import re
import secrets
import shutil
import subprocess
import tempfile
import urllib.error
import urllib.parse
import urllib.request

from operator_login_http import run, wait_for, quote, FormParser

ROOT=Path(__file__).resolve().parents[1]
BASE='79aad6e0918c77699837213081ab68cd9339bcb8'
BASELINE=os.environ.get('CHILLI_SIGNUP_BASELINE')=='1'
TAG='u39-'+secrets.token_hex(6)
DB,WEB,NET=TAG+'-db',TAG+'-web',TAG+'-net'
FAMILIES=[('portal1/signup-free','signup.php','usergroup'),
          ('portal2/signup-free','index.php','radusergroup'),
          ('portal3/signup-free','index.php','usergroup')]


def php_quote(value):
    return "'" + str(value).replace('\\', '\\\\').replace("'", "\\'") + "'"


def sql(query):
    r=subprocess.run(['docker','exec','-i',DB,'mariadb','-uroot','-N','-B','radius'],input=query,
                     capture_output=True,text=True,timeout=30)
    if r.returncode:raise RuntimeError('Fixture SQL failed (details omitted)')
    return r.stdout.strip()


def state():
    # No generated usernames or password values in comparison snapshots.
    return [sql(q) for q in (
        'SELECT id,attribute,op FROM radcheck ORDER BY id',
        'SELECT id,firstname,lastname,email FROM userinfo ORDER BY id',
        'SELECT id,groupname,priority FROM radusergroup ORDER BY id',
        'SELECT groupname,priority FROM usergroup ORDER BY groupname,priority')]


class Client:
    def __init__(self,base):
        self.base=base;self.jar=http.cookiejar.CookieJar()
        self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.jar))
    def get(self,path,fields=None):
        data=urllib.parse.urlencode(fields,doseq=True).encode() if fields is not None else None
        try:r=self.opener.open(self.base+path,data=data,timeout=40)
        except urllib.error.HTTPError as e:r=e
        return r.status,r.read()
    def form(self,family,page):
        status,body=self.get(family+'/'+page);assert status==200
        p=FormParser();p.feed(body.decode());assert BASELINE or p.csrf
        captcha_path=family+('/php_captcha.php' if family.startswith('portal1') else '/include/common/php-captcha.php')
        status,image=self.get(captcha_path);assert status==200 and image.startswith(b'\xff\xd8'), 'CAPTCHA image fixture failed'
        key=json.loads(self.get(family+'/captcha_state.php')[1])['key']
        return {'submit':'Register','firstname':'Fixture','lastname':'Person','email':'fixture@example.invalid',
                'formKey':key,'csrf_token':p.csrf or 'baseline-unused'}


def created(body,family):
    text=body.decode()
    if family.startswith('portal1'):
        m=re.search(r'Your username is: (\S+) <br/>and your password is: (\S+) <br/>',text)
    elif family.startswith('portal2'):
        m=re.search(r'Username:</td><td><b>(.*?)</b>.*?Password:</td><td><b>(.*?)</b>',text,re.S)
    else:
        m=re.search(r'Username: <b>(.*?)</b>.*?Password: <b>(.*?)</b>',text,re.S)
    if not m:raise AssertionError('Signup did not return credentials (response omitted)')
    import html
    return tuple(html.unescape(x) for x in m.groups())


def main():
    scratch=Path.home()/'.hermes/cache/scratch';scratch.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='chilli-signup-',dir=scratch) as directory:
        fixture=Path(directory)
        shutil.copytree(ROOT/'contrib/chilli',fixture/'contrib/chilli')
        common=fixture/'app/common/includes';common.mkdir(parents=True)
        shutil.copy2(ROOT/'app/common/includes/pdo_connection.php',common/'pdo_connection.php')
        originals={}
        for family,page,_ in FAMILIES:
            path=fixture/'contrib/chilli'/family/page
            if BASELINE:path.write_text(run('git','show',BASE+':contrib/chilli/'+family+'/'+page)+'\n')
            originals[family]=path.read_text()
            # Session control exists only in disposable fixture; never a production bypass.
            (path.parent/'captcha_state.php').write_text("""<?php
session_start();echo json_encode(array('key'=>$_SESSION['key'] ?? null));
""")
        try:
            run('docker','network','create','--internal',NET)
            run('docker','run','-d','--name',DB,'--network',NET,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            sql('CREATE TABLE usergroup (username VARCHAR(64),groupname VARCHAR(64),priority INT) ENGINE=InnoDB')
            group="fixture-group"
            sql("INSERT INTO radgroupcheck (groupname,attribute,op,value) VALUES ('fixture-group','Filter-Id','=','fixture')")
            settings={'CONFIG_DB_ENGINE':'mysqli','CONFIG_DB_HOST':DB,'CONFIG_DB_PORT':'3306','CONFIG_DB_NAME':'radius',
                      'CONFIG_DB_USER':'root','CONFIG_DB_PASS':'','CONFIG_DB_TBL_RADCHECK':'radcheck',
                      'CONFIG_DB_TBL_DALOUSERINFO':'userinfo','CONFIG_DB_TBL_RADGROUPCHECK':'radgroupcheck',
                      'CONFIG_DB_TBL_RADGROUPREPLY':'radgroupreply','CONFIG_GROUP_NAME':group,'CONFIG_GROUP_PRIORITY':'0',
                      'CONFIG_USERNAME_PREFIX':'fixture-','CONFIG_USERNAME_LENGTH':'4','CONFIG_PASSWORD_LENGTH':'4',
                      'CONFIG_USER_ALLOWEDRANDOMCHARS':'abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
                      'CONFIG_SIGNUP_MSG_TITLE':'Signup','CONFIG_SIGNUP_SUCCESS_MSG_HEADER':'Welcome ',
                      'CONFIG_SIGNUP_SUCCESS_MSG_BODY':'Account:','CONFIG_SIGNUP_SUCCESS_MSG_LOGIN_LINK':'',
                      'CONFIG_SIGNUP_FAILURE_MSG_FIELDS':'Fields invalid','CONFIG_SIGNUP_FAILURE_MSG_CAPTCHA':'Captcha invalid'}
            def configure(family,updates=None):
                cfg=dict(settings);cfg.update(updates or {})
                target=fixture/'contrib/chilli'/family
                if family.startswith('portal1'):
                    text=originals[family]
                    for k,v in cfg.items():
                        pattern=r"(\$configValues\['"+re.escape(k)+r"'\]\s*=\s*)([^;]*);"
                        text=re.sub(pattern,lambda m:m.group(1)+php_quote(v)+';',text)
                    text=text.replace('$usernamePrefix = "guest";',"$usernamePrefix = "+php_quote(cfg['CONFIG_USERNAME_PREFIX'])+';')
                    # Extra overrides stay inside the inline configuration, before request processing.
                    at=text.index('session_start();')
                    overrides=''.join('$configValues[%s]=%s;\n'%(php_quote(k),php_quote(v)) for k,v in cfg.items())
                    text=text[:at]+overrides+text[at:]
                    (target/'signup.php').write_text(text)
                else:
                    cfg['CONFIG_DB_TBL_RADUSERGROUP']='radusergroup' if family.startswith('portal2') else 'usergroup'
                    config='<?php\n'+''.join('$configValues[%s]=%s;\n'%(php_quote(k),php_quote(v)) for k,v in cfg.items())
                    f=target/'library/daloradius.conf.php';f.write_text(config);f.chmod(0o600)
            for family,_,_ in FAMILIES:configure(family)
            run('docker','run','-d','--name',WEB,'--network',NET,'-v',f'{fixture}:/fixtures',
                '-e','PHP_CLI_SERVER_WORKERS=2','--entrypoint','php','lirantal/daloradius',
                '-d','display_errors=0','-d','opcache.enable=0','-d','opcache.enable_cli=0',
                '-S','0.0.0.0:8080','-t','/fixtures/contrib/chilli')
            ip=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            base='http://'+ip+':8080/'
            wait_for(lambda:Client(base).get(FAMILIES[0][0]+'/signup.php')[0]==200,'PHP HTTP')
            secrets_seen=[]
            for family,page,mapping in FAMILIES:
                c=Client(base);fields=c.form(family,page)
                # Fixture CAPTCHA state returns full generated key, legacy uses first five bytes.
                fields['formKey']=fields['formKey'][:5]
                status,body=c.get(family+'/'+page,fields);assert status==200
                username,password=created(body,family);secrets_seen.append(password)
                assert sql('SELECT COUNT(*) FROM radcheck WHERE username=%s AND attribute=\'User-Password\' AND op=\'==\' AND value=%s'%
                           (quote(username),quote(password)))=='1'
                assert sql('SELECT firstname,lastname,email FROM userinfo WHERE username='+quote(username))=='Fixture\tPerson\tfixture@example.invalid'
                assert sql('SELECT groupname,priority FROM '+mapping+' WHERE username='+quote(username))==group+'\t0'
                print('PASS:',family,'real signup and equivalent account/info/group state')
                if BASELINE:
                    # Show the old committed partial-success behavior using a late database error.
                    trigger='u39_late_'+family.split('/')[0]
                    sql("CREATE TRIGGER "+trigger+" BEFORE INSERT ON "+mapping+" FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture late failure'")
                    try:
                        fields=c.form(family,page);fields['formKey']=fields['formKey'][:5]
                        before=int(sql('SELECT COUNT(*) FROM radcheck'))
                        status,body=c.get(family+'/'+page,fields);assert status==200
                        assert int(sql('SELECT COUNT(*) FROM radcheck'))==before+1
                        assert 'Signup could not be completed' not in body.decode()
                    finally:sql('DROP TRIGGER '+trigger)
                    print('PASS: pinned baseline leaves partial account after later mapping failure')
                    continue
                # Missing CAPTCHA, wrong CAPTCHA, missing/array CSRF, and malformed fields.
                for updates in ({'formKey':'wrong'}, {'formKey[]':'bad','formKey':None}, {'csrf_token':'wrong'}, {'csrf_token[]':'bad','csrf_token':None},
                                {'submit[]':'bad','submit':None}, {'firstname[]':'bad','firstname':None}, {'lastname':'x'*201}, {'email[]':'bad','email':None}):
                    fields=c.form(family,page);fields['formKey']=fields['formKey'][:5]
                    fields.update(updates);fields={k:v for k,v in fields.items() if v is not None}
                    before=state();status,body=c.get(family+'/'+page,fields)
                    assert status==200 and state()==before and 'Your username is:' not in body.decode()
                fresh=Client(base);before=state()
                status,raw=fresh.get(family+'/'+page);assert status==200
                parser=FormParser();parser.feed(raw.decode());assert parser.csrf
                status,body=fresh.get(family+'/'+page,{'submit':'Register','firstname':'Fixture','lastname':'Person',
                                                     'formKey':'','csrf_token':parser.csrf})
                assert status==200 and state()==before and 'Signup could not be completed' not in body.decode()
                before=state();status,body=c.get(family+'/'+page,fields);assert status==200 and state()==before
                for table in ('userinfo',mapping):
                    sql("CREATE TRIGGER u39_failure BEFORE INSERT ON "+table+" FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture late failure'")
                    try:
                        fields=c.form(family,page);fields['formKey']=fields['formKey'][:5]
                        before=state();status,body=c.get(family+'/'+page,fields)
                        assert status==200 and 'Signup could not be completed' in body.decode() and state()==before
                    finally:sql('DROP TRIGGER u39_failure')
                for table in ('radcheck','userinfo',mapping,'radgroupcheck','radgroupreply'):
                    sql('ALTER TABLE '+table+' ENGINE=MyISAM')
                    try:
                        fields=c.form(family,page);fields['formKey']=fields['formKey'][:5]
                        before=state();status,body=c.get(family+'/'+page,fields)
                        assert status==200 and 'Signup could not be completed' in body.decode() and state()==before
                    finally:sql('ALTER TABLE '+table+' ENGINE=InnoDB')
                for updates in ({'CONFIG_GROUP_NAME':'absent-group'}, {'CONFIG_DB_TBL_DALOUSERINFO':'userinfo;bad'},
                                {'CONFIG_DB_NAME':'missing-database'}):
                    configure(family,updates)
                    try:
                        fields=c.form(family,page);fields['formKey']=fields['formKey'][:5]
                        before=state();status,body=c.get(family+'/'+page,fields)
                        assert status==200 and 'Signup could not be completed' in body.decode() and state()==before, (family, list(updates), status, 'Signup could not be completed' in body.decode(), state()==before)
                    finally:configure(family)
                print('PASS:',family,'gates, complete late-error rollback, malformed controls and engine/config rejection')
                # Special values are stored verbatim, success output escapes untrusted first names.
                fields=c.form(family,page);fields['formKey']=fields['formKey'][:5]
                fields.update(firstname="é-'-%-<script>",lastname='0',email="quote'@example.invalid")
                status,body=c.get(family+'/'+page,fields);assert status==200
                username,password=created(body,family);secrets_seen.append(password)
                assert sql('SELECT firstname,lastname,email FROM userinfo WHERE username='+quote(username))=="é-'-%-<script>\t0\tquote'@example.invalid"
                assert '&lt;script&gt;' in body.decode() and "é-'-%-<script>" not in body.decode()
                before=state();status,body=c.get(family+'/'+page,fields);assert state()==before
                print('PASS:',family,'Unicode/quotes/percent/zero, escaped success and consumed CAPTCHA replay')
            if not BASELINE:
                family,page,mapping=FAMILIES[1]
                configure(family,{'CONFIG_GROUP_NAME':''})
                c=Client(base);fields=c.form(family,page);fields['formKey']=fields['formKey'][:5]
                before=sql('SELECT COUNT(*) FROM radusergroup');status,body=c.get(family+'/'+page,fields)
                username,password=created(body,family);secrets_seen.append(password)
                assert sql('SELECT COUNT(*) FROM radusergroup')==before
                # Both group sources and bound quote/percent/Unicode identity/config values.
                special_group="group-'-%-é"
                sql("INSERT INTO radgroupreply(groupname,attribute,op,value) VALUES (%s,'Filter-Id','=','fixture')"%quote(special_group))
                configure(family,{'CONFIG_GROUP_NAME':special_group,'CONFIG_GROUP_PRIORITY':'-1',
                                  'CONFIG_USERNAME_PREFIX':"identity-'-%-é-"})
                fields=c.form(family,page);fields['formKey']=fields['formKey'][:5]
                status,body=c.get(family+'/'+page,fields);assert status==200
                username,password=created(body,family);secrets_seen.append(password)
                assert username.startswith("identity-'-%-é-")
                assert sql('SELECT groupname,priority FROM radusergroup WHERE username='+quote(username))==special_group+'\t-1'
                configure(family,{'CONFIG_USER_ALLOWEDRANDOMCHARS':'x','CONFIG_USERNAME_LENGTH':'1','CONFIG_USERNAME_PREFIX':'collision-'})
                sql("INSERT INTO radcheck(username,attribute,op,value) VALUES ('collision-x','Filter-Id','=','fixture')")
                fields=c.form(family,page);fields['formKey']=fields['formKey'][:5]
                before=state();status,body=c.get(family+'/'+page,fields)
                assert 'Signup could not be completed' in body.decode() and state()==before
                sql("DELETE FROM radcheck WHERE username='collision-x'")
                clients=[Client(base),Client(base)]
                forms=[c.form(family,page) for c in clients]
                for f in forms:f['formKey']=f['formKey'][:5]
                def post(pair):return pair[0].get(family+'/'+page,pair[1])
                with concurrent.futures.ThreadPoolExecutor(2) as pool:
                    outcomes=list(pool.map(post,zip(clients,forms)))
                assert sum('Signup could not be completed' not in b.decode() for _,b in outcomes)==1
                assert sql("SELECT COUNT(*) FROM radcheck WHERE username='collision-x'")=='1'
                assert sql("SELECT COUNT(*) FROM userinfo WHERE username='collision-x'")=='1'
                configure(family)
                print('PASS: optional group, preexisting collision and concurrent independent HTTP sessions')
                status,_=Client(base).get('common/freeSignup.php');assert status==404
            logs=subprocess.run(['docker','logs',WEB],capture_output=True,text=True,timeout=30)
            text=logs.stdout+logs.stderr
            assert not any(s in text for s in secrets_seen)
            assert 'PHP Fatal error' not in text
            if not BASELINE:assert 'PHP Warning' not in text
            print('PASS: no generated passwords or fatal errors in PHP logs')
        finally:
            run('docker','exec','-u','root',WEB,'chown','-R',f'{os.getuid()}:{os.getgid()}','/fixtures',check=False)
            for name in (WEB,DB):run('docker','rm','-f','-v',name,check=False)
            run('docker','network','rm',NET,check=False)
            for kind,name in (('container',WEB),('container',DB),('network',NET)):
                assert subprocess.run(['docker',kind,'inspect',name],capture_output=True,timeout=30).returncode!=0
            print('CLEANUP VERIFIED: isolated containers and network removed')

if __name__=='__main__':main()
