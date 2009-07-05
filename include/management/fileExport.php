<?php
/*********************************************************************
 *
 * Filename: fileExport.php
 * Author: Liran Tal <liran.tal@gmail.com>
 *
 * Description:
 * The purpose of this extension is to handle exports of different
 * formats like CSV and PDF to the user's desktop
 *********************************************************************/

include('../../library/checklogin.php');

if (isset($_GET['reportFormat'])) {

	$reportFormat = $_GET['reportFormat'];			// reportFormat is either CSV or PDF
	$reportType = $_SESSION['reportType'];			// reportType defines the sql query string
	$reportQuery = $_SESSION['reportQuery'];		// reportQuery adds the WHERE fields for page-specific reports
	$reportTable = $_SESSION['reportTable'];		// get table name (radacct/radcheck/etc)

	switch ($reportType) {

		case "accountingGeneric":

				include_once('../../library/opendb.php');

				$outputHeader = "Id,NAS/Hotspot,UserName,IP Address,Start Time,Stop Time,".
						"Total Session Time (seconds),Total Upload (bytes),Total Downloads (bytes),".
						"Termination Cause,NAS IP Address".
						"\n";
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

				if ($reportFormat == "csv") {

				        $res = $dbSocket->query($sql);

				        while($row = $res->fetchRow()) {
						$outputContent .= "$row[0],$row[1],$row[2],$row[3],$row[4],$row[5],$row[6],".
									"$row[7],$row[8],$row[9],$row[10]\n";
					}


					$output = $outputHeader . $outputContent;
					exportCSVFile($output);	

					include_once('../../library/closedb.php');
				}

				break;


		case "usernameListGeneric":
				include_once('../../library/opendb.php');

				$outputHeader = "Id,Username,Attribute,Value".
						"\n";
				$outputContent = "";

                                $sql = "SELECT Id, Username, Attribute, Value FROM ".
                                        $configValues['CONFIG_DB_TBL_RADCHECK'].
                                        " $reportQuery ORDER BY Username ASC";


				if ($reportFormat == "csv") {

				        $res = $dbSocket->query($sql);

				        while($row = $res->fetchRow()) {
						$outputContent .= "$row[0],$row[1],$row[2]\n";
					}

					$output = $outputHeader . $outputContent;
					exportCSVFile($output);	

					include_once('../../library/closedb.php');
				}

				break;
				
		case "reportsOnlineUsers":
				include_once('../../library/opendb.php');
	
				$outputHeader = "Username, User IP Address, User MAC Address, Start Time, Total Time, NAS IP Address, NAS MAC Address".
						"\n";
				$outputContent = "";
	
					$sql = "SELECT Username, FramedIPAddress, CallingStationId, AcctStartTime, AcctSessionTime, NASIPAddress, CalledStationId FROM ".
						$configValues['CONFIG_DB_TBL_RADACCT']." $reportQuery ORDER BY Username ASC";
	
				if ($reportFormat == "csv") {
					$res = $dbSocket->query($sql);
	
						while($row = $res->fetchRow()) {
						$outputContent .= "$row[0],$row[1],$row[2],$row[3],$row[4],$row[5],$row[6]\n";
					}
					$output = $outputHeader . $outputContent;
					exportCSVFile($output);	
					include_once('../../library/closedb.php');
				}
	
				break;


		case "reportsLastConnectionAttempts":
				include_once('../../library/opendb.php');
	
				$outputHeader = "Username, Password, Start Time, RADIUS Reply".
						"\n";
				$outputContent = "";
	
				$sql = "SELECT user, pass, reply, date FROM ".$configValues['CONFIG_DB_TBL_RADPOSTAUTH'].
					" $reportQuery ORDER BY User ASC";
	
				if ($reportFormat == "csv") {
					$res = $dbSocket->query($sql);
	
						while($row = $res->fetchRow()) {
						$outputContent .= "$row[0],$row[1],$row[2],$row[3]\n";
					}
					$output = $outputHeader . $outputContent;
					exportCSVFile($output);	
					include_once('../../library/closedb.php');
				}
	
				break;

		case "TopUsers":
				include_once('../../library/opendb.php');
	
				$outputHeader = "Username, IP Address, Start Time,Stop Time, Account Session Time, Account Input, Account Output, Total Bandwidth".
						"\n";
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
	
				if ($reportFormat == "csv") {
					$res = $dbSocket->query($sql);
	
					while($row = $res->fetchRow()) {
						$outputContent .= "$row[0],$row[1],$row[2],$row[3],$row[4],$row[5],$row[6],$row[9]\n";
					}
						
					$output = $outputHeader . $outputContent;
					exportCSVFile($output);	

				}
				include_once('../../library/closedb.php');	
				break;
				

		case "reportsPlansUsage":
				include_once('../../library/opendb.php');
	
				$outputHeader = "Username, Planname, Used Time, Upload, Download, Plan Time, Plan Time Type".
						"\n";
				$outputContent = "";
	
				$sql = "".
					"SELECT ".
						$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username as username,".
						$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planname as planname,".
						"SUM(".$configValues['CONFIG_DB_TBL_RADACCT'].".acctsessiontime) as sessiontime,".
						"SUM(".$configValues['CONFIG_DB_TBL_RADACCT'].".acctinputoctets) as upload,".
						"SUM(".$configValues['CONFIG_DB_TBL_RADACCT'].".acctoutputoctets) as download,".
						$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTimeBank as planTimeBank,".
						$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTimeType as planTimeType".
					" FROM ".
						$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].",".
						$configValues['CONFIG_DB_TBL_RADACCT'].",".
						$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
					$reportQuery;
	
				if ($reportFormat == "csv") {
					$res = $dbSocket->query($sql);
	
						while($row = $res->fetchRow()) {
						$outputContent .= "$row[0],$row[1],$row[2],$row[3],$row[4],$row[5],$row[6]\n";
					}
					$output = $outputHeader . $outputContent;
					exportCSVFile($output);	
					include_once('../../library/closedb.php');
				}
	
				break;

	}


	exit;
}





function exportCSVFile($output) {

	header("Content-type: application/vnd.ms-excel");
	header("Content-disposition: csv; filename=daloradius__" . date("Ymd") . ".csv; size=" . strlen($output));
	print $output;
	
}

