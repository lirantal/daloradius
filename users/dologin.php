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
 *
 * Description:    logs in users by validating credentials and checking
 *                 authorization in the database
 *
 * Authors:	       Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *******************************************************************************
 */

include('library/sessions.php');
include_once('../common/includes/config_read.php');

dalo_session_start();

$errorMessage = '';

// we interact with the db, ONLY IF user provided
// both operator_user and operator_pass params
if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token']) &&
    array_key_exists('login_user', $_POST) && !empty($_POST['login_user']) &&
    array_key_exists('login_pass', $_POST) && !empty($_POST['login_pass'])) {

    $login_user = $_POST['login_user'];
    $login_pass = $_POST['login_pass'];

    include('../common/includes/db_open.php');

    $sql_WHERE = array();
    $sql_WHERE[] = "enableportallogin=1";
    $sql_WHERE[] = "portalloginpassword<>''";
    $sql_WHERE[] = "portalloginpassword IS NOT NULL";
    $sql_WHERE[] = sprintf("portalloginpassword='%s'", $dbSocket->escapeSimple($login_pass));
    $sql_WHERE[] = sprintf("username='%s'", $dbSocket->escapeSimple($login_user));

    $sql = sprintf("SELECT COUNT(id) FROM %s WHERE ", $configValues['CONFIG_DB_TBL_DALOUSERINFO'])
         . implode(" AND ", $sql_WHERE);

    $res = $dbSocket->query($sql);
    $numrows = intval($res->fetchrow()[0]);

    // we only accept ONE AND ONLY ONE RECORD as result
    if ($numrows === 1) {
        $_SESSION['logged_in'] = true;
        $_SESSION['login_user'] = $login_user;
    }

    include('../common/includes/db_close.php');

}

// if everything went fine logged_in session param has been set to true,
// so we can check it for deciding where and how redirect user browser
$header_location = "index.php";

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] !== true) {
    $header_location = "login.php";
    $_SESSION['login_error'] = true;
}

header("Location: $header_location");
