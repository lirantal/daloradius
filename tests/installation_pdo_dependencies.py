#!/usr/bin/env python3
"""R29 isolated package/PDO/Apache/SQL/PEAR Mail smoke; not full install/upgrade.
Build the repository Dockerfile first, then run --image <candidate-image>.
No CI integration. Historical PEAR fixtures need their separate legacy runtime.
"""
import argparse
import http.client
import json
from pathlib import Path
import re
import secrets
import socket
import subprocess
import time

ROOT = Path(__file__).resolve().parents[1]
SMTP = r'''
import socketserver
class Handler(socketserver.StreamRequestHandler):
 def handle(self):
  self.wfile.write(b'220 fixture ESMTP\r\n'); self.wfile.flush()
  data=False; body=[]
  while True:
   line=self.rfile.readline()
   if not line:return
   if data:
    if line==b'.\r\n':
     packet=b''.join(body)
     ok=b'R29 dependency SMTP smoke' in packet and b'MIME-Version:' in packet
     print('R29_SMTP_ACCEPTED' if ok else 'R29_SMTP_INVALID',flush=True)
     self.wfile.write(b'250 queued\r\n');data=False
    else:body.append(line)
   elif line.upper().startswith((b'EHLO',b'HELO')):self.wfile.write(b'250 fixture\r\n')
   elif line.upper().startswith((b'MAIL FROM:',b'RCPT TO:',b'RSET',b'NOOP')):self.wfile.write(b'250 OK\r\n')
   elif line.upper().startswith(b'DATA'):self.wfile.write(b'354 send data\r\n');data=True;body=[]
   elif line.upper().startswith(b'QUIT'):self.wfile.write(b'221 bye\r\n');self.wfile.flush();return
   else:self.wfile.write(b'500 unsupported\r\n')
   self.wfile.flush()
class Server(socketserver.TCPServer):allow_reuse_address=True
Server(('0.0.0.0',2525),Handler).serve_forever()
'''
PROBE = r'''<?php
header('Content-Type: application/json');
error_log('R29_LOG_PROBE');
require_once '/var/www/daloradius/app/common/includes/daloradius.conf.php';
require_once '/var/www/daloradius/app/common/includes/pdo_connection.php';
require_once 'Mail.php'; require_once 'Mail/mime.php';
try {
 $r=array('pdo_mysql'=>class_exists('PDO') && in_array('mysql',PDO::getAvailableDrivers(),true),
 'db_entry_absent'=>stream_resolve_include_path('DB.php')===false,
 'db_class_absent'=>!class_exists('DB',false),
 'mail'=>class_exists('Mail',false),'mime'=>class_exists('Mail_mime',false));
 foreach(array('mysql','mysqli') as $engine){
  $c=$configValues;$c['CONFIG_DB_ENGINE']=$engine;$pdo=dalo_pdo_connect($c);
  $s=$pdo->prepare('SELECT COUNT(*) FROM radacct WHERE username=?');$s->execute(array('r29-account'));
  $r[$engine.'_native_read']=(int)$s->fetchColumn()===1;
  $pdo->beginTransaction();$s=$pdo->prepare('INSERT INTO radreply(username,attribute,op,value) VALUES (?,?,?,?)');
  $s->execute(array('r29-caller','Class',':=','fixture'));$r[$engine.'_transaction']=$pdo->inTransaction();
  $pdo->rollBack();$s=$pdo->prepare('SELECT COUNT(*) FROM radreply WHERE username=?');$s->execute(array('r29-caller'));
  $r[$engine.'_rollback']=(int)$s->fetchColumn()===0;
 }
 $mime=new Mail_mime("\r\n");$mime->setTXTBody('R29 dependency SMTP smoke');
 $body=$mime->get(array('text_charset'=>'UTF-8'));
 $headers=$mime->headers(array('From'=>'fixture@example.invalid','To'=>'recipient@example.invalid','Subject'=>'R29 isolated probe'));
 $smtp=Mail::factory('smtp',array('host'=>'smtp','port'=>2525,'auth'=>false));
 $sent=$smtp->send('recipient@example.invalid',$headers,$body);$r['smtp']=!PEAR::isError($sent);
 http_response_code(in_array(false,$r,true)?500:200);echo json_encode($r);
} catch(Throwable $e){http_response_code(500);echo json_encode(array('error'=>'Isolated R29 dependency probe failed','class'=>get_class($e),'file'=>basename($e->getFile()),'line'=>$e->getLine()));}
'''


