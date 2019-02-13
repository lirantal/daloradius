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
 * 			Filippo Maria Del Prete <filippo.delprete@gmail.com>
 *
 *********************************************************************************************************
 */
 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	include 'library/opendb.php';

        isset($_REQUEST['paymentname']) ? $paymentname = $_REQUEST['paymentname'] : $paymentname = "";
        isset($_REQUEST['paymentnotes']) ? $paymentnotes = $_REQUEST['paymentnotes'] : $paymentnotes = "";

	$edit_paymentname = $paymentname; //feed the sidebar variables	

	$logAction = "";
	$logDebugSQL = "";

	if (isset($_POST['submit'])) {

                $paymentname = $_POST['paymentname'];
                $paymentnotes = $_POST['paymentnotes'];

		if (trim($paymentname) != "") {

			$currDate = date('Y-m-d H:i:s');
			$currBy = $_SESSION['operator_user'];

			$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES']." SET ".
			" value='".$dbSocket->escapeSimple($paymentname)."', ".
			" notes='".$dbSocket->escapeSimple($paymentnotes).	"', ".
			" updatedate='$currDate', updateby='$currBy' ".
			" WHERE value='".$dbSocket->escapeSimple($paymentname)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL = "";
			$logDebugSQL .= $sql . "\n";
			
			$successMsg = "Updated payment type: <b> $paymentname </b>";
			$logAction .= "Successfully updated payment type [$paymentname] on page: ";
			
		} else {
			$failureMsg = "no payment type was entered, please specify a payment type to edit.";
			$logAction .= "Failed updating payment type [$paymentname] on page: ";
		}
		
	}
	

	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES']." WHERE value='".$dbSocket->escapeSimple($paymentname)."'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	$row = $res->fetchRow();
	$paymenttype = $row[1];
	$paymentnotes = $row[2];
	$creationdate = $row[3];
	$creationby = $row[4];
	$updatedate = $row[5];
	$updateby = $row[6];

	include 'library/closedb.php';


	if (trim($paymentname) == "") {
		$failureMsg = "no payment type was entered or found in database, please specify a payment type to edit";
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
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<?php
	include_once ("library/tabber/tab-layout.php");
?>
 
<?php
	include ("menu-bill-payments.php");
?>		
	<div id="contentnorightbar">
		
		<h2 id="Intro" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','paymenttypesedit.php') ?>
		:: <?php if (isset($paymentname)) { echo $paymentname; } ?><h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','paymenttypesedit') ?>
			<br/>
		</div>
		<?php
			include_once('include/management/actionMessages.php');
		?>

		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

	<div class="tabbertab" title="<?php echo t('title','PayTypeInfo'); ?>">


	<fieldset>

		<h302> <?php echo t('title','PayTypeInfo'); ?> </h302>
		<br/>

		<ul>

			<li class='fieldset'>
			<label for='paymentname' class='form'><?php echo t('all','PayTypeName') ?></label>
			<input disabled name='paymentname' type='text' id='paymentname' value='<?php echo $paymentname ?>' tabindex=100 />
			</li>

			<li class='fieldset'>
			<label for='paymentnotes' class='form'><?php echo t('all','PayTypeNotes') ?></label>

	                <input class='text' name='paymentnotes' type='text' id='paymentnotes' value='<?php echo $paymentnotes ?>' tabindex=101 />

			</li>

			<li class='fieldset'>
			<br/>
			<hr><br/>
			<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000
				class='button' />
			</li>

		</ul>

	</fieldset>

	<input type=hidden value="<?php echo $paymentname ?>" name="paymentname"/>

</div>

<div class="tabbertab" title="<?php echo t('title','Optional'); ?>">

<fieldset>

        <h302> Optional </h302>
        <br/>

        <br/>
        <h301> Other </h301>
        <br/>

        <br/>
        <label for='creationdate' class='form'><?php echo t('all','CreationDate') ?></label>
        <input disabled value='<?php if (isset($creationdate)) echo $creationdate ?>' tabindex=313 />
        <br/>

        <label for='creationby' class='form'><?php echo t('all','CreationBy') ?></label>
        <input disabled value='<?php if (isset($creationby)) echo $creationby ?>' tabindex=314 />
        <br/>

        <label for='updatedate' class='form'><?php echo t('all','UpdateDate') ?></label>
        <input disabled value='<?php if (isset($updatedate)) echo $updatedate ?>' tabindex=315 />
        <br/>

        <label for='updateby' class='form'><?php echo t('all','UpdateBy') ?></label>
        <input disabled value='<?php if (isset($updateby)) echo $updateby ?>' tabindex=316 />
        <br/>


        <br/><br/>
        <hr><br/>

        <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000
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





