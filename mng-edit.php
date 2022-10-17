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
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

	include ("library/checklogin.php");
	$operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	$logAction = "";
	$logDebugSQL = ""; 	// initialize variable

	include 'library/opendb.php';

	if (isset($_REQUEST['submit'])) {

		$currDate = date('Y-m-d H:i:s');			// current date and time to enter as creationdate field
		$currBy = $_SESSION['operator_user'];

		$username = trim($_REQUEST['username']);
		$password = "";						// we initialize the $password variable to contain nothing

		isset ($_REQUEST['newgroups']) ? $newgroups = $_REQUEST['newgroups'] : $newgroups = "";
//		isset ($_REQUEST['oldgroups']) ? $oldgroups = $_REQUEST['oldgroups'] : $oldgroups = "";
		isset ($_REQUEST['groups']) ? $groups = $_REQUEST['groups'] : $groups = "";
		isset ($_REQUEST['groups_priority']) ? $groups_priority = $_REQUEST['groups_priority'] : $groups_priority = "";

		$firstname = $_REQUEST['firstname'];
		$lastname = $_REQUEST['lastname'];
		$email = $_REQUEST['email'];
		$department = $_REQUEST['department'];
		$company = $_REQUEST['company'];
		$workphone = $_REQUEST['workphone'];
		$homephone = $_REQUEST['homephone'];
		$mobilephone = $_REQUEST['mobilephone'];
		$address = $_REQUEST['address'];
		$city = $_REQUEST['city'];
		$state = $_REQUEST['state'];
		$country = $_REQUEST['country'];
		$zip = $_REQUEST['zip'];
		$notes = $_REQUEST['notes'];
		isset ($_POST['changeUserInfo']) ? $ui_changeuserinfo = $_POST['changeUserInfo'] : $ui_changeuserinfo = "0";
		isset($_POST['enableUserPortalLogin']) ? $ui_enableUserPortalLogin = $_POST['enableUserPortalLogin'] : $ui_enableUserPortalLogin = "0";
		isset($_POST['portalLoginPassword']) ? $ui_PortalLoginPassword = $_POST['portalLoginPassword'] : $ui_PortalLoginPassword = "1234";

		isset($_POST['planName']) ? $planName = $_POST['planName'] : $planName = "";
		isset($_POST['oldplanName']) ? $oldplanName = $_POST['oldplanName'] : $oldplanName = "";

		isset($_POST['bi_contactperson']) ? $bi_contactperson = $_POST['bi_contactperson'] : $bi_contactperson = "";
		isset($_POST['bi_planname']) ? $bi_planname = $_POST['bi_planname'] : $bi_planname = "";
		isset($_POST['bi_company']) ? $bi_company = $_POST['bi_company'] : $bi_company = "";
		isset($_POST['bi_email']) ? $bi_email = $_POST['bi_email'] : $bi_email = "";
		isset($_POST['bi_phone']) ? $bi_phone = $_POST['bi_phone'] : $bi_phone = "";
		isset($_POST['bi_address']) ? $bi_address = $_POST['bi_address'] : $bi_address = "";
		isset($_POST['bi_city']) ? $bi_city = $_POST['bi_city'] : $bi_city = "";
		isset($_POST['bi_state']) ? $bi_state = $_POST['bi_state'] : $bi_state = "";
		isset($_POST['bi_country']) ? $bi_country = $_POST['bi_country'] : $bi_country = "";
		isset($_POST['bi_zip']) ? $bi_zip = $_POST['bi_zip'] : $bi_zip = "";
		isset($_POST['bi_paymentmethod']) ? $bi_paymentmethod = $_POST['bi_paymentmethod'] : $bi_paymentmethod = "";
		isset($_POST['bi_cash']) ? $bi_cash = $_POST['bi_cash'] : $bi_cash = "";
		isset($_POST['bi_creditcardname']) ? $bi_creditcardname = $_POST['bi_creditcardname'] : $bi_creditcardname = "";
		isset($_POST['bi_creditcardnumber']) ? $bi_creditcardnumber = $_POST['bi_creditcardnumber'] : $bi_creditcardnumber = "";
		isset($_POST['bi_creditcardverification']) ? $bi_creditcardverification = $_POST['bi_creditcardverification'] : $bi_creditcardverification = "";
		isset($_POST['bi_creditcardtype']) ? $bi_creditcardtype = $_POST['bi_creditcardtype'] : $bi_creditcardtype = "";
		isset($_POST['bi_creditcardexp']) ? $bi_creditcardexp = $_POST['bi_creditcardexp'] : $bi_creditcardexp = "";
		isset($_POST['bi_notes']) ? $bi_notes = $_POST['bi_notes'] : $bi_notes = "";
		isset($_POST['bi_lead']) ? $bi_lead = $_POST['bi_lead'] : $bi_lead = "";
		isset($_POST['bi_coupon']) ? $bi_coupon = $_POST['bi_coupon'] : $bi_coupon = "";
		isset($_POST['bi_ordertaker']) ? $bi_ordertaker = $_POST['bi_ordertaker'] : $bi_ordertaker = "";
		isset($_POST['bi_billstatus']) ? $bi_billstatus = $_POST['bi_billstatus'] : $bi_billstatus = "";
		isset($_POST['bi_lastbill']) ? $bi_lastbill = $_POST['bi_lastbill'] : $bi_lastbill = "";
		isset($_POST['bi_nextbill']) ? $bi_nextbill = $_POST['bi_nextbill'] : $bi_nextbill = "";
		isset($_POST['bi_nextinvoicedue']) ? $bi_nextinvoicedue = $_POST['bi_nextinvoicedue'] : $bi_nextinvoicedue = "";
		isset($_POST['bi_billdue']) ? $bi_billdue = $_POST['bi_billdue'] : $bi_billdue = "";
		isset($_POST['bi_postalinvoice']) ? $bi_postalinvoice = $_POST['bi_postalinvoice'] : $bi_postalinvoice = "";
		isset($_POST['bi_faxinvoice']) ? $bi_faxinvoice = $_POST['bi_faxinvoice'] : $bi_faxinvoice = "";
		isset($_POST['bi_emailinvoice']) ? $bi_emailinvoice = $_POST['bi_emailinvoice'] : $bi_emailinvoice = "";
		isset($_POST['changeUserBillInfo']) ? $bi_changeuserbillinfo = $_POST['changeUserBillInfo'] : $bi_changeuserbillinfo = "0";

		isset($_POST['passwordOrig']) ? $passwordOrig = $_POST['passwordOrig'] : $passwordOrig = "";

		//Fix up errors with droping the Plan name
		if ($planName== "")
			$planName = $oldplanName;

		function addPlanProfile($dbSocket, $username, $planName, $oldplanName) {


			if ($planName == $oldplanName)
				return;

			global $logDebugSQL;
			global $configValues;

			$sql = "SELECT planGroup FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
						" WHERE planName='".$dbSocket->escapeSimple($oldplanName)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";

			$row = $res->fetchRow();
			$oldplanGroup = $row[0];

			if ( (isset($oldplanGroup)) && ($oldplanGroup != "") ) {

				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." WHERE ".
							" (Username='".$dbSocket->escapeSimple($username)."' AND GroupName='".$dbSocket->escapeSimple($oldplanGroup)."') ";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
			}

			$sql = "SELECT planGroup FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
						" WHERE planName='".$dbSocket->escapeSimple($planName)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";

			$row = $res->fetchRow();
			$planGroup = $row[0];

			if ( (isset($planGroup)) && ($planGroup != "") ) {

				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." (UserName,GroupName,priority) ".
							" VALUES ('".$dbSocket->escapeSimple($username)."', '".$dbSocket->escapeSimple($planGroup)."',0) ";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
			}

		}


		if (trim($username) != "") {

			/* perform user info table instructions */
			$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].
					" WHERE username='".$dbSocket->escapeSimple($username)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";

			// if there were no records for this user present in the userinfo table
			if ($res->numRows() == 0) {
				// we add these records to the userinfo table
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].
					" (id, username, firstname, lastname, email, department, company, workphone, homephone, mobilephone,".
					" address, city, state, country, zip, ".
					" notes, changeuserinfo, portalloginpassword, enableportallogin, creationdate, creationby, updatedate, updateby) ".
					" VALUES (0, '".$dbSocket->escapeSimple($username)."', '".
					$dbSocket->escapeSimple($firstname)."', '".$dbSocket->escapeSimple($lastname)."', '".
					$dbSocket->escapeSimple($email)."','".$dbSocket->escapeSimple($department)."', '".
					$dbSocket->escapeSimple($company)."', '".$dbSocket->escapeSimple($workphone)."','".
					$dbSocket->escapeSimple($homephone)."', '".$dbSocket->escapeSimple($mobilephone)."', '".
					$dbSocket->escapeSimple($address)."', '".$dbSocket->escapeSimple($city)."', '".
					$dbSocket->escapeSimple($state)."', '".$dbSocket->escapeSimple($country)."', '".
					$dbSocket->escapeSimple($zip)."', '".
					$dbSocket->escapeSimple($notes)."', '".$dbSocket->escapeSimple($ui_changeuserinfo)."', '".
					$dbSocket->escapeSimple($ui_PortalLoginPassword)."', '".$dbSocket->escapeSimple($ui_enableUserPortalLogin)."', ".
					"'$currDate', '$currBy', NULL, NULL)";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
			} else {
				// update user information table
			   $sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOUSERINFO']." SET firstname='".
					$dbSocket->escapeSimple($firstname).
					"', lastname='".$dbSocket->escapeSimple($lastname).
					"', email='".$dbSocket->escapeSimple($email).
					"', department='".$dbSocket->escapeSimple($department).
					"', company='".$dbSocket->escapeSimple($company).
					"', workphone='".$dbSocket->escapeSimple($workphone).
					"', homephone='".$dbSocket->escapeSimple($homephone).
					"', mobilephone='".$dbSocket->escapeSimple($mobilephone).
					"', address='".$dbSocket->escapeSimple($address).
					"', city='".$dbSocket->escapeSimple($city).
					"', state='".$dbSocket->escapeSimple($state).
					"', country='".$dbSocket->escapeSimple($country).
					"', zip='".$dbSocket->escapeSimple($zip).
					"', notes='".$dbSocket->escapeSimple($notes).
					"', changeuserinfo='".$dbSocket->escapeSimple($ui_changeuserinfo).
					"', portalloginpassword='".$dbSocket->escapeSimple($ui_PortalLoginPassword).
					"', enableportallogin='".$dbSocket->escapeSimple($ui_enableUserPortalLogin).
					"', updatedate='$currDate', updateby='$currBy' ".
					" WHERE username='".$dbSocket->escapeSimple($username)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
			}




			/* perform user billing info table instructions */
			$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
					" WHERE username='".$dbSocket->escapeSimple($username)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";

			// if there were no records for this user present in the userbillinfo table
			if ($res->numRows() == 0) {
	                        $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
	                                " (id, planname,username, contactperson, company, email, phone, ".
	                                " address, city, state, country, zip, ".
	                                " paymentmethod, cash, creditcardname, creditcardnumber, creditcardverification, creditcardtype, creditcardexp, ".
	                                " notes, changeuserbillinfo, ".
                                        " `lead`, coupon, ordertaker, billstatus, lastbill, nextbill, nextinvoicedue, billdue, postalinvoice, faxinvoice, emailinvoice, ".
	                                " creationdate, creationby, updatedate, updateby) ".
	                                " VALUES (0, '".$dbSocket->escapeSimple($planName)."',
	                                '".$dbSocket->escapeSimple($username)."', '".$dbSocket->escapeSimple($bi_contactperson)."', '".
	                                $dbSocket->escapeSimple($bi_company)."', '".$dbSocket->escapeSimple($bi_email)."', '".
	                                $dbSocket->escapeSimple($bi_phone)."', '".$dbSocket->escapeSimple($bi_address)."', '".
	                                $dbSocket->escapeSimple($bi_city)."', '".$dbSocket->escapeSimple($bi_state)."', '".
	                                $dbSocket->escapeSimple($bi_country)."', '".
	                                $dbSocket->escapeSimple($bi_zip)."', '".$dbSocket->escapeSimple($bi_paymentmethod)."', '".
	                                $dbSocket->escapeSimple($bi_cash)."', '".$dbSocket->escapeSimple($bi_creditcardname)."', '".
	                                $dbSocket->escapeSimple($bi_creditcardnumber)."', '".$dbSocket->escapeSimple($bi_creditcardverification)."', '".
	                                $dbSocket->escapeSimple($bi_creditcardtype)."', '".$dbSocket->escapeSimple($bi_creditcardexp)."', '".
	                                $dbSocket->escapeSimple($bi_notes)."', '".
	                                $dbSocket->escapeSimple($bi_changeuserbillinfo)."', '".
                                        $dbSocket->escapeSimple($bi_lead)."', '".$dbSocket->escapeSimple($bi_coupon)."', '".
                                        $dbSocket->escapeSimple($bi_ordertaker)."', '".$dbSocket->escapeSimple($bi_billstatus)."', '".
                                        $dbSocket->escapeSimple($bi_lastbill)."', '".$dbSocket->escapeSimple($bi_nextbill)."', '".
                                        $dbSocket->escapeSimple($bi_nextinvoicedue)."', '".$dbSocket->escapeSimple($bi_billdue)."', '".
                                        $dbSocket->escapeSimple($bi_postalinvoice)."', '".$dbSocket->escapeSimple($bi_faxinvoice)."', '".
                                        $dbSocket->escapeSimple($bi_emailinvoice).
	                                "', '$currDate', '$currBy', NULL, NULL)";
	                        $res = $dbSocket->query($sql);
	                        $logDebugSQL .= $sql . "\n";
			} else {
				// update user information table
			   $sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO']." SET contactperson='".
					$dbSocket->escapeSimple($bi_contactperson).
					"', planname='".$dbSocket->escapeSimple($planName).
					"', company='".$dbSocket->escapeSimple($bi_company).
					"', email='".$dbSocket->escapeSimple($bi_email).
					"', phone='".$dbSocket->escapeSimple($bi_phone).
					"', paymentmethod='".$dbSocket->escapeSimple($bi_paymentmethod).
					"', cash='".$dbSocket->escapeSimple($bi_cash).
					"', creditcardname='".$dbSocket->escapeSimple($bi_creditcardname).
					"', creditcardnumber='".$dbSocket->escapeSimple($bi_creditcardnumber).
					"', creditcardverification='".$dbSocket->escapeSimple($bi_creditcardverification).
					"', creditcardtype='".$dbSocket->escapeSimple($bi_creditcardtype).
					"', creditcardexp='".$dbSocket->escapeSimple($bi_creditcardexp).
					"', address='".$dbSocket->escapeSimple($bi_address).
					"', city='".$dbSocket->escapeSimple($bi_city).
					"', state='".$dbSocket->escapeSimple($bi_state).
					"', country='".$dbSocket->escapeSimple($bi_country).
					"', zip='".$dbSocket->escapeSimple($bi_zip).
					"', notes='".$dbSocket->escapeSimple($bi_notes).
					"', changeuserbillinfo='".$dbSocket->escapeSimple($bi_changeuserbillinfo).
                                        "', `lead`='".$dbSocket->escapeSimple($bi_lead).
                                        "', coupon='".$dbSocket->escapeSimple($bi_coupon).
                                        "', ordertaker='".$dbSocket->escapeSimple($bi_ordertaker).
                                        "', billstatus='".$dbSocket->escapeSimple($bi_billstatus).
/*
                                        "', lastbill='".$dbSocket->escapeSimple($bi_lastbill).
                                        "', nextbill='".$dbSocket->escapeSimple($bi_nextbill).
*/
                                        "', nextinvoicedue='".$dbSocket->escapeSimple($bi_nextinvoicedue).
                                        "', billdue='".$dbSocket->escapeSimple($bi_billdue).

                                        "', postalinvoice='".$dbSocket->escapeSimple($bi_postalinvoice).
                                        "', faxinvoice='".$dbSocket->escapeSimple($bi_faxinvoice).
                                        "', emailinvoice='".$dbSocket->escapeSimple($bi_emailinvoice).
					"', updatedate='$currDate', updateby='$currBy' ".
					" WHERE username='".$dbSocket->escapeSimple($username)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
			}

			 // update usergroup mapping (existing)
			 if ($groups) {

				$sql = "DELETE FROM ". $configValues['CONFIG_DB_TBL_RADUSERGROUP'] ." WHERE UserName='".
					$dbSocket->escapeSimple($username)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$grpcnt = 0;			// group counter
				foreach ($groups as $group) {

//					$oldgroup = $oldgroups[$grpcnt];

					if (!($groups_priority[$grpcnt]))
						$group_priority = 0;
					else
						$group_priority = $groups_priority[$grpcnt];

/*
					if (trim($group) != "") {  // if the group was marked as an empty option, then it means we need to remove it
						$sql = "DELETE FROM ". $configValues['CONFIG_DB_TBL_RADUSERGROUP'] ." WHERE UserName='".
							$dbSocket->escapeSimple($username)."' AND GroupName='".$dbSocket->escapeSimple($oldgroup)."'";
						$res = $dbSocket->query($sql);
						$logDebugSQL .= $sql . "\n";
					} else {
*/

					if (trim($group) != "") {
		      	                         $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." (UserName,GroupName,priority) ".
                	                                " VALUES ('".$dbSocket->escapeSimple($username)."', '".$dbSocket->escapeSimple($group)."', ".
							$dbSocket->escapeSimple($group_priority).")";
       	        	              	         $res = $dbSocket->query($sql);
					}

/*
						$sql = "UPDATE ". $configValues['CONFIG_DB_TBL_RADUSERGROUP'] ." SET UserName='".
							$dbSocket->escapeSimple($username)."', GroupName='".$dbSocket->escapeSimple($group)."', priority=".
							$dbSocket->escapeSimple($group_priority)." WHERE UserName='".$dbSocket->escapeSimple($username).
							"' AND GroupName='".$dbSocket->escapeSimple($oldgroup)."';";
						$res = $dbSocket->query($sql);
*/
						$logDebugSQL .= $sql . "\n";
//					}

					$grpcnt++;		// we increment group index count so we can access the group priority array
				}
			}


			// insert usergroup mapping (new groups)
	                if (isset($newgroups)) {

	                        foreach ($newgroups as $newgroup) {

               	                 if (trim($newgroup) != "") {
               	                         $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." (UserName,GroupName,priority) ".
                	                                " VALUES ('".$dbSocket->escapeSimple($username)."', '".$dbSocket->escapeSimple($newgroup)."',0) ";
       	                      	         $res = $dbSocket->query($sql);
                       	                $logDebugSQL .= $sql . "\n";
                	                }
        	                }
	                }

	addPlanProfile($dbSocket, $username, $planName, $oldplanName);

			foreach( $_POST as $element=>$field ) {

				// switch case to rise the flag for several $attribute which we do not
				// wish to process (ie: do any sql related stuff in the db)
				switch ($element) {
					case "username":
					case "submit":
					case "oldgroups":
					case "groups":
					case "planName":
					case "oldplanName":
					case "groups_priority":
					case "copycontact";
					case "firstname":
					case "lastname":
					case "email":
					case "department":
					case "company":
					case "workphone":
					case "homephone":
					case "mobilephone":
					case "address":
					case "city":
					case "state":
					case "country":
					case "zip":
					case "notes":
					case "changeUserInfo":
                    case "bi_contactperson":
                    case "bi_company":
                    case "bi_email":
                    case "bi_phone":
                    case "bi_address":
                    case "bi_city":
                    case "bi_state":
                    case "bi_country":
                    case "bi_zip":
                    case "bi_paymentmethod":
                    case "bi_cash":
                    case "bi_creditcardname":
                    case "bi_creditcardnumber":
                    case "bi_creditcardverification":
                    case "bi_creditcardtype":
                    case "bi_creditcardexp":
                    case "bi_notes":
                    case "changeUserBillInfo":
                    case "bi_lead":
                    case "bi_coupon":
                    case "bi_ordertaker":
                    case "bi_billstatus":
                    case "bi_lastbill":
                    case "bi_nextbill":
                    case "bi_nextinvoicedue":
                    case "bi_billdue":
                    case "bi_postalinvoice":
                    case "bi_faxinvoice":
                    case "bi_emailinvoice":
      				case "bi_planname":
					case "passwordOrig":
					case "newgroups":
					case "portalLoginPassword":
					case "enableUserPortalLogin":

						$skipLoopFlag = 1;      // if any of the cases above has been met we set a flag
												// to skip the loop (continue) without entering it as
												// we do not want to process this $attribute in the following
												// code block
						break;
				}

				if ($skipLoopFlag == 1) {
					$skipLoopFlag = 0; 		// resetting the loop flag
					continue;
				}

				if (isset($field[0])) {
					if (preg_match('/__/', $field[0]))
						list($columnId, $attribute) = explode("__", $field[0]);
					else {
						$columnId = 0;				// we need to set a non-existent column id so that the attribute would
											// not match in the database (as it is added from the Attributes tab)
											// and the if/else check will result in an INSERT instead of an UPDATE for the
											// the last attribute
						$attribute = $field[0];
					}
				}

		                if (isset($field[1]))
					$value = $field[1];
		                if (isset($field[2]))
					$op = $field[2];
		                if (isset($field[3]))
					$table = $field[3];

		                if ($table == 'check')
					$table = $configValues['CONFIG_DB_TBL_RADCHECK'];
		                if ($table == 'reply')
					$table = $configValues['CONFIG_DB_TBL_RADREPLY'];

				if ( (!($value)) || (!($attribute)) )
					continue;

				$counter = 0;

				// because the $value[0] which is the attribute value is later manually appended the '' so that
				// password policies are enforced by the php server we need to perform the secure method escapeSimple()
				// at an early point in the script.
				$value = $dbSocket->escapeSimple($value);

				// we set the $password variable to the attribute value only if that attribute is actually a password attribute indeed
				// and this has to be done because we're looping on all attributes that were submitted with the form
				switch($attribute) {
					case "User-Password":
					case "CHAP-Password":
					case "Cleartext-Password":
					case "Crypt-Password":
					case "MD5-Password":
					case "SHA1-Password":
						$value = "'$value'";
						$passwordAttribute = 1;	// if this is a password
						break;					// attribute then we tag it
												// as true
					default:
						$value = "'$value'";
						$passwordAttribute = 0;
				}

				// first we check that the config option is actually set and available in the config file
				if ( (isset($configValues['CONFIG_DB_PASSWORD_ENCRYPTION'])) and ($passwordAttribute == 1) ) {
					// if so we need to use different function for each encryption type and so we force it here
					$passwordOrig = "'$passwordOrig'";
					switch($configValues['CONFIG_DB_PASSWORD_ENCRYPTION']) {
						case "cleartext":
							if ( ($value != $passwordOrig) )
								$value = "$value";
							break;
						case "crypt":
							if ( ($value != $passwordOrig) )
								$value = "ENCRYPT($value, 'SALT_DALORADIUS')";
							break;
						case "md5":
							if ( ($value != $passwordOrig) )
								$value = "MD5($value)";
							break;
					}
				}

				/* we can't simply UPDATE because it might be that the attribute
				doesn't exist at all and we need to insert it.
				for this reason we need to check if it exists or not, if exists we update, if not we insert
				*/

				$sql = "SELECT Attribute FROM $table WHERE UserName='".$dbSocket->escapeSimple($username).
					"' AND Attribute='".$dbSocket->escapeSimple($attribute)."' AND id=".$dbSocket->escapeSimple($columnId);

				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				if ($res->numRows() == 0) {
					/* if the returned rows equal 0 meaning this attribute is not found and we need to add it */
					$sql = "INSERT INTO $table (id,Username,Attribute,op,Value) ".
						" VALUES (0,'".$dbSocket->escapeSimple($username)."', '".
						$dbSocket->escapeSimple($attribute)."', '".$dbSocket->escapeSimple($op).
						"', $value)";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				} else {
					/* we update the $value[0] entry which is the attribute's value */
					$sql = "UPDATE $table SET Value=$value WHERE UserName='".
						$dbSocket->escapeSimple($username)."' AND Attribute='".
						$dbSocket->escapeSimple($attribute)."' AND id=".$dbSocket->escapeSimple($columnId);
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";

					/* then we update $value[1] which is the attribute's operator */
					$sql = "UPDATE $table SET Op='".$dbSocket->escapeSimple($op).
						"' WHERE UserName='".$dbSocket->escapeSimple($username).
						"' AND Attribute='".$dbSocket->escapeSimple($attribute).
						"' AND id=".$dbSocket->escapeSimple($columnId);
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}

				$counter++;
				$password = "";		// we MUST reset the $password variable to nothing  so that it's not kepy in the loop and will repeat itself as the value to set

	        } //foreach $_POST

			$successMsg = "Updated attributes for: <b> $username </b>";
			$logAction .= "Successfully updates attributes for user [$username] on page: ";

		} else { // if username != ""
			$failureMsg = "no user was entered, please specify a username to edit";
			$logAction .= "Failed updating attributes for user [$username] on page: ";
		}
	} // if isset post submit


	if (isset($_REQUEST['username']))
		$username = trim($_REQUEST['username']);
	else
		$username = "";

	if (trim($username) != "") {
		$username = trim($_REQUEST['username']);
	} else {
		$failureMsg = "no user was entered, please specify a username to edit";
	}

	$edit_username = $username; //feed the sidebar variables



	/* an sql query to retrieve the password for the username to use in the quick link for the user test connectivity
	*/
	$sql = "SELECT Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='".
		$dbSocket->escapeSimple($username)."' AND Attribute like '%Password'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	$row = $res->fetchRow();
	$user_password = $row[0];


	/* fill-in all the user info details */
	$sql = "SELECT firstname, lastname, email, department, company, workphone, homephone, mobilephone, address, city, state, country, zip, notes, ".
		" changeuserinfo, portalloginpassword, enableportallogin, creationdate, creationby, updatedate, updateby FROM ".
		$configValues['CONFIG_DB_TBL_DALOUSERINFO'].
		" WHERE UserName='".
		$dbSocket->escapeSimple($username)."'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	$row = $res->fetchRow();

	$ui_firstname = $row[0];
	$ui_lastname = $row[1];
	$ui_email = $row[2];
	$ui_department = $row[3];
	$ui_company = $row[4];
	$ui_workphone = $row[5];
	$ui_homephone = $row[6];
	$ui_mobilephone = $row[7];
	$ui_address = $row[8];
	$ui_city = $row[9];
	$ui_state = $row[10];
	$ui_country = $row[11];
	$ui_zip = $row[12];
	$ui_notes = $row[13];
	$ui_changeuserinfo = $row[14];
	$ui_PortalLoginPassword = $row[15];
	$ui_enableUserPortalLogin = $row[16];
	$ui_creationdate = $row[17];
	$ui_creationby = $row[18];
	$ui_updatedate = $row[19];
	$ui_updateby = $row[20];

	/* fill-in all the user bill info details */
	$sql = "SELECT ".
               " planName, contactperson, company, email, phone, ".
               " address, city, state, country, zip, ".
               " paymentmethod, cash, creditcardname, creditcardnumber, creditcardverification, creditcardtype, creditcardexp, ".
               " notes, changeuserbillinfo, ".
               " `lead`, coupon, ordertaker, billstatus, lastbill, nextbill, nextinvoicedue, billdue, postalinvoice, faxinvoice, emailinvoice, ".
               " creationdate, creationby, updatedate, updateby FROM ".
		$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
		" WHERE UserName='".
		$dbSocket->escapeSimple($username)."'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	$bi_contactperson = $row['contactperson'];
	$bi_planname = $row['planName'];
	$bi_company = $row['company'];
	$bi_email = $row['email'];
	$bi_phone = $row['phone'];
	$bi_address = $row['address'];
	$bi_city = $row['city'];
	$bi_state = $row['state'];
	$bi_country = $row['country'];
	$bi_zip = $row['zip'];
	$bi_paymentmethod = $row['paymentmethod'];
	$bi_cash = $row['cash'];
	$bi_creditcardname = $row['creditcardname'];
	$bi_creditcardnumber = $row['creditcardnumber'];
	$bi_creditcardverification = $row['creditcardverification'];
	$bi_creditcardtype = $row['creditcardtype'];
	$bi_creditcardexp = $row['creditcardexp'];
	$bi_notes = $row['notes'];
    $bi_lead = $row['lead'];
    $bi_coupon = $row['coupon'];
    $bi_ordertaker = $row['ordertaker'];
    $bi_billstatus = $row['billstatus'];
    $bi_lastbill = $row['lastbill'];
    $bi_nextbill = $row['nextbill'];
    $bi_nextinvoicedue = $row['nextinvoicedue'];
    $bi_billdue = $row['billdue'];
    $bi_postalinvoice = $row['postalinvoice'];
    $bi_faxinvoice = $row['faxinvoice'];
    $bi_emailinvoice = $row['emailinvoice'];
	$bi_changeuserbillinfo = $row['changeuserbillinfo'];
	$bi_creationdate = $row['creationdate'];
	$bi_creationby = $row['creationby'];
	$bi_updatedate = $row['updatedate'];
	$bi_updateby = $row['updateby'];

	include 'library/closedb.php';

	include_once('library/config_read.php');
	$log = "visited page: ";

