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
 * Authors:    Liran Tal <liran@enginx.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    // prevent this file to be directly accessed
    if (strpos($_SERVER['PHP_SELF'], '/notifications/processNotificationUserDetailsInvoice.php') !== false) {
        header("Location: ../index.php");
        exit;
    }
    
    // common notification functions
    include("common.php");
    
    $base = dirname(__FILE__);
	$base_path = "$base/templates/";
    
	/**
	 * createUserDetailsInvoiceNotification()
	 * wrapper-function to create the notification to the customer
	 * 
	 * @param		array			customer information array
	 */
	function createUserDetailsInvoiceNotification($customerInfo) {
		global $base_path;
        
        $html = prepareNotificationTemplate($customerInfo);
		return createPDF($html, $base_path);
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
        $sendTo = $customerInfo['business_email'];
        $subject = "User Invoice Notification";
        $body = "Notification letter of service";
        $attachmentName = "invoice.pdf";
        
        return send_notification_via_email($pdfDocument, $smtpInfo, $sendTo, $from, $subject, $body, $attachmentName);
	}
	
	
	/**
	 * prepareNotificationTemplate()
	 * reads the notification (html) template and returns the formatted (html) notification
	 * 
	 * @param		array			Array of notification information
	 * @return		string			HTML notification
	 */
	function prepareNotificationTemplate($customerInfo) {
		global $base_path;
		
		// the HTML template
		$notification_template = $base_path . "/user_invoice_details.html";
		$notification_html_template = file_get_contents($notification_template);
	
		$date = date("Y-m-d");
	
		$business_name = $customerInfo['business_name']; 
		$business_address = $customerInfo['business_address'];
		$business_phone = $customerInfo['business_phone'];
		$business_email = $customerInfo['business_email'];
		$service_plan_info = $customerInfo['service_plan_info'];
	
		$notification_html_template = str_replace("####__INVOICE_CREATION_DATE__####", $date, $notification_html_template);
		
		$notification_html_template = str_replace("####__BUSINESS_NAME__####", $business_name, $notification_html_template);
		$notification_html_template = str_replace("####__BUSINESS_ADDRESS__####", $business_address, $notification_html_template);
		$notification_html_template = str_replace("####__BUSINESS_PHONE__####", $business_phone, $notification_html_template);
		$notification_html_template = str_replace("####__BUSINESS_EMAIL__####", $business_email, $notification_html_template);
		$notification_html_template = str_replace("####__SERVICE_PLAN_INFO__####", $service_plan_info, $notification_html_template);
		
		return $notification_html_template;
	}

?>
