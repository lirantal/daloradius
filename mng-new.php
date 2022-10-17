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

	// declaring variables
	$logAction = "";
	$logDebugSQL = "";

	isset($_POST['username']) ? $username = $_POST['username'] : $username = "";
	isset($_POST['password']) ? $password = $_POST['password'] : $password = "";
	isset($_POST['groups']) ? $groups = $_POST['groups'] : $groups = "";
	isset($_POST['authType']) ? $authType = $_POST['authType'] : $authType = "";

	isset($_POST['username']) ? $username = $_POST['username'] : $username = "";
	isset($_POST['password']) ? $password = $_POST['password'] : $password = "";
	isset($_POST['passwordType']) ? $passwordtype = $_POST['passwordType'] : $passwordtype = "";

	isset($_POST['macaddress']) ? $macaddress = $_POST['macaddress'] : $macaddress = "";
	isset($_POST['pincode']) ? $pincode = $_POST['pincode'] : $pincode = "";

	isset($_POST['group_macaddress']) ? $group_macaddress = $_POST['group_macaddress'] : $group_macaddress = "";
	isset($_POST['group_pincode']) ? $group_pincode = $_POST['group_pincode'] : $group_pincode = "";


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
	isset($_POST['address']) ? $ui_address = $_POST['address'] : $ui_address = "";
	isset($_POST['city']) ? $ui_city = $_POST['city'] : $ui_city = "";
	isset($_POST['state']) ? $ui_state = $_POST['state'] : $ui_state = "";
	isset($_POST['country']) ? $country = $_POST['country'] : $country = "";
	isset($_POST['zip']) ? $ui_zip = $_POST['zip'] : $ui_zip = "";
	isset($_POST['notes']) ? $notes = $_POST['notes'] : $notes = "";
	isset($_POST['changeUserInfo']) ? $ui_changeuserinfo = $_POST['changeUserInfo'] : $ui_changeuserinfo = "0";
	
	isset($_POST['enableUserPortalLogin']) ? $ui_enableUserPortalLogin = $_POST['enableUserPortalLogin'] : $ui_enableUserPortalLogin = "0";
	isset($_POST['portalLoginPassword']) ? $ui_PortalLoginPassword = $_POST['portalLoginPassword'] : $ui_PortalLoginPassword = "";
	
	isset($_POST['dictAttributes']) ? $dictAttributes = $_POST['dictAttributes'] : $dictAttributes = "";		


	function addGroups($dbSocket, $username, $groups) {

		global $logDebugSQL;
		global $configValues;

		// insert usergroup mapping
		if (isset($groups)) {

			foreach ($groups as $group) {

				if (trim($group) != "") {
					$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." (UserName,GroupName,priority) ".
						" VALUES ('".$dbSocket->escapeSimple($username)."', '".$dbSocket->escapeSimple($group)."',0) ";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}
			}
		}
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
		global $ui_PortalLoginPassword;
		global $ui_enableUserPortalLogin;
		
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



	function addUserBillInfo($dbSocket, $username) {

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
		global $bi_nextinvoicedue;
		global $bi_billdue;
        global $bi_postalinvoice;
        global $bi_faxinvoice;
        global $bi_emailinvoice;
		global $bi_changeuserbillinfo;
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
                                " `lead`, coupon, ordertaker, billstatus, lastbill, nextbill, nextinvoicedue, billdue, postalinvoice, faxinvoice, emailinvoice, ".
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
                $dbSocket->escapeSimple($bi_nextinvoicedue)."', '".$dbSocket->escapeSimple($bi_billdue)."', '".
                $dbSocket->escapeSimple($bi_postalinvoice)."', '".$dbSocket->escapeSimple($bi_faxinvoice)."', '".
                $dbSocket->escapeSimple($bi_emailinvoice).
				"', '$currDate', '$currBy', NULL, NULL)";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";
		} //FIXME:
		  //if the user already exist in userinfo then we should somehow alert the user
		  //that this has happened and the administrator/operator will take care of it

	}


	function addAttributes($dbSocket, $username) {
		
		global $logDebugSQL;
		global $configValues;

		foreach($_POST as $element=>$field) { 

			// switch case to rise the flag for several $attribute which we do not
			// wish to process (ie: do any sql related stuff in the db)
			switch ($element) {

				case "authType":
				case "username":
				case "password":
				case "passwordType":
				case "groups":
				case "group_macaddress":
				case "group_pincode":
				case "macaddress":
				case "pincode":
				case "submit":
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
				case "bi_nextinvoicedue":
				case "bi_billdue":
				case "bi_postalinvoice":
				case "bi_faxinvoice":
				case "bi_emailinvoice":
				case "changeUserBillInfo":
				case "changeUserInfo":
				case "copycontact":
				case "portalLoginPassword":
				case "enableUserPortalLogin":
					$skipLoopFlag = 1;	// if any of the cases above has been met we set a flag
								// to skip the loop (continue) without entering it as
								// we do not want to process this $attribute in the following
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
	
			$sql = "INSERT INTO $table (id,Username,Attribute,op,Value) ".
					" VALUES (0, '".$dbSocket->escapeSimple($username)."', '".
					$dbSocket->escapeSimple($attribute)."', '".$dbSocket->escapeSimple($op)."', '".
					$dbSocket->escapeSimple($value)."')  ";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";

		} // foreach

	}


	if (isset($_POST['submit'])) {

		include 'library/opendb.php';

		global $username;
		global $authType;
		global $password;
		global $passwordtype;

		switch($authType) {
			case "userAuth":
				break;
			case "macAuth":
				$username = $macaddress;
				break;
			case "pincodeAuth":
				$username = $pincode;
				break;
		}

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='".
				$dbSocket->escapeSimple($username)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 0) {

		    if ($authType == "userAuth") {

				if (trim($username) != "" and trim($password) != "") {

					// we need to perform the secure method escapeSimple on $dbPassword early because as seen below
					// we manipulate the string and manually add to it the '' which screw up the query if added in $sql
					$password = $dbSocket->escapeSimple($password);

					switch($configValues['CONFIG_DB_PASSWORD_ENCRYPTION']) {
						case "cleartext":
							$dbPassword = "'$password'";
							break;
						case "crypt":
							$dbPassword = "ENCRYPT('$password', 'SALT_DALORADIUS')";
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
							$dbPassword = "ENCRYPT('$password', 'SALT_DALORADIUS')";
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
					
					addGroups($dbSocket, $username, $groups);
					addUserInfo($dbSocket, $username);
					addUserBillInfo($dbSocket, $username);
					addAttributes($dbSocket, $username);

					$successMsg = "Added to database new user: <b> $username </b>";
					$logAction .= "Successfully added new user [$username] on page: ";

				} else {

					$failureMsg = "username or password are empty";
					$logAction .= "Failed adding (possible empty user/pass) new user [$username] on page: ";
				}

		   } elseif ($authType == "macAuth") {
			    
				$macaddress = trim($macaddress);
				
				if (filter_var($macaddress, FILTER_VALIDATE_MAC)) {

					// insert username/password
					$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,Username,Attribute,op,Value) ".
						" VALUES (0, '".$dbSocket->escapeSimple($macaddress)."', 'Auth-Type', ':=', 'Accept')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				
					addGroups($dbSocket, $macaddress, $group_macaddress);
					addUserInfo($dbSocket, $macaddress);
                                	addUserBillInfo($dbSocket, $username);
					addAttributes($dbSocket, $macaddress);

					$successMsg = "Added to database new mac auth user: <b> $macaddress </b>";
					$logAction .= "Successfully added new mac auth user [$macaddress] on page: ";
				} else { 
					$failureMsg = "Invalid Mac address format: <b> $username </b>";
					$logAction .= "Failed adding new user invalid mac address format [$username] on page: ";
				}

		   } elseif ($authType == "pincodeAuth") {

				// insert username/password
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,Username,Attribute,op,Value) ".
						" VALUES (0, '".$dbSocket->escapeSimple($pincode)."', 'Auth-Type', ':=', 'Accept')";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				addGroups($dbSocket, $pincode, $group_pincode);
				addUserInfo($dbSocket, $pincode);
                                addUserBillInfo($dbSocket, $username);
				addAttributes($dbSocket, $pincode);

				$successMsg = "Added to database new pincode: <b> $pincode </b>";
				$logAction .= "Successfully added new pincode [$pincode] on page: ";

		   } else {
				echo "unknown authentication method <br/>";
		   }

		} else { 
			$failureMsg = "user already exist in database: <b> $username </b>";
			$logAction .= "Failed adding new user already existing in database [$username] on page: ";
		}
		
		include 'library/closedb.php';

	}




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
<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->
</head>
 
