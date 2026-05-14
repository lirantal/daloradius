<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
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
 *  Description:   the purpose of this extension is to handle exports of different
 *                 formats like CSV and PDF to the user's desktop
 *
 * Authors:	       Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */
 
include('../../library/checklogin.php');

// reportFormat is either CSV or PDF
$reportFormat = (array_key_exists('reportFormat', $_GET) && isset($_GET['reportFormat']) &&
                 in_array(strtolower($_GET['reportFormat']), array( "csv", "pdf" )))
              ? $_GET['reportFormat'] : "csv";

// this are all the report types this script can generate
$types = array(
                "accountingGeneric", "usernameListGeneric", "usernameListByGroup", "reportsOnlineUsers", "reportsLastConnectionAttempts",
                "TopUsers", "reportsPlansUsage", "reportsBatchActiveUsers", "reportsBatchList",
                "reportsBatchTotalUsers", "reportsInvoiceList"
              );

// reportType defines the sql query string,
// we look for it in both, $_GET and $_SESSION superglobal
$superglobals = array( $_GET, $_SESSION );
$found = false;
foreach ($superglobals as $g) {
    if (array_key_exists('reportType', $g) && isset($g['reportType'])) {
        if (in_array($g['reportType'], $types)) {
            $reportType = $g['reportType'];
            $found = true;
            break;
        }
    }
}

// if we don't find a valid reportType, we exit
if (!$found) {
    exit;
}

// reportQuery adds the WHERE fields for page-specific reports
$reportQuery = $_SESSION['reportQuery'] ?? "";

// get table name (radacct/radcheck/etc)
$reportTable = $_SESSION['reportTable'] ?? "";

// the following two functions tell the browser what file, size, etc. it should expect
function exportCSVFile($output) {
	header("Content-type: text/csv");
	header(sprintf("Content-disposition: attachment; filename=daloradius__%s.csv; size=%s", date("Ymd"), strlen($output)));
	print $output;
}

function exportPDFFile($output) {
	header("Content-type: application/pdf");
	header(sprintf("Content-disposition: attachment; filename=daloradius__%s.pdf; size=%s", date("Ymd"), strlen($output)));
	print $output;
}

include_once('../../../common/includes/db_open.php');

// we init output
$output = "";

