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

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include_once('../common/includes/config_read.php');
    include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    $log = "visited page: ";
    $logQuery = "performed query on page: ";

    // print HTML prologue
    $title = "RAID Status";
    $help = "";

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    $failureMsg = "";
    $error = '<strong>Error</strong> accessing RAID device information';

    if (!file_exists('/proc/mdstat')) {
        $failureMsg = $error;
    } else {
        exec("cat /proc/mdstat | awk '/md/ {print $1}'", $mdstat, $retStatus);

        if ($retStatus !== 0) {
            $failureMsg = $error;
        } else {
            if (count($mdstat) > 0) {

                $navkeys = array();
                foreach($mdstat as $mddevice) {
                    $key = sprintf("%s-tab", $mddevice);
                    //~ $navbuttons[$key] = $mddevice;
                    $navkeys[] = array( $mddevice, $mddevice );
                }

                // print navbar controls
                print_tab_header($navkeys);
                //~ print_tab_navbuttons($navbuttons);

                // open tab wrapper
                open_tab_wrapper();

                $counter = 0;
                foreach($mdstat as $mddevice) {

                    open_tab($navkeys, $counter, ($counter == 0));

                    $dev = "/dev/$mddevice";
                    $cmd = sprintf("sudo /sbin/mdadm --detail %s", escapeshellarg($dev));
                    $output = "";
                    exec($cmd, $output);

                    $table = array( 'title' => $mddevice, 'rows' => array() );

                    foreach($output as $line) {
                        list($var, $val) = split(":", $line);
                        $var = htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
                        $val = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');

                        $table['rows'][] = array( $var, $val );

                    }

                    print_simple_table($table);

                    close_tab($navkeys, $counter);

                    $counter++;

                }

                // close tab wrapper
                close_tab_wrapper();


            } else {
                $failureMsg = $error;
            }
        }
    }

    if (!empty($failureMsg)) {
        include_once('include/management/actionMessages.php');
    }

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
