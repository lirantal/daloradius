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
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
    
    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');
    require_once('../common/includes/pdo_connection.php');
    require_once('library/invoice_delete.php');
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            $invoice_id = null;
            try {
                // Validate the full selection before opening the write transaction.
                $invoice_id = dalo_invoice_ids_from_post($_POST['invoice_id'] ?? array());
            } catch (InvalidArgumentException $error) {
                $failureMsg = "Empty or invalid invoice id(s)";
                $logAction .= sprintf("Failed deleting invoice(s) [%s] on page: ", $failureMsg);
            }
            if (isset($invoice_id)) {
                try {
                    $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                    list($removed_invoice_ids, $removed_invoice_items, $removed_payments) =
                        dalo_delete_invoices($pdo, $configValues, $invoice_id);
                    $successMsg = sprintf("Deleted %d invoice id(s), %d item(s) and %d payment(s)",
                                          $removed_invoice_ids, $removed_invoice_items, $removed_payments);
                    $logAction .= sprintf("Successfully %s on page: ", $successMsg);
                } catch (Throwable $error) {
                    // Do not expose driver errors, credentials or SQL to the page/log.
                    $failureMsg = "Failed to delete invoice(s) and dependent records";
                    $logAction .= sprintf("Failed deleting invoice(s) [%s] on page: ", $failureMsg);
                }
            }
            
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    
        
    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // print HTML prologue
    $title = t('Intro','billinvoicedel.php');
    $help = t('helpPage','billinvoicedel');
    
    print_html_prologue($title, $langCode);

    
    
    if (!empty($invoice_id) && !is_array($invoice_id)) {
        $title .= " :: #" . htmlspecialchars($invoice_id, ENT_QUOTES, 'UTF-8');
    }
    

    print_title_and_help($title, $help);

    
    require_once __DIR__.'/library/invoice_reads_pdo.php';
    $options = array();
    try {
    $invoiceReadPDO=dalo_invoice_read_open($configValues);
    // load options

    $readBindings = array();
    $sql = sprintf("SELECT id FROM %s", dalo_invoice_read_table($configValues, 'CONFIG_DB_TBL_DALOBILLINGINVOICE'));
    $res = dalo_invoice_read_rows($invoiceReadPDO, $sql, $readBindings);
    $logDebugSQL .= "$sql;\n";
    
    $options = array();
    foreach ($res as $row) {
        $id = intval($row[0]);
        $options[$id] = $id;
    }
    

    } catch (Throwable $error) {
        $options=array(); dalo_invoice_read_failure($error);
    }
    unset($invoiceReadPDO);
    include_once 'include/management/actionMessages.php';
    $input_descriptors1 = array();

    $input_descriptors1[] = array(
                                    'name' => 'invoice_id[]',
                                    'id' => 'invoice_id',
                                    'type' => 'select',
                                    'caption' => t('all','InvoiceID'),
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
                                    "title" => t('title','InvoiceRemoval'),
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
