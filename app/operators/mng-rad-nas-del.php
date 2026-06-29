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

    // init logging variables
    $logAction = "";
    $logDebugSQL = "";
    $log = "visited page: ";

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);

    // build a whitelist of existing NAS names; only these can be deleted
    $valid_values = array();
    $sql = sprintf("SELECT DISTINCT(nasname) FROM %s", $configValues['CONFIG_DB_TBL_RADNAS']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    if (!DB::isError($res)) {
        while ($row = $res->fetchRow()) {
            $valid_values[] = $row[0];
        }
    }

    $nasnames = array();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('nasname', $_POST) && isset($_POST['nasname'])) {
            $nasnames = (!is_array($_POST['nasname'])) ? array($_POST['nasname']) : $_POST['nasname'];
        }
    } else {
        if (array_key_exists('nasname', $_REQUEST) && isset($_REQUEST['nasname'])) {
            $nasnames = (!is_array($_REQUEST['nasname'])) ? array($_REQUEST['nasname']) : $_REQUEST['nasname'];
        }
    }

    $selected_values = array();
    foreach ($nasnames as $value) {
        $value = trim($value);
        if (in_array($value, $valid_values)) {
            $selected_values[] = $value;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
        array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
        $deleted_values = array();

        if (count($selected_values) > 0) {
            foreach ($selected_values as $value) {
                $sql = sprintf("DELETE FROM %s WHERE nasname='%s'", $configValues['CONFIG_DB_TBL_RADNAS'],
                                                                    $dbSocket->escapeSimple($value));
                $result = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";

                if ($result > 0) {
                    $deleted_values[] = $value;
                }
            }
        }

        $success = $_SERVER['REQUEST_METHOD'] == 'POST' && count($selected_values) > 0 && count($deleted_values) > 0;

        // present results
        if ($success) {
            $tmp = array();
            foreach ($deleted_values as $deleted_value) {
                $tmp[] = htmlspecialchars($deleted_value, ENT_QUOTES, 'UTF-8');
            }
            $label = (count($tmp) == 1) ? "NAS device" : "NAS devices";
            $successMsg = sprintf("Successfully deleted %d %s: <strong>%s</strong>.", count($tmp), $label, implode(", ", $tmp));
            $successMsg .= '<br><strong>Restart FreeRADIUS for the changes to take effect.</strong>';
            $logAction .= sprintf("Successfully deleted %s [%s] on page: ", $label, implode(", ", $deleted_values));
        } else {
            $failureMsg = "No valid NAS hostname/IP provided; nothing was deleted.";
            $logAction .= sprintf("Failed deleting NAS(s) [%s] on page: ", implode(", ", $valid_values));
        }

    } else {
        $success = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);

    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);

    // print HTML prologue
    $title = t('Intro','mngradnasdel.php');
    $help = t('helpPage','mngradnasdel');

    print_html_prologue($title, $langCode);
    print_title_and_help($title, $help);

    if ($_SERVER['REQUEST_METHOD'] != 'GET') {
        include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);
    }

    if (!$success) {
        $options = array();
        foreach ($valid_values as $valid_value) {
            $options[$valid_value] = $valid_value;
        }

        $input_descriptors1 = array();

        $input_descriptors1[0] = array(
                                        'name' => 'nasname[]',
                                        'id' => 'nasname',
                                        'type' => 'select',
                                        'caption' => t('all','NasIPHost'),
                                        'options' => $options,
                                        'multiple' => true,
                                        'size' => 5,
                                        'selected_value' => $selected_values,
                                      );
        if (count($options) == 0) {
            $input_descriptors1[0]['disabled'] = true;
        }

        $input_descriptors1[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                      );

        $input_descriptors1[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );

        $fieldset1_descriptor = array(
                                        "title" => t('title','NASInfo'),
                                        "disabled" => (count($options) == 0)
                                     );

        open_form();

        open_fieldset($fieldset1_descriptor);

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        close_form();

    }

    print_back_to_previous_page();

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    print_footer_and_html_epilogue();
