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

	// read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-validate';

	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$req .= "&$key=$value";
	}

	// post back to PayPal system to validate
	$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

	// if using paypal sandbox then change this address to:
	// ssl://www.sandbox.paypal.com otherwise its:
	// ssl://www.paypal.com
	$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);

	if (!$fp) {
		// HTTP ERROR
		// the program ends here
	} else {
		fputs ($fp, $header . $req);
		$res = "";
		while (!feof($fp)) {
			$res .= fgets ($fp, 1024);
		}

		if (preg_match("/VERIFIED/", $res)) {

			// we assume that this is a valid paypal response and we save the response details
			// into the database

			// log the reponse with a custom message
			logToFile();
			saveToDb();

			include('library/opendb.php');
			require_once('include/common/provisionUser.php');

			// the payment is valid, we activate the user by adding him to freeradius's set of tables (radcheck etc)
			// get transaction id from paypal ipn POST
			$txnId = $dbSocket->escapeSimple($_POST['option_selection1']);
			
			provisionUser($dbSocket, $txnId);

			include('library/closedb.php');

		} else {
			// log for manual investigation
			//logToFile("PayPal Tranasction INVALID:\n");
			//logToFile($res);
			saveToDb();
		} 

	fclose ($fp);

	}


/*****************************************************************************************
 * logToFile()
 * logs all post variables to file
 *
 * $customMsg		custom text to be written to the log file
 *****************************************************************************************/
function logToFile($customMsg) {

	include('library/config_read.php');

	$fh = fopen($configValues['CONFIG_LOG_MERCHANT_IPN_FILENAME'], 'a');
	
	if ($fh) {
				
		// only if the file size is 0 (meaning it's a new file) then we shuld
		// create the titles of all the fields in csv format, otherwise we assume
		// that it has been created before and we simply append to it
		
		if (!filesize($configValues['CONFIG_LOG_MERCHANT_IPN_FILENAME']) > 0) {
			$csvFields = "test_ipn,payment_type,payment_date,payment_status,pending_reason,address_status,".
				"payer_status,first_name,last_name,payer_email,payer_id,address_name,address_country,".
				"address_country_code,address_zip,address_state,address_city,address_street,business,".
				"receiver_email,receiver_id,residence_country,item_name,item_number,item_name1,item_number1,".
				"quantity,quantity1,shipping,tax,mc_currency,mc_fee,mc_gross,mc_gross1,mc_handling,mc_handling1,".
				"mc_shipping,mc_shipping1,txn_type,txn_id,parent_txn_id,notify_version,reason_code,receipt_ID,".
				"custom,invoice,charset,verify_sign";
			fwrite($fh, $csvFields . "\n");
		}
		
		// get all the values of each field and write it
		$csvDataArray['test_ipn'] = str_replace(",", " ", $_POST['test_ipn']);
		$csvDataArray['payment_type'] = str_replace(",", " ", $_POST['payment_type']);
		$csvDataArray['payment_date'] = date('Y-m-d H:i:s', strtotime($_POST['payment_date']));
		$csvDataArray['payment_status'] = str_replace(",", " ", $_POST['payment_status']);
		$csvDataArray['pending_reason'] = str_replace(",", " ", $_POST['pending_reason']);
		$csvDataArray['address_status'] = str_replace(",", " ", $_POST['address_status']);
		$csvDataArray['payer_status'] = str_replace(",", " ", $_POST['payer_status']);
		$csvDataArray['first_name'] = str_replace(",", " ", $_POST['first_name']);
		$csvDataArray['last_name'] = str_replace(",", " ", $_POST['last_name']);
		$csvDataArray['payer_email'] = str_replace(",", " ", $_POST['payer_email']);
		$csvDataArray['payer_id'] = str_replace(",", " ", $_POST['payer_id']);
		$csvDataArray['address_name'] = str_replace(",", " ", $_POST['address_name']);
		$csvDataArray['address_country'] = str_replace(",", " ", $_POST['address_country']);
		$csvDataArray['address_country_code'] = str_replace(",", " ", $_POST['address_country_code']);
		$csvDataArray['address_zip'] = str_replace(",", " ", $_POST['address_zip']);
		$csvDataArray['address_state'] = str_replace(",", " ", $_POST['address_state']);
		$csvDataArray['address_city'] = str_replace(",", " ", $_POST['address_city']);
		$csvDataArray['address_street'] = str_replace(",", " ", $_POST['address_street']);
		$csvDataArray['business'] = str_replace(",", " ", $_POST['business']);
		$csvDataArray['receiver_email'] = str_replace(",", " ", $_POST['receiver_email']);
		$csvDataArray['receiver_id'] = str_replace(",", " ", $_POST['receiver_id']);
		$csvDataArray['residence_country'] = str_replace(",", " ", $_POST['residence_country']);
		$csvDataArray['item_name'] = str_replace(",", " ", $_POST['item_name']);
		$csvDataArray['item_number'] = str_replace(",", " ", $_POST['item_number']);
		$csvDataArray['item_name1'] = str_replace(",", " ", $_POST['item_name1']);
		$csvDataArray['item_number1'] = str_replace(",", " ", $_POST['item_number1']);
		$csvDataArray['quantity'] = str_replace(",", " ", $_POST['quantity']);
		$csvDataArray['quantity1'] = str_replace(",", " ", $_POST['quantity1']);
		$csvDataArray['shipping'] = str_replace(",", " ", $_POST['shipping']);
		$csvDataArray['tax'] = str_replace(",", " ", $_POST['tax']);
		$csvDataArray['mc_currency'] = str_replace(",", " ", $_POST['mc_currency']);
		$csvDataArray['mc_fee'] = str_replace(",", " ", $_POST['mc_fee']);
		$csvDataArray['mc_gross'] = str_replace(",", " ", $_POST['mc_gross']);
		$csvDataArray['mc_gross1'] = str_replace(",", " ", $_POST['mc_gross1']);
		$csvDataArray['mc_handling'] = str_replace(",", " ", $_POST['mc_handling']);
		$csvDataArray['mc_handling1'] = str_replace(",", " ", $_POST['mc_handling1']);
		$csvDataArray['mc_shipping'] = str_replace(",", " ", $_POST['mc_shipping']);
		$csvDataArray['mc_shipping1'] = str_replace(",", " ", $_POST['mc_shipping1']);
		$csvDataArray['txn_type'] = str_replace(",", " ", $_POST['txn_type']);
		$csvDataArray['txn_id'] = str_replace(",", " ", $_POST['txn_id']);
		$csvDataArray['parent_txn_id'] = str_replace(",", " ", $_POST['parent_txn_id']);
		$csvDataArray['notify_version'] = str_replace(",", " ", $_POST['notify_version']);
		$csvDataArray['reason_code'] = str_replace(",", " ", $_POST['reason_code']);
		$csvDataArray['receipt_ID'] = str_replace(",", " ", $_POST['receipt_ID']);
		$csvDataArray['custom'] = str_replace(",", " ", $_POST['custom']);
		$csvDataArray['invoice'] = str_replace(",", " ", $_POST['invoice']);
		$csvDataArray['charset'] = str_replace(",", " ", $_POST['charset']);
		$csvDataArray['verify_sign'] = str_replace(",", " ", $_POST['verify_sign']);
		$csvData = implode(",", array_values($csvDataArray));
		fwrite($fh, $csvData . "\n");
		
		fclose($fh);
	}
}



