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

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');
    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    $param_label = array(
                            'CONFIG_IFACE_PASSWORD_HIDDEN' => t('all','PasswordHidden'),
                            'CONFIG_IFACE_TABLES_LISTING_NUM' => t('all','TablesListingNum'),
                            'CONFIG_IFACE_AUTO_COMPLETE' => t('all','AjaxAutoComplete')
                        );

    $invalid_input = array();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            // validate yes/no params
            foreach ($param_label as $param => $label) {
                if (array_key_exists($param, $_POST) && !empty(trim($_POST[$param])) &&
                    in_array(strtolower(trim($_POST[$param])), array("yes", "no"))) {
                    $configValues[$param] = $_POST[$param];
                } else {
                    $invalid_input[$param] = $param_label[$param];
                }
            }

            // validate other param
            if (
                    array_key_exists('CONFIG_IFACE_TABLES_LISTING', $_POST) &&
                    !empty(trim($_POST['CONFIG_IFACE_TABLES_LISTING'])) &&
                    intval(trim($_POST['CONFIG_IFACE_TABLES_LISTING'])) >= 1 &&
                    intval(trim($_POST['CONFIG_IFACE_TABLES_LISTING'])) <= 100
               ) {
                $configValues['CONFIG_IFACE_TABLES_LISTING'] = intval(trim($_POST['CONFIG_IFACE_TABLES_LISTING']));
            } else {
                $invalid_input['CONFIG_IFACE_TABLES_LISTING'] = t('all','TablesListing');
            }


            if (count($invalid_input) > 0) {
                $failureMsg = sprintf("Invalid input: [%s]", implode(", ", array_values($invalid_input)));
                $logAction .= "$failureMsg on page: ";
            } else {
                include("../common/includes/config_write.php");
            }

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }


    // print HTML prologue
    $title = t('Intro','configinterface.php');
    $help = t('helpPage','configinterface');

    print_html_prologue($title, $langCode);

    


    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    $fieldset0_descriptor = array(
                                    "title" => t('title','Settings')
                                 );


    $input_descriptors0 = array();

    foreach ($param_label as $name => $label) {
        $input_descriptors0[] = array(
                                        "type" => "select",
                                        "options" => array( "yes", "no" ),
                                        "caption" => $label,
                                        "name" => $name,
                                        "selected_value" => (!array_key_exists($name, $invalid_input)
                                                             ? $configValues[$name] : "yes")
                                     );
    }

    $input_descriptors0[] = array(
                                    "type" => "number",
                                    "caption" => t('all','TablesListing'),
                                    "name" => 'CONFIG_IFACE_TABLES_LISTING',
                                    "value" => (!array_key_exists('CONFIG_IFACE_TABLES_LISTING', $invalid_input)
                                                ? $configValues['CONFIG_IFACE_TABLES_LISTING'] : ""),
                                    "min" => 1,
                                    "max" => 100
                                 );

    $input_descriptors0[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );

    $input_descriptors0[] = array(
                                    'type' => 'submit',
                                    'name' => 'submit',
                                    'value' => t('buttons','apply')
                                 );

    open_form();

    // open 0-th fieldset
    open_fieldset($fieldset0_descriptor);

    foreach ($input_descriptors0 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_fieldset();

    close_form();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
