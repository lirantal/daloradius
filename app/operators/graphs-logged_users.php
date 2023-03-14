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
 * Authors:    Liran Tal <liran@enginx.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include_once('../common/includes/config_read.php');
    include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");

    $logged_users_on_date = (array_key_exists('logged_users_on_date', $_GET) && isset($_GET['logged_users_on_date']) &&
                             preg_match(DATE_REGEX, $_GET['logged_users_on_date'], $m) !== false &&
                             checkdate($m[2], $m[3], $m[1])) ? $_GET['logged_users_on_date'] : date("Y-m-d");

    preg_match(DATE_REGEX, $logged_users_on_date, $match);
    $month = intval($match[2]);
    $day = intval($match[3]);
    $year = intval($match[1]);

    $log = "visited page: ";
    $logQuery = "performed query on the following interval  [$day - $month - $year] on page: ";


    // print HTML prologue
    $title = t('Intro','graphsloggedusers.php');
    $help = t('helpPage','graphsloggedusers');
    
    print_html_prologue($title, $langCode);
    
	print_title_and_help($title, $help);
    
    // set navbar stuff
    $navkeys = array(
                            array( 'Daily', "Daily Chart" ),
                            array( 'Monthly', "Monthly Chart" ),
                        );

    // print navbar controls
    print_tab_header($navkeys);

    $img_format = '<div class="my-3 text-center"><img src="%s" alt="%s"></div>';

    // open tab wrapper
    open_tab_wrapper();

    // tab 0
    open_tab($navkeys, 0, true);
    
    $daily_src = sprintf("library/graphs/logged_users.php?day=%02d&month=%02d&year=%04d", $day, $month, $year);
    $daily_alt = sprintf("user accounted per-hour on the %04d-%02d-%02d", $year, $month, $day);
    
    printf($img_format, $daily_src, $daily_alt);
    
    close_tab($navkeys, 0);
    
    // tab 1
    open_tab($navkeys, 1);
    
    $monthly_src = sprintf("library/graphs/logged_users.php?month=%02d&year=%04d", $month, $year);
    $monthly_alt = sprintf("min/max user accounted per-day in the month %02d-%04d", $month, $year);
    
    printf($img_format, $monthly_src, $monthly_alt);
    
    close_tab($navkeys, 1);
    
    // close tab wrapper
    close_tab_wrapper();
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
