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
	include('include/management/pages_common.php');

	
	/* variables for batch_history */
	isset($_POST['batch_name']) ? $batch_name = $_POST['batch_name'] : $batch_name = "";
	isset($_POST['batch_description']) ? $batch_description = $_POST['batch_description'] : $batch_description = "";
	isset($_POST['hotspot_id']) ? $hotspot_id = $_POST['hotspot_id'] : $hotspot_id = "";
	
	/* variables for userbillinfo */
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
	isset($_POST['bi_postalinvoice']) ? $bi_postalinvoice = $_POST['bi_postalinvoice'] : $bi_postalinvoice = "";
	isset($_POST['bi_faxinvoice']) ? $bi_faxinvoice = $_POST['bi_faxinvoice'] : $bi_faxinvoice = "";
	isset($_POST['bi_emailinvoice']) ? $bi_emailinvoice = $_POST['bi_emailinvoice'] : $bi_emailinvoice = "";
	isset($_POST['bi_batch_id']) ? $bi_batch_id = $_POST['bi_batch_id'] : $bi_batch_id = "";
	isset($_POST['changeUserBillInfo']) ? $bi_changeuserbillinfo = $_POST['changeUserBillInfo'] : $bi_changeuserbillinfo = "0";

	/* variables for userinfo */
	isset($_POST['firstname']) ? $firstname = $_POST['firstname'] : $firstname = "";
	isset($_POST['lastname']) ? $lastname = $_POST['lastname'] : $lastname = "";
	isset($_POST['email']) ? $email = $_POST['email'] : $email = "";
	isset($_POST['department']) ? $department = $_POST['department'] : $department = "";
	isset($_POST['company']) ? $company = $_POST['company'] : $company = "";
	isset($_POST['workphone']) ? $workphone = $_POST['workphone'] : $workphone = "";
	isset($_POST['homephone']) ? $homephone = $_POST['homephone'] :  $homephone = "";
	isset($_POST['mobilephone']) ? $mobilephone = $_POST['mobilephone'] : $mobilephone = "";
	isset($_POST['address']) ? $ui_address = $_POST['address'] : $ui_address = "";
	isset($_POST['city']) ? $ui_city = $_POST['city'] : $ui_city = "";
	isset($_POST['state']) ? $ui_state = $_POST['state'] : $ui_state = "";
	isset($_POST['country']) ? $ui_country = $_POST['country'] : $ui_country = "";
	isset($_POST['zip']) ? $ui_zip = $_POST['zip'] : $ui_zip = "";
	isset($_POST['notes']) ? $notes = $_POST['notes'] : $notes = "";
	isset($_POST['changeUserInfo']) ? $ui_changeuserinfo = $_POST['changeUserInfo'] : $ui_changeuserinfo = "0";
	isset($_POST['enableUserPortalLogin']) ? $ui_enableUserPortalLogin = $_POST['enableUserPortalLogin'] : $ui_enableUserPortalLogin = "0";
	isset($_POST['portalLoginPassword']) ? $ui_PortalLoginPassword = $_POST['portalLoginPassword'] : $ui_PortalLoginPassword = "";

	$username_prefix = "";
	$number = "";
	$length_pass = "";
	$length_user = "";
	$pass_type = "";
	$group = "";
	$group_priority = "";

	$logAction = "";
	$logDebugSQL = "";
	
	if (isset($_POST['submit'])) {
		$username_prefix = $_POST['username_prefix'];
		$number = $_POST['number'];
		$length_pass = $_POST['length_pass'];
		(isset($_POST['length_user'])) ? $length_user = $_POST['length_user'] : $length_user = 0;
		$pass_type = $_POST['passwordType'];
		$group = $_POST['group'];
		$plan = $_POST['plan'];
		$group_priority = $_POST['group_priority'];
		
		(isset($_POST['startingIndex'])) ? $startingIndex = $_POST['startingIndex'] : $startingIndex = 0;
		$createBatchUsersType = $_POST['createBatchUsersType'];
		//$createRandomUsers = $_POST['createRandomUsers'];
		//$createIncrementUsers = $_POST['createIncrementUsers'];


		$currDate = date('Y-m-d H:i:s');			// current date and time to enter as creationdate field
		$currBy = $_SESSION['operator_user'];

		include 'library/opendb.php';

		// before looping through all generated batch users we create the batch_history entry
		// to associate the created users with a batch_history entry
		if (!empty($batch_name)) {
			$sql_batch_id = addUserBatchHistory($dbSocket);
		
			if ($sql_batch_id == 0) {
				// 0 may be returned in the case of failure in adding the batch_history record due
				// to SQL related issues or in case where there is a duplicate record of the batch_history,
				// meaning, the same batch_name is used to identify the batch entry
				$failureMsg = "Failure creating batch users due to an error or possible duplicate entry: <b> $batch_name </b>";
				$logAction .= "Failure creating a batch_history entry on page: ";
			}
			
		} else {
			$sql_batch_id = 0;
			
			$failureMsg = "Failure creating batch - please provide a batch name";
			$logAction .= "Failure creating batch - missing field [batch_name] on page: ";

		}
		
		
		$actionMsgBadUsernames = "";
		$actionMsgGoodUsernames = "";

		$exportCSV = "Username,Password||";
		
		for ($i=0; $i<$number; $i++) {
			
			// we do not create users and continue with the batch loop process
			// if batch_history creation failed.
			if ($sql_batch_id == 0)
				break;
			
			switch ($createBatchUsersType) {
				case "createRandomUsers":
					$username = createPassword($length_user, $configValues['CONFIG_USER_ALLOWEDRANDOMCHARS']);
					break;
					
				case "createIncrementUsers":
					$username = $startingIndex + $i;
					break;
			}


			// append the prefix to the username
			$username  = $username_prefix . $username;
			$password = createPassword($length_pass, $configValues['CONFIG_USER_ALLOWEDRANDOMCHARS']);

			$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='".
				$dbSocket->escapeSimple($username)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";

			if ($res->numRows() > 0) {
				$actionMsgBadUsernames = $actionMsgBadUsernames . $username . ", " ;
				$failureMsg = "skipping matching entry: <b> $actionMsgBadUsernames </b>";
			} else {
				
				// insert username/password
				$actionMsgGoodUsernames .= $username;
				if ($i+1 != $number)
					$actionMsgGoodUsernames .= ", ";

				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." VALUES (0, '".$dbSocket->escapeSimple($username)."',
				'".$dbSocket->escapeSimple($pass_type)."', ':=', '".$dbSocket->escapeSimple($password)."')";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
				
				
				// if a group was defined to add the user to in the form let's add it to the database
				if (isset($group)) {

					if (!($group_priority))
						$group_priority=0;		// if group priority wasn't set we
										// initialize it to 0 by default
					$sql = "INSERT INTO ". $configValues['CONFIG_DB_TBL_RADUSERGROUP'] ." VALUES ('".
						$dbSocket->escapeSimple($username)."', '".
						$dbSocket->escapeSimple($group)."', ".
						$dbSocket->escapeSimple($group_priority).") ";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}


				addUserInfo($dbSocket, $username);
				addUserBillInfo($dbSocket, $username, $sql_batch_id);
				
				foreach($_POST as $element=>$field) {

						// switch case to rise the flag for several $attribute which we do not
						// wish to process (ie: do any sql related stuff in the db)
						switch ($element) {

						case "username_prefix":
						case "passwordType":
						case "length_pass":
						case "length_user":
						case "number":
						case "plan":
						case "submit":
						case "group":
						case "group_priority":
						case "createBatchUsersType":
						case "startingIndex":
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
						case "bi_lead":
						case "bi_coupon":
						case "bi_ordertaker":
						case "bi_billstatus":
						case "bi_lastbill":
						case "bi_nextbill":
						case "bi_postalinvoice":
						case "bi_faxinvoice":
						case "bi_emailinvoice":
						case "bi_batch_id":
						case "changeUserBillInfo":
						case "changeUserInfo":
						case "batch_description":
						case "batch_name":
						case "hotspot":
						case "hotspot_id":
						case "copycontact":
						case "enableUserPortalLogin":
						case "portalLoginPassword":
								$skipLoopFlag = 1;      // if any of the cases above has been met weset a flag
														// to skip the loop (continue) without entering it as
														// we do not want to process this $attributein the following
														// code block
								break;
						}

						if ($skipLoopFlag == 1) {
								$skipLoopFlag = 0;              // resetting the loop flag
								continue;
						}


						if (isset($field[0]))
								$attribute = $field[0];
						if (isset($field[1]))
								$value = $field[1];
						if (isset($field[2]))
								$op = $field[2];
						if (isset($field[3]))
								$table = $field[3];

						if ( isset($table) && ($table == 'check') )
								$table = $configValues['CONFIG_DB_TBL_RADCHECK'];
						if ( isset($table) && ($table == 'reply') )
								$table = $configValues['CONFIG_DB_TBL_RADREPLY'];

						if ( (isset($field)) && (!isset($field[1])) )
								continue;
							
						$sql = "INSERT INTO $table values (0, '".$dbSocket->escapeSimple($username)."', '".
						$dbSocket->escapeSimple($attribute)."', '".
						$dbSocket->escapeSimple($op)."', '".
						$dbSocket->escapeSimple($value)."')  ";
						$res = $dbSocket->query($sql);
						$logDebugSQL .= $sql . "\n";

					} // foreach

					$exportCSV .= "$username,$password||";

			}
		
		}

		// if batch_history record was created successfuly
		if ($sql_batch_id != 0) {
			// remove the last || chars to sanitize it for proper format
			$exportCSV = substr($exportCSV, 0, -2);
			$successMsg = "Exported Usernames - ".
								"<a href='include/common/fileExportCSV.php?csv_output=$exportCSV'>download</a><br/>".
							"Printable Tickets - ".
								"<a href='include/common/printTickets.php?type=batch&plan=$plan&accounts=$exportCSV'>view</a><br/>".
							"Added to database new user(s): <b> $actionMsgGoodUsernames </b><br/>";
							
			$logAction .= "Successfully added to database new users [$actionMsgGoodUsernames] with prefix [$username_prefix] on page: ";
		}
		
		include 'library/closedb.php';

	}



	function addUserBatchHistory($dbSocket) {
		
		global $batch_name;
		global $batch_description;
		global $hotspot_id;
		global $logDebugSQL;
		global $configValues;
		
		// the returned id of last insert batch_history record
		$batch_id = 0;
		
		$currDate = date('Y-m-d H:i:s');
		$currBy = $_SESSION['operator_user'];
		
		$sql = "INSERT INTO ".
				$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].
				" (id, batch_name, batch_description, hotspot_id, creationdate, creationby, updatedate, updateby) ".
				" VALUES ".
				" (0, ".
				"'".$dbSocket->escapeSimple($batch_name)."', ".
				"'".$dbSocket->escapeSimple($batch_description)."', ".
				"'".$dbSocket->escapeSimple($hotspot_id)."', ".
				"'$currDate', '$currBy', NULL, NULL)";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";
		
		$sql = "SELECT id FROM ".$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].
				" WHERE batch_name = '".$dbSocket->escapeSimple($batch_name)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		// if the INSERT to the batch_history table was succesful and there exist
		// only 1 record (meaning, we don't have a duplicate) then we return the id
		if ($res->numRows() == 1) {
			$row = $res->fetchRow();
			$batch_id = $row[0];
		}
		
		return $batch_id;
		
	}
	function addUserInfo($dbSocket, $username) {

		global $firstname;
		global $lastname;
		global $email;
		global $department;
		global $company;
		global $workphone;
		global $homephone;
		global $mobilephone;
		global $ui_address;
		global $ui_city;
		global $ui_state;
		global $ui_country;
		global $ui_zip;
		global $notes;
		global $ui_changeuserinfo;
		global $ui_enableUserPortalLogin;
		global $ui_PortalLoginPassword;
		
		global $logDebugSQL;
		global $configValues;

		$currDate = date('Y-m-d H:i:s');
		$currBy = $_SESSION['operator_user'];

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].
				" WHERE username='".$dbSocket->escapeSimple($username)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		// if there were no records for this user present in the userinfo table
		if ($res->numRows() == 0) {
			// insert user information table
			$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].
				" (id, username, firstname, lastname, email, department, company, workphone, homephone, ".
				" mobilephone, address, city, state, country, zip, notes, changeuserinfo, portalloginpassword, enableportallogin, creationdate, creationby, updatedate, updateby) ".
				" VALUES (0, 
				'".$dbSocket->escapeSimple($username)."', '".$dbSocket->escapeSimple($firstname)."', '".
				$dbSocket->escapeSimple($lastname)."', '".$dbSocket->escapeSimple($email)."', '".
				$dbSocket->escapeSimple($department)."', '".$dbSocket->escapeSimple($company)."', '".
				$dbSocket->escapeSimple($workphone)."', '".$dbSocket->escapeSimple($homephone)."', '".
				$dbSocket->escapeSimple($mobilephone)."', '".$dbSocket->escapeSimple($ui_address)."', '".
				$dbSocket->escapeSimple($ui_city)."', '".$dbSocket->escapeSimple($ui_state)."', '".
				$dbSocket->escapeSimple($ui_country)."', '".
				$dbSocket->escapeSimple($ui_zip)."', '".$dbSocket->escapeSimple($notes)."', '".
				$dbSocket->escapeSimple($ui_changeuserinfo)."', '".
				$dbSocket->escapeSimple($ui_PortalLoginPassword)."', '".$dbSocket->escapeSimple($ui_enableUserPortalLogin).
				"', '$currDate', '$currBy', NULL, NULL)";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";
		} //FIXME:
		  //if the user already exist in userinfo then we should somehow alert the user
		  //that this has happened and the administrator/operator will take care of it

	}



	function addUserBillInfo($dbSocket, $username, $sql_batch_id = 0) {

		global $bi_contactperson;
		global $bi_company;
		global $bi_email;
		global $bi_phone;
		global $bi_address;
		global $bi_city;
		global $bi_state;
		global $bi_country;
		global $bi_zip;
		global $bi_paymentmethod;
		global $bi_cash;
		global $bi_creditcardname;
		global $bi_creditcardnumber;
		global $bi_creditcardexp;
		global $bi_creditcardverification;
		global $bi_creditcardtype;
		global $bi_notes;
		global $bi_lead;
		global $bi_coupon;
		global $bi_ordertaker;
		global $bi_billstatus;
		global $bi_lastbill;
		global $bi_nextbill;
		global $bi_postalinvoice;
		global $bi_faxinvoice;
		global $bi_emailinvoice;
		//global $bi_batch_id;
		global $bi_changeuserbillinfo;
		global $plan;
		global $logDebugSQL;
		global $configValues;

		$currDate = date('Y-m-d H:i:s');
		$currBy = $_SESSION['operator_user'];

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
				" WHERE username='".$dbSocket->escapeSimple($username)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		// if there were no records for this user present in the userbillinfo table
		if ($res->numRows() == 0) {
			// insert user billing information table
			$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
				" (id, username, contactperson, company, email, phone, ".
				" address, city, state, country, zip, ".
				" paymentmethod, cash, creditcardname, creditcardnumber, creditcardverification, creditcardtype, creditcardexp, ".
				" notes, changeuserbillinfo, ".
				" `lead`, coupon, ordertaker, billstatus, lastbill, nextbill, postalinvoice, faxinvoice, emailinvoice, batch_id, planName, ".
				" creationdate, creationby, updatedate, updateby) ".
				" VALUES (0, 
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
				$dbSocket->escapeSimple($bi_postalinvoice)."', '".$dbSocket->escapeSimple($bi_faxinvoice)."', '".
				$dbSocket->escapeSimple($bi_emailinvoice)."', '".
				$dbSocket->escapeSimple($sql_batch_id)."', '".
				$dbSocket->escapeSimple($plan).
				"', '$currDate', '$currBy', NULL, NULL)";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";
		} //FIXME:
		  //if the user already exist in userinfo then we should somehow alert the user
		  //that this has happened and the administrator/operator will take care of it

	}

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


