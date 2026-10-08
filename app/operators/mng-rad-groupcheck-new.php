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
/* UNIT-036: migrate group attribute create/edit writes to caller-owned PDO. */

    include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);
    $operator = $_SESSION['operator_user'];
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'populate_selectbox.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'attributes.php' ]);
    require_once implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'pdo_connection.php' ]);
    require_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'attributes_pdo.php' ]);

    $log = 'visited page: ';
    $logAction = '';
    $logDebugSQL = '';

    $groupname = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_string($_POST['csrf_token'] ?? null) || !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
            $logAction .= 'Failed creating groupcheck: CSRF on page: ';
        } else {
            $postedGroup = $_POST['groupname'] ?? null;
            $groupname = is_string($postedGroup) ? trim($postedGroup) : '';
            if ($groupname === '') {
                $failureMsg = 'The specified group name is empty or invalid';
                $logAction .= 'Failed creating groupcheck: invalid group name on page: ';
            } else if (!in_array($groupname, array_keys(get_groups()), true)) {
                $groupname_enc = htmlspecialchars($groupname, ENT_QUOTES, 'UTF-8');
                $failureMsg = "The chosen group [<strong>$groupname_enc</strong>] does not exist";
                $logAction .= 'Failed creating groupcheck: group does not exist on page: ';
            } else {
                $pdo = null;
                try {
                    $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                    if (!$pdo->beginTransaction()) { throw new RuntimeException('Attribute transaction unavailable'); }
                    $count = handleAttributes($pdo, $groupname,
                        array('groupname', 'submit', 'csrf_token'), true, 'group');
                    if ($count < 1) { throw new DomainException('No valid group attributes'); }
                    $last = $pdo->lastInsertId();
                    if (!ctype_digit((string) $last) || (int) $last < 1) {
                        throw new RuntimeException('Attribute insert ID unavailable');
                    }
                    if (!$pdo->commit()) { throw new RuntimeException('Attribute commit failed'); }
                    $item_id = 'groupcheck-' . $last;
                    $successMsg = sprintf('Successfully added a new groupcheck item (item id: %s)', $item_id)
                        . sprintf(' [<a href="mng-rad-groupcheck-edit.php?item=%s" title="Edit">Edit</a>]', urlencode($item_id));
                    $logAction .= 'Successfully added a new groupcheck item on page: ';
                } catch (Throwable $exception) {
                    if ($pdo instanceof PDO && $pdo->inTransaction()) { $pdo->rollBack(); }
                    error_log('groupcheck create: ' . get_class($exception));
                    $failureMsg = $exception instanceof DomainException
                        ? 'Failed adding a new groupcheck item, invalid or empty attributes list'
                        : 'Unable to add groupcheck; please retry';
                    $logAction .= 'Failed creating groupcheck on page: ';
                } finally {
                    $pdo = null;
                }
            }
        }
    }

    // print HTML prologue
    $extra_js = array(
        "static/js/request.js",
        "static/js/dynamic_attributes.js",
    );
    
    $title = t('Intro','mngradgroupchecknew.php');
    $help = t('helpPage','mngradgroupchecknew');
    
    print_html_prologue($title, $langCode, array(), $extra_js);

    
    

    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    if (!isset($successMsg)) {
        
        // set form component descriptors
        $input_descriptors0 = array();
        
        $groups = get_groups();
        array_unshift($groups , '');
        $input_descriptors0[] = array(
                                        "name" => "groupname",
                                        "caption" => t('all','Groupname'),
                                        "type" => "select",
                                        "options" => $groups,
                                        "selected_value" => ((isset($groupname)) ? $groupname : "")
                                     );

        $input_descriptors1 = array();
        $input_descriptors1[] = array(
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                        "name" => "csrf_token"
                                     );

        $input_descriptors1[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                     );
                                     
        open_form();
        
        $fieldset0_descriptor = array( "title" => t('title','GroupInfo') );
        
        open_fieldset($fieldset0_descriptor);
        
        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        include_once('include/management/attributes.php');
        
        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_form();
        
    }
    
    print_back_to_previous_page();
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
