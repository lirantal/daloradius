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

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
    
    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            $invoice_id = array();
        
            if (array_key_exists('invoice_id', $_POST) && !empty($_POST['invoice_id'])) {
                $tmparr = (!is_array($_POST['invoice_id'])) ? array( $_POST['invoice_id'] ) : $_POST['invoice_id'];
                
                foreach ($tmparr as $tmp_id) {
                    $tmp_id = intval(trim($tmp_id));
                    if (!in_array($tmp_id, $invoice_id)) {
                        $invoice_id[] = intval($tmp_id);
                    }
                }
            }
            
            if (count($invoice_id) > 0) {
                include('../common/includes/db_open.php');
                
                // remove invoice id(s)
                $sql = sprintf("DELETE FROM %s WHERE id IN ('%s')",
                               $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'], implode(", ", $invoice_id));
                $removed_invoice_ids = intval($dbSocket->query($sql));
                $logDebugSQL .= "$sql;\n";
                
                // remove invoice items associated with this invoice id(s)
                $sql = sprintf("DELETE FROM %s WHERE invoice_id IN ('%s')",
                               $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'], implode(", ", $invoice_id));
                $removed_invoice_items = intval($dbSocket->query($sql));
                $logDebugSQL .= "$sql;\n";
                
                $successMsg = sprintf("Deleted %d invoice id(s) and %d item(s)", $removed_invoice_ids, $removed_invoice_items);
                $logAction .= sprintf("Successfully %s on page: ", $successMsg);
                
                include('../common/includes/db_close.php');
            } else {
                $failureMsg = "Empty or invalid invoice id(s)";
                $logAction .= sprintf("Failed deleting invoice(s) [%s] on page: ", $failureMsg);
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

    include_once('include/management/actionMessages.php');
    
    // load options
    include('../common/includes/db_open.php');
    $sql = sprintf("SELECT id FROM %s", $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    $options = array();
    while ($row = $res->fetchrow()) {
        $id = intval($row[0]);
        $options[$id] = $id;
    }
    include('../common/includes/db_close.php');
    
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
