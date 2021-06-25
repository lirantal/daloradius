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
 * Authors:     Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

// Include the 2checkout library
include_once ('include/merchant/TwoCo.php');
include_once ('include/common/common.php');
include('library/config_read.php');

// Create an instance of the 2Checkout library
$my2CO = new TwoCo();

// Log the IPN results
// $my2CO->ipnLog = TRUE;

// Specify your 2Checkout login and secret
$my2CO->setSecret($configValues['CONFIG_MERCHANT_IPN_SECRET']);

// Enable test mode if needed
$my2CO->enableTestMode();

// Check validity and write it down.
if ($my2CO->validateIpn())
{
	$logFile = $configValues['CONFIG_LOG_MERCHANT_IPN_FILENAME'];
	logToFile("\n", $logFile);

	saveToDb($my2CO->ipnData, "Completed");

    include('library/opendb.php');
	require_once('include/common/provisionUser.php');

	// the payment is valid, we activate the user by adding him to freeradius's set of tables (radcheck etc)
	// get transaction id from 2checkout ipn POST
	$txnId = $dbSocket->escapeSimple($my2CO->ipnData['custom']);

	provisionUser($dbSocket, $txnId);

	include('library/closedb.php');

}
else
{
	$logFile = $configValues['CONFIG_LOG_MERCHANT_IPN_FILENAME'];
	logToFile("\n", $logFile);

	saveToDb($my2CO->ipnData, "Failed");

}


/*****************************************************************************************
 * saveToDb()
 * saves the 2co session variables (in POST) to database table billing_merchant
 *****************************************************************************************/
function saveToDb($postData, $status) {

    include('library/opendb.php');

    $txnId = $dbSocket->escapeSimple($postData['custom']);
    $planId = $dbSocket->escapeSimple($postData['cart_order_id']);
    $business_id = $dbSocket->escapeSimple($postData['sid']);
    $payment_total = $dbSocket->escapeSimple($postData['total']);
    $payer_email = $dbSocket->escapeSimple($postData['email']);
	$payer_phone = $dbSocket->escapeSimple($postData['phone']);
    $payer_address_street = $dbSocket->escapeSimple($postData['street_address']);
    $payer_address_country = $dbSocket->escapeSimple($postData['country']);
    $payer_address_country_code = $dbSocket->escapeSimple($postData['country']);
    $payer_address_city = $dbSocket->escapeSimple($postData['city']);
    $payer_address_state = $dbSocket->escapeSimple($postData['state']);
    $payer_address_zip = $dbSocket->escapeSimple($postData['zip']);
	$payment_method = $dbSocket->escapeSimple($postData['pay_method']);
	$payment_status = $status;

	$vendor = "2Checkout";
	$payer_name = $dbSocket->escapeSimple($postData['card_holder_name']);
	list($first_name, $last_name) = explode("  ", $payer_name);

    // convert date (in PDT time zone) into local time, formatted for mysql
    $payment_date = date('Y-m-d H:i:s');

	$sql = "SELECT username FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT']." WHERE txnId = '$txnId'";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow();
	$username = $row[0];

    $sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT']." SET ".
        " planId='$planId',".
        " business_id='$business_id',".
        " payment_total='$payment_total',".
        " first_name='$first_name',".
        " last_name='$last_name',".
        " payer_email='$payer_email',".
        " payer_address_street='$payer_address_street',".
        " payer_address_country='$payer_address_country',".
        " payer_address_country_code='$payer_address_country_code',".
        " payer_address_city='$payer_address_city',".
        " payer_address_state='$payer_address_state',".
        " payer_address_zip='$payer_address_zip',".
        " payment_date='$payment_date',".
		" payment_status='$payment_status' ".
        " WHERE txnId='$txnId'";
    $res = $dbSocket->query($sql);

    $sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO']." SET ".
		" email='$payer_email',".
		" phone='$payer_phone',".
		" paymentmethod='$payment_method',".
        " contactperson='$payer_name',".
        " address='$payer_address_street',".
        " city='$payer_address_city',".
        " state='$payer_address_state',".
        " zip='$payer_address_zip',".
        " updatedate='$payment_date',".
        " updateby='2Checkout-ipn',".
        " lead='2Checkout-webinterface',".
		" ordertaker='2Checkout-webinterface' ".
        " WHERE username='$username'";
    $res = $dbSocket->query($sql);

    include('library/closedb.php');

}



?>

<html>
<body>

<?php

	echo '
		<script type="text/javascript">
		<!--
		window.location = "'.$configValues['CONFIG_MERCHANT_IPN_URL_ROOT']."/".
								$configValues['CONFIG_MERCHANT_IPN_URL_RELATIVE_SUCCESS'].
								'?txnId='.$my2CO->ipnData['custom'].'"
		//-->
		</script>

	';

?>

</body>
</html>
