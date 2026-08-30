<?php
/* JSON and data helpers for Chart.js graph endpoints. */

function dalo_chart_bar_dataset($label, $values) {
    return array(
        'label' => $label,
        'data' => $values,
        'backgroundColor' => 'rgba(54, 162, 235, 0.55)',
        'borderColor' => 'rgb(54, 162, 235)',
        'borderWidth' => 1,
    );
}

function dalo_chart_overall_user_statistics($dbSocket, $radacct_table, $username, $category, $type, $size, $traffic_title_template, $require_existing_user = false) {
    $labels = array();
    $values = array();

    if (!empty($username)) {
        $escaped_username = $dbSocket->escapeSimple($username);
        $has_matching_user = true;

        if ($require_existing_user) {
            $check_sql = sprintf(
                "SELECT DISTINCT(username) FROM %s WHERE username='%s'",
                $radacct_table,
                $escaped_username
            );
            $has_matching_user = $dbSocket->query($check_sql)->numRows() === 1;
        }

        if ($has_matching_user) {
            $dbfield = $category === 'login'
                ? 'COUNT(AcctStartTime)'
                : ($category === 'upload' ? 'SUM(AcctInputOctets)' : 'SUM(AcctOutputOctets)');

            $session_filter = $category === 'login' ? '' : ' AND AcctStopTime>0';

            if ($type === 'yearly') {
                $sql = "SELECT YEAR(AcctStartTime), %s FROM %s WHERE username='%s'%s GROUP BY YEAR(AcctStartTime) ORDER BY YEAR(AcctStartTime) DESC LIMIT 36";
            } elseif ($type === 'monthly') {
                $sql = "SELECT CONCAT(LEFT(MONTHNAME(AcctStartTime), 3), ' (', YEAR(AcctStartTime), ')'), %s FROM %s WHERE username='%s'%s GROUP BY YEAR(AcctStartTime), MONTH(AcctStartTime) ORDER BY YEAR(AcctStartTime) DESC, MONTH(AcctStartTime) DESC LIMIT 36";
            } else {
                $sql = "SELECT DATE(AcctStartTime), %s FROM %s WHERE username='%s'%s GROUP BY DATE(AcctStartTime) ORDER BY DATE(AcctStartTime) DESC LIMIT 36";
            }

            $res = $dbSocket->query(sprintf($sql, $dbfield, $radacct_table, $escaped_username, $session_filter));
            $division = $size === 'gigabytes' ? 1073741824 : 1048576;

            while ($row = $res->fetchRow()) {
                $labels[] = strval($row[0]);
                $values[] = $category === 'login'
                    ? intval($row[1])
                    : round(floatval($row[1]) / $division, 1);
            }
        }
    }

    $ytitle = $category === 'login'
        ? 'Login count'
        : ucfirst($size) . ' ' . $category . 'ed';
    $title = $category === 'login'
        ? sprintf('login statistics for user %s', $username)
        : sprintf($traffic_title_template, $category, $username);

    return array(
        'labels' => $labels,
        'values' => $values,
        'title' => $title,
        'ytitle' => $ytitle,
    );
}

function dalo_chart_response($type, $labels, $datasets, $title, $x_title = '', $y_title = '') {
    $options = array(
        'responsive' => true,
        'maintainAspectRatio' => false,
        'plugins' => array(
            'title' => array('display' => true, 'text' => $title),
            'tooltip' => array('enabled' => true),
        ),
    );

    if ($type !== 'pie' && $type !== 'doughnut') {
        $options['scales'] = array(
            'x' => array('title' => array('display' => !empty($x_title), 'text' => $x_title)),
            'y' => array('beginAtZero' => true, 'title' => array('display' => !empty($y_title), 'text' => $y_title)),
        );
    }

    $json = json_encode(array(
        'type' => $type,
        'data' => array('labels' => array_values($labels), 'datasets' => $datasets),
        'options' => $options,
    ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    header('Content-Type: application/json; charset=utf-8');
    if ($json === false) {
        http_response_code(500);
        echo '{"error":"Unable to encode chart response"}';
        exit;
    }

    echo $json;
    exit;
}
