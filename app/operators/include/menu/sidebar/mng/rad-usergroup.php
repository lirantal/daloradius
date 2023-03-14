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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/mng/rad-usergroup.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $username, $current_groupname;

include_once("include/management/populate_selectbox.php");
$menu_usernames = get_users_that_have_groups();
$menu_groupnames = get_groups_that_have_users();

// define descriptors
$descriptors1 = array();

$descriptors1[] = array( 'type' => 'link', 'label' => t('button','NewUserGroup'), 'href' =>'mng-rad-usergroup-new.php',
                         'icon' => 'person-fill-add', 'img' => array( 'src' => 'static/images/icons/userNew.gif', ), );

if (count($menu_usernames) > 0) {
    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','ListUserGroup'), 'href' => 'mng-rad-usergroup-list.php',
                             'icon' => 'person-lines-fill', 'img' => array( 'src' => 'static/images/icons/userList.gif', ), );

    $components = array();
    $components[] = array(
                            "name" => "username",
                            "type" => "text",
                            "value" => ((isset($username)) ? $username : ""),
                            "required" => true,
                            "datalist" => array(
                                                    'type' => 'traditional',
                                                    'options' => (($autocomplete) ? $menu_usernames : array()),
                                               ),
                            "tooltipText" => t('Tooltip','Username'),
                            "caption" => t('all','Username'),
                            "sidebar" => true,
                          );

    // we fix the components[0] id
    $id0 = "id_" . rand();
    $components[0]['id'] = $id0;
    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','ListUsersGroup'), 'action' => 'mng-rad-usergroup-list-user.php', 'method' => 'GET',
                             'icon' => 'person-lines-fill', 'img' => array( 'src' => 'static/images/icons/userList.gif', ), 'form_components' => $components, );

    $components[] = array(
                            "id" => 'random',
                            "name" => "current_group",
                            "type" => "text",
                            "value" => ((isset($current_groupname)) ? $current_groupname : ""),
                            "required" => true,
                            "datalist" => array(
                                                    'type' => 'traditional',
                                                    'options' => (($autocomplete) ? $menu_groupnames : array()),
                                               ),
                            "tooltipText" => t('Tooltip','GroupName'),
                            "caption" => t('all','Groupname'),
                            "sidebar" => true,
                          );

    // in order to reuse it, we reset the components[0] id
    $components[0]['id'] = 'random';
    // we plan to share datalist related to components[0]
    // with this new component (that has a random id)
    $components[0]["datalist"] = array(
                                        'type' => 'shared',
                                        'id' => $id0,
                                      );

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','EditUserGroup'), 'action' => 'mng-rad-usergroup-edit.php', 'method' => 'GET',
                             'icon' => 'pencil-square', 'img' => array( 'src' => 'static/images/icons/userList.gif', ), 'form_components' => $components, );

    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','RemoveUserGroup'), 'href' => 'mng-rad-usergroup-del.php',
                             'icon' => 'person-fill-x', 'img' => array( 'src' => 'static/images/icons/userRemove.gif', ), );
}

$sections = array();
$sections[] = array( 'title' => 'User-Group Management', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'Management',
                'sections' => $sections,
             );
