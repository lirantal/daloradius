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
 * Description:    provides user billing information input fields
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/management/userbillinfo.php') !== false) {
    header('Location: ../../index.php');
    exit;
}

$_input_descriptors0 = array();

$_input_descriptors0[] = array( 'type' => 'text', 'caption' => t('ContactInfo','PlanName'), 'disabled' => true,
                               'value' => ((isset($bi_planname)) ? $bi_planname : ''), 'name' => 'bi_planname' );
$_input_descriptors0[] = array( 'type' => 'text', 'caption' => t('ContactInfo','ContactPerson'),
                               'value' => ((isset($bi_contactperson)) ? $bi_contactperson : ''), 'name' => 'bi_contactperson' );
$_input_descriptors0[] = array( 'type' => 'text', 'caption' => t('ContactInfo','Company'),
                               'value' => ((isset($bi_company)) ? $bi_company : ''), 'name' => 'bi_company' );
$_input_descriptors0[] = array( 'type' => 'text', 'caption' => t('ContactInfo','Email'),
                               'value' => ((isset($bi_email)) ? $bi_email : ''), 'name' => 'bi_email' );
$_input_descriptors0[] = array( 'type' => 'text', 'caption' => t('ContactInfo','Phone'),
                               'value' => ((isset($bi_phone)) ? $bi_phone : ''), 'name' => 'bi_phone'  );
$_input_descriptors0[] = array( 'type' => 'text', 'caption' => t('ContactInfo','Address'),
                               'value' => ((isset($bi_address)) ? $bi_address : ''), 'name' => 'bi_address' );
$_input_descriptors0[] = array( 'type' => 'text', 'caption' => t('ContactInfo','City'),
                               'value' => ((isset($bi_city)) ? $bi_city : ''), 'name' => 'bi_city' );
$_input_descriptors0[] = array( 'type' => 'text', 'caption' => t('ContactInfo','State'),
                               'value' => ((isset($bi_state)) ? $bi_state : ''), 'name' => 'bi_state' );
$_input_descriptors0[] = array( 'type' => 'text', 'caption' => t('ContactInfo','Country'),
                               'value' => ((isset($bi_country)) ? $bi_country : ''), 'name' => 'bi_country' );
$_input_descriptors0[] = array( 'type' => 'text', 'caption' => t('ContactInfo','Zip'),
                               'value' => ((isset($bi_zip)) ? $bi_zip : ''), 'name' => 'bi_zip' );
$_input_descriptors0[] = array( 'type' => 'text', 'caption' => t('all','PostalInvoice'),
                               'value' => ((isset($bi_postalinvoice)) ? $bi_postalinvoice : ''), 'name' => 'bi_postalinvoice' );
$_input_descriptors0[] = array( 'type' => 'text', 'caption' => t('all','FaxInvoice'),
                               'value' => ((isset($bi_faxinvoice)) ? $bi_faxinvoice : ''), 'name' => 'bi_faxinvoice' );
$_input_descriptors0[] = array( 'type' => 'text', 'caption' => t('all','EmailInvoice'),
                               'value' => ((isset($bi_emailinvoice)) ? $bi_emailinvoice : ''), 'name' => 'bi_emailinvoice' );

$_input_descriptors1 = array();

$_input_descriptors1[] = array( 'caption' => t('ContactInfo','PaymentMethod'), 'type' => 'text',
                               'value' => ((isset($bi_paymentmethod)) ? $bi_paymentmethod : ''), 'name' => 'bi_paymentmethod' );
$_input_descriptors1[] = array( 'caption' => t('ContactInfo','Cash'), 'type' => 'text',
                               'value' => ((isset($bi_cash)) ? $bi_cash : ''), 'name' => 'bi_cash' );
$_input_descriptors1[] = array( 'caption' => t('ContactInfo','CreditCardName'), 'type' => 'text',
                               'value' => ((isset($bi_creditcardname)) ? $bi_creditcardname : ''), 'name' => 'bi_creditcardname' );
$_input_descriptors1[] = array( 'caption' => t('ContactInfo','CreditCardNumber'), 'type' => 'text',
                               'value' => ((isset($bi_creditcardnumber)) ? $bi_creditcardnumber : ''), 'name' => 'bi_creditcardnumber' );
$_input_descriptors1[] = array( 'caption' => t('ContactInfo','CreditCardVerificationNumber'), 'type' => 'text',
                               'value' => ((isset($bi_creditcardverification)) ? $bi_creditcardverification : ''), 'name' => 'bi_creditcardverification' );
$_input_descriptors1[] = array( 'caption' => t('ContactInfo','CreditCardType'), 'type' => 'text',
                               'type' => 'select', 'name' => 'bi_creditcardtype',
                               'selected_value' => ((isset($bi_creditcardtype)) ? $bi_creditcardtype : ''),
                               'options' => array( 'Other', 'VISA', 'MasterCard', 'Diners' ) );
$_input_descriptors1[] = array( 'type' => 'text', 'caption' => t('ContactInfo','CreditCardExpiration'),
                               'value' => ((isset($bi_creditcardexp)) ? $bi_creditcardexp : ''), 'name' => 'bi_creditcardexp' );



