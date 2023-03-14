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

    // init logging variables
    $logAction = "";
    $logDebugSQL = "";

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");
    include_once("include/management/functions.php");

    // validate path
    $backup_path_prefix = $configValues['CONFIG_PATH_DALO_VARIABLE_DATA'] . "/backup";
    $backup_file_suffix = ".sql";

    $file = "";
    if (array_key_exists('file', $_POST) && !empty(trim($_POST['file']))) {
        $candidate_backup_file = trim($_POST['file']);

        if (
                // this ensures that candidate_backup_file does not contain any ".." sequence
                strpos($candidate_backup_file, "..") === false &&

                // this ensures that candidate_backup_file does not contain any "/" char
                strpos($candidate_backup_file, "/") === false &&

                // this ensures that candidate_backup_file ends with the backup_file_suffix
                substr($candidate_backup_file, -strlen($backup_file_suffix)) === $backup_file_suffix
           ) {

            $file = $candidate_backup_file;
        }

    }

    $backupAction = (array_key_exists('action', $_POST) && isset($_POST['action']) &&
                     in_array($_POST['action'], array_keys($valid_backupActions))) ? $_POST['action'] : "";

    $cols = array(
                    t('all', 'CreationDate'),
                    "filename" => t('all', 'Name'),
                    "Size",
                    t('all', 'Action'),
                 );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);

    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }

    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "asc";

    // init backup paths
    $fileName = sprintf("%s/%s", $backup_path_prefix, $file);
    $baseFile = basename($fileName);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            if (!empty($file) && !empty($backupAction) && is_dir($backup_path_prefix) && is_readable($fileName)) {

                $fileContents = file_get_contents($fileName);
                $fileLen = strlen($fileContents);

                switch($backupAction) {

                    default:
                    case "download":
                        if (!empty($fileContents)) {
                            header("Content-type: application/sql");
                            header(sprintf("Content-Disposition: attachment; filename=%s; size=%d", $baseFile, $fileLen));
                            print $fileContents;

                            exit; //~ break;
                        }

                        $failureMsg = sprintf("Cannot %s backup file %s (file is empty)", $backupAction, $baseFile);
                        $logAction .= "$failureMsg on page: ";
                        break;

                    case "delete":
                        unlink($fileName);

                        $successMsg = sprintf("Successfully performed %s action on backup file %s", $backupAction, $baseFile);
                        $logAction .= "$successMsg on page: ";
                        break;

                    case "rollback":

                        if (!empty($fileContents)) {

                            include('../common/includes/db_open.php');

                            $rollBackQuery = explode("\n\n\n", $fileContents);

                            $isError = 0;
                            $tables = array();

                            foreach ($rollBackQuery as $query) {
                                $query = trim($query);

                                // no need to use the full query, we do some check only on the first 200 chars
                                $query200 = substr($query, 0, 200);

                                if (!preg_match('/^INSERT\s+INTO\s+.*$/', $query200)) {
                                    continue;
                                }

                                // we extract the <table> from the string: INSERT INTO <table>
                                $table = trim(preg_split('/\s+/', $query200)[2], '`');

                                if (empty($table)) {
                                    continue;
                                }

                                $queries = array(
                                                    sprintf("DELETE FROM `%s`", $table),

                                                    // this is a large SQL query, hopefully database can handle it without overflowing
                                                    $query
                                                );

                                // executing delete/insert queries
                                foreach ($queries as $sql) {
                                    $res = $dbSocket->query($sql);
                                    if (DB::isError($res)) {
                                        $isError++;
                                        break;
                                    }
                                }

                                $tables[] = $table;
                            }

                            include('../common/includes/db_close.php');

                            if ($isError > 0) {
                                $failureMsg = sprintf("Cannot %s backup file %s, please check file availability and permissions",
                                                      $backupAction, $baseFile);
                                $logAction .= "$failureMsg on page: ";
                            } else {
                                $successMsg = sprintf("Successfully performed %s of table(s) [%s] from source file %s",
                                                      $backupAction, implode(", ", $tables), $baseFile);
                                $logAction .= "$successMsg on page: ";
                            }
                        } else {
                            $failureMsg = sprintf("Cannot %s backup file %s (file is empty)", $backupAction, $baseFile);
                            $logAction .= "$failureMsg on page: ";
                        }

                        break;
                }

            } else {
                $failureMsg = sprintf("The requested action cannot be performed");
                $logAction .= "$failureMsg on page: ";
            }


        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    // print HTML prologue
    $title = t('Intro','configbackupmanagebackups.php');
    $help = t('helpPage','configbackupmanagebackups');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    include('include/management/pages_common.php');

    // get backup info
    $backupInfo = array();

    if (is_dir($backup_path_prefix)) {
        $files = scandir($backup_path_prefix);
        if ($orderType == "desc") {
            rsort($files);
        }

        $skipList = array( ".", "..", ".svn", ".git" );
        foreach ($files as $this_file) {

            if (in_array($this_file, $skipList)) {
                continue;
            }

            list($junk, $date, $time) = explode("-", $this_file);

            $fileDate = substr($date, 0, 4) . "-" . substr($date, 4, 2) . "-" . substr($date, 6, 2);
            $fileTime = substr($time, 0, 2) . ":" . substr($time, 2, 2) . ":" . substr($time, 4, 2);

            $fileSize = filesize(sprintf("%s/%s", $backup_path_prefix, $this_file));

            $backupInfo[] = array(
                                    sprintf("%s, %s", $fileDate, $fileTime),
                                    $this_file,
                                    toxbyte($fileSize),
                                 );

        }
    }

    $numrows = count($backupInfo);

    if ($numrows > 0) {

        $csrf_token = dalo_csrf_token();
        $token = array( "type" => "hidden", "name" => "csrf_token", "value" => $csrf_token );

        // print table top
        print_table_top();

        // second line of table header
        printTableHead($cols, $orderBy, $orderType);

        // closes table header, opens table body
        print_table_middle();

        // table content
        $count = 0;
        foreach ($backupInfo as $row) {
            $rowlen = count($row);

            echo "<tr>";

            // print escaped row elements
            for ($i = 0; $i < $rowlen; $i++) {
                printf("<td>%s</td>", htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8'));
            }

            // actions
            echo "<td>";

            // create form for actions
            $components = array( $token );
            $components[] = array( "type" => "hidden", "name" => "action", "value" => "" );
            $components[] = array( "type" => "hidden", "name" => "file", "value" => $row[1] );

            $form = array( "name" => sprintf("form-%d", $count), "hidden" => true );

            open_form($form);

            foreach ($components as $component) {
                print_form_component($component);
            }

            close_form();

            // print actions
            $actions = array();

            foreach ($valid_backupActions as $action => $label) {
                $onclick = sprintf("performAction('form-%s', '%s')", $count, $action);
                $actions[] = sprintf('<a class="tablenovisit" href="#" onclick="%s">%s</a>', $onclick, $label);
            }

            echo implode(", ", $actions);

            echo "</td>";
            echo "</tr>";

            $count++;
        }

        print_table_bottom();

    } else {
        // no backup file(s)
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }

    include('include/config/logging.php');

        $inline_extra_js = <<<EOF
function performAction(formId, action) {
    var f = document.getElementById(formId);
    f.elements['action'].value = action;

    var m = 'Do you really want to ' + action + ' ' + f.elements['file'].value + '?';
    if (confirm(m)) {
        f.submit();
        return false;
    }
}

EOF;

    print_footer_and_html_epilogue($inline_extra_js);
?>
