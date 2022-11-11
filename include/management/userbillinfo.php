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

$input_descriptors1 = array();

$input_descriptors1[] = array( 'type' => 'text', 'caption' => t('ContactInfo','PlanName'), 'disabled' => true,
                               'value' => ((isset($bi_planname)) ? $bi_planname : ''), 'name' => 'bi_planname' );
$input_descriptors1[] = array( 'type' => 'text', 'caption' => t('ContactInfo','ContactPerson'),
                               'value' => ((isset($bi_contactperson)) ? $bi_contactperson : ''), 'name' => 'bi_contactperson' );
$input_descriptors1[] = array( 'type' => 'text', 'caption' => t('ContactInfo','Company'),
                               'value' => ((isset($bi_company)) ? $bi_company : ''), 'name' => 'bi_company' );
$input_descriptors1[] = array( 'type' => 'text', 'caption' => t('ContactInfo','Email'),
                               'value' => ((isset($bi_email)) ? $bi_email : ''), 'name' => 'bi_email' );
$input_descriptors1[] = array( 'type' => 'text', 'caption' => t('ContactInfo','Phone'),
                               'value' => ((isset($bi_phone)) ? $bi_phone : ''), 'name' => 'bi_phone'  );
$input_descriptors1[] = array( 'type' => 'text', 'caption' => t('ContactInfo','Address'),
                               'value' => ((isset($bi_address)) ? $bi_address : ''), 'name' => 'bi_address' );
$input_descriptors1[] = array( 'type' => 'text', 'caption' => t('ContactInfo','City'),
                               'value' => ((isset($bi_city)) ? $bi_city : ''), 'name' => 'bi_city' );
$input_descriptors1[] = array( 'type' => 'text', 'caption' => t('ContactInfo','State'),
                               'value' => ((isset($bi_state)) ? $bi_state : ''), 'name' => 'bi_state' );
$input_descriptors1[] = array( 'type' => 'text', 'caption' => t('ContactInfo','Country'),
                               'value' => ((isset($bi_country)) ? $bi_country : ''), 'name' => 'bi_country' );
$input_descriptors1[] = array( 'type' => 'text', 'caption' => t('ContactInfo','Zip'),
                               'value' => ((isset($bi_zip)) ? $bi_zip : ''), 'name' => 'bi_zip' );
$input_descriptors1[] = array( 'type' => 'text', 'caption' => t('all','PostalInvoice'),
                               'value' => ((isset($bi_postalinvoice)) ? $bi_postalinvoice : ''), 'name' => 'bi_postalinvoice' );
$input_descriptors1[] = array( 'type' => 'text', 'caption' => t('all','FaxInvoice'),
                               'value' => ((isset($bi_faxinvoice)) ? $bi_faxinvoice : ''), 'name' => 'bi_faxinvoice' );
$input_descriptors1[] = array( 'type' => 'text', 'caption' => t('all','EmailInvoice'),
                               'value' => ((isset($bi_emailinvoice)) ? $bi_emailinvoice : ''), 'name' => 'bi_emailinvoice' );

$input_descriptors2 = array();

$input_descriptors2[] = array( 'caption' => t('ContactInfo','PaymentMethod'), 'type' => 'text',
                               'value' => ((isset($bi_paymentmethod)) ? $bi_paymentmethod : ''), 'name' => 'bi_paymentmethod' );
$input_descriptors2[] = array( 'caption' => t('ContactInfo','Cash'), 'type' => 'text',
                               'value' => ((isset($bi_cash)) ? $bi_cash : ''), 'name' => 'bi_cash' );
$input_descriptors2[] = array( 'caption' => t('ContactInfo','CreditCardName'), 'type' => 'text',
                               'value' => ((isset($bi_creditcardname)) ? $bi_creditcardname : ''), 'name' => 'bi_creditcardname' );
$input_descriptors2[] = array( 'caption' => t('ContactInfo','CreditCardNumber'), 'type' => 'text',
                               'value' => ((isset($bi_creditcardnumber)) ? $bi_creditcardnumber : ''), 'name' => 'bi_creditcardnumber' );
$input_descriptors2[] = array( 'caption' => t('ContactInfo','CreditCardVerificationNumber'), 'type' => 'text',
                               'value' => ((isset($bi_creditcardverification)) ? $bi_creditcardverification : ''), 'name' => 'bi_creditcardverification' );
$input_descriptors2[] = array( 'caption' => t('ContactInfo','CreditCardType'), 'type' => 'text',
                               'type' => 'select', 'name' => 'bi_creditcardtype',
                               'selected_value' => ((isset($bi_creditcardtype)) ? $bi_creditcardtype : ''),
                               'options' => array( 'Other', 'VISA', 'MasterCard', 'Diners' ) );
