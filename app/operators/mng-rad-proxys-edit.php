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
    include_once("include/management/populate_selectbox.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";
    
    // load valid proxies
    $valid_proxynames = get_proxies();


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $item = (array_key_exists('item', $_POST) && !empty(str_replace("%", "", trim($_POST['item']))))
              ? str_replace("%", "", trim($_POST['item'])) : "";
    } else {
        $item = (array_key_exists('item', $_REQUEST) && !empty(str_replace("%", "", trim($_REQUEST['item']))))
              ? str_replace("%", "", trim($_REQUEST['item'])) : "";
    }

    $exists = in_array($item, array_keys($valid_proxynames));
    
    if (!$exists) {
        // we reset the rate if it does not exist
        $item = "";
        $internal_id = "";
    } else {
        $internal_id = intval(str_replace("proxy-", "", $item));
    }

    //feed the sidebar variables
    $selected_proxy = $item;

    include('../common/includes/db_open.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            if (empty($internal_id)) {
                // required
                $failureMsg = sprintf("Selected an empty/invalid proxy");
                $logAction .= "$failureMsg on page: ";
            } else {
                
                $proxyname = (array_key_exists('proxyname', $_POST) && !empty(str_replace("%", "", trim($_POST['proxyname']))))
                           ? str_replace("%", "", trim($_POST['proxyname'])) : "";
                           
                if (empty($proxyname)) {
                    // required
                    $failureMsg = sprintf("Empty/invalid %s", t('all','ProxyName'));
                    $logAction .= "$failureMsg on page: ";
                } else {
                    $sql = sprintf("SELECT COUNT(id)
                                      FROM %s
                                     WHERE proxyname=? AND id<>?", $configValues['CONFIG_DB_TBL_DALOPROXYS']);
                    $prep = $dbSocket->prepare($sql);
                    $values = array( $proxyname, $internal_id, );
                    $res = $dbSocket->execute($prep, $values);
                    $logDebugSQL .= "$sql;\n";

                    $exists = $res->fetchrow()[0] > 0;
                    
                    if ($exists) {
                        // invalid
                        $failureMsg = sprintf("The chosen %s is already in use", t('all','ProxyName'));
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
                        
                        $sql = sprintf("UPDATE %s
                                           SET retry_delay=?, retry_count=?, dead_time=?,
                                               default_fallback=?, updatedate=?, updateby=?
                                         WHERE proxyname=?", $configValues['CONFIG_DB_TBL_DALOPROXYS']);
                        $prep = $dbSocket->prepare($sql);
                        $values = array( $retry_delay, $retry_count, $dead_time, $default_fallback, $currDate, $currBy, $proxyname );
                        $res = $dbSocket->execute($prep, $values);
                        $logDebugSQL .= "$sql;\n";

                        if (!DB::isError($res)) {
                            $successMsg = "Successfully updated proxy";
                            $logAction .= "Successfully updated proxy [$proxyname] on page: ";
                            
                            // write file
                            if (isset($configValues['CONFIG_FILE_RADIUS_PROXY'])) {
                                $filenameRealmsProxys = $configValues['CONFIG_FILE_RADIUS_PROXY'];
                                $fileFlag = 1;
                            } else {
                                $filenameRealmsProxys = "";
                                $fileFlag = 0;
                            }
                            
                            if (!(file_exists($filenameRealmsProxys))) {
                                $logAction .= "Failed non-existed proxys configuration file [$filenameRealmsProxys] on page: ";
                                $failureMsg = "the file $filenameRealmsProxys doesn't exist, I can't save proxys information to the file";
                                $fileFlag = 0;
                            }

                            if (!(is_writable($filenameRealmsProxys))) {
                                $logAction .= "Failed writing proxys configuration to file [$filenameRealmsProxys] on page: ";
                                $failureMsg = "the file $filenameRealmsProxys isn't writable, I can't save proxys information to the file";
                                $fileFlag = 0;
                            }
                            
                            /*******************************************************************/
                            /* enumerate from database all proxy entries */
                            include_once('include/management/saveRealmsProxys.php');
                            /*******************************************************************/
                            
                            
                        } else {
                            $failureMsg = "Failed to update proxy";
                            $logAction .= "Failed to update proxy [$proxyname] on page: ";
                        }
                    }
                }
                
            }
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }
    
    if (empty($internal_id)) {
        $failureMsg = sprintf("Selected an empty/invalid proxy item");
        $logAction .= "Failed updating this proxy (possible empty or invalid proxy item) on page: ";
    } else {
        $sql = sprintf("SELECT proxyname, retry_delay, retry_count, dead_time, default_fallback,
                               creationdate, creationby, updatedate, updateby
                          FROM %s
                         WHERE id=?", $configValues['CONFIG_DB_TBL_DALOPROXYS']);
        $prep = $dbSocket->prepare($sql);
        $values = array( $internal_id );
        $res = $dbSocket->execute($prep, $values);
        $logDebugSQL .= "$sql;\n";

        list(
                $proxyname, $retry_delay, $retry_count, $dead_time, $default_fallback,
                $creationdate, $creationby, $updatedate, $updateby
            ) = $res->fetchrow();
    }

    include('../common/includes/db_close.php');


    // print HTML prologue
    $title = t('Intro','mngradproxysedit.php');
    $help = t('helpPage','mngradproxysedit');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');

    if (!empty($internal_id)) {

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
                                        "name" => "item",
                                        "type" => "hidden",
                                        "value" => sprintf("proxy-%d", $internal_id),
                                     );

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
        
        // fieldset 1
        $fieldset1_descriptor = array(
                                        "title" => "Other Information",
                                     );

        open_fieldset($fieldset1_descriptor);

        foreach ($input_descriptors1 as $input_descriptor) {
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
