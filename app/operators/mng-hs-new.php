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

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            $macaddress = (array_key_exists('macaddress', $_POST) && isset($_POST['macaddress']) &&
                           (preg_match(MACADDR_REGEX, trim($_POST['macaddress'])) ||
                            preg_match(IP_REGEX, trim($_POST['macaddress']))))
                        ? trim($_POST['macaddress']) : "";

            $name = (array_key_exists('name', $_POST) && !empty(str_replace("%", "", trim($_POST['name']))))
                  ? str_replace("%", "", trim($_POST['name'])) : "";
            $name_enc = (!empty($name)) ? htmlspecialchars($name, ENT_QUOTES, 'UTF-8') : "";

            $geocode = (array_key_exists('geocode', $_POST) && !empty(trim($_POST['geocode']))) ? trim($_POST['geocode']) : "";
            $hotspot_type = (array_key_exists('hotspot_type', $_POST) && !empty(trim($_POST['hotspot_type']))) ? trim($_POST['hotspot_type']) : "";

            $ownername = (array_key_exists('ownername', $_POST) && !empty(trim($_POST['ownername']))) ? trim($_POST['ownername']) : "";
            $managername = (array_key_exists('managername', $_POST) && !empty(trim($_POST['managername']))) ? trim($_POST['managername']) : "";
            $emailmanager = (array_key_exists('emailmanager', $_POST) && !empty(trim($_POST['emailmanager'])) &&
                             filter_var(trim($_POST['emailmanager']), FILTER_VALIDATE_EMAIL)) ? trim($_POST['emailmanager']) : "";
            $emailowner = (array_key_exists('emailowner', $_POST) && !empty(trim($_POST['emailowner'])) &&
                           filter_var(trim($_POST['emailowner']), FILTER_VALIDATE_EMAIL)) ? trim($_POST['emailowner']) : "";
            $address = (array_key_exists('address', $_POST) && !empty(trim($_POST['address']))) ? trim($_POST['address']) : "";
            $company = (array_key_exists('company', $_POST) && !empty(trim($_POST['company']))) ? trim($_POST['company']) : "";
            $phone1 = (array_key_exists('phone1', $_POST) && !empty(trim($_POST['phone1']))) ? trim($_POST['phone1']) : "";
            $phone2 = (array_key_exists('phone2', $_POST) && !empty(trim($_POST['phone2']))) ? trim($_POST['phone2']) : "";

            $companyphone = (array_key_exists('companyphone', $_POST) && !empty(trim($_POST['companyphone']))) ? trim($_POST['companyphone']) : "";
            $companywebsite = (array_key_exists('companywebsite', $_POST) && !empty(trim($_POST['companywebsite']))) ? trim($_POST['companywebsite']) : "";
            $companyemail = (array_key_exists('companyemail', $_POST) && !empty(trim($_POST['companyemail'])) &&
                             filter_var(trim($_POST['companyemail']), FILTER_VALIDATE_EMAIL)) ? trim($_POST['companyemail']) : "";
            $companycontact = (array_key_exists('companycontact', $_POST) && !empty(trim($_POST['companycontact']))) ? trim($_POST['companycontact']) : "";

            include('../common/includes/db_open.php');

            if (empty($macaddress) || empty($name)) {
                // if statement returns false which means that the user has left an empty field for
                // either macaddress, name, or both

                $failureMsg = "IP/MAC address or name is empty or not valid";
                $logAction .= "Failed adding (possible empty IP/MAC addr and/or name) new HS on page: ";
            } else {
                $sql = sprintf("SELECT COUNT(id) FROM %s WHERE name='%s' OR mac='%s'",
                               $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'],
                               $dbSocket->escapeSimple($name), $dbSocket->escapeSimple($macaddress));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";

                $exists = $res->fetchrow()[0] > 0;

                if ($exists) {
                    // if statement returns false which means there is at least one HS
                    // in the database with the same name or macaddress

                    $failureMsg = sprintf("Name <strong>%s</strong> or IP/MAC address <strong>%s</strong> already exist in database", $name_enc, $macaddress);
                    $logAction .= "Failed adding new HS. Name or IP/MAC address already existing in database [$name, $macaddress] on page: ";
                } else {
                    $currDate = date('Y-m-d H:i:s');
                    $currBy = $_SESSION['operator_user'];

                    $sql = sprintf("INSERT INTO %s (id, name, mac, geocode, owner, email_owner, manager, email_manager, address,
                                                    company, phone1, phone2, type, companywebsite, companyemail, companycontact,
                                                    companyphone, creationdate, creationby, updatedate, updateby)
                                            VALUES (0, '%s', '%s', '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
                                                    '%s', '%s', '%s', NULL, NULL)", $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'],
                                   $dbSocket->escapeSimple($name), $dbSocket->escapeSimple($macaddress), $dbSocket->escapeSimple($geocode),
                                   $dbSocket->escapeSimple($ownername), $dbSocket->escapeSimple($emailowner), $dbSocket->escapeSimple($managername),
                                   $dbSocket->escapeSimple($emailmanager), $dbSocket->escapeSimple($address), $dbSocket->escapeSimple($company),
                                   $dbSocket->escapeSimple($phone1), $dbSocket->escapeSimple($phone2), $dbSocket->escapeSimple($hotspot_type),
                                   $dbSocket->escapeSimple($companywebsite), $dbSocket->escapeSimple($companyemail),
                                   $dbSocket->escapeSimple($companycontact), $dbSocket->escapeSimple($companyphone), $currDate, $currBy);

                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";

                    if (!DB::isError($res)) {
                        $successMsg = sprintf('Successfully added a new hotspot (<strong>%s</strong>) '
                                            . '<a href="mng-hs-edit.php?name=%s" title="Edit">Edit</a>',
                                              $name_enc, urlencode($name_enc));
                        $logAction .= "Successfully added a new hotspot [$name] on page: ";
                    } else {
                        // it seems that operator could not be added
                        $f = "Failed to add a new hostspot [%s] to database";
                        $failureMsg = sprintf($f, $name_enc);
                        $logAction .= sprintf($f, $name);
                    }
                }

            }

            include('../common/includes/db_close.php');
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }


    // print HTML prologue
    $title = t('Intro','mnghsnew.php');
    $help = t('helpPage','mnghsnew');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    if (!isset($successMsg)) {

        // set form component descriptors
        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        "name" => "name",
                                        "caption" => t('all','HotSpotName'),
                                        "type" => "text",
                                        "value" => ((isset($name)) ? $name : ""),
                                        "tooltipText" => t('Tooltip','hotspotNameTooltip')
                                     );

        $input_descriptors0[] = array(
                                        "name" => "macaddress",
                                        "caption" => t('all','MACAddress'),
                                        "type" => "text",
                                        "value" => ((isset($macaddress)) ? $macaddress : ""),
                                        "tooltipText" => t('Tooltip','hotspotMacaddressTooltip')
                                     );

        $input_descriptors0[] = array(
                                        "name" => "geocode",
                                        "caption" => t('all','Geocode'),
                                        "type" => "text",
                                        "value" => ((isset($geocode)) ? $geocode : ""),
                                        "tooltipText" => t('Tooltip','geocodeTooltip')
                                     );

        $input_descriptors1 = array();

        $input_descriptors1[] = array(
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                        "name" => "csrf_token"
                                     );

        $input_descriptors1[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                     );

        // set navbar stuff
        $navkeys = array( 'HotspotInfo', 'ContactInfo', );

        // print navbar controls
        print_tab_header($navkeys);

        // open form
        open_form();

        // open tab wrapper
        open_tab_wrapper();

        // open first tab (shown)
        open_tab($navkeys, 0, true);

        // open a fieldset
        $fieldset0_descriptor = array(
                                        "title" => t('title','HotspotInfo'),
                                     );

        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        close_tab($navkeys, 0);

        // open second tab
        open_tab($navkeys, 1);
        include_once('include/management/contactinfo.php');
        close_tab($navkeys, 1);

        // close tab wrapper
        close_tab_wrapper();

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_form();

    }

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
