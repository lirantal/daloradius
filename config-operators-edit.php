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
	$operator_id = $_SESSION['operator_id'];

	include('library/check_operator_perm.php');

	$logAction = "";
	$logDebugSQL = "";
	include 'library/opendb.php';

	if (isset($_POST['submit'])) {

		$operator_username = $dbSocket->escapeSimple($_POST['operator_username']);
		if (trim($operator_username) != "") {

			$currDate = date('Y-m-d H:i:s');
			$currBy = $_SESSION['operator_user'];
			
			(isset($_POST['password'])) ? $operator_password = $_POST['password'] : $operator_password = "";
			(isset($_POST['firstname'])) ? $firstname = $_POST['firstname'] : $firstname = "";
			(isset($_POST['lastname'])) ? $lastname = $_POST['lastname'] : $lastname = "";
			(isset($_POST['title'])) ? $title = $_POST['title'] : $title = "";
			(isset($_POST['department'])) ? $department = $_POST['department'] : $department = "";
			(isset($_POST['company'])) ? $company = $_POST['company'] : $company = "";
			(isset($_POST['phone1'])) ? $phone1 = $_POST['phone1'] : $phone1 = "";
			(isset($_POST['phone2'])) ? $phone2 = $_POST['phone2'] : $phone2 = "";
			(isset($_POST['email1'])) ? $email1 = $_POST['email1'] : $email1 = "";
			(isset($_POST['email2'])) ? $email2 = $_POST['email2'] : $email2 = "";
			(isset($_POST['messenger1'])) ? $messenger1 = $_POST['messenger1'] : $messenger1 = "";
			(isset($_POST['messenger2'])) ? $messenger2 = $_POST['messenger2'] : $messenger2 = "";
			(isset($_POST['notes'])) ? $notes = $_POST['notes'] : $notes = "";
			
			// update username and password of operator into the database
			$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOOPERATORS'].
				" SET ".
				"password='".$dbSocket->escapeSimple($operator_password)."', ".
				"firstname='".$dbSocket->escapeSimple($firstname)."', ".
				"lastname='".$dbSocket->escapeSimple($lastname)."', ".
				"title='".$dbSocket->escapeSimple($title)."', ".
				"department='".$dbSocket->escapeSimple($department)."', ".
				"company='".$dbSocket->escapeSimple($company)."', ".
				"phone1='".$dbSocket->escapeSimple($phone1)."', ".
				"phone2='".$dbSocket->escapeSimple($phone2)."', ".
				"email1='".$dbSocket->escapeSimple($email1)."', ".
				"email2='".$dbSocket->escapeSimple($email2)."', ".
				"messenger1='".$dbSocket->escapeSimple($messenger1)."', ".
				"messenger2='".$dbSocket->escapeSimple($messenger2)."', ".
				"updatedate='$currDate', updateby='$currBy' ".
				" WHERE username='$operator_username' ";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";

			$sql = "SELECT id FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATORS']." WHERE username='".
					$operator_username."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";
			
			if ($res->numRows() == 1) {
				
				$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
				$curr_operator_id = $row['id'];
			
				// insert operators acl for this operator
				foreach ($_POST as $field => $value ) {
					
					if ( preg_match('/^ACL_/', $field) ) {
						$access = $value;
						$file = substr($field, 4);
						
						$sql = "SELECT id FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'].
								" WHERE operator_id='".$curr_operator_id."'".
								" AND file='$file'";
						$res = $dbSocket->query($sql);
						$logDebugSQL .= $sql . "\n";
						
						if ($res->numRows() == 0) {
							$sql = "INSERT INTO  ".$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'].
								" ( operator_id, file, access ) VALUES ".
								" ( '$curr_operator_id', '$file', '$access') ";
							$res = $dbSocket->query($sql);
							$logDebugSQL .= $sql . "\n";
							
						} else {
							$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL']." SET ".
								" access='$access' ".
								" WHERE file='$file' AND operator_id='$curr_operator_id'";
							$res = $dbSocket->query($sql);
							$logDebugSQL .= $sql . "\n";
						}
						
					}
			
				} // foreach
			
			} //if numrows()
			
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

	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATORS']." WHERE UserName='$operator_username'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
	$curr_operator_id = $row['id'];
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
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','configoperatorsedit.php') ?>
				<h144>&#x2754;</h144></a></h2>
				
                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo t('helpPage','configoperatorsedit') ?>
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

                <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />

	</fieldset>


	</div>
     <div class="tabbertab" title="Contact Info">

<?php
	include_once('include/management/operatorinfo.php');
?>


	</div>
     <div class="tabbertab" title="ACL Settings">

	 <fieldset>
<?php
        include_once('include/management/operator_acls.php');
        drawOperatorACLs($curr_operator_id);
?>
	<br/>

	<br/><br/>
	<hr><br/>

	<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />
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

