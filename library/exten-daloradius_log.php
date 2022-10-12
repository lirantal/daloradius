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
 * Description:
 *        this script displays the daloradius log file ofcourse
 *        proper premissions must be applied on the log file for the web
 *        server to be able to read it
 *
 * Authors:    Liran Tal <liran@enginx.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/library/exten-daloradius_log.php') !== false) {
    header("Location: ../index.php");
    exit;
}

// check if daloradius logfile is set
if (array_key_exists('CONFIG_LOG_FILE', $configValues) && isset($configValues['CONFIG_LOG_FILE'])) {

    $logfile = $configValues['CONFIG_LOG_FILE'];
    $logfile_enc = (!empty($logfile)) ? htmlspecialchars($logfile, ENT_QUOTES, 'UTF-8') : '(none)';

    // check if file exists
    if (!file_exists($logfile)) {
        $failureMsg = sprintf("<br><br>Error accessing log file: <strong>%s</strong><br><br>"
                            . "Looked for log file in <strong>%s</strong> but could not find it.<br>"
                            . "If you know where your <em>daloradius log file</em> is located, "
                            . "specify its location in your <strong>library/daloradius.conf.php</strong> file",
                              $logfile_enc, $logfile_enc);
    } else {
        // check if it is readable
        if (is_readable($logfile) !== true) {
            $failureMsg = sprintf("<br><br>Error reading log file: <strong>%s</strong>.<br><br>Is this file readable?<br>",
                                  $logfile_enc);
        } else {
            // get its content
            $logcontent = file_get_contents($logfile);
            if (!empty($logcontent)) {
                $counter = $daloradiusLineCount;
                $fileReversed = array_reverse(file($logfile));

                echo '<div style="font-family: monospace">';
                foreach ($fileReversed as $line) {
                    if ($counter == 0) {
                        break;
                    }
                    echo nl2br(htmlspecialchars($line, ENT_QUOTES, 'UTF-8'), false);
                    $counter--;
                
                }
                echo '</div>';
            }
        }
    }
}
?>
