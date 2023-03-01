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


    include('../common/includes/db_open.php');
    
    // get valid payment types
    $sql = sprintf("SELECT id, value FROM %s", $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    $valid_paymentTypes = array( );
    while ($row = $res->fetchrow()) {
        list($id, $value) = $row;
        
        $valid_paymentTypes["paymentType-$id"] = $value;
    }

    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
        
            // required later
            $currDate = date('Y-m-d H:i:s');
            $currBy = $operator;
        
            $required_fields = array();
        
            $payment_invoice_id = (array_key_exists('payment_invoice_id', $_POST) && intval(trim($_POST['payment_invoice_id'])) > 0)
                                ? intval(trim($_POST['payment_invoice_id'])) : "";
            if (empty($payment_invoice_id)) {
                $required_fields['payment_invoice_id'] = t('all','PaymentInvoiceID');
            }
            
            $payment_type_id = (array_key_exists('payment_type_id', $_POST) && !empty(trim($_POST['payment_type_id'])) &&
                                in_array(trim($_POST['payment_type_id']), array_keys($valid_paymentTypes)))
                             ? intval(str_replace("paymentType-", "", trim($_POST['payment_type_id']))) : "";
            
            $payment_amount = (array_key_exists('payment_amount', $_POST) && is_numeric(trim($_POST['payment_amount'])))
                             ? trim($_POST['payment_amount']) : 0;
            if (empty($payment_amount)) {
                $required_fields['payment_amount'] = t('all','PaymentAmount');
            }
            
            $payment_date = (
                                array_key_exists('payment_date', $_POST) &&
                                !empty(trim($_POST['payment_date'])) &&
                                preg_match(DATE_REGEX, trim($_POST['payment_date']), $m) !== false &&
                                checkdate($m[2], $m[3], $m[1])
                            ) ? trim($_POST['payment_date']) : date('Y-m-d');
            if (empty($payment_date)) {
                $required_fields['payment_date'] = t('all','PaymentDate');
            }
            
            $payment_notes = (array_key_exists('payment_notes', $_POST) && !empty(trim($_POST['payment_notes'])))
                           ? trim($_POST['payment_notes']) : "";
            
            if (count($required_fields) > 0) {
                // required/invalid
                $failureMsg = sprintf("Empty or invalid required field(s) [%s]", implode(", ", array_values($required_fields)));
                $logAction .= "$failureMsg on page: ";
            } else {
                $sql = sprintf("INSERT INTO %s (id, invoice_id, amount, date, type_id, notes,
                                                creationdate, creationby, updatedate, updateby)
                                        VALUES (0, %d, %s, '%s', %d, '%s', '%s', '%s', NULL, NULL)",
                               $configValues['CONFIG_DB_TBL_DALOPAYMENTS'], $payment_invoice_id, $payment_amount,
                               $payment_date, $payment_type_id, $dbSocket->escapeSimple($payment_notes), $currDate, $currBy);
                               
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                
                if (!DB::isError($res)) {
                    $successMsg = sprintf("Inserted new payment for invoice: #<strong>%d</strong><br>", $payment_invoice_id)
                                . sprintf('<a href="bill-invoice-edit.php?invoice_id=%d" title="Edit">edit invoice #%d</a>',
                                          $payment_invoice_id, $payment_invoice_id);
                    $logAction .= "Successfully inserted new payment for invoice [#$payment_invoice_id] on page: ";
                } else {
                    $failureMsg = "Failed to insert new payment for invoice: #<strong>$payment_invoice_id</strong>";
                    $logAction .= "Failed to insert new payment for invoice [#$payment_invoice_id] on page: ";
                }
            }

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    } else {
        $payment_invoice_id = (array_key_exists('payment_invoice_id', $_REQUEST) && intval(trim($_REQUEST['payment_invoice_id'])) > 0)
                            ? intval(trim($_REQUEST['payment_invoice_id'])) : "";
        
        $payment_date = (
                            array_key_exists('payment_date', $_REQUEST) &&
                            !empty(trim($_REQUEST['payment_date'])) &&
                            preg_match(DATE_REGEX, trim($_REQUEST['payment_date']), $m) !== false &&
                            checkdate($m[2], $m[3], $m[1])
                        ) ? trim($_REQUEST['payment_date']) : "";
    }


    include('../common/includes/db_close.php');

    // print HTML prologue   
    $title = t('Intro','paymentsnew.php');
    $help = t('helpPage','paymentsnew');
    
    print_html_prologue($title, $langCode);

    


    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');


    if (!isset($successMsg)) {
    
        // descriptors 0
        $input_descriptors0 = array();
        
        $input_descriptors0[] = array(
                                        "name" => "payment_invoice_id",
                                        "caption" => t('all','PaymentInvoiceID'),
                                        "type" => "number",
                                        "value" => ((isset($payment_invoice_id)) ? $payment_invoice_id : ""),
                                        "min" => 1,
                                        "required" => true,
                                        "tooltipText" => t('Tooltip','paymentInvoiceTooltip')
                                     );
        
        $input_descriptors0[] = array(
                                        "name" => "payment_amount",
                                        "caption" => t('all','PaymentAmount'),
                                        "type" => "number",
                                        "value" => ((isset($payment_amount)) ? $payment_amount : ""),
                                        "min" => 0,
                                        "step" => ".01",
                                        "required" => true,
                                        "tooltipText" => t('Tooltip','amountTooltip')
                                     );
        
        $input_descriptors0[] = array(
                                        "name" => "payment_date",
                                        "caption" => t('all','PaymentDate'),
                                        "type" => "date",
                                        "value" => ((!empty($payment_date)) ? $payment_date : date("Y-m-d")),
                                        "required" => true,
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

        $input_descriptors1[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );
        
        $input_descriptors1[] = array(
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
        
        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_form();
    }
    
    
    print_back_to_previous_page();
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
