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
    $operator_id = $_SESSION['operator_id'];

    include('library/check_operator_perm.php');

    // init logging variables
    $logAction = "";
    $logDebugSQL = "";
    $log = "visited page: ";

    include('../common/includes/db_open.php');

    // init field_name and values (all, valid and to delete)
    $field_name = 'operator_username';
    
    $valid_values = array();
    
    $sql = sprintf("SELECT id, username FROM %s", $configValues['CONFIG_DB_TBL_DALOOPERATORS']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    // foreach username we obtain a map of valid username => id
    while ($row = $res->fetchRow()) {
        list($id, $username) = $row;
        if (!in_array($username, array_values($valid_values))) {
            $valid_values["$id"] = $username;
        }
    }
    
    if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
        $values = array();
        $deleted_values = array();
        
        // validate values
        if (array_key_exists($field_name, $_POST) && isset($_POST[$field_name])) {
            
            $tmp = (!is_array($_POST[$field_name])) ? array($_POST[$field_name]) : $_POST[$field_name];
            foreach ($tmp as $value) {
                
                $value = trim(str_replace("%", "", $value));
        
                foreach ($valid_values as $id => $valid_value) {
                    if ($value == $valid_value) {
                        $values[] = $id;
                    }
                }
            }
        }
        
        // use valid values for updating db,
        // update deleted_values as a valid value has been removed
        if (count($values) > 0) {
            foreach ($values as $id) {
                $id = intval($id);
                
                // delete all operators' acl entries
                $sql = sprintf("DELETE FROM %s WHERE operator_id=%d",
                               $configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'], $id);
                $result = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                
                // delete operator from database
                $sql = sprintf("DELETE FROM %s WHERE id=%d", $configValues['CONFIG_DB_TBL_DALOOPERATORS'], $id);
                $result += $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                
                if ($result > 0) {
                    $deleted_values[] = $valid_values["$id"];
                }
            }
        }
        
        $success = $_SERVER['REQUEST_METHOD'] == 'POST' && count($values) > 0 && count($deleted_values) > 0;
        
        // present results
        if ($success) {
            $tmp = array();
            foreach ($deleted_values as $deleted_value) {
                $tmp[] = htmlspecialchars($deleted_value, ENT_QUOTES, 'UTF-8');
            }
            
            $successMsg = sprintf("Deleted operator(s): <strong>%s</strong>", implode(", ", $tmp));
            $logAction .= sprintf("Successfully deleted operator(s) [%s] on page: ", implode(", ", $deleted_values));
        } else {
            $failureMsg = "empty or invalid operator(s) have been entered";
            $logAction .= sprintf("Failed deleting operator(s) [%s] on page: ", implode(", ", $valid_values));
        }
        
        include('../common/includes/db_close.php');
    } else {
        $success = false;
        $failureMsg = "CSRF token error";
        $logAction .= "$failureMsg on page: ";
    }
    
    include_once('../common/includes/config_read.php');
    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // print HTML prologue
    
    $title = t('Intro','configoperatorsdel.php');
    $help = t('helpPage','configoperatorsdel');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);
    
    if ($_SERVER['REQUEST_METHOD'] != 'GET') {
        include_once('include/management/actionMessages.php');
    }

    if (!$success) {
        $options = array_values($valid_values);
    
        $input_descriptors1 = array();
        
        $input_descriptors1[0] = array(
                                        'name' => 'operator_username[]',
                                        'id' => 'operator_username',
                                        'type' => 'text',
                                        'caption' => 'Operator Username',
                                      );
        
        if (count($options) > 0) {
            $input_descriptor[0]['datalist'] = $options;
        } else {
            $input_descriptor[0]['disabled'] = true;
        }

        $input_descriptors1[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                      );
                                  
        $input_descriptors1[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );
                                     
        $fieldset1_descriptor = array(
                                        "title" => "Operator Account Removal",
                                        "disabled" => (count($options) == 0)
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
