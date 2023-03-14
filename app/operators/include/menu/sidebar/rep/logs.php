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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/rep/logs.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

global $count, $filter;

$count_options = array(
                            '20' => '20 Lines',
                            '50' => '50 Lines',
                            '100' => '100 Lines',
                            '500' => '500 Lines',
                            '1000' => '1000 Lines'
                       );

$count_select = array(
                            "id" => 'random',
                            "name" => "count",
                            "type" => "select",
                            "selected_value" => ((isset($count)) ? $count : ""),
                            "options" => $count_options,
                            "integer_value" => true,
                            "caption" => "Lines count",
                            "tooltipText" => "Show only the selected number of lines",
                        );

$daloradius_options = array(
                                "",
                                "QUERY" => "Query Only",
                                "NOTICE" => "Notice Only",
                                "INSERT" => "SQL INSERT Only",
                                "SELECT" => "SQL SELECT Only",
                           );

$radius_options = array(
                            "",
                            "Auth" => "Auth Only",
                            "Info" => "Info Only",
                            "Error" => "Error Only",
                       );

$descriptors1 = array();

$components = array();
$components[] = array(
                        "id" => 'random',
                        "name" => "filter",
                        "type" => "select",
                        "selected_value" => ((isset($filter)) ? $filter : ""),
                        "options" => $daloradius_options,
                        "caption" => "Filter",
                     );

$components[] = $count_select;

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','daloRADIUSLog'),
                         'action' => 'rep-logs-daloradius.php', 'method' => 'GET', 'form_components' => $components, );


$components = array();
$components[] = array(
                        "id" => 'random',
                        "name" => "filter",
                        "type" => "select",
                        "selected_value" => ((isset($filter)) ? $filter : ""),
                        "options" => $radius_options,
                        "caption" => "Filter",
                     );

$components[] = $count_select;

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','RadiusLog'),
                         'action' => 'rep-logs-radius.php', 'method' => 'GET', 'form_components' => $components, );


$components = array();
$components[] = array(
                        "id" => 'random',
                        "name" => "filter",
                        "type" => "text",
                        "value" => ((isset($filter)) ? $filter : ""),
                        "caption" => "Filter",
                        "tooltipText" => t('Tooltip', 'Filter'),
                        "sidebar" => true,
                     );

$components[] = $count_select;

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','SystemLog'),
                         'action' => 'rep-logs-system.php', 'method' => 'GET', 'form_components' => $components, );

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','BootLog'),
                         'action' => 'rep-logs-boot.php', 'method' => 'GET', 'form_components' => $components, );

$sections = array();
$sections[] = array( 'title' => 'Log Files', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'Logs',
                'sections' => $sections,
             );
