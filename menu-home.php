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
if (strpos($_SERVER['PHP_SELF'], '/menu-home.php') !== false) {
    header("Location: index.php");
    exit;
}

include_once("lang/main.php");

// define descriptors
$descriptors1 = array();
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','ServerStatus'), 'href' => 'rep-stat-server.php', 
                         'img' => array( 'src' => 'static/images/icons/reportsStatus.png', ), );
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','ServicesStatus'), 'href' => 'rep-stat-services.php',
                         'img' => array( 'src' => 'static/images/icons/reportsStatus.png', ), );
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','LastConnectionAttempts'), 'href' => 'rep-lastconnect.php',
                         'img' => array( 'src' => 'static/images/icons/userList.gif', ), );

$descriptors2 = array();
$descriptors2[] = array( 'type' => 'link', 'label' => t('button','RadiusLog'), 'href' => 'rep-logs-radius.php', );
$descriptors2[] = array( 'type' => 'link', 'label' => t('button','SystemLog'), 'href' => 'rep-logs-system.php', );

$descriptors3 = array();
$descriptors3[] = array(
                            'type' => 'textarea',
                            'content' => sprintf('daloRADIUS - RADIUS Management<br>%s / %s',
                                                 t('all', 'daloRADIUSVersion'), $configValues['DALORADIUS_DATE']),
                            'readmore' => array( 'href' => 'https://github.com/lirantal/daloradius',
                                                 'title' => 'Read More',
                                                 'label' => 'Read More &raquo;',
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

menu_print($menu);
