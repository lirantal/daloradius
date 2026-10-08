<?php
require_once __DIR__ . '/../app/users/library/user_report_export.php';

function check($condition, $label) {
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $label);
    }
    echo "PASS: $label\n";
}
function rejects($callback, $label) {
    try {
        $callback();
    } catch (InvalidArgumentException $exception) {
        echo "PASS: $label\n";
        return;
    }
    throw new RuntimeException('FAIL: ' . $label);
}

$config = array(
    'CONFIG_DB_TBL_RADACCT' => 'radacct',
    'CONFIG_DB_TBL_DALOHOTSPOTS' => 'hotspots',
    'CONFIG_DB_TBL_DALOBILLINGINVOICE' => 'invoice',
    'CONFIG_DB_TBL_DALOUSERBILLINFO' => 'userbillinfo',
    'CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS' => 'invoice_status',
    'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS' => 'invoice_items',
    'CONFIG_DB_TBL_DALOPAYMENTS' => 'payment',
);
$descriptor = array('source' => 'acct-date', 'filters' => array(
    'startdate' => '2020-01-01', 'enddate' => '2020-01-03'));
list($sql, $bindings, $header) = dalo_user_export_query($descriptor, "a' OR 1=1 --", $config);
check(strpos($sql, '1=1') === false && $bindings[':username'] === "a' OR 1=1 --",
      'username is bound data, not session SQL');
check(strpos($sql, 'AcctStartTime > :startdate') !== false &&
      strpos($sql, 'AcctStartTime < :enddate') !== false, 'accounting date bounds are exclusive');
check(count($header) === 10, 'accounting CSV columns stay ordered');
rejects(function() use ($descriptor, $config) {
    $descriptor['source'] = 'anything';
    dalo_user_export_query($descriptor, 'alice', $config);
}, 'unknown source fails closed');
rejects(function() use ($descriptor, $config) {
    $descriptor['filters']['username'] = 'bob';
    dalo_user_export_query($descriptor, 'alice', $config);
}, 'descriptor cannot select another user');
rejects(function() use ($descriptor, $config) {
    $descriptor['filters']['startdate'] = '2020-02-31';
    dalo_user_export_query($descriptor, 'alice', $config);
}, 'invalid date rejected');
rejects(function() use ($descriptor, $config) {
    $config['CONFIG_DB_TBL_RADACCT'] = 'radacct; SELECT 1';
    dalo_user_export_query($descriptor, 'alice', $config);
}, 'table identifier cannot inject SQL');
$invoice = array('source' => 'bill-invoice-report', 'filters' => array('invoice_status' => '2'));
list($invoiceSql, $invoiceParams, $invoiceHeader) = dalo_user_export_query($invoice, 'alice', $config);
check(strpos($invoiceSql, 'b.username=:username') !== false &&
      strpos($invoiceSql, 'a.status_id = :status') !== false &&
      $invoiceParams[':status'] === '2' && count($invoiceHeader) === 5,
      'invoice query is user-scoped with bound status');
check(dalo_user_export_csv_row(array('Lab, East', '=SUM(A1:A2)', '-12.50'), array(2)) ===
      array('Lab, East', "'=SUM(A1:A2)", '-12.50'),
      'text formulas neutralized without changing numeric values');
echo "ALL PASSED\n";
