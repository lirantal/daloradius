<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
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
 * Authors:    Liran Tal <liran@enginx.com>
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
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";
    
    // load valid proxies
    $valid_proxynames = get_proxies();
    
    include('../common/includes/db_open.php');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            
            $proxyname = (array_key_exists('proxyname', $_POST) && !empty(str_replace("%", "", trim($_POST['proxyname']))) &&
                          !in_array(str_replace("%", "", trim($_POST['proxyname'])), $valid_proxynames))
                       ? str_replace("%", "", trim($_POST['proxyname'])) : "";
            $proxyname_enc = (!empty($proxyname)) ? htmlspecialchars($proxyname, ENT_QUOTES, 'UTF-8') : "";
            
            if (empty($proxyname)) {
                // emptyn invalid or already existent
                $failureMsg = sprintf("Empty or invalid %s", t('all','ProxyName'));
                $logAction .= "$failureMsg on page: ";
            } else {
                
                // required later
                $currDate = date('Y-m-d H:i:s');
                $currBy = $operator;
                
                $retry_delay = (array_key_exists('retry_delay', $_POST) && intval(trim($_POST['retry_delay'])) > 0)
                             ? intval(trim($_POST['retry_delay'])) : "";
                
                $retry_count = (array_key_exists('retry_count', $_POST) && intval(trim($_POST['retry_count'])) > 0)
                             ? intval(trim($_POST['retry_count'])) : "";
                             
                $dead_time = (array_key_exists('dead_time', $_POST) && intval(trim($_POST['dead_time'])) > 0)
                           ? intval(trim($_POST['dead_time'])) : "";
                           
                $default_fallback = (array_key_exists('default_fallback', $_POST) && intval(trim($_POST['default_fallback'])) > 0)
                                  ? intval(trim($_POST['default_fallback'])) : "";
                
                $sql = sprintf("INSERT INTO %s (id, retry_delay, retry_count, dead_time, default_fallback,
                                                creationdate, creationby, updatedate, updateby, proxyname)
                                        VALUES (0, ?, ?, ?, ?, ?, ?, NULL, NULL, ?)", $configValues['CONFIG_DB_TBL_DALOPROXYS']);
                $prep = $dbSocket->prepare($sql);
                $values = array( $retry_delay, $retry_count, $dead_time, $default_fallback, $currDate, $currBy, $proxyname );
                $res = $dbSocket->execute($prep, $values);
                $logDebugSQL .= "$sql;\n";
                
                if (!DB::isError($res)) {
                    // retrieve invoice id
                    $sql = sprintf("SELECT CONCAT('proxy-', LAST_INSERT_ID()) FROM %s",
                                   $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE']);
                    $item_id = $dbSocket->getOne($sql);
                    
                    $successMsg = sprintf('Successfully inserted new proxy in db [<a href="mng-rad-proxys-edit.php?item=%s">Edit</a>]',
                                          urlencode($item_id), $item_id);
                    $logAction .= "Successfully inserted new proxy (item id: $item_id) in db";
                    
                    // write file
                    if (isset($configValues['CONFIG_FILE_RADIUS_PROXY'])) {
                        $filenameRealmsProxys = $configValues['CONFIG_FILE_RADIUS_PROXY'];
                        $fileFlag = 1;
                    } else {
                        $filenameRealmsProxys = "";
                        $fileFlag = 0;
                    }
                    
                    if (!(file_exists($filenameRealmsProxys))) {
                        $logAction .= "Failed non-existed realm configuration file [$filenameRealmsProxys] on page: ";
                        $failureMsg = "the file $filenameRealmsProxys doesn't exist, I can't save realm information to the file";
                        $fileFlag = 0;
                    }

                    if (!(is_writable($filenameRealmsProxys))) {
                        $logAction .= "Failed writing realm configuration to file [$filenameRealmsProxys] on page: ";
                        $failureMsg = "the file $filenameRealmsProxys isn't writable, I can't save realm information to the file";
                        $fileFlag = 0;
                    }
                    
                    /*******************************************************************/
                    /* enumerate from database all proxy entries */
                    include_once('include/management/saveRealmsProxys.php');
                    /*******************************************************************/
                } else {
                    $failureMsg = "Failed to insert new realm in db";
                    $logAction .= "$failureMsg on page: ";
                }
                
            }
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }
    
    
    // print HTML prologue
    $title = t('Intro','mngradproxysnew.php');
    $help = t('helpPage','mngradproxysnew');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    if (!isset($successMsg)) {
        
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
