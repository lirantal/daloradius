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
if (strpos($_SERVER['PHP_SELF'], '/menu-bill-plans.php') !== false) {
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
    
    <div id="wrapper">
        <div id="innerwrapper">

<?php
    include_once("include/menu/menu-items.php");
	include_once("include/menu/billing-subnav.php");
	include_once("include/management/autocomplete.php");
?>

            <div id="sidebar">

                <h2>Billing</h2>
                
                <h3>Plans Management</h3>
                <ul class="subnav">
                
                    <li>
                        <a title="<?= t('button','ListPlans') ?>" tabindex="1" href="bill-plans-list.php">
                            <b>&raquo;</b><?= t('button','ListPlans') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= t('button','NewPlan') ?>" tabindex="2" href="bill-plans-new.php">
                            <b>&raquo;</b><?= t('button','NewPlan') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= t('button','EditPlan') ?>" tabindex="3" href="javascript:document.billplansedit.submit();">
                            <b>&raquo;</b><?= t('button','EditPlan') ?></a>
                        <form name="billplansedit" action="bill-plans-edit.php" method="GET" class="sidebar">
                            <input tabindex="4" name="planName" type="text" id="planNameEdit" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','BillingPlanName'); ?><br>"
                                value="<?= (isset($edit_planname)) ? $edit_planname : "" ?>">
                        </form>
                    </li>
                    <li>
                        <a title="<?= t('button','RemovePlan') ?>" tabindex="5" href="bill-plans-del.php">
                            <b>&raquo;</b><?= t('button','RemovePlan') ?>
                        </a>
                    </li>
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->

<script>
<?php
if ($autoComplete) {
?>
    /** Making planNameEdit interactive **/
    var autoComEdit = new DHTMLSuite.autoComplete();
    autoComEdit.add('planNameEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteBillingPlans');
<?php
}
?>
    
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>
