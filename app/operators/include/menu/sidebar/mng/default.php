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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/mng/default.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $username;

include_once("include/management/populate_selectbox.php");
$menu_users = get_users('CONFIG_DB_TBL_RADCHECK');


// define descriptors
$descriptors1 = array();

$descriptors1[] = array( 'type' => 'link', 'label' => t('button','NewUser'), 'href' =>'mng-new.php',
                         'icon' => 'person-fill-add', 'img' => array( 'src' => 'static/images/icons/userNew.gif', ), );
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','NewUserQuick'), 'href' =>'mng-new-quick.php',
                         'icon' => 'person-fill-add', 'img' => array( 'src' => 'static/images/icons/userNew.gif', ), );

if (count($menu_users) > 0) {
    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','ListUsers'), 'href' => 'mng-list-all.php',
                             'icon' => 'person-lines-fill', 'img' => array( 'src' => 'static/images/icons/userList.gif', ), );

    $components = array();
    $components[] = array(
                            // this will produce a random id
                            "id" => 'random',
                            "name" => "username",
                            "type" => "text",
                            "value" => ((isset($username)) ? $username : ""),
                            "required" => true,
                            "datalist" => array(
                                                    'type' => 'ajax',
                                                    'url' => 'library/ajax/json_api.php',
                                                    'search_param' => 'username',
                                                    'params' => array(
                                                                        'datatype' => 'usernames',
                                                                        'action' => 'list',
                                                                        'table' => 'CONFIG_DB_TBL_RADCHECK',
                                                                     ),
                                               ),
                            "tooltipText" => t('Tooltip','usernameTooltip'),
                            "caption" => t('all','Username'),
                            "sidebar" => true,
                          );

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','EditUser'), 'action' => 'mng-edit.php', 'method' => 'GET',
                             'icon' => 'person-fill-gear', 'img' => array( 'src' => 'static/images/icons/userEdit.gif', ), 'form_components' => $components, );

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','SearchUsers'), 'action' => 'mng-search.php', 'method' => 'GET',
                             'icon' => 'search', 'img' => array( 'src' => 'static/images/icons/userSearch.gif', ), 'form_components' => $components, );

    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','RemoveUsers'), 'href' => 'mng-del.php',
                             'icon' => 'person-fill-x', 'img' => array( 'src' => 'static/images/icons/userRemove.gif', ), );
}

$descriptors2 = array();
$descriptors2[] = array( 'type' => 'link', 'label' => t('button','ImportUsers'), 'href' =>'mng-import-users.php',
                         'icon' => 'upload', 'img' => array( 'src' => 'static/images/icons/userNew.gif', ), );
$sections = array();
$sections[] = array( 'title' => 'Users Management', 'descriptors' => $descriptors1 );
$sections[] = array( 'title' => 'Extended Capabilities', 'descriptors' => $descriptors2 );

// add sections to menu
$menu = array(
                'title' => 'Management',
                'sections' => $sections,
             );
