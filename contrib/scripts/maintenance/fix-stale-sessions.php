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
 * Description:    This script manages non-terminated (or stale) sessions in a RADIUS accounting table. 
 *                 It updates the `acctstoptime` field to the current time and sets `acctterminatecause` 
 *                 to 'Stale-Session' for sessions that exceed a predefined time threshold.
 *                 The threshold is determined by adding a configured interval and grace period.
 *                 It ensures the threshold is greater than the Acct-Interim-Interval to avoid premature
 *                 session termination.
 * 
 * Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// The generated cron entry invokes PHP CLI. Never accept unauthenticated HTTP writes.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo 'Stale-session repair is available only through PHP CLI.';
    exit;
}

$pdo = null;
try {
    $includes = dirname(__DIR__, 3) . '/app/common/includes';
    foreach (array('config_read.php', 'daloradius.conf.php', 'pdo_connection.php') as $file) {
        if (!is_file($includes . '/' . $file) || !is_readable($includes . '/' . $file)) {
            throw new RuntimeException('Maintenance configuration is unavailable');
        }
    }
    require_once $includes . '/config_read.php';
    require_once $includes . '/pdo_connection.php';

    // Preserve the historical interval/grace conversion and defaults.
    foreach (array('CONFIG_FIX_STALE_INTERVAL', 'CONFIG_FIX_STALE_GRACE') as $key) {
        if (isset($configValues[$key]) && !is_scalar($configValues[$key])) {
            throw new InvalidArgumentException('Invalid maintenance threshold');
        }
    }
    $interval = intval($configValues['CONFIG_FIX_STALE_INTERVAL'] ?? 0);
    if ($interval <= 0) {
        $interval = 60;
    }
    $grace = intval($configValues['CONFIG_FIX_STALE_GRACE'] ?? 0);
    if ($grace <= 0 || $grace > $interval) {
        $grace = intdiv($interval, 2);
    }
    if ($interval > PHP_INT_MAX - $grace) {
        throw new InvalidArgumentException('Maintenance threshold overflow');
    }
    $timeThreshold = $interval + $grace;

    $table = $configValues['CONFIG_DB_TBL_RADACCT'] ?? null;
    if (!is_string($table) || strlen($table) > 64 || !preg_match('/\A[A-Za-z0-9_]+\z/D', $table)) {
        throw new InvalidArgumentException('Invalid accounting table');
    }
    $quotedTable = '`' . $table . '`';
    $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') {
        throw new RuntimeException('Stale-session repair requires MySQL or MariaDB');
    }
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start maintenance transaction');
    }

    // Hold the target's metadata lock before engine preflight so concurrent DDL
    // cannot replace a transactional table between the check and the updates.
    $probe = $pdo->query('SELECT 1 FROM ' . $quotedTable . ' LIMIT 0');
    $probe->closeCursor();
    $engine = $pdo->prepare('SELECT ENGINE, TABLE_TYPE FROM information_schema.TABLES
                            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table');
    $engine->execute(array(':table' => $table));
    $metadata = $engine->fetch();
    $engine->closeCursor();
    if (!$metadata || strcasecmp($metadata['ENGINE'] ?? '', 'InnoDB') !== 0 ||
        $metadata['TABLE_TYPE'] !== 'BASE TABLE') {
        throw new RuntimeException('Accounting table must be transactional');
    }

    $stop = $pdo->prepare("UPDATE $quotedTable
                             SET `acctstoptime` = NOW(), `acctterminatecause` = 'Stale-Session'
                           WHERE (UNIX_TIMESTAMP(NOW()) - (UNIX_TIMESTAMP(`acctstarttime`) + `acctsessiontime`)) > :threshold
                             AND (`acctstoptime` = '0000-00-00 00:00:00' OR `acctstoptime` IS NULL)");
    $stop->bindValue(':threshold', $timeThreshold, PDO::PARAM_INT);
    if (!$stop->execute()) {
        throw new RuntimeException('Session-stop update failed');
    }
    $stop->closeCursor();

    // Intentionally retain the legacy DATE_ADD formula and its update order.
    $start = $pdo->prepare("UPDATE $quotedTable
                              SET `acctstarttime` = DATE_ADD(NOW(), INTERVAL (`acctsessiontime` + :threshold) SECOND)
                            WHERE (`acctstarttime` = '0000-00-00 00:00:00' OR `acctstarttime` IS NULL)
                              AND `acctsessiontime` > 0");
    $start->bindValue(':threshold', $timeThreshold, PDO::PARAM_INT);
    if (!$start->execute()) {
        throw new RuntimeException('Session-start update failed');
    }
    $start->closeCursor();
    if (!$pdo->commit()) {
        throw new RuntimeException('Maintenance commit failed');
    }
    $probe = $engine = $stop = $start = null;
    $pdo = null;
} catch (Throwable $error) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        try {
            $pdo->rollBack();
        } catch (Throwable $rollbackError) {
            // Do not turn an uncertain rollback into a success or expose driver details.
        }
    }
    $pdo = null;
    fwrite(STDERR, 'Stale-session repair failed.' . PHP_EOL);
    exit(1);
}
