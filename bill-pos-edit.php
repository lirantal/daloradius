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

    isset($_REQUEST['username']) ? $username = $_REQUEST['username'] : $username = "";
    isset($_POST['password']) ? $password = $_POST['password'] : $password = "";
	isset($_POST['oldplanName']) ? $oldplanName = $_POST['oldplanName'] : $oldplanName = "";
	isset($_POST['planName']) ? $planName = $_POST['planName'] : $planName = "";
	isset($_POST['profiles']) ? $profiles = $_POST['profiles'] : $profiles = "";
    isset($_POST['passwordType']) ? $passwordtype = $_POST['passwordType'] : $passwordtype = "";
    isset($_POST['reassignplanprofiles']) ? $reassignplanprofiles = $_POST['reassignplanprofiles'] : $reassignplanprofiles = "";
    

    isset($_POST['bi_contactperson']) ? $bi_contactperson = $_POST['bi_contactperson'] : $bi_contactperson = "";
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

    isset($_POST['firstname']) ? $firstname = $_POST['firstname'] : $firstname = "";
    isset($_POST['lastname']) ? $lastname = $_POST['lastname'] : $lastname = "";
    isset($_POST['email']) ? $email = $_POST['email'] : $email = "";
    isset($_POST['department']) ? $department = $_POST['department'] : $department = "";
    isset($_POST['company']) ? $company = $_POST['company'] : $company = "";
    isset($_POST['workphone']) ? $workphone = $_POST['workphone'] : $workphone = "";
    isset($_POST['homephone']) ? $homephone = $_POST['homephone'] :  $homephone = "";
    isset($_POST['mobilephone']) ? $mobilephone = $_POST['mobilephone'] : $mobilephone = "";
    isset($_POST['address']) ? $address = $_POST['address'] : $address = "";
    isset($_POST['city']) ? $city = $_POST['city'] : $city = "";
    isset($_POST['state']) ? $state = $_POST['state'] : $state = "";
    isset($_POST['zip']) ? $zip = $_POST['zip'] : $zip = "";
    isset($_POST['notes']) ? $notes = $_POST['notes'] : $notes = "";
    isset($_POST['changeUserInfo']) ? $ui_changeuserinfo = $_POST['changeUserInfo'] : $ui_changeuserinfo = "0";
	isset($_POST['enableUserPortalLogin']) ? $ui_enableUserPortalLogin = $_POST['enableUserPortalLogin'] : $ui_enableUserPortalLogin = "0";
	isset($_POST['portalLoginPassword']) ? $ui_PortalLoginPassword = $_POST['portalLoginPassword'] : $ui_PortalLoginPassword = "";

	$logAction = "";
	$logDebugSQL = "";
	
	function addPlanProfile($dbSocket, $username, $planName, $oldplanName) {

        global $logDebugSQL;
        global $configValues;

        $sql = "DELETE FROM ". $configValues['CONFIG_DB_TBL_RADUSERGROUP'] ." WHERE UserName='".
				$dbSocket->escapeSimple($username)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";        
        
		// search to see if the plan is associated with any profiles
		$sql = "SELECT profile_name FROM ".
				$configValues['CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES'].
				" WHERE plan_name='$planName'";
		// $res is an array of all profiles associated with this plan
		$res = $dbSocket->getCol($sql);
		
		// if the profile list for this plan isn't empty, we associate it with the user
		if (count($res) != 0) {
	
			// if profiles are associated with this plan, loop through each and add a usergroup entry for each
			foreach($res as $profile_name) {
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." (UserName,GroupName,priority) ".
					" VALUES ('".$dbSocket->escapeSimple($username)."','$profile_name','0')";
				$res = $dbSocket->query($sql);
			}
		
		}
		
	}
	
	
	function addUserProfiles($dbSocket, $username, $planName, $oldplanName, $groups, $groups_priority, $newgroups) {

        global $logDebugSQL;
        global $configValues;
        
		// update usergroup mapping (existing)
        if ($groups) {

			$sql = "DELETE FROM ". $configValues['CONFIG_DB_TBL_RADUSERGROUP'] ." WHERE UserName='".
					$dbSocket->escapeSimple($username)."'";
			$res = $dbSocket->query($sql);
	        $logDebugSQL .= $sql . "\n";
	
	        $grpcnt = 0;                    // group counter
	        foreach ($groups as $group) {
	
	        	if (!($groups_priority[$grpcnt]))
	            	$group_priority = 0;
	            else
	            	$group_priority = $groups_priority[$grpcnt];
	
				if (trim($group) != "") {
	            	$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." (UserName,GroupName,priority) ".
	                			" VALUES ('".$dbSocket->escapeSimple($username)."', '".$dbSocket->escapeSimple($group)."', ".
					$dbSocket->escapeSimple($group_priority).")";
	                $res = $dbSocket->query($sql);
	            }
	
				$logDebugSQL .= $sql . "\n";
	
				// we increment group index count so we can access the group priority array
	           	$grpcnt++;
	           	
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
           
	}
	
	

	include 'library/opendb.php';

	if (isset($_POST["submit"])) {

                $currDate = date('Y-m-d H:i:s');                        // current date and time to enter as creationdate field
                $currBy = $_SESSION['operator_user'];

                isset ($_POST['newgroups']) ? $newgroups = $_POST['newgroups'] : $newgroups = "";
                isset ($_POST['groups']) ? $groups = $_POST['groups'] : $groups = "";
                isset ($_POST['groups_priority']) ? $groups_priority = $_POST['groups_priority'] : $groups_priority = "";
		
//                global $username;
//                global $password;
//                global $passwordtype;

				/* update user information and user billing information */

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
                                        " notes, changeuserinfo, portalloginpassword, enableportallogin, creationdate, creationby, updatedate, updateby) ".
                                        " VALUES (0, '".$dbSocket->escapeSimple($username)."', '".
                                        $dbSocket->escapeSimple($firstname)."', '".$dbSocket->escapeSimple($lastname)."', '".
                                        $dbSocket->escapeSimple($email)."','".$dbSocket->escapeSimple($department)."', '".
                                        $dbSocket->escapeSimple($company)."', '".$dbSocket->escapeSimple($workphone)."','".
	                                $dbSocket->escapeSimple($homephone)."', '".$dbSocket->escapeSimple($mobilephone)."', '".
                                        $dbSocket->escapeSimple($address)."', '".$dbSocket->escapeSimple($city)."', '".
                                        $dbSocket->escapeSimple($state)."', '".$dbSocket->escapeSimple($zip)."', '".
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
                                        " (id, planname, username, contactperson, company, email, phone, ".
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
                                        "', lead='".$dbSocket->escapeSimple($bi_lead).
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

            if ($reassignplanprofiles == 1) {
   	            // if the user chose to re-assign profiles from the change of plan then we proceed with removing
   	            // all profiles associated with the user and re-assigning them based on the plan's profiles associations
				addPlanProfile($dbSocket, $username, $planName, $oldplanName);
            } else {
            	// otherwise, we remove all profiles and assign profiles as configured in the profiles tab by the user
            	addUserProfiles($dbSocket, $username, $planName, $oldplanName, $groups, $groups_priority, $newgroups);
            }

/*
	
	                $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='".
	                                $dbSocket->escapeSimple($username)."'";
	                $res = $dbSocket->query($sql);
	                $logDebugSQL .= $sql . "\n";
	
	                if ($res->numRows() == 0) {
				if (trim($username) != "" and trim($password) != "") {
	
	                                        // we need to perform the secure method escapeSimple on $dbPassword early because as seen below
	                                        // we manipulate the string and manually add to it the '' which screw up the query if added in $sql
	                                        $password = $dbSocket->escapeSimple($password);
	
	                                        switch($configValues['CONFIG_DB_PASSWORD_ENCRYPTION']) {
	                                                case "cleartext":
	                                                        $dbPassword = "'$password'";
	                                                        break;
	                                                case "crypt":
	                                                        $dbPassword = "ENCRYPT('$password')";
	                                                        break;
	                                                case "md5":
	                                                        $dbPassword = "MD5('$password')";
	                                                        break;
	                                                default:
	                                                        $dbPassword = "'$password'";
	                                        }
	
	                                        // at this stage $dbPassword contains the password string encapsulated by '' and either uses
	                                        // a function to encrypt it like ENCRYPT or it doesn't, it's based on the configuration
	                                        // but here we provide another stage, for Crypt-Password and MD5-Password it's obvious
	                                        // that the password need be encrypted so even if this option is not in the configuration
	                                        // we enforce it.
	
	                                        // we first check if the password attribute is to be encrypted at all
	                                        if (preg_match("/crypt/i", $passwordtype)) {
	                                                // if we don't find the encrypt function even though we identified
	                                                // a Crypt-Password attribute
	                                                if (!(preg_match("/encrypt/i",$dbPassword))) {
	                                                        $dbPassword = "ENCRYPT('$password')";
	                                                }
	
	                                                // we now perform the same check but for an MD5-Password attribute
	                                        } elseif (preg_match("/md5/i", $passwordtype)) {
	                                                // if we don't find the md5 function even though we identified
	                                                // a MD5-Password attribute
	                                                if (!(preg_match("/md5/i",$dbPassword))) {
	                                                        $dbPassword = "MD5('$password')";
	                                                }
	                                        }
	
	                                        // insert username/password
	                                        $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,Username,Attribute,op,Value) ".
                                                        " VALUES (0, '".$dbSocket->escapeSimple($username)."', '".$dbSocket->escapeSimple($passwordtype).
                                                        "', ':=', $dbPassword)";
	                                        $res = $dbSocket->query($sql);
	                                        $logDebugSQL .= $sql . "\n";
	
//						addPlanProfile($dbSocket, $username, $planName);

	
	                                        $successMsg = "Added to database new user: <b> $username </b>";
	                                        $logAction .= "Successfully added new user [$username] on page: ";
				} else {
	                                        $failureMsg = "username or password are empty";
	                                        $logAction .= "Failed adding (possible empty user/pass) new user [$username] on page: ";
				}
			} else { 
	                        $failureMsg = "user already exist in database: <b> $username </b>";
	                        $logAction .= "Failed adding new user already existing in database [$username] on page: ";
			}

*/

		} // if username

	}

	$edit_username = $dbSocket->escapeSimple($username);


	/* fill-in password field for username */
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
               " id, planName, contactperson, company, email, phone, ".
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

        $user_id = $row['id'];
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

        if ($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] == "yes")
                $hiddenPassword = "type=\"password\"";

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
</head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script src="library/javascript/productive_funcs.js" type="text/javascript"></script>
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

