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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/mng/rad-ippool.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $item;

include_once("include/management/populate_selectbox.php");
$menu_options = get_ippools();

// define descriptors
$descriptors1 = array();

$descriptors1[] = array( 'type' => 'link', 'label' => t('button','NewIPPool'), 'href' =>'mng-rad-ippool-new.php',
                         'icon' => 'plus-circle-fill', 'img' => array( 'src' => 'static/images/icons/groupsAdd.png', ), );

if (count($menu_options) > 0) {
    array_unshift($menu_options, "");

    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','ListIPPools'), 'href' => 'mng-rad-ippool-list.php',
                             'icon' => 'list-ul', 'img' => array( 'src' => 'static/images/icons/groupsList.png', ), );

    $components = array();
    $components[] = array(
                            "name" => "item",
                            "type" => "select",
                            "selected_value" => ((isset($item)) ? $item : ""),
                            "required" => true,
                            "options" => $menu_options,
                            "caption" => sprintf("%s - %s", t('all','PoolName'), t('all','IPAddress')),
                            "tooltipText" => sprintf("Please select a %s - %s pair", t('all','PoolName'), t('all','IPAddress')),
                          );

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','EditIPPool'), 'action' => 'mng-rad-ippool-edit.php', 'method' => 'GET',
                             'icon' => 'pencil-square', 'img' => array( 'src' => 'static/images/icons/groupsEdit.png', ), 'form_components' => $components, );

    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','RemoveIPPool'), 'href' => 'mng-rad-ippool-del.php',
                             'icon' => 'x-circle-fill', 'img' => array( 'src' => 'static/images/icons/groupsRemove.png', ), );
}


$sections = array();
$sections[] = array( 'title' => 'IP-Pools Management', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'Management',
                'sections' => $sections,
             );
