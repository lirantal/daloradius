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
    
	// invoice details
	isset($_REQUEST['invoice_id']) ? $invoice_id = $_REQUEST['invoice_id'] : $invoice_id = "";
	
	isset($_POST['invoice_status_id']) ? $invoice_status_id = $_POST['invoice_status_id'] : $invoice_status_id = "";
	isset($_POST['invoice_type_id']) ? $invoice_type_id = $_POST['invoice_type_id'] : $invoice_type_id = "";
	isset($_POST['invoice_date']) ? $invoice_date = $_POST['invoice_date'] : $invoice_date = "";
	isset($_POST['invoice_notes']) ? $invoice_notes = $_POST['invoice_notes'] : $invoice_notes = "";
		
	$username = $login;
	$showInvoice = false;

	include 'library/opendb.php';
	
	$sql = "SELECT id, contactperson, city, state, username FROM ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
	" WHERE username = '".$dbSocket->escapeSimple($username)."'";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
	$user_id = $row['id'];
	
	$userInfo['contactperson'] = $row['contactperson'];
	$userInfo['username'] = $row['username'];
	$userInfo['city'] = $row['city'];
	$userInfo['state'] = $row['state'];
	
	$logAction = "";
	$logDebugSQL = "";


	if (trim($invoice_id) != "" && $user_id) {
		
		// get invoice details
		$sql = "SELECT a.id, a.date, a.status_id, a.type_id, a.user_id, a.notes, b.contactperson, b.username, ".
				" b.city, b.state, f.value as type, ".
				" c.value AS status, COALESCE(e2.totalpayed, 0) as totalpayed, COALESCE(d2.totalbilled, 0) as totalbilled ".
				" FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE']." AS a".
				" INNER JOIN ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO']." AS b ON (a.user_id = b.id) ".
				" INNER JOIN ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS']." AS c ON (a.status_id = c.id) ".
				" INNER JOIN ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICETYPE']." AS f ON (a.type_id = f.id) ".
				" LEFT JOIN (SELECT SUM(d.amount + d.tax_amount) ".
					" as totalbilled, invoice_id, amount, tax_amount, notes, plan_id FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS']." AS d ".
					" GROUP BY d.invoice_id) AS d2 ON (d2.invoice_id = a.id) ".
				" LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']." AS bp2 ON (bp2.id = d2.plan_id) ".
				" LEFT JOIN (SELECT SUM(e.amount) as totalpayed, invoice_id FROM ". 
				$configValues['CONFIG_DB_TBL_DALOPAYMENTS']." AS e GROUP BY e.invoice_id) AS e2 ON (e2.invoice_id = a.id) ".
				" WHERE a.id = '".$dbSocket->escapeSimple($invoice_id)."'".
				" AND a.user_id = ".$dbSocket->escapeSimple($user_id).
				" GROUP BY a.id ";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";
	
		$edit_invoiceid = $invoice_id;
		$invoiceDetails = $res->fetchRow(DB_FETCHMODE_ASSOC);
		if ($invoiceDetails)
			$showInvoice = true;
				
	}
	
	include 'library/closedb.php';
	
	include_once('library/config_read.php');
    $log = "visited page: ";

    
	include_once('include/management/populate_selectbox.php');
	
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
<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>
<script type="text/javascript" src="library/javascript/pages_common.js"></script>
<script type="text/javascript" src="library/javascript/ajax.js"></script>
<script type="text/javascript" src="library/javascript/dynamic_attributes.js"></script>
<script type="text/javascript" src="library/javascript/ajaxGeneric.js"></script>

<?php
	include ("menu-billing.php");	
?>

