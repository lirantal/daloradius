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
 * Description:    This script is responsible for managing non-terminated (or stale) sessions.
 *                 It updates the acctstoptime field in the RADIUS accounting table to the current time
 *                 and sets the acctterminatecause field to 'Stale-Session' for sessions
 *                 that have exceeded a predefined time threshold. The time threshold is determined
 *                 by adding the configured interval and grace period, ensuring it's greater than the
 *                 Acct-Interim-Interval to avoid premature session termination.
 * 
 * Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', '..', '..', 'app', 'common', 'includes', 'config_read.php' ]);

    if (strtolower($configValues['CONFIG_MAIL_ENABLED']) !== 'yes') {
        echo "SMTP Server not configured";
        return;
    }

    $configValues['CONFIG_NODE_STATUS_MONITOR_EMAIL_TO'] =
        filter_var(trim($configValues['CONFIG_NODE_STATUS_MONITOR_EMAIL_TO'] ?? ''), FILTER_VALIDATE_EMAIL)
            ? trim($configValues['CONFIG_NODE_STATUS_MONITOR_EMAIL_TO']) : '';

    if (empty($configValues['CONFIG_NODE_STATUS_MONITOR_EMAIL_TO'])) {
        echo "Email not valid";
        return;
    }

    $configValues['CONFIG_NODE_STATUS_MONITOR_HARD_DELAY'] = max(1, intval($_POST['CONFIG_NODE_STATUS_MONITOR_HARD_DELAY'] ?? 15));

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);

    $columns = ['mac', 'memfree', 'cpu', 'wan_ip', 'wan_gateway', 'lan_mac', 'firmware', 'firmware_revision'];
    $imploded_columns = '`' . implode('`, `', $columns) . '`';

    $sql = sprintf("SELECT %s
                      FROM %s WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`time`) > %d", $imploded_columns,
                      $configValues['CONFIG_DB_TBL_DALONODE'], $configValues['CONFIG_NODE_STATUS_MONITOR_HARD_DELAY']);

    // Execute SQL query on the database
    $res = $dbSocket->query($sql);

    $numrows = $res->numRows();        
    
    if ($numrows > 0) {
        $body = <<<EOF
Dear system administrator,
the following nodes seem to be offline:

{$imploded_columns}

EOF;

        while ($row = $res->fetchRow()) {
            $body .= implode($row) . "\n";
        }

        $subject = "daloRADIUS node status monitor";

        include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'notifications.php' ]);
        list($success, $message) = send_email($configValues, $configValues['CONFIG_NODE_STATUS_MONITOR_EMAIL_TO'],
                                              'daloRADIUS sysadmin', $subject, $body);
        printf("%s: %s", (($success) ? "SUCCESS" : "FAILURE"), $message);
    }

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);