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
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include_once('../common/includes/config_read.php');
    include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // validate parameters
    $type = (array_key_exists('type', $_GET) && isset($_GET['type']) &&
             in_array(strtolower($_GET['type']), array( "daily", "monthly", "yearly" )))
          ? strtolower($_GET['type']) : "daily";

    $size = (array_key_exists('size', $_GET) && isset($_GET['size']) &&
             in_array(strtolower($_GET['size']), array( "gigabytes", "megabytes" )))
          ? strtolower($_GET['size']) : "megabytes";

    $log = "visited page: ";
    $logQuery = "performed query of type [$type] and size [$size] on page: ";

    $traffic_compare_type = $type;
    $traffic_compare_size = $size;


    // print HTML prologue
    $title = t('Intro','graphsalltimetrafficcompare.php');
    $help = t('helpPage','graphsalltimetrafficcompare');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    // set navbar stuff
    $navkeys = array(
                            array( 'Download', "Download Chart" ),
                            array( 'Upload', "Upload Chart" ),
                        );

    // print navbar controls
    print_tab_header($navkeys);

    $img_format = '<div class="my-3 text-center"><img src="%s" alt="%s"></div>';

    // open tab wrapper
    open_tab_wrapper();

    // tab 0
    open_tab($navkeys, 0, true);

    $download_src = sprintf("library/graphs/alltime_users_data.php?category=download&type=%s&size=%s", $type, $size);
    $download_alt = sprintf("%s all-time download traffic (in %s) statistics", ucfirst($type), $size);

    printf($img_format, $download_src, $download_alt);

    close_tab($navkeys, 0);

    // tab 1
    open_tab($navkeys, 1);

    $upload_src = sprintf("library/graphs/alltime_users_data.php?category=upload&type=%s&size=%s", $type, $size);
    $upload_alt = sprintf("%s all-time upload traffic (in %s) statistics", ucfirst($type), $size);

    printf($img_format, $upload_src, $upload_alt);

    close_tab($navkeys, 1);
    
    // close tab wrapper
    close_tab_wrapper();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