?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->


</head>

<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>

<script type="text/javascript" src="library/javascript/ajax.js"></script>
<script type="text/javascript" src="library/javascript/dynamic_attributes.js"></script>
<script type="text/javascript" src="library/javascript/ajaxGeneric.js"></script>

<script type="text/javascript">


function disableUser() {
	strUsername = "username=<?php echo urlencode($username)?>";
	if (confirm("You are about to disable this user account\nDo you want to continue?"))  {
		ajaxGeneric("include/management/userOperations.php","userDisable","returnMessages",strUsername);
		window.location.reload();
		return true;
	}
}

function enableUser() {
	strUsername = "username=<?php echo urlencode($username)?>";
	if (confirm("You are about to enable this user account\nDo you want to continue?"))  {
		ajaxGeneric("include/management/userOperations.php","userEnable","returnMessages",strUsername);
		window.location.reload();
		return true;
	}
}


</script>

<?php
	include_once ("library/tabber/tab-layout.php");
?>

<?php
	include ("menu-mng-users.php");
?>

<div id="contentnorightbar">

	<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngedit.php') ?>
	:: <?php if (isset($username)) { echo $username; } ?><h144>&#x2754;</h144></a></h2>

	<div id="helpPage" style="display:none;visibility:visible" >
		<?php echo t('helpPage','mngedit') ?>
		<br/>
	</div>
	<?php
		include_once('include/management/actionMessages.php');
	?>

	<?php
		include_once('include/management/userOperations.php');
		checkDisabled($username);
	?>

	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

	<input type="hidden" value="<?php echo $username ?>" name="username" />

	<div class="tabber">
 <div class="tabbertab" title="<?php echo t('title','AccountInfo'); ?>">

	<fieldset>

                <h302> <?php echo t('title','AccountInfo'); ?> </h302>

                <ul>

                <div id='UserContainer'>
                <li class='fieldset'>
                <label for='username' class='form'><?php echo t('all','Username')?></label>
		<input name='username' type='hidden' value='<?php if (isset($username)) echo $username ?>' />
                <input name='username' type='text' id='username' value='<?php if (isset($username)) echo $username ?>' disabled tabindex=100 />
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('usernameTooltip')" />

                <div id='usernameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','usernameTooltip') ?>
                </div>
                </li>

                <li class='fieldset'>
                <label for='password' class='form'><?php echo t('all','Password')?></label>
                <input name='password' type='text' id='password' value='<?php if (isset($user_password)) echo $user_password ?>'
                        <?php if (isset($hiddenPassword)) echo $hiddenPassword ?> disabled tabindex=101 />
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('passwordTooltip')" />

                <div id='passwordTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','passwordTooltip') ?>
                </div>
                </li>
                </div>



		<li class='fieldset'>
		<label for='planName' class='form'><?php echo t('all','PlanName') ?></label>
		<input name='oldplanName' type='hidden' value='<?php if (isset($bi_planname)) echo $bi_planname ?>' />
                <?php
 	               include 'include/management/populate_selectbox.php';
                       populate_plans($bi_planname,"planName","form");
                ?>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planNameTooltip')" />

		<div id='planNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','planNameTooltip') ?>
		</div>
		</li>


		<li class='fieldset'>
		<br/><br/>
		<hr><br/>

