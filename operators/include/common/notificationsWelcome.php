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
    if (strpos($_SERVER['PHP_SELF'], '/include/common/notificationWelcome.php') !== false) {
        header("Location: ../../index.php");
        exit;
    }
 
    include_once("notifications/processNotificationWelcome.php");
    
    // we (try to) init the email
    if (isset($email) && !empty($email)) {
        $invoice_email = $email;
    } else if (isset($bi_emailinvoice) && !empty($bi_emailinvoice)) {
        $invoice_email = $bi_emailinvoice;
    } else if (isset($bi_email) && !empty($bi_email)) {
        $invoice_email = $bi_email;
    } else {
        $invoice_email = "";
    }
    
    
    // we (try to) init the phone
    if (isset($mobilephone) && !empty($mobilephone)) {
        $invoice_phone = $mobilephone;
    } else if (isset($workphone) && !empty($workphone)) {
        $invoice_phone = $workphone;
    } else if (isset($homephone) && !empty($homephone)) {
        $invoice_phone = $homephone;
    } else {
        $invoice_phone = "(n/a)";
    }
    
    // we also (try to) init the address
    $invoice_address = "";
    if (isset($ui_address) &&!empty($ui_address)) {
        $invoice_address = $ui_address;
    }
    
    if (isset($ui_city) && !empty($ui_city)) {
        $invoice_address .= ", " . $ui_city;
    }
    
    if (isset($ui_state) && !empty($ui_state)) {
        $invoice_address .= "<br>" . $ui_state;
    }
    
    if (isset($ui_zip) && !empty($ui_zip)) {
        $invoice_address .= " " . $ui_zip;
    }
    
    if (empty($invoice_address)) {
        $invoice_address = "(n/a)";
    }
    
    // set SMTP server info
    $smtpInfo['host'] = $configValues['CONFIG_MAIL_SMTPADDR'];
    $smtpInfo['port'] = $configValues['CONFIG_MAIL_SMTPPORT'];
    $smtpInfo['auth'] = $configValues['CONFIG_MAIL_SMTPAUTH'];
    
    // set the from field
    $from = $configValues['CONFIG_MAIL_SMTPFROM'];
    
    // set customer info
    $customerInfo = array();
    $customerInfo['customer_name'] = sprintf("%s %s", $firstname, $lastname);
    $customerInfo['customer_address'] = $invoice_address;
    $customerInfo['customer_phone'] = $invoice_phone;
    $customerInfo['customer_email'] = $invoice_email;
    
    $customerInfo['service_plan_name'] = $planName;
    
    @sendWelcomeNotification($customerInfo, $smtpInfo, $from);

?>
