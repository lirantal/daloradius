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


    include('../common/includes/db_open.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            if (array_key_exists('paymentname', $_POST) && !empty($_POST['paymentname'])) {
                $paymentname = array();
            
                $tmparr = (!is_array($_POST['paymentname'])) ? array( $_POST['paymentname'] ) : $_POST['paymentname'];
                
                foreach ($tmparr as $tmp_name) {
                    $tmp_name = $dbSocket->escapeSimple(trim($tmp_name));
                    if (!in_array($tmp_name, $paymentname)) {
                        $paymentname[] = intval($tmp_name);
                    }
                }
                
                if (count($paymentname) > 0) {
                    // delete all payment types 
                    $sql = sprintf("DELETE FROM %s WHERE value IN ('%s')",
                                   $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'], implode("', '", $paymentname));
                    $count = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                    
                    $successMsg = sprintf("Deleted %d payment type(s)", intval($count));
                    $logAction .= "$successMsg on page: ";
                    
                } else {
                    // invalid
                    $failureMsg = "Empty or invalid payment name(s)";
                    $logAction .= sprintf("Failed deleting payment type(s) [%s] on page: ", $failureMsg);
                }
                
                
                
            } else {
                $failureMsg = "Empty or invalid payment name(s)";
                $logAction .= sprintf("Failed deleting payment type(s) [%s] on page: ", $failureMsg);
            }
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    } else {
        $paymentname = (array_key_exists('paymentname', $_REQUEST) && !empty(str_replace("%", "", trim($_REQUEST['paymentname']))))
                     ? str_replace("%", "", trim($_REQUEST['paymentname'])) : "";
        
        if (!empty($paymentname)) {
            $sql = sprintf("SELECT COUNT(DISTINCT(value)) FROM %s WHERE value = '%s'",
                           $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'], $dbSocket->escapeSimple($paymentname));
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";
            $exists = $res->fetchrow()[0] > 0;
            
            if (!$exists) {
                $paymentname = "";
            }
        }
    }

    // (re)load options
    $sql = sprintf("SELECT DISTINCT(value) FROM %s", $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    $options = array();
    while ($row = $res->fetchrow()) {
        $options[] = $row[0];
    }
    
    include('../common/includes/db_close.php');

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
                                'selected_value' => ((!isset($successMsg) && !empty($paymentname)) ? $paymentname : "")
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
