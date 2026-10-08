<?php
/*
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or later.
 *
 * Operator CSV export: descriptor + fixed query builders, never session SQL.
 */

include_once __DIR__ . '/../../../common/includes/config_read.php';
include $configValues['OPERATORS_LIBRARY'] . '/checklogin.php';
require_once $configValues['COMMON_INCLUDES'] . '/pdo_connection.php';
require_once __DIR__ . '/../../library/report_export.php';

$format = $_GET['reportFormat'] ?? 'csv';
if (!is_string($format) || strtolower($format) !== 'csv') {
    // PDF was never implemented in the legacy endpoint.
    exit;
}

try {
    $descriptor = dalo_export_descriptor($_SESSION, $_GET);
    $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');

    // An export is an operation on its source page, not an ACL bypass. The
    // direct group export is authorized against mng-rad-profiles-list.
    $acl = dalo_export_table($configValues, 'CONFIG_DB_TBL_DALOOPERATORS_ACL');
    $permission = $pdo->prepare("SELECT access FROM $acl WHERE operator_id=:operator AND file=:file");
    $permission->execute([
        ':operator' => (int) $_SESSION['operator_id'],
        ':file' => str_replace('-', '_', $descriptor['source']),
    ]);
    if ((int) $permission->fetchColumn() !== 1) {
        http_response_code(403);
        exit;
    }

    $res = dalo_export_fetch($pdo, $descriptor, $configValues);
    $type = $descriptor['type'];
    $output = '';

    switch ($type) {
        case 'accountingGeneric':
            $output = "Id,NAS/Hotspot,UserName,IP Address,Start Time,Stop Time,"
                    . "Total Session Time (seconds),Total Upload (bytes),Total Downloads (bytes),"
                    . "Termination Cause,NAS IP Address\n";
            while ($row = $res->fetch(PDO::FETCH_NUM)) {
                $output .= implode(',', $row) . "\n";
            }
            break;

        case 'usernameListGeneric':
        case 'usernameListByGroup':
            // Same import-compatible order and CSV quoting as the legacy export.
            $fields = ['username', 'password', 'email', 'firstname', 'lastname',
                       'framedipaddress', 'expiration', 'department', 'company',
                       'mobilephone', 'workphone', 'homephone', 'address', 'city',
                       'state', 'country', 'zip', 'sessiontimeout', 'idletimeout',
                       'maxdailysession'];
            $csv = fopen('php://temp', 'r+');
            fputcsv($csv, $fields, ',', '"', '');
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['expiration'])) {
                    $expiration = DateTime::createFromFormat('d M Y', $row['expiration']);
                    if ($expiration !== false) {
                        $row['expiration'] = $expiration->format('Y-m-d');
                    }
                }
                $values = [];
                foreach ($fields as $field) {
                    $values[] = $row[$field] ?? '';
                }
                fputcsv($csv, $values, ',', '"', '');
            }
            rewind($csv);
            $output = stream_get_contents($csv);
            fclose($csv);
            break;

        case 'reportsOnlineUsers':
            $output = "Username,User IP Address,User MAC Address,Start Time,Total Time,NAS IP Address,NAS MAC Address\n";
            while ($row = $res->fetch(PDO::FETCH_NUM)) {
                $output .= implode(',', $row) . "\n";
            }
            break;

        case 'reportsLastConnectionAttempts':
            $output = "Username,Fullname,Start Time,RADIUS Reply\n";
            while ($row = $res->fetch(PDO::FETCH_NUM)) {
                $output .= implode(',', $row) . "\n";
            }
            break;

        case 'TopUsers':
            $output = "Username, IP Address, Start Time,Stop Time, Account Session Time, Account Input, Account Output, Total Bandwidth\n";
            while ($row = $res->fetch(PDO::FETCH_NUM)) {
                // Legacy SELECT includes termination cause and NAS IP at 7/8.
                $output .= implode(',', [$row[0], $row[1], $row[2], $row[3],
                                        $row[4], $row[5], $row[6], $row[9]]) . "\n";
            }
            break;

        case 'reportsPlansUsage':
            $output = "Username, Plan Name, Used Time, Upload, Download, Plan Time, Plan Time Type\n";
            while ($row = $res->fetch(PDO::FETCH_NUM)) {
                $output .= implode(',', $row) . "\n";
            }
            break;

        case 'reportsBatchActiveUsers':
            $output = "Batch Name, Username, Start Time\n";
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                $output .= ($row['batch_name'] ?? '') . ',' . ($row['username'] ?? '')
                        . ',' . ($row['acctstarttime'] ?? '') . "\n";
            }
            break;

        case 'reportsBatchList':
            $output = "Batch Name, Hotspot, Status, Total Users, Active Users, Plan Name, Plan Cost, Batch Cost, Creation Date, Created By\n";
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                $cost = ($row['active_users'] ?? 0) * ($row['plancost'] ?? 0);
                $output .= implode(',', [$row['batch_name'] ?? '', $row['HotspotName'] ?? '',
                    $row['batch_status'] ?? '', $row['total_users'] ?? '', $row['active_users'] ?? '',
                    $row['planname'] ?? '', $row['plancost'] ?? '', $cost,
                    $row['creationdate'] ?? '', $row['creationby'] ?? '']) . "\n";
            }
            break;

        case 'reportsBatchTotalUsers':
            $batchId = $descriptor['filters']['batch_id'] ?? null;
            if (!is_int($batchId) || $batchId <= 0) {
                throw new InvalidArgumentException('Invalid batch identifier');
            }
            $batchTable = dalo_export_table($configValues, 'CONFIG_DB_TBL_DALOBATCHHISTORY');
            $batch = $pdo->prepare("SELECT batch_name FROM $batchTable WHERE id=:id LIMIT 1");
            $batch->bindValue(':id', $batchId, PDO::PARAM_INT);
            $batch->execute();
            $batchName = $batch->fetchColumn();
            $rows = $res->fetchAll(PDO::FETCH_NUM);
            $exportable = false;
            foreach ($rows as $row) {
                if (in_array($row[1], ['Cleartext-Password', 'User-Password'], true)) {
                    $exportable = true;
                    break;
                }
            }
            $output = sprintf("# batch name: %s, users num.: %d\n", $batchName, count($rows));
            $output .= 'Username' . ($exportable ? ',Password' : '') . "\n";
            foreach ($rows as $row) {
                $output .= $row[0];
                if ($exportable) {
                    $output .= ',' . (in_array($row[1], ['Cleartext-Password', 'User-Password'], true)
                        ? $row[2] : '(empty)');
                }
                $output .= "\n";
            }
            break;

        case 'reportsInvoiceList':
            $output = "Invoice ID, Customer Name, Username, Date, Total Billed, Total Payed, Balance, Invoice Status\n";
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                $balance = ($row['totalpayed'] ?? 0) - ($row['totalbilled'] ?? 0);
                $output .= implode(',', [$row['id'], $row['contactperson'], $row['username'],
                    $row['date'], $row['totalbilled'], $row['totalpayed'], $balance,
                    $row['status']]) . "\n";
            }
            break;

        default:
            throw new InvalidArgumentException('Unsupported export type');
    }
    $pdo = null;
} catch (InvalidArgumentException $exception) {
    http_response_code(400);
    exit;
} catch (RuntimeException $exception) {
    // Never echo driver errors: they may contain SQL or credentials.
    http_response_code(500);
    exit;
}

if ($output !== '') {
    header('Content-type: text/csv');
    header(sprintf('Content-disposition: attachment; filename=daloradius__%s.csv; size=%s',
                   date('Ymd'), strlen($output)));
    print $output;
}
