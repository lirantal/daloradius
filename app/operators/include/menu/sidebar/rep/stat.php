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
$descriptors1 = [];
$descriptors1[] = [ 'type' => 'link', 'label' => t('button','ServerStatus'), 'href' => 'rep-stat-server.php', 'icon' => 'pc', ];
$descriptors1[] = [ 'type' => 'link', 'label' => t('button','ServicesStatus'), 'href' => 'rep-stat-services.php', 'icon' => 'server', ];

$descriptors2 = [];
$descriptors2[] = [ 'type' => 'link', 'label' => 'CRON Status', 'href' => 'config-crontab.php', 'icon' => 'calendar-date', ];
$descriptors2[] = [ 'type' => 'link', 'label' => 'UPS Status', 'href' => 'rep-stat-ups.php', 'icon' => 'battery-charging', ];
$descriptors2[] = [ 'type' => 'link', 'label' => 'RAID Status', 'href' => 'rep-stat-raid.php', 'icon' => 'hdd-stack-fill', ];

$sections = [];
$sections[] = [ 'title' => 'Status', 'descriptors' => $descriptors1 ];
$sections[] = [ 'title' => 'Extended Peripherals', 'descriptors' => $descriptors2 ];

// add sections to menu
$menu = [ 'title' => 'Status', 'sections' => $sections, ];
