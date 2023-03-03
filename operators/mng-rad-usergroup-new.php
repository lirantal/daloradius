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
    include("include/management/functions.php");

    // declaring variables
    $username = (array_key_exists('username', $_POST) && isset($_POST['username']))
              ? trim(str_replace("%", "", $_POST['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    
    $groupname = (array_key_exists('group', $_POST) && isset($_POST['group']))
               ? trim(str_replace("%", "", $_POST['group'])) : "";
    $groupname_enc = (!empty($groupname)) ? htmlspecialchars($groupname, ENT_QUOTES, 'UTF-8') : "";
    
    $priority = (array_key_exists('priority', $_POST) && isset($_POST['priority']) &&
                 intval(trim($_POST['priority'])) >= 0) ? intval(trim($_POST['priority'])) : 0;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
    
            if (empty($username) || empty($groupname)) {
                // username and groupname are required
                $failureMsg = "Username and groupname are required.";
                $logAction .= "Failed adding user-group mapping (username and/or groupname missing): ";
            } else {
                include('../common/includes/db_open.php');
            
                // check if this mapping is already in place
                $user_group_mappings = get_user_group_mappings($dbSocket, $username);
                $exists = in_array($groupname, $user_group_mappings);
                
                if ($exists) {
                    // this user mapping is already in place
                    $failureMsg = "The chosen user mapping ($username_enc &isin; $groupname_enc) is already in place.";
                    $logAction .= "Failed adding user-group mapping [$username_enc < $groupname_enc already in place]: ";
                } else {
                    // insert usergroup details
                    $success = insert_single_user_group_mapping($dbSocket, $username, $groupname, $priority);
                    
                    if ($success) {
                        $successMsg = sprintf('Added new user-group mapping (%s &isin; %s) '
                                            . '[<a href="mng-rad-usergroup-edit.php?username=%s&current_group=%s">Edit</a>]',
                                              $username_enc, $groupname_enc, urlencode($username_enc), urlencode($groupname_enc));
                        $logAction .= "Added new user-group mapping [$username < $groupname]: ";
                    } else {
                        $failureMsg = "DB Error when adding the chosen user mapping ($username_enc &isin; $groupname_enc)";
                        $logAction .= "Failed adding user-group mapping [$username < $groupname, db error]: ";
                    }
                }
                
                include('../common/includes/db_close.php');
            }
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }


    $title = t('Intro','mngradusergroupnew.php');
    $help = t('helpPage','mngradusergroupnew');
    
    print_html_prologue($title, $langCode);
    
    


    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    include_once('include/management/populate_selectbox.php');

    $input_descriptors0 = array();
    
    $options = get_users();
    array_unshift($options , '');
    
    $input_descriptors0[] = array(
                                    "id" => "username",
                                    "name" => "username",
                                    "caption" => t('all','Username'),
                                    "type" => "text",
                                    "value" => ((isset($failureMsg)) ? $username : ""),
                                    "tooltipText" => t('Tooltip','usernameTooltip'),
                                    "datalist" => $options,
                                 );

    $options = get_groups();
    array_unshift($options , '');
    $input_descriptors0[] = array(
                                    "id" => "group",
                                    "name" => "group",
                                    "caption" => t('all','Groupname'),
                                    "type" => "select",
                                    "options" => $options,
                                    "selected_value" => ((isset($failureMsg)) ? $groupname : ""),
                                    "tooltipText" => t('Tooltip','groupTooltip')
                                 );
                                 
    $input_descriptors0[] = array(
                                    "id" => "priority",
                                    "name" => "priority",
                                    "caption" => t('all','Priority'),
                                    "type" => "number",
                                    "min" => "0",
                                    "value" => ((isset($failureMsg)) ? $priority : "0"),
                                 );

    $input_descriptors1 = array();

    $input_descriptors1[] = array(
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                        "name" => "csrf_token"
                                     );

    $input_descriptors1[] = array(
                                    'type' => 'submit',
                                    'name' => 'submit',
                                    'value' => t('buttons','apply')
                                 );

    $fieldset0_descriptor = array( "title" => t('title','GroupInfo') );
        
    open_form();
    
    open_fieldset($fieldset0_descriptor);
    
    foreach ($input_descriptors0 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
    
    close_fieldset();
    
    foreach ($input_descriptors1 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
    
    close_form();
    
    print_back_to_previous_page();

    include('include/config/logging.php');

    print_footer_and_html_epilogue();
?>
