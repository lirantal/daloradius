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
include('../../../common/includes/db_open.php');
// The default PEAR callback prints HTML, which would corrupt the JSON response.
$dbSocket->setErrorHandling(PEAR_ERROR_RETURN);
include_once('../../include/management/pages_common.php');

$sql = sprintf("SELECT SUM(AcctInputOctets) AS Upload, SUM(AcctOutputOctets) AS Download FROM %s WHERE username='%s'",
               $configValues['CONFIG_DB_TBL_RADACCT'], $dbSocket->escapeSimple($value));
$res = $dbSocket->query($sql);
if (DB::isError($res)) {
    dalo_info_response(['error' => 'Unable to load user information.'], 500);
}
$row = $res->fetchRow();
if (DB::isError($row)) {
    dalo_info_response(['error' => 'Unable to load user information.'], 500);
}
$data = ['upload' => dalo_info_bytes($row[0] ?? null), 'download' => dalo_info_bytes($row[1] ?? null)];

include('../../../common/includes/db_close.php');
dalo_info_response($data);
