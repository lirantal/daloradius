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
if (strpos($_SERVER['PHP_SELF'], '/include/menu/menu-items.php') !== false) {
    header("Location: ../../index.php");
    exit;
}
?>
            <div id="header">
                <span id="login_data">
                    Welcome, <strong><?= htmlspecialchars($operator, ENT_QUOTES, 'UTF-8') ?></strong>.
                    <a href="logout.php" title="Logout" style="padding: 1px; background-color: #FF8040; color: white">
                        <strong>&times;</strong>
                    </a>
                    <br>
                    Location: <strong><?= htmlspecialchars($_SESSION['location_name'], ENT_QUOTES, 'UTF-8') ?></strong>.
                </span>
                
                <span class="sep">&nbsp;</span>
                
                <form action="mng-search.php" method="GET">
                    <input name="username" value="" placeholder="<?= t('button','SearchUsers') ?>"
                        title="<?= strip_tags(t('Tooltip','Username') . '. ' . t('Tooltip','UsernameWildcard')) ?>">
                </form>

                <span class="sep">&nbsp;</span>
                
                <h1>
                    <a title="<?= strip_tags(t('menu','Home')) ?>" href="index.php">
                        <img style="border: 0" src="static/images/daloradius_small.png">
                    </a>
                </h1>
                <h2><?= t('all','copyright1') ?></h2>
                <a name="top"></a>
                <ul id="nav">
<?php
                $nav_elements = array(
                                        'Home'       => 'index.php',
                                        'Managment'  => 'mng-main.php',
                                        'Reports'    => 'rep-main.php',
                                        'Accounting' => 'acct-main.php',
                                        'Billing'    => 'bill-main.php',
                                        'Gis'        => 'gis-main.php',
                                        'Graphs'     => 'graph-main.php',
                                        'Config'     => 'config-main.php',
                                        'Help'       => 'help-main.php'
                );
                
                foreach ($nav_elements as $label => $href) {
                    $class = ($m_active == $label) ? 'active' : '';
                    printf('<li><a class="%s" title="%s" href="%s">%s</a></li>',
                           $class, strip_tags(t('menu', $label)), $href, t('menu', $label));
                }
?>

                </ul><!-- #nav -->

