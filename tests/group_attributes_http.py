#!/usr/bin/env python3
"""UNIT-036 differential group attribute HTTP/PHP/MariaDB test."""
import os
import secrets
import shutil
import subprocess
import tempfile
from pathlib import Path
from pear_baseline_fixture import restore_pear_bootstrap

import operator_login_http as auth
from operator_login_http import Client, FormParser, add_operator, hash_password, login, quote, run, sql, wait_for

ROOT = Path(__file__).resolve().parents[1]
BASELINE = os.environ.get('GROUP_ATTRIBUTES_BASELINE') == '1'
BASE_COMMIT = '9cf49ec788a9cd3f85f13c17dae935d997a21db3'
DB, WEB, NETWORK = auth.DB, auth.WEB, auth.NETWORK


def state():
    # Values are returned only for assertions inside the disposable fixture.
    return sql("SELECT 'check',id,HEX(groupname),HEX(attribute),op,HEX(value) FROM radgroupcheck "
               "UNION ALL SELECT 'reply',id,HEX(groupname),HEX(attribute),op,HEX(value) "
               "FROM radgroupreply ORDER BY 1,2")


def form(client, page):
    status, url, _, body = client.request(page)
    assert status == 200, (page, status)
    parser = FormParser(); parser.feed(body)
    if not parser.csrf:
        import re
        failure = re.search(r'<div class="failure">(.*?)</div>', body, re.S)
        safe_failure = re.sub(r'(?is)Debug info:.*', '[debug details omitted]', failure.group(1)) if failure else '[no rendered failure block]'
        safe_failure = re.sub(r'<[^>]+>', ' ', safe_failure)
        safe_failure = re.sub(r'\s+', ' ', safe_failure)[:500]
        print('FORM_DIAGNOSTIC', page, 'url=', url, 'csrf_name_count=', body.count('csrf_token'),
                            'group_info=', 'GroupInfo' in body, 'permission=', 'permission' in body.lower(),
              'home_error=', 'home-error.php' in body, 'failure=', safe_failure)
    assert parser.csrf, (page, body[:200])
    return parser.csrf


def post(client, page, fields):
    status, _, _, body = client.request(page, fields)
    assert status == 200, (page, status)
    return body


def attrs(attribute, value, op=':=', table='check', number=2):
    return [(f'dictValues{number}[]', item) for item in (attribute, value, op, table)]


def create(client, kind, group, attribute, value, op=':=', table='check'):
    page = f'mng-rad-group{kind}-new.php'
    csrf = form(client, page)
    return post(client, page, [('csrf_token', csrf), ('groupname', group)] +
                attrs(attribute, value, op, table))


def edit(client, kind, item, group, attribute, value, op=':='):
    page = f'mng-rad-group{kind}-edit.php?item={item}'
    csrf = form(client, page)
    return post(client, f'mng-rad-group{kind}-edit.php', [
        ('csrf_token', csrf), ('item', item), ('groupname', group),
        ('attribute', attribute), ('op', op), ('value', value),
    ])


def add_acl(operator_id):
    files = (
        'mng_rad_groupcheck_new', 'mng_rad_groupcheck_edit',
        'mng_rad_groupreply_new', 'mng_rad_groupreply_edit',
    )
    for filename in files:
        sql('INSERT INTO operators_acl (operator_id,file,access) VALUES (%s,%s,1)' %
            (operator_id, quote(filename)))


