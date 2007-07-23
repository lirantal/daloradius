<?php
/*********************************************************************
* Name: logging.php
* Author: Liran tal <liran.tal@gmail.com>
*
* This file is used for controlling the logging actions
*
*********************************************************************/


/*
* It is important to understand that when logging.php is included in
* a page AFTER an include for config_read.php it gains access to all
* of the variables it's scope including the $configValues[....] because
* it was included just before it.
*
* But it should be noticed that these variables are only accessible
* in the scope of the general or main block of php code and are
* not accessible from functions, so we can't just use $configValues[...]
* variables from within logMessageNotice() or any other function
* and so we must use them here as references.
*
* The relevant variables are:
*
* $operator
* $_SERVER["SCRIPT_NAME"]
* $configValues['CONFIG_LOG_FILE']
*
*/

if ($configValues['CONFIG_LOG_PAGES'] == "yes") {
        $msg = $operator . " " . $log;
        logMessage("NOTICE", $msg, $configValues['CONFIG_LOG_FILE']);
}


function logMessage($type, $msg, $logFile) {
/*
* @param $msg           The message string which should be logged to the file
* @param $logFile               The full path for the filename to write logs to
* @return $table                The table name, either radcheck or radreply
*/

        $date = date('M d G:i:s');
        $msgString = $date . " " . $type . " " . $msg;

        $fp = fopen($logFile, "a");
        if ($fp) {
        fwrite($fp, $msgString  . "\n");
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