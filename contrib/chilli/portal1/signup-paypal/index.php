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

	include('library/opendb.php');
	include_once('include/common/common.php');
	$txnId = createPassword(64, $configValues['CONFIG_USER_ALLOWEDRANDOMCHARS']);			
							// to be used for setting up the return url (success.php page)
							// for later retreiving of the transaction details

	$stage2 = false;
	$errorMissingFields = false;
	$userPIN = "";

	if (isset($_POST['submit'])) {

		if (isset($_POST['firstName']))
			$firstName = $_POST['firstName'];

		if (isset($_POST['lastName']))
			$lastName = $_POST['lastName'];

		if (isset($_POST['address']))
			$address = $_POST['address'];

		if (isset($_POST['city']))
			$city = $_POST['city'];

		if (isset($_POST['state']))
			$state = $_POST['state'];

		if (isset($_POST['planId']))
			$planId = $_POST['planId'];

		if ( (isset($firstName)) && (isset($lastName)) && (isset($address)) && (isset($city)) && (isset($state)) && (isset($planId)) ) {

			// all paramteres have been set, save it in the database
			$currDate = date('Y-m-d H:i:s');
			$currBy = "paypal-webinterface";

			// lets create some random data for user pin
			$userPIN = createPassword(8, $configValues['CONFIG_USER_ALLOWEDRANDOMCHARS']);
			


			$planId = $dbSocket->escapeSimple($planId);

			// grab information about a plan from the table
			$sql = "SELECT planId,planName,planCost,planTax,planCurrency FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
				" WHERE (planType='PayPal') AND (planId='$planId') ";
			$res = $dbSocket->query($sql);
			$row = $res->fetchRow();
			$planId = $row[0];
			$planName = $row[1];
			$planCost = $row[2];
			$planTax = $row[3];
			$planCurrency = $row[4];

			// lets add user information to the database
	                $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].
				" (id, username, firstname, lastname, creationdate, creationby)".
                                " VALUES (0,'$userPIN','".$dbSocket->escapeSimple($firstName)."','".$dbSocket->escapeSimple($lastName)."',".
				"'$currDate','$currBy'".
				")";
	                $res = $dbSocket->query($sql);

			// lets add user billing information to the database
	                $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGPAYPAL'].
				" (id, username, txnId, planName, planId)".
                                " VALUES (0,'$userPIN','$txnId','$planName','$planId'".
				")";
	                $res = $dbSocket->query($sql);

			$stage2 = true;

			include('library/closedb.php');	

		} else {

			// if the paramteres haven't been set, we alert the user that these are required
			$errorMissingFields = true;
		}


	}

?>

<html>
<title> Online Registration  </title>
<script src="library/javascript/common.js" type="text/javascript"></script>
<body>

<br/>
<br/>


	<form name="newuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

Select your plan:
<br/>
	<select id="planId" name="planId">
<?php
	include('library/opendb.php');

		$sql = "SELECT planId,planName,planCost,planTax,planCurrency FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']." WHERE planType='PayPal'";
		$res = $dbSocket->query($sql);
		while ($row = $res->fetchRow()) {
			echo "<option value=\"$row[0]\">$row[1] - Cost $row[2] $row[4] </option>";
		}

	include('library/closedb.php');

?>
	</select>

<br/>

<br/>
<b>Personal Details:</b>
<br/><br/>

	First Name <br/>
    <input name="firstName" value="<?php if (isset($firstName)) echo $firstName ?>" />
	<br/>
	Last Name <br/>
    <input name="lastName" value="<?php if (isset($lastName)) echo $lastName ?>" />
	<br/>
	Address <br/>
    <input name="address" value="<?php if (isset($address)) echo $address ?>" />
	<br/>
	City <br/>
    <input name="city" value="<?php if (isset($city)) echo $city ?>" />
	<br/>
	State <br/>
    <input name="state" value="<?php if (isset($state)) echo $state ?>" />

	<br/><br/>
    <input type="submit" value="submit" name="submit">
	<br/><br/>

	</form>

<br/>
<br/>




<?php

if ( (isset($errorMissingFields)) && ($errorMissingFields == true) ) {

	printq('
		<br/>
			<b> Missing fields, please fill out all fields! </b>
		<br/>
		');
}



if ( (isset($stage2)) && ($stage2 == true) ) {
	printq('

		<br/>
			Thank you... this is your user PIN: <b>');
	echo $userPIN;

	echo'</b>
		<br/>
			Please write it down, you will need to enter it at the login page.
		<br/>
		<br/>
			Your account has been created but it will only be active after you complete your payment
		<br/>
			through Paypal, please click the Buy Now button below to complete your payment.

		<br/><br/>

		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_xclick" />
			<input type="hidden" name="business" value="liran_1217096095_biz@enginx.com" />
	
			<input type="hidden" name="return" value="http://84.95.241.193/paypal/success.php?txnId='.$txnId.'" />
			<input type="hidden" name="cancel_return" value="http://84.95.241.193/paypal/cancelled.php" />
			<input type="hidden" name="notify_url" value="http://84.95.241.193/paypal/paypal-ipn.php" />
		
			<input type="hidden" id="amount" name="amount" value="'; if (isset($planCost)) echo $planCost; echo '" />
			<input type="hidden" id="item_name" name="item_name" value="'; if (isset($planName)) echo $planName; echo '" />
			<input type="hidden" name="quantity" value="1" />
			<input type="hidden" id="tax" name="tax" value="'; if (isset($planTax)) echo $planTax; echo '" />
			<input type="hidden" id="item_number" name="item_number" value="'; if (isset($planId)) echo $planId; echo '" />
	
			<input type="hidden" name="no_note" value="1">
			<input type="hidden" id="currency_code" "name="currency_code" value="'; if (isset($planCurrency)) echo $planCurrency; echo '">
			<input type="hidden" name="lc" value="US">

			<input type="hidden" name="on0" value="Transaction ID" />
			<input type="hidden" name="os0" value="'.$txnId.'" />

			<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but23.gif" border="0" name="submit" 
			alt="Make payments with PayPal - its fast, free and secure!">
		</form>	
		';


}

?>


</body>
</html>

