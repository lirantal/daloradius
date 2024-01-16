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
 * Authors:    Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    //~ include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');
    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");
    include("../common/includes/functions.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    include('../common/includes/db_open.php');

    function get_caption($message) {
        $caption = sprintf("Created by <strong>%s</strong> on <strong>%s</strong>.",
                            htmlspecialchars($message['created_by'], ENT_QUOTES, 'UTF-8'),
                            htmlspecialchars($message['created_on'], ENT_QUOTES, 'UTF-8'));
        if (isset($message['modified_on']) && !empty($message['modified_on']) &&
            isset($message['modified_by']) && !empty($message['modified_by'])) {
            $caption .= sprintf("<br>Last modification by <strong>%s</strong> on <strong>%s</strong>.",
                                htmlspecialchars($message['modified_by'], ENT_QUOTES, 'UTF-8'),
                                htmlspecialchars($message['modified_on'], ENT_QUOTES, 'UTF-8'));
        }
        return $caption;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            $updated_types = array();
            foreach ($valid_message_types as $type) {
                if (should_update_message($type)) {
                    if (update_message($dbSocket, $type)) {
                        $updated_types[] = $type;
                    }
                }
            }

            if (count($updated_types) > 0) {
                $successMsg = sprintf("Updated messages of the following types: [%s]", implode(", ", $updated_types));
                $logAction .= "$successMsg on page: ";
            } else {
                $failureMsg = "No messages have been updated";
                $logAction .= "$failureMsg on page: ";
            }

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }


    // print HTML prologue
    $extra_css = array();

    $extra_js = array();

    $title = "User messages";
    $help = "In this section you can specify messages that will be shown to users through the user portal. You have three areas where you can write: the sidebar, the login page and the welcome dashboard";

    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');


    // input group 0
    $input_descriptors0 = array();

    $input_descriptors0[] = array(
                                    "name" => "login_message_changed",
                                    "type" => "hidden",
                                    "value" => "no",
                                 );

    $message0 = get_message($dbSocket, "login");
    $input_descriptors0[] = array(
                                        "name" => "login_message",
                                        "caption" => get_caption($message0),
                                        "type" => "textarea",
                                        "content" => $message0["content"],
                                        "oninput" => "document.getElementById('login_message_changed').value='yes'"
                                     );

    // input group 1
    $input_descriptors1 = array();

    $input_descriptors1[] = array(
                                    "name" => "support_message_changed",
                                    "type" => "hidden",
                                    "value" => "no",
                                 );

    $message1 = get_message($dbSocket, "support");
    $input_descriptors1[] = array(
                                        "name" => "support_message",
                                        "caption" => get_caption($message1),
                                        "type" => "textarea",
                                        "content" => $message1["content"],
                                        "oninput" => "document.getElementById('support_message_changed').value='yes'"
                                     );

    

    // input group 2
    $input_descriptors2 = array();

    $input_descriptors2[] = array(
                                    "name" => "dashboard_message_changed",
                                    "type" => "hidden",
                                    "value" => "no",
                                 );

    $message2 = get_message($dbSocket, "dashboard");
    $input_descriptors2[] = array(
                                        "name" => "dashboard_message",
                                        "caption" => get_caption($message2),
                                        "type" => "textarea",
                                        "content" => $message2["content"],
                                        "oninput" => "document.getElementById('dashboard_message_changed').value='yes'"
                                     );


    // set navbar stuff
    $navkeys = array(
                        array("login", "Login Message"),
                        array("support","Support message"),
                        array("dashboard", "Dashboard message")
                    );

    // print navbar controls
    print_tab_header($navkeys);


    $form_name = "form_" . rand();
    $form0_descriptor = array(
                                "name" => $form_name
                             );
    open_form($form0_descriptor);

    // open tab wrapper
    open_tab_wrapper();

    // open tab 0
    open_tab($navkeys, 0, true);
    foreach ($input_descriptors0 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
    close_tab($navkeys, 0);

    // open tab 1
    open_tab($navkeys, 1);
    foreach ($input_descriptors1 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
    close_tab($navkeys, 1);

    // open tab 2
    open_tab($navkeys, 2);
    foreach ($input_descriptors2 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
    close_tab($navkeys, 2);

    // close tab wrapper
    close_tab_wrapper();

    $input_descriptors3 = array();

    $input_descriptors3[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );

    $input_descriptors3[] = array(
                                    "type" => "submit",
                                    "name" => "submit",
                                    "value" => t('buttons','apply')
                                  );

    foreach ($input_descriptors3 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_form();

    include('../common/includes/db_close.php');

    include('include/config/logging.php');

    // build javascript
    $inline_extra_js = "var labels = ['" . implode("','", $valid_message_types) . "'];" . <<<EOF

    function stretchText(index) {
        var tid = labels[index] + "_message";
        var t = document.getElementById(tid);
        t.style.height = "";
        t.style.height = t.scrollHeight + "px";
    }

    function addEvent(index) {
        var bid = labels[i] + "-button";
        var button = document.getElementById(bid);
        button.addEventListener("click", function() {
            stretchText(index);
        });

    }

    for (var i=0; i<labels.length; i++) {
        (addEvent)(i);
    }

    window.onload = function() { stretchText(0); }
EOF;

    print_footer_and_html_epilogue($inline_extra_js);
?>
