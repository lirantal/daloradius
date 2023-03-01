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
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    $batch_id = array();
    if (isset($_POST['batch_id']) && !empty($_POST['batch_id'])) {
        $batch_id = (is_array($_POST['batch_id'])) ? $_POST['batch_id'] : array( $_POST['batch_id'] );
    }

    $batch_ids = array();
    foreach ($batch_id as $id) {
        $id = intval(trim($id));

        if (in_array($id, $batch_ids)) {
            continue;
        }

        $batch_ids[] = $id;
    }

    $deleted_batches = 0;

    include('../common/includes/db_open.php');

    $valid_batch_names = array();
    $sql = sprintf("SELECT DISTINCT(batch_name) FROM %s ORDER BY batch_name ASC", $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    while ($row = $res->fetchrow()) {
        $valid_batch_names[] = $row[0];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            if (array_key_exists('batch_name', $_POST) && !empty(trim(str_replace("%", "", $_POST['batch_name'])))) {
                $batch_name = trim(str_replace("%", "", $_POST['batch_name']));

                $sql = sprintf("SELECT id FROM %s WHERE batch_name='%s'",
                               $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'],
                               $dbSocket->escapeSimple($batch_name));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";

                $id = intval($res->fetchrow()[0]);
                if (!in_array($id, $batch_ids)) {
                    $batch_ids[] = $id;
                }
            }

            $deleted_usernames = 0;
            if (count($batch_ids) > 0) {

                foreach ($batch_ids as $bid) {

                    // delete batch history
                    $sql0 = sprintf("DELETE FROM %s WHERE id = %d",
                                   $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'], $bid);
                    $res0 = $dbSocket->query($sql0);
                    $logDebugSQL .= "$sql0;\n";

                    // we grab all users which are associated with this batch_id
                    $sql1 = sprintf("SELECT username FROM %s WHERE batch_id = %d",
                                   $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'], $bid);
                    $res1 = $dbSocket->query($sql1);
                    $logDebugSQL .= "$sql1;\n";

                    $usernames = array();
                    while ($row = $res1->fetchrow()) {
                        $usernames[] = $dbSocket->escapeSimple($row[0]);
                    }

                    // setting table-related parameters first
                    switch($configValues['FREERADIUS_VERSION']) {
                        case '1' :
                            $tableSetting['postauth']['user'] = 'user';
                            $tableSetting['postauth']['date'] = 'date';
                            break;
                        case '2' :
                            // down
                        case '3' :
                            // down
                        default  :
                            $tableSetting['postauth']['user'] = 'username';
                            $tableSetting['postauth']['date'] = 'authdate';
                            break;
                    }



                    $sql_format = "DELETE FROM %s WHERE %s IN ('%s')";

                    $sql = sprintf($sql_format, $configValues['CONFIG_DB_TBL_RADPOSTAUTH'],
                                                $tableSetting['postauth']['user'],
                                                implode("', '", $usernames));
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";

                    $tables = array(
                                        $configValues['CONFIG_DB_TBL_RADCHECK'],
                                        $configValues['CONFIG_DB_TBL_RADREPLY'],
                                        $configValues['CONFIG_DB_TBL_DALOUSERINFO'],
                                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                        $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                                        $configValues['CONFIG_DB_TBL_RADACCT']
                                   );

                    foreach ($tables as $table) {
                        $sql = sprintf($sql_format, $table, 'username', implode("', '", $usernames));
                        $res = $dbSocket->query($sql);
                        $logDebugSQL .= "$sql;\n";
                    }

                    $deleted_usernames += count($usernames);
                    $deleted_batches++;
                }

                $successMsg = sprintf("Successfully deleted %d batch(es) [%d user(s)]", $deleted_batches, $deleted_usernames);
                $logAction .= "$successMsg on page: ";

            } else {
                $failureMsg = "You have provided an empty or invalid batch list";
                $logAction = "Provided an empty or invalid batch list (batch(es) deletion) on page: ";
            }

        } else {
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }


    include('../common/includes/db_close.php');



    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // print HTML prologue
    $title = t('Intro','mngbatchdel.php');
    $help = t('helpPage','mngbatchdel');

    print_html_prologue($title, $langCode);



    if (!empty($batch_name) && !is_array($batch_name)) {
        $title .= " :: " . htmlspecialchars($batch_name, ENT_QUOTES, 'UTF-8');
    }


    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    if ($deleted_batches == 0) {
        $options = $valid_batch_names;

        $input_descriptors1 = array();

        $input_descriptors1[0] = array(
                                        "name" => "batch_name",
                                        "caption" => t('all','BatchName'),
                                        "type" => "text",
                                     );

        if (count($options) > 0) {
            $input_descriptor[0]['datalist'] = $options;
        } else {
            $input_descriptor[0]['disabled'] = true;
        }

        $input_descriptors1[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );

        $input_descriptors1[] = array(
                                        'type' => 'submit',
                                        'name' => 'submit',
                                        'value' => t('buttons','apply')
                                     );

        $fieldset1_descriptor = array(
                                        "title" => t('title','BatchRemoval'),
                                        "disabled" => (count($options) == 0)
                                     );

        open_form();

        open_fieldset($fieldset1_descriptor);

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        close_form();

    }

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
