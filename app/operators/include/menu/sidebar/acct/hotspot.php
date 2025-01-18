<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
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
 * Authors:    Liran Tal <liran@lirantal.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/acct/hotspot.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

global $hotspot;

include_once("include/management/populate_selectbox.php");
$menu_options = get_hotspots();
array_unshift($menu_options, "");

// define descriptors
$descriptors1 = array();

$components = array();
$components[] = array(
                        "id" => 'random',
                        "name" => "hotspot[]",
                        "type" => "select",
                        "selected_value" => ((isset($hotspot)) ? $hotspot : ""),
                        "options" => $menu_options,
                        "caption" => t('all','HotSpots'),
                        "tooltipText" => "Please select one or multiple " . t('all','HotSpots'),
                        "multiple" => true,
                        "size" => 5,
                        "show_controls" => true,
                      );

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','HotspotAccounting'),
                         'action' => 'acct-hotspot-accounting.php', 'method' => 'GET',
                         'icon' => 'router-fill', 'form_components' => $components, );

$descriptors1[] = array( 'type' => 'link', 'label' => t('button','HotspotsComparison'),
                         'href' => 'acct-hotspot-compare.php', 'icon' => 'router-fill', );

$sections = array();
$sections[] = array( 'title' => 'Hotspots Accounting', 'descriptors' => $descriptors1 );


// add sections to menu
$menu = array(
                'title' => 'Accounting',
                'sections' => $sections,
             );
