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

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $langCode ?>" lang="<?= $langCode ?>">
<head>
    <title>daloRADIUS :: Reports</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="css/1.css" media="screen">
    <link rel="stylesheet" href="css/form-field-tooltip.css" media="screen">
    <link rel="stylesheet" href="library/js_date/datechooser.css">

    <script src="library/js_date/date-functions.js"></script>
    <script src="library/js_date/datechooser.js"></script>
    <script src="library/javascript/pages_common.js"></script>
    <script src="library/javascript/rounded-corners.js"></script>
    <script src="library/javascript/form-field-tooltip.js"></script>
</head>

<body>

    <div id="wrapper">
        <div id="innerwrapper">

<?php
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
                            <b>&raquo;</b><img style="border:0" src="images/icons/reportsOnlineUsers.gif">
							<?= t('button','OnlineUsers') ?>
                        </a>
							
                        <form name="reponline" action="rep-online.php" method="get" class="sidebar">
                            <input name="usernameOnline" type="text" id="usernameOnline"
                                <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','Username') ?><br><?= t('Tooltip','UsernameWildcard') ?><br>"
								value="<?= (isset($usernameOnline)) ? $usernameOnline : "" ?>" tabindex="1">
                        </form>
                    </li>							

                    <li>
                        <a title="<?= t('button','LastConnectionAttempts') ?>" href="javascript:document.replastconnect.submit();">
                            <b>&raquo;</b><img style="border:0" src="images/icons/reportsLastConnection.png">
							<?= t('button','LastConnectionAttempts') ?>
                        </a>
                        
                        <form name="replastconnect" action="rep-lastconnect.php" method="GET" class="sidebar">
                            <input name="usernameLastConnect" type="text" id="usernameLastConnect"
                                <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','Username') ?><br><?= t('Tooltip','UsernameWildcard') ?><br>"
								value="<?= (isset($usernameLastConnect)) ? $usernameLastConnect : "" ?>" tabindex="2">
                            
                            <select class="generic" name="radiusreply" tabindex="3">
                                <option value="Any">Any</option>
                                <option value="Access-Accept">Access-Accept</option>
                                <option value="Access-Reject">Access-Reject</option>
                            </select>
                            
                            <h4>Start Date</h4>
                            <img style="border:0" src="library/js_date/calendar.gif"
                                onclick="showChooser(this, 'startdate_lastconnect', 'chooserSpan', 1950, <?= date('Y', time()) ?>, 'Y-m-d', false);">
							<input name="startdate" type="date" id="startdate_lastconnect" tooltipText="<?= t('Tooltip','Date') ?>"
                                value="<?= (isset($startdate)) ? $startdate : date("Y-m-01") ?>" tabindex="4">
							<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
							
                            <h4>End Date</h4>
                            <img style="border:0" src="library/js_date/calendar.gif" 
								onclick="showChooser(this, 'enddate_lastconnect', 'chooserSpan', 1950, <?= date('Y', time()) ?>, 'Y-m-d', false);">
							<input name="enddate" type="date" id="enddate_lastconnect" tooltipText="<?= t('Tooltip','Date') ?>"
								value="<?= (isset($enddate)) ? $enddate : date("Y-m-t") ?>" tabindex="5">
                            <div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
                        </form>
                    </li>
                    
                    <li>
                        <a title="<?= strip_tags(t('button','NewUsers')) ?>" href="javascript:document.repnewusers.submit();">
                            <b>&raquo;</b><img style="border:0" src="images/icons/userList.gif">
                            <?= t('button','NewUsers') ?>
                        </a>

						<form name="repnewusers" action="rep-newusers.php" method="GET" class="sidebar">
							<h4>Start Date</h4>
							<img style="border:0" src="library/js_date/calendar.gif" 
								onclick="showChooser(this, 'startdate', 'chooserSpan', 1950, <?= date('Y', time()) ?>, 'Y-m-d', false);">
							<input name="startdate" type="date" id="startdate" tooltipText="<?= t('Tooltip','Date') ?>"
                                value="<?= (isset($startdate)) ? $startdate : date("Y-01-01") ?>" tabindex="6">
							<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
							
							<h4>End Date</h4>
							<img style="border:0" src="library/js_date/calendar.gif" 
								onclick="showChooser(this, 'enddate', 'chooserSpan', 1950, <?= date('Y', time()) ?>, 'Y-m-d', false);">
							<input name="enddate" type="date" id="enddate" tooltipText="<?= t('Tooltip','Date') ?>"
								value="<?= (isset($enddate)) ? $enddate : date("Y-m-t") ?>" tabindex="7">
							<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
 						</form>
                    </li>
					
                    <li>
                        <a title="<?= strip_tags(t('button','TopUser')) ?>" href="javascript:document.topusers.submit();"><b>&raquo;</b>
							<img style="border:0" src="images/icons/reportsTopUsers.png">
							<?= t('button','TopUser') ?>
                        </a>
                        
                        <form name="topusers" action="rep-topusers.php" method="GET" class="sidebar">
                            <input type="number" class="generic" name="limit" max="1000" min="1"
                                value="<?= (isset($limit) && intval($limit) > 0) ? $limit : "" ?>" tabindex="8">
                            
                            <h4>Username Filter</h4>
                            <input name="username" type="text" id="username" value="<?= (isset($username)) ? $username : "" ?>" tabindex="9">
			
                            <h4>Start Date</h4>
                            <img style="border:0" src="library/js_date/calendar.gif"
                                onclick="showChooser(this, 'startdate_topuser', 'chooserSpan', 1950, <?= date('Y', time()) ?>, 'Y-m-d', false);">
                            
                            <input name="startdate" type="date" id="startdate_topuser" tooltipText="<?= t('Tooltip','Date') ?>"
                                value="<?= (isset($startdate)) ? $startdate : date("Y-m-01") ?>" tabindex="10">
                            <div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
			
                            <h4>End Date</h4>
                            <img style="border:0" src="library/js_date/calendar.gif" 
                                onclick="showChooser(this, 'enddate_topuser', 'chooserSpan', 1950, <?= date('Y', time()) ?>, 'Y-m-d', false);">
                            <input name="enddate" type="date" id="enddate_topuser" tooltipText="<?= t('Tooltip','Date') ?>"
                                value="<?= (isset($enddate)) ? $enddate : date("Y-m-t") ?>" tabindex="11">
                            <div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>

                            <h4>Report By</h4>
                            <select class="generic" name="orderBy" type="text" tabindex="12">
								<option value="Time">Time</option>
                                <option value="Download">Download (bytes)</option>
                                <option value="Upload">Upload (bytes)</option>
							</select>
                        </form>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','History')) ?>" href="rep-history.php">
                            <b>&raquo;</b><img style="border:0" src="images/icons/reportsHistory.png">
							<?= t('button','History') ?>
                        </a>
                    </li>
				</ul>
            </div>

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
