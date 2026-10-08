#!/usr/bin/env python3
"""Compare operator CSV exports against an isolated PEAR baseline.

Run this script separately with DALO_SOURCE_ROOT pointing at the baseline and
candidate worktrees; each run creates disposable MariaDB/PHP containers.
"""
import json
import os
from pathlib import Path
import secrets
import shutil
import tempfile
import urllib.error
import urllib.parse
import urllib.request
import user_actions_http as harness

ROOT = Path(os.environ['DALO_SOURCE_ROOT'])
run, sql, wait_for = harness.run, harness.sql, harness.wait_for
DB, WEB, NETWORK = harness.DB, harness.WEB, harness.NETWORK


def main():
    results = {}
    with tempfile.TemporaryDirectory(prefix='dalo-export-diff-') as directory:
        fixture = Path(directory)
        shutil.copytree(ROOT / 'app', fixture / 'app', symlinks=True)
        try:
            run('docker', 'network', 'create', '--internal', NETWORK)
            run('docker', 'run', '-d', '--name', DB, '--network', NETWORK,
                '--tmpfs', '/var/lib/mysql', '-e', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
                '-e', 'MARIADB_DATABASE=radius', 'mariadb:11.8')
            wait_for(lambda: sql('SELECT 1'), 'MariaDB')
            for name in ['fr3-mariadb-freeradius.sql', 'mariadb-daloradius.sql']:
                sql((ROOT / 'contrib/db' / name).read_text())
            config = (ROOT / 'app/common/includes/daloradius.conf.php.sample').read_text().replace('?>', '')
            for key, value in {'CONFIG_DB_HOST': DB, 'CONFIG_DB_USER': 'root',
                               'CONFIG_DB_PASS': '', 'CONFIG_DB_NAME': 'radius'}.items():
                config += '\n$configValues[' + repr(key) + '] = ' + repr(value) + ';\n'
            (fixture / 'app/common/includes/daloradius.conf.php').write_text(config)
            (fixture / 'session.php').write_text('''<?php
session_name('daloradius_operator_sid'); session_id($argv[1]); session_start();
$_SESSION = ['daloradius_logged_in'=>true, 'operator_id'=>9001,
'operator_user'=>'export-fixture', 'location_name'=>'default', 'time'=>time()];
session_write_close();
''')
            acl = ['acct_all','acct_username','acct_date','acct_ipaddress','acct_nasipaddress',
                   'acct_hotspot_accounting','acct_plans_usage','mng_list_all','mng_search',
                   'mng_rad_profiles_list','rep_online','rep_lastconnect','rep_topusers',
                   'rep_batch_list','mng_batch_list','rep_batch_details','bill_invoice_report']
            sql('INSERT INTO operators_acl (operator_id,file,access) VALUES ' +
                ','.join("(9001,'" + key + "',1)" for key in acl))
            sql("""SET sql_mode='';
INSERT INTO radcheck (username,attribute,op,value) VALUES
 ('alice','Cleartext-Password',':=','alice-secret'),
 ('bob','Cleartext-Password',':=','bob-secret'),
 ('outside','Cleartext-Password',':=','outside-secret');
INSERT INTO userinfo (username,email,firstname,lastname) VALUES
 ('alice','alice@example.invalid','Alice','Smith'),
 ('bob','bob@example.invalid','Bob','Jones'),
 ('outside','outside@example.invalid','Outside','User');
INSERT INTO radusergroup (username,groupname,priority) VALUES
 ('alice','fixture-group',1),('bob','fixture-group',1);
INSERT INTO radacct (username,acctsessionid,acctuniqueid,nasipaddress,framedipaddress,
 acctstarttime,acctstoptime,acctsessiontime,acctinputoctets,acctoutputoctets) VALUES
 ('alice','a1','fixture-a1','192.0.2.1','198.51.100.1','2020-01-01 12:00:00',NULL,11,12,13),
 ('bob','b1','fixture-b1','192.0.2.2','198.51.100.2','2020-01-02 12:00:00',NULL,21,22,23),
 ('outside','o1','fixture-o1','192.0.2.3','198.51.100.3','2020-01-03 12:00:00',NULL,31,32,33);
INSERT INTO radpostauth (username,pass,reply,authdate) VALUES
 ('alice','x','Access-Accept','2020-01-01 12:00:00'),
 ('bob','x','Access-Reject','2020-01-02 12:00:00');
INSERT INTO hotspots (id,name,mac) VALUES (21,'Lab hotspot','aa:bb:cc:dd:ee:ff');
UPDATE radacct SET calledstationid='aa:bb:cc:dd:ee:ff' WHERE username='alice';
UPDATE radacct SET acctstoptime='2020-01-02 13:00:00' WHERE username='bob';
INSERT INTO billing_plans (id,planName,planCost,planTimeBank,planTimeType) VALUES (11,'Basic','2','100','Seconds');
INSERT INTO batch_history (id,batch_name,hotspot_id,batch_status,creationdate,creationby)
 VALUES (42,'fixture-batch',21,'Active','2020-01-01 09:00:00','fixture');
INSERT INTO userbillinfo (id,username,planName,batch_id,contactperson)
 VALUES (10,'alice','Basic',42,'Alice Smith'),(11,'bob','Basic',42,'Bob Jones');
INSERT INTO invoice (id,user_id,date,status_id,type_id,notes)
 VALUES (30,10,'2020-01-02 00:00:00',1,1,'fixture');
INSERT INTO invoice_items (id,invoice_id,amount,tax_amount,notes)
 VALUES (31,30,2.00,0.20,'fixture');
INSERT INTO payment (id,invoice_id,amount,date,notes)
 VALUES (32,30,1.00,'2020-01-02 00:00:00','fixture');
""")
            run('docker', 'run', '-d', '--name', WEB, '--network', NETWORK,
                '-v', f'{fixture}:/fixtures', '-w', '/fixtures/app/operators',
                '--entrypoint', 'php', 'lirantal/daloradius', '-d', 'display_errors=0',
                '-S', '0.0.0.0:8080', '-t', '.')
            address = run('docker', 'inspect', '-f',
                          '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}', WEB)
            base = 'http://' + address + ':8080/'
            wait_for(lambda: urllib.request.urlopen(base + 'login.php', timeout=10), 'PHP HTTP server')
            session = secrets.token_hex(16)
            run('docker', 'exec', WEB, 'php', '/fixtures/session.php', session)
            def request(path):
                req = urllib.request.Request(base + path, headers={'Cookie': 'daloradius_operator_sid=' + session})
                try:
                    with urllib.request.urlopen(req, timeout=15) as response:
                        return response.status, response.read().decode('utf-8')
                except urllib.error.HTTPError as error:
                    return error.code, error.read().decode('utf-8')
            cases = [
                ('acct-all', 'accountingGeneric', {}),
                ('acct-username', 'accountingGeneric', {'username':'alice'}),
                ('acct-date', 'accountingGeneric', {'username':'alice','startdate':'2020-01-01','enddate':'2020-01-02'}),
                ('acct-ipaddress', 'accountingGeneric', {'ipaddress':'198.51.100.1'}),
                ('acct-nasipaddress', 'accountingGeneric', {'nasipaddress':'192.0.2.1'}),
                ('acct-hotspot-accounting', 'accountingGeneric', {'hotspot[]':'Lab hotspot'}),
                ('acct-plans-usage', 'reportsPlansUsage', {'username':'alice','startdate':'2020-01-01','enddate':'2020-01-03'}),
                ('mng-list-all', 'usernameListGeneric', {}),
                ('mng-search', 'usernameListGeneric', {'username':'alice'}),
                ('rep-online', 'reportsOnlineUsers', {'username':'alice'}),
                ('rep-lastconnect', 'reportsLastConnectionAttempts', {'username':'alice','startdate':'2020-01-01','enddate':'2020-01-03'}),
                ('rep-topusers', 'TopUsers', {'username':'bob','startdate':'2020-01-01','enddate':'2020-01-03'}),
                ('rep-batch-list', 'reportsBatchList', {}),
                ('mng-batch-list', 'reportsBatchList', {}),
                ('rep-batch-details', 'reportsBatchActiveUsers', {'batch_name':'fixture-batch','username':'alice'}),
                ('bill-invoice-report', 'reportsInvoiceList', {'startdate':'2020-01-01','enddate':'2020-01-03'}),
            ]
            export = 'include/management/fileExport.php?reportFormat=csv&reportType='
            for page, typ, params in cases:
                path = page + '.php' + ('?' + urllib.parse.urlencode(params) if params else '')
                page_status, page_body = request(path)
                status, csv = request(export + typ)
                results[page] = {'page_status':page_status, 'page_length':len(page_body),
                                 'export_status':status, 'csv':csv}
                if page == 'rep-batch-details':
                    results[page]['page_has_alice_row'] = 'value="alice" name="username[]"' in page_body
                    total_status, total_csv = request(export + 'reportsBatchTotalUsers')
                    results['rep-batch-total-users'] = {'export_status':total_status, 'csv':total_csv}
            status, csv = request(export + 'usernameListByGroup&groupname=fixture-group')
            results['usernameListByGroup'] = {'export_status':status, 'csv':csv}
            baseline_file = os.environ.get('DALO_BASELINE_JSON')
            if baseline_file:
                baseline = json.loads(Path(baseline_file).read_text())
                assert baseline.keys() == results.keys(), 'Differential case sets differ'
                for name, actual in results.items():
                    expected = baseline[name]
                    if name == 'acct-plans-usage':
                        # Legacy SELECT has an ambiguous username column.
                        assert expected['export_status'] == 500 and actual['export_status'] == 200
                        assert 'alice,Basic,' in actual['csv']
                    elif name == 'rep-batch-details':
                        # Legacy producer did not provide the formatter's
                        # batch_name/acctstarttime fields (or matching rows
                        # with this search filter); now they are populated.
                        assert actual['export_status'] == expected['export_status'] == 200
                        assert expected['page_has_alice_row'] is False
                        assert actual['page_has_alice_row'] is True
                        assert 'fixture-batch,alice,' in actual['csv']
                    else:
                        assert actual['export_status'] == expected['export_status'], name
                        assert actual['csv'] == expected['csv'], name
                    if 'page_status' in actual:
                        assert actual['page_status'] == expected['page_status'] == 200, name
                print('DIFFERENTIAL PASS: 16 exact CSVs; 2 documented legacy defects repaired', file=__import__('sys').stderr)
            print(json.dumps(results, indent=2))
        finally:
            for container in (WEB, DB):
                run('docker', 'rm', '-f', '-v', container, check=False)
            run('docker', 'network', 'rm', NETWORK, check=False)

if __name__ == '__main__':
    main()
