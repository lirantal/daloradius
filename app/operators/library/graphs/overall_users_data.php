<?php
include('../checklogin.php');
include('../../../common/includes/chart.php');

$category = (isset($_GET['category']) && in_array(strtolower(trim($_GET['category'])), array('upload', 'download', 'login')))
    ? strtolower(trim($_GET['category']))
    : 'download';
$type = (isset($_GET['type']) && in_array(strtolower($_GET['type']), array('daily', 'monthly', 'yearly')))
    ? strtolower($_GET['type'])
    : 'daily';
$size = (isset($_GET['size']) && in_array(strtolower($_GET['size']), array('gigabytes', 'megabytes')))
    ? strtolower($_GET['size'])
    : 'megabytes';
$username = isset($_GET['user']) ? str_replace('%', '', $_GET['user']) : '';

include('../../../common/includes/db_open.php');
$statistics = dalo_chart_overall_user_statistics(
    $dbSocket,
    $configValues['CONFIG_DB_TBL_RADACCT'],
    $username,
    $category,
    $type,
    $size,
    'traffic %sed by user %s',
    true
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
