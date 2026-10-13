<?php
/*
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, version 2 or later.
 * User portal CSV export: fixed PDO queries, no session-supplied SQL.
 */

include __DIR__ . '/../../library/checklogin.php';
include_once __DIR__ . '/../../../common/includes/config_read.php';
require_once __DIR__ . '/../../../common/includes/pdo_connection.php';
require_once __DIR__ . '/../../library/user_report_export.php';

// The old exporter consumed export state on each request; do not replay it.
$descriptor = $_SESSION['userReportExport'] ?? null;
unset($_SESSION['userReportExport'], $_SESSION['export_query'],
      $_SESSION['export_items'], $_SESSION['export_title']);

try {
    [$sql, $bindings, $header, $numericColumns] = dalo_user_export_query(
        $descriptor, $_SESSION['login_user'] ?? null, $configValues
    );
    $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
    $statement = $pdo->prepare($sql);
    foreach ($bindings as $name => $value) {
        $statement->bindValue($name, $value, PDO::PARAM_STR);
    }
    $statement->execute();
    $first = $statement->fetch(PDO::FETCH_NUM);
    if ($first === false) {
        // Legacy behavior: an empty report has no attachment or CSV header.
        exit;
    }

    $csv = fopen('php://temp', 'w+');
    if ($csv === false) {
        throw new RuntimeException('Cannot prepare export');
    }
    fputcsv($csv, $header, ',', '"', '');
    fputcsv($csv, dalo_user_export_csv_row($first, $numericColumns), ',', '"', '');
    while ($row = $statement->fetch(PDO::FETCH_NUM)) {
        fputcsv($csv, dalo_user_export_csv_row($row, $numericColumns), ',', '"', '');
    }
    rewind($csv);
    $output = stream_get_contents($csv);
    fclose($csv);
    $pdo = null;
} catch (InvalidArgumentException $exception) {
    http_response_code(400);
    exit;
} catch (Throwable $exception) {
    // Never expose PDO errors, SQL, connection names or credentials.
    http_response_code(500);
    exit;
}

header('Content-type: text/csv');
header(sprintf('Content-disposition: attachment; filename=daloradius__%s.csv; size=%s',
               date('Ymd'), strlen($output)));
print $output;
