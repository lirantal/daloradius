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

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    //~ include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');
    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    $param_label = array(
                            'CONFIG_MAIL_SMTPADDR' => t('all','SMTPServerAddress'),
                            'CONFIG_MAIL_SMTPPORT' => t('all','SMTPServerPort'),
                            'CONFIG_MAIL_SMTPFROM' => t('all','SMTPServerFromEmail'),
                        );

    $invalid_input = array();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            // validate email enabled
            if (
                    array_key_exists('CONFIG_MAIL_ENABLED', $_POST) &&
                    !empty(trim($_POST['CONFIG_MAIL_ENABLED'])) &&
                    in_array(strtolower(trim($_POST['CONFIG_MAIL_ENABLED'])), array("yes", "no"))
               ) {
               $configValues['CONFIG_MAIL_ENABLED'] = strtolower(trim($_POST['CONFIG_MAIL_ENABLED']));
            } else {
                $configValues['CONFIG_MAIL_ENABLED'] = "no";
            }

            // validate email security
            if (
                    array_key_exists('CONFIG_MAIL_SMTP_SECURITY', $_POST) &&
                    !empty(trim($_POST['CONFIG_MAIL_SMTP_SECURITY'])) &&
                    in_array(strtolower(trim($_POST['CONFIG_MAIL_SMTP_SECURITY'])), array("ssl", "tls", "none"))
               ) {
               $configValues['CONFIG_MAIL_SMTP_SECURITY'] = strtolower(trim($_POST['CONFIG_MAIL_SMTP_SECURITY']));
            } else {
                $configValues['CONFIG_MAIL_SMTP_SECURITY'] = "none";
            }

            // validate sender email
            if (
                    array_key_exists('CONFIG_MAIL_SMTPFROM', $_POST) &&
                    !empty(trim($_POST['CONFIG_MAIL_SMTPFROM'])) &&
                    filter_var(trim($_POST['CONFIG_MAIL_SMTPFROM']), FILTER_VALIDATE_EMAIL)
               ) {
                $configValues['CONFIG_MAIL_SMTPFROM'] = trim($_POST['CONFIG_MAIL_SMTPFROM']);
            } else {
                $invalid_input['CONFIG_MAIL_SMTPFROM'] = $param_label['CONFIG_MAIL_SMTPFROM'];
                $configValues['CONFIG_MAIL_ENABLED'] = "no";
            }

            // validate sender name
            if (
                    array_key_exists('CONFIG_MAIL_SMTP_SENDER_NAME', $_POST) &&
                    !empty(trim($_POST['CONFIG_MAIL_SMTP_SENDER_NAME'])) &&
                    preg_match(SENDER_NAME_REGEX, trim($_POST['CONFIG_MAIL_SMTP_SENDER_NAME']))
               ) {
                $configValues['CONFIG_MAIL_SMTP_SENDER_NAME'] = trim($_POST['CONFIG_MAIL_SMTP_SENDER_NAME']);
            } else {
                $configValues['CONFIG_MAIL_SMTP_SENDER_NAME'] = "";
            }

            // validate subject prefix
            if (
                    array_key_exists('CONFIG_MAIL_SMTP_SUBJECT_PREFIX', $_POST) &&
                    !empty(trim($_POST['CONFIG_MAIL_SMTP_SUBJECT_PREFIX'])) &&
                    preg_match(SUBJECT_PREFIX_REGEX, trim($_POST['CONFIG_MAIL_SMTP_SUBJECT_PREFIX']))
               ) {
                $configValues['CONFIG_MAIL_SMTP_SUBJECT_PREFIX'] = trim($_POST['CONFIG_MAIL_SMTP_SUBJECT_PREFIX']);
            } else {
                $configValues['CONFIG_MAIL_SMTP_SUBJECT_PREFIX'] = "";
            }

            // validate port
            if (
                    array_key_exists('CONFIG_MAIL_SMTPPORT', $_POST) &&
                    !empty(trim($_POST['CONFIG_MAIL_SMTPPORT'])) &&
                    intval(trim($_POST['CONFIG_MAIL_SMTPPORT'])) >= 0 &&
                    intval(trim($_POST['CONFIG_MAIL_SMTPPORT'])) <= 65535
               ) {
                $configValues['CONFIG_MAIL_SMTPPORT'] = intval(trim($_POST['CONFIG_MAIL_SMTPPORT']));
            } else {
                $invalid_input['CONFIG_MAIL_SMTPPORT'] = $param_label['CONFIG_MAIL_SMTPPORT'];
                $configValues['CONFIG_MAIL_ENABLED'] = "no";
            }

            // validate ip address/hostname
            if (
                    array_key_exists('CONFIG_MAIL_SMTPADDR', $_POST) &&
                    !empty(trim($_POST['CONFIG_MAIL_SMTPADDR'])) &&
                    (
                        preg_match(HOSTNAME_REGEX, trim($_POST['CONFIG_MAIL_SMTPADDR'])) ||
                        preg_match(IP_REGEX, trim($_POST['CONFIG_MAIL_SMTPADDR']))
                    )
               ) {
                $configValues['CONFIG_MAIL_SMTPADDR'] = trim($_POST['CONFIG_MAIL_SMTPADDR']);
            } else {
                $invalid_input['CONFIG_MAIL_SMTPADDR'] = $param_label['CONFIG_MAIL_SMTPADDR'];
                $configValues['CONFIG_MAIL_ENABLED'] = "no";
            }

            // validate SMTP username
            if (
                array_key_exists('CONFIG_MAIL_SMTP_USERNAME', $_POST) &&
                !empty(trim($_POST['CONFIG_MAIL_SMTP_USERNAME']))
            ) {
                $configValues['CONFIG_MAIL_SMTP_USERNAME'] = trim($_POST['CONFIG_MAIL_SMTP_USERNAME']);
            } else {
                $configValues['CONFIG_MAIL_SMTP_USERNAME'] = "";
            }

            // validate SMTP password
            if (
                array_key_exists('CONFIG_MAIL_SMTP_PASSWORD', $_POST)
            ) {
                $configValues['CONFIG_MAIL_SMTP_PASSWORD'] = trim($_POST['CONFIG_MAIL_SMTP_PASSWORD']);
            } else {
                $configValues['CONFIG_MAIL_SMTP_PASSWORD'] = "";
            }

            // display message
            if (count($invalid_input) > 0) {
                $failureMsg = sprintf("Invalid input: [%s]", implode(", ", array_values($invalid_input)));
                $logAction .= "$failureMsg on page: ";
            } else {
                include("../common/includes/config_write.php");
            }

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    // print HTML prologue
    $title = t('Intro','configmail.php');
    $help = t('helpPage','configmail');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    // set navbar stuff
    $navkeys = array( array("smtp-server-settings", 'SMTP Server Settings'), array("mail-settings", 'Mail Settings'), );

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
        "type" => "select",
        "options" => array("yes", "no"),
        "caption" => "Enabled",
        "name" => 'CONFIG_MAIL_ENABLED',
        "selected_value" => (!array_key_exists('CONFIG_MAIL_ENABLED', $invalid_input)
            ? $configValues['CONFIG_MAIL_ENABLED'] : "no"),
        "tooltipText" => "Whether email should be sent or not",
    );

    $input_descriptors0[] = array(
        "type" => "text",
        "caption" => t('all', 'SMTPServerAddress'),
        "name" => 'CONFIG_MAIL_SMTPADDR',
        "value" => (!array_key_exists('CONFIG_MAIL_SMTPADDR', $invalid_input)
            ? $configValues['CONFIG_MAIL_SMTPADDR'] : ""),
        "tooltipText" => "The address of your SMTP server",
    );

    $input_descriptors0[] = array(
        "type" => "number",
        "caption" => t('all', 'SMTPServerPort'),
        "name" => 'CONFIG_MAIL_SMTPPORT',
        "value" => (!array_key_exists('CONFIG_MAIL_SMTPPORT', $invalid_input)
            ? $configValues['CONFIG_MAIL_SMTPPORT'] : ""),
        "min" => 0,
        "max" => 65535,
        "tooltipText" => "The port number used by the SMTP server",
    );

    $input_descriptors0[] = array(
        "type" => "select",
        "options" => array("ssl", "tls", "none"),
        "caption" => "SMTP Security",
        "name" => 'CONFIG_MAIL_SMTP_SECURITY',
        "selected_value" => (!array_key_exists('CONFIG_MAIL_SMTP_SECURITY', $invalid_input)
            ? $configValues['CONFIG_MAIL_SMTP_SECURITY'] : "none"),
        "tooltipText" => "Select the security protocol for the SMTP connection",
    );

    $input_descriptors0[] = array(
        "type" => "text",
        "caption" => "SMTP Username",
        "name" => 'CONFIG_MAIL_SMTP_USERNAME',
        "value" => (!array_key_exists('CONFIG_MAIL_SMTP_USERNAME', $invalid_input)
            ? $configValues['CONFIG_MAIL_SMTP_USERNAME'] : ""),
        "tooltipText" => "The username for SMTP authentication. Leave blank to skip authentication.",
    );

    $input_descriptors0[] = array(
        "type" => "password",
        "caption" => "SMTP Password",
        "name" => 'CONFIG_MAIL_SMTP_PASSWORD',
        "value" => (!array_key_exists('CONFIG_MAIL_SMTP_PASSWORD', $invalid_input)
            ? $configValues['CONFIG_MAIL_SMTP_PASSWORD'] : ""),
        "tooltipText" => "The password for SMTP authentication. Leave blank to skip authentication.",
    );

    $fieldset0_descriptor = array(
                                    "title" => t('title','Settings')
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

    $input_descriptors1[] = array(
        "type" => "email",
        "caption" => t('all', 'SMTPServerFromEmail'),
        "name" => 'CONFIG_MAIL_SMTPFROM',
        "value" => (!array_key_exists('CONFIG_MAIL_SMTPFROM', $invalid_input)
            ? $configValues['CONFIG_MAIL_SMTPFROM'] : ""),
        "tooltipText" => "The email address that will be used as the sender of the emails",
    );

    $input_descriptors1[] = array(
        "type" => "text",
        "caption" => "Sender's name",
        "name" => 'CONFIG_MAIL_SMTP_SENDER_NAME',
        "value" => (!array_key_exists('CONFIG_MAIL_SMTP_SENDER_NAME', $invalid_input)
            ? $configValues['CONFIG_MAIL_SMTP_SENDER_NAME'] : "daloRADIUS message"),
        "pattern" => trim(SENDER_NAME_REGEX, "/"),
        "title" => "allowed letters, numbers, and spaces",
        "tooltipText" => "The name associated with the sender's email address",
    );

    $input_descriptors1[] = array(
        "type" => "text",
        "caption" => "Subject prefix",
        "name" => 'CONFIG_MAIL_SMTP_SUBJECT_PREFIX',
        "value" => (!array_key_exists('CONFIG_MAIL_SMTP_SUBJECT_PREFIX', $invalid_input)
            ? $configValues['CONFIG_MAIL_SMTP_SUBJECT_PREFIX'] : ""),
        "pattern" => trim(SUBJECT_PREFIX_REGEX, "/"),
        "title" => "allowed letters, numbers, spaces, and square brackets",
        "tooltipText" => "A prefix for the email subjects",
    );


    $fieldset1_descriptor = array(
                                    "title" => "Other settings",
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
