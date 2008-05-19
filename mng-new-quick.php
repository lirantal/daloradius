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
	
	$logAction = "";
	$logDebugSQL = "";

	if (isset($_POST['submit'])) {
		$username = $_REQUEST['username'];
		$password = $_REQUEST['password'];
		$passwordType = $_REQUEST['passwordType'];
		$group = $_REQUEST['group'];
		$maxallsession = $_REQUEST['maxallsession'];
		$expiration = $_REQUEST['expiration'];
		$sessiontimeout = $_REQUEST['sessiontimeout'];
		$idletimeout = $_REQUEST['idletimeout'];

		isset($_REQUEST['firstname']) ? $firstname = $_REQUEST['firstname'] : $firstname = "";
		isset($_REQUEST['lastname']) ? $lastname = $_REQUEST['lastname'] : $lastname = " ";
		isset($_REQUEST['email']) ? $email = $_REQUEST['email'] : $email = "";
		isset($_REQUEST['department']) ? $department = $_REQUEST['department'] : $department = "";
		isset($_REQUEST['company']) ? $company = $_REQUEST['company'] : $company = "";
		isset($_REQUEST['workphone']) ? $workphone = $_REQUEST['workphone'] : $workphone =  "";
		isset($_REQUEST['homephone']) ? $homephone = $_REQUEST['homephone'] : $homephone = "";
		isset($_REQUEST['mobilephone']) ? $mobilephone = $_REQUEST['mobilephone'] : $mobilephone = "";
		isset($_REQUEST['notes']) ? $notes = $_REQUEST['notes'] : $notes = "";

		include 'library/opendb.php';
		
		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='$username'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 0) {
		
			if (trim($username) != "" and trim($password) != "") {

				// insert username/password
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,Username,Attribute,op,Value) ".
						" VALUES (0, '".$dbSocket->escapeSimple($username)."', '$passwordType', ':=', '".
						$dbSocket->escapeSimple($password)."')";
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

				if (isset($group)) {
					$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." (Username,GroupName,priority) ".
							" VALUES ('".$dbSocket->escapeSimple($username)."', '".$dbSocket->escapeSimple($group)."', '0')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
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
							" mobilephone, notes, creationdate, creationby, updatedate, updateby) ".
							" VALUES (0,
							'".$dbSocket->escapeSimple($username)."', '".$dbSocket->escapeSimple($firstname)."', '".
							$dbSocket->escapeSimple($lastname)."', '".$dbSocket->escapeSimple($email)."', '".
							$dbSocket->escapeSimple($department)."', '".$dbSocket->escapeSimple($company)."', '".
							$dbSocket->escapeSimple($workphone)."', '".$dbSocket->escapeSimple($homephone)."', '".
							$dbSocket->escapeSimple($mobilephone)."', '".$dbSocket->escapeSimple($notes).
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

<?php
	include_once ("library/tabber/tab-layout.php");
?>

<?php

	include ("menu-mng-users.php");
	
?>

	<div id="contentnorightbar">

		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngnewquick.php'] ?>
		<h144>+</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['mngnewquick'] ?>
			<br/>
		</div>
		<?php
			include_once('include/management/actionMessages.php');
		?>
		
		<form name="newuser" action="mng-new-quick.php" method="post" >
<div class="tabber">

     <div class="tabbertab" title="<?php echo $l['title']['AccountInfo']; ?>">

        <fieldset>

			<h302> <?php echo $l['title']['AccountInfo']; ?> </h302>
			<br/>
		
		<ul>

		<li class='fieldset'>
		<label for='username' class='form'><?php echo $l['all']['Username']?></label>
		<input name='username' type='text' id='username' value='' tabindex=100  />
		<input type='button' value='Random' class='button' onclick="javascript:randomAlphanumeric('username',8)" />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('usernameTooltip')" />

		<div id='usernameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo $l['Tooltip']['usernameTooltip'] ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='password' class='form'><?php echo $l['all']['Password']?></label>
		<input name='password' type='text' id='password' value='' <?php if (isset($hiddenPassword)) 
			echo $hiddenPassword ?> tabindex=101 />
		<input type='button' value='Random' class='button' onclick="javascript:randomAlphanumeric('password',8)" />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('passwordTooltip')" />

		<div id='passwordTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo $l['Tooltip']['passwordTooltip'] ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='passwordType' class='form'><?php echo $l['all']['PasswordType']?> </label>
		<select class='form' tabindex=102 name='passwordType' >
			<option value='User-Password'>User-Password</option>
			<option value='Cleartext-Password'>Cleartext-Password</option>
			<option value='Crypt-Password'>Crypt-Password</option>
			<option value='MD5-Password'>MD5-Password</option>
			<option value='SHA1-Password'>SHA1-Password</option>
			<option value='CHAP-Password'>CHAP-Password</option>
		</select>
		<br />
		</li>

		<li class='fieldset'>
		<label for='group' class='form'><?php echo $l['all']['Group']?></label>
		<?php   
			include_once 'include/management/populate_selectbox.php';
			populate_groups("Select Groups","group");
		?>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('group')" />

		<div id='groupTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo $l['Tooltip']['groupTooltip'] ?>
		</div>
		</li>

		<li class='fieldset'>
		<br/>
                <hr><br/>
		<input type="submit" name="submit" value="<?php echo $l['buttons']['apply']?>" 
			onclick = "javascript:small_window(document.newuser.username.value, 
			document.newuser.password.value, document.newuser.maxallsession.value);" tabindex=10000 class='button' />
		</li>
		</ul>
        </fieldset>

	<br/>

	<fieldset>

		<h302> <?php echo $l['title']['Attributes']; ?> </h302>

		<label for='expiration' class='form'><?php echo $l['all']['Expiration']?></label>		
		<input value='' id='expiration' name='expiration'  tabindex=106 />

		<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'expiration', 'chooserSpan', 1950, 2010, 'd M Y', false);">

		<br/>

		<label for='sessiontimeout' class='form'><?php echo $l['all']['SessionTimeout']?></label>
		<input value='' id='sessiontimeout' name='sessiontimeout'  tabindex=107 />
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

		<label for='idletimeout' class='form'><?php echo $l['all']['IdleTimeout']?></label>
		<input value='' id='idletimeout' name='idletimeout'  tabindex=107 />
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
			echo $l['all']['MaxAllSession'] ?></label>
		<input value='' id='maxallsession' name='maxallsession'  tabindex=108 />
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


     <div class="tabbertab" title="<?php echo $l['title']['UserInfo']; ?>">

	<fieldset>

        <h302> Contact Info </h302>
        <br/>

        <h301> Personal </h301>
        <br/>

        <label for='username' class='form'><?php echo $l['ContactInfo']['FirstName'] ?></label>
        <input value='<?php if (isset($ui_firstname)) echo $ui_firstname; ?>' name='firstname' tabindex=300 />
        <br/>

        <label for='lastname' class='form'><?php echo $l['ContactInfo']['LastName'] ?></label>
        <input value='<?php if (isset($ui_lastname)) echo $ui_lastname; ?>' name='lastname' tabindex=301 />
        <br/>

        <label for='email' class='form'><?php echo $l['ContactInfo']['Email'] ?></label>
        <input value='<?php if (isset($ui_email)) echo $ui_email; ?>' name='email' tabindex=302 />
        <br/>

        <br/>
        <h301> Business </h301>
        <br/>

        <label for='department' class='form'><?php echo $l['ContactInfo']['Department'] ?></label>
        <input value='<?php if (isset($ui_department)) echo $ui_department; ?>' name='department' tabindex=303 />
        <br/>

        <label for='company' class='form'><?php echo $l['ContactInfo']['Company'] ?></label>
        <input value='<?php if (isset($ui_company)) echo $ui_company; ?>' name='company' tabindex=304 />
        <br/>

        <label for='workphone' class='form'><?php echo $l['ContactInfo']['WorkPhone'] ?></label>
        <input value='<?php if (isset($ui_workphone)) echo $ui_workphone; ?>' name='workphone' tabindex=305 />
        <br/>

        <label for='homephone' class='form'><?php echo $l['ContactInfo']['HomePhone'] ?></label>
        <input value='<?php if (isset($ui_homephone)) echo $ui_homephone; ?>' name='homephone' tabindex=306 />
        <br/>

        <label for='mobilephone' class='form'><?php echo $l['ContactInfo']['MobilePhone'] ?></label>
        <input value='<?php if (isset($ui_mobilephone)) echo $ui_mobilephone; ?>' name='mobilephone' tabindex=307 />
        <br/>

        <br/>
        <h301> Other </h301>
        <br/>

        <label for='notes' class='form'><?php echo $l['ContactInfo']['Notes'] ?></label>
        <textarea class='form' name='notes' value='<?php if (isset($ui_notes)) echo $ui_notes; ?>' tabindex=308 ></textarea>
        <br/>

        <br/>
        <label for='creationdate' class='form'><?php echo $l['all']['CreationDate'] ?></label>
        <input disabled value='<?php if (isset($ui_creationdate)) echo $ui_creationdate; ?>' tabindex=309 />
        <br/>

        <label for='creationby' class='form'><?php echo $l['all']['CreationBy'] ?></label>
        <input disabled value='<?php if (isset($ui_creationby)) echo $ui_creationby; ?>' tabindex=310 />
        <br/>

        <label for='updatedate' class='form'><?php echo $l['all']['UpdateDate'] ?></label>
        <input disabled value='<?php if (isset($ui_updatedate)) echo $ui_updatedate; ?>' tabindex=311 />
        <br/>

        <label for='updateby' class='form'><?php echo $l['all']['UpdateBy'] ?></label>
        <input disabled value='<?php if (isset($ui_updateby)) echo $ui_updateby; ?>' tabindex=312 />
        <br/>

        <br/>
        <hr><br/>

		<input type="submit" name="submit" value="<?php echo $l['buttons']['apply']?>" 
			onclick = "javascript:small_window(document.newuser.username.value, 
			document.newuser.password.value, document.newuser.maxallsession.value);" tabindex=10000 
			class='button' />

	</fieldset>

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





