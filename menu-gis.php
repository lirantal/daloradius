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
if (strpos($_SERVER['PHP_SELF'], '/menu-gis.php') !== false) {
    header("Location: index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Gis";

?>


<?php
    include_once ("include/menu/menu-items.php");
    include_once ("include/menu/gis-subnav.php");
?>      

            <div id="sidebar">

                <h2>GIS</h2>
                
                <h3>GIS Mapping</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= t('button','ViewMAP') ?>" href="gis-viewmap.php" tabindex="1">
                            <b>&raquo;</b><?= t('button','ViewMAP') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= t('button','EditMAP') ?>" href="gis-editmap.php" tabindex="2">
                            <b>&raquo;</b><?= t('button','EditMAP') ?>
                        </a>
                    </li>
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->
