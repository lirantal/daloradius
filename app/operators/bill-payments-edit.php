<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
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
 * Authors:    Liran Tal <liran@lirantal.com>
 *             Filippo Maria Del Prete <filippo.delprete@gmail.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */
 
    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";
    
    
    require_once('library/payments_pdo.php');
    $payment_pdo = null; $valid_paymentTypes = array(); $payment_id = '';
    $payment_invoice_id = ''; $payment_date = ''; $payment_amount = '';
    $payment_type_id = ''; $payment_notes = ''; $payment_read_failed = false;
    try {
        $payment_pdo = dalo_payment_open($configValues);
        $valid_paymentTypes = dalo_payment_types($payment_pdo, $configValues);
        $input = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
        $raw_id = dalo_payment_scalar($input, 'payment_id');
        if ($raw_id !== '') { $payment_id = dalo_payment_id($raw_id); }
        $table = dalo_payment_table($payment_pdo, $configValues, 'CONFIG_DB_TBL_DALOPAYMENTS');
        if ($payment_id !== '' && !dalo_catalog_read_rows($payment_pdo, "SELECT id FROM $table WHERE id=:id", array(':id'=>$payment_id))) { $payment_id = ''; }
    } catch (Throwable $error) {
        dalo_payment_read_failure($error); $payment_read_failed = true; $payment_id = '';
    } finally { $payment_pdo = null; }
    $edit_payment_id = $payment_id;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$payment_read_failed) {
        if (isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            try {
                if ($payment_id === '') { throw new DomainException('Payment no longer exists'); }
                $values = dalo_payment_fields($_POST, false);
                foreach (array('invoice_id','amount','date','type_id','notes') as $field) {
                    if (isset($values[$field])) { ${'payment_' . $field} = $values[$field]; }
                }
                $payment_pdo = dalo_payment_open($configValues);
                $result = dalo_payment_mutate($payment_pdo, $configValues, 'edit', array($payment_id), $values, $operator);
                $successMsg = "Successfully updated payment (id: #<strong>$payment_id</strong>)";
                $logAction .= 'Successful payment mutation on page: ';
                $logDebugSQL .= 'Payment mutation (PDO transaction, bound values);\n';
            } catch (Throwable $error) {
                $failureMsg = 'Failed to update payment; verify its state before retrying';
                $logAction .= 'Payment mutation failed [' . get_class($error) . '] on page: ';
            } finally { $payment_pdo = null; }
        } else { $failureMsg = 'CSRF token error'; $logAction .= 'CSRF token error on page: '; }
    }
    if ($payment_id === '') {
        if (!isset($failureMsg)) { $failureMsg = 'invalid or empty payment id entered, please specify a valid payment id to edit.'; }
    } else {
        try {
            $payment_pdo = dalo_payment_open($configValues);
            $table = dalo_payment_table($payment_pdo, $configValues, 'CONFIG_DB_TBL_DALOPAYMENTS');
            $types = dalo_payment_table($payment_pdo, $configValues, 'CONFIG_DB_TBL_DALOPAYMENTTYPES');
            $rows = dalo_catalog_read_rows($payment_pdo, "SELECT dp.id, dp.invoice_id, dp.amount, dp.date, dp.type_id, dp.notes,
                dp.creationdate, dp.creationby, dp.updatedate, dp.updateby, dpt.value
                FROM $table AS dp LEFT JOIN $types AS dpt ON dp.type_id=dpt.id WHERE dp.id=:id", array(':id'=>$payment_id));
            if (!$rows) { throw new DomainException('Payment no longer exists'); }
            list($payment_id,$payment_invoice_id,$payment_amount,$payment_date,$payment_type_id,$payment_notes,
                $creationdate,$creationby,$updatedate,$updateby,$value) = $rows[0];
        } catch (Throwable $error) {
            dalo_payment_read_failure($error); $payment_id = '';
        } finally { $payment_pdo = null; }
    }

    // print HTML prologue
    $title = t('Intro','paymentsedit.php');
    $help = t('helpPage','paymentsedit');
    
    print_html_prologue($title, $langCode);

    if (!empty($payment_id)) {
        $title .= sprintf(" (id: #%d)", $payment_id);
    }
    
    
    

    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    if (!empty($payment_id)) {
    
        // descriptors 0
        $input_descriptors0 = array();
        
        $input_descriptors0[] = array(
                                        "name" => "payment_invoice_id",
                                        "caption" => t('all','PaymentInvoiceID'),
                                        "type" => "number",
                                        "value" => ((isset($payment_invoice_id)) ? $payment_invoice_id : ""),
                                        "min" => 1,
                                        "tooltipText" => t('Tooltip','paymentInvoiceTooltip')
                                     );
        
        $input_descriptors0[] = array(
                                        "name" => "payment_amount",
                                        "caption" => t('all','PaymentAmount'),
                                        "type" => "number",
                                        "value" => ((isset($payment_amount)) ? $payment_amount : ""),
                                        "min" => 0,
                                        "step" => ".01",
                                        "tooltipText" => t('Tooltip','amountTooltip')
                                     );
        
        $input_descriptors0[] = array(
                                        "name" => "payment_date",
                                        "caption" => t('all','PaymentDate'),
                                        "type" => "date",
                                        "value" => ((isset($payment_date)) ? $payment_date : date("Y-m-d")),
                                        "min" => date("1970-m-01")
                                     );
        
        $input_descriptors0[] = array(
                                        "name" => "payment_notes",
                                        "caption" => t('all','PaymentNotes'),
                                        "type" => "textarea",
                                        "content" => ((isset($payment_notes)) ? $payment_notes : ""),
                                        "tooltipText" => t('Tooltip','paymentNotesTooltip')
                                     );
        
        $options = $valid_paymentTypes;
        array_unshift($options , '');
        $input_descriptors0[] = array(
                                        "type" =>"select",
                                        "name" => "payment_type_id",
                                        "caption" => t('all','PaymentType'),
                                        "options" => $options,
                                        "selected_value" => ((isset($payment_type_id) && intval($payment_type_id) > 0) ? "paymentType-$payment_type_id" : ""),
                                        "tooltipText" => t('Tooltip','paymentTypeIdTooltip')
                                     );
        
        // descriptors 1
        $input_descriptors1 = array();
        $input_descriptors1[] = array( 'name' => 'creationdate', 'caption' => t('all','CreationDate'), 'type' => 'datetime-local',
                                       'disabled' => true, 'value' => ((isset($creationdate)) ? $creationdate : '') );
        $input_descriptors1[] = array( 'name' => 'creationby', 'caption' => t('all','CreationBy'), 'type' => 'text',
                                       'disabled' => true, 'value' => ((isset($creationby)) ? $creationby : '') );
        $input_descriptors1[] = array( 'name' => 'updatedate', 'caption' => t('all','UpdateDate'), 'type' => 'datetime-local',
                                       'disabled' => true, 'value' => ((isset($updatedate)) ? $updatedate : '') );
        $input_descriptors1[] = array( 'name' => 'updateby', 'caption' => t('all','UpdateBy'), 'type' => 'text',
                                       'disabled' => true, 'value' => ((isset($updateby)) ? $updateby : '') );
        
        // descriptors 2
        $input_descriptors2 = array();

        $input_descriptors2[] = array(
                                        "name" => "payment_id",
                                        "type" => "hidden",
                                        "value" => ((isset($payment_id)) ? $payment_id : ""),
                                     );
        
        $input_descriptors2[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );
        
        $input_descriptors2[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                      );
        
        open_form();
        
        // fieldset 0
        $fieldset0_descriptor = array(
                                        "title" => t('title','PaymentInfo'),
                                     );
                                     
        open_fieldset($fieldset0_descriptor);
        
        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        // fieldset 1
        $fieldset1_descriptor = array(
                                        "title" => "Other Information",
                                     );
        
        open_fieldset($fieldset1_descriptor);
        
        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        
        foreach ($input_descriptors2 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_form();
    }
    
    
    print_back_to_previous_page();
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
    
?>
