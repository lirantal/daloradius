<?php
$conn = mysql_connect($config_db, $config_db_user, $config_db_pass) or die ('Error connecting to mysql');
mysql_select_db($config_db_name);
?>
