<?php

$configFile = "library/daloradius.conf";
$commentChar = "#";

$fp = fopen($configFile, "r");
if ($fp)) {
			while (!feof($fp)) {
				$line = trim(fgets($fp));
				if ($line && !ereg("^$commentChar", $line)) {
					$pieces = explode("=", $line);
					$option = trim($pieces[0]);
					$value = trim($pieces[1]);
					$configValues[$option] = $value;
				}
			}
	} else {
            echo "<font color='#FF0000'>error: could not open the file for reading:<b> $configFile </b><br/></font>";
			echo "Check file permissions. The file should be readable by the webserver's user/group<br/>";
                        echo "
                                <script language='JavaScript'>
                                <!--
                                alert('could not open the file <b> $configFile </b> for reading!\\nCheck file permissions.');
                                -->
                                </script>
                                ";
	}
	
fclose($fp);

?>
