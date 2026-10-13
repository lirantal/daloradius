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

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";


    require_once 'library/billing_rates_pdo.php';
    $source = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
    $ratename = isset($source['ratename']) && is_string($source['ratename']) ? trim($source['ratename']) : '';
    $ratename_enc = htmlspecialchars($ratename, ENT_QUOTES, 'UTF-8');
    $edit_ratename = $ratename;
    $pdo = null; $exists = false;
    try {
        $pdo = dalo_catalog_read_open($configValues);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) || !dalo_check_csrf_token($_POST['csrf_token'])) {
                $failureMsg = 'CSRF token error';
            } else {
                try {
                    dalo_rate_mutate($pdo, $configValues, 'edit', $_POST['ratename'] ?? '', $_POST, $operator);
                    $successMsg = "Successfully updated rate (<strong>$ratename_enc</strong>)";
                    $logAction .= 'Successfully updated rate on page: ';
                } catch (Throwable $error) {
                    $failureMsg = 'Failed to update rate; check the fields and current state before retrying';
                    $logAction .= 'Rate edit failed [' . get_class($error) . '] on page: ';
                }
            }
        }
        if ($ratename !== '') {
            $row = dalo_rate_read($pdo, $configValues, $ratename);
            list($id, $stored_name, $ratetype, $ratecost, $creationdate, $creationby, $updatedate, $updateby) = $row;
            list($ratetypenum, $ratetypetime) = array_pad(explode('/', $ratetype, 2), 2, '');
            $exists = true;
        } elseif (!isset($failureMsg)) { $failureMsg = 'invalid or empty rate name entered, please specify a valid rate name to edit.'; }
    } catch (DomainException $error) {
        if (!isset($failureMsg)) { $failureMsg = 'invalid or empty rate name entered, please specify a valid rate name to edit.'; }
    } catch (Throwable $error) { dalo_rate_failure($error); }
    finally { $pdo = null; }

    // print HTML prologue
    $title = t('Intro','billratesedit.php');
    $help = t('helpPage','billratesedit');
    
    print_html_prologue($title, $langCode);

    if (!empty($ratename_enc)) {
        $title .= " :: $ratename_enc";
    } 

    

    
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    if ($exists) {
        // descriptors 0
        $input_descriptors0 = array();
        
        $input_descriptors0[] = array(
                                        'name' => 'ratename',
                                        'caption' => t('all','RateName'),
                                        'type' => 'text',
                                        'disabled' => true,
                                        'value' => $ratename,
                                     );
                                     
        $input_descriptors0[] = array(
                                        "name" => "ratetypenum",
                                        "caption" => t('all','RateType') . " (number)",
                                        "type" => "number",
                                        "value" => $ratetypenum,
                                        "min" => 1,
                                     );
        
        $options = $valid_timeUnits;
        array_unshift($options , '');
        $input_descriptors0[] = array(
                                        "type" =>"select",
                                        "name" => "ratetypetime",
                                        "caption" => t('all','RateType') . " (time unit)",
                                        "options" => $options,
                                        "selected_value" => $ratetypetime,
                                        "tooltipText" => t('Tooltip','rateTypeTooltip')
                                     );
    
        $input_descriptors0[] = array(
                                        "name" => "ratecost",
                                        "caption" => t('all','RateCost'),
                                        "type" => "number",
                                        "value" => $ratecost,
                                        "min" => 1,
                                        "tooltipText" => t('Tooltip','rateCostTooltip')
                                     );
    
        // descriptors 1
        $input_descriptors1 = array();
    
        $input_descriptors1[] = array( 'name' => 'creationdate', 'caption' => t('all','CreationDate'), 'type' => 'text',
                                       'disabled' => true, 'value' => ((isset($creationdate)) ? $creationdate : '') );
        $input_descriptors1[] = array( 'name' => 'creationby', 'caption' => t('all','CreationBy'), 'type' => 'text',
                                       'disabled' => true, 'value' => ((isset($creationby)) ? $creationby : '') );
        $input_descriptors1[] = array( 'name' => 'updatedate', 'caption' => t('all','UpdateDate'), 'type' => 'text',
                                       'disabled' => true, 'value' => ((isset($updatedate)) ? $updatedate : '') );
        $input_descriptors1[] = array( 'name' => 'updateby', 'caption' => t('all','UpdateBy'), 'type' => 'text',
                                       'disabled' => true, 'value' => ((isset($updateby)) ? $updateby : '') );
    
        // descriptors 2
        $input_descriptors2 = array();
        
        $input_descriptors2[] = array(
                                        "name" => "ratename",
                                        "type" => "hidden",
                                        "value" => $ratename,
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
                                        "title" => t('title','RateInfo'),
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
