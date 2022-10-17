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
<script src="library/javascript/pages_common.js" type="text/javascript"></script>

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
?>

<div id="sidebar">

                                <h2>Billing</h2>

                                <h3>Track PayPal Transactions</h3>
	<ul class="subnav">

        <form name="billpaypaltransactions" action="bill-merchant-transactions.php" method="get" class="sidebar">

        <input class="sidebutton" type="submit" name="submit" value="<?php echo t('button','ProcessQuery') ?>" tabindex=3 />
	<br/><br/>

		<h109><?php echo t('button','BetweenDates'); ?></h109> <br/>

                        <input name="startdate" type="text" id="startdate"
                                value="<?php if (isset($billing_date_startdate)) echo $billing_date_startdate;
                        else echo date("Y-m-01"); ?>">

                        <img src="library/js_date/calendar.gif"
                                onclick="showChooser(this, 'startdate', 'chooserSpan', 1950, <?php echo date('Y', time());?>, 'Y-m-d', false);">
                        <div id="chooserSpan" class="dateChooser select-free"
                                style="display: none; visibility: hidden;       width: 160px;"></div>

                        <input name="enddate" type="text" id="enddate"
                                value="<?php if (isset($billing_date_enddate)) echo $billing_date_enddate;
                                else date("Y-m-t"); ?>">

                        <img src="library/js_date/calendar.gif"
                                onclick="showChooser(this, 'enddate', 'chooserSpan', 1950, <?php echo date('Y', time());?>, 'Y-m-d', false);">
                        <div id="chooserSpan" class="dateChooser select-free"
                                style="display: none; visibility: hidden; width: 160px;"></div>
			<br/><br/>

		<h109><?php echo t('all','VendorType'); ?></h109> <br/>
                        <select name="vendor_type" size="1">
                                <option value="<?php if (isset($billing_paypal_vendor_type)) echo $billing_paypal_vendor_type; else echo "%"; ?>">
                                        <?php if (isset($billing_paypal_vendor_type)) echo $billing_paypal_vendor_type; else echo "Any"; ?>
                                </option>
				<option value=""></option>
				<option value="%">Any</option>
				<option value="PayPal">PayPal</option>
				<option value="2Checkout">2Checkout</option>
                        </select>
			<br/><br/>

		<h109><?php echo t('all','PayerEmail'); ?></h109> <br/>
                        <input name="payer_email" type="text"
                                value="<?php if (isset($billing_paypal_payeremail)) echo $billing_paypal_payeremail; else echo "*"; ?>">
			<br/>

		<h109><?php echo t('all','PaymentStatus'); ?></h109> <br/>
                        <select name="payment_status" size="1">
                                <option value="<?php if (isset($billing_paypal_paymentstatus)) echo $billing_paypal_paymentstatus; else echo "%"; ?>">
                                        <?php if (isset($billing_paypal_paymentstatus)) echo $billing_paypal_paymentstatus; else echo "Any"; ?>
                                </option>
				<option value=""></option>
				<option value="Completed">Completed</option>
				<option value="Denied">Denied</option>
				<option value="Expired">Expired</option>
				<option value="Failed">Failed</option>
				<option value="In-Progress">In-Progress</option>
				<option value="Pending">Pending</option>
				<option value="Processed">Processed</option>
				<option value="Refunded">Refunded</option>
				<option value="Reversed">Reversed</option>
				<option value="Canceled-Reversal">Canceled-Reversal</option>
				<option value="Voided">Voided</option>
                        </select>
			<br/><br/>


                <br/><br/><br/>
                <h109><?php echo t('button','AccountingFieldsinQuery'); ?></h109><br/>
                <input type="checkbox" name="sqlfields[]" value="id" /> <h109> <?php echo t('all','ID'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="username" checked /> <h109><?php echo t('all','Username'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="password"  /> <h109><?php echo t('all','Password'); ?></h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="txnId"  /> <h109><?php echo t('all','TxnId'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="planName" checked /> <h109><?php echo t('all','PlanName'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="planId"  /> <h109><?php echo t('all','PlanId'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="quantity"  /> <h109><?php echo t('all','Quantity'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="business_email"  /> <h109><?php echo t('all','ReceiverEmail'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="business_id"  /> <h109><?php echo t('all','Business'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="payment_tax" /> <h109><?php echo t('all','Tax'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="payment_cost"  /> <h109><?php echo t('all','Cost'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="payment_fee" checked /> <h109><?php echo t('all','TransactionFee'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="payment_total" checked /> <h109><?php echo t('all','TotalCost'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="payment_currency" checked /> <h109><?php echo t('all','PaymentCurrency'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="first_name" checked /> <h109><?php echo t('all','FirstName'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="last_name" checked /> <h109><?php echo t('all','LastName'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="payer_email" checked /> <h109><?php echo t('all','PayerEmail'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="payer_address_name"  /> <h109><?php echo t('all','AddressRecipient'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="payer_address_street"  /> <h109><?php echo t('all','Street'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="payer_address_country" checked /> <h109><?php echo t('all','Country'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="payer_address_country_code"  /> <h109><?php echo t('all','CountryCode'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="payer_address_city" checked /> <h109><?php echo t('all','City'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="payer_address_state" checked /> <h109><?php echo t('all','State'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="payer_address_zip"  /> <h109><?php echo t('all','Zip'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="payment_date" checked /> <h109><?php echo t('all','PaymentDate'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="payment_status" checked /> <h109><?php echo t('all','PaymentStatus'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="payer_status" /> <h109><?php echo t('all','PayerStatus'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="payment_address_status" /> <h109><?php echo t('all','PaymentAddressStatus'); ?> </h109> <br/>
                <input type="checkbox" name="sqlfields[]" value="vendor_type" checked /> <h109><?php echo t('all','VendorType'); ?> </h109> <br/>
                Select:
                <a class="table" href="javascript:SetChecked(1,'sqlfields[]','billpaypaltransactions')">All</a>
                <a class="table" href="javascript:SetChecked(0,'sqlfields[]','billpaypaltransactions')">None</a>


                <br/><br/>
                <h109><?php echo t('button','OrderBy') ?><h109> <br/>
                        <center>
                        <select name="orderBy" size="1">
                                <option value="id"> Id </option>
                                <option value="username"> username </option>
                                <option value="txnId"> txnId </option>
			</select>

                        <select name="orderType" size="1">
                                <option value="ASC"> Ascending </option>
                                <option value="DESC"> Descending </option>
                        </select>
                        </center>

        <br/>
        <input class="sidebutton" type="submit" name="submit" value="<?php echo t('button','ProcessQuery') ?>" tabindex=3 />



                        </form></li>

		</ul>


                                <br/><br/>
                                

			

                </div>

