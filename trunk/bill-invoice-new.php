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
	isset($_POST['invoice_status_id']) ? $invoice_status_id = $_POST['invoice_status_id'] : $invoice_status_id = "";
	isset($_POST['invoice_type_id']) ? $invoice_type_id = $_POST['invoice_type_id'] : $invoice_type_id = "";
	isset($_POST['invoice_date']) ? $invoice_date = $_POST['invoice_date'] : $invoice_date = "";
	isset($_POST['invoice_notes']) ? $invoice_notes = $_POST['invoice_notes'] : $invoice_notes = "";
	
	isset($_POST['invoice_items']) ? $invoice_items = $_POST['invoice_items'] : $invoice_items = "";
	
	isset($_GET['user_id']) ? $user_id = $_GET['user_id'] : $user_id = "";


	$logAction = "";
	$logDebugSQL = "";

	if (isset($_POST["submit"])) {
		
		isset($_POST['user_id']) ? $user_id = $_POST['user_id'] : $user_id = "";
		
		include 'library/opendb.php';

			if (trim($user_id) != "") {

				$currDate = date('Y-m-d H:i:s');
				$currBy = $_SESSION['operator_user'];

				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'].
				" (id, user_id, date, status_id, type_id, notes, creationdate, creationby, updatedate, updateby) ".
				" VALUES (0, '".$dbSocket->escapeSimple($user_id)."', '".
				$dbSocket->escapeSimple($invoice_date)."', '".
				$dbSocket->escapeSimple($invoice_status_id)."', '".
				$dbSocket->escapeSimple($invoice_type_id)."', '".
				$dbSocket->escapeSimple($invoice_notes)."', ".
				" '$currDate', '$currBy', NULL, NULL)";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				if (!PEAR::isError($res)) {

					$invoice_id = $dbSocket->getOne( "SELECT LAST_INSERT_ID() FROM `".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE']."`" ); 
					
					// add the invoice items which the user created
					addInvoiceItems($dbSocket, $invoice_id, $invoice_items);
					
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
	
		include 'library/closedb.php';
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
	
	
	
	if (isset($user_id) && (!empty($user_id))) {
		include 'library/opendb.php';

		$sql = "SELECT id, contactperson, city, state, username FROM ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
		" WHERE id = '".$dbSocket->escapeSimple($user_id)."'";
		$res = $dbSocket->query($sql);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		
		$userInfo['contactperson'] = $row['contactperson'];
		$userInfo['username'] = $row['username'];
		$userInfo['city'] = $row['city'];
		$userInfo['state'] = $row['state'];
		
		$logDebugSQL .= $sql . "\n";

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

	<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['billinvoicenew.php'] ?>
	<h144>+</h144></a></h2>
	
	<div id="helpPage" style="display:none;visibility:visible" >
		<?php echo $l['helpPage']['billinvoicesnew'] ?>
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
		echo '<a href="/bill-pos-edit.php?username='.$userInfo['username'].'">'.$userInfo['contactperson'].'</a><br/>'.
			$userInfo['city']. (!empty($userInfo['state']) ? ', '.$userInfo['state'] : '' );
		echo '</b>';
		?>
		<br/>

		<li class='fieldset'>
		<label for='invoice_status_id' class='form'><?php echo $l['all']['InvoiceStatus']?></label>
		<?php
		        include_once('include/management/populate_selectbox.php');
		        populate_invoice_status_id("Select Status", "invoice_status_id");
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
		        populate_invoice_type_id("Select Type", "invoice_type_id");
		?>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('invoice_type_id')" />
		<div id='invoiceTypeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo $l['Tooltip']['invoiceTypeTooltip'] ?>
		</div>
		</li>


		<li class='fieldset'>
		<label for='user_id' class='form'><?php echo $l['all']['UserId'] ?></label>
		<input name='user_id' type='text' id='user_id' value='<?php echo $user_id ?>' tabindex=101 />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('user_idTooltip')" /> 
		
		<div id='user_idTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo $l['Tooltip']['user_idTooltip'] ?>
		</div>
		</li>



		<label for='invoice_date' class='form'><?php echo $l['all']['Date']?></label>		
		<input value='' id='invoice_date' name='invoice_date'  tabindex=108 />
		<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'invoice_date', 'chooserSpan_invoicedate', 1950, 2010, 'Y-m-d H:i:s', true);">
		<br/>

		<label for='invoice_notes' class='form'><?php echo $l['ContactInfo']['Notes']?></label>
		<textarea class='form' name='invoice_notes' ></textarea>


		<li class='fieldset'>
		<br/>
		<hr><br/>
		<input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' tabindex=10000 class='button' />
		</li>
		
		</ul>
	
	</fieldset>
	<div id="chooserSpan_invoicedate" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
	</div>

	<div class="tabbertab" title="<?php echo $l['title']['Items']; ?>">
	<fieldset>

		<h302> <?php echo $l['title']['Items']; ?> </h302>
		<br/>

		<ul>


		<li class='fieldset'>
		<label for='planName' class='form'><?php echo $l['all']['PlanName'] ?></label>
                <?php
                       populate_plans("Select Plan","invoice_items[0][planId]","form", "", "", true);
                ?>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planNameTooltip')" /> 
		
		<div id='planNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo $l['Tooltip']['planNameTooltip'] ?>
		</div>
		</li>


		<li class='fieldset'>
		<label for='amount' class='form'><?php echo $l['all']['Amount'] ?></label>
		<input class='integer5len' name='invoice_items[0][amount]' type='text' id='invoice_items[0][amount]' value='000.00' tabindex=103 />
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
		<input class='integer5len' name='invoice_items[0][tax]' type='text' id='invoice_items[0][tax]' value='000.00' tabindex=103 />
                <img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('invoice_items[0][tax]','increment')" />
                <img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('invoice_items[0][tax]','decrement')"/>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('taxTooltip')" /> 
		
		<div id='taxTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo $l['Tooltip']['taxTooltip'] ?>
		</div>
		</li>
				

		<label for='notes' class='form'><?php echo $l['ContactInfo']['Notes']?></label>
		<textarea class='form' name='invoice_items[0][notes]' ></textarea>




		<li class='fieldset'>
		<br/>
		<hr><br/>
		<input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' tabindex=10000 class='button' />
		</li>
	
		</ul>

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