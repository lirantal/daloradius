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
 * Description:
 *              returns user Connection Status, Subscription Analysis, Account Status etc...
 *		(concept borrowed from Joachim's capture pages)
 *
 * Authors:     Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

/*
 *********************************************************************************************************
 * userSubscriptionAnalysis
 * $username            username to provide information of
 * $drawTable           if set to 1 (enabled) a toggled on/off table will be drawn
 * 
 * provides information for user's subscription (packages or session limits) such as Max-All-Session,
 * Max-Monthly-Session, Max-Daily-Session, Expiration attribute, etc...
 *********************************************************************************************************
 */
function userSubscriptionAnalysis($username, $drawTable) {

	include_once('include/management/pages_common.php');
	include 'library/opendb.php';

	$username = $dbSocket->escapeSimple($username);			// sanitize variable for sql statement
	

	/*
	 *********************************************************************************************************
	 * Global (Max-All-Session) Limit calculations
	 *********************************************************************************************************/
        $sql = "SELECT SUM(AcctSessionTime) AS 'SUMSession', SUM(AcctOutputOctets) AS 'SUMDownload', SUM(AcctInputOctets) AS 'SUMUpload', ".
		" COUNT(DISTINCT AcctSessionID) AS 'Logins' ".
		" FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." WHERE UserName='$username' AND acctstoptime>0";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	(isset($row['SUMSession'])) ? $userSumMaxAllSession = time2str($row['SUMSession']) : $userSumMaxAllSession = "unavailable";
	(isset($row['SUMDownload'])) ? $userSumDownload = toxbyte($row['SUMDownload']) : $userSumDownload = "unavailable";
	(isset($row['SUMUpload'])) ? $userSumUpload = toxbyte($row['SUMUpload']) : $userSumUpload = "unavailable";
	if ( (isset($row['SUMUpload'])) && (isset($row['SUMDownload'])) )
		$userSumAllTraffic = toxbyte($row['SUMUpload']+$row['SUMDownload']);
	else
		$userSumAllTraffic = "unavailable";
	(isset($row['Logins'])) ? $userAllLogins = $row['Logins'] : $userAllLogins = "unavailable";



	/*
	 *********************************************************************************************************
	 * Monthly Limit calculations
	 *********************************************************************************************************/
	$currMonth = date("Y-m-01");
	$nextMonth = date("Y-m-01", mktime(0, 0, 0, date("m")+ 1, date("d"), date("Y")));

	$sql = "SELECT SUM(AcctSessionTime) AS 'SUMSession', SUM(AcctOutputOctets) AS 'SUMDownload', SUM(AcctInputOctets) AS 'SUMUpload', ".
		" COUNT(DISTINCT AcctSessionID) AS 'Logins' ".
		" FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].
		" WHERE AcctStartTime<'$nextMonth' AND AcctStartTime>='$currMonth' AND UserName='$username' AND acctstoptime>0";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	(isset($row['SUMSession'])) ? $userSumMaxMonthlySession = time2str($row['SUMSession']) : $userSumMaxMonthlySession = "unavailable";
	(isset($row['SUMDownload'])) ? $userSumMonthlyDownload = toxbyte($row['SUMDownload']) : $userSumMonthlyDownload = "unavailable";
	(isset($row['SUMUpload'])) ? $userSumMonthlyUpload = toxbyte($row['SUMUpload']) : $userSumMonthlyUpload = "unavailable";
	if ( (isset($row['SUMUpload'])) && (isset($row['SUMDownload'])) ) 
		$userSumMonthlyTraffic = toxbyte($row['SUMUpload']+$row['SUMDownload']);
	else
		$userSumMonthlyTraffic = "unavailable";
	(isset($row['Logins'])) ? $userMonthlyLogins = $row['Logins'] : $userMonthlyLogins = "unavailable";


	/*
	 *********************************************************************************************************
	 * Weekly Limit calculations
	 *********************************************************************************************************/
	$currDay = date("Y-m-d", strtotime(date("Y").'W'.date('W')));
	$nextDay = date("Y-m-d", strtotime(date("Y").'W'.date('W')."7"));
        $sql = "SELECT SUM(AcctSessionTime) AS 'SUMSession', SUM(AcctOutputOctets) AS 'SUMDownload', SUM(AcctInputOctets) AS 'SUMUpload', ".
		" COUNT(DISTINCT AcctSessionID) AS 'Logins' ".
		" FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].
		" WHERE AcctStartTime<'$nextDay' AND AcctStartTime>='$currDay' AND UserName='$username' AND acctstoptime>0";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	(isset($row['SUMSession'])) ? $userSumMaxWeeklySession = time2str($row['SUMSession']) : $userSumMaxWeeklySession = "unavailable";
	(isset($row['SUMDownload'])) ? $userSumWeeklyDownload = toxbyte($row['SUMDownload']) : $userSumWeeklyDownload = "unavailable";
	(isset($row['SUMUpload'])) ? $userSumWeeklyUpload = toxbyte($row['SUMUpload']) : $userSumWeeklyUpload = "unavailable";
	if ( (isset($row['SUMUpload'])) && (isset($row['SUMDownload'])) )
		$userSumWeeklyTraffic = toxbyte($row['SUMUpload']+$row['SUMDownload']);
	else
		$userSumWeeklyTraffic = "unavailable";
	(isset($row['Logins'])) ? $userWeeklyLogins = $row['Logins'] : $userWeeklyLogins = "unavailable";






	/*
	 *********************************************************************************************************
	 * Daily Limit calculations
	 *********************************************************************************************************/
	$currDay = date("Y-m-d");
	$nextDay = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+1, date("Y")));
        $sql = "SELECT SUM(AcctSessionTime) AS 'SUMSession', SUM(AcctOutputOctets) AS 'SUMDownload', SUM(AcctInputOctets) AS 'SUMUpload', ".
		" COUNT(DISTINCT AcctSessionID) AS 'Logins' ".
		" FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].
		" WHERE AcctStartTime<'$nextDay' AND AcctStartTime>='$currDay' AND UserName='$username' AND acctstoptime>0";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	(isset($row['SUMSession'])) ? $userSumMaxDailySession = time2str($row['SUMSession']) : $userSumMaxDailySession = "unavailable";
	(isset($row['SUMDownload'])) ? $userSumDailyDownload = toxbyte($row['SUMDownload']) : $userSumDailyDownload = "unavailable";
	(isset($row['SUMUpload'])) ? $userSumDailyUpload = toxbyte($row['SUMUpload']) : $userSumDailyUpload = "unavailable";
	if ( (isset($row['SUMUpload'])) && (isset($row['SUMDownload'])) )
		$userSumDailyTraffic = toxbyte($row['SUMUpload']+$row['SUMDownload']);
	else
		$userSumDailyTraffic = "unavailable";
	(isset($row['Logins'])) ? $userDailyLogins = $row['Logins'] : $userDailyLogins = "unavailable";



	/*
	 *********************************************************************************************************
	 * Expiration calculations
	 *********************************************************************************************************/
        $sql = "SELECT Value AS 'Expiration' FROM ".$configValues['CONFIG_DB_TBL_RADCHECK'].
		" WHERE (UserName='$username') AND (Attribute='Expiration')";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	(isset($row['Expiration'])) ? $userExpiration = $row['Expiration'] : $userExpiration = "unset";


	/*
	 *********************************************************************************************************
	 * Session-Timeout calculations
	 *********************************************************************************************************/
        $sql = "SELECT Value AS 'Session-Timeout' FROM ".$configValues['CONFIG_DB_TBL_RADREPLY'].
		" WHERE (UserName='$username') AND (Attribute='Session-Timeout')";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	(isset($row['Session-Timeout'])) ? $userSessionTimeout = $row['Session-Timeout'] : $userSessionTimeout = "unset";


	/*
	 *********************************************************************************************************
	 * Idle-Timeout calculations
	 *********************************************************************************************************/
        $sql = "SELECT Value AS 'Idle-Timeout' FROM ".$configValues['CONFIG_DB_TBL_RADREPLY'].
		" WHERE (UserName='$username') AND (Attribute='Idle-Timeout')";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	(isset($row['Idle-Timeout'])) ? $userIdleTimeout = $row['Idle-Timeout'] : $userIdleTimeout = "unset";



        include 'library/closedb.php';


        if ($drawTable == 1) {

                echo "<table border='0' class='table1'>";
                echo "
        		<thead>
        			<tr>
        	                <th colspan='10' align='left'> 
        				<a class=\"table\" href=\"javascript:toggleShowDiv('divSubscriptionAnalysis')\">Subscription Analysis</a>
        	                </th>
        	                </tr>
        		</thead>
        		</table>
        	";
        
                echo "
        		<div id='divSubscriptionAnalysis' style='visibility:visible'>
        		<table border='0' class='table1'>
        		<thread> <tr>
        
                        <th scope='col'>
                        </th>
        
                        <th scope='col'>
        		Global
                        </th>
        
                        <th scope='col'>
        		Monthly
                        </th>
        
                        <th scope='col'>
        		Weekly
                        </th>

                        <th scope='col'>
        		Daily
                        </th>
        
                        </tr> </thread>";
        
        	echo "
        		<tr>
        			<td>Session Limit</td>
        			<td>$userTimeLimitGlobal</td>
        			<td>$userTimeLimitMonthly</td>
        			<td>$userTimeLimitWeekly</td>
        			<td>$userTimeLimitDaily</td>
        		</tr>
        
        		<tr>
        			<td>Session Used</td>
        			<td>$userSumMaxAllSession</td>
        			<td>$userSumMaxMonthlySession</td>
        			<td>$userSumMaxWeeklySession</td>
        			<td>$userSumMaxDailySession</td>
        		</tr>
        
        		<tr>
        			<td>Session Download</td>
        			<td>$userSumDownload</td>
        			<td>$userSumMonthlyDownload</td>
        			<td>$userSumWeeklyDownload</td>
        			<td>$userSumDailyDownload</td>
        		</tr>
        
        		<tr>
        			<td>Session Upload</td>
        			<td>$userSumUpload</td>
        			<td>$userSumMonthlyUpload</td>
        			<td>$userSumWeeklyUpload</td>
        			<td>$userSumDailyUpload</td>
        		</tr>
        
        		<tr>
        			<td>Session Traffic (Up+Down)</td>
        			<td>$userSumAllTraffic</td>
        			<td>$userSumMonthlyTraffic</td>
        			<td>$userSumWeeklyTraffic</td>
        			<td>$userSumDailyTraffic</td>
        		</tr>

        		<tr>
        			<td>Logins</td>
        			<td>$userAllLogins</td>
        			<td>$userMonthlyLogins</td>
        			<td>$userWeeklyLogins</td>
        			<td>$userDailyLogins</td>
        		</tr>
        
        		</table>

        		<table border='0' class='table1'>
        		<thread>


                        <tr>
                        <th scope='col' align='right'>
                        Expiration
                        </th> 
        
                        <th scope='col' align='left'>
                        $userExpiration
                        </th>
                        </tr>


                        <tr>
                        <th scope='col' align='right'>
                        Session-Timeout
                        </th> 
        
                        <th scope='col' align='left'>
                        $userSessionTimeout
                        </th>
                        </tr>


                        <tr>
                        <th scope='col' align='right'>
                        Idle-Timeout
                        </th> 
        
                        <th scope='col' align='left'>
                        $userIdleTimeout
                        </th>
                        </tr>

                        </table>

        		</div>
        	";

	}		

}





