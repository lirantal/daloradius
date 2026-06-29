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

    include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);
    $operator = $_SESSION['operator_user'];

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'functions.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY_EXTENSIONS'], 'maintenance_radclient.php' ]);

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    $valid_packetTypes = array(
                                    "disconnect" => 'PoD - Packet of Disconnect',
                                    "coa" => 'CoA - Change of Authorization'
                              );

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);

    $sql = sprintf("SELECT DISTINCT(nasname), shortname, CONCAT('nas-', id) FROM %s ORDER BY nasname ASC",
                   $configValues['CONFIG_DB_TBL_RADNAS']);
    $res = $dbSocket->query($sql);

    $valid_nas_ids = array();
    while ($row = $res->fetchRow()) {
        list($nasname, $shortname, $nas_id) = $row;
        $shortname = trim($shortname);
        $valid_nas_ids[$nas_id] = ($shortname !== "") ? sprintf("%s (%s)", $shortname, $nasname) : $nasname;
    }

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);

    $radclient_path = RadClient::is_radclient_present();

    if ($radclient_path !== false) {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
                $required_fields = array();

                $username = (isset($_POST['username']) && !empty(trim($_POST['username']))) ? trim($_POST['username']) : "";
                if (empty($username)) {
                    $required_fields['username'] = t('all','Username');
                }

                $nas_id = (isset($_POST['nas_id']) && in_array(trim($_POST['nas_id']), array_keys($valid_nas_ids)))
                        ? trim($_POST['nas_id']) : "";
                if (empty($nas_id)) {
                    $required_fields['nas_id'] = t('all','NasIPHost');
                }

                if (count($required_fields) > 0) {
                    // required/invalid
                    $failureMsg = sprintf("Empty or invalid required field(s) [%s]", implode(", ", array_values($required_fields)));
                    $logAction .= "$failureMsg on page: ";
                } else {

                    $packetType = (isset($_POST['packetType']) && array_key_exists(trim($_POST['packetType']), $valid_packetTypes))
                                ? trim($_POST['packetType']) : array_key_first($valid_packetTypes);
                    $customAttributes = (array_key_exists('customAttributes', $_POST) && !empty(trim($_POST['customAttributes'])))
                                      ? trim($_POST['customAttributes']) : "";

                    $port = (array_key_exists('port', $_POST) && !empty(trim($_POST['port'])) &&
                             intval(trim($_POST['port'])) >= 1 && intval(trim($_POST['port'])) <= 65535)
                          ? intval(trim($_POST['port'])) : RadClient::DEFAULT_COA_PORT;
                    $debug = (isset($_POST['debug']) && in_array($_POST['debug'], array("yes", "no"))) ? $_POST['debug'] : "no";
                    $timeout = (isset($_POST['timeout']) && intval($_POST['timeout']) > 0) ? intval($_POST['timeout']) : 3;
                    $retries = (isset($_POST['retries']) && intval($_POST['retries']) > 0) ? intval($_POST['retries']) : 3;
                    $count = (isset($_POST['count']) && intval($_POST['count']) > 0) ? intval($_POST['count']) : 1;
                    $requests = (isset($_POST['requests']) && intval($_POST['requests']) > 0) ? intval($_POST['requests']) : 1;

                    $simulate = (isset($_POST['simulate']) && $_POST['simulate'] === "on");

                    // this will be passed to RadClient::disconnect()
                    $params = array(
                        "nas_id"           => intval(str_replace("nas-", "", $nas_id)),
                        "username"         => $username,
                        "count"            => $count,
                        "requests"         => $requests,
                        "retries"          => $retries,
                        "timeout"          => $timeout,
                        "debug"            => ($debug == "yes"),
                        "command"          => $packetType,
                        "customAttributes" => $customAttributes,
                        "simulate"         => $simulate,
                        "port"             => $port,   // CoA/PoD port, default 3799
                    );

                    // disconnect user
                    try {
                        $result = (new RadClient($params))->disconnect($params);
                    } catch (RuntimeException $e) {
                        $result = array("error" => true, "output" => $e->getMessage());
                    }

                    $username_enc = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

                    if ($result["error"]) {
                        if (!empty($failureMsg)) {
                            $failureMsg .= str_repeat("<br>", 2);
                        }

                        $failureMsg = sprintf("Cannot perform disconnect action on user [<strong>%s</strong>, reason: <strong>%s</strong>]",
                                              $username_enc, $result["output"]);
                        $logAction .= sprintf("Cannot perform disconnect action on user [%s, reason: %s] on page: ",
                                              $username, $result["output"]);
                    } else {
                        if (!empty($successMsg)) {
                            $successMsg .= str_repeat("<br>", 2);
                        }

                        $successMsg = sprintf('Performed disconnect action on user <strong>%s</strong>.'
                                            . '<pre class="font-monospace my-1">%s</pre>',
                                              $username_enc, $result["output"]);
                        $logAction .= sprintf("Performed disconnect action on user [%s] on page: ",
                                              $username, $result["output"]);
                    }
                }

            } else {
                // csrf
                $failureMsg = "CSRF token error";
                $logAction .= "$failureMsg on page: ";
            }
        }

    } else {
        $failureMsg = "Cannot perform disconnect action [radclient binary not found on the system]";
        $logAction .= "$failureMsg on page: ";
    }


    // print HTML prologue
    $title = t('Intro','configmaintdisconnectuser.php');
    $help = t('helpPage','configmaintdisconnectuser');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);

    if ($radclient_path !== false) {
        include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'populate_selectbox.php' ]);

        $options = get_online_users();
        array_unshift($options, "");

        $input_descriptors0 = array();
        $input_descriptors0[] = array(
                                        "name" => "username",
                                        "caption" => t('all','Username'),
                                        "type" => "select",
                                        "options" => $options,
                                        "selected_value" => ((isset($username)) ? $username : ""),
                                     );

        $options = $valid_packetTypes;
        array_unshift($options, "");

        $input_descriptors0[] = array(
                                        "name" => "packetType",
                                        "caption" => t('all','PacketType'),
                                        "type" => "select",
                                        "options" => $options,
                                        "selected_value" => ((isset($packetType)) ? $packetType : ""),
                                     );

        $options = $valid_nas_ids;
        array_unshift($options, "");

        $input_descriptors0[] = array(
                                        "name" => "nas_id",
                                        "caption" => t('all','NasIPHost'),
                                        "type" => "select",
                                        "options" => $options,
                                        "selected_value" => ((isset($nas_id)) ? $nas_id : ""),
                                     );

        $input_descriptors0[] = array( "name" => "customAttributes", "caption" => t('all','customAttributes'),
                                       "type" => "textarea", "content" => ((isset($customAttributes)) ? $customAttributes : ""),
                                     );

        $input_descriptors0[] = array(
                                        "name" => "simulate",
                                        "caption" => "Simulate (only show command, don't execute)",
                                        "type" => "checkbox",
                                        "checked" => (isset($simulate) ? $simulate : false),
                                     );

        // descriptors 1
        $input_descriptors1 = array();
        $input_descriptors1[] = array( "name" => "debug", "caption" => t('all','Debug'), "type" => "select", "options" => array("yes", "no"), );
        $input_descriptors1[] = array( "name" => "port", "caption" => "CoA/PoD Port", "type" => "number", "min" => "1",
                                       "max" => "65535", "value" => ((isset($port)) ? $port : RadClient::DEFAULT_COA_PORT),
                                       "tooltipText" => "UDP port the NAS listens on for CoA/PoD packets. Default 3799 (some legacy NAS use 1700). (Optional)"
                                     );
        $input_descriptors1[] = array( "name" => "timeout", "caption" => t('all','Timeout'), "type" => "number", "value" => "3", "min" => "1", );
        $input_descriptors1[] = array( "name" => "retries", "caption" => t('all','Retries'), "type" => "number", "value" => "3", "min" => "0", );
        $input_descriptors1[] = array( "name" => "count", "caption" => t('all','Count'), "type" => "number", "value" => "1", "min" => "1", );
        $input_descriptors1[] = array( "name" => "requests", "caption" => t('all','Requests'), "type" => "number", "value" => "3", "min" => "1", );

        // descriptors 2
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
                                        "title" => "Disconnect User",
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
