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
 *              returns user status (active, expired, disabled)
 *		as well as performs different user operations (disable user, enable user, etc)
 *
 * Authors:     Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */


/*
 * The following handles disabling the user
 */
if ((isset($_GET['userDisable'])) && (isset($_GET['username']))) {
	userDisable($_GET['username'], $_GET['divContainer']);
}

/*
 * The following handles disabling the user
 */
if ((isset($_GET['userEnable'])) && (isset($_GET['username']))) {
	userEnable($_GET['username'], $_GET['divContainer']);
}


/*
 * The following handles refilling of user session for billing purposes
 */
if ((isset($_GET['refillSessionTime'])) && (isset($_GET['username'])))
	userRefillSessionTime($_GET['username'], $_GET['divContainer']);

if ((isset($_GET['refillSessionTraffic'])) && (isset($_GET['username'])))
	userRefillSessionTraffic($_GET['username'], $_GET['divContainer']);




function userDisable($username, $divContainer) {

	include 'pages_common.php';
	include('../../library/checklogin.php');
	include '../../library/opendb.php';

	//echo "alert('{$username}');";

	if (!is_array($username))
		$username = array($username);

	foreach ($username as $variable=>$value) {
	
		$user = $dbSocket->escapeSimple($value);		// clean username argument from harmful code

		$sql = "INSERT IGNORE INTO ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." (Username,Groupname,Priority) ".
				" VALUES ('$user','daloRADIUS-Disabled-Users',0) ";		
		$res = $dbSocket->query($sql);
	
	}

	$users = implode(',', $username);
	
	printqn("
    	var divContainer = document.getElementById('{$divContainer}');
        divContainer.innerHTML += '<div class=\"success\">User(s) <b>$users</b> are now disabled.</div>';
	");

	include '../../library/closedb.php';

}





function userEnable($username, $divContainer) {

	include 'pages_common.php';
	include('../../library/checklogin.php');
	include '../../library/opendb.php';

	if (!is_array($username))
		$username = array($username);

	foreach ($username as $variable=>$value) {
	
		$user = $dbSocket->escapeSimple($value);		// clean username argument from harmful code
		if ($user) {
	        $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP'].
	                " WHERE username='$user' AND groupname='daloRADIUS-Disabled-Users'";
	        $res = $dbSocket->query($sql);
		}
	}

	$users = implode(',', $username);
	
	printqn("
    	var divContainer = document.getElementById('{$divContainer}');
        divContainer.innerHTML += '<div class=\"success\">User(s) <b>$users</b> are now enabled.</div>';
	");


        include '../../library/closedb.php';

}



function checkDisabled($username) {

	include 'library/opendb.php';

	$username = $dbSocket->escapeSimple($username);

        $sql = "SELECT Username FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP'].
			" WHERE Username='$username' AND Groupname='daloRADIUS-Disabled-Users'";
	$res = $dbSocket->query($sql);
	if ($numrows = $res->numRows() >= 1) {
	
	        echo "<div class='failure'>
	              	Please note, the user <b>$username</b> is currently disabled.<br/>
					To enable the user, remove the user from the daloRADIUS-Disabled-Users profile <br/>
	              </div>";

	}

	include 'library/closedb.php';

}





function userRefillSessionTime($username, $divContainer) {

	include 'pages_common.php';
	include('../../library/checklogin.php');
	include '../../library/opendb.php';

	if (!is_array($username))
		$username = array($username);

	$allUsers = "";

	foreach ($username as $variable=>$value) {
	
		$user = $dbSocket->escapeSimple($value);		// clean username argument from harmful code
		$allUsers .= $user . ", ";

		// we update the sessiontime value to be 0 - this will only work though
		// for accumulative type accounts. For TTF accounts we need to completely
		// delete the record.
		// to handle this - as a work-around I've modified the accessperiod sql
		// counter definition in radiusd.conf to check for records with AcctSessionTime>=1
		
		
		$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_RADACCT'].
			" SET AcctSessionTime=0 ".
			" WHERE Username='$user'";
		
		$res = $dbSocket->query($sql);

	}

	// take care of recording the billing action in billing_history table
	foreach ($username as $variable=>$value) {

		$user = $dbSocket->escapeSimple($value);                // clean username argument from harmful code

		$sql = "SELECT ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".id, ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username, ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planName, ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".id as PlanID, ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTimeRefillCost, ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTax, ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".paymentmethod, ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".cash, ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardname, ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardnumber, ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardverification, ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardtype, ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardexp ".
			" FROM ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].", ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']." ".
			" WHERE ".
			"(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planname=".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planname)".
			" AND ".
			"(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username='".$user."')";
		$res = $dbSocket->query($sql);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

		$refillCost = $row['planTimeRefillCost'];

		$currDate = date('Y-m-d H:i:s');
		$currBy = $_SESSION['operator_user'];

		$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY'].
			" (id,username,planId,billAmount,billAction,billPerformer,billReason,".
			" paymentmethod,cash,creditcardname,creditcardnumber,creditcardverification,creditcardtype,creditcardexp,".
			" creationdate,creationby".
			")".
			" VALUES ".
			" (0,'$user','".$row['planName']."','".$row['planTimeRefillCost']."','Refill Session Time','daloRADIUS Web Interface','Refill Session Time','".
				$row['paymentmethod']."','".$row['cash']."','".$row['creditcardname']."','".
				$row['creditcardnumber']."','".$row['creditcardverification']."','".$row['creditcardtype']."','".$row['creditcardexp']."',".
				"'$currDate', '$currBy'".
			")";
		$res = $dbSocket->query($sql);

		
		// if the refill cost is anything beyond the amount 0, we create an invoice for it.
		if ($refillCost > 0) {
			
			// if the user id indeed set in the userbillinfo table
			if ($row['id']) {
				include_once("userBilling.php");
		
				$invoiceInfo['notes'] = 'refill user account';
				
				// calculate tax (planTax is the numerical percentage amount) 
				$calcTax = (float) ($row['planTimeRefillCost'] * (float)($row['planTax'] / 100) );
				$invoiceItems[0]['plan_id'] = $row['PlanID'];
				$invoiceItems[0]['amount'] = $row['planTimeRefillCost'];
				$invoiceItems[0]['tax'] = $calcTax;
				$invoiceItems[0]['notes'] = 'refill user session time';
									
				userInvoiceAdd($row['id'], $invoiceInfo, $invoiceItems);
				
			}
		
		}
		
	}


	$users = substr($allUsers, 0, -2);
	printqn("
		var divContainer = document.getElementById('{$divContainer}');
	        divContainer.innerHTML += '<div class=\"success\">User(s) <b>$users</b> session time has been successfully refilled and billed.</div>';
	");

	include '../../library/closedb.php';

}



function userRefillSessionTraffic($username, $divContainer) {

	include 'pages_common.php';
	include('../../library/checklogin.php');
	include '../../library/opendb.php';

	if (!is_array($username))
		$username = array($username);

	$allUsers = "";

	foreach ($username as $variable=>$value) {
	
	        $user = $dbSocket->escapeSimple($value);		// clean username argument from harmful code
		$allUsers .= $user . ", ";

		$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_RADACCT'].
			" SET AcctInputOctets=0, AcctOutputOctets=0 ".
			" WHERE Username='$user'";
		$res = $dbSocket->query($sql);

	}

	// take care of recording the billing action in billing_history table
	foreach ($username as $variable=>$value) {

                $user = $dbSocket->escapeSimple($value);                // clean username argument from harmful code

                $sql = "SELECT ".
                		$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".id, ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username, ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planName, ".
						$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".id as PlanID, ".
						$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTax, ".
                        $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTrafficRefillCost, ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".paymentmethod, ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".cash, ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardname, ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardnumber, ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardverification, ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardtype, ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardexp ".
                        " FROM ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].", ".
                        $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']." ".
                        " WHERE ".
                        "(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planname=".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planname)".
                        " AND ".
                        "(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username='".$user."')";
                $res = $dbSocket->query($sql);
                $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

                $refillCost = $row['planTrafficRefillCost'];

                $currDate = date('Y-m-d H:i:s');
                $currBy = $_SESSION['operator_user'];

                $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY'].
                        " (id,username,planId,billAmount,billAction,billPerformer,billReason,".
                        " paymentmethod,cash,creditcardname,creditcardnumber,creditcardverification,creditcardtype,creditcardexp,".
                        " creationdate,creationby".
                        ")".
                        " VALUES ".
                        " (0,'$user','".$row['planName']."','".$row['planTrafficRefillCost']."','Refill Session Traffic','daloRADIUS Web Interface','Refill Session Traffic','".
                                $row['paymentmethod']."','".$row['cash']."','".$row['creditcardname']."','".
                                $row['creditcardnumber']."','".$row['creditcardverification']."','".$row['creditcardtype']."','".$row['creditcardexp']."',".
                                "'$currDate', '$currBy'".
                        ")";
                $res = $dbSocket->query($sql);
                
                
        // if the refill cost is anything beyond the amount 0, we create an invoice for it.
		if ($refillCost > 0) {
			
			// if the user id indeed set in the userbillinfo table
			if ($row['id']) {
				include_once("userBilling.php");
		
				$invoiceInfo['notes'] = 'refill user account';
				
				// calculate tax (planTax is the numerical percentage amount) 
				$calcTax = (float) ($row['planTrafficRefillCost'] * (float)($row['planTax'] / 100) );
				$invoiceItems[0]['plan_id'] = $row['PlanID'];
				$invoiceItems[0]['amount'] = $row['planTrafficRefillCost'];
				$invoiceItems[0]['tax'] = $calcTax;
				$invoiceItems[0]['notes'] = 'refill user session traffic';
									
				userInvoiceAdd($row['id'], $invoiceInfo, $invoiceItems);
				
			}
		
		}
                
	}


	
	
	
	
	$users = substr($allUsers, 0, -2);
	printqn("
		var divContainer = document.getElementById('{$divContainer}');
	        divContainer.innerHTML += '<div class=\"success\">User(s) <b>$users</b> session traffic has been successfully refilled and billed.</div>';
	");

        include '../../library/closedb.php';

}

