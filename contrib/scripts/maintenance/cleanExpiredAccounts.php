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
 * Package:		Clean Expired Accounts
 * Description:	This script should be placed in cron to run scheduled every X minutes and clear
 *				expired accounts from the database. It cleans Accumulative and Time-To-Finish accounts
 *				and requires that all of these accounts are associated with the corrosponding billing plan
 * Authors:     Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */


/* Configuration Section ************************************************ */

$configValues['CONFIG_DB_ENGINE'] = 'mysql';
$configValues['CONFIG_DB_HOST'] = 'localhost';
$configValues['CONFIG_DB_PORT'] = '3306';
$configValues['CONFIG_DB_USER'] = 'root';
$configValues['CONFIG_DB_PASS'] = 'dalodevPOLQWS1029';
$configValues['CONFIG_DB_NAME'] = 'radius_bluechip';
$configValues['CONFIG_DB_TBL_RADACCT'] = 'radacct';
$configValues['CONFIG_DB_TBL_RADCHECK'] = 'radcheck';
$configValues['CONFIG_DB_TBL_RADREPLY'] = 'radreply';
$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'] = 'userbillinfo';
$configValues['CONFIG_DB_TBL_DALOUSERINFO'] = 'userinfo';
$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'] = 'billing_plans';

//interval is specified in seconds
$configValues['INTERVAL'] = 60;
$configValues['GRACE'] = 30;


// Expire Accumulative definitions
// defines a threshold of 70% - if accumulative accounts used up 
// more than 70% of their alotted time then they are considered expired 
$configValues['TYPE_ACCUMULATIVE_THRESHOLD'] = 0.7;


// Expire Time To Finish definitions


// Expire Due Login definitions
$configValues['TYPE_DUELOGIN_DAYS_OVERDUE'] = 90;


/* ********************************************************************** */

require_once('DB.php');

