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
    include("include/management/functions.php");
    include_once("include/management/populate_selectbox.php");
    
    include_once('include/management/realmProxyPdo.php');
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";
    
    // load valid proxies
    $valid_proxynames = get_proxies();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $proxyname = '';
        $retry_delay = $retry_count = $dead_time = $default_fallback = '';
        if (!is_string($_POST['csrf_token'] ?? null) || !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
        } else {
            try {
                $fields = realm_proxy_fields('proxy', $_POST);
                foreach ($fields as $key => $value) { $$key = $value; }
                $result = realm_proxy_mutate($configValues, $_SESSION['location_name'] ?? 'default',
                                             $operator, 'proxy', 'create', $_POST);
                $successMsg = sprintf('Successfully inserted new proxy in db [<a href="mng-rad-proxys-edit.php?item=%s">Edit</a>]',
                    urlencode('proxy-' . $result['id']));
                $logAction .= 'Successfully inserted new proxy on page: ';
            } catch (Throwable $exception) {
                error_log('Proxy create page: ' . get_class($exception));
                $failureMsg = $exception instanceof RealmProxyUncertainException
                    ? 'Realm/proxy state uncertain; verify file and database before retrying'
                    : 'Unable to save proxy and configuration; no change applied';
            }
        }
    }

    // print HTML prologue
    $title = t('Intro','mngradproxysnew.php');
    $help = t('helpPage','mngradproxysnew');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    if (!isset($successMsg)) {
        
        // ensure form variables are initialized (they may not be set on initial
        // GET requests or when POST validation fails before reaching their assignment)
        $proxyname = (isset($proxyname)) ? $proxyname : "";
        $retry_delay = (isset($retry_delay)) ? $retry_delay : "";
        $retry_count = (isset($retry_count)) ? $retry_count : "";
        $dead_time = (isset($dead_time)) ? $dead_time : "";
        $default_fallback = (isset($default_fallback)) ? $default_fallback : "";
        
        // descriptors 0
        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        'name' => 'proxyname',
                                        'caption' => t('all','ProxyName'),
                                        'type' => 'text',
                                        'value' => $proxyname,
                                        'required' => true,
                                        'tooltipText' => t('Tooltip','proxyNameTooltip'),
                                     );

        $input_descriptors0[] = array(
                                        'name' => 'retry_delay',
                                        'caption' => t('all','RetryDelay'),
                                        'type' => 'number',
                                        'value' => $retry_delay,
                                        'tooltipText' => t('Tooltip','proxyRetryDelayTooltip'),
                                     );
        
        $input_descriptors0[] = array(
                                        'name' => 'retry_count',
                                        'caption' => t('all','RetryCount'),
                                        'type' => 'number',
                                        'value' => $retry_count,
                                        'tooltipText' => t('Tooltip','proxyRetryCountTooltip'),
                                     );
                                     
        $input_descriptors0[] = array(
                                        'name' => 'dead_time',
                                        'caption' => t('all','DeadTime'),
                                        'type' => 'number',
                                        'value' => $dead_time,
                                        'tooltipText' => t('Tooltip','proxyDeadTimeTooltip'),
                                     );
                                     
        $input_descriptors0[] = array(
                                        'name' => 'default_fallback',
                                        'caption' => t('all','DefaultFallback'),
                                        'type' => 'number',
                                        'value' => $default_fallback,
                                        'tooltipText' => t('Tooltip','proxyDefaultFallbackTooltip'),
                                     );
        
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
                                        "value" => t('buttons','apply')
                                      );

        open_form();

        // fieldset 0
        $fieldset0_descriptor = array(
                                        "title" => t('title','ProxyInfo'),
                                     );

        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();
        
        foreach ($input_descriptors2 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_form();
        
    }

    print_back_to_previous_page();
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
