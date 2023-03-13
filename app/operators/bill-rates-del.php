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
    
    include('../common/includes/db_open.php');

    $valid_ratenames = array();
    
    $sql = sprintf("SELECT DISTINCT(ratename) FROM %s ORDER BY ratename ASC",
                   $configValues['CONFIG_DB_TBL_DALOBILLINGRATES']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    while ($row = $res->fetchrow()) {
        $valid_ratenames[] = $row[0];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            if (array_key_exists('ratename', $_POST) && !empty($_POST['ratename'])) {
                $ratename = array();
            
                $tmparr = (!is_array($_POST['ratename'])) ? array( $_POST['ratename'] ) : $_POST['ratename'];
                
                foreach ($tmparr as $tmp_name) {
                    $tmp_name = trim($tmp_name);
                    if (!in_array($tmp_name, $valid_ratenames)) {
                        continue;
                    }
                
                    $tmp_name = $dbSocket->escapeSimple($tmp_name);
                    if (!in_array($tmp_name, $ratename)) {
                        $ratename[] = $tmp_name;
                    }
                }
                
                if (count($ratename) > 0) {
                
                    $sql = sprintf("DELETE FROM %s WHERE ratename IN ('%s')",
                                   $configValues['CONFIG_DB_TBL_DALOBILLINGRATES'], implode("', '", $ratename));
                    $count = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                    
                    if (!DB::isError($res)) {
                        $successMsg = sprintf("Deleted %d rate(s)", intval($count));
                        $logAction .= "$successMsg on page: ";
                    } else {
                        $failureMsg = "Failed deleting rate(s)";
                        $logAction .= sprintf("Failed deleting rate(s) [%s] on page: ", $failureMsg);
                    }
                
                } else {
                    // invalid
                    $failureMsg = "Empty or invalid rate name(s)";
                    $logAction .= sprintf("Failed deleting rate(s) [%s] on page: ", $failureMsg);
                }
            } else {
                // invalid
                $failureMsg = "Empty or invalid rate name(s)";
                $logAction .= sprintf("Failed deleting rate(s) [%s] on page: ", $failureMsg);
            }
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    } else {
        // !POST
        $ratename = (array_key_exists('ratename', $_REQUEST) && !empty(str_replace("%", "", trim($_REQUEST['ratename']))))
                     ? str_replace("%", "", trim($_REQUEST['ratename'])) : "";
        
        if (empty($ratename) || !in_array($ratename, $valid_ratenames)) {
            $ratename = "";
        }
    }

    include('../common/includes/db_close.php');

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
                                    'selected_value' => $ratename
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
