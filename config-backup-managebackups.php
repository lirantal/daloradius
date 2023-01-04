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
    include_once('library/config_read.php');

    // init logging variables
    $logAction = "";
    $logDebugSQL = "";

    include_once("lang/main.php");
    include("library/validation.php");
    include("library/layout.php");
    include_once("include/management/functions.php");

    $file = (array_key_exists('file', $_POST) && isset($_POST['file'])) ? $_POST['file'] : "";

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
    $filePath = $configValues['CONFIG_PATH_DALO_VARIABLE_DATA'] . "/backup";
    $fileName = sprintf("%s/%s", $filePath, $file);
    $baseFile = basename($fileName);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            
            if (!empty($file) && !empty($backupAction) && is_dir($filePath) && is_readable($fileName)) {
        
                $fileContents = file_get_contents($fileName);
                $fileLen = strlen($fileContents);
                
                if (!empty($fileContents)) {
                
                    switch($backupAction) {
                    
                        default:
                        case "download":
                        
                            header("Content-type: application/sql");
                            header(sprintf("Content-Disposition: attachment; filename=%s; size=%d", $baseFile, $fileLen));
                            print $fileContents;

                            exit; //~ break;
                    
                        case "delete":
                            unlink($fileName);
                            $successMsg = sprintf("Successfully performed %s action on backup file %s",
                                                  $backupAction, $baseFile);
                            $logAction .= "$successMsg on page: ";
                            
                            break;
                    
                        case "rollback":
                        
                            include('library/opendb.php');

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
                            
                            include('library/closedb.php');

                            if ($isError > 0) {
                                $failureMsg = sprintf("Cannot %s backup file %s, please check file availability and permissions",
                                                      $backupAction, $baseFile);
                                $logAction .= "$failureMsg on page: ";
                            } else {
                                $successMsg = sprintf("Successfully performed %s of table(s) [%s] from source file %s",
                                                      $backupAction, implode(", ", $tables), $baseFile);
                                $logAction .= "$successMsg on page: ";
                            }
                            
                            break;
                    }
                    
                } else {
                    $failureMsg = sprintf("Cannot %s backup file %s, please check file availability and permissions",
                                          $backupAction, $baseFile);
                    $logAction .= "$failureMsg on page: ";
                }

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

    include("menu-config-backup.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    include('include/management/pages_common.php');
    
    // get backup info
    $backupInfo = array();
    
    if (is_dir($filePath)) {
        $files = scandir($filePath);
        if ($orderType == "desc") {
            rsort($files);
        }
    
        $skipList = array( ".", "..", ".svn", ".git" );
        foreach ($files as $file) {
            
            if (in_array($file, $skipList)) {
                continue;
            }

            list($junk, $date, $time) = explode("-", $file);
            
            $fileDate = substr($date, 0, 4) . "-" . substr($date, 4, 2) . "-" . substr($date, 6, 2);
            $fileTime = substr($time, 0, 2) . ":" . substr($time, 2, 2) . ":" . substr($time, 4, 2);

            $fileSize = filesize($filePath."/".$file);
            
            $backupInfo[] = array(
                                    sprintf("%s, %s", $fileDate, $fileTime),
                                    $file,
                                    toxbyte($fileSize),
                                 );
            
        }
    }
    
    $numrows = count($backupInfo);
    
    if ($numrows > 0) {

        $csrf_token = dalo_csrf_token();
        $token = array( "type" => "hidden", "name" => "csrf_token", "value" => $csrf_token );

        echo '<table border="0" class="table1">';

        echo "<tr>";
        printTableHead($cols, $orderBy, $orderType);
        echo "</tr>";

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

        echo '</table>';

    } else {
        // no backup file(s)
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
    
    include('include/config/logging.php');
    
        $inline_extra_js = "
function performAction(formId, action) {
    var f = document.getElementById(formId);
    f.elements['action'].value = action;
    
    var m = 'Do you really want to ' + action + ' ' + f.elements['file'].value + '?';
    if (confirm(m)) {
        f.submit();
        return false;
    }
}";
    
    print_footer_and_html_epilogue($inline_extra_js);
?>
