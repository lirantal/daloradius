<?php
include('../checklogin.php');
require_once __DIR__ . '/../ajax/json_info.php';
$dalo_info_database_error_message='Unable to load hotspot chart.';
$db_error_handler='dalo_info_database_error';
$operator_perm_file='acct_hotspot_compare';$operator_perm_deny_http_status=403;
include('../check_operator_perm.php');
include('../../../common/includes/chart.php');
require_once dirname(__DIR__) . '/hotspot_pages_pdo.php';
if ($_SERVER['REQUEST_METHOD']!=='GET') { header('Allow: GET');dalo_info_response(['error'=>'Method not allowed.'],405); }
$category=isset($_GET['category']) && is_string($_GET['category']) && in_array(strtolower(trim($_GET['category'])),array('avg_session_time','total_session_time','login_hits','unique_users'),true) ? strtolower(trim($_GET['category'])) : 'unique_users';
$definitions = array(
    'total_session_time' => array('per-hotspot total session time', 'SUM(ra.acctsessiontime)', '%s (%s seconds)'),
    'avg_session_time' => array('per-hotspot average session time', 'AVG(ra.acctsessiontime)', '%s (%s seconds)'),
    'login_hits' => array('per-hotspot login hits', 'COUNT(ra.radacctid)', '%s (%s login hits)'),
    'unique_users' => array('per-hotspot unique users', 'COUNT(DISTINCT(ra.username))', '%s (%s unique users)'),
);
list($title, $dbfield, $format) = $definitions[$category];

try {
    $pdo=dalo_pdo_connect($configValues,$_SESSION['location_name'] ?? 'default');
    $acct=dalo_hotspot_table($configValues,'CONFIG_DB_TBL_RADACCT');$hs=dalo_hotspot_table($configValues);
    $rows=dalo_hotspot_query($pdo,"SELECT hs.name,$dbfield FROM $acct AS ra JOIN $hs AS hs ON ra.calledstationid=hs.mac GROUP BY hs.name ORDER BY 2 DESC")->fetchAll(PDO::FETCH_NUM);
    $labels=$values=array();
    foreach ($rows as $row) { $value=intval($row[1]);$labels[]=sprintf($format,$row[0],$value);$values[]=$value; }
} catch (Throwable $e) { dalo_info_response(['error'=>'Unable to load hotspot chart.'],500); }
$dataset=array('data'=>$values,'backgroundColor'=>array('rgba(54, 162, 235, 0.65)','rgba(255, 99, 132, 0.65)','rgba(255, 206, 86, 0.65)','rgba(75, 192, 192, 0.65)','rgba(153, 102, 255, 0.65)','rgba(255, 159, 64, 0.65)'));
dalo_chart_response('pie',$labels,array($dataset),$title);
