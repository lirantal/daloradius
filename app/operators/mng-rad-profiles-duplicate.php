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
    
    include_once('../common/includes/pdo_connection.php');
    require_once('library/profile_duplicate.php');
    $pdo = dalo_pdo_connect($configValues, isset($_SESSION['location_name'])
                                          ? $_SESSION['location_name'] : 'default');
    $tables = dalo_profile_duplicate_tables($configValues);
    $sourceProfile = '';
    $targetProfile = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) ||
            !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
            $logAction .= 'CSRF token error on page: ';
        } else {
            $submittedSource = isset($_POST['sourceProfile']) ? $_POST['sourceProfile'] : '';
            $submittedTarget = isset($_POST['targetProfile']) ? $_POST['targetProfile'] : '';
            $sourceProfile = is_string($submittedSource) ? trim($submittedSource) : '';
            $targetProfile = is_string($submittedTarget) ? trim($submittedTarget) : '';
            try {
                $copied = dalo_profile_duplicate($pdo, $configValues,
                                                 $submittedSource, $submittedTarget);
                $successMsg = sprintf('Profile <strong>%s</strong> has been successfully cloned into <strong>%s</strong> (%d attributes)',
                                      htmlspecialchars($sourceProfile, ENT_QUOTES, 'UTF-8'),
                                      htmlspecialchars($targetProfile, ENT_QUOTES, 'UTF-8'), $copied);
                $logAction .= 'Successfully cloned profile on page: ';
                $logDebugSQL .= "INSERT INTO configured groupcheck/reply SELECT bound profile attributes;\n";
                $sourceProfile = '';
                $targetProfile = '';
            } catch (Throwable $exception) {
                // Do not expose database errors or submitted values to logs or the page.
                $failureMsg = 'Cannot clone profile: invalid selection, name, or database operation';
                $logAction .= 'Failed cloning profile on page: ';
            }
        }
    }

    include_once('include/management/actionMessages.php');
    
    $options = dalo_profile_duplicate_list($pdo, $tables);
    
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
