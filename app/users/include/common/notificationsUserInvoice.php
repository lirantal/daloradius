<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
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
 * Authors:	Liran Tal <liran@lirantal.com>
 *
 *********************************************************************************************************
 */

	require_once(dirname(__FILE__)."/../../library/checklogin.php");
    require_once dirname(__DIR__, 3) . '/common/includes/config_read.php';
    require_once dirname(__DIR__, 2) . '/library/portal_pages_pdo.php';
    $username = $_SESSION['login_user'] ?? null;
    try {
        dalo_portal_username($username);
        $invoice_id = dalo_portal_id($_GET['invoice_id'] ?? '');
        $destination = $_GET['destination'] ?? 'download';
        if (!is_string($destination) || $destination !== 'download') {
            throw new InvalidArgumentException('Invalid invoice destination');
        }
    } catch (InvalidArgumentException $exception) {
        http_response_code(400); exit('Invalid invoice request');
    }
    try {
        $customerInfo = getInvoiceDetails($invoice_id, $username);
        if ($customerInfo === false) { http_response_code(404); exit('Invoice not found'); }
        // Loading the PDF processor is deferred until authorization and all SQL reads succeed.
        require_once dirname(__DIR__, 2) . '/notifications/processNotificationUserInvoice.php';
        $pdfDocument = createNotification($customerInfo);
    } catch (Throwable $exception) {
        http_response_code(503); exit('Invoice unavailable');
    }
    header('Content-type: application/pdf');
    header('Content-Disposition: attachment; filename=notification_user_invoice_' . date('Ymd') .
        '.pdf; size=' . strlen($pdfDocument));
    print $pdfDocument;

	function getInvoiceDetails($invoice_id, $username) {
		
        global $configValues, $logDebugSQL;
        require_once dirname(__DIR__, 2) . '/lang/main.php';
        $pdo = null;
        try {
            $pdo = dalo_portal_handle($configValues);
            $invoice = dalo_portal_invoice($pdo, $configValues, $username, $invoice_id);
        } finally { $pdo = null; }
        $invoiceDetails = $invoice['header'];
        if (!$invoiceDetails) { return false; }
        $tableTags = "width='580px' ";
        $tableTrTags = "bgcolor='#ECE5B6'";

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

		foreach ($invoice['items'] as $row) {

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
		

		
		
		return $customerInfo;
		
		
	}
	
?>
