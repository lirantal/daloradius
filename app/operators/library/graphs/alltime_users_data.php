<?php
include_once __DIR__.'/../../../common/includes/config_read.php';
include __DIR__.'/../checklogin.php';
require_once __DIR__.'/../../../common/includes/chart.php';
require_once __DIR__.'/../widget_reads_pdo.php';
dalo_widget_inputs(true);


$category = (isset($_GET['category']) && in_array(strtolower(trim($_GET['category'])), array('upload', 'download', 'login')))
    ? strtolower(trim($_GET['category']))
    : 'download';
$dbfield = $category === 'login'
    ? 'COUNT(AcctStartTime)'
    : ($category === 'upload' ? 'SUM(AcctInputOctets)' : 'SUM(AcctOutputOctets)');
$type = (isset($_GET['type']) && in_array(strtolower($_GET['type']), array('daily', 'monthly', 'yearly')))
    ? strtolower($_GET['type'])
    : 'daily';
$size = (isset($_GET['size']) && in_array(strtolower($_GET['size']), array('gigabytes', 'megabytes')))
    ? strtolower($_GET['size'])
    : 'megabytes';

if ($type === 'yearly') {
    $sql = 'SELECT YEAR(AcctStartTime), %s FROM %s GROUP BY YEAR(AcctStartTime) ORDER BY YEAR(AcctStartTime) DESC LIMIT 36';
} elseif ($type === 'monthly') {
    $sql = "SELECT CONCAT(LEFT(MONTHNAME(AcctStartTime), 3), ' (', YEAR(AcctStartTime), ')'), %s FROM %s GROUP BY YEAR(AcctStartTime), MONTH(AcctStartTime) ORDER BY YEAR(AcctStartTime) DESC, MONTH(AcctStartTime) DESC LIMIT 36";
} else {
    $sql = 'SELECT DATE(AcctStartTime), %s FROM %s GROUP BY DATE(AcctStartTime) ORDER BY DATE(AcctStartTime) DESC LIMIT 36';
}

try {
$widgetPDO=dalo_widget_open();
dalo_widget_authorize($widgetPDO,array($category==='login'?'graphs-alltime_logins':'graphs-alltime_traffic_compare'));
$res = dalo_widget_rows($widgetPDO,sprintf($sql, $dbfield, dalo_widget_table($configValues,'CONFIG_DB_TBL_RADACCT')));
$labels = array();
$values = array();
$division = $size === 'gigabytes' ? 1073741824 : 1048576;
foreach ($res as $row) {
    $labels[] = strval($row[0]);
    $values[] = $category === 'login' ? intval($row[1]) : round(floatval($row[1]) / $division, 1);
}
unset($widgetPDO);
} catch (Throwable $exception) {dalo_widget_failure($exception,true);}

$ytitle = $category === 'login' ? 'Login count' : ucfirst($size) . ' ' . $category . 'ed';
$dataset = array(
    'label' => $ytitle,
    'data' => $values,
    'backgroundColor' => 'rgba(54, 162, 235, 0.55)',
    'borderColor' => 'rgb(54, 162, 235)',
    'borderWidth' => 1,
);
dalo_chart_response('bar', $labels, array($dataset), sprintf('all-time %s statistics', $category), ucfirst($type) . ' distribution', $ytitle);
