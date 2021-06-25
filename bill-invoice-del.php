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

	isset($_REQUEST['invoice_id']) ? $invoice_id = $_REQUEST['invoice_id'] : $invoice_id = "";
	$logAction = "";
	$logDebugSQL = "";

	$showRemoveDiv = "block";

	if (isset($_REQUEST['invoice_id'])) {

		if (!is_array($invoice_id))
			$invoice_id = array($invoice_id);

		$allInvoices = "";

		include 'library/opendb.php';
	
		foreach ($invoice_id as $variable=>$value) {
			if (trim($value) != "") {

				$invoice_id_single = $value;
				$allInvoices .= $invoice_id_single . ", ";

				// remove invoice id 
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'].
						" WHERE id='".$dbSocket->escapeSimple($invoice_id_single)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				// remove invoice items associated with this invoice id
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'].
						" WHERE invoice_id='".$dbSocket->escapeSimple($invoice_id_single)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
				
				$successMsg = "Deleted invoice id(s): <b> $allInvoices </b>";
				$logAction .= "Successfully deleted invoice id(s) [$allInvoices] on page: ";
				
			} else { 
				$failureMsg = "no invoice id was entered, please specify an invoice id to remove from database";
				$logAction .= "Failed deleting invoice id(s) [$allInvoices] on page: ";
			}

		} //foreach

		$plans = "";
		include 'library/closedb.php';

		$showRemoveDiv = "none";
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
	include ("menu-bill-invoice.php");
?>		

<div id="contentnorightbar">

	<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','billinvoicedel.php') ?>
	:: <?php if (isset($invoice_id)) { echo $invoice_id; } ?><h144>&#x2754;</h144></a></h2>

	<div id="helpPage" style="display:none;visibility:visible" >
		<?php echo t('helpPage','billinvoicedel') ?>
		<br/>
	</div>
	<?php
		include_once('include/management/actionMessages.php');
	?>

	<div id="removeDiv" style="display:<?php echo $showRemoveDiv ?>;visibility:visible" >
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

	<fieldset>

		<h302> <?php echo t('title','InvoiceRemoval') ?> </h302>
		<br/>

		<label for='invoice_id' class='form'><?php echo t('all','InvoiceID') ?></label>
		<input name='invoice_id[]' type='text' id='invoice_id' value='<?php echo $invoice_id ?>' tabindex=100 autocomplete="off" />
		<br/>

		<br/><br/>
		<hr><br/>

		<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=1000 
			class='button' />

	</fieldset>

	</form>
	</div>

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





