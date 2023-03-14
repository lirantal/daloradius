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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/rep/default.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

include_once("../common/includes/validation.php");

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $username, $startdate, $enddate, $radiusReply, $valid_radiusReplys, $orderBy;

include_once("include/management/populate_selectbox.php");
$usernameOnline_options = get_online_users();

$username_input = array(
                            "id" => 'random',
                            "name" => "username",
                            "type" => "text",
                            "value" => ((isset($username)) ? $username : ""),
                            "datalist" => array(
                                                    'type' => 'traditional',
                                                    'options' => (($autocomplete) ? $usernameOnline_options : array()),
                                               ),
                            "tooltipText" => t('Tooltip','Username'),
                            "caption" => t('all','Username'),
                            "sidebar" => true,
                       );

$orderBy_options = array(
                            "Time" => "Time",
                            "Download" => "Download (bytes)",
                            "Upload" => "Upload (bytes)",
                        );

$date_select_components = array();
$date_select_components[] = array(
                                        "id" => 'random',
                                        "name" => "startdate",
                                        "type" => "date",
                                        "value" => ((isset($startdate)) ? $startdate : date("Y-01-01")),
                                        "caption" => t('all','StartingDate'),
                                        "tooltipText" => t('Tooltip','Date'),
                                 );

$date_select_components[] = array(
                                        "id" => 'random',
                                        "name" => "enddate",
                                        "type" => "date",
                                        "value" => ((isset($enddate)) ? $enddate : date("Y-01-01", mktime(0, 0, 0, date('n') + 1, 1, date('Y')))),
                                        "caption" => t('all','EndingDate'),
                                        "tooltipText" => t('Tooltip','Date'),
                                 );

// define descriptors
$descriptors1 = array();

if (count($usernameOnline_options) > 0) {
    $components = array();
    $components[] = $username_input;

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','OnlineUsers'), 'action' => 'rep-online.php',
                             'method' => 'GET', 'icon' => 'person-lines-fill', 'form_components' => $components, );
}

// users are taken from the radacct table and datalist is updated via ajax
$components = array();
$username_input["datalist"] = array(
                                        'type' => 'ajax',
                                        'url' => 'library/ajax/json_api.php',
                                        'search_param' => 'username',
                                        'params' => array(
                                                            'datatype' => 'usernames',
                                                            'action' => 'list',
                                                            'table' => 'CONFIG_DB_TBL_RADACCT',
                                                         ),
                                   );
$components[] = $username_input;

$components[] = array(
                            "id" => 'random',
                            "caption" => "RADIUS Reply",
                            "name" => "radiusReply",
                            "type" => "select",
                            "selected_value" => ((isset($radiusReply)) ? $radiusReply : $valid_radiusReplys[0]),
                            "options" => $valid_radiusReplys,
                            "tooltipText" => "Filter records with the selected RADIUS Reply"
                          );

$components = array_merge($components, $date_select_components);

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','LastConnectionAttempts'), 'action' => 'rep-lastconnect.php',
                         'method' => 'GET', 'icon' => 'person-lines-fill', 'form_components' => $components, );

// new descriptor
$components = array();
$components = $date_select_components;

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','NewUsers'), 'action' => 'rep-newusers.php',
                         'method' => 'GET', 'icon' => 'person-lines-fill', 'form_components' => $components, );

$components = array();

$components[] = $username_input;
$components = array_merge($components, $date_select_components);

$components[] = array(
                            "id" => 'random',
                            "caption" => t('button','OrderBy'),
                            "name" => "orderBy",
                            "type" => "select",
                            "selected_value" => ((isset($orderBy)) ? $orderBy : array_keys($orderBy_options)[0]),
                            "options" => $orderBy_options,
                            "tooltipText" => "You can order the results by: " . implode(" or ", array_keys($orderBy_options)),
                          );


$descriptors1[] = array( 'type' => 'form', 'title' => t('button','TopUser'), 'action' => 'rep-topusers.php',
                         'method' => 'GET', 'icon' => 'person-lines-fill', 'form_components' => $components, );

$descriptors2 = array();
$descriptors2[] = array( 'type' => 'link', 'label' => t('button','History'),
                         'href' => 'rep-history.php', 'icon' => 'clock-history', );

$sections = array();
$sections[] = array( 'title' => 'User Reports', 'descriptors' => $descriptors1 );
$sections[] = array( 'title' => 'Other Reports', 'descriptors' => $descriptors2 );

// add sections to menu
$menu = array(
                'title' => 'Reports',
                'sections' => $sections,
             );
