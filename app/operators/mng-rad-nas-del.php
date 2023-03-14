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

    include('library/check_operator_perm.php');

    // init logging variables
    $logAction = "";
    $logDebugSQL = "";
    $log = "visited page: ";

    include('../common/includes/db_open.php');
    
    // init field_name and values (all, valid and to delete)
    $field_name = 'nashost';
    $db_field_name = 'nasname';
    
    $valid_values = array();
    
    $sql = sprintf("SELECT DISTINCT(%s) FROM %s", $db_field_name, $configValues['CONFIG_DB_TBL_RADNAS']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    while ($row = $res->fetchRow()) {
        $valid_values[] = $row[0];
    }
    
    if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
    
        $values = array();
        $deleted_values = array();
        
        // validate values
        if (array_key_exists($field_name, $_POST) && isset($_POST[$field_name])) {
            
            $tmp = (!is_array($_POST[$field_name])) ? array($_POST[$field_name]) : $_POST[$field_name];
            foreach ($tmp as $value) {
                if (in_array($value, $valid_values)) {
                    $values[] = $value;
                }
            }
        }
        
        // use valid values for updating db,
        // update deleted_values as a valid value has been removed
        if (count($values) > 0) {
            foreach ($values as $value) {
                $sql = sprintf("DELETE FROM %s WHERE %s='%s'", $configValues['CONFIG_DB_TBL_RADNAS'],
                                                               $db_field_name, $dbSocket->escapeSimple($value));
                $result = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                
                if ($result > 0) {
                    $deleted_values[] = $value;
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
            
            $label = (count($tmp) > 1 || count($tmp) == 0) ? "NASs" : "NAS";
            
            $successMsg = sprintf("Deleted %s: <strong>%s</strong>.", $label, implode(", ", $tmp));
            $logAction .= sprintf("Successfully deleted %s [%s] on page: ", $label, implode(", ", $deleted_values));
        } else {
            $failureMsg = "Empty or invalid NAS hostname/IP(s).";
            $logAction .= sprintf("Failed deleting NAS(s) [%s] on page: ", implode(", ", $valid_values));
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
    
    $title = t('Intro','mngradnasdel.php');
    $help = t('helpPage','mngradnasdel');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);
    
    if ($_SERVER['REQUEST_METHOD'] != 'GET') {
        include_once('include/management/actionMessages.php');
    }
    
    if (!$success) {
        $options = $valid_values;
        
        $input_descriptors1 = array();
        
        $input_descriptors1[0] = array(
                                        'name' => $field_name . "[]",
                                        'id' => $field_name,
                                        'type' => 'text',
                                        'caption' => t('all','NasIPHost'),
                                      );
        
        
        if (count($options) > 0) {
            $input_descriptors1[0]['datalist'] = $options;
        } else {
            $input_descriptors1[0]['disabled'] = true;
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
                                        "title" => t('title','NASInfo'),
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