<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script src="library/javascript/productive_funcs.js" type="text/javascript"></script>

<script type="text/javascript" src="library/javascript/ajax.js"></script>
<script type="text/javascript" src="library/javascript/dynamic_attributes.js"></script>
<script type="text/javascript" src="library/javascript/ajaxGeneric.js"></script>


 
<?php
	include_once ("library/tabber/tab-layout.php");
?>

<?php
	include ("menu-mng-users.php");
?>
	<div id="contentnorightbar">

		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngnew.php') ?>
		<h144>&#x2754;</h144></a></h2>
		
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','mngnew') ?>
			<br/>
		</div>
		<?php
			include_once('include/management/actionMessages.php');
		?>
		
		<form name="newuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="<?php echo t('title','AccountInfo'); ?>">

	<fieldset>

		<h302> <?php echo t('title','AccountInfo'); ?> </h302>

		<input checked type='radio' value="userAuth" name="authType" onclick="javascript:toggleUserAuth()"/>
		<b> Username Authentication </b>
		<br/>

		<ul>

		<div id='UserContainer'>
		<li class='fieldset'>
		<label for='username' class='form'><?php echo t('all','Username')?></label>
		<input name='username' type='text' id='username' value='' tabindex=100 />
		<input type='button' value='Random' class='button' onclick="javascript:randomAlphanumeric('username',8,<?php
		echo "'".$configValues['CONFIG_USER_ALLOWEDRANDOMCHARS']."'" ?>)" />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('usernameTooltip')" /> 

		<div id='usernameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','usernameTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='password' class='form'><?php echo t('all','Password')?></label>
		<input name='password' type='text' id='password' value='' 
			<?php if (isset($hiddenPassword)) echo $hiddenPassword ?> tabindex=101 />
		<input type='button' value='Random' class='button' onclick="javascript:randomAlphanumeric('password',8,<?php
		echo "'".$configValues['CONFIG_USER_ALLOWEDRANDOMCHARS']."'" ?>)" />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('passwordTooltip')" />

		<div id='passwordTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' /> 
			<?php echo t('Tooltip','passwordTooltip') ?>
		</div>
		</li>
		</div>


		<li class='fieldset'>
		<label for='passwordType' class='form'><?php echo t('all','PasswordType')?> </label>
		<select class='form' tabindex=102 name='passwordType' >
			<option value='Cleartext-Password'>Cleartext-Password</option>
			<option value='User-Password'>User-Password</option>
			<option value='Crypt-Password'>Crypt-Password</option>
			<option value='MD5-Password'>MD5-Password</option>
			<option value='SHA1-Password'>SHA1-Password</option>
			<option value='CHAP-Password'>CHAP-Password</option>
		</select>
		</li>


		<li class='fieldset'>
		<label for='group' class='form'><?php echo t('all','Group')?></label>
		<?php   
			include_once 'include/management/populate_selectbox.php';
			populate_groups("Select Groups","groups[]");
		?>

		<a class='tablenovisit' href='#'
			onClick="javascript:ajaxGeneric('include/management/dynamic_groups.php','getGroups','divContainerGroups',genericCounter('divCounter')+'&elemName=groups[]');">Add</a>

		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('group')" />

		<div id='divContainerGroups'>
		</div>


		<div id='groupTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' /> 
			<?php echo t('Tooltip','groupTooltip') ?>
		</div>
		</li>


		<li class='fieldset'>
		<br/>
		<hr><br/>
		<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />
		</li>

		</ul>

	</fieldset>

	<br/>

	<fieldset>

		<h302> <?php echo t('title','AccountInfo'); ?> </h302>


		<input type='radio' name="authType" value="macAuth"  onclick="javascript:toggleMacAuth()"/>
		<b> MAC Address Authentication </b>
		<br/>

		<ul>

		<li class='fieldset'>
		<label for='macaddress' class='form'><?php echo t('all','MACAddress')?></label>
		<input name='macaddress' type='text' id='macaddress' value='' tabindex=105 disabled />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('macaddressTooltip')"  />

		<div id='macaddressTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','macaddressTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='group' class='form'><?php echo t('all','Group')?></label>
		<?php   
			include_once 'include/management/populate_selectbox.php';
			populate_groups("Select Groups", "group_macaddress[]", "form", "disabled");
		?>

                <a class='tablenovisit' href='#'
                        onClick="javascript:ajaxGeneric('include/management/dynamic_groups.php','getGroups','divContainerGroupsMacAuth',genericCounter('divCounter')+'&elemName=group_macaddress[]');">Add</a>

		<div id='divContainerGroupsMacAuth'>
		</div>
		</li>


		<li class='fieldset'>
		<br/>
		<hr><br/>
		<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />
		</li>

		</ul>

	</fieldset>


	<br/>

	<fieldset>

		<h302> <?php echo t('title','AccountInfo'); ?> </h302>

		<input type='radio' name="authType" value="pincodeAuth" onclick="javascript:togglePinCode()"/>
		<b> PIN Code Authentication </b>
		<br/>

		<ul>

		<li class='fieldset'>
		<label for='pincode' class='form'><?php echo t('all','PINCode')?></label>
		<input name='pincode' type='text' id='pincode' value='' tabindex=106 disabled />
		<input type='button' value='Generate' class='button' onclick="javascript:randomAlphanumeric('pincode',10)" />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('pincodeTooltip')" />		
		
		<div id='pincodeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','pincodeTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='group' class='form'><?php echo t('all','Group')?></label>
		<?php   
			include_once 'include/management/populate_selectbox.php';
			populate_groups("Select Groups", "group_pincode[]", "form", "disabled");
		?>

                <a class='tablenovisit' href='#'
                        onClick="javascript:ajaxGeneric('include/management/dynamic_groups.php','getGroups','divContainerGroupsPinAuth',genericCounter('divCounter')+'&elemName=group_pincode[]');">Add</a>

		<div id='divContainerGroupsPinAuth'>
		</div>
		</li>


		<li class='fieldset'>
		<br/>
		<hr><br/>
		<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />
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

