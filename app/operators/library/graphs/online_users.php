<?php
include('../checklogin.php'); include('../../../common/includes/chart.php'); include('../../../common/includes/db_open.php');
$res = $dbSocket->query(sprintf("SELECT COUNT(DISTINCT(username)) FROM %s", $configValues['CONFIG_DB_TBL_RADCHECK'])); $total = intval($res->fetchRow()[0]);
$res = $dbSocket->query(sprintf("SELECT COUNT(DISTINCT(username)) FROM %s WHERE AcctStopTime IS NULL OR AcctStopTime = '0000-00-00 00:00:00'", $configValues['CONFIG_DB_TBL_RADACCT'])); $online = intval($res->fetchRow()[0]);
include('../../../common/includes/db_close.php');
$labels = $total > 0 ? array(sprintf('%d user(s) offline', max(0, $total - $online)), sprintf('%d user(s) online', $online)) : array();
$values = $total > 0 ? array(max(0, $total - $online), $online) : array();
dalo_chart_response('pie', $labels, array(array('data' => $values, 'backgroundColor' => array('rgba(54, 162, 235, 0.65)', 'rgba(255, 99, 132, 0.65)', 'rgba(255, 206, 86, 0.65)', 'rgba(75, 192, 192, 0.65)', 'rgba(153, 102, 255, 0.65)', 'rgba(255, 159, 64, 0.65)'))), 'online/offline users');
