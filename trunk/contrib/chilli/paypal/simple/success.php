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


	$successMsg = "Dear customer, we thank you for completing your Paypal payment. <br/>".
			"It takes a couple of seconds until Paypal performs payment validation with our systems <br/>".
			"which upon successful validation we will <b>enable</b> your account and provide you with access.<br/><br/>".
			"Please be patient, this web page will refresh automatically every 5 seconds to check for payment completion";
			
	$refresh = true;

	if (isset($_GET['txnId'])) {
		// txnId variable is set, let's check it against the database

		include('library/opendb.php');

		$txnId = $_GET['txnId'];

		$sql = "SELECT txnId, username, payment_status FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPAYPAL'].
			" WHERE txnId='".$dbSocket->escapeSimple($txnId)."'";
		$res = $dbSocket->query($sql);

		$row = $res->fetchRow();

		if ( ($row[0] == $txnId) && ($row[2] == "Completed") ) {
			$successMsg = "We have succesfully validated your payment.<br/>".
					"Your txnId is: <b>$row[0]</b> <br/>".
					"Your user PIN is: <b>$row[1]</b> <br/>".
					"Please enter it at the login page to start your surfing";
			$refresh = false;
		}

		include('library/closedb.php');

	}

?> 


<html>
<head>
<?php
	if ($refresh == true)
		echo '<meta http-equiv="refresh" content="5">';
?>
</head>
<body>

<br/>
Thanks for paying!
<br/><br/>


<?php
	echo $successMsg;
?>





</body>
</html>
