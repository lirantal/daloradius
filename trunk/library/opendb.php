<?php
	include (dirname(__FILE__).'/config_read.php');

	// we introduced support for php's database abstraction layer which simplifies database connections
	// to different technologies like mysql, oracle, postgresql, etc...
	// until everything is completely migrated we will leave these commented out

	include_once ('DB.php');

	$dbConnectString = $configValues['CONFIG_DB_ENGINE'] . "://".$configValues['CONFIG_DB_USER'].":".$configValues['CONFIG_DB_PASS']."@".$configValues['CONFIG_DB_HOST']."/".$configValues['CONFIG_DB_NAME'];
	$dbSocket = DB::connect($dbConnectString);

	if (DB::isError ($dbSocket))
		die ("<b>Datanase connection error</b><br/>
			<b>Error Message</b>: " . $dbSocket->getMessage () . "<br/>" . 
			"<b>Debug</b>: " . $dbSocket->getDebugInfo() . "<br/>");

	
	include_once ('errorHandling.php');		// we declare the errorHandler() function in errorHandling.php

	$dbSocket->setErrorHandling(PEAR_ERROR_CALLBACK, 'errorHandler');	// setting errorHandler function for the dbSocket obj
?>
