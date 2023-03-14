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
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/acct/plans.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $username, $startdate, $enddate, $planname;

include_once("include/management/populate_selectbox.php");
$menu_plannames = get_plans();
array_unshift($menu_plannames, "");

$menu_usernames = get_users();
array_unshift($menu_usernames, "");

// define descriptors
$descriptors1 = array();

$components = array();

$components[] = array(
                        // this will produce a random id
                        "id" => 'random',
                        "name" => "username",
                        "type" => "text",
                        "value" => ((isset($username)) ? $username : ""),
                        "required" => true,
                        "datalist" => array(
                                                'type' => 'ajax',
                                                'url' => 'library/ajax/json_api.php',
                                                'search_param' => 'username',
                                                'params' => array(
                                                                    'datatype' => 'usernames',
                                                                    'action' => 'list',
                                                                    'table' => 'CONFIG_DB_TBL_RADCHECK',
                                                                 ),
                                           ),
                        "tooltipText" => t('Tooltip','usernameTooltip'),
                        "caption" => t('all','Username'),
                        "sidebar" => true,
                      );

$components[] = array(
                        // this will produce a random id
                        "id" => 'random',
                        "name" => "startdate",
                        "type" => "date",
                        "value" => ((isset($startdate)) ? $startdate : date("Y-m-01")),
                        "caption" => t('all','StartingDate'),
                        "tooltipText" => t('Tooltip','Date'),
                     );

$components[] = array(
                        // this will produce a random id
                        "id" => 'random',
                        "name" => "enddate",
                        "type" => "date",
                        "value" => ((isset($enddate)) ? $enddate : date("Y-m-t")),
                        "caption" => t('all','EndingDate'),
                        "tooltipText" => t('Tooltip','Date'),
                     );


$components[] = array(
                        // this will produce a random id
                        "id" => 'random',
                        "name" => "planname",
                        "type" => "select",
                        "selected_value" => ((isset($planname)) ? $planname : ""),
                        "options" => $menu_plannames,
                        "caption" => t('all','PlanName'),
                        "tooltipText" => t('Tooltip','PlanName'),
                     );

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','PlanUsage'), 'action' => 'acct-plans-usage.php',
                         'method' => 'GET', 'icon' => 'card-list', 'form_components' => $components, );


$sections = array();
$sections[] = array( 'title' => 'Plan Accounting', 'descriptors' => $descriptors1 );


// add sections to menu
$menu = array(
                'title' => 'Accounting',
                'sections' => $sections,
             );
