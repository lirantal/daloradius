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
    header("Location: index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Management";

include_once("include/menu/menu-items.php");
include_once("include/menu/management-subnav.php");


// init select components

function get_select_options($item_table, $item_prefix) {
    include('library/opendb.php');
    
    $options_format = "%s: [%s %s %s]";

    $sql = sprintf("SELECT id, groupname, attribute, op, value FROM %s ORDER BY groupname, attribute DESC", $item_table);
    $res = $dbSocket->query($sql);

    $result = array();

    while ($row = $res->fetchrow()) {
        list($id, $groupname, $attribute, $op, $value) = $row;
        $key = $item_prefix . $id;
        $result[$key] = sprintf($options_format, $groupname, $attribute, $op, $value);
    }

    include('library/closedb.php');
    
    return $result;
}

$caption = sprintf($options_format, t('all','Groupname'), t('all','Attribute'), "op", t('all','Value'));
$radgroupreply_options = get_select_options($configValues['CONFIG_DB_TBL_RADGROUPREPLY'], "groupreply-");
$radgroupcheck_options = get_select_options($configValues['CONFIG_DB_TBL_RADGROUPCHECK'], "groupcheck-");

$menu_radgroupreply_select = array(
                                    'name' => 'item',
                                    'type' => 'select',
                                    'caption' => $caption,
                                    'options' => $radgroupreply_options,
                                    'selected_value' => $selected_groupreply_item,
                                    'disabled' => (count($radgroupreply_options) == 0),
                                  );

$menu_radgroupcheck_select = array(
                                    'name' => 'item',
                                    'type' => 'select',
                                    'caption' => $caption,
                                    'options' => $radgroupcheck_options,
                                    'selected_value' => $selected_groupcheck_item,
                                  );

?>

            <div id="sidebar">

                <h2>Management</h2>

                <h3>Group Check Management</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= strip_tags(t('button','NewGroupCheck')) ?>" href="mng-rad-groupcheck-new.php" tabindex="12">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsAdd.png">
                            <?= t('button','NewGroupCheck') ?>
                        </a>
                    </li>
<?php
                    // shown only if needed
                    if (count($radgroupcheck_options) > 0) {
?>
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
                        <a title="<?= strip_tags(t('button','EditGroupCheck')) ?>" href="javascript:document.mngradgrpchkedit.submit();" tabindex="13">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsEdit.png">
                            <?= t('button','EditGroupCheck') ?>
                        </a>
                        <form name="mngradgrpchkedit" action="mng-rad-groupcheck-edit.php" method="GET" class="sidebar">
<?php
                            print_form_component($menu_radgroupcheck_select);
?>
                        </form>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','RemoveGroupCheck')) ?>" href="mng-rad-groupcheck-del.php" tabindex="16">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsRemove.png">
                            <?= t('button','RemoveGroupCheck') ?>
                        </a>
                    </li>
<?php
                    }
?>
                </ul><!-- .subnav -->
                
                <h3>Group Reply Management</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= strip_tags(t('button','NewGroupReply')) ?>" href="mng-rad-groupreply-new.php" tabindex="4">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsAdd.png">
                            <?= t('button','NewGroupReply') ?>
                        </a>
                    </li>
<?php
                    if (count($radgroupreply_options) > 0) {
?>
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
                        <a title="<?= strip_tags(t('button','EditGroupReply')) ?>" href="javascript:document.mngradgrprplyedit.submit();" tabindex="5">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsEdit.png">
                            <?= t('button','EditGroupReply') ?>
                        </a>
                        <form name="mngradgrprplyedit" action="mng-rad-groupreply-edit.php" method="GET" class="sidebar">
<?php
                            print_form_component($menu_radgroupreply_select);
?>
                        </form>
                    </li>
                    
                    <li>
                        <a title="<?= strip_tags(t('button','RemoveGroupReply')) ?>" href="mng-rad-groupreply-del.php" tabindex="8">
                            <b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/groupsRemove.png">
                            <?= t('button','RemoveGroupReply') ?>
                        </a>
                    </li>
<?php
                    }
?>
                </ul><!-- .subnav -->
                
            </div><!-- #sidebar -->

