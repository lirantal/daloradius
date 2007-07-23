<?php
/*********************************************************************
* Name: logging.php
* Author: Liran tal <liran.tal@gmail.com>
* 
* This file is used for controlling the logging actions
* 
*********************************************************************/


function logMessage($msg) {
/* 
* @param $msg		The message string which should be logged to the file
* @return $table		The table name, either radcheck or radreply
*/

	$logFile = $configValues['CONFIG_LOG_FILE'];
	$fp = fopen($logFile, "w");
	if ($fp) {
        fwrite($fp, $msg . "\n");
		fclose($fp);
	} else {
	        echo "<font color='#FF0000'>error: could not open the file for writing:<b> $logFile </b><br/></font>";
			echo "Check file permissions. The file should be writable by the webserver's user/group<br/>";
	        echo "
	            <script language='JavaScript'>
	            <!--
	            alert('could not open the file $logFile for writing!\\nCheck file permissions.');
	            -->
	            </script>
			";
	}	
	
}



?>
