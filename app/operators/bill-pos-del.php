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
    require_once('../common/includes/pdo_connection.php');
    require_once('library/pos_delete.php');
    
    // init logging variables
    $logAction = "";
    $logDebugSQL = "";
    $log = "visited page: ";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            $usernameInput = $_POST['username'] ?? '';
            $username = is_string($usernameInput) ? trim($usernameInput) : '';
            $accountingInput = $_POST['delradacct'] ?? '';

            if ($username !== '' && is_string($accountingInput) &&
                in_array(strtolower(trim($accountingInput)), array('', 'yes', 'no'), true)) {
                try {
                    $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                    $counts = dalo_delete_pos_user($pdo, $configValues, $username,
                                                   strtolower(trim($accountingInput)) === 'yes');
                    $username_enc = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
                    $successMsg = "Deleted user: <strong>$username_enc</strong>";
                    $logAction .= "Successfully deleted user [$username] on page: ";
                    $logDebugSQL .= "POS user and dependent billing/RADIUS deletion (PDO transaction);\n";
                } catch (Throwable $error) {
                    $failureMsg = 'Failed to delete user';
                    $logAction .= 'Failed POS user deletion on page: ';
                    error_log('POS deletion failed (' . get_class($error) . ')');
                }
            } else {
                $failureMsg = "Empty or invalid username";
                $logAction .= sprintf("Failed deleting user [%s] on page: ", $failureMsg);
            }
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    } else {
        $usernameInput = $_GET['username'] ?? '';
        $username = is_string($usernameInput) ? trim($usernameInput) : '';
    }
    
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    
    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // print HTML prologue
    $title = t('Intro','billposdel.php');
    $help = t('helpPage','billposdel');
    
    print_html_prologue($title, $langCode);

    
    
    if (!empty($username_enc) && !is_array($username_enc)) {
        $title .= " :: $username_enc";
    }
    

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    
    // load options
    require_once('library/catalog_reads_pdo.php');
    $options = array('');
    $catalog_pdo = null;
    try {
        $catalog_pdo = dalo_catalog_read_open($configValues);
        $options = array_merge($options, dalo_catalog_read_options($catalog_pdo, $configValues, 'CONFIG_DB_TBL_RADCHECK', 'username'));
    } catch (Throwable $error) {
        dalo_catalog_read_failure($error);
        include('include/management/actionMessages.php');
    } finally { $catalog_pdo = null; }

    $input_descriptors1 = array();

    $input_descriptors1[] = array(
                                'name' => 'username',
                                'type' => 'select',
                                'caption' => t('all','Username'),
                                'options' => $options,
                                'selected_value' => (!isset($successMsg) ? $username : "")
                             );

    $input_descriptors1[] = array(
                                'name' => 'delradacct',
                                'type' => 'select',
                                'caption' => t('all','RemoveRadacctRecords'),
                                'options' => array("", "yes", "no"),
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
                                    "title" => t('title','AccountRemoval'),
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
