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
if (strpos($_SERVER['PHP_SELF'], '/menu-mng-rad-usergroup.php') !== false) {
    header("Location: index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Management";



include_once("include/management/autocomplete.php");
?>

            <div id="sidebar">
                <h2>Management</h2>

                <h3>User-Group Management</h3>
                <ul class="subnav">

                    <li>
                        <a title="<?= strip_tags(t('button','ListUserGroup')) ?>" tabindex="1" href="mng-rad-usergroup-list.php">
                            <b>&raquo;</b><?= t('button','ListUserGroup') ?>
                        </a>
                    </li>
                        
                    <li>
                        <a title="<?= strip_tags(t('button','ListUsersGroup')) ?>" tabindex="2" href="javascript:document.mngradusrgrplist.submit();">
                            <b>&raquo;</b><?= t('button','ListUsersGroup') ?>
                        <a>
                        <form name="mngradusrgrplist" action="mng-rad-usergroup-list-user.php" method="GET" class="sidebar">
                            <input name="username" type="text" id="usernameList" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','Username'); ?><br>"
                                tabindex="3" value="<?= (isset($usernameList)) ? $usernameList : "" ?>">
                        </form>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','NewUserGroup')) ?>" tabindex="4" href="mng-rad-usergroup-new.php">
                            <b>&raquo;</b><?= t('button','NewUserGroup') ?>
                        </a>
                    </li>
                    
                    <li>
                        <a title="<?= strip_tags(t('button','EditUserGroup')) ?>" tabindex="5" href="javascript:document.mngradusrgrpedit.submit();">
                            <b>&raquo;</b><?= t('button','EditUserGroup') ?>
                        </a>
                        <form name="mngradusrgrpedit" action="mng-rad-usergroup-edit.php" method="GET" class="sidebar">
                            <input name="username" type="text" value="" id="usernameEdit" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','Username'); ?><br>" tabindex="6">
                            <input name="current_group" type="text" value="" id="groupnameEdit" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','GroupName'); ?><br>" tabindex="7">
                        </form>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','RemoveUserGroup')) ?>" tabindex="8" href="mng-rad-usergroup-del.php">
                            <b>&raquo;</b><?= t('button','RemoveUserGroup') ?>
                        </a>
                    </li>
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->

<script>
<?php
    if ($autoComplete) {
?>
    var autoComEdit = new DHTMLSuite.autoComplete();
    autoComEdit.add('usernameList','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');

    autoComEdit = new DHTMLSuite.autoComplete();
    autoComEdit.add('usernameEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');

    autoComEdit = new DHTMLSuite.autoComplete();
    autoComEdit.add('groupnameEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteGroupName');
<?php
    }
?>
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>
