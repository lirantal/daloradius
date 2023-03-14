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
 * Description:    verifies a user session, valid or invalid based on
 *                 the random session_id generated on dologin.php
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
$this_filename = '/library/checklogin.php';
if (strpos($_SERVER['PHP_SELF'], $this_filename) !== false) {
    header("Location: ../index.php");
    exit;
}

include('sessions.php');
dalo_session_start();

if (!array_key_exists('logged_in', $_SESSION) || $_SESSION['logged_in'] !== true) {
    $_SESSION['logged_in'] = false;

    // from the document root, we strip out this_filename set above
    // this will tell us what is our "document root"
    $my_document_root = str_replace($this_filename, "", __FILE__);

    // we try to detect if there are extra directories
    // in between the root and the requested file
    $extra_directory = str_replace($_SERVER['DOCUMENT_ROOT'], "", $my_document_root);

    // we strip out this extra directory from the requested file
    $my_php_self = str_replace($extra_directory, "", $_SERVER['PHP_SELF']);

    // we implement a sort of "dynamic redirect finder" based on the number of "/" found in our "php_self" value
    $count = substr_count($my_php_self, "/", 1);
    $location = str_repeat("../", $count) . "login.php";
    $header = sprintf("Location: %s", $location);

    header($header);
    exit;
}
