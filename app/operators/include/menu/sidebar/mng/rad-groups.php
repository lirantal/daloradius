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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/mng/rad-groups.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

$options_format = "%s: [%s %s %s]";

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $groupname;

include_once("include/management/populate_selectbox.php");
$menu_groups = get_groups();

// init select components
function get_select_options($item_table, $item_prefix) {
    global $options_format;

    include('../common/includes/db_open.php');

    $sql = sprintf("SELECT id, groupname, attribute, op, value FROM %s ORDER BY groupname, attribute DESC", $item_table);
    $res = $dbSocket->query($sql);

    $result = array();

    while ($row = $res->fetchrow()) {
        list($id, $groupname, $attribute, $op, $value) = $row;
        $key = $item_prefix . $id;
        $result[$key] = sprintf($options_format, $groupname, $attribute, $op, $value);
    }

    include('../common/includes/db_close.php');

    return $result;
}

$caption = sprintf($options_format, t('all','Groupname'), t('all','Attribute'), "op", t('all','Value'));
$radgroupreply_options = get_select_options($configValues['CONFIG_DB_TBL_RADGROUPREPLY'], "groupreply-");
$radgroupcheck_options = get_select_options($configValues['CONFIG_DB_TBL_RADGROUPCHECK'], "groupcheck-");

$menu_radgroupreply_select = array(
                                    'id' => 'random',
                                    'name' => 'item',
                                    'type' => 'select',
                                    'caption' => $caption,
                                    'options' => $radgroupreply_options,
                                    'disabled' => (count($radgroupreply_options) == 0),
                                  );

$menu_radgroupcheck_select = array(
                                    'id' => 'random',
                                    'name' => 'item',
                                    'type' => 'select',
                                    'caption' => $caption,
                                    'options' => $radgroupcheck_options,
                                    'disabled' => (count($radgroupcheck_options) == 0),
                                  );

// define descriptors
$descriptors1 = array();

$descriptors1[] = array( 'type' => 'link', 'label' => t('button','NewGroupCheck'), 'href' =>'mng-rad-groupcheck-new.php',
                         'img' => array( 'src' => 'static/images/icons/groupsAdd.png', ), );


if (count($menu_groups) > 0 && count($radgroupcheck_options) > 0) {
    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','ListGroupCheck'), 'href' => 'mng-rad-groupcheck-list.php',
                             'img' => array( 'src' => 'static/images/icons/groupsList.png', ), );

    $components = array();
    $components[] = array(
                            "id" => 'random',
                            "name" => "groupname",
                            "type" => "text",
                            "value" => ((isset($groupname)) ? $groupname : ""),
                            "required" => true,
                            "datalist" => (($autocomplete) ? $menu_groups : array()),
                            "tooltipText" => t('Tooltip','GroupName'),
                            "sidebar" => true,
                          );

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','SearchGroupCheck'), 'action' => 'mng-rad-groupcheck-search.php', 'method' => 'GET',
                             'img' => array( 'src' => 'static/images/icons/groupsList.png', ), 'form_components' => $components, );

    $components = array();
    $components[] = $menu_radgroupcheck_select;

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','EditGroupReply'), 'action' => 'mng-rad-groupcheck-edit.php', 'method' => 'GET',
                             'img' => array( 'src' => 'static/images/icons/groupsEdit.png', ), 'form_components' => $components, );
    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','RemoveGroupCheck'), 'href' => 'mng-rad-groupcheck-del.php',
                             'img' => array( 'src' => 'static/images/icons/groupsRemove.png', ), );
}

$descriptors2 = array();

$descriptors2[] = array( 'type' => 'link', 'label' => t('button','NewGroupReply'), 'href' =>'mng-rad-groupreply-new.php',
                         'img' => array( 'src' => 'static/images/icons/groupsAdd.png', ), );

if (count($menu_groups) > 0 && count($radgroupreply_options) > 0) {
    $descriptors2[] = array( 'type' => 'link', 'label' => t('button','ListGroupReply'), 'href' => 'mng-rad-groupreply-list.php',
                             'img' => array( 'src' => 'static/images/icons/groupsList.png', ), );

    $components = array();
    $components[] = array(
                            "id" => 'random',
                            "name" => "groupname",
                            "type" => "text",
                            "value" => ((isset($groupname)) ? $groupname : ""),
                            "required" => true,
                            "datalist" => (($autocomplete) ? $menu_groups : array()),
                            "tooltipText" => t('Tooltip','GroupName'),
                            "sidebar" => true,
                          );

    $descriptors2[] = array( 'type' => 'form', 'title' => t('button','SearchGroupReply'), 'action' => 'mng-rad-groupreply-search.php', 'method' => 'GET',
                             'img' => array( 'src' => 'static/images/icons/groupsList.png', ), 'form_components' => $components, );

    $components = array();
    $components[] = $menu_radgroupreply_select;

    $descriptors2[] = array( 'type' => 'form', 'title' => t('button','EditGroupReply'), 'action' => 'mng-rad-groupreply-edit.php', 'method' => 'GET',
                             'img' => array( 'src' => 'static/images/icons/groupsEdit.png', ), 'form_components' => $components, );
    $descriptors2[] = array( 'type' => 'link', 'label' => t('button','RemoveGroupReply'), 'href' => 'mng-rad-groupreply-del.php',
                             'img' => array( 'src' => 'static/images/icons/groupsRemove.png', ), );
}

$sections = array();
$sections[] = array( 'title' => 'Group Check Management', 'descriptors' => $descriptors1 );
$sections[] = array( 'title' => 'Group Reply Management', 'descriptors' => $descriptors2 );

// add sections to menu
$menu = array(
                'title' => 'Management',
                'sections' => $sections,
             );
