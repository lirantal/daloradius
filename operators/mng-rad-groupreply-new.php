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

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");

    include_once('include/management/populate_selectbox.php');
    

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
    
            $groupname = (array_key_exists('groupname', $_POST) && isset($_POST['groupname']))
                     ? trim(str_replace("%", "", $_POST['groupname'])) : "";
            $groupname_enc = (!empty($groupname)) ? htmlspecialchars($groupname, ENT_QUOTES, 'UTF-8') : "";
        
            if (empty($groupname)) {
                // profile required
                $failureMsg = "The specified group name is empty or invalid";
                $logAction .= "Failed creating a new group reply mapping [empty or invalid group name] on page: ";
            } else {
                
                include('../common/includes/db_open.php');
                
                $groups = array_keys(get_groups());
                if (!in_array($groupname, $groups)) {
                    // invalid profile name
                    $failureMsg = "The chosen group [<strong>$groupname_enc</strong>] does not exist";
                    $logAction .= "Failed creating group reply mapping [$groupname, does not exist] on page: ";
                } else {
        
                    include("library/attributes.php");
                    $skipList = array( "groupname", "submit", "csrf_token" );
                    $count = handleAttributes($dbSocket, $groupname, $skipList, true, 'group');

                    if ($count > 0) {
                        // retrieve item id
                        $sql = sprintf("SELECT CONCAT('groupreply-', LAST_INSERT_ID()) FROM %s",
                                       $configValues['CONFIG_DB_TBL_RADGROUPREPLY']);
                        $item_id = $dbSocket->getOne($sql);
                        
                        $successMsg = sprintf("Successfully added a new groupreply item (item id: %s)", $item_id)
                                    . sprintf(' [<a href="mng-rad-groupreply-edit.php?item=%s" title="Edit">Edit</a>]',
                                              urlencode($item_id));
                        $logAction .= "Successfully added a new groupreply item (item id: $item_id) on page: ";
                    } else {
                        $failureMsg = "Failed adding a new groupreply item (item id: $item_id), invalid or empty attributes list";
                        $logAction .= "Failed adding a new groupreply item (item id: $item_id) [invalid or empty attributes list] on page: ";
                    }

                } // profile non-existent
                
                include('../common/includes/db_close.php');
                
            } // profile name not empty
        
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
        
    }


    // print HTML prologue
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/dynamic_attributes.js",
        "static/js/ajaxGeneric.js",
    );
    
    $title = t('Intro','mngradgroupreplynew.php');
    $help = t('helpPage','mngradgroupreplynew');
    
    print_html_prologue($title, $langCode, array(), $extra_js);

    

    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    if (!isset($successMsg)) {
        
        // set form component descriptors
        $input_descriptors0 = array();
        
        $groups = get_groups();
        array_unshift($groups , '');
        $input_descriptors0[] = array(
                                        "name" => "groupname",
                                        "caption" => t('all','Groupname'),
                                        "type" => "select",
                                        "options" => $groups,
                                        "selected_value" => ((isset($groupname)) ? $groupname : "")
                                     );

        $input_descriptors1 = array();
        $input_descriptors1[] = array(
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                        "name" => "csrf_token"
                                     );

        $input_descriptors1[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                     );
                                     
        open_form();
        
        $fieldset0_descriptor = array( "title" => t('title','GroupInfo') );
        
        open_fieldset($fieldset0_descriptor);
        
        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        include_once('include/management/attributes.php');
        
        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_form();
        
    }
    
    print_back_to_previous_page();
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
