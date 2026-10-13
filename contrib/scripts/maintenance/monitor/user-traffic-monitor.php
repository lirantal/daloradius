<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Description:    This script monitors user traffic. It retrieves user data from the database and
 *                 sends email notifications to the system administrator for users exceeding these limits.
 *                 The script distinguishes between hard and soft limit violations,
 *                 providing detailed information about the users and their traffic usage.
 * 
 * Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// Scheduled maintenance runs in PHP CLI; deny HTTP before config, SQL or SMTP.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

$pdo = null;
$mail_failed = false;
$monitor_failed = false;
try {
    $includes = dirname(__DIR__, 4) . '/app/common/includes';
    foreach (array('config_read.php', 'daloradius.conf.php', 'pdo_connection.php') as $file) {
        if (!is_file($includes . '/' . $file) || !is_readable($includes . '/' . $file)) {
            throw new RuntimeException('Monitor configuration unavailable');
        }
    }
    require_once $includes . '/config_read.php';
    if (strtolower($configValues['CONFIG_MAIL_ENABLED'] ?? '') !== 'yes') {
        echo "SMTP Server not configured";
        return;
    }
    $recipient = trim($configValues['CONFIG_USER_TRAFFIC_MONITOR_EMAIL_TO'] ?? '');
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        echo "Email not valid";
        return;
    }
    require_once $includes . '/pdo_connection.php';
    $table = $configValues['CONFIG_DB_TBL_RADACCT'] ?? null;
    if (!is_string($table) || strlen($table) > 64 || !preg_match('/\A[A-Za-z0-9_]+\z/D', $table)) {
        throw new InvalidArgumentException('Invalid monitor table');
    }
    $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') {
        throw new RuntimeException('Monitor SQL requires MySQL/MariaDB');
    }
    $hard = $_POST['CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT']
        ?? $configValues['CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT'] ?? 1073741824;
    if (!is_scalar($hard)) {
        throw new InvalidArgumentException('Invalid hard limit');
    }
    $hard = max(1, intval($hard));
    $soft = $_POST['CONFIG_USER_TRAFFIC_MONITOR_SOFTLIMIT']
        ?? $configValues['CONFIG_USER_TRAFFIC_MONITOR_SOFTLIMIT'] ?? intdiv($hard, 2);
    if (!is_scalar($soft)) {
        throw new InvalidArgumentException('Invalid soft limit');
    }
    $soft = max(1, intval($soft));
    $columns = ['radacctid', 'acctsessionid', 'username', 'nasipaddress', 'nasportid', 'acctstarttime', 'acctsessiontime',
                'acctinputoctets', 'acctoutputoctets', 'calledstationid', 'callingstationid', 'framedipaddress'];
    $imploded_columns = implode(', ', $columns);
    $sql = "SELECT $imploded_columns FROM `$table` WHERE (acctstoptime = '0000-00-00 00:00:00' OR acctstoptime IS NULL) ";
    $sum = '(CAST(`acctinputoctets` AS UNSIGNED) + CAST(`acctoutputoctets` AS UNSIGNED))';
    $stmt = $pdo->prepare($sql . "AND $sum >= ?");
    $stmt->bindValue(1, $hard, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    $stmt = null;
    // Preserve the historical policy: soft is checked only after a hard match.
    if ($rows) {
        $users = [];
        $subject = "daloRADIUS user traffic monitor";
        $body1 = <<<EOF
    Dear system administrator,
    the following users seem to have exceeded the traffic monitor hard limit threshold ({$hard} bytes):

    EOF;
        $body1 .= implode(", ", array_keys($rows[0])) . "\n";
        foreach ($rows as $row) {
            $users[] = $row['username'];
            $body1 .= implode(", ", $row) . "\n";
        }
        require_once $includes . '/mail.php';
        list($success, $message) = send_email($configValues, $recipient, 'daloRADIUS sysadmin', $subject, $body1);
        $mail_failed = !$success;
        printf("HARD LIMIT TRAFFIC MONITOR => %s: %s", $success ? "SUCCESS" : "FAILURE",
               $success ? "Email sent successfully" : "Email delivery failed");

        // Values from the hard result are still data: never interpolate names.
        $placeholders = implode(', ', array_fill(0, count($users), '?'));
        $stmt = $pdo->prepare($sql . "AND $sum > ? AND `username` NOT IN ($placeholders)");
        $stmt->bindValue(1, $soft, PDO::PARAM_INT);
        foreach ($users as $index => $username) {
            $stmt->bindValue($index + 2, $username, $username === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $stmt = null;
        if ($rows) {
            $body2 = <<<EOF
    Dear system administrator,
    the following users seem to have exceeded the traffic monitor soft limit threshold ({$soft} bytes):

    EOF;
            $body2 .= implode(", ", array_keys($rows[0])) . "\n";
            foreach ($rows as $row) {
                $body2 .= implode(", ", $row) . "\n";
            }
            // Correct the old copy/paste bug: this alert carries the soft body.
            list($success, $message) = send_email($configValues, $recipient, 'daloRADIUS sysadmin', $subject, $body2);
            $mail_failed = $mail_failed || !$success;
            printf("SOFT LIMIT TRAFFIC MONITOR => %s: %s", $success ? "SUCCESS" : "FAILURE",
                   $success ? "Email sent successfully" : "Email delivery failed");
        }
    }
} catch (Throwable $error) {
    // SQL/SMTP exceptions can contain connection or account data; never print them.
    fwrite(STDERR, "Unable to run user traffic monitor.\n");
    $monitor_failed = true;
} finally {
    $stmt = $rows = $row = $pdo = null;
}
exit(($monitor_failed || $mail_failed) ? 1 : 0);
