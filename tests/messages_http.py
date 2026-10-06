#!/usr/bin/env python3
"""R01b message editor/provider: native isolated PEAR/PDO comparison and rollback."""
import json
import os
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap
import re
import secrets
import shutil
import subprocess
import tempfile
from concurrent.futures import ThreadPoolExecutor
from html.parser import HTMLParser
import urllib.request

import operator_login_http as harness
from operator_login_http import Client, NoRedirect, quote, wait_for

ROOT = Path(__file__).resolve().parents[1]
BASE = 'b59b278cdefc9a66313746914a20dfb6523d3748'
BASELINE = os.environ.get('MESSAGES_BASELINE') == '1'
DB, WEB, NETWORK = harness.DB, harness.WEB, harness.NETWORK
IMAGE = os.environ.get('MESSAGES_WEB_IMAGE', 'lirantal/daloradius')


def run(*args, input=None, check=True, timeout=180):
    p = subprocess.run(args, input=input, text=True, capture_output=True, timeout=timeout)
    if check and p.returncode:
        raise RuntimeError('Isolated fixture operation failed; details suppressed')
    return (p.stdout+p.stderr).strip() if args[:2]==('docker','logs') else p.stdout.strip()


def sql(query, database='radius'):
    return run('docker','exec','-i',DB,'mariadb','-uroot','-N','-B',database,input=query)


class Form(HTMLParser):
    def __init__(self, body):
        super().__init__(); self.csrf=None; self.textareas={}; self.name=None; self.feed(body)

    def handle_starttag(self, tag, attrs):
        attrs=dict(attrs)
        if tag=='input' and attrs.get('name')=='csrf_token': self.csrf=attrs.get('value')
        if tag=='textarea':
            self.name=attrs.get('name'); self.textareas[self.name]=''

    def handle_endtag(self, tag):
        if tag=='textarea': self.name=None

    def handle_data(self,data):
        if self.name is not None: self.textareas[self.name]+=data


