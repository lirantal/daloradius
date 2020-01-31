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

			$currDate = date('Y-m-d H:i:s');
			$currBy = "2Checkout-webinterface";

			$userPIN = createPassword($configValues['CONFIG_USERNAME_LENGTH'], $configValues['CONFIG_USER_ALLOWEDRANDOMCHARS']);
                   // lets create some random data for user pin


			$planId = $dbSocket->escapeSimple($planId);

			// grab information about a plan from the table
			$sql = "SELECT planId,planName,planCost,planTax,planCurrency FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
					" WHERE (planType='2Checkout') AND (planId='$planId') ";
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
			$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
					" (id, username, planname, ".
					" creationdate, creationby) ".
					" VALUES (0, '$userPIN', '$planName', ".
					" '$currDate', '$currBy'".
					")";
			$res = $dbSocket->query($sql);
			

			// lets add user billing information to the database
			$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT'].
					" (id, username, txnId, planName, planId, vendor_type, payment_date)".
					" VALUES (0,'$userPIN','$txnId','$planName','$planId', '2Checkout', '$currDate'".
					")";
			$res = $dbSocket->query($sql);

			$status = "2checkout";

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
                                        Complete the form and click the Apply button to register in our database, after which you will <br/>
                                        need to confirm the transaction with a Buy button, redirecting you to the merchant account to complete <br/>
										the payment

                                        <form name='newuser' action='".$_SERVER['PHP_SELF']."' method='post'>

					<table>
                                        <tr><td>Select your plan:</td>
                                        	<td><select id='planId' name='planId'>
                                        ";

                                include('library/opendb.php');

                                $sql = "SELECT planId,planName,planCost,planTax,planCurrency FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
                                        " WHERE planType='2Checkout'";
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


                        case "2checkout":
                                printq('

                                        <font color="blue"><b>Thank you...</b></font>
                                        <br/><br/>

                                        Your PIN code has been created but it will only be activated after you complete and confirm<br/>
                                        your payment. Following is your PIN code, which you will need in-order to access<br/>
                                        our Hotspot services.<br/><br/>

                                        <table><tr><td>PIN Code:</td><td> <b>
                                        ');

                                echo $userPIN;

                                echo'
                                        </b></td></tr></table>
                                        <br/>

                                        <form action="2co_start.php" method="post">
                                                <input type="hidden" name="business" value="'.$configValues['CONFIG_MERCHANT_BUSINESS_ID'].'" />

                                                <input type="hidden" id="amount" name="amount" value="'; if (isset($planCost)) echo $planCost; echo '" />
                                                <input type="hidden" id="item_name" name="item_name" value="'; if (isset($planName)) echo $planName; echo '" />
                                                <input type="hidden" name="quantity" value="1" />
                                                <input type="hidden" id="tax" name="tax" value="'; if (isset($planTax)) echo $planTax; echo '" />
                                                <input type="hidden" id="item_number" name="item_number" value="'; if (isset($planId)) echo $planId; echo '" />
                                                <input type="hidden" id="currency_code" "name="currency_code" value="'; if (isset($planCurrency)) echo $planCurrency; echo '">
                                                <input type="hidden" name="custom" value="'.$txnId.'" />

												<input type="submit" name="submit" value="Buy" />
                                        </form>

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