function databaseConnect() {

	global $configValues;
	
    $mydbEngine = $configValues['CONFIG_DB_ENGINE'];
    $mydbUser = $configValues['CONFIG_DB_USER'];
    $mydbPass = $configValues['CONFIG_DB_PASS'];
    $mydbHost = $configValues['CONFIG_DB_HOST'];
    $mydbPort = $configValues['CONFIG_DB_Port'];
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
 * Description:	The logic of cleaning stale sessions
 *
 *	Since interim updates are enabled, then the accounting information should			
 *				
 */
function clearExpiredAccumulative($dbSocket) {

	global $configValues;
	
	/* query to get all accumulative type accounts which expired
	 * where-as expired means they have used up a specific percentage of their alotted time
	 *
		SELECT
		    DISTINCT(radacct.username) as Username,
		    SUM(radacct.AcctSessionTime) as UserTotalTime,
		    billing_plans.planTimeBank as UserAllowedTime
		FROM
		    radacct,
		    billing_plans,
		    userbillinfo
		WHERE
		    userbillinfo.planName = billing_plans.planName
		    AND
		    billing_plans.planTimeType = "Accumulative"
		
		    AND
		    radacct.username = userbillinfo.username
		GROUP BY
		   radacct.username, billing_plans.planTimeBank
		HAVING (UserTotalTime/UserAllowedTime >= 0.9)
	 *
	 */
	
	$sql = '
		# Create the temporary table
		CREATE TEMPORARY TABLE tmptable_1
		# Run the select query to fill the table with the result set
		SELECT
		    DISTINCT('.$configValues['CONFIG_DB_TBL_RADACCT'].'.username) as Username,
		    SUM('.$configValues['CONFIG_DB_TBL_RADACCT'].'.AcctSessionTime) as UserTotalTime,
		    '.$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].'.planTimeBank as UserAllowedTime
		FROM
		    '.$configValues['CONFIG_DB_TBL_RADACCT'].',
		    '.$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].',
		    '.$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].' 
		WHERE
		    '.$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].'.planName = '.$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].'.planName
		    AND
		    '.$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].'.planTimeType = "Accumulative"
		
		    AND
		    '.$configValues['CONFIG_DB_TBL_RADACCT'].'.username = '.$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].'.username
		GROUP BY
		   '.$configValues['CONFIG_DB_TBL_RADACCT'].'.username, '.$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].'.planTimeBank
		HAVING (UserTotalTime/UserAllowedTime >= '.$configValues['TYPE_ACCUMULATIVE_THRESHOLD'].')
		;
		';
		
	$res = $dbSocket->query($sql);
	
	dbDeleteRecords($dbSocket, 'tmptable_1');	
}





/**
 *
 *				
 */
function clearExpiredTimeToFinish($dbSocket) {

	global $configValues;
	
	/* query to get all time to finish type accounts which expired
	 * where-as expired means they have used up at least an X percentage 
	 * of their alotted time
		SELECT
		    DISTINCT(radacct.username) as Username,
		    radacct.acctstarttime,
		    billing_plans.planname,
		    billing_plans.plantimetype
		
		FROM
		    radacct,
		    billing_plans,
		    userbillinfo
		WHERE
		    userbillinfo.planName = billing_plans.planName
		    AND
		    billing_plans.planTimeType = "Time-To-Finish"
		
		    AND
		    radacct.username = userbillinfo.username
		GROUP BY
		   radacct.username, billing_plans.planTimeBank
		HAVING
		   (
		       (IFNULL(UNIX_TIMESTAMP(AcctStartTime),0)) / UNIX_TIMESTAMP() > 0.9
		   )
		ORDER BY
		   radacct.acctstarttime ASC
	 * 
	 */
	
	$sql = '
		# Create the temporary table
		CREATE TEMPORARY TABLE tmptable_2
		# Run the select query to fill the table with the result set
		
		SELECT
		    DISTINCT('.$configValues['CONFIG_DB_TBL_RADACCT'].'.username) as Username,
		    '.$configValues['CONFIG_DB_TBL_RADACCT'].'.acctstarttime,
		    '.$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].'.planname,
		    '.$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].'.plantimetype
		
		FROM
		    '.$configValues['CONFIG_DB_TBL_RADACCT'].',
		    '.$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].',
		    '.$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].'
		WHERE
		    '.$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].'.planName = '.$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].'.planName
		    AND
		    '.$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].'.planTimeType = "Time-To-Finish"
		
		    AND
		    '.$configValues['CONFIG_DB_TBL_RADACCT'].'.username = '.$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].'.username
		GROUP BY
		   '.$configValues['CONFIG_DB_TBL_RADACCT'].'.username, '.$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].'.planTimeBank
		HAVING
		   (
		       (IFNULL(UNIX_TIMESTAMP(AcctStartTime),0)) / UNIX_TIMESTAMP() > 0.9
		   )
		ORDER BY
		   '.$configValues['CONFIG_DB_TBL_RADACCT'].'.acctstarttime ASC
		;
		';
		
	$res = $dbSocket->query($sql);
	
	dbDeleteRecords($dbSocket, 'tmptable_2');	

}







/**
 *
 * Clean all users in the database which did not login for the past X period
 * (setup using the config variable at the top of the script) but at least logged-in
 * one time to the system.
 *
 */
function clearExpiredDueLogin($dbSocket) {

	global $configValues;
	
	/* This query will match all users which didn't login for the past month (30 days):
	 * 
	 *
	
		SELECT
		    DISTINCT(radacct.username) as Username,
		    AcctStartTime
		FROM
		    radacct
		WHERE
		    (
		        (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(AcctStartTime) >= 2592000)
		        
		    )
		GROUP BY
		    radacct.username 
		ORDER BY
		    radacct.radacctid DESC
	 * 
	 */
	
	$sql = '
		CREATE TEMPORARY TABLE tmptable_3
		SELECT
		    DISTINCT('.$configValues['CONFIG_DB_TBL_RADACCT'].'.username) as Username,
		    AcctStartTime
		FROM
		    '.$configValues['CONFIG_DB_TBL_RADACCT'].'
		WHERE
		    (
		        (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(AcctStartTime) >= '.(86400*$configValues['TYPE_DUELOGIN_DAYS_OVERDUE']).')
		        
		    )
		GROUP BY
		    '.$configValues['CONFIG_DB_TBL_RADACCT'].'.username 
		ORDER BY
		    '.$configValues['CONFIG_DB_TBL_RADACCT'].'.radacctid DESC
		;';

	$res = $dbSocket->query($sql);
	
	dbDeleteRecords($dbSocket, 'tmptable_3');

}


/**
 * 
 * This function relies on an active and open database connection and given a $dbSocket
 * handle it performs a delete for all accounts
 * 
 * @param string	the temporary table name
 * @param sockethandler	the socket handler for the database connection
 * @return true
 */
function dbDeleteRecords($dbSocket, $tmpTableName) {
	
	global $configValues;
		
	$sql = '
		# Delete all the records from the related tables
		DELETE '.$configValues['CONFIG_DB_TBL_RADACCT'].' FROM '.$configValues['CONFIG_DB_TBL_RADACCT'].', '.$tmpTableName.'
			WHERE '.$configValues['CONFIG_DB_TBL_RADACCT'].'.username = '.$tmpTableName.'.username; 
		';
	$res = $dbSocket->query($sql);
	
	$sql = '
		DELETE '.$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].' FROM '.$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].', '.$tmpTableName.'
			WHERE '.$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].'.username = '.$tmpTableName.'.username;
			';
	$res = $dbSocket->query($sql);
	
	$sql = '
		DELETE '.$configValues['CONFIG_DB_TBL_DALOUSERINFO'].' FROM '.$configValues['CONFIG_DB_TBL_DALOUSERINFO'].', '.$tmpTableName.'
			WHERE '.$configValues['CONFIG_DB_TBL_DALOUSERINFO'].'.username = '.$tmpTableName.'.username;
		';
	$res = $dbSocket->query($sql);
	
	$sql = '
		DELETE '.$configValues['CONFIG_DB_TBL_RADCHECK'].' FROM '.$configValues['CONFIG_DB_TBL_RADCHECK'].', '.$tmpTableName.'
			WHERE '.$configValues['CONFIG_DB_TBL_RADCHECK'].'.username = '.$tmpTableName.'.username;
			';
	$res = $dbSocket->query($sql);
	
	$sql = '
		DELETE '.$configValues['CONFIG_DB_TBL_RADREPLY'].' FROM '.$configValues['CONFIG_DB_TBL_RADREPLY'].', '.$tmpTableName.'
			WHERE '.$configValues['CONFIG_DB_TBL_RADREPLY'].'.username = '.$tmpTableName.'.username;
		';
	$res = $dbSocket->query($sql);
	
	
    if (DB::isError ($res)) 
        die ("<b>Database connection error</b><br/>
            <b>Error Message</b>: " . $res->getMessage () . "<br/>" .
            "<b>Debug</b>: " . $res->getDebugInfo() . "<br/>");
	
	
	return true;
}


$dbh = databaseConnect();

// perform cleanup
clearExpiredDueLogin($dbh);
clearExpiredTimeToFinish($dbh);
clearExpiredAccumulative($dbh);

databaseDisconnect($dbh);


?>
