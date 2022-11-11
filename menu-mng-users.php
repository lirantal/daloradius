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
if (strpos($_SERVER['PHP_SELF'], '/menu-mng-users.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Management";

?>

    <script src="library/javascript/rounded-corners.js"></script>
    <script src="library/javascript/form-field-tooltip.js"></script>
    <link rel="stylesheet" tabindex="" href="css/form-field-tooltip.css" media="screen">

<?php
    include_once("include/menu/menu-items.php");
	include_once("include/menu/management-subnav.php");
    include_once("include/management/autocomplete.php");
?>

            <div id="sidebar">
                <h2>Management</h2>
                
                <h3>Users Management</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= t('button','ListUsers') ?>" tabindex="1" href="mng-list-all.php">
                            <b>&raquo;</b><img style="margin-right:5px; border:0" src="images/icons/userList.gif"><?= t('button','ListUsers') ?>
                        </a>
                    </li>
                    
                    <li>
                        <a title="<?= t('button','NewUser') ?>" tabindex="2" href="mng-new.php">
                            <b>&raquo;</b><img style="margin-right:5px; border:0" src="images/icons/userNew.gif"><?= t('button','NewUser') ?>
                        </a>
                    </li>
                    
                    <li>
                        <a title="<?= t('button','NewUserQuick') ?>" tabindex="3" href="mng-new-quick.php">
                            <b>&raquo;</b><img style="margin-right:5px; border:0" src="images/icons/userNew.gif"><?= t('button','NewUserQuick') ?>
                        </a>
                    </li>
                    
                    <li>
                        <a title="<?= t('button','EditUser') ?>" tabindex="4" href="javascript:document.mngedit.submit();">
                            <b>&raquo;</b><img style="margin-right:5px; border:0" src="images/icons/userEdit.gif"><?= t('button','EditUser') ?>
                        </a>
                        
                        <form name="mngedit" action="mng-edit.php" method="GET" class="sidebar">
                            <input name="username" type="text" id="usernameEdit" autocomplete="off"
                                tooltipText="<?= t('Tooltip','Username'); ?> <br>" tabindex="5"
                                value="<?= (isset($edit_username)) ? $edit_username : "" ?>">
                        </form>
                    </li>
                    
                    <li>
                        <a title="<?= t('button','SearchUsers') ?>" tabindex="6" href="javascript:document.mngsearch.submit();">
                            <b>&raquo;</b><img style="margin-right:5px; border:0" src="images/icons/userSearch.gif"><?= t('button','SearchUsers') ?>
                        </a>
                        
                        <form name="mngsearch" action="mng-search.php" method="GET" class="sidebar">
                            <input name="username" type="text" id="usernameSearch" autocomplete="off" tabindex="7"
                                tooltipText="<?= t('Tooltip','Username') . "<br>" . t('Tooltip','UsernameWildcard'); ?>"
                                value="<?= (isset($search_username)) ? $search_username : "" ?>">
                        </form>
                    </li>
                    
                    <li>
                        <a title="<?= t('button','RemoveUsers') ?>" tabindex="8" href="mng-del.php">
                            <b>&raquo;</b><img style="margin-right:5px; border:0" src="images/icons/userRemove.gif"><?= t('button','RemoveUsers') ?>
                        </a>
                    </li>
                </ul>

                <br>
                
                <h3>Extended Capabilities</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= t('button','ImportUsers') ?>" tabindex="9" href="mng-import-users.php">
                            <b>&raquo;</b><img style="margin-right:5px; border:0" src="images/icons/userNew.gif"><?= t('button','ImportUsers') ?>
                        </a>
                    </li>
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->

<script>
<?php
    
    if ($autoComplete) {
?>
    var autoComEdit = new DHTMLSuite.autoComplete();

    /** Making usernameEdit interactive **/
    autoComEdit.add('usernameEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');

    /** Making usernameSearch interactive **/
    autoComEdit.add('usernameSearch','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');
<?php
    }
?>
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>

