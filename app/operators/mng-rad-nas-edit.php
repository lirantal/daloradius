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

    $nasname = '';
    $sourceName = $_SERVER['REQUEST_METHOD'] === 'POST'
        ? ($_POST['nasname'] ?? null) : ($_GET['nasname'] ?? null);
    if (is_string($sourceName)) { $nasname = trim($sourceName); }
    $pdo = null;
    $nasLock = null;
    $csrfValid = $_SERVER['REQUEST_METHOD'] === 'POST' &&
        is_string($_POST['csrf_token'] ?? null) && dalo_check_csrf_token($_POST['csrf_token']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$csrfValid) {
        $failureMsg = 'CSRF token error';
    }
    try {
        list($pdo, $table) = nas_management_connect($configValues,
            $_SESSION['location_name'] ?? 'default', $csrfValid);
        if ($csrfValid) {
            $nasLock = nas_management_lock($pdo, $configValues);
            if (!$pdo->beginTransaction()) { throw new RuntimeException('NAS transaction unavailable'); }
            $current = $nasname !== '' ? nas_management_find($pdo, $table, $nasname, true) : null;
            if ($current === null) { throw new InvalidArgumentException('NAS missing'); }
            $fields = nas_management_fields($_POST, $valid_nastypes, $current['type']);
            if ($fields['nasname'] !== $current['nasname']) {
                throw new InvalidArgumentException('NAS name changed');
            }
            $update = $pdo->prepare("UPDATE $table SET shortname=?,type=?,ports=?,secret=?,server=?,"
                                    . 'community=?,description=? WHERE id=? AND HEX(nasname)=?');
            $update->execute(array($fields['shortname'],$fields['type'],$fields['ports'],
                $fields['secret'],$fields['server'],$fields['community'],$fields['description'],
                $current['id'],strtoupper(bin2hex($current['nasname']))));
            // A no-op update changes zero rows; verify the complete locked row instead.
            $stored = nas_management_find($pdo, $table, $nasname, true);
            if (!nas_management_row_matches($stored, $fields) ||
                (string)$stored['id'] !== (string)$current['id']) {
                throw new RuntimeException('NAS update verification failed');
            }
            if (!$pdo->commit()) { throw new RuntimeException('NAS commit failed'); }
            $nasname_enc = htmlspecialchars($nasname, ENT_QUOTES, 'UTF-8');
            $successMsg = sprintf('Edited NAS: <strong>%s</strong>', $nasname_enc);
            $successMsg .= '<br><strong>Restart FreeRADIUS for NAS changes to take effect.</strong>';
            $logAction .= "Successfully edited NAS [$nasname] on page: ";
        }
        $row = $nasname !== '' ? nas_management_find($pdo, $table, $nasname) : null;
        if ($row !== null) {
            foreach (array('nasname','shortname','type','ports','secret','server','community','description') as $field) {
                $$field = $row[$field];
            }
            $nasname_enc = htmlspecialchars($nasname, ENT_QUOTES, 'UTF-8');
        } else {
            $nasname = '';
            if (!isset($failureMsg)) { $failureMsg = sprintf("%s is invalid", t('all','NasIPHost')); }
        }
    } catch (Throwable $exception) {
        error_log('NAS edit: ' . get_class($exception));
        $failureMsg = $exception instanceof InvalidArgumentException
            ? 'NAS fields or hostname are invalid' : 'Unable to edit NAS; please retry';
        // Read the row for the form after a failed mutation, not from untrusted POST data.
        try {
            if ($pdo instanceof PDO && $pdo->inTransaction()) { $pdo->rollBack(); }
            $row = $nasname !== '' && $pdo instanceof PDO ? nas_management_find($pdo, $table, $nasname) : null;
            if ($row !== null) {
                foreach (array('nasname','shortname','type','ports','secret','server','community','description') as $field) {
                    $$field = $row[$field];
                }
                $nasname_enc = htmlspecialchars($nasname, ENT_QUOTES, 'UTF-8');
            } else { $nasname = ''; }
        } catch (Throwable $ignored) { $nasname = ''; }
    } finally {
        nas_management_finish($pdo, $nasLock);
        $pdo = null;
    }

    // print HTML prologue
    $title = t('Intro','mngradnasedit.php');
    $help = t('helpPage','mngradnasedit');

    print_html_prologue($title, $langCode);

    if (isset($nasname_enc)) {
        $title .= " :: $nasname_enc";
    }

    print_title_and_help($title, $help);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);

    if (!empty($nasname)) {

        // set form component descriptors
        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        "name" => "nasname",
                                        "type" => "hidden",
                                        "value" => ((isset($nasname)) ? $nasname : "")
                                     );

        $input_descriptors0[] = array(
                                        "name" => "nasname_presentation",
                                        "caption" => t('all','NasIPHost'),
                                        "type" => "text",
                                        "value" => ((isset($nasname)) ? $nasname : ""),
                                        "disabled" => true,
                                        "tooltipText" => "IP address or hostname of the NAS device. (Required)"
                                     );

        $input_descriptors0[] = array(
                                        "name" => "secret",
                                        "caption" => t('all','NasSecret'),
                                        "type" => "text",
                                        "value" => ((isset($secret)) ? $secret : ""),
                                        "tooltipText" => "Shared secret used to authenticate RADIUS traffic with the NAS. (Required)"
                                     );

        // preserve legacy/custom NAS type values loaded from DB
        $nastype_options = $valid_nastypes;
        $nastype_selected = (isset($type) && $type !== "") ? $type : "other";
        $nastype_tooltip = "Select the NAS vendor type from the predefined list.";

        if ($nastype_selected !== "other" && !in_array($nastype_selected, $valid_nastypes)) {
            // DB contains a type not in the standard list — build an associative
            // options map so the legacy value is shown and stays selected
            $nastype_options = array();
            $nastype_options[$nastype_selected] = $nastype_selected . " (legacy)";
            foreach ($valid_nastypes as $nt) {
                $nastype_options[$nt] = $nt;
            }
            $nastype_tooltip = "The current type is not in the standard list. "
                             . "It will be preserved unless you select a different value.";
        }

        $input_descriptors0[] = array(
                                        "name" => "type",
                                        "caption" => t('all','NasType'),
                                        "type" => "select",
                                        "options" => $nastype_options,
                                        "selected_value" => $nastype_selected,
                                        "tooltipText" => $nastype_tooltip
                                     );

        $input_descriptors0[] = array(
                                        "name" => "shortname",
                                        "caption" => t('all','NasShortname'),
                                        "type" => "text",
                                        "value" => ((isset($shortname)) ? $shortname : ""),
                                        "tooltipText" => "Friendly short name to identify this NAS. (Optional)"
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

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    print_footer_and_html_epilogue();
