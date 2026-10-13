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
    $operator_id = $_SESSION['operator_id'];

    include('library/check_operator_perm.php');

    // init logging variables
    $logAction = "";
    $logDebugSQL = "";
    $log = "visited page: ";

    require_once('../common/includes/pdo_connection.php');
    require_once('library/operator_delete.php');

    $success = false;
    $options = array();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) ||
            !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
            $logAction .= 'CSRF token error on page: ';
        } else {
            try {
                $selected = dalo_operator_delete_selection($_POST['operator_username'] ?? null);
                $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                $deleted = dalo_operator_delete($pdo, $configValues, $selected);
                $escaped = array_map(function ($name) {
                    return htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                }, $deleted);
                $successMsg = sprintf('Deleted operator(s): <strong>%s</strong>', implode(', ', $escaped));
                $logAction .= 'Successfully deleted operator account(s) on page: ';
                $logDebugSQL .= 'Deleted operator ACLs and accounts using PDO;';
                $success = true;
            } catch (InvalidArgumentException $error) {
                $failureMsg = $error->getMessage();
                $logAction .= 'Rejected invalid operator selection on page: ';
            } catch (DomainException $error) {
                $failureMsg = $error->getMessage();
                $logAction .= 'Rejected stale operator selection on page: ';
            } catch (Throwable $error) {
                $failureMsg = 'Failed to delete operator(s); no changes saved';
                $logAction .= 'Failed deleting operator account(s) on page: ';
            }
        }
    }
    if (!$success) {
        try {
            if (!isset($pdo)) {
                $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
            }
            $options = dalo_operator_delete_options($pdo, $configValues);
        } catch (Throwable $error) {
            $failureMsg = 'Unable to load operator list';
            $logAction .= 'Failed loading operator list on page: ';
        }
    }

    include_once('../common/includes/config_read.php');
    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // print HTML prologue
    
    $title = t('Intro','configoperatorsdel.php');
    $help = t('helpPage','configoperatorsdel');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);
    
    if ($_SERVER['REQUEST_METHOD'] != 'GET' || isset($failureMsg)) {
        include_once('include/management/actionMessages.php');
    }

    if (!$success) {
        $input_descriptors1 = array();
        
        $input_descriptors1[0] = array(
                                        'name' => 'operator_username[]',
                                        'id' => 'operator_username',
                                        'type' => 'text',
                                        'caption' => 'Operator Username',
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
                                        "title" => "Operator Account Removal",
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
