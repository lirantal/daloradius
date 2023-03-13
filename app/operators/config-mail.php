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

    $param_label = array(
                            'CONFIG_MAIL_SMTPADDR' => t('all','SMTPServerAddress'),
                            'CONFIG_MAIL_SMTPPORT' => t('all','SMTPServerPort'),
                            'CONFIG_MAIL_SMTPFROM' => t('all','SMTPServerFromEmail'),
                        );

    $invalid_input = array();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            // validate email
            if (
                    array_key_exists('CONFIG_MAIL_SMTPFROM', $_POST) &&
                    !empty(trim($_POST['CONFIG_MAIL_SMTPFROM'])) &&
                    filter_var(trim($_POST['CONFIG_MAIL_SMTPFROM']), FILTER_VALIDATE_EMAIL)
               ) {
                $configValues['CONFIG_MAIL_SMTPFROM'] = trim($_POST['CONFIG_MAIL_SMTPFROM']);
            } else {
                $invalid_input['CONFIG_MAIL_SMTPFROM'] = $param_label['CONFIG_MAIL_SMTPFROM'];
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
            }

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

    $fieldset0_descriptor = array(
                                    "title" => t('title','Settings')
                                 );

    $input_descriptors0 = array();


    $input_descriptors0[] = array(
                                        "type" => "text",
                                        "caption" => t('all','SMTPServerAddress'),
                                        "name" => 'CONFIG_MAIL_SMTPADDR',
                                        "value" => (!array_key_exists('CONFIG_MAIL_SMTPADDR', $invalid_input)
                                                    ? $configValues['CONFIG_MAIL_SMTPADDR'] : "")
                                     );

    $input_descriptors0[] = array(
                                        "type" => "number",
                                        "caption" => t('all','SMTPServerPort'),
                                        "name" => 'CONFIG_MAIL_SMTPPORT',
                                        "value" => (!array_key_exists('CONFIG_MAIL_SMTPPORT', $invalid_input)
                                                    ? $configValues['CONFIG_MAIL_SMTPPORT'] : ""),
                                        "min" => 0,
                                        "max" => 65535
                                 );

    $input_descriptors0[] = array(
                                        "type" => "email",
                                        "caption" => t('all','SMTPServerFromEmail'),
                                        "name" => 'CONFIG_MAIL_SMTPFROM',
                                        "value" => (!array_key_exists('CONFIG_MAIL_SMTPFROM', $invalid_input)
                                                    ? $configValues['CONFIG_MAIL_SMTPFROM'] : ""),
                                     );

    $input_descriptors0[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );

    $input_descriptors0[] = array(
                                    'type' => 'submit',
                                    'name' => 'submit',
                                    'value' => t('buttons','apply')
                                 );

    open_form();

    // open 0-th fieldset
    open_fieldset($fieldset0_descriptor);

    foreach ($input_descriptors0 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_fieldset();

    close_form();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
