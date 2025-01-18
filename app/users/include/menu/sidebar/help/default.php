<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
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
 * Authors:    Liran Tal <liran@lirantal.com>
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
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','Preferences'), 'href' => 'pref-main.php', 'icon' => 'gear-fill', );
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','Accounting'), 'href' => 'acct-main.php', 'icon' => 'person-lines-fill', );
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','Graphs'), 'href' => 'graphs-main.php', 'icon' => 'bar-chart-line-fill', );

$descriptors2 = array();
$descriptors2[] = array(
                            'type' => 'textarea',
                            'content' => "Need help? Contact us!",
                            'readmore' => array( 'href' => 'help-main.php',
                                                 'title' => 'Read More',
                                                 'label' => 'Read More',
                                               ),
                      );


$sections = array();
$sections[] = array( 'title' => 'Quick Navigation', 'descriptors' => $descriptors1 );
$sections[] = array( 'title' => 'Support', 'descriptors' => $descriptors2 );

// add sections to menu
$menu = array(
                'title' => 'Home',
                'sections' => $sections,
             );
