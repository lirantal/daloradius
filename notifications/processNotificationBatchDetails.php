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
    if (strpos($_SERVER['PHP_SELF'], '/notifications/processNotificationBatchDetails.php') !== false) {
        header("Location: ../index.php");
        exit;
    }

    // common notification functions
    include("common.php");
    
    $base = dirname(__FILE__);
    $base_path = "$base/templates/";
    
    /**
     * createBatchDetailsNotification()
     * wrapper-function to create the notification to the customer
     * 
     * @param        array        customer information array
     */
    function createBatchDetailsNotification($customerInfo) {
        global $base_path;
        
        $html = prepareNotificationTemplate($customerInfo);
        return createPDF($html, $base_path);
    }

    
    /**
     * emailNotification()
     * creates an email message with the pdf and sends it
     * 
     * @param        string        pdf binary/string stream
     * @param        array         customer information array
     * @param        array         smtp server information
     * @param        string        from email address of the sender identity
     */
    function emailNotification($pdfDocument, $customerInfo, $smtpInfo, $from) {
        $sendTo = $customerInfo['business_email'];
        $subject = "Business Pre-Paid Batch Information";
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
        $notification_template = $base_path . "/batch_details.html";
        
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
    
        $tagname_value_mappings = array(
                                            "__INVOICE_CREATION_DATE__" => $date,
                                            "__BUSINESS_NAME__" => $business_name,
                                            "__BUSINESS_OWNER_NAME__" => $business_owner_name,
                                            "__BUSINESS_ADDRESS__" => $business_address,
                                            "__BUSINESS_PHONE__" => $business_phone,
                                            "__BUSINESS_EMAIL__" => $business_email,
                                            "__BUSINESS_WEB__" => $business_web,
                                            "__BATCH_DETAILS__" => $batch_details,
                                            "__BATCH_ACTIVE_USERS__" => $batch_active_users,
                                            "__SERVICE_PLAN_INFO__" => $service_plan_info,
                                       );
    
        foreach ($tagname_value_mappings as $tagname => $value) {
            $tag = sprintf("####%s####", $tagname);
            $notification_html_template = str_replace($tag, $value, $notification_html_template);
        }

        // this fix has been set in place according to this thread:
        // https://stackoverflow.com/questions/37521775/dompdf-error-no-block-level-parent-found-not-good
        return str_replace("\n", "", $notification_html_template);
    }


?>
