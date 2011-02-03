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
	//include the dompdf class
	require_once("dompdf/dompdf_config.inc.php");

	//include the Pear Mail classes for sending out emails
	@require_once('Mail.php');
	@require_once('Mail/mime.php');
	
	$base = dirname(__FILE__);
	
	/**
	 * createBatchDetailsNotification()
	 * wrapper-function to create the notification to the customer
	 * 
	 * @param		array			customer information array
	 */
	function createBatchDetailsNotification($customerInfo) {

		global $base;
		
		$html = prepareNotificationTemplate($customerInfo);
		$pdfDocument = createPDF($html);
		
		return $pdfDocument;
		
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
		
		if (empty($customerInfo['business_email']))
			return;
		
		$headers = array(	"From"	=>	$from, 
							"Subject"	=>	"Business Pre-Paid Batch Information",
							"Reply-To"=> $from
					);
				
		$mime = new Mail_mime();
		$mime->setTXTBody("Notification letter of service"); 
		$mime->addAttachment($pdfDocument, "application/pdf", "notification.pdf", false, 'base64');
		$body = $mime->get();
		$headers = $mime->headers($headers);
		$mail =& Mail::factory("smtp", $smtpInfo);
		$mail->send($customerInfo['business_email'], $headers, $body);
	
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
		
		// the HTML template
		$notification_template = "$base/templates/batch_details.html";
		$notification_html_template = file_get_contents($notification_template);
	
		$date = date("Y-m-d");
		
		$business_name = $customerInfo['business_name'];
		$business_owner_name = $customerInfo['business_owner_name']; 
		$business_address = $customerInfo['business_address'];
		$business_phone = $customerInfo['business_phone'];
		$business_email = $customerInfo['business_email'];
		$business_web = $customerInfo['business_web'];
		$batch_details = $customerInfo['batch_details'];
		$batch_active_users = $customerInfo['batch_active_users'];
		$service_plan_info = $customerInfo['service_plan_info'];
	
		$notification_html_template = str_replace("####__INVOICE_CREATION_DATE__####", $date, $notification_html_template);
		
		$notification_html_template = str_replace("####__BUSINESS_NAME__####", $business_name, $notification_html_template);
		$notification_html_template = str_replace("####__BUSINESS_OWNER_NAME__####", $business_owner_name, $notification_html_template);
		$notification_html_template = str_replace("####__BUSINESS_ADDRESS__####", $business_address, $notification_html_template);
		$notification_html_template = str_replace("####__BUSINESS_PHONE__####", $business_phone, $notification_html_template);
		$notification_html_template = str_replace("####__BUSINESS_EMAIL__####", $business_email, $notification_html_template);
		$notification_html_template = str_replace("####__BUSINESS_WEB__####", $business_web, $notification_html_template);
		$notification_html_template = str_replace("####__BATCH_DETAILS__####", $batch_details, $notification_html_template);
		$notification_html_template = str_replace("####__BATCH_ACTIVE_USERS__####", $batch_active_users, $notification_html_template);
		$notification_html_template = str_replace("####__SERVICE_PLAN_INFO__####", $service_plan_info, $notification_html_template);
		

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
