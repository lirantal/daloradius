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
 * Description:    This page displays logs from daloRADIUS, allowing customization of line count
 *                 and filtering by queries, notices, inserts, or selects.
 * 
 * Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
 *                 Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

    include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);
    $operator = $_SESSION['operator_user'];

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY_EXTENSIONS'], 'system_logs.php' ]);
    
    $log = "visited page: ";

    // Parameter validation
    $count = filter_var($_GET['count'] ?? 50, FILTER_VALIDATE_INT, ['options' => ['default' => 50, 'min_range' => 1]]);
    $filter = (in_array(strtoupper($_GET['filter']) ?? "", ["QUERY", "NOTICE", "INSERT", "SELECT"])) ? strtoupper($_GET['filter']) : "";

    // Print HTML prologue
    $title = sprintf("%s &bull; lines: %d", t('Intro','replogsdaloradius.php'), $count);
    if (!empty($filter)) {
        $title .= sprintf(", filter: %s", $filter);
    }
    $help = t('helpPage','replogsdaloradius');
    
    print_html_prologue($title, $langCode);
    print_title_and_help($title, $help);
    
    // Check if the daloradius logfile is set
    $log_label = "daloRADIUS log file";

    if (!isset($configValues['CONFIG_LOG_FILE']) || empty($configValues['CONFIG_LOG_FILE'])) {
        $failureMsg = sprintf('The system could not locate your <em>%s</em>.<br>' .
                              'Please specify its location in the <a href="config-logging.php">%s</a> section.',
                              $log_label, t('button','LoggingSettings'));
    } else {
        $logfile_paths = [$configValues['CONFIG_LOG_FILE']];
        $failureMsg = print_system_log($logfile_paths, $log_label, $filter, $count);
    }

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    print_footer_and_html_epilogue();
