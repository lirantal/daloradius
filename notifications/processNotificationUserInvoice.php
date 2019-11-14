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
 *          Miguel García <miguelvisgarcia@gmail.com>
 *
 *********************************************************************************************************
 */
	//include the dompdf class
	require_once("dompdf/dompdf_config.inc.php");

	//include the Pear Mail classes for sending out emails
	@require_once('Mail.php');
	@require_once('Mail/mime.php');
	
	$base = dirname(__FILE__);
	
	/**
	 * createNotification()
	 * wrapper-function to create the notification to the customer
	 * 
	 * @param		array			customer information array
	 * @param		boolean			true for HTML output, false for PDF output
	 * @return 		string			returns the HTML or PDF output
	 */
	function createNotification($customerInfo, $asHTML = false) {

		global $base;
		
		$html = prepareNotificationTemplate($customerInfo);
		
		if($asHTML) {
			$document = $html;
		}
		else {
			$document = createPDF($html);
		}

		return $document;
	}

	
	/**
	 * emailNotification()
	 * creates an email message with the pdf and sends it
	 * 
	 * @param		string			pdf binary/string stream
	 * @param		array			customer information array
	 * @param		array			smtp server information
	 * @param		string			from email address of the sender identity
	 */
	function emailNotification($pdfDocument, $customerInfo, $smtpInfo, $from) {

		global $base;
		
		if (empty($customerInfo['customer_email']))
			return;
		
		$headers = array (
			"From" => $from, 
			"Subject" => "Invoice Information",
			"Reply-To" => $from
		);
				
		$mime = new Mail_mime();
		$mime->setTXTBody("Notification letter of service"); 
		$mime->addAttachment($pdfDocument, "application/pdf", "notification.pdf", false, 'base64');
		$body = $mime->get();
		$headers = $mime->headers($headers);
		$mail =& Mail::factory("smtp", $smtpInfo);
		$mail->send($customerInfo['customer_email'], $headers, $body);
	}
	
	
	/**
	 * prepareNotificationTemplate()
	 * reads the notification (html) template and returns the formatted (html) notification
	 * 
	 * @param		array			Array of notification information
	 * @return		string			HTML notification
	 */
	function prepareNotificationTemplate($customerInfo) {
	
		global $base;
		global $configValues;
		
		// the default HTML template - OLD STYLE
		$notification_template = "$base/templates/user_invoice.html";
		$notification_item_template = null;
		
		// the default HTML template - NEW STYLE
		if(isset($configValues['CONFIG_INVOICE_TEMPLATE'])) {
			$notification_template = "$base/templates/" . $configValues['CONFIG_INVOICE_TEMPLATE'];
			
			if(isset($configValues['CONFIG_INVOICE_ITEM_TEMPLATE'])) {
				$notification_item_template = "$base/templates/" . $configValues['CONFIG_INVOICE_ITEM_TEMPLATE'];
			}
		}
		
		// the default HTML template for current location - NEW STYLE
		if((isset($_SESSION['location_name'])) && ($_SESSION['location_name'] != "default")) {
			if(isset($configValues['CONFIG_LOCATIONS'][$_SESSION['location_name']]['CONFIG_INVOICE_TEMPLATE'])) {
				$notification_template = "$base/templates/" . $configValues['CONFIG_LOCATIONS'][$_SESSION['location_name']]['CONFIG_INVOICE_TEMPLATE'];
				
				if(isset($configValues['CONFIG_LOCATIONS'][$_SESSION['location_name']]['CONFIG_INVOICE_ITEM_TEMPLATE'])) {
					$notification_item_template = "$base/templates/" . $configValues['CONFIG_LOCATIONS'][$_SESSION['location_name']]['CONFIG_INVOICE_ITEM_TEMPLATE'];
				}
			}
		}

		// load template for invoice
		$notification_html_template = file_get_contents($notification_template);
		
		// load template for each invoice item
		if($notification_item_template !== null) {
			$notification_item_html_template = file_get_contents($notification_item_template);
		}

		$date = date("Y-m-d");
		
		$name = $customerInfo['customer_name'];
		$address = $customerInfo['customer_address'];
		$phone = $customerInfo['customer_phone'];
		$email = $customerInfo['customer_email'];
		
		$invoice_details = $customerInfo['invoice_details'];
		$invoice_items = $customerInfo['invoice_items'];
	
		// notification date
		$notification_html_template = str_replace("####__INVOICE_CREATION_DATE__####", $date, $notification_html_template);
		
		// user details
		$notification_html_template = str_replace("####__CUSTOMER_NAME__####", $name, $notification_html_template);
		$notification_html_template = str_replace("####__CUSTOMER_ADDRESS__####", $address, $notification_html_template);
		$notification_html_template = str_replace("####__CUSTOMER_PHONE__####", $phone, $notification_html_template);
		$notification_html_template = str_replace("####__CUSTOMER_EMAIL__####", $email, $notification_html_template);
		
		// invoice information
		$notification_html_template = str_replace("####__INVOICE_DETAILS__####", $invoice_details, $notification_html_template);
		$notification_html_template = str_replace("####__INVOICE_ITEMS__####", $invoice_items, $notification_html_template);
		
		// customer details - NEW STYLE			
		$notification_html_template = str_replace("[CustomerId]", $customerInfo['customerId'], $notification_html_template);
		$notification_html_template = str_replace("[CustomerName]", $customerInfo['customerName'], $notification_html_template);
		$notification_html_template = str_replace("[CustomerAddress]", $customerInfo['customerAddress'], $notification_html_template);
		$notification_html_template = str_replace("[CustomerAddress2]", $customerInfo['customerAddress2'], $notification_html_template);
		$notification_html_template = str_replace("[CustomerPhone]", $customerInfo['customerPhone'], $notification_html_template);
		$notification_html_template = str_replace("[CustomerEmail]", $customerInfo['customerEmail'], $notification_html_template);
		$notification_html_template = str_replace("[CustomerContact]", $customerInfo['customerContact'], $notification_html_template);
		
		// invoice details - NEW STYLE
		$notification_html_template = str_replace("[InvoiceNumber]", $customerInfo['invoiceNumber'], $notification_html_template);
		$notification_html_template = str_replace("[InvoiceDate]", $customerInfo['invoiceDate'], $notification_html_template);
		$notification_html_template = str_replace("[InvoiceStatus]", $customerInfo['invoiceStatus'], $notification_html_template);
		$notification_html_template = str_replace("[InvoiceTotalBilled]", $customerInfo['invoiceTotalBilled'], $notification_html_template);
		$notification_html_template = str_replace("[InvoicePaid]", $customerInfo['invoicePaid'], $notification_html_template);
		$notification_html_template = str_replace("[InvoiceDue]", $customerInfo['invoiceDue'], $notification_html_template);
		$notification_html_template = str_replace("[InvoiceNotes]", $customerInfo['invoiceNotes'], $notification_html_template);
		
		// invoice items - NEW STYLE
		if($notification_item_template !== null) {
			$invoiceItems = '';
			
			foreach($customerInfo['invoiceItems'] as $invoiceItem) {
				$invoiceItemTemplate = $notification_item_html_template;
		
				$invoiceItemTemplate = str_replace("[InvoiceItemNumber]", $invoiceItem['invoiceItemNumber'], $invoiceItemTemplate);
				$invoiceItemTemplate = str_replace("[InvoiceItemPlan]", $invoiceItem['invoiceItemPlan'], $invoiceItemTemplate);
				$invoiceItemTemplate = str_replace("[InvoiceItemNotes]", $invoiceItem['invoiceItemNotes'], $invoiceItemTemplate);
				$invoiceItemTemplate = str_replace("[InvoiceItemAmount]", $invoiceItem['invoiceItemAmount'], $invoiceItemTemplate);
				$invoiceItemTemplate = str_replace("[InvoiceItemTaxAmount]", $invoiceItem['invoiceItemTaxAmount'], $invoiceItemTemplate);
				$invoiceItemTemplate = str_replace("[InvoiceItemTotalAmount]", $invoiceItem['invoiceItemTotalAmount'], $invoiceItemTemplate);
				
				$invoiceItems .= $invoiceItemTemplate;
			}

			$notification_html_template = str_replace("[InvoiceItems]", $invoiceItems, $notification_html_template);
		}
		
		// more invoice details - NEW STYLE
		$notification_html_template = str_replace("[InvoiceTotalAmount]", $customerInfo['invoiceTotalAmount'], $notification_html_template);
		$notification_html_template = str_replace("[InvoiceTotalTax]", $customerInfo['invoiceTotalTax'], $notification_html_template);
		
		return $notification_html_template;
	}
	
	/**
	 * createPDF()
	 * creates a PDF document for a given html file
	 * 
	 * @param 		string			the html file to convert to pdf			
	 * @return 		string			returns the pdf in binary/string stream
	 */
	function createPDF($html) {
	
		global $base;
		
		// instansiate the pdf document
		$dompdf = new DOMPDF();
		$dompdf->set_base_path("$base/templates/");
		$dompdf->load_html($html);
		$dompdf->render();
	
		$notification_pdf = $dompdf->output();
		
		return $notification_pdf;
	}

?>