/*
 *********************************************************************************************************
 * userPlanInformation
 * $username            username to provide information of
 * $drawTable           if set to 1 (enabled) a toggled on/off table will be drawn
 * 
 * returns user plan information: name, cost, bandwidth, data volume cap/remaining, time cap/remaining
 *
 *********************************************************************************************************
 */
function userPlanInformation($username, $drawTable) {

	include_once('include/management/pages_common.php');
	include 'library/opendb.php';
	
	/*
	 *********************************************************************************************************
	 * check which kind of subscription does the user have
	 *********************************************************************************************************/
	$sql  = "SELECT ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTimeType, ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planName, ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTimeBank, ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planBandwidthUp, ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planBandwidthDown, ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTrafficTotal, ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTrafficUp, ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTrafficDown, ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planRecurringPeriod ".
		" FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].", ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
		" WHERE ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planname=".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planname ".
		" AND ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username='$username' ";
			
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	
	empty($row['planName']) ? $planName = "unavailable" : $planName = $row['planName'];
	empty($row['planRecurringPeriod']) ? $planRecurringPeriod = "unavailable" : $planRecurringPeriod = $row['planRecurringPeriod'];  
	empty($row['planTimeType']) ? $planTimeType = "unavailable" : $planTimeType = $row['planTimeType'];
	empty($row['planTimeBank']) ? $planTimeBank = "unavailable" : $planTimeBank = $row['planTimeBank'];
		
	$planBandwidthUp = $row['planBandwidthUp'];
	$planBandwidthDown = $row['planBandwidthDown'];
	$planTrafficTotal = $row['planTrafficTotal'];


	(isset($row['planTrafficDown'])) ? $planTrafficDown = $row['planTrafficDown'] : $planTrafficDown = "unavailable";
	(isset($row['planTrafficUp'])) ? $planTrafficUp = $row['planTrafficUp'] : $planTrafficUp = "unavailable";

    (isset($row['Access-Period'])) ? $userLimitAccessPeriod = time2str($row['Access-Period']) : $userLimitAccessPeriod = "none";
    
    
	$sql  = "SELECT SUM(AcctSessionTime), SUM(AcctOutputOctets), SUM(AcctInputOctets) ".
		" FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." WHERE username='$username'";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow();
    $totalTimeUsed = $row[0];
    $totalTrafficDown = $row[1];
    $totalTrafficUp = $row[2];
	
    $timeDiff = ($planTimeBank - $totalTimeUsed);
    ($planTrafficDown != 0) ? $trafficDownDiff = ($planTrafficDown - $totalTrafficDown) : $trafficDownDiff = 0;
    ($planTrafficUp != 0) ? $trafficUpDiff = ($planTrafficUp - $totalTrafficUp) : $trafficUpDiff = 0;
    
    
	/*
	 *********************************************************************************************************
	 * Plan Usage calculations
	 *********************************************************************************************************/	
	
        if ($drawTable == 1) {

                echo "<table border='0' class='table1'>
                
        		<thead>
        			<tr>
        	                <th colspan='10' align='left'> 
        						<a class=\"table\" href=\"javascript:toggleShowDiv('divPlanInformation')\">Plan Information</a>
        	                </th>
        	                </tr>
        		</thead>
        		</table>

        		<div id='divPlanInformation' style='visibility:visible'>
        		<table border='0' class='table1'>
        		<thread> <tr>
        
                        <th scope='col'>
                Item
                        </th>
       			
                        <th scope='col'>
        		Allowed by plan
                        </th>
        
                        <th scope='col'>
        		Used 
                        </th>
        
                        <th scope='col'>
        		Remainning
                        </th>
        
                        </tr> </thread>";
        
        	echo "
        		<tr>
        			<td>Session Time</td>
        			<td>".time2str($planTimeBank)."</td>
        			<td>".time2str($totalTimeUsed)."</td>
        			<td>".time2str($timeDiff)."</td>
        		</tr>

        		<tr>
        			<td>Session Download</td>
        			<td>".toxbyte($planTrafficDown)."</td>
        			<td>".toxbyte($totalTrafficDown)."</td>
        			<td>".toxbyte($trafficDownDiff)."</td>
        		</tr>
        
        		<tr>
        			<td>Session Upload</td>
        			<td>".toxbyte($planTrafficUp)."</td>
        			<td>".toxbyte($totalTrafficUp)."</td>
        			<td>".toxbyte($trafficUpDiff)."</td>
        		</tr>

        		</table>
        		

        		<table border='0' class='table1'>
        		<thread>

                        <tr>        
                        <th scope='col' align='right'>
                        Plan Name
                        </th> 
        
                        <th scope='col' align='left'>
                        $planName
                        </th>
                        </tr>

                        <tr>        
                        <th scope='col' align='right'>
                        Plan Recurring Period
                        </th> 
        
                        <th scope='col' align='left'>
                        $planRecurringPeriod
                        </th>
                        </tr>
                        
                        <tr>        
                        <th scope='col' align='right'>
                        Plan Time Type
                        </th> 
        
                        <th scope='col' align='left'>
                        $planTimeType
                        </th>
                        </tr>
                        
                        <tr>        
                        <th scope='col' align='right'>
                        Plan Bandwidth Up
                        </th> 
        
                        <th scope='col' align='left'>
                        $planBandwidthUp
                        </th>
                        </tr>
                        
                        <tr>        
                        <th scope='col' align='right'>
                        Plan Bandwidth Down
                        </th> 
        
                        <th scope='col' align='left'>
                        $planBandwidthDown
                        </th>
                        </tr>
                        
                        </table>

        		</div>
        	";

	}		
}


