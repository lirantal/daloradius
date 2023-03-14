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

    include_once('../common/includes/config_read.php');
    include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    $log = "visited page: ";
    $logQuery = "performed query on page: ";


    // print HTML prologue
    $title = "UPS Status";
    $help = "";

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    exec("which apcaccess || command -v apcaccess", $output, $retStatus);

    $failureMsg = "";

    $sep = ":";
    if ($retStatus !== 0) {
        $sep = "\n";
        $failureMsg = '<strong>Error</strong> accessing UPS device information';
    } else {

        $table = array( 'title' => 'General Information', 'rows' => array() );
        foreach ($output as $line) {
            list($var, $val) = split($sep, $line);
            $var = htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
            $val = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');

            $table['rows'][] = array( $var, $val );
        }

        print_simple_table($table);
    }

    if (!empty($failureMsg)) {
        include_once('include/management/actionMessages.php');
    }

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
