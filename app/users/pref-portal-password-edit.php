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
    include_once('../common/includes/portal_password.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])
            && dalo_check_csrf_token($_POST['csrf_token'])) {
            $current_password = (isset($_POST['current_password']) &&
                                 dalo_portal_password_is_acceptable($_POST['current_password']))
                              ? trim($_POST['current_password']) : "";
            $lookup_error = false;
            $numrows = 0;
            $row = null;
            $verification = array('verified' => false);
            $pdo = null;
            $table = null;

            if ($current_password !== '') {
                try {
                    require_once('../common/includes/pdo_connection.php');
                    $tableName = $configValues['CONFIG_DB_TBL_DALOUSERINFO'] ?? null;
                    if (!is_string($tableName) ||
                        !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $tableName)) {
                        throw new InvalidArgumentException('Invalid portal user table');
                    }
                    $table = '`' . $tableName . '`';
                    $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                    $stmt = $pdo->prepare("SELECT id, portalloginpassword FROM $table WHERE username=? LIMIT 2");
                    $stmt->execute(array($login_user));
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $numrows = count($rows);
                    if ($numrows === 1) {
                        $row = $rows[0];
                        $verification = dalo_portal_password_verify(
                            $current_password, $row['portalloginpassword']);
                    }
                } catch (Throwable $exception) {
                    // Database exceptions can contain bound values: never log the message.
                    error_log('Portal password lookup failure: ' . get_class($exception));
                    $lookup_error = true;
                }
            }

            if ($lookup_error) {
                $failureMsg = "Something went wrong while checking your current portal password.";
                $logAction = "User $login_user failed to check their portal password [db error]";
            } else if ($numrows === 1 && $verification['verified']) {
                $new_password1 = (isset($_POST['new_password1']) &&
                                  dalo_portal_password_is_acceptable($_POST['new_password1']))
                               ? trim($_POST['new_password1']) : "";
                $new_password2 = (isset($_POST['new_password2']) &&
                                  dalo_portal_password_is_acceptable($_POST['new_password2']))
                               ? trim($_POST['new_password2']) : "";

                $error = false;
                if ($new_password1 === '') {
                    $error = true;
                    $failureMsg = "The new password you provided is empty or invalid";
                } else if ($new_password2 === '') {
                    $error = true;
                    $failureMsg = "The new password (confirmation) you provided is empty or invalid";
                } else if ($new_password1 !== $new_password2) {
                    $error = true;
                    $failureMsg = "Password and password (confirmation) should match";
                }

                if (!$error) {
                    $new_hash = dalo_portal_password_hash($new_password1);
                    if ($new_hash === false) {
                        $failureMsg = "Something went wrong while attempting to change your password for logging into the user portal.";
                        $logAction = "User $login_user failed to hash their new portal password";
                    } else {
                        try {
                            // Compare the exact old bytes: a reset after verification must win.
                            $affected_rows = dalo_portal_password_update(
                                $pdo, $table, $row, $login_user, $new_hash);
                            if ($affected_rows === 1) {
                                $successMsg = "The password for logging into the user portal has been changed";
                                $logAction = "User $login_user has changed their password for logging into the user portal";
                            } else if ($affected_rows === 0) {
                                $failureMsg = "Your portal password changed while this request was being processed. Please enter the current password again and retry.";
                                $logAction = "User $login_user did not change their portal password [concurrent update]";
                            } else {
                                $failureMsg = "Something went wrong while attempting to change your password for logging into the user portal.";
                                $logAction = "User $login_user failed to change their password for logging into the user portal [unexpected affected rows]";
                            }
                        } catch (Throwable $exception) {
                            error_log('Portal password update failure: ' . get_class($exception));
                            $failureMsg = "Something went wrong while attempting to change your password for logging into the user portal.";
                            $logAction = "User $login_user failed to change their password for logging into the user portal [db error]";
                        }
                    }
                }
            } else {
                $failureMsg = "In order to proceed you have to correctly provide your current password for logging into the user portal.";
                $logAction = "Wrong current password provided by user $login_user while attempting to change their password for logging into the user portal";
            }
            $pdo = null;
        } else {
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
