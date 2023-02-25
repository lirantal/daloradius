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
 * Description:    displays a welcome page for the main index.php file
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Miguel García <miguelvisgarcia@gmail.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/library/extensions/welcome_page.php') !== false) {
    header("Location: ../../index.php");
    exit;
}
?>

<div class="text-center">
    <h2>daloRADIUS Web Management Server</h2>
    <h3><?= t('all', 'daloRADIUSVersion') ?> / <?= htmlspecialchars($configValues['DALORADIUS_DATE'], ENT_QUOTES, 'UTF-8') ?></h3>
    <h4>
        <a class="text-decoration-none" title="Mail to Liran Tal" href="mailto:Liran Tal &lt;liran.tal@gmail.com&gt;">Liran Tal</a>
        &amp;
        <a class="text-decoration-none" title="Mail to Filippo Lauria" href="mailto:Filippo Lauria &lt;filippo.lauria@iit.cnr.it&gt;">Filippo Lauria</a>
    </h4>
    <img class="my-3" src="static/images/daloradius_logo.jpg">
</div>