function refillSessionTime() {
	strUsername = "username=<?php echo urlencode($username)?>";
	if (confirm("You are about to refill session time for this user account\nDo you want to continue?\n\nSuch action will also bill the user if set so in the plant the user is associated with!"))  {
		ajaxGeneric("include/management/userOperations.php","refillSessionTime","returnMessages",strUsername);
		window.location.reload();
		return true;	
    }
}


function refillSessionTraffic() {
	strUsername = "username=<?php echo urlencode($username)?>";
	if (confirm("You are about to refill session traffic for this user account\nDo you want to continue?\n\nSuch action will also bill the user if set so in the plant the user is associated with!"))  {
		ajaxGeneric("include/management/userOperations.php","refillSessionTraffic","returnMessages",strUsername);
		window.location.reload();
		return true;	
    }
}


</script>

<?php
	include_once ("library/tabber/tab-layout.php");
?>
 
<?php

	include ("menu-bill-pos.php");
	
?>

<div id="contentnorightbar">

	<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','billposedit.php') ?>
	<h144>&#x2754;</h144></a></h2>
	
	<div id="helpPage" style="display:none;visibility:visible" >
		<?php echo t('helpPage','billposedit') ?>
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

<div class="tabber">

     <div class="tabbertab" title="<?php echo t('title','AccountInfo'); ?>">

	<fieldset>

				<?php
					include_once('include/management/populate_selectbox.php');
				?>
				
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
		                       populate_plans("$bi_planname","planName","form", NULL, $bi_planname);
		                ?>
				<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planNameTooltip')" /> 
				
				<div id='planNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
					<img src='images/icons/comment.png' alt='Tip' border='0' />
					<?php echo t('Tooltip','planNameTooltip') ?>
				</div>
				</li>
	

                <div id='UserContainer'>
                <li class='fieldset'>
                <label for='reassignplanprofiles' class='form'><?php echo t('button','ReAssignPlanProfiles') ?></label>
				<input name='reassignplanprofiles' type='checkbox' value='1' />
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('reassignplanprofiles')" />

                <div id='reassignplanprofiles'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','reassignplanprofiles') ?>
                </div>
                </li>
		
	
	

		<li class='fieldset'>
		<br/>
		<hr><br/>
			<br/>
			
	        <input class='button' type='button' value='Refill Session Time'
				onClick='javascript:refillSessionTime()' />
	        <input class='button' type='button' value='Refill Session Traffic'
				onClick='javascript:refillSessionTraffic()' />
			
			<br/>
			
			<input class='button' type='button' value='Enable User'
				onClick='javascript:enableUser()' />
				
			<input class='button' type='button' value='Disable User'
				onClick='javascript:disableUser()' />
				
			<br/>		
			
			<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000 class='button' />

		</li>

		</ul>

	</fieldset>

	</div>


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



     <div class="tabbertab" title="<?php echo t('title','Profiles'); ?>">

