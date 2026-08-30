<?php
include('../checklogin.php');
include('../../../common/includes/chart.php');

$day = isset($_GET['day']) && intval($_GET['day']) > 0 && intval($_GET['day']) <= 31 ? intval($_GET['day']) : '';
$month = isset($_GET['month']) && intval($_GET['month']) > 0 && intval($_GET['month']) <= 12 ? intval($_GET['month']) : intval(date('m'));
$year = isset($_GET['year']) && intval($_GET['year']) > 1970 && intval($_GET['year']) <= intval(date('Y')) ? intval($_GET['year']) : intval(date('Y'));

include('../../../common/includes/db_open.php');
if ($day !== '') {
    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
    $sql = sprintf("SELECT DATE(acctstarttime) AS starting_day, HOUR(acctstarttime) AS starting_hour, HOUR(DATE_ADD(acctstoptime, INTERVAL 1 HOUR)) AS ending_hour, DATE(acctstoptime) AS ending_day FROM %s WHERE acctstarttime <= '%s' AND (acctstoptime >= '%s' OR (acctsessiontime = 0 AND acctinputoctets = 0 AND acctoutputoctets = 0))", $configValues['CONFIG_DB_TBL_RADACCT'], $date, $date);
    $res = $dbSocket->query($sql);
    $by_hour = array_fill(0, 24, 0);
    while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $end = $row['starting_day'] === $row['ending_day'] ? intval($row['ending_hour']) : 23;
        for ($i = intval($row['starting_hour']); $i <= $end; $i++) {
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
        $sql = sprintf("SELECT COUNT(DISTINCT(radacctid)) FROM %s WHERE DATE(acctstarttime) <= '%s' AND (DATE(acctstoptime) >= '%s' OR (acctsessiontime = 0 AND acctinputoctets = 0 AND acctoutputoctets = 0)) GROUP BY HOUR(acctstarttime)", $configValues['CONFIG_DB_TBL_RADACCT'], $date, $date);
        $res = $dbSocket->query($sql);
        $counts = array();
        while ($row = $res->fetchRow()) {
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
include('../../../common/includes/db_close.php');
dalo_chart_response('bar', $labels, $datasets, $title, $xtitle, 'accounted users');
