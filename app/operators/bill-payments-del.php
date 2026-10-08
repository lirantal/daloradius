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

    require_once('library/payments_pdo.php');
    $payment_id = ''; $selected_payment_ids = array(); $payment_pdo = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            try {
                $selected_payment_ids = dalo_payment_ids($_POST['payment_id'] ?? array());
                $payment_pdo = dalo_payment_open($configValues);
                $removed_payment_ids = dalo_payment_mutate($payment_pdo, $configValues, 'del', $selected_payment_ids, array(), $operator);
                $successMsg = sprintf('Deleted %d payment(s)', $removed_payment_ids);
                $logAction .= 'Successful payment deletion on page: ';
            } catch (Throwable $error) {
                $failureMsg = 'Failed to delete payments; verify the selected rows before retrying';
                $logAction .= 'Payment deletion failed [' . get_class($error) . '] on page: ';
            } finally { $payment_pdo = null; }
        } else { $failureMsg = 'CSRF token error'; }
    }

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // print HTML prologue
    
    $title = t('Intro','paymentsdel.php');
    $help = t('helpPage','paymentsdel');
    
    print_html_prologue($title, $langCode);

    
    
    if (!empty($payment_id) && !is_array($payment_id)) {
        $title .= " :: " . htmlspecialchars($payment_id, ENT_QUOTES, 'UTF-8');
    }
    

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    
    // load options
    $options = array();
    try {
        $payment_pdo = dalo_payment_open($configValues);
        $table = dalo_payment_table($payment_pdo, $configValues, 'CONFIG_DB_TBL_DALOPAYMENTS');
        foreach (dalo_catalog_read_rows($payment_pdo, "SELECT id FROM $table") as $row) {
            $id = (int)$row[0]; $options[$id] = $id;
        }
    } catch (Throwable $error) {
        dalo_payment_read_failure($error);
        include('include/management/actionMessages.php');
    } finally { $payment_pdo = null; }


    $input_descriptors1 = array();

    $input_descriptors1[] = array(
                                    'name' => 'payment_id[]',
                                    'id' => 'payment_id',
                                    'type' => 'select',
                                    'caption' => t('all','PaymentId'),
                                    'options' => $options,
                                    'multiple' => true,
                                    'size' => 5
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
                                    "title" => t('title','PaymentInfo'),
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
