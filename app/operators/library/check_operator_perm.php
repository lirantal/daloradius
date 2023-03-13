<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
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
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/library/check_operator_perm.php') !== false) {
    header("Location: ../index.php");
    exit;
}

// we format the php script file in the following manner:
// we replace every instance of the - symbol with _ and we completely
// remove the .php extension
// this formatting is done to match the exact entry for the page as it
// appears in the operators_acl table
$file = str_replace("-", "_", basename($_SERVER['SCRIPT_NAME'], ".php"));

include('../common/includes/db_open.php');

$sql = sprintf("SELECT access FROM %s WHERE operator_id=%d AND file='%s'",
               $configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'], $_SESSION['operator_id'], $file);
$access = intval($dbSocket->getOne($sql)) === 1;

include('../common/includes/db_close.php');

// we finally check if the access to the requested page could be granted
if (!$access) {
    header('Location: home-error.php');
    exit;
}

?>
