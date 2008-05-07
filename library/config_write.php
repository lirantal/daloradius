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
 * 		writes configuration information from the $configValues array to daloradius.conf
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

$configFile = dirname(__FILE__).'/daloradius.conf';
$commentChar = "#";

$fp = fopen($configFile, "w");
if ($fp) {
	foreach ($configValues as $_configOption => $_configElem) {
        fwrite($fp, $_configOption . " = " . $configValues[$_configOption] . "\n");
	}
	fclose($fp);
	$actionStatus = "success";
	$actionMsg = "Updated database settings for configuration file";
} else {
	$actionStatus = "failure";
	$actionMsg = "could not open the file for writing:<b> $configFile </b>
	<br/> Check file permissions. The file should be writable by the webserver's user/group";
}

?>
