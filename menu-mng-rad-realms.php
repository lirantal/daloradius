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
if (strpos($_SERVER['PHP_SELF'], '/menu-mng-rad-realms.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Management";

?>
<body>
    <div id="wrapper">
        <div id="innerwrapper">

<?php
	include_once ("include/menu/menu-items.php");
	include_once ("include/menu/management-subnav.php");
?>

            <div id="sidebar">
                <h2>Management</h2>
                
                <h3>Realms Management</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= strip_tags(t('button','ListRealms')) ?>" tabindex="1" href="mng-rad-realms-list.php">
                            <b>&raquo;</b><?= t('button','ListRealms') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','NewRealm')) ?>" tabindex="2" href="mng-rad-realms-new.php">
                            <b>&raquo;</b><?= t('button','NewRealm') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','EditRealm')) ?>" tabindex="3" href="javascript:document.mngradrealmedit.submit();">
                            <b>&raquo;</b><?= t('button','EditRealm') ?>
                        </a>
                        <form name="mngradrealmedit" action="mng-rad-realms-edit.php" method="GET" class="sidebar">
<?php
                        include_once('include/management/populate_selectbox.php');
                        populate_realms("Select Realm","realmname","generic");
?>
                        </form>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','RemoveRealm')) ?>" tabindex="4" href="mng-rad-realms-del.php">
                            <b>&raquo;</b><?= t('button','RemoveRealm') ?>
                        </a>
                    </li>
                </ul><!-- .subnav -->


                <h3>Proxys Management</h3>
                <ul class="subnav">

                    <li>
                        <a title="<?= strip_tags(t('button','ListProxys')) ?>" tabindex="5" href="mng-rad-proxys-list.php">
                            <b>&raquo;</b><?= t('button','ListProxys') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','NewProxy')) ?>" tabindex="6" href="mng-rad-proxys-new.php">
                            <b>&raquo;</b><?= t('button','NewProxy') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','EditProxy')) ?>" tabindex="7" href="javascript:document.mngradproxyedit.submit();">
                            <b>&raquo;</b><?= t('button','EditProxy') ?>
                        </a>
                        <form name="mngradproxyedit" action="mng-rad-proxys-edit.php" method="GET" class="sidebar">
<?php
                        populate_proxys("Select Proxy","proxyname","generic");
?>
                        </form>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','RemoveProxy')) ?>" tabindex="8" href="mng-rad-proxys-del.php">
                            <b>&raquo;</b><?= t('button','RemoveProxy') ?>
                        </a>
                    </li>
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->
