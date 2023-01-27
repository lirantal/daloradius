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
if (strpos($_SERVER['PHP_SELF'], '/menu-accounting-maintenance.php') !== false) {
    header("Location: index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Accounting";


//~
include_once("include/menu/auto_subnav.php");

$menu_elements = array(
                        "acct-maintenance-cleanup.php" => t('button','CleanupStaleSessions'),
                        "acct-maintenance-delete.php" => t('button','DeleteAccountingRecords'),
                          );
?>

            <div id="sidebar">
                <h2>Accounting</h2>
    
                <h3>Maintenance</h3>
                <ul class="subnav">
<?php
                $tabindex = 1;
                foreach ($menu_elements as $href => $caption) {
                    printf('<li><a href="%s" title="%s" tabindex="%s"><b>&raquo;</b>%s</a></li>',
                           $href, strip_tags($caption), $tabindex, $caption);
                    $tabindex++;
}
?>

                </ul><!-- .subnav -->
            </div><!-- #sidebar -->
