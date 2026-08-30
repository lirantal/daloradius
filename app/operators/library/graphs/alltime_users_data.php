<?php
include('../checklogin.php');
include('../../../common/includes/chart.php');
$category = (isset($_GET['category']) && in_array(strtolower(trim($_GET['category'])), array('upload', 'download', 'login'))) ? strtolower(trim($_GET['category'])) : 'download';
$dbfield = $category === 'login' ? 'COUNT(AcctStartTime)' : ($category === 'upload' ? 'SUM(AcctInputOctets)' : 'SUM(AcctOutputOctets)');
$type = (isset($_GET['type']) && in_array(strtolower($_GET['type']), array('daily', 'monthly', 'yearly'))) ? strtolower($_GET['type']) : 'daily';
$size = (isset($_GET['size']) && in_array(strtolower($_GET['size']), array('gigabytes', 'megabytes'))) ? strtolower($_GET['size']) : 'megabytes';
if ($type === 'yearly') $sql = "SELECT YEAR(AcctStartTime), %s FROM %s GROUP BY YEAR(AcctStartTime) ORDER BY YEAR(AcctStartTime) DESC LIMIT 36";
elseif ($type === 'monthly') $sql = "SELECT CONCAT(LEFT(MONTHNAME(AcctStartTime), 3), ' (', YEAR(AcctStartTime), ')'), %s FROM %s GROUP BY YEAR(AcctStartTime), MONTH(AcctStartTime) ORDER BY YEAR(AcctStartTime) DESC, MONTH(AcctStartTime) DESC LIMIT 36";
else $sql = "SELECT DATE(AcctStartTime), %s FROM %s GROUP BY DATE(AcctStartTime) ORDER BY DATE(AcctStartTime) DESC LIMIT 36";
include('../../../common/includes/db_open.php');
$res = $dbSocket->query(sprintf($sql, $dbfield, $configValues['CONFIG_DB_TBL_RADACCT']));
$labels = array(); $values = array(); $division = $size === 'gigabytes' ? 1073741824 : 1048576;
while ($row = $res->fetchRow()) { $labels[] = strval($row[0]); $values[] = $category === 'login' ? intval($row[1]) : round(floatval($row[1]) / $division, 1); }
include('../../../common/includes/db_close.php');
$ytitle = $category === 'login' ? 'Login count' : ucfirst($size) . ' ' . $category . 'ed';
dalo_chart_response('bar', $labels, array(array_merge(array('label' => $ytitle, 'data' => $values), array('backgroundColor' => 'rgba(54, 162, 235, 0.55)', 'borderColor' => 'rgb(54, 162, 235)', 'borderWidth' => 1))), sprintf('all-time %s statistics', $category), ucfirst($type) . ' distribution', $ytitle);
