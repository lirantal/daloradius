<?php

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
