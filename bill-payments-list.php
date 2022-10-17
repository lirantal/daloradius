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
 * 			Filippo Maria Del Prete <filippo.delprete@gmail.com>
 *
 *********************************************************************************************************
 */
 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "id";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "desc";

	isset($_GET['invoice_id']) ? $invoice_id = $_GET['invoice_id'] : $invoice_id = "";
	isset($_GET['user_id']) ? $user_id = $_GET['user_id'] : $user_id = "";
	isset($_GET['username']) ? $username = $_GET['username'] : $username = "";

	
	$edit_username = $username;
	$edit_invoice_id = $invoice_id;

	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" href="css/form-field-tooltip.css" type="text/css" media="screen,projection" />
</head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script src="library/javascript/rounded-corners.js" type="text/javascript"></script>
<script src="library/javascript/form-field-tooltip.js" type="text/javascript"></script>
<?php

	include ("menu-bill-payments.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','paymentslist.php') ?>
				<h144>&#x2754;</h144></a></h2>
				
				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo t('helpPage','paymentslist') ?>
					<br/>
				</div>
				<br/>


<?php

        
	include 'library/opendb.php';
	include 'include/management/pages_common.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

	$sql_WHERE = ' WHERE ';
	$sql_JOIN = '';
	// if invoice_id then we need to lookup specific invoices
	if (!empty($invoice_id)) {
		$sql_WHERE .= ' invoice_id = \''.$dbSocket->escapeSimple($invoice_id).'\'';
		$sql_WHERE .= ' AND ';
	}
	
	// if provided username, we'll need to turn that into the userbillinfo user id
	if (!empty($username)) {
		$username = $dbSocket->escapeSimple($username);
		$sql = 'SELECT id FROM '.$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
				' WHERE username="'.$username.'"';
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";
		
		$row = $res->fetchRow();
		$user_id = $row[0];	
	}
	
	// if we did get a user id let's make the sql query specific to payments by this user 
	if ($user_id && !empty($user_id)) {
		$sql_JOIN .= ' JOIN '.$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'].' ON '.
						$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'].'.id = '.$configValues['CONFIG_DB_TBL_DALOPAYMENTS'].'.invoice_id';
		$sql_WHERE .= $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'].'.user_id = '.$dbSocket->escapeSimple($user_id);
		$sql_WHERE .= ' AND ';
	}
		
	$sql_WHERE .= ' 1=1 ';
	
	//orig: used as method to get total rows - this is required for the pages_numbering.php page
    $sql = "SELECT ".$configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".id, ".
			$configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".invoice_id, ".
            $configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".amount, ".
			$configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".date, ".
            $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'].".value, ".
            $configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".notes ".
            " FROM ".$configValues['CONFIG_DB_TBL_DALOPAYMENTS'].
            $sql_JOIN.
            " LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'].
            " ON ".$configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".type_id=".$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'].".id ".
			$sql_WHERE;
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();

    $sql = "SELECT ".$configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".id, ".
			$configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".invoice_id, ".
			$configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".amount, ".
			$configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".date, ".
    	    $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'].".value, ".
    	    $configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".notes ".
    	    " FROM ".$configValues['CONFIG_DB_TBL_DALOPAYMENTS'].
    	    $sql_JOIN.
  	      	" LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'].
			" ON ".$configValues['CONFIG_DB_TBL_DALOPAYMENTS'].".type_id=".
			$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'].".id ".
			$sql_WHERE.
			" ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";
	
	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	/* END */

    
	echo "<form name='listallpayments' method='post' action='bill-payments-del.php'>";

	echo "<table border='0' class='table1'>\n";
	echo "
					<thead>
                                                        <tr>
                                                        <th colspan='12' align='left'>
                                Select:
                                <a class=\"table\" href=\"javascript:SetChecked(1,'payment_id[]','listallpayments_id')\">All</a> 
                                
                                <a class=\"table\" href=\"javascript:SetChecked(0,'payment_id[]','listallpayments_id')\">None</a>
	                 <br/>
                                <input class='button' type='button' value='Delete' onClick='javascript:removeCheckbox(\"listallpayments_id\",\"bill-payments-del.php\")' />
                                <br/><br/>

        ";

        if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
                setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);

        echo " </th></tr>
                                        </thead>

                        ";

        if ($orderType == "asc") {
                $orderTypeNextPage = "desc";
        } else  if ($orderType == "desc") {
                $orderTypeNextPage = "asc";
        }

	echo "<thread> <tr>
		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=id&orderType=$orderTypeNextPage\">
		".t('all','ID')."</a>
		</th>

		<th scope='col'> 
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=invoice_id&orderType=$orderTypeNextPage\">
		".t('all','PaymentInvoiceID')."</a>
		</th>

		<th scope='col'> 
		".t('all','PaymentAmount')."
		</th>

		<th scope='col'> 
		".t('all','PaymentDate')."
		</th>

		<th scope='col'> 
		".t('all','PaymentType')."
		</th>

		<th scope='col'> 
		".t('all','PaymentNotes')."
		</th>


	</tr> </thread>";
	while($row = $res->fetchRow()) {
		printqn("<tr>
                        <td> <input type='checkbox' name='payment_id[]' value='$row[0]'> 

                        	<a class='tablenovisit' href='#'
								onclick='javascript:return false;'
                                tooltipText=\"
                                        <a class='toolTip' href='bill-payments-edit.php?payment_id=$row[0]'>".t('Tooltip','EditPayment')."</a>
					<br/><br/>
                                        <a class='toolTip' href='bill-payments-del.php?payment_id=$row[0]'>".t('Tooltip','RemovePayment')."</a>
                                        <br/><br/>\"
                              >#$row[0]</a>
                        </td>
                        
                        
                        <td> <a class='tablenovisit' href='#'
								onclick='javascript:return false;'
                                tooltipText=\"
                                        <a class='toolTip' href='bill-invoice-edit.php?invoice_id=$row[1]'>".t('Tooltip','InvoiceEdit')."</a>
                                        <br/><br/>\"
                              >#$row[1]</a>
                        </td>
                       
                                <td class='money'> $row[2] </td>
                                <td> $row[3] </td>
                                <td> $row[4] </td>
                                <td> $row[5] </td>
		</tr>");
	}

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='12' align='left'>
        ";
        setupLinks($pageNum, $maxPage, $orderBy, $orderType);
        echo "
                                                        </th>
                                                        </tr>
                                        </tfoot>
                ";


	echo "</table>";
        echo "</form>";

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

<script type="text/javascript">
var tooltipObj = new DHTMLgoodies_formTooltip();
tooltipObj.setTooltipPosition('right');
tooltipObj.setPageBgColor('#EEEEEE');
tooltipObj.setTooltipCornerSize(15);
tooltipObj.initFormFieldTooltip();
</script>

</body>
</html>
