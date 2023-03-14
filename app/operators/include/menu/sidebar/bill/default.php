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

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $planname, $username;

include_once("include/management/populate_selectbox.php");
$menu_usernames = get_users('CONFIG_DB_TBL_DALOUSERBILLINFO');
$menu_plannames = get_plans();

// define descriptors
$descriptors1 = array();

$descriptors1[] = array( 'type' => 'link', 'label' => t('button','NewUser'), 'href' =>'bill-pos-new.php',
                         'icon' => 'person-fill-add', );


if (count($menu_plannames) > 0) {
    $components = array();
    $components[] = array(
                            "id" => 'random',
                            "name" => "planname",
                            "type" => "select",
                            "selected_value" => ((isset($planname)) ? $planname : ""),
                            "required" => true,
                            "options" => $menu_plannames,
                            "caption" => t('all','PlanName'),
                            "tooltipText" => t('Tooltip','BillingPlanName'),
                          );

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','ListUsers'), 'action' => 'bill-pos-list.php', 'method' => 'GET',
                             'icon' => 'person-lines-fill', 'form_components' => $components, );
}

if (count($menu_usernames) > 0) {
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

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','EditUser'), 'action' => 'bill-pos-edit.php', 'method' => 'GET',
                             'icon' => 'pencil-square', 'form_components' => $components, );


    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','RemoveUsers'), 'href' => 'bill-pos-del.php',
                             'icon' => 'person-fill-x', );
}

$sections = array();
$sections[] = array( 'title' => 'Point of Sales Management', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'Billing',
                'sections' => $sections,
             );
