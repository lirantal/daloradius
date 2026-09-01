<?php
include('../checklogin.php');
include('../../../common/includes/chart.php');

$username = !empty($_SESSION['login_user']) ? $_SESSION['login_user'] : '';
$category = (isset($_GET['category']) && in_array(strtolower(trim($_GET['category'])), array('upload', 'download', 'login')))
    ? strtolower(trim($_GET['category']))
    : 'download';
$type = (isset($_GET['type']) && in_array(strtolower($_GET['type']), array('daily', 'monthly', 'yearly')))
    ? strtolower($_GET['type'])
    : 'daily';
$size = (isset($_GET['size']) && in_array(strtolower($_GET['size']), array('gigabytes', 'megabytes')))
    ? strtolower($_GET['size'])
    : 'megabytes';

include('../../../common/includes/db_open.php');
$statistics = dalo_chart_overall_user_statistics(
    $dbSocket,
    $configValues['CONFIG_DB_TBL_RADACCT'],
    $username,
    $category,
    $type,
    $size,
    'traffic in %s by user %s'
);
include('../../../common/includes/db_close.php');

dalo_chart_response(
    'bar',
    $statistics['labels'],
    array(dalo_chart_bar_dataset($statistics['ytitle'], $statistics['values'])),
    $statistics['title'],
    ucfirst($type) . ' distribution',
    $statistics['ytitle']
);
