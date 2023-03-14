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
    $login_user = $_SESSION['login_user'];

    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    function can_change_userinfo($dbSocket, $username) {
        global $configValues, $logDebugSQL;

        $sql = sprintf("SELECT changeuserinfo FROM %s WHERE username='%s'",
                       $configValues['CONFIG_DB_TBL_DALOUSERINFO'], $dbSocket->escapeSimple($username));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        return intval($res->fetchrow()[0]) === 1;
    }

    include('../common/includes/db_open.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            if (can_change_userinfo($dbSocket, $login_user)) {

                $firstname = (array_key_exists('firstname', $_POST) && isset($_POST['firstname'])) ? $_POST['firstname'] : "";
                $lastname = (array_key_exists('lastname', $_POST) && isset($_POST['lastname'])) ? $_POST['lastname'] : "";
                $email = (array_key_exists('email', $_POST) && isset($_POST['email'])) ? $_POST['email'] : "";
                $department = (array_key_exists('department', $_POST) && isset($_POST['department'])) ? $_POST['department'] : "";
                $company = (array_key_exists('company', $_POST) && isset($_POST['company'])) ? $_POST['company'] : "";
                $workphone = (array_key_exists('workphone', $_POST) && isset($_POST['workphone'])) ? $_POST['workphone'] : "";
                $homephone = (array_key_exists('homephone', $_POST) && isset($_POST['homephone'])) ? $_POST['homephone'] : "";
                $mobilephone = (array_key_exists('mobilephone', $_POST) && isset($_POST['mobilephone'])) ? $_POST['mobilephone'] : "";
                $address = (array_key_exists('address', $_POST) && isset($_POST['address'])) ? $_POST['address'] : "";
                $city = (array_key_exists('city', $_POST) && isset($_POST['city'])) ? $_POST['city'] : "";
                $state = (array_key_exists('state', $_POST) && isset($_POST['state'])) ? $_POST['state'] : "";
                $country = (array_key_exists('country', $_POST) && isset($_POST['country'])) ? $_POST['country'] : "";
                $zip = (array_key_exists('zip', $_POST) && isset($_POST['zip'])) ? $_POST['zip'] : "";

                // update user information table
                $sql = sprintf("UPDATE %s SET firstname='%s', lastname='%s', email='%s', department='%s', company='%s', workphone='%s',
                                              homephone='%s', mobilephone='%s', address='%s', city='%s', state='%s', country='%s',
                                              zip='%s' WHERE username='%s'",
                               $configValues['CONFIG_DB_TBL_DALOUSERINFO'], $dbSocket->escapeSimple($firstname),
                               $dbSocket->escapeSimple($lastname), $dbSocket->escapeSimple($email),
                               $dbSocket->escapeSimple($department), $dbSocket->escapeSimple($company),
                               $dbSocket->escapeSimple($workphone), $dbSocket->escapeSimple($homephone),
                               $dbSocket->escapeSimple($mobilephone), $dbSocket->escapeSimple($address),
                               $dbSocket->escapeSimple($city), $dbSocket->escapeSimple($state),
                               $dbSocket->escapeSimple($country), $dbSocket->escapeSimple($zip),
                               $dbSocket->escapeSimple($username));

                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";

                if (!DB::isError($res)) {
                    // success
                    $successMsg = "User info have been updated";
                    $logAction = "User $login_user has updated their user info";
                } else {
                    // failed
                    $failureMsg = "Something went wrong while attempting to update your user info";
                    $logAction = "User $login_user failed to update their user info [db error]";
                }

            } else {
                // err
                $failureMsg = "You are not allowed to update your user info";
                $logAction = "User $login_user failed to update their user info [not allowed]";
            }



        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    $sql = sprintf("SELECT firstname, lastname, email, department, company, workphone, homephone, mobilephone,
                           address, city, state, country, zip
                      FROM %s WHERE username='%s'", $configValues['CONFIG_DB_TBL_DALOUSERINFO'],
                                                    $dbSocket->escapeSimple($login_user));
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";

    list( $ui_firstname, $ui_lastname, $ui_email, $ui_department, $ui_company, $ui_workphone, $ui_homephone,
          $ui_mobilephone, $ui_address, $ui_city, $ui_state, $ui_country, $ui_zip ) = $res->fetchRow();

    include('../common/includes/db_close.php');

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
