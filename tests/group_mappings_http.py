#!/usr/bin/env python3
"""UNIT-037 real PHP/PEAR/PDO/MariaDB shared-provider fixture, no live writes."""
import json
import os
import secrets
import shutil
import subprocess
import tempfile
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap

import operator_login_http as auth
from operator_login_http import Client, FormParser, login, quote, run, sql, wait_for

ROOT = Path(__file__).resolve().parents[1]
BASELINE = os.environ.get('GROUP_MAPPINGS_BASELINE') == '1'
BASE_COMMIT = 'd744a5653e382cd6025b816fd144e21d97bfbb47'
DB, WEB, NETWORK = auth.DB, auth.WEB, auth.NETWORK

FORM = '''<?php
include 'library/checklogin.php';
include_once '../common/includes/config_read.php';
include_once 'lang/main.php';
include_once '../common/includes/validation.php';
echo '<input name="csrf_token" value="'.htmlspecialchars(dalo_csrf_token(), ENT_QUOTES).'">';
'''
ENDPOINT = '''<?php
include 'library/checklogin.php';
include_once '../common/includes/config_read.php';
include_once 'lang/main.php';
include_once '../common/includes/validation.php';
require_once '../common/includes/pdo_connection.php';
header('Content-Type: application/json');
$logDebugSQL=''; $socket=null; $legacy=false;
try {
    $data=json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($data) || !is_string($data['csrf_token'] ?? null) ||
        !dalo_check_csrf_token($data['csrf_token'])) { throw new RuntimeException('Invalid request'); }
    $legacy=__BASELINE__ || ($data['mode'] ?? '') === 'legacy';
    include_once ($legacy ? 'include/management/functions-legacy.php' : 'include/management/functions.php');
    if ($legacy) {
        $db_error_handler=static function($error) {}; // Redact legacy driver error output only.
        include '../../legacy/app/common/includes/db_open.php'; $socket=$dbSocket;
    } else { $socket=dalo_pdo_connect($configValues, 'default'); }
    if ($data['action']==='exists') {
        $result=group_exists($socket, $data['group']);
        echo json_encode(array('ok'=>true,'result'=>$result)); exit;
    }
    if (!empty($data['bad_table'])) { $configValues[$data['bad_table']]='radusergroup;invalid'; }
    if (empty($data['no_tx'])) {
        if ($legacy) { $socket->autoCommit(false); }
        else { if (!$socket->beginTransaction()) { throw new RuntimeException('Transaction unavailable'); } }
        // A dependent caller write must be rolled back on a later provider failure.
        $prior="INSERT INTO radreply (username,attribute,op,value) VALUES ('unit037-sentinel','Reply-Message','=','caller')";
        if ($legacy) { $socket->query($prior); } else { $socket->exec($prior); }
    }
    switch ($data['action']) {
        case 'user': $result=insert_multiple_user_group_mappings($socket,$data['subject'],$data['groups']); break;
        case 'plan': $result=insert_multiple_plan_group_mappings($socket,$data['subject'],$data['groups']); break;
        case 'single': $result=insert_single_user_group_mapping($socket,$data['subject'],$data['group'],$data['priority'] ?? 0); break;
        case 'priority': $result=update_user_group_mapping_priority($socket,$data['subject'],$data['group'],$data['priority']); break;
        default: throw new RuntimeException('Unknown fixture action');
    }
    if (!$legacy && !$socket->inTransaction()) { throw new RuntimeException('Provider took transaction ownership'); }
    if (!empty($data['check_templates']) &&
        (strpos($logDebugSQL, $data['subject']) !== false ||
         strpos($logDebugSQL, $data['group'] ?? $data['groups'][0]) !== false)) {
        throw new RuntimeException('Provider debug leaked a bound value');
    }
    if (!empty($data['abort'])) { throw new RuntimeException('Caller requested rollback'); }
    if ($legacy) { $socket->commit(); } else { if (!$socket->commit()) { throw new RuntimeException('Commit unavailable'); } }
    echo json_encode(array('ok'=>true,'result'=>$result));
} catch (Throwable $error) {
    if ($socket instanceof PDO && $socket->inTransaction()) { $socket->rollBack(); }
    elseif ($legacy && is_object($socket)) { $socket->rollback(); }
    echo json_encode(array('ok'=>false));
}
'''


