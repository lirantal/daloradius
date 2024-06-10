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
 * Description:    This script manages non-terminated (or stale) sessions in a RADIUS accounting table. 
 *                 It updates the `acctstoptime` field to the current time and sets `acctterminatecause` 
 *                 to 'Stale-Session' for sessions that exceed a predefined time threshold.
 *                 The threshold is determined by adding a configured interval and grace period.
 *                 It ensures the threshold is greater than the Acct-Interim-Interval to avoid premature
 *                 session termination.
 * 
 * Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    // Include necessary configuration files to establish a database connection and read configuration values
    include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', '..', '..', 'app', 'common', 'includes', 'config_read.php' ]);
    
    // Check and set default values for INTERVAL and GRACE if they are not set or are invalid
    if (!array_key_exists('CONFIG_FIX_STALE_INTERVAL', $configValues) || intval($configValues['CONFIG_FIX_STALE_INTERVAL']) <= 0) {
        $configValues['CONFIG_FIX_STALE_INTERVAL'] = 60;
    } else {
        $configValues['CONFIG_FIX_STALE_INTERVAL'] = intval($configValues['CONFIG_FIX_STALE_INTERVAL']);
    }

    if (!array_key_exists('CONFIG_FIX_STALE_GRACE', $configValues) || intval($configValues['CONFIG_FIX_STALE_GRACE']) <= 0 ||
        intval($configValues['CONFIG_FIX_STALE_GRACE']) > $configValues['CONFIG_FIX_STALE_INTERVAL']) {
        $configValues['CONFIG_FIX_STALE_GRACE'] = intdiv($configValues['CONFIG_FIX_STALE_INTERVAL'], 2);
    } else {
        $configValues['CONFIG_FIX_STALE_GRACE'] = intval($configValues['CONFIG_FIX_STALE_GRACE']);
    }

    // Calculate the time threshold by summing the INTERVAL and GRACE.
    // It's important to ensure that the time threshold is set appropriately relative to the Acct-Interim-Interval,
    // especially to ensure it's greater than the Acct-Interim-Interval to avoid premature session termination.
    $timeThreshold = $configValues['CONFIG_FIX_STALE_INTERVAL'] + $configValues['CONFIG_FIX_STALE_GRACE'];

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);

    // Construct SQL query to update non-terminated sessions marked as stale
    $sql = sprintf("UPDATE %s
                       SET `acctstoptime` = NOW(), `acctterminatecause` = 'Stale-Session'
                     WHERE (UNIX_TIMESTAMP(NOW()) - (UNIX_TIMESTAMP(`acctstarttime`) + `acctsessiontime`)) > %d
                       AND (`acctstoptime` = '0000-00-00 00:00:00' OR `acctstoptime` IS NULL)",
                   $configValues['CONFIG_DB_TBL_RADACCT'], $timeThreshold);

    // Execute SQL query on the database
    $res = $dbSocket->query($query);

    // Update the `acctstarttime` field by adding the session time and a specified threshold to the current time.
    // This is performed for records where `acctstarttime` is either '0000-00-00 00:00:00' or NULL
    // and `acctsessiontime` is greater than 0.
    $sql = sprintf("UPDATE %s
                       SET `acctstarttime` = DATE_ADD(NOW(), INTERVAL (`acctsessiontime` + %d) SECOND)
                     WHERE (`acctstarttime` = '0000-00-00 00:00:00' OR `acctstarttime` IS NULL) AND `acctsessiontime` > 0",
                   $configValues['CONFIG_DB_TBL_RADACCT'], $timeThreshold);

    
    $res = $dbSocket->query($query);

    // Close the database connection
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
