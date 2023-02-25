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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/bill/default.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

global $invoice_id, $invoice_status_id, $startdate, $enddate;

include('../common/includes/db_open.php');

$sql = sprintf("SELECT id, value FROM %s ORDER BY value ASC",
               $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS']);
$res = $dbSocket->query($sql);

$menu_invoice_status_id = array( "" );
while($row = $res->fetchRow()) {
    list($id, $value) = $row;
    $id = intval($id);
    
    $menu_invoice_status_id[$id] = $value;
}

include('../common/includes/db_close.php');

$descriptors1 = array();

$components = array();

$components[] = array(
                            "name" => "startdate",
                            "type" => "date",
                            "value" => ((isset($startdate)) ? $startdate : date("Y-m-01")),
                            "caption" => t('all','StartingDate'),
                            "tooltipText" => t('Tooltip','Date'),
                            "sidebar" => true
                     );

$components[] = array(
                            "name" => "enddate",
                            "type" => "date",
                            "value" => ((isset($enddate)) ? $enddate : date("Y-m-t")),
                            "caption" => t('all','EndingDate'),
                            "tooltipText" => t('Tooltip','Date'),
                            "sidebar" => true
                     );

$components[] = array(
                        "name" => "invoice_status_id",
                        "caption" => "Invoice Status",
                        "type" => "select",
                        "options" => $menu_invoice_status_id,
                        "selected_value" => (isset($invoice_status_id)) ? $invoice_status_id : "",
                        "tooltipText" => t('Tooltip','invoiceID'),
                        "sidebar" => true,
                        "integer_value" => true,
                     );

$descriptors1 = array();
$descriptors1[] = array( 'type' => 'form', 'title' => t('button','GenerateReport'), 'action' => 'bill-invoice-report.php', 'method' => 'GET',
                         'icon' => 'database-gear', 'form_components' => $components, );

$components = array();
$components[] = array(
                        "name" => "invoice_id",
                        "type" => "number",
                        "value" => ((isset($invoice_id)) ? $invoice_id : ""),
                        "min" => "1",
                        "tooltipText" => t('Tooltip','invoiceID'),
                        "sidebar" => true,
                        "required" => true,
                     );
$descriptors1[] = array( 'type' => 'form', 'title' => t('button','ShowInvoice'), 'action' => 'bill-invoice-show.php', 'method' => 'GET',
                         'icon' => 'receipt', 'form_components' => $components, );

$sections = array();
$sections[] = array( 'title' => 'Invoice Report', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'User Billing',
                'sections' => $sections,
             );
