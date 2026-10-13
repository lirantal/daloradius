<?php
include_once __DIR__.'/../../../common/includes/config_read.php';
include __DIR__.'/../checklogin.php';
include_once $configValues['OPERATORS_LANG'].'/main.php';
include_once implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', 'common', 'includes', 'validation.php'));
require_once __DIR__.'/../../../common/includes/chart.php';
require_once __DIR__.'/../widget_reads_pdo.php';
dalo_widget_inputs(true);


$startdate = isset($_GET['startdate']) && preg_match(DATE_REGEX, trim($_GET['startdate']), $m) && checkdate($m[2], $m[3], $m[1])
    ? trim($_GET['startdate'])
    : '';
$enddate = isset($_GET['enddate']) && preg_match(DATE_REGEX, trim($_GET['enddate']), $m) && checkdate($m[2], $m[3], $m[1])
    ? trim($_GET['enddate'])
    : '';

try {
$widgetPDO=dalo_widget_open();
dalo_widget_authorize($widgetPDO,array('rep-newusers'));
$where = array();$bindings=array();
if ($startdate !== '') {
    $where[] = "CreationDate >= :startdate"; $bindings[':startdate']=$startdate;
}
if ($enddate !== '') {
    // inclusive end date: match the whole $enddate day
    $where[] = "CreationDate < (:enddate + INTERVAL 1 DAY)"; $bindings[':enddate']=$enddate;
}
$sql = sprintf("SELECT COUNT(*), CONCAT(YEAR(CreationDate), ' ', LEFT(MONTHNAME(CreationDate), 3)) FROM %s", dalo_widget_table($configValues,'CONFIG_DB_TBL_DALOUSERINFO'))
    . (count($where) ? ' WHERE ' . implode(' AND ', $where) : '')
    . ' GROUP BY YEAR(CreationDate), MONTH(CreationDate) ORDER BY YEAR(CreationDate), MONTH(CreationDate)';
$res = dalo_widget_rows($widgetPDO,$sql,$bindings);
$labels = array();
$values = array();
foreach ($res as $row) {
    $values[] = intval($row[0]);
    $labels[] = strval($row[1]);
}
unset($widgetPDO);
} catch (Throwable $exception) {dalo_widget_failure($exception,true);}

$dataset = array(
    'label' => 'users',
    'data' => $values,
    'backgroundColor' => 'rgba(54, 162, 235, 0.55)',
    'borderColor' => 'rgb(54, 162, 235)',
    'borderWidth' => 1,
);
dalo_chart_response('bar', $labels, array($dataset), 'new users amount', 'per-month distribution', 'users');
