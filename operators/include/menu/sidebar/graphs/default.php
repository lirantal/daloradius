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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/graphs/default.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $type, $size, $username, $logged_users_on_date;

include_once("include/management/populate_selectbox.php");
$username_options = get_users('CONFIG_DB_TBL_RADACCT');

$timeunit_options = array(
                            "daily" => t('all','Daily'),
                            "monthly" => t('all','Monthly'),
                            "yearly" => t('all','Yearly')
                         );

$sizeunit_options = array(
                            "megabytes" => t('all','Megabytes'),
                            "gigabytes" => t('all','Gigabytes')
                         );


$timeunit_select = array(
                            "name" => "type",
                            "type" => "select",
                            "selected_value" => ((isset($type)) ? $type : ""),
                            "options" => $timeunit_options,
                        );

$sizeunit_select = array(
                            "name" => "size",
                            "type" => "select",
                            "selected_value" => ((isset($size)) ? $size : ""),
                            "options" => $sizeunit_options,
                          );

$username_input = array(
                            "name" => "username",
                            "type" => "text",
                            "value" => ((isset($username)) ? $username : ""),
                            "required" => true,
                            "datalist" => (($autocomplete) ? $username_options : array()),
                            "tooltipText" => t('Tooltip','Username'),
                            "sidebar" => true
                       );


// define descriptors
$descriptors1 = array();

$components = array();
$components[] = $username_input;
$components[] = $timeunit_select;

$id1 = "id_" . rand();
$components[0]['id'] = $id1;
$components[1]['id'] = "id_" . rand();

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','UserLogins'), 'action' => 'graphs-overall_logins.php', 'method' => 'GET',
                         'icon' => 'graph-up', 'img' => array( 'src' => 'static/images/icons/graphsGeneral.gif', ), 'form_components' => $components, );

$components[] = $sizeunit_select;

$components[0]['id'] = "id_" . rand();
unset($components[0]["datalist"]);
// this means that this component should use the datalist
// that has been previously loaded by the component that has $id1 as its id
$components[0]['shared_datalist'] = $id1;
$components[1]['id'] = "id_" . rand();
$components[2]['id'] = "id_" . rand();

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','UserDownloads'), 'action' => 'graphs-overall_download.php', 'method' => 'GET',
                         'icon' => 'graph-up', 'img' => array( 'src' => 'static/images/icons/graphsGeneral.gif', ), 'form_components' => $components, );

$components[0]['id'] = "id_" . rand();
// this means that this component should use the datalist
// that has been previously loaded by the component that has $id1 as its id
$components[0]['shared_datalist'] = $id1;
$components[1]['id'] = "id_" . rand();
$components[2]['id'] = "id_" . rand();

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','UserUploads'), 'action' => 'graphs-overall_upload.php', 'method' => 'GET',
                         'icon' => 'graph-up', 'img' => array( 'src' => 'static/images/icons/graphsGeneral.gif', ), 'form_components' => $components, );

$components = array();
$components[] = $timeunit_select;

$components[0]['id'] = "id_" . rand();

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','TotalLogins'), 'action' => 'graphs-alltime_logins.php', 'method' => 'GET',
                         'icon' => 'graph-up', 'img' => array( 'src' => 'static/images/icons/graphsGeneral.gif', ), 'form_components' => $components, );

$components[] = $sizeunit_select;

$components[0]['id'] = "id_" . rand();
$components[1]['id'] = "id_" . rand();

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','TotalTraffic'), 'action' => 'graphs-alltime_traffic_compare.php', 'method' => 'GET',
                         'icon' => 'graph-up', 'img' => array( 'src' => 'static/images/icons/graphsGeneral.gif', ), 'form_components' => $components, );

$components = array();
$components[] = array(
                            "name" => "logged_users_on_date",
                            "type" => "date",
                            "value" => ((isset($logged_users_on_date)) ? $logged_users_on_date : date("Y-m-d")),
                            "caption" => t('all','Date'),
                            "tooltipText" => t('Tooltip','Date'),
                            "sidebar" => true,
                     );

$components[0]['id'] = "id_" . rand();

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','LoggedUsers'), 'action' => 'graphs-logged_users.php', 'method' => 'GET',
                         'icon' => 'graph-up', 'img' => array( 'src' => 'static/images/icons/graphsGeneral.gif', ), 'form_components' => $components, );

$sections = array();
$sections[] = array( 'title' => 'User Charts', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'Charts',
                'sections' => $sections,
             );
