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

	if (isset($_POST['submit'])) {
		(isset($_POST['operator_username'])) ? $operator_username = $_POST['operator_username'] : $operator_username = "";
		(isset($_POST['operator_password'])) ? $operator_password = $_POST['operator_password'] : $operator_password = "";
		
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

		include 'library/opendb.php';

		if ( (trim($operator_username) != "") && (trim($operator_password) != "") ) {

			$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATORS']." WHERE username='".
					$dbSocket->escapeSimple($operator_username)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";
			
			// there is no operator in the database with this username
			if ($res->numRows() == 0) {

				$currDate = date('Y-m-d H:i:s');
				$currBy = $_SESSION['operator_user'];

				// insert username and password of operator into the database
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOOPERATORS'].
					" (id, username, password, firstname, lastname, title, department, company, ".
					" phone1, phone2, email1, email2, messenger1, messenger2, notes, ".
					" creationdate, creationby, updatedate, updateby) VALUES (0, ".
					"'".$dbSocket->escapeSimple($operator_username)."', ".
					"'".$dbSocket->escapeSimple($operator_password)."', ".
					"'".$dbSocket->escapeSimple($firstname)."', ".
					"'".$dbSocket->escapeSimple($lastname)."', ".
					"'".$dbSocket->escapeSimple($title)."', ".
					"'".$dbSocket->escapeSimple($department)."', ".
					"'".$dbSocket->escapeSimple($company)."', ".
					"'".$dbSocket->escapeSimple($phone1)."', ".
					"'".$dbSocket->escapeSimple($phone2)."', ".
					"'".$dbSocket->escapeSimple($email1)."', ".
					"'".$dbSocket->escapeSimple($email2)."', ".
					"'".$dbSocket->escapeSimple($messenger1)."', ".
					"'".$dbSocket->escapeSimple($messenger2)."', ".
					"'".$dbSocket->escapeSimple($notes)."', ".
					" '$currDate', '$currBy', '$currDate', '$currBy' ".
					" )";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				
				// lets make sure we've inserted the new operator successfully and grab his operator_id
				$sql = "SELECT id FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATORS']." WHERE username='".
						$dbSocket->escapeSimple($operator_username)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
				
				if ($res->numRows() == 1) {
					
					$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
					$new_operator_id = $row['id'];

					// insert operators acl for this operator
					foreach ($_POST as $field => $value ) {
						
						if ( preg_match('/^ACL_/', $field) ) {
							$access = $value;
							$file = substr($field, 4);
							
							$sql = "INSERT INTO  ".$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'].
								" ( operator_id, file, access ) VALUES ".
								" ( '$new_operator_id', '$file', '$access') ";
							$res = $dbSocket->query($sql);
							$logDebugSQL .= $sql . "\n";
						}
	
					} // foreach
				
				} //if numrows()
				

				$successMsg = "Added to database new operator user: <b> $operator_username </b>";
				$logAction .= "Successfully added new operator user [$operator_username] on page: ";

			} else {
				// if statement returns false which means there is at least one operator
				// in the database with the same username

				$failureMsg = "operator user already exist in database: <b> $operator_username </b>";
				$logAction .= "Failed adding new operator user already existing in database [$operator_username] on page: ";
			}
			
		} else {
			// if statement returns false which means that the user has left an empty field for
			// either the username or password, or both

			$failureMsg = "username or password are empty";
			$logAction .= "Failed adding (possible empty user/pass) new operator user [$operator_username] on page: ";
		}


	include 'library/closedb.php';

	} // if form was submitted
	

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


<?php
        include_once ("library/tabber/tab-layout.php");
?>
 
<?php

	include ("menu-config-operators.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','configoperatorsnew.php') ?>
				<h144>&#x2754;</h144></a></h2>
				
                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo t('helpPage','configoperatorsnew') ?>
					<br/>
				</div>
                <?php
					include_once('include/management/actionMessages.php');
                ?>

				<form name="newoperator" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="Operator Info">

	<fieldset>

                <h302>Account Settings</h302>
		<br/>

                <label for='operator_username' class='form'>Operator Username</label>
                <input name='operator_username' type='text' id='operator_username' 
			value='<?php if (isset($operator_username)) echo $operator_username ?>' tabindex=100 />
                <br/>

                <label for='operator_password' class='form'>Operator Password</label>
                <input name='operator_password' id='operator_password' 
			value='<?php if (isset($operator_password)) echo $operator_password ?>' 
			type='<?php if (isset($operator_hiddenPassword)) echo $hiddenPassword; else echo "text"; ?>'
			tabindex=101 />
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
        drawOperatorACLs(0);
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





