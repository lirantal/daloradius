<?php
/* JSON helpers for Chart.js graph endpoints. */
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
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array(
        'type' => $type,
        'data' => array('labels' => array_values($labels), 'datasets' => $datasets),
        'options' => $options,
    ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    exit;
}
