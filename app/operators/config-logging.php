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

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');
    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    $param_label = array(
                            'CONFIG_LOG_PAGES' => t('all','PagesLogging'),
                            'CONFIG_LOG_QUERIES' => t('all','QueriesLogging'),
                            'CONFIG_LOG_ACTIONS' => t('all','ActionsLogging'),
                            'CONFIG_DEBUG_SQL' => t('all','LoggingDebugInfo'),
                            'CONFIG_DEBUG_SQL_ONPAGE' => t('all','LoggingDebugOnPages')
                        );

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            $isError = false;

            // validate yes/no params
            foreach ($param_label as $param => $label) {
                if (array_key_exists($param, $_POST) && !empty(strtolower(trim($_POST[$param]))) &&
                    in_array(strtolower(trim($_POST[$param])), array("yes", "no"))) {
                    $configValues[$param] = $_POST[$param];
                }
            }

            // validate path
            $log_path_prefix = $configValues['CONFIG_PATH_DALO_VARIABLE_DATA'] . "/log";
            $log_file_suffix = ".log";

            if (array_key_exists('CONFIG_LOG_FILE', $_POST) && !empty(trim($_POST['CONFIG_LOG_FILE']))) {
                $candidate_log_file = trim($_POST['CONFIG_LOG_FILE']);

                if (
                        // this ensure that the log_path_prefix is a directory
                        is_dir($log_path_prefix) &&

                        // this ensures that candidate_log_file starts with the log_path_prefix
                        substr($candidate_log_file, 0, strlen($log_path_prefix)) === $log_path_prefix &&

                        // this ensures that candidate_backup_file does not contain any ".." sequence
                        strpos($candidate_log_file, "..") === false &&

                        // this ensures that candidate_log_file ends with the log_file_suffix
                        substr($candidate_log_file, -strlen($log_file_suffix)) === $log_file_suffix &&

                        // this ensures that candidate_log_file is at a writable location
                        // or that at least it can be written inside the parent directory
                        (is_writable($candidate_log_file) || is_writable(dirname($candidate_log_file)))
                    ) {

                    $configValues['CONFIG_LOG_FILE'] = $candidate_log_file;

                } else {
                    $isError = true;
                }
            } else {
                $isError = true;
            }

            // we write ONLY IF isError is false
            if (!$isError) {
                include("../common/includes/config_write.php");
            } else {
                $failureMsg = sprintf("Log path not allowed. Ensure that log file name locates a writable %s file contained in %s",
                                      $log_file_suffix, $log_path_prefix);
                $logAction .= "$failureMsg on page: ";
            }

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }


    // print HTML prologue
    $title = t('Intro','configlogging.php');
    $help = t('helpPage','configlogging');

    print_html_prologue($title, $langCode);

    


    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    $fieldset0_descriptor = array(
                                    "title" => t('title','Settings')
                                 );


    $input_descriptors0 = array();

    foreach ($param_label as $name => $label) {
        $input_descriptors0[] = array(
                                        "type" => "select",
                                        "options" => array( "yes", "no" ),
                                        "caption" => $label,
                                        "name" => $name,
                                        "selected_value" => $configValues[$name]
                                     );
    }

    $input_descriptors0[] = array(
                                        "type" => "text",
                                        "caption" => t('all','FilenameLogging'),
                                        "name" => 'CONFIG_LOG_FILE',
                                        "value" => $configValues['CONFIG_LOG_FILE']
                                     );

    $input_descriptors0[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );

    $input_descriptors0[] = array(
                                    'type' => 'submit',
                                    'name' => 'submit',
                                    'value' => t('buttons','apply')
                                 );

    open_form();

    // open 0-th fieldset
    open_fieldset($fieldset0_descriptor);

    foreach ($input_descriptors0 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_fieldset();

    close_form();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
