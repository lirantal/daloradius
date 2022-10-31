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
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Home";

?>

<body>

    <div id="wrapper">
        <div id="innerwrapper">

<?php
    include_once("include/menu/menu-items.php");
    include_once("include/menu/home-subnav.php");
    
    $status_menu_elements = array(
                                    'rep-stat-server.php' => t('button','ServerStatus'),
                                    'rep-stat-services.php' => t('button','ServicesStatus'),
                                    'rep-lastconnect.php' => t('button','LastConnectionAttempts')
                                 );
                          
    $logs_menu_elements = array(
                                    'rep-logs-radius.php' => t('button','RadiusLog'),
                                    'rep-logs-system.php' => t('button','SystemLog')
                               );
    
    $element_format = '<li><a href="%s" title="%s" tabindex="%s"><b>&raquo;</b>%s</a></li>';
    
?>      

            <div id="sidebar">
                <h2>Home</h2>
                
                <h3>Status</h3>
                <ul class="subnav">
<?php
                $tabindex = 1;
                foreach ($status_menu_elements as $href => $caption) {
                    printf($element_format, $href, strip_tags($caption), $tabindex, $caption);
                    $tabindex++;
                }
?>

                </ul><!-- .subnav -->

                <h3>Logs</h3>
                <ul class="subnav">
<?php
                $tabindex = count($status_menu_elements) + 1;
                foreach ($logs_menu_elements as $href => $caption) {
                    printf($element_format, $href, strip_tags($caption), $tabindex, $caption);
                }
?>

                </ul><!-- .subnav -->
    
                <h3>Support</h3>

                <p class="news">
                    daloRADIUS<br>
                    RADIUS Management 
                    <a target="_blank" href="https://github.com/lirantal/daloradius" class="more">Read More &raquo;</a>
                </p>
            </div><!-- #sidebar -->