<?php
	include 'include/management/buttons.php';
?>

		<br/>

			<input class='button' type='button' value='Enable User'
				onClick='javascript:enableUser()' />

			<input class='button' type='button' value='Disable User'
				onClick='javascript:disableUser()' />

		<br/><br/>
		<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000 class='button' />

		<div style="float: right; text-align: right;">
				<a href="<?php echo $_SESSION['PREV_LIST_PAGE']; ?>">Back to Listing Page</a>
		</div>

		</li>

		</ul>

	</fieldset>

	</div>


		<div class="tabbertab" title="<?php echo t('title','RADIUSCheck'); ?>">

		<fieldset>

			<h302> <?php echo t('title','RADIUSCheck'); ?> </h302>
			<br/>

			<ul>
<?php

	include 'library/opendb.php';
	include_once('include/management/pages_common.php');
	include_once('include/management/populate_selectbox.php');

	$editCounter = 0;

	$sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute, ".
		$configValues['CONFIG_DB_TBL_RADCHECK'].".op, ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Value, ".
		$configValues['CONFIG_DB_TBL_DALODICTIONARY'].".Type, ".
		$configValues['CONFIG_DB_TBL_DALODICTIONARY'].".RecommendedTooltip, ".
		$configValues['CONFIG_DB_TBL_RADCHECK'].".id ".
		" FROM ".
		$configValues['CONFIG_DB_TBL_RADCHECK']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALODICTIONARY'].
		" ON ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute=".
		$configValues['CONFIG_DB_TBL_DALODICTIONARY'].".attribute ".
                " AND ".$configValues['CONFIG_DB_TBL_DALODICTIONARY'].".Value IS NULL ".
		" WHERE ".
		$configValues['CONFIG_DB_TBL_RADCHECK'].".UserName='".$dbSocket->escapeSimple($username)."'";

	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	if ($numrows = $res->numRows() == 0) {
		echo "<center>";
		echo t('messages','noCheckAttributesForUser');
		echo "</center>";
	}

	while($row = $res->fetchRow()) {

		echo "<label class='attributes'>";
		echo "<a class='tablenovisit' href='mng-del.php?username=$username&attribute=$row[5]__$row[0]&tablename=radcheck'>
				<img src='images/icons/delete.png' border=0 alt='Remove' /> </a>";
		echo "</label>";
		echo "<label for='attribute' class='attributes'>&nbsp;&nbsp;&nbsp;$row[0]</label>";

		echo "<input type='hidden' name='editValues".$editCounter."[]' value='$row[5]__$row[0]' />";

		if (preg_match("/.*-Password/", $row[0])) {
			if ($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] == "yes") {
				echo "<input type='password' value='$row[2]' name='editValues".$editCounter."[]'  style='width: 115px' />";
				echo "<input type='hidden' value='$row[2]' name='passwordOrig' />";
			} else {
				echo "<input type='text' value='$row[2]' name='editValues".$editCounter."[]'  style='width: 115px' />";
				echo "<input type='hidden' value='$row[2]' name='passwordOrig' />";
			}
		} else {
			echo "<input value='$row[2]' name='editValues".$editCounter."[]' style='width: 115px' />";
		}
		echo "&nbsp;";
		echo "<select name='editValues".$editCounter."[]' style='width: 45px' class='form'>";
		echo "<option value='$row[1]'>$row[1]</option>";
		drawOptions();
		echo "</select>";

		echo "<input type='hidden' name='editValues".$editCounter."[]' value='radcheck' style='width: 90px'>";

		$editCounter++;			// we increment the counter for the html elements of the edit attributes


		if (!$row[3])
			$row[3] = "unavailable";
		if (!$row[4])
			$row[4] = "unavailable";

		printq("
			<img src='images/icons/comment.png' alt='Tip' border='0' onClick=\"javascript:toggleShowDiv('$row[0]Tooltip')\" />
			<br/>
	                <div id='$row[0]Tooltip'  style='display:none;visibility:visible' class='ToolTip2'>
	                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<i><b>Type:</b> $row[3]</i><br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<i><b>Tooltip Description:</b> $row[4]</i><br/>
				<br/>
	                </div>
		");

	}

