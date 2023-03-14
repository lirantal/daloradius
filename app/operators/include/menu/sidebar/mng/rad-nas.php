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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/mng/rad-nas.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $nasname;

include_once("include/management/populate_selectbox.php");
$menu_nasnames = get_nas_names();

// define descriptors
$descriptors1 = array();

$descriptors1[] = array( 'type' => 'link', 'label' => t('button','NewNAS'),
                         'href' =>'mng-rad-nas-new.php', 'icon' => 'plus-circle-fill', );

if (count($menu_nasnames) > 0) {
    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','ListNAS'),
                             'href' => 'mng-rad-nas-list.php', 'icon' => 'list-ul', );

    $components = array();
    $components[] = array(
                            "id" => 'random',
                            "name" => "nasname",
                            "type" => "text",
                            "value" => ((isset($nasname)) ? $nasname : ""),
                            "required" => true,
                            "datalist" => array(
                                                    'type' => 'traditional',
                                                    'options' => (($autocomplete) ? $menu_nasnames : array()),
                                               ),
                            "tooltipText" => "Please insert a valid " . t('all','NasIPHost'),
                            "caption" => t('all','NasIPHost'),
                            "sidebar" => true,
                          );

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','EditNAS'), 'action' => 'mng-rad-nas-edit.php',
                             'method' => 'GET', 'icon' => 'pencil-square', 'form_components' => $components, );

    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','RemoveNAS'),
                             'href' => 'mng-rad-nas-del.php', 'icon' => 'x-circle-fill', );
}

$sections = array();
$sections[] = array( 'title' => 'NAS Management', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'Management',
                'sections' => $sections,
             );
