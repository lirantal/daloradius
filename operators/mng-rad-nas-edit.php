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

    include('../common/includes/config_read.php');
    include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    include('../common/includes/db_open.php');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nasname = (array_key_exists('nasname', $_POST) && !empty(str_replace("%", "", trim($_POST['nasname']))))
                 ? str_replace("%", "", trim($_POST['nasname'])) : "";
    } else {
        $nasname = (array_key_exists('nasname', $_REQUEST) && !empty(str_replace("%", "", trim($_REQUEST['nasname']))))
                 ? str_replace("%", "", trim($_REQUEST['nasname'])) : "";
    }
    
    // check if this nas exists
    $sql = sprintf("SELECT COUNT(id) FROM %s WHERE nasname='%s'", $configValues['CONFIG_DB_TBL_RADNAS'],
                                                                  $dbSocket->escapeSimple($nasname));
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    $exists = $res->fetchrow()[0] == 1;
    
    if (!$exists) {
        // we reset the nasname if it does not exist
        $nasname = "";
    }
    
    
    // from now on, we can assume that nasname is valid
    $nasname_enc = (!empty($nasname)) ? htmlspecialchars($nasname, ENT_QUOTES, 'UTF-8') : "";
    
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            $secret = (array_key_exists('secret', $_POST) && !empty(str_replace("%", "", trim($_POST['secret']))))
                    ? str_replace("%", "", trim($_POST['secret'])) : "";
    
            $type = (array_key_exists('type', $_POST) && isset($_POST['type']) &&
                     in_array($_POST['type'], $valid_nastypes)) ? $_POST['type'] : $valid_nastypes[0];
            
            $shortname = (array_key_exists('shortname', $_POST) && !empty(str_replace("%", "", trim($_POST['shortname']))))
                       ? str_replace("%", "", trim($_POST['shortname'])) : "";
            $ports = (array_key_exists('ports', $_POST) && !empty(trim($_POST['ports'])) &&
                      intval(trim($_POST['ports'])) >= 1 && intval(trim($_POST['ports'])) <= 65535)
                   ? intval(trim($_POST['ports'])) : "";
            
            $description = (array_key_exists('description', $_POST) && !empty(str_replace("%", "", trim($_POST['description']))))
                         ? str_replace("%", "", trim($_POST['description'])) : "";
            $community = (array_key_exists('community', $_POST) && !empty(str_replace("%", "", trim($_POST['community']))))
                       ? str_replace("%", "", trim($_POST['community'])) : "";
            $server = (array_key_exists('server', $_POST) && !empty(str_replace("%", "", trim($_POST['server']))))
                           ? str_replace("%", "", trim($_POST['server'])) : "";
    
            if (empty($nasname) || empty($secret)) {
                // required
                $failureMsg = sprintf("%s and/or %s are empty or invalid", t('all','NasIPHost'), t('all','NasSecret'));
                $logAction .= sprintf("Failed editing NAS (%s) on page: ", $failureMsg);
            } else {
                
                $sql = sprintf("UPDATE %s SET shortname='%s', type='%s', ports=%d, secret='%s',
                                                  server='%s', community='%s', description='%s'
                                 WHERE nasname='%s'", $configValues['CONFIG_DB_TBL_RADNAS'],
                               $dbSocket->escapeSimple($shortname), $dbSocket->escapeSimple($type), $ports,
                               $dbSocket->escapeSimple($secret), $dbSocket->escapeSimple($server),
                               $dbSocket->escapeSimple($community), $dbSocket->escapeSimple($description),
                               $dbSocket->escapeSimple($nasname));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";

                if (!DB::isError($res)) {
                    $successMsg = sprintf("Edited NAS: <strong>%s</strong>", $nasname_enc);
                    $logAction .= sprintf("Successfully edited NAS [%s] on page: ", $nasname);
                } else {
                    // it seems that operator could not be added
                    $f = "Failed to add edit NAS [%s] to database";
                    $failureMsg = sprintf($f, $nasname_enc);
                    $logAction .= sprintf($f, $nasname);
                }
                
            }
    
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }
    
    
    if (!empty($nasname)) {
        $sql = sprintf("SELECT nasname, shortname, type, ports, secret, server, community, description
                          FROM %s WHERE nasname='%s' LIMIT 1", $configValues['CONFIG_DB_TBL_RADNAS'],
                                                               $dbSocket->escapeSimple($nasname));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        $row = $res->fetchrow();
        
        list($nasname, $shortname, $type, $ports, $secret, $server, $community, $description) = $row;
        
    } else {
        $failureMsg = sprintf("%s is invalid", t('all','NasIPHost'));
        $logAction .= sprintf("Requested editing invalid NAS (%s) on page: ", $failureMsg);
    }
    
    include('../common/includes/db_close.php');
    
    // print HTML prologue
    $title = t('Intro','mngradnasedit.php');
    $help = t('helpPage','mngradnasedit');
    
    print_html_prologue($title, $langCode);

    if (isset($nasname_enc)) {
        $title .= " :: $nasname_enc";
    } 

    print_title_and_help($title, $help);
    
    
    include_once('include/management/actionMessages.php');
    
    
    if (!empty($nasname)) {
        
        // set form component descriptors
        $input_descriptors0 = array();
                
        $input_descriptors0[] = array(
                                        "name" => "nasname",
                                        "type" => "hidden",
                                        "value" => ((isset($nasname)) ? $nasname : "")
                                     );
                                     
        $input_descriptors0[] = array(
                                        "name" => "nasname_presentation",
                                        "caption" => t('all','NasIPHost'),
                                        "type" => "text",
                                        "value" => ((isset($nasname)) ? $nasname : ""),
                                        "disabled" => true
                                     );
                                     
        $input_descriptors0[] = array(
                                        "name" => "secret",
                                        "caption" => t('all','NasSecret'),
                                        "type" => "text",
                                        "value" => ((isset($secret)) ? $secret : "")
                                     );
                                     
        $input_descriptors0[] = array(
                                        "name" => "type",
                                        "caption" => t('all','NasType'),
                                        "type" => "text",
                                        "datalist" => $valid_nastypes,
                                        "value" => ((isset($type)) ? $type : $valid_nastypes[0])
                                     );
                                     
        $input_descriptors0[] = array(
                                        "name" => "shortname",
                                        "caption" => t('all','NasShortname'),
                                        "type" => "text",
                                        "value" => ((isset($shortname)) ? $shortname : "")
                                     );

        
        $input_descriptors1 = array();
        
        $input_descriptors1[] = array(
                                        "name" => "ports",
                                        "caption" => t('all','NasPorts'),
                                        "type" => "number",
                                        "min" => "1",
                                        "max" => "65535",
                                        "value" => ((isset($ports)) ? $ports : ""),
                                        "tooltipText" => "e.g. 1700, 3799, etc.",
                                     );
                                     
        $input_descriptors1[] = array(
                                        "name" => "community",
                                        "caption" => t('all','NasCommunity'),
                                        "type" => "text",
                                        "value" => ((isset($community)) ? $community : "")
                                     );
                                     
        $input_descriptors1[] = array(
                                        "name" => "server",
                                        "caption" => t('all','NasVirtualServer'),
                                        "type" => "text",
                                        "value" => ((isset($server)) ? $server : "")
                                     );
                                     
        $input_descriptors1[] = array(
                                        "name" => "description",
                                        "caption" => t('all','NasDescription'),
                                        "type" => "textarea",
                                        "content" => ((isset($description)) ? $description : "")
                                     );

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

        // fieldset
        $fieldset0_descriptor = array(
                                        "title" => t('title','NASInfo'),
                                     );
                                     
        $fieldset1_descriptor = array(
                                        "title" => t('title','NASAdvanced'),
                                     );


        // set navbar stuff
        $navkeys = array( 'NASInfo', 'NASAdvanced' );

        // print navbar controls
        print_tab_header($navkeys);
        
        open_form();
        
        // open tab wrapper
        open_tab_wrapper();
        
        // open 0-th tab (shown)
        open_tab($navkeys, 0, true);
        
        // open 0-th fieldset
        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        close_tab($navkeys, 0);
        
        // open 1-st tab
        open_tab($navkeys, 1);
        
        // open 1-th fieldset
        open_fieldset($fieldset1_descriptor);
        
        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        close_tab($navkeys, 1);
        
        // close tab wrapper
        close_tab_wrapper();
        
        foreach ($input_descriptors2 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_form();
        
    }

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
    
?>
