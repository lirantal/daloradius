<?php
include_once __DIR__.'/../../../common/includes/config_read.php';
include __DIR__.'/../checklogin.php';
require_once __DIR__.'/../../../common/includes/chart.php';
require_once __DIR__.'/../widget_reads_pdo.php';
dalo_widget_inputs(true);


$day = isset($_GET['day']) && intval($_GET['day']) > 0 && intval($_GET['day']) <= 31 ? intval($_GET['day']) : '';
$month = isset($_GET['month']) && intval($_GET['month']) > 0 && intval($_GET['month']) <= 12 ? intval($_GET['month']) : intval(date('m'));
$year = isset($_GET['year']) && intval($_GET['year']) > 1970 && intval($_GET['year']) <= intval(date('Y')) ? intval($_GET['year']) : intval(date('Y'));

try {
$widgetPDO=dalo_widget_open();
dalo_widget_authorize($widgetPDO,array('graphs-logged_users'));
if ($day !== '') {
    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
    $sql = sprintf("SELECT DATE(acctstarttime) AS starting_day, HOUR(acctstarttime) AS starting_hour, HOUR(DATE_ADD(acctstoptime, INTERVAL 1 HOUR)) AS ending_hour, DATE(DATE_ADD(acctstoptime, INTERVAL 1 HOUR)) AS ending_day FROM %s WHERE acctstarttime <= :widget_start AND (acctstoptime >= :widget_end OR (acctsessiontime = 0 AND acctinputoctets = 0 AND acctoutputoctets = 0))", dalo_widget_table($configValues,'CONFIG_DB_TBL_RADACCT'));
    $res = dalo_widget_rows($widgetPDO,$sql,array(':widget_start'=>$date,':widget_end'=>$date));
    $by_hour = array_fill(0, 24, 0);
    foreach ($res as $row) {
        $end = $row[0] === $row[3] ? intval($row[2]) : 23;
        for ($i = intval($row[1]); $i <= $end; $i++) {
            $by_hour[$i]++;
        }
    }
    $labels = array();
    for ($i = 0; $i < 24; $i++) {
        $labels[] = sprintf('%d:00-%d:59', $i, $i);
    }
    $datasets = array(array(
        'label' => 'accounted users',
        'data' => $by_hour,
        'backgroundColor' => 'rgba(54, 162, 235, 0.55)',
        'borderColor' => 'rgb(54, 162, 235)',
        'borderWidth' => 1,
    ));
    $title = sprintf('hour distribution of users accounted on %s', $date);
    $xtitle = 'time slot';
} else {
    $start = sprintf('%04d-%02d-01', $year, $month);
    $end = date('Y-m-d', strtotime($start . ' +1 month'));
    $labels = array();
    $min = array();
    $max = array();
    for ($date = $start; $date <= $end; $date = date('Y-m-d', strtotime($date . ' +1 day'))) {
        $sql = sprintf("SELECT COUNT(DISTINCT(radacctid)) FROM %s WHERE DATE(acctstarttime) <= :widget_start AND (DATE(acctstoptime) >= :widget_end OR (acctsessiontime = 0 AND acctinputoctets = 0 AND acctoutputoctets = 0)) GROUP BY HOUR(acctstarttime)", dalo_widget_table($configValues,'CONFIG_DB_TBL_RADACCT'));
        $res = dalo_widget_rows($widgetPDO,$sql,array(':widget_start'=>$date,':widget_end'=>$date));
        $counts = array();
        foreach ($res as $row) {
            $counts[] = intval($row[0]);
        }
        if (count($counts)) {
            $labels[] = $date;
            $min[] = min($counts);
            $max[] = max($counts);
        }
    }
    $datasets = array(
        array('label' => 'minimum', 'data' => $min, 'backgroundColor' => 'rgba(54, 162, 235, 0.55)'),
        array('label' => 'maximum', 'data' => $max, 'backgroundColor' => 'rgba(255, 99, 132, 0.55)'),
    );
    $title = sprintf('min/max per-day accounted users from %s to %s', $start, $end);
    $xtitle = 'time slot';
}
unset($widgetPDO);
} catch (Throwable $exception) {dalo_widget_failure($exception,true);}
dalo_chart_response('bar', $labels, $datasets, $title, $xtitle, 'accounted users');
