<?php
include('../checklogin.php'); include('../../../common/includes/chart.php'); include('../../../common/includes/db_open.php');
$sql = sprintf("SELECT n.shortname, COUNT(DISTINCT(ra.username)) FROM %s AS ra, %s AS n WHERE n.nasname = ra.nasipaddress AND (ra.acctstoptime IS NULL OR ra.acctstoptime = '0000-00-00 00:00:00') GROUP BY ra.nasipaddress", $configValues['CONFIG_DB_TBL_RADACCT'], $configValues['CONFIG_DB_TBL_RADNAS']);
$res = $dbSocket->query($sql); $labels = array(); $values = array(); while ($row = $res->fetchRow()) { $labels[] = strval($row[0]); $values[] = intval($row[1]); } include('../../../common/includes/db_close.php');
dalo_chart_response('bar', $labels, array(array_merge(array('label' => 'users', 'data' => $values), array('backgroundColor' => 'rgba(54, 162, 235, 0.55)', 'borderColor' => 'rgb(54, 162, 235)', 'borderWidth' => 1))), 'per-NAS online users', 'NAS', 'users');
