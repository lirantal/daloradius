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
 *  Description:    this script displays the daloradius log file. Of course,
 *                  proper premissions must be applied on the log file for the web
 *                  server to be able to read it
 *
 * Authors:         Liran Tal <liran@enginx.com>
 *                  Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/library/extensions/daloradius_log.php') !== false) {
    header("Location: ../../index.php");
    exit;
}

// check if daloradius logfile is set
if (array_key_exists('CONFIG_LOG_FILE', $configValues) && isset($configValues['CONFIG_LOG_FILE'])) {

    $logfile = $configValues['CONFIG_LOG_FILE'];
    $logfile_enc = (!empty($logfile)) ? htmlspecialchars($logfile, ENT_QUOTES, 'UTF-8') : '(none)';

    // check if file exists
    if (!file_exists($logfile)) {
        $failureMsg = sprintf('Error accessing log file: <strong>%s</strong>.<br>'
                            . 'Looked for log file in <strong>%s</strong> but could not find it.<br>'
                            . 'If you know where your <em>daloradius log file</em> is located, '
                            . 'specify its location <a href="config-logging.php">here</a>.',
                              $logfile_enc, $logfile_enc);
    } else {
        // check if it is readable
        if (is_readable($logfile) !== true) {
            $failureMsg = sprintf("Error reading log file: <strong>%s</strong>.<br>Is this file readable?",
                                  $logfile_enc);
        } else {
            // get its content
            $logcontent = file($logfile);
            if ($logcontent !== false && count($logcontent) > 0) {
                $reversed_content = array_reverse($logcontent);

                // set internal count & filter
                $_count = (isset($count) && is_numeric($count)) ? $count : 50;
                $_filter = (isset($filter) && !empty($filter)) ? preg_quote($filter, "/") : "";

                echo '<pre class="font-monospace my-1">';
                foreach ($reversed_content as $line) {
                    if (empty($_filter) || preg_match("/$_filter/i", $line)) {
                        if ($_count == 0) {
                            break;
                        }

                        echo htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
                        $_count--;
                    }
                }
                echo '</pre>';
            } else {
                $failureMsg = sprintf("It looks like log file <strong>%s</strong> is empty.", $logfile_enc);
            }
        }
    }
}
?>
