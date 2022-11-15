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
if (strpos($_SERVER['PHP_SELF'], '/menu-billing.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Billing";

include_once("include/menu/menu-items.php");
include_once("include/menu/billing-subnav.php");


$hotspot_options = "";

include('library/opendb.php');
$sql = sprintf("select name from %s", $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
$res = $dbSocket->query($sql);
while($row = $res->fetchRow()) {
    $name = htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8');
    $hotspot_options .= sprintf('<option value="%s">%s</option>', $name, $name);
}
include('library/closedb.php');
    
?>      

                <div id="sidebar">
		
				<h2>Billing</h2>
				
				<h3>Billing Engine</h3>
				<ul class="subnav">
				
                    <li>
                        <a title="Prepaid Accounting" href="javascript:document.billprepaidhotspot.submit();">
                            <b>&raquo;</b>Prepaid Accounting
                        </a>
                        <form name="billprepaidhotspot" action="bill-prepaid.php" method="GET" class="sidebar">
                            <select name="hotspot" size="3">
                                <option value="%">all</option>
                                <?= $hotspot_options ?>
                            </select>
                            
                            <br><br>
                            
                            Filter by date
                            <label style="user-select: none" for="startdate"><?= t('all','StartingDate') ?></label>
                            <input name="startdate" type="date" id="startdate" value="<?= date('Y-01-01') ?>"
                                tooltipText="<?= t('Tooltip','Date') ?>">

                            <label style="user-select: none" for="enddate"><?= t('all','EndingDate') ?></label>
                            <input name="enddate" type="date" id="enddate" value="<?= date('Y-m-d') ?>"
                                tooltipText="<?= t('Tooltip','Date') ?>">
                        </form>
                    </li>


                    <li>
                        <a title="Per-second Accounting" href="javascript:document.billpersecondhotspot.submit();">
                            <b>&raquo;</b>Per-second Accounting
                        </a>
                        <form name="billpersecondhotspot" action="bill-persecond.php" method="GET" class="sidebar">
                            <select name="ps-hotspot" size="3">
                                <option value="%">all</option>
                                <?= $hotspot_options ?>
                            </select>
                            
                            <br><br>

                            Filter by date
                            <label style="user-select: none" for="ps-startdate"><?= t('all','StartingDate') ?></label>
                            <input name="ps-startdate" type="date" id="ps-startdate" value="<?= date('Y-01-01') ?>"
                                 tooltipText="<?= t('Tooltip','Date') ?>">

                            <label style="user-select: none" for="ps-enddate"><?= t('all','EndingDate') ?></label>
                            <input name="ps-enddate" type="date" id="ps-enddate" value="<?= date('Y-m-d') ?>"
                                 tooltipText="<?= t('Tooltip','Date') ?>">
                        </form>
                    </li>

				</ul><!-- .subnav -->
		
				<h3>Rates Management</h3>
				<ul class="subnav">
				
                    <li><a title="Show rates" href="bill-rates-list.php"><b>&raquo;</b><?= t('button','ListRates') ?></a></li>
                    <li><a title="New rate" href="bill-rates-new.php"><b>&raquo;</b><?= t('button','NewRate') ?></a></li>
                    <li>
                        <a title="Edit rate" href="javascript:document.billratesedit.submit();"><b>&raquo;</b><?= t('button','EditRate') ?></a>
                        <form name="billratesedit" action="bill-rates-edit.php" method="GET" class="sidebar">
                            <input name="type" type="text">
                        </form>
                    </li>
                    <li><a title="Delete rate" href="bill-rates-del.php"><b>&raquo;</b><?= t('button','RemoveRate') ?></a></li>
				</ul><!-- .subnav -->
            </div><!-- #sidebar -->
		
<?php
    if isset($actionStatus) {
?>
            <div id="contentnorightbar">
                <h9 id="Intro"><?= ucfirst($actionStatus) ?></h9>
                <br><br>
                <span style="color: <?= ($actionStatus == "success") ? "green" : "red" ?>">
                    <?= $actionMsg ?>
                </span>
            </div>
<?php
    }
?>

<script>
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>
