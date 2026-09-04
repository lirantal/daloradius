<?php
/* JSON response helpers for the read-only operator information endpoints. */

function dalo_info_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function dalo_info_parameter($name) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header('Allow: GET');
        dalo_info_response(['error' => 'Method not allowed.'], 405);
    }
    if (!isset($_GET[$name]) || !is_string($_GET[$name]) || trim($_GET[$name]) === '') {
        dalo_info_response(['error' => 'Missing or invalid parameter.'], 400);
    }
    // Preserve the existing lookup normalization.
    return str_replace('%', '', trim($_GET[$name]));
}

function dalo_info_bytes($value) {
    return empty($value) ? '(n/a)' : toxbyte(max(0, intval($value)));
}
