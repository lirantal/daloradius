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
if (strpos($_SERVER['PHP_SELF'], '/menu-mng-rad-groups.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Management";

?>

<body>
    <script src="library/javascript/rounded-corners.js"></script>
    <script src="library/javascript/form-field-tooltip.js"></script>
    <link rel="stylesheet" href="css/form-field-tooltip.css" media="screen">

    <div id="wrapper">
        <div id="innerwrapper">

<?php
    include_once("include/menu/menu-items.php");
	include_once("include/menu/management-subnav.php");
?>

            <div id="sidebar">

                <h2>Management</h2>

                <h3>Group Reply Management</h3>
                <ul class="subnav">

                    <li>
                        <a title="<?= strip_tags(t('button','ListGroupReply')) ?>" href="mng-rad-groupreply-list.php" tabindex="1">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsList.png">
                            <?= t('button','ListGroupReply') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','SearchGroupReply')) ?>" href="javascript:document.mngradgroupreplysearch.submit();" tabindex="2">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsList.png">
                            <?= t('button','SearchGroupReply') ?>
                        </a>
                        <form name="mngradgroupreplysearch" action="mng-rad-groupreply-search.php" method="GET" class="sidebar">
                            <input name="groupname" type="text" tooltipText="<?= t('Tooltip','GroupName'); ?><br>" tabindex="3"
                                value="<?= (isset($search_groupname)) ? $search_groupname : "" ?>">
                        </form>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','NewGroupReply')) ?>" href="mng-rad-groupreply-new.php" tabindex="4">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsAdd.png">
                            <?= t('button','NewGroupReply') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','EditGroupReply')) ?>" href="javascript:document.mngradgrprplyedit.submit();" tabindex="5">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsEdit.png">
                            <?= t('button','EditGroupReply') ?>
                        </a>
                        <form name="mngradgrprplyedit" action="mng-rad-groupreply-edit.php" method="get" class="sidebar">
                            <input tabindex="6" name="groupname" type="text" value="" tooltipText="<?= t('Tooltip','GroupName'); ?><br>">
                            <input tabindex="7" name="attribute" type="text" value="" tooltipText="<?= t('Tooltip','AttributeName'); ?><br/>">
                        </form>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','RemoveGroupReply')) ?>" href="mng-rad-groupreply-del.php" tabindex="8">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsRemove.png">
                            <?= t('button','RemoveGroupReply') ?>
                        </a>
                    </li>
                    
                </ul><!-- .subnav -->

                <h3>Group Check Management</h3>
                <ul class="subnav">

                    <li>
                        <a title="<?= strip_tags(t('button','ListGroupCheck')) ?>" href="mng-rad-groupcheck-list.php" tabindex="9">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsList.png">
                            <?= t('button','ListGroupCheck') ?></a></li>
                    <li>
                        <a title="<?= strip_tags(t('button','SearchGroupCheck')) ?>" href="javascript:document.mngradgroupchecksearch.submit();" tabindex="10">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsList.png">
                            <?= t('button','SearchGroupCheck') ?>
                        </a>
                        <form name="mngradgroupchecksearch" action="mng-rad-groupcheck-search.php" method="GET" class="sidebar">
                            <input name="groupname" type="text" tooltipText="<?= t('Tooltip','GroupName'); ?><br>" tabindex="11"
                                value="<?= (isset($search_groupname)) ? $search_groupname : "" ?>">
                        </form>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','NewGroupCheck')) ?>" href="mng-rad-groupcheck-new.php" tabindex="12">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsAdd.png">
                            <?= t('button','NewGroupCheck') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','EditGroupCheck')) ?>" href="javascript:document.mngradgrpchkedit.submit();" tabindex="13">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsEdit.png">
                            <?= t('button','EditGroupCheck') ?>
                        </a>
                        <form name="mngradgrpchkedit" action="mng-rad-groupcheck-edit.php" method="get" class="sidebar">
                            <input tabindex="14" name="groupname" type="text" value="" tooltipText="<?= t('Tooltip','GroupName'); ?><br>">
                            <input tabindex="15" name="attribute" type="text" value="" tooltipText="<?= t('Tooltip','AttributeName'); ?><br>">
                        </form>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','RemoveGroupCheck')) ?>" href="mng-rad-groupcheck-del.php" tabindex="16">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsRemove.png">
                            <?= t('button','RemoveGroupCheck') ?>
                        </a>
                    </li>
                    
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->

<script>
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>
