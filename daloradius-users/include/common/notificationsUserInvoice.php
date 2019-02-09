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

	require_once(dirname(__FILE__)."/../../library/checklogin.php");
	require_once(dirname(__FILE__)."/../../notifications/processNotificationUserInvoice.php");
	require_once(dirname(__FILE__)."/../../library/config_read.php");
	
	isset($_GET['invoice_id']) ? $invoice_id = $_GET['invoice_id'] : $invoice_id = "";
	isset($_GET['destination']) ? $destination = $_GET['destination'] : $destination = "download";
	
	$login = $_SESSION['login_user'];
	$username = $login;
	
	if (!$username)
		return false;
		
	if ($invoice_id != "") {
		$customerInfo = @getInvoiceDetails($invoice_id, $username);
		
		$pdfDocument = @createNotification($customerInfo);
		
		if ($destination == "download") {
			header("Content-type: application/pdf");
			header("Content-Disposition: attachment; filename=notification_user_invoice_" . date("Ymd") . ".pdf; size=" . strlen($pdfDocument));
			print $pdfDocument;
		}
		
	}
	
	
	function getInvoiceDetails($invoice_id = NULL, $username) {
		
		require(dirname(__FILE__)."/../../library/opendb.php");
		require_once(dirname(__FILE__)."/../../lang/main.php");
		
		global $configValues;
		

		$sql = "SELECT id, contactperson, city, state, username FROM ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
		" WHERE username = '".$dbSocket->escapeSimple($username)."'";
		$res = $dbSocket->query($sql);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$user_id = $row['id'];
		
		if (!$user_id)
			return false;
		
		if ($invoice_id == NULL || empty($invoice_id))
			exit;
			

		$tableTags = "width='580px' ";
		$tableTrTags = "bgcolor='#ECE5B6'";
		
		
		// get invoice details
		$sql = "SELECT a.id, a.date, a.status_id, a.type_id, a.user_id, a.notes, b.contactperson, b.username, ".
				" b.city, b.state, b.address, b.email, b.emailinvoice, b.phone, f.value as type, ".
				" c.value AS status, COALESCE(e2.totalpayed, 0) as totalpayed, COALESCE(d2.totalbilled, 0) as totalbilled ".
				" FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE']." AS a".
				" INNER JOIN ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO']." AS b ON (a.user_id = b.id) ".
				" INNER JOIN ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS']." AS c ON (a.status_id = c.id) ".
				" INNER JOIN ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICETYPE']." AS f ON (a.type_id = f.id) ".
				" LEFT JOIN (SELECT SUM(d.amount + d.tax_amount) ".
					" as totalbilled, invoice_id, amount, tax_amount, notes, plan_id FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS']." AS d ".
					" GROUP BY d.invoice_id) AS d2 ON (d2.invoice_id = a.id) ".
				" LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']." AS bp2 ON (bp2.id = d2.plan_id) ".
				" LEFT JOIN (SELECT SUM(e.amount) as totalpayed, invoice_id FROM ". 
				$configValues['CONFIG_DB_TBL_DALOPAYMENTS']." AS e GROUP BY e.invoice_id) AS e2 ON (e2.invoice_id = a.id) ".
				" WHERE a.id = '".$dbSocket->escapeSimple($invoice_id)."'".
				" AND a.user_id = ".$dbSocket->escapeSimple($user_id).
				" GROUP BY a.id ";
		$res = $dbSocket->query($sql);	
		$invoiceDetails = $res->fetchRow(DB_FETCHMODE_ASSOC);

		if (!$invoiceDetails)
			return false;

			
		if (empty($invoiceDetails['email']))
			$customer_email = $invoiceDetails['emailinvoice'];
		else
			$customer_email = $invoiceDetails['email'];
			
		// populate user contact informatin
		$customerInfo['customer_name'] = $invoiceDetails['contactperson'];
		$customerInfo['customer_address'] = $invoiceDetails['address']. " " . $invoiceDetails['city']. " " . $invoiceDetails['state'];
		$customerInfo['customer_email'] = $customer_email;
		$customerInfo['customer_phone'] = $invoiceDetails['phone'];
		
		// populate user invoice details
		$balance = (float) ($invoiceDetails['totalpayed'] - $invoiceDetails['totalbilled']);
		$invoice_details = "";
		$invoice_details .= "".
		"<b>".t('all','ClientName')."</b>: ".$invoiceDetails['contactperson']."<br/>".
		"<b>".t('all','Invoice')."</b>: ".$invoice_id."<br/>".
		"<b>".t('all','Date')."</b>: ".$invoiceDetails['date']."<br/>".
		"<b>".t('all','TotalBilled')."</b>: ".$invoiceDetails['totalbilled']."<br/>".
		"<b>".t('all','TotalPayed')."</b>: ".$invoiceDetails['totalpayed']."<br/>".
		"<b>".t('all','Balance')."</b>: ".$balance."<br/>".
		"<b>".t('all','Status')."</b>: ".$invoiceDetails['status']."<br/>".
		"<b>".t('ContactInfo','Notes')."</b>: ".$invoiceDetails['notes']."<br/><br/><br/>";
		
		$customerInfo['invoice_details'] = $invoice_details;
		
		// populate user invoice items
		$invoice_items = "";
		$invoice_items .= "<table $tableTags><tr $tableTrTags>
			<th>Plan</th>
			<th>Item Amount</th>
			<th>Item Tax</th>
			<th>Notes</th>
			</tr>
			";

		// get all invoice items
		$sql = 'SELECT a.id, a.plan_id, a.amount, a.tax_amount, a.notes, b.planName '.
				' FROM '.$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'].' a '.
				' LEFT JOIN '.$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].' b ON a.plan_id = b.id '.
				' WHERE a.invoice_id = '.$invoice_id.' ORDER BY a.id ASC';
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";
		
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {

			$invoice_items .= "". 
				"<tr>".
					"<td>".$row['planName']."</td>".
					"<td>".$row['amount']."</td>".
					"<td>".$row['tax_amount']."</td>".
					"<td>".$row['notes']."</td>".
				"</tr>";
			
		}

		$invoice_items .= "</table>";
		
		$customerInfo['invoice_items'] = $invoice_items;
		
		require(dirname(__FILE__)."/../../library/closedb.php");
		
		
		return $customerInfo;
		
		
	}
	
?>
