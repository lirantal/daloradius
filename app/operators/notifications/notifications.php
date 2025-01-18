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
 * Authors:    Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/notifications/notifications.php') !== false) {
    http_response_code(404);
    exit;
}

include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);

function prepare_welcome_notification($configValues, $info) {
    $template_path = implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_NOTIFICATIONS_TEMPLATES'], 'welcome.html' ]);
    $template_content = file_get_contents($template_path);

    $date = date("Y-m-d");

    $template_content = str_replace("####__INVOICE_CREATION_DATE__####", $date, $template_content);
    $template_content = str_replace("####__CUSTOMER_NAME__####", $info['customer_name'], $template_content);
    $template_content = str_replace("####__CUSTOMER_ADDRESS__####", $info['customer_address'], $template_content);
    $template_content = str_replace("####__CUSTOMER_PHONE__####", $info['customer_phone'], $template_content);
    $template_content = str_replace("####__CUSTOMER_EMAIL__####", $info['customer_email'], $template_content);

    // this fix has been set in place according to this thread:
    // https://stackoverflow.com/questions/37521775/dompdf-error-no-block-level-parent-found-not-good
    return str_replace("\n", "", $template_content);
}

function prepare_user_invoice_details_notification($configValues, $info) {
    $template_path = implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_NOTIFICATIONS_TEMPLATES'], 'user_invoice_details.html' ]);
    $template_content = file_get_contents($template_path);

    $date = date("Y-m-d");

    $template_content = str_replace("####__INVOICE_CREATION_DATE__####", $date, $template_content);
    $template_content = str_replace("####__BUSINESS_NAME__####", $info['business_name'], $template_content);
    $template_content = str_replace("####__BUSINESS_ADDRESS__####", $info['business_address'], $template_content);
    $template_content = str_replace("####__BUSINESS_PHONE__####", $info['business_phone'], $template_content);
    $template_content = str_replace("####__BUSINESS_EMAIL__####", $info['business_email'], $template_content);
    $template_content = str_replace("####__SERVICE_PLAN_INFO__####", $info['service_plan_info'], $template_content);
    
    // this fix has been set in place according to this thread:
    // https://stackoverflow.com/questions/37521775/dompdf-error-no-block-level-parent-found-not-good
    return str_replace("\n", "", $template_content);
}

function prepare_batch_details_notification($configValues, $info) {
    $template_path = implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_NOTIFICATIONS_TEMPLATES'], 'batch_details.html' ]);
    $template_content = file_get_contents($template_path);

    $date = date("Y-m-d");

    $tagname_value_mappings = [
                                    "__INVOICE_CREATION_DATE__" => $date,
                                    "__BUSINESS_NAME__" => $info['business_name'],
                                    "__BUSINESS_OWNER_NAME__" => $info['business_owner_name'],
                                    "__BUSINESS_ADDRESS__" => $info['business_address'],
                                    "__BUSINESS_PHONE__" => $info['business_phone'],
                                    "__BUSINESS_EMAIL__" => $info['business_email'],
                                    "__BUSINESS_WEB__" => $info['business_web'],
                                    "__BATCH_DETAILS__" => $info['batch_details'],
                                    "__BATCH_ACTIVE_USERS__" => $info['batch_active_users'],
                                    "__SERVICE_PLAN_INFO__" => $info['service_plan_info'],
                              ];

    foreach ($tagname_value_mappings as $tagname => $value) {
        $tag = sprintf("####%s####", $tagname);
        $template_content = str_replace($tag, $value, $template_content);
    }

    // this fix has been set in place according to this thread:
    // https://stackoverflow.com/questions/37521775/dompdf-error-no-block-level-parent-found-not-good
    return str_replace("\n", "", $template_content);
}

