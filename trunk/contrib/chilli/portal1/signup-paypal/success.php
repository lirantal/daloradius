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

	include('library/config_read.php');

	$successMsg = $configValues['CONFIG_PAYPAL_SUCCESS_MSG_PRE'];
			
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
			$successMsg = "Your user PIN is: <b>$row[1]</b> <br/>".$configValues['CONFIG_PAYPAL_SUCCESS_MSG_POST'];
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

<?php
	echo $configValues['CONFIG_PAYPAL_SUCCESS_MSG_HEADER'];
	echo $successMsg;
?>





</body>
</html>
