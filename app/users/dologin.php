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

/* A configured table name is an identifier, never a bound value. */
function dalo_portal_login_table(array $config)
{
    $name = $config['CONFIG_DB_TBL_DALOUSERINFO'] ?? null;
    if (!is_string($name) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $name)) {
        throw new InvalidArgumentException('Invalid portal user table');
    }
    return '`' . $name . '`';
}

function dalo_portal_login_innodb(PDO $pdo, $table)
{
    $check = $pdo->prepare('SELECT ENGINE FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
    $check->execute(array(trim($table, '`')));
    if (strcasecmp((string) $check->fetchColumn(), 'InnoDB') !== 0) {
        throw new RuntimeException('Portal authentication requires a transactional table');
    }
}

/** Never overwrite a password or access flag changed after initial verification. */
function dalo_portal_login_rehash(PDO $pdo, $table, array $row, $oldHash, $newHash)
{
    $condition = dalo_portal_password_match_condition($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
    $sql = "UPDATE $table SET portalloginpassword=? WHERE id=? AND username=? "
         . "AND enableportallogin=1 AND $condition";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(array($newHash, (int) $row['id'], $row['username'], $oldHash))
        && $stmt->rowCount() === 1;
}

/** Recheck the authorized row under lock before opening a browser session. */
function dalo_portal_login_current(PDO $pdo, $table, array $row, $expectedHash)
{
    if (!$pdo->beginTransaction()) {
        return false;
    }
    try {
        $stmt = $pdo->prepare("SELECT username,enableportallogin,portalloginpassword "
                            . "FROM $table WHERE id=? FOR UPDATE");
        if (!$stmt->execute(array((int) $row['id']))) {
            throw new RuntimeException('Portal identity recheck failed');
        }
        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $current = count($matches) === 1 ? $matches[0] : null;
        $valid = $current !== null && (int) $current['enableportallogin'] === 1
            && is_string($current['username']) && is_string($current['portalloginpassword'])
            && hash_equals((string) $row['username'], $current['username'])
            && hash_equals($expectedHash, $current['portalloginpassword']);
        if (!$valid) {
            $pdo->rollBack();
            return false;
        }
        if (!$pdo->commit()) {
            throw new RuntimeException('Portal authentication commit failed');
        }
        return true;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

if (defined('DALORADIUS_PORTAL_LOGIN_TEST_ONLY')) {
    return;
}

dalo_session_start();

$errorMessage = '';
$authenticated = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$authentication_attempted = false;

$validCsrf = isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])
    && dalo_check_csrf_token($_POST['csrf_token']);
$loginFormSubmitted = $validCsrf
    && (array_key_exists('login_user', $_POST) || array_key_exists('login_pass', $_POST));
if ($loginFormSubmitted) {
    $authentication_attempted = true;
    $authenticated = false;
}

if ($loginFormSubmitted && isset($_POST['login_user'], $_POST['login_pass'], $_POST['language'])
    && is_string($_POST['login_user']) && !empty($_POST['login_user'])
    && is_string($_POST['login_pass']) && $_POST['login_pass'] !== ''
    && is_string($_POST['language']) && trim($_POST['language']) !== '') {
    $language = strtolower(trim($_POST['language']));
    $selectedLanguage = in_array($language, array_keys($users_valid_languages), true) ? $language : 'en';
    setcookie('daloradius_language', $selectedLanguage, time() + 31536000);

    $login_user = $_POST['login_user'];
    $login_pass = $_POST['login_pass'];
    $pdo = null;
    try {
        require_once __DIR__ . '/../common/includes/pdo_connection.php';
        $table = dalo_portal_login_table($configValues);
        $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        dalo_portal_login_innodb($pdo, $table);
        $stmt = $pdo->prepare("SELECT id,username,portalloginpassword FROM $table WHERE username=? "
                            . "AND enableportallogin=1 AND portalloginpassword IS NOT NULL "
                            . "AND portalloginpassword<>'' LIMIT 2");
        $stmt->execute(array($login_user));
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) !== 1) {
            dalo_portal_password_dummy_verify($login_pass);
        } else {
            $row = $rows[0];
            $stored_password = $row['portalloginpassword'];
            $verification = dalo_portal_password_verify($login_pass, $stored_password);
            if ($verification['verified']) {
                $expectedHash = $stored_password;
                $rehashOk = true;
                if ($verification['needs_rehash']) {
                    $newHash = dalo_portal_password_hash($login_pass);
                    if ($newHash !== false) {
                        $rehashOk = dalo_portal_login_rehash($pdo, $table, $row,
                                                              $stored_password, $newHash);
                        if ($rehashOk) {
                            $expectedHash = $newHash;
                        }
                    }
                }
                // A changed password, disabled portal or deleted row cannot
                // become an authenticated session after verification.
                if ($rehashOk && dalo_portal_login_current($pdo, $table, $row, $expectedHash)) {
                    if (!session_regenerate_id(true)) {
                        throw new RuntimeException('Portal session rotation failed');
                    }
                    $_SESSION['logged_in'] = true;
                    $_SESSION['login_user'] = $login_user;
                    $authenticated = true;
                }
            }
        }
    } catch (Throwable $exception) {
        // PDO exceptions can include bound values; never log their messages.
        error_log('Portal authentication failure: ' . get_class($exception));
        $authenticated = false;
    } finally {
        $pdo = null;
        unset($login_pass);
    }
}

// if everything went fine logged_in session param has been set to true,
// so we can check it for deciding where and how redirect user browser
$header_location = $authenticated ? "index.php" : "login.php";

if (!$authenticated && $authentication_attempted) {
    $_SESSION['logged_in'] = false;
    unset($_SESSION['login_user']);
    $_SESSION['login_error'] = true;
}

header("Location: $header_location");
