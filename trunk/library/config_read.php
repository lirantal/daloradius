<?php

$_configFile = dirname(__FILE__).'/daloradius.conf';
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
	$actionStatus = "failure";
	$actionMsg = "could not open the file for reading:<b> $_configFile </b>
	<br/>Check file permissions. The file should be readable by the webserver's user/group";
}

?>
