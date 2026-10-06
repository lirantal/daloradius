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
 * Authors:    Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/library/attributes.php') !== false) {
    header("Location: ../index.php");
    exit;
}

function is_group($user_or_group) {
    return strtolower(trim($user_or_group)) === 'group';
}

// dalo_cleartext_password_attributes() and dalo_cleartext_password_allowed()
// live in common/includes/validation.php, which every caller of this file
// includes beforehand.

function is_passwordlike_attribute($attribute) {
    return preg_match("/-Password$/", $attribute) === 1;
}

/**
 * Hashes the password attribute if applicable.
 *
 * If the attribute indicates a password (ends with "-Password"), 
 * this function hashes the provided value according to the hashing method.
 *
 * @param string $attribute The attribute to hash.
 * @param string $value The value to hash.
 * @return string|bool The hashed version of the value, or false if not applicable.
 */
function hashPasswordAttribute($attribute, $value) {
    if (!is_passwordlike_attribute($attribute)) {
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

/**
 * Checks if a specific attribute is already present in the database table.
 *
 * @param PDO $dbSocket The PDO database connection.
 * @param string $table The name of the database table to query.
 * @param string $param The parameter to compare in the database table.
 * @param string $subject The subject to match in the database table.
 * @param string $attribute The attribute to match in the database table.
 * @param string $op The operator to match in the database table.
 * @param string $value The value to match in the database table.
 * @return bool True if the attribute is already present, otherwise false.
 */
function is_attribute_already_present(PDO $dbSocket, $table, $param, $subject, $attribute, $op, $value) {
    global $logDebugSQL, $configValues;

    require_once __DIR__ . '/attributes_pdo.php';
    return dalo_attribute_exists_pdo($dbSocket, $configValues, $table, $param,
                                     $subject, $attribute, $op, $value);
}

/**
 * Determine the appropriate database table based on the user or group parameter.
 *
 * @param string $user_or_group The type of entity (user or group).
 * @param string $table The name of the database table.
 * @return string The name of the appropriate database table.
 */
function get_table_name($user_or_group, $table) {
    global $configValues;

    $is_reply_table = mb_strpos(strtolower(trim($table)), 'reply') !== false;

    // Determine the appropriate table based on the user or group parameter
    if (is_group($user_or_group)) {
        // If 'group' is provided, use group-specific tables
        $key = ($is_reply_table) ? 'CONFIG_DB_TBL_RADGROUPREPLY' : 'CONFIG_DB_TBL_RADGROUPCHECK';
    } else {
        // If 'user' or any other value is provided, use user-specific tables
        $key = ($is_reply_table) ? 'CONFIG_DB_TBL_RADREPLY' : 'CONFIG_DB_TBL_RADCHECK';
    }

    return $configValues[$key];
}

// iterates through $_POST for retrieving attributes to be inserted (or updated) in the db.
// $dbSocket db connector
// $subject is the username/groupname
// $skipList is an array containing $_POST param to avoid checking.
// $insert_only is a boolean. if set to true ignore update requests and force only insert queries
// $user_or_group could be 'user' for user attributes or 'group' for group attributes

//
// returns an array of prepared attributes
function handleAttributes(PDO $dbSocket, $subject, $skipList, $insert_only=true, $user_or_group='user') {
    global $configValues, $valid_ops, $logDebugSQL;

    require_once __DIR__ . '/attributes_pdo.php';
    return dalo_handle_attributes_pdo($dbSocket, $configValues, $_POST, $subject,
                                      $skipList, $valid_ops, $insert_only, $user_or_group);
}
