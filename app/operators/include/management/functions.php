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

include_once dirname(__DIR__, 3) . '/common/includes/portal_password.php';



function quote_sql_identifier($identifier) {
    if (!is_string($identifier) || !preg_match('/^[A-Za-z0-9_]+$/', $identifier)) {
        return '';
    }

    return sprintf('`%s`', $identifier);
}

function get_table_column_names(PDO $dbSocket, $table_name, $fallback_columns=array()) {
    require_once __DIR__ . '/read_helpers_pdo.php';
    try {
        $quoted = dalo_read_identifier($dbSocket, $table_name);
        if ($dbSocket->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $columns = dalo_read_statement($dbSocket, "SHOW COLUMNS FROM $quoted")->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $columns = dalo_read_statement($dbSocket,
                'SELECT column_name FROM information_schema.columns WHERE table_schema=current_schema() AND table_name=? ORDER BY ordinal_position',
                array($table_name))->fetchAll(PDO::FETCH_COLUMN);
        }
        $columns = array_values(array_filter($columns, function ($column) {
            return is_string($column) && preg_match('/^[A-Za-z0-9_]+$/', $column);
        }));
        return $columns ?: $fallback_columns;
    } catch (Throwable $error) {
        error_log('Column read failed: ' . get_class($error));
        return $fallback_columns;
    }
}

function get_accounting_custom_query_options(PDO $dbSocket, $radacct_table, $fallback_all, $fallback_default) {
    $all = get_table_column_names($dbSocket, $radacct_table, $fallback_all);
    $default = array_values(array_intersect($fallback_default, $all));

    if (count($default) == 0) {
        $default = array_slice($all, 0, min(10, count($all)));
    }

    return array($all, $default);
}



function insert_single_attribute(PDO $dbSocket, $subject, $attribute, $op, $value, $table_index='CONFIG_DB_TBL_RADCHECK') {
    global $configValues, $logDebugSQL;

    require_once __DIR__ . '/../../library/user_create.php';
    return dalo_create_attribute($dbSocket,$configValues,$subject,$attribute,$op,$value,$table_index);
}

function hotspots_exists(PDO $dbSocket, $hotspot_name) {
    require_once __DIR__ . '/read_helpers_pdo.php';
    global $configValues, $logDebugSQL;
    $table = dalo_read_table($dbSocket, $configValues, 'CONFIG_DB_TBL_DALOHOTSPOTS');
    $sql = "SELECT COUNT(DISTINCT(id)) FROM $table WHERE name=?";
    $logDebugSQL .= "$sql;\n";
    return dalo_read_statement($dbSocket, $sql, array($hotspot_name))->fetchColumn() > 0;
}


// give an open $dbSocket and a $username,
// returns true if the provided username is found
// in the radcheck table, if $table_index is not provided
// otherwise in the table associated with $table_index
// in the $convigValues array
function user_exists(PDO $dbSocket, $username, $table_index='CONFIG_DB_TBL_RADCHECK') {
    require_once __DIR__ . '/read_helpers_pdo.php';
    global $configValues, $logDebugSQL;
    $table = dalo_read_table($dbSocket, $configValues, $table_index);
    $sql = "SELECT COUNT(DISTINCT(username)) FROM $table WHERE username=?";
    $logDebugSQL .= "$sql;\n";
    return dalo_read_statement($dbSocket, $sql, array(trim($username)))->fetchColumn() > 0;
}

// give an open $dbSocket and a $groupname,
// returns true if the provided groupname is found
// in the radgroupcheck and/or radgroupreply tables
function group_exists(PDO $dbSocket, $groupname) {
    global $configValues, $logDebugSQL;

    require_once __DIR__ . '/groupMappingsPdo.php';
    return dalo_mapping_group_exists($dbSocket, $configValues, $groupname);
}

if (!defined('DALO_DISABLED_USERS_GROUP')) {
    define('DALO_DISABLED_USERS_GROUP', 'daloRADIUS-Disabled-Users');
}
if (!defined('DALO_DISABLED_USERS_GROUP_PRIORITY')) {
    define('DALO_DISABLED_USERS_GROUP_PRIORITY', -1);
}

function is_disabled_users_group($groupname) {
    return trim($groupname) === DALO_DISABLED_USERS_GROUP;
}

function normalize_user_group_priority($groupname, $priority=0) {
    if (is_disabled_users_group($groupname)) {
        return DALO_DISABLED_USERS_GROUP_PRIORITY;
    }

    $priority = intval($priority);
    return ($priority < 0) ? 0 : $priority;
}

function update_user_group_mapping_priority(PDO $dbSocket, $username, $groupname, $new_priority) {
    global $configValues;
    require_once __DIR__ . '/groupMappingsPdo.php';
    return dalo_mapping_update_priority($dbSocket, $configValues, $username, $groupname, $new_priority);
}

