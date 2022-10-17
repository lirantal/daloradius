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
 *      session_id to be assigned to this session and then validates the
 *      operators credentials in the database
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *******************************************************************************
 */

include('library/sessions.php');

dalo_session_start();
dalo_session_regenerate_id();

$errorMessage = '';
include('library/opendb.php');

$login_user = $_POST['login_user'];
$login_pass = $_POST['login_pass'];

$sqlFormat = "select * from %s where username='%s' "
    . "and portalloginpassword='%s' and enableportallogin=1";
$sql = sprintf($sqlFormat,
    $configValues['CONFIG_DB_TBL_DALOUSERINFO'], 
    $dbSocket->escapeSimple($login_user),
    $dbSocket->escapeSimple($login_pass));
$res = $dbSocket->query($sql);

$numRows = $res->numRows();
include('library/closedb.php');

if ($numRows != 1) {
    $_SESSION['logged_in'] = false;
    $_SESSION['login_error'] = true;
    header('Location: login.php');
    exit;
}

if (array_key_exists('login_error', $_SESSION)) {
    unset($_SESSION['login_error']);
}
$_SESSION['logged_in'] = true;
$_SESSION['login_user'] = $login_user;
header('Location: index.php');

?>