$_input_descriptors3 = array();

$_input_descriptors3[] = array(
                                'type' => 'textarea',
                                'name' => 'bi_notes',
                                'caption' => t('ContactInfo','Notes'),
                                'content' => ((isset($bi_notes)) ? $bi_notes : '')
                             );

$_input_descriptors3[] = array(
                                    'type' => 'select',
                                    'name' => 'bi_changeuserbillinfo',
                                    'caption' => t('ContactInfo','EnableUserUpdate'),
                                    'options' => array( "0" => "no", "1" => "yes" ),
                                    'integer_value' => true,
                                    'selected_value' => (isset($bi_changeuserbillinfo) && intval($bi_changeuserbillinfo) == 1) ? '1' : '0',
                              );

$_input_descriptors3[] = array( 'caption' => t('all','BillStatus'), 'type' => 'text',  'name' => 'bi_billstatus',
                               'value' => ((isset($bi_billstatus)) ? $bi_billstatus : ''), 'disabled' => true );
$_input_descriptors3[] = array( 'caption' => t('all','LastBill'), 'type' => 'text',  'name' => 'bi_lastbill',
                               'value' => ((isset($bi_lastbill)) ? $bi_lastbill : ''), 'disabled' => true );
$_input_descriptors3[] = array( 'caption' => t('all','NextBill'), 'type' => 'text',  'name' => 'bi_nextbill',
                               'value' => ((isset($bi_nextbill)) ? $bi_nextbill : ''), 'disabled' => true );
$_input_descriptors3[] = array( 'caption' => t('all','BillDue'), 'type' => 'text',  'name' => 'bi_billdue',
                               'value' => ((isset($bi_billdue)) ? $bi_billdue : '') );
$_input_descriptors3[] = array( 'caption' => t('all','NextInvoiceDue'), 'type' => 'text', 'name' => 'bi_nextinvoicedue',
                               'value' => ((isset($bi_nextinvoicedue)) ? $bi_nextinvoicedue : '') );

$_input_descriptors3[] = array( 'caption' => t('all','CreationDate'), 'type' => 'datetime-local', 'name' => 'bi_creationdate',
                               'disabled' => true, 'value' =>((isset($bi_creationdate)) ? $bi_creationdate : '') );
$_input_descriptors3[] = array( 'caption' => t('all','CreationBy'), 'type' => 'text', 'name' => 'bi_creationby',
                               'disabled' => true, 'value' =>((isset($bi_creationby)) ? $bi_creationby : '') );
$_input_descriptors3[] = array( 'caption' => t('all','UpdateDate'), 'type' => 'datetime-local', 'name' => 'bi_updatedate',
                               'disabled' => true, 'value' =>((isset($bi_updatedate)) ? $bi_updatedate : '') );
$_input_descriptors3[] = array( 'caption' => t('all','UpdateBy'), 'type' => 'text', 'name' => 'bi_updateby',
                               'disabled' => true, 'value' =>((isset($bi_updateby)) ? $bi_updateby : '') );


$_input_descriptors2 = array();

$_input_descriptors2[] = array( 'type' => 'select', 'caption' => t('all','Lead'),
                               'name' => 'bi_lead', 'selected_value' => ((isset($bi_lead)) ? $bi_lead : ''),
                               'options' => array('Internet', 'Friend Referral', 'News Medium', 'Advertisment') );

$_input_descriptors2[] = array( 'caption' => t('all','Coupon'), 'type' => 'text',
                               'value' => ((isset($bi_coupon)) ? $bi_coupon : ''), 'name' => 'bi_coupon' );
$_input_descriptors2[] = array( 'caption' => t('all','OrderTaker'), 'type' => 'text',
                               'value' => ((isset($bi_ordertaker)) ? $bi_ordertaker : ''), 'name' => 'bi_ordertaker' );

// fieldset
$_fieldset0_descriptor = array(
                                "title" => "Billing Information",
                              );

open_fieldset($_fieldset0_descriptor);

foreach ($_input_descriptors0 as $input_descriptor) {
    print_form_component($input_descriptor);
}

close_fieldset();

unset($_input_descriptors0);

// fieldset
$_fieldset1_descriptor = array(
                                "title" => "Payment Details",
                              );

open_fieldset($_fieldset1_descriptor);

foreach ($_input_descriptors1 as $input_descriptor) {
    print_form_component($input_descriptor);
}

close_fieldset();

unset($_input_descriptors1);

// fieldset
$_fieldset2_descriptor = array(
                                "title" => "Promotion Details",
                              );

open_fieldset($_fieldset2_descriptor);

foreach ($_input_descriptors2 as $input_descriptor) {
    print_form_component($input_descriptor);
}

close_fieldset();

unset($_input_descriptors2);

// fieldset
$_fieldset3_descriptor = array(
                                "title" => "Other",
                              );

open_fieldset($_fieldset3_descriptor);

foreach ($_input_descriptors3 as $input_descriptor) {
    print_form_component($input_descriptor);
}

close_fieldset();

unset($_input_descriptors3);
