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

// "key", "label", "parent_id", "open"
function open_accordion_item($descriptor) {
    $label = $descriptor['label'];
    $parent_id = $descriptor['parent_id'];
    $key = (isset($descriptor['key'])) ? $descriptor['key'] : "key-" . rand();
    $show = (isset($descriptor['open']) && $descriptor['open']) ? " show" : "";
    $expanded = (isset($descriptor['open']) && $descriptor['open']) ? "true" : "false";

    echo <<<EOF
<div class="accordion-item">
    <h2 class="accordion-header" id="{$key}-head">
        <button class="accordion-button" type="button" data-bs-toggle="collapse"
            data-bs-target="#{$key}-content" aria-expanded="{$expanded}" aria-controls="{$key}-content">
            {$label}
        </button>
    </h2>

    <div id="{$key}-content" class="accordion-collapse collapse{$show}" aria-labelledby="{$key}-head" data-bs-parent="#{$parent_id}">
        <div class="accordion-body">
EOF;
}

function close_accordion_item() {
    echo <<<EOF
        </div><!-- .accordion-body -->
    </div>
</div><!-- .accordion-item -->

EOF;
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
function userSubscriptionAnalysis($username, $drawTable, $openAccordion=false) {

    include_once('include/management/pages_common.php');
    include('../common/includes/db_open.php');

    $username = $dbSocket->escapeSimple($username);

    $keys = array("Logins", "SUMSession", "SUMDownload", "SUMUpload", "SUMTraffic", );

    $data1 = array(
                    "Logins" => array( "Label" => "Login Count", "Global" => "(n/a)", "Daily" => "(n/a)", "Weekly" => "(n/a)", "Monthly" => "(n/a)",  ),
                    "SUMSession" => array( "Label" => "Session Time", "Global" => "(n/a)", "Daily" => "(n/a)", "Weekly" => "(n/a)", "Monthly" => "(n/a)",  ),
                    "SUMDownload" => array( "Label" => "Downloaded Traffic", "Global" => "(n/a)", "Daily" => "(n/a)", "Weekly" => "(n/a)", "Monthly" => "(n/a)",  ),
                    "SUMUpload" => array( "Label" => "Uploaded Traffic", "Global" => "(n/a)", "Daily" => "(n/a)", "Weekly" => "(n/a)", "Monthly" => "(n/a)",  ),
                    "SUMTraffic" => array( "Label" => "Uploaded+Downloaded Traffic", "Global" => "(n/a)", "Daily" => "(n/a)", "Weekly" => "(n/a)", "Monthly" => "(n/a)",  ),
                 );

    /*
     *********************************************************************************************************
     * Global (Max-All-Session) Limit calculations
     *********************************************************************************************************
     */
    $sql = sprintf("SELECT SUM(AcctSessionTime) AS 'SUMSession', SUM(AcctOutputOctets) AS 'SUMDownload',
                           SUM(AcctInputOctets) AS 'SUMUpload', COUNT(DISTINCT AcctSessionID) AS 'Logins',
                           SUM(AcctInputOctets)+SUM(AcctOutputOctets) AS 'SUMTraffic'
                      FROM %s WHERE UserName='%s' AND acctstoptime>0",
                   $configValues['CONFIG_DB_TBL_RADACCT'], $username);
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

    foreach ($keys as $key) {
        $value = "(n/a)";

        if (isset($row[$key])) {
            $row[$key] = intval($row[$key]);

            if ($key == "SUMSession") {
                $value = time2str($row[$key]);
            } else if (in_array($key, array("SUMDownload", "SUMUpload", "SUMTraffic"))) {
                $value = toxbyte($row[$key]);
            } else {
                $value = $row[$key];
            }
        }

        $data1[$key]["Global"] = $value;
    }

    /*
     *********************************************************************************************************
     * Monthly Limit calculations
     *********************************************************************************************************
     */
    $currMonth = date("Y-m-01");
    $nextMonth = date("Y-m-01", mktime(0, 0, 0, date("m")+ 1, date("d"), date("Y")));

    $sql = sprintf("SELECT SUM(AcctSessionTime) AS 'SUMSession', SUM(AcctOutputOctets) AS 'SUMDownload',
                           SUM(AcctInputOctets) AS 'SUMUpload', COUNT(DISTINCT AcctSessionID) AS 'Logins',
                           SUM(AcctInputOctets)+SUM(AcctOutputOctets) AS 'SUMTraffic'
                      FROM %s
                     WHERE AcctStartTime<'%s' AND AcctStartTime>='%s'
                       AND UserName='%s' AND acctstoptime>0", $configValues['CONFIG_DB_TBL_RADACCT'],
                                                              $nextMonth, $currMonth, $username);
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

    foreach ($keys as $key) {
        $value = "(n/a)";

        if (isset($row[$key])) {
            $row[$key] = intval($row[$key]);

            if ($key == "SUMSession") {
                $value = time2str($row[$key]);
            } else if (in_array($key, array("SUMDownload", "SUMUpload", "SUMTraffic"))) {
                $value = toxbyte($row[$key]);
            } else {
                $value = $row[$key];
            }
        }

        $data1[$key]["Monthly"] = $value;
    }

    /*
     *********************************************************************************************************
     * Weekly Limit calculations
     *********************************************************************************************************
     */
    $currDay = date("Y-m-d", strtotime(date("Y").'W'.date('W')));
    $nextDay = date("Y-m-d", strtotime(date("Y").'W'.date('W')."7"));
    $sql = sprintf("SELECT SUM(AcctSessionTime) AS 'SUMSession', SUM(AcctOutputOctets) AS 'SUMDownload',
                           SUM(AcctInputOctets) AS 'SUMUpload', COUNT(DISTINCT AcctSessionID) AS 'Logins',
                           SUM(AcctInputOctets)+SUM(AcctOutputOctets) AS 'SUMTraffic'
                      FROM %s
                     WHERE AcctStartTime<'%s' AND AcctStartTime>='%s'
                       AND UserName='%s' AND acctstoptime>0", $configValues['CONFIG_DB_TBL_RADACCT'],
                                                              $nextDay, $currDay, $username);
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

    foreach ($keys as $key) {
        $value = "(n/a)";

        if (isset($row[$key])) {
            $row[$key] = intval($row[$key]);

            if ($key == "SUMSession") {
                $value = time2str($row[$key]);
            } else if (in_array($key, array("SUMDownload", "SUMUpload", "SUMTraffic"))) {
                $value = toxbyte($row[$key]);
            } else {
                $value = $row[$key];
            }
        }

        $data1[$key]["Weekly"] = $value;
    }

    /*
     *********************************************************************************************************
     * Daily Limit calculations
     *********************************************************************************************************
     */
    $currDay = date("Y-m-d");
    $nextDay = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+1, date("Y")));
    $sql = sprintf("SELECT SUM(AcctSessionTime) AS 'SUMSession', SUM(AcctOutputOctets) AS 'SUMDownload',
                           SUM(AcctInputOctets) AS 'SUMUpload', COUNT(DISTINCT AcctSessionID) AS 'Logins',
                           SUM(AcctInputOctets)+SUM(AcctOutputOctets) AS 'SUMTraffic'
                      FROM %s
                     WHERE AcctStartTime<'%s' AND AcctStartTime>='%s'
                       AND UserName='%s' AND acctstoptime>0", $configValues['CONFIG_DB_TBL_RADACCT'],
                                                              $nextDay, $currDay, $username);
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

    if ($row) {
        foreach ($keys as $key) {
            $value = "(n/a)";

            if (isset($row[$key])) {
                $row[$key] = intval($row[$key]);

                if ($key == "SUMSession") {
                    $value = time2str($row[$key]);
                } else if (in_array($key, array("SUMDownload", "SUMUpload", "SUMTraffic"))) {
                    $value = toxbyte($row[$key]);
                } else {
                    $value = $row[$key];
                }
            }

            $data1[$key]["Daily"] = $value;
        }
    }

    $data2 = array(
                    "Expiration" => "(n/a)",
                    "Session-Timeout" => "(n/a)",
                    "Idle-Timeout" => "(n/a)",
                  );

    /*
     *********************************************************************************************************
     * Expiration calculations
     *********************************************************************************************************
     */
    $sql = sprintf("SELECT Value AS 'Expiration' FROM %s WHERE UserName='%s' AND Attribute='Expiration'",
                   $configValues['CONFIG_DB_TBL_RADCHECK'], $username);
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

    if (isset($row['Expiration'])) {
        $data2["Expiration"] = $row['Expiration'];
    }

    /*
     *********************************************************************************************************
     * Session-Timeout calculations
     *********************************************************************************************************
     */
    $sql = sprintf("SELECT Value AS 'Session-Timeout' FROM %s WHERE UserName='%s' AND Attribute='Session-Timeout'",
                   $configValues['CONFIG_DB_TBL_RADREPLY'], $username);
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

    if (isset($row['Session-Timeout'])) {
        $data2["Session-Timeout"] = $row['Session-Timeout'];
    }

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

    if (isset($row['Idle-Timeout'])) {
        $data2["Idle-Timeout"] = $row['Idle-Timeout'];
    }

    include('../common/includes/db_close.php');

    if ($drawTable == 1) {

        // print headings
        $labels = array("", "Global", "Monthly", "Weekly", "Daily", );

        // accordion
        $d = array( 'label' => 'Subscription Analysis', 'parent_id' => 'accordion-parent', 'open' => $openAccordion );
        open_accordion_item($d);

        echo '<table class="table table-striped">'
           . '<tr>';

        echo '<th style="width: 25%"></th>';
        foreach ($labels as $label) {
            if (empty($label)) {
                continue;
            }

            $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            printf('<th>%s</th>', $label);
        }

        echo '</tr>';

        // print other lines
        foreach ($data1 as $arr) {
            echo '<tr>';

            printf('<th style="width: 25%%;">%s</th>', $arr["Label"]);
            for ($i = 1; $i < count($labels); $i++) {
                $label = $labels[$i];
                printf('<td>%s</td>', htmlspecialchars($arr[$label], ENT_QUOTES, 'UTF-8'));
            }

            echo '</tr>';
        }

        echo '</table>';

        // print other table
        echo '<table class="table table-striped">';

        foreach ($data2 as $label => $value) {
            $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            printf('<tr><th style="width: 25%%;text-align: right">%s</th><td style="text-align: left">%s</td></tr>', $label, $value);
        }

        echo '</table>';

        close_accordion_item();
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
function userPlanInformation($username, $drawTable, $openAccordion=false) {

    include_once('include/management/pages_common.php');
    include('../common/includes/db_open.php');

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

    $data2 = array(
                    "planName" => array( "Label" => "Plan Name", "Value" => "(n/a)", ),
                    "planRecurringPeriod" => array( "Label" => "Plan Recurring Period", "Value" => "(n/a)", ),
                    "planTimeType" => array( "Label" => "Plan Time Type", "Value" => "(n/a)", ),
                    "planBandwidthDown" => array( "Label" => "Plan Bandwidth Download", "Value" => "(n/a)", ),
                    "planBandwidthUp" => array( "Label" => "Plan Bandwidth Upload", "Value" => "(n/a)", ),
                 );
    $fields = array_keys($data2);

    foreach ($fields as $field) {
        if (!empty($row[$field])) {
            $data2[$field]["Value"] = $row[$field];
        }
    }


    $planTimeBank = (isset($row['planTimeBank'])) ? intval($row['planTimeBank']) : 0;

    $planTrafficTotal = (isset($row['planTrafficTotal'])) ? intval($row['planTrafficTotal']) : 0;
    $planTrafficDown = (isset($row['planTrafficDown'])) ? intval($row['planTrafficDown']) : 0;
    $planTrafficUp = (isset($row['planTrafficUp'])) ? intval($row['planTrafficUp']) : 0;

    $userLimitAccessPeriod = (isset($row['Access-Period'])) ? time2str($row['Access-Period']) : "none";


    $sql = sprintf("SELECT SUM(AcctSessionTime), SUM(AcctOutputOctets), SUM(AcctInputOctets)
                      FROM %s WHERE username='%s'", $configValues['CONFIG_DB_TBL_RADACCT'], $username);
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow();
    $totalTimeUsed = isset($row[0]) ? intval($row[0]) : 0;
    $totalTrafficDown = isset($row[1]) ? intval($row[1]) : 0;
    $totalTrafficUp = isset($row[2]) ? intval($row[2]) : 0;

    $timeDiff = $planTimeBank - $totalTimeUsed;
    $trafficDownDiff = ($planTrafficDown != 0) ? ($planTrafficDown - $totalTrafficDown) : 0;
    $trafficUpDiff = ($planTrafficUp != 0) ? ($planTrafficUp - $totalTrafficUp) : 0;


    $table_header = array( "Item", "Allowed by plan", "Used", "Remainning", );

    $table_body = array(
                            array( "Session Time", time2str($planTimeBank), time2str($totalTimeUsed), time2str($timeDiff), ),
                            array( "Session Download", toxbyte($planTrafficDown), toxbyte($totalTrafficDown), toxbyte($trafficDownDiff), ),
                            array( "Session Upload", toxbyte($planTrafficUp), toxbyte($totalTrafficUp), toxbyte($trafficUpDiff), ),
                       );

    include('../common/includes/db_close.php');

    /*
     *********************************************************************************************************
     * Plan Usage calculations
     *********************************************************************************************************
     */

    if ($drawTable == 1) {
        // accordion
        $d = array( 'label' => 'Plan Information', 'parent_id' => 'accordion-parent', 'open' => $openAccordion );
        open_accordion_item($d);

        echo '<table class="table table-striped">'
           . '<tr>';

        // print header
        foreach ($table_header as $label) {
            $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            printf('<th style="width: 25%%">%s</th>', $label);
        }

        echo '</tr>';

        // print body

        foreach ($table_body as $arr) {
            echo '<tr>';
            foreach ($arr as $value) {
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                printf('<td style="width: 25%%">%s</td>', $value);
            }
            echo '</tr>';
        }

        echo '</table>';

        // print other table
        echo '<table class="table table-striped">';

        foreach ($data2 as $field => $arr) {
            $label = htmlspecialchars($arr["Label"], ENT_QUOTES, 'UTF-8');
            $value = htmlspecialchars($arr["Value"], ENT_QUOTES, 'UTF-8');
            printf('<tr><th style="width: 25%%;text-align: right">%s</th><td style="text-align: left">%s</td></tr>',
                   $label, $value);
        }

        echo '</table>';

        close_accordion_item();
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
function userConnectionStatus($username, $drawTable, $openAccordion=false) {

    $userStatus = checkUserOnline($username);

    include_once('include/management/pages_common.php');
    include('../common/includes/db_open.php');

    // sanitize variable for sql statement
    $username = $dbSocket->escapeSimple($username);

    $sql = sprintf("SELECT AcctStartTime,
                           CASE WHEN AcctStopTime IS NULL THEN timestampdiff(SECOND,AcctStartTime,NOW())
                                ELSE AcctSessionTime
                            END AS AcctSessionTime, AcctInputOctets, AcctOutputOctets,
                           CONCAT(NASIPAddress, ' / %s: ', CalledStationId) AS NAS_IP_ID,
                           CONCAT(FramedIPAddress, ' / %s: ', CallingStationId) AS User_IP_ID
                      FROM %s WHERE Username='%s'
                     ORDER BY RadAcctId DESC LIMIT 1",
                    "Station ID", "Station ID", $configValues['CONFIG_DB_TBL_RADACCT'], $username);

    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

    $data = array(
                    "userStatus" => array( "Label" => "User Status", "Value" => $userStatus, ),
                    "AcctStartTime" => array( "Label" => "Last Connection", "Value" => "(n/a)", ),
                    "AcctSessionTime" => array( "Label" => "Online Time", "Value" => "(n/a)", ),

                    "NAS_IP_ID" => array( "Label" => "Network Access Server (NAS)", "Value" => "(n/a)", ),
                    "User_IP_ID" => array( "Label" => "User Device", "Value" => "(n/a)", ),

                    "AcctInputOctets" => array( "Label" => "User Upload", "Value" => "(n/a)", ),
                    "AcctOutputOctets" => array( "Label" => "User Download", "Value" => "(n/a)", ),
                 );

    $fields = array_keys($data);

    foreach ($fields as $field) {
        if (isset($row) && array_key_exists($field, $row) && !empty($row[$field])) {

            if ($field == "AcctSessionTime") {
                $value = time2str($row[$field]);
            } else if (in_array($field, array("AcctInputOctets", "AcctOutputOctets"))) {
                $value = toxbyte($row[$field]);
            } else {
                $value = $row[$field];
            }

            $data[$field]["Value"] = $value;
        }
    }

    include('../common/includes/db_close.php');

    if ($drawTable == 1) {
        // accordion
        $d = array( 'label' => 'Session Information', 'parent_id' => 'accordion-parent', 'open' => $openAccordion );
        open_accordion_item($d);

        echo '<table class="table table-striped">';

        foreach ($data as $field => $arr) {
            $label = htmlspecialchars($arr["Label"], ENT_QUOTES, 'UTF-8');
            $value = htmlspecialchars($arr["Value"], ENT_QUOTES, 'UTF-8');
            printf('<tr><th style="width: 25%%;text-align: right">%s</th><td style="text-align: left">%s</td></tr>',
                   $label, $value);
        }

        echo '</table>';

        close_accordion_item();
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

    include('../common/includes/db_open.php');

    $username = $dbSocket->escapeSimple($username);

    $sql = sprintf("SELECT COUNT(username) FROM %s
                     WHERE AcctStopTime IS NULL AND Username='%s'
                        OR AcctStopTime = '0000-00-00 00:00:00' AND Username='%s'",
                   $configValues['CONFIG_DB_TBL_RADACCT'], $username, $username);
    $res = $dbSocket->query($sql);

    $numrows = intval($res->fetchRow()[0]);

    include('../common/includes/db_close.php');

    return "User is " . (($numrows > 0) ? "online" : "offline");
}

?>
