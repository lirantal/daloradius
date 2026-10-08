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
    
    require_once 'library/billing_rates_pdo.php';
    $ratename = isset($_GET['ratename']) && is_string($_GET['ratename']) ? trim($_GET['ratename']) : '';
    $valid_ratenames = array(); $selected_ratenames = $ratename !== '' ? array($ratename) : array();
    $pdo = null;
    try {
        $pdo = dalo_catalog_read_open($configValues);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) || !dalo_check_csrf_token($_POST['csrf_token'])) {
                $failureMsg = 'CSRF token error';
            } else {
                try {
                    $count = dalo_rate_mutate($pdo, $configValues, 'del', $_POST['ratename'] ?? array(), array(), $operator);
                    $successMsg = sprintf('Deleted %d rate(s)', $count);
                    $logAction .= 'Deleted rates on page: ';
                } catch (Throwable $error) {
                    $failureMsg = 'Failed deleting rate(s); check the selection and current state before retrying';
                    $logAction .= 'Rate delete failed [' . get_class($error) . '] on page: ';
                }
            }
        }
        $table = dalo_read_table($pdo, $configValues, 'CONFIG_DB_TBL_DALOBILLINGRATES');
        $valid_ratenames = array_map(function($row) { return (string)$row[0]; },
            dalo_catalog_read_rows($pdo, "SELECT DISTINCT(rateName) FROM $table ORDER BY rateName ASC"));
        if (!in_array($ratename, $valid_ratenames, true)) { $ratename = ''; $selected_ratenames = array(); }
    } catch (Throwable $error) { dalo_rate_failure($error); }
    finally { $pdo = null; }

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // print HTML prologue
    $title = t('Intro','billratesdel.php');
    $help = t('helpPage','billratesdel');
    
    print_html_prologue($title, $langCode);

    
    
    if (!empty($ratename) && !is_array($ratename)) {
        $title .= " :: " . htmlspecialchars($ratename, ENT_QUOTES, 'UTF-8');
    }
    

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    
    if (!isset($successMsg)) {
    
        $input_descriptors1 = array();

        $input_descriptors1[] = array(
                                    'name' => 'ratename[]',
                                    'id' => 'ratename',
                                    'type' => 'select',
                                    'caption' => t('all','RateName'),
                                    'options' => $valid_ratenames,
                                    'multiple' => true,
                                    'size' => 5,
                                    'selected_value' => $selected_ratenames
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
                                        "title" => t('title','RateInfo'),
                                        "disabled" => (count($valid_ratenames) == 0)
                                     );

        open_form();
        
        open_fieldset($fieldset1_descriptor);

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        close_form();
        
    }

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