// give an open $dbSocket, an $username and $groupname
// inserts (if possible) an user-group mapping with the
// provided $priority (default: 0)
function insert_single_user_group_mapping(PDO $dbSocket, $username, $groupname, $priority=0) {
    global $configValues, $logDebugSQL;

    require_once __DIR__ . '/groupMappingsPdo.php';
    return dalo_mapping_insert_single($dbSocket, $configValues, $username, $groupname, $priority);
}


// delete all group mappings for user
function delete_user_group_mappings(PDO $dbSocket, $username) {
    global $configValues;
    require_once __DIR__ . '/groupMappingsPdo.php';
    return dalo_mapping_delete_all($dbSocket, $configValues, $username);
}

// returns all groups associated with a provided $username
function get_user_group_mappings(PDO $dbSocket, $username) {
    require_once __DIR__ . '/read_helpers_pdo.php';
    global $configValues, $logDebugSQL;
    try {
        $table = dalo_read_table($dbSocket, $configValues, 'CONFIG_DB_TBL_RADUSERGROUP');
        $sql = "SELECT DISTINCT(groupname) FROM $table WHERE username=? ORDER BY groupname ASC";
        $logDebugSQL .= "$sql;\n";
        return dalo_read_statement($dbSocket, $sql, array(trim($username)))->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $error) {
        error_log('Group list read failed: ' . get_class($error));
        return array();
    }
}

// give an open $dbSocket, a $planName and an array of groupnames
// inserts (if possible) an plan-group mapping for each groupname
function insert_multiple_plan_group_mappings(PDO $dbSocket, $planName, $groupnames) {
    global $configValues;
    require_once __DIR__ . '/groupMappingsPdo.php';
    return dalo_mapping_insert_many($dbSocket, $configValues, $planName, $groupnames, true);
}

// give an open $dbSocket, an $username and an array of groupnames
// inserts (if possible) an user-group mapping for each groupname
function insert_multiple_user_group_mappings(PDO $dbSocket, $username, $groupnames) {
    global $configValues, $logDebugSQL;

    require_once __DIR__ . '/groupMappingsPdo.php';
    return dalo_mapping_insert_many($dbSocket, $configValues, $username, $groupnames);
}

