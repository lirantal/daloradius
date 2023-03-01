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
 *             Miguel García <miguelvisgarcia@gmail.com>
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

            $username = (array_key_exists('username', $_POST) && !empty(trim($_POST['username'])) &&
                         in_array(trim($_POST['username']), $valid_usernames))
                      ? trim($_POST['username']) : "";

            $enddate = (array_key_exists('enddate', $_POST) && isset($_POST['enddate']) &&
                        preg_match(DATE_REGEX, $_POST['enddate'], $m) !== false &&
                        checkdate($m[2], $m[3], $m[1]))
                     ? $_POST['enddate'] : "";

            if (!empty($username)) {

                $username_enc = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

                $sql = sprintf("SELECT COUNT(radacctid) FROM %s WHERE username='%s' AND acctstoptime IS NULL",
                               $configValues['CONFIG_DB_TBL_RADACCT'], $dbSocket->escapeSimple($username));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";

                $numrows = intval($res->fetchrow()[0]);

                if ($numrows > 0) {
                    $sql = sprintf("UPDATE %s SET acctstoptime=NOW(), acctterminatecause='Admin-Reset'
                                     WHERE username='%s' AND acctstoptime IS NULL",
                                   $configValues['CONFIG_DB_TBL_RADACCT'],
                                   $dbSocket->escapeSimple($username));
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";

                    if (!DB::isError($res)) {
                        $successMsg = "Cleaned up stale sessions for user: $username_enc";
                        $logAction .= "Successfully cleaned up stale sessions for username [$username] on page: ";
                    } else {
                        $failureMsg = "Cannot clean up stale sessions for user: $username_enc";
                        $logAction .= "$failureMsg page: ";
                    }

                } else {
                    // nothing to delete
                    $failureMsg = "There are no stale sessions for user [$username_enc]";
                    $logAction .= "Cannot clean up stale sessions for user $username [no stale sessions for this user] on page: ";
                }

            } else if (!empty($enddate)) {
                if ($enddate >= $mindate && $enddate <= $maxdate) {

                    // delete all stale sessions in the database that occur until $enddate
                    $sql = sprintf("DELETE FROM %s
                                          WHERE acctstarttime < '%s'
                                            AND (acctstoptime = '0000-00-00 00:00:00' OR acctstoptime IS NULL)",
                                   $configValues['CONFIG_DB_TBL_RADACCT'], $enddate);
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql\n";

                    if (!DB::isError($res)) {
                        $successMsg = "Cleaned up stale sessions until date: $enddate";
                        $logAction .= "Successfully cleaned up stale sessions until date [$enddate] on page: ";
                    } else {
                        $failureMsg = "Cannot clean up stale sessions until date: $enddate";
                        $logAction .= "$failureMsg page: ";
                    }


                } else {
                    // invalid
                    $failureMsg = "Cannot clean up stale sessions: $enddate is invalid";
                    $logAction .= "$failureMsg page: ";
                }
            } else {
                // invalid
                $failureMsg = "Cannot clean up stale sessions: provided empty/invalid username or ending date";
                $logAction .= "$failureMsg page: ";
            }

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    include('../common/includes/db_close.php');


    // print HTML prologue
    $title = t('Intro','acctmaintenancecleanup.php');
    $help = t('helpPage','acctmaintenancecleanup');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    // set navbar stuff
    $navkeys = array( 'CleanupRecordsByUsername', 'CleanupRecordsByDate', );

    // print navbar controls
    print_tab_header($navkeys);

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

    $fieldset0_descriptor = array(
                                    "title" => t('title','CleanupRecordsByUsername'),
                                    "disabled" => (count($valid_usernames) == 0)
                                 );

    $input_descriptors1 = array();
    $input_descriptors1[] = array(
                                    'name' => 'enddate',
                                    'caption' => t('all','CleanupSessions'),
                                    'type' => 'date',
                                    'value' => $enddate,
                                    'min' => $mindate,
                                    'max' => $maxdate,
                                 );

    $fieldset1_descriptor = array(
                                    "title" => t('title','CleanupRecordsByDate'),
                                    "disabled" => (count($valid_usernames) == 0)
                                 );

    open_form();

    // open tab wrapper
    open_tab_wrapper();

    // open tab 0
    open_tab($navkeys, 0, true);

    open_fieldset($fieldset0_descriptor);

    foreach ($input_descriptors0 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_fieldset();

    close_tab($navkeys, 0);

    // open tab 1
    open_tab($navkeys, 1);

    open_fieldset($fieldset1_descriptor);

    foreach ($input_descriptors1 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_fieldset();

    close_tab($navkeys, 1);

    // close tab wrapper
    close_tab_wrapper();

    $input_descriptors2 = array();
    $input_descriptors2[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );

    $input_descriptors2[] = array(
                                    "type" => "submit",
                                    "name" => "submit",
                                    "value" => t('buttons','apply')
                                  );

    foreach ($input_descriptors2 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_form();

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