def run(*args, input=None):
    p = subprocess.run(args, input=input, text=True, capture_output=True)
    if p.returncode:
        raise RuntimeError('Fixture operation failed: ' + args[0])
    return p.stdout.strip()


def wait(predicate, label, seconds=90):
    deadline = time.monotonic() + seconds
    while time.monotonic() < deadline:
        try:
            if predicate():
                return
        except (RuntimeError, OSError):
            pass
        time.sleep(.2)
    raise RuntimeError('Fixture not ready: ' + label)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--image', required=True)
    args = parser.parse_args()
    suffix = secrets.token_hex(5)
    network, db, web, smtp = ['pdo-r29-' + name + '-' + suffix for name in ('net', 'db', 'web', 'smtp')]
    report = {'level': 'isolated rebuilt image, real Apache/PHP/MariaDB and PEAR Mail/Mime to internal SMTP; seeded session, no password login'}
    before = {name for name in run('docker', 'ps', '--format', '{{.Names}}').splitlines() if not name.startswith('pdo-r29-')}
    try:
        run('docker', 'network', 'create', '--internal', network)
        run('docker', 'run', '-d', '--name', db, '--network', network, '--network-alias', 'db',
            '--tmpfs', '/var/lib/mysql', '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1', '-e', 'MARIADB_DATABASE=radius', 'mariadb:11.8')
        def sql(text):
            return run('docker', 'exec', '-i', db, 'mariadb', '--protocol=TCP', '-h127.0.0.1', '-uroot', '-N', '-B', 'radius', input=text)
        wait(lambda: sql('SELECT 1') == '1', 'final MariaDB TCP server')
        for name in ('fr3-mariadb-freeradius.sql', 'mariadb-daloradius.sql'):
            sql((ROOT / 'contrib/db' / name).read_text())
        sql("INSERT INTO operators_acl(operator_id,file,access) VALUES (9001,'acct_username',1);"
            "INSERT INTO radacct(acctsessionid,acctuniqueid,username,acctinputoctets,acctoutputoctets) VALUES ('r29','r29','r29-account',1024,2048);")
        run('docker', 'run', '-d', '--name', smtp, '--network', network, '--network-alias', 'smtp',
            '--entrypoint', 'python3', 'python:3.13-alpine', '-u', '-c', SMTP)
        run('docker', 'run', '-d', '--name', web, '--network', network, '--tmpfs', '/r29-fixture',
            '--tmpfs', '/var/lib/php/sessions', '--entrypoint', 'bash', args.image, '-c',
            '. /etc/apache2/envvars; exec apache2 -DFOREGROUND')
        sid = secrets.token_hex(16)
        # Configuration/session values remain inside disposable container tmpfs.
        setup = r"""<?php
$c=file_get_contents('/var/www/daloradius/app/common/includes/daloradius.conf.php.sample');
$c=str_replace('?>','',$c);
$c.= <<<'R29CONFIG'

$configValues['CONFIG_DB_ENGINE']='mysqli';
$configValues['CONFIG_DB_HOST']='db';
$configValues['CONFIG_DB_PORT']='3306';
$configValues['CONFIG_DB_USER']='root';
$configValues['CONFIG_DB_PASS']='';
$configValues['CONFIG_DB_NAME']='radius';
R29CONFIG;
file_put_contents('/r29-fixture/config.php',$c);
symlink('/r29-fixture/config.php','/var/www/daloradius/app/common/includes/daloradius.conf.php');
session_name('daloradius_operator_sid');session_id('__SID__');session_start();
$_SESSION=array('daloradius_logged_in'=>true,'operator_id'=>9001,'operator_user'=>'fixture','time'=>time(),'location_name'=>'default');session_write_close();
$session=session_save_path().'/sess___SID__';chown($session,'www-data');chgrp($session,'www-data');
""".replace('__SID__', sid)
        run('docker', 'exec', '-i', web, 'php', input=setup)
        run('docker', 'exec', '-i', web, 'php', '-r',
            "file_put_contents('/var/www/daloradius/app/operators/r29_probe.php',stream_get_contents(STDIN));", input=PROBE)
        ip = run('docker', 'inspect', '-f', '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}', web)
        def ready():
            with socket.create_connection((ip, 8000), timeout=1):
                return True
        wait(ready, 'Apache operators port')
        def request(path, cookie=False):
            c = http.client.HTTPConnection(ip, 8000, timeout=30)
            c.request('GET', path, headers={'Cookie': 'daloradius_operator_sid=' + sid} if cookie else {})
            res = c.getresponse(); status, body = res.status, res.read().decode(); c.close()
            try:
                return status, json.loads(body)
            except json.JSONDecodeError:
                raise AssertionError(('Non-JSON fixture response', status, len(body))) from None
        status, result = request('/r29_probe.php')
        assert status == 200 and result and all(v is True for v in result.values()), ('Native dependency probe failed',status,result)
        report['apache_sql_mail'] = result
        status, data = request('/library/ajax/user_info.php?username=r29-account', True)
        assert status == 200 and data == {'upload': '1 KB', 'download': '2 KB'}, 'Actual nonempty accounting endpoint failed'
        report['actual_nonempty_accounting_endpoint'] = True
        sql('RENAME TABLE radacct TO r29_missing')
        status, data = request('/library/ajax/user_info.php?username=r29-account', True)
        assert status == 500 and data == {'error': 'Unable to load user information.'}, 'Actual endpoint SQL error contract failed'
        report['actual_sql_failure_redacted'] = True
        package = subprocess.run(['docker', 'exec', web, 'dpkg-query', '-W', '-f=${db:Status-Status}', 'php-db'], capture_output=True, text=True)
        assert package.stdout.strip() != 'installed'
        report['php_db_package_absent'] = True
        p = subprocess.run(['docker', 'logs', web], capture_output=True, text=True)
        logs = p.stdout + p.stderr
        # Apache can reopen its stderr onto the global error file before the
        # vhost /proc/self/fd/2 target is resolved. Inspect file logs too, returning
        # only booleans rather than retaining session/configuration material.
        file_flags = json.loads(run('docker', 'exec', web, 'php', '-r',
            "$logs='';foreach(array_merge(glob('/var/log/apache2/*.log'),glob('/var/log/apache2/daloradius/*.log'),array('/var/www/daloradius/var/log/daloradius.log')) as $f){if(is_file($f))$logs.=file_get_contents($f);}echo json_encode(array('marker'=>strpos($logs,'R29_LOG_PROBE')!==false,'clean'=>!preg_match('/PHP (?:Warning|Fatal error|Parse error)/',$logs)));"))
        assert 'R29_LOG_PROBE' in logs or file_flags['marker'], 'PHP log collection was not positively verified'
        assert not re.search(r'PHP (?:Warning|Fatal error|Parse error)', logs) and file_flags['clean'], 'Unexpected PHP diagnostic'
        mail = subprocess.run(['docker', 'logs', smtp], capture_output=True, text=True)
        assert 'R29_SMTP_ACCEPTED' in mail.stdout + mail.stderr and 'R29_SMTP_INVALID' not in mail.stdout + mail.stderr
        report['smtp_received_mime_message'] = True
        report['php_logs_clean_and_capture_verified'] = True
    finally:
        for name in (web, smtp, db):
            subprocess.run(['docker', 'rm', '-f', name], capture_output=True)
        subprocess.run(['docker', 'network', 'rm', network], capture_output=True)
        after = {name for name in run('docker', 'ps', '--format', '{{.Names}}').splitlines() if not name.startswith('pdo-r29-')}
        assert before == after, 'Fixture cleanup or unrelated running-container state changed'
        assert not any(name in run('docker', 'ps', '-a', '--format', '{{.Names}}').splitlines() for name in (web, smtp, db))
        assert network not in run('docker', 'network', 'ls', '--format', '{{.Name}}').splitlines()
    report['fixture_cleanup_verified'] = True
    print(json.dumps(report, indent=2))
    print('PASS R29 rebuilt runtime without PEAR DB; native PDO/Apache/SQL and retained MIME/SMTP')


if __name__ == '__main__':
    main()
