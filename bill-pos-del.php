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

	
	isset($_GET['username']) ? $username = $_GET['username'] : $username = "";

	$logAction = "";
	$logDebugSQL = "";

	$showRemoveDiv = "block";

	if ($username != '') {

		$allUsernames = "";
		$isSuccessful = 0;

		/* since the foreach loop will report an error/notice of undefined variable $value because
		   it is possible that the $username is not an array, but rather a simple GET request
		   with just some value, in this case we check if it's not an array and convert it to one with
		   a NULL 2nd element
		*/
		if (!is_array($username))
			$username = array($username, NULL);

		foreach ($username as $variable => $value) {

			if (trim($variable) != "") {
			
				$username = $value;
				$allUsernames .= $username . ", ";

				include 'library/opendb.php';
				
				// get user id from userbillinfo table 
				$sql = "SELECT id FROM ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO']." WHERE username='".$dbSocket->escapeSimple($username)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
				$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
				$userId = $row['id'];
				
				
				// delete all attributes associated with a username
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE Username='".$dbSocket->escapeSimple($username)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADREPLY']." WHERE Username='".$dbSocket->escapeSimple($username)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOUSERINFO']." WHERE Username='".$dbSocket->escapeSimple($username)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO']." WHERE Username='".$dbSocket->escapeSimple($username)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." WHERE Username='".$dbSocket->escapeSimple($username)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				if (strtolower($delradacct) == "yes") {
					$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." WHERE Username='".$dbSocket->escapeSimple($username)."'";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}
				
				
				// to remove all invoices and payments we need to get the invoices_id
				$sql1 = "SELECT id FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE']." WHERE user_id=".$userId;
				$res2 = $dbSocket->query($sql1);
				$logDebugSQL .= $sql1 . "\n";
				while ($row = $res2->fetchRow(DB_FETCHMODE_ASSOC)) {

					// delete all invoice items
					$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS']." WHERE invoice_id='".$row['id']."'";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";					
						
					// delete all payment items
					$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOPAYMENTS']." WHERE invoice_id='".$row['id']."'";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
					
				}
				
				// remove all invoices by this user
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE']." WHERE user_id='".$userId."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
				

				$successMsg = "Deleted user(s): <b> $allUsernames </b>";
				$logAction .= "Successfully deleted user(s) [$allUsernames] on page: ";

				include 'library/closedb.php';

			}  else { 
				$failureMsg = "no user was entered, please specify a username to remove from database";		
				$logAction .= "Failed deleting user(s) [$allUsernames] on page: ";
			}


		$showRemoveDiv = "none";

		}//foreach
		
		
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

</head>
 
<?php

	include ("menu-bill-pos.php");
	
?>


<div id="contentnorightbar">
	
	<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','billposdel.php') ?>
	:: <?php if (isset($username)) { echo $username; } ?><h144>&#x2754;</h144></a></h2>

	<div id="helpPage" style="display:none;visibility:visible" >
		<?php echo t('helpPage','billposdel') ?>
		<br/>
	</div>
	<?php
		include_once('include/management/actionMessages.php');
	?>

	<div id="removeDiv" style="display:<?php echo $showRemoveDiv ?>;visibility:visible" >
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
	
	<fieldset>

		<h302> <?php echo t('title','AccountRemoval') ?> </h302>
		<br/>

		<label for='username' class='form'><?php echo t('all','Username')?></label>
		<input name='username[]' type='text' id='username' value='<?php echo $username ?>' tabindex=100 />
		<br />

		<label for='delradacct' class='form'><?php echo t('all','RemoveRadacctRecords')?></label>
		<select class='form' tabindex=102 name='delradacct' tabindex=101>
			<option value='no'>no</option>
			<option value='yes'>yes</option>
		</select>
		<br />

		<br/><br/>
		<hr><br/>
		<input type="submit" name="submit" value="<?php echo t('buttons','apply') ?>" tabindex=1000 
			class='button' />

	</fieldset>
	
	</form>
	</div>


<?php
        include_once("include/management/autocomplete.php");

        if ($autoComplete) {
                echo "<script type=\"text/javascript\">
                      autoComEdit = new DHTMLSuite.autoComplete();
                      autoComEdit.add('username','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');
                      </script>";
        }
?>


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