?>
			<br/><br/>
			<hr><br/>

			<br/>
			<input type='submit' name='submit' value='<?php echo t('buttons','apply')?>' class='button' />
			<br/>

			</ul>

		</fieldset>
	</div>

	<div class='tabbertab' title='<?php echo t('title','RADIUSReply')?>' >

	<fieldset>

		<h302> <?php echo t('title','RADIUSReply'); ?> </h302>
		<br/>

		<ul>

<?php

	$sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADREPLY'].".Attribute, ".
		$configValues['CONFIG_DB_TBL_RADREPLY'].".op, ".$configValues['CONFIG_DB_TBL_RADREPLY'].".Value, ".
		$configValues['CONFIG_DB_TBL_DALODICTIONARY'].".Type, ".
		$configValues['CONFIG_DB_TBL_DALODICTIONARY'].".RecommendedTooltip, ".
		$configValues['CONFIG_DB_TBL_RADREPLY'].".id ".
		" FROM ".
		$configValues['CONFIG_DB_TBL_RADREPLY']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALODICTIONARY'].
		" ON ".$configValues['CONFIG_DB_TBL_RADREPLY'].".Attribute=".
		$configValues['CONFIG_DB_TBL_DALODICTIONARY'].".attribute ".
                " AND ".$configValues['CONFIG_DB_TBL_DALODICTIONARY'].".Value IS NULL ".
		" WHERE ".
		$configValues['CONFIG_DB_TBL_RADREPLY'].".UserName='".$dbSocket->escapeSimple($username)."'";


	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	if ($numrows = $res->numRows() == 0) {
		echo "<center>";
		echo t('messages','noReplyAttributesForUser');
		echo "</center>";
	}

	while($row = $res->fetchRow()) {

		echo "<label class='attributes'>";
		echo "<a class='tablenovisit' href='mng-del.php?username=$username&attribute=$row[5]__$row[0]&tablename=radreply'>
				<img src='images/icons/delete.png' border=0 alt='Remove' /> </a>";
		echo "</label>";
                echo "<label for='attribute' class='attributes'>&nbsp;&nbsp;&nbsp;$row[0]</label>";

		echo "<input type='hidden' name='editValues".$editCounter."[]' value='$row[5]__$row[0]' />";

		if ( ($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] == "yes") and (preg_match("/.*-Password/", $row[0])) ) {
			echo "<input type='password' value='$row[2]' name='editValues".$editCounter."[]'  style='width: 115px' />";
			echo "&nbsp;";
			echo "<select name='editValues".$editCounter."[]' style='width: 45px' class='form'>";
			echo "<option value='$row[1]'>$row[1]</option>";
			drawOptions();
			echo "</select>";
		} else {
			echo "<input value='$row[2]' name='editValues".$editCounter."[]' style='width: 115px' />";
			echo "&nbsp;";
			echo "<select name='editValues".$editCounter."[]' style='width: 45px' class='form'>";
			echo "<option value='$row[1]'>$row[1]</option>";
			drawOptions();
			echo "</select>";
		}

		echo "<input type='hidden' name='editValues".$editCounter."[]' value='radreply' style='width: 90px'>";
		$editCounter++;			// we increment the counter for the html elements of the edit attributes

		if (!$row[3])
			$row[3] = "unavailable";
		if (!$row[4])
			$row[4] = "unavailable";

		printq("
			<img src='images/icons/comment.png' alt='Tip' border='0' onClick=\"javascript:toggleShowDiv('$row[0]Tooltip')\" />
			<br/>
	                <div id='$row[0]Tooltip'  style='display:none;visibility:visible' class='ToolTip2'>
	                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<i><b>Type:</b> $row[3]</i><br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<i><b>Tooltip Description:</b> $row[4]</i><br/>
				<br/>
	                </div>
		");

	}

