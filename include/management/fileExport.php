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
 *  Description:   the purpose of this extension is to handle exports of different
 *                 formats like CSV and PDF to the user's desktop
 *
 * Authors:	       Liran Tal <liran@enginx.com>
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
                "accountingGeneric", "usernameListGeneric", "reportsOnlineUsers", "reportsLastConnectionAttempts",
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
$reportQuery = $_SESSION['reportQuery'];

// get table name (radacct/radcheck/etc)
$reportTable = $_SESSION['reportTable'];

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

include_once('../../library/opendb.php');

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
        // we use this associative array for generating both,
        // Output Header and SQL selected fields
        $cols = array(
                        "Id" => "id",
                        "Username" => "username",
                        "Attribute" => "attribute",
                        "Value" => "value"
                     );
        
        $selected_fields = implode(", ", array_values($cols));
        $sql = sprintf("SELECT %s FROM %s %s ORDER BY %s ASC",
                       $selected_fields, $reportTable, $reportQuery, array_values($cols)[1]);
        $res = $dbSocket->query($sql);
        
        // this is the output header
        $output = implode(", ", array_keys($cols)) . "\n";
        
        // this is the remaining part of the output content
        while($row = $res->fetchRow()) {
            $output .= implode(",", $row) . "\n";
        }

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
                            "Fullname" => "CONCAT(ui.firstname, ' ', ui.lastname) AS fullname",
                            "Username" => sprintf("pa.%s AS username", $tableSetting['postauth']['user']),
                            "Start Time" => sprintf("pa.%s", $tableSetting['postauth']['date']),
                            "RADIUS Reply" => "pa.reply",
                         );

            $sql_format = "SELECT " . implode(", ", array_values($cols))
                        . " FROM %s %s"
                        . " ORDER BY username ASC";
            $sql = sprintf($sql_format, $reportTable, $reportQuery);

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
            $outputHeader = "Batch Name, Username, Password" . "\n";
            $outputContent = "";

            $batch_id = $_SESSION['reportParams']['batch_id'];

            $sql = "SELECT ".
                    $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".id,".
                    $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_name,".
                    $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username,".
                    $configValues['CONFIG_DB_TBL_RADCHECK'].".Value ".

                    " FROM ".$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].
                    ", ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
                    ", ".$configValues['CONFIG_DB_TBL_RADCHECK'].
                    " WHERE ".
                    $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".batch_id = $batch_id".
                    " AND ".
                    $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".id = $batch_id".
                    " AND ".
                    $configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute LIKE '%-Password'".
                    " AND ".
                    "( ".$configValues['CONFIG_DB_TBL_RADCHECK'].".username = ".
                    $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username) ";

				
            $res = $dbSocket->query($sql);
            while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                $outputContent .= $row['batch_name'].",".$row['username'].",".$row['Value']."\n";
            }
            
            $output = $outputHeader . $outputContent;

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

include_once('../../library/closedb.php');

// at this point, if $output is not empty we can export the file
if (!empty($output)) {
    if ($reportFormat == "csv") {
        exportCSVFile($output);
    }
}
