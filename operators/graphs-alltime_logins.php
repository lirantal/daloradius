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
    include("../common/includes/layout.php");

    // validate (or pre-validate) parameters
    $goto_stats = (array_key_exists('goto_stats', $_GET) && isset($_GET['goto_stats']));

    $type = (array_key_exists('type', $_GET) && isset($_GET['type']) &&
             in_array(strtolower($_GET['type']), array( "daily", "monthly", "yearly" )))
          ? strtolower($_GET['type']) : "daily";

    //feed the sidebar variables
    $alltime_login_type = $type;

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query of type [$type] on page: ";


    // print HTML prologue
    $title = t('Intro','graphsalltimelogins.php');
    $help = t('helpPage','graphsalltimelogins');

    print_html_prologue($title, $langCode,);
    
    print_title_and_help($title, $help);

    // set navbar stuff
    $navkeys = array(
                        array( 'Graphs', t('menu', 'Graphs') ),
                        array( 'Statistics', t('all', 'Statistics') ),
                    );

    // print navbar controls
    print_tab_header($navkeys);

    // open tab wrapper
    open_tab_wrapper();

    // tab 0
    open_tab($navkeys, 0, true);

    $img_format = '<div class="my-3 text-center"><img src="%s" alt="%s"></div>';
    $alt = sprintf("%s all-time login/hit statistics", ucfirst($type));
    $src = sprintf("library/graphs/alltime_users_data.php?category=login&type=%s", $type);
    printf($img_format, $src, $alt);

    close_tab($navkeys, 0);

    // tab 1
    open_tab($navkeys, 1);

    echo '<div class="my-3 text-center">';
    include('library/tables/alltime_users_login.php');
    echo '</div>';

    close_tab($navkeys, 1);

    // close tab wrapper
    close_tab_wrapper();

    $inline_extra_js = "";
    if ($goto_stats) {
        $button_id = sprintf("%s-button", strtolower($navkeys[1][0]));
        $inline_extra_js = <<<EOF

window.addEventListener('load', function() {
    document.getElementById('{$button_id}').click();
});

EOF;
    }

    include('include/config/logging.php');
    print_footer_and_html_epilogue($inline_extra_js);
?>
