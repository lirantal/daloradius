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

    include ("library/checklogin.php");
    $login_user = $_SESSION['login_user'];

    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    require_once __DIR__ . '/library/portal_pages_pdo.php';
    $portalPdo = null;
    $portalInfo = array_fill(0, count(dalo_portal_fields()), '');
    try {
        $portalPdo = dalo_portal_handle($configValues);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? null;
            if (!is_string($token) || !dalo_check_csrf_token($token)) {
                $failureMsg = "CSRF token error";
            } else {
                try {
                    dalo_portal_update_userinfo($portalPdo, $configValues, $login_user, $_POST);
                    $successMsg = "User info have been updated";
                    $logAction = "User has updated their user info";
                } catch (DomainException $exception) {
                    $failureMsg = "You are not allowed to update your user info";
                }
            }
        }
        $portalInfo = dalo_portal_userinfo($portalPdo, $configValues, $login_user);

    } catch (Throwable $exception) {
        // A committed write and failed subsequent display are not a rolled-back write.
        $failureMsg = isset($successMsg) ? "User info updated; display is unavailable" :
            "Something went wrong while attempting to update your user info";
        $logAction = 'Portal information unavailable [' . get_class($exception) . ']';
    } finally { $portalPdo = null; }
    list($ui_firstname, $ui_lastname, $ui_email, $ui_department, $ui_company, $ui_workphone,
         $ui_homephone, $ui_mobilephone, $ui_address, $ui_city, $ui_state, $ui_country, $ui_zip) = $portalInfo;

    // print HTML prologue
    $title = t('Intro','prefuserinfoedit.php');
    $help = t('helpPage','prefuserinfoedit');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    // open form
    open_form();

    include_once('include/management/userinfo.php');

    $input_descriptors0 = array();

    $input_descriptors0[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );

    $input_descriptors0[] = array(
                                    "type" => "submit",
                                    "name" => "submit",
                                    "value" => "Update user info",
                                 );
                                 
    foreach ($input_descriptors0 as $input_descriptor) {
    print_form_component($input_descriptor);
}

    close_form();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
