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

    // load valid realmnames
    $valid_realmnames = get_realms();

    $valid_types = array( "fail-over", "load-balance", "client-balance", "client-port-balance", "keyed-balance" );

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $realmname = (array_key_exists('realmname', $_POST) && !empty(str_replace("%", "", trim($_POST['realmname']))))
                   ? str_replace("%", "", trim($_POST['realmname'])) : "";
    } else {
        $realmname = (array_key_exists('realmname', $_REQUEST) && !empty(str_replace("%", "", trim($_REQUEST['realmname']))))
                   ? str_replace("%", "", trim($_REQUEST['realmname'])) : "";
    }

    $exists = in_array($realmname, array_keys($valid_realmnames));

    
    if (!$exists) {
        $realmname = "";
    }
    
    //feed the sidebar variables
    $selected_realmname = $realmname;

    include('../common/includes/db_open.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            
            $type = (array_key_exists('type', $_POST) && !empty(trim($_POST['type'])) && in_array(trim($_POST['type']), $valid_types))
                  ? trim($_POST['type']) : $valid_types[0];
            $nostrip = (array_key_exists('nostrip', $_POST) && strtolower(trim($_POST['nostrip'])) === "yes");
            $authhost = (array_key_exists('authhost', $_POST) && !empty(trim($_POST['authhost']))) ? trim($_POST['authhost']) : "";
            $accthost = (array_key_exists('accthost', $_POST) && !empty(trim($_POST['accthost']))) ? trim($_POST['accthost']) : "";
            $secret = (array_key_exists('secret', $_POST) && !empty(trim($_POST['secret']))) ? trim($_POST['secret']) : "";
            
            $ldflag = (array_key_exists('ldflag', $_POST) && !empty(trim($_POST['ldflag']))) ? trim($_POST['ldflag']) : "";
            $hints = (array_key_exists('hints', $_POST) && !empty(trim($_POST['hints']))) ? trim($_POST['hints']) : "";
            $notrealm = (array_key_exists('notrealm', $_POST) && !empty(trim($_POST['notrealm']))) ? trim($_POST['notrealm']) : "";
            
            if (empty($realmname)) {
                // required
                $failureMsg = sprintf("Empty/invalid %s", t('all','RealmName'));
                $logAction .= "$failureMsg on page: ";
            } else {                
                // required later
                $currDate = date('Y-m-d H:i:s');
                $currBy = $operator;
                
                $sql = sprintf("UPDATE %s SET type=?, authhost=?, accthost=?, secret=?, ldflag=?,
                                              nostrip=?, hints=?, notrealm=?, updatedate=?, updateby=?
                                        WHERE realmname=?", $configValues['CONFIG_DB_TBL_DALOREALMS']);
                $prep = $dbSocket->prepare($sql);
                $values = array( 
                                    $type, $authhost, $accthost, $secret, $ldflag,
                                    $nostrip, $hints, $notrealm, $currDate, $currBy, $realmname
                               );
                $res = $dbSocket->execute($prep, $values);
                $logDebugSQL .= "$sql;\n";
                
                if (!DB::isError($res)) {
                    $successMsg = "Successfully updated realm";
                    $logAction .= "Successfully updated realm [$realmname] on page: ";
                    
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
                    $failureMsg = "Failed to update proxy";
                    $logAction .= "Failed to update proxy [$proxyname] on page: ";
                }
            }
            
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }


    if (empty($realmname)) {
        $failureMsg = sprintf("Selected an empty/invalid realm item");
        $logAction .= "Failed updating this realm (possible empty or invalid realm item) on page: ";
    } else {
        $sql = sprintf("SELECT id, realmname, type, authhost, accthost, secret, ldflag, nostrip,
                               hints, notrealm, creationdate, creationby, updatedate, updateby
                          FROM %s
                         WHERE realmname=?", $configValues['CONFIG_DB_TBL_DALOREALMS']);
        $prep = $dbSocket->prepare($sql);
        $values = array( $realmname );
        $res = $dbSocket->execute($prep, $values);
        $logDebugSQL .= "$sql;\n";

        list(
                $id, $realmname, $type, $authhost, $accthost, $secret, $ldflag, $nostrip,
                $hints, $notrealm, $creationdate, $creationby, $updatedate, $updateby
            ) = $res->fetchrow();
    }

    include('../common/includes/db_close.php');


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
                                        "name" => "type",
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
