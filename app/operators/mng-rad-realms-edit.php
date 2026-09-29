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

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");
    include_once("include/management/populate_selectbox.php");
    
    include_once('include/management/realmProxyPdo.php');
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    // load valid realmnames
    $valid_realmnames = get_realms();

    $valid_types = array( "fail-over", "load-balance", "client-balance", "client-port-balance", "keyed-balance" );

    $rawName = $_SERVER['REQUEST_METHOD'] === 'POST'
        ? ($_POST['realmname'] ?? null) : ($_GET['realmname'] ?? null);
    $realmname = is_string($rawName) ? trim($rawName) : '';
    $selected_realmname = $realmname;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_string($_POST['csrf_token'] ?? null) || !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
        } else {
            try {
                if ($realmname === '') { throw new InvalidArgumentException('Invalid realm name'); }
                realm_proxy_mutate($configValues, $_SESSION['location_name'] ?? 'default',
                                   $operator, 'realm', 'edit', $_POST);
                $successMsg = 'Successfully updated realm';
                $logAction .= 'Successfully updated realm on page: ';
            } catch (Throwable $exception) {
                error_log('Realm edit page: ' . get_class($exception));
                $failureMsg = $exception instanceof RealmProxyUncertainException
                    ? 'Realm/proxy state uncertain; verify file and database before retrying'
                    : 'Unable to save realm and configuration; no change applied';
            }
        }
    }
    try {
        list($pdo, $tables) = realm_proxy_connect($configValues, $_SESSION['location_name'] ?? 'default');
        $stmt = $pdo->prepare("SELECT id,realmname,type,authhost,accthost,secret,ldflag,nostrip,"
                              . "hints,notrealm,creationdate,creationby,updatedate,updateby "
                              . "FROM {$tables['realm']} WHERE BINARY realmname=BINARY ? LIMIT 2");
        $stmt->execute(array($realmname));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || $stmt->fetch(PDO::FETCH_ASSOC)) { $realmname = ''; }
        else { foreach ($row as $key => $value) { $$key = $value; } }
    } catch (Throwable $exception) {
        error_log('Realm edit lookup: ' . get_class($exception));
        $realmname = '';
        $failureMsg = 'Unable to read realm';
    } finally { $pdo = null; }
    if ($realmname === '' && !isset($failureMsg)) { $failureMsg = 'Selected an empty/invalid realm item'; }

    // print HTML prologue
    $extra_css = array();
    
    $extra_js = array(
        "static/js/pages_common.js",
    );

    $title = t('Intro','mngradrealmsedit.php');
    $help = t('helpPage','mngradrealmsedit');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');

    
    if (!empty($realmname)) {
        
        // set navbar stuff
        $navkeys = array( 'RealmInfo', 'Advanced' );

        // print navbar controls
        print_tab_header($navkeys);
        
        // descriptors 0
        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        'name' => 'realmname-presentation',
                                        'caption' => t('all','RealmName'),
                                        'type' => 'text',
                                        'value' => $realmname,
                                        'disabled' => true,
                                        'tooltipText' => t('Tooltip','realmNameTooltip'),
                                     );
                                     
        $options = $valid_types;
        array_unshift($options, '');
        $input_descriptors0[] = array(
                                        "type" =>"select",
                                        "name" => "type",
                                        "caption" => t('all','Type'),
                                        "options" => $options,
                                        "selected_value" => ((isset($type)) ? $type : ""),
                                        "tooltipText" => t('Tooltip','realmTypeTooltip')
                                     );
                                     
        $input_descriptors0[] = array(
                                        'name' => 'authhost',
                                        'caption' => t('all','AuthHost'),
                                        'type' => 'text',
                                        'value' => ((isset($authhost)) ? $authhost : ""),
                                        'tooltipText' => t('Tooltip','realmAuthhostTooltip'),
                                     );
                                     
        $input_descriptors0[] = array(
                                        'name' => 'accthost',
                                        'caption' => t('all','AcctHost'),
                                        'type' => 'text',
                                        'value' => ((isset($accthost)) ? $accthost : ""),
                                        'tooltipText' => t('Tooltip','realmAccthostTooltip'),
                                     );

        $input_descriptors0[] = array(
                                        'name' => 'secret',
                                        'caption' => t('all','AcctHost'),
                                        'type' => 'text',
                                        'value' => ((isset($secret)) ? $secret : ""),
                                        'tooltipText' => t('Tooltip','realmSecretTooltip'),
                                     );

        // descriptors 1
        $input_descriptors1 = array();
        $input_descriptors1[] = array( 'name' => 'creationdate', 'caption' => t('all','CreationDate'), 'type' => 'datetime-local',
                                       'disabled' => true, 'value' => ((isset($creationdate)) ? $creationdate : '') );
        $input_descriptors1[] = array( 'name' => 'creationby', 'caption' => t('all','CreationBy'), 'type' => 'text',
                                       'disabled' => true, 'value' => ((isset($creationby)) ? $creationby : '') );
        $input_descriptors1[] = array( 'name' => 'updatedate', 'caption' => t('all','UpdateDate'), 'type' => 'datetime-local',
                                       'disabled' => true, 'value' => ((isset($updatedate)) ? $updatedate : '') );
        $input_descriptors1[] = array( 'name' => 'updateby', 'caption' => t('all','UpdateBy'), 'type' => 'text',
                                       'disabled' => true, 'value' => ((isset($updateby)) ? $updateby : '') );

        // descriptors 2
        $input_descriptors2 = array();
        $input_descriptors2[] = array(
                                        "type" =>"select",
                                        "name" => "nostrip",
                                        "caption" => t('all','Nostrip'),
                                        "options" => array( "yes", "no" ),
                                        "selected_value" => ((isset($nostrip) && $nostrip) ? "yes" : "no"),
                                        "tooltipText" => t('Tooltip','realmNostripTooltip')
                                     );
                                     
        $input_descriptors2[] = array(
                                        'name' => 'ldflag',
                                        'caption' => t('all','Ldflag'),
                                        'type' => 'text',
                                        'value' => ((isset($ldflag)) ? $ldflag : ""),
                                        'tooltipText' => t('Tooltip','realmLdflagTooltip'),
                                     );
                                     
        $input_descriptors2[] = array(
                                        'name' => 'hints',
                                        'caption' => t('all','Hints'),
                                        'type' => 'text',
                                        'value' => ((isset($hints)) ? $hints : ""),
                                        'tooltipText' => t('Tooltip','realmHintsTooltip'),
                                     );
                                     
        $input_descriptors2[] = array(
                                        'name' => 'notrealm',
                                        'caption' => t('all','Notrealm'),
                                        'type' => 'text',
                                        'value' => ((isset($notrealm)) ? $notrealm : ""),
                                        'tooltipText' => t('Tooltip','realmNotrealmTooltip'),
                                     );

        open_form();

        // open tab wrapper
        open_tab_wrapper();

        // tab 0
        open_tab($navkeys, 0, true);

        // fieldset 0
        $fieldset0_descriptor = array(
                                        "title" => t('title','RealmInfo'),
                                     );

        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();
        
        // fieldset 1
        $fieldset1_descriptor = array(
                                        "title" => "Other Information",
                                     );

        open_fieldset($fieldset1_descriptor);

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();
        
        close_tab($navkeys, 0);
        
        // tab 1
        open_tab($navkeys, 1);
        
        // fieldset 1
        $fieldset2_descriptor = array(
                                        "title" => t('title','Advanced'),
                                     );
        
        open_fieldset($fieldset2_descriptor);
        
        foreach ($input_descriptors2 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        close_tab($navkeys, 1);
        
        // close tab wrapper
        close_tab_wrapper();
        
        $input_descriptors3 = array();
        
        $input_descriptors3[] = array(
                                        "name" => "realmname",
                                        "type" => "hidden",
                                        "value" => $realmname,
                                     );
        
        $input_descriptors3[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );

        $input_descriptors3[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                     );
        
        foreach ($input_descriptors3 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_form();
        
    }

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
