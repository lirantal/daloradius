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
 * Description:    open database connection
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/common/includes/db_open.php') !== false) {
    http_response_code(404);
    exit;
}

    include(__DIR__ . '/config_read.php');
    include(__DIR__ . '/db_table_conventions.php');

    // setup database connection information according to the session's location name which is held in $SESSION['location_name'].
    // this is introduced in order to provide daloRADIUS to authenticate and manage several database backends without having to
    // install several web directories of daloradius

    $location = (array_key_exists('location_name', $_SESSION) &&
                 isset($_SESSION['location_name']) &&
                 $_SESSION['location_name'] != "default")
              ? $configValues['CONFIG_LOCATIONS'][$_SESSION['location_name']]
              : "";

    $mydbEngine = ($location) ? $location['Engine']     : $configValues['CONFIG_DB_ENGINE'];
    $mydbUser   = ($location) ? $location['Username']   : $configValues['CONFIG_DB_USER'];
    $mydbPass   = ($location) ? $location['Password']   : $configValues['CONFIG_DB_PASS'];
    $mydbHost   = ($location) ? $location['Hostname']   : $configValues['CONFIG_DB_HOST'];
    $mydbPort   = ($location) ? $location['Port']       : $configValues['CONFIG_DB_PORT'];
    $mydbName   = ($location) ? $location['Database']   : $configValues['CONFIG_DB_NAME'];

    $dbConnectString = sprintf("%s://%s:%s@%s:%s/%s", $mydbEngine, $mydbUser, $mydbPass, $mydbHost, $mydbPort, $mydbName);

    // we introduced support for php's database abstraction layer which simplifies database connections
    // to different technologies like mysql, oracle, postgresql, etc...
    // until everything is completely migrated we will leave these commented out

    include_once('DB.php');

    $dbSocket = DB::connect($dbConnectString);

    if (DB::isError($dbSocket)) {
        die(sprintf("<b>Database connection error</b><br/><b>Error Message</b>: %s<br/>", $dbSocket->getMessage()));
    }

    include_once(dirname(__FILE__) . '/db_error_handler.php');      // we declare the errorHandler() function in errorHandling.php

    $dbSocket->setErrorHandling(PEAR_ERROR_CALLBACK, 'errorHandler');   // setting errorHandler function for the dbSocket obj
    $dbSocket->query("SET SESSION sql_mode = '';");
