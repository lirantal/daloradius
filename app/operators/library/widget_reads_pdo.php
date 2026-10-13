<?php
/* R14 read-only operator widgets. No PEAR opens or implicit provider switch. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/widget_reads_pdo.php')!==false) {
    http_response_code(404);exit;
}
require_once __DIR__.'/../../common/includes/pdo_connection.php';
require_once __DIR__.'/../../common/includes/chart_pdo.php';
function dalo_widget_table($config,$key) {return dalo_chart_table($config[$key] ?? null);}
function dalo_widget_open() {
    global $configValues;
    return dalo_pdo_connect($configValues,$_SESSION['location_name'] ?? 'default');
}
function dalo_widget_rows(PDO $pdo,$sql,$bindings=array()) {
    global $logDebugSQL;
    $logDebugSQL=($logDebugSQL ?? '').$sql.";\n";
    return dalo_chart_rows($pdo,$sql,$bindings);
}
function dalo_widget_inputs($json=false) {
    foreach(array('category','type','size','user','username','startdate','enddate','day','month','year','orderBy','orderType','page') as $key) {
        if (isset($_GET[$key]) && !is_string($_GET[$key])) {
            http_response_code(400);
            if ($json) {header('Content-Type: application/json; charset=utf-8');echo '{"error":"Invalid widget filters"}';}
            else {echo 'Invalid widget filters';}
            exit;
        }
    }
    if (isset($_REQUEST['page']) && !is_string($_REQUEST['page'])) {$_REQUEST['page']='1';}
}
function dalo_widget_failure(Throwable $exception,$json=false) {
    error_log('Widget read failed ('.get_class($exception).')');
    if ($json) {
        http_response_code(500);header('Content-Type: application/json; charset=utf-8');
        echo '{"error":"Unable to read widget data"}';exit;
    }
    global $failureMsg;
    $failureMsg='Unable to read widget data';
}

function dalo_widget_authorize(PDO $pdo,$sources) {
    global $configValues;
    require_once __DIR__.'/operator_acl_read.php';
    foreach($sources as $source) {
        if (dalo_operator_acl_allowed($pdo,$configValues,$_SESSION['operator_id'] ?? null,str_replace('-','_',$source))) {return;}
    }
    http_response_code(403);header('Content-Type: application/json; charset=utf-8');echo '{"error":"Widget access denied"}';exit;
}
