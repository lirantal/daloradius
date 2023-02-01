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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/bill/payments.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

include_once("include/management/populate_selectbox.php");
$menu_usernames = get_users('CONFIG_DB_TBL_DALOUSERBILLINFO');
$menu_paymentnames = get_payment_types();

$username_select = array(
                            "name" => "username",
                            "type" => "text",
                            "value" => ((isset($username)) ? $username : ""),
                            "datalist" => (($autocomplete) ? $menu_usernames : array()),
                            "caption" => t('all','Username'),
                            "tooltipText" => t('Tooltip','usernameTooltip'),
                            "sidebar" => true,
                        );

// define descriptors
$descriptors1 = array();

$descriptors1[] = array( 'type' => 'link', 'label' => t('button','NewPayment'), 'href' =>'bill-payments-new.php', );

$components = array();

$components[] = $username_select;

$components[] = array(
                        "name" => "invoice_id",
                        "type" => "number",
                        "value" => ((isset($invoice_id)) ? $invoice_id : ""),
                        "min" => "1",
                        "tooltipText" => t('Tooltip','invoiceID'),
                        "sidebar" => true,
                     );

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','ListPayments'), 'action' => 'bill-payments-list.php', 'method' => 'GET',
                         'form_components' => $components, );

$components = array();

$components[] = array(
                        "name" => "payment_id",
                        "type" => "number",
                        "value" => ((isset($payment_id)) ? $payment_id : ""),
                        "min" => "1",
                        "tooltipText" => t('Tooltip','PaymentId'),
                        "sidebar" => true,
                     );

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','EditPayment'), 'action' => 'bill-payments-edit.php', 'method' => 'GET',
                         'form_components' => $components, );

$descriptors1[] = array( 'type' => 'link', 'label' => t('button','RemovePayment'), 'href' =>'bill-payments-del.php', );


$descriptors2 = array();
$descriptors2[] = array( 'type' => 'link', 'label' => t('button','NewPayType'), 'href' =>'bill-payment-types-new.php', );

if (count($menu_paymentnames) > 0) {
    $descriptors2[] = array( 'type' => 'link', 'label' => t('button','ListPayTypes'), 'href' => 'bill-payment-types-list.php', );
    
    $components = array();
    $components[] = array(
                                "name" => "paymentname",
                                "type" => "select",
                                "selected_value" => ((isset($paymentname)) ? $paymentname : ""),
                                "required" => true,
                                "options" => $menu_paymentnames,
                                "caption" => t('all','PayTypeName'),
                                "title" => t('Tooltip','PayTypeName'),
                                "sidebar" => true,
                            );

    $descriptors2[] = array( 'type' => 'form', 'title' => t('button','EditPayType'), 'action' => 'bill-payment-types-edit.php', 'method' => 'GET',
                             'form_components' => $components, );
 
    $descriptors2[] = array( 'type' => 'link', 'label' => t('button','RemovePayType'), 'href' => 'bill-payment-types-del.php', );
}

$sections = array();
$sections[] = array( 'title' => 'Payments Management', 'descriptors' => $descriptors1 );
$sections[] = array( 'title' => 'Payments Types Management', 'descriptors' => $descriptors2 );

// add sections to menu
$menu = array(
                'title' => 'Billing',
                'sections' => $sections,
             );
