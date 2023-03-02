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

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    $log = "visited page: ";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            if (isset($_POST['CONFIG_DASHBOARD_DALO_SECRETKEY']) && !empty(trim($_POST['CONFIG_DASHBOARD_DALO_SECRETKEY']))) {
                $configValues['CONFIG_DASHBOARD_DALO_SECRETKEY'] = trim($_POST['CONFIG_DASHBOARD_DALO_SECRETKEY']);
            }

            if (array_key_exists('CONFIG_DASHBOARD_DALO_DEBUG', $_POST) &&  isset($_POST['CONFIG_DASHBOARD_DALO_DEBUG']) &&
                in_array($_POST['CONFIG_DASHBOARD_DALO_DEBUG'], array( "yes", "no" ))) {
                $configValues['CONFIG_DASHBOARD_DALO_DEBUG'] = $_POST['CONFIG_DASHBOARD_DALO_DEBUG'];
            }

            if (array_key_exists('CONFIG_DASHBOARD_DALO_DELAYHARD', $_POST) && isset($_POST['CONFIG_DASHBOARD_DALO_DELAYHARD']) &&
                intval($_POST['CONFIG_DASHBOARD_DALO_DELAYHARD']) > 0) {
                $configValues['CONFIG_DASHBOARD_DALO_DELAYHARD'] = intval($_POST['CONFIG_DASHBOARD_DALO_DELAYHARD']);
            }

            if (array_key_exists('CONFIG_DASHBOARD_DALO_DELAYSOFT', $_POST) && isset($_POST['CONFIG_DASHBOARD_DALO_DELAYSOFT']) &&
                intval($_POST['CONFIG_DASHBOARD_DALO_DELAYSOFT']) > 0) {
                $configValues['CONFIG_DASHBOARD_DALO_DELAYSOFT'] = intval($_POST['CONFIG_DASHBOARD_DALO_DELAYSOFT']);
            }

            include("../common/includes/config_write.php");

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }


    // print HTML prologue
    $title = t('Intro','configdashboard.php');
    $help = t('helpPage','configdashboard');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    // set navbar stuff
    $navkeys = array( 'Dashboard', 'Settings', );

    // print navbar controls
    print_tab_header($navkeys);


    $input_descriptors0 = array();
    $input_descriptors0[] = array( "name" => "CONFIG_DASHBOARD_DALO_SECRETKEY", "caption" => t('all','DashboardSecretKey'),
                                   "type" => "text", "value" => $configValues['CONFIG_DASHBOARD_DALO_SECRETKEY'] );

    $input_descriptors0[] = array( "name" => "CONFIG_DASHBOARD_DALO_DEBUG", "caption" => t('all','DashboardDebug'),
                                   "type" => "select", "selected_value" => $configValues['CONFIG_DASHBOARD_DALO_DEBUG'],
                                   "options" => array("yes", "no")
                                 );

    $input_descriptors1 = array();
    $input_descriptors1[] = array( "name" => "CONFIG_DASHBOARD_DALO_DELAYSOFT", "caption" => t('all','DashboardDelaySoft'),
                                   "type" => "number", "value" => $configValues['CONFIG_DASHBOARD_DALO_DELAYSOFT'] );

    $input_descriptors1[] = array( "name" => "CONFIG_DASHBOARD_DALO_DELAYHARD", "caption" => t('all','DashboardDelayHard'),
                                   "type" => "number", "value" => $configValues['CONFIG_DASHBOARD_DALO_DELAYHARD'] );

    $input_descriptors2 = array();
    $input_descriptors2[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );

    $input_descriptors2[] = array(
                                    "type" => "submit",
                                    "name" => "submit",
                                    "value" => t('buttons','apply')
                                  );

    $fieldset0_descriptor = array(
                                    "title" => t('title','Dashboard'),
                                 );
    $fieldset1_descriptor = array(
                                    "title" => t('title','Settings'),
                                 );

    open_form();

    // open tab wrapper
    open_tab_wrapper();

    // open tab 0
    open_tab($navkeys, 0, true);

    open_fieldset($fieldset0_descriptor);

    foreach ($input_descriptors0 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_fieldset();

    close_tab($navkeys, 0);

    // open tab 1
    open_tab($navkeys, 1);

    open_fieldset($fieldset1_descriptor);

    foreach ($input_descriptors1 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_fieldset();

    close_tab($navkeys, 1);

    // close tab wrapper
    close_tab_wrapper();

    $input_descriptors2 = array();
    $input_descriptors2[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );

    $input_descriptors2[] = array(
                                    "type" => "submit",
                                    "name" => "submit",
                                    "value" => t('buttons','apply')
                                  );

    foreach ($input_descriptors2 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_form();

    include('include/config/logging.php');

    print_footer_and_html_epilogue();

?>
