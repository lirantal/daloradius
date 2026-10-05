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
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/management/populate_selectbox.php') !== false) {
    header("Location: ../../index.php");
    exit;
}
























require_once __DIR__ . '/selectbox_read.php';

function get_invoice_status_id() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $table = dalo_selectbox_table($pdo, $config, 'CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS');
        return "SELECT id, value FROM $table ORDER BY value ASC";
    });
    $result = array();
    foreach ($rows as $row) {
        $result[(int) $row[0]] = $row[1];
    }
    return $result;
}

function get_active_plans() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $table = dalo_selectbox_table($pdo, $config, 'CONFIG_DB_TBL_DALOBILLINGPLANS');
        return "SELECT DISTINCT(planName) AS planName, id FROM $table WHERE planActive='yes' ORDER BY planName ASC";
    });
    $result = array();
    foreach ($rows as $row) {
        $result[$row[1]] = $row[0];
    }
    return $result;
}




function get_proxies() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $table = dalo_selectbox_table($pdo, $config, 'CONFIG_DB_TBL_DALOPROXYS');
        return "SELECT id, proxyname FROM $table ORDER BY proxyname ASC";
    });
    $result = array();
    foreach ($rows as $row) {
        $result[sprintf('proxy-%d', (int) $row[0])] = $row[1];
    }
    return $result;
}


function get_ippools() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $table = dalo_selectbox_table($pdo, $config, 'CONFIG_DB_TBL_RADIPPOOL');
        return "SELECT id, pool_name, framedipaddress FROM $table ORDER BY pool_name ASC, framedipaddress ASC";
    });
    $result = array();
    foreach ($rows as $row) {
        $result[sprintf('ippool-%d', (int) $row[0])] = sprintf('%s - %s', $row[1], $row[2]);
    }
    return $result;
}


function get_huntgroups() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $table = dalo_selectbox_table($pdo, $config, 'CONFIG_DB_TBL_RADHG');
        return "SELECT id, groupname, nasipaddress, nasportid FROM $table ORDER BY groupname, nasipaddress ASC";
    });
    $result = array();
    foreach ($rows as $row) {
        $result[sprintf('huntgroup-%d', (int) $row[0])] = sprintf('%s:%s (%s)', $row[2], $row[3], $row[1]);
    }
    return $result;
}


function get_groups() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $pieces = array();
        foreach (array('CONFIG_DB_TBL_RADGROUPCHECK', 'CONFIG_DB_TBL_RADGROUPREPLY',
                       'CONFIG_DB_TBL_RADUSERGROUP') as $key) {
            $table = dalo_selectbox_table($pdo, $config, $key);
            $pieces[] = "SELECT DISTINCT(groupname) FROM $table";
        }
        return implode(' UNION ', $pieces);
    });
    $result = array();
    foreach ($rows as $row) {
        $key = $row[0] ?? '';
        if (!array_key_exists($key, $result)) {
            $result[$key] = $row[0];
        }
    }
    return $result;
}


function list_from_db($sql) {
    $result = array();
    foreach (dalo_selectbox_rows($sql) as $row) {
        $result[] = $row[0];
    }
    return $result;
}


function get_payment_types() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $sql = sprintf("SELECT DISTINCT(value) FROM %s ORDER BY value ASC", dalo_selectbox_table($pdo, $config, 'CONFIG_DB_TBL_DALOPAYMENTTYPES'));
        return $sql;
    });
    return array_map(function ($row) { return $row[0]; }, $rows);
}

function get_realms() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $sql = sprintf("SELECT DISTINCT(realmname) FROM %s ORDER BY realmname ASC", dalo_selectbox_table($pdo, $config, 'CONFIG_DB_TBL_DALOREALMS'));
        return $sql;
    });
    return array_map(function ($row) { return $row[0]; }, $rows);
}


