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

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";


    include('../common/includes/db_open.php');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ratename = (array_key_exists('ratename', $_POST) && !empty(str_replace("%", "", trim($_POST['ratename']))))
                  ? str_replace("%", "", trim($_POST['ratename'])) : "";
    } else {
        $ratename = (array_key_exists('ratename', $_REQUEST) && !empty(str_replace("%", "", trim($_REQUEST['ratename']))))
                  ? str_replace("%", "", trim($_REQUEST['ratename'])) : "";
    }


    // check if this rate exists
    $sql = sprintf("SELECT COUNT(id) FROM %s WHERE rateName='%s'", $configValues['CONFIG_DB_TBL_DALOBILLINGRATES'],
                                                                   $dbSocket->escapeSimple($ratename));
    $res = $dbSocket->query($sql);
    
    $exists = intval($res->fetchrow()[0]) == 1;

    if (!$exists) {
        // we reset the rate if it does not exist
        $ratename = "";
    }

    $ratename_enc = (!empty($ratename)) ? htmlspecialchars($ratename, ENT_QUOTES, 'UTF-8') : "";
    
    //feed the sidebar variables
    $edit_ratename = $ratename_enc;


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
        
            if (empty($ratename)) {
                // required
                $failureMsg = "invalid or empty rate name, please specify a valid rate name to edit.";
                $logAction .= "invalid or empty rate name on page: ";
            } else {
                $sql_SET = array();
                
                // required later
                $currDate = date('Y-m-d H:i:s');
                $currBy = $operator;
            
                $sql_SET[] = sprintf("updatedate='%s'", $currDate);
                $sql_SET[] = sprintf("updateby='%s'", $currBy);
                
                $ratecost = (array_key_exists('ratecost', $_POST) && intval(trim($_POST['ratecost'])) > 0)
                          ? intval(trim($_POST['ratecost'])) : "";
                if (!empty($ratecost)) {
                    $sql_SET[] = sprintf("rateCost=%d", $ratecost);
                }
                
                $ratetypenum = (array_key_exists('ratetypenum', $_POST) && intval(trim($_POST['ratetypenum'])) > 0)
                          ? intval(trim($_POST['ratetypenum'])) : 1;
                
                $ratetypetime = (array_key_exists('ratetypetime', $_POST) && !empty(trim($_POST['ratetypetime'])) &&
                                 in_array(trim($_POST['ratetypetime']), $valid_timeUnits))
                              ? trim($_POST['ratetypetime']) : "";
                
                if (!empty($ratetypetime)) {
                    $sql_SET[] = sprintf("rateType='%d/%s'", $ratetypenum, $ratetypetime);
                }
                
                $sql = sprintf("UPDATE %s SET ", $configValues['CONFIG_DB_TBL_DALOBILLINGRATES'])
                     . implode(", ", $sql_SET)
                     . sprintf(" WHERE rateName='%s'", $dbSocket->escapeSimple($ratename));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                
                if (!DB::isError($res)) {
                    $successMsg = "Successfully updated rate (<strong>$ratename_enc</strong>)";
                    $logAction .= "Successfully updated rate [$ratename] on page: ";
                } else {
                    $failureMsg = "Failed to updated rate (<strong>$ratename_enc</strong>)";
                    $logAction .= "Failed to updated rate [$ratename] on page: ";
                }
            }
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    if (empty($ratename)) {
        $failureMsg = "invalid or empty rate name entered, please specify a valid rate name to edit.";
        $logAction .= "$failureMsg on page: ";
    } else {
    
        $sql = sprintf("SELECT id, rateType, rateCost, creationdate, creationby, updatedate, updateby FROM %s WHERE rateName='%s'",
                       $configValues['CONFIG_DB_TBL_DALOBILLINGRATES'], $dbSocket->escapeSimple($ratename));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
    
        $row = $res->fetchrow();
    
        list( $id, $ratetype, $ratecost, $creationdate, $creationby, $updatedate, $updateby ) = $row;
        list( $ratetypenum, $ratetypetime ) = explode("/", $ratetype);
    
    }

    include('../common/includes/db_close.php');


    // print HTML prologue
    $title = t('Intro','billratesedit.php');
    $help = t('helpPage','billratesedit');
    
    print_html_prologue($title, $langCode);

    if (!empty($ratename_enc)) {
        $title .= " :: $ratename_enc";
    } 

    

    
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    if (!empty($ratename)) {
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
