<?php
include('../checklogin.php');
include('../../../common/includes/chart.php');
include_once('../../../common/includes/config_read.php');

$username = $_SESSION['login_user'] ?? '';
$category = (isset($_GET['category']) && is_string($_GET['category']) && in_array(strtolower(trim($_GET['category'])), array('upload', 'download', 'login')))
    ? strtolower(trim($_GET['category']))
    : 'download';
$type = (isset($_GET['type']) && is_string($_GET['type']) && in_array(strtolower($_GET['type']), array('daily', 'monthly', 'yearly')))
    ? strtolower($_GET['type'])
    : 'daily';
$size = (isset($_GET['size']) && is_string($_GET['size']) && in_array(strtolower($_GET['size']), array('gigabytes', 'megabytes')))
    ? strtolower($_GET['size'])
    : 'megabytes';

require_once __DIR__ . '/../portal_widgets_pdo.php';
$widgetPdo = null;
$statistics = null;
try {
    $username = dalo_portal_widget_identity($username);
    $widgetPdo = dalo_portal_handle($configValues);
    $statistics = dalo_chart_overall_user_statistics(
        $widgetPdo,
        $configValues['CONFIG_DB_TBL_RADACCT'],
        $username,
        $category,
        $type,
        $size,
        'traffic in %s by user %s'
    );
} catch (Throwable $exception) {
    $statistics = null;
} finally { $widgetPdo = null; }

if ($statistics === null) {
    http_response_code(503);
    header('Content-Type: application/json; charset=utf-8');
    echo '{"error":"Portal statistics unavailable"}';
    exit;
}

dalo_chart_response(
    'bar',
    $statistics['labels'],
    array(dalo_chart_bar_dataset($statistics['ytitle'], $statistics['values'])),
    $statistics['title'],
    ucfirst($type) . ' distribution',
    $statistics['ytitle']
);
