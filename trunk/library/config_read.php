<?php

$_configFile = "library/daloradius.conf";
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
        echo "<font color='#FF0000'>error: could not open the file for reading:<b> $_configFile </b><br/></font>";
		echo "Check file permissions. The file should be readable by the webserver's user/group<br/>";
        echo "
            <script language='JavaScript'>
            <!--
            alert('could not open the file $_configFile for reading!\\nCheck file permissions.');
            -->
            </script>
            ";
}

?>
