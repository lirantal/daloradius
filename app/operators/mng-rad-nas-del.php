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
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'nasImportExport.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'nasManagementPdo.php' ]);

    // init logging variables
    $logAction = '';
    $logDebugSQL = '';
    $log = 'visited page: ';
    $success = false;
    $pdo = null;
    $nasLock = null;
    $valid_values = array();
    $selected_values = array();
    $csrfValid = $_SERVER['REQUEST_METHOD'] === 'POST' &&
        is_string($_POST['csrf_token'] ?? null) && dalo_check_csrf_token($_POST['csrf_token']);
    $raw = $_SERVER['REQUEST_METHOD'] === 'POST'
        ? ($_POST['nasname'] ?? array()) : ($_GET['nasname'] ?? array());
    $submitted = is_string($raw) ? array($raw) : $raw;
    $selectionInvalid = !is_array($submitted) || count($submitted) > 5000;
    if (!$selectionInvalid) {
        foreach ($submitted as $value) {
            if (!is_string($value) || trim($value) === '') { $selectionInvalid = true; break; }
            $selected_values[trim($value)] = trim($value);
        }
    }
    $selected_values = array_values($selected_values);
    if ($selectionInvalid) { $selected_values = array(); }
    try {
        list($pdo, $table) = nas_management_connect($configValues,
            $_SESSION['location_name'] ?? 'default', $csrfValid && !$selectionInvalid && count($selected_values) > 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$csrfValid) { $failureMsg = 'CSRF token error'; }
            elseif ($selectionInvalid || count($selected_values) === 0) {
                $failureMsg = 'No valid NAS hostname/IP provided; nothing was deleted.';
            } else {
                $nasLock = nas_management_lock($pdo, $configValues);
                if (!$pdo->beginTransaction()) { throw new RuntimeException('NAS transaction unavailable'); }
                $targets = array();
                foreach ($selected_values as $name) {
                    $row = nas_management_find($pdo, $table, $name, true);
                    if ($row === null) { throw new InvalidArgumentException('Stale NAS selection'); }
                    $targets[] = $row;
                }
                usort($targets, static function($left, $right) { return $left['id'] <=> $right['id']; });
                $delete = $pdo->prepare("DELETE FROM $table WHERE id=? AND HEX(nasname)=?");
                foreach ($targets as $row) {
                    $delete->execute(array($row['id'],strtoupper(bin2hex($row['nasname']))));
                    if ($delete->rowCount() !== 1) { throw new RuntimeException('NAS delete changed unexpectedly'); }
                }
                if (!$pdo->commit()) { throw new RuntimeException('NAS commit failed'); }
                $success = true;
                $tmp = array_map(static function($name) {
                    return htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                }, $selected_values);
                $label = count($tmp) === 1 ? 'NAS device' : 'NAS devices';
                $successMsg = sprintf('Successfully deleted %d %s: <strong>%s</strong>.',
                    count($tmp), $label, implode(', ', $tmp));
                $successMsg .= '<br><strong>Restart FreeRADIUS for the changes to take effect.</strong>';
                $logAction .= sprintf('Successfully deleted %s on page: ', $label);
            }
        }
        $stmt = $pdo->query("SELECT nasname FROM $table ORDER BY nasname");
        $valid_values = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $exception) {
        error_log('NAS delete: ' . get_class($exception));
        $failureMsg = $exception instanceof InvalidArgumentException
            ? 'Invalid or stale NAS selection; nothing was deleted.' : 'Unable to delete NAS; please retry';
        $success = false;
        try {
            if ($pdo instanceof PDO && $pdo->inTransaction()) { $pdo->rollBack(); }
            if ($pdo instanceof PDO) {
                $stmt = $pdo->query("SELECT nasname FROM $table ORDER BY nasname");
                $valid_values = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }
        } catch (Throwable $ignored) { /* Render an empty list on lookup failure. */ }
    } finally {
        nas_management_finish($pdo, $nasLock);
        $pdo = null;
    }
    // Only show names still present in the current options.
    $selected_values = array_values(array_intersect($selected_values, $valid_values));

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
