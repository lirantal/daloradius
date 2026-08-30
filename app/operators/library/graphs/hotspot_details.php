<?php
include('../checklogin.php');
include('../../../common/includes/chart.php');

$category = isset($_GET['category']) && in_array(strtolower(trim($_GET['category'])), array('avg_session_time', 'total_session_time', 'login_hits', 'unique_users'))
    ? strtolower(trim($_GET['category']))
    : 'unique_users';
$definitions = array(
    'total_session_time' => array('per-hotspot total session time', 'SUM(ra.acctsessiontime)', '%s (%s seconds)'),
    'avg_session_time' => array('per-hotspot average session time', 'AVG(ra.acctsessiontime)', '%s (%s seconds)'),
    'login_hits' => array('per-hotspot login hits', 'COUNT(ra.radacctid)', '%s (%s login hits)'),
    'unique_users' => array('per-hotspot unique users', 'COUNT(DISTINCT(ra.username))', '%s (%s unique users)'),
);
list($title, $dbfield, $format) = $definitions[$category];

include('../../../common/includes/db_open.php');
$sql = sprintf('SELECT hs.name, %s FROM %s AS ra, %s AS hs WHERE ra.calledstationid = hs.mac GROUP BY hs.name ORDER BY 2 DESC', $dbfield, $configValues['CONFIG_DB_TBL_RADACCT'], $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
$res = $dbSocket->query($sql);
$labels = array();
$values = array();
while ($row = $res->fetchRow()) {
    $value = intval($row[1]);
    $labels[] = sprintf($format, $row[0], $value);
    $values[] = $value;
}
include('../../../common/includes/db_close.php');

$dataset = array(
    'data' => $values,
    'backgroundColor' => array('rgba(54, 162, 235, 0.65)', 'rgba(255, 99, 132, 0.65)', 'rgba(255, 206, 86, 0.65)', 'rgba(75, 192, 192, 0.65)', 'rgba(153, 102, 255, 0.65)', 'rgba(255, 159, 64, 0.65)'),
);
dalo_chart_response('pie', $labels, array($dataset), $title);

