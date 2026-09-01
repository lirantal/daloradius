<?php
include('../checklogin.php');
include('../../../common/includes/chart.php');
include('../../../common/includes/db_open.php');

$res = $dbSocket->query(sprintf('SELECT COUNT(DISTINCT(username)) FROM %s', $configValues['CONFIG_DB_TBL_RADCHECK']));
$total = intval($res->fetchRow()[0]);
$res = $dbSocket->query(sprintf("SELECT COUNT(DISTINCT(username)) FROM %s WHERE AcctStopTime IS NULL OR AcctStopTime = '0000-00-00 00:00:00'", $configValues['CONFIG_DB_TBL_RADACCT']));
$online = intval($res->fetchRow()[0]);
include('../../../common/includes/db_close.php');

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
