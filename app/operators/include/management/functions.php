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
 * Description:    provides common functions
 *
 * Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/management/functions.php') !== false) {
    header('Location: ../../index.php');
    exit;
}


// add invoice items contained in the $_POST array.
// a single items is an associatime array starting with the string 'item'
// and containing exactly 4 elements: plan, amount, tax and notes
function add_invoice_items($dbSocket, $invoice_id='', $clean_before_adding=true) {
    global $configValues, $logDebugSQL;

    if (empty($invoice_id) || intval($invoice_id) == 0) {
        return 0;
    }

    $invoice_id = intval($invoice_id);

    if ($clean_before_adding) {
        // first remove all items for this invoice
        $sql = sprintf("DELETE FROM %s WHERE invoice_id = %d",
                       $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'], $invoice_id);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
    }

    $currDate = date('Y-m-d H:i:s');
    $currBy = $_SESSION['operator_user'];

    // insert invoice's items
    $items = 0;
    foreach ($_POST as $itemName => $value) {
        if (substr($itemName, 0, 4) != 'item' || ( !is_array($value) && count($value) != 4 )) {
            continue;
        }

        $planId = $value['plan'];
        $amount = $value['amount'];
        $tax = $value['tax'];
        $notes = $value['notes'];

        // if no amount is provided just break out
        if (empty($amount)) {
            return 0;
        }

        $sql = sprintf("INSERT INTO %s (id, invoice_id, plan_id, amount, tax_amount, notes, creationdate, creationby) ".
                        " VALUES (0, %d, '%s', '%s', '%s', '%s', '%s', '%s')",
                        $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'], $invoice_id,
                        $dbSocket->escapeSimple($planId), $dbSocket->escapeSimple($amount),
                        $dbSocket->escapeSimple($tax), $dbSocket->escapeSimple($notes), $currDate, $currBy);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        $items++;
    }

    return $items;

}


function insert_single_attribute($dbSocket, $subject, $attribute, $op, $value, $table_index='CONFIG_DB_TBL_RADCHECK') {
    global $configValues, $logDebugSQL;

    $subject = trim($subject);

    if (preg_match('/^CONFIG_DB_TBL/', $table_index) !== false &&
        array_key_exists($table_index, $configValues)) {

        $param = (preg_match('/GROUP/', $table_index)) ? "groupname" : "username";

        $sql = sprintf("INSERT INTO %s (id, `%s`, `attribute`, `op`, `value`) VALUES (0, '%s', '%s', '%s', '%s')",
                       $configValues[$table_index], $param, $dbSocket->escapeSimple($subject),
                       $dbSocket->escapeSimple($attribute), $dbSocket->escapeSimple($op),
                       $dbSocket->escapeSimple($value));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        return $res == 1;

    }

    return false;
}

// give an open $dbSocket and a $username,
// returns true if the provided username is found
// in the radcheck table, if $table_index is not provided
// otherwise in the table associated with $table_index
// in the $convigValues array
function user_exists($dbSocket, $username, $table_index='CONFIG_DB_TBL_RADCHECK') {
    global $configValues, $logDebugSQL;

    $username = trim($username);

    if (preg_match('/^CONFIG_DB_TBL/', $table_index) !== false &&
        array_key_exists($table_index, $configValues)) {

        // check if user exists in radcheck
        $sql = sprintf("SELECT COUNT(DISTINCT(username)) FROM %s WHERE username='%s'",
                       $configValues[$table_index], $dbSocket->escapeSimple($username));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        return $res->fetchrow()[0] > 0;
    }
}

// give an open $dbSocket and a $groupname,
// returns true if the provided groupname is found
// in the radgroupcheck and/or radgroupreply tables
function group_exists($dbSocket, $groupname) {
    global $configValues, $logDebugSQL;

    $groupname = trim($groupname);

    $tables = array(
                     $configValues['CONFIG_DB_TBL_RADGROUPCHECK'],
                     $configValues['CONFIG_DB_TBL_RADGROUPREPLY']
                   );


    foreach ($tables as $table) {
        $sql = sprintf("SELECT COUNT(DISTINCT(groupname)) FROM %s", $table);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        if ($res->fetchrow()[0] > 0) {
            return true;
        }
    }

    return false;

}

function update_user_group_mapping_priority($dbSocket, $username, $groupname, $new_priority) {
    global $configValues, $logDebugSQL;

    $username = trim($username);
    $groupname = trim($groupname);


    $sql = sprintf("SELECT priority FROM %s WHERE username='%s' AND groupname='%s'",
                   $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username),
                   $dbSocket->escapeSimple($groupname));
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";

    $numrows = $res->numRows();


    if ($numrows > 0) {
        $priority = (intval($new_priority) < 0) ? 0 : intval($new_priority);

        if ($numrows == 1) {
            $sql = sprintf("UPDATE %s SET priority=%d WHERE username='%s' AND groupname='%s'",
                           $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $priority,
                           $dbSocket->escapeSimple($username), $dbSocket->escapeSimple($groupname));
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";
        } else {
            // if we have more than one row, we delete all and insert only a new one
            $sql = sprintf("DELETE FROM %s WHERE username='%s' AND groupname='%s'",
                           $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username),
                           $dbSocket->escapeSimple($groupname));
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";

            $sql = sprintf("INSERT INTO %s (username, groupname, priority) VALUES ('%s', '%s', %d)",
                           $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username),
                           $dbSocket->escapeSimple($groupname), $priority);
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";
        }
        return true;
    }

    return false;

}

