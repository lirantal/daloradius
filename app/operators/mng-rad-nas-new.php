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
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
        
            $nasname = (array_key_exists('nasname', $_POST) && !empty(str_replace("%", "", trim($_POST['nasname']))))
                     ? str_replace("%", "", trim($_POST['nasname'])) : "";
            $nassecret = (array_key_exists('nassecret', $_POST) && !empty(str_replace("%", "", trim($_POST['nassecret']))))
                       ? str_replace("%", "", trim($_POST['nassecret'])) : "";
            
            $nasname_enc = (!empty($nasname)) ? htmlspecialchars($nasname, ENT_QUOTES, 'UTF-8') : "";
            
            $nastype = (array_key_exists('nastype', $_POST) && isset($_POST['nastype']) &&
                        in_array($_POST['nastype'], $valid_nastypes)) ? $_POST['nastype'] : $valid_nastypes[0];
            
            $shortname = (array_key_exists('shortname', $_POST) && !empty(str_replace("%", "", trim($_POST['shortname']))))
                       ? str_replace("%", "", trim($_POST['shortname'])) : "";
            $nasports = (array_key_exists('nasports', $_POST) && !empty(trim($_POST['nasports'])) &&
                         intval(trim($_POST['nasports'])) >= 1 && intval(trim($_POST['nasports'])) <= 65535)
                      ? intval(trim($_POST['nasports'])) : "";
            
            $nasdescription = (array_key_exists('nasdescription', $_POST) && !empty(str_replace("%", "", trim($_POST['nasdescription']))))
                            ? str_replace("%", "", trim($_POST['nasdescription'])) : "";
            $nascommunity = (array_key_exists('nascommunity', $_POST) && !empty(str_replace("%", "", trim($_POST['nascommunity']))))
                          ? str_replace("%", "", trim($_POST['nascommunity'])) : "";
            $nasvirtualserver = (array_key_exists('nasvirtualserver', $_POST) && !empty(str_replace("%", "", trim($_POST['nasvirtualserver']))))
                              ? str_replace("%", "", trim($_POST['nasvirtualserver'])) : "";
            
            if (empty($nasname) || empty($nassecret)) {
                // required
                $failureMsg = sprintf("%s and/or %s are empty or invalid", t('all','NasIPHost'), t('all','NasSecret'));
                $logAction .= "Failed adding (possible empty user/pass) new operator on page: ";
            } else {
                include('../common/includes/db_open.php');
                
                $sql = sprintf("SELECT COUNT(id) FROM %s WHERE nasname='%s'", $configValues['CONFIG_DB_TBL_RADNAS'],
                                                                              $dbSocket->escapeSimple($nasname));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                
                $exists = $res->fetchrow()[0] > 0;
                
                if ($exists) {
                    // name already taken
                    $failureMsg = sprintf("This %s already exists: <b>%s</b>", t('all','NasIPHost'), $nasname_enc);
                    $logAction .= "Failed adding a new NAS [$nasname already exists] on page: ";
                } else {
                    
                    $sql = sprintf("INSERT INTO %s (id, nasname, shortname, type, ports, secret, server, community, description)
                                            VALUES (0, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $configValues['CONFIG_DB_TBL_RADNAS'],
                                   $dbSocket->escapeSimple($nasname), $dbSocket->escapeSimple($shortname), $dbSocket->escapeSimple($nastype),
                                   $dbSocket->escapeSimple($nasports), $dbSocket->escapeSimple($nassecret), $dbSocket->escapeSimple($nasvirtualserver),
                                   $dbSocket->escapeSimple($nascommunity), $dbSocket->escapeSimple($nasdescription));
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                    
                    if (!DB::isError($res)) {
                        $successMsg = sprintf('Successfully added a new NAS (<strong>%s</strong>) '
                                            . '<a href="mng-rad-nas-edit.php?nasname=%s" title="Edit">Edit</a>',
                                              $nasname_enc, urlencode($nasname_enc));
                        $logAction .= "Successfully added a new NAS [$nasname] on page: ";
                    } else {
                        // it seems that operator could not be added
                        $f = "Failed to add a new NAS [%s] to database";
                        $failureMsg = sprintf($f, $nasname_enc);
                        $logAction .= sprintf($f, $nasname);
                    }
                }
                
                include('../common/includes/db_close.php');
            }
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }


    // print HTML prologue
    $title = t('Intro','mngradnasnew.php');
    $help = t('helpPage','mngradnasnew');
    
    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');

    if (!isset($successMsg)) {

        // set form component descriptors
        $input_descriptors0 = array();
        
        $input_descriptors0[] = array(
                                        "name" => "nasname",
                                        "caption" => t('all','NasIPHost'),
                                        "type" => "text",
                                        "value" => ((isset($nasname)) ? $nasname : "")
                                     );
                                     
        $input_descriptors0[] = array(
                                        "name" => "nassecret",
                                        "caption" => t('all','NasSecret'),
                                        "type" => "text",
                                        "value" => ((isset($nassecret)) ? $nassecret : "")
                                     );
                                     
        $input_descriptors0[] = array(
                                        "name" => "nastype",
                                        "caption" => t('all','NasType'),
                                        "type" => "text",
                                        "datalist" => $valid_nastypes,
                                        "value" => ((isset($nastype)) ? $nastype : $valid_nastypes[0])
                                     );
                                     
        $input_descriptors0[] = array(
                                        "name" => "shortname",
                                        "caption" => t('all','NasShortname'),
                                        "type" => "text",
                                        "value" => ((isset($shortname)) ? $shortname : "")
                                     );

        
        $input_descriptors1 = array();
        
        $input_descriptors1[] = array(
                                        "name" => "nasports",
                                        "caption" => t('all','NasPorts'),
                                        "type" => "number",
                                        "min" => "1",
                                        "max" => "65535",
                                        "value" => ((isset($nasports)) ? $nasports : ""),
                                        "tooltipText" => "e.g. 1700, 3799, etc.",
                                     );
                                     
        $input_descriptors1[] = array(
                                        "name" => "nascommunity",
                                        "caption" => t('all','NasCommunity'),
                                        "type" => "text",
                                        "value" => ((isset($nascommunity)) ? $nascommunity : "")
                                     );
                                     
        $input_descriptors1[] = array(
                                        "name" => "nasvirtualserver",
                                        "caption" => t('all','NasVirtualServer'),
                                        "type" => "text",
                                        "value" => ((isset($nasvirtualserver)) ? $nasvirtualserver : "")
                                     );
                                     
        $input_descriptors1[] = array(
                                        "name" => "nasdescription",
                                        "caption" => t('all','NasDescription'),
                                        "type" => "textarea",
                                        "content" => ((isset($nasdescription)) ? $nasdescription : "")
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

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