function get_online_users() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $sql = sprintf("SELECT DISTINCT(username)
                          FROM %s
                         WHERE AcctStopTime IS NULL
                            OR AcctStopTime='0000-00-00 00:00:00'
                         ORDER BY username ASC", dalo_selectbox_table($pdo, $config, 'CONFIG_DB_TBL_RADACCT'));
        return $sql;
    });
    return array_map(function ($row) { return $row[0]; }, $rows);
}


function get_users($TBL_KEY='CONFIG_DB_TBL_RADCHECK') {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) use ($TBL_KEY) {
        if (!in_array($TBL_KEY, array('CONFIG_DB_TBL_RADCHECK', 'CONFIG_DB_TBL_RADACCT',
                                    'CONFIG_DB_TBL_DALOUSERINFO', 'CONFIG_DB_TBL_DALOUSERBILLINFO'), true)) {
            throw new InvalidArgumentException('Invalid username selector');
        }

        $sql = sprintf("SELECT DISTINCT(username) FROM %s ORDER BY username ASC", dalo_selectbox_table($pdo, $config, $TBL_KEY));
        return $sql;
    });
    return array_map(function ($row) { return $row[0]; }, $rows);
}


function get_nas_names() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $sql = sprintf("SELECT nasname FROM %s ORDER BY nasname ASC", dalo_selectbox_table($pdo, $config, 'CONFIG_DB_TBL_RADNAS'));
        return $sql;
    });
    return array_map(function ($row) { return $row[0]; }, $rows);
}


function get_plans() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $sql = sprintf("SELECT DISTINCT(planName)
                          FROM %s WHERE planActive='yes'
                         ORDER BY planName ASC", dalo_selectbox_table($pdo, $config, 'CONFIG_DB_TBL_DALOBILLINGPLANS'));
        return $sql;
    });
    return array_map(function ($row) { return $row[0]; }, $rows);
}


function get_ratenames() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $sql = sprintf("SELECT DISTINCT(rateName) FROM %s ORDER BY rateName ASC", dalo_selectbox_table($pdo, $config, 'CONFIG_DB_TBL_DALOBILLINGRATES'));
        return $sql;
    });
    return array_map(function ($row) { return $row[0]; }, $rows);
}


function get_groups_that_have_users() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $sql = sprintf("SELECT DISTINCT(groupname) FROM %s ORDER BY groupname ASC", dalo_selectbox_table($pdo, $config, 'CONFIG_DB_TBL_RADUSERGROUP'));
        return $sql;
    });
    return array_map(function ($row) { return $row[0]; }, $rows);
}

function get_users_that_have_groups() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $sql = sprintf("SELECT DISTINCT(username) FROM %s ORDER BY username ASC", dalo_selectbox_table($pdo, $config, 'CONFIG_DB_TBL_RADUSERGROUP'));
        return $sql;
    });
    return array_map(function ($row) { return $row[0]; }, $rows);
}


function get_operators() {
    global $configValues;
    // This sidebar read owns its handle; never close a page's business connection.
    require_once __DIR__ . '/../../../common/includes/pdo_connection.php';
    $operator_names_pdo = null;
    try {
        $operator_names_pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        $name = $configValues['CONFIG_DB_TBL_DALOOPERATORS'] ?? null;
        if (!is_string($name) || !preg_match('/\A[A-Za-z_][A-Za-z0-9_]{0,63}\z/', $name)) {
            throw new InvalidArgumentException('Invalid operator selector table');
        }
        $driver = $operator_names_pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if (!in_array($driver, array('mysql', 'pgsql'), true)) {
            throw new InvalidArgumentException('Unsupported operator selector driver');
        }
        $quote = $driver === 'mysql' ? '`' : '"';
        $table = $quote . $name . $quote;
        $stmt = $operator_names_pdo->query("SELECT DISTINCT(username) FROM $table ORDER BY username ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $error) {
        error_log('Operator selector read failed: ' . get_class($error));
        return array();
    } finally {
        $stmt = null;
        $operator_names_pdo = null;
    }
}

function get_attributes() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $sql = sprintf("SELECT DISTINCT(attribute) AS attribute FROM %s ORDER BY attribute ASC", dalo_selectbox_table($pdo, $config, 'CONFIG_DB_TBL_DALODICTIONARY'));
        return $sql;
    });
    return array_map(function ($row) { return $row[0]; }, $rows);
}

