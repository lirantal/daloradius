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

    include('../common/includes/config_read.php');
    include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    $invalid_input = array();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            // validate allow Cleartext-Password attributes
            if (
                    array_key_exists('CONFIG_DB_PASSWORD_ENCRYPTION', $_POST) &&
                    !empty(trim($_POST['CONFIG_DB_PASSWORD_ENCRYPTION'])) &&
                    in_array(strtolower(trim($_POST['CONFIG_DB_PASSWORD_ENCRYPTION'])), array("yes", "no"))
               ) {
                $configValues['CONFIG_DB_PASSWORD_ENCRYPTION'] = strtolower(trim($_POST['CONFIG_DB_PASSWORD_ENCRYPTION']));
            } else {
                $invalid_input['CONFIG_DB_PASSWORD_ENCRYPTION'] = "Allow cleartext password in db";
            }

            // validate allowed random characters
            if (
                    array_key_exists('CONFIG_USER_ALLOWEDRANDOMCHARS', $_POST) &&
                    !empty(trim($_POST['CONFIG_USER_ALLOWEDRANDOMCHARS'])) &&
                    preg_match(ALLOWED_RANDOM_CHARS_REGEX, trim($_POST['CONFIG_USER_ALLOWEDRANDOMCHARS'])) !== false
               ) {
                $configValues['CONFIG_USER_ALLOWEDRANDOMCHARS'] = trim($_POST['CONFIG_USER_ALLOWEDRANDOMCHARS']);
            } else {
                $invalid_input['CONFIG_USER_ALLOWEDRANDOMCHARS'] = t('all','RandomChars');
            }

            if (isset($_POST['CONFIG_DB_PASSWORD_MIN_LENGTH']) && intval($_POST['CONFIG_DB_PASSWORD_MIN_LENGTH']) > 0) {
                $configValues['CONFIG_DB_PASSWORD_MIN_LENGTH'] = intval($_POST['CONFIG_DB_PASSWORD_MIN_LENGTH']);

                if (isset($_POST['CONFIG_DB_PASSWORD_MAX_LENGTH']) &&
                    intval($_POST['CONFIG_DB_PASSWORD_MAX_LENGTH']) > $configValues['CONFIG_DB_PASSWORD_MIN_LENGTH']) {
                    $configValues['CONFIG_DB_PASSWORD_MAX_LENGTH'] = intval($_POST['CONFIG_DB_PASSWORD_MAX_LENGTH']);
                } else {
                    $configValues['CONFIG_DB_PASSWORD_MAX_LENGTH'] = $configValues['CONFIG_DB_PASSWORD_MIN_LENGTH'] + 4;
                }

            } else {
                $invalid_input['CONFIG_DB_PASSWORD_MAX_LENGTH'] = "Password max length";
                $invalid_input['CONFIG_DB_PASSWORD_MIN_LENGTH'] = "Password min length";
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
    $title = t('Intro','configuser.php');
    $help = t('helpPage','configuser');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    $fieldset0_descriptor = array(
                                    "title" => t('title','Settings')
                                 );

    $input_descriptors0 = array();

    $input_descriptors0[] = array(
                                    "type" => "select",
                                    "options" => array( "yes", "no" ),
                                    "caption" => "Allow cleartext password in db",
                                    "name" => 'CONFIG_DB_PASSWORD_ENCRYPTION',
                                    "selected_value" => $configValues['CONFIG_DB_PASSWORD_ENCRYPTION'],
                                 );

    $input_descriptors0[] = array(
                                        "type" => "text",
                                        "caption" => t('all','RandomChars'),
                                        "name" => 'CONFIG_USER_ALLOWEDRANDOMCHARS',
                                        "value" => $configValues['CONFIG_USER_ALLOWEDRANDOMCHARS'],
                                        "pattern" => trim(ALLOWED_RANDOM_CHARS_REGEX, "/")
                                     );

    $input_descriptors0[] = array(
                                        "name" => "CONFIG_DB_PASSWORD_MIN_LENGTH",
                                        "caption" => "Password min length",
                                        "value" => $configValues['CONFIG_DB_PASSWORD_MIN_LENGTH'],
                                        "type" => "number",
                                        "min" => "1",
                                     );

    $input_descriptors0[] = array(
                                        "name" => "CONFIG_DB_PASSWORD_MAX_LENGTH",
                                        "caption" => "Password max length",
                                        "value" => $configValues['CONFIG_DB_PASSWORD_MAX_LENGTH'],
                                        "type" => "number",
                                        "min" => "2",
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