$input_descriptors2[] = array( 'type' => 'text', 'caption' => t('ContactInfo','CreditCardExpiration'),
                               'value' => ((isset($bi_creditcardexp)) ? $bi_creditcardexp : ''), 'name' => 'bi_creditcardexp' );



$input_descriptors3 = array();

$input_descriptors3[] = array(
                                'type' => 'textarea',
                                'name' => 'notes',
                                'caption' => t('ContactInfo','Notes'),
                                'content' => ((isset($bi_notes)) ? $bi_notes : '')
                             );

$input_descriptors3[] = array( 'caption' => t('ContactInfo','EnableUserUpdate'), 'name' => 'bi_changeuserbillinfo',
                               'type' => 'checkbox', 'value' => '1', 'checked' => ($bi_changeuserbillinfo == 1) );
$input_descriptors3[] = array( 'caption' => t('all','BillStatus'), 'type' => 'text',  'name' => 'bi_billstatus',
                               'value' => ((isset($bi_billstatus)) ? $bi_billstatus : ''), 'disabled' => true );
$input_descriptors3[] = array( 'caption' => t('all','LastBill'), 'type' => 'text',  'name' => 'bi_lastbill',
                               'value' => ((isset($bi_lastbill)) ? $bi_lastbill : ''), 'disabled' => true );
$input_descriptors3[] = array( 'caption' => t('all','NextBill'), 'type' => 'text',  'name' => 'bi_nextbill',
                               'value' => ((isset($bi_nextbill)) ? $bi_nextbill : ''), 'disabled' => true );
$input_descriptors3[] = array( 'caption' => t('all','BillDue'), 'type' => 'text',  'name' => 'bi_billdue',
                               'value' => ((isset($bi_billdue)) ? $bi_billdue : '') );
$input_descriptors3[] = array( 'caption' => t('all','NextInvoiceDue'), 'type' => 'text', 'name' => 'bi_nextinvoicedue',
                               'value' => ((isset($bi_nextinvoicedue)) ? $bi_nextinvoicedue : '') );
                               
$input_descriptors3[] = array( 'caption' => t('all','CreationDate'), 'type' => 'text', 'name' => 'bi_creationdate',
                               'disabled' => true, 'value' =>((isset($bi_creationdate)) ? $bi_creationdate : '') );
$input_descriptors3[] = array( 'caption' => t('all','CreationBy'), 'type' => 'text', 'name' => 'bi_creationby',
                               'disabled' => true, 'value' =>((isset($bi_creationby)) ? $bi_creationby : '') );
$input_descriptors3[] = array( 'caption' => t('all','UpdateDate'), 'type' => 'text', 'name' => 'bi_updatedate',
                               'disabled' => true, 'value' =>((isset($bi_updatedate)) ? $bi_updatedate : '') );
$input_descriptors3[] = array( 'caption' => t('all','UpdateBy'), 'type' => 'text', 'name' => 'bi_updateby',
                               'disabled' => true, 'value' =>((isset($bi_updateby)) ? $bi_updateby : '') );


$input_descriptors4 = array();

$input_descriptors4[] = array( 'type' => 'select', 'caption' => t('all','Lead'),
                               'name' => 'bi_lead', 'selected_value' => ((isset($bi_lead)) ? $bi_lead : ''),
                               'options' => array('Internet', 'Friend Referral', 'News Medium', 'Advertisment') );

$input_descriptors4[] = array( 'caption' => t('all','Coupon'), 'type' => 'text',
                               'value' => ((isset($bi_coupon)) ? $bi_coupon : ''), 'name' => 'bi_coupon' );
$input_descriptors4[] = array( 'caption' => t('all','OrderTaker'), 'type' => 'text',
                               'value' => ((isset($bi_ordertaker)) ? $bi_ordertaker : ''), 'name' => 'bi_ordertaker' );

?>


<fieldset>
    
    <h302>Bill Info</h302>

    <h301>Billing Information</h301>
    
    <ul>
<?php
    foreach ($input_descriptors1 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
?>
    </ul>


    <h301>Payment Details</h301>

    <ul>
<?php
    foreach ($input_descriptors2 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
?>
    </ul>

    
    <h301>Promotion Details</h301>

    <ul>

<?php
    foreach ($input_descriptors4 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
?>
    </ul>
    
    <h301>Other</h301>
    
    <ul>

<?php
    foreach ($input_descriptors3 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
?>

    </ul>
</fieldset>

<?php
    echo $customApplyButton;
?>
