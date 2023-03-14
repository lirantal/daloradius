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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/mng/rad-profiles.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $profile_name;

include_once("include/management/populate_selectbox.php");
$menu_options = get_groups();

// define descriptors
$descriptors1 = array();

$descriptors1[] = array( 'type' => 'link', 'label' => t('button','NewProfile'), 'href' =>'mng-rad-profiles-new.php',
                         'icon' => 'plus-circle-fill', 'img' => array( 'src' => 'static/images/icons/groupsAdd.png', ), );

if (count($menu_options) > 0) {
    array_unshift($menu_options, "");

    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','ListProfiles'), 'href' => 'mng-rad-profiles-list.php',
                             'icon' => 'list-ul', 'img' => array( 'src' => 'static/images/icons/groupsList.png', ), );

    $components = array();
    $components[] = array(
                            "name" => "profile_name",
                            "type" => "select",
                            "selected_value" => ((isset($profile_name)) ? $profile_name : ""),
                            "options" => $menu_options,
                            "caption" => t('all','Profile'),
                            "tooltipText" => "Please select a " . t('all','Profile'),
                          );

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','EditProfile'), 'action' => 'mng-rad-profiles-edit.php', 'method' => 'GET',
                             'icon' => 'pencil-square', 'img' => array( 'src' => 'static/images/icons/groupsEdit.png', ), 'form_components' => $components, );

    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','DuplicateProfile'), 'href' =>'mng-rad-profiles-duplicate.php',
                             'icon' => 'gear', 'img' => array( 'src' => 'static/images/icons/groupsEdit.png', ), );

    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','RemoveProfile'), 'href' =>'mng-rad-profiles-del.php',
                             'icon' => 'x-circle-fill', 'img' => array( 'src' => 'static/images/icons/groupsRemove.png', ), );
}

$sections = array();
$sections[] = array( 'title' => 'Profiles Management', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'Management',
                'sections' => $sections,
             );
