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
function provisionUser($dbSocket, $txnId) {

	include('library/config_read.php');

	// find the pin code to activate using the pin
	$sql = "SELECT username,planId FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT']." WHERE txnId='$txnId'";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow();
	$pin = $row[0];
	$planId = $row[1];

	// firstly, we add the user to the radcheck table and authorize him
	$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
		" VALUES (0,'$pin','Auth-Type', ':=', 'Accept')";
	$res = $dbSocket->query($sql);

	// we then search the plans to see if the user should belong to a specific
	// usergroup or shall we just add the appropriate attribute to radcheck
	$sql = "SELECT planTimeBank,planGroup,planTimeType,planRecurring,planRecurringPeriod FROM ".
		$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']." WHERE planId='$planId'";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow();
	$planTimeBank = $row[0];
	$planGroup = $row[1];
	$planTimeType = $row[2];
	$planRecurring = $row[3];
	$planRecurringPeriod = $row[4];

	// the group is set, so we simply add the user to this group
	if ($planGroup != "") {

		$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." (UserName,GroupName,priority) ".
			" VALUES ('$pin','planGroup','0')";
		$res = $dbSocket->query($sql);

	} else {

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

				if ((isset($planRecurring)) && ($planRecurring == "Yes")) {

					switch ($planRecurringPeriod) {

						case "Never":
							$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
								" VALUES (0,'$pin','Max-All-Session', ':=', '$planTimeBank')";
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



?>
