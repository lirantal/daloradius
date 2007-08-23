<?php
	include (dirname(__FILE__).'/config_read.php');

	// we introduced support for php's database abstraction layer which simplifies database connections
	// to different technologies like mysql, oracle, postgresql, etc...
	// until everything is completely migrated we will leave these commented out
	
	include_once ('DB.php');	
	$dbConnectString = "mysql://".$configValues['CONFIG_DB_USER'].":".$configValues['CONFIG_DB_PASS']."@".$configValues['CONFIG_DB_HOST']."/".$configValues['CONFIG_DB_NAME'];
	$dbSocket = DB::connect($dbConnectString);

	// error handling support
    $dbSocket->setErrorHandling(PEAR_ERROR_PRINT, "Database query error: %s");
	
	/*
	mysql functions are obsolete now
	$mysql_conn = mysql_connect($configValues['CONFIG_DB_HOST'], $configValues['CONFIG_DB_USER'], $configValues['CONFIG_DB_PASS']) or die ('Error connecting to MySQL Server');
	mysql_select_db($configValues['CONFIG_DB_NAME']);
	*/
?>