def main():
    scratch = Path.home() / '.hermes/cache/scratch'; scratch.mkdir(parents=True, exist_ok=True)
    operator = 'u36-op-' + secrets.token_hex(6)
    password = secrets.token_urlsafe(24)
    group = 'u36-group-' + secrets.token_hex(5)
    second_group = 'u36-second-' + secrets.token_hex(5)
    percent_group = 'u36-%-' + secrets.token_hex(5)
    with tempfile.TemporaryDirectory(prefix='dalo-group-attributes-', dir=scratch) as directory:
        fixture = Path(directory)
        shutil.copytree(ROOT / 'app', fixture / 'app', symlinks=True,ignore=shutil.ignore_patterns('daloradius.conf.php'))
        if BASELINE:
            restore_pear_bootstrap((fixture / 'app').parent, BASE_COMMIT)
            for filename in ('mng-rad-groupcheck-new.php', 'mng-rad-groupcheck-edit.php',
                             'mng-rad-groupreply-new.php', 'mng-rad-groupreply-edit.php'):
                old = run('git', 'show', BASE_COMMIT + ':app/operators/' + filename)
                (fixture / 'app/operators' / filename).write_text(old + '\n')
        try:
            run('docker', 'network', 'create', '--internal', NETWORK)
            run('docker', 'run', '-d', '--name', DB, '--network', NETWORK, '--tmpfs', '/var/lib/mysql',
                '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1', '-e', 'MARIADB_DATABASE=radius',
                'mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'), 'MariaDB')
            for name in ('fr3-mariadb-freeradius.sql', 'mariadb-daloradius.sql',
                         'mariadb-daloradius-dictionaries.sql'):
                sql((ROOT / 'contrib/db' / name).read_text())
            fixture_tables = sql("SELECT table_name FROM information_schema.tables WHERE table_schema='radius' AND table_name IN ('radgroupcheck','radgroupreply','radusergroup') ORDER BY table_name")
            assert fixture_tables.splitlines() == ['radgroupcheck', 'radgroupreply', 'radusergroup'], 'FreeRADIUS group fixture tables were not all imported'
            print('FIXTURE_GROUP_TABLES', fixture_tables.replace('\n', ','))
            sql('SELECT DISTINCT(groupname) FROM radgroupcheck UNION SELECT DISTINCT(groupname) FROM radgroupreply UNION SELECT DISTINCT(groupname) FROM radusergroup')
            print('FIXTURE_GROUP_UNION', 'ok')
            config = (ROOT / 'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>', '')
            overrides = {
                'CONFIG_DB_ENGINE': 'mysqli', 'CONFIG_DB_HOST': DB, 'CONFIG_DB_PORT': '3306',
                'CONFIG_DB_USER': 'root', 'CONFIG_DB_PASS': '', 'CONFIG_DB_NAME': 'radius',
                'CONFIG_LOG_PAGES': 'no', 'CONFIG_LOG_QUERIES': 'no', 'CONFIG_LOG_ACTIONS': 'no',
                'CONFIG_DEBUG_SQL': 'no', 'CONFIG_DEBUG_SQL_ONPAGE': 'no',
            }
            for key, value in overrides.items():
                config += "\n$configValues[%s] = %s;\n" % (quote(key), quote(value))
            (fixture / 'app/common/includes/daloradius.conf.php').write_text(config)
            run('docker', 'run', '-d', '--name', WEB, '--network', NETWORK,
                '-v', f'{fixture}:/fixtures', '-w', '/fixtures/app/operators',
                '--entrypoint', 'php', 'lirantal/daloradius', '-d', 'display_errors=0',
                '-S', '0.0.0.0:8080', '-t', '.')
            address = run('docker', 'inspect', '-f', '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}', WEB)
            base = 'http://' + address + ':8080/'
            wait_for(lambda: Client(base).request('login.php')[0] == 200, 'operators HTTP')

            add_operator(operator, hash_password(WEB, password))
            oid = sql('SELECT id FROM operators WHERE username=%s' % quote(operator))
            add_acl(oid)
            # get_groups() is intentionally the legacy PEAR read: seed two selectable groups.
            sql("INSERT INTO radgroupreply (groupname,attribute,op,value) VALUES "
                "(%s,'Reply-Name','=', 'seed')" % quote(group))
            sql("INSERT INTO radgroupreply (groupname,attribute,op,value) VALUES "
                "(%s,'Reply-Name','=', 'seed2')" % quote(second_group))
            if not BASELINE:
                sql("INSERT INTO radgroupreply (groupname,attribute,op,value) VALUES "
                    "(%s,'Reply-Name','=', 'percent')" % quote(percent_group))

            client = Client(base)
            status, _, headers, _ = login(client, operator, password, 'local', 'default')
            assert status == 302 and headers.get('Location', '').endswith('index.php')

            normal_value = 'fixture-value'
            page = create(client, 'check', group, 'Filter-Id', normal_value)
            assert 'Successfully added a new groupcheck item' in page
            assert sql("SELECT COUNT(*) FROM radgroupcheck WHERE groupname=%s AND attribute='Filter-Id' "
                       "AND value=%s" % (quote(group), quote(normal_value))) == '1'
            check_id = sql("SELECT id FROM radgroupcheck WHERE groupname=%s AND attribute='Filter-Id' "
                           "ORDER BY id DESC LIMIT 1" % quote(group))
            print('PASS: groupcheck create through HTTP with dictionary-shaped attribute')

            edited_value = 'edited-value'
            page = edit(client, 'check', 'groupcheck-' + check_id, second_group,
                        'Filter-Id', edited_value, '=')
            assert ('Successfully updated radgroupcheck item' if BASELINE else 'Successfully updated groupcheck item') in page
            assert sql("SELECT groupname,attribute,op,value FROM radgroupcheck WHERE id=%s" % check_id) == \
                second_group + '\tFilter-Id\t=\t' + edited_value
            print('PASS: groupcheck edit changes group, attribute operator, and value')

            reply_value = 'reply-value'
            page = create(client, 'reply', group, 'Reply-Name', reply_value, '=', 'reply')
            assert 'Successfully added a new groupreply item' in page
            assert sql("SELECT COUNT(*) FROM radgroupreply WHERE groupname=%s AND attribute='Reply-Name' "
                       "AND value=%s" % (quote(group), quote(reply_value))) == '1'
            print('PASS: groupreply create and PDO lastInsertId path')

            reply_id = sql("SELECT id FROM radgroupreply WHERE groupname=%s AND attribute='Reply-Name' "
                           "AND value=%s ORDER BY id DESC LIMIT 1" % (quote(group), quote(reply_value)))
            page = edit(client, 'reply', 'groupreply-' + reply_id, second_group,
                        'Reply-Name', 'reply-edited', '=')
            assert ('Successfully updated radgroupreply item' if BASELINE else 'Successfully updated groupreply item') in page
            print('PASS: groupreply edit persists through PDO')
            assert sql('SELECT groupname,attribute,op,value FROM radgroupreply WHERE id=' + reply_id) == \
                second_group + '\tReply-Name\t=\treply-edited'

            # The legacy text field allows moving an attribute into a new group.
            destination = 'u36-new-destination-' + secrets.token_hex(4)
            for kind, row_id, attribute, original_value in (
                    ('check', check_id, 'Filter-Id', edited_value),
                    ('reply', reply_id, 'Reply-Name', 'reply-edited')):
                item = 'group' + kind + '-' + row_id
                page = edit(client, kind, item, destination + '-' + kind, attribute, original_value, '=')
                expected = 'Successfully updated ' + ('radgroup' if BASELINE else 'group') + kind + ' item'
                assert expected in page, 'Edit must accept a new destination group: ' + kind
                assert sql('SELECT groupname FROM radgroup' + kind + ' WHERE id=' + row_id) == destination + '-' + kind
                page = edit(client, kind, item, second_group, attribute, original_value, '=')
                assert expected in page
            print('PASS: edits can move an attribute to a new destination group, as on PEAR')

            if not BASELINE:
                # Literal percent, value 0, malformed/stale IDs, and duplicate protection.
                page = create(client, 'check', percent_group, 'Reply-Name', '0', '=', 'check')
                assert 'Successfully added a new groupcheck item' in page
                assert sql("SELECT COUNT(*) FROM radgroupcheck WHERE groupname=%s AND value='0'" %
                           quote(percent_group)) == '1'
                print('PASS: literal percent and value 0 survive create')

                before = state()
                csrf = form(client, 'mng-rad-groupcheck-edit.php?item=groupcheck-' + check_id)
                page = post(client, 'mng-rad-groupcheck-edit.php', [
                    ('csrf_token', 'invalid-token'), ('item', 'groupcheck-' + check_id),
                    ('groupname', second_group), ('attribute', 'Filter-Id'), ('op', '='), ('value', 'csrf-rejected')])
                assert 'CSRF token error' in page and state() == before
                print('PASS: invalid CSRF is rejected before writes')

                csrf = form(client, 'mng-rad-groupcheck-edit.php?item=groupcheck-' + check_id)
                page = post(client, 'mng-rad-groupcheck-edit.php', [
                    ('csrf_token', csrf), ('item', 'groupcheck-999999999'),
                    ('groupname', second_group), ('attribute', 'Filter-Id'), ('op', '='), ('value', 'x')])
                assert state() == before and 'SQLSTATE' not in page
                print('PASS: stale group attribute ID is rejected without mutation')

                before = state()
                csrf = form(client, 'mng-rad-groupcheck-edit.php?item=groupcheck-' + check_id)
                page = post(client, 'mng-rad-groupcheck-edit.php', [
                    ('csrf_token', csrf), ('item', 'groupcheck-' + check_id),
                    ('groupname[]', second_group), ('attribute', 'Filter-Id'),
                    ('op', '='), ('value', 'array-rejected')])
                assert state() == before and 'SQLSTATE' not in page
                print('PASS: array-typed edit controls are rejected before writes')

                # Seed another matching attribute value, then verify duplicate rejection.
                sql("INSERT INTO radgroupcheck (groupname,attribute,op,value) VALUES (%s,'Filter-Id','=','duplicate-value')" %
                    quote(second_group))
                before = state()
                page = edit(client, 'check', 'groupcheck-' + check_id, second_group,
                            'Filter-Id', 'duplicate-value', '=')
                assert 'duplicate entry' in page and state() == before
                print('PASS: duplicate group attribute is rejected transactionally')

                # Force a late insert failure: first row must roll back with the second.
                before = state()
                sql("DELIMITER //\nCREATE TRIGGER u36_groupcheck_late BEFORE INSERT ON radgroupcheck "
                    "FOR EACH ROW BEGIN IF NEW.attribute='Trigger-Second' THEN SIGNAL SQLSTATE '45000' "
                    "SET MESSAGE_TEXT='fixture late attribute'; END IF; END//\nDELIMITER ;\n")
                try:
                    csrf = form(client, 'mng-rad-groupcheck-new.php')
                    fields = [('csrf_token', csrf), ('groupname', group)]
                    fields += attrs('Trigger-First', 'one', '=', 'check', 2)
                    fields += attrs('Trigger-Second', 'two', '=', 'check', 3)
                    page = post(client, 'mng-rad-groupcheck-new.php', fields)
                    assert 'Unable to add groupcheck' in page and state() == before
                    assert 'fixture late attribute' not in page
                finally:
                    sql('DROP TRIGGER u36_groupcheck_late')
                print('PASS: late child insert failure rolls back the whole create')

                for kind, row_id, attribute in (('check', check_id, 'Filter-Id'),
                                                ('reply', reply_id, 'Reply-Name')):
                    table = 'radgroup' + kind
                    item = 'group' + kind + '-' + row_id
                    page = edit(client, kind, item, percent_group, attribute, '0', '=')
                    assert 'Successfully updated group' + kind in page
                    assert sql('SELECT COUNT(*) FROM ' + table + ' WHERE id=' + row_id +
                               ' AND groupname=' + quote(percent_group) + " AND value='0'") == '1'
                    before = state()
                    trigger = 'u36_' + kind + '_update_error'
                    sql('CREATE TRIGGER ' + trigger + ' BEFORE UPDATE ON ' + table +
                        " FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture edit rejected'")
                    try:
                        page = edit(client, kind, item, second_group, attribute, 'must-not-persist', '=')
                        assert 'Unable to update group' + kind in page and state() == before
                        assert 'fixture edit rejected' not in page and 'SQLSTATE' not in page
                    finally:
                        sql('DROP TRIGGER ' + trigger)
                    token = form(client, 'mng-rad-group' + kind + '-edit.php?item=' + item)
                    wrong = 'group' + ('reply' if kind == 'check' else 'check') + '-' + row_id
                    page = post(client, 'mng-rad-group' + kind + '-edit.php', [
                        ('csrf_token', token), ('item', wrong), ('groupname', second_group),
                        ('attribute', attribute), ('op', '='), ('value', 'wrong-family')])
                    assert state() == before and 'Successfully updated' not in page
                print('PASS: zero/percent edits, UPDATE failures and wrong-family IDs in both families')

                # The candidate refuses a nontransactional participating table.
                sql('ALTER TABLE radgroupreply ENGINE=MyISAM')
                try:
                    before = state()
                    page = create(client, 'reply', group, 'Reply-Name', 'nontrans', '=', 'reply')
                    assert 'Unable to add groupreply' in page and state() == before
                finally:
                    sql('ALTER TABLE radgroupreply ENGINE=InnoDB')
                print('PASS: non-InnoDB group attribute creates fail closed')

                for kind, row_id, attribute in (('check', check_id, 'Filter-Id'),
                                                ('reply', reply_id, 'Reply-Name')):
                    table = 'radgroup' + kind
                    sql('ALTER TABLE ' + table + ' ENGINE=MyISAM')
                    try:
                        before = state()
                        page = edit(client, kind, 'group' + kind + '-' + row_id,
                                    second_group, attribute, 'myisam-edit-rejected', '=')
                        assert 'Unable to update group' + kind in page and state() == before, \
                            'Nontransactional edit was not rejected: ' + kind
                    finally:
                        sql('ALTER TABLE ' + table + ' ENGINE=InnoDB')
                print('PASS: non-InnoDB edits fail closed for both attribute families')

            logs = subprocess.run(['docker', 'logs', WEB], capture_output=True, text=True, timeout=30, check=True)
            combined = logs.stdout + logs.stderr
            assert 'PHP Fatal error' not in combined and 'PHP Warning' not in combined
            print('PASS: isolated PHP logs contain no fatal errors or warnings')
        finally:
            logs = subprocess.run(['docker', 'logs', WEB], capture_output=True, text=True, check=False)
            for line in (logs.stdout + logs.stderr).splitlines():
                if 'PHP Fatal error:' in line:
                    print('FIXTURE_PHP_FATAL', line)
            run('docker', 'exec', '-u', 'root', WEB, 'chown', '-R',
                f'{os.getuid()}:{os.getgid()}', '/fixtures', check=False)
            for container in (WEB, DB):
                run('docker', 'rm', '-f', '-v', container, check=False)
            run('docker', 'network', 'rm', NETWORK, check=False)
            print('CLEANUP: disposable HTTP/PHP/MariaDB fixture removed')


if __name__ == '__main__':
    main()
