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
if (strpos($_SERVER['PHP_SELF'], '/menu-mng-rad-profiles.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Management";

?>

<?php
    include_once("include/menu/menu-items.php");
	include_once("include/menu/management-subnav.php");
    include_once("include/management/autocomplete.php");
?>

            <div id="sidebar">
                <h2>Management</h2>

                <h3>Profiles Management</h3>
                <ul class="subnav">

                    <li>
                        <a title="<?= strip_tags(t('button','ListProfiles')) ?>" href="mng-rad-profiles-list.php" tabindex="1">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsList.png">
                            <?= t('button','ListProfiles') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','NewProfile')) ?>" href="mng-rad-profiles-new.php" tabindex="2">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsAdd.png">
                            <?= t('button','NewProfile') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','EditProfile')) ?>" href="javascript:document.mngradprofileedit.submit();" tabindex="3">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsEdit.png">
                            <?= t('button','EditProfile') ?>
                        </a>
                        <form name="mngradprofileedit" action="mng-rad-profiles-edit.php" method="GET" class="sidebar">
<?php   
                            include('include/management/populate_selectbox.php');
                            populate_groups("Select Profile", "profile", "generic");
?>
                        </form>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','DuplicateProfile')) ?>" href="mng-rad-profiles-duplicate.php" tabindex="4">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsEdit.png">
                            <?= t('button','DuplicateProfile') ?>
                        </a>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','RemoveProfile')) ?>" href="mng-rad-profiles-del.php" tabindex="5">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsRemove.png">
                            <?= t('button','RemoveProfile') ?>
                        </a>
                    </li>
                
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->

<?php 
	if ($autoComplete) {
?>
<script>
    /** Making usernameEdit interactive **/
    autoComEdit = new DHTMLSuite.autoComplete();
</script>
<?php
	} 
?>
