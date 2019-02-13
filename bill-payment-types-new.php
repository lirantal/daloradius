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

	isset($_POST['paymentname']) ? $paymentname = $_POST['paymentname'] : $paymentname = "";
	isset($_POST['paymentnotes']) ? $paymentnotes = $_POST['paymentnotes'] : $paymentnotes = "";

	$logAction = "";
	$logDebugSQL = "";

	if (isset($_POST["submit"])) {
		$paymentname = $_POST['paymentname'];
		$paymentnotes = $_POST['paymentnotes'];
		
		include 'library/opendb.php';

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES']." WHERE value='".$dbSocket->escapeSimple($paymentname)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 0) {
			if (trim($paymentname) != "") {

				$currDate = date('Y-m-d H:i:s');
				$currBy = $_SESSION['operator_user'];
				
				// insert apyment type info
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'].
					" (id, value, notes, ".
					"  creationdate, creationby, updatedate, updateby) ".
					" VALUES (0, '".$dbSocket->escapeSimple($paymentname)."', '".
					$dbSocket->escapeSimple($paymentnotes)."', ".
					" '$currDate', '$currBy', NULL, NULL)";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$successMsg = "Added to database new payment type: <b>$paymentname</b>";
				$logAction .= "Successfully added new payment type [$paymentname] on page: ";
			} else {
				$failureMsg = "you must provide a payment type name";	
				$logAction .= "Failed adding new payment type [$paymentname] on page: ";	
			}
		} else { 
			$failureMsg = "You have tried to add a payment type that already exist in the database: $paymentname";
			$logAction .= "Failed adding new payment type already in database [$paymentname] on page: ";		
		}
	
		include 'library/closedb.php';

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

	<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','paymenttypesnew.php') ?>
	<h144>&#x2754;</h144></a></h2>
	
	<div id="helpPage" style="display:none;visibility:visible" >
		<?php echo t('helpPage','paymenttypesnew') ?>
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
		<label for='name' class='form'><?php echo t('all','PayTypeName') ?></label>
		<input name='paymentname' type='text' id='paymentname' value='' tabindex=100 />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('paymentTypeTooltip')" /> 
		
		<div id='paymentTypeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','paymentTypeTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='paymentnotes' class='form'><?php echo t('all','PayTypeNotes') ?></label>
		<input name='paymentnotes' type='text' id='paymentnotes' value='' tabindex=101 />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('paymentTypeNotesTooltip')" /> 
		
		<div id='paymentTypeNotesTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','paymentTypeNotesTooltip') ?>
		</div>
		</li>
	
		<li class='fieldset'>
		<br/>
		<hr><br/>
		<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000 class='button' />
		</li>

		</ul>
	</fieldset>

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





