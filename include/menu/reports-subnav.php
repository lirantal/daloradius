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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/reports-subnav.php') !== false) {
    header("Location: ../../index.php");
    exit;
}
?>
                <ul id="subnav">
<?php
$subnav_elements = array(
                            'General' => 'rep-main.php',
                            'Logs' => 'rep-logs.php',
                            'Status' => 'rep-status.php',
                            'Batch Users' => 'rep-batch.php',
                            'Dashboard' => 'rep-hb.php'
                        );
                        
foreach ($subnav_elements as $label => $href) {
    printf('<li><a href="%s" title="%s">%s</a></li>', $href, strip_tags($label), $label);
}
?>

                </ul><!-- #subnav -->
            </div>
