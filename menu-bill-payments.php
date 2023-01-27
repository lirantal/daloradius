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
if (strpos($_SERVER['PHP_SELF'], '/menu-bill-payments.php') !== false) {
    header("Location: index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Billing";



include_once("include/management/autocomplete.php");
?>

            <div id="sidebar">

                <h2>Billing</h2>

                <h3>Payments Management</h3>
                <ul class="subnav">

                    <li>
                        <a title="<?= strip_tags(t('button','ListPayments')) ?>" href="javascript:document.paymentslist.submit();">
                            <b>&raquo;</b><?= t('button','ListPayments') ?>
                        </a>
                    
                        <form name="paymentslist" action="bill-payments-list.php" method="GET" class="sidebar">
                            <input name="username" type="text" id="username" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','Username'); ?><br>" value="<?= (isset($edit_username)) ? $edit_username : "" ?>">
                            <input name="invoice_id" type="text" id="invoice_id" tooltipText="<?= t('Tooltip','invoiceID'); ?><br>"
                                value="<?= (isset($edit_invoice_id)) ? $edit_invoice_id : "" ?>">
                        </form>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','NewPayment')) ?>" href="bill-payments-new.php">
                            <b>&raquo;</b><?= t('button','NewPayment') ?>
                        </a>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','EditPayment')) ?>" href="javascript:document.paymentsedit.submit();">
                            <b>&raquo;</b><?= t('button','EditPayment') ?></a>
                        
                        <form name="paymentsedit" action="bill-payments-edit.php" method="GET" class="sidebar">
                            <input name="payment_id" type="text" id="payment_id" tooltipText="<?= t('Tooltip','PaymentId'); ?><br>"
                                value="<?= (isset($edit_payment_id)) ? $edit_payment_id : "" ?>">
                        </form>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','RemovePayment')) ?>" href="bill-payments-del.php">
                            <b>&raquo;</b><?= t('button','RemovePayment') ?>
                        </a>
                    </li>
                </ul><!-- .subnav -->


                <h3>Payment Types Management</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= strip_tags(t('button','ListPayTypes')) ?>" href="bill-payment-types-list.php">
                            <b>&raquo;</b><?= t('button','ListPayTypes') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','NewPayType')) ?>" href="bill-payment-types-new.php">
                            <b>&raquo;</b><?= t('button','NewPayType') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','EditPayType')) ?>" href="javascript:document.paymenttypesedit.submit();">
                            <b>&raquo;</b><?= t('button','EditPayType') ?>
                        </a>
                        
                        <form name="paymenttypesedit" action="bill-payment-types-edit.php" method="GET" class="sidebar">
                            <input name="paymentname" type="text" id="paymentname" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','PayTypeName'); ?><br>"
                                value="<?= (isset($edit_paymentName)) ? $edit_paymentName : "" ?>">
                        </form>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','RemovePayType')) ?>" href="bill-payment-types-del.php">
                            <b>&raquo;</b><?= t('button','RemovePayType') ?>
                        </a>
                    </li>
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->

<script>
<?php
    if ($autoComplete) {
?>
    /** Making username interactive **/
    var autoComEdit = new DHTMLSuite.autoComplete();
    autoComEdit.add('username','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');
<?php
    }
?>

    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>
