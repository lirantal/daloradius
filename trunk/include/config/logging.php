<?php
/*********************************************************************
* Name: logging.php
* Author: Liran tal <liran.tal@gmail.com>
*
* This file is used for controlling the logging actions
*
*********************************************************************/


function logMessageNotice($msg, $logFile) {
/*
* @param $msg           The message string which should be logged to the file
* @param $logFile               The full path for the filename to write logs to
* @return $table                The table name, either radcheck or radreply
*/

        $date = date('M d G:i:s');
        $msgString = $date . " NOTICE " . $msg;

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
