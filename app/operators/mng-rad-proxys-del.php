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

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');

    include_once('../common/includes/config_read.php');
    include_once('include/management/realmProxyPdo.php');
    $logAction = '';
    $logDebugSQL = '';
    $log = 'visited page: ';
    $field_name = 'proxyname';
    $success = false;
    $valid_values = array();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_string($_POST['csrf_token'] ?? null) || !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
        } else {
            try {
                $raw = $_POST[$field_name] ?? array();
                if (is_string($raw)) { $raw = array($raw); }
                $result = realm_proxy_mutate($configValues, $_SESSION['location_name'] ?? 'default',
                                             $operator, 'proxy', 'delete', $raw);
                $success = true;
                $escaped = array_map(static function($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); },
                                     $result['names']);
                $successMsg = 'Deleted proxy(s): <strong>' . implode(', ', $escaped) . '</strong>';
                $logAction .= 'Successfully deleted proxy(s) on page: ';
            } catch (Throwable $exception) {
                error_log('Proxy delete page: ' . get_class($exception));
                $failureMsg = $exception instanceof RealmProxyUncertainException
                    ? 'Realm/proxy state uncertain; verify file and database before retrying'
                    : 'Unable to delete proxy(s) and update configuration; no change applied';
            }
        }
    }
    try {
        list($pdo, $tables) = realm_proxy_connect($configValues, $_SESSION['location_name'] ?? 'default');
        $stmt = $pdo->query("SELECT DISTINCT($field_name) FROM {$tables['proxy']} ORDER BY $field_name");
        $valid_values = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $exception) {
        error_log('Proxy delete lookup: ' . get_class($exception));
        $failureMsg = 'Unable to read proxy(s)';
    } finally { $pdo = null; }

    include_once('../common/includes/config_read.php');
    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // print HTML prologue
    
    $title = t('Intro','mngradproxysdel.php');
    $help = t('helpPage','mngradproxysdel');
    
    print_html_prologue($title, $langCode);

    
    


    print_title_and_help($title, $help);
    
    if ($_SERVER['REQUEST_METHOD'] != 'GET') {
        include_once('include/management/actionMessages.php');
    }
    
    if (!$success) {
        $options = $valid_values;
        
        $input_descriptors1 = array();

        $input_descriptors1[] = array(
                                    'name' => $field_name . "[]",
                                    'id' => $field_name,
                                    'type' => 'select',
                                    'caption' => t('all','ProxyName'),
                                    'options' => $options,
                                    'multiple' => true,
                                    'size' => 5,
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
                                        "title" => t('title','ProxyInfo'),
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

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
