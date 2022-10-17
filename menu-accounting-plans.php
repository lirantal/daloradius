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
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/menu-accounting-plans.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $langCode ?>" lang="<?= $langCode ?>">
<head>
    <title>daloRADIUS :: Accounting / Plans</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="css/1.css" media="screen">
    <link rel="stylesheet" href="css/form-field-tooltip.css" media="screen">
    <link rel="stylesheet" href="library/js_date/datechooser.css">
    <!--[if lte IE 6.5]>
    <link rel="stylesheet" href="library/js_date/select-free.css">
    <![endif]-->

    <script src="library/js_date/date-functions.js"></script>
    <script src="library/js_date/datechooser.js"></script>
    <script src="library/javascript/pages_common.js"></script>
    <script src="library/javascript/rounded-corners.js"></script>
    <script src="library/javascript/form-field-tooltip.js"></script>
    <script src="library/javascript/ajax.js"></script>
    <script src="library/javascript/ajaxGeneric.js"></script>
</head>

<body>
    <div id="wrapper">
        <div id="innerwrapper">

<?php
	$m_active = "Accounting";
	include_once("include/menu/menu-items.php");
	include_once("include/menu/accounting-subnav.php");
	include_once("include/management/autocomplete.php");
?>	

            <div id="sidebar">
                <h2>Plan Accounting</h2>
                
                <h3>Accounting</h3>
                <ul class="subnav">
                    <li>
                        <a href="javascript:document.acctdate.submit();">
                            <b>&raquo;</b><?= t('button','PlanUsage') ?>
                        </a>
                        <form name="acctdate" action="acct-plans-usage.php" method="get" class="sidebar">
                            <input name="username" type="text" id="usernamePlan"
                                <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText='<?= t('Tooltip','Username'); ?>'
                                value="<?= (isset($accounting_plan_username)) ? $accounting_plan_username : "" ?>">
                            
                            <input name="startdate" type="date" id="startdate" tooltipText="<?= t('Tooltip','Date'); ?>"
                                value="<?= (isset($accounting_plan_startdate)) ? $accounting_plan_startdate: date("Y-m-01") ?>">
                            
                            <img src="library/js_date/calendar.gif"
                                onclick="showChooser(this, 'startdate', 'chooserSpan', 1950, <?= date('Y', time()) ?>, 'Y-m-d', false);">
                            <div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>

                            <input name="enddate" type="date" id="enddate" tooltipText="<?= t('Tooltip','Date'); ?>"
                                value="<?= (isset($accounting_plan_enddate)) ? $accounting_plan_enddate : date("Y-m-t") ?>">

                            <img src="library/js_date/calendar.gif" 
                                onclick="showChooser(this, 'enddate', 'chooserSpan', 1950, <?= date('Y', time()) ?>, 'Y-m-d', false);">
                            <div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>

                            <br><br>
<?php   
				include('include/management/populate_selectbox.php');
				populate_plans("Select Plan", "planname", "generic");
?>
                    </li>
                </form>
            </ul>
            <br><br>
        </div>

<script>
<?php
    if ($autoComplete) {
?>
    var autoComEdit = new DHTMLSuite.autoComplete();
    autoComEdit.add('usernamePlan','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');
<?php
    }
?>
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>
