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
 *
 * Package:		Clean Stale Sessions
 * Description:	This script should be placed in cron to run scheduled every X minutes and clear
 *				all sessions greater than T, where T is the time specified in seconds for reply
 *				attribute Acct-Interim-Interval
 * Authors:     Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */


/* Configuration Section ************************************************ */

$configValues['CONFIG_DB_ENGINE'] = 'mysqli';
$configValues['CONFIG_DB_HOST'] = 'localhost';
$configValues['CONFIG_DB_PORT'] = '3306';
$configValues['CONFIG_DB_USER'] = 'radius';
$configValues['CONFIG_DB_PASS'] = 'radpass';
$configValues['CONFIG_DB_NAME'] = 'radius';
$configValues['CONFIG_DB_TBL_RADACCT'] = 'radacct';

//interval is specified in seconds
$configValues['INTERVAL'] = 60;
//grace time
$configValues['GRACE'] = 30;

/* ********************************************************************** */

require_once('DB.php');

function databaseConnect() {

	global $configValues;
	
    $mydbEngine = $configValues['CONFIG_DB_ENGINE'];
    $mydbUser = $configValues['CONFIG_DB_USER'];
    $mydbPass = $configValues['CONFIG_DB_PASS'];
    $mydbHost = $configValues['CONFIG_DB_HOST'];
    $mydbPort = $configValues['CONFIG_DB_PORT'];
    $mydbName = $configValues['CONFIG_DB_NAME'];

    $dbConnectString = $mydbEngine . "://".$mydbUser.":".$mydbPass."@".
               $mydbHost.":".$mydbPort."/".$mydbName;

    $dbSocket = DB::connect($dbConnectString);

    if (DB::isError ($dbSocket))
        die ("<b>Database connection error</b><br/>
            <b>Error Message</b>: " . $dbSocket->getMessage () . "<br/>" .
            "<b>Debug</b>: " . $dbSocket->getDebugInfo() . "<br/>");

	return $dbSocket;

}


function databaseDisconnect($dbSocket) {

    $dbSocket->disconnect();

}


/**
 *
 *
 * Description:	The logic of cleaning stale sessions
 *
 *	Since interim updates are enabled, then the accounting information should
 * 	be populated every U time. If a record exist with AcctStopTime
 * 	equal to 0 or NULL and time elapsed since AcctStartTime+SessionTime is > U then this
 * 	is a stale session. The query would then be:
 *
 * 		WHERE
 * 			((UNIX_TIMESTAMP(NOW()) - (UNIX_TIMESTAMP(AcctStartTime) + AcctSessionTime)) > U+GRACEFUL)
 * 		AND
 * 			(AcctStopTime = '0000-00-00 00:00:00' OR AcctStopTime IS NULL)
 *				
 *				
 */
function clearStaleSessions($dbSocket) {

	global $configValues;
	
	
	// get all entries which we are stale sessions
	/*
	$sql = " SELECT ".
				" username ".
			" FROM ".
				$configValues['CONFIG_DB_TBL_RADACCT'].
			" WHERE ".
				"((UNIX_TIMESTAMP(NOW()) - (UNIX_TIMESTAMP(".$configValues['CONFIG_DB_TBL_RADACCT'].".acctstarttime) + ".$configValues['CONFIG_DB_TBL_RADACCT'].".acctsessiontime)) > (".$configValues['INTERVAL']."+".
				$configValues['GRACE']."))".
			" AND ".
				" (AcctStopTime = '0000-00-00 00:00:00' OR AcctStopTime IS NULL) ";
	
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();
	*/
	
	$sql = " UPDATE ".
				$configValues['CONFIG_DB_TBL_RADACCT'].
			" SET ".
				$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime=NOW()".
				",".
				$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause='Stale-Session'".
			" WHERE ".
				"((UNIX_TIMESTAMP(NOW()) - (UNIX_TIMESTAMP(".$configValues['CONFIG_DB_TBL_RADACCT'].".acctstarttime) + ".$configValues['CONFIG_DB_TBL_RADACCT'].".acctsessiontime)) > (".$configValues['INTERVAL']."+".
				$configValues['GRACE']."))".
			" AND ".
				" (AcctStopTime IS NULL) ";

	$res = $dbSocket->query($sql);


}


$dbh = databaseConnect();
clearStaleSessions($dbh);
databaseDisconnect($dbh);


?>
