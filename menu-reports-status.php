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
if (strpos($_SERVER['PHP_SELF'], '/menu-mng-batch.php') !== false) {
    header("Location: /index.php");
    exit;
}

$m_active = "Reports";

?>

<body>
    <div id="wrapper">
        <div id="innerwrapper">

<?php
    include_once("include/menu/menu-items.php");
	include_once("include/menu/reports-subnav.php");
    
    $status_menu_elements = array(
        "rep-stat-server.php" => array(t('button','ServerStatus'), "images/icons/reportsStatus.png"),
        "rep-stat-services.php" => array(t('button','ServicesStatus'), "images/icons/reportsStatus.png")
    );
    
    $peripherals_menu_elements = array(
        "rep-stat-cron.php" => array("CRON Status", "images/icons/reportsStatus.png"),
        "rep-stat-ups.php" => array("UPS Status", "images/icons/reportsStatus.png"),
        "rep-stat-raid.php" => array("RAID Status", "images/icons/reportsStatus.png")
    );
    $element_format = '<li><a href="%s" title="%s" tabindex="%s"><b>&raquo;</b><img style="border: 0; margin-right: 5px" src="%s">%s</a></li>';
?>      

            <div id="sidebar">
                <h2>Status</h2>
                
                <h3>Status</h3>
                <ul class="subnav">
<?php
                $tabindex = 1;
                foreach ($status_menu_elements as $href => $items) {
                    list($caption, $src) = $items;
                    printf($element_format, $href, strip_tags($caption), $tabindex, $src, $caption);
                    $tabindex++;
                }
?>
                </ul><!-- .subnav -->

                <h3 style="margin-top: 20px">Extended Peripherals</h3>
                <ul class="subnav">
<?php
$tabindex = count($status_menu_elements) + 1;
foreach ($peripherals_menu_elements as $href => $items) {
    list($caption, $src) = $items;
    printf($element_format, $href, strip_tags($caption), $tabindex, $src, $caption);
    $tabindex++;
}
?>
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->
