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

include_once("library/validation.php");

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $username, $startdate, $enddate, $radiusReply, $valid_radiusReplys, $orderBy;

include_once("include/management/populate_selectbox.php");
$username_options = get_users('CONFIG_DB_TBL_RADACCT');
$usernameOnline_options = get_online_users();

$username_input = array(
                            "name" => "username",
                            "type" => "text",
                            "value" => ((isset($username)) ? $username : ""),
                            "datalist" => (($autocomplete) ? $usernameOnline_options : array()),
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
                                        "name" => "startdate",
                                        "type" => "date",
                                        "value" => ((isset($startdate)) ? $startdate : date("Y-01-01")),
                                        "caption" => t('all','StartingDate'),
                                        "tooltipText" => t('Tooltip','Date'),
                                 );
                     
$date_select_components[] = array(
                                        "name" => "enddate",
                                        "type" => "date",
                                        "value" => ((isset($enddate)) ? $enddate : date("Y-01-01", mktime(0, 0, 0, date('n') + 1, 1, date('Y')))),
                                        "caption" => t('all','EndingDate'),
                                        "tooltipText" => t('Tooltip','Date'),
                                 );

// define descriptors
$descriptors1 = array();

$components = array();
$components[] = $username_input;

// reset components IDs
for ($i = 0; $i < count($components); $i++) {
    $components[$i]['id'] = "id_" . rand();
}

if (count($usernameOnline_options) > 0) {
    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','OnlineUsers'), 'action' => 'rep-online.php', 'method' => 'GET',
                             'icon' => 'person-lines-fill', 'img' => array( 'src' => 'static/images/icons/reportsOnlineUsers.gif', ), 'form_components' => $components, );
}

$components = array();
$username_input["datalist"] = (($autocomplete) ? $username_options : array());
$components[] = $username_input;

$components[] = array(
                            "caption" => "RADIUS Reply",
                            "name" => "radiusReply",
                            "type" => "select",
                            "selected_value" => ((isset($radiusReply)) ? $radiusReply : $valid_radiusReplys[0]),
                            "options" => $valid_radiusReplys,
                            "tooltipText" => "Filter records with the selected RADIUS Reply"
                          );

$components = array_merge($components, $date_select_components);

// reset components IDs
for ($i = 0; $i < count($components); $i++) {
    $components[$i]['id'] = "id_" . rand();
}

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','LastConnectionAttempts'), 'action' => 'rep-lastconnect.php', 'method' => 'GET',
                         'icon' => 'person-lines-fill', 'img' => array( 'src' => 'static/images/icons/reportsLastConnection.png', ), 'form_components' => $components, );

$components = array();
$components = $date_select_components;

// reset components IDs
for ($i = 0; $i < count($components); $i++) {
    $components[$i]['id'] = "id_" . rand();
}

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','NewUsers'), 'action' => 'rep-newusers.php', 'method' => 'GET',
                         'icon' => 'person-lines-fill', 'img' => array( 'src' => 'static/images/icons/userList.gif', ), 'form_components' => $components, );

$components = array();

$components[] = $username_input;
$components = array_merge($components, $date_select_components);

$components[] = array(
                            "caption" => t('button','OrderBy'),
                            "name" => "orderBy",
                            "type" => "select",
                            "selected_value" => ((isset($orderBy)) ? $orderBy : array_keys($orderBy_options)[0]),
                            "options" => $orderBy_options,
                            "tooltipText" => "You can order the results by: " . implode(" or ", array_keys($orderBy_options)),
                          );

// reset components IDs
for ($i = 0; $i < count($components); $i++) {
    $components[$i]['id'] = "id_" . rand();
}

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','TopUser'), 'action' => 'rep-topusers.php', 'method' => 'GET',
                         'icon' => 'person-lines-fill', 'img' => array( 'src' => 'static/images/icons/reportsTopUsers.png', ), 'form_components' => $components, );

$descriptors2 = array();
$descriptors2[] = array( 'type' => 'link', 'label' => t('button','History'), 'href' => 'rep-history.php',
                         'icon' => 'clock-history', 'img' => array( 'src' => 'static/images/icons/reportsHistory.png', ), );

$sections = array();
$sections[] = array( 'title' => 'User Reports', 'descriptors' => $descriptors1 );
$sections[] = array( 'title' => 'Other Reports', 'descriptors' => $descriptors2 );

// add sections to menu
$menu = array(
                'title' => 'Reports',
                'sections' => $sections,
             );
