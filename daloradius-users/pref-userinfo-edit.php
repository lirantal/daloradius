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

	$logAction = "";
	$logDebugSQL = "";

	if (isset($_POST['submit'])) {

		include 'library/opendb.php';

		$sql = "SELECT changeuserinfo ".
			" FROM ".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].
			" WHERE UserName='".$dbSocket->escapeSimple($login)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		$row = $res->fetchRow();
		$ui_changeuserinfo = $row[0];

		if ($ui_changeuserinfo == 1) {

			$firstname = $_POST['firstname'];
			$lastname = $_POST['lastname'];
			$email = $_POST['email'];
			$department = $_POST['department'];
			$company = $_POST['company'];
			$workphone = $_POST['workphone'];
			$homephone = $_POST['homephone'];
			$mobilephone = $_POST['mobilephone'];
			$address = $_POST['address'];
			$city = $_POST['city'];
			$state = $_POST['state'];
			$country = $_POST['country'];
			$zip = $_POST['zip'];

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
					"' WHERE username='".$dbSocket->escapeSimple($login)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";
		
			$successMsg = "Updated user information for user: <b>$login</b>";
			$logAction .= "Successfully updated user information for user [$login] on page: ";
	
			include 'library/closedb.php';
		} else {

			$failureMsg = "Failure updating user information, you are not permitted to do that.";
			$logAction .= "Failed updating user information for user [$login], not permitted to do that on page: ";

		} // checking user permission to update his settings

	} // if (is submit)




	include 'library/opendb.php';	

	/* fill-in all the user info details */

	$sql = "SELECT firstname, lastname, email, department, company, workphone, homephone, mobilephone, address, city, state, country, zip".
			" FROM ".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].
			" WHERE UserName='".$dbSocket->escapeSimple($login)."'";
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
</head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script type="text/javascript">
</script> 
<?php

	include ("menu-preferences.php");
	
?>		
	<div id="contentnorightbar">

		<h2 id="Intro" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','prefuserinfoedit.php') ?>
		:: <?php if (isset($login)) { echo $login; } ?><h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','prefuserinfoedit') ?>
			<br/>
		</div>
		<?php
			include_once('include/management/actionMessages.php');
		?>

		<form name="prefuserinfoedit" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

	<?php
		include_once('include/management/userinfo.php');
	?>


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