<?php
        include 'library/opendb.php';
		$groupTerminology = "Profile";
		$groupTerminologyPriority = "ProfilePriority";
        include_once('include/management/groups.php');
        include 'library/closedb.php';
?>

        </ul>

        <br/>
        <h301> Assign New Profiles </h301>
        <br/>
        <ul>

        <li class='fieldset'>

                <li class='fieldset'>
                <label for='profile' class='form'><?php echo t('all','Profile')?></label>
                <?php
                        populate_groups("Select Profile","newgroups[]");
                ?>

                <a class='tablenovisit' href='#'
                        onClick="javascript:ajaxGeneric('include/management/dynamic_groups.php','getGroups','divContainerProfiles',genericCounter('divCounter')+'&elemName=newgroups[]');">Add</a>

                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('groupTooltip')" />

                <div id='divContainerProfiles'>
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



        <div class="tabbertab" title="<?php echo t('title','Invoices'); ?>">
        <?php
                include_once('include/management/userBilling.php');
                userInvoicesStatus($user_id, 1);
        ?>
        </div>


</div>

	</form>


<?php
        include_once('include/management/userReports.php');
        userPlanInformation($username, 1);
        userSubscriptionAnalysis($username, 1);                 // userSubscriptionAnalysis with argument set to 1 for drawing the table
        userConnectionStatus($username, 1);                     // userConnectionStatus (same as above)
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





