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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            
            $paymentname = (array_key_exists('paymentname', $_POST) && !empty(trim($_POST['paymentname'])))
                     ? trim($_POST['paymentname']) : "";
            $paymentname_enc = (!empty($paymentname)) ? htmlspecialchars($paymentname, ENT_QUOTES, 'UTF-8') : "";
            
            $paymentnotes = (array_key_exists('paymentnotes', $_POST) && !empty(trim($_POST['paymentnotes'])))
                          ? trim($_POST['paymentnotes']) : "";
                          
            
            if (empty($paymentname)) {
                // required
            } else {
                // check if this payment name exists
                $sql = sprintf("SELECT COUNT(id) FROM %s WHERE value='%s'", $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'],
                                                                          $dbSocket->escapeSimple($paymentname));
                $res = $dbSocket->query($sql);
                
                $exists = intval($res->fetchrow()[0]) == 1;

                if ($exists) {
                    // invalid
                } else {
                    // required later
                    $currDate = date('Y-m-d H:i:s');
                    $currBy = $operator;
                    
                    // insert apyment type info
                    $sql = sprintf("INSERT INTO %s (id, value, notes, creationdate, creationby, updatedate, updateby)
                                            VALUES (0, '%s', '%s', '%s', '%s', NULL, NULL)",
                                   $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'], $dbSocket->escapeSimple($paymentname),
                                   $dbSocket->escapeSimple($paymentnotes), $currDate, $currBy);
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                    
                    if (!DB::isError($res)) {
                        $successMsg = sprintf('Successfully inserted new payment type (<strong>%s</strong>) '
                                            . '[<a href="bill-payment-types-edit.php?paymentname=%s" title="Edit">Edit</a>]',
                                              $paymentname_enc, urlencode($paymentname_enc));
                        $logAction .= "Successfully inserted new payment type [$paymentname] on page: ";
                    } else {
                        $failureMsg = "Failed to insert new payment type (<strong>$paymentname_enc</strong>)";
                        $logAction .= "Failed to insert new payment type [$paymentname] on page: ";
                    }
                }
            }
            
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    include('../common/includes/db_close.php');

    
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
