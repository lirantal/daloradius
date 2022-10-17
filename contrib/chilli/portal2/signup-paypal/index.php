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
 *currency_code
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Authors:     Liran Tal <liran@enginx.com>
 *
 * Credits to the implementation of captcha are due to G.Sujith Kumar of codewalkers
 *
 *********************************************************************************************************
 */

	include_once('include/common/common.php');
	include_once('library/config_read.php');
	$txnId = createPassword(64, $configValues['CONFIG_USER_ALLOWEDRANDOMCHARS']); 
                   // to be used for setting up the return url (success.php page)
				   // for later retreiving of the transaction details

	$status = "firstload";
	$errorMissingFields = false;
	$userPIN = "";

	if (isset($_POST['submit'])) {

		(isset($_POST['firstName'])) ? $firstName = $_POST['firstName'] : $firstName = "";
		(isset($_POST['lastName'])) ? $lastName = $_POST['lastName'] : $lastName =  "";
		(isset($_POST['address'])) ? $address = $_POST['address'] : $address = "";
		(isset($_POST['city'])) ? $city = $_POST['city'] : $city = "";
		(isset($_POST['state'])) ? $state = $_POST['state'] : $state = "";
		(isset($_POST['planId'])) ? $planId = $_POST['planId'] : $planId = "";

		if ( ($firstName != "") && ($lastName != "") && ($address != "") && ($city != "") && ($state != "") && ($planId != "") ) {

			// all paramteres have been set, save it in the database
			include('library/opendb.php');

			$currDate = date('Y-m-d H:i:s');
			$currBy = "paypal-webinterface";

			// lets create some random data for user pin
			while (true) {
				
				// generate the pin for the user
				$userPIN = createPassword($configValues['CONFIG_USERNAME_LENGTH'], $configValues['CONFIG_USER_ALLOWEDRANDOMCHARS']);
				
				// check if this pin, although random, may be used before
				$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='".
					$dbSocket->escapeSimple($userPIN)."'";
				$res = $dbSocket->query($sql);
	
				// if it wasn't used then we break out of the loop, otherwise we continue to 
				// generate pins and check them
				if ($res->numRows() == 0)
					break;
			}
			
			
			$planId = $dbSocket->escapeSimple($planId);

			// grab information about a plan from the table
			$sql = "SELECT id AS planId,planName,planCost,planTax,planCurrency,planRecurring,planRecurringPeriod FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
					" WHERE (planType='PayPal') AND (id=$planId) ";
			$res = $dbSocket->query($sql);
			$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
			$planId = $row['planId'];
			$planName = $row['planName'];
			$planCost = $row['planCost'];
			$planTax = $row['planTax'];
			$planRecurring = $row['planRecurring'];
			$planRecurringPeriod = $row['planRecurringPeriod'];
			
			// the tax is a relative percentage amount of the price, thus we need to
			// calculate the tax amount
			$planTax = (($planTax/100)*$planCost);

			$planCurrency = $row['planCurrency'];

            $planTax = number_format($planTax, 2, '.', '');
            $planCost = number_format($planCost, 2, '.', '');

			// lets add user information to the database
			$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].
					" (id, username, firstname, lastname, address, city, state, creationdate, creationby)".
					" VALUES (0,'$userPIN','".$dbSocket->escapeSimple($firstName)."','".$dbSocket->escapeSimple($lastName)."', '".
					$dbSocket->escapeSimple($address)."','".$dbSocket->escapeSimple($city)."','".$dbSocket->escapeSimple($state)."', ".
					"'$currDate','$currBy'".
					")";
			$res = $dbSocket->query($sql);
			
			// lets add user billing information to the database
			$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
					" (id, username, planname, contactperson, address, city, state, creationdate, creationby) ".
					" VALUES (0, '$userPIN', '$planName', '".$dbSocket->escapeSimple($firstName)." ".$dbSocket->escapeSimple($lastName)."', '".
					$dbSocket->escapeSimple($address)."','".$dbSocket->escapeSimple($city)."','".$dbSocket->escapeSimple($state)."', ".
					" '$currDate', '$currBy'".
					")";
			$res = $dbSocket->query($sql);
			
			//if ($planRecurring == "No") {
				// lets add user billing information to the database
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT'].
						" (id, username, txnId, planId, vendor_type, payment_date)".
						" VALUES (0,'$userPIN','$txnId', $planId, 'PayPal', '$currDate'".
						")";
				$res = $dbSocket->query($sql);
			//}
			
			$status = "paypal";

			include('library/closedb.php');

		} else {

			// if the paramteres haven't been set, we alert the user that these are required
			$errorMissingFields = true;
		}

	}

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>User Sign-Up</title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
</head>
<script src="library/javascript/common.js" type="text/javascript"></script>
<body>

