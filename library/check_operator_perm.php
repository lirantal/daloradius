<?php
/*
 *******************************************************************************
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
 *******************************************************************************
 * Description:
 * 		check operators permissions
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *******************************************************************************
 */

// we format the php script file in the following manner:
// we replace every instance of the - symbol with _ and we completely
// remove the .php extension
// this formatting is done to match the exact entry for the page as it
// appears in the operators_acl table
$currFile = str_replace("-", "_", basename($_SERVER['SCRIPT_NAME'], ".php"));

include('library/opendb.php');

$sqlFormat = "select access from %s where operator_id=%d and file='%s'";
$sql = sprintf($sqlFormat,
    $configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'],
    $_SESSION['operator_id'],
    $currFile);

$res = $dbSocket->query($sql);
$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
$access = (bool)$row['access'];

include('library/closedb.php');

// the following checks if the access field is set to 1 or 0,
// 1 is access granted to the page
// 0 means no access, in which case we forward to an error page
if (!$access) {
    header('Location: msg-error-permissions.php');
    exit;
}

?>
