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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/mng/hs.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $name;

include_once("include/management/populate_selectbox.php");
$menu_datalist = get_hotspots();

// define descriptors
$descriptors1 = array();

$descriptors1[] = array( 'type' => 'link', 'label' => t('button','NewHotspot'), 'href' =>'mng-hs-new.php',
                         'icon' => 'plus-circle-fill', 'img' => array( 'src' => 'static/images/icons/userNew.gif', ), );

if (count($menu_datalist) > 0) {
    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','ListHotspots'), 'href' => 'mng-hs-list.php',
                             'icon' => 'list-ul', 'img' => array( 'src' => 'static/images/icons/userList.gif', ), );

    $components = array();
    $components[] = array(
                            "id" => 'random',
                            "name" => "name",
                            "type" => "text",
                            "value" => ((isset($name)) ? $name : ""),
                            "required" => true,
                            "datalist" => array(
                                                    'type' => 'traditional',
                                                    'options' => (($autocomplete) ? $menu_datalist : array()),
                                               ),
                            "tooltipText" => t('Tooltip','HotspotName'),
                            "caption" => t('all','HotSpot'),
                            "sidebar" => true
                          );

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','EditHotspot'), 'action' => 'mng-hs-edit.php', 'method' => 'GET',
                             'icon' => 'pencil-square', 'img' => array( 'src' => 'static/images/icons/userEdit.gif', ), 'form_components' => $components, );

    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','RemoveHotspot'), 'href' => 'mng-hs-del.php',
                             'icon' => 'x-circle-fill', 'img' => array( 'src' => 'static/images/icons/userRemove.gif', ), );
}


$sections = array();
$sections[] = array( 'title' => 'Hotspots Management', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'Management',
                'sections' => $sections,
             );
