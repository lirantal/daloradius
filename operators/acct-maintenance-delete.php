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

    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');
    
    // init logging variables
    $logAction = "";
    $logDebugSQL = "";
    $log = "visited page: ";

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");
    
    
    include('../common/includes/db_open.php');
    
    // valid min/max dates
    $sql = sprintf("SELECT DATE(MIN(acctstarttime)), DATE(MAX(acctstarttime)) FROM %s", $configValues['CONFIG_DB_TBL_RADACCT']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    list($mindate, $maxdate) = $res->fetchrow();
    
    // valid usernames
    $sql = sprintf("SELECT DISTINCT(username) FROM %s ORDER BY username ASC", $configValues['CONFIG_DB_TBL_RADACCT']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    $valid_usernames = array();
    while ($row = $res->fetchrow()) {
        $valid_usernames[] = $row[0];
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            $required_fields = array();
            $sql_WHERE = array();
            
            $username = (array_key_exists('username', $_POST) && !empty(trim($_POST['username'])) &&
                         in_array(trim($_POST['username']), $valid_usernames))
                      ? trim($_POST['username']) : "";
            if (empty($username)) {
                $required_fields['username'] = t('all','Username');
            } else {
                $username_enc = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
                $sql_WHERE[] = sprintf("username='%s'", $dbSocket->escapeSimple($username));
            }
            
            $startdate = (array_key_exists('startdate', $_POST) && isset($_POST['startdate']) &&
                          preg_match(DATE_REGEX, $_POST['startdate'], $m) !== false &&
                          checkdate($m[2], $m[3], $m[1]))
                       ? $_POST['startdate'] : "";
            if (empty($startdate)) {
                $required_fields['startdate'] = t('all','StartingDate');
            } else {
                $sql_WHERE[] = sprintf("AcctStartTime > '%s'", $startdate);
            }
            
            $enddate = (array_key_exists('enddate', $_POST) && isset($_POST['enddate']) &&
                        preg_match(DATE_REGEX, $_POST['enddate'], $m) !== false &&
                        checkdate($m[2], $m[3], $m[1]))
                     ? $_POST['enddate'] : "";
            if (empty($enddate)) {
                $required_fields['enddate'] = t('all','EndingDate');
            } else {
                $sql_WHERE[] = sprintf("AcctStartTime < '%s'", $enddate);
            }
            
            // further checks
            if (!empty($startdate) && !empty($enddate) && $startdate >= $mindate && $enddate <= $maxdate && $startdate > $enddate) {
                $required_fields['startdate'] = t('all','StartingDate');
                $required_fields['enddate'] = t('all','EndingDate');
            }
            
            if (count($required_fields) > 0) {
                // required/invalid
                $failureMsg = sprintf("Empty or invalid required field(s) [%s]", implode(", ", array_values($required_fields)));
                $logAction .= "$failureMsg on page: ";
            } else {
                
                $sql = sprintf("DELETE FROM %s WHERE %s", $configValues['CONFIG_DB_TBL_RADACCT'],
                                                          implode(" AND ", $sql_WHERE));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                
                if (!DB::isError($res)) {
                    $successMsg = sprintf("Deleted accounting records for user %s [period: %s - %s]",
                                          $username_enc, $startdate, $enddate);
                    $logAction .= "$successMsg on page: ";
                } else {
                    $failureMsg = sprintf("Failed to deleted accounting records for user %s [period: %s - %s]",
                                          $username_enc, $startdate, $enddate);
                    $logAction .= "$failureMsg page: ";
                }
            }
            
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    include('../common/includes/db_close.php');
    

    // print HTML prologue
    $title = t('Intro','acctmaintenancedelete.php');
    $help = t('helpPage','acctmaintenancedelete');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    $options = $valid_usernames;
    array_unshift($options , '');
    
    $input_descriptors0 = array();

    $input_descriptors0[] = array(
                                    'name' => 'username',
                                    'type' => 'select',
                                    'caption' => t('all','Username'),
                                    'options' => $options,
                                    'selected_value' => $username
                                 );

    $input_descriptors0[] = array(
                                    'name' => 'startdate',
                                    'caption' => t('all','StartingDate'),
                                    'type' => 'date',
                                    'value' => $startdate,
                                    'min' => $mindate,
                                    'max' => $maxdate,
                                 );
                                 
    $input_descriptors0[] = array(
                                    'name' => 'enddate',
                                    'caption' => t('all','EndingDate'),
                                    'type' => 'date',
                                    'value' => $enddate,
                                    'min' => $mindate,
                                    'max' => $maxdate,
                                 );
                                 
    $input_descriptors0[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );
    
    $input_descriptors0[] = array(
                                    "type" => "submit",
                                    "name" => "submit",
                                    "value" => t('buttons','apply')
                                  );
    
    $fieldset0_descriptor = array(
                                    "title" => t('title','DeleteRecords'),
                                    "disabled" => (count($valid_usernames) == 0)
                                 );
                                 
    open_form();
    
    open_fieldset($fieldset0_descriptor);

    foreach ($input_descriptors0 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
    
    close_fieldset();
    
    close_form();

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
    
?>
