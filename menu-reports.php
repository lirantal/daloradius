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
if (strpos($_SERVER['PHP_SELF'], '/menu-reports.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Reports";

include_once("include/menu/menu-items.php");
include_once("include/menu/reports-subnav.php");
include_once("include/management/autocomplete.php");

?>      

            <div id="sidebar">
                <h2>Reports</h2>
                
                <h3>Users Reports</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= strip_tags(t('button','OnlineUsers')) ?>" href="javascript:document.reponline.submit();">
                            <b>&raquo;</b><img style="border: 0; margin-right: 5px" src="images/icons/reportsOnlineUsers.gif">
                            <?= t('button','OnlineUsers') ?>
                        </a>
                            
                        <form name="reponline" action="rep-online.php" method="GET" class="sidebar">
                            <input name="usernameOnline" type="text" id="usernameOnline"
                                <?= ($autoComplete) ? 'autocomplete="off"' : "" ?> placeholder="<?= t('all','Username') ?>"
                                tooltipText="<?= t('Tooltip','Username') ?><br><?= t('Tooltip','UsernameWildcard') ?><br>"
                                value="<?= (isset($usernameOnline)) ? $usernameOnline : "" ?>" tabindex="1">
                        </form>
                    </li>                            

                    <li>
                        <a title="<?= t('button','LastConnectionAttempts') ?>" href="javascript:document.replastconnect.submit();">
                            <b>&raquo;</b><img style="border: 0; margin-right: 5px" src="images/icons/reportsLastConnection.png">
                            <?= t('button','LastConnectionAttempts') ?>
                        </a>
                        
                        <form name="replastconnect" action="rep-lastconnect.php" method="GET" class="sidebar">
                            <input name="usernameLastConnect" type="text" id="usernameLastConnect"
                                <?= ($autoComplete) ? 'autocomplete="off"' : "" ?> placeholder="<?= t('all','Username') ?>"
                                tooltipText="<?= t('Tooltip','Username') ?><br><?= t('Tooltip','UsernameWildcard') ?><br>"
                                value="<?= (isset($usernameLastConnect)) ? $usernameLastConnect : "" ?>" tabindex="2">
                            
                            <select class="generic" name="radiusreply" tabindex="3">
                                <option value="Any">Any</option>
                                <option value="Access-Accept">Access-Accept</option>
                                <option value="Access-Reject">Access-Reject</option>
                            </select>
                            
                            <label style="user-select: none" for="startdate_lastconnect"><?= t('all','StartingDate') ?></label>
                            <input name="startdate" type="date" id="startdate_lastconnect" tooltipText="<?= t('Tooltip','Date') ?>"
                                value="<?= (isset($startdate)) ? $startdate : date("Y-m-01") ?>" tabindex="4">
                            
                            <label style="user-select: none" for="enddate_lastconnect"><?= t('all','EndingDate') ?></label>
                            <input name="enddate" type="date" id="enddate_lastconnect" tooltipText="<?= t('Tooltip','Date') ?>"
                                value="<?= (isset($enddate)) ? $enddate : date("Y-m-t") ?>" tabindex="5">
                        </form>
                    </li>
                    
                    <li>
                        <a title="<?= strip_tags(t('button','NewUsers')) ?>" href="javascript:document.repnewusers.submit();">
                            <b>&raquo;</b><img style="border: 0; margin-right: 5px" src="images/icons/userList.gif">
                            <?= t('button','NewUsers') ?>
                        </a>

                        <form name="repnewusers" action="rep-newusers.php" method="GET" class="sidebar">
                            <label style="user-select: none" for="startdate"><?= t('all','StartingDate') ?></label>
                            <input name="startdate" type="date" id="startdate" tooltipText="<?= t('Tooltip','Date') ?>"
                                value="<?= (isset($startdate)) ? $startdate : date("Y-01-01") ?>" tabindex="6">
                            
                            <label style="user-select: none" for="enddate"><?= t('all','EndingDate') ?></label>
                            <input name="enddate" type="text" id="enddate" tooltipText="<?= t('Tooltip','Date') ?>"
                                value="<?= (isset($enddate)) ? $enddate : date("Y-m-t") ?>" tabindex="7">
                         </form>
                    </li>
                    
                    <li>
                        <a title="<?= strip_tags(t('button','TopUser')) ?>" href="javascript:document.topusers.submit();"><b>&raquo;</b>
                            <img style="border: 0; margin-right: 5px" src="images/icons/reportsTopUsers.png">
                            <?= t('button','TopUser') ?>
                        </a>
                        
                        <form name="topusers" action="rep-topusers.php" method="GET" class="sidebar">
                            <input type="number" class="generic" name="limit" max="1000" min="1"
                                value="<?= (isset($limit) && intval($limit) > 0) ? $limit : "50" ?>" tabindex="8">
                            
                            <label for="usernameFilter">Username Filter</label>
                            <input name="username" type="text" id="usernameFilter" value="<?= (isset($username)) ? $username : "" ?>"
                                 placeholder="<?= t('all','Username') ?>" tabindex="9">
            
                            <label style="user-select: none" for="startdate_topuser"><?= t('all','StartingDate') ?></label>
                            <input name="startdate" type="date" id="startdate_topuser" tooltipText="<?= t('Tooltip','Date') ?>"
                                value="<?= (isset($startdate)) ? $startdate : date("Y-m-01") ?>" tabindex="10">
                            
                            <label style="user-select: none" for="enddate_topuser"><?= t('all','EndingDate') ?></label>
                            <input name="enddate" type="date" id="enddate_topuser" tooltipText="<?= t('Tooltip','Date') ?>"
                                value="<?= (isset($enddate)) ? $enddate : date("Y-m-t") ?>" tabindex="11">

                            <label for="orderBy"><?= t('button','OrderBy') ?></label>
                            <select class="generic" id="orderBy" name="orderBy" type="text" tabindex="12">
                                <option value="Time">Time</option>
                                <option value="Download">Download (bytes)</option>
                                <option value="Upload">Upload (bytes)</option>
                            </select>
                        </form>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','History')) ?>" href="rep-history.php">
                            <b>&raquo;</b><img style="border: 0; margin-right: 5px" src="images/icons/reportsHistory.png">
                            <?= t('button','History') ?>
                        </a>
                    </li>
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->


<script>
<?php
    if ($autoComplete) {
?>

    var autoComEditElements = ["usernameOnline","usernameLastConnect"];
    for (var i = 0; i < autoComEditElements.length; i++) {
        var autoComEdit = new DHTMLSuite.autoComplete();
        autoComEdit.add(autoComEditElements[i],
                        'include/management/dynamicAutocomplete.php',
                        '_small',
                        'getAjaxAutocompleteUsernames');
    }
    
<?php
    }
?>
        var tooltipObj = new DHTMLgoodies_formTooltip();
        tooltipObj.setTooltipPosition('right');
        tooltipObj.setPageBgColor('#EEEEEE');
        tooltipObj.setTooltipCornerSize(15);
        tooltipObj.initFormFieldTooltip();
</script>