<div id="contentnorightbar">

	<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','billinvoiceedit.php') ?>
	<h144>&#x2754;</h144></a></h2>
	
	<div id="helpPage" style="display:none;visibility:visible" >
		<?php echo t('helpPage','billinvoicesedit') ?>
		<br/>
	</div>
	<?php
		include_once('include/management/actionMessages.php');
	?>

	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

	<div class="tabbertab" title="<?php echo t('title','Invoice'); ?>">
	<fieldset>

		<h2> Invoice Details </h2>
		<h302> <?php echo t('title','Invoice'); ?> </h302>

		<ul>

		<?php
		echo $invoiceDetails['contactperson'].'</a><br/>'.
			$invoiceDetails['city']. (!empty($invoiceDetails['state']) ? ', '.$invoiceDetails['state'] : '' );
		echo '</b>';
		?>
		<br/><br/>

		<li class='fieldset'>
		<label for='' class='form'><?php echo t('all','TotalBilled')?></label>
		<input name='' type='text' disabled id='' value='<?php echo $invoiceDetails['totalbilled']?>' tabindex=101 />
		</li>
		
		<li class='fieldset'>
		<label for='' class='form'><?php echo t('all','TotalPayed')?></label>
		<input name='' type='text' disabled id='' value='<?php echo $invoiceDetails['totalpayed']?>' tabindex=101 />
		</li>
		
		<li class='fieldset'>
		<label for='' class='form'><?php echo t('all','Balance')?></label>
		<input name='' type='text' disabled id='' value='<?php echo (float) ($invoiceDetails['totalpayed'] - $invoiceDetails['totalbilled'])?>' tabindex=101 />
		</li>
		
		<li class='fieldset'>
		<label for='' class='form'><?php echo t('all','InvoiceStatus')?></label>
		<input name='' type='text' disabled id='' value='<?php echo $invoiceDetails['status']?>' tabindex=101 />
		</li>
		
		<li class='fieldset'>
		<label for='' class='form'><?php echo t('all','InvoiceType')?></label>
		<input name='' type='text' disabled id='' value='<?php echo $invoiceDetails['type']?>' tabindex=101 />
		</li>

		<br/>

		<label for='invoice_date' class='form'><?php echo t('all','Date')?></label>		
		<input disabled value='<?php echo $invoiceDetails['date']?>' id='invoice_date' name='invoice_date'  tabindex=108 />
		<br/>


		<label for='invoice_notes' class='form'><?php echo t('ContactInfo','Notes')?></label>
		<textarea disabled class='form' name='invoice_notes' ><?php echo $invoiceDetails['notes']?></textarea>

		<li class='fieldset'>
		<br/>
		<br/>
		
		<input class='button' type='button' value='Download Invoice' onClick="javascript:window.location.href='include/common/notificationsUserInvoice.php?invoice_id=<?php echo $invoice_id ?>&destination=download'"/>
	  	              			
		<br/><br/>
		</li>
		
		</ul>
	
	</fieldset>
	</div>


			
	<div class="tabbertab" title="<?php echo t('title','Items'); ?>">
	<fieldset>

		<h2> Item Listing </h2>
		<h302> <?php echo t('title','Items'); ?> </h302>

		<input type="hidden" value="0" id="counter" />

		<table BORDER="7" CELLPADDING="10">
		<tbody id="container">
		<tr>
			<th>Plan</th>
			<th>Item Amount</th>
			<th>Item Tax</th>
			<th>Notes</th>
		</tr>
		
		<?php
		
			if (!empty($invoice_id) && $showInvoice) {
		
				include 'library/opendb.php';
		
				// get all invoice items
				$sql = 'SELECT a.id, a.plan_id, a.amount, a.tax_amount, a.notes, b.planName '.
						' FROM '.$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'].' a '.
						' LEFT JOIN '.$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].' b ON a.plan_id = b.id '.
						' WHERE a.invoice_id = '.$invoice_id.' ORDER BY a.id ASC';
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
				
				while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
					
					$itemName = $row['id'];
					
					echo "<tr id='itemsRow_".$itemName."'>
						<td> <input disabled type='text' id='_item".$itemName."' value='".$row['planName']."' name='_item".$itemName."[plan]' /> </td>
						<td> <input disabled type='text' id='_item".$itemName."' value='".$row['amount']."'name='item_".$itemName."[amount]' /> </td>
						<td> <input disabled type='text' id='_item".$itemName."' value='".$row['tax_amount']."'name='item_".$itemName."[tax]' /> </td>
						<td> <input disabled type='text' id='_item".$row['plan_id']."' value='".$row['notes']."' name='item_".$itemName."[notes]' /> </td>
						</tr>";
					
				}
				
				include 'library/closedb.php';
							
			} 
		?>
		
		
		
		</tbody>
		</table>
		
	</fieldset>
	</div>
	

	<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>

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

</body>
</html>
