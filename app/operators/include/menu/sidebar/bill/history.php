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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/bill/history.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

include_once("../common/includes/validation.php");

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $username, $billaction, $sqlfields, $orderBy, $orderType, $valid_billactions, $bill_history_query_options_default,
       $bill_history_query_options_all;

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
                                                                    'table' => 'CONFIG_DB_TBL_DALOUSERBILLINFO',
                                                                 ),
                                           ),
                        "tooltipText" => t('Tooltip','Username'),
                        "caption" => t('all','Username'),
                        "sidebar" => true,
                     );

$components[] = array(
                        "id" => 'random',
                        "caption" => t('all','BillAction'),
                        "type" => "select",
                        "name" => "billaction",
                        "options" => $valid_billactions,
                        "selected_value" => ((isset($billaction)) ? $billaction : $valid_billactions[0]),
                     );

$components[] = array(
                        "id" => 'random',
                        "caption" => t('button','AccountingFieldsinQuery'),
                        "type" => "select",
                        "name" => "sqlfields[]",
                        "options" => $bill_history_query_options_all,
                        "selected_value" => ((isset($sqlfields)) ? $sqlfields : $bill_history_query_options_default),
                        "multiple" => true,
                        "size" => 7,
                        "show_controls" => true,
                     );

$components[] = array(
                        "id" => 'random',
                        "caption" => t('button','OrderBy'),
                        "type" => "select",
                        "name" => "orderBy",
                        "options" => $bill_history_query_options_all,
                        "selected_value" => ((isset($orderBy)) ? $orderBy : array_keys($bill_history_query_options_all)[0])
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
$descriptors1[] = array( 'type' => 'form', 'title' => t('button','ProcessQuery'), 'action' => 'bill-history-query.php', 'method' => 'GET',
                         'icon' => 'filter-circle-fill', 'form_components' => $components, );

$sections = array();
$sections[] = array( 'title' => 'Track Billing History', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'Billing',
                'sections' => $sections,
             );
