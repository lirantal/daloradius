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

	$username = "";
	$password = "";
	$maxallsession = "";
	$expiration = "";
	$sessiontimeout = "";
	$idletimeout = "";
	$ui_changeuserinfo = "0";
	$bi_changeuserbillinfo = "0";
	
	$logAction = "";
	$logDebugSQL = "";

	if (isset($_POST['submit'])) {
		$username = $_POST['username'];
		$password = $_POST['password'];
		$passwordType = $_POST['passwordType'];
		$groups = $_POST['groups'];
		$maxallsession = $_POST['maxallsession'];
		$expiration = $_POST['expiration'];
		$sessiontimeout = $_POST['sessiontimeout'];
		$idletimeout = $_POST['idletimeout'];
		$simultaneoususe = $_POST['simultaneoususe'];
		$framedipaddress = $_POST['framedipaddress'];


		isset($_POST['firstname']) ? $firstname = $_POST['firstname'] : $firstname = "";
		isset($_POST['lastname']) ? $lastname = $_POST['lastname'] : $lastname = " ";
		isset($_POST['email']) ? $email = $_POST['email'] : $email = "";
		isset($_POST['department']) ? $department = $_POST['department'] : $department = "";
		isset($_POST['company']) ? $company = $_POST['company'] : $company = "";
		isset($_POST['workphone']) ? $workphone = $_POST['workphone'] : $workphone =  "";
		isset($_POST['homephone']) ? $homephone = $_POST['homephone'] : $homephone = "";
		isset($_POST['mobilephone']) ? $mobilephone = $_POST['mobilephone'] : $mobilephone = "";
	    isset($_POST['address']) ? $address = $_POST['address'] : $address = "";
	    isset($_POST['city']) ? $city = $_POST['city'] : $city = "";
	    isset($_POST['state']) ? $state = $_POST['state'] : $state = "";
	    isset($_POST['country']) ? $country = $_POST['country'] : $country = "";
	    isset($_POST['zip']) ? $zip = $_POST['zip'] : $zip = "";
		isset($_POST['notes']) ? $notes = $_POST['notes'] : $notes = "";
		isset($_POST['changeuserinfo']) ? $ui_changeuserinfo = $_POST['ui_changeuserinfo'] : $ui_changeuserinfo = "0";
		isset($_POST['enableUserPortalLogin']) ? $ui_enableUserPortalLogin = $_POST['enableUserPortalLogin'] : $ui_enableUserPortalLogin = "0";
		isset($_POST['portalLoginPassword']) ? $ui_PortalLoginPassword = $_POST['portalLoginPassword'] : $ui_PortalLoginPassword = "";
		
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
	    isset($_POST['changeUserBillInfo']) ? $bi_changeuserbillinfo = $_POST['changeUserBillInfo'] : $bi_changeuserbillinfo = "0";
	    
		include 'library/opendb.php';
		
		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='$username'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 0) {
		
			if (trim($username) != "" and trim($password) != "") {

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

				// insert username/password
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK'].
						" (id,Username,Attribute,op,Value) ".
						" VALUES (0, '".$dbSocket->escapeSimple($username)."', '$passwordType', ".
						" ':=', $dbPassword)";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
	
				if ($maxallsession) {
					$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,Username,Attribute,op,Value) ".
							" VALUES (0, '".$dbSocket->escapeSimple($username)."', 'Max-All-Session', ':=', '".
							$dbSocket->escapeSimple($maxallsession)."')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}

				if ($expiration) {
					$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,Username,Attribute,op,Value) ".
							" VALUES (0, '".$dbSocket->escapeSimple($username)."', 'Expiration', ':=', '".
							$dbSocket->escapeSimple($expiration)."')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}

				if ($sessiontimeout) {
					$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADREPLY']." (id,Username,Attribute,op,Value) ".
							" VALUES (0, '".$dbSocket->escapeSimple($username)."', 'Session-Timeout', ':=', '".
							$dbSocket->escapeSimple($sessiontimeout)."')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}

				if ($idletimeout) {
					$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADREPLY']." (id,Username,Attribute,op,Value) ".
							" VALUES (0, '".$dbSocket->escapeSimple($username)."', 'Idle-Timeout', ':=', '".
							$dbSocket->escapeSimple($idletimeout)."')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}

				if ($simultaneoususe) {
					$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,Username,Attribute,op,Value) ".
							" VALUES (0, '".$dbSocket->escapeSimple($username)."', 'Simultaneous-Use', ':=', '".
							$dbSocket->escapeSimple($simultaneoususe)."')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}

				if ($framedipaddress) {
					$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADREPLY']." (id,Username,Attribute,op,Value) ".
							" VALUES (0, '".$dbSocket->escapeSimple($username)."', 'Framed-IP-Address', ':=', '".
							$dbSocket->escapeSimple($framedipaddress)."')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}

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

				//insert userinfo
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
							$dbSocket->escapeSimple($mobilephone)."', '".$dbSocket->escapeSimple($address)."', '".
							$dbSocket->escapeSimple($city)."', '".$dbSocket->escapeSimple($state)."', '".
							$dbSocket->escapeSimple($country)."', '".
							$dbSocket->escapeSimple($zip)."', '".$dbSocket->escapeSimple($notes)."', '".
							$dbSocket->escapeSimple($ui_changeuserinfo)."', '".
							$dbSocket->escapeSimple($ui_PortalLoginPassword)."', '".$dbSocket->escapeSimple($ui_enableUserPortalLogin).
							"', '$currDate', '$currBy', NULL, NULL)";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";

				}


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
		                                $dbSocket->escapeSimple($bi_changeuserbillinfo).
		                                "', '$currDate', '$currBy', NULL, NULL)";
			                        $res = $dbSocket->query($sql);
		                        $logDebugSQL .= $sql . "\n";
		                }

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
<script type="text/javascript" src="library/javascript/ajaxGeneric.js"></script>

<?php
	include_once ("library/tabber/tab-layout.php");
?>

<?php

	include ("menu-mng-users.php");
	
?>

	<div id="contentnorightbar">

		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngnewquick.php') ?>
		<h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','mngnewquick') ?>
			<br/>
		</div>
		<?php
			include_once('include/management/actionMessages.php');
		?>
		
		<form name="newuser" action="mng-new-quick.php" method="post" >
<div class="tabber">

     <div class="tabbertab" title="<?php echo t('title','AccountInfo'); ?>">

        <fieldset>

			<h302> <?php echo t('title','AccountInfo'); ?> </h302>
			<br/>
		
		<ul>

		<li class='fieldset'>
		<label for='username' class='form'><?php echo t('all','Username')?></label>
		<input name='username' type='text' id='username' value='' tabindex=100  />
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
		<input name='password' type='text' id='password' value='' <?php if (isset($hiddenPassword)) 
			echo $hiddenPassword ?> tabindex=101 />
		<input type='button' value='Random' class='button' onclick="javascript:randomAlphanumeric('password',8,<?php
		echo "'".$configValues['CONFIG_USER_ALLOWEDRANDOMCHARS']."'" ?>)" />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('passwordTooltip')" />

		<div id='passwordTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','passwordTooltip') ?>
		</div>
		</li>

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
		<br />
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
		<input type="submit" name="submit" value="<?php echo t('buttons','apply')?>" 
			onclick = "javascript:small_window(document.newuser.username.value, 
			document.newuser.password.value, document.newuser.maxallsession.value);" tabindex=10000 class='button' />
		</li>
		</ul>
        </fieldset>

	<br/>

	<fieldset>

		<h302> <?php echo t('title','Attributes'); ?> </h302>
	<br/>

		<label for='simultaneoususe' class='form'><?php echo t('all','SimultaneousUse')?></label>
		<input name='simultaneoususe' type='text' value='' tabindex=106 />
		<br/>

		<label for='framedipaddress' class='form'><?php echo t('all','FramedIPAddress')?></label>
		<input name='framedipaddress' type='text' value='' tabindex=107 />
		<br/>

		<label for='expiration' class='form'><?php echo t('all','Expiration')?></label>		
		<input value='' id='expiration' name='expiration'  tabindex=108 />
		<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'expiration', 'chooserSpan', 1950, <?php echo date('Y', time());?>, 'd M Y', false);">
		<br/>

		<label for='sessiontimeout' class='form'><?php echo t('all','SessionTimeout')?></label>
		<input value='' id='sessiontimeout' name='sessiontimeout'  tabindex=109 />
		<select onChange="javascript:setText(this.id,'sessiontimeout')" id="option0" class='form' >
			<option value="1">calculate time</option>
			<option value="1">seconds</option>
			<option value="60">minutes</option>
			<option value="3600">hours</option>
			<option value="86400">days</option>
			<option value="604800">weeks</option>
			<option value="2592000">months (30 days)</option>
		</select>
		<br/>

		<label for='idletimeout' class='form'><?php echo t('all','IdleTimeout')?></label>
		<input value='' id='idletimeout' name='idletimeout'  tabindex=110 />
		<select onChange="javascript:setText(this.id,'idletimeout')" id="option1" class='form' >
			<option value="1">calculate time</option>
			<option value="1">seconds</option>
			<option value="60">minutes</option>
			<option value="3600">hours</option>
			<option value="86400">days</option>
			<option value="604800">weeks</option>
			<option value="2592000">months (30 days)</option>
		</select>
		<br/>

		<label for='maxallsession' class='form'><?php 
			echo t('all','MaxAllSession') ?></label>
		<input value='' id='maxallsession' name='maxallsession'  tabindex=111 />
		<select onChange="javascript:setText(this.id,'maxallsession')" id="option2" class='form' >
			<option value="1">calculate time</option>
			<option value="1">seconds</option>
			<option value="60">minutes</option>
			<option value="3600">hours</option>
			<option value="86400">days</option>
			<option value="604800">weeks</option>
			<option value="2592000">months (30 days)</option>
		</select>
		<br/>

		<br/>	
	</fieldset>

	<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>

        </div>


     <div class="tabbertab" title="<?php echo t('title','UserInfo'); ?>">

        <?php
		$customApplyButton = "<input type=\"submit\" name=\"submit\" value=\"".t('buttons','apply')."\"
		                        onclick = \"javascript:small_window(document.newuser.username.value,
		                        document.newuser.password.value, document.newuser.maxallsession.value);\" tabindex=10000
		                        class='button' />";

                include_once('include/management/userinfo.php');
        ?>

     </div>



        <div class="tabbertab" title="<?php echo t('title','BillingInfo'); ?>">
        <?php
                $customApplyButton = "<input type='submit' name='submit' value=".t('buttons','apply')." class='button' />";
                include_once('include/management/userbillinfo.php');
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





