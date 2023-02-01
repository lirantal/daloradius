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
if (strpos($_SERVER['PHP_SELF'], '/menu-help.php') !== false) {
    header("Location: index.php");
    exit;
}

include_once("lang/main.php");


// define descriptors
$descriptors1 = array();

$descriptors1[] = array(
                            'type' => 'textarea',
                            'content' => sprintf('daloRADIUS - RADIUS Management<br>%s / %s',
                                                 t('all', 'daloRADIUSVersion'), $configValues['DALORADIUS_DATE']),
                            'readmore' => array( 'href' => 'https://github.com/lirantal/daloradius',
                                                 'title' => 'Read More',
                                                 'label' => 'Read More &raquo;',
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

menu_print($menu);

?>


