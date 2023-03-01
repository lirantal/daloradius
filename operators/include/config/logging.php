<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 * Description:	   This file is used for controlling the logging actions
 * 
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/config/logging.php') !== false) {
    header("Location: ../../index.php");
    exit;
}

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

/*
 * @param $type        The message type, for example, NOTICE, DEBUG, ERROR, ACTION, etc...
 * @param $msg         The message string which should be logged to the file
 * @param $logFile     The full path for the filename to write logs to
 * @param $currPage    The current page that we included from
 * @return $table      The table name, either radcheck or radreply
 */
function logMessage($type, $msg, $logFile, $currPage) {
    $date = date('M d G:i:s');
    $msgString = $date . " " . $type . " " . $msg . " " . $currPage;

    $fp = fopen($logFile, "a");
    if ($fp) {
        fwrite($fp, $msgString  . "\n");
        fclose($fp);
        return;
    }

    echo "<div>"
       . '<span style="color: red">error: could not open the file for writing: <strong>'
       . $logFile . "</strong></span><br>"
       . "Check file permissions. The file should be writable by the webserver's user/group"
       . "</div>";
}

$logger_work = array();

if ($configValues['CONFIG_LOG_PAGES'] == "yes" && isset($log) && !empty($log)) {
    $logger_work['NOTICE'] = "$operator $log";
}

if ($configValues['CONFIG_LOG_QUERIES'] == "yes" && isset($logQuery) && !empty($logQuery)) {
    $logger_work['QUERIES'] = "$operator $logQuery";
}


if ($configValues['CONFIG_LOG_ACTIONS'] == "yes" && isset($logAction) && !empty($logAction)) {
    $logger_work['ACTIONS'] = "$operator $logAction";
}

/*
 ********************************************************************************
 * evaluating whether we need to debug SQL queries to the database as well.
 * $logDebugSQL is set for each $sql = "query statement..." on the actual page
 * in the following form:         $logDebugSQL += $sql . "\n";
 *
 ********************************************************************************
 */
if ($configValues['CONFIG_DEBUG_SQL'] == "yes" && isset($logDebugSQL) && !empty($logDebugSQL)) {
    $logger_work['DEBUG'] = "- SQL -" . " " . $logDebugSQL . " on page: ";
}

foreach ($logger_work as $type => $message) {
    logMessage($type, $message, $configValues['CONFIG_LOG_FILE'], $_SERVER["SCRIPT_NAME"]);
}

/* the continuation of the CONFIG_DEBUG_SQL actually, this prints to the page
 * being viewed */
if ($configValues['CONFIG_DEBUG_SQL_ONPAGE'] == "yes"  && isset($logDebugSQL) && !empty($logDebugSQL)) {
	echo "<br><br>"
       . "Debugging SQL Queries: <br>"
       . "<pre>$logDebugSQL</pre>"
       . "<br><br>";
}

?>
