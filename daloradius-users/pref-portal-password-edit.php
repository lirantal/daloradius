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
    $login = $_SESSION['login_user'];

	isset($_POST['currentpassword']) ? $currentpassword = $_POST['currentpassword'] : $currentpassword = "";
	isset($_POST['newpassword']) ? $newpassword = $_POST['newpassword'] : $newpassword = "";
	isset($_POST['verifypassword']) ? $verifypassword = $_POST['verifypassword'] : $verifypassword = "";

	$logAction = "";
	$logDebugSQL = "";

	if (isset($_POST['submit'])) {

		$currentPassword = $_POST['currentpassword'];
		$newPassword = $_POST['newpassword'];
		$verifyPassword = $_POST['verifypassword'];

		if ($newPassword == $verifyPassword) {

			if (trim($currentPassword) != "") {

				include 'library/opendb.php';
				
				$sql = "SELECT portalloginpassword FROM ".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].
					" WHERE username='".$dbSocket->escapeSimple($login)."'";
				$res = $dbSocket->query($sql);
				$row = $res->fetchRow();
	
				$logDebugSQL .= $sql . "\n";
				
				if ( ($res->numRows() == 1) && ($row[0] == $currentPassword) ) {
	
					$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].
						" SET portalloginpassword='".$dbSocket->escapeSimple($newPassword)."'".
						" WHERE UserName='".$dbSocket->escapeSimple($login)."'";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";

					$successMsg = "Updated password for user: <b>$login</b>";
					$logAction .= "Successfully update password for user [$login] on page: ";

					include 'library/closedb.php';

				} else {

					$failureMsg = "Failed updating password, possibly wrong password entered for user: <b>$login</b>";
					$logAction .= "Failed updating password, possibly wrong password entered for user [$login] on page: ";

				}
				
			} else {
				$failureMsg = "New Password field was left empty, please provide a new password to change to";
				$logAction .= "Failed changing user password, empty current password for user [$login] on page: ";
			} // if (trim($currentPassword) != "")

		} else {
			$failureMsg = "Passwords do not match, please type the new password and re-type it again to verify";
			$logAction .= "Failed changing user password, passwords do not match for user [$login] on page: ";
		} // if ($newPassword == $verifyPassword)
			
	} // if (is submit)
	


	include_once('library/config_read.php');
	$log = "visited page: ";
	
?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
</head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script type="text/javascript">

function verifyPassword(passwordStr1, passwordStr2) {

	objPasswordStr1 = document.getElementById(passwordStr1);
	objPassword1Val = objPasswordStr1.value;
	objPasswordStr2 = document.getElementById(passwordStr2);
	objPassword2Val = objPasswordStr2.value;

	if (objPassword1Val == objPassword2Val) {
		document.forms[0].submit();
	} else { 
		alert("Passwords do not match, please re-type your new password and verify it");
		return false;
	}
}

</script> 
<?php

	include ("menu-preferences.php");
	
?>		
	<div id="contentnorightbar">

		<h2 id="Intro" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','prefpasswordedit.php') ?>
		:: <?php if (isset($login)) { echo $login; } ?><h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','prefpasswordedit') ?>
			<br/>
		</div>
		<?php
				include_once('include/management/actionMessages.php');
		?>

		<form name="prefpasswordedit" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

        <fieldset>

			<h302> <?php echo t('title','ChangePassword'); ?> </h302>
			<br/>

			<ul>


			<li class='fieldset'>
			<label for='currentpassword' class='form'><?php echo t('all','CurrentPassword') ?></label>
			<input name='currentpassword' type='password' id='currentpassword' value='<?php echo $currentpassword ?>' 
				tabindex=101 />
			</li>

			<li class='fieldset'>
			<label for='newpassword' class='form'><?php echo t('all','NewPassword') ?></label>
			<input name='newpassword' type='password' id='newpassword' value='<?php echo $newpassword ?>' 
				tabindex=101 />
			</li>

			<li class='fieldset'>
			<label for='verifypassword' class='form'><?php echo t('all','VerifyPassword') ?></label>
			<input name='verifypassword' type='password' id='verifypassword' value='<?php echo $verifypassword ?>' 
				tabindex=101 />
			</li>



			<li class='fieldset'>
			<br/>
			<hr><br/>
			<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000
					class='button' onClick="return verifyPassword('newpassword','verifypassword');" />
		</li>

		<ul>

        </fieldset>

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
