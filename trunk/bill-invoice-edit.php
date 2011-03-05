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

	// invoice details
	
	isset($_REQUEST['invoice_id']) ? $invoice_id = $_REQUEST['invoice_id'] : $invoice_id = "";
	
	isset($_POST['invoice_status_id']) ? $invoice_status_id = $_POST['invoice_status_id'] : $invoice_status_id = "";
	isset($_POST['invoice_type_id']) ? $invoice_type_id = $_POST['invoice_type_id'] : $invoice_type_id = "";
	isset($_POST['user_id']) ? $user_id = $_POST['user_id'] : $user_id = "";
	isset($_POST['invoice_date']) ? $invoice_date = $_POST['invoice_date'] : $invoice_date = "";
	isset($_POST['invoice_notes']) ? $invoice_notes = $_POST['invoice_notes'] : $invoice_notes = "";
	
	isset($_POST['invoice_items']) ? $invoice_items = $_POST['invoice_items'] : $invoice_items = "";


	$logAction = "";
	$logDebugSQL = "";

	
	include 'library/opendb.php';
	
	if (isset($_POST["submit"])) {
				
		if (trim($invoice_id) != "") {

			$currDate = date('Y-m-d H:i:s');
			$currBy = $_SESSION['operator_user'];

			$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE']." SET ".
			" date='".$dbSocket->escapeSimple($invoice_date)."', ".
			" status_id='".$dbSocket->escapeSimple($invoice_status_id)."', ".
			" type_id='".$dbSocket->escapeSimple($invoice_type_id)."', ".
			" notes='".$dbSocket->escapeSimple($invoice_notes)."', ".
			" updatedate='$currDate', updateby='$currBy' ".
			" WHERE id='".$dbSocket->escapeSimple($invoice_id)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";

			if (!PEAR::isError($res)) {
/*
				$invoice_id = $dbSocket->getOne( "SELECT LAST_INSERT_ID() FROM `".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE']."`" );  
				var_dump($invoice_id);
				
				// add the invoice items which the user created
				addInvoiceItems($dbSocket, $invoice_id, $invoice_items);
*/				
				$successMsg = "Added to database new invoice: <b>$invoice_id</b>";
				$logAction .= "Successfully added new invoice [$invoice_id] on page: ";
				
			} else {
				
				$failureMsg = "Error in executing invoice INSERT statement";	
				$logAction .= "Failed adding new invoice on page: ";

			}
			
		} else {
			$failureMsg = "you must provide a user id which matches the userbillinfo records";	
			$logAction .= "Failed adding new invoice on page: ";	
		}
	
	}


	function addInvoiceItems($dbSocket, $invoice_id, $invoice_items) {

		global $logDebugSQL;
		global $configValues;

		$currDate = date('Y-m-d H:i:s');
		$currBy = $_SESSION['operator_user'];
	
		// insert invoice's items
		if (isset($invoice_items)) {

			foreach ($invoice_items as $item) {

				$planId = $item['planId'];
				$amount = $item['amount'];
				$tax = $item['tax'];
				$notes = $item['notes'];
				
				if ($invoice_id) {
					$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'].
						" (id, invoice_id, plan_id, amount, tax_amount, notes, ".
						" creationdate, creationby, updatedate, updateby) ".
						" VALUES (0, '".$dbSocket->escapeSimple($invoice_id)."', '".
						$dbSocket->escapeSimple($planId)."', '".
						$dbSocket->escapeSimple($amount)."', '".
						$dbSocket->escapeSimple($tax)."', '".
						$dbSocket->escapeSimple($notes)."', ".
						" '$currDate', '$currBy', NULL, NULL)";

					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}
			}
		}
	}
	
	
	if (trim($invoice_id) != "") {
		
		// get invoice details
		$sql = "SELECT a.id, a.date, a.status_id, a.type_id, a.user_id, a.notes, b.contactperson, b.username, ".
				" b.city, b.state, f.value as type, bp2.planName as planName, bp2.id as planId, ".
				" c.value AS status, COALESCE(e2.totalpayed, 0) as totalpayed, COALESCE(d2.totalbilled, 0) as totalbilled, d2.plan_id, ".
				" d2.amount as itemAmount, d2.tax_amount as itemTaxAmount, d2.notes as itemNotes ".
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
				" GROUP BY a.id ";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";
	
		$edit_invoiceid = $invoice_id;
		$invoiceDetails = $res->fetchRow(DB_FETCHMODE_ASSOC);
	}
	
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
	include_once ("library/tabber/tab-layout.php");
?>
 
<?php

	include ("menu-bill-invoice.php");
	
?>

