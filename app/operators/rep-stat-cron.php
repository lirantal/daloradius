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

    $cronUser = shell_exec('whoami');

    $dalo_crontab_file = realpath(__DIR__ . '/../../contrib/scripts/dalo-crontab');
    $is_crontab_file_accessible = (file_exists($dalo_crontab_file) && is_readable($dalo_crontab_file));

    if ($is_crontab_file_accessible) {

        $valid_cmds = array( "enable" => "crontab is enabled", "disable" => "crontab is disabled" );
        $failureMsg = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

                // validating params
                $cmd = (isset($_POST['cmd']) && !empty(trim($_POST['cmd'])) &&
                        in_array(strtolower(trim($_POST['cmd'])), array_keys($valid_cmds)))
                     ? strtolower(trim($_POST['cmd'])) : "";

                $exec = "";

                switch ($cmd) {
                    case "disable":
                    $exec = '$(which crontab || command -v crontab) -r';
                    break;

                    case "enable":
                    $exec = sprintf('$(which crontab || command -v crontab) %s', $dalo_crontab_file);
                    break;
                }

                if (!empty($exec)) {
                    exec($exec);
                }

            } else {
                // csrf
                $cmd = "";
                $failureMsg = "CSRF token error";
                $logAction .= "$failureMsg on page: ";
            }
        }
    } else {
        $cmd = "";
        $failureMsg = sprintf("Error. User <strong>%s</strong> is not able to access/locate the <strong>dalo-crontab</strong> file "
                            . "in the subdirectory <strong>contrib/scripts</strong>", $cronUser);
        $logAction .= sprintf("User %s is not able to access/locate dalo-crontab file on page: ", $cronUser);
    }


    // print HTML prologue
    $title = "CRON Status";
    $help = "";

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    if ($is_crontab_file_accessible) {

        $exec = '$(which crontab || command -v crontab) -l 2>&1';
        exec($exec, $output, $retStatus);

        if ($retStatus !== 0) {
            $cmd = "disable";
            $valid_cmds["enable"] = "enable crontab";

            if (!empty($failureMsg)) {
                $failureMsg .= str_repeat("<br>", 2);
            }

            $failureMsg .= sprintf('<strong>Error</strong>: crontab is not configured for <strong>%s</strong>', $cronUser);

            if (!empty($output)) {
                $text = htmlspecialchars(trim($output[0]), ENT_QUOTES, 'UTF-8');

                $failureMsg .= sprintf('<br><strong>Details</strong>: %s', $text)
                            .  ((count($output) > 1) ?  "&hellip;" : ".");
            }

        } else {
            $successMsg = sprintf('<strong>Success</strong>: crontab is configured for <strong>%s</strong>', $cronUser);

            $cmd = "enable";
            $valid_cmds["disable"] = "disable crontab";

            $crontabContent = "";

            foreach($output as $i => $text) {
                $crontabContent .= sprintf('<strong>%02d</strong>: %s' . "\n", $i+1, htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
            }
        }
    }

    include_once('include/management/actionMessages.php');

    $form_name = "form_" . rand();

    $form0_descriptor = array(
                                "name" => $form_name
                             );

    $fieldset0_descriptor = array(
                                    "title" => "enable/disable crontab"
                                 );

    $input_descriptors0 = array();

    $options = $valid_cmds;
    array_unshift($options, "");

    $input_descriptors0[] = array(
                                    "type" => "select",
                                    "options" => $options,
                                    "caption" => "status/action",
                                    "name" => "cmd",
                                    "selected_value" => (isset($cmd) ? $cmd : ""),
                                    "onchange" => sprintf("document.getElementById('%s').submit()", $form_name),
                                 );

    $input_descriptors0[] = array(
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                        "name" => "csrf_token"
                                     );

    open_form($form0_descriptor);

    // open 0-th fieldset
    open_fieldset($fieldset0_descriptor);

    foreach ($input_descriptors0 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_fieldset();

    close_form();

    if (isset($crontabContent)) {
        $fieldset0_descriptor = array(
                                    "title" => "crontab content"
                                 );

        open_fieldset($fieldset0_descriptor);

        echo '<pre class="font-monospace m-1 bg-light">';
        echo $crontabContent;
        echo '</pre>';

        close_fieldset();
    }

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
