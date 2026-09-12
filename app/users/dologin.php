<?php
/*
 *******************************************************************************
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
 *******************************************************************************
 *
 * Description:    logs in users by validating credentials and checking
 *                 authorization in the database
 *
 * Authors:	       Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *******************************************************************************
 */

include('library/sessions.php');
include_once('../common/includes/config_read.php');
include_once('../common/includes/portal_password.php');
include_once('lang/main.php');

dalo_session_start();

$errorMessage = '';
$authenticated = false;

// we interact with the db, ONLY IF user provided both operator_user and operator_pass params
if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token']) &&
    array_key_exists('login_user', $_POST) && !empty($_POST['login_user']) &&
    array_key_exists('login_pass', $_POST) && is_string($_POST['login_pass']) && $_POST['login_pass'] !== '' &&
    array_key_exists('language', $_POST) && !empty(trim($_POST['language']))) {

    $language = strtolower(trim($_POST['language']));
    if (in_array($language, array_keys($users_valid_languages))) {
        $selectedLanguage = $language;
    } else {
        $selectedLanguage = 'en';
    }
    
    // 31536000 = 365 * 24 * 60 * 60
    setcookie('daloradius_language', $selectedLanguage, time() + 31536000);

    $login_user = $_POST['login_user'];
    $login_pass = $_POST['login_pass'];

    include('../common/includes/db_open.php');

    $sql = sprintf(
        "SELECT id, portalloginpassword FROM %s WHERE username=? AND enableportallogin=1 AND portalloginpassword IS NOT NULL AND portalloginpassword<>''",
        $configValues['CONFIG_DB_TBL_DALOUSERINFO']
    );
    $stmt = $dbSocket->prepare($sql);
    $res = $dbSocket->execute($stmt, array($login_user));
    $dbSocket->freePrepared($stmt);

    // We only accept one and only one user information record.
    if (!DB::isError($res) && $res->numRows() === 1) {
        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
        $res->free();
        $stored_password = $row['portalloginpassword'];
        $verification = dalo_portal_password_verify($login_pass, $stored_password);

        if ($verification['verified']) {
            $authenticated = true;
            session_regenerate_id(true);
            $_SESSION['logged_in'] = true;
            $_SESSION['login_user'] = $login_user;

            if ($verification['needs_rehash']) {
                $new_hash = dalo_portal_password_hash($login_pass);
                if ($new_hash !== false) {
                    $sql = sprintf(
                        "UPDATE %s SET portalloginpassword=? WHERE id=? AND portalloginpassword=?",
                        $configValues['CONFIG_DB_TBL_DALOUSERINFO']
                    );
                    dalo_portal_db_sensitive_call(
                        $dbSocket,
                        function() use ($dbSocket, $sql, $new_hash, $row, $stored_password) {
                            $stmt = $dbSocket->prepare($sql);
                            $res = $dbSocket->execute($stmt, array($new_hash, intval($row['id']), $stored_password));
                            $dbSocket->freePrepared($stmt);
                            return $res;
                        },
                        $error_handler
                    );
                }
            }
        }
    }

    include('../common/includes/db_close.php');

}

// if everything went fine logged_in session param has been set to true,
// so we can check it for deciding where and how redirect user browser
$header_location = "index.php";

if (!$authenticated) {
    $header_location = "login.php";
    $_SESSION['logged_in'] = false;
    unset($_SESSION['login_user']);
    $_SESSION['login_error'] = true;
}

header("Location: $header_location");
