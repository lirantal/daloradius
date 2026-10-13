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
$dalo_info_database_error_message = 'Unable to load hotspot information.';
$db_error_handler = 'dalo_info_database_error';
$operator_perm_file = 'acct_hotspot_accounting';
$operator_perm_deny_http_status = 403;
include('../check_operator_perm.php');

require_once dirname(__DIR__) . '/hotspot_pages_pdo.php';
if ($_SERVER['REQUEST_METHOD']!=='GET') { header('Allow: GET');dalo_info_response(['error'=>'Method not allowed.'],405); }
try { $value=dalo_hotspot_name($_GET['hotspot'] ?? null); }
catch (Throwable $e) { dalo_info_response(['error'=>'Missing or invalid parameter.'],400); }
include_once('../../include/management/pages_common.php');
try {
    $pdo=dalo_pdo_connect($configValues,$_SESSION['location_name'] ?? 'default');
    $acct=dalo_hotspot_table($configValues,'CONFIG_DB_TBL_RADACCT');$hs=dalo_hotspot_table($configValues);
    $row=dalo_hotspot_query($pdo,"SELECT COUNT(ra.radacctid),SUM(ra.AcctInputOctets),SUM(ra.AcctOutputOctets)
                              FROM $acct AS ra JOIN $hs AS hs ON ra.calledstationid=hs.mac WHERE hs.name=? GROUP BY hs.name",array($value))->fetch(PDO::FETCH_NUM);
    $data=['upload'=>dalo_info_bytes($row[1] ?? null),'download'=>dalo_info_bytes($row[2] ?? null),'hits'=>empty($row[0]) ? '(n/a)' : intval($row[0])];
} catch (Throwable $e) { dalo_info_response(['error'=>'Unable to load hotspot information.'],500); }
dalo_info_response($data);
