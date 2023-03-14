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
    include_once('../common/includes/config_read.php');
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    include('../common/includes/db_open.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            
            $arr = array();
            
            if (array_key_exists('vendor__attribute', $_POST) && !empty($_POST['vendor__attribute'])) {
                $arr = (!is_array($_POST['vendor__attribute']))
                     ? array( $_POST['vendor__attribute'] ) : $_POST['vendor__attribute'];
            }
            
            if (count($arr) > 0) {
                
                $deleted = 0;
                foreach ($arr as $arr_elem) {
                    $tmp = explode("__", $arr_elem);
                    if (count($tmp) != 2) {
                        continue;
                    }
                    
                    list($vendor, $attribute) = $tmp;
                    $vendor = str_replace("%", "", trim($vendor));
                    $attribute = str_replace("%", "", trim($attribute));
                    
                    if (empty($vendor) || empty($attribute)) {
                        continue;
                    }
                    
                    $sql_WHERE = array();
                    $sql_WHERE[] = sprintf("vendor='%s'", $dbSocket->escapeSimple($vendor));
                    $sql_WHERE[] = sprintf("attribute='%s'", $dbSocket->escapeSimple($attribute));
                    
                    
                    // check if attribute exists
                    $sql = sprintf("SELECT COUNT(id) FROM %s", $configValues['CONFIG_DB_TBL_DALODICTIONARY'])
                         . " WHERE " . implode(" AND ", $sql_WHERE);
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                    
                    $exists = $res->fetchrow()[0] == 1;
                    
                    if (!$exists) {
                        continue;
                    }
                    
                    $sql = sprintf("DELETE FROM %s", $configValues['CONFIG_DB_TBL_DALODICTIONARY'])
                         . " WHERE " . implode(" AND ", $sql_WHERE);
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                    
                    if (!DB::isError($res)) {
                        $deleted++;
                    }
                }
                
                if ($deleted > 0) {                
                    $successMsg = sprintf("Deleted %s vendor/attribute(s)", $deleted);
                    $logAction .= "$successMsg on page: ";
                } else {
                    // invalid
                    $failureMsg = "Empty or invalid vendor/attribute list";
                    $logAction .= sprintf("Failed deleting vendor/attribute(s) [%s] on page: ", $failureMsg);
                }
                
            } else {
                // invalid
                $failureMsg = "Empty or invalid vendor/attribute list";
                $logAction .= sprintf("Failed deleting vendor/attribute(s) [%s] on page: ", $failureMsg);
            }
            
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // print HTML prologue
    $title = t('Intro','mngradattributesdel.php');
    $help = t('helpPage','mngradattributesdel');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    // load options
    $options = array();
    
    $sql = sprintf("SELECT vendor, attribute FROM %s ORDER BY vendor, attribute",
                   $configValues['CONFIG_DB_TBL_DALODICTIONARY']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    while ($row = $res->fetchrow()) {
        list($vendor, $attribute) = $row;
        $value = sprintf("%s__%s", $vendor, $attribute);
        $caption = sprintf("%s - %s", $vendor, $attribute);
        $options[$value] = $caption;
    }
    
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
