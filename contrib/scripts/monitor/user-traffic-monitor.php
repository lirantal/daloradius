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
 * Description:    This script monitors user traffic. It retrieves user data from the database and
 *                 sends email notifications to the system administrator for users exceeding these limits.
 *                 The script distinguishes between hard and soft limit violations,
 *                 providing detailed information about the users and their traffic usage.
 * 
 * Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    // Include the configuration file
    include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', '..', '..', 'app', 'common', 'includes', 'config_read.php' ]);

    // Check if SMTP server is enabled
    if (strtolower($configValues['CONFIG_MAIL_ENABLED']) !== 'yes') {
        echo "SMTP Server not configured";
        return;
    }

    // Validate and sanitize the email address for traffic monitoring
    $configValues['CONFIG_USER_TRAFFIC_MONITOR_EMAIL_TO'] =
        filter_var(trim($configValues['CONFIG_USER_TRAFFIC_MONITOR_EMAIL_TO'] ?? ''), FILTER_VALIDATE_EMAIL)
            ? trim($configValues['CONFIG_USER_TRAFFIC_MONITOR_EMAIL_TO']) : '';

    // Check if the email address is valid
    if (empty($configValues['CONFIG_USER_TRAFFIC_MONITOR_EMAIL_TO'])) {
        echo "Email not valid";
        return;
    }

    // Set the hard limit for user traffic monitoring
    $configValues['CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT'] = max(1, intval($_POST['CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT'] ?? 1073741824));

    // Calculate the soft limit for user traffic monitoring
    $configValues['CONFIG_USER_TRAFFIC_MONITOR_SOFTLIMIT'] =
        max(1, intval($_POST['CONFIG_USER_TRAFFIC_MONITOR_SOFTLIMIT'] ?? intdiv($configValues['CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT'], 2)));

    // Include the database connection file
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);

    // Define the columns to be selected in the SQL query
    $columns = ['radacctid', 'acctsessionid', 'username', 'nasipaddress', 'nasportid', 'acctstarttime', 'acctsessiontime',
                'acctinputoctets', 'acctoutputoctets', 'calledstationid', 'callingstationid', 'framedipaddress'];
    $imploded_columns = implode(', ', $columns);

    // Construct the SQL query to retrieve user data
    $sql = sprintf("SELECT %s FROM %s WHERE (acctstoptime = '0000-00-00 00:00:00' OR acctstoptime IS NULL) ",
                $imploded_columns, $configValues['CONFIG_DB_TBL_RADACCT']);

    // Add conditions to the SQL query based on hard traffic limit
    $sql1 = $sql . sprintf("AND (CAST(`acctinputoctets` AS UNSIGNED) + CAST(`acctoutputoctets` AS UNSIGNED)) >= %d", $configValues['CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT']);

    // Execute the SQL query
    $res = $dbSocket->query($sql1);

    // Get the number of rows returned by the query
    $numrows1 = $res->numRows();   

    // Initialize an array to store user information
    $users = [];

    // Define the email subject
    $subject = "daloRADIUS user traffic monitor";

    // Include the notifications file
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'notifications.php' ]);

    // Check if there are users who have exceeded the hard traffic limit
    if ($numrows1 > 0) {
        // Initialize the email body
        $body1 = <<<EOF
    Dear system administrator,
    the following users seem to have exceeded the traffic monitor hard limit threshold ({$configValues['CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT']} bytes):

    EOF;
        // Iterate over the query results and append user information to the email body
        $print_columns = true;
        while ($row1 = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            if ($print_columns) {
                $body1 .= implode(", ", array_keys($row1)) . "\n";
                $print_columns = false;
            }
            $users[] = $row1['username'];
            $body1 .= implode(", ", $row1) . "\n";
        }

        // Send an email notification for users exceeding the hard limit
        list($success, $message) = send_email($configValues, $configValues['CONFIG_USER_TRAFFIC_MONITOR_EMAIL_TO'],
                                            'daloRADIUS sysadmin', $subject, $body1);
        printf("HARD LIMIT TRAFFIC MONITOR => %s: %s", (($success) ? "SUCCESS" : "FAILURE"), $message);

        // Construct a new SQL query to check for users exceeding the soft traffic limit
        $sql2 = $sql . sprintf("AND (CAST(`acctinputoctets` AS UNSIGNED) + CAST(`acctoutputoctets` AS UNSIGNED)) > %d", $configValues['CONFIG_USER_TRAFFIC_MONITOR_SOFTLIMIT']);

        if ($users > 0) {
            $sql2 .= sprintf(" AND `username` NOT IN ('%s')", implode("', '", $users));
        }

        // Execute the SQL query
        $res = $dbSocket->query($sql2);

        // Get the number of rows returned by the query
        $numrows2 = $res->numRows();
        
        // Check if there are users who have exceeded the soft traffic limit
        if ($numrows2 > 0) {
            // Initialize the email body
            $body2 = <<<EOF
    Dear system administrator,
    the following users seem to have exceeded the traffic monitor soft limit threshold ({$configValues['CONFIG_USER_TRAFFIC_MONITOR_SOFTLIMIT']} bytes):

    EOF;
            // Iterate over the query results and append user information to the email body
            $print_columns = true;
            while ($row2 = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                if ($print_columns) {
                    $body2 .= implode(", ", array_keys($row2)) . "\n";
                    $print_columns = false;
                }
                $body2 .= implode(", ", $row2) . "\n";
            }

            // Send an email notification for users exceeding the soft limit
            list($success, $message) = send_email($configValues, $configValues['CONFIG_USER_TRAFFIC_MONITOR_EMAIL_TO'],
                                            'daloRADIUS sysadmin', $subject, $body1);
            printf("SOFT LIMIT TRAFFIC MONITOR => %s: %s", (($success) ? "SUCCESS" : "FAILURE"), $message);
        }
        
    }

    // Close the database connection
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
