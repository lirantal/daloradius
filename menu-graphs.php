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
if (strpos($_SERVER['PHP_SELF'], '/menu-graphs.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Graphs";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $langCode ?>" lang="<?= $langCode ?>">
<head>
    <title>daloRADIUS :: <?= $m_active ?></title>
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
	include_once("include/menu/menu-items.php");
	include_once("include/menu/graphs-subnav.php");
	include_once("include/management/autocomplete.php");
    
    $timeunit_options = array(
                                "daily" => t('all','Daily'),
                                "monthly" => t('all','Monthly'),
                                "yearly" => t('all','Yearly')
                             );
    
    $sizeunit_options = array(
                                "megabytes" => t('all','Megabytes'),
                                "gigabytes" => t('all','Gigabytes')
                             );
                             
    $showChooser_format = "showChooser(this, '%s', 'chooserSpan', '1970', '%s', 'Y-m-d', false);";
    $chooserSpan = '<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px"></div>';
?>
        <div id="sidebar">

            <h2>Graphs</h2>

            <h3>User Graph</h3>
            <ul class="subnav">

                <li>
                    <a title="<?= strip_tags(t('button','UserLogins')) ?>" tabindex="1" href="javascript:document.overall_logins.submit();">
                        <b>&raquo;</b><img style="border: 0; margin-right: 5px" src="images/icons/graphsGeneral.gif"><?= t('button','UserLogins') ?>
                    </a>
                    <form name="overall_logins" action="graphs-overall_logins.php" method="GET" class="sidebar">
                        <input tabindex="2" name="username" type="text" id="usernameLogins"
                            <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                            tooltipText="<?= t('Tooltip','Username'); ?><br>"
                            value="<?= (isset($overall_logins_username)) ? $overall_logins_username : "" ?>">
                        
                        <select tabindex="3" class="generic" name="type">
<?php
                        foreach ($timeunit_options as $value => $label) {
                            $selected = (isset($overall_logins_type) && $overall_logins_type == $value) ? "selected" : "";
                            printf('<option value="%s"%s>%s</option>', $value, $selected, $label);
                        }
?>

                        </select>
                    </form>
                </li>

                <li>
                    <a title="<?= strip_tags(t('button','UserDownloads')) ?>" tabindex="4" href="javascript:document.overall_download.submit();">
                        <b>&raquo;</b><img style="border: 0; margin-right: 5px" src="images/icons/graphsGeneral.gif"><?= t('button','UserDownloads') ?>
                    </a>
                    <form name="overall_download" action="graphs-overall_download.php" method="GET" class="sidebar">
                        <input tabindex="5" name="username" type="text" id="usernameDownloads"
                            <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                            tooltipText="<?= t('Tooltip','Username'); ?><br>"
                            value="<?= (isset($overall_download_username)) ? $overall_download_username : "" ?>">

                        <select tabindex="6" class="generic" name="type">
<?php
                        foreach ($timeunit_options as $value => $label) {
                            $selected = (isset($overall_download_type) && $overall_download_type == $value) ? "selected" : "";
                            printf('<option value="%s"%s>%s</option>', $value, $selected, $label);
                        }
?>

                        </select>

                        <select tabindex="7" class="generic" name="size">
<?php
                        foreach ($sizeunit_options as $value => $label) {
                            $selected = (isset($overall_download_size) && $overall_download_size == $value) ? "selected" : "";
                            printf('<option value="%s"%s>%s</option>', $value, $selected, $label);
                        }
?>

                        </select>
                    </form>
                </li>

                <li>
                    <a title="<?= strip_tags(t('button','UserUploads')) ?>" tabindex="8" href="javascript:document.overall_upload.submit();">
                        <b>&raquo;</b><img style="border: 0; margin-right: 5px" src="images/icons/graphsGeneral.gif">
                        <?= t('button','UserUploads') ?>
                    </a>
                    <form name="overall_upload" action="graphs-overall_upload.php" method="GET" class="sidebar">
                        <input tabindex="9" name="username" type="text" id="usernameUploads"
                            <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                            tooltipText="<?= t('Tooltip','Username'); ?><br>"
                            value="<?= (isset($overall_upload_username)) ? $overall_upload_username : "" ?>">
                    
                        <select tabindex="10" class="generic" name="type">
<?php
                        foreach ($timeunit_options as $value => $label) {
                            $selected = (isset($overall_upload_type) && $overall_upload_type == $value) ? "selected" : "";
                            printf('<option value="%s"%s>%s</option>', $value, $selected, $label);
                        }
?>

                        </select>

                        <select tabindex="10" class="generic" name="size">
<?php
                        foreach ($sizeunit_options as $value => $label) {
                            $selected = (isset($overall_upload_size) && $overall_upload_size == $value) ? "selected" : "";
                            printf('<option value="%s"%s>%s</option>', $value, $selected, $label);
                        }
?>

                        </select>
                    </form>
                </li>
                
            </ul>

            <h3>Statistics</h3>
            <ul class="subnav">
                <li>
                    <a title="<?= strip_tags(t('button','TotalLogins')) ?>" tabindex="11" href="javascript:document.alltime_logins.submit();">
                        <b>&raquo;</b><img style="border: 0; margin-right: 5px" src="images/icons/graphsGeneral.gif">
                        <?= t('button','TotalLogins') ?>
                    </a>
                    <form name="alltime_logins" action="graphs-alltime_logins.php" method="GET" class="sidebar">
                        <select tabindex="12" class="generic" name="type">
<?php
                        foreach ($timeunit_options as $value => $label) {
                            $selected = (isset($alltime_login_type) && $alltime_login_type == $value) ? "selected" : "";
                            printf('<option value="%s"%s>%s</option>', $value, $selected, $label);
                        }
?>

                        </select>
                    </form>
                </li>

                <li>
                    <a title="<?= strip_tags(t('button','TotalTraffic')) ?>" tabindex="13" href="javascript:document.alltime_traffic_compare.submit();">
                        <b>&raquo;</b><img style="border: 0; margin-right: 5px" src="images/icons/graphsGeneral.gif">
                        <?= t('button','TotalTraffic') ?>
                    </a>
                    <form name="alltime_traffic_compare" action="graphs-alltime_traffic_compare.php" method="GET" class="sidebar">
                        <select tabindex="14" class="generic" name="type">
<?php
                        foreach ($timeunit_options as $value => $label) {
                            $selected = (isset($traffic_compare_type) && $traffic_compare_type == $value) ? "selected" : "";
                            printf('<option value="%s"%s>%s</option>', $value, $selected, $label);
                        }
?>

                        </select>

                        <select tabindex="15" class="generic" name="size">
<?php
                        foreach ($sizeunit_options as $value => $label) {
                            $selected = (isset($traffic_compare_size) && $traffic_compare_size == $value) ? "selected" : "";
                            printf('<option value="%s"%s>%s</option>', $value, $selected, $label);
                        }
?>

                        </select>
                    </form>
                </li>

                <li>
                    <a title="<?= strip_tags(t('button','LoggedUsers')) ?>" tabindex="16" href="javascript:document.logged_users.submit();">
                        <b>&raquo;</b><img style="border: 0; margin-right: 5px" src="images/icons/graphsGeneral.gif">
                        <?= t('button','LoggedUsers') ?>
                    </a>
                    <form name="logged_users" action="graphs-logged_users.php" method="GET" class="sidebar">
                        
                        <label style="user-select: none" for="logged_users_on_date"
                            onclick="<?= sprintf($showChooser_format, "logged_users_on_date", date('Y', time())) ?>">
                            <img style="border: 0; margin-right: 5px" src="library/js_date/calendar.gif">
                            Date
                        </label>
                        <input tabindex="17" type="text" name="logged_users_on_date" id="logged_users_on_date"
                            tooltipText="<?= t('Tooltip','Date') ?>"
                            value="<?= (isset($logged_users_on_date)) ? $logged_users_on_date : date("Y-m-d") ?>">
                        <?= $chooserSpan ?>
                        
                        
                    </form>
                </li>
            </ul>
        </div>

<script>
<?php
    if ($autoComplete) {
?>

    var autoComEditElements = ["usernameLogins","usernameDownloads","usernameUploads"];
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