// this switch/case only produces a valid $output content
switch ($reportType) {
    case "accountingGeneric":
        $outputHeader = "Id,NAS/Hotspot,UserName,IP Address,Start Time,Stop Time,".
                        "Total Session Time (seconds),Total Upload (bytes),Total Downloads (bytes),".
                        "Termination Cause,NAS IP Address". "\n";
        $outputContent = "";
        
        $sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].
                    ".RadAcctId, ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
                    ".name as hotspot, ".$configValues['CONFIG_DB_TBL_RADACCT'].
                    ".UserName, ".$configValues['CONFIG_DB_TBL_RADACCT'].
                    ".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].
                    ".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].
                    ".AcctStopTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].
                    ".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].
                    ".AcctInputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].
                    ".AcctOutputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].
                    ".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].
                    ".NASIPAddress FROM $reportTable".
                    " LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
                    " ON ".$configValues['CONFIG_DB_TBL_RADACCT'].
                    ".calledstationid = ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
                    ".mac $reportQuery ORDER BY RadAcctId DESC";

        $res = $dbSocket->query($sql);
        while ($row = $res->fetchRow()) {
            $outputContent .= implode(",", $row) . "\n";
        }

        $output = $outputHeader . $outputContent;

        break;

    case "usernameListGeneric":
    case "usernameListByGroup":
        // Export users in the same field order accepted by mng-import-users.php.
        // Required (5): username, password, email, firstname, lastname
        // Optional (15): framedipaddress, expiration, department, company, mobilephone,
        //                workphone, homephone, address, city, state, country, zip,
        //                sessiontimeout, idletimeout, maxdailysession
        if ($reportType === "usernameListByGroup") {
            if (!array_key_exists('groupname', $_GET) || empty(trim($_GET['groupname']))) {
                break;
            }

            $groupname = trim($_GET['groupname']);
            $reportTable = sprintf("%s AS rug INNER JOIN %s AS rc ON rc.username = rug.username "
                                . "LEFT JOIN %s AS ui ON rc.username = ui.username",
                                   $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                                   $configValues['CONFIG_DB_TBL_RADCHECK'],
                                   $configValues['CONFIG_DB_TBL_DALOUSERINFO']);
            $reportQuery = sprintf(" WHERE rug.groupname='%s'", $dbSocket->escapeSimple($groupname));
        }

        $cols = array(
                        "username" => "rc.username AS username",
                        "password" => "COALESCE(MAX(CASE WHEN rc_export.attribute LIKE '%%-Password' THEN rc_export.value END), "
                                    . "MAX(CASE WHEN rc_export.attribute='Auth-Type' THEN rc_export.value END), '') AS password",
                        "email" => "COALESCE(MAX(ui.email), '') AS email",
                        "firstname" => "COALESCE(MAX(ui.firstname), '') AS firstname",
                        "lastname" => "COALESCE(MAX(ui.lastname), '') AS lastname",
                        "framedipaddress" => "COALESCE(MAX(CASE WHEN rr_export.attribute='Framed-IP-Address' THEN rr_export.value END), '') "
                                           . "AS framedipaddress",
                        "expiration" => "COALESCE(MAX(CASE WHEN rc_export.attribute='Expiration' THEN rc_export.value END), '') AS expiration",
                        "department" => "COALESCE(MAX(ui.department), '') AS department",
                        "company" => "COALESCE(MAX(ui.company), '') AS company",
                        "mobilephone" => "COALESCE(MAX(ui.mobilephone), '') AS mobilephone",
                        "workphone" => "COALESCE(MAX(ui.workphone), '') AS workphone",
                        "homephone" => "COALESCE(MAX(ui.homephone), '') AS homephone",
                        "address" => "COALESCE(MAX(ui.address), '') AS address",
                        "city" => "COALESCE(MAX(ui.city), '') AS city",
                        "state" => "COALESCE(MAX(ui.state), '') AS state",
                        "country" => "COALESCE(MAX(ui.country), '') AS country",
                        "zip" => "COALESCE(MAX(ui.zip), '') AS zip",
                        "sessiontimeout" => "COALESCE(MAX(CASE WHEN rr_export.attribute='Session-Timeout' THEN rr_export.value END), '') "
                                          . "AS sessiontimeout",
                        "idletimeout" => "COALESCE(MAX(CASE WHEN rr_export.attribute='Idle-Timeout' THEN rr_export.value END), '') "
                                       . "AS idletimeout",
                        "maxdailysession" => "COALESCE(MAX(CASE WHEN rc_export.attribute='Max-Daily-Session' THEN rc_export.value END), '') "
                                           . "AS maxdailysession"
                     );

        $selected_fields = implode(", ", array_values($cols));
        $sql = sprintf("SELECT %s FROM %s LEFT JOIN %s AS rc_export ON rc_export.username=rc.username "
                     . "LEFT JOIN %s AS rr_export ON rr_export.username=rc.username %s "
                     . "GROUP BY rc.username ORDER BY rc.username ASC",
                       $selected_fields, $reportTable, $configValues['CONFIG_DB_TBL_RADCHECK'],
                       $configValues['CONFIG_DB_TBL_RADREPLY'], $reportQuery);
        $res = $dbSocket->query($sql);

        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, array_keys($cols), ',', '"', '');

        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            // mng-import-users.php expects expiration as YYYY-MM-DD, while FreeRADIUS stores it as "d M Y".
            if (!empty($row['expiration'])) {
                $expiration = DateTime::createFromFormat('d M Y', $row['expiration']);
                if ($expiration !== false) {
                    $row['expiration'] = $expiration->format('Y-m-d');
                }
            }

            $fields = array();
            foreach (array_keys($cols) as $field) {
                $fields[] = $row[$field] ?? '';
            }

            fputcsv($csv, $fields, ',', '"', '');
        }

        rewind($csv);
        $output = stream_get_contents($csv);
        fclose($csv);

        break;

    case "reportsOnlineUsers":
        // we use this associative array for generating both,
        // Output Header and SQL selected fields
        $cols = array(
                        "Username" => "username",
                        "User IP Address" => "framedipaddress",
                        "User MAC Address" => "callingstationid",
                        "Start Time" => "acctstarttime",
                        "Total Time" => "acctsessiontime",
                        "NAS IP Address" => "nasipaddress",
                        "NAS MAC Address" => "calledstationid"
                     );    
                     
        // alias for the CONFIG_DB_TBL_RADACCT table
        $as = "ra";
        $selected_fields = "$as." . implode(", $as.", array_values($cols));
        $sql = sprintf("SELECT %s FROM %s AS %s %s ORDER BY %s.%s ASC",
                       $selected_fields, $reportTable, $as, $reportQuery, $as, array_values($cols)[0]);
        
        $res = $dbSocket->query($sql);
        
        // this is the output header
        $output = implode(",", array_keys($cols)) . "\n";
        
        // this is the remaining part of the output content
        while($row = $res->fetchRow()) {
            $output .= implode(",", $row) . "\n";
        }
        
        break;


        case "reportsLastConnectionAttempts":
            // setting table-related parameters first
            switch($configValues['FREERADIUS_VERSION']) {
                case '1':
                    $tableSetting['postauth']['user'] = 'user';
                    $tableSetting['postauth']['date'] = 'date';
                    break;
                case '2':
                case '3':
                default :
                    $tableSetting['postauth']['user'] = 'username';
                    $tableSetting['postauth']['date'] = 'authdate';
                    break;
            }

            
            // pa & ui are aliases for the joined tables
            $cols = array(
                            "Fullname" => "IF(STRCMP(CONCAT(ui.firstname, ' ', ui.lastname), ' ') = 0, "
                                            . "'(n/a)', "
                                            . "CONCAT(ui.firstname, ' ', ui.lastname))",
                            "Username" => sprintf("pa.%s AS username", $tableSetting['postauth']['user']),
                            "Start Time" => sprintf("pa.%s", $tableSetting['postauth']['date']),
                            "RADIUS Reply" => "pa.reply",
                         );

            $sql_format = "SELECT " . implode(", ", array_values($cols))
                        . " FROM %s %s"
                        . " ORDER BY %s DESC";
            $sql = sprintf($sql_format, $reportTable, $reportQuery, array_values($cols)[2]);

            $res = $dbSocket->query($sql);
            
            $output = implode(",", array_keys($cols)) . "\n";
            
            while($row = $res->fetchRow()) {
                $output .= implode(",", $row) . "\n";
            }
            break;

		case "TopUsers":
            $outputHeader = "Username, IP Address, Start Time,Stop Time, Account Session Time, Account Input, Account Output, Total Bandwidth" . "\n";
            $outputContent = "";

            $sql = "SELECT distinct(radacct.UserName), ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".
                            $configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].
                            ".AcctStopTime, sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime) as Time, ".
                            " sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets) as Upload,sum(".
                            $configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets) as Download, ".
                            $configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".
                            $configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress, sum(".
                            $configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets+".
                            $configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets) as Bandwidth FROM ".
                            $configValues['CONFIG_DB_TBL_RADACCT']." $reportQuery Group BY Username ASC";

            $res = $dbSocket->query($sql);
            while ($row = $res->fetchRow()) {
                $outputContent .= "$row[0],$row[1],$row[2],$row[3],$row[4],$row[5],$row[6],$row[9]\n";
            }

            $output = $outputHeader . $outputContent;
				
            break;

		case "reportsPlansUsage":
        
            // we use this associative array for generating both,
            // Output Header and SQL selected fields
            $cols = array(
                            "Username" => "username",
                            "Plan Name" => "planname",
                            "Used Time" => "sessiontime",
                            "Upload" => "upload",
                            "Download" => "download",
                            "Plan Time" => "planTimeBank",
                            "Plan Time Type" => "planTimeType",
                         );
            
            $selected_fields = implode(", ", array_values($cols));
            $sql = sprintf("SELECT %s FROM %s %s ORDER BY %s ASC",
                           $selected_fields, $reportTable, $reportQuery, array_values($cols)[0]);
            $res = $dbSocket->query($sql);
            
            // this is the output header
            $output = implode(", ", array_keys($cols)) . "\n";
            
            // this is the remaining part of the output content
            while($row = $res->fetchRow()) {
                $output .= implode(",", $row) . "\n";
            }
        
            break;

        case "reportsBatchActiveUsers":
            $outputHeader = "Batch Name, Username, Start Time" . "\n";
            $outputContent = "";

            $sql = $reportQuery;
            
            $res = $dbSocket->query($sql);
            while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                $outputContent .= $row['batch_name'].",".$row['username'].",".$row['acctstarttime']."\n";
            }
            
            $output = $outputHeader . $outputContent;

            break;


		case "reportsBatchList":
            $outputHeader = "Batch Name, Hotspot, Status, Total Users, Active Users, Plan Name, Plan Cost, Batch Cost, Creation Date, Creation By" . "\n";
            $outputContent = "";

            $sql = $reportQuery;

			$res = $dbSocket->query($sql);            
            while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                $batch_cost = ($row['active_users'] * $row['plancost']);
                $outputContent .= $row['batch_name'].",".$row['HotspotName'].",".$row['batch_status'].",".$row['total_users'].",".$row['active_users'].
                                ",".$row['planname'].",".$row['plancost'].",".$batch_cost.",".$row['creationdate'].
                                ",".$row['creationby']."\n";
            }
            
            $output = $outputHeader . $outputContent;

			break;


		case "reportsBatchTotalUsers":
        
            $batch_id = intval($_SESSION['reportParams']['batch_id']);

            // check if in this batch there are some Cleartext-Password attributes
            $sql = sprintf("SELECT COUNT(ubi.username)
                              FROM %s AS ubi, %s AS rc
                             WHERE rc.username=ubi.username
                               AND ubi.batch_id=%d
                               AND rc.op=':='
                               AND rc.attribute='Cleartext-Password'",
                           $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                           $configValues['CONFIG_DB_TBL_RADCHECK'], $batch_id);

            $res = $dbSocket->query($sql);
            $exportableUsers = intval($res->fetchRow()[0]);
            
            // get batch name
            $sql = sprintf("SELECT bh.batch_name FROM %s AS bh WHERE bh.id=%d LIMIT 1",
                           $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'], $batch_id);
            $res = $dbSocket->query($sql);
            $batch_name = $res->fetchRow()[0];
            
            // get all users of this batch
            $sql = sprintf("SELECT ubi.username, rc.attribute, rc.value
                              FROM %s AS bh, %s AS ubi, %s AS rc
                             WHERE rc.username = ubi.username
                               AND ubi.batch_id=bh.id
                               AND bh.id=%d AND rc.op=':='
                               AND (rc.attribute='Auth-Type' OR rc.attribute LIKE '%%-Password')",
                           $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'],
                           $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                           $configValues['CONFIG_DB_TBL_RADCHECK'], $batch_id);
            $res = $dbSocket->query($sql);
            $totalUsers = intval($res->numRows());
            
            // this is the output header
            $output = sprintf("# batch name: %s, users num.: %d\n", $batch_name, $totalUsers);
            
            $output .= "Username";
            if ($exportableUsers > 0) {
                // if all passwords are not cleartext, we don't show password column
                $output .= ",Password";
            }
            $output .= "\n";
            
            while ($row = $res->fetchRow()) {
                
                list($username, $attribute, $value) = $row;
                
                if ($attribute != "Cleartext-Password" || $attribute == "Auth-Type") {
                    $value = "(empty)";
                }
                
                $output .= $username;
                if ($exportableUsers > 0) {
                    $output .= ",$value";
                }
                $output .= "\n";
            }
            
            break;

		case "reportsInvoiceList":
            $outputHeader = "Invoice ID, Customer Name, Username, Date, Total Billed, Total Payed, Balance, Invoice Status" . "\n";
            $outputContent = "";

            $sql = $_SESSION['reportQuery'];
				
            $res = $dbSocket->query($sql);
            while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                $balance = ($row['totalpayed'] - $row['totalbilled']);
                $outputContent .= $row['id'].",".$row['contactperson'].",".$row['username'].",".$row['date'].",".$row['totalbilled'].",".$row['totalpayed'].",".$balance.",".$row['status']."\n";
            }

            $output = $outputHeader . $outputContent;
            
            break;

}

include_once('../../../common/includes/db_close.php');

// at this point, if $output is not empty we can export the file
if (!empty($output)) {
    if ($reportFormat == "csv") {
        exportCSVFile($output);
    }
}