def state():
    return (sql('SELECT id,username,groupname,priority FROM radusergroup ORDER BY id'),
            sql('SELECT id,plan_name,profile_name FROM billing_plans_profiles ORDER BY id'),
            sql("SELECT id,username,attribute,op,value FROM radreply ORDER BY id"))


def call(client, action, **fields):
    status, _, _, body = client.request('mapping_form.php')
    assert status == 200, ('mapping form status', status)
    parser = FormParser(); parser.feed(body); assert parser.csrf
    payload = dict(fields, action=action, csrf_token=parser.csrf)
    # Same authenticated client/opener, JSON arrays preserve malformed shapes.
    import urllib.request
    request = urllib.request.Request(client.base + 'mapping_call.php',
                                     data=json.dumps(payload).encode(),
                                     headers={'Content-Type': 'application/json',
                                              'Cookie': 'daloradius_operator_sid=' + client.sid})
    response = client.opener.open(request, timeout=40)
    assert response.status == 200
    return json.loads(response.read())


def main():
    scratch = Path.home() / '.hermes/cache/scratch'; scratch.mkdir(parents=True, exist_ok=True)
    operator = 'u37-op-' + secrets.token_hex(5); password = secrets.token_urlsafe(24)
    user = 'u37-user-' + secrets.token_hex(4); plan = 'u37-plan-' + secrets.token_hex(4)
    first = 'u37-check-' + secrets.token_hex(4); second = 'u37-reply-' + secrets.token_hex(4)
    disabled = 'daloRADIUS-Disabled-Users'
    with tempfile.TemporaryDirectory(prefix='dalo-group-mappings-', dir=scratch) as directory:
        fixture = Path(directory); shutil.copytree(ROOT / 'app', fixture / 'app', symlinks=True, ignore=shutil.ignore_patterns('daloradius.conf.php'))
        restore_pear_bootstrap(fixture / 'legacy', BASE_COMMIT if BASELINE else '2753c9d1c2fb922e64cf978d889f49626042c25b')
        # R05 migrated the live page; pin its previous PEAR producer for compatibility coverage.
        legacy_page = run('git', 'show', 'fb33d38a505bf8d3a1cfcd3d987b3f67d939932b:app/operators/mng-rad-usergroup-new.php')
        # R28 removes obsolete PEAR dispatch; historical requests use a pinned helper.
        legacy_functions = run('git', 'show', (BASE_COMMIT if BASELINE else '2753c9d1c2fb922e64cf978d889f49626042c25b') + ':app/operators/include/management/functions.php')
        (fixture / 'app/operators/include/management/functions-legacy.php').write_text(legacy_functions + '\n')
        legacy_page = legacy_page.replace("include/management/functions.php", "include/management/functions-legacy.php").replace("../common/includes/db_open.php", "../../legacy/app/common/includes/db_open.php").replace("../common/includes/db_close.php", "../../legacy/app/common/includes/db_close.php")
        (fixture / 'app/operators/mng-rad-usergroup-new.php').write_text(legacy_page + '\n')
        if BASELINE:
            old = run('git', 'show', BASE_COMMIT + ':app/operators/include/management/functions.php')
            (fixture / 'app/operators/include/management/functions.php').write_text(old + '\n')
        (fixture / 'legacy/app/common/includes/config_read.php').write_text("<?php require '/fixtures/app/common/includes/config_read.php';")
        (fixture / 'legacy/app/common/includes/pdo_connection.php').write_text("<?php require_once '/fixtures/app/common/includes/pdo_connection.php';")
        for name in ('db_table_conventions.php',):
            shutil.copyfile(fixture / 'app/common/includes' / name, fixture / 'legacy/app/common/includes' / name)
        (fixture / 'app/operators/mapping_form.php').write_text(FORM)
        (fixture / 'app/operators/mapping_call.php').write_text(ENDPOINT.replace('__BASELINE__', 'true' if BASELINE else 'false'))
        try:
            run('docker', 'network', 'create', '--internal', NETWORK)
            run('docker', 'run', '-d', '--name', DB, '--network', NETWORK, '--tmpfs', '/var/lib/mysql',
                '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1', '-e', 'MARIADB_DATABASE=radius', 'mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'), 'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql', 'mariadb-daloradius.sql'):
                sql((ROOT / 'contrib/db' / name).read_text())
            config = (ROOT / 'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>', '')
            for key, value in {'CONFIG_DB_ENGINE':'mysqli','CONFIG_DB_HOST':DB,'CONFIG_DB_PORT':'3306',
                'CONFIG_DB_USER':'root','CONFIG_DB_PASS':'','CONFIG_DB_NAME':'radius',
                'CONFIG_LOG_PAGES':'no','CONFIG_LOG_QUERIES':'no','CONFIG_LOG_ACTIONS':'no',
                'CONFIG_DEBUG_SQL':'no','CONFIG_DEBUG_SQL_ONPAGE':'no'}.items():
                config += '\n$configValues[%s] = %s;\n' % (quote(key), quote(value))
            (fixture / 'app/common/includes/daloradius.conf.php').write_text(config)
            run('docker', 'run', '-d', '--name', WEB, '--network', NETWORK,
                '-v', f'{fixture}:/fixtures', '-w', '/fixtures/app/operators',
                '-e', 'PHP_CLI_SERVER_WORKERS=2', '--entrypoint', 'php', 'lirantal/daloradius',
                '-d', 'display_errors=0', '-S', '0.0.0.0:8080', '-t', '.')
            address = run('docker', 'inspect', '-f', '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}', WEB)
            base = 'http://' + address + ':8080/'
            wait_for(lambda: Client(base).request('login.php')[0] == 200, 'HTTP')
            auth.add_operator(operator, auth.hash_password(WEB, password))
            sql("INSERT INTO radcheck (username,attribute,op,value) VALUES (%s,'Filter-Id','=','fixture')" % quote(user))
            sql('INSERT INTO billing_plans (planName) VALUES (%s)' % quote(plan))
            for table, group in (('radgroupcheck', first), ('radgroupreply', second), ('radgroupcheck', disabled)):
                sql("INSERT INTO " + table + " (groupname,attribute,op,value) VALUES (%s,'Filter-Id','=','fixture')" % quote(group))
            client = Client(base)
            status, _, headers, _ = login(client, operator, password, 'local', 'default')
            assert status == 302 and headers.get('Location', '').endswith('index.php')
            for group in (first, second, disabled):
                assert call(client, 'exists', group=group) == {'ok':True,'result':True}
            assert call(client, 'exists', group='absent-group')['result'] is BASELINE
            print('PASS: group lookup checks both sources; baseline predicate defect classified')

            assert call(client, 'user', subject=user, groups=[first, second, first, '']) == {'ok':True,'result':2}
            assert sql('SELECT groupname,priority FROM radusergroup WHERE username=%s ORDER BY id' % quote(user)) == first+'\t0\n'+second+'\t0'
            assert call(client, 'plan', subject=plan, groups=[first, second, first, '']) == {'ok':True,'result':2}
            assert sql('SELECT profile_name FROM billing_plans_profiles WHERE plan_name=%s ORDER BY id' % quote(plan)) == first+'\n'+second
            assert call(client, 'single', subject=user, group=disabled, priority=99) == {'ok':True,'result':True}
            assert sql('SELECT priority FROM radusergroup WHERE username=%s AND groupname=%s' % (quote(user),quote(disabled))) == '-1'
            assert call(client, 'priority', subject=user, group=first, priority=-8) == {'ok':True,'result':True}
            assert sql('SELECT priority FROM radusergroup WHERE username=%s AND groupname=%s' % (quote(user),quote(first))) == '0'
            print('PASS: ordinary PEAR/PDO mappings, deduplication, blanks and reserved priorities')

            if BASELINE:
                # Prove, rather than infer, the legacy partial-success contract.
                for action, table, column, subject in (
                    ('user','radusergroup','groupname',user),
                    ('plan','billing_plans_profiles','profile_name',plan)):
                    trigger = 'u37_baseline_late_' + action
                    sql("DELIMITER //\nCREATE TRIGGER "+trigger+" BEFORE INSERT ON "+table+" FOR EACH ROW "
                        "BEGIN IF NEW."+column+"="+quote(second)+" THEN SIGNAL SQLSTATE '45000' "
                        "SET MESSAGE_TEXT='fixture late mapping'; END IF; END//\nDELIMITER ;\n")
                    before_count = sql('SELECT COUNT(*) FROM '+table)
                    try:
                        assert call(client, action, subject=subject, groups=[first,second]) == {'ok':True,'result':1}
                        assert int(sql('SELECT COUNT(*) FROM '+table)) == int(before_count) + 1
                    finally:
                        sql('DROP TRIGGER '+trigger)
                print('PASS: pinned PEAR baseline commits partial batches after later SQL failure')

            if not BASELINE:
                # Coexisting PEAR dispatch and the actual legacy page remain usable.
                assert call(client, 'exists', group=first, mode='legacy')['result'] is True
                assert call(client, 'exists', group='absent-group', mode='legacy')['result'] is False
                before = state()
                assert call(client, 'single', subject=user, group='absent-group', priority=0, mode='legacy')['result'] is False
                assert state()[0] == before[0]
                assert call(client, 'user', subject=user, groups=[second], mode='legacy')['result'] == 1
                oid = sql('SELECT id FROM operators WHERE username=%s' % quote(operator))
                sql("INSERT INTO operators_acl (operator_id,file,access) VALUES (%s,'mng_rad_usergroup_new',1)" % oid)
                status, _, _, page = client.request('mng-rad-usergroup-new.php')
                parser = FormParser(); parser.feed(page); assert status == 200 and parser.csrf
                assert 'DB Error' not in page
                http_group = 'u37-pear-http-' + secrets.token_hex(4)
                sql("INSERT INTO radgroupreply (groupname,attribute,op,value) VALUES (%s,'Filter-Id','=','fixture')" % quote(http_group))
                status, _, _, page = client.request('mng-rad-usergroup-new.php', [
                    ('csrf_token', parser.csrf), ('username', user), ('group', http_group), ('priority', '6')])
                assert status == 200 and 'Added new user-group mapping' in page
                assert sql('SELECT COUNT(*) FROM radusergroup WHERE username=%s AND groupname=%s AND priority=6' %
                           (quote(user),quote(http_group))) == '1'
                status, _, _, page = client.request('mng-rad-usergroup-new.php')
                parser = FormParser(); parser.feed(page); assert parser.csrf
                before = state()
                status, _, _, page = client.request('mng-rad-usergroup-new.php', [
                    ('csrf_token', parser.csrf), ('username', user), ('group', 'absent-group'), ('priority', '6')])
                assert status == 200 and 'DB Error when adding' in page and state() == before
                print('PASS: pinned PEAR page creates valid mapping and rejects nonexistent group')

                # Validate every input before the first provider write; caller rollback includes sentinel.
                for action, fields in (
                    ('user', dict(subject=user, groups=[first,'absent-group'])),
                    ('plan', dict(subject=plan, groups=[first,'absent-group'])),
                    ('user', dict(subject=user, groups=[first,['nested']])),
                    ('user', dict(subject=[user], groups=[first])),
                    ('user', dict(subject=user, groups=[first,'x'*65])),
                    ('user', dict(subject='missing-user', groups=[first])),
                    ('plan', dict(subject='missing-plan', groups=[first])),
                    ('single', dict(subject=user, group=first, priority=['bad'])),
                    ('single', dict(subject=user, group=first, priority='2147483648')),
                    ('user', dict(subject=user, groups=[first], no_tx=True)),
                    ('single', dict(subject=user, group=first, priority=0, no_tx=True)),
                    ('plan', dict(subject=plan, groups=[first], no_tx=True)),
                    ('priority', dict(subject=user, group=first, priority=0, no_tx=True)),
                    ('user', dict(subject=user, groups=[first], bad_table='CONFIG_DB_TBL_RADUSERGROUP')),
                    ('plan', dict(subject=plan, groups=[first], bad_table='CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES'))):
                    before = state(); assert call(client, action, **fields) == {'ok':False}; assert state() == before
                before = state(); assert call(client, 'user', subject=user, groups=[first], abort=True) == {'ok':False}; assert state() == before
                print('PASS: stale, malformed and oversized inputs plus caller-owned rollback')

                # A second insertion must undo the first provider row AND the caller's prior write.
                for action, table, column, subject in (
                    ('user','radusergroup','groupname',user),
                    ('plan','billing_plans_profiles','profile_name',plan)):
                    trigger = 'u37_late_'+action
                    sql("DELIMITER //\nCREATE TRIGGER "+trigger+" BEFORE INSERT ON "+table+" FOR EACH ROW "
                        "BEGIN IF NEW."+column+"="+quote(second)+" THEN SIGNAL SQLSTATE '45000' "
                        "SET MESSAGE_TEXT='fixture late mapping'; END IF; END//\nDELIMITER ;\n")
                    try:
                        before=state(); assert call(client, action, subject=subject, groups=[first,second]) == {'ok':False}; assert state()==before
                    finally: sql('DROP TRIGGER '+trigger)
                print('PASS: later mapping failure rolls back whole batch and dependent caller write')

                # Duplicate collapse on priority update is also transactional.
                sql('INSERT INTO radusergroup (username,groupname,priority) VALUES (%s,%s,7)' % (quote(user),quote(first)))
                before=state()
                sql("CREATE TRIGGER u37_priority_failure BEFORE INSERT ON radusergroup FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture collapse failure'")
                try:
                    assert call(client,'priority',subject=user,group=first,priority=3)=={'ok':False};assert state()==before
                finally:sql('DROP TRIGGER u37_priority_failure')
                assert call(client,'priority',subject=user,group=first,priority=3)['result'] is True
                assert sql('SELECT priority FROM radusergroup WHERE username=%s AND groupname=%s' % (quote(user),quote(first)))=='3'
                assert call(client,'priority',subject=user,group=first,priority=3)['result'] is True
                print('PASS: priority duplicate-collapse rollback, success and idempotent update')

                # Rejected engines on every write/locking table, not just the destination.
                for table in ('radusergroup','radcheck','radgroupcheck','radgroupreply','billing_plans_profiles','billing_plans'):
                    action='plan' if table.startswith('billing_') else 'user'
                    subject=plan if action=='plan' else user
                    sql('ALTER TABLE '+table+' ENGINE=MyISAM')
                    try:
                        before=state();assert call(client,action,subject=subject,groups=[first])=={'ok':False};assert state()==before
                    finally:sql('ALTER TABLE '+table+' ENGINE=InnoDB')
                print('PASS: nontransactional parents, sources and target tables rejected')

                # Special values are bound; provider SQL debug contains templates, never names.
                special_user="u37-%-é-'"; special_group="g37-%-é-'"; special_plan="p37-%-é-'"
                sql("INSERT INTO radcheck (username,attribute,op,value) VALUES (%s,'Filter-Id','=','fixture')" % quote(special_user))
                sql("INSERT INTO radgroupreply (groupname,attribute,op,value) VALUES (%s,'Filter-Id','=','fixture')" % quote(special_group))
                sql('INSERT INTO billing_plans (planName) VALUES (%s)' % quote(special_plan))
                assert call(client,'single',subject=special_user,group=special_group,priority=4,check_templates=True)['result'] is True
                assert call(client,'plan',subject=special_plan,groups=[special_group],check_templates=True)['result']==1
                assert sql('SELECT COUNT(*) FROM radusergroup WHERE username=%s AND groupname=%s AND priority=4' % (quote(special_user),quote(special_group)))=='1'
                print('PASS: quote, Unicode and literal percent names preserved')

                # Concurrent cooperating requests serialize on the existing parent row.
                race_user='u37-race-'+secrets.token_hex(4)
                sql("INSERT INTO radcheck (username,attribute,op,value) VALUES (%s,'Filter-Id','=','fixture')" % quote(race_user))
                clients=[]
                for _ in range(2):
                    c=Client(base);assert login(c,operator,password,'local','default')[0]==302;clients.append(c)
                with ThreadPoolExecutor(max_workers=2) as pool:
                    outcomes=list(pool.map(lambda c:call(c,'single',subject=race_user,group=first,priority=5),clients))
                assert all(r['ok'] for r in outcomes)
                assert sorted(r['result'] for r in outcomes)==[False,True]
                assert sql('SELECT COUNT(*) FROM radusergroup WHERE username=%s AND groupname=%s' % (quote(race_user),quote(first)))=='1'
                print('PASS: independent concurrent HTTP sessions insert one single mapping')

                # Deterministic overlap with a separate connection holding the parent lock.
                import time
                guard = "chdir('/fixtures/app/operators'); $_SERVER['PHP_SELF']='mapping_guard.php';" \
                    "include '../common/includes/config_read.php'; require '../common/includes/pdo_connection.php';" \
                    "$p=dalo_pdo_connect($configValues);$p->beginTransaction();" \
                    "$s=$p->prepare('SELECT id FROM radcheck WHERE username=? FOR UPDATE');" \
                    "$s->execute(array($argv[1]));echo $s->fetchColumn()!==false?'1':'0';" \
                    "flush();sleep(2);$p->rollBack();"
                holder = subprocess.Popen(['docker','exec',WEB,'php','-r',guard,race_user],
                                          stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
                try:
                    assert holder.stdout.read(1) == '1'
                    start = time.monotonic()
                    assert call(client,'priority',subject=race_user,group=first,priority=6)['result'] is True
                    assert time.monotonic() - start >= 1.0
                    holder.communicate(timeout=20)
                    assert holder.returncode == 0
                finally:
                    if holder.poll() is None:
                        holder.terminate(); holder.communicate(timeout=20)
                assert sql('SELECT priority FROM radusergroup WHERE username=%s AND groupname=%s' %
                           (quote(race_user),quote(first))) == '6'
                print('PASS: priority mutation waits for a real second-connection parent lock')

            logs = subprocess.run(['docker','logs',WEB],capture_output=True,text=True,timeout=30,check=True)
            combined=logs.stdout+logs.stderr
            assert 'PHP Fatal error' not in combined and 'PHP Warning' not in combined and password not in combined
            print('PASS: no PHP warnings, fatal errors or logged fixture password')
        finally:
            logs = subprocess.run(['docker','logs',WEB],capture_output=True,text=True,check=False)
            for line in (logs.stdout + logs.stderr).splitlines():
                if 'PHP Fatal error:' in line:
                    print('FIXTURE_FATAL: PHP fatal detected (details omitted)')
            run('docker','exec','-u','root',WEB,'chown','-R',f'{os.getuid()}:{os.getgid()}','/fixtures',check=False)
            for name in (WEB,DB):run('docker','rm','-f','-v',name,check=False)
            run('docker','network','rm',NETWORK,check=False)
            print('CLEANUP: disposable mapping fixture removed')

if __name__ == '__main__':
    main()