// give an open $dbSocket, an $username and $groupname
// inserts (if possible) an user-group mapping with the
// provided $priority (default: 0)
function insert_single_user_group_mapping($dbSocket, $username, $groupname, $priority=0) {
    global $configValues, $logDebugSQL;

    $username = trim($username);
    $groupname = trim($groupname);


    if (!user_exists($dbSocket, $username) || !group_exists($dbSocket, $groupname)) {
        return false;
    }

    $priority = (intval($priority) < 0) ? 0 : intval($priority);

    $sql = sprintf("INSERT INTO %s (username, groupname, priority) VALUES ('%s', '%s', %d)",
                   $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username),
                   $dbSocket->escapeSimple($groupname), $priority);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";

    return $res == 1;
}


// delete all group mappings for user
function delete_user_group_mappings($dbSocket, $username) {
    global $configValues, $logDebugSQL;

    $username = trim($username);

    if (!user_exists($dbSocket, $username)) {
        return false;
    }

    $sql = sprintf("DELETE FROM %s WHERE username='%s'",
                   $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username));
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";

    return !DB::isError($res);
}

// returns all groups associated with a provided $username
function get_user_group_mappings($dbSocket, $username) {
    global $configValues, $logDebugSQL;

    $username = trim($username);
    $result = array();

    $sql = sprintf("SELECT DISTINCT(groupname) FROM %s WHERE username='%s' ORDER BY groupname ASC",
                   $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username));
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";

    if (!DB::isError($res)) {
        while ($row = $res->fetchRow()) {
            $result[] = $row[0];
        }
    }

    return $result;

}

// give an open $dbSocket, a $planName and an array of groupnames
// inserts (if possible) an plan-group mapping for each groupname
function insert_multiple_plan_group_mappings($dbSocket, $planName, $groupnames) {
    global $configValues, $logDebugSQL;

    if (!is_array($groupnames)) {
        return false;
    }

    $groupnames = array_unique($groupnames);

    if (count($groupnames) == 0) {
        return false;
    }

    $counter = 0;
    foreach ($groupnames as $groupname) {
        $groupname = trim($groupname);

        if (empty($groupname)) {
            continue;
        }

        // check if group exists
        if (!group_exists($dbSocket, $groupname)) {
            continue;
        }

        // insert user-group mapping with default priority 0
        $sql = sprintf("INSERT INTO %s (id, plan_name, profile_name) VALUES (0, '%s', '%s')",
                       $configValues['CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES'],
                       $dbSocket->escapeSimple($planName),
                       $dbSocket->escapeSimple($groupname));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        if (!DB::isError($res)) {
            $counter++;
        }
    }

    return $counter;

}

// give an open $dbSocket, an $username and an array of groupnames
// inserts (if possible) an user-group mapping for each groupname
function insert_multiple_user_group_mappings($dbSocket, $username, $groupnames) {
    global $configValues, $logDebugSQL;

    if (!is_array($groupnames)) {
        return false;
    }

    $groupnames = array_unique($groupnames);

    if (count($groupnames) == 0) {
        return false;
    }

    if (!user_exists($dbSocket, $username)) {
        return false;
    }

    $counter = 0;
    foreach ($groupnames as $groupname) {
        $groupname = trim($groupname);

        if (empty($groupname)) {
            continue;
        }

        // check if group exists
        if (!group_exists($dbSocket, $groupname)) {
            continue;
        }

        // insert user-group mapping with default priority 0
        $sql = sprintf("INSERT INTO %s (username, groupname, priority) VALUES ('%s', '%s', 0)",
                       $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                       $dbSocket->escapeSimple($username),
                       $dbSocket->escapeSimple($groupname));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        if (!DB::isError($res)) {
            $counter++;
        }
    }

    return $counter;

}

function prepare_fields_and_values($dbSocket, $username, $params, $allowedFields, $skipFields, $table_index) {

    $fields = array();
    $values = array();
    foreach ($params as $field => $value) {
        $value = trim($value);
        $field = trim($field);

        // validate $field
        if (empty($field) || in_array($field, $skipFields) || !in_array($field, $allowedFields)) {
            continue;
        }

        $fields[] = $field;
        
        // validate (and set) $value
        // empty returns true even if $value is "0", but "0" could be a valid value
        $values[] = ($value !== "0" && empty($value)) ? "" : $dbSocket->escapeSimple($value);
    }

    if (count($fields) == 0) {
        return null;
    }

    return array( "fields" => $fields, "values" => $values );
}

