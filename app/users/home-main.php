<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
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
 * Authors:    Liran Tal <liran@lirantal.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include('library/checklogin.php');
    $login_user = $_SESSION['login_user'];

    include_once('../common/includes/config_read.php');
    include_once("lang/main.php");
    include("../common/includes/layout.php");

    include('../common/includes/functions.php');
    include('../common/includes/db_open.php');
    $message = get_message($dbSocket, "dashboard")["content"];
    include('../common/includes/db_close.php');
    
    if (!empty($message)) {
        $help = $message;
    } else {
        $help = t('helpPage','loginUsersPortal');
    }

    // print HTML prologue
    $title = "Home";
    print_html_prologue($title, $langCode);

    $title = "Welcome to the daloRADIUS User Portal";
    print_title_and_help($title, $help);

    // main accordion
    echo '<div class="accordion m-2" id="accordion-parent">';

    include_once('include/management/userReports.php');

    // Display user session status as a table and open the accordion.
    userConnectionStatus($login_user, 1, true);

    // Show user plan information in a table.
    userPlanInformation($login_user, 1);

    // Analyze and display user subscription details in a table.
    userSubscriptionAnalysis($login_user, 1);

    echo '</div>';

    include('include/config/logging.php');

    print_footer_and_html_epilogue();
