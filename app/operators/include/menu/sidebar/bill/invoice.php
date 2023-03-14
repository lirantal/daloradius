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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/bill/invoice.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}


$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $username, $invoice_status_id, $user_id, $startdate, $enddate;

include('../common/includes/db_open.php');

// get valid users
$sql = sprintf("SELECT id, username FROM %s ORDER BY username ASC", $configValues['CONFIG_DB_TBL_DALOUSERINFO']);
$res = $dbSocket->query($sql);

$menu_users = array();
while ($row = $res->fetchrow()) {
    list($id, $value) = $row;
    $id = intval($id);

    $menu_users[$id] = $value;
}

include('../common/includes/db_close.php');


include_once("include/management/populate_selectbox.php");
include_once("../common/includes/validation.php");

$menu_invoice_status_id = get_invoice_status_id();
$menu_invoice_status_id[] = "";
$descriptors1 = array();

$components = array();
$components[] = array(
                        "id" => 'random',
                        "name" => "username",
                        "type" => "text",
                        "value" => ((isset($username)) ? $username : ""),
                        "datalist" => array(
                                                'type' => 'ajax',
                                                'url' => 'library/ajax/json_api.php',
                                                'search_param' => 'username',
                                                'params' => array(
                                                                    'datatype' => 'usernames',
                                                                    'action' => 'list',
                                                                    'table' => 'CONFIG_DB_TBL_DALOUSERINFO',
                                                                 ),
                                           ),
                        "tooltipText" => t('Tooltip','Username'),
                        "caption" => t('all','Username'),
                        "sidebar" => true,
                     );

$components[] = array(
                        "id" => 'random',
                        "name" => "invoice_status_id",
                        "caption" => "Invoice Status",
                        "type" => "select",
                        "options" => $menu_invoice_status_id,
                        "selected_value" => (isset($invoice_status_id)) ? $invoice_status_id : "",
                        "tooltipText" => t('Tooltip','invoiceID'),
                        "sidebar" => true,
                        "integer_value" => true,
                     );

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','ListInvoices'), 'action' => 'bill-invoice-list.php', 'method' => 'GET',
                         'icon' => 'list', 'form_components' => $components, );

$components = array();
$components[] = array(
                        "id" => 'random',
                        "name" => "user_id",
                        "caption" => t('all','Username'),
                        "type" => "select",
                        "options" => $menu_users,
                        "selected_value" => (isset($user_id)) ? $user_id : "",
                        "tooltipText" => t('Tooltip','usernameTooltip'),
                        "sidebar" => true,
                        "integer_value" => true,
                     );

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','NewInvoice'), 'action' => 'bill-invoice-new.php', 'method' => 'GET',
                         'icon' => 'plus-circle-fill', 'form_components' => $components, );

$components = array();
$components[] = array(
                        "id" => 'random',
                        "name" => "invoice_id",
                        "type" => "number",
                        "value" => ((isset($invoice_id)) ? $invoice_id : ""),
                        "min" => "1",
                        "tooltipText" => t('Tooltip','invoiceID'),
                        "sidebar" => true,
                     );
$descriptors1[] = array( 'type' => 'form', 'title' => t('button','EditInvoice'), 'action' => 'bill-invoice-edit.php', 'method' => 'GET',
                         'icon' => 'pencil-square', 'form_components' => $components, );

$descriptors1[] = array( 'type' => 'link', 'label' => t('button','RemoveInvoice'), 'href' =>'bill-invoice-del.php',
                         'icon' => 'x-circle-fill', );


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
                        "name" => "invoice_status_id",
                        "caption" => "Invoice Status",
                        "type" => "select",
                        "options" => $menu_invoice_status_id,
                        "selected_value" => (isset($invoice_status_id)) ? $invoice_status_id : "",
                        "tooltipText" => t('Tooltip','invoiceID'),
                        "sidebar" => true,
                        "integer_value" => true
                     );

$components[] = array(
                        "id" => 'random',
                        "name" => "username",
                        "type" => "text",
                        "value" => ((isset($username)) ? $username : ""),
                        "datalist" => array(
                                                'type' => 'ajax',
                                                'url' => 'library/ajax/json_api.php',
                                                'search_param' => 'username',
                                                'params' => array(
                                                                    'datatype' => 'usernames',
                                                                    'action' => 'list',
                                                                    'table' => 'CONFIG_DB_TBL_DALOUSERINFO',
                                                                 ),
                                           ),
                        "tooltipText" => t('Tooltip','Username'),
                        "caption" => t('all','Username'),
                        "sidebar" => true,
                     );

$descriptors2 = array();
$descriptors2[] = array( 'type' => 'form', 'title' => t('button','GenerateReport'), 'action' => 'bill-invoice-report.php',
                         'method' => 'GET', 'icon' => 'database-gear', 'form_components' => $components, );

$sections = array();
$sections[] = array( 'title' => 'Invoice Management', 'descriptors' => $descriptors1 );
$sections[] = array( 'title' => 'Invoice Report', 'descriptors' => $descriptors2 );

// add sections to menu
$menu = array(
                'title' => 'Billing',
                'sections' => $sections,
             );
