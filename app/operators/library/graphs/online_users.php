<?php
include_once __DIR__.'/../../../common/includes/config_read.php';
include __DIR__.'/../checklogin.php';
require_once __DIR__.'/../../../common/includes/chart.php';
require_once __DIR__.'/../widget_reads_pdo.php';
dalo_widget_inputs(true);

try {
$widgetPDO=dalo_widget_open();
dalo_widget_authorize($widgetPDO,array('rep-online'));

$res = dalo_widget_rows($widgetPDO,sprintf('SELECT COUNT(DISTINCT(username)) FROM %s', dalo_widget_table($configValues,'CONFIG_DB_TBL_RADCHECK')));
$total = intval($res[0][0]);
$res = dalo_widget_rows($widgetPDO,sprintf("SELECT COUNT(DISTINCT(username)) FROM %s WHERE AcctStopTime IS NULL OR AcctStopTime = '0000-00-00 00:00:00'", dalo_widget_table($configValues,'CONFIG_DB_TBL_RADACCT')));
$online = intval($res[0][0]);
unset($widgetPDO);
} catch (Throwable $exception) {dalo_widget_failure($exception,true);}

$labels = array();
$values = array();
if ($total > 0) {
    $offline = max(0, $total - $online);
    $labels = array(sprintf('%d user(s) offline', $offline), sprintf('%d user(s) online', $online));
    $values = array($offline, $online);
}
$dataset = array(
    'data' => $values,
    'backgroundColor' => array('rgba(54, 162, 235, 0.65)', 'rgba(255, 99, 132, 0.65)'),
);
dalo_chart_response('pie', $labels, array($dataset), 'online/offline users');
