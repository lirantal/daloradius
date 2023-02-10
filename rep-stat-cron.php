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
 * Authors:     Liran Tal <liran@enginx.com>
 *              Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include_once('library/config_read.php');
    include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include("library/layout.php");

    $log = "visited page: ";
    $logQuery = "performed query on page: ";


    $cronUser = get_current_user();

    // validating params
    $cmd = (isset($_GET['cmd']) && !empty(trim($_GET['cmd'])) &&
            in_array(strtolower(trim($_GET['cmd'])), array( "enable", "disable" )))
         ? strtolower($_GET['cmd']) : "";

    $dalo_crontab_file = dirname(__FILE__) . '/contrib/scripts/dalo-crontab';

    $exec = "";

    switch ($cmd) {
        case "disable":
        $exec = sprintf("$(which crontab || command -v crontab) -u %s -r", escapeshellarg($cronUser));
        break;

        case "enable":
        $exec = sprintf("$(which crontab || command -v crontab) -u %s %s", escapeshellarg($cronUser), $dalo_crontab_file);
        break;
    }

    if (!empty($exec)) {
        exec($exec);
    }

    // print HTML prologue
    $title = "CRON Status";
    $help = "";

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    $failureMsg = "";

    $exec = sprintf("$(which crontab || command -v crontab) -u %s -l 2>&1", escapeshellarg($cronUser));
    exec($exec, $output, $retStatus);

    if ($retStatus !== 0) {
        $failureMsg = '<strong>Error</strong> no crontab is configured for this user or user does not exist';

        if (!empty($output)) {
            $failureMsg .= '<pre>';
            foreach ($output as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }
                $failureMsg .= htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
            }
            $failureMsg .= '</pre>';
        }

    } else {
        $i = 1;
        foreach($output as $text) {
            printf('<strong>#%d</strong>: %s', $i, htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
            $i++;
        }
    }

    if (!empty($failureMsg)) {
        include_once('include/management/actionMessages.php');
    }

    echo '<div class="btn-group my-3" role="group">';
    echo '<a class="btn btn-success" href="?cmd=enable">Enable CRON</a>';
    echo '<a class="btn btn-danger" href="?cmd=disable">Disable CRON</a>';
    echo '</div>';

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
