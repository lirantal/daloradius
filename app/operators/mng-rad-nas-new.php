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
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'nasImportExport.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'nasManagementPdo.php' ]);

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $pdo = null;
        $nasLock = null;
        $nasname = $secret = $shortname = $server = $community = $description = '';
        $nastype = 'other';
        $ports = 0;
        if (!is_string($_POST['csrf_token'] ?? null) || !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
        } else {
            try {
                $fields = nas_management_fields($_POST, $valid_nastypes);
                $nasname = $fields['nasname'];
                $shortname = $fields['shortname'];
                $nastype = $fields['type'];
                $ports = $fields['ports'];
                $secret = $fields['secret'];
                $server = $fields['server'];
                $community = $fields['community'];
                $description = $fields['description'];
                list($pdo, $table) = nas_management_connect($configValues,
                    $_SESSION['location_name'] ?? 'default', true);
                $nasLock = nas_management_lock($pdo, $configValues);
                if (!$pdo->beginTransaction()) { throw new RuntimeException('NAS transaction unavailable'); }
                $check = $pdo->prepare("SELECT id FROM $table WHERE LOWER(nasname)=LOWER(?) "
                                      . "OR HEX(nasname)=? LIMIT 1 FOR UPDATE");
                $check->execute(array($nasname, strtoupper(bin2hex($nasname))));
                if ($check->fetchColumn() !== false) {
                    throw new DomainException('NAS name already exists');
                }
                $insert = $pdo->prepare("INSERT INTO $table "
                    . '(nasname,shortname,type,ports,secret,server,community,description) '
                    . 'VALUES (?,?,?,?,?,?,?,?)');
                $insert->execute(array($nasname,$shortname,$nastype,$ports,$secret,$server,$community,$description));
                $id = $pdo->lastInsertId();
                $stored = nas_management_find($pdo, $table, $nasname, true);
                if (!nas_management_row_matches($stored, $fields) || (string)$stored['id'] !== (string)$id) {
                    throw new RuntimeException('NAS insert verification failed');
                }
                if (!$pdo->commit()) { throw new RuntimeException('NAS commit failed'); }
                $nasname_enc = htmlspecialchars($nasname, ENT_QUOTES, 'UTF-8');
                $successMsg = sprintf('Successfully added a new NAS (<strong>%s</strong>) '
                    . '<a href="mng-rad-nas-edit.php?nasname=%s" title="Edit">Edit</a>',
                    $nasname_enc, urlencode($nasname));
                $successMsg .= '<br><strong>Restart FreeRADIUS for NAS changes to take effect.</strong>';
                $logAction .= "Successfully added a new NAS [$nasname] on page: ";
            } catch (Throwable $exception) {
                error_log('NAS create: ' . get_class($exception));
                $nasname_enc = htmlspecialchars($nasname, ENT_QUOTES, 'UTF-8');
                $failureMsg = $exception instanceof DomainException
                    ? sprintf('This %s already exists: <b>%s</b>', t('all','NasIPHost'), $nasname_enc)
                    : ($exception instanceof InvalidArgumentException
                       ? 'NAS fields are empty or invalid' : 'Unable to add NAS; please retry');
                $logAction .= 'Failed adding a new NAS on page: ';
            } finally {
                nas_management_finish($pdo, $nasLock);
                $pdo = null;
            }
        }
        // Never echo a submitted shared secret after an unsuccessful request.
        if (!isset($successMsg)) { $secret = ''; }
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
