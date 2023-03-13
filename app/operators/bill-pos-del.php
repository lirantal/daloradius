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
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            $username = (array_key_exists('username', $_POST) && !empty(str_replace("%", trim($_POST['username']))))
                      ? str_replace("%", trim($_POST['username'])) : "";
            
            if (!empty($username)) {
            
                $delradacct = (array_key_exists('delradacct', $_POST) && strtolower(trim($_POST['delradacct'])) == 'yes');
            
                $tables = array(
                                    $configValues['CONFIG_DB_TBL_RADCHECK'],
                                    $configValues['CONFIG_DB_TBL_RADREPLY'],
                                    $configValues['CONFIG_DB_TBL_DALOUSERINFO'],
                                    $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                    $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                               );
                               
                if ($delradacct) {
                    $tables[] = $configValues['CONFIG_DB_TBL_RADACCT'];
                }
            
                include('../common/includes/db_open.php');
            
                $format = "DELETE FROM %s WHERE username='%s'";
                foreach ($tables as $table) {
                    $sql = sprintf($format, $table, $dbSocket->escapeSimple($username));
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                }
                
                // get user id from userbillinfo table 
                $sql = sprintf("SELECT id FROM %s WHERE username='%s'",
                               $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'], $dbSocket->escapeSimple($username));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                $user_id = intval($res->fetchrow()[0]);
                
                // to remove all invoices and payments we need to get the invoices_id
                $sql = sprintf("SELECT id FROM %s WHERE user_id=%d",
                               $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'], $user_id);
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                
                $invoice_id = array();
                while ($row = $res->fetchrow()) {
                    $invoice_id[] = intval($row[0]);
                }
                
                $tables = array(
                                    $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'],
                                    $configValues['CONFIG_DB_TBL_DALOPAYMENTS']
                               );
                
                $format = "DELETE FROM %s WHERE invoice_id IN (%s)";
                
                // delete all invoice items and all payment items
                foreach ($tables as $table) {
                    $sql = sprintf($format, $table, implode(", ", $invoice_id));
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";  
                }

                // remove all invoices by this user
                $sql = sprintf("DELETE FROM %s WHERE user_id=%d",
                               $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'], $user_id);
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                
                include('../common/includes/db_close.php');
            
                $successMsg = "Deleted user: <strong>$username_enc</strong>";
                $logAction .= "Successfully deleted user [$username] on page: ";
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
        $username = (array_key_exists('username', $_REQUEST) && !empty(str_replace("%", trim($_REQUEST['username']))))
                  ? str_replace("%", trim($_REQUEST['username'])) : "";
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
    include('../common/includes/db_open.php');
    
    $sql = sprintf("SELECT DISTINCT(username) FROM %s", $configValues['CONFIG_DB_TBL_RADCHECK']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    $options = array( "" );
    while ($row = $res->fetchrow()) {
        $options[] = $row[0];
    }
    
    include('../common/includes/db_close.php');

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