/*
 *********************************************************************************************************
 * userConnectionStatus
 * $username            username to provide information of
 * $drawTable           if set to 1 (enabled) a toggled on/off table will be drawn
 * 
 * returns user connection information: uploads, download, last connectioned, total online time,
 * whether user is now connected or not.
 *
 *********************************************************************************************************
 */
function userConnectionStatus($username, $drawTable) {

	$userStatus = checkUserOnline($username);

	include_once('include/management/pages_common.php');
	include 'library/opendb.php';

	$username = $dbSocket->escapeSimple($username);			// sanitize variable for sql statement

        $sql = "SELECT AcctStartTime,AcctSessionTime,NASIPAddress,CalledStationId,FramedIPAddress,CallingStationId".
		",AcctInputOctets,AcctOutputOctets FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].
		" WHERE Username='$username' ORDER BY RadAcctId DESC LIMIT 1";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	$userUpload = toxbyte($row['AcctInputOctets']);
	$userDownload = toxbyte($row['AcctOutputOctets']);
	$userLastConnected = $row['AcctStartTime'];
	$userOnlineTime = time2str($row['AcctSessionTime']);

        $nasIPAddress = $row['NASIPAddress'];
        $nasMacAddress = $row['CalledStationId'];
        $userIPAddress = $row['FramedIPAddress'];
        $userMacAddress = $row['CallingStationId'];

        include 'library/closedb.php';

        if ($drawTable == 1) {

                echo "<table border='0' class='table1'>";
                echo "
        		<thead>
        			<tr>
        	                <th colspan='10' align='left'> 
        				<a class=\"table\" href=\"javascript:toggleShowDiv('divConnectionStatus')\">Session Info</a>
        	                </th>
        	                </tr>
        		</thead>
        		</table>
        	";
        
                echo "
                        <div id='divConnectionStatus' style='visibility:visible'>
               		<table border='0' class='table1'>
        		<thread>

                        <tr>        
                        <th scope='col' align='right'>
                        User Status
                        </th> 
        
                        <th scope='col' align='left'>
                        $userStatus
                        </th>
                        </tr>

                        <tr>
                        <th scope='col' align='right'>
                        Last Connection                        
                        </th> 
        
                        <th scope='col' align='left'>
                        $userLastConnected
                        </th>
                        </tr>

                        <tr>
                        <th scope='col' align='right'>
                        Online Time
                        </th> 
        
                        <th scope='col' align='left'>
                        $userOnlineTime
                        </th>
                        </tr>

                        <tr>
                        <th scope='col' align='right'>
                        Server (NAS)
                        </th> 
        
                        <th scope='col' align='left'>
                        $nasIPAddress (MAC: $nasMacAddress)
                        </th>
                        </tr>

                        <tr>
                        <th scope='col' align='right'>
                        User Workstation
                        </th> 
        
                        <th scope='col' align='left'>
                        $userIPAddress (MAC: $userMacAddress)
                        </th>
                        </tr>

                        <tr>
                        <th scope='col' align='right'>
                        User Upload
                        </th> 
        
                        <th scope='col' align='left'>
                        $userUpload
                        </th>
                        </tr>


                        <tr>
                        <th scope='col' align='right'>
                        User Download
                        </th> 
        
                        <th scope='col' align='left'>
                        $userDownload
                        </th>
                        </tr>

                        </table>

        		</div>
        	";

	}		


}


/*
 *********************************************************************************************************
 * checkUserOnline
 * returns string variable "User is online" or "User is offline" based on radacct check for AcctStopTime
 * not set or set to 0000-00-00 00:00:00
 *
 *********************************************************************************************************
 */
function checkUserOnline($username) {

	include 'library/opendb.php';

	$username = $dbSocket->escapeSimple($username);

        $sql = "SELECT Username FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].
		" WHERE AcctStopTime IS NULL OR AcctStopTime = '0000-00-00 00:00:00' AND Username='$username'";
	$res = $dbSocket->query($sql);
	if ($numrows = $res->numRows() >= 1) {
		$userStatus = "User is online";
	} else {
		$userStatus = "User is offline";
	}	

	include 'library/closedb.php';

	return $userStatus;

}