<div id="contentnorightbar">

	<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['billinvoiceedit.php'] ?>
	<h144>+</h144></a></h2>
	
	<div id="helpPage" style="display:none;visibility:visible" >
		<?php echo $l['helpPage']['billinvoicesedit'] ?>
		<br/>
	</div>
	<?php
		include_once('include/management/actionMessages.php');
	?>

	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

	<div class="tabbertab" title="<?php echo $l['title']['Invoice']; ?>">
	<fieldset>

		<h302> <?php echo $l['title']['Invoice']; ?> </h302>

		<ul>

		<?php
		echo 'Customer:<b/><br/>'; 
		echo '<a href="/bill-pos-edit.php?username='.$invoiceDetails['username'].'">'.$invoiceDetails['contactperson'].'</a><br/>'.
			$invoiceDetails['city']. (!empty($invoiceDetails['state']) ? ', '.$invoiceDetails['state'] : '' );
		echo '</b>';
		?>
		<br/>

					<input class="button" type="button" value="New Payment" 
						onClick="javascript:window.location = 'bill-payments-new.php?payment_invoice_id=<?php echo $invoiceDetails['id'] ?>';" />
						

					<input class="button" type="button" value="Show Payments" 
						onClick="javascript:window.location = 'bill-payments-list.php?invoice_id=<?php echo $invoiceDetails['id'] ?>';" />
						
		<br/><br/>

		<!--  hidden invoice_id field -->
		<input type='hidden' name='invoice_id' value='<?php echo $invoice_id ?>' />
		


		<li class='fieldset'>
		<label for='' class='form'><?php echo $l['all']['TotalBilled']?></label>
		<input name='' type='text' disabled id='' value='<?php echo $invoiceDetails['totalbilled']?>' tabindex=101 />
		</li>
		
		<li class='fieldset'>
		<label for='' class='form'><?php echo $l['all']['TotalPayed']?></label>
		<input name='' type='text' disabled id='' value='<?php echo $invoiceDetails['totalpayed']?>' tabindex=101 />
		</li>
		
		<li class='fieldset'>
		<label for='' class='form'><?php echo $l['all']['Balance']?></label>
		<input name='' type='text' disabled id='' value='<?php echo (float) ($invoiceDetails['totalpayed'] - $invoiceDetails['totalbilled'])?>' tabindex=101 />
		</li>

		<br/>

		<li class='fieldset'>
		<label for='invoice_status_id' class='form'><?php echo $l['all']['InvoiceStatus']?></label>
		<?php
		        include_once('include/management/populate_selectbox.php');
		        populate_invoice_status_id($invoiceDetails['status'], "invoice_status_id", "form", "", $invoiceDetails['status_id']);
		?>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('invoice_status_id')" />
		<div id='invoiceStatusTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo $l['Tooltip']['invoiceStatusTooltip'] ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='invoice_type_id' class='form'><?php echo $l['all']['InvoiceType']?></label>
		<?php
		        include_once('include/management/populate_selectbox.php');
		        populate_invoice_type_id($invoiceDetails['type'], "invoice_type_id", "form", "", $invoiceDetails['type_id']);
		?>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('invoice_type_id')" />
		<div id='invoiceTypeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo $l['Tooltip']['invoiceTypeTooltip'] ?>
		</div>
		</li>


		<li class='fieldset'>
		<label for='user_id' class='form'><?php echo $l['all']['UserId'] ?></label>
		<input name='user_id' type='text' id='user_id' value='<?php echo $invoiceDetails['user_id']?>' tabindex=101 />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('user_idTooltip')" /> 
		
		<div id='user_idTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo $l['Tooltip']['user_idTooltip'] ?>
		</div>
		</li>



		<label for='invoice_date' class='form'><?php echo $l['all']['Date']?></label>		
		<input value='<?php echo $invoiceDetails['date']?>' id='invoice_date' name='invoice_date'  tabindex=108 />
		<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'invoice_date', 'chooserSpan', 1950, <?= date('Y', time());?>, 'Y-m-d H:i:s', true);">
		<br/>


		<label for='invoice_notes' class='form'><?php echo $l['ContactInfo']['Notes']?></label>
		<textarea class='form' name='invoice_notes' ><?php echo $invoiceDetails['notes']?></textarea>








		<li class='fieldset'>
		<br/>
		<hr><br/>
		<input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' tabindex=10000 class='button' />
		</li>
		
		</ul>
	
	</fieldset>
	</div>

	<div class="tabbertab" title="<?php echo $l['title']['Items']; ?>">
	<fieldset>

		<h302> <?php echo $l['title']['Items']; ?> </h302>
		<br/>

		<ul>


		<li class='fieldset'>
		<label for='planName' class='form'><?php echo $l['all']['PlanName'] ?></label>
                <?php
                       populate_plans($invoiceDetails['planName'],$invoiceDetails['planId'],"form", "", "", true);
                ?>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planNameTooltip')" /> 
		
		<div id='planNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo $l['Tooltip']['planNameTooltip'] ?>
		</div>
		</li>


		<li class='fieldset'>
		<label for='amount' class='form'><?php echo $l['all']['Amount'] ?></label>
		<input class='integer5len' name='invoice_items[0][amount]' type='text' id='invoice_items[0][amount]' value='<?php echo $invoiceDetails['itemAmount'] ?>' tabindex=103 />
                <img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('invoice_items[0][amount]','increment')" />
                <img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('invoice_items[0][amount]','decrement')"/>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('amountTooltip')" /> 
		
		<div id='amountTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo $l['Tooltip']['amountTooltip'] ?>
		</div>
		</li>
		

		<li class='fieldset'>
		<label for='tax' class='form'><?php echo $l['all']['Tax'] ?></label>
		<input class='integer5len' name='invoice_items[0][tax]' type='text' id='invoice_items[0][tax]' value='<?php echo $invoiceDetails['itemTaxAmount']?>' tabindex=103 />
                <img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('invoice_items[0][tax]','increment')" />
                <img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('invoice_items[0][tax]','decrement')"/>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('taxTooltip')" /> 
		
		<div id='taxTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo $l['Tooltip']['taxTooltip'] ?>
		</div>
		</li>
				

		<label for='notes' class='form'><?php echo $l['ContactInfo']['Notes']?></label>
		<textarea class='form' name='invoice_items[0][notes]' ><?php echo $invoiceDetails['itemNotes']?></textarea>




		<li class='fieldset'>
		<br/>
		<hr><br/>
		<input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' tabindex=10000 class='button' />
		</li>
	
		</ul>

	</fieldset>
	</div>

	<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>

	
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