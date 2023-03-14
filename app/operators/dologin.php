<?php
/*
 *******************************************************************************
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
 *******************************************************************************
 * Description:
 * 		performs the logging-in authorization. First creates a random
 *      session_id to be assigned to this session and then validates
 *      the operators credentials in the database
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *******************************************************************************
 */

include('library/sessions.php');
include_once('../common/includes/config_read.php');

dalo_session_start();

$errorMessage = '';

// we need to set location name session variable before opening the database
// since the whole point is to authenticate to a spefific pre-defined database server

// validate location
$location_name = (array_key_exists('location', $_POST) && isset($_POST['location']))
               ? $_POST['location']
               : "default";

// we initialize some session params that will be useful later
$_SESSION['location_name'] = (array_key_exists('CONFIG_LOCATIONS', $configValues) &&
                              is_array($configValues['CONFIG_LOCATIONS']) &&
                              count($configValues['CONFIG_LOCATIONS']) > 0 &&
                              array_key_exists($location_name, $configValues['CONFIG_LOCATIONS']))
                           ? $location_name
                           : "default";

$_SESSION['daloradius_logged_in'] = false;

// we interact with the db, ONLY IF user provided
// both operator_user and operator_pass params
if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) &&
    dalo_check_csrf_token($_POST['csrf_token']) &&
    array_key_exists('operator_user', $_POST) && isset($_POST['operator_user']) && 
    array_key_exists('operator_pass', $_POST) && isset($_POST['operator_pass'])) {

    include('../common/includes/db_open.php');
    
    $operator_user = $dbSocket->escapeSimple($_POST['operator_user']);
    $operator_pass = $dbSocket->escapeSimple($_POST['operator_pass']);
    
    $sqlFormat = "select * from %s where username='%s' and password='%s'";
    $sql = sprintf($sqlFormat, $configValues['CONFIG_DB_TBL_DALOOPERATORS'], $operator_user, $operator_pass);
    $res = $dbSocket->query($sql);
    $numRows = $res->numRows();
    
    // we only accept ONE AND ONLY ONE RECORD as result
    if ($numRows === 1) {
        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
        $operator_id = $row['id'];
        
        $_SESSION['daloradius_logged_in'] = true;
        $_SESSION['operator_user'] = $operator_user;
        $_SESSION['operator_id'] = $operator_id;
        
        // lets update the lastlogin time for this operator
        $now = date("Y-m-d H:i:s");
        $sqlFormat = "update %s set lastlogin='%s' where username='%s'";
        $sql = sprintf($sqlFormat, $configValues['CONFIG_DB_TBL_DALOOPERATORS'], $now, $operator_user);
        $res = $dbSocket->query($sql);

    }
    
    // close connection to db before redirecting
    include('../common/includes/db_close.php');

}

// if everything went fine daloradius_logged_in session param has been set to true,
// so we can check it for deciding where and how redirect user browser
$header_location = "index.php";

if ($_SESSION['daloradius_logged_in'] !== true) {
    $header_location = "login.php";
    $_SESSION['operator_login_error'] = true;
}

header("Location: $header_location");

?>
