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
        $payment_id = (array_key_exists('payment_id', $_POST) && intval(trim($_POST['payment_id'])) > 0)
                    ? intval(trim($_POST['payment_id'])) : "";
    } else {
        $payment_id = (array_key_exists('payment_id', $_REQUEST) && intval(trim($_REQUEST['payment_id'])) > 0)
                    ? intval(trim($_REQUEST['payment_id'])) : "";
    }

    // check if this payment exists
    $sql = sprintf("SELECT COUNT(id) FROM %s WHERE id=%d", $configValues['CONFIG_DB_TBL_DALOPAYMENTS'], $payment_id);
    $res = $dbSocket->query($sql);
    
    $exists = intval($res->fetchrow()[0]) == 1;

    if (!$exists) {
        // we reset the payment if it does not exist
        $payment_id = "";
    }
    
    //feed the sidebar variables
    $edit_payment_id = $payment_id;
    
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
        
            if (empty($payment_id)) {
                // required
                $failureMsg = "invalid or empty payment id, please specify a valid payment id to edit.";
                $logAction .= "invalid or empty payment id on page: ";
            } else {
                $sql_SET = array();
                
                // required later
                $currDate = date('Y-m-d H:i:s');
                $currBy = $operator;
            
                $sql_SET[] = sprintf("updatedate='%s'", $currDate);
                $sql_SET[] = sprintf("updateby='%s'", $currBy);
            
                $payment_invoice_id = (array_key_exists('payment_invoice_id', $_POST) && intval(trim($_POST['payment_invoice_id'])) > 0)
                                    ? intval(trim($_POST['payment_invoice_id'])) : "";
                if (!empty($payment_invoice_id)) {
                    $sql_SET[] = sprintf("invoice_id=%d", $payment_invoice_id);
                }
                
                $payment_type_id = (array_key_exists('payment_type_id', $_POST) && !empty(trim($_POST['payment_type_id'])) &&
                                    in_array(trim($_POST['payment_type_id']), array_keys($valid_paymentTypes)))
                                 ? intval(str_replace("paymentType-", "", trim($_POST['payment_type_id']))) : "";
                if (!empty($payment_type_id)) {
                    $sql_SET[] = sprintf("type_id=%d", $payment_type_id);
                }
                
                $payment_amount = (array_key_exists('payment_amount', $_POST) && is_numeric(trim($_POST['payment_amount'])))
                                 ? trim($_POST['payment_amount']) : 0;
                if ($payment_amount > 0) {
                    $sql_SET[] = sprintf("amount='%s'", $payment_amount);
                }

                $payment_date = (
                                    array_key_exists('payment_date', $_POST) &&
                                    !empty(trim($_POST['payment_date'])) &&
                                    preg_match(DATE_REGEX, trim($_POST['payment_date']), $m) !== false &&
                                    checkdate($m[2], $m[3], $m[1])
                                ) ? trim($_POST['payment_date']) : "";
                if (!empty($payment_date)) {
                    $sql_SET[] = sprintf("date='%s'", $payment_date);
                }
                                
                $payment_notes = (array_key_exists('payment_notes', $_POST) && !empty(trim($_POST['payment_notes'])))
                               ? trim($_POST['payment_notes']) : "";
                if (!empty($payment_notes)) {
                    $sql_SET[] = sprintf("notes='%s'", $dbSocket->escapeSimple($payment_notes));
                }
                
                $sql = sprintf("UPDATE %s SET ", $configValues['CONFIG_DB_TBL_DALOPAYMENTS'])
                     . implode(", ", $sql_SET)
                     . sprintf(" WHERE id=%d", $payment_id);
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                
                if (!DB::isError($res)) {
                    $successMsg = "Successfully updated payment (id: #<strong>$payment_id</strong>)";
                    $logAction .= "Successfully updated payment [id: #$payment_id] on page: ";
                } else {
                    $failureMsg = "Failed to updated payment (id: #<strong>$payment_id</strong>)";
                    $logAction .= "Failed to updated payment [id: #$payment_id] on page: ";
                }
                
            }
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }
    
        
    if (empty($payment_id)) {
        $failureMsg = "invalid or empty payment id entered, please specify a valid payment id to edit.";
        $logAction .= "$failureMsg on page: ";
    } else {
    
        $sql = sprintf("SELECT dp.id, dp.invoice_id, dp.amount, dp.date, dp.type_id, dp.notes,
                               dp.creationdate, dp.creationby, dp.updatedate, dp.updateby, dpt.value
                          FROM %s AS dp LEFT JOIN %s AS dpt ON dp.type_id = dpt.id
                         WHERE dp.id=%d", $configValues['CONFIG_DB_TBL_DALOPAYMENTS'],
                                          $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'], $payment_id);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
    
        $row = $res->fetchrow();
    
        list(
                $payment_id, $payment_invoice_id, $payment_amount, $payment_date, $payment_type_id,
                $payment_notes, $creationdate, $creationby, $updatedate, $updateby, $value
            ) = $row;
    
    }

    include('../common/includes/db_close.php');

    
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
