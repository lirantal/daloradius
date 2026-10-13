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
 * Description:    Read-only node status monitor; send offline-node alerts via SMTP.
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
    $recipient = trim($configValues['CONFIG_NODE_STATUS_MONITOR_EMAIL_TO'] ?? '');
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        echo "Email not valid";
        return;
    }
    require_once $includes . '/pdo_connection.php';
    $table = $configValues['CONFIG_DB_TBL_DALONODE'] ?? null;
    if (!is_string($table) || strlen($table) > 64 || !preg_match('/\A[A-Za-z0-9_]+\z/D', $table)) {
        throw new InvalidArgumentException('Invalid monitor table');
    }
    $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') {
        throw new RuntimeException('Monitor SQL requires MySQL/MariaDB');
    }
    $delay = $_POST['CONFIG_NODE_STATUS_MONITOR_HARD_DELAY']
        ?? $configValues['CONFIG_NODE_STATUS_MONITOR_HARD_DELAY'] ?? 15;
    if (!is_scalar($delay)) {
        throw new InvalidArgumentException('Invalid node delay');
    }
    $delay = max(1, intval($delay));
    $columns = ['mac', 'memfree', 'cpu', 'wan_ip', 'wan_gateway', 'lan_mac', 'firmware', 'firmware_revision'];
    $imploded_columns = '`' . implode('`, `', $columns) . '`';
    $stmt = $pdo->prepare("SELECT $imploded_columns FROM `$table`
                           WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`time`) > ?");
    $stmt->bindValue(1, $delay, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_NUM);
    $stmt->closeCursor();
    $stmt = null;
    if ($rows) {
        $body = <<<EOF
Dear system administrator,
the following nodes seem to be offline:

{$imploded_columns}

EOF;
        foreach ($rows as $row) {
            // Preserve the historical separator-free node row format.
            $body .= implode($row) . "\n";
        }
        $subject = "daloRADIUS node status monitor";
        require_once $includes . '/mail.php';
        list($success, $message) = send_email($configValues, $recipient, 'daloRADIUS sysadmin', $subject, $body);
        $mail_failed = !$success;
        printf("%s: %s", $success ? "SUCCESS" : "FAILURE",
               $success ? "Email sent successfully" : "Email delivery failed");
    }
} catch (Throwable $error) {
    // SQL/SMTP exceptions can contain connection or account data; never print them.
    fwrite(STDERR, "Unable to run node status monitor.\n");
    $monitor_failed = true;
} finally {
    $stmt = $rows = $row = $pdo = null;
}
exit(($monitor_failed || $mail_failed) ? 1 : 0);
