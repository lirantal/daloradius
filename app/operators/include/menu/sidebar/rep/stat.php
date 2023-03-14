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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/rep/status.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

// define descriptors
$descriptors1 = array();
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','ServerStatus'), 'href' => 'rep-stat-server.php',
                         'icon' => 'pc', 'img' => array( 'src' => 'static/images/icons/reportsStatus.png', ), );
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','ServicesStatus'), 'href' => 'rep-stat-services.php',
                         'icon' => 'server', 'img' => array( 'src' => 'static/images/icons/reportsStatus.png', ), );

$descriptors2 = array();
$descriptors2[] = array( 'type' => 'link', 'label' => 'CRON Status', 'href' => 'rep-stat-cron.php',
                         'icon' => 'calendar-date', 'img' => array( 'src' => 'static/images/icons/reportsStatus.png', ), );
$descriptors2[] = array( 'type' => 'link', 'label' => 'UPS Status', 'href' => 'rep-stat-ups.php',
                         'icon' => 'battery-charging', 'img' => array( 'src' => 'static/images/icons/reportsStatus.png', ), );
$descriptors2[] = array( 'type' => 'link', 'label' => 'RAID Status', 'href' => 'rep-stat-raid.php',
                         'icon' => 'hdd-stack-fill', 'img' => array( 'src' => 'static/images/icons/reportsStatus.png', ), );

$sections = array();
$sections[] = array( 'title' => 'Status', 'descriptors' => $descriptors1 );
$sections[] = array( 'title' => 'Extended Peripherals', 'descriptors' => $descriptors2 );

// add sections to menu
$menu = array(
                'title' => 'Status',
                'sections' => $sections,
             );
