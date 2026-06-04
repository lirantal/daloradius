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
 * Description:    performs the logging-in authorization. First creates a random
 *                 session_id to be assigned to this session and then validates
 *                 the operators credentials in the database
 * 
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);
include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'sessions.php' ]);
include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'totp.php' ]);

dalo_session_start();

unset($_SESSION['operator_2fa_pending'], $_SESSION['operator_2fa_id'], $_SESSION['operator_2fa_user'], $_SESSION['operator_2fa_attempts']);

$errorMessage = '';

$location_name = (array_key_exists('location', $_POST) && isset($_POST['location']))
               ? $_POST['location']
               : "default";

$_SESSION['location_name'] = (array_key_exists('CONFIG_LOCATIONS', $configValues) &&
                              is_array($configValues['CONFIG_LOCATIONS']) &&
                              count($configValues['CONFIG_LOCATIONS']) > 0 &&
                              array_key_exists($location_name, $configValues['CONFIG_LOCATIONS']))
                           ? $location_name
                           : "default";

$_SESSION['daloradius_logged_in'] = false;

if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token']) &&
    array_key_exists('operator_user', $_POST) && isset($_POST['operator_user']) &&
    array_key_exists('operator_pass', $_POST) && isset($_POST['operator_pass'])) {

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);

    $operator_user = $dbSocket->escapeSimple($_POST['operator_user']);
    $operator_pass = $_POST['operator_pass'];

    $sqlFormat = "select * from %s where username='%s'";
    $sql = sprintf($sqlFormat, $configValues['CONFIG_DB_TBL_DALOOPERATORS'], $operator_user);
    $res = $dbSocket->query($sql);
    $numRows = $res->numRows();

    if ($numRows === 1) {
        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
        $stored_password = $row['password'];
        $verified = password_verify($operator_pass, $stored_password);
        $legacy_verified = (!$verified && hash_equals($stored_password, $operator_pass));

        $totp_enabled = array_key_exists('totp_enabled', $row) && intval($row['totp_enabled']) === 1;
        $totp_secret = (array_key_exists('totp_secret', $row) && !empty($row['totp_secret'])) ? $row['totp_secret'] : '';

        if ($verified || $legacy_verified) {
            $operator_id = intval($row['id']);

            if ($legacy_verified || password_needs_rehash($stored_password, PASSWORD_DEFAULT)) {
                $operator_pass_hash = $dbSocket->escapeSimple(password_hash($operator_pass, PASSWORD_DEFAULT));
                $sqlFormat = "update %s set password='%s' where username='%s'";
                $sql = sprintf($sqlFormat, $configValues['CONFIG_DB_TBL_DALOOPERATORS'], $operator_pass_hash, $operator_user);
                $res = $dbSocket->query($sql);
            }

            if ($totp_enabled && !empty($totp_secret)) {
                $_SESSION['operator_2fa_pending'] = true;
                $_SESSION['operator_2fa_id'] = $operator_id;
                $_SESSION['operator_2fa_user'] = $operator_user;
                $_SESSION['operator_2fa_attempts'] = 0;
                
                include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
                
                header('Location: login-otp.php');
                exit;
            }

            $_SESSION['daloradius_logged_in'] = true;
            unset($_SESSION['operator_2fa_pending'], $_SESSION['operator_2fa_id'], $_SESSION['operator_2fa_user'], $_SESSION['operator_2fa_attempts']);
            $_SESSION['operator_user'] = $operator_user;
            $_SESSION['operator_id'] = $operator_id;

            $now = date("Y-m-d H:i:s");
            $sqlFormat = "update %s set lastlogin='%s' where username='%s'";
            $sql = sprintf($sqlFormat, $configValues['CONFIG_DB_TBL_DALOOPERATORS'], $now, $operator_user);
            $res = $dbSocket->query($sql);
        }
    }

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
}

$header_location = "index.php";

if ($_SESSION['daloradius_logged_in'] !== true) {
    $header_location = "login.php";
    $_SESSION['operator_login_error'] = true;
}

header("Location: $header_location");
?>
