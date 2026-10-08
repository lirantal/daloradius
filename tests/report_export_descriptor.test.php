<?php
/* UNIT-003 descriptor and identifier guards; no database required. */
require dirname(__DIR__) . '/app/operators/library/report_export.php';

function dalo_export_check($label, $value) {
    if (!$value) {
        fwrite(STDERR, "FAIL: $label\n");
        exit(1);
    }
    echo "PASS: $label\n";
}
function dalo_export_rejects($callback) {
    try {
        $callback();
    } catch (InvalidArgumentException $exception) {
        return true;
    }
    return false;
}
$session = ['reportType' => 'accountingGeneric', 'reportExport' => [
    'source' => 'acct-username', 'type' => 'accountingGeneric',
    'filters' => ['username' => "alice' OR 1=1 --"],
]];
$descriptor = dalo_export_descriptor($session, []);
dalo_export_check('descriptor preserves the filter as data',
    $descriptor['filters']['username'] === "alice' OR 1=1 --");
dalo_export_check('GET cannot change the report type for a prior source',
    dalo_export_rejects(function() use ($session) {
        dalo_export_descriptor($session, ['reportType' => 'TopUsers']);
    }));
dalo_export_check('raw session SQL is not a descriptor',
    dalo_export_rejects(function() {
        dalo_export_descriptor(['reportType'=>'accountingGeneric',
            'reportQuery'=>'SELECT * FROM radacct'], []);
    }));
dalo_export_check('group export takes a group value, not raw SQL',
    dalo_export_descriptor([], ['reportType'=>'usernameListByGroup',
        'groupname'=>"group'; DROP TABLE radacct; --"])['filters']['groupname']
        === "group'; DROP TABLE radacct; --");
dalo_export_check('group name array fails closed', dalo_export_rejects(function() {
    dalo_export_descriptor([], ['reportType'=>'usernameListByGroup', 'groupname'=>['x']]);
}));
$config = ['CONFIG_DB_TBL_RADACCT' => 'radacct'];
dalo_export_check('configuration table is quoted',
    dalo_export_table($config, 'CONFIG_DB_TBL_RADACCT') === '`radacct`');
$config['CONFIG_DB_TBL_RADACCT'] = 'radacct; DROP TABLE radacct';
dalo_export_check('table configuration cannot inject SQL', dalo_export_rejects(function() use ($config) {
    dalo_export_table($config, 'CONFIG_DB_TBL_RADACCT');
}));
dalo_export_check('unknown source cannot select an export builder',
    dalo_export_rejects(function() {
        dalo_export_query(['source'=>'unknown', 'type'=>'accountingGeneric',
            'filters'=>[]], []);
    }));
echo "ALL PASSED\n";
