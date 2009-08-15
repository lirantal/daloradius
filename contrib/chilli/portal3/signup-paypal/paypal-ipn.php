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
			logToFile("PayPal Tranasction VERIFIED:\n");
			saveToDb();

			include('library/opendb.php');

			// the payment is valid, we activate the user by adding him to freeradius's set of tables (radcheck etc)
			// get transaction id from paypal ipn POST
			$txnId = $dbSocket->escapeSimple($_POST['option_selection1']);

			// find the pin code to activate using the pin
			$sql = "SELECT username,planId FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPAYPAL']." WHERE txnId='$txnId'";
			$res = $dbSocket->query($sql);
			$row = $res->fetchRow();
			$pin = $row[0];
			$planId = $row[1];

			// firstly, we add the user to the radcheck table and authorize him
			$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
				" VALUES (0,'$pin','Auth-Type', ':=', 'Accept')";
			$res = $dbSocket->query($sql);


			// we then search the plans to see if the user should belong to a specific
			// usergroup or shall we just add the appropriate attribute to radcheck
			$sql = "SELECT planTimeBank,planGroup,planTimeType,planRecurring,planRecurringPeriod FROM ".
				$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']." WHERE planId='$planId'";
			$res = $dbSocket->query($sql);
			$row = $res->fetchRow();
			$planTimeBank = $row[0];
			$planGroup = $row[1];
			$planTimeType = $row[2];
			$planRecurring = $row[3];
			$planRecurringPeriod = $row[4];
			
			// the group is set, so we simply add the user to this group
			if ($planGroup != "") {

				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." (UserName,GroupName,priority) ".
					" VALUES ('$pin','$planGroup','0')";
				$res = $dbSocket->query($sql);
			
			} else {
	
				switch ($planTimeType) {

					case "Time-To-Finish":
						// time to finish means that the time credit for the user starts at first login and then the counter
						// starts running, even if he disconnects, his time is running down, until it's 0 and then he used it all up

						$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
							" VALUES (0,'$pin','Access-Period', ':=', '$planTimeBank')";
						$res = $dbSocket->query($sql);

						break;

					case "Accumulative":
						// accumulate means that the user was given a time credit of N minutes and he can use them whenever he wants,
						// and spreads it towards hours, days, weeks or months.

						if ((isset($planRecurring)) && ($planRecurring == "Yes")) {

							switch ($planRecurringPeriod) {

								case "Never":
									$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
										" VALUES (0,'$pin','Max-All-Session', ':=', '$planTimeBank')";
									$res = $dbSocket->query($sql);
									break;

								case "Monthly":
									$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
										" VALUES (0,'$pin','Max-Monthly-Session', ':=', '$planTimeBank')";
									$res = $dbSocket->query($sql);
									break;

								case "Weekly":
									$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
										" VALUES (0,'$pin','Max-Weekly-Session', ':=', '$planTimeBank')";
									$res = $dbSocket->query($sql);
									break;

								case "Daily":
									$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,UserName,Attribute,op,Value) ".
										" VALUES (0,'$pin','Max-Daily-Session', ':=', '$planTimeBank')";
									$res = $dbSocket->query($sql);
									break;
							}
						}						
						break;
				}

			}

			include('library/closedb.php');

		} else {
			// log for manual investigation
			logToFile("PayPal Tranasction INVALID:\n");
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

	$myTime = date("F j, Y, g:i a");

	$fh = fopen($configValues['CONFIG_LOG_PAYPAL_IPN_FILENAME'], 'a');

	if ($fh) {
		fwrite($fh, $myTime ." - ". $customMsg);
	
		$str = $myTime . " *** PAYPAL TRANSACTION BEGIN \n";
		fwrite($fh, $str);

		//loop through the $_POST array and print all vars to the screen.
		foreach($_POST as $key => $value){
			$postdata = $myTime ." - ". $key." = ". $value."\n";
		        fwrite($fh, $postdata);
		}

		$str = $myTime . " *** PAYPAL TRANSACTION END \n\n";
		fwrite($fh, $str);
	
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
	$mc_currency = $dbSocket->escapeSimple($_POST['mc_currency']);	$first_name = $dbSocket->escapeSimple($_POST['first_name']);
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

	$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOBILLINGPAYPAL']." SET ".
		" planName='$planName',".
		" planId='$planId',".
		" quantity='$quantity',".
		" receiver_email='$receiver_email',".
		" business='$business',".
		" tax='$tax',".
		" mc_gross='$mc_gross',".
		" mc_fee='$mc_fee',".
		" mc_currency='$mc_currency',".
		" first_name='$first_name',".
		" last_name='$last_name',".
		" payer_email='$payer_email',".
		" address_name='$address_name',".
		" address_street='$address_street',".
		" address_country='$address_country',".
		" address_country_code='$address_country_code',".
		" address_city='$address_city',".
		" address_state='$address_state',".
		" address_zip='$address_zip',".
		" payment_date='$payment_date',".
                " payment_status='$payment_status', ".
                " payment_address_status='$payment_address_status', ".
                " payer_status='$payer_status' ".
		" WHERE txnId='$txnId'";
	$res = $dbSocket->query($sql);

	include('library/closedb.php');

}

?>
