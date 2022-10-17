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
if (strpos($_SERVER['PHP_SELF'], '/menu-accounting-custom.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Accounting";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $langCode ?>" lang="<?= $langCode ?>">
<head>
    <title>daloRADIUS :: <?= $m_active ?></title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">

    <link rel="stylesheet" href="css/1.css" media="screen">
    <link rel="stylesheet" href="library/js_date/datechooser.css">
    <!--[if lte IE 6.5]>
    <link rel="stylesheet" href="library/js_date/select-free.css">
    <![endif]-->
    <link rel="stylesheet" href="css/form-field-tooltip.css" media="screen">

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
	include_once("include/menu/accounting-subnav.php");
    
    $options = array(
                        "RadAcctId",
                        "AcctSessionId",
                        "AcctUniqueId",
                        "UserName",
                        "Realm",
                        "NASIPAddress",
                        "NASPortId",
                        "NASPortType",
                        "AcctStartTime",
                        "AcctStopTime",
                        "AcctSessionTime",
                        "AcctAuthentic",
                        "ConnectInfo_start",
                        "ConnectInfo_stop",
                        "AcctInputOctets",
                        "AcctOutputOctets",
                        "CalledStationId",
                        "CallingStationId",
                        "AcctTerminateCause",
                        "ServiceType",
                        "FramedProtocol",
                        "FramedIPAddress",
                        "AcctStartDelay",
                        "AcctStopDelay"
                    );

    $options_checked = array("UserName", "Realm", "NASIPAddress", "AcctStartTime", "AcctStopTime", "AcctSessionTime",
                             "AcctInputOctets", "AcctOutputOctets", "CalledStationId", "CallingStationId",
                             "AcctTerminateCause", "FramedIPAddress");
    
    $showChooser_format = "showChooser(this, '%s', 'chooserSpan', '1970', '%s', 'Y-m-d', false);";
    $chooserSpan = '<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px"></div>';
    
?>	
            <div id="sidebar">
                <h2>Accounting</h2>
                
                <h3>Custom Query</h3>
                <ul class="subnav">
                
                    <li>
                        <form name="acctcustomquery" action="acct-custom-query.php" method="get" class="sidebar">
                            <input class="sidebutton" type="submit" name="submit" value="<?= t('button','ProcessQuery') ?>">
                            <br><br>
                            
                            <h109><?= t('button','BetweenDates') ?></h109><br>
                            
                            <label style="user-select: none" for="startdate"
                                onclick="<?= sprintf($showChooser_format, "startdate", date('Y', time())) ?>">
                                <img style="border: 0; margin-right: 5px" src="library/js_date/calendar.gif">
                                Start Date
                            </label>
                            <input name="startdate" type="text" id="startdate" tooltipText="<?= t('Tooltip','Date') ?><br>"
                                value="<?= (isset($accounting_custom_startdate)) ? $accounting_custom_startdate : date("Y-m-01") ?>">
                            <?= $chooserSpan ?>

                            <label style="user-select: none" for="enddate"
                                onclick="<?= sprintf($showChooser_format, "enddate", date('Y', time())) ?>">
                                <img style="border: 0; margin-right: 5px" src="library/js_date/calendar.gif">
                                End Date
                            </label>
                            <input name="enddate" type="text" id="enddate" tooltipText="<?= t('Tooltip','Date') ?><br>"
                                value="<?= (isset($accounting_custom_enddate)) ? $accounting_custom_enddate : date("Y-m-t") ?>">
                            <?= $chooserSpan ?>
                            
                            <br><br>
            
                            <h109><?= t('button','Where') ?></h109><br>
                            <div style="text-aling: center">
                                <select name="fields" size="1" class="generic">
<?php
                                foreach ($options as $option) {
                                    printf('<option value="%s">%s</option>', $option, $option);
                                }
?>

                                </select><!-- .generic -->

                                <select name="operator" size="1" class="generic">
                                    <option value="=">Equals</option>
                                    <option value="LIKE">Contains</option>
                                </select><!-- .generic -->
                            </div>
                            
                            <input type="text" name="where_field" tooltipText="<?= t('Tooltip','Filter') ?><br>"
                                value="<?= (isset($accounting_custom_value)) ? $accounting_custom_value : "" ?>">
                            <br><br>
                            
                            <h109><?= t('button','AccountingFieldsinQuery') ?></h109><br>
<?php
                            foreach ($options as $option) {
                                $checked = in_array($option, $options_checked) ? ' checked' : '';
                                printf('<input type="checkbox" name="sqlfields[]" value="%s"%s><h109>%s<h109><br>',
                                       $option, $checked, $option);
                            }
?>

                            Select:
                            <a class="table" href="javascript:SetChecked(1,'sqlfields[]','acctcustomquery')">All</a>
                            <a class="table" href="javascript:SetChecked(0,'sqlfields[]','acctcustomquery')">None</a>
                            <br><br>
                            
                            <h109><?= t('button','OrderBy') ?><h109><br>
                            <div style="text-aling: center">
                                <select name="orderBy" size="1" class="generic">
<?php
                            foreach ($options as $option) {
                                printf('<option value="%s">%s</option>', $option, $option);
                            }
?>

                                </select><!-- .generic -->

                                <select name="orderType" size="1" class="generic">
                                    <option value="ASC">Ascending</option>
                                    <option value="DESC">Descending</option>
                                </select><!-- .generic -->
                            </div>
                            <br>
                            
                            <input class="sidebutton" type="submit" name="submit" value="<?= t('button','ProcessQuery') ?>">
                        </form>
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
