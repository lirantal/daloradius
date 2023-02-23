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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/graphs/default.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

global $type, $size;

$timeunit_options = array(
                            "daily" => t('all','Daily'),
                            "monthly" => t('all','Monthly'),
                            "yearly" => t('all','Yearly')
                         );

$sizeunit_options = array(
                            "megabytes" => t('all','Megabytes'),
                            "gigabytes" => t('all','Gigabytes')
                         );


$timeunit_select = array(
                            "name" => "type",
                            "type" => "select",
                            "selected_value" => ((isset($type)) ? $type : ""),
                            "options" => array_keys($timeunit_options),
                        );

$sizeunit_select = array(
                            "name" => "size",
                            "type" => "select",
                            "selected_value" => ((isset($size)) ? $size : ""),
                            "options" => array_keys($sizeunit_options),
                          );


// define descriptors
$descriptors1 = array();

$components = array();
$components[] = $timeunit_select;

for ($i = 0; $i < count($components); $i++) {
    $components[$i]['id'] = "id_" . rand();
}

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','UserLogins'), 'action' => 'graphs-overall_logins.php', 'method' => 'GET',
                         'icon' => 'graph-up', 'form_components' => $components, );

$components[] = $sizeunit_select;

for ($i = 0; $i < count($components); $i++) {
    $components[$i]['id'] = "id_" . rand();
}

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','UserDownloads'), 'action' => 'graphs-overall_download.php', 'method' => 'GET',
                         'icon' => 'graph-up', 'form_components' => $components, );

for ($i = 0; $i < count($components); $i++) {
    $components[$i]['id'] = "id_" . rand();
}

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','UserUploads'), 'action' => 'graphs-overall_upload.php', 'method' => 'GET',
                         'icon' => 'graph-up', 'form_components' => $components, );

$sections = array();
$sections[] = array( 'title' => 'User Charts', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'Charts',
                'sections' => $sections,
             );
