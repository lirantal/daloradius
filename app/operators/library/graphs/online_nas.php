<?php
include_once __DIR__.'/../../../common/includes/config_read.php';
include __DIR__.'/../checklogin.php';
require_once __DIR__.'/../../../common/includes/chart.php';
require_once __DIR__.'/../widget_reads_pdo.php';
dalo_widget_inputs(true);

try {
$widgetPDO=dalo_widget_open();
dalo_widget_authorize($widgetPDO,array('rep-online'));

$sql = sprintf("SELECT n.shortname, COUNT(DISTINCT(ra.username)) FROM %s AS ra, %s AS n WHERE n.nasname = ra.nasipaddress AND (ra.acctstoptime IS NULL OR ra.acctstoptime = '0000-00-00 00:00:00') GROUP BY ra.nasipaddress", dalo_widget_table($configValues,'CONFIG_DB_TBL_RADACCT'), dalo_widget_table($configValues,'CONFIG_DB_TBL_RADNAS'));
$res = dalo_widget_rows($widgetPDO,$sql);
$labels = array();
$values = array();
foreach ($res as $row) {
    $labels[] = strval($row[0]);
    $values[] = intval($row[1]);
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
dalo_chart_response('bar', $labels, array($dataset), 'per-NAS online users', 'NAS', 'users');
