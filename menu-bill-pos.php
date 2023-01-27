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
if (strpos($_SERVER['PHP_SELF'], '/menu-bill-pos.php') !== false) {
    header("Location: index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Billing";



include_once("include/management/autocomplete.php");
?>

            <div id="sidebar">

                <h2>Billing</h2>
                
                <h3>Point of Sales Management</h3>
                <ul class="subnav">
                
                    <li>
                        <a title="<?= strip_tags(t('button','ListUsers')) ?>" href="javascript:document.billposlist.submit();">
                            <b>&raquo;</b><?= t('button','ListUsers') ?></a>
                        <form name="billposlist" action="bill-pos-list.php" method="GET" class="sidebar">
                    
<?php
                            include 'include/management/populate_selectbox.php';
                            populate_plans("Select Plan","planname","generic");
?>
                        </form>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','NewUser')) ?>" href="bill-pos-new.php">
                            <b>&raquo;</b><?= t('button','NewUser') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','EditUser')) ?>" href="javascript:document.billposedit.submit();">
                            <b>&raquo;</b><?= t('button','EditUser') ?></a>
                        <form name="billposedit" action="bill-pos-edit.php" method="GET" class="sidebar">
                            <input name="username" type="text" id="usernameEdit" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','Username'); ?><br>"
                                value="<?= (isset($edit_username)) ? $edit_username :"" ?>">
                        </form>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','RemoveUsers')) ?>" href="bill-pos-del.php">
                            <b>&raquo;</b><?= t('button','RemoveUsers') ?>
                        </a>
                    </li>
                    
            </ul><!-- .subnav -->
        </div><!-- #sidebar -->

<script>
<?php
if ($autoComplete) {
?>
    /** Making usernameEdit interactive **/
    var autoComEdit = new DHTMLSuite.autoComplete();
    autoComEdit.add('usernameEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');
<?php
}
?>
    
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>
