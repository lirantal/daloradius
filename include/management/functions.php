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
    
    $priority = intval($priority);
    
    $sql = sprintf("INSERT INTO %s (username, groupname, priority) VALUES ('%s', '%s', %d)",
                   $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username),
                   $dbSocket->escapeSimple($group), $priority);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    return boolval(DB::isError($res));
}

// give an open $dbSocket, an $username and an array of groupnames
// inserts (if possible) an user-group mapping
function add_user_to_groups($dbSocket, $username, $groups) {
    global $configValues, $logDebugSQL;

    if (!is_array($groups)) {
        return false;
    }

    $groups = array_unique($groups);
    
    if (count($groups) > 0) {
        return false;
    }

    if (!user_exists($dbSocket, $username)) {
        return false;
    }

    $counter = 0;
    foreach ($groups as $groupname) {
        $groupname = trim($groupname);
            
        if (empty($group)) {
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
                       $dbSocket->escapeSimple($group));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        if (!DB::isError($res)) {
            $counter++;
        }
    }
    
    return $counter;
    
}

function add_info($dbSocket, $username, $params, $allowedFields, $skipFields, $table_index) {
    global $configValues, $logDebugSQL;
    
    // if info already exists for this user we return false
    if (user_exists($dbSocket, $username, $table_index)) {
        return false;
    }
    
    $fields = array();
    $values = array();
    foreach ($params as $field => $value) {
        $value = trim($value);
        $field = trim($field);
    
        if (empty($value) || empty($field) || in_array($field, $skipFields) || !in_array($field, $allowedFields)) {
            continue;
        }
        
        $fields[] = $field;
        $values[] = $dbSocket->escapeSimple($value);
    }

    if (count($fields) > 0) {

        $sql = sprintf("INSERT INTO %s (`id`, `username`, ", $configValues[$table_index])
             . "`" . implode("`, `", $fields)
             . sprintf("`) VALUES (0, '%s', '", $dbSocket->escapeSimple($username))
             . implode("', '", $values) . "')";
        
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        return $res == 1;
    }
    
    return null;
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

