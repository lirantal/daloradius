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
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    $db_tbl_param_label = array(
                                    'CONFIG_DB_TBL_RADCHECK' => t('all','radcheck'),
                                    'CONFIG_DB_TBL_RADREPLY' => t('all','radreply'),
                                    'CONFIG_DB_TBL_RADGROUPREPLY' => t('all','radgroupreply'),
                                    'CONFIG_DB_TBL_RADGROUPCHECK' => t('all','radgroupcheck'),
                                    'CONFIG_DB_TBL_RADUSERGROUP' => t('all','usergroup'),
                                    'CONFIG_DB_TBL_RADACCT' => t('all','radacct'),
                                    'CONFIG_DB_TBL_RADNAS' => t('all','nas'),
                                    'CONFIG_DB_TBL_RADHG' => t('all','hunt'),
                                    'CONFIG_DB_TBL_RADPOSTAUTH' => t('all','radpostauth'),
                                    'CONFIG_DB_TBL_RADIPPOOL' => t('all','radippool'),
                                    'CONFIG_DB_TBL_DALOUSERINFO' => t('all','userinfo'),
                                    'CONFIG_DB_TBL_DALODICTIONARY' => t('all','dictionary'),
                                    'CONFIG_DB_TBL_DALOREALMS' => t('all','realms'),
                                    'CONFIG_DB_TBL_DALOPROXYS' => t('all','proxys'),
                                    'CONFIG_DB_TBL_DALOBILLINGMERCHANT' => t('all','billingmerchant'),
                                    'CONFIG_DB_TBL_DALOBILLINGPAYPAL' => t('all','billingpaypal'),
                                    'CONFIG_DB_TBL_DALOBILLINGPLANS' => t('all','billingplans'),
                                    'CONFIG_DB_TBL_DALOBILLINGRATES' => t('all','billingrates'),
                                    'CONFIG_DB_TBL_DALOBILLINGHISTORY' => t('all','billinghistory'),
                                    'CONFIG_DB_TBL_DALOBATCHHISTORY' => t('button', 'BatchHistory'),
                                    'CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES' => 'Billing Plans Profiles',
                                    'CONFIG_DB_TBL_DALOUSERBILLINFO' => t('all','billinginfo'),
                                    'CONFIG_DB_TBL_DALOBILLINGINVOICE' => t('all','Invoice'),
                                    'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS' => t('all','InvoiceItems'),
                                    'CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS' => t('all','InvoiceStatus'),
                                    'CONFIG_DB_TBL_DALOBILLINGINVOICETYPE' => t('all','InvoiceType'),
                                    'CONFIG_DB_TBL_DALOPAYMENTTYPES' => t('all','payment_type'),
                                    'CONFIG_DB_TBL_DALOPAYMENTS' => t('all','payments'),
                                    'CONFIG_DB_TBL_DALOOPERATORS' => t('all','operators'),
                                    'CONFIG_DB_TBL_DALOOPERATORS_ACL' => t('all','operators_acl'),
                                    'CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES' => t('all','operators_acl_files'),
                                    'CONFIG_DB_TBL_DALOHOTSPOTS' => t('all','hotspots'),
                                    'CONFIG_DB_TBL_DALONODE' => t('all','node'),
                                );

    $generic_db_conf_params = array(
                                        'CONFIG_DB_HOST' => t('all','DatabaseHostname'),
                                        'CONFIG_DB_USER' => t('all','DatabaseUser'),
                                        'CONFIG_DB_PASS' => t('all','DatabasePass'),
                                        'CONFIG_DB_NAME' => t('all','DatabaseName'),
                                   );

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            // if the form has been submitted we validate and store the configuration
            if (array_key_exists('CONFIG_DB_ENGINE', $_POST) && isset($_POST['CONFIG_DB_ENGINE']) &&
                in_array(strtolower($_POST['CONFIG_DB_ENGINE']), array_keys($valid_db_engines))) {
                $configValues['CONFIG_DB_ENGINE'] = $_POST['CONFIG_DB_ENGINE'];
            }

            if (array_key_exists('CONFIG_DB_PORT', $_POST) && isset($_POST['CONFIG_DB_PORT']) &&
                intval($_POST['CONFIG_DB_PORT']) >= 0 && intval($_POST['CONFIG_DB_PORT']) <= 65535) {
                $configValues['CONFIG_DB_PORT'] = intval($_POST['CONFIG_DB_PORT']);
            }

            foreach ($generic_db_conf_params as $param => $caption) {
                if (array_key_exists($param, $_POST) && isset($_POST[$param])) {
                    $configValues[$param] = $_POST[$param];
                }
            }

            // validate table name
            foreach ($db_tbl_param_label as $param => $label) {
                if (array_key_exists($param, $_POST) && isset($_POST[$param]) && preg_match(DB_TABLE_NAME_REGEX, $_POST[$param]) !== false) {
                    $configValues[$param] = $_POST[$param];
                }
            }

            include("../common/includes/config_write.php");

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }


    // print HTML prologue
    $title = t('Intro','configmain.php');
    $help = t('helpPage','configmain');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    // set navbar stuff
    $navkeys = array( 'Settings', 'DatabaseTables' );

    // print navbar controls
    print_tab_header($navkeys);

    // open form
    open_form();

    // open tab wrapper
    open_tab_wrapper();

    // tab 0
    open_tab($navkeys, 0, true);

    $input_descriptors0 = array();

    $input_descriptors0[] = array(
                                        "name" => "CONFIG_DB_ENGINE",
                                        "caption" => t('all','DBEngine'),
                                        "selected_value" => $configValues['CONFIG_DB_ENGINE'],
                                        "type" => "select",
                                        "options" => $valid_db_engines,
                                     );

    $input_descriptors0[] = array(
                                        "name" => "CONFIG_DB_PORT",
                                        "caption" => t('all','DatabasePort'),
                                        "value" => $configValues['CONFIG_DB_PORT'],
                                        "type" => "number",
                                        "min" => "0",
                                        "max" => "65535",
                                     );

    foreach ($generic_db_conf_params as $name => $caption) {
        $input_descriptors0[] = array(
                                        "name" => $name,
                                        "caption" => $caption,
                                        "value" => $configValues[$name],
                                        "type" => "text"
                                     );
    }

    $fieldset0_descriptor = array(
                                    "title" => t('title','Settings'),
                                 );

    open_fieldset($fieldset0_descriptor);

    foreach ($input_descriptors0 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_fieldset();

    close_tab();

    // tab 1
    open_tab($navkeys, 1);

    $input_descriptors1 = array();

    foreach ($db_tbl_param_label as $name => $caption) {
        $input_descriptors1[] = array(
                                        "name" => $name,
                                        "caption" => $caption,
                                        "value" => $configValues[$name],
                                        "pattern" => trim(DB_TABLE_NAME_REGEX, "/"),
                                        "type" => "text"
                                     );
    }

    $fieldset1_descriptor = array(
                                    "title" => t('title','DatabaseTables'),
                                 );

    open_fieldset($fieldset1_descriptor);

    foreach ($input_descriptors1 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_fieldset();

    close_tab();

    // close tab wrapper
    close_tab_wrapper();

    // other fields
    $input_descriptors2 = array();
    $input_descriptors2[] = array(
                                    "type" => "submit",
                                    "name" => "submit",
                                    "value" => t('buttons','apply')
                                 );

    $input_descriptors2[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );

    foreach ($input_descriptors2 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_form();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
