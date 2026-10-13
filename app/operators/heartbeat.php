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
 * Description:    This script validates authorization and
 *                 updates/inserts system information into a database.
 *
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    die("wrong HTTP method");
}

$secret_key=isset($_GET['secret_key']) && is_string($_GET['secret_key']) ? trim($_GET['secret_key']) : '';
if ($secret_key==='') { die('secret_key not provided'); }
include implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);
$configured_secret=$configValues['CONFIG_DASHBOARD_DALO_SECRETKEY'] ?? null;
if (!is_string($configured_secret) || $configured_secret==='' || !hash_equals($configured_secret,$secret_key)) { die('authorization denied'); }
unset($secret_key,$configured_secret);
require_once __DIR__ . '/library/geo_heartbeat_pdo.php';
try {
    $fields=dalo_heartbeat_fields($_GET);
    // Heartbeat is not an operator-session endpoint; it uses the configured default backend.
    $pdo=dalo_pdo_connect($configValues,'default');
    dalo_heartbeat_save($pdo,$configValues,$fields);
} catch (InvalidArgumentException $e) { http_response_code(400);die('invalid input'); }
catch (Throwable $e) { http_response_code(500);die('unable to save heartbeat'); }
if (isset($_GET['CONFIG_DASHBOARD_DALO_DEBUG']) && is_string($_GET['CONFIG_DASHBOARD_DALO_DEBUG']) && intval($_GET['CONFIG_DASHBOARD_DALO_DEBUG'])>0) {
    echo "Debug: \nheartbeat accepted\n";
}
echo 'success';
