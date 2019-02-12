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

        isset($_REQUEST['payment_id']) ? $payment_id = $_REQUEST['payment_id'] : $payment_id = "";
        isset($_REQUEST['payment_invoice_id']) ? $payment_invoice_id = $_REQUEST['payment_invoice_id'] : $payment_invoice_id = "";
        isset($_REQUEST['payment_amount']) ? $payment_amount = $_REQUEST['payment_amount'] : $payment_amount = "";
        isset($_REQUEST['payment_date']) ? $payment_date = $_REQUEST['payment_date'] : $payment_date = "";
        isset($_REQUEST['payment_type_id']) ? $payment_type_id = $_REQUEST['payment_type_id'] : $payment_type_id = "";
        isset($_REQUEST['payment_notes']) ? $payment_notes = $_REQUEST['payment_notes'] : $payment_notes = "";

	$edit_payment_id = $payment_id; //feed the sidebar variables	

	$logAction = "";
	$logDebugSQL = "";

	if (isset($_POST['submit'])) {

                $payment_id = $_POST['payment_id'];

		if (trim($payment_id) != "") {

			$currDate = date('Y-m-d H:i:s');
			$currBy = $_SESSION['operator_user'];

			$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOPAYMENTS']." SET ".
			" invoice_id=".$dbSocket->escapeSimple($payment_invoice_id).", ".
			" amount=".$dbSocket->escapeSimple($payment_amount).", ".
			" date='".$dbSocket->escapeSimple($payment_date)."', ".
			" type_id=".$dbSocket->escapeSimple($payment_type_id).", ".
			" notes='".$dbSocket->escapeSimple($payment_notes)."', ".
			" updatedate='$currDate', updateby='$currBy' ".
			" WHERE id=".$dbSocket->escapeSimple($payment_id)."";
			$res = $dbSocket->query($sql);
			$logDebugSQL = "";
			$logDebugSQL .= $sql . "\n";
			
			$successMsg = "Updated payment type: <b> $payment_id </b>";
			$logAction .= "Successfully updated payment type [$payment_id] on page: ";
			
		} else {
			$failureMsg = "no payment type was entered, please specify a payment type to edit.";
			$logAction .= "Failed updating payment type [$payment_id] on page: ";
		}
		
	}
	

        $sql = "SELECT ".$configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".id, ".
                $configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".invoice_id, ".
                $configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".amount, ".
                $configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".date, ".
                $configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".type_id, ".
                $configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".notes, ".
                $configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".creationdate, ".
                $configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".creationby, ".
                $configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".updatedate, ".
                $configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".updateby, ".
                $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'].".value ".
                " FROM ".$configValues['CONFIG_DB_TBL_DALOPAYMENTS'].
                " LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'].
                " ON ".$configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".type_id=".$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'].".id ".
		" WHERE ".$configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".id=".$dbSocket->escapeSimple($payment_id)."";



	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	$row = $res->fetchRow();
	$payment_id = $row[0];
	$payment_invoice_id = $row[1];
	$payment_amount = $row[2];
	$payment_date = $row[3];
	$payment_type_id = $row[4];
	$payment_notes = $row[5];
	$creationdate = $row[6];
	$creationby = $row[7];
	$updatedate = $row[8];
	$updateby = $row[9];
	$payment_type_value = $row[10];

	include 'library/closedb.php';


	if (trim($payment_id) == "") {
		$failureMsg = "no payment id was entered or found in database, please specify a payment id to edit";
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
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<?php
	include_once ("library/tabber/tab-layout.php");
?>
 
<?php
	include ("menu-bill-payments.php");
?>		
	<div id="contentnorightbar">
		
		<h2 id="Intro" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','paymentsedit.php') ?>
		:: <?php if (isset($payment_id)) { echo $payment_id; } ?><h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','paymentsedit') ?>
			<br/>
		</div>
		<?php
			include_once('include/management/actionMessages.php');
		?>

		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

	<div class="tabbertab" title="<?php echo t('title','PaymentInfo'); ?>">


	<fieldset>

		<h302> <?php echo t('title','PaymentInfo'); ?> </h302>
		<br/>

		<ul>

			<li class='fieldset'>
			<label for='payment_id' class='form'><?php echo t('all','PaymentId') ?></label>
			<input disabled name='payment_id' type='text' id='payment_id' value='<?php echo $payment_id ?>' tabindex=100 />
			</li>

                <li class='fieldset'>
                <label for='name' class='form'><?php echo t('all','PaymentInvoiceID') ?></label>
                <input name='payment_invoice_id' type='text' id='payment_invoice_id' value='<?php echo $payment_invoice_id ?>' tabindex=102 />
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('paymentInvoiceTooltip')" />

                <div id='paymentInvoiceTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','paymentInvoiceTooltip') ?>
                </div>
                </li>

                <li class='fieldset'>
                <label for='payment_amount' class='form'><?php echo t('all','PaymentAmount') ?></label>
                <input class='integer5len' name='payment_amount' type='text' id='payment_amount' value='<?php echo $payment_amount ?>' tabindex=103 />
                   <img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('payment_amount','increment')" />
                   <img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('payment_amount','decrement')"/>
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('amountTooltip')" />

                <div id='amountTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','amountTooltip') ?>
                </div>
                </li>

                <label for='payment_date' class='form'><?php echo t('all','PaymentDate')?></label>
                <input value='<?php echo $payment_date ?>' id='payment_date' name='payment_date'  tabindex=108 />
                <img src="library/js_date/calendar.gif" onclick="showChooser(this, 'payment_date', 'chooserSpan', 1950, <?php echo date('Y', time());?>, 'Y-m-d H:i:s', true);">
                <br/>

                <li class='fieldset'>
                <label for='payment_type_id' class='form'><?php echo t('all','PaymentType')?></label>
                <?php
                        include_once('include/management/populate_selectbox.php');
                        populate_payment_type_id("$payment_type_value", "payment_type_id", "form", "", "$payment_type_id");
                ?>
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('paymentTypeIdTooltip')" />
                <div id='paymentTypeIdTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','paymentTypeIdTooltip') ?>
                </div>
                </li>

                <li class='fieldset'>
                <label for='payment_notes' class='form'><?php echo t('all','PaymentNotes') ?></label>
                <textarea name='payment_notes' id='payment_notes'><?php echo $payment_notes ?></textarea>
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('paymentNotesTooltip')" />

                <div id='paymentNotesTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','paymentNotesTooltip') ?>
                </div>
                </li>


			<li class='fieldset'>
			<br/>
			<hr><br/>
			<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000
				class='button' />
			</li>

		</ul>

	</fieldset>

	<input type=hidden value="<?php echo $payment_id ?>" name="payment_id"/>

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





