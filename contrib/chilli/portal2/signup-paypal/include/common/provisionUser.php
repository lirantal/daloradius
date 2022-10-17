<?php
/*********************************************************************
* Name: provisionUser.php
* Author: Liran tal <liran.tal@gmail.com>
*
* Provision user in database according to plan information
*
*********************************************************************/


/*****************************************************************************************
 * provisionUser()
 * Provision user in database according to plan information
 *
 * $dbSocket       database socket link
 * $txnId			tranasaction id
 *****************************************************************************************/
function provisionUser($dbSocket, $txnId, $txn_id) {

	include('library/config_read.php');

	// find the pin code to activate using the pin
	$sql = "SELECT username,".$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT'].".planId,".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planName, ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".id, txn_type,payment_status,payment_date,payment_cost FROM ".
		$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT'].
		" LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
		" ON ".
		$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT'].".planId=".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".id ".
		" WHERE txnId='$txnId' AND txn_id='$txn_id' ORDER BY ".$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT'].".id DESC LIMIT 1";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
	
	$data['pin'] = $row['username'];
	$data['planId'] = $row['planId'];
	$data['planName'] = $row['planName'];
	$data['txn_type'] = $row['txn_type'];
	$data['payment_status'] = $row['payment_status'];
	$data['payment_date'] = $row['payment_date'];
	$data['payment_cost'] = $row['payment_cost'];
	
	switch ($data['txn_type']) {
		
		case "web_accept":
			
			if ($data['payment_status'] == "Completed")
				enableUser($dbSocket, $data);
			
			// update a new billing record
			updateBilling($dbSocket, $data);
			
			break;
	
		// Subscription started
		case "subscr_signup":
			
			// a signup from paypal is always followed by a subscr_payment for
			// a payment to be made
			enableUser($dbSocket, $data);
			
			break;
			
		// Subscription canceled
		case "subscr_cancel":
			
			// a user is canceled for whatever reason, when that happens, paypal
			// sends a notice
			disableUser($dbSocket, $data);
			
			break;
			
		// Subscription expired
		case "subscr_eot":
			break;
		// Subscription signup failed
		case "subscr_failed":
			break;
		// Subscription modified
		case "subscr_modify":
			break;
		// Subscription payment received
		case "subscr_payment":
			updateBilling($dbSocket, $data);
			break;
		// Recurring payment received 
		case "recurring_payment":
			
			break;
			
			
			
			
		default:
			
			break;
	}



}






/*****************************************************************************************
 * calcNextBillingDate()
 * calculate the next billing date based on the billing period
 *
 * @param		$data			array of user information
 * @param		$data			array of user information elements: pin, txn_type, planId, payment_status
 * @return		$data			array of billing cycles
 *****************************************************************************************/
function calcNextBillingDate($data, $payment_date) {

	$myData = array();
	
	if ($data['planRecurring'] == "Yes") {
		switch ($data['planRecurringPeriod']) {
			case "Daily":
				$myData['nextbill'] = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($payment_date)));
				break;
			case "Weekly":
				$myData['nextbill'] = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($payment_date)));
				break;
			case "Monthly":
				$myData['nextbill'] = date('Y-m-d H:i:s', strtotime("+1 month", strtotime($payment_date)));
				break;
			case "Quarterly":
				$myData['nextbill'] = date('Y-m-d H:i:s', strtotime("+3 months", strtotime($payment_date)));
				break;
			case "Year":
				$myData['nextbill'] = date('Y-m-d H:i:s', strtotime("+1 year", strtotime($payment_date)));
				break;
		}
	} else {
		$myData['nextbill'] = "0000-00-00 00:00:00";
	}
	
	return $myData;

}


/*****************************************************************************************
 * updateBilling()
 * update billing information due to a payment received
 *
 * @param		$dbSocket       database socket link
 * @param		$data			array of user information elements: pin, txn_type, planId, payment_status
 *****************************************************************************************/
function updateBilling($dbSocket, $data) {

	include('library/config_read.php');

	$pin = $data['pin'];
	$planId = $data['planId'];
	$payment_date = $data['payment_date'];
	
	//returns myData['nextbill']
	$myData = calcNextBillingDate($row, $payment_date);
	// updating last and next bill periods
	$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
		" SET ".
		" lastbill='".$payment_date."', ".
		" nextbill='".$myData['nextbill']."', ".
	$res = $dbSocket->query($sql);

	
	$creationby = "PayPal Provision";
	$paymentmethod = "PayPal";
	$billReason = $data['txn_type'];
	$billAmount = $data['payment_cost'];
	$billPerformer = $creationby;
	// adding a bill history record to billing_history table
	$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY'].
		" (id, username, planId, billAmount, billPerformer, billReason, paymentmethod, creationdate, creationby ) ".
		" VALUES (0, ".
		"'".$pin."', ".
		"'".$planId."', ".
		"'".$billAmount."', ".
		"'".$billPerformer."', ".
		"'".$billReason."', ".
		"'".$paymentmethod."', ".
		"'".$payment_date."', ".
		"'".$creationby."' )";
	$res = $dbSocket->query($sql);
	
}



