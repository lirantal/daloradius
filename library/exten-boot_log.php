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
 *        this script displays the radius log file.
 *        Of course proper premissions must be applied on the log file for the web
 *        server to be able to read it
 *
 * Authors:    Liran Tal <liran@enginx.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
$extension_file = '/library/exten-boot_log.php';
if (strpos($_SERVER['PHP_SELF'], $extension_file) !== false) {
    header("Location: ../index.php");
    exit;
}

// possible locations for radius logs
$logfile_loc = array(
    '/var/log/boot',
    '/var/log/dmesg',
    '/usr/local/var/log/dmesg'
);

// select one log file
$logfile = "";

foreach ($logfile_loc as $tmp) {
    if (file_exists($tmp)) { 
    $logfile = $tmp;
        break;
    }
}

$logfile_enc = (!empty($logfile)) ? htmlspecialchars($logfile, ENT_QUOTES, 'UTF-8') : '(none)';

// check if it is empty
if (empty($logfile)) {
    $failureMsg = sprintf("<br><br>Error accessing log file: <strong>%s</strong><br><br>" . 
                          "Looked for log file in <strong>%s</strong> but could not find it.<br>" .
                          "If you know where your <em>dmesg (boot) log file</em> is located, " .
                          "specify its location in <strong>%s</strong>",
              $logfile_enc,
              htmlspecialchars(implode(", ", $logfile_loc), ENT_QUOTES, 'UTF-8'),
              htmlspecialchars($extension_file, ENT_QUOTES, 'UTF-8'));
} else {

    // check if it is readable
    if (is_readable($logfile) !== true) {
        $failureMsg = sprintf("<br><br>Error reading log file: <strong>%s</strong>.<br><br>Is this file readable?<br>",
                              $logfile_enc);
    } else {

        // get its content
        $logcontent = file_get_contents($logfile);
        if (!empty($logcontent)) {
            $counter = $bootLineCount;
            $filter = (!empty($bootFilter)) ? preg_quote($bootFilter, "/") : ".+";
            $fileReversed = array_reverse(file($logfile));

            echo '<div style="font-family: monospace">';
            foreach ($fileReversed as $line) {
                if (preg_match("/$filter/i", $line)) {
                    if ($counter == 0) {
                        break;
                    }
                    echo nl2br(htmlspecialchars($line, ENT_QUOTES, 'UTF-8'), false);
                    $counter--;
            }
            }
            echo '</div>';
        }
    }
}

?>
