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
    
    include_once('../common/includes/config_read.php');
    include('library/check_operator_perm.php');
    
    include_once("lang/main.php");
    include("../common/includes/layout.php");
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    
    // print HTML prologue
    $title = t('Intro','mngradprofilesduplicate.php');
    $help = t('helpPage','mngradprofilesduplicate');
    
    print_html_prologue($title, $langCode);
    
    print_title_and_help($title, $help);
    
    // we use get_groups() to check for the existance of new and old profile
    include_once('include/management/populate_selectbox.php');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) &&
            dalo_check_csrf_token($_POST['csrf_token'])) {
        
            include('../common/includes/db_open.php');
            
            $sourceProfile = (array_key_exists('sourceProfile', $_REQUEST) && isset($_REQUEST['sourceProfile']))
                           ? trim(str_replace("%", "", $_REQUEST['sourceProfile'])) : "";
            $sourceProfile_enc = (!empty($sourceProfile)) ? htmlspecialchars($sourceProfile, ENT_QUOTES, 'UTF-8') : "";
            
            $targetProfile = (array_key_exists('targetProfile', $_REQUEST) && isset($_REQUEST['targetProfile']))
                           ? trim(str_replace("%", "", $_REQUEST['targetProfile'])) : "";
            $targetProfile_enc = (!empty($targetProfile)) ? htmlspecialchars($targetProfile, ENT_QUOTES, 'UTF-8') : "";
        
            if (empty($sourceProfile) || empty($targetProfile)) {
                // profiles are required
                $failureMsg = "Source and target profile names are required";
                $logAction .= "Failed duplicating profile [$failureMsg] on page: ";
            } else {
            
                $groups = get_groups();
            
                if (!in_array($sourceProfile, $groups)) {
                    // source profile non-existent
                    $failureMsg = "Invalid source profile name";
                    $logAction .= "Failed duplicating profile [$failureMsg] on page: ";
                } else {
                    if (in_array($targetProfile, $groups)) {
                        // target profile already inplace
                        $failureMsg = "Invalid target profile name";
                        $logAction .= "Failed duplicating profile [$failureMsg] on page: ";
                    } else {
                        
                        // we duplicate group attributes which are present in these two tables
                        $tables = array(
                                          $configValues['CONFIG_DB_TBL_RADGROUPCHECK'],
                                          $configValues['CONFIG_DB_TBL_RADGROUPREPLY']
                                       );
                        
                        // this are the query used
                        $sql_select_format = "SELECT '%s', attribute, op, value FROM %s WHERE groupname='%s'";
                        $sql_insert_format = "INSERT INTO %s (groupname, attribute, op, value)";
                        
                        $counter = 0;
                        foreach ($tables as $table) {
                            $sql_select = sprintf($sql_select_format, $dbSocket->escapeSimple($targetProfile),
                                                                      $table,
                                                                      $dbSocket->escapeSimple($sourceProfile));
                            $sql_insert = sprintf($sql_insert_format, $table, $sql_select);
                            
                            $sql = $sql_insert . " " . $sql_select;
                            $res = $dbSocket->query($sql);
                            $logDebugSQL .= "$sql;\n";
                            
                            if (!DB::isError($res)) {
                                $counter += $res;
                            }
                        }
                        
                        if ($counter > 0) {
                            $successMsg = sprintf("Profile <strong>%s</strong> has been successfully cloned into <strong>%s</strong>",
                                                  $sourceProfile_enc, $targetProfile_enc);
                            $logAction .= "Successfully cloned profile [$sourceProfile] to new profile name [$targetProfile] on page: ";
                            
                            // we empty these two variables for presentation purpose
                            $sourceProfile = "";
                            $targetProfile = "";
                        } else {
                            $failureMsg = sprintf("Cannot clone profile <strong>%s</strong> into <strong>%s</strong>",
                                                  $sourceProfile_enc, $targetProfile_enc);
                            $logAction .= "Failed while cloning profile [$sourceProfile] on page: ";
                        }
                        
                    }
                }
            }
                           
            include('../common/includes/db_close.php');

        } else {
            $failureMsg = sprintf("CSRF token error");
            $logAction .= sprintf("CSRF token error on page: ");
        }
    }
    
    include_once('include/management/actionMessages.php');
    
    $options = get_groups();
    
    $input_descriptors0 = array();
    $input_descriptors0[] = array(
                                    "name" => "sourceProfile",
                                    "caption" => "Profile Name to Duplicate",
                                    "type" => "select",
                                    "options" => $options,
                                    "selected_value" => (isset($sourceProfile)) ? $sourceProfile : "",
                                 );
    $input_descriptors0[] = array(
                                    "name" => "targetProfile",
                                    "caption" => "New Profile Name",
                                    "type" => "text",
                                    "value" => (isset($targetProfile)) ? $targetProfile : "",
                                 );
    
    $input_descriptors1 = array();
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
                                 
    // open a fieldset
    $fieldset0_descriptor = array(
                                    "title" => t('title','ProfileInfo'),
                                 );
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

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
