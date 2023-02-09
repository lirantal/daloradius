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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/help/default.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}


// define descriptors
$descriptors1 = array();

$content = "";
if (isset($configValues['DALORADIUS_VERSION'])) {
    $content .= "<br>version " . $configValues['DALORADIUS_VERSION'];
    if (!empty($configValues['DALORADIUS_DATE'])) {
        $content .= " / " . $configValues['DALORADIUS_DATE'];
    }
}

$descriptors1[] = array(
                            'type' => 'textarea',
                            'content' => sprintf('daloRADIUS - RADIUS Management%s', $content),
                            'readmore' => array( 'href' => 'https://github.com/lirantal/daloradius',
                                                 'title' => 'Read More',
                                                 'label' => 'Read More',
                                               ),
                       );

// add descriptors to a sections
$sections = array();
$sections[] = array( 'title' => 'Support', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'Help',
                'sections' => $sections,
             );
