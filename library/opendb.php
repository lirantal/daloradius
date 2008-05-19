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
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */
 
	include (dirname(__FILE__).'/config_read.php');

	// we introduced support for php's database abstraction layer which simplifies database connections
	// to different technologies like mysql, oracle, postgresql, etc...
	// until everything is completely migrated we will leave these commented out

	include_once ('DB.php');

	$dbConnectString = $configValues['CONFIG_DB_ENGINE'] . "://".$configValues['CONFIG_DB_USER'].":".$configValues['CONFIG_DB_PASS']."@".$configValues['CONFIG_DB_HOST']."/".$configValues['CONFIG_DB_NAME'];
	$dbSocket = DB::connect($dbConnectString);

	if (DB::isError ($dbSocket))
		die ("<b>Database connection error</b><br/>
			<b>Error Message</b>: " . $dbSocket->getMessage () . "<br/>" . 
			"<b>Debug</b>: " . $dbSocket->getDebugInfo() . "<br/>");

	
	include_once ('errorHandling.php');		// we declare the errorHandler() function in errorHandling.php

	$dbSocket->setErrorHandling(PEAR_ERROR_CALLBACK, 'errorHandler');	// setting errorHandler function for the dbSocket obj
?>
