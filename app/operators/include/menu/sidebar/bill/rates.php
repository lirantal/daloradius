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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/bill/rates.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $ratename, $username, $startdate, $enddate;

include_once("include/management/populate_selectbox.php");
$menu_usernames = get_users('CONFIG_DB_TBL_DALOUSERBILLINFO');
$menu_ratenames = get_ratenames();

$ratename_select = array(
                            "id" => 'random',
                            "name" => "ratename",
                            "type" => "select",
                            "selected_value" => ((isset($ratename)) ? $ratename : ""),
                            "required" => true,
                            "options" => $menu_ratenames,
                            "caption" => t('all','RateName'),
                            "tooltipText" => t('Tooltip','RateName'),
                            "sidebar" => true,
                        );

// define descriptors
$descriptors1 = array();

$components = array();
$components[] = $ratename_select;

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
                        "name" => "startdate",
                        "type" => "date",
                        "value" => ((isset($startdate)) ? $startdate : date("Y-m-01")),
                        "caption" => t('all','StartingDate'),
                        "tooltipText" => t('Tooltip','Date'),
                        "sidebar" => true,
                     );

$components[] = array(
                        "id" => 'random',
                        "name" => "enddate",
                        "type" => "date",
                        "value" => ((isset($enddate)) ? $enddate : date("Y-m-t")),
                        "caption" => t('all','EndingDate'),
                        "tooltipText" => t('Tooltip','Date'),
                        "sidebar" => true,
                     );

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','DateAccounting'), 'action' => 'bill-rates-date.php', 'method' => 'GET',
                         'icon' => 'calendar-range', 'form_components' => $components, );

$descriptors2 = array();
$descriptors2[] = array( 'type' => 'link', 'label' => t('button','NewRate'), 'href' =>'bill-rates-new.php',
                         'icon' => 'plus-circle-fill', );

if (count($menu_ratenames) > 0) {
    $descriptors2[] = array( 'type' => 'link', 'label' => t('button','ListRates'), 'href' => 'bill-rates-list.php',
                             'icon' => 'list', );

    $components = array();
    $components[] = $ratename_select;

    $descriptors2[] = array( 'type' => 'form', 'title' => t('button','EditRate'), 'action' => 'bill-rates-edit.php', 'method' => 'GET',
                             'icon' => 'pencil-square', 'form_components' => $components, );

    $descriptors2[] = array( 'type' => 'link', 'label' => t('button','RemoveRate'), 'href' => 'bill-plans-del.php',
                             'icon' => 'x-circle-fill', );
}

$sections = array();
$sections[] = array( 'title' => 'Track Rates', 'descriptors' => $descriptors1 );
$sections[] = array( 'title' => 'Rates Management', 'descriptors' => $descriptors2 );

// add sections to menu
$menu = array(
                'title' => 'Billing',
                'sections' => $sections,
             );
