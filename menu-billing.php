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

        <script src="library/js_date/date-functions.js"></script>
        <script src="library/js_date/datechooser.js"></script>
    </head>

    <body>
        <div id="wrapper">
            <div id="innerwrapper">

<?php
    include_once("include/menu/menu-items.php");
	include_once("include/menu/billing-subnav.php");
    
    $showChooser_format = "showChooser(this, '%s', 'chooserSpan', '1970', '%s', 'Y-m-d', false);";
    $chooserSpan = '<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px"></div>';
    
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
                            <label style="user-select: none" for="startdate"
                                onclick="<?= sprintf($showChooser_format, "startdate", date('Y', time())) ?>">
                                <img style="border: 0; margin-right: 5px" src="library/js_date/calendar.gif">
                                Start Date
                            </label>
                            <input name="startdate" type="text" id="startdate" value="<?= date('Y-01-01') ?>"
                                tooltipText="<?= t('Tooltip','Date') ?>">
                            <?= $chooserSpan ?>

                            <label style="user-select: none" for="enddate"
                                onclick="<?= sprintf($showChooser_format, "enddate", date('Y', time())) ?>">
                                <img style="border: 0; margin-right: 5px" src="library/js_date/calendar.gif">
                                End Date
                            </label>
                            <input name="enddate" type="text" id="enddate" value="<?= date('Y-m-d') ?>"
                                tooltipText="<?= t('Tooltip','Date') ?>">
                            <?= $chooserSpan ?>
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
                            <label style="user-select: none" for="ps-startdate"
                                onclick="<?= sprintf($showChooser_format, "ps-startdate", date('Y', time())) ?>">
                                <img style="border: 0; margin-right: 5px" src="library/js_date/calendar.gif">
                                Start Date
                            </label>
                            <input name="ps-startdate" type="text" id="ps-startdate" value="<?= date('Y-01-01') ?>"
                                 tooltipText="<?= t('Tooltip','Date') ?>">
                            <?= $chooserSpan ?>

                            <label style="user-select: none" for="ps-enddate"
                                onclick="<?= sprintf($showChooser_format, "ps-enddate", date('Y', time())) ?>">
                                <img style="border: 0; margin-right: 5px" src="library/js_date/calendar.gif">
                                End Date
                            </label>
                            <input name="ps-enddate" type="text" id="ps-enddate" value="<?= date('Y-m-d') ?>"
                                 tooltipText="<?= t('Tooltip','Date') ?>">
                            <?= $chooserSpan ?>
                        </form>
                    </li>

				</ul><!-- .subnav -->
		
				<h3>Rates Management</h3>
				<ul class="subnav">
				
                    <li><a title="Show rates" href="bill-rates-list.php"><b>&raquo;</b>Show rates</a></li>
                    <li><a title="New rate" href="bill-rates-new.php"><b>&raquo;</b>New rate</a></li>
                    <li>
                        <a title="Edit rate" href="javascript:document.billratesedit.submit();"><b>&raquo;</b>Edit rate</a>
                        <form name="billratesedit" action="bill-rates-edit.php" method="GET" class="sidebar">
                            <input name="type" type="text">
                        </form>
                    </li>
                    <li><a title="Delete rate" href="bill-rates-del.php"><b>&raquo;</b>Delete rate</a></li>
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
