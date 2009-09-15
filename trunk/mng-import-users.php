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
	$logDebugSQL = "";

	isset($_POST['csvdata']) ? $csvdata = $_POST['csvdata'] : $csvdata = "";

	if (isset($_POST['submit'])) {

		$users = array();
		if ( (isset($csvdata)) && (!empty($csvdata)) ) {

			$csvFormattedData = explode("\n", $csvdata);
		
			include 'library/opendb.php';

			// initialize some required variables

			$currDate = date('Y-m-d H:i:s');
			$currBy = $_SESSION['operator_user'];
			
			$passwordType = "Cleartext-Password";
			
			$userCount = 0;
			
			//var_dump($csvFormattedData);
			foreach($csvFormattedData as $csvLine) {
				//list($user, $pass) = explode(",", $csvLine);
				$users = explode(",", $csvLine);

				//makeing sure user and pass are specified and are not empty
				//columns by chance
				if ( (isset($users[0]) && (!empty($users[0])))
						&& 
						((isset($users[1]) && (!empty($users[1])))) )
					{

						$user = $dbSocket->escapeSimple($users[0]);
						$pass = $dbSocket->escapeSimple($users[1]);
						
						// insert username/password into radcheck
						$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK'].
								" (id,Username,Attribute,op,Value) ".
								" VALUES (0, '$user', '$passwordType', ".
								" ':=', '$pass')";
						$res = $dbSocket->query($sql);
						$logDebugSQL .= $sql . "\n";

						// insert user into userinfo table
						$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].
								" (id,username,creationdate,creationby) ".
								" VALUES (0, '$user', '$currDate', '$currBy')";
						$res = $dbSocket->query($sql);
						$logDebugSQL .= $sql . "\n";
						
						$userCount++;

					}
			}
			
			include 'library/closedb.php';

		   $successMsg = "Successfully imported a total of <b>$userCount</b> users to database";
		   $logAction .= "Successfully imported a total of <b>$userCount</b> users to database on page: ";
	   
		} else {
			
		   $failureMsg = "No CSV data was provided";
		   $logAction .= "Failed importing users, no CSV data was provided on page: ";
		}

	} //if (isset)


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
<?php

	include ("menu-mng-users.php");
	
?>

	<div id="contentnorightbar">
	
			<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngimportusers.php'] ?>
			<h144>+</h144></a></h2>
			
			<div id="helpPage" style="display:none;visibility:visible" >
				<?php echo $l['helpPage']['mngimportusers'] ?>
				<br/>
			</div>
			<?php
				include_once('include/management/actionMessages.php');
			?>

			<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

	<fieldset>

		<h302> <?php echo $l['title']['ImportUsers']; ?> </h302>
		<br/>

		<ul>


		Paste a CSV-formatted data input of users, expected format is: user,password<br/>
		Note: any CSV fields beyond the first 2 (user and password) are ignored<br/>
		<br/>
		<li class='fieldset'>
		<label for='csvdata' class='form'><?php echo $l['all']['CSVData'] ?></label>
		<textarea class='form_fileimport' name='csvdata' tabindex=101></textarea>
		</li>

	
		<li class='fieldset'>
		<br/>
		<hr><br/>
		<input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' tabindex=10000 class='button' />
		</li>

		</ul>
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