<?php
	include_once ("library/tabber/tab-layout.php");
?>

<?php

	include ("menu-mng-batch.php");
	
?>

		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngbatch.php') ?>
				<h144>&#x2754;</h144></a></h2>

				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo t('helpPage','mngbatch') ?>
					<br/>
				</div>
                <?php
					include_once('include/management/actionMessages.php');
                ?>

				<form name="batchuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="<?php echo t('title','AccountInfo'); ?>">

	<fieldset>

                <h302> <?php echo t('title','AccountInfo'); ?> </h302>
		<br/>

		<ul>


		<li class='fieldset'>
		<label for='batchName' class='form'><?php echo t('all','batchName') ?></label>
		<input name='batch_name' type='text' id='batch_name' value='' tabindex='100' />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('batchNameTooltip')" />
		
		<div id='batchNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','batchNameTooltip') ?>
		</div>
		</li>
		
		<li class='fieldset'>
		<label for='batchDescription' class='form'><?php echo t('all','batchDescription') ?></label>
		<input name='batch_description' type='text' id='batch_description' value='' tabindex='101' />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('batchDescriptionTooltip')" />
		
		<div id='batchDescriptionTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','batchDescriptionTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='hotspot' class='form'><?php echo t('all','HotSpot')?></label>
		<?php
		        include_once('include/management/populate_selectbox.php');
		        populate_hotspots("Select Hotspot", "hotspot_id");
		?>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('hotspot')" />
		<div id='hotspotTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','hotspotTooltip') ?>
		</div>
		</li>







		<br/>
		<br/>
		
		<li class='fieldset'>
		<label for='usernamePrefix' class='form'><?php echo t('all','UsernamePrefix') ?></label>
		<input name='username_prefix' type='text' id='username_prefix' value='' tabindex='102' />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('usernamePrefixTooltip')" />
		
		<div id='usernamePrefixTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','usernamePrefixTooltip') ?>
		</div>
		</li>
		
		<br/>

		<li class='fieldset'>
		<input checked type='radio' value="createRandomUsers" name="createBatchUsersType" 
			onclick="javascript:toggleRandomUsers()"/>
		<b> <?php echo t('all','CreateRandomUsers') ?> </b>
		<br/>
		</li>

		<li class='fieldset'>
		<label for='usernameLength' class='form'><?php echo t('all','UsernameLength') ?></label>
		<input class="integer" name='length_user' type='text' id='length_user' value='8' tabindex='103' />
		<img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('length_user','increment')" />
		<img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('length_user','decrement')"/>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('lengthOfUsernameTooltip')" />
		
		<div id='lengthOfUsernameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','lengthOfUsernameTooltip') ?>
		</div>
		</li>


		
		<br/>
		<li class='fieldset'>
		<input type='radio' value="createIncrementUsers" name="createBatchUsersType" 
			onclick="javascript:toggleIncrementUsers()"/>
		<b> <?php echo t('all','CreateIncrementingUsers') ?> </b>
		<br/>
		</li>

		<li class='fieldset'>
		<label for='startingIndex' class='form'><?php echo t('all','StartingIndex') ?></label>
		<input class="integerLarge" name='startingIndex' type='text' id='startingIndex' value='1' disabled tabindex='104' />
		<img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('startingIndex','increment')" />
		<img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('startingIndex','decrement')"/>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('startingIndexTooltip')" />

		<div id='startingIndexTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','startingIndexTooltip') ?>
		</div>
		<li>


		<br/>
		
		<li class='fieldset'>
		<label for='passwordType' class='form'><?php echo t('all','PasswordType')?></label>
		<?php
		        include_once('include/management/populate_selectbox.php');
		        populate_password_types("passwordType");
		?>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('passwordTypeTooltip')" />
		<div id='passwordTypeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','passwordTypeTooltip') ?>
		</div>
		</li>
		
		<li class='fieldset'>
		<label for='passwordLength' class='form'><?php echo t('all','PasswordLength') ?></label>
		<input class="integer" name='length_pass' type='text' id='length_pass' value='8' tabindex='105' />
		<img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('length_pass','increment')" />
		<img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('length_pass','decrement')"/>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('lengthOfPasswordTooltip')" />

		<div id='lengthOfPasswordTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','lengthOfPasswordTooltip') ?>
		</div>
		</li>		

		<li class='fieldset'>
		<label for='numberInstances' class='form'><?php echo t('all','NumberInstances') ?></label>
		<input class="integer" name='number' type='text' id='number' value='1' tabindex='106' />
		<img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('number','increment')" />
		<img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('number','decrement')"/>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('instancesToCreateTooltip')" />

		<div id='instancesToCreateTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','instancesToCreateTooltip') ?>
		</div>
		<li>
		
		<li class='fieldset'>
		<label for='group' class='form'><?php echo t('all','Group')?></label>
		<?php
		        include_once('include/management/populate_selectbox.php');
		        populate_groups("Select Groups","group");
		?>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('group')" />
		<div id='groupTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','groupTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='groupPriority' class='form'><?php echo t('all','GroupPriority') ?></label>
		<input class="integer" name='group_priority' type='text' id='group_priority' value='0' tabindex='107' />
		<img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('group_priority','increment')" />
		<img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('group_priority','decrement')"/>
		</li>

		<li class='fieldset'>
		<label for='plan' class='form'><?php echo t('all','PlanName')?></label>
		<?php
		        include_once('include/management/populate_selectbox.php');
		        populate_plans("Select Plan","plan");
		?>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('plan')" />
		<div id='planTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','planTooltip') ?>
		</div>
		</li>
		
		<li class='fieldset'>
		<br/><br/>
		<hr><br/>
		<input type="submit" name="submit" value="<?php echo t('buttons','apply') ?> " tabindex=1000 
			class='button' />
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

     <div class="tabbertab" title="<?php echo t('title','Attributes'); ?>">
	<?php
		include_once('include/management/attributes.php');
	?>
     </div>		

</div>



	<br/>

     </div>

</div>

				</form>


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





