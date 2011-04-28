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

	$header = "";
	
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
		return false;
	} else {
		fputs ($fp, $header . $req);
		$res = "";
		while (!feof($fp)) {
			$res .= fgets ($fp, 1024);
		}


		/*
		//uncomment for extended debugging 
		$fh = fopen("/tmp/paypal-log.log", "a");
		fwrite($fh, "\nREQUEST---------------\n");
		fwrite($fh, $req."\n\n");
		fwrite($fh, "\nRESPONSE--------------\n");
		fwrite($fh, $res."\n\n");
		fclose($fh);
		*/

		
		if (preg_match("/VERIFIED/", $res)) {

			// we assume that this is a valid paypal response and we save the response details
			// into the database

			// log the reponse with a custom message
			logToFile();
			saveToDb();

			include('library/opendb.php');
			require_once('include/common/provisionUser.php');

			// the payment is valid, we activate the user by adding him to freeradius's set of tables (radcheck etc)
			// get transaction id from paypal ipn POST. txnId is dalo's generated unique txnid
			$txnId = $dbSocket->escapeSimple($_POST['option_selection1']);

			// paypal's unique transaction id 
			$txn_id = isset($_POST['txn_id']) ? $dbSocket->escapeSimple($_POST['txn_id']) : "";
		
			// if the $txn_id isn't set then this might be a recurring payment id, for which we log the txn_id as it uses the
			// field name recurring_payment_id instead of txn_id for recurring profiles (PayPal)
			if (!$txn_id)
				$txn_id = isset($_POST['recurring_payment_id']) ? $dbSocket->escapeSimple($_POST['recurring_payment_id']) : "";
			
			// sadly, on some txn_type's like subscribe and not payment the field name will be subscr_id and on recurring payments it
			// will be recurring_payments_id, though both of those fields are idential, just differ in the different transactions types
			// being made.
			if (!$txn_id)
				$txn_id = isset($_POST['subscr_id']) ? $dbSocket->escapeSimple($_POST['subscr_id']) : "";
			
			provisionUser($dbSocket, $txnId, $txn_id);

			include('library/closedb.php');

		} else {
			// log for manual investigation
			logToFile();
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
function logToFile($customMsg = "") {

	include('library/config_read.php');

	$fh = fopen($configValues['CONFIG_LOG_MERCHANT_IPN_FILENAME'], 'a');
	
	if ($fh) {
				
		// only if the file size is 0 (meaning it's a new file) then we shuld
		// create the titles of all the fields in csv format, otherwise we assume
		// that it has been created before and we simply append to it
		
		if (!filesize($configValues['CONFIG_LOG_MERCHANT_IPN_FILENAME']) > 0) {
			$csvFields = "test_ipn,payment_type,payment_date,payment_status,pending_reason,option_name1,option_selection1,".
				"option_name2,option_selection2,address_status,".
				"payer_status,first_name,last_name,payer_email,payer_id,address_name,address_country,".
				"address_country_code,address_zip,address_state,address_city,address_street,business,".
				"receiver_email,receiver_id,residence_country,item_name,item_number,item_name1,item_number1,".
				"quantity,quantity1,shipping,tax,mc_currency,mc_fee,mc_gross,mc_gross1,mc_handling,mc_handling1,".
				"mc_shipping,mc_shipping1,txn_type,txn_id,parent_txn_id,recurring_payment_id,subscr_id,notify_version,reason_code,receipt_ID,".
				"custom,invoice,charset,verify_sign,profile_status";
			fwrite($fh, $csvFields . "\n");
		}
		
		/*
		$keys="";
		foreach($_POST as $key => $val) {
			$post[$key] = $val;
			$keys.="$key,";
		}
		*/

		// get all the values of each field and write it
		$csvDataArray['test_ipn'] = str_replace(",", " ", (isset($_POST['option_selection1']) ? $_POST['test_ipn'] : ""));
		$csvDataArray['payment_type'] = str_replace(",", " ",(isset($_POST['payment_type']) ? $_POST['payment_type'] : ""));
		$csvDataArray['payment_date'] = date('Y-m-d H:i:s', strtotime((isset($_POST['payment_date']) ? $_POST['payment_date'] : "")));
		$csvDataArray['subscr_date'] = date('Y-m-d H:i:s', strtotime((isset($_POST['subscr_date']) ? $_POST['subscr_date'] : "")));
		$csvDataArray['payment_status'] = str_replace(",", " ", (isset($_POST['payment_status']) ? $_POST['payment_status'] : ""));
		$csvDataArray['pending_reason'] = str_replace(",", " ", (isset($_POST['pending_reason']) ? $_POST['pending_reason'] : ""));
		$csvDataArray['option_name1'] = str_replace(",", " ", (isset($_POST['option_name1']) ? $_POST['option_name1'] : ""));
		$csvDataArray['option_selection1'] = str_replace(",", " ", (isset($_POST['option_selection1']) ? $_POST['option_selection1'] : ""));
		$csvDataArray['option_name2'] = str_replace(",", " ",(isset($_POST['option_name2']) ?  $_POST['option_name2'] : ""));
		$csvDataArray['option_selection2'] = str_replace(",", " ", (isset($_POST['option_selection2']) ? $_POST['option_selection2'] : ""));
		$csvDataArray['address_status'] = str_replace(",", " ", (isset($_POST['address_status']) ? $_POST['address_status'] : ""));
		$csvDataArray['payer_status'] = str_replace(",", " ", (isset($_POST['payer_status']) ? $_POST['payer_status'] : ""));
		$csvDataArray['first_name'] = str_replace(",", " ", (isset($_POST['first_name']) ? $_POST['first_name'] : ""));
		$csvDataArray['last_name'] = str_replace(",", " ", (isset($_POST['last_name']) ? $_POST['last_name'] : ""));
		$csvDataArray['payer_email'] = str_replace(",", " ", (isset($_POST['payer_email']) ? $_POST['payer_email'] : ""));
		$csvDataArray['payer_id'] = str_replace(",", " ", (isset($_POST['payer_id']) ? $_POST['payer_id'] : ""));
		$csvDataArray['address_name'] = str_replace(",", " ", (isset($_POST['address_name']) ? $_POST['address_name'] : ""));
		$csvDataArray['address_country'] = str_replace(",", " ", (isset($_POST['address_country']) ? $_POST['address_country'] : ""));
		$csvDataArray['address_country_code'] = str_replace(",", " ", (isset($_POST['address_country_code']) ? $_POST['address_country_code'] : ""));
		$csvDataArray['address_zip'] = str_replace(",", " ", (isset($_POST['address_zip']) ? $_POST['address_zip'] : ""));
		$csvDataArray['address_state'] = str_replace(",", " ", (isset($_POST['address_state']) ? $_POST['address_state'] : ""));
		$csvDataArray['address_city'] = str_replace(",", " ", (isset($_POST['address_city']) ?$_POST['address_city'] : ""));
		$csvDataArray['address_street'] = str_replace(",", " ", (isset($_POST['address_street']) ?$_POST['address_street'] : ""));
		$csvDataArray['business'] = str_replace(",", " ", (isset($_POST['business']) ?$_POST['business'] : ""));
		$csvDataArray['receiver_email'] = str_replace(",", " ", (isset($_POST['receiver_email']) ?$_POST['receiver_email'] : ""));
		$csvDataArray['receiver_id'] = str_replace(",", " ", (isset($_POST['receiver_id']) ?$_POST['receiver_id'] : ""));
		$csvDataArray['residence_country'] = str_replace(",", " ", (isset($_POST['residence_country']) ?$_POST['residence_country'] : ""));
		$csvDataArray['item_name'] = str_replace(",", " ", (isset($_POST['item_name']) ?$_POST['item_name'] : ""));
		$csvDataArray['item_number'] = str_replace(",", " ", (isset($_POST['item_number']) ?$_POST['item_number'] : ""));
		$csvDataArray['item_name1'] = str_replace(",", " ", (isset($_POST['item_name1']) ?$_POST['item_name1'] : ""));
		$csvDataArray['item_number1'] = str_replace(",", " ", (isset($_POST['item_number1']) ?$_POST['item_number1'] : ""));
		$csvDataArray['quantity'] = str_replace(",", " ", (isset($_POST['quantity']) ?$_POST['quantity'] : ""));
		$csvDataArray['quantity1'] = str_replace(",", " ", (isset($_POST['quantity1']) ?$_POST['quantity1'] : ""));
		$csvDataArray['shipping'] = str_replace(",", " ", (isset($_POST['shipping']) ?$_POST['shipping'] : ""));
		$csvDataArray['tax'] = str_replace(",", " ", (isset($_POST['tax']) ?$_POST['tax'] : ""));
		$csvDataArray['mc_currency'] = str_replace(",", " ", (isset($_POST['mc_currency']) ?$_POST['mc_currency'] : ""));
		$csvDataArray['mc_fee'] = str_replace(",", " ", (isset($_POST['mc_fee']) ?$_POST['mc_fee'] : ""));
		$csvDataArray['mc_gross'] = str_replace(",", " ", (isset($_POST['mc_gross']) ?$_POST['mc_gross'] : ""));
		$csvDataArray['mc_gross1'] = str_replace(",", " ", (isset($_POST['mc_gross1']) ?$_POST['mc_gross1'] : ""));
		$csvDataArray['mc_handling'] = str_replace(",", " ", (isset($_POST['mc_handling']) ?$_POST['mc_handling'] : ""));
		$csvDataArray['mc_handling1'] = str_replace(",", " ", (isset($_POST['mc_handling1']) ?$_POST['mc_handling1'] : ""));
		$csvDataArray['mc_shipping'] = str_replace(",", " ", (isset($_POST['mc_shipping']) ?$_POST['mc_shipping'] : ""));
		$csvDataArray['mc_shipping1'] = str_replace(",", " ", (isset($_POST['mc_shipping1']) ?$_POST['mc_shipping1'] : ""));
		$csvDataArray['txn_type'] = str_replace(",", " ", (isset($_POST['txn_type']) ?$_POST['txn_type'] : ""));
		$csvDataArray['txn_id'] = str_replace(",", " ", (isset($_POST['txn_id']) ?$_POST['txn_id'] : ""));
		$csvDataArray['parent_txn_id'] = str_replace(",", " ", (isset($_POST['parent_txn_id']) ?$_POST['parent_txn_id'] : ""));
		$csvDataArray['recurring_payment_id'] = str_replace(",", " ", (isset($_POST['recurring_payment_id']) ?$_POST['recurring_payment_id'] : ""));
		$csvDataArray['subscr_id'] = str_replace(",", " ", (isset($_POST['subscr_id']) ?$_POST['subscr_id'] : ""));
		$csvDataArray['notify_version'] = str_replace(",", " ", (isset($_POST['notify_version']) ?$_POST['notify_version'] : ""));
		$csvDataArray['reason_code'] = str_replace(",", " ", (isset($_POST['reason_code']) ?$_POST['reason_code'] : ""));
		$csvDataArray['receipt_ID'] = str_replace(",", " ", (isset($_POST['receipt_ID']) ?$_POST['receipt_ID'] : ""));
		$csvDataArray['custom'] = str_replace(",", " ", (isset($_POST['custom']) ?$_POST['custom'] : ""));
		$csvDataArray['invoice'] = str_replace(",", " ", (isset($_POST['invoice']) ?$_POST['invoice'] : ""));
		$csvDataArray['charset'] = str_replace(",", " ", (isset($_POST['charset']) ?$_POST['charset'] : ""));
		$csvDataArray['verify_sign'] = str_replace(",", " ", (isset($_POST['verify_sign']) ?$_POST['verify_sign'] : ""));
		$csvDataArray['profile_status'] = str_replace(",", " ", (isset($_POST['profile_status']) ?$_POST['profile_status'] : ""));
		
		
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
	
	$txnId = isset($_POST['option_selection1']) ? $dbSocket->escapeSimple($_POST['option_selection1']) : "";
	$username = isset($_POST['option_selection2']) ? $dbSocket->escapeSimple($_POST['option_selection2']) : "";
	//$planName = isset($_POST['item_name']) ? $dbSocket->escapeSimple($_POST['item_name']) : "";
	$planId = isset($_POST['item_number']) ? $dbSocket->escapeSimple($_POST['item_number']) : "";
	$quantity = isset($_POST['quantity']) ? $dbSocket->escapeSimple($_POST['quantity']) : "";
	$receiver_email = isset($_POST['receiver_email']) ? $dbSocket->escapeSimple($_POST['receiver_email']) : "";
	$business = isset($_POST['business']) ? $dbSocket->escapeSimple($_POST['business']) : "";
	$tax = isset($_POST['tax']) ? $dbSocket->escapeSimple($_POST['tax']) : "";
	$mc_gross = isset($_POST['mc_gross']) ? $dbSocket->escapeSimple($_POST['mc_gross']) : "";
	$mc_fee = isset($_POST['mc_fee']) ? $dbSocket->escapeSimple($_POST['mc_fee']) : "";
	$mc_handling = isset($_POST['mc_handling']) ? $dbSocket->escapeSimple($_POST['mc_handling']) : "";
	$mc_currency = isset($_POST['mc_currency']) ? $dbSocket->escapeSimple($_POST['mc_currency']) : "";
	$first_name = isset($_POST['first_name']) ? $dbSocket->escapeSimple($_POST['first_name']) : "";
	$last_name = isset($_POST['last_name']) ? $dbSocket->escapeSimple($_POST['last_name']) : "";
	$payer_email = isset($_POST['payer_email']) ? $dbSocket->escapeSimple($_POST['payer_email']) : "";
	$address_name = isset($_POST['address_name']) ? $dbSocket->escapeSimple($_POST['address_name']) : "";
	$address_street = isset($_POST['address_street']) ? $dbSocket->escapeSimple($_POST['address_street']) : "";
	$address_country = isset($_POST['address_country']) ? $dbSocket->escapeSimple($_POST['address_country']) : "";
	$address_country_code = isset($_POST['address_country_code']) ? $dbSocket->escapeSimple($_POST['address_country_code']) : "";
	$address_city = isset($_POST['address_city']) ? $dbSocket->escapeSimple($_POST['address_city']) : "";
	$address_state = isset($_POST['address_state']) ? $dbSocket->escapeSimple($_POST['address_state']) : "";
	$address_zip = isset($_POST['address_zip']) ? $dbSocket->escapeSimple($_POST['address_zip']) : "";
	$payment_date = isset($_POST['payment_date']) ? $dbSocket->escapeSimple($_POST['payment_date']) : "";
	$subscr_date = isset($_POST['subscr_date']) ? $dbSocket->escapeSimple($_POST['subscr_date']) : "";
	$payment_status = isset($_POST['payment_status']) ? $dbSocket->escapeSimple($_POST['payment_status']) : "";
	$payment_address_status = isset($_POST['payment_address_status']) ? $dbSocket->escapeSimple($_POST['payment_address_status']) : "";
	$payer_status = isset($_POST['payer_status']) ? $dbSocket->escapeSimple($_POST['payer_status']) : "";
	$pending_reason = isset($_POST['pending_reason']) ? $dbSocket->escapeSimple($_POST['pending_reason']) : "";
	$reason_code = isset($_POST['reason_code']) ? $dbSocket->escapeSimple($_POST['reason_code']) : "";
	$receipt_ID = isset($_POST['receipt_ID']) ? $dbSocket->escapeSimple($_POST['receipt_ID']) : "";
	$payment_type = isset($_POST['payment_type']) ? $dbSocket->escapeSimple($_POST['payment_type']) : "";
	$txn_type =isset($_POST['txn_type']) ?  $dbSocket->escapeSimple($_POST['txn_type']) : "";
	$txn_id = isset($_POST['txn_id']) ? $dbSocket->escapeSimple($_POST['txn_id']) : "";
	
	// if the $txn_id isn't set then this might be a recurring payment id, for which we log the txn_id as it uses the
	// field name recurring_payment_id instead of txn_id for recurring profiles (PayPal)
	if (!$txn_id)
		$txn_id = isset($_POST['recurring_payment_id']) ? $dbSocket->escapeSimple($_POST['recurring_payment_id']) : "";
	
	// sadly, on some txn_type's like subscribe and not payment the field name will be subscr_id and on recurring payments it
	// will be recurring_payments_id, though both of those fields are idential, just differ in the different transactions types
	// being made.
	if (!$txn_id)
		$txn_id = isset($_POST['subscr_id']) ? $dbSocket->escapeSimple($_POST['subscr_id']) : "";
		
	// if payment_date is not set, then this is probably because this is a recurring
	// transaction, hence it is using the subscr_date parameter instead
	if ($payment_date == "")
		$payment_date = $subscr_date;
	// convert paypal date (in PDT time zone) into local time, formatted for mysql
	$payment_date = date('Y-m-d H:i:s', strtotime($payment_date));
	
	// for this plan that the payment is being made, query to check what is the
	// plan's settings in terms of Recurring and the Recurring Period
	$sql = "SELECT planRecurring, planRecurringPeriod ".
			" FROM ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
			" WHERE (id=$planId) ";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
	$planRecurring = $row['planRecurring'];
	$planRecurringPeriod = $row['planRecurringPeriod'];

	// if this plan is not recurring then we update the record with the status confirmation from paypal
	if ($planRecurring == "No") {
	
		$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT']." SET ".
			" planId=$planId,".
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
			" pending_reason='$pending_reason', ".
			" reason_code='$reason_code', ".
			" receipt_ID='$receipt_ID', ".
			" payment_type='$payment_type', ".
			" txn_type='$txn_type', ".
			" txn_id='$txn_id', ".
			" vendor_type='PayPal', ".
			" payer_status='$payer_status' ".
			" WHERE txnId='$txnId'";
		$res = $dbSocket->query($sql);
	} else {
		
		// otherwise, if the plan is recurring we insert a record
		// query the billing_merchant transactions tables to check if a single entry
		// with this transaction exists or are there more?
		$sql = "SELECT txnId, txn_type ".
				" FROM ".
				$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT'].
				" WHERE (txnId='$txnId') ";
		$res = $dbSocket->query($sql);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		
		if ( ($res->numRows() == 1) && ($row['txn_type'] == "") ) {
			
			// if one entry exists for this transaction then we update it
			$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT']." SET ".
				" planId=$planId,".
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
				" pending_reason='$pending_reason', ".
				" reason_code='$reason_code', ".
				" receipt_ID='$receipt_ID', ".
				" payment_type='$payment_type', ".
				" txn_type='$txn_type', ".
				" txn_id='$txn_id', ".
				" vendor_type='PayPal', ".
				" payer_status='$payer_status' ".
				" WHERE txnId='$txnId'";
			$res = $dbSocket->query($sql);
		
		} else {
			
			$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT'].
				" ( ".
				" id, username, txnId, planId, quantity, business_email, business_id, txn_type, txn_id, ".
				"payment_type, payment_tax, payment_cost, payment_fee, payment_total, payment_currency, first_name, ".
				"last_name, payer_email, payer_address_name, payer_address_street, payer_address_country, payer_address_country_code, ".
				"payer_address_city, payer_address_state, payer_address_zip, payment_date, payment_status, pending_reason, reason_code, ".
				"receipt_ID, payment_address_status, vendor_type, payer_status ".
				" ) ".
				" VALUES (0, ".
				"'".$username."', ".
				"'".$txnId."', ".
				$planId.", ".
				"'".$quantity."', ".
				"'".$receiver_email."', ".
				"'".$business."', ".
				"'".$txn_type."', ".
				"'".$txn_id."', ".
				"'".$payment_type."', ".
				"'".$tax."', ".
				"'".$mc_gross."', ".
				"'".$mc_fee."', ".
				"'".$mc_handling."', ".
				"'".$mc_currency."', ".
				"'".$first_name."', ".
				"'".$last_name."', ".
				"'".$payer_email."', ".
				"'".$address_name."', ".
				"'".$address_street."', ".
				"'".$address_country."', ".
				"'".$address_country_code."', ".
				"'".$address_city."', ".
				"'".$address_state."', ".
				"'".$address_zip."', ".
				"'".$payment_date."', ".
				"'".$payment_status."', ".
				"'".$pending_reason."', ".
				"'".$reason_code."', ".
				"'".$receipt_ID."', ".
				"'".$payment_address_status."', ".
				"'PayPal', ".
				"'".$payer_status."' ".
				" ) ";
			$res = $dbSocket->query($sql);
		}
	}

	include('library/closedb.php');

}

?>
