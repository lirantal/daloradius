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
 * Description:    this script displays the radius log file. Of course, proper premissions
 *                 must be applied on the log file for the web server to be able to read it
 *
 * Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/library/extensions/system_log.php') !== false) {
    header("Location: ../../index.php");
    exit;
}

function print_system_log($logfile_paths, $log_label, $filter, $count) {

    $logfile_paths = array_unique($logfile_paths);

    // Filter unique paths and select the first existing log file
    $logfile = array_filter($logfile_paths, 'file_exists')[0] ?? '';

    // If no existing log file was found, generate an error message and exit
    if (empty($logfile)) {
        $failureMsg = sprintf("An error occurred while accessing the <em>%s</em>.<br>" .
                              "The system tried to locate the log file in the following locations, but couldn't find it: <strong>%s</strong>.<br>" .
                              'If you know where the log file is located, please specify <em>its absolute path</em> ' .
                              'in the <a href="config-logging.php">%s</a> section.',
                              $log_label, htmlspecialchars(implode(", ", $logfile_paths), ENT_QUOTES, 'UTF-8'), t('button','LoggingSettings'));

        return $failureMsg;
    }

    // If the log file is not readable, generate an error message and exit
    if (!is_readable($logfile)) {
        $failureMsg = sprintf("Error reading log file: <strong>%s</strong>.<br>Is this file readable?",
                              htmlspecialchars($logfile, ENT_QUOTES, 'UTF-8'));
        return $failureMsg;
    }

    // Get the content of the log file
    $logcontent = file($logfile);
    if ($logcontent !== false && count($logcontent) > 0) {
        // Reverse the content of the file and apply filter and count
        $_count = isset($count) && is_numeric($count) ? $count : 50;
        $_filter = isset($filter) && !empty($filter) ? preg_quote($filter, "/") : "";
        $reversed_content = array_reverse($logcontent);
        
        echo '<pre class="font-monospace my-1">';
        foreach ($reversed_content as $line) {
            if (empty($_filter) || preg_match("/$_filter/i", $line) === 1) {
                if ($_count == 0) {
                    break;
                }

                echo htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
                $_count--;
            }
        }
        echo '</pre>';
        return null;
    }

    $failureMsg = sprintf("It looks like log file <strong>%s</strong> is empty.", htmlspecialchars($logfile, ENT_QUOTES, 'UTF-8'));
    return $failureMsg;

}