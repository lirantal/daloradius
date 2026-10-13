<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
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
 * Authors:    Liran Tal <liran@lirantal.com>
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

    require_once('../common/includes/pdo_connection.php');
    require_once('library/profile_delete.php');
    $pdoProfiles = dalo_pdo_connect($configValues);
    $profile_tables = dalo_profile_delete_tables($configValues);
    $valid_profiles = dalo_profile_delete_list($pdoProfiles, $profile_tables);
    $profile_name = '';
    $profile__id__table = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $csrf = $_POST['csrf_token'] ?? null;
        if (!is_string($csrf) || !dalo_check_csrf_token($csrf)) {
            $failureMsg = 'CSRF token error';
            $logAction .= "$failureMsg on page: ";
        } else {
            try {
                if (array_key_exists('profile_names', $_POST) &&
                    array_key_exists('profile__id__table', $_POST)) {
                    throw new InvalidArgumentException('Ambiguous profile selection');
                }
                if (array_key_exists('profile_names', $_POST)) {
                    $names = dalo_profile_delete_names($_POST['profile_names']);
                    $choice = $_POST['profile_delete_assoc'] ?? '';
                    if (!is_string($choice) || !in_array($choice, array('', 'no', 'yes'), true)) {
                        throw new InvalidArgumentException('Invalid profile deletion mode');
                    }
                    $mappingsOnly = $choice === 'yes';
                    $count = dalo_profile_delete_groups($pdoProfiles, $configValues, $names, $mappingsOnly);
                    $successMsg = $mappingsOnly
                        ? sprintf('Removed all user mappings for %d profile(s)', $count)
                        : sprintf('Completely removed attributes and user mappings for %d profile(s)', $count);
                    $logAction .= "$successMsg on page: ";
                } elseif (array_key_exists('profile__id__table', $_POST)) {
                    $items = dalo_profile_delete_items($_POST['profile__id__table'], $configValues);
                    $count = dalo_profile_delete_attributes($pdoProfiles, $configValues, $items);
                    $successMsg = sprintf('%d profile(s) have been deleted/modified', $count);
                    $logAction .= "$successMsg on page: ";
                } else {
                    throw new InvalidArgumentException('No profile selected');
                }
            } catch (Throwable $error) {
                // Do not disclose driver errors or submitted data in the response/logs.
                $failureMsg = 'Invalid or stale profile selection; no changes were saved';
                $logAction .= "$failureMsg on page: ";
            }
        }
    } else {
        $requested = $_GET['profile_name'] ?? '';
        $profile_name = is_string($requested) ? trim($requested) : '';
        if (!in_array($profile_name, $valid_profiles, true)) {
            $profile_name = '';
        }
        $id = $_GET['id'] ?? null;
        $table = $_GET['tablename'] ?? null;
        if ($profile_name !== '' && is_string($id) && ctype_digit($id) && (int) $id > 0 &&
            is_string($table) && in_array($table, array($configValues['CONFIG_DB_TBL_RADGROUPCHECK'],
                $configValues['CONFIG_DB_TBL_RADGROUPREPLY']), true)) {
            $profile__id__table = sprintf('%s__%d__%s', $profile_name, (int) $id, $table);
        }
    }

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
            
            foreach (array('CONFIG_DB_TBL_RADGROUPCHECK', 'CONFIG_DB_TBL_RADGROUPREPLY') as $key) {
                $table = $profile_tables[$key];
                $sql = "SELECT id,groupname,attribute FROM $table ORDER BY id";
                foreach ($pdoProfiles->query($sql)->fetchAll(PDO::FETCH_NUM) as $row) {
                    list($id, $this_profile, $attribute) = $row;
                    $table_value = $configValues[$key];
                    $value = sprintf('%s__%s__%s', $this_profile, $id, $table_value);
                    $options[$value] = sprintf('%s, %s (%s)', $this_profile, $attribute, $table_value);
                }
            }

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
