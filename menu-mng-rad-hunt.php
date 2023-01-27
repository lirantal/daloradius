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
if (strpos($_SERVER['PHP_SELF'], '/menu-mng-rad-hunt.php') !== false) {
    header("Location: index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Management";




?>
        
            <div id="sidebar">

                <h2>Management</h2>
                
                <h3>HuntGroup Management</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= strip_tags(t('button','ListHG')) ?>" href="mng-rad-hunt-list.php" tabindex="1">
                            <b>&raquo;</b><?= t('button','ListHG') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','NewHG')) ?>" href="mng-rad-hunt-new.php" tabindex="2">
                            <b>&raquo;</b><?= t('button','NewHG') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','EditHG')) ?>" href="javascript:document.mngradhuntedit.submit();" tabindex="3">
                            <b>&raquo;</b><?= t('button','EditHG') ?>
                        </a>
                        <form name="mngradhuntedit" action="mng-rad-hunt-edit.php" method="GET" class="sidebar">
<?php

include_once("include/management/populate_selectbox.php");
$menu_valid_huntgroups = get_huntgroups();

$options = $menu_valid_huntgroups;
array_unshift($options , '');
$huntgroups_select = array(
                                "name" => "item",
                                "caption" => "Huntgroup item",
                                "type" => "select",
                                "options" => $options,
                                "selected_value" => (isset($selected_huntgroup) ? $selected_huntgroup : ""),
                             );
print_form_component($huntgroups_select);

?>
                        </form>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','RemoveHG')) ?>" href="mng-rad-hunt-del.php" tabindex="6">
                            <b>&raquo;</b><?= t('button','RemoveHG') ?>
                        </a>
                    </li>
                    
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->
