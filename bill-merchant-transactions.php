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
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');
	
	//setting values for the order by and order type variables
	isset($_GET['orderBy']) ? $orderBy = $_GET['orderBy'] : $orderBy = "radacctid";
	isset($_GET['orderType']) ? $orderType = $_GET['orderType'] : $orderType = "asc";


	isset($_GET['payer_email']) ? $payer_email = $_GET['payer_email'] : $payer_email = "%";
	isset($_GET['payment_address_status']) ? $payment_address_status = $_GET['payment_address_status'] : $payment_address_status = "%";
	isset($_GET['payer_status']) ? $payer_status = $_GET['payer_status'] : $payer_status = "%";
	isset($_GET['payment_status']) ? $payment_status = $_GET['payment_status'] : $payment_status = "%";
	isset($_GET['vendor_type']) ? $vendor_type = $_GET['vendor_type'] : $vendor_type = "%";
	isset($_GET['sqlfields']) ? $sqlfields = $_GET['sqlfields'] : $sqlfields = "";
	isset($_GET['startdate']) ? $startdate = $_GET['startdate'] : $startdate = "";
	isset($_GET['enddate']) ? $enddate = $_GET['enddate'] : $enddate = "";


	$payer_email = str_replace('*', '%', $payer_email);

	//feed the sidebar variables
	$billing_date_startdate = $startdate;
	$billing_date_enddate = $enddate;
	//$billing_paypal_firstname = $value;
	$billing_paypal_payeremail = $payer_email;
	$billing_paypal_paymentaddressstatus = $payment_address_status;
	$billing_paypal_payerstatus = $payer_status;
	$billing_paypal_paymentstatus = $payment_status;
	$billing_paypal_vendor_type = $vendor_type;


	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for all accounting records on page: ";

?>

<?php
	
	include("menu-bill-merchant.php");
	
?>

		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','billpaypaltransactions.php')?>
		<h144>&#x2754;</h144></a></h2>
				
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','billpaypaltransactions') ?>
			<br/>
		</div>
		<br/>



