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
include_once dirname(__DIR__, 3) . '/common/includes/config_read.php';
$operator_perm_file = 'mng_rad_attributes_list';
$operator_perm_deny_http_status = 403;
include('../check_operator_perm.php');
require_once dirname(__DIR__) . '/dictionary_pages_pdo.php';
$value = dalo_info_parameter('attribute');
try {
    dalo_dictionary_text($value, 64, true);
    $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
    $table = dalo_dictionary_table($configValues);
    $stmt = $pdo->prepare("SELECT RecommendedTooltip FROM $table WHERE Attribute=:attribute LIMIT 1");
    $stmt->execute(array(':attribute'=>$value));
    $tooltip = trim((string)$stmt->fetchColumn());
    dalo_info_response(['description' => $tooltip === '' ? '(n/a)' : $tooltip]);
} catch (InvalidArgumentException $e) {
    dalo_info_response(['error' => 'Missing or invalid parameter.'], 400);
} catch (Throwable $e) {
    dalo_info_response(['error' => 'Unable to load attribute information.'], 500);
}