def main():
    scratch=Path.home()/'.hermes/cache/scratch'; scratch.mkdir(parents=True,exist_ok=True)
    actor="msg-O'Reilly%+É"
    with tempfile.TemporaryDirectory(prefix='dalo-messages-',dir=scratch) as directory:
        fixture=Path(directory)
        shutil.copytree(ROOT/'app',fixture/'app',symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
        if BASELINE:
            restore_pear_bootstrap((fixture / 'app').parent, BASE)
            for path in ('app/operators/config-messages.php','app/common/includes/functions.php',
                         'app/users/login.php','app/users/help-main.php'):
                (fixture/path).write_text(run('git','show',BASE+':'+path)+'\n')
        restore_pear_bootstrap(fixture / 'legacy', BASE)
        for name in ('functions.php', 'portal_password.php'):
            rel = 'app/common/includes/' + name
            (fixture/'legacy'/rel).write_text(run('git','show',BASE+':'+rel)+'\n')
        for name in ('config_read.php', 'db_table_conventions.php', 'pdo_connection.php'):
            (fixture/'legacy/app/common/includes'/name).write_text("<?php require_once '/fixtures/app/common/includes/"+name+"';")
        # Historical message purification resolves vendor files relative to this tree.
        (fixture/'legacy/app/common/library').symlink_to('../../../app/common/library', target_is_directory=True)
        (fixture/'session.php').write_text('''<?php
$p=json_decode(stream_get_contents(STDIN),true); session_name($p['user']?'daloradius_user_sid':'daloradius_operator_sid');
session_id($p['sid']);session_start();$_SESSION=array('time'=>time());
if ($p['user']) {$_SESSION['logged_in']=true;$_SESSION['login_user']='portal-fixture';}
else {$_SESSION['daloradius_logged_in']=true;$_SESSION['operator_id']=$p['id'];$_SESSION['operator_user']=$p['actor'];$_SESSION['location_name']=$p['location'];}
session_write_close();
''')
        (fixture/'app/operators/messages-legacy.php').write_text('''<?php
include 'library/checklogin.php';include '../common/includes/config_read.php';include 'lang/main.php';
include '../common/includes/validation.php';include '../../legacy/app/common/includes/functions.php';
include '../../legacy/app/common/includes/db_open.php';echo json_encode(get_message($dbSocket,$_GET['type'] ?? 'dashboard'));
include '../../legacy/app/common/includes/db_close.php';
''')
        (fixture/'app/operators/log-channel.php').write_text("<?php error_log('FIXTURE_LOG_CHANNEL'); echo 'ok';")
        try:
            run('docker','network','create','--internal',NETWORK)
            run('docker','run','-d','--name',DB,'--network',NETWORK,'--tmpfs','/var/lib/mysql',
                '-e','MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1','-e','MARIADB_DATABASE=radius','mariadb:11.8')
            wait_for(lambda:sql('SELECT 1'),'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql','mariadb-daloradius.sql'):
                sql((ROOT/'contrib/db'/name).read_text())
            sql('CREATE TABLE messages_custom LIKE messages; INSERT INTO messages_custom SELECT * FROM messages; '
                "UPDATE messages_custom SET created_on='2000-01-01',created_by='fixture creator'; "
                "INSERT INTO operators_acl(operator_id,file,access) VALUES(9001,'config_messages',1),(9002,'config_messages',0); "
                'CREATE DATABASE radius_other; CREATE TABLE radius_other.messages_custom LIKE messages_custom; '
                'INSERT INTO radius_other.messages_custom SELECT * FROM messages_custom; '
                'CREATE TABLE radius_other.operators_acl LIKE operators_acl; '
                'INSERT INTO radius_other.operators_acl SELECT * FROM operators_acl;')
            config=(ROOT/'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>','')
            config+='''
$configValues['CONFIG_DB_HOST']=getenv('MESSAGES_TEST_DB');$configValues['CONFIG_DB_USER']=getenv('MESSAGES_TEST_USER');
$configValues['CONFIG_DB_PASS']='';$configValues['CONFIG_DB_NAME']='radius';$configValues['CONFIG_DB_ENGINE']='mysqli';
$configValues['CONFIG_DB_TBL_DALOMESSAGES']='messages_custom';
$configValues['CONFIG_LOG_PAGES']='no';$configValues['CONFIG_LOG_QUERIES']='no';$configValues['CONFIG_LOG_ACTIONS']='no';
$configValues['CONFIG_DEBUG_SQL']='no';$configValues['CONFIG_DEBUG_SQL_ONPAGE']='yes';
$configValues['CONFIG_LOCATIONS']['other']=array('Engine'=>'mysqli','Hostname'=>getenv('MESSAGES_TEST_DB'),
'Username'=>getenv('MESSAGES_TEST_USER'),'Password'=>'','Database'=>'radius_other','Port'=>'3306');
'''
            (fixture/'app/common/includes/daloradius.conf.php').write_text(config)
            environment=os.environ.copy(); environment.update(MESSAGES_TEST_DB=DB,MESSAGES_TEST_USER='root')
            p=subprocess.run(['docker','run','-d','--name',WEB,'--network',NETWORK,
                '-e','MESSAGES_TEST_DB','-e','MESSAGES_TEST_USER','-e','PHP_CLI_SERVER_WORKERS=4',
                '-v',f'{fixture}:/fixtures','-w','/fixtures','--entrypoint','php',IMAGE,
                '-d','display_errors=0','-d','opcache.enable=0','-S','0.0.0.0:8080','-t','/fixtures/app'],
                env=environment,text=True,capture_output=True,timeout=60)
            assert p.returncode==0,'PHP fixture startup failed'
            address=run('docker','inspect','-f','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}',WEB)
            origin='http://'+address+':8080/'
            wait_for(lambda:Client(origin+'operators/').request('login.php')[0]==200,'PHP HTTP')
            print('RUNTIME: PHP '+run('docker','exec',WEB,'php','-r','echo PHP_VERSION;')+'; MariaDB '+sql('SELECT VERSION()'))

            def session(identity=9001,location='default',user=False):
                client=Client(origin+'operators/'); client.sid=secrets.token_hex(16)
                run('docker','exec','-i',WEB,'php','/fixtures/session.php',input=json.dumps(
                    {'sid':client.sid,'id':identity,'actor':actor,'location':location,'user':user}))
                return client

            def get(client):
                response=client.request('config-messages.php'); assert response[0]==200
                form=Form(response[3]); assert form.csrf and len(form.textareas)==3
                return form,response[3]

            def post(client,values):
                form,_=get(client)
                fields=[('csrf_token',form.csrf)]
                for key,value in values.items():fields.extend([(key+'_message_changed','yes'),(key+'_message',value)])
                return client.request('config-messages.php',fields)

            def state(database='radius'):
                return sql('SELECT id,type,content,COALESCE(modified_by,\'<NULL>\'),created_on,created_by '
                           'FROM messages_custom ORDER BY id',database)

            client=session(); initial=state()
            assert Client(origin+'operators/').request('config-messages.php')[0]==302
            assert session(9002).request('config-messages.php')[0]==302
            assert 'CSRF token error' in client.request('config-messages.php',
                [('csrf_token','bad'),('login_message_changed','yes'),('login_message','must not persist')])[3]
            assert state()==initial
            assert 'No messages have been updated' in post(client,{})[3]
            content='<p class="notice">Étoile O\'Reilly % + &amp; <strong>ok</strong><script>alert(1)</script></p>'
            values={'login':content,'support':'<p>private-marker-support</p>','dashboard':'<p>private-marker-dashboard</p>'}
            response=post(client,values)
            assert response[0]==200 and 'Updated messages of the following types: [login, support, dashboard]' in response[3]
            stored=state(); assert 'alert(1)' not in stored and '<script>' not in stored and "O'Reilly % +" in stored
            assert sql('SELECT COUNT(*) FROM messages_custom WHERE modified_by='+quote(actor)+
                       " AND created_by='fixture creator' AND created_on='2000-01-01' AND modified_on IS NOT NULL")=='3'
            debug=response[3].split('Debugging SQL Queries:',1)[-1]
            assert ('private-marker-support' in debug)==BASELINE
            assert post(client,values)[0]==200 and state()==stored,'Unchanged save differs'
            assert 'Updated messages' in post(client,{'support':''})[3]
            assert sql("SELECT content='' FROM messages_custom WHERE type='support'")=='1'
            post(client,{'support':values['support']}); assert state()==stored
            other=session(location='other'); before_other=state('radius_other')
            assert 'Updated messages' in post(other,{'login':'<p>other location</p>'})[3]
            assert state()==stored and state('radius_other')!=before_other
            print('PASS: gates, no-change and multi-message save, purification/quotes/Unicode, captions, idempotent save and named-location isolation')

            # Current portal entry points are PDO since R21; only the explicit fixture probe
            # retains PEAR until the common compatibility branch is retired in R29.
            portal=Client(origin+'users/'); response=portal.request('login.php')
            assert response[0]==200 and "O'Reilly % +" in response[3]
            user=session(user=True)
            req=urllib.request.Request(origin+'users/help-main.php',headers={'Cookie':'daloradius_user_sid='+user.sid})
            response=urllib.request.build_opener(NoRedirect()).open(req,timeout=25)
            assert response.status==200 and 'private-marker-support' in response.read().decode()
            result=client.request('messages-legacy.php?type=dashboard')
            assert result[0]==200, 'Legacy helper HTTP status: '+str(result[0])
            message=json.loads(result[3])
            assert message['content']=='<p>private-marker-dashboard</p>', 'Legacy helper content differs'
            print('PASS: real users login/help and explicitly retained dashboard PEAR helper remain usable')

            # Duplicated type rows remain updated together; rendering still reads the lowest id.
            sql("INSERT INTO messages_custom(type,content,created_on,created_by) VALUES('login','<p>duplicate</p>','2000-01-01','fixture creator')")
            response=post(client,{'login':'<p>all duplicates</p>'}); assert 'Updated messages' in response[3]
            assert sql("SELECT COUNT(*) FROM messages_custom WHERE type='login' AND content='<p>all duplicates</p>'")=='2'
            assert get(client)[0].textareas['login_message']=='<p>all duplicates</p>'
            print('PASS: duplicate-type update and first-id display semantics match the legacy editor')

            original=state()
            sql("DELIMITER //\nCREATE TRIGGER reject_support BEFORE UPDATE ON messages_custom FOR EACH ROW BEGIN IF NEW.type='support' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture late error'; END IF; END//\nDELIMITER ;")
            try:
                response=post(client,{'login':'<p>early change</p>','support':'<p>late change</p>'})
                if BASELINE:
                    assert state()!=original and sql("SELECT COUNT(*) FROM messages_custom WHERE type='login' AND content='<p>early change</p>'")=='2'
                else:
                    assert response[0]==200 and 'Unable to update user messages' in response[3]
                    assert 'SQLSTATE' not in response[3] and 'fixture late error' not in response[3]
                    assert state()==original,'Later SQL error did not roll back earlier type'
            finally:sql('DROP TRIGGER reject_support')
            print('PASS: baseline late-type failure retains earlier writes' if BASELINE else
                  'PASS: PDO later-type error rolls back the complete selected message batch')

            if not BASELINE:
                original=state()
                for extras in ([('login_message_changed','yes'),('login_message','early'),('support_message_changed','yes'),('support_message[]','bad')],
                    [('login_message_changed','yes'),('login_message','early'),('support_message_changed[]','yes')],
                    [('csrf_token[]','bad'),('login_message_changed','yes'),('login_message','early')]):
                    form,_=get(client); fields=list(extras)
                    if not any(k.startswith('csrf_token') for k,v in fields):fields.append(('csrf_token',form.csrf))
                    response=client.request('config-messages.php',fields)
                    assert response[0]==200 and state()==original
                sql('ALTER TABLE messages_custom ENGINE=MyISAM')
                try:
                    assert 'Unable to update' in post(client,{'login':'refused'})[3] and state()==original
                finally:sql('ALTER TABLE messages_custom ENGINE=InnoDB')
                sql('UPDATE messages_custom SET modified_by=NULL; ALTER TABLE messages_custom MODIFY modified_by VARCHAR(2) NULL')
                try:
                    before_short=state(); assert 'Unable to update' in post(client,{'login':'refused'})[3] and state()==before_short
                finally:sql('ALTER TABLE messages_custom MODIFY modified_by VARCHAR(32) NULL')
                sql("DELETE FROM messages_custom WHERE type='support'")
                before_missing=state()
                assert get(client)[0].textareas['support_message']==''
                assert 'Unable to update' in post(client,{'login':'earlier','support':'missing'})[3]
                assert state()==before_missing
                sql('RENAME TABLE messages_custom TO messages_unavailable')
                try:
                    response=client.request('config-messages.php');assert response[0]==200 and 'Unable to load' in response[3]
                    assert 'SQLSTATE' not in response[3] and 'messages_unavailable' not in response[3]
                finally:sql('RENAME TABLE messages_unavailable TO messages_custom')
                print('PASS: malformed later controls, CSRF arrays, missing selected rows, short storage, MyISAM and SQL read errors fail safely')

                # A second connection deliberately holds the row lock; an actual process-list
                # observation precedes the competing POST, not a blind sleep assumption.
                holder=subprocess.Popen(['docker','exec','-i',DB,'mariadb','-uroot','radius'],stdin=subprocess.PIPE,
                    stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL,text=True)
                holder.stdin.write("BEGIN; SELECT id FROM messages_custom WHERE type='login' ORDER BY id FOR UPDATE; DO SLEEP(3); COMMIT;")
                holder.stdin.close()
                wait_for(lambda:int(sql("SELECT COUNT(*) FROM INFORMATION_SCHEMA.PROCESSLIST WHERE INFO LIKE '%DO SLEEP(3)%' AND COMMAND<>'Sleep' AND INFO NOT LIKE '%PROCESSLIST%'"))>0,'second-connection row lock')
                try:
                    response=post(client,{'login':'<p>serialized update</p>'})
                    assert response[0]==200 and 'Updated messages' in response[3]
                    assert holder.wait(timeout=10)==0
                    assert sql("SELECT COUNT(*) FROM messages_custom WHERE type='login' AND content='<p>serialized update</p>'")=='2'
                finally:
                    if holder.poll() is None:holder.kill();holder.wait()
                print('PASS: native second-connection lock contention serializes the complete duplicate-type update')

            assert client.request('log-channel.php')[0]==200
            logs=run('docker','logs',WEB)
            assert 'FIXTURE_LOG_CHANNEL' in logs, 'PHP stderr log channel was not captured'
            assert 'PHP Fatal error' not in logs and 'PHP Warning' not in logs
            print('PASS: no PHP fatal errors/warnings in the exercised native paths')
        finally:
            run('docker','exec',WEB,'chown','-R',f'{os.getuid()}:{os.getgid()}','/fixtures',check=False)
            for name in (WEB,DB):run('docker','rm','-f',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            for name in (WEB,DB):assert not run('docker','ps','-a','-q','--filter','name=^/'+name+'$')
            assert not run('docker','network','ls','-q','--filter','name=^'+NETWORK+'$')
    print('PASS: disposable configuration/session/data resources removed; live lab untouched')


if __name__=='__main__':main()
