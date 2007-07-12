<?php

$configFile = "library/daloradius.conf";
$commentChar = "#";

$fp = fopen($configFile, "w");
if ($fp) {
	foreach ($configValues as $option => $elem) {
        fwrite($fp, $option . " = " . $configValues[$option] . "\n");
	}
	fclose($fp);
} else {
        echo "<font color='#FF0000'>error: could not open the file for writing:<b> $configFile </b><br/></font>";
		echo "Check file permissions. The file should be writable by the webserver's user/group<br/>";
        echo "
            <script language='JavaScript'>
            <!--
            alert('could not open the file $configFile for writing!\\nCheck file permissions.');
            -->
            </script>
		";
}

?>
