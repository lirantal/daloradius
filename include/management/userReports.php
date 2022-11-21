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
 * Description:    returns user Connection Status, Subscription Analysis, Account Status etc.
 *                 (concept borrowed from Joachim's capture pages)
 * 
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/management/userReports.php') !== false) {
    header('Location: ../../index.php');
    exit;
}


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
    include('library/opendb.php');

    $username = $dbSocket->escapeSimple($username);

    /*
     *********************************************************************************************************
     * Global (Max-All-Session) Limit calculations
     *********************************************************************************************************
     */
    $sql = sprintf("SELECT SUM(AcctSessionTime) AS 'SUMSession', SUM(AcctOutputOctets) AS 'SUMDownload',
                           SUM(AcctInputOctets) AS 'SUMUpload', COUNT(DISTINCT AcctSessionID) AS 'Logins'
                      FROM %s WHERE UserName='%s' AND acctstoptime>0",
                   $configValues['CONFIG_DB_TBL_RADACCT'], $username);
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

    $userSumMaxAllSession = (isset($row['SUMSession'])) ? time2str($row['SUMSession']) : "(n/a)";
    $userSumDownload = (isset($row['SUMDownload'])) ? toxbyte($row['SUMDownload']) : "(n/a)";
    $userSumUpload = (isset($row['SUMUpload'])) ? toxbyte($row['SUMUpload']) : "(n/a)";
    $userSumAllTraffic = (isset($row['SUMUpload']) && isset($row['SUMDownload']))
                       ? (toxbyte($row['SUMUpload'] + $row['SUMDownload']))
                       : "(n/a)";
    $userAllLogins = (isset($row['Logins'])) ? $row['Logins'] : "(n/a)";


    /*
     *********************************************************************************************************
     * Monthly Limit calculations
     *********************************************************************************************************
     */
    $currMonth = date("Y-m-01");
    $nextMonth = date("Y-m-01", mktime(0, 0, 0, date("m")+ 1, date("d"), date("Y")));

    $sql = sprintf("SELECT SUM(AcctSessionTime) AS 'SUMSession', SUM(AcctOutputOctets) AS 'SUMDownload',
                           SUM(AcctInputOctets) AS 'SUMUpload', COUNT(DISTINCT AcctSessionID) AS 'Logins'
                      FROM %s
                     WHERE AcctStartTime<'%s' AND AcctStartTime>='%s'
                       AND UserName='%s' AND acctstoptime>0", $configValues['CONFIG_DB_TBL_RADACCT'],
                                                              $nextMonth, $currMonth, $username);
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

    $userSumMaxMonthlySession = (isset($row['SUMSession'])) ? time2str($row['SUMSession']) : "(n/a)";
    $userSumMonthlyDownload = (isset($row['SUMDownload'])) ? toxbyte($row['SUMDownload']) : "(n/a)";
    $userSumMonthlyUpload = (isset($row['SUMUpload'])) ? toxbyte($row['SUMUpload']) : "(n/a)";
    $userSumMonthlyTraffic = (isset($row['SUMUpload']) && isset($row['SUMDownload']))
                           ? (toxbyte($row['SUMUpload'] + $row['SUMDownload']))
                           : "(n/a)";
    $userMonthlyLogins = (isset($row['Logins'])) ? $row['Logins'] : "(n/a)";


    /*
     *********************************************************************************************************
     * Weekly Limit calculations
     *********************************************************************************************************
     */
    $currDay = date("Y-m-d", strtotime(date("Y").'W'.date('W')));
    $nextDay = date("Y-m-d", strtotime(date("Y").'W'.date('W')."7"));
    $sql = sprintf("SELECT SUM(AcctSessionTime) AS 'SUMSession', SUM(AcctOutputOctets) AS 'SUMDownload',
                           SUM(AcctInputOctets) AS 'SUMUpload', COUNT(DISTINCT AcctSessionID) AS 'Logins'
                      FROM %s
                     WHERE AcctStartTime<'%s' AND AcctStartTime>='%s'
                       AND UserName='%s' AND acctstoptime>0", $configValues['CONFIG_DB_TBL_RADACCT'],
                                                              $nextDay, $currDay, $username);
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

    $userSumMaxWeeklySession = (isset($row['SUMSession'])) ? time2str($row['SUMSession']) : "(n/a)";
    $userSumWeeklyDownload = (isset($row['SUMDownload'])) ? toxbyte($row['SUMDownload']) : "(n/a)";
    $userSumWeeklyUpload = (isset($row['SUMUpload'])) ? toxbyte($row['SUMUpload']) : "(n/a)";
    $userSumWeeklyTraffic = (isset($row['SUMUpload']) && isset($row['SUMDownload']))
                           ? (toxbyte($row['SUMUpload'] + $row['SUMDownload']))
                           : "(n/a)";
    $userWeeklyLogins = (isset($row['Logins'])) ? $row['Logins'] : "(n/a)";


    /*
     *********************************************************************************************************
     * Daily Limit calculations
     *********************************************************************************************************
     */
    $currDay = date("Y-m-d");
    $nextDay = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+1, date("Y")));
    $sql = sprintf("SELECT SUM(AcctSessionTime) AS 'SUMSession', SUM(AcctOutputOctets) AS 'SUMDownload',
                           SUM(AcctInputOctets) AS 'SUMUpload', COUNT(DISTINCT AcctSessionID) AS 'Logins'
                      FROM %s
                     WHERE AcctStartTime<'%s' AND AcctStartTime>='%s'
                       AND UserName='%s' AND acctstoptime>0", $configValues['CONFIG_DB_TBL_RADACCT'],
                                                              $nextDay, $currDay, $username);
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

    $userSumMaxDailySession = (isset($row['SUMSession'])) ? time2str($row['SUMSession']) : "(n/a)";
    $userSumDailyDownload = (isset($row['SUMDownload'])) ? toxbyte($row['SUMDownload']) : "(n/a)";
    $userSumDailyUpload = (isset($row['SUMUpload'])) ? toxbyte($row['SUMUpload']) : "(n/a)";
    $userSumDailyTraffic = (isset($row['SUMUpload']) && isset($row['SUMDownload']))
                           ? (toxbyte($row['SUMUpload'] + $row['SUMDownload']))
                           : "(n/a)";
    $userDailyLogins = (isset($row['Logins'])) ? $row['Logins'] : "(n/a)";
    
    
    /*
     *********************************************************************************************************
     * Expiration calculations
     *********************************************************************************************************
     */
    $sql = sprintf("SELECT Value AS 'Expiration' FROM %s WHERE UserName='%s' AND Attribute='Expiration'",
                   $configValues['CONFIG_DB_TBL_RADCHECK'], $username);
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

    $userExpiration = (isset($row['Expiration'])) ? $row['Expiration'] : "(n/a)";


    /*
     *********************************************************************************************************
     * Session-Timeout calculations
     *********************************************************************************************************
     */
    $sql = sprintf("SELECT Value AS 'Session-Timeout' FROM %s WHERE UserName='%s' AND Attribute='Session-Timeout'",
                   $configValues['CONFIG_DB_TBL_RADREPLY'], $username);
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

    $userSessionTimeout = (isset($row['Session-Timeout'])) ? $row['Session-Timeout'] : "(n/a)";


    /*
     *********************************************************************************************************
     * Idle-Timeout calculations
     *********************************************************************************************************
     */
    $sql = sprintf("SELECT Value AS 'Idle-Timeout' FROM %s AS rr WHERE UserName='%s' AND Attribute='Idle-Timeout'
                     UNION
                    SELECT Value AS 'Idle-Timeout' FROM %s AS rgr
                     WHERE Attribute='Idle-Timeout'
                       AND GroupName IN (SELECT groupname FROM %s rug WHERE username='%s' ORDER BY priority)
                     LIMIT 1", 
                    $configValues['CONFIG_DB_TBL_RADREPLY'], $username, $configValues['CONFIG_DB_TBL_RADGROUPREPLY'],
                    $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $username);
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

    $userIdleTimeout = (isset($row['Idle-Timeout'])) ? $row['Idle-Timeout'] : "(n/a)";
    
    include('library/closedb.php');
    
    if ($drawTable == 1) {
        $href = "javascript:toggleShowDiv('divSubscriptionAnalysis')";
    
        printf('
<table border="0" class="table1" style="margin: 10px auto">
    <thead>
        <tr>
            <th colspan="10" align="left">
                <a class="table" href="%s">
                    Subscription Analysis
                </a>
            </th>
        </tr>
    </thead>
</table>' . "\n", $href);
        
        echo '
<div id="divSubscriptionAnalysis">
    <table border="0" class="table1">
        <thread>
            <tr>
                <th scope="col"></th>
                <th scope="col">Global</th>
                <th scope="col">Monthly</th>
                <th scope="col">Weekly</th>
                <th scope="col">Daily</th>
            </tr> 
        </thread>' . "\n";

        echo "
        <tbody>
            <tr>
                <th>Session Used</th>
                <td>$userSumMaxAllSession</td>
                <td>$userSumMaxMonthlySession</td>
                <td>$userSumMaxWeeklySession</td>
                <td>$userSumMaxDailySession</td>
            </tr>
    
            <tr>
                <th>Session Download</th>
                <td>$userSumDownload</td>
                <td>$userSumMonthlyDownload</td>
                <td>$userSumWeeklyDownload</td>
                <td>$userSumDailyDownload</td>
            </tr>
    
            <tr>
                <th>Session Upload</th>
                <td>$userSumUpload</td>
                <td>$userSumMonthlyUpload</td>
                <td>$userSumWeeklyUpload</td>
                <td>$userSumDailyUpload</td>
            </tr>
    
            <tr>
                <th>Session Traffic (Up+Down)</th>
                <td>$userSumAllTraffic</td>
                <td>$userSumMonthlyTraffic</td>
                <td>$userSumWeeklyTraffic</td>
                <td>$userSumDailyTraffic</td>
            </tr>

            <tr>
                <th>Logins</th>
                <td>$userAllLogins</td>
                <td>$userMonthlyLogins</td>
                <td>$userWeeklyLogins</td>
                <td>$userDailyLogins</td>
            </tr>
        </tbody>
    </table>" . "\n";
    
    printf('
    <table border="0" class="table1" style="margin: 10px auto">
        <tbody>
            <tr>
                <th scope="col" align="right">Expiration</th>
                <td scope="col" align="left">%s</td>
            </tr>
            
            <tr>
                <th scope="col" align="right">Session-Timeout</th>
                <td scope="col" align="left">%s</td>
            </tr>
            
            <tr>
                <th scope="col" align="right">Idle-Timeout</th>
                <td scope="col" align="left">%s</td>
            </tr>
        </tbody>
    </table>
</div>', $userExpiration, $userSessionTimeout, $userIdleTimeout);

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
    include('library/opendb.php');
    
    $username = $dbSocket->escapeSimple($username);
    
    /*
     *********************************************************************************************************
     * check which kind of subscription does the user have
     *********************************************************************************************************
     */
    $sql = sprintf("SELECT bp.planTimeType, bp.planName, bp.planTimeBank, bp.planBandwidthUp, bp.planBandwidthDown,
                           bp.planTrafficTotal, bp.planTrafficUp, bp.planTrafficDown, bp.planRecurringPeriod
                      FROM %s AS bp, %s AS ubi
                     WHERE bp.planname = ubi.planname AND ubi.username = '%s'",
                   $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'], $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                   $username);
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

    $planName = empty($row['planName']) ? "(n/a)" : $row['planName'];
    $planRecurringPeriod = empty($row['planRecurringPeriod']) ? "(n/a)" :$row['planRecurringPeriod'];  
    $planTimeType = empty($row['planTimeType']) ? "(n/a)" : $row['planTimeType'];
    $planTimeBank = empty($row['planTimeBank']) ? 0 : $row['planTimeBank'];
        
    $planBandwidthUp = (isset($row['planBandwidthUp'])) ? $row['planBandwidthUp'] : "(n/a)";
    $planBandwidthDown = (isset($row['planBandwidthDown'])) ? $row['planBandwidthDown'] : "(n/a)";
    $planTrafficTotal = (isset($row['planTrafficTotal'])) ? $row['planTrafficTotal'] : "(n/a)";

    $planTrafficDown = (isset($row['planTrafficDown'])) ? $row['planTrafficDown'] : 0;
    $planTrafficUp = (isset($row['planTrafficUp'])) ? $row['planTrafficUp'] : 0;

    $userLimitAccessPeriod = (isset($row['Access-Period'])) ? time2str($row['Access-Period']) : "none";
    
    
    $sql = sprintf("SELECT SUM(AcctSessionTime), SUM(AcctOutputOctets), SUM(AcctInputOctets)
                      FROM %s WHERE username='%s'", $configValues['CONFIG_DB_TBL_RADACCT'], $username);
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow();
    $totalTimeUsed = isset($row[0]) ? $row[0] : 0;
    $totalTrafficDown = isset($row[1]) ? $row[1] : 0;
    $totalTrafficUp = isset($row[2]) ? $row[2] : 0;
    
    $timeDiff = ($planTimeBank - $totalTimeUsed);
    $trafficDownDiff = ($planTrafficDown != 0) ? ($planTrafficDown - $totalTrafficDown) : 0;
    $trafficUpDiff = ($planTrafficUp != 0) ? ($planTrafficUp - $totalTrafficUp) : 0;
    
    include('library/closedb.php');
    
    /*
     *********************************************************************************************************
     * Plan Usage calculations
     *********************************************************************************************************
     */    
    
    if ($drawTable == 1) {
        $href = "javascript:toggleShowDiv('divPlanInformation')";
    
        printf('
<table border="0" class="table1" style="margin: 10px auto">
    <thead>
        <tr>
            <th colspan="10" align="left">
                <a class="table" href="%s">
                    Plan Information
                </a>
            </th>
        </tr>
    </thead>
</table>' . "\n", $href);

        echo '
<div id="divPlanInformation" style="display:none;visibility:visible">
    <table border="0" class="table1" style="margin: 10px auto">
        <thread>
            <tr>
                <th scope="col">Item</th>
                <th scope="col">Allowed by plan</th>
                <th scope="col">Used </th>
                <th scope="col">Remainning</th>
            </tr>
        </thread>' . "\n";

        echo "
        <tbody>
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
        </tbody>
    </table>" . "\n";
                

        printf('
    <table border="0" class="table1">
        <tr>
            <th scope="col" align="right">Plan Name</th> 
            <td scope="col" align="left">%s</td>
        </tr>

        <tr>        
            <th scope="col" align="right">Plan Recurring Period</th>
            <td scope="col" align="left">%s</td>
        </tr>

        <tr>        
            <th scope="col" align="right">Plan Time Type</th>
            <td scope="col" align="left">%s</td>
        </tr>

        <tr>        
            <th scope="col" align="right">Plan Bandwidth Up</th>
            <td scope="col" align="left">%s</td>
        </tr>

        <tr>        
            <th scope="col" align="right">Plan Bandwidth Down</th>
            <td scope="col" align="left">%s</td>
        </tr>
    </table>
</div>' . "\n", $planName, $planRecurringPeriod, $planTimeType, $planBandwidthUp, $planBandwidthDown);

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
    include('library/opendb.php');

    $username = $dbSocket->escapeSimple($username);            // sanitize variable for sql statement

    $sql = sprintf("SELECT AcctStartTime,
                           CASE WHEN AcctStopTime IS NULL THEN timestampdiff(SECOND,AcctStartTime,NOW())
                                ELSE AcctSessionTime
                            END AS AcctSessionTime, NASIPAddress, CalledStationId, FramedIPAddress, CallingStationId,
                           AcctInputOctets, AcctOutputOctets
                      FROM %s WHERE Username='%s'
                     ORDER BY RadAcctId DESC LIMIT 1", $configValues['CONFIG_DB_TBL_RADACCT'], $username);
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

    include('library/closedb.php');

    if ($drawTable == 1) {
        $href = "javascript:toggleShowDiv('divConnectionStatus')";
    
        printf('
<table border="0" class="table1" style="margin: 10px auto">
    <thead>
        <tr>
            <th colspan="10" align="left">
                <a class="table" href="%s">
                    Session Info
                </a>
            </th>
        </tr>
    </thead>
</table>' . "\n", $href);

        echo "
<div id='divConnectionStatus' style='display:none;visibility:visible'>
    <table border='0' class='table1' style='margin: 10px auto'>
        <tr>        
            <th scope='col' align='right'>User Status</th>
            <td scope='col' align='left'>$userStatus</td>
        </tr>

        <tr>
            <th scope='col' align='right'>Last Connection</th> 
            <td scope='col' align='left'>$userLastConnected</td>
        </tr>

        <tr>
            <th scope='col' align='right'>Online Time</th>
            <td scope='col' align='left'>$userOnlineTime</td>
        </tr>

        <tr>
            <th scope='col' align='right'>Server (NAS)</th>
            <td scope='col' align='left'>$nasIPAddress (MAC: $nasMacAddress)</td>
        </tr>

        <tr>
            <th scope='col' align='right'>User Workstation</th>
            <td scope='col' align='left'>$userIPAddress (MAC: $userMacAddress)</td>
        </tr>

        <tr>
            <th scope='col' align='right'>User Upload</th>
            <td scope='col' align='left'>$userUpload</td>
        </tr>

        <tr>
            <th scope='col' align='right'>User Download</th>
            <td scope='col' align='left'>$userDownload</td>
        </tr>
    </table>
</div>" . "\n";

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

    include('library/opendb.php');

    $username = $dbSocket->escapeSimple($username);

    $sql = sprintf("SELECT COUNT(username) FROM %s
                     WHERE AcctStopTime IS NULL AND Username='%s'
                        OR AcctStopTime = '0000-00-00 00:00:00' AND Username='%s'",
                   $configValues['CONFIG_DB_TBL_RADACCT'], $username, $username);
    $res = $dbSocket->query($sql);

    $numrows = intval($res->fetchRow()[0]);

    include('library/closedb.php');

    return "User is " . (($numrows > 0) ? "online" : "offline");
}
