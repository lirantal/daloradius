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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            include('../common/includes/db_open.php');

            $current_password = (isset($_POST['current_password']) && !empty(trim($_POST['current_password']))) ? trim($_POST['current_password']) : "";

            if (empty($current_password)) {
                $numrows = 0;
            } else {
                // check if current password is valid
                $sql = sprintf("SELECT COUNT(id) FROM %s WHERE username='%s' AND portalloginpassword='%s'",
                               $configValues['CONFIG_DB_TBL_DALOUSERINFO'], $dbSocket->escapeSimple($login_user),
                               $dbSocket->escapeSimple($current_password));

                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                $numrows = intval($res->fetchRow()[0]);
            }

            if ($numrows === 1) {

                $new_password1 = (isset($_POST['new_password1']) && !empty(trim($_POST['new_password1']))) ? trim($_POST['new_password1']) : "";
                $new_password2 = (isset($_POST['new_password2']) && !empty(trim($_POST['new_password2']))) ? trim($_POST['new_password2']) : "";

                $error = false;
                if (empty($new_password1)) {
                    $error = true;
                    $failureMsg = "The new password you provided is empty or invalid";
                } else if (empty($new_password2)) {
                    $error = true;
                    $failureMsg = "The new password (confirmation) you provided is empty or invalid";
                } else if ($new_password1 !== $new_password2) {
                    $error = true;
                    $failureMsg = "Password and password (confirmation) should match";
                }

                if (!$error) {
                    $sql = sprintf("UPDATE %s SET portalloginpassword='%s' WHERE username='%s'",
                                   $configValues['CONFIG_DB_TBL_DALOUSERINFO'], $dbSocket->escapeSimple($new_password1),
                                   $dbSocket->escapeSimple($login_user));
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";

                    if (!DB::isError($res)) {
                        // success
                        $successMsg = "The password for logging into the user portal has been changed";
                        $logAction = "User $login_user has changed their password for logging into the user portal";
                    } else {
                        // failed
                        $failureMsg = "Something went wrong while attempting to change your password for logging into the user portal.";
                        $logAction = "User $login_user failed to change their password for logging into the user portal [db error]";
                    }
                }

            } else {
                // wrong password
                $failureMsg = "In order to proceed you have to correctly provide your current password for logging into the user portal.";
                $logAction = "Wrong current password provided by user $login_user while attempting to change their password for logging into the user portal";
            }

            include('../common/includes/db_close.php');

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }


    // print HTML prologue
    $title = t('Intro','prefpasswordedit.php');
    $help = t('helpPage','prefpasswordedit');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    $input_descriptors0 = array();

    $input_descriptors0[] = array(
                                    "name" => "current_password",
                                    "caption" => t('all','CurrentPassword'),
                                    "type" => "password",
                                 );

    $input_descriptors0[] = array(
                                    "name" => "new_password1",
                                    "caption" => t('all','NewPassword'),
                                    "type" => "password",
                                 );

    $input_descriptors0[] = array(
                                    "name" => "new_password2",
                                    "caption" => t('all','VerifyPassword'),
                                    "type" => "password",
                                 );

    $input_descriptors0[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );

    $input_descriptors0[] = array(
                                    "type" => "button",
                                    "name" => "submit",
                                    "value" => "Change portal login password",
                                    "onclick" => "return verifyPassword('new_password1', 'new_password2')",
                                 );

    // open form
    open_form();

    foreach ($input_descriptors0 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_form();

    $inline_extra_js = <<<EOF
function verifyPassword(passwordStr1, passwordStr2) {

    objPasswordStr1 = document.getElementById(passwordStr1);
    objPassword1Val = objPasswordStr1.value;
    objPasswordStr2 = document.getElementById(passwordStr2);
    objPassword2Val = objPasswordStr2.value;

    if (objPassword1Val == objPassword2Val) {
        document.forms[0].submit();
    } else {
        alert("Passwords do not match, please re-type your new password and verify it");
        return false;
    }
}
EOF;

    include('include/config/logging.php');

    print_footer_and_html_epilogue($inline_extra_js);
