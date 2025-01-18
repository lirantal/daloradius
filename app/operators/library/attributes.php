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
            return crypt($value, 'SALT_DALORADIUS');

        case "MD5-Password":
            return strtoupper(md5($value));

        case "SHA1-Password":
            return sha1($value);

        case "NT-Password":
            return strtoupper(bin2hex(mhash(MHASH_MD4, iconv('UTF-8', 'UTF-16LE', $value))));

        default:
        // TODO
        //~ case "CHAP-Password":
        case "User-Password":
        case "Cleartext-Password":
            return $value;
    }
}

/**
 * Checks if a specific attribute is already present in the database table.
 *
 * @param DB $dbSocket The DB database connection object.
 * @param string $table The name of the database table to query.
 * @param string $param The parameter to compare in the database table.
 * @param string $subject The subject to match in the database table.
 * @param string $attribute The attribute to match in the database table.
 * @param string $op The operator to match in the database table.
 * @param string $value The value to match in the database table.
 * @return bool True if the attribute is already present, otherwise false.
 */
function is_attribute_already_present($dbSocket, $table, $param, $subject, $attribute, $op, $value) {
    global $logDebugSQL;

    // Construct the SQL query
    $sql = sprintf("SELECT COUNT(`id`) FROM `%s` WHERE `%s`='%s' AND `attribute`='%s' AND `op`='%s' AND `value`='%s'",
                    $table, $param, $dbSocket->escapeSimple($subject), $dbSocket->escapeSimple($attribute),
                    $dbSocket->escapeSimple($op), $dbSocket->escapeSimple($value));

    // Execute the query
    $res = $dbSocket->query($sql);

    // Log the SQL query for debugging purposes
    $logDebugSQL .= "$sql;\n";

    // Return true if the attribute is already present, otherwise false
    return $res->fetchrow()[0] > 0;
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
function handleAttributes($dbSocket, $subject, $skipList, $insert_only=true, $user_or_group='user') {
    global $configValues, $valid_ops, $logDebugSQL;

    $param = (is_group($user_or_group)) ? 'groupname' : 'username';
    $counter = 0;

    foreach ($_POST as $element => $field) {

        // we skip several attributes (contained in the $skipList array)
        // which we do not wish to process (ie: do any sql related stuff in the db)
        if (in_array($element, $skipList)) {
            continue;
        }

        // we need each $field to be exactly a 4-elements array:
        // $attribute, $value, $op, $table
        if (!is_array($field) || count($field) != 4) {
            continue;
        }

        // we trim all array values
        foreach ($field as $i => $v) {
            $field[$i] = trim($v);
        }

        // we assign all the elements
        list($id__attribute, $value, $op, $table) = $field;

        if (preg_match("/__/", $id__attribute) === 1) {

            list($columnId, $attribute) = explode("__", $id__attribute);

            $attribute = trim($attribute);

            // if $insert_only is set to true,
            // we ignore updates, so force $columnId(s) to 0
            $columnId = intval(trim($columnId));
            if ($insert_only || $columnId < 0) {
                $columnId = 0;
            }

        } else {
            $columnId = 0;      // we need to set a non-existent column id so that the attribute would
                                // not match in the database (as it is added from the Attributes tab)
                                // and the if/else check will result in an INSERT instead of an UPDATE for the
                                // the last attribute
            $attribute = $id__attribute;
        }

        // value and attribute are required
        if (empty($value) || empty($attribute)) {
                continue;
        }

        // we only accept valid ops
        if (!in_array($op, $valid_ops)) {
            continue;
        }

        // we determine the appropriate table name based on
        // the user or group parameter and adjust the input table accordingly
        $table = get_table_name($user_or_group, $table);

        // we have to prepare the "value".
        // we distinguish between password and non-password attributes
        if (is_passwordlike_attribute($attribute)) {
            // before we proceed we need to understand if the password should be updated or skipped

            if (!$insert_only) {

                // if we find the exact same password attribute, we skip password-update
                $sql = sprintf("SELECT `value`, `op` FROM `%s` WHERE `id`=%s", $table, $columnId);
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";

                list($old_value, $old_op) = $res->fetchrow();

                // If the new value matches the old value, check if the operator has changed.
                // If so, update the operator and continue iterating.
                // This helps maintain consistency when updating records.
                if ($old_value === $value) {
                    if ($old_op !== $op) {
                        // Update the operator in the database
                        $sql = sprintf("UPDATE `%s` SET `op`='%s' WHERE `id`=%s", $table,
                                       $dbSocket->escapeSimple($op), $dbSocket->escapeSimple($columnId));
                        $res = $dbSocket->query($sql);
                        $logDebugSQL .= "$sql;\n";
                    }
                    continue;
                }

            }

            // here we can safely prepare the hashed value
            $value = hashPasswordAttribute($attribute, $value);

        }

        // before we continue we check if this attribute already exists
        // so we can insert/update only if the exact same attribute is not already present in the db
        $already_present = is_attribute_already_present($dbSocket, $table, $param, $subject, $attribute, $op, $value);

        if ($already_present) {
            continue;
        }

        // here we decide if we have to insert or update
        // if $columnId is 0 we have to insert, otherwise we have to update
        if ($columnId == 0) {
            // insert
            $sql = sprintf("INSERT INTO `%s` (`id`, `%s`, `attribute`, `op`, `value`) VALUES (0, '%s', '%s', '%s', '%s')",
                           $table, $param, $dbSocket->escapeSimple($subject), $dbSocket->escapeSimple($attribute),
                           $dbSocket->escapeSimple($op), $dbSocket->escapeSimple($value));
        } else {
            // update
            $sql = sprintf("UPDATE `%s` SET `value`='%s', `op`='%s' WHERE `%s`='%s' AND `attribute`='%s' AND `id`=%s",
                           $table, $dbSocket->escapeSimple($value), $dbSocket->escapeSimple($op),
                           $param, $dbSocket->escapeSimple($subject), $dbSocket->escapeSimple($attribute),
                           $dbSocket->escapeSimple($columnId));
        }


        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        if (!DB::isError($res)) {
            $counter++;
        }

    } // end foreach

    return $counter;
}
