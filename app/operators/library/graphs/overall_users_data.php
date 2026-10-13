<?php
include_once __DIR__.'/../../../common/includes/config_read.php';
include __DIR__.'/../checklogin.php';
require_once __DIR__.'/../../../common/includes/chart.php';
require_once __DIR__.'/../widget_reads_pdo.php';
dalo_widget_inputs(true);


$category = (isset($_GET['category']) && in_array(strtolower(trim($_GET['category'])), array('upload', 'download', 'login')))
    ? strtolower(trim($_GET['category']))
    : 'download';
$type = (isset($_GET['type']) && in_array(strtolower($_GET['type']), array('daily', 'monthly', 'yearly')))
    ? strtolower($_GET['type'])
    : 'daily';
$size = (isset($_GET['size']) && in_array(strtolower($_GET['size']), array('gigabytes', 'megabytes')))
    ? strtolower($_GET['size'])
    : 'megabytes';
$username = isset($_GET['user']) ? $_GET['user'] : '';

try {
$widgetPDO=dalo_widget_open();
dalo_widget_authorize($widgetPDO,array('graphs-overall_'.($category==='login'?'logins':$category)));
$statistics = dalo_chart_overall_user_statistics(
    $widgetPDO,
    $configValues['CONFIG_DB_TBL_RADACCT'],
    $username,
    $category,
    $type,
    $size,
    'traffic %sed by user %s',
    true
);
unset($widgetPDO);
} catch (Throwable $exception) {dalo_widget_failure($exception,true);}

dalo_chart_response(
    'bar',
    $statistics['labels'],
    array(dalo_chart_bar_dataset($statistics['ytitle'], $statistics['values'])),
    $statistics['title'],
    ucfirst($type) . ' distribution',
    $statistics['ytitle']
);
