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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/mng/rad-realms.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $realmname, $item;

include_once("include/management/populate_selectbox.php");
$menu_options = get_realms();

// define descriptors
$descriptors1 = array();

$descriptors1[] = array( 'type' => 'link', 'label' => t('button','NewRealm'), 'href' =>'mng-rad-realms-new.php',
                         'icon' => 'plus-circle-fill', 'img' => array( 'src' => 'static/images/icons/groupsAdd.png', ), );

if (count($menu_options) > 0) {
    array_unshift($menu_options, "");

    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','ListRealms'), 'href' => 'mng-rad-realms-list.php',
                             'icon' => 'list-ul', 'img' => array( 'src' => 'static/images/icons/groupsList.png', ), );

    $components = array();
    $components[] = array(
                            "name" => "realmname",
                            "type" => "select",
                            "selected_value" => ((isset($realmname)) ? $realmname : ""),
                            "required" => true,
                            "options" => $menu_options,
                            "caption" => t('all','Realm'),
                            "tooltipText" => "Please select a " . t('all','Realm'),
                          );

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','EditRealm'), 'action' => 'mng-rad-realms-edit.php', 'method' => 'GET',
                             'icon' => 'pencil-square', 'img' => array( 'src' => 'static/images/icons/groupsEdit.png', ), 'form_components' => $components, );

    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','RemoveRealm'), 'href' => 'mng-rad-realms-del.php',
                             'icon' => 'x-circle-fill', 'img' => array( 'src' => 'static/images/icons/groupsRemove.png', ), );
}


$menu_options = get_proxies();

// define descriptors
$descriptors2 = array();

$descriptors2[] = array( 'type' => 'link', 'label' => t('button','NewProxy'), 'href' =>'mng-rad-proxys-new.php',
                         'icon' => 'plus-circle-fill', 'img' => array( 'src' => 'static/images/icons/groupsAdd.png', ), );

if (count($menu_options) > 0) {
    array_unshift($menu_options, "");

    $descriptors2[] = array( 'type' => 'link', 'label' => t('button','ListProxys'), 'href' => 'mng-rad-proxys-list.php',
                             'icon' => 'list-ul', 'img' => array( 'src' => 'static/images/icons/groupsList.png', ), );

    $components = array();
    $components[] = array(
                            "name" => "item",
                            "type" => "select",
                            "selected_value" => ((isset($item)) ? $item : ""),
                            "required" => true,
                            "options" => $menu_options,
                            "caption" => t('all','Proxy'),
                            "tooltipText" => "Please select a " . t('all','Proxy'),
                          );

    $descriptors2[] = array( 'type' => 'form', 'title' => t('button','EditProxy'), 'action' => 'mng-rad-proxys-edit.php', 'method' => 'GET',
                             'icon' => 'pencil-square', 'img' => array( 'src' => 'static/images/icons/groupsEdit.png', ), 'form_components' => $components, );

    $descriptors2[] = array( 'type' => 'link', 'label' => t('button','RemoveProxy'), 'href' => 'mng-rad-proxys-del.php',
                             'icon' => 'x-circle-fill', 'img' => array( 'src' => 'static/images/icons/groupsRemove.png', ), );
}

$sections = array();
$sections[] = array( 'title' => 'Realms Management', 'descriptors' => $descriptors1 );
$sections[] = array( 'title' => 'Proxies Management', 'descriptors' => $descriptors2 );

// add sections to menu
$menu = array(
                'title' => 'Management',
                'sections' => $sections,
             );
