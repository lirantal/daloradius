<?php
/*
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com>
 * SPDX-License-Identifier: GPL-2.0-or-later
 * Shared PDO-only Chilli connection boundary.
 */

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(404);
    exit;
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
    // Four historical wrappers omitted the port. Preserve their standard-port policy.
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

/**
 * PDO-only entry point. Never assigns $dbSocket.
 * Every dependent write must use this one returned handle.
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

/** PDO-only close never commits. Rollback covers retained handle/statement refs. */
function dalo_chilli_database_close(&$socket) {
    if ($socket === null) { return; }
    try {
        if (!($socket instanceof PDO)) {
            throw new RuntimeException('Invalid database handle');
        }
        if ($socket->inTransaction() && !$socket->rollBack()) {
            throw new RuntimeException('Rollback failed');
        }
    } catch (Throwable $error) {
        throw new RuntimeException('Database close failed');
    } finally {
        $socket = null;
    }
}
