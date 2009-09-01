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
 * Description:
 * 		check operators permissions
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

    include 'library/opendb.php';
    $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE UserName='".
		$dbSocket->escapeSimple($operator)."'";
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
	
	$currFile = basename($_SERVER['SCRIPT_NAME']);
	$currFile = str_replace("-", "_", $currFile);
	$currFile = str_replace(".php", "", $currFile);

	// the importance of the following is not to be discarded.
	// the following tests if the page is defined and valid in the  include/management/operator_tables.php array 
	// and if it isn't it will force the page to not be displayed. meaning that all pages (for example newer pages) 
	// must be defined in that array otherwise they will not be accessible.
	isset($row[$currFile]) ? $test = $row[$currFile] : $test="no";
	
	if (!( (strcasecmp($test, "y") == 0) || (strcasecmp($test, "yes") == 0) || (strcasecmp($test, "on") == 0)   )) {
		header('Location: msg-error-permissions.php');
		exit;
	}
	
	include 'library/closedb.php';


?>
