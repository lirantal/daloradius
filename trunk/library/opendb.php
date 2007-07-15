<?php
	include (dirname(__FILE__).'/config_read.php');
	
	$mysql_conn = mysql_connect($configValues['CONFIG_DB_HOST'], $configValues['CONFIG_DB_USER'], $configValues['CONFIG_DB_PASS']) or die ('Error connecting to MySQL Server');
	mysql_select_db($configValues['CONFIG_DB_NAME']);
?>
