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
 * Package:        Clean Stale Sessions
 *
 * Description:    This script should be placed in cron to run scheduled every X minutes and clear
 *                 all sessions greater than T, where T is the time specified in seconds for reply
 *                 attribute Acct-Interim-Interval
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */


/* Configuration Section ************************************************ */

$configPath = dirname(__FILE__) . '/../../../library/config_read.php';
include($configPath);

//interval is specified in seconds
$configValues['INTERVAL'] = 60;
//grace time
$configValues['GRACE'] = 30;

/* ********************************************************************** */

require_once('DB.php');


function databaseConnect() {
    global $configValues;

    $mydbEngine = $configValues['CONFIG_DB_ENGINE'];
    $mydbUser = $configValues['CONFIG_DB_USER'];
    $mydbPass = $configValues['CONFIG_DB_PASS'];
    $mydbHost = $configValues['CONFIG_DB_HOST'];
    $mydbPort = $configValues['CONFIG_DB_PORT'];
    $mydbName = $configValues['CONFIG_DB_NAME'];

    $dbConnectString = sprintf("%s://%s:%s@%s:%s/%s", $mydbEngine, $mydbUser, $mydbPass, $mydbHost, $mydbPort, $mydbName);

    $dbSocket = DB::connect($dbConnectString);

    if (DB::isError($dbSocket)) {
        die(sprintf("DB connection error.\nMessage: %s\n", $dbSocket->getMessage()));
    }

    return $dbSocket;
}


function databaseDisconnect($dbSocket) {
    $dbSocket->disconnect();
}


/**
 * Description:The logic of cleaning stale sessions
 *
 * Since interim updates are enabled, then the accounting information should
 * be populated every U time. If a record exist with AcctStopTime
 * equal to 0 or NULL and time elapsed since AcctStartTime+SessionTime is > U then this
 * is a stale session. The query would then be:
 *
 * WHERE
 *       ((UNIX_TIMESTAMP(NOW()) - (UNIX_TIMESTAMP(AcctStartTime) + AcctSessionTime)) > U+GRACEFUL)
 *   AND
 *       (AcctStopTime = '0000-00-00 00:00:00' OR AcctStopTime IS NULL)
 */
function clearStaleSessions($dbSocket) {
    global $configValues;

    // get all entries which we are stale sessions
    $sql = sprintf("UPDATE %s AS ra SET ra.AcctStopTime=NOW(), ra.AcctTerminateCause='Stale-Session'
                     WHERE ((UNIX_TIMESTAMP(NOW()) - (UNIX_TIMESTAMP(ra.acctstarttime) + ra.acctsessiontime)) > (%d+%d))
                       AND (AcctStopTime='0000-00-00 00:00:00' OR AcctStopTime IS NULL)",
                   $configValues['CONFIG_DB_TBL_RADACCT'], $configValues['INTERVAL'], $configValues['GRACE']);
    $res = $dbSocket->query($sql);
}


// connect to db
$dbConn = databaseConnect();

// perform cleanup
clearStaleSessions($dbConn);

// disconnect from db
databaseDisconnect($dbConn);
