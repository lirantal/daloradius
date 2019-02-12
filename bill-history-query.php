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


	isset($_GET['username']) ? $username = trim($_GET['username']) : $username = "%";
	isset($_GET['billaction']) ? $billaction = trim($_GET['billaction']) : $billaction = "";
	isset($_GET['sqlfields']) ? $sqlfields = $_GET['sqlfields'] : $sqlfields = "";
	isset($_GET['startdate']) ? $startdate = $_GET['startdate'] : $startdate = "";
	isset($_GET['enddate']) ? $enddate = $_GET['enddate'] : $enddate = "";


	$username = str_replace('*', '%', $username);

	//feed the sidebar variables
	$billing_date_startdate = $startdate;
	$billing_date_enddate = $enddate;
	$billing_history_username = $username;
	$billing_history_billaction = $billaction;


	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for all accounting records on page: ";

?>

<?php
	include("menu-bill-history.php");
?>

		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','billhistoryquery.php')?>
		<h144>&#x2754;</h144></a></h2>
				
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','billhistoryquery') ?>
			<br/>
		</div>
		<br/>



<?php

		include 'library/opendb.php';
		include 'include/management/pages_common.php';	
		include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

		// let's sanitize the values passed to us:
		$username = $dbSocket->escapeSimple($username);
		$billaction = $dbSocket->escapeSimple($billaction);
		$startdate = $dbSocket->escapeSimple($startdate);
		$enddate = $dbSocket->escapeSimple($enddate);

//	        include_once('include/management/userBilling.php');
//	        userBillingPayPalSummary($startdate, $enddate, $payer_email, $payment_address_status, $payer_status, $payment_status, 1);
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
		$getQuery .= "&username=$username";
		$getQuery .= "&billAction=$billaction";
		$getQuery .= "&startdate=$startdate&enddate=$enddate";


		$select = implode(",", $sqlfields);
		// sanitizing the array passed to us in the get request
		$select = $dbSocket->escapeSimple($select);


		$sql = "SELECT $select FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY']." WHERE ".
			" (username LIKE '$username') AND ".
			" (billAction LIKE '$billaction') ";
		$res = $dbSocket->query($sql);
		$numrows = $res->numRows();


		$sql = "SELECT $select FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY']." WHERE ".
			" (username LIKE '$username') AND ".
			" (billAction LIKE '$billaction') ".
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
		case "planId":
			$title = t('all','PlanId');
			break;
		case "billAmount":
			$title = t('all','BillAmount');
			break;
		case "billAction":
			$title = t('all','BillAction');
			break;
		case "billPerformer":
			$title = t('all','BillPerformer');
			break;
		case "billReason":
			$title = t('all','BillReason');
			break;
		case "paymentmethod":
			$title = t('ContactInfo','PaymentMethod');
			break;
		case "cash":
			$title = t('ContactInfo','Cash');
			break;
		case "creditcardname":
			$title = t('ContactInfo','CreditCardName');
			break;
		case "creditcardnumber":
			$title = t('ContactInfo','CreditCardNumber');
			break;
		case "creditcardverification":
			$title = t('ContactInfo','CreditCardVerificationNumber');
			break;
		case "creditcardtype":
			$title = t('ContactInfo','CreditCardType');
			break;
		case "creditcardexp":
			$title = t('ContactInfo','CreditCardExpiration');
			break;
		case "coupon":
			$title = t('all','Coupon');
			break;
		case "discount":
			$title = t('all','Discount');
			break;
		case "notes":
			$title = t('ContactInfo','Notes');
			break;
		case "creationdate":
			$title = t('all','CreationDate');
			break;
		case "creationby":
			$title = t('all','CreationBy');
			break;
		case "updatedate":
			$title = t('all','UpdateDate');
			break;
		case "updateby":
			$title = t('all','UpdateBy');
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
