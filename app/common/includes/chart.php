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

function dalo_chart_overall_user_statistics(PDO $dbSocket, $radacct_table, $username, $category, $type, $size, $traffic_title_template, $require_existing_user = false) {
    require_once __DIR__ . '/chart_pdo.php';
    return dalo_chart_overall_pdo($dbSocket,$radacct_table,$username,$category,$type,$size,$traffic_title_template,$require_existing_user);
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
