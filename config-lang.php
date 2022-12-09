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

    include_once('library/config_read.php');
    $log = "visited page: ";

    include("library/validation.php");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
        
            if (array_key_exists('CONFIG_LANG', $_POST) && !empty(strtolower(trim($_POST['CONFIG_LANG']))) &&
                in_array(strtolower(trim($_POST['CONFIG_LANG'])), array_keys($valid_languages))) {
                
                $configValues['CONFIG_LANG'] = $_POST['CONFIG_LANG'];
                include("library/config_write.php");
            }
        
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','configlang.php');
    $help = t('helpPage','configlang');
    
    print_html_prologue($title, $langCode);

    include("menu-config.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    
    $input_descriptors1 = array();
    
    $input_descriptors1[] = array(
                                    'name' => 'CONFIG_LANG',
                                    'type' => 'select',
                                    'caption' => t('all','PrimaryLanguage'),
                                    'options' => $valid_languages,
                                    'selected_value' => $configValues['CONFIG_LANG']
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
                                    "title" => t('title','Settings'),
                                 );

    open_form();
    
    open_fieldset($fieldset1_descriptor);

    foreach ($input_descriptors1 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
    
    close_fieldset();
    
    close_form();
   
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
