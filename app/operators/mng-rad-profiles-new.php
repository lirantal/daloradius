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

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");

    require_once __DIR__ . '/library/group_profiles_pdo.php';
    $profile = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_string($_POST['csrf_token'] ?? null) || !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
        } else {
            $pdo = null;
            try {
                $profile = is_string($_POST['profile'] ?? null) ? trim($_POST['profile']) : '';
                $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                dalo_profile_write($pdo, $configValues, $profile, $_POST, $valid_ops, true);
                $profile_enc = htmlspecialchars($profile, ENT_QUOTES, 'UTF-8');
                $successMsg = sprintf('Successfully added a new profile (<strong>%s</strong>)', $profile_enc)
                    . sprintf(' [<a href="mng-rad-profiles-edit.php?profile_name=%s" title="Edit">Edit</a>]', urlencode($profile));
                $logAction = 'Successfully added profile on page: ';
            } catch (Throwable $error) {
                $failureMsg = 'Unable to create profile: invalid input, existing name or database operation failed';
                $logAction = 'Profile creation failed on page: ';
            } finally { $pdo = null; }
        }
    }

    // print HTML prologue
    $extra_css = array();

    $extra_js = array(
        "static/js/request.js",
        "static/js/dynamic_attributes.js",
        "static/js/productive_funcs.js",
    );

    $title = t('Intro','mngradprofilesnew.php');
    $help = t('helpPage','mngradprofilesnew');

    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    if (!isset($successMsg)) {

        // set form component descriptors
        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        "name" => "profile",
                                        "caption" => "Profile Name",
                                        "type" => "text",
                                        "value" => ((isset($profile)) ? $profile : "")
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

        $fieldset0_descriptor = array( "title" => t('title','ProfileInfo') );

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
