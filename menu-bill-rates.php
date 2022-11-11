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
if (strpos($_SERVER['PHP_SELF'], '/menu-bill-rates.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Billing";

include_once("include/menu/menu-items.php");
include_once("include/menu/billing-subnav.php");
include_once("include/management/autocomplete.php");

$showChooser_format = "showChooser(this, '%s', 'chooserSpan', '1970', '%s', 'Y-m-d', false);";
$chooserSpan = '<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px"></div>';
?>

            <div id="sidebar">

                <h2>Billing</h2>

                <h3>Track Rates</h3>
                
                <ul class="subnav">

                <li>
                    <a title="<?= strip_tags(t('button','DateAccounting')) ?>" href="javascript:document.billrates.submit();">
                        <b>&raquo;</b><?= t('button','DateAccounting') ?>
                    </a>
                    
                    <form name="billrates" action="bill-rates-date.php" method="GET" class="sidebar">
                        <select name="ratename" size="1" class="generic">
                            <option value="<?= (isset($billing_date_ratename)) ? $billing_date_ratename : "" ?>">
                                <?php (isset($billing_date_ratename)) ? $billing_date_ratename : "Choose Rate" ?>
                            </option>
<?php
                            include('library/opendb.php');
                            $sql = sprintf("SELECT rateName FROM %s", $configValues['CONFIG_DB_TBL_DALOBILLINGRATES']);
                            $res = $dbSocket->query($sql);

                            while ($row = $res->fetchRow()) {
                                $rateName =  htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8');
                                printf('<option value="%s"></option>', $rateName, $rateName);
                            }
                            include('library/closedb.php');
?>
                        </select><!-- .generic -->

                        <input name="username" type="text" id="username" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                            tooltipText="<?= t('Tooltip','Username'); ?><br>"
                            value="<?php (isset($billing_date_username)) ? $billing_date_username : "" ?>">

                        <label style="user-select: none" for="startdate"
                            onclick="<?= sprintf($showChooser_format, "startdate", date('Y', time())) ?>">
                            <img style="border: 0; margin-right: 5px" src="library/js_date/calendar.gif">
                            Start Date
                        </label>
                        <input name="startdate" type="text" id="startdate" tooltipText="<?= t('Tooltip','Date'); ?><br>"
                            value="<?= (isset($billing_date_startdate)) ? $billing_date_startdate : date("Y-m-01") ?>">
                        <?= $chooserSpan ?>

                        <label style="user-select: none" for="enddate"
                            onclick="<?= sprintf($showChooser_format, "enddate", date('Y', time())) ?>">
                            <img src="library/js_date/calendar.gif">
                            End Date
                        </label>
                        <input name="enddate" type="text" id="enddate" tooltipText="<?= t('Tooltip','Date'); ?><br>"
                            value="<?= (isset($billing_date_enddate)) ? $billing_date_enddate : date("Y-m-t") ?>">
                        <?= $chooserSpan ?>
                        
                    </form>
                </li>
            </ul><!-- .subnav -->

            <h3>Rates Management</h3>
            <ul class="subnav">
                <li>
                    <a title="<?= strip_tags(t('button','ListRates')) ?>" href="bill-rates-list.php">
                        <b>&raquo;</b><?= t('button','ListRates') ?>
                    </a>
                </li>
                <li>
                    <a title="<?= strip_tags(t('button','NewRate')) ?>" href="bill-rates-new.php">
                        <b>&raquo;</b><?= t('button','NewRate') ?>
                    </a>
                </li>
                <li>
                    <a title="<?= strip_tags(t('button','EditRate')) ?>" href="javascript:document.billratesedit.submit();">
                            <b>&raquo;</b><?= t('button','EditRate') ?></a>
                    <form name="billratesedit" action="bill-rates-edit.php" method="GET" class="sidebar">
                        <input name="ratename" type="text" id="ratename" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                            tooltipText="<?= t('Tooltip','RateName'); ?><br>"
                            value="<?= (isset($edit_rateName)) ? $edit_rateName : "" ?>">
                    </form>
                </li>
                <li>
                    <a title="<?= strip_tags(t('button','RemoveRate')) ?>" href="bill-rates-del.php">
                        <b>&raquo;</b><?= t('button','RemoveRate') ?>
                    </a>
                </li>
            </ul><!-- .subnav -->
        </div><!-- #sidebar -->

<script>
<?php
    if ($autoComplete) {
?>
    var autoComEdit = new DHTMLSuite.autoComplete();
    autoComEdit.add('username','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');

    autoComEdit = new DHTMLSuite.autoComplete();
    autoComEdit.add('ratename','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteRateName');
<?php
    }
?>
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>
