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

    include_once('include/management/populate_selectbox.php');
    $valid_profiles = get_groups();

    $profile_tables = array(
                                $configValues['CONFIG_DB_TBL_RADGROUPCHECK'],
                                $configValues['CONFIG_DB_TBL_RADGROUPREPLY']
                           );

    include('../common/includes/db_open.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
         
           
        
            if (array_key_exists('profile_names', $_POST) && !empty($_POST['profile_names'])) {
            
                $profile_delete_assoc = (array_key_exists('profile_delete_assoc', $_POST) &&
                                         strtolower(trim($_POST['profile_delete_assoc'])) == "yes");
                
                $profile_names = (!is_array($_POST['profile_names'])) ? array( trim($_POST['profile_names']) ) : $_POST['profile_names'];
                
                $profile_tables[] = $configValues['CONFIG_DB_TBL_RADUSERGROUP'];
                                    
                $sql_format = "DELETE FROM %s WHERE groupname='%s'";
                
                $deleted_profiles = 0;
                $deleted_mappings = 0;
                foreach ($profile_names as $profile_name) {
                    $profile_name = trim($profile_name);
                    
                    if (!in_array($profile_name, $valid_profiles)) {
                        continue;
                    }
                    
                    if ($profile_delete_assoc) {
                        // only delete user-profile mappings
                        $sql = sprintf($sql_format, $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                                                    $dbSocket->escapeSimple($profile_name));
                        $res = $dbSocket->query($sql);
                        $logDebugSQL .= "$sql;\n";

                        if (!DB::isError($res)) {
                            $deleted_mappings++;
                        }

                    } else {
                        
                        // delete everything (all attributes and user-profile mappings)
                        foreach ($profile_tables as $profile_table) {
                            $sql = sprintf($sql_format, $profile_table, $dbSocket->escapeSimple($profile_name));
                            $res = $dbSocket->query($sql);
                            $logDebugSQL .= "$sql;\n";
                            
                            if (!DB::isError($res)) {
                                $deleted_profiles++;
                            }
                        }
                    }
                }
                
                if ($deleted_profiles > 0) {
                    $successMsg = sprintf("Completely removed attributes and user mappings for %s profile(s)", $deleted_profiles);
                    $logAction .= "$successMsg on page: ";
                } else if ($deleted_mappings > 0) {
                    $successMsg = sprintf("Removed all user mappings for %s profile(s)", $deleted_profiles);
                    $logAction .= "$successMsg on page: ";
                } else {
                    $failureMsg = "Failed to remove attributes and/or user mappings for the selected profile(s)";
                    $logAction .= "$failureMsg on page: ";
                }
                
            } else if (array_key_exists('profile__id__table', $_POST) && !empty($_POST['profile__id__table'])) {
                
                $arr = (!is_array($_POST['profile__id__table'])) ? array( trim($_POST['profile__id__table']) ) : $_POST['profile__id__table'];
                
                // needed for a possible later clean up
                $modified_profiles = array();
                
                foreach ($arr as $arr_item) {
                    
                    $tmp = explode("__", $arr_item);
                    if (count($tmp) != 3) {
                        continue;
                    }
                    
                    list($profile, $id, $table) = $tmp;
                    
                    // validate table
                    $table = trim($table);
                    if (!in_array($table, $profile_tables)) {
                        continue;
                    }
                    
                    // validate id
                    $id = trim($id);
                    if (preg_match("/^[0-9]+$/", $id) === false) {
                        continue;
                    }
                    
                    $id = intval($id);
                    
                    // validate profile name
                    $profile = trim($profile);
                    
                    if (!in_array($profile, $valid_profiles)) {
                        continue;
                    }
                    
                    $sql = sprintf("SELECT COUNT(id) FROM %s WHERE id=%d", $dbSocket->escapeSimple($table), $id);
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                    $exists = $res->fetchrow()[0] == 1;
                    
                    if (!$exists) {
                        continue;
                    }
                    
                    $sql = sprintf("DELETE FROM %s WHERE id=%d", $dbSocket->escapeSimple($table), $id);
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                    
                    if (!DB::isError($res)) {
                        if (!in_array($profile, $modified_profiles)) {
                            $modified_profiles[] = $profile;
                        }
                    }
                    
                } // foreach
                
                $modified = count($modified_profiles);
                if ($modified > 0) {
                    // if there are no check and reply attributes left for a modified profile
                    // we clean up user-profile mapping(s) (if any)
                    
                    
                    foreach ($modified_profiles as $profile_name) {
                    
                        $attributes_left = 0;
                        foreach ($profile_tables as $profile_table) {
                            $sql = sprintf("SELECT COUNT(id) FROM %s WHERE groupname='%s'",
                                           $profile_table, $dbSocket->escapeSimple($profile_name));
                            $res = $dbSocket->query($sql);
                            $logDebugSQL .= "$sql;\n";
                            
                            $attributes_left += intval($res->fetchrow()[0]);
                        }
                        
                        if ($attributes_left == 0) {
                            $sql = sprintf("DELETE FROM %s WHERE groupname='%s'",
                                           $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                                           $dbSocket->escapeSimple($profile_name));
                            $res = $dbSocket->query($sql);
                            $logDebugSQL .= "$sql;\n";
                        }
                        
                    }
                    
                    $successMsg = sprintf("%s profile(s) have been deleted/modified", $modified);
                    $logAction .= "$successMsg on page: ";

                } else {
                    // 
                    $failureMsg = "No profile(s) have been changed";
                    $logAction .= "$successMsg on page: ";
                }
                
            } else {
                // invalid request
                $failureMsg = "Invalid request";
                $logAction .= "$failureMsg on page: ";
            }

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "CSRF token error on page: ";
        }
    } else {
        // !POST
        $profile_name = (array_key_exists('profile_name', $_REQUEST) && !empty(str_replace("%", "", trim($_REQUEST['profile_name']))))
                      ? str_replace("%", "", trim($_REQUEST['profile_name'])) : "";
                      
        $id = (array_key_exists('id', $_REQUEST) && preg_match("/^[0-9]+$/", $_REQUEST['id'])) ? intval($_REQUEST['id']) : "";
        
        $profile_table = (array_key_exists('tablename', $_REQUEST) && in_array($_REQUEST['tablename'], $profile_tables))
                       ? $_REQUEST['tablename'] : "";
        
        if (!empty($profile_name) && !empty($id) && !empty($profile_table)) {
            $profile__id__table = sprintf("%s__%d__%s", $profile_name, $id, $profile_table);
        }
        
    }

    include('../common/includes/db_close.php');
    
    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // print HTML prologue
    
    $title = t('Intro','mngradprofilesdel.php');
    $help = t('helpPage','mngradprofilesdel');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    
    
    if (!isset($successMsg)) {
        
        $frameset_disabled = false;
        
        $input_descriptors1 = array();
    
        
        if (!empty($profile__id__table) || empty($profile_name)) {
            $options = array();
            
            include('../common/includes/db_open.php');
            
            foreach ($profile_tables as $profile_table) {
                $sql = sprintf("SELECT id, groupname, attribute FROM %s", $profile_table);
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                
                while ($row = $res->fetchrow()) {
                    list($id, $profile_name, $attribute) = $row;
                    
                    $key = sprintf("%s__%s__%s", $profile_name, $id, $profile_table);
                    
                    if (array_key_exists($key, $options)) {
                        continue;
                    }
                    
                    $options[$key] = sprintf("%s, %s (%s)", $profile_name, $attribute, $profile_table);
                }

            }
            
            include('../common/includes/db_close.php');
            
            $input_descriptors1[] = array(
                                            'name' => 'profile__id__table[]',
                                            'id' => 'profile__id__table',
                                            'type' => 'select',
                                            'caption' => "Profile, attribute (attribute type)",
                                            'options' => $options,
                                            'multiple' => true,
                                            'size' => 5,
                                            'selected_value' => ((!empty($profile__id__table)) ? $profile__id__table : ""),
                                         );
                                         
            $frameset_disabled = count($options) == 0;
        } else {
            $input_descriptors1[] = array(
                                            'name' => 'profile_names[]',
                                            'id' => 'profile_names',
                                            'type' => 'select',
                                            'caption' => "Profile Name",
                                            'options' => $valid_profiles,
                                            'selected_value' => ((!empty($profile_name)) ? $profile_name : ""),
                                            'multiple' => true,
                                            'size' => 5,
                                         );

            $input_descriptors1[] = array(
                                            'name' => 'profile_delete_assoc',
                                            'type' => 'select',
                                            'caption' => "Only remove user mappings for this profile(s)",
                                            'options' => array("", "yes", "no"),
                                         );
                                         
            $frameset_disabled = count($valid_profiles) == 0;
        }
        
        
        
        
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
                                        "title" => t('title','ProfileInfo'),
                                        "disabled" => $frameset_disabled
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
