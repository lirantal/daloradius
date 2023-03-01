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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/home/default.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}


include_once('../common/includes/config_read.php');

// define descriptors
$descriptors1 = array();
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','ServerStatus'), 'href' => 'rep-stat-server.php', 
                         'icon' => 'pc', 'img' => array( 'src' => 'static/images/icons/reportsStatus.png', ), );
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','ServicesStatus'), 'href' => 'rep-stat-services.php',
                         'icon' => 'server', 'img' => array( 'src' => 'static/images/icons/reportsStatus.png', ), );
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','LastConnectionAttempts'), 'href' => 'rep-lastconnect.php',
                         'icon' => 'clock-history', 'img' => array( 'src' => 'static/images/icons/userList.gif', ), );

$descriptors2 = array();
$descriptors2[] = array( 'type' => 'link', 'label' => t('button','RadiusLog'), 'href' => 'rep-logs-radius.php',
                         'icon' => 'file-earmark-text', );
$descriptors2[] = array( 'type' => 'link', 'label' => t('button','SystemLog'), 'href' => 'rep-logs-system.php',
                         'icon' => 'file-earmark-text', );

$descriptors3 = array();

$content = "";
if (isset($configValues['DALORADIUS_VERSION'])) {
    $content .= "<br>version " . $configValues['DALORADIUS_VERSION'];
    if (!empty($configValues['DALORADIUS_DATE'])) {
        $content .= " / " . $configValues['DALORADIUS_DATE'];
    }
}

$descriptors3[] = array(
                            'type' => 'textarea',
                            'content' => sprintf('daloRADIUS - RADIUS Management%s', $content),
                            'readmore' => array( 'href' => 'https://github.com/lirantal/daloradius',
                                                 'title' => 'Read More',
                                                 'label' => 'Read More',
                                               ),
                      );


$sections = array();
$sections[] = array( 'title' => 'Status', 'descriptors' => $descriptors1 );
$sections[] = array( 'title' => 'Logs', 'descriptors' => $descriptors2 );
$sections[] = array( 'title' => 'Support', 'descriptors' => $descriptors3 );

// add sections to menu
$menu = array(
                'title' => 'Home',
                'sections' => $sections,
             );
