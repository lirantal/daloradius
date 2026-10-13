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

    require_once('library/payment_types_pdo.php');
    $paymentname = ''; $paymentname_enc = ''; $edit_paymentname = ''; $paymentnotes = ''; $type_pdo = null;
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) || !dalo_check_csrf_token($_POST['csrf_token'])) {
                $failureMsg = 'CSRF token error';
            } else {
                $paymentname = dalo_payment_type_text(dalo_payment_scalar($_POST,'paymentname'),32,true);
                $paymentnotes = dalo_payment_type_text(dalo_payment_scalar($_POST,'paymentnotes'),128);
                $type_pdo = dalo_payment_open($configValues);
                dalo_payment_type_mutate($type_pdo,$configValues,'new',$paymentname,$paymentnotes,$operator);
                $paymentname_enc = htmlspecialchars($paymentname, ENT_QUOTES, 'UTF-8');
                $successMsg = sprintf('Successfully inserted new payment type (<strong>%s</strong>) [<a href="bill-payment-types-edit.php?paymentname=%s" title="Edit">Edit</a>]',
                    $paymentname_enc, htmlspecialchars(urlencode($paymentname), ENT_QUOTES, 'UTF-8'));
                $logAction .= 'Successful payment type mutation on page: ';
                $logDebugSQL .= 'Payment type mutation (PDO transaction, bound values);\n';
            }
        }
    } catch (Throwable $error) {
        $failureMsg = 'Failed to insert payment type; verify its state before retrying';
        $logAction .= 'Payment type mutation failed [' . get_class($error) . '] on page: ';
    } finally { $type_pdo = null; }
    $paymentname_enc = $paymentname !== '' ? htmlspecialchars($paymentname, ENT_QUOTES, 'UTF-8') : '';
    $edit_paymentname = $paymentname;

    // print HTML prologue
    $title = t('Intro','paymenttypesnew.php');
    $help = t('helpPage','paymenttypesnew');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');

    if (!isset($successMsg)) {
        // descriptors 0
        $input_descriptors0 = array();
        
        $input_descriptors0[] = array(
                                        'name' => 'paymentname',
                                        'caption' => t('all','PayTypeName'),
                                        'type' => 'text',
                                        'value' => $paymentname,
                                        'tooltipText' => t('Tooltip','paymentTypeTooltip'),
                                     );
        
        $input_descriptors0[] = array(
                                        "name" => "paymentnotes",
                                        "caption" => t('all','PayTypeNotes'),
                                        "type" => "textarea",
                                        "content" => $paymentnotes,
                                        'tooltipText' => t('Tooltip','paymentTypeNotesTooltip'),
                                     );
        
        $input_descriptors0[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );
        
        $input_descriptors0[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                      );
                                      
        
        open_form();
        
        // fieldset 0
        $fieldset0_descriptor = array(
                                        "title" => t('title','PayTypeInfo'),
                                     );
                                     
        open_fieldset($fieldset0_descriptor);
        
        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        close_form();
    }

    print_back_to_previous_page();
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
