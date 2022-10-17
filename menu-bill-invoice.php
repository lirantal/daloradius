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
if (strpos($_SERVER['PHP_SELF'], '/menu-bill-invoice.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Billing";

?>
<body>
    <script src="library/javascript/rounded-corners.js"></script>
    <script src="library/javascript/form-field-tooltip.js"></script>
    <link rel="stylesheet" href="css/form-field-tooltip.css" media="screen">
    <link rel="stylesheet" href="library/js_date/datechooser.css">
    <!--[if lte IE 6.5]>
    <link rel="stylesheet" href="library/js_date/select-free.css"/>
    <![endif]-->
    <script src="library/js_date/date-functions.js"></script>
    <script src="library/js_date/datechooser.js"></script>

    <div id="wrapper">
        <div id="innerwrapper">

<?php
    include_once("include/menu/menu-items.php");
	include_once("include/menu/billing-subnav.php");
	include_once("include/management/autocomplete.php");
	include_once("include/management/populate_selectbox.php");
    
    $showChooser_format = "showChooser(this, '%s', 'chooserSpan', '1970', '%s', 'Y-m-d', false);";
    $chooserSpan = '<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px"></div>';
?>

            <div id="sidebar">

                <h2>Billing</h2>
                
                <h3>Invoice Management</h3>
                <ul class="subnav">
	
                    <li>
                        <a href="javascript:document.invoicelist.submit();"><b>&raquo;</b><?= t('button','ListInvoices') ?></a>
                        
                        <form name="invoicelist" action="bill-invoice-list.php" method="GET" class="sidebar">
                            <input name="username" type="text" id="invoiceUsername" autocomplete="off"
                                tooltipText="<?= t('Tooltip','Username') ?><br>"
                                value="<?= (isset($edit_invoiceUsername)) ? $edit_invoiceUsername : "" ?>">
<?php
				$edit_invoice_status_id = (isset($edit_invoice_status_id)) ? $edit_invoice_status_id : "";
				populate_invoice_status_id("Select Invoice Status","invoice_status_id","form", '', $edit_invoice_status_id);
?>
                        </form>
                    </li>

                    <li>
                        <a href="javascript:document.invoicenew.submit();"><b>&raquo;</b><?= t('button','NewInvoice') ?></a>
                        <form name="invoicenew" action="bill-invoice-new.php" method="GET" class="sidebar">
                            <input name="username" type="text" id="invoiceUsernameNew" autocomplete="off"
                                tooltipText="<?= t('Tooltip','Username') ?><br>"
                                value="<?= (isset($edit_invoiceUsername)) ? $edit_invoiceUsername : "" ?>">
                        </form>
                    </li>

                    <li>
                        <a href="javascript:document.billinvoiceedit.submit();"><b>&raquo;</b><?= t('button','EditInvoice') ?></a>
                        <form name="billinvoiceedit" action="bill-invoice-edit.php" method="GET" class="sidebar">
                            <input name="invoice_id" type="text" id="invoiceIdEdit" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','invoiceID') ?><br>"
                                value="<?= (isset($edit_invoiceid)) ? $edit_invoiceid : "" ?>">
                        </form>
                    </li>
                    
                    <li><a href="bill-invoice-del.php"><b>&raquo;</b><?= t('button','RemoveInvoice') ?></a></li>
                </ul><!-- .subnav -->
                
                <br>
                
                <h3>Invoice Report</h3>
                <ul class="subnav">
                    <li>
                        <form name="billinvoicereport" action="bill-invoice-report.php" method="GET" class="sidebar">
                            <h109><?= t('button','BetweenDates') ?></h109><br>
                            
                            <label style="user-select: none" for="startdate"
                                onclick="<?= sprintf($showChooser_format, "startdate", date('Y', time())) ?>">
                                <img style="border: 0; margin-right: 5px" src="library/js_date/calendar.gif">
                                Start Date
                            </label>
                            <input name="startdate" type="text" id="startdate" tooltipText="<?= t('Tooltip','Date') ?><br>"
                                value="<?= (isset($billinvoice_startdate)) ? $billinvoice_startdate : date("Y-m-01") ?>">
                            <?= $chooserSpan ?>

                            <label style="user-select: none" for="enddate"
                                onclick="<?= sprintf($showChooser_format, "enddate", date('Y', time())) ?>">
                                <img style="border: 0; margin-right: 5px" src="library/js_date/calendar.gif">
                                End Date
                            </label>
                            <input name="enddate" type="text" id="enddate" tooltipText="<?= t('Tooltip','Date') ?><br>"
                                value="<?= (isset($billinvoice_enddate)) ? $billinvoice_enddate : date("Y-m-t") ?>">
                            <?= $chooserSpan ?>

                            <br>

<?php
                            include_once('include/management/populate_selectbox.php');
                            populate_invoice_status_id("All Invoice Types", "invoice_status", "form", "", "%");
?>

                            <input name="username" type="text" id="usernameEdit" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','Username') ?><br>"
                                value="<?= (isset($billinvoice_username) && $billinvoice_username != '%') ? $billinvoice_username : "" ?>">
                                
                            <br>
                            
                            <input class="sidebutton" type="submit" name="submit" value="<?= t('button','GenerateReport') ?>">
                        </form>
                    </li>
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->

<script>
<?php
    if ($autoComplete) {
?>
    /** Making usernameEdit, invoiceUsername and invoiceUsernameNew interactive **/
    var autoComEditElements = ["usernameEdit","invoiceUsername","invoiceUsernameNew"];
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
