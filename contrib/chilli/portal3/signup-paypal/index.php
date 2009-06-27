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
 * Credits to the implementation of captcha are due to G.Sujith Kumar of codewalkers
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
                        $currBy = "paypal-webinterface";
						
						// lets create some random data for user pin
                        $userPIN = createPassword($configValues['CONFIG_PASSWORD_LENGTH'], $configValues['CONFIG_USER_ALLOWEDRANDOMCHARS']);


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
<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<script src="library/javascript/common.js" type="text/javascript"></script>
<body>
<div id="wrapper">
  <div id="header">
    <div id="nav">	<a href="index.html">Sign-Up</a> &nbsp;|&nbsp; 
			<a href="#">Terms Of Service</a> &nbsp;|&nbsp; 
			<a href="#">About us</a> &nbsp;|&nbsp; 
			<a href="#">Contact us</a> &nbsp;|&nbsp; 
     </div>
    <div id="bg"></div>
  </div>
  <div id="main-content">
    <div id="left-column">
      <div id="logo"><img src="images/big-paw.gif" alt="Pet Logo" width="42" height="45" align="left" />
		<span class="logotxt1">daloRADIUS</span>
		<span class="logotxt2">user Sign-Up</span><br />
      		<span style="margin-left:15px;">daloRADIUS, driving smart hotspots to the limit</span></div>
      <div class="box">

        <h1>Sign-Up</h1>
	<p>

	<?php

		/*************************************************************************************************************************************************
		 *
		 * switch case for status of the sign-up process, whether it's the first time the user accesses it, or rather he already submitted
		 * the form with either successful or errornous result
		 *
		 *************************************************************************************************************************************************/     

		if ( (isset($errorMissingFields)) && ($errorMissingFields == true) ) {

		        printq('
					<font color="red"><b> Missing fields, please fill out all fields! </b></font>
		                <br/><br/>
		                ');
		}


		switch ($status) {
			case "firstload":

				echo "
					We allow our customers to sign-up for Internet access plans using their PayPal accounts.
					Complete the form and click the Apply button to register in our database, shortly after you will see
					a Buy Now button, click it to redirect to your PayPal homepage and confirm the transaction.<br/><br/>

					<form name='newuser' action='".$_SERVER['PHP_SELF']."' method='post'>

					Select your plan:
					<br/>
				        <select id='planId' name='planId'>
					";

			        include('library/opendb.php');

		                $sql = "SELECT planId,planName,planCost,planTax,planCurrency FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
					" WHERE planType='PayPal'";
		                $res = $dbSocket->query($sql);
			        while ($row = $res->fetchRow()) {
					echo "<option value=\"$row[0]\">$row[1] - Cost $row[2] $row[4] </option>";
			        }

			        include('library/closedb.php');

				echo "
				        </select>

					<br/><br/>
					<ul>

				        First name:
					    <li> <input name='firstName' value='"; if (isset($firstName)) echo $firstName; echo "' /> </li>
				        Last name:
					    <li> <input name='lastName' value='"; if (isset($lastName)) echo $lastName; echo "' /> </li>
				        Address:
					    <li> <input name='address' value='"; if (isset($address)) echo $address; echo "' /> </li>
				        City:
					    <li> <input name='city' value='"; if (isset($city)) echo $city; echo "' /> </li>
				        State:
					    <li> <input name='state' value='"; if (isset($state)) echo $state; echo "' /> </li>

					<br/>
					    <input type='submit' value='Submit' name='submit'>

					</ul>
				        </form>
					";

				break;


			case "paypal":
			        printq('

					<font color="blue">Thank you...</font>
			                <br/>

					Your PIN code has been created but it will only be activated after you complete and confirm
					your payment through PayPal. Following is your PIN code, which you will need in-order to access
					our Hotspot services.<br/><br/>

					<ul><li>PIN Code: <b> 
		                        ');
			
			        echo $userPIN;
	
			        echo'
					</b></li></ul>
					<br/>
					It is recommended that you will write it down now in-case of a failure.<br/><br/>
			
                                        <form action="'.$configValues['CONFIG_MERCHANT_WEB_PAYMENT'].'" method="post">
                                                <input type="hidden" name="cmd" value="_xclick" />
                                                <input type="hidden" name="business" value="'.$configValues['CONFIG_MERCHANT_BUSINESS_ID'].'" />

                                                <input type="hidden" name="return" value="'.$configValues['CONFIG_MERCHANT_IPN_URL_ROOT'].'/'.
																								$configValues['CONFIG_MERCHANT_IPN_URL_RELATIVE_SUCCESS'].
																								'?txnId='.$txnId.'" />
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
                                                <input type="hidden" id="currency_code" "name="currency_code" value="'; if (isset($planCurrency)) echo $planCurrency; echo '">
                                                <input type="hidden" name="lc" value="US">

                                                <input type="hidden" name="on0" value="Transaction ID" />
                                                <input type="hidden" name="os0" value="'.$txnId.'" />

                                                <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but23.gif" border="0" name="submit"
                                                alt="Make payments with PayPal - its fast, free and secure!">
                                        </form>
		                ';

				break;

		}


	?>



	</p>
      </div>

    </div>
    <div id="right-column">
      <div id="main-image"><img src="images/lady.jpg" alt="I love Pets" width="153" height="222" /></div>
      <div class="sidebar">

        <h3>About daloRADIUS</h3>
	<p>
		daloRADIUS is an advanced RADIUS web management application aimed at managing hotspots and
		general-purpose ISP deployments. It features user management, graphical reporting, accounting,
		a billing engine and integrates with GoogleMaps for geo-locating.		
	</p>
        <h3>Resources</h3>
        <div class="box">
          <ul>
            <li><a href="http://www.daloradius.com" target="_blank">daloRADIUS Official homepage</a></li>
            <li><a href="http://daloradius.wiki.sourceforge.net/" target="_blank">daloRADIUS Wiki</a></li>
          </ul>
        </div><a href="http://www.web-designers-directory.org/"></a><a href="http://www.medicine-pet.com/"></a>
      </div>
    </div>
  </div>
  <div id="footer">Copyright &copy; 2008 Liran Tal and daloRADIUS Project of Enginx.com, All rights reserved.<br />
    <a href="http://validator.w3.org/check?uri=referer" target="_blank">XHTML</a>  |  <a href="http://jigsaw.w3.org/css-validator/check/referer?warning=no&amp;profile=css2" target="_blank">CSS</a>  - Thanks to: <a href="http://www.medicine-pet.com/" target="_blank">Pet Medicine</a> | <span class="crd"><a href="http://www.web-designers-directory.org/">Web site Design</a></span> by : <a href="http://www.web-designers-directory.org/" target="_blank">WDD</a></div>
</div>

</body>
</html>
