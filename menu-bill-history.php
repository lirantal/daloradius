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
if (strpos($_SERVER['PHP_SELF'], '/menu-bill-history.php') !== false) {
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
    <link rel="stylesheet" href="css/form-field-tooltip.css" media="screen">
    <link rel="stylesheet" href="library/js_date/datechooser.css">
    <!--[if lte IE 6.5]>
    <link rel="stylesheet" href="library/js_date/select-free.css"/>
    <![endif]-->
    
    <script src="library/js_date/date-functions.js"></script>
    <script src="library/js_date/datechooser.js"></script>
    <script src="library/javascript/rounded-corners.js"></script>
    <script src="library/javascript/form-field-tooltip.js"></script>
    
</head>

<body>
    <div id="wrapper">
        <div id="innerwrapper">

<?php
    include_once("include/menu/menu-items.php");
	include_once("include/menu/billing-subnav.php");
    
    $checkboxes = array(
                            "id" => t('all','ID'),
                            "username" => t('all','Username'),
                            "planId" => t('all','PlanId'),

                            "billAmount" => t('all','BillAmount'),
                            "billAction" => t('all','BillAction'),
                            "billPerformer" => t('all','BillPerformer'),
                            "billReason" => t('all','BillReason'),

                            "paymentmethod" => t('ContactInfo','PaymentMethod'),
                            "cash" => t('ContactInfo','Cash'),

                            "creditcardname" => t('ContactInfo','CreditCardName'),
                            "creditcardnumber" => t('ContactInfo','CreditCardNumber'),
                            "creditcardverification" => t('ContactInfo','CreditCardVerificationNumber'),
                            "creditcardtype" => t('ContactInfo','CreditCardType'),
                            "creditcardexp" => t('ContactInfo','CreditCardExpiration'),
                            "coupon" => t('all','Coupon'),
                            "discount" => t('all','Discount'),
                            "notes" => t('ContactInfo','Notes'),
                            "creationdate" => t('all','CreationDate'),
                            "creationby" => t('all','CreationBy'),
                            "updatedate" => t('all','UpdateDate'),
                            "updateby" => t('all','UpdateBy')
                        );

    $checkboxes_checked = array(
                                    "username",
                                    "planId",
                                    "billAmount",
                                    "billAction",
                                    "billPerformer",
                                    "paymentmethod"
                               );
                               
    $showChooser_format = "showChooser(this, '%s', 'chooserSpan', '1970', '%s', 'Y-m-d', false);";
    $chooserSpan = '<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px"></div>';
?>

            <div id="sidebar">
                <h2>Billing</h2>
                
                <h3>Track Billing History</h3>
                <ul class="subnav">
                    <li>
                        <form name="billhistory" action="bill-history-query.php" method="GET" class="sidebar">
                            <input class="sidebutton" type="submit" name="submit" value="<?= t('button','ProcessQuery') ?>">
                            
                            <br><br>

                            <h109><?= t('button','BetweenDates') ?></h109><br>
                            <label style="user-select: none" for="startdate"
                                onclick="<?= sprintf($showChooser_format, "startdate", date('Y', time())) ?>">
                                <img style="border: 0; margin-right: 5px" src="library/js_date/calendar.gif">
                                Start Date
                            </label>
                            <input name="startdate" type="text" id="startdate" tooltipText="<?= t('Tooltip','Date') ?>"
                                value="<?= (isset($billing_date_startdate)) ? $billing_date_startdate : date("Y-m-01") ?>">
                            <?= $chooserSpan ?>

                            <label style="user-select: none" for="enddate"
                                onclick="<?= sprintf($showChooser_format, "enddate", date('Y', time())) ?>">
                                <img style="border: 0; margin-right: 5px" src="library/js_date/calendar.gif">
                                Start Date
                            </label>
                            <input name="enddate" type="text" id="enddate" tooltipText="<?= t('Tooltip','Date') ?>"
                                value="<?= (isset($billing_date_enddate)) ? $billing_date_enddate : date("Y-m-t") ?>">
                            <?= $chooserSpan ?>
                            
                            <br><br>

                            <h109><?= t('all','Username') ?></h109><br>
                            <input name="username" type="text"
                                value="<?= (isset($billing_history_username)) ? $billing_history_username : "*" ?>">

                            <h109><?= t('all','BillAction') ?></h109><br>
                            <select name="billaction" size="1">
                                <option value="<?= (isset($billing_history_billaction)) ? $billing_history_billaction : "%" ?>">
                                    <?= (isset($billing_history_billaction)) ? $billing_history_billaction : "Any" ?>
                                </option>
            
                                <option value=""></option>
                                <option value="Refill Session Time">Refill Session Time</option>
                                <option value="Refill Session Traffic">Refill Session Traffic</option>
                            </select>
                            
                            <br><br>
                                
                            <h109><?= t('button','AccountingFieldsinQuery') ?></h109><br>
<?php
                            foreach ($checkboxes as $value => $caption) {
                                $checked = in_array($value, $checkboxes_checked) ? ' checked' : '';
                                printf('<input type="checkbox" name="sqlfields[]" value="%s"%s><h109>%s</h109><br>', $value, $checked, $caption);
                            }
?>

                            <br>Select:
                            <a class="table" href="javascript:SetChecked(1,'sqlfields[]','billhistory')">All</a>
                            <a class="table" href="javascript:SetChecked(0,'sqlfields[]','billhistory')">None</a>

                            <br><br>
            
                            <h109><?= t('button','OrderBy') ?><h109><br>         
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