function prepare_fields_and_values(PDO $dbSocket, $username, $params, $allowedFields, $skipFields, $table_index) {
    $fields=array(); $values=array();
    foreach ($params as $field=>$value) {
        if (!in_array($field,$allowedFields,true) || in_array($field,$skipFields,true)) { continue; }
        if (!is_string($value) && !is_int($value)) { throw new InvalidArgumentException('Invalid information field'); }
        $fields[]=$field; $values[]=trim((string)$value);
    }
    return $fields ? array('fields'=>$fields,'values'=>$values) : null;
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

function redact_sensitive_values($fields, $values, $sensitiveFields) {
    $redacted = $values;
    foreach ($fields as $index => $field) {
        if (in_array($field, $sensitiveFields, true)) {
            $redacted[$index] = '[redacted]';
        }
    }

    return $redacted;
}

function update_info(PDO $dbSocket, $username, $params, $allowedFields, $skipFields, $table_index,
                     $sensitiveFields = array()) {
    global $configValues, $logDebugSQL;

    require_once __DIR__ . '/../../library/user_create.php';
    return dalo_create_info($dbSocket,$configValues,$username,$params,$allowedFields,$skipFields,$table_index,true);
}

function update_user_info(PDO $dbSocket, $username, $params) {

    $allowedFields = array(
                            "id", "username", "firstname", "lastname", "email", "department", "company", "workphone",
                            "homephone", "mobilephone", "address", "city", "state", "country", "zip", "notes",
                            "changeuserinfo", "portalloginpassword", "enableportallogin", "creationdate",
                            "creationby", "updatedate", "updateby"
                          );

    $skipFields = array( "id", "username" );

    if (array_key_exists('portalloginpassword', $params)) {
        if ($params['portalloginpassword'] === '') {
            unset($params['portalloginpassword']);
        } else {
            $params['portalloginpassword'] = dalo_portal_password_hash($params['portalloginpassword']);
            if ($params['portalloginpassword'] === false) {
                throw new RuntimeException('Could not hash portal password');
            }
        }
    }

    return update_info($dbSocket, $username, $params, $allowedFields, $skipFields,
                       'CONFIG_DB_TBL_DALOUSERINFO', array('portalloginpassword'));
}

function update_user_billing_info(PDO $dbSocket, $username, $params) {
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

function add_info(PDO $dbSocket, $username, $params, $allowedFields, $skipFields, $table_index,
                  $sensitiveFields = array()) {
    global $configValues, $logDebugSQL;

    require_once __DIR__ . '/../../library/user_create.php';
    return dalo_create_info($dbSocket,$configValues,$username,$params,$allowedFields,$skipFields,$table_index,false);
}

function add_user_info(PDO $dbSocket, $username, $params) {
    $allowedFields = array(
                            "id", "username", "firstname", "lastname", "email", "department", "company", "workphone",
                            "homephone", "mobilephone", "address", "city", "state", "country", "zip", "notes",
                            "changeuserinfo", "portalloginpassword", "enableportallogin", "creationdate",
                            "creationby", "updatedate", "updateby"
                          );

    $skipFields = array( "id", "username" );

    if (array_key_exists('portalloginpassword', $params)) {
        if ($params['portalloginpassword'] === '') {
            unset($params['portalloginpassword']);
        } else {
            $params['portalloginpassword'] = dalo_portal_password_hash($params['portalloginpassword']);
            if ($params['portalloginpassword'] === false) {
                throw new RuntimeException('Could not hash portal password');
            }
        }
    }

    return add_info($dbSocket, $username, $params, $allowedFields, $skipFields,
                    'CONFIG_DB_TBL_DALOUSERINFO', array('portalloginpassword'));
}

function user_portal_password_is_set(PDO $dbSocket, $username) {
    require_once __DIR__ . '/read_helpers_pdo.php';
    global $configValues;
    try {
        $table = dalo_read_table($dbSocket, $configValues, 'CONFIG_DB_TBL_DALOUSERINFO');
        $sql = "SELECT COUNT(id) FROM $table WHERE username=? AND portalloginpassword IS NOT NULL AND portalloginpassword<>''";
        return (int) dalo_read_statement($dbSocket, $sql, array($username))->fetchColumn() === 1;
    } catch (Throwable $error) {
        error_log('Portal presence read failed: ' . get_class($error));
        return false;
    }
}

function add_user_billing_info(PDO $dbSocket, $username, $params) {
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

/**
 * Counts the number of records returned by a given SQL query.
 *
 * @param object $dbSocket The database connection object with a query() method.
 * @param string $sql The SQL query to be executed.
 * @return int The number of records returned by the query.
 */
function count_sql(PDO $dbSocket, $sql) {
    require_once __DIR__ . '/read_helpers_pdo.php';
    return (int) dalo_read_statement($dbSocket, $sql)->fetchColumn();
}

/**
 * Counts the number of distinct users in the database.
 *
 * @param object $dbSocket The database connection object with a query() method.
 * @return int The number of distinct users.
 */
function count_users(PDO $dbSocket) {
    global $configValues;

    $sql = sprintf("SELECT COUNT(DISTINCT `ui`.`username`) FROM %s AS `rc`, %s AS `ui`
                    WHERE `ui`.`username`=`rc`.`username` AND (`rc`.`attribute`='Auth-Type' OR `rc`.`attribute` LIKE '%%-Password')",
                    $configValues['CONFIG_DB_TBL_RADCHECK'], $configValues['CONFIG_DB_TBL_DALOUSERINFO']);
    return count_sql($dbSocket, $sql);
}

/**
 * Counts the number of hotspots in the database.
 *
 * @param object $dbSocket The database connection object with a query() method.
 * @return int The number of hotspots.
 */
function count_hotspots(PDO $dbSocket) {
    global $configValues;
    $sql = sprintf("SELECT COUNT(`id`) FROM %s", $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
    return count_sql($dbSocket, $sql);
}

/**
 * Counts the number of NAS devices in the database.
 *
 * @param object $dbSocket The database connection object with a query() method.
 * @return int The number of NAS devices.
 */
function count_nas(PDO $dbSocket) {
    global $configValues;
    $sql = sprintf("SELECT COUNT(`id`) FROM %s", $configValues['CONFIG_DB_TBL_RADNAS']);
    return count_sql($dbSocket, $sql);  
}

/**
 * Get the number of rows from a COUNT query.
 *
 * This function executes a SQL COUNT query and returns the result as an integer.
 *
 * @param PDO $dbSocket The database connection object.
 * @param string $query The SQL query string. It should be in the form of "SELECT COUNT(...) FROM ...".
 *
 * @return int The number of rows returned by the COUNT query.
 *
 *
 * @note The query should return only one column with the count result.
 *       Queries returning multiple columns or rows may lead to unexpected results.
 */
function get_numrows(PDO $dbSocket, $query) {
    require_once __DIR__ . '/read_helpers_pdo.php';
    return dalo_read_statement($dbSocket, $query)->fetchColumn();
}
