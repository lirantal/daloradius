<?php
/*
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com>
 * SPDX-License-Identifier: GPL-2.0-or-later
 * Shared Chilli connection boundary. Legacy callers remain on PEAR DB.
 */

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(404);
    exit;
}

/** Never render driver details: they can contain credentials and SQL values. */
function dalo_chilli_database_error($error) {
    echo '<br/><b>Database error</b><br>';
}

/** Validate settings without building a credential-bearing URI. */
function dalo_chilli_database_settings($config, $useConfiguredPort) {
    if (!is_array($config)) {
        throw new RuntimeException('Invalid database configuration');
    }
    foreach (array('ENGINE', 'HOST', 'NAME', 'USER', 'PASS') as $key) {
        $name = 'CONFIG_DB_' . $key;
        if (!isset($config[$name]) || !is_string($config[$name])) {
            throw new RuntimeException('Invalid database configuration');
        }
    }
    foreach (array('HOST', 'NAME', 'ENGINE') as $key) {
        $value = $config['CONFIG_DB_' . $key];
        if ($value === '' || strpbrk($value, ";\r\n\0") !== false) {
            throw new RuntimeException('Invalid database configuration');
        }
    }
    // Four historical wrappers omitted the port. Preserve that choice for PEAR.
    if (!$useConfiguredPort || !array_key_exists('CONFIG_DB_PORT', $config)) {
        $config['CONFIG_DB_PORT'] = strtolower($config['CONFIG_DB_ENGINE']) === 'pgsql' ? '5432' : '3306';
    }
    $port = $config['CONFIG_DB_PORT'];
    if ((!is_string($port) && !is_int($port)) || !ctype_digit((string) $port) ||
        (int) $port < 1 || (int) $port > 65535) {
        throw new RuntimeException('Invalid database configuration');
    }
    return $config;
}

/** Retained compatibility boundary: no PDO adapter or mixed-client transaction. */
function dalo_chilli_pear_open($config, $useConfiguredPort = false) {
    $socket = null;
    try {
        $config = dalo_chilli_database_settings($config, $useConfiguredPort);
        require_once 'DB.php';
        $dsn = array(
            'phptype' => $config['CONFIG_DB_ENGINE'],
            'username' => $config['CONFIG_DB_USER'],
            'password' => $config['CONFIG_DB_PASS'],
            'hostspec' => $config['CONFIG_DB_HOST'],
            'database' => $config['CONFIG_DB_NAME'],
        );
        if ($useConfiguredPort) { $dsn['port'] = (int) $config['CONFIG_DB_PORT']; }
        $socket = DB::connect($dsn);
        if (DB::isError($socket)) { throw new RuntimeException('Database unavailable'); }
        $socket->setErrorHandling(PEAR_ERROR_CALLBACK, 'dalo_chilli_database_error');
        return $socket;
    } catch (Throwable $error) {
        if (is_object($socket) && !($socket instanceof PEAR_Error)) {
            try { $socket->disconnect(); } catch (Throwable $ignored) { /* Keep errors redacted. */ }
        }
        throw new RuntimeException('Database connection failed');
    }
}

/**
 * Explicit entry point for migrated callers only. Never assigns $dbSocket and
 * never opens PEAR. Every dependent write must use this one returned handle.
 * Uses UNIT-001 settings/options; caller owns begin/commit/rollback.
 */
function dalo_chilli_pdo_open($config) {
    try {
        $config = dalo_chilli_database_settings($config, true);
        require_once dirname(__DIR__, 3) . '/app/common/includes/pdo_connection.php';
        return dalo_pdo_connect($config, 'default');
    } catch (Throwable $error) {
        throw new RuntimeException('Database connection failed');
    }
}

/** Close never commits. Explicit rollback also covers retained statement refs. */
function dalo_chilli_database_close(&$socket) {
    if ($socket === null) { return; }
    try {
        if ($socket instanceof PDO) {
            if ($socket->inTransaction() && !$socket->rollBack()) {
                throw new RuntimeException('Rollback failed');
            }
        } else {
            $result = $socket->disconnect();
            if (DB::isError($result)) { throw new RuntimeException('Disconnect failed'); }
        }
    } catch (Throwable $error) {
        throw new RuntimeException('Database close failed');
    } finally {
        $socket = null;
    }
}
