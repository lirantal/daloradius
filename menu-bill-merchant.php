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
if (strpos($_SERVER['PHP_SELF'], '/menu-bill-merchant.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Billing";

include_once("include/menu/menu-items.php");
include_once("include/menu/billing-subnav.php");
    
$checkboxes = array(
                        "id" => t('all','ID'),
                        "username" => t('all','Username'),
                        "password"  => t('all','Password'),
                        "txnId"  => t('all','TxnId'),
                        "planName" => t('all','PlanName'),
                        "planId"  => t('all','PlanId'),
                        "quantity"  => t('all','Quantity'),
                        "business_email"  => t('all','ReceiverEmail'),
                        "business_id"  => t('all','Business'),
                        "payment_tax" => t('all','Tax'),
                        "payment_cost"  => t('all','Cost'),
                        "payment_fee" => t('all','TransactionFee'),
                        "payment_total" => t('all','TotalCost'),
                        "payment_currency" => t('all','PaymentCurrency'),
                        "first_name" => t('all','FirstName'),
                        "last_name" => t('all','LastName'),
                        "payer_email" => t('all','PayerEmail'),
                        "payer_address_name"  => t('all','AddressRecipient'),
                        "payer_address_street"  => t('all','Street'),
                        "payer_address_country" => t('all','Country'),
                        "payer_address_country_code"  => t('all','CountryCode'),
                        "payer_address_city" => t('all','City'),
                        "payer_address_state" => t('all','State'),
                        "payer_address_zip"  => t('all','Zip'),
                        "payment_date" => t('all','PaymentDate'),
                        "payment_status" => t('all','PaymentStatus'),
                        "payer_status" => t('all','PayerStatus'),
                        "payment_address_status" => t('all','PaymentAddressStatus'),
                        "vendor_type" => t('all','VendorType')
                   );
$checkboxes_checked = array(
                            "username",
                            "planName",
                            "payment_fee",
                            "payment_total",
                            "payment_currency",
                            "first_name",
                            "last_name",
                            "payer_email",
                            "payer_address_country",
                            "payer_address_city",
                            "payer_address_state",
                            "payment_date",
                            "payment_status",
                            "vendor_type"
                           );

?>

                <div id="sidebar">
                    <h2>Billing</h2>
                    
                    <h3>Track PayPal Transactions</h3>
                    <ul class="subnav">
                        <li>
                            <form name="billpaypaltransactions" action="bill-merchant-transactions.php" method="GET" class="sidebar">
                                <input class="sidebutton" type="submit" name="submit" value="<?= t('button','ProcessQuery') ?>">
                                
                                <br><br>
                                
                                <h109><?= t('button','BetweenDates'); ?></h109><br>
                                
                                <label style="user-select: none" for="startdate"><?= t('all','StartingDate') ?></label>
                                <input name="startdate" type="date" id="startdate" tooltipText="<?= t('Tooltip','Date') ?>"
                                    value="<?= (isset($billing_date_startdate)) ? $billing_date_startdate : date("Y-m-01") ?>">

                                <label style="user-select: none" for="enddate"><?= t('all','EndingDate') ?></label>
                                <input name="enddate" type="text" id="enddate" tooltipText="<?= t('Tooltip','Date') ?>"
                                    value="<?= (isset($billing_date_enddate)) ? $billing_date_enddate : date("Y-m-t") ?>">

                                <br><br>

                                <h109><?= t('all','VendorType'); ?></h109><br>
                                <select name="vendor_type" size="1">
                                    <option value="<?= (isset($billing_paypal_vendor_type)) ? $billing_paypal_vendor_type : "%" ?>">
                                        <?= (isset($billing_paypal_vendor_type)) ? $billing_paypal_vendor_type : "Any" ?>
                                    </option>
                                    <option value=""></option>
                                    <option value="%">Any</option>
                                    <option value="PayPal">PayPal</option>
                                    <option value="2Checkout">2Checkout</option>
                                </select>
                                
                                <h109><?= t('all','PayerEmail'); ?></h109><br>
                                <input name="payer_email" type="text"
                                    value="<?= (isset($billing_paypal_payeremail)) ? $billing_paypal_payeremail : "*" ?>">
			
                                <br><br>

                                <h109><?= t('all','PaymentStatus'); ?></h109><br>
                                <select name="payment_status" size="1">
                                    <option value="<?= (isset($billing_paypal_paymentstatus)) ? $billing_paypal_paymentstatus : "%" ?>">
                                        <?= (isset($billing_paypal_paymentstatus)) ? $billing_paypal_paymentstatus : "Any" ?>
                                    </option>
                                    <option value=""></option>
                                    <option value="Completed">Completed</option>
                                    <option value="Denied">Denied</option>
                                    <option value="Expired">Expired</option>
                                    <option value="Failed">Failed</option>
                                    <option value="In-Progress">In-Progress</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Processed">Processed</option>
                                    <option value="Refunded">Refunded</option>
                                    <option value="Reversed">Reversed</option>
                                    <option value="Canceled-Reversal">Canceled-Reversal</option>
                                    <option value="Voided">Voided</option>
                                </select>

                                <br><br>

                                <h109><?= t('button','AccountingFieldsinQuery'); ?></h109><br>
<?php
                                foreach ($checkboxes as $value => $caption) {
                                    $checked = in_array($value, $checkboxes_checked) ? ' checked' : '';
                                    printf('<input type="checkbox" name="sqlfields[]" value="%s"%s><h109>%s</h109><br>',
                                           $value, $checked, $caption);
                                }
?>
                                <br><h109>Select:</h109>
                                <a class="table" href="javascript:SetChecked(1,'sqlfields[]','billpaypaltransactions')">All</a>
                                <a class="table" href="javascript:SetChecked(0,'sqlfields[]','billpaypaltransactions')">None</a>

                                <br><br>
                                
                                <h109><?= t('button','OrderBy') ?></h109><br>
                                
                                <div style="text-align: center">
                                    <select name="orderBy" size="1">
                                        <option value="id"> Id </option>
                                        <option value="username"> username </option>
                                        <option value="txnId"> txnId </option>
                                    </select>

                                    <select name="orderType" size="1">
                                        <option value="ASC"> Ascending </option>
                                        <option value="DESC"> Descending </option>
                                    </select>
                                </div>

                                <br><br>
                                
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
