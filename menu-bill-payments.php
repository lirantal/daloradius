<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->
<link rel="stylesheet" href="css/form-field-tooltip.css" type="text/css" media="screen,projection" />

<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>
<script src="library/javascript/rounded-corners.js" type="text/javascript"></script>
<script src="library/javascript/form-field-tooltip.js" type="text/javascript"></script>

<body>
<?php
    include_once ("lang/main.php");
?>
<div id="wrapper">
<div id="innerwrapper">

<?php
    $m_active = "Billing";
    include_once ("include/menu/menu-items.php");
	include_once ("include/menu/billing-subnav.php");
	include_once("include/management/autocomplete.php");
?>

<div id="sidebar">

                                <h2>Billing</h2>

                                <h3>Payments Management</h3>
                                <ul class="subnav">

                                                <li><a href="javascript:document.paymentslist.submit();"><b>&raquo;</b><?php echo $l['button']['ListPayments'] ?></a>
                                                
													<form name="paymentslist" action="bill-payments-list.php" method="get" class="sidebar">
                                                        <input name="username" type="text" id="username" 
                                                        autocomplete='off'
                                						onClick='javascript:__displayTooltip();'
														tooltipText='<?php echo $l['Tooltip']['Username']; ?> <br/>'
                                                    	value="<?php if (isset($edit_username)) echo $edit_username; ?>" tabindex=3>
                                                    	
                                                        <input name="invoice_id" type="text" id="invoice_id" 
                                						onClick='javascript:__displayTooltip();'
														tooltipText='<?php echo $l['Tooltip']['invoiceID']; ?> <br/>'
                                                    	value="<?php if (isset($edit_invoice_id)) echo $edit_invoice_id; ?>" tabindex=3>
													</form>
                                                
                                                </li>
                                                <li><a href="bill-payments-new.php"><b>&raquo;</b><?php echo $l['button']['NewPayment'] ?></a></li>
                                                <li><a href="javascript:document.paymentsedit.submit();"><b>&raquo;</b><?php echo $l['button']['EditPayment'] ?></a>
                                                        <form name="paymentsedit" action="bill-payments-edit.php" method="get" class="sidebar">
                                                        <input name="payment_id" type="text" id="payment_id" 
                                onClick='javascript:__displayTooltip();'
                                tooltipText='<?php echo $l['Tooltip']['PaymentId']; ?> <br/>'
                                                                value="<?php if (isset($edit_payment_id)) echo $edit_payment_id; ?>" tabindex=3>
                                                        </form></li>

                                                <li><a href="bill-payments-del.php"><b>&raquo;</b><?php echo $l['button']['RemovePayment'] ?></a></li>
                                </ul>


                                <h3>Payment Types Management</h3>
                                <ul class="subnav">

                                                <li><a href="bill-payment-types-list.php"><b>&raquo;</b><?php echo $l['button']['ListPayTypes'] ?></a></li>
                                                <li><a href="bill-payment-types-new.php"><b>&raquo;</b><?php echo $l['button']['NewPayType'] ?></a></li>
                                                <li><a href="javascript:document.paymenttypesedit.submit();""><b>&raquo;</b><?php echo $l['button']['EditPayType'] ?></a>
                                                        <form name="paymenttypesedit" action="bill-payment-types-edit.php" method="get" class="sidebar">
                                                        <input name="paymentname" type="text" id="paymentname" <?php if ($autoComplete) echo "autocomplete='off'"; ?>
                                onClick='javascript:__displayTooltip();'
                                tooltipText='<?php echo $l['Tooltip']['PayTypeName']; ?> <br/>'
                                                                value="<?php if (isset($edit_paymentName)) echo $edit_paymentName; ?>" tabindex=3>
                                                        </form></li>
                                                <li><a href="bill-payment-types-del.php"><b>&raquo;</b><?php echo $l['button']['RemovePayType'] ?></a></li>
                                </ul>




                                <br/><br/>
                                <h2>Search</h2>

			<input name="" type="text" value="Search" tabindex=4 />

                </div>

<?php
        include_once("include/management/autocomplete.php");

        if ($autoComplete) {
                echo "<script type=\"text/javascript\">
                        /** Making usernameEdit interactive **/
                      autoComEdit = new DHTMLSuite.autoComplete();
                      autoComEdit.add('username','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');
                      </script>";
        }
?>

<script type="text/javascript">
        var tooltipObj = new DHTMLgoodies_formTooltip();
        tooltipObj.setTooltipPosition('right');
        tooltipObj.setPageBgColor('#EEEEEE');
        tooltipObj.setTooltipCornerSize(15);
        tooltipObj.initFormFieldTooltip();
</script>
