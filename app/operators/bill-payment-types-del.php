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
    
    // init logging variables
    $logAction = "";
    $logDebugSQL = "";
    $log = "visited page: ";


    require_once('library/payment_types_pdo.php');
    $paymentname = ''; $selected_names = array(); $options = array(); $type_pdo = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            try {
                $selected_names = dalo_payment_type_names($_POST['paymentname'] ?? array());
                $type_pdo = dalo_payment_open($configValues);
                $count = dalo_payment_type_mutate($type_pdo,$configValues,'del',$selected_names,'',$operator);
                $successMsg = sprintf('Deleted %d payment type(s)',$count);
                $logAction .= 'Successful payment type deletion on page: ';
            } catch (Throwable $error) {
                $failureMsg = 'Failed to delete payment types: invalid, missing, ambiguous or referenced selection; verify its state before retrying';
                $logAction .= 'Payment type deletion failed [' . get_class($error) . '] on page: ';
            } finally { $type_pdo = null; }
        } else { $failureMsg = 'CSRF token error'; }
    }
    try {
        $type_pdo = dalo_payment_open($configValues);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $paymentname = dalo_payment_type_text(dalo_payment_scalar($_GET,'paymentname'),32);
            if ($paymentname !== '') {
                try { dalo_payment_type_read($type_pdo,$configValues,$paymentname); }
                catch (DomainException $error) { $paymentname = ''; }
            }
        }
        $table = dalo_payment_table($type_pdo,$configValues,'CONFIG_DB_TBL_DALOPAYMENTTYPES');
        foreach (dalo_catalog_read_rows($type_pdo,"SELECT DISTINCT(value) FROM $table") as $row) { $options[] = (string)$row[0]; }
    } catch (Throwable $error) { dalo_payment_type_read_failure($error); $paymentname = ''; }
    finally { $type_pdo = null; }

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // print HTML prologue
    $title = t('Intro','paymenttypesdel.php');
    $help = t('helpPage','paymenttypesdel');
    
    print_html_prologue($title, $langCode);

    
    
    if (!empty($paymentname) && !is_array($paymentname)) {
        $title .= " :: " . htmlspecialchars($paymentname, ENT_QUOTES, 'UTF-8');
    }
    

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');


    $input_descriptors1 = array();

    $input_descriptors1[] = array(
                                'name' => 'paymentname[]',
                                'id' => 'paymentname',
                                'type' => 'select',
                                'caption' => t('all','PayTypeName'),
                                'options' => $options,
                                'multiple' => true,
                                'size' => 5,
                                'selected_value' => ((!isset($successMsg) && $paymentname !== '') ? array($paymentname) : array())
                             );
                             
    $input_descriptors1[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );

    $input_descriptors1[] = array(
                                    'type' => 'submit',
                                    'name' => 'submit',
                                    'value' => t('buttons','apply')
                                 );

    $fieldset1_descriptor = array(
                                    "title" => t('title','PayTypeInfo'),
                                    "disabled" => (count($options) == 0)
                                 );

    open_form();
    
    open_fieldset($fieldset1_descriptor);

    foreach ($input_descriptors1 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
    
    close_fieldset();
    
    close_form();

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
