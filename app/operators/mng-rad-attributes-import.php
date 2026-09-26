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
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    $valid_importStrategies = array(
                                        "insert_or_update" => "insert new/update already-known attributes",
                                        "delete_then_insert" => "delete all already-known, then insert new attributes",
                                        "only_insert_new" => "only insert new attributes"
                                   );

    require_once('library/dictionary_import.php');
    include_once('../common/includes/pdo_connection.php');

    $importStrategy = 'insert_or_update';
    $detectVendor = true;
    $vendor = '';
    $dictionary = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $submittedStrategy = isset($_POST['importStrategy']) ? $_POST['importStrategy'] : null;
        $submittedVendor = isset($_POST['vendor']) ? $_POST['vendor'] : '';
        $submittedDictionary = isset($_POST['dictionary']) ? $_POST['dictionary'] : '';
        $importStrategy = (is_string($submittedStrategy) &&
                           isset($valid_importStrategies[$submittedStrategy]))
                        ? $submittedStrategy : 'insert_or_update';
        $detectVendor = isset($_POST['detectVendor']);
        $vendor = is_string($submittedVendor) ? trim($submittedVendor) : '';
        $dictionary = is_string($submittedDictionary) ? $submittedDictionary : '';

        if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) ||
            !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
            $logAction .= 'CSRF token error on page: ';
        } else {
            try {
                list($selectedVendor, $attributes) = dalo_dictionary_import_text(
                    $submittedDictionary, $detectVendor, $submittedVendor);
                $pdo = dalo_pdo_connect($configValues, isset($_SESSION['location_name'])
                                                       ? $_SESSION['location_name'] : 'default');
                $counts = dalo_dictionary_import($pdo, $configValues, $submittedStrategy,
                                                 $selectedVendor, $attributes);
                $successMsg = sprintf(
                    'processed: %d, deleted: %d, inserted: %d, updated: %d attributes for vendor %s',
                    $counts['processed'], $counts['deleted'], $counts['inserted'],
                    $counts['updated'], htmlspecialchars($selectedVendor, ENT_QUOTES, 'UTF-8'));
                $logAction .= 'Successfully imported vendor dictionary on page: ';
                $logDebugSQL .= "DELETE/UPDATE/INSERT configured dictionary with bound values;\n";
            } catch (Throwable $exception) {
                // No partial counts or bound values are logged or displayed on failure.
                $failureMsg = 'Cannot import dictionary: invalid input or database operation';
                $logAction .= 'Failed importing vendor dictionary on page: ';
            }
        }
    }

    // print HTML prologue
    $title = t('Intro','mngradattributesimport.php');
    $help = t('helpPage','mngradattributesimport');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    if (!isset($successMsg)) {
        
        $fieldset0_descriptor = array(
                                        "title" => t('title','VendorAttribute'),
                                     );

        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        "caption" => "Dictionary import strategy",
                                        "type" => "select",
                                        "name" => "importStrategy",
                                        "options" => $valid_importStrategies,
                                        "selected_value" => ((isset($failureMsg)) ? $importStrategy : ""),
                                     );
        
        $input_descriptors0[] = array(
                                        "name" => "vendor",
                                        "caption" => t('all','VendorName'),
                                        "type" => "text",
                                        "tooltipText" => t('Tooltip','vendorNameTooltip'),
                                        "value" => (isset($vendor) ? $vendor : ""),
                                        "disabled" => (isset($detectVendor) ? $detectVendor : true)
                                     );
                                     
        $input_descriptors0[] = array(
                                        "name" => "detectVendor",
                                        "caption" => "Auto-detect vendor from dictionary",
                                        "type" => "checkbox",
                                        "checked" => (isset($detectVendor) ? $detectVendor : true),
                                        "onclick" => "document.getElementById('vendor').disabled=document.getElementById('detectVendor').checked"
                                     );

        $input_descriptors0[] = array(
                                        "caption" => t('all','Dictionary'),
                                        "type" => "textarea",
                                        "name" => "dictionary",
                                        "content" => ((isset($failureMsg)) ? $dictionary : ""),
                                     );
        
        $input_descriptors0[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );

        $input_descriptors0[] = array(
                                        'type' => 'submit',
                                        'name' => 'submit',
                                        'value' => t('buttons','apply')
                                     );
        
        open_form();
        
        open_fieldset($fieldset0_descriptor);
        
        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        close_form();
        
    }

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
