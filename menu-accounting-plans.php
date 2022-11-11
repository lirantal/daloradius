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

$m_active = "Accounting";

include_once("include/menu/menu-items.php");
include_once("include/menu/accounting-subnav.php");
include_once("include/management/autocomplete.php");

$showChooser_format = "showChooser(this, '%s', 'chooserSpan', '1970', '%s', 'Y-m-d', false);";
$chooserSpan = '<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px"></div>';
?>	

            <div id="sidebar">
                <h2>Plan Accounting</h2>
                
                <h3>Accounting</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= strip_tags(t('button','PlanUsage')) ?>" href="javascript:document.acctdate.submit();">
                            <b>&raquo;</b><?= t('button','PlanUsage') ?>
                        </a>
                        <form name="acctdate" action="acct-plans-usage.php" method="GET" class="sidebar">
                            <input name="username" type="text" id="usernamePlan"
                                <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText='<?= t('Tooltip','Username'); ?>'
                                value="<?= (isset($accounting_plan_username)) ? $accounting_plan_username : "" ?>">
                            
                            <label style="user-select: none" for="startdate"
                                onclick="<?= sprintf($showChooser_format, "startdate", date('Y', time())) ?>">
                                <img style="border: 0; margin-right: 5px" src="library/js_date/calendar.gif">
                                Start Date
                            </label>
                            <input name="startdate" type="text" id="startdate" tooltipText="<?= t('Tooltip','Date'); ?>"
                                value="<?= (isset($accounting_plan_startdate)) ? $accounting_plan_startdate: date("Y-m-01") ?>">
                            <?= $chooserSpan ?>

                            <label style="user-select: none" for="enddate"
                                onclick="<?= sprintf($showChooser_format, "enddate", date('Y', time())) ?>">
                                <img style="border: 0; margin-right: 5px" src="library/js_date/calendar.gif">
                                End Date
                            </label>
                            <input name="enddate" type="text" id="enddate" tooltipText="<?= t('Tooltip','Date'); ?>"
                                value="<?= (isset($accounting_plan_enddate)) ? $accounting_plan_enddate : date("Y-m-t") ?>">
                            <?= $chooserSpan ?>

                            <br><br>
<?php   
                            include('include/management/populate_selectbox.php');
                            populate_plans("Select Plan", "planname", "generic");
?>
                        </form>
                    </li>
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->

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
