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
    if (strpos($_SERVER['PHP_SELF'], '/notifications/processNotificationWelcome.php') !== false) {
        header("Location: ../index.php");
        exit;
    }
    
    // common notification functions
    include("common.php");
    
    $base = dirname(__FILE__);
    $base_path = "$base/templates/";
    
    /**
     * sendWelcomeNotification()
     * wrapper-function to send notification to the customer
     * 
     * @param        array         customer information array
     * @param        array         smtp server information
     * @param        string        from email address of the sender identity
     */
    function sendWelcomeNotification($customerInfo, $smtpInfo, $from) {
        $sendTo = $customerInfo['business_email'];
        $subject = "Welcome new customer!";
        $body = "Notification letter of service";
        $attachmentName = "notification.pdf";
        
        return send_notification_via_email($pdfDocument, $smtpInfo, $sendTo, $from, $subject, $body, $attachmentName);
    }

    
    /**
     * prepareNotificationTemplate()
     * reads the notification (html) template and returns the formatted (html) notification
     * 
     * @param         array         Array of notification information
     * @return        string        HTML notification
     */
    function prepareNotificationTemplate($customerInfo) {
        global $base_path;
        
        // the HTML template
        $notification_template = $base_path . "welcome.html";
        $notification_html_template = file_get_contents($notification_template);
    
        $date = date("Y-m-d");
    
        $customer_name = $customerInfo['customer_name'];
        $customer_address = $customerInfo['customer_address']; 
        $customer_phone = $customerInfo['customer_phone'];
        $customer_email = $customerInfo['customer_email'];
    
        $notification_html_template = str_replace("####__INVOICE_CREATION_DATE__####", $date, $notification_html_template);
        $notification_html_template = str_replace("####__CUSTOMER_NAME__####", $customer_name, $notification_html_template);
        $notification_html_template = str_replace("####__CUSTOMER_ADDRESS__####", $customer_address, $notification_html_template);
        $notification_html_template = str_replace("####__CUSTOMER_PHONE__####", $customer_phone, $notification_html_template);
        $notification_html_template = str_replace("####__CUSTOMER_EMAIL__####", $customer_email, $notification_html_template);

        return $notification_html_template;
    }

?>