function prepare_user_invoice_notification($configValues, $info) {
    $templates_base = $configValues['OPERATORS_NOTIFICATIONS_TEMPLATES'];
    
    // the default HTML template - OLD STYLE
    $notification_template = implode(DIRECTORY_SEPARATOR, [ $templates_base, 'user_invoice.html' ]);
    $notification_item_template = null;
    
    // the default HTML template - NEW STYLE
    if (isset($configValues['CONFIG_INVOICE_TEMPLATE'])) {
        $notification_template = implode(DIRECTORY_SEPARATOR, [ $templates_base, $configValues['CONFIG_INVOICE_TEMPLATE'] ]);
        
        if (isset($configValues['CONFIG_INVOICE_ITEM_TEMPLATE'])) {
            $notification_item_template = implode(DIRECTORY_SEPARATOR, [ $templates_base, $configValues['CONFIG_INVOICE_ITEM_TEMPLATE'] ]);
        }
    }
    
    // the default HTML template for current location - NEW STYLE
    if ((isset($_SESSION['location_name'])) && ($_SESSION['location_name'] != "default")) {
        $location_name = $_SESSION['location_name'];
        $location = $configValues['CONFIG_LOCATIONS'][$location_name];
    
        if (isset($location['CONFIG_INVOICE_TEMPLATE'])) {
            $notification_template = implode(DIRECTORY_SEPARATOR, [ $templates_base, $configValues['CONFIG_INVOICE_TEMPLATE'] ]);
            
            if (isset($location['CONFIG_INVOICE_ITEM_TEMPLATE'])) {
                $notification_item_template = implode(DIRECTORY_SEPARATOR, [ $templates_base, $configValues['CONFIG_INVOICE_ITEM_TEMPLATE'] ]);
            }
        }
    }

    // load template for invoice
    $notification_html_template = file_get_contents($notification_template);

    // load template for each invoice item
    if ($notification_item_template !== null) {
        $notification_item_html_template = file_get_contents($notification_item_template);
    }

    $date = date("Y-m-d");

    // notification date
    $notification_html_template = str_replace("####__INVOICE_CREATION_DATE__####", $date, $notification_html_template);
    
    // user details
    $notification_html_template = str_replace("####__CUSTOMER_NAME__####", $info['customer_name'], $notification_html_template);
    $notification_html_template = str_replace("####__CUSTOMER_ADDRESS__####", $info['customer_address'], $notification_html_template);
    $notification_html_template = str_replace("####__CUSTOMER_PHONE__####", $info['customer_phone'], $notification_html_template);
    $notification_html_template = str_replace("####__CUSTOMER_EMAIL__####", $info['customer_email'], $notification_html_template);
    
    // invoice information
    $notification_html_template = str_replace("####__INVOICE_DETAILS__####", $info['invoice_details'], $notification_html_template);
    $notification_html_template = str_replace("####__INVOICE_ITEMS__####", $info['invoice_items'], $notification_html_template);
    
    // customer details - NEW STYLE            
    $notification_html_template = str_replace("[CustomerId]", $info['customerId'], $notification_html_template);
    $notification_html_template = str_replace("[CustomerName]", $info['customerName'], $notification_html_template);
    $notification_html_template = str_replace("[CustomerAddress]", $info['customerAddress'], $notification_html_template);
    $notification_html_template = str_replace("[CustomerAddress2]", $info['customerAddress2'], $notification_html_template);
    $notification_html_template = str_replace("[CustomerPhone]", $info['customerPhone'], $notification_html_template);
    $notification_html_template = str_replace("[CustomerEmail]", $info['customerEmail'], $notification_html_template);
    $notification_html_template = str_replace("[CustomerContact]", $info['customerContact'], $notification_html_template);
    
    // invoice details - NEW STYLE
    $notification_html_template = str_replace("[InvoiceNumber]", $info['invoiceNumber'], $notification_html_template);
    $notification_html_template = str_replace("[InvoiceDate]", $info['invoiceDate'], $notification_html_template);
    $notification_html_template = str_replace("[InvoiceStatus]", $info['invoiceStatus'], $notification_html_template);
    $notification_html_template = str_replace("[InvoiceTotalBilled]", $info['invoiceTotalBilled'], $notification_html_template);
    $notification_html_template = str_replace("[InvoicePaid]", $info['invoicePaid'], $notification_html_template);
    $notification_html_template = str_replace("[InvoiceDue]", $info['invoiceDue'], $notification_html_template);
    $notification_html_template = str_replace("[InvoiceNotes]", $info['invoiceNotes'], $notification_html_template);
    
    // invoice items - NEW STYLE
    if ($notification_item_template !== null) {
        $invoiceItems = '';
        
        foreach($info['invoiceItems'] as $invoiceItem) {
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
    $notification_html_template = str_replace("[InvoiceTotalAmount]", $info['invoiceTotalAmount'], $notification_html_template);
    $notification_html_template = str_replace("[InvoiceTotalTax]", $info['invoiceTotalTax'], $notification_html_template);
    
    // this fix has been set in place according to this thread:
    // https://stackoverflow.com/questions/37521775/dompdf-error-no-block-level-parent-found-not-good
    return str_replace("\n", "", $notification_html_template);
}
