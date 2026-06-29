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
 * Description:    This script is responsible for testing user connectivity to a RADIUS server.
 *
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */


    include implode(DIRECTORY_SEPARATOR, [ __DIR__, 'library', 'checklogin.php' ]);
    $operator = $_SESSION['operator_user'];
    include implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'functions.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY_EXTENSIONS'], 'maintenance_radclient.php' ]);


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

    $radclient_path = RadClient::is_radclient_present();

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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

                include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);

                if (!user_exists($dbSocket, $username)) {
                    // required
                    $failureMsg = "This user does not exist";
                } else {
                    $selected_dictionary = (isset($_POST['dictionary']) && !empty(trim($_POST['dictionary'])) &&
                                            in_array(trim($_POST['dictionary']), $valid_dictionaries, true))
                                        ? trim($_POST['dictionary']) : "";

                    $dictionaryPath = ($selected_dictionary !== "") ? "$dictionaryDir/dictionary.$selected_dictionary" : "";

                    $debug = (isset($_POST['debug']) && in_array($_POST['debug'], array("yes", "no"))) ? $_POST['debug'] : "no";
                    $timeout = (isset($_POST['timeout']) && intval($_POST['timeout']) > 0) ? intval($_POST['timeout']) : 3;
                    $retries = (isset($_POST['retries']) && intval($_POST['retries']) > 0) ? intval($_POST['retries']) : 3;
                    $count = (isset($_POST['count']) && intval($_POST['count']) > 0) ? intval($_POST['count']) : 1;
                    $requests = (isset($_POST['requests']) && intval($_POST['requests']) > 0) ? intval($_POST['requests']) : 1;

                    $simulate = (isset($_POST['simulate']) && $_POST['simulate'] === "on");

                    $password1 = (isset($_POST['password1']) && !empty(trim($_POST['password1']))) ? trim($_POST['password1']) : "";
                    $password2 = (isset($_POST['password2']) && !empty(trim($_POST['password2']))) ? trim($_POST['password2']) : "";

                    // this will be passed to RadClient::check_connectivity()
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
                        include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'config_write.php' ]);

                        // test user
                        try {
                            $result = (new RadClient($params))->check_connectivity($params);
                        } catch (RuntimeException $e) {
                            $result = array("error" => true, "output" => $e->getMessage());
                        }

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

                include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);

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

    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);

    if ($radclient_path !== false) {

        $input_descriptors0 = array();
        $input_descriptors0[] = array(
            "name" => "username",
            "caption" => t('all','Username'),
            "type" => "text",
            "value" => ((isset($username)) ? $username : ""),
            "tooltipText" => "Username to authenticate against the RADIUS server. (Required)"
        );
        $input_descriptors0[] = array(
            "name" => "password1",
            "caption" => t('all','Password'),
            "type" => "password",
            "value" => ((isset($password)) ? $password : ""),
            "tooltipText" => "Password for the user being tested. (Required)"
        );
        $input_descriptors0[] = array(
            "name" => "password2",
            "caption" => t('all','Password') . " (confirmation)",
            "type" => "password",
            "value" => ((isset($password)) ? $password : ""),
            "tooltipText" => "Re-enter the password to confirm it matches. (Required)"
        );
        $input_descriptors0[] = array(
            "name" => "radius_addr",
            "caption" => t('all','RadiusServer'),
            "type" => "text",
            "value" => ((isset($radius_addr)) ? $radius_addr : $configValues['CONFIG_MAINT_TEST_USER_RADIUSSERVER']),
            "tooltipText" => "IP address of the RADIUS server to query. Defaults to the saved value. (Optional)"
        );
        $input_descriptors0[] = array(
            "name" => "radius_port",
            "caption" => t('all','RadiusPort'),
            "type" => "number",
            "min" => 1,
            "max" => 65535,
            "value" => ((isset($radius_port)) ? $radius_port : $configValues['CONFIG_MAINT_TEST_USER_RADIUSPORT']),
            "tooltipText" => "UDP authentication port of the RADIUS server. Default 1812. (Optional)"
        );
        $input_descriptors0[] = array(
            "name" => "secret",
            "caption" => t('all','NasSecret'),
            "type" => "text",
            "value" => ((isset($secret)) ? $secret : $configValues['CONFIG_MAINT_TEST_USER_RADIUSSECRET']),
            "tooltipText" => "Shared secret between this client and the RADIUS server. (Optional)"
        );
        $input_descriptors0[] = array(
            "name" => "simulate",
            "caption" => "Simulate (only show command, don't execute)",
            "type" => "checkbox",
            "checked" => (isset($simulate) ? $simulate : false),
            "tooltipText" => "Show the radclient command that would run, without executing it. (Optional)"
        );

        $input_descriptors1 = array();
        $input_descriptors1[] = array(
            "name" => "debug", "caption" => t('all','Debug'), "type" => "select", "options" => array("yes", "no"),
            "tooltipText" => "Enable radclient verbose output (-x) for troubleshooting. (Optional)"
        );
        $input_descriptors1[] = array(
            "name" => "timeout", "caption" => t('all','Timeout'), "type" => "number", "value" => "3", "min" => "1",
            "tooltipText" => "Seconds to wait for a reply before retrying (-t). Default 3. (Optional)"
        );
        $input_descriptors1[] = array(
            "name" => "retries", "caption" => t('all','Retries'), "type" => "number", "value" => "3", "min" => "0",
            "tooltipText" => "Number of times to resend a packet on timeout (-r). (Optional)"
        );
        $input_descriptors1[] = array(
            "name" => "count", "caption" => t('all','Count'), "type" => "number", "value" => "1", "min" => "1",
            "tooltipText" => "Number of times to send each packet (-c). Default 1. (Optional)"
        );
        $input_descriptors1[] = array(
            "name" => "requests", "caption" => t('all','Requests'), "type" => "number", "value" => "3", "min" => "1",
            "tooltipText" => "Number of packets sent in parallel (-n). (Optional)"
        );

        if (count($valid_dictionaries) > 0) {

            $options = $valid_dictionaries;
            array_unshift($options, "");

            $input_descriptors1[] = array(
                                            "name" => "dictionary",
                                            "caption" => t('all','RADIUSDictionaryPath'),
                                            "type" => "select",
                                            "selected_value" => ((isset($selected_dictionary)) ? $selected_dictionary : ""),
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

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);

    print_footer_and_html_epilogue();
