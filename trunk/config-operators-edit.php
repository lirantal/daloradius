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
	include 'library/opendb.php';

	if (isset($_REQUEST['submit'])) {

		$operator_username = $_REQUEST['operator_username'];
		if (trim($operator_username) != "") {

                         $currDate = date('Y-m-d H:i:s');
                         $currBy = $_SESSION['operator_user'];

                         // set creation date for this operator
                         $sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." SET ".
				" updatedate='$currDate', updateby='$currBy' ".
				" WHERE username='$operator_username' ";
                         $res = $dbSocket->query($sql);
                         $logDebugSQL .= $sql . "\n";

			 foreach( $_POST as $field=>$value ) { 

				if ( ($field == "operator_username") || ($field == "submit") )	// we skip these post variables as they are not important
					continue;	

				if ( ($field == "lastlogin") )
					continue;	
					
					$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." SET ".
						" $field='$value' ".
						" WHERE username='$operator_username'";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";

	        } //foreach $_POST

			$successMsg = "Updated settings for: <b> $operator_username </b>";
			$logAction .= "Successfully updated settings for operator user [$operator_username] on page: ";
			
		} else { // if username != ""
			$failureMsg = "no operator user was entered, please specify an operator username to edit";
			$logAction .= "Failed updating settings for operator user [$operator_username] on page: ";
		}
	} // if isset post submit


	if (isset($_REQUEST['operator_username']))
		$operator_username = $_REQUEST['operator_username'];
	else
		$operator_username = "";

	if (trim($operator_username) != "") {
		$operator_username = $_REQUEST['operator_username'];
	} else {
		$failureMsg = "no operator user was entered, please specify an operator username to edit";
	}

	

	/* fill-in all the operator settings */

	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE UserName='$operator_username'";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";

	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
	$operator_password = $row['password'];
	$operator_firstname = $row['firstname'];
	$operator_lastname = $row['lastname'];
	$operator_title = $row['title'];
	$operator_department = $row['department'];
	$operator_company = $row['company'];
	$operator_phone1 = $row['phone1'];
	$operator_phone2 = $row['phone2'];
	$operator_email1 = $row['email1'];
	$operator_email2 = $row['email2'];
	$operator_messenger1 = $row['messenger1'];
	$operator_messenger2 = $row['messenger2'];
	$operator_notes = $row['notes'];
	$operator_lastlogin = $row['lastlogin'];
	$operator_creationdate = $row['creationdate'];
	$operator_creationby = $row['creationby'];
	$operator_updatedate = $row['updatedate'];
	$operator_updateby = $row['updateby'];

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


<?php
        include_once ("library/tabber/tab-layout.php");
?>
 
<?php

	include ("menu-config-operators.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['configoperatorsedit.php'] ?>
				<h144>+</h144></a></h2>
				
                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['configoperatorsedit'] ?>
					<br/>
				</div>
                <?php
					include_once('include/management/actionMessages.php');
                ?>


				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
				<input type="hidden" value="<?php echo $operator_username ?>" name="operator_username" />

<div class="tabber">

     <div class="tabbertab" title="Operator Info">

	<fieldset>
	
		<h302> Operator Settings </h302>
		<br/>

                <label for='operator_password' class='form'>Operator Password</label>
                <input name='password' id='password'
		<?php
			if ($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] == "yes")
				echo "type='password'";
			else 
				echo "type='text'";
		?>
			value='<?php if (isset($operator_password)) echo $operator_password ?>' tabindex=101 />
                <br/>

                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' class='button' />

	</fieldset>


	</div>
     <div class="tabbertab" title="Contact Info">

<?php
	include_once('include/management/operatorinfo.php');
?>


	</div>
     <div class="tabbertab" title="ACL Settings">

<?php
        include_once('include/management/operator_tables.php');
        drawPagesPermissions($arrayPagesAvailable, $operator_username);
?>

                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' class='button' />

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

