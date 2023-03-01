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

if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/config/operators.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $operator_username;

include_once("include/management/populate_selectbox.php");
$menu_datalist = get_operators();

// define descriptors
$descriptors1 = array();

$descriptors1[] = array( 'type' => 'link', 'label' => t('button','NewOperator'), 'href' =>'config-operators-new.php',
                         'icon' => 'person-fill-add', 'img' => array( 'src' => 'static/images/icons/userNew.gif', ), );

if (count($menu_datalist) > 0) {
    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','ListOperators'), 'href' => 'config-operators-list.php',
                             'icon' => 'person-lines-fill', 'img' => array( 'src' => 'static/images/icons/userList.gif', ), );

    $components = array();
    $components[] = array(
                            "name" => "operator_username",
                            "type" => "text",
                            "value" => ((isset($operator_username)) ? $operator_username : ""),
                            "required" => true,
                            "datalist" => (($autocomplete) ? $menu_datalist : array()),
                            "tooltipText" => t('Tooltip','OperatorName'),
                            "caption" => t('all','Operator'),
                          );
    
    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','EditOperator'), 'action' => 'config-operators-edit.php', 'method' => 'GET',
                             'icon' => 'person-fill-gear', 'img' => array( 'src' => 'static/images/icons/userEdit.gif', ), 'form_components' => $components, );
                             
    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','RemoveOperator'), 'href' => 'config-operators-del.php',
                             'icon' => 'person-fill-x', 'img' => array( 'src' => 'static/images/icons/userRemove.gif', ), );
}


$sections = array();
$sections[] = array( 'title' => 'Operators Management', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'Configuration',
                'sections' => $sections,
             );