<div id="wrap">

                <div class="header"><p>Hotspot<span>Login</span><sup>
                        By <a href="http://templatefusion.org">TemplateFusion.org</a></sup></p>
                </div>

        <div id="navigation">
                <ul class="glossymenu">
                        <li><a href="index.php" class="current"><b>Home</b></a></li>
                        <li><a href="#"><b>Services</b></a></li>
                        <li><a href="#"><b>About Us</b></a></li>
                        <li><a href="#"><b>Contact</b></a></li>
                </ul>
        </div>

        <div id="body">
                <h1>Sign-Up</h1>
                <p>
                        <center>

	<?php

                /*************************************************************************************************************************************************
                 *
                 * switch case for status of the sign-up process, whether it's the first time the user accesses it, or rather he already submitted
                 * the form with either successful or errornous result
                 *
                 *************************************************************************************************************************************************/

                if ( (isset($errorMissingFields)) && ($errorMissingFields == true) ) {

                        printq('
                                <br/>
                                        <font color="red"><b> Missing fields, please fill out all fields! </b></font>
                                <br/><br/>
                                ');
                }


                switch ($status) {
                        case "firstload":

                                echo "
                                        We allow our customers to sign-up for Internet access plans using their PayPal accounts.<br/>
                                        Complete the form and click the Apply button to register in our database, shortly after you will see<br/>
                                        a Buy Now button, click it to redirect to your PayPal homepage and confirm the transaction.

                                        <form name='newuser' action='".$_SERVER['PHP_SELF']."' method='post'>

					<table>
                                        <tr><td>Select your plan:</td>
                                        	<td><select id='planId' name='planId'>
                                        ";

                                include('library/opendb.php');

                                $sql = "SELECT id AS planId,planName,planCost,planTax,planCurrency FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
                                        " WHERE planType='PayPal'";
                                $res = $dbSocket->query($sql);
                                while ($row = $res->fetchRow()) {
                                        echo "<option value=\"$row[0]\">$row[1] - Cost $row[2] $row[4] </option>";
                                }

                                include('library/closedb.php');

                                echo "
                                        </td></select></tr>

                                        <br/><br/>

                                        <tr><td>First name:</td>
                                            <td> <input name='firstName' value='"; if (isset($firstName)) echo $firstName; echo "' /> </td></tr>
                                        <tr><td>Last name:</td>
                                            <td> <input name='lastName' value='"; if (isset($lastName)) echo $lastName; echo "' /> </td></tr>
                                        <tr><td>Address:</td>
                                            <td> <input name='address' value='"; if (isset($address)) echo $address; echo "' /> </td></tr>
                                        <tr><td>City:</td>
                                            <td> <input name='city' value='"; if (isset($city)) echo $city; echo "' /> </td></tr>
                                        <tr><td>State:</td>
                                            <td> <input name='state' value='"; if (isset($state)) echo $state; echo "' /> </td></tr>

					</table><br/><br/>
                                            <input type='submit' value='Submit' name='submit'>

                                        </form>
                                        ";

                                break;


                        case "paypal":
                                printq('

                                        <font color="blue"><b>Thank you...</b></font>
                                        <br/><br/>

                                        Your PIN code has been created but it will only be activated after you complete and confirm<br/>
                                        your payment through PayPal. Following is your PIN code, which you will need in-order to access<br/>
                                        our Hotspot services.<br/><br/>

                                        <table><tr><td>PIN Code:</td><td> <b>
                                        ');

                                echo $userPIN;
								
                                echo '
                                        </b></td></tr></table>
                                        <br/>
										';
										
								if ($planRecurring == "No") {
								
									echo '
                                        <form action="'.$configValues['CONFIG_MERCHANT_WEB_PAYMENT'].'" method="post">
                                                <input type="hidden" name="cmd" value="_xclick" />
                                                <input type="hidden" name="business" value="'.$configValues['CONFIG_MERCHANT_BUSINESS_ID'].'" />

                                                <input type="hidden" name="return" value="'.$configValues['CONFIG_MERCHANT_IPN_URL_ROOT'].'/'.
																								$configValues['CONFIG_MERCHANT_IPN_URL_RELATIVE_SUCCESS'].
																								'?txnId='.$txnId.'&username='.$userPIN.'" />
                                                <input type="hidden" name="cancel_return" value="'.$configValues['CONFIG_MERCHANT_IPN_URL_ROOT'].'/'.
																										$configValues['CONFIG_MERCHANT_IPN_URL_RELATIVE_FAILURE'].'" />
                                                <input type="hidden" name="notify_url" value="'.$configValues['CONFIG_MERCHANT_IPN_URL_ROOT'].'/'.
																										$configValues['CONFIG_MERCHANT_IPN_URL_RELATIVE_DIR'].'" />

                                                <input type="hidden" id="amount" name="amount" value="'; if (isset($planCost)) echo $planCost; echo '" />
                                                <input type="hidden" id="item_name" name="item_name" value="'; if (isset($planName)) echo $planName; echo '" />
                                                <input type="hidden" name="quantity" value="1" />
                                                <input type="hidden" id="tax" name="tax" value="'; if (isset($planTax)) echo $planTax; echo '" />
                                                <input type="hidden" id="item_number" name="item_number" value="'; if (isset($planId)) echo $planId; echo '" />

                                                <input type="hidden" name="no_note" value="1">
                                                <input type="hidden" id="currency_code" name="currency_code" value="'; if (isset($planCurrency)) echo $planCurrency; echo '">
												<input type="hidden" name="no_shipping" value="1">
                                                <input type="hidden" name="lc" value="US">

                                                <input type="hidden" name="on0" value="Transaction ID" />
                                                <input type="hidden" name="os0" value="'.$txnId.'" />

                                                <input type="hidden" name="on1" value="Username" />
                                                <input type="hidden" name="os1" value="'.$userPIN.'" />
												
                                                <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but23.gif" border="0" name="submit"
                                                alt="Make payments with PayPal - its fast, free and secure!">
                                        </form>
										';
								
								} else if ($planRecurring == "Yes") {
								
									//$t3 = billing cycle period
									
									$t3 = "M"; //billing cycle period
									$p3 = "1"; //billing cycle length
									if ($planRecurringPeriod == "Daily")
										$t3 = "D";
									
									if ($planRecurringPeriod == "Weekly")
										$t3 = "W";
									
									if ($planRecurringPeriod == "Monthly")
										$t3 = "M";
									
									if ($planRecurringPeriod == "Yearly")
										$t3 = "Y";
									
									echo '
										<form name="_xclick" action="'.$configValues['CONFIG_MERCHANT_WEB_PAYMENT'].'" method="post">
										<input type="hidden" name="cmd" value="_xclick-subscriptions">
										<input type="hidden" name="business" value="'.$configValues['CONFIG_MERCHANT_BUSINESS_ID'].'">
										<input type="hidden" name="no_shipping" value="1">
										<input type="hidden" name="return" value="'.$configValues['CONFIG_MERCHANT_IPN_URL_ROOT'].'/'.
																						$configValues['CONFIG_MERCHANT_IPN_URL_RELATIVE_SUCCESS'].
																						'?txnId='.$txnId.'" />
										<input type="hidden" name="cancel_return" value="'.$configValues['CONFIG_MERCHANT_IPN_URL_ROOT'].'/'.
																								$configValues['CONFIG_MERCHANT_IPN_URL_RELATIVE_FAILURE'].'" />
										<input type="hidden" name="notify_url" value="'.$configValues['CONFIG_MERCHANT_IPN_URL_ROOT'].'/'.
																								$configValues['CONFIG_MERCHANT_IPN_URL_RELATIVE_DIR'].'" />
										<input type="hidden" id="currency_code" name="currency_code" value="'; if (isset($planCurrency)) echo $planCurrency; echo '">
										<input type="hidden" name="lc" value="US">
										
										<input type="hidden" name="on0" value="Transaction ID" />
										<input type="hidden" name="os0" value="'.$txnId.'" />
										<input type="hidden" name="on1" value="Username" />
										<input type="hidden" name="os1" value="'.$userPIN.'" />
										
										<input type="hidden" id="item_name" name="item_name" value="'; if (isset($planName)) echo $planName; echo '" />
										<input type="hidden" name="quantity" value="1" />
										<input type="hidden" id="tax" name="tax" value="'; if (isset($planTax)) echo $planTax; echo '" />
										<input type="hidden" id="item_number" name="item_number" value="'; if (isset($planId)) echo $planId; echo '" />

										<input type="hidden" name="no_note" value="1">
										
										<input type="hidden" id="a3" name="a3" value="'; if (isset($planCost)) echo $planCost; echo '" />
										<input type="hidden" name="p3" value="'.$p3.'">
										<input type="hidden" name="t3" value="'.$t3.'">
										<input type="hidden" name="src" value="1">
										<input type="hidden" name="sra" value="1">
										
										<input type="hidden" name="custom" value="'.$userPIN.'" />
										
										<input type="image" src="http://www.paypal.com/en_US/i/btn/btn_subscribe_LG.gif" border="0" name="submit" alt="Make payments with PayPal - its fast, free and secure!">
										</form>
									';
								}

								echo '
									<br/>
									<b>It is recommended that you will write it down now in-case of a failure.</b><br/><br/>
									<br/><br/>
									 ';


                                break;

                }


        ?>


                        </center>
                </p>


                <h1>Hotspot References</h1>
                <a href="#"><img src="images/portfolio1.jpg" alt="portfolio1" /></a>
                <a href="#"><img src="images/portfolio2.jpg" alt="portfolio2" /></a>
                <a href="#"><img src="images/portfolio3.jpg" alt="portfolio3" /></a>
                <a href="#"><img src="images/portfolio4.jpg" alt="portfolio4" /></a>
        </div>



        <div id="footer">Enginx&copy;2008 All Rights Reserved &bull; Enginx and daloRADIUS Hotspot Systems <br/>
                Design by <a href="http://templatefusion.org">TemplateFusion</a>
        </div>


</div>

</body>
</html>

