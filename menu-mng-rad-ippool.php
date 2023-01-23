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
if (strpos($_SERVER['PHP_SELF'], '/menu-mng-rad-ippool.php') !== false) {
    header("Location: index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Management";

include_once ("include/menu/menu-items.php");
include_once ("include/menu/management-subnav.php");
?>
        
            <div id="sidebar">

                <h2>Management</h2>
                
                <h3>IP Pools</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= strip_tags(t('button','ListIPPools')) ?>" href="mng-rad-ippool-list.php" tabindex="1">
                            <b>&raquo;</b><?= t('button','ListIPPools') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','NewIPPool')) ?>" href="mng-rad-ippool-new.php" tabindex="2">
                            <b>&raquo;</b><?= t('button','NewIPPool') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','EditIPPool')) ?>" href="javascript:document.mngradippooledit.submit();" tabindex="3">
                            <b>&raquo;</b><?= t('button','EditIPPool') ?>
                        </a>
                        <form name="mngradippooledit" action="mng-rad-ippool-edit.php" method="GET" class="sidebar">
<?php

include_once("include/management/populate_selectbox.php");
$menu_valid_ippools = get_ippools();

$options = $menu_valid_ippools;
array_unshift($options , '');
$ippools_select = array(
                                "name" => "item",
                                "caption" => sprintf("%s - %s", t('all','PoolName'), t('all','IPAddress')),
                                "type" => "select",
                                "options" => $options,
                                "selected_value" => (isset($selected_ippool) ? $selected_ippool : ""),
                             );
print_form_component($ippools_select);

?>
                        </form>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','RemoveIPPool')) ?>" href="mng-rad-ippool-del.php" tabindex="6">
                            <b>&raquo;</b><?= t('button','RemoveIPPool') ?>
                        </a>
                    </li>
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->
