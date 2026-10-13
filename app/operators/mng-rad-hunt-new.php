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

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    require_once __DIR__ . '/library/huntgroup_pages_pdo.php';
    $groupname=$nasipaddress=$nasportid='';
    if (($_SERVER['REQUEST_METHOD'] ?? '')==='POST') {
        if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) || !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg='CSRF token error';
        } else {
            try {
                $fields=dalo_hunt_fields($_POST);
                $groupname=$fields['groupname']; $nasipaddress=$fields['nasipaddress']; $nasportid=$fields['nasportid'];
                $pdo=dalo_pdo_connect($configValues,$_SESSION['location_name'] ?? 'default');
                $item_id=dalo_hunt_save($pdo,$configValues,$fields);
                if ($item_id===false) { $failureMsg=sprintf('The chosen %s/%s pair is already contained in a group',t('all','HgIPHost'),t('all','HgPortId')); }
                else {
                    $successMsg=sprintf('Successfully added a new huntgroup item (item id: %s) [<a href="mng-rad-hunt-edit.php?item=%s" title="Edit">Edit</a>]',
                                        htmlspecialchars($item_id,ENT_QUOTES,'UTF-8'),rawurlencode($item_id));
                    $logAction.='Successfully added a new huntgroup item on page: ';
                }
            } catch (Throwable $e) { $failureMsg='Unable to create Huntgroup item'; }
        }
    }

    // print HTML prologue    
    $title = t('Intro','mngradhuntnew.php');
    $help = t('helpPage','mngradhuntnew');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');

    if (!isset($successMsg)) {
        
        // descriptors 0
        $input_descriptors0 = array();
        
        $input_descriptors0[] = array(
                                        'name' => 'nasipaddress',
                                        'caption' => t('all','HgIPHost'),
                                        'type' => 'text',
                                        'value' => (isset($nasipaddress) ? $nasipaddress : ""),
                                        'pattern' => trim(IP_REGEX, '/'),
                                        'required' => true,
                                     );
                                     
        $input_descriptors0[] = array(
                                        'name' => 'groupname',
                                        'caption' => t('all','HgGroupName'),
                                        'type' => 'text',
                                        'value' => (isset($groupname) ? $groupname : ""),
                                        'required' => true,
                                     );
                                     
        $input_descriptors0[] = array(
                                        'name' => 'nasportid',
                                        'caption' => t('all','HgPortId'),
                                        'type' => 'text',
                                        'value' => (isset($nasportid) ? $nasportid : ""),
                                     );
        // descriptors 1
        $input_descriptors1 = array();
        
        $input_descriptors1[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );
        
        $input_descriptors1[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                      );
    
        open_form();
        
        // fieldset 0
        $fieldset0_descriptor = array(
                                        "title" => t('title','HGInfo'),
                                     );
                                     
        open_fieldset($fieldset0_descriptor);
        
        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_form();
    
    }
    
    print_back_to_previous_page();
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
