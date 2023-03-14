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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/mng/rad-hunt.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $item;

include_once("include/management/populate_selectbox.php");
$menu_options = get_huntgroups();

// define descriptors
$descriptors1 = array();

$descriptors1[] = array( 'type' => 'link', 'label' => t('button','NewHG'), 'href' =>'mng-rad-hunt-new.php',
                         'icon' => 'plus-circle-fill', 'img' => array( 'src' => 'static/images/icons/groupsAdd.png', ), );

if (count($menu_options) > 0) {
    array_unshift($menu_options, "");

    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','ListHG'), 'href' => 'mng-rad-hunt-list.php',
                             'icon' => 'list-ul', 'img' => array( 'src' => 'static/images/icons/groupsList.png', ), );

    $components = array();
    $components[] = array(
                            "id" => 'random',
                            "name" => "item",
                            "type" => "select",
                            "selected_value" => ((isset($item)) ? $item : ""),
                            "required" => true,
                            "options" => $menu_options,
                            "caption" => "Huntgroup",
                            "tooltipText" => "Please select a Huntgroup",
                          );

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','EditHG'), 'action' => 'mng-rad-hunt-edit.php', 'method' => 'GET',
                             'icon' => 'pencil-square', 'img' => array( 'src' => 'static/images/icons/groupsEdit.png', ), 'form_components' => $components, );

    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','RemoveHG'), 'href' => 'mng-rad-hunt-del.php',
                             'icon' => 'x-circle-fill', 'img' => array( 'src' => 'static/images/icons/groupsRemove.png', ), );
}


$sections = array();
$sections[] = array( 'title' => 'Huntgroups Management', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'Management',
                'sections' => $sections,
             );