function get_vendors() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $sql = sprintf("SELECT DISTINCT(Vendor) AS Vendor FROM %s ORDER BY Vendor ASC", dalo_selectbox_table($pdo, $config, 'CONFIG_DB_TBL_DALODICTIONARY'));
        return $sql;
    });
    return array_map(function ($row) { return $row[0]; }, $rows);
}

function get_hotspots() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $sql = sprintf("SELECT DISTINCT(name) FROM %s ORDER BY name ASC", dalo_selectbox_table($pdo, $config, 'CONFIG_DB_TBL_DALOHOTSPOTS'));
        return $sql;
    });
    return array_map(function ($row) { return $row[0]; }, $rows);
}

function get_batch_names() {
    $rows = dalo_selectbox_rows(function (PDO $pdo, $config) {
        $sql = sprintf("SELECT DISTINCT(batch_name) FROM %s batch_name ORDER BY batch_name ASC",
                       dalo_selectbox_table($pdo, $config, 'CONFIG_DB_TBL_DALOBATCHHISTORY'));
        return $sql;
    });
    return array_map(function ($row) { return $row[0]; }, $rows);
}
    






















// populate_password_types() used to live here with its own hardcoded list of
// password types. It had no callers left: the pages offering a password type
// build their select box out of $valid_passwordTypes (see
// common/includes/validation.php), narrowed down through
// dalo_filter_password_types(). Keeping a second list around only invited the
// two to drift apart, so it was dropped.



/*
 * drawTables()
 *
 * an aid function to return the possible options for tables (check or reply)
 */
function drawTables() {

    echo "
        <option value='check'>check</option>
        <option value='reply'>reply</option>
    ";
}






/*
 * drawOptions()
 *
 * an aid function to return the possible options for op (operator) values
 * for attributes
 */
function drawOptions() {

    echo "
                <option value='='>=</option>
                <option value=':='>:=</option>
                <option value='=='>==</option>
                <option value='+='>+=</option>
                <option value='!='>!=</option>
                <option value='>'>></option>
                <option value='>='>>=</option>
                <option value='<'><</option>
                <option value='<='><=</option>
                <option value='=~'>=~</option>
                <option value='!~'>!~</option>
                <option value='=*'>=*</option>
                <option value='!*'>!*</option>

        ";
}





/*
 * drawTypes()
 *
 * an aid function to return the possible attribute types for
 * a given attribute
 */
function drawTypes() {

    echo "
                <option value='string'>string</option>
                <option value='integer'>integer</option>
                <option value='ipaddr'>ipaddr</option>
                <option value='date'>date</option>
                <option value='octets'>octets</option>
                <option value='ipv6addr'>ipv6addr</option>
                <option value='ifid'>ifid</option>
                <option value='abinary'>abinary</option>
        ";
}


/*
 * drawRecommendedHelpers()
 *
 * an aid function to return the possible helper functions for
 * different attributes
 */
function drawRecommendedHelper() {

    echo "
                <option value='date'>date</option>
                <option value='datetime'>datetime</option>
                <option value='authtype'>authtype</option>
                <option value='framedprotocol'>framedprotocol</option>
                <option value='servicetype'>servicetype</option>
                <option value='kbitspersecond'>kbitspersecond</option>
                <option value='bitspersecond'>bitspersecond</option>
                <option value='volumebytes'>volumebytes</option>
                <option value='mikrotikRateLimit'>mikrotikRateLimit</option>
        ";
}





?>
