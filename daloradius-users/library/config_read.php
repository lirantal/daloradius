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
 * 		reads configuration file from daloradius.conf and appends it to the $configValues associated array
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */
 
$_configFile = dirname(__FILE__).'/daloradius.conf.php';
include($_configFile);

/*
****************************************************************************************************
* deprecated for handling the configuration variables as a PHP page for the sake of security
****************************************************************************************************
$_configCommentChar = "#";

$_configFp = fopen($_configFile, "r");
if ($_configFp) {
	while (!feof($_configFp)) {
		$_configLine = trim(fgets($_configFp));
		if ($_configLine && !ereg("^$_configCommentChar", $_configLine)) {
			$_configPieces = explode("=", $_configLine);
			$_configOption = trim($_configPieces[0]);
			$_configValue = trim($_configPieces[1]);
			$configValues[$_configOption] = $_configValue;
		}
	}
	fclose($_configFp);
} else {
	$failureMsg = "Could not open the file for reading:<b> $_configFile </b>
	<br/>Check file permissions. The file should be readable by the webserver's user/group";
}
****************************************************************************************************
*/

?>
