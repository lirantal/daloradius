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
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    require_once implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'pdo_connection.php' ]);
    require_once __DIR__ . '/library/user_delete.php';
    $username = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $csrf = $_POST['csrf_token'] ?? null;
        if (!is_string($csrf) || !dalo_check_csrf_token($csrf)) {
            $failureMsg = 'CSRF token error';
            $logAction = "$failureMsg on page: ";
        } else {
            try {
                if (array_key_exists('username', $_POST) && array_key_exists('clearSessionsUsers', $_POST)) {
                    throw new InvalidArgumentException('Ambiguous deletion request');
                }
                if (array_key_exists('username', $_POST)) {
                    $pdo = dalo_pdo_connect($configValues);
                    if (array_key_exists('attribute', $_POST) || array_key_exists('tablename', $_POST)) {
                        if (!is_string($_POST['username']) || !isset($_POST['attribute'], $_POST['tablename']) ||
                            !is_string($_POST['attribute']) || !is_string($_POST['tablename'])) {
                            throw new InvalidArgumentException('Invalid attribute removal');
                        }
                        $username = $_POST['username'];
                        dalo_user_delete_attribute($pdo, $configValues, $username,
                                                   $_POST['attribute'], $_POST['tablename']);
                        $successMsg = sprintf('Deleted attribute %s for user %s',
                            htmlspecialchars(explode('__', $_POST['attribute'], 2)[1], ENT_QUOTES, 'UTF-8'),
                            htmlspecialchars($username, ENT_QUOTES, 'UTF-8'));
                        $logAction = 'Deleted user attribute on page: ';
                    } else {
                        $users = dalo_user_delete_selection($_POST['username']);
                        $choice = $_POST['delradacct'] ?? '';
                        if (!is_string($choice) || !in_array($choice, array('', 'yes', 'no'), true)) {
                            throw new InvalidArgumentException('Invalid accounting selection');
                        }
                        $count = dalo_user_delete_accounts($pdo, $configValues, $users, $choice === 'yes');
                        $successMsg = sprintf('%d user(s) have been deleted', $count);
                        $logAction = sprintf('%d user(s) deleted on page: ', $count);
                    }
                } elseif (array_key_exists('clearSessionsUsers', $_POST)) {
                    $sessions = dalo_user_delete_sessions_selection($_POST['clearSessionsUsers']);
                    $pdo = dalo_pdo_connect($configValues);
                    $count = dalo_user_delete_sessions($pdo, $configValues, $sessions);
                    $successMsg = sprintf("%d user' session(s) have been cleaned", $count);
                    $logAction = sprintf('%d session(s) cleaned on page: ', $count);
                } else {
                    throw new InvalidArgumentException('No users or sessions selected');
                }
            } catch (Throwable $error) {
                // Never include submitted data or driver errors in the response/logs.
                $failureMsg = ($error instanceof DomainException)
                            ? htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8')
                            : 'Invalid or stale removal selection; no changes were saved';
                $logAction = 'Failed user removal on page: ';
            }
        }
    }

    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);

    // print HTML prologue
    $title = t('Intro','mngdel.php');
    $help = t('helpPage','mngdel');
    
    print_html_prologue($title, $langCode);
    
    if (!empty($username) && !is_array($username)) {
        $title .= " :: " . htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    }

    print_title_and_help($title, $help);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);
    $pdoList = dalo_pdo_connect($configValues);
    $checkTable = dalo_user_delete_table($configValues, 'CONFIG_DB_TBL_RADCHECK');
    $options = $pdoList->query("SELECT DISTINCT username FROM $checkTable ORDER BY username")
                       ->fetchAll(PDO::FETCH_COLUMN);

    $input_descriptors1 = [];

    $input_descriptors1[] = array(
                                'name' => 'username[]',
                                'id' => 'username',
                                'type' => 'select',
                                'caption' => t('all','Username'),
                                'options' => $options,
                                'multiple' => true,
                                'size' => 5
                             );

    $input_descriptors1[] = array(
                                'name' => 'delradacct',
                                'type' => 'select',
                                'caption' => t('all','RemoveRadacctRecords'),
                                'options' => array("", "yes", "no"),
                             );

    $input_descriptors1[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );

    $input_descriptors1[] = array(
                                    'type' => 'submit',
                                    'name' => 'submit',
                                    'value' => t('buttons','apply')
                                 );
                                 
    $fieldset1_descriptor = array(
                                    "title" => t('title','AccountRemoval'),
                                    "disabled" => (count($options) == 0)
                                 );

    open_form();
    
    open_fieldset($fieldset1_descriptor);

    foreach ($input_descriptors1 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
    
    close_fieldset();
    
    close_form();

    print_back_to_previous_page();

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    print_footer_and_html_epilogue();
