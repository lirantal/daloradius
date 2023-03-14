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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/rep/batch.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

global $batch_name;

include_once("include/management/populate_selectbox.php");
$menu_datalist = get_batch_names();

// define descriptors
$descriptors1 = array();
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','BatchHistory'), 'href' => 'rep-batch-list.php',
                         'icon' => 'clock-history', );

$components[] = array(
                        "id" => 'random',
                        "name" => "batch_name",
                        "type" => "text",
                        "value" => ((isset($batch_name)) ? $batch_name : ""),
                        "required" => true,
                        "datalist" => array(
                                                'type' => 'traditional',
                                                'options' => (($autocomplete) ? $menu_datalist : array()),
                                           ),
                        "tooltipText" => t('Tooltip','BatchName'),
                        "caption" => t('all','BatchName'),
                        "sidebar" => true,
                      );

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','BatchDetails'), 'action' => 'rep-batch-details.php', 'method' => 'GET',
                         'icon' => 'info-circle', 'form_components' => $components, );

$sections = array();
$sections[] = array( 'title' => 'List', 'descriptors' => $descriptors1 );


// add sections to menu
$menu = array(
                'title' => 'Batch Users',
                'sections' => $sections,
             );
