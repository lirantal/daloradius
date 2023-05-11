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

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('../common/includes/config_read.php');
    include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");
    include("include/management/functions.php");
    include("library/extensions/maintenance_radclient.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    if (!isset($configValues['CONFIG_MAINT_TEST_USER_RADIUSSERVER'])) {
        $configValues['CONFIG_MAINT_TEST_USER_RADIUSSERVER'] = "127.0.0.1";
    }

    if (!isset($configValues['CONFIG_MAINT_TEST_USER_RADIUSPORT'])) {
        $configValues['CONFIG_MAINT_TEST_USER_RADIUSPORT'] = '1812';
    }

    if (!isset($configValues['CONFIG_MAINT_TEST_USER_RADIUSSECRET'])) {
        $configValues['CONFIG_MAINT_TEST_USER_RADIUSSECRET'] = "testing123";
    }

    $radclient_path = is_radclient_present();

    if ($radclient_path !== false) {

        $dictionaryDir = '/usr/share/freeradius';
        $valid_dictionaries = array();
        if ($handle = opendir($dictionaryDir)) {
            while (false !== ($file = readdir($handle))) {
                if (substr( $file, 0, 11 ) === 'dictionary.') {
                    $valid_dictionaries[] = str_replace('dictionary.', '', $file);
                }
            }

            closedir($handle);
        }

        asort($valid_dictionaries);

        $radius_addr = (
                            isset($_REQUEST['radius_addr']) &&
                            !empty(trim($_REQUEST['radius_addr'])) &&
                            filter_var(trim($_REQUEST['radius_addr']), FILTER_VALIDATE_IP) !== false
                       ) ? trim($_REQUEST['radius_addr']) : $configValues['CONFIG_MAINT_TEST_USER_RADIUSSERVER'];

        $radius_port = (
                            isset($_REQUEST['radius_port']) &&
                            !empty(trim($_REQUEST['radius_port'])) &&
                            intval(trim($_REQUEST['radius_port'])) >= 1 &&
                            intval(trim($_REQUEST['radius_port'])) <= 65535
                       ) ? intval(trim($_REQUEST['radius_port'])) : intval($configValues['CONFIG_MAINT_TEST_USER_RADIUSPORT']);

        $secret = (isset($_REQUEST['secret']) && !empty(trim($_REQUEST['secret'])))
                ? trim($_REQUEST['secret']) : $configValues['CONFIG_MAINT_TEST_USER_RADIUSSECRET'];

        $username = (isset($_REQUEST['username']) && !empty(trim($_REQUEST['username']))) ? trim($_REQUEST['username']) : "";
        $password = (isset($_REQUEST['password']) && !empty(trim($_REQUEST['password']))) ? trim($_REQUEST['password']) : "";


        include('../common/includes/db_open.php');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

                include('../common/includes/db_open.php');

                if (!user_exists($dbSocket, $username)) {
                    // required
                    $failureMsg = "This user does not exist";
                } else {
                    $selected_dictionary = (isset($_POST['selected_dictionary']) && !empty(trim($_POST['selected_dictionary'])) &&
                                       in_array(trim($_POST['selected_dictionary']), $valid_dictionaryPaths))
                                    ? trim($_POST['selected_dictionary']) : "";

                    $dictionaryPath = (!empty($selected_dictionary)) ? "$dictionaryDir/dictionary.$selected_dictionary" : "";

                    $debug = (isset($_POST['debug']) && in_array($_POST['debug'], array("yes", "no"))) ? $_POST['debug'] : "no";
                    $timeout = (isset($_POST['timeout']) && intval($_POST['timeout']) > 0) ? intval($_POST['timeout']) : 3;
                    $retries = (isset($_POST['retries']) && intval($_POST['retries']) > 0) ? intval($_POST['retries']) : 3;
                    $count = (isset($_POST['count']) && intval($_POST['count']) > 0) ? intval($_POST['count']) : 1;
                    $requests = (isset($_POST['requests']) && intval($_POST['requests']) > 0) ? intval($_POST['requests']) : 1;

                    $simulate = (isset($_POST['simulate']) && $_POST['simulate'] === "on");

                    $password1 = (isset($_POST['password1']) && !empty(trim($_POST['password1']))) ? trim($_POST['password1']) : "";
                    $password2 = (isset($_POST['password2']) && !empty(trim($_POST['password2']))) ? trim($_POST['password2']) : "";

                    // this will be passed to user_auth function
                    $params =  array(
                                        "command" => "auth",
                                        "server" => $radius_addr,
                                        "port" => $radius_port,
                                        "username" => $username,
                                        "secret" => $secret,
                                        "count" => $count,
                                        "requests" => $requests,
                                        "retries" => $retries,
                                        "timeout" => $timeout,
                                        "debug" => ($debug == "yes"),
                                        "dictionary" => $dictionaryPath,
                                        "simulate" => $simulate,
                                    );


                    $error = false;
                    if (empty($password1)) {
                        $error = true;
                        $failureMsg = "The provided password is empty or invalid";
                    } else if (empty($password2)) {
                        $error = true;
                        $failureMsg = "The provided password (confirmation) is empty or invalid";
                    } else if ($password1 !== $password2) {
                        $error = true;
                        $failureMsg = "Password and password (confirmation) should match";
                    } else {
                        $params["password"] = $password1;
                        $params["password_type"] = "User-Password";
                    }

                    if (!$error) {
                        $failureMsg = "";
                        $successMsg = "";

                        // update configuration
                        $configValues['CONFIG_MAINT_TEST_USER_RADIUSSERVER'] = $radius_addr;
                        $configValues['CONFIG_MAINT_TEST_USER_RADIUSPORT'] = $radius_port;
                        $configValues['CONFIG_MAINT_TEST_USER_RADIUSSECRET'] = $secret;
                        include("../common/includes/config_write.php");

                        // test user
                        $result = user_auth($params);

                        $username_enc = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

                        if ($result["error"]) {
                            if (!empty($failureMsg)) {
                                $failureMsg .= str_repeat("<br>", 2);
                            }

                            $failureMsg .= sprintf("Cannot perform informative action on user [<strong>%s</strong>, reason: <strong>%s</strong>]",
                                                  $username_enc, $result["output"]);
                            $logAction .= sprintf("Cannot perform informative action on user [%s, reason: %s] on page: ",
                                                 $username, $result["output"]);
                        } else {
                            if (!empty($successMsg)) {
                                $successMsg .= str_repeat("<br>", 2);
                            }

                            $successMsg .= sprintf('Performed informative action on user <strong>%s</strong>.'
                                                 . '<pre class="font-monospace my-1">%s</pre>',
                                                  $username_enc, $result["output"]);
                            $logAction .= sprintf("Performed informative action on user [%s] on page: ",
                                                 $username, $result["output"]);
                        }
                    }
                }

                include('../common/includes/db_close.php');

            } else {
                // csrf
                $failureMsg = "CSRF token error";
                $logAction .= "$failureMsg on page: ";
            }
        }
    } else {
        $failureMsg = "Cannot perform informative action [radclient binary not found on the system]";
        $logAction .= "$failureMsg on page: ";
    }


    // print HTML prologue
    $title = t('Intro','configmainttestuser.php');
    $help = t('helpPage','configmainttestuser');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    if ($radclient_path !== false) {

        $input_descriptors0 = array();
        $input_descriptors0[] = array(
                                        "name" => "username",
                                        "caption" => t('all','Username'),
                                        "type" => "text",
                                        "value" => ((isset($username)) ? $username : ""),
                                     );

        $input_descriptors0[] = array(
                                        "name" => "password1",
                                        "caption" => t('all','Password'),
                                        "type" => "password",
                                        "value" => ((isset($password)) ? $password : ""),
                                     );

        $input_descriptors0[] = array(
                                        "name" => "password2",
                                        "caption" => t('all','Password') . " (confirmation)",
                                        "type" => "password",
                                        "value" => ((isset($password)) ? $password : ""),
                                     );

        $input_descriptors0[] = array(
                                        "name" => "radius_addr",
                                        "caption" => t('all','RadiusServer'),
                                        "type" => "text",
                                        "value" => ((isset($radius_addr)) ? $radius_addr : $configValues['CONFIG_MAINT_TEST_USER_RADIUSSERVER']),
                                     );

        $input_descriptors0[] = array(
                                        "name" => "radius_port",
                                        "caption" => t('all','RadiusPort'),
                                        "type" => "number",
                                        "min" => 1,
                                        "max" => 65535,
                                        "value" => ((isset($radius_port)) ? $radius_port : $configValues['CONFIG_MAINT_TEST_USER_RADIUSPORT']),
                                     );

        $input_descriptors0[] = array( "name" => "secret",
                                       "caption" => t('all','NasSecret'),
                                       "type" => "text",
                                       "value" => ((isset($secret)) ? $secret : $configValues['CONFIG_MAINT_TEST_USER_RADIUSSECRET']),
                                     );

        $input_descriptors0[] = array(
                                        "name" => "simulate",
                                        "caption" => "Simulate (only show command, don't execute)",
                                        "type" => "checkbox",
                                        "checked" => (isset($simulate) ? $simulate : false),
                                     );

        $input_descriptors1 = array();
        $input_descriptors1[] = array( "name" => "debug", "caption" => t('all','Debug'), "type" => "select", "options" => array("yes", "no"), );
        $input_descriptors1[] = array( "name" => "timeout", "caption" => t('all','Timeout'), "type" => "number", "value" => "3", "min" => "1", );
        $input_descriptors1[] = array( "name" => "retries", "caption" => t('all','Retries'), "type" => "number", "value" => "3", "min" => "0", );
        $input_descriptors1[] = array( "name" => "count", "caption" => t('all','Count'), "type" => "number", "value" => "1", "min" => "1", );
        $input_descriptors1[] = array( "name" => "requests", "caption" => t('all','Requests'), "type" => "number", "value" => "3", "min" => "1", );

        if (count($valid_dictionaries) > 0) {

            $options = $valid_dictionaries;
            array_unshift($options, "");

            $input_descriptors1[] = array(
                                            "name" => "dictionaryPath",
                                            "caption" => t('all','RADIUSDictionaryPath'),
                                            "type" => "select",
                                            "selected_value" => ((isset($dictionaryPath)) ? $dictionaryPath : ""),
                                            "options" => $options,
                                         );
        }
        $input_descriptors2 = array();

        $input_descriptors2[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );

        $input_descriptors2[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('all','TestUser'),
                                     );

        // set navbar stuff
        $navkeys = array( 'Settings', 'Advanced', );

        // print navbar controls
        print_tab_header($navkeys);

        // open form
        open_form();

        // open tab wrapper
        open_tab_wrapper();

        // open tab 0 (shown)
        open_tab($navkeys, 0, true);

        // open a fieldset
        $fieldset0_descriptor = array(
                                        "title" => "Test User Connectivity",
                                     );

        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        close_tab($navkeys, 0);

        // open tab 1
        open_tab($navkeys, 1);

        // open a fieldset
        $fieldset1_descriptor = array(
                                        "title" => t('title','Advanced'),
                                     );

        open_fieldset($fieldset1_descriptor);

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        close_tab($navkeys, 1);

        // close tab wrapper
        close_tab_wrapper();

        foreach ($input_descriptors2 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_form();
    }

    include('include/config/logging.php');

    print_footer_and_html_epilogue();

?>
