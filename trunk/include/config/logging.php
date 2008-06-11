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
	if (isset($log)) {
	        $msgNotice = $operator . " " . $log;
	        logMessage("NOTICE", $msgNotice, $configValues['CONFIG_LOG_FILE'], $_SERVER["SCRIPT_NAME"]);
	}
}



if ($configValues['CONFIG_LOG_QUERIES'] == "yes") {
	if (isset($logQuery)) {
	        $msgQuery = $operator . " " . $logQuery;
	        logMessage("QUERY", $msgQuery, $configValues['CONFIG_LOG_FILE'], $_SERVER["SCRIPT_NAME"]);
	}
}



if ($configValues['CONFIG_LOG_ACTIONS'] == "yes") {
	if (isset($logAction)) {
	        $msgAction = $operator . " " . $logAction;
	        logMessage("ACTION", $msgAction, $configValues['CONFIG_LOG_FILE'], $_SERVER["SCRIPT_NAME"]);
	}
}


/********************************************************************************
 * evaluating whether we need to debug SQL queries to the database as well.
 * $logDebugSQL is set for each $sql = "query statement..." on the actual page
 * in the following form:         $logDebugSQL += $sql . "\n";
 *
 ********************************************************************************/
if ($configValues['CONFIG_DEBUG_SQL'] == "yes") {
	if (isset($logDebugSQL)) {
	        $msgDebugSQL = "- SQL -" . " " . $logDebugSQL . " on page: ";
	        logMessage("DEBUG", $msgDebugSQL, $configValues['CONFIG_LOG_FILE'], $_SERVER["SCRIPT_NAME"]);
	}
}

/* the continuation of the CONFIG_DEBUG_SQL actually, this prints to the page
 * being viewed */
if ($configValues['CONFIG_DEBUG_SQL_ONPAGE'] == "yes") {
	if (isset($logDebugSQL)) {
			echo "<br/><br/>";
			echo "Debugging SQL Queries: <br/>";
			echo $logDebugSQL;
			echo "<br/><br/>";
		}
}



function logMessage($type, $msg, $logFile, $currPage) {
/*
* @param $type               The message type, for example, NOTICE, DEBUG, ERROR, ACTION, etc...
* @param $msg           	The message string which should be logged to the file
* @param $logFile           The full path for the filename to write logs to
* @param $currPage       The current page that we included from
* @return $table              The table name, either radcheck or radreply
*/

        $date = date('M d G:i:s');
        $msgString = $date . " " . $type . " " . $msg . " " . $currPage;

        $fp = fopen($logFile, "a");
        if ($fp) {
        fwrite($fp, $msgString  . "\n");
                fclose($fp);
        } else {

                echo "<font color='#FF0000'>error: could not open the file for writing:<b> $logFile </b><br/></font>";
                        echo "Check file permissions. The file should be writable by the webserver's user/group<br/>";
        }

}

?>
