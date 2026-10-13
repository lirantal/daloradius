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


    require_once __DIR__ . '/library/user_group_pages_pdo.php';
    $success = false;
    $input = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
    $username = is_string($input['username'] ?? null) ? trim($input['username']) : '';
    $groupname = is_string($input['group'] ?? null) ? trim($input['group']) : '';
    $pdo = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_string($_POST['csrf_token'] ?? null) || !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
        } else {
            try {
                $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                list($count_involved_groups, $count_involved_users) = dalo_usergroup_delete($pdo, $configValues, $_POST);
                $success = true;
                $successMsg = sprintf('Deleted %d group mapping(s) for a total of %d user(s)', $count_involved_groups, $count_involved_users);
                $logAction = 'Deleted user-group mappings on page: ';
            } catch (Throwable $error) {
                $failureMsg = 'Unable to delete user-group mappings: invalid or stale selection or database operation failed';
                $logAction = 'User-group deletion failed on page: ';
            } finally { $pdo = null; }
        }
    } elseif ($username !== '' && $groupname !== '') {
        try {
            $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
            $table = dalo_usergroup_table($configValues, 'CONFIG_DB_TBL_RADUSERGROUP');
            if (dalo_usergroup_query($pdo, "SELECT id FROM $table WHERE username=? AND groupname=? LIMIT 1",
                                    array($username, $groupname))->fetchColumn() === false) {
                $username = $groupname = '';
            }
        } catch (Throwable $error) {
            $username = $groupname = '';
            $failureMsg = 'Unable to load user-group mapping';
        } finally { $pdo = null; }
    }

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // print HTML prologue
    
    $title = t('Intro','mngradusergroupdel.php');
    $help = t('helpPage','mngradusergroupdel');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);

    if (isset($failureMsg) || $_SERVER['REQUEST_METHOD'] != 'GET') {
        include_once('include/management/actionMessages.php');
    }

    if (!$success) {
        include_once('include/management/populate_selectbox.php');

        $input_descriptors1[] = array(
                                        "name" => "username",
                                        "caption" => t('all','Username'),
                                        "type" => "text",
                                        "value" => $username,
                                        "tooltipText" => t('Tooltip','usernameTooltip'),
                                     );

        $options = get_groups();
        $input_descriptors1[] = array(
                                        "id" => "group",
                                        "name" => "group",
                                        "caption" => t('all','Groupname'),
                                        "type" => "select",
                                        "options" => $options,
                                        "selected_value" => $groupname,
                                        "tooltipText" => t('Tooltip','groupTooltip')
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
                                        "title" => t('title','GroupInfo'),
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
