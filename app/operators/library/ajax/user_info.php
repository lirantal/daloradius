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
 * Authors:    Liran Tal <liran@lirantal.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

require_once __DIR__ . '/json_info.php';
include('../checklogin.php');
$dalo_info_database_error_message = 'Unable to load user information.';
$db_error_handler = 'dalo_info_database_error';
$operator_perm_file = 'acct_username';
$operator_perm_deny_http_status = 403;
include('../check_operator_perm.php');

$value = dalo_info_parameter('username');
require_once '../../../common/includes/pdo_connection.php';
require_once '../../include/management/read_helpers_pdo.php';
require_once __DIR__ . '/../../include/management/pages_common.php';
try {
    $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
    $table = dalo_read_table($pdo, $configValues, 'CONFIG_DB_TBL_RADACCT');
    $stmt = $pdo->prepare("SELECT SUM(AcctInputOctets), SUM(AcctOutputOctets) FROM $table WHERE username=?");
    $stmt->execute(array($value));
    $row = $stmt->fetch(PDO::FETCH_NUM);
    $data = ['upload' => dalo_info_bytes($row[0] ?? null), 'download' => dalo_info_bytes($row[1] ?? null)];
    $pdo = null;
} catch (Throwable $error) {
    dalo_info_response(['error' => 'Unable to load user information.'], 500);
}
dalo_info_response($data);
