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

    $size = (array_key_exists('size', $_GET) && isset($_GET['size']) &&
             in_array(strtolower($_GET['size']), array( "gigabytes", "megabytes" )))
          ? strtolower($_GET['size']) : "megabytes";

    $username = (array_key_exists('username', $_GET) && isset($_GET['username']))
              ? str_replace('%', '', $_GET['username']) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

    //feed the sidebar variables
    $overall_upload_username = $username_enc;
    $overall_upload_type = $type;
    $overall_upload_size = $size;

    // init logging variables
    $log = "visited page: ";
    if (!empty($username)) {
        $logQuery = "performed query for user [$username] of type [$type] on page: ";
    }


    // print HTML prologue
    $title = t('Intro','graphsoverallupload.php');
    $help = t('helpPage','graphsoverallupload');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    $inline_extra_js = "";
    if (!empty($username)) {

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
        $src = sprintf("library/graphs/overall_users_data.php?category=upload&type=%s&size=%s&user=%s", $type, $size, $username_enc);
        $alt = sprintf("traffic uploaded by user %s", $username_enc);
        printf($img_format, $src, $alt);
        
        close_tab($navkeys, 0);

        // tab 1
        open_tab($navkeys, 1);

        echo '<div class="my-3 text-center">';
        include('library/tables/overall_users_upload.php');
        echo '</div>';

        close_tab($navkeys, 1);

        // close tab wrapper
        close_tab_wrapper();

        if ($goto_stats) {
            $button_id = sprintf("%s-button", strtolower($navkeys[1][0]));
            $inline_extra_js = <<<EOF

window.addEventListener('load', function() {
    document.getElementById('{$button_id}').click();
});

EOF;
        }

    } else {
        $failureMsg = "You must provide a valid username";
        include_once("include/management/actionMessages.php");
    }

    include('include/config/logging.php');
    print_footer_and_html_epilogue($inline_extra_js);

?>