/*****************************************************************************************
 * enableUser()
 * Enable the user in terms of activating the user in radcheck, userbillinfo and
 * associating the user with any pre-defined plan profiles
 *
 * @param		$dbSocket       database socket link
 * @param		$data			array of user information elements: pin, txn_type, planId, payment_status
 *****************************************************************************************/
function enableUser($dbSocket, $data) {

	include('library/config_read.php');
	
	$pin = $data['pin'];
	$planId = $data['planId'];
	$planName = $data['planName'];

	// firstly, we add the user to the radcheck table and authorize him
	$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
		" VALUES (0,'$pin','Auth-Type', ':=', 'Accept')";
	$res = $dbSocket->query($sql);

	// search to see if the plan is associated with any profiles
	$sql = "SELECT profile_name FROM ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES'].
			" WHERE plan_name='$planName'";
	$res = $dbSocket->getCol($sql);
	// $res is an array of all profiles associated with this plan
	
	// the group is set, so we simply add the user to this group
	if (count($res) != 0) {

		// if profiles are associated with this plan, loop through each and add a usergroup entry for each
		foreach($res as $profile_name) {
			$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." (UserName,GroupName,priority) ".
				" VALUES ('$pin','$profile_name','0')";
			$res = $dbSocket->query($sql);
		}

	} else {

		// we then search the plans to see if the user should belong to a specific
		// usergroup or shall we just add the appropriate attribute to radcheck
		$sql = "SELECT planTimeBank,planTimeType,planRecurring,planRecurringPeriod FROM ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']." WHERE planName='$planName'";
		$res = $dbSocket->query($sql);
		$row = $res->fetchRow();
		$planTimeBank = $row[0];
		$planTimeType = $row[1];
		$planRecurring = $row[2];
		$planRecurringPeriod = $row[3];
		
		switch ($planTimeType) {

			case "Time-To-Finish":
				// time to finish means that the time credit for the user starts at first login and then the counter
				// starts running, even if he disconnects, his time is running down, until it's 0 and then he used it all up

				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
					" VALUES (0,'$pin','Access-Period', ':=', '$planTimeBank')";
				$res = $dbSocket->query($sql);

				break;

			case "Accumulative":
				// accumulate means that the user was given a time credit of N minutes and he can use them whenever he wants,
				// and spreads it towards hours, days, weeks or months.

				if ((isset($planRecurring)) && ($planRecurring == "No")) {
					
					switch ($planRecurringPeriod) {
						case "Never":
							$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
								" VALUES (0,'$pin','Max-All-Session', ':=', '$planTimeBank')";
							$res = $dbSocket->query($sql);
							break;
					}

				}
				
				if ((isset($planRecurring)) && ($planRecurring == "Yes")) {

					switch ($planRecurringPeriod) {

						case "Never":
							$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
								" VALUES (0,'$pin','Max-All-Session', ':=', '$planTimeBank')";
							$res = $dbSocket->query($sql);
							break;

						case "Yearly":
							$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
								" VALUES (0,'$pin','Max-Yearly-Session', ':=', '$planTimeBank')";
							$res = $dbSocket->query($sql);
							break;

						case "Quaterly":
							$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
								" VALUES (0,'$pin','Max-Quaterly-Session', ':=', '$planTimeBank')";
							$res = $dbSocket->query($sql);
							break;
							
						case "Monthly":
							$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
								" VALUES (0,'$pin','Max-Monthly-Session', ':=', '$planTimeBank')";
							$res = $dbSocket->query($sql);
							break;

						case "Weekly":
							$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
								" VALUES (0,'$pin','Max-Weekly-Session', ':=', '$planTimeBank')";
							$res = $dbSocket->query($sql);
							break;

						case "Daily":
							$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
								" VALUES (0,'$pin','Max-Daily-Session', ':=', '$planTimeBank')";
							$res = $dbSocket->query($sql);
							break;
					} //switch
				} //if
				break;
		} //switch

	} //else 

}





/*****************************************************************************************
 * disableUser()
 *
 *
 *
 * @param		$dbSocket       database socket link
 *****************************************************************************************/
function disableUser($dbSocket, $data) {

	include('library/config_read.php');

	$pin = $data['pin'];
	$planId = $data['planId'];

	// firstly, we add the user to the radcheck table and authorize him
	$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
		" VALUES (0,'$pin','Auth-Type', ':=', 'Reject')";
	$res = $dbSocket->query($sql);
	
	
}


?>