?>
        <br/><br/>
        <hr><br/>

        <br/>
        <input type='submit' name='submit' value='<?php echo t('buttons','apply')?>' class='button' />
        <br/>

	</ul>

        </fieldset>
    </div>

<?php
    include 'library/closedb.php';
?>


     <div class="tabbertab" title="<?php echo t('title','UserInfo'); ?>">
        <?php
                $customApplyButton = "<input type='submit' name='submit' value=".t('buttons','apply')." class='button' />";
                include_once('include/management/userinfo.php');
        ?>
     </div>

        <div class="tabbertab" title="<?php echo t('title','BillingInfo'); ?>">
        <?php
                $customApplyButton = "<input type='submit' name='submit' value=".t('buttons','apply')." class='button' />";
                include_once('include/management/userbillinfo.php');
        ?>
        </div>

     <div class="tabbertab" title="<?php echo t('title','Attributes'); ?>">
        <?php
                include_once('include/management/attributes.php');
        ?>
     </div>

     <div class="tabbertab" title="<?php echo t('title','Groups'); ?>">

<?php
        include 'library/opendb.php';
        include_once('include/management/groups.php');
        include 'library/closedb.php';
?>

	</ul>

	<br/>
        <h301> Assign New Groups </h301>
        <br/>
	<ul>

        <li class='fieldset'>

                <li class='fieldset'>
                <label for='group' class='form'><?php echo t('all','Group')?></label>
                <?php
                        include_once 'include/management/populate_selectbox.php';
                        populate_groups("Select Groups","newgroups[]");
                ?>

                <a class='tablenovisit' href='#'
                        onClick="javascript:ajaxGeneric('include/management/dynamic_groups.php','getGroups','divContainerGroups',genericCounter('divCounter')+'&elemName=newgroups[]');">Add</a>

                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('group')" />

                <div id='divContainerGroups'>
                </div>


                <div id='groupTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','groupTooltip') ?>
                </div>
                </li>



	<br/><br/>

        <br/>
        <hr><br/>
        <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />
        </li>

        </ul>

        </fieldset>
        <br/>

     </div>

</div>

	</form>


<?php
	include_once('include/management/userReports.php');
	userPlanInformation($username, 1);
	userSubscriptionAnalysis($username, 1);			// userSubscriptionAnalysis with argument set to 1 for drawing the table
	userConnectionStatus($username, 1);			// userConnectionStatus (same as above)
?>

<?php
	include('include/config/logging.php');
?>

	</div>

	<div id="footer">

<?php
       include 'page-footer.php';
?>


	</div>

</div>
</div>

</body>
</html>
