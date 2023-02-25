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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/gis/default.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

// define descriptors
$descriptors1 = array();
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','ViewMAP'), 'href' => 'gis-viewmap.php',
                         'icon' => 'globe-europe-africa', );
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','EditMAP'), 'href' => 'gis-editmap.php',
                         'icon' => 'geo-alt-fill', );


$sections = array();
$sections[] = array( 'title' => 'GIS Mapping', 'descriptors' => $descriptors1 );


// add sections to menu
$menu = array(
                'title' => 'GIS',
                'sections' => $sections,
             );
