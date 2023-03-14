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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/mng/rad-attributes.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $vendor, $attribute;

include_once("include/management/populate_selectbox.php");
$menu_vendors = get_vendors();
$menu_attributes = get_attributes();


// define descriptors
$descriptors1 = array();

$descriptors1[] = array( 'type' => 'link', 'label' => t('button','NewVendorAttribute'), 'href' =>'mng-rad-attributes-new.php',
                         'icon' => 'plus-circle-fill', 'img' => array( 'src' => 'static/images/icons/groupsAdd.png', ), );

if (count($menu_vendors) > 0 && count($menu_attributes) > 0) {
    array_unshift($menu_vendors, "");

    $components = array();
    $components[] = array(
                            "name" => "vendor",
                            "type" => "select",
                            "selected_value" => ((isset($vendor)) ? $vendor : ""),
                            "required" => true,
                            "options" => $menu_vendors,
                            "caption" => t('all','VendorName'),
                            "tooltipText" => t('Tooltip','VendorName'),
                            "sidebar" => true,
                          );

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','ListAttributesforVendor'), 'action' => 'mng-rad-attributes-list.php', 'method' => 'GET',
                             'icon' => 'list-ul', 'img' => array( 'src' => 'static/images/icons/groupsList.png', ), 'form_components' => $components, );

        $components[] = array(
                            "name" => "attribute",
                            "type" => "text",
                            "value" => ((isset($attribute)) ? $attribute : ""),
                            "required" => true,
                            "datalist" => array(
                                                    'type' => 'traditional',
                                                    'options' => $menu_attributes,
                                               ),
                            "tooltipText" => t('Tooltip','AttributeName'),
                            "sidebar" => true,
                          );

    // we fix the components[1] id
    $id1 = "id_" . rand();
    $components[1]['id'] = $id1;

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','EditVendorAttribute'), 'action' => 'mng-rad-attributes-edit.php', 'method' => 'GET',
                             'icon' => 'pencil-square', 'img' => array( 'src' => 'static/images/icons/groupsEdit.png', ), 'form_components' => $components, );

    $components = array();
    $components[] = array(
                            "id" => 'random',
                            "name" => "attribute",
                            "type" => "text",
                            "value" => ((isset($attribute)) ? $attribute : ""),
                            "required" => true,
                            "datalist" => array(
                                                    'type' => 'shared',
                                                    'id' => $id1,
                                               ),
                            "tooltipText" => t('Tooltip','AttributeName'),
                            "sidebar" => true,
                          );

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','SearchVendorAttribute'), 'action' =>'mng-rad-attributes-search.php', 'method' => 'GET',
                             'icon' => 'search', 'form_components' => $components, );

    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','RemoveVendorAttribute'), 'href' => 'mng-rad-attributes-del.php',
                             'icon' => 'x-circle-fill', 'img' => array( 'src' => 'static/images/icons/groupsRemove.png', ), );
}

$descriptors2 = array();
$descriptors2[] = array( 'type' => 'link', 'label' => t('button','ImportVendorDictionary'), 'href' =>'mng-rad-attributes-import.php',
                         'icon' => 'upload', 'img' => array( 'src' => 'static/images/icons/groupsAdd.png', ), );

$sections = array();
$sections[] = array( 'title' => 'Attributes Management', 'descriptors' => $descriptors1 );
$sections[] = array( 'title' => 'Extended Capabilities', 'descriptors' => $descriptors2 );

// add sections to menu
$menu = array(
                'title' => 'Management',
                'sections' => $sections,
             );
