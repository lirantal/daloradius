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

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            $nasname = (array_key_exists('nasname', $_POST) && !empty(str_replace("%", "", trim($_POST['nasname']))))
                     ? str_replace("%", "", trim($_POST['nasname'])) : "";
            $secret = (array_key_exists('secret', $_POST) && !empty(str_replace("%", "", trim($_POST['secret']))))
                       ? str_replace("%", "", trim($_POST['secret'])) : "";

            $nasname_enc = (!empty($nasname)) ? htmlspecialchars($nasname, ENT_QUOTES, 'UTF-8') : "";

            $nastype = (array_key_exists('nastype', $_POST) && isset($_POST['nastype']) &&
                        in_array($_POST['nastype'], $valid_nastypes)) ? $_POST['nastype'] : "other";

            $shortname = (array_key_exists('shortname', $_POST) && !empty(str_replace("%", "", trim($_POST['shortname']))))
                       ? str_replace("%", "", trim($_POST['shortname'])) : "";

            $ports = (array_key_exists('ports', $_POST) && !empty(trim($_POST['ports'])) &&
                         intval(trim($_POST['ports'])) >= 0 && intval(trim($_POST['ports'])) <= 99999)
                      ? intval(trim($_POST['ports'])) : 0;

            $description = (array_key_exists('description', $_POST) && !empty(str_replace("%", "", trim($_POST['description']))))
                            ? str_replace("%", "", trim($_POST['description'])) : "";
            $community = (array_key_exists('community', $_POST) && !empty(str_replace("%", "", trim($_POST['community']))))
                          ? str_replace("%", "", trim($_POST['community'])) : "";
            $server = (array_key_exists('server', $_POST) && !empty(str_replace("%", "", trim($_POST['server']))))
                              ? str_replace("%", "", trim($_POST['server'])) : "";

            if (empty($nasname) || empty($secret)) {
                // required
                $failureMsg = sprintf("%s and/or %s are empty or invalid", t('all','NasIPHost'), t('all','NasSecret'));
                $logAction .= "Failed adding (possible empty user/pass) new operator on page: ";
            } else {
                include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);

                $sql = sprintf("SELECT COUNT(id) FROM %s WHERE nasname='%s'", $configValues['CONFIG_DB_TBL_RADNAS'],
                                                                              $dbSocket->escapeSimple($nasname));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";

                $exists = $res->fetchrow()[0] > 0;

                if ($exists) {
                    // name already taken
                    $failureMsg = sprintf("This %s already exists: <b>%s</b>", t('all','NasIPHost'), $nasname_enc);
                    $logAction .= "Failed adding a new NAS [$nasname already exists] on page: ";
                } else {

                    $sql = sprintf("INSERT INTO %s (nasname, shortname, type, ports, secret, server, community, description)
                                            VALUES ('%s', '%s', '%s', %d, '%s', '%s', '%s', '%s')", $configValues['CONFIG_DB_TBL_RADNAS'],
                                   $dbSocket->escapeSimple($nasname), $dbSocket->escapeSimple($shortname), $dbSocket->escapeSimple($nastype),
                                   $dbSocket->escapeSimple($ports), $dbSocket->escapeSimple($secret), $dbSocket->escapeSimple($server),
                                   $dbSocket->escapeSimple($community), $dbSocket->escapeSimple($description));
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";

                    if (!DB::isError($res)) {
                        $successMsg = sprintf('Successfully added a new NAS (<strong>%s</strong>) '
                                            . '<a href="mng-rad-nas-edit.php?nasname=%s" title="Edit">Edit</a>',
                                              $nasname_enc, urlencode($nasname_enc));
                        $successMsg .= '<br><strong>Restart FreeRADIUS for NAS changes to take effect.</strong>';
                        $logAction .= "Successfully added a new NAS [$nasname] on page: ";
                    } else {
                        // it seems that operator could not be added
                        $f = "Failed to add a new NAS [%s] to database";
                        $failureMsg = sprintf($f, $nasname_enc);
                        $logAction .= sprintf($f, $nasname);
                    }
                }

                include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
            }
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }


    // print HTML prologue
    $title = t('Intro','mngradnasnew.php');
    $help = t('helpPage','mngradnasnew');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);

    if (!isset($successMsg)) {

        // set form component descriptors
        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        "name" => "nasname",
                                        "caption" => t('all','NasIPHost'),
                                        "type" => "text",
                                        "value" => ((isset($nasname)) ? $nasname : ""),
                                        "tooltipText" => "IP address or hostname of the NAS device. (Required)"
                                     );

        $input_descriptors0[] = array(
                                        "name" => "secret",
                                        "caption" => t('all','NasSecret'),
                                        "type" => "text",
                                        "value" => ((isset($secret)) ? $secret : ""),
                                        "tooltipText" => "Shared secret used to authenticate RADIUS traffic with the NAS. (Required)"
                                     );

        $input_descriptors0[] = array(
                                        "name" => "nastype",
                                        "caption" => t('all','NasType'),
                                        "type" => "select",
                                        "options" => $valid_nastypes,
                                        "selected_value" => ((isset($nastype)) ? $nastype : "other"),
                                        "tooltipText" => "NAS vendor type; used by checkrad for simultaneous-use checks. (Optional)"
                                     );

        $input_descriptors0[] = array(
                                        "name" => "shortname",
                                        "caption" => t('all','NasShortname'),
                                        "type" => "text",
                                        "value" => ((isset($shortname)) ? $shortname : ""),
                                        "tooltipText" => "A friendly short name to identify this NAS."
                                     );


        $input_descriptors1 = array();

        $input_descriptors1[] = array(
                                        "name" => "ports",
                                        "caption" => t('all','NasPorts'),
                                        "type" => "number",
                                        "min" => "0",
                                        "max" => "99999",
                                        "value" => ((isset($ports)) ? $ports : ""),
                                        "tooltipText" => "Number of ports on the NAS; informational only, not used by the server. (Optional)"
                                     );

        $input_descriptors1[] = array(
                                        "name" => "community",
                                        "caption" => t('all','NasCommunity'),
                                        "type" => "text",
                                        "value" => ((isset($community)) ? $community : ""),
                                        "tooltipText" => "SNMP community string for querying the NAS. (Optional)"
                                     );

        $input_descriptors1[] = array(
                                        "name" => "server",
                                        "caption" => t('all','NasVirtualServer'),
                                        "type" => "text",
                                        "value" => ((isset($server)) ? $server : ""),
                                        "tooltipText" => "FreeRADIUS virtual server that processes requests from this NAS. (Optional)"
                                     );

        $input_descriptors1[] = array(
                                        "name" => "description",
                                        "caption" => t('all','NasDescription'),
                                        "type" => "textarea",
                                        "content" => ((isset($description)) ? $description : ""),
                                        "tooltipText" => "Notes or additional details about this NAS. (Optional)"
                                     );

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

        // fieldset
        $fieldset0_descriptor = array(
                                        "title" => t('title','NASInfo'),
                                     );

        $fieldset1_descriptor = array(
                                        "title" => t('title','NASAdvanced'),
                                     );


        // set navbar stuff
        $navkeys = array( 'NASInfo', 'NASAdvanced' );

        // print navbar controls
        print_tab_header($navkeys);

        open_form();

        // open tab wrapper
        open_tab_wrapper();

        // open 0-th tab (shown)
        open_tab($navkeys, 0, true);

        // open 0-th fieldset
        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        close_tab($navkeys, 0);

        // open 1-st tab
        open_tab($navkeys, 1);

        // open 1-th fieldset
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

    print_back_to_previous_page();

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    print_footer_and_html_epilogue();
