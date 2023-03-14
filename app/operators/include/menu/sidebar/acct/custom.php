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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/acct/custom.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

include_once("../common/includes/validation.php");

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $startdate, $enddate, $acct_custom_query_options_all, $where_field, $operator, $accounting_custom_value,
       $acct_custom_query_options_default, $acct_custom_query_options_all, $sqlfields, $orderBy, $orderType;

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
                        "caption" => t('button','Where'),
                        "type" => "select",
                        "name" => "where_field",
                        "options" => $acct_custom_query_options_all,
                        "selected_value" => ((isset($where_field)) ? $where_field : $acct_custom_query_options_all[0]),
                     );

$components[] = array(
                        "id" => 'random',
                        "caption" => "Operator",
                        "type" => "select",
                        "name" => "operator",
                        "options" => array("equals", "contains"),
                        "selected_value" => ((isset($operator)) ? $operator : "equals"),
                     );

$components[] = array(
                        "id" => 'random',
                        "type" => "text",
                        "name" => "where_value",
                        "caption" => "Filter",
                        "tooltipText" => t('Tooltip','Filter'),
                        "sidebar" => true,
                        "value" => ((isset($accounting_custom_value)) ? $accounting_custom_value : ""),

                     );

$components[] = array(
                        "id" => 'random',
                        "caption" => t('button','AccountingFieldsinQuery'),
                        "type" => "select",
                        "name" => "sqlfields[]",
                        "options" => $acct_custom_query_options_all,
                        "selected_value" => ((isset($sqlfields)) ? $sqlfields : $acct_custom_query_options_default),
                        "multiple" => true,
                        "size" => 7,
                        "show_controls" => true,
                     );

$components[] = array(
                        "id" => 'random',
                        "caption" => t('button','OrderBy'),
                        "type" => "select",
                        "name" => "orderBy",
                        "options" => $acct_custom_query_options_all,
                        "selected_value" => ((isset($orderBy)) ? $orderBy : $acct_custom_query_options_all[0])
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
$descriptors1[] = array( 'type' => 'form', 'title' => t('button','ProcessQuery'), 'action' => 'acct-custom-query.php', 'method' => 'GET',
                         'icon' => 'filter-circle-fill', 'form_components' => $components, );

$sections = array();
$sections[] = array( 'title' => 'Custom Query', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'Accounting',
                'sections' => $sections,
             );
