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
 * Package:		   Clean Expired Accounts
 *
 * Description:    This script should be placed in cron to run scheduled every X minutes and clear
 *				   expired accounts from the database. It cleans Accumulative and Time-To-Finish accounts
 *				   and requires that all of these accounts are associated with the corrosponding billing plan
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */


/* Configuration Section ************************************************ */

$configPath = dirname(__FILE__) . '/../../../library/config_read.php';
include($configPath);

//interval is specified in seconds
$configValues['INTERVAL'] = 60;
$configValues['GRACE'] = 30;

// Expire Accumulative definitions
// defines a threshold of 70% - if accumulative accounts used up
// more than 70% of their alotted time then they are considered expired
$configValues['TYPE_ACCUMULATIVE_THRESHOLD'] = 0.7;

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
    $mydbPort = $configValues['CONFIG_DB_PORT'];
    $mydbName = $configValues['CONFIG_DB_NAME'];

    $dbConnectString = sprintf("%s://%s:%s@%s:%s/%s", $mydbEngine, $mydbUser, $mydbPass, $mydbHost, $mydbPort, $mydbName);

    $dbSocket = DB::connect($dbConnectString);

    if (DB::isError($dbSocket)) {
        die(sprintf("DB connection error.\nMessage: %s\n", $dbSocket->getMessage()));
    }

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

    $tmptable = 'tmptable_1';
    $sql = sprintf( "# Create the temporary table
                     CREATE TEMPORARY TABLE %s

                     # Run the select query to fill the table with the result set
                     SELECT DISTINCT(ra.username) AS Username, SUM(ra.AcctSessionTime) AS UserTotalTime,
                            dbp.planTimeBank AS UserAllowedTime
                       FROM %s AS ra, %s AS dbp, %s AS ubi
                      WHERE ubi.planName=dbp.planName
                        AND dbp.planTimeType='Accumulative'
                        AND ra.username=ubi.username
                      GROUP BY ra.username, dbp.planTimeBank
                      HAVING (UserTotalTime/UserAllowedTime >= %f)", $tmptable, $configValues['CONFIG_DB_TBL_RADACCT'],
                                                                     $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                                                                     $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                                                     $configValues['TYPE_ACCUMULATIVE_THRESHOLD']);
	$res = $dbSocket->query($sql);
	dbDeleteRecords($dbSocket, $tmptable);
}


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
function clearExpiredTimeToFinish($dbSocket) {
    global $configValues;

    $tmptable = 'tmptable_2';
    $sql = sprintf( "# Create the temporary table
                     CREATE TEMPORARY TABLE %s

                     # Run the select query to fill the table with the result set
                     SELECT DISTINCT(ra.username) AS Username, ra.acctstarttime, dbp.planname, dbp.plantimetype
                       FROM %s AS ra, %s AS dbp, %s AS ubi
                      WHERE ubi.planName=dbp.planName
                        AND dbp.planTimeType='Time-To-Finish'
                        AND ra.username=ubi.username
                      GROUP BY ra.username, dbp.planTimeBank
                     HAVING ( (IFNULL(UNIX_TIMESTAMP(AcctStartTime),0)) / UNIX_TIMESTAMP() > 0.9 )
                      ORDER BY ra.acctstarttime ASC", $tmptable, $configValues['CONFIG_DB_TBL_RADACCT'],
                                                      $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                                                      $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO']);
	$res = $dbSocket->query($sql);
	dbDeleteRecords($dbSocket, $tmptable);
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

    $tmptable = 'tmptable_3';
    $sql = sprintf("# Create the temporary table
                    CREATE TEMPORARY TABLE %s

                    # Run the select query to fill the table with the result set
                    SELECT DISTINCT(ra.username) AS Username, AcctStartTime
                      FROM %s AS ra
                     WHERE UNIX_TIMESTAMP() - UNIX_TIMESTAMP(AcctStartTime) >= (86400*%d)
                     GROUP BY ra.username
                     ORDER BY ra.radacctid DESC", $tmptable, $configValues['CONFIG_DB_TBL_RADACCT'],
                                                  $configValues['TYPE_DUELOGIN_DAYS_OVERDUE']);
	$res = $dbSocket->query($sql);

	dbDeleteRecords($dbSocket, $tmptable);
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

    $tables = array(
                        $configValues['CONFIG_DB_TBL_RADACCT'],
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                        $configValues['CONFIG_DB_TBL_DALOUSERINFO'],
                        $configValues['CONFIG_DB_TBL_RADCHECK'],
                        $configValues['CONFIG_DB_TBL_RADREPLY']
                   );

    // Delete all the records from the related tables
    foreach ($tables as $table) {
        $sql = sprintf("DELETE t_1 FROM %s AS t_1, %s AS t_tmp WHERE t_1.username=t_tmp.username", $table, $tmpTableName);
        $res = $dbSocket->query($sql);

        if (DB::isError($res)) {
            $message = sprintf("DB error.\nMessage: %s\nDebug: %s\n", $res->getMessage(), $res->getDebugInfo());
            die($message);
        }
    }

    return true;
}


// connect to db
$dbConn = databaseConnect();

// perform cleanup
clearExpiredDueLogin($dbConn);
clearExpiredTimeToFinish($dbConn);
clearExpiredAccumulative($dbConn);

// disconnect from db
databaseDisconnect($dbConn);