function make_insert_query($table, $escaped_username, $fields, $values) {
    $sql = sprintf("INSERT INTO %s (`id`, `username`, ", $table)
         . "`" . implode("`, `", $fields)
         . sprintf("`) VALUES (0, '%s', '", $escaped_username)
         . implode("', '", $values) . "')";

    return $sql;
}

function make_update_query($table, $escaped_username, $fields, $values) {
    $fieldsCount = count($fields);

    $setList = array();
    for ($i = 0; $i < $fieldsCount; $i++) {
        $setList[] = sprintf("`%s`='%s'", $fields[$i], $values[$i]);
    }

    $sql = "";
    if (count($setList) > 0) {
        $sql = sprintf("UPDATE %s SET ", $table)
             . implode(", ", $setList)
             . sprintf(" WHERE `username`='%s'", $escaped_username);
    }

    return $sql;
}

function update_info($dbSocket, $username, $params, $allowedFields, $skipFields, $table_index) {
    global $configValues, $logDebugSQL;

    // if info do not exist for this user we return false
    if (!user_exists($dbSocket, $username, $table_index)) {
        return false;
    }

    $arr = prepare_fields_and_values($dbSocket, $username, $params, $allowedFields, $skipFields, $table_index);

    if (!is_array($arr)) {
        return null;
    }

    $sql = make_update_query($configValues[$table_index], $dbSocket->escapeSimple($username),
                             $arr["fields"], $arr["values"]);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";

    return $res == 1;
}

function update_user_info($dbSocket, $username, $params) {

    $allowedFields = array(
                            "id", "username", "firstname", "lastname", "email", "department", "company", "workphone",
                            "homephone", "mobilephone", "address", "city", "state", "country", "zip", "notes",
                            "changeuserinfo", "portalloginpassword", "enableportallogin", "creationdate",
                            "creationby", "updatedate", "updateby"
                          );

    $skipFields = array( "id", "username" );

    return update_info($dbSocket, $username, $params, $allowedFields, $skipFields, 'CONFIG_DB_TBL_DALOUSERINFO');
}

function update_user_billing_info($dbSocket, $username, $params) {
    $allowedFields = array(
                            "id", "username", "planName", "hotspot_id", "hotspotlocation", "contactperson", "company",
                            "email", "phone", "address", "city", "state", "country", "zip", "paymentmethod", "cash",
                            "creditcardname", "creditcardnumber", "creditcardverification", "creditcardtype",
                            "creditcardexp", "notes", "changeuserbillinfo", "lead", "coupon", "ordertaker", "billstatus",
                            "lastbill", "nextbill", "nextinvoicedue", "billdue", "postalinvoice", "faxinvoice",
                            "emailinvoice", "batch_id", "creationdate", "creationby", "updatedate", "updateby"
                          );

    $skipFields = array( "id", "username" );

    return update_info($dbSocket, $username, $params, $allowedFields, $skipFields, 'CONFIG_DB_TBL_DALOUSERBILLINFO');
}

function add_info($dbSocket, $username, $params, $allowedFields, $skipFields, $table_index) {
    global $configValues, $logDebugSQL;

    // if info do not exist for this user we return false
    if (user_exists($dbSocket, $username, $table_index)) {
        return false;
    }

    $arr = prepare_fields_and_values($dbSocket, $username, $params, $allowedFields, $skipFields, $table_index);

    if (!is_array($arr)) {
        return null;
    }

    $sql = make_insert_query($configValues[$table_index], $dbSocket->escapeSimple($username),
                             $arr["fields"], $arr["values"]);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";

    return $res == 1;
}

function add_user_info($dbSocket, $username, $params) {
    $allowedFields = array(
                            "id", "username", "firstname", "lastname", "email", "department", "company", "workphone",
                            "homephone", "mobilephone", "address", "city", "state", "country", "zip", "notes",
                            "changeuserinfo", "portalloginpassword", "enableportallogin", "creationdate",
                            "creationby", "updatedate", "updateby"
                          );

    $skipFields = array( "id", "username" );

    return add_info($dbSocket, $username, $params, $allowedFields, $skipFields, 'CONFIG_DB_TBL_DALOUSERINFO');
}

function add_user_billing_info($dbSocket, $username, $params) {
    $allowedFields = array(
                            "id", "username", "planName", "hotspot_id", "hotspotlocation", "contactperson", "company",
                            "email", "phone", "address", "city", "state", "country", "zip", "paymentmethod", "cash",
                            "creditcardname", "creditcardnumber", "creditcardverification", "creditcardtype",
                            "creditcardexp", "notes", "changeuserbillinfo", "lead", "coupon", "ordertaker", "billstatus",
                            "lastbill", "nextbill", "nextinvoicedue", "billdue", "postalinvoice", "faxinvoice",
                            "emailinvoice", "batch_id", "creationdate", "creationby", "updatedate", "updateby"
                          );

    $skipFields = array( "id", "username" );

    return add_info($dbSocket, $username, $params, $allowedFields, $skipFields, 'CONFIG_DB_TBL_DALOUSERBILLINFO');
}
