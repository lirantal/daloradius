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

$m_active = "Help";



?>

            <div id="sidebar">

                <h2>Help</h2>

                <h3>Support</h3>

                <p class="news">
                    daloRADIUS - RADIUS Management<br>
                    <?= t('all', 'daloRADIUSVersion') ?> / <?= htmlspecialchars($configValues['DALORADIUS_DATE'], ENT_QUOTES, 'UTF-8') ?>
                     
                    <a href="https://github.com/lirantal/daloradius" class="more">Read More &raquo;</a>
                </p>

            </div><!-- #sidebar -->
