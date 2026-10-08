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

    // if $attribute is a Password attribute,
    // return an hashed version of $value
    // otherwise false
    // if $attribute refers to a non-supported
    // hashing method, it just returns $value
    function hashPasswordAttribute($attribute, $value) {
        if (preg_match("/-Password$/", $attribute) !== 1) {
            return false;
        }
        
        switch ($attribute) {
            case "Crypt-Password":
                // crypt() picks the algorithm from the salt prefix. A salt that does not
                // start with $ selects traditional DES: 8-character truncation and, here,
                // a salt shared by every user. Use SHA-512 crypt with a per-user salt.
                return crypt($value, '$6$' . bin2hex(random_bytes(8)) . '$');
                
            case "MD5-Password":
                return strtoupper(md5($value));
            
            case "SHA1-Password":
                return sha1($value);

            case "SHA2-Password":
                return hash('sha256', $value);
            
            case "NT-Password":
                return strtoupper(bin2hex(mhash(MHASH_MD4, iconv('UTF-8', 'UTF-16LE', $value))));

            default:
            // TODO: Add support for CHAP-Password.
            case "User-Password":
            case "Cleartext-Password":
                return $value;
        }
    }

    function dalo_auth_password_table(array $config) {
        $name = $config['CONFIG_DB_TBL_RADCHECK'] ?? null;
        if (!is_string($name) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $name)) {
            throw new InvalidArgumentException('Invalid RADIUS check table');
        }
        return '`' . $name . '`';
    }

    function dalo_auth_password_innodb(PDO $pdo, $table) {
        $check = $pdo->prepare('SELECT ENGINE FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
        $check->execute(array(trim($table, '`')));
        if (strcasecmp((string) $check->fetchColumn(), 'InnoDB') !== 0) {
            throw new RuntimeException('RADIUS password change requires a transactional table');
        }
    }

    function has_password_like_attributes(PDO $pdo, $table, $username) {
        $stmt = $pdo->prepare("SELECT COUNT(id) FROM $table WHERE op=':=' AND username=? AND attribute LIKE '%-Password'");
        $stmt->execute(array($username));
        return (int) $stmt->fetchColumn() > 0;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])
            && dalo_check_csrf_token($_POST['csrf_token'])) {
            $current_password = (isset($_POST['current_password']) && is_string($_POST['current_password'])
                && trim($_POST['current_password']) !== '') ? trim($_POST['current_password']) : '';
            $new_password1 = (isset($_POST['new_password1']) && is_string($_POST['new_password1'])
                && trim($_POST['new_password1']) !== '') ? trim($_POST['new_password1']) : '';
            $new_password2 = (isset($_POST['new_password2']) && is_string($_POST['new_password2'])
                && trim($_POST['new_password2']) !== '') ? trim($_POST['new_password2']) : '';

            // The legacy page does not mutate or display a message for a blank
            // current password. Keep the same form behavior.
            if ($current_password !== '' && $current_password !== '0') {
                if ($new_password1 === '' || $new_password1 === '0') {
                    $failureMsg = "The new password you provided is empty or invalid";
                } else if ($new_password2 === '' || $new_password2 === '0') {
                    $failureMsg = "The new password (confirmation) you provided is empty or invalid";
                } else if ($new_password1 !== $new_password2) {
                    $failureMsg = "Password and password (confirmation) should match";
                } else {
                    $pdo = null;
                    try {
                        require_once('../common/includes/pdo_connection.php');
                        $table = dalo_auth_password_table($configValues);
                        $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                        dalo_auth_password_innodb($pdo, $table);
                        if (!$pdo->beginTransaction()) {
                            throw new RuntimeException('RADIUS password transaction unavailable');
                        }
                        if (has_password_like_attributes($pdo, $table, $login_user)) {
                            $stmt = $pdo->prepare("SELECT id,attribute,value FROM $table WHERE op=':=' "
                                                . "AND username=? AND attribute LIKE '%-Password' ORDER BY id FOR UPDATE");
                            $stmt->execute(array($login_user));
                            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $update = $pdo->prepare("UPDATE $table SET value=? WHERE id=? AND username=? "
                                                  . "AND op=':=' AND attribute=? AND BINARY value=BINARY ?");
                            $count = 0;
                            foreach ($rows as $row) {
                                $password_type = $row['attribute'];
                                $password_value = $row['value'];
                                if (!is_string($password_value)) {
                                    continue;
                                }
                                if ($password_type === 'Crypt-Password') {
                                    $verified = hash_equals($password_value,
                                                            crypt($current_password, $password_value));
                                } else {
                                    $current_hash = hashPasswordAttribute($password_type, $current_password);
                                    $verified = $current_hash !== false
                                        && hash_equals($password_value, (string) $current_hash);
                                }
                                if (!$verified) {
                                    continue;
                                }
                                $new_hash = hashPasswordAttribute($password_type, $new_password1);
                                if (!is_string($new_hash)) {
                                    throw new RuntimeException('RADIUS password hash failed');
                                }
                                if (!hash_equals($password_value, $new_hash)) {
                                    $update->execute(array($new_hash, (int) $row['id'], $login_user,
                                                           $password_type, $password_value));
                                    if ($update->rowCount() !== 1) {
                                        throw new RuntimeException('RADIUS password changed concurrently');
                                    }
                                }
                                $count++;
                            }
                            if ($count > 0) {
                                if (!$pdo->commit()) {
                                    throw new RuntimeException('RADIUS password commit failed');
                                }
                                $successMsg = "$count auth password(s) have been changed";
                                $logAction = "User $login_user has changed their auth password(s) [num. $count]";
                            } else {
                                $pdo->rollBack();
                                $failureMsg = "Something went wrong while attempting to change your auth password(s)";
                                $logAction = "User $login_user failed to change their auth password(s)";
                            }
                        } else {
                            $pdo->rollBack();
                        }
                    } catch (Throwable $exception) {
                        if ($pdo instanceof PDO && $pdo->inTransaction()) {
                            $pdo->rollBack();
                        }
                        // No SQL text, bound credential or driver message is logged.
                        error_log('RADIUS password change failure: ' . get_class($exception));
                        $failureMsg = "Something went wrong while attempting to change your auth password(s)";
                        $logAction = "User $login_user failed to change their auth password(s) [db error]";
                    } finally {
                        $pdo = null;
                    }
                }
            }
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
                                    "value" => "Change authentication password(s)",
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
