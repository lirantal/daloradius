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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/acct/default.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

global $startdate, $enddate;

// define descriptors
$descriptors1 = array();
 
$components = array();

$components[] = array(
                            "name" => "startdate",
                            "type" => "date",
                            "value" => ((isset($startdate)) ? $startdate : date("Y-m-01")),
                            "caption" => t('all','StartingDate'),
                            "tooltipText" => t('Tooltip','Date'),
                     );
                     
$components[] = array(
                            "name" => "enddate",
                            "type" => "date",
                            "value" => ((isset($enddate)) ? $enddate : date("Y-m-t")),
                            "caption" => t('all','EndingDate'),
                            "tooltipText" => t('Tooltip','Date'),
                     );

$descriptors1[] = array( 'type' => 'form', 'title' => t('button','DateAccounting'), 'action' => 'acct-date.php', 'method' => 'GET',
                         'icon' => 'calendar-range', 'form_components' => $components, );
$sections = array();
$sections[] = array( 'title' => 'Users Accounting', 'descriptors' => $descriptors1 );


// add sections to menu
$menu = array(
                'title' => 'Accounting',
                'sections' => $sections,
             );