/*****************************************************************************************
 * saveToDb()
 * save the paypal session variables (in POST) to database table billing_paypal
 *****************************************************************************************/
function saveToDb() {

	include('library/opendb.php');

	$txnId = $dbSocket->escapeSimple($_POST['option_selection1']);
	$planName = $dbSocket->escapeSimple($_POST['item_name']);
	$planId = $dbSocket->escapeSimple($_POST['item_number']);
	$quantity = $dbSocket->escapeSimple($_POST['quantity']);
	$receiver_email = $dbSocket->escapeSimple($_POST['receiver_email']);
	$business = $dbSocket->escapeSimple($_POST['business']);
	$tax = $dbSocket->escapeSimple($_POST['tax']);
	$mc_gross = $dbSocket->escapeSimple($_POST['mc_gross']);
	$mc_fee = $dbSocket->escapeSimple($_POST['mc_fee']);
	$mc_handling = $dbSocket->escapeSimple($_POST['mc_handling']);
	$mc_currency = $dbSocket->escapeSimple($_POST['mc_currency']);
	$first_name = $dbSocket->escapeSimple($_POST['first_name']);
	$last_name = $dbSocket->escapeSimple($_POST['last_name']);
	$payer_email = $dbSocket->escapeSimple($_POST['payer_email']);
	$address_name = $dbSocket->escapeSimple($_POST['address_name']);
	$address_street = $dbSocket->escapeSimple($_POST['address_street']);
	$address_country = $dbSocket->escapeSimple($_POST['address_country']);
	$address_country_code = $dbSocket->escapeSimple($_POST['address_country_code']);
	$address_city = $dbSocket->escapeSimple($_POST['address_city']);
	$address_state = $dbSocket->escapeSimple($_POST['address_state']);
	$address_zip = $dbSocket->escapeSimple($_POST['address_zip']);
	$payment_date = $dbSocket->escapeSimple($_POST['payment_date']);
	$payment_status = $dbSocket->escapeSimple($_POST['payment_status']);
	$payment_address_status = $dbSocket->escapeSimple($_POST['payment_address_status']);
	$payer_status = $dbSocket->escapeSimple($_POST['payer_status']);

	// convert paypal date (in PDT time zone) into local time, formatted for mysql
	$payment_date = date('Y-m-d H:i:s', strtotime($payment_date));

	$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT']." SET ".
		" planName='$planName',".
		" planId='$planId',".
		" quantity='$quantity',".
		" business_email='$receiver_email',".
		" business_id='$business',".
		" payment_tax='$tax',".
		" payment_cost='$mc_gross',".
		" payment_fee='$mc_fee',".
		" payment_total='$mc_handling',".
		" payment_currency='$mc_currency',".
		" first_name='$first_name',".
		" last_name='$last_name',".
		" payer_email='$payer_email',".
		" payer_address_name='$address_name',".
		" payer_address_street='$address_street',".
		" payer_address_country='$address_country',".
		" payer_address_country_code='$address_country_code',".
		" payer_address_city='$address_city',".
		" payer_address_state='$address_state',".
		" payer_address_zip='$address_zip',".
		" payment_date='$payment_date',".
		" payment_status='$payment_status', ".
		" payment_address_status='$payment_address_status', ".
		" vendor_type='PayPal', ".
		" payer_status='$payer_status' ".
		" WHERE txnId='$txnId'";
	$res = $dbSocket->query($sql);

	include('library/closedb.php');

}

?>
