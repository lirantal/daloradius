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
    
    include_once('../common/includes/config_read.php');
    include('library/check_operator_perm.php');
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    require_once __DIR__ . '/library/dictionary_pages_pdo.php';
    $valid_csrf = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token']);
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        if (!$valid_csrf) { $failureMsg = 'CSRF token error'; }
        else {
            try {
                $limit=(int)ini_get('max_input_vars');
                if ($limit>0 && count($_POST, COUNT_RECURSIVE)>=$limit) { throw new InvalidArgumentException('Truncated selection'); }
                $selection=$_POST['vendor__attribute'] ?? null;
                if (is_string($selection)) { $selection=array($selection); }
                $pairs = dalo_dictionary_selection($selection);
                $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                $deleted = dalo_dictionary_delete($pdo, $configValues, $pairs);
                $successMsg = sprintf('Deleted %s dictionary row(s) in %s vendor/attribute(s)', $deleted, count($pairs));
                $logAction .= 'Deleted dictionary selection on page: ';
                $logDebugSQL = 'DELETE FROM configured dictionary WHERE Vendor=:vendor AND Attribute=:attribute;';
            } catch (Throwable $e) { $failureMsg = 'Could not delete dictionary selection'; }
        }
    }

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // print HTML prologue
    $title = t('Intro','mngradattributesdel.php');
    $help = t('helpPage','mngradattributesdel');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);


    // Read options independently from the completed page-owned mutation.
    $options = array();
    try {
        $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        $dictionaryTable = dalo_dictionary_table($configValues);
        $rows = $pdo->query("SELECT Vendor, Attribute FROM $dictionaryTable ORDER BY Vendor, Attribute")->fetchAll(PDO::FETCH_NUM);
        foreach ($rows as $row) {
            list($vendor, $attribute) = $row;
            if ($vendor === null || $attribute === null || $vendor === '' || $attribute === '') { continue; }
            $options[dalo_dictionary_selection_token($vendor, $attribute)] = $vendor . ' - ' . $attribute;
        }
    } catch (Throwable $e) { $failureMsg = 'Could not load dictionary selection'; }
    include_once('include/management/actionMessages.php');

    $input_descriptors1 = array();

    $input_descriptors1[] = array(
                                    'name' => 'vendor__attribute[]',
                                    'id' => 'vendor__attribute',
                                    'type' => 'select',
                                    'caption' => t('all','VendorName') . " - " . t('all','Attribute'),
                                    'options' => $options,
                                    'multiple' => true,
                                    'size' => 25,
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
                                    "title" => t('title','VendorAttribute'),
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
