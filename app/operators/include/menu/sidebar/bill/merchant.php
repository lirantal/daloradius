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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/bill/merchant.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

include_once("../common/includes/validation.php");

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $startdate, $enddate, $valid_vendorTypes, $billing_paypal_vendor_type, $billing_paypal_payeremail,
       $valid_paymentStatus, $bill_merchant_transactions_options_all, $bill_merchant_transactions_options_default,
       $sqlfields, $orderBy, $orderType;

$components = array();

$components[] = array(
                            "id" => 'random',
                            "name" => "startdate",
                            "type" => "date",
                            "value" => ((isset($startdate)) ? $startdate : date("Y-m-01")),
                            "caption" => t('all','StartingDate'),
                            "tooltipText" => t('Tooltip','Date'),
                            "sidebar" => true
                     );

$components[] = array(
                            "id" => 'random',
                            "name" => "enddate",
                            "type" => "date",
                            "value" => ((isset($enddate)) ? $enddate : date("Y-m-t")),
                            "caption" => t('all','EndingDate'),
                            "tooltipText" => t('Tooltip','Date'),
                            "sidebar" => true
                     );

$components[] = array(
                            "id" => 'random',
                            "caption" => t('all','VendorType'),
                            "type" => "select",
                            "name" => "vendor_type",
                            "options" => $valid_vendorTypes,
                            "selected_value" => ((isset($billing_paypal_vendor_type)) ? $billing_paypal_vendor_type : $valid_vendorTypes[0])
                     );

$components[] = array(
                            "id" => 'random',
                            "caption" => t('all','PayerEmail'),
                            "type" => "email",
                            "name" => "payer_email",
                            "value" => ((isset($billing_paypal_payeremail)) ? $billing_paypal_payeremail : ""),
                     );

$components[] = array(
                            "id" => 'random',
                            "caption" => t('all','PaymentStatus'),
                            "type" => "select",
                            "name" => "payment_status",
                            "options" => $valid_paymentStatus,
                            "selected_value" => ((isset($billing_paypal_paymentstatus)) ? $billing_paypal_paymentstatus : $valid_paymentStatus[0])
                     );

$components[] = array(
                            "id" => 'random',
                            "caption" => t('button','AccountingFieldsinQuery'),
                            "type" => "select",
                            "name" => "sqlfields[]",
                            "options" => $bill_merchant_transactions_options_all,
                            "selected_value" => ((isset($sqlfields)) ? $sqlfields : $bill_merchant_transactions_options_default),
                            "multiple" => true,
                            "size" => 7,
                            "show_controls" => true,
                     );

$components[] = array(
                            "id" => 'random',
                            "caption" => t('button','OrderBy'),
                            "type" => "select",
                            "name" => "orderBy",
                            "options" => $bill_merchant_transactions_options_all,
                            "selected_value" => ((isset($orderBy)) ? $orderBy : $bill_merchant_transactions_options_default[0])
                     );

$components[] = array(
                            "id" => 'random',
                            "caption" => "Order Type",
                            "type" => "select",
                            "name" => "orderType",
                            "options" => array("asc" => "Ascending", "desc" => "Descending"),
                            "selected_value" => ((isset($orderType)) ? $orderType : "asc")
                     );

$descriptors1 = array();
$descriptors1[] = array( 'type' => 'form', 'title' => t('button','ProcessQuery'), 'action' => 'bill-merchant-transactions.php', 'method' => 'GET',
                         'icon' => 'filter-circle-fill', 'form_components' => $components, );

$sections = array();
$sections[] = array( 'title' => 'Track Merchant Transactions', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'Billing',
                'sections' => $sections,
             );