<?php

		include 'library/opendb.php';
		include 'include/management/pages_common.php';	
		include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

		// let's sanitize the values passed to us:
		$payer_email = $dbSocket->escapeSimple($payer_email);
		$payment_address_status = $dbSocket->escapeSimple($payment_address_status);
		$payer_status = $dbSocket->escapeSimple($payer_status);
		$payment_status = $dbSocket->escapeSimple($payment_status);
		$vendor_type = $dbSocket->escapeSimple($vendor_type);
		$startdate = $dbSocket->escapeSimple($startdate);
		$enddate = $dbSocket->escapeSimple($enddate);

	        include_once('include/management/userBilling.php');
	        userBillingPayPalSummary($startdate, $enddate, $payer_email, $payment_address_status, $payer_status, $payment_status, $vendor_type, 1);
									                         // draw the billing rates summary table


	        include 'library/opendb.php';
		// since we need to span through pages, which we do using GET queries I can't rely on this page
		// to be processed through POST but rather using GET only (with the current design anyway).
		// For this reason, I need to build the GET query which I will later use in the page number's links

		$getFields = "";
		$counter = 0;
		foreach ($sqlfields as $elements) {
			$getFields .= "&sqlfields[$counter]=$elements";
			$counter++;
		}

		// we should also sanitize the array that we will be passing to this page in the next query
		$getFields = $dbSocket->escapeSimple($getFields);


		$getQuery = "";
		$getQuery .= "&payer_email=$payer_email";
		$getQuery .= "&payment_address_status=$payment_address_status";
		$getQuery .= "&payer_status=$payer_status";
		$getQuery .= "&payment_status=$payment_status";
		$getQuery .= "&vendor_type=$vendor_type";
		$getQuery .= "&startdate=$startdate&enddate=$enddate";


		$select = implode(",", $sqlfields);
		// sanitizing the array passed to us in the get request
		$select = $dbSocket->escapeSimple($select);


		$sql = "SELECT $select FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT']." WHERE ".
			" (payer_email LIKE '$payer_email') AND ".
			" (payment_address_status LIKE '$payment_address_status') AND ".
			" (payer_status LIKE '$payer_status') AND ".
			" (payment_status LIKE '$payment_status') AND ".
			" (vendor_type LIKE '$vendor_type') AND ".
			" (payment_date>'$startdate' AND payment_date<'$enddate')";
		$res = $dbSocket->query($sql);
		$numrows = $res->numRows();


		$sql = "SELECT $select FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT']." WHERE ".
			" (payer_email LIKE '$payer_email') AND ".
			" (payment_address_status LIKE '$payment_address_status') AND ".
			" (payer_status LIKE '$payer_status') AND ".
			" (payment_status LIKE '$payment_status') AND ".
			" (vendor_type LIKE '$vendor_type') AND ".
			" (payment_date>'$startdate' AND payment_date<'$enddate') ".
			" ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
		$res = $dbSocket->query($sql);
		$logDebugSQL = "";
		$logDebugSQL .= $sql . "\n";


	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	/* END */


	echo "<table border='0' class='table1'>\n";
	echo "
					<thead>
							<tr>
							<th colspan='25'>".t('all','Records')."</th>
							</tr>

                                                        <tr>
                                                        <th colspan='25' align='left'>
                <br/>
        ";

        if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
                setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $getFields, $getQuery);

        echo " </th></tr>
                                        </thead>

                        ";


	// building the dybamic table list fields
	echo "<thread> <tr>";
	foreach ($sqlfields as $value) {
		switch($value) {

		case "id":
			$title = t('all','ID');
			break;
		case "username":
			$title = t('all','Username');
			break;
		case "password":
			$title = t('all','Password');
			break;
		case "txnId":
			$title = t('all','TxnId');
			break;
		case "planId":
			$title = t('all','PlanId');
			break;
		case "quantity":
			$title = t('all','Quantity');
			break;
		case "business_email":
			$title = t('all','ReceiverEmail');
			break;
		case "business_id":
			$title = t('all','Business');
			break;
		case "payment_tax":
			$title = t('all','Tax');
			break;
		case "payment_cost":
			$title = t('all','Cost');
			break;
		case "payment_fee":
			$title = t('all','TransactionFee');
			break;
		case "payment_total":
			$title = t('all','TotalCost');
			break;
		case "payment_currency":
			$title = t('all','PaymentCurrency');
			break;
		case "first_name":
			$title = t('all','FirstName');
			break;
		case "last_name":
			$title = t('all','LastName');
			break;
		case "payer_email":
			$title = t('all','PayerEmail');
			break;
		case "payer_address_name":
			$title = t('all','AddressRecipient');
			break;
		case "payer_address_street":
			$title = t('all','Street');
			break;
		case "payer_address_country":
			$title = t('all','Country');
			break;
		case "payer_address_country_code":
			$title = t('all','CountryCode');
			break;
		case "payer_address_city":
			$title = t('all','City');
			break;
		case "payer_address_state":
			$title = t('all','State');
			break;
		case "payer_address_zip":
			$title = t('all','Zip');
			break;
		case "payment_date":
			$title = t('all','PaymentDate');
			break;
		case "payment_status":
			$title = t('all','PaymentStatus');
			break;
		case "payer_status":
			$title = t('all','PayerStatus');
			break;
		case "vendor_type":
			$title = t('all','VendorType');
			break;
		case "payment_address_status":
			$title = t('all','PaymentAddressStatus');
			break;
		default:
			$title = $value;
			break;
		}

		echo "<th scope='col'> $title   </th>";
	} //foreach $sqlfields
	echo "</tr> </thread>";


	// inserting the values of each field from the database to the table
	while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
		echo "<tr>";
		foreach ($sqlfields as $value) {
			echo "<td> " . $row[$value] . "</td>";
		}
		echo "</tr>";
	}

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='25' align='left'>
        ";
        setupLinks($pageNum, $maxPage, $orderBy, $orderType, $getFields, $getQuery);
        echo "
                                                        </th>
                                                        </tr>
                                        </tfoot>
                ";

	echo "</table>";

	include 'library/closedb.php';

?>



<?php
	include('include/config/logging.php');
?>

		</div>
		
		<div id="footer">
		
								<?php
        include 'page-footer.php';
?>

		
		</div>
		
</div>
</div>


</body>
</html>
