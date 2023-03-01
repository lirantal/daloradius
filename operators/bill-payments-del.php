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
    
    // init logging variables
    $logAction = "";
    $logDebugSQL = "";
    $log = "visited page: ";

    $payment_id = array();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            if (array_key_exists('payment_id', $_POST) && !empty($_POST['payment_id'])) {
                $tmparr = (!is_array($_POST['payment_id'])) ? array( $_POST['payment_id'] ) : $_POST['payment_id'];
                
                foreach ($tmparr as $tmp_id) {
                    $tmp_id = intval(trim($tmp_id));
                    if (!in_array($tmp_id, $payment_id)) {
                        $payment_id[] = intval($tmp_id);
                    }
                }
            }
            
            if (count($payment_id) > 0) {
                include('../common/includes/db_open.php');
                
                // remove payment(s)
                $sql = sprintf("DELETE FROM %s WHERE id IN ('%s')",
                               $configValues['CONFIG_DB_TBL_DALOPAYMENTS'], implode(", ", $payment_id));
                $removed_payment_ids = intval($dbSocket->query($sql));
                $logDebugSQL .= "$sql;\n";
                
                $successMsg = sprintf("Deleted %d payment(s)", $removed_payment_ids);
                $logAction .= sprintf("Successfully %s on page: ", $successMsg);
                
                include('../common/includes/db_close.php');
            } else {
                $failureMsg = "Empty or invalid payment id(s)";
                $logAction .= sprintf("Failed deleting payment(s) [%s] on page: ", $failureMsg);
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
    
    $title = t('Intro','paymentsdel.php');
    $help = t('helpPage','paymentsdel');
    
    print_html_prologue($title, $langCode);

    
    
    if (!empty($payment_id) && !is_array($payment_id)) {
        $title .= " :: " . htmlspecialchars($payment_id, ENT_QUOTES, 'UTF-8');
    }
    

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    
    // load options
    include('../common/includes/db_open.php');
    $sql = sprintf("SELECT id FROM %s", $configValues['CONFIG_DB_TBL_DALOPAYMENTS']);
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
