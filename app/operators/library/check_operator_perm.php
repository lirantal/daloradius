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
 * Description:    check operators permissions
 * 
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/library/check_operator_perm.php') !== false) {
    header("Location: ../index.php");
    exit;
}

// The permission read is independent of any caller-owned business transaction.
// Keep its handle local and do not close/replace a caller's $pdo or $dbSocket.
$operator_acl_pdo = null;
$operator_acl_failure = false;
try {
    include dirname(__DIR__, 2) . '/common/includes/config_read.php';
    require_once dirname(__DIR__, 2) . '/common/includes/pdo_connection.php';
    require_once __DIR__ . '/operator_acl_read.php';
    $file = (isset($operator_perm_file) && !empty($operator_perm_file))
          ? $operator_perm_file
          : str_replace('-', '_', basename($_SERVER['SCRIPT_NAME'], '.php'));
    $operator_acl_pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
    $access = dalo_operator_acl_allowed($operator_acl_pdo, $configValues,
                                      $_SESSION['operator_id'] ?? null, $file);
} catch (InvalidArgumentException $error) {
    // Invalid identity/page/configuration is never permission to proceed.
    $access = false;
} catch (Throwable $error) {
    // Do not pass raw PDO errors to a renderer: they may contain SQL or connection values.
    error_log('Operator permission lookup failed: ' . get_class($error));
    $operator_acl_failure = true;
} finally {
    $operator_acl_pdo = null;
}

if ($operator_acl_failure) {
    if (isset($db_error_handler) && is_callable($db_error_handler)) {
        call_user_func($db_error_handler, new RuntimeException('Operator permission lookup failed'));
    }
    http_response_code(503);
    exit('Unable to check operator permissions.');
}

if (!$access) {
    if (isset($operator_perm_deny_http_status)) {
        http_response_code(intval($operator_perm_deny_http_status));
        exit;
    }
    header('Location: home-error.php');
    exit;
}
