<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
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
 * Authors:    Liran Tal <liran@lirantal.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');
    require_once('../common/includes/pdo_connection.php');
    require_once('library/batch_delete.php');

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    $batch_name = '';
    $deleted_batches = 0;

    require_once('library/catalog_reads_pdo.php');
    $valid_batch_names = array(); $catalog_pdo = null;
    try {
        $catalog_pdo = dalo_catalog_read_open($configValues);
        $valid_batch_names = dalo_catalog_read_options($catalog_pdo, $configValues, 'CONFIG_DB_TBL_DALOBATCHHISTORY', 'batch_name', true);
    } catch (Throwable $error) {
        dalo_catalog_read_failure($error);
    } finally { $catalog_pdo = null; }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            try {
                $inputName = $_POST['batch_name'] ?? '';
                if (!is_string($inputName)) {
                    throw new InvalidArgumentException('Invalid batch name');
                }
                $batch_name = trim($inputName);
                $ids = dalo_batch_delete_ids($_POST['batch_id'] ?? null);
                $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                list($deleted_batches, $deleted_usernames) = dalo_delete_user_batches(
                    $pdo, $configValues, $ids, $batch_name);
                $successMsg = sprintf('Successfully deleted %d batch(es) [%d user(s)]',
                                      $deleted_batches, $deleted_usernames);
                $logAction .= "$successMsg on page: ";
                $logDebugSQL .= "Batch users and dependents deleted (PDO transaction);\n";
            } catch (Throwable $error) {
                $failureMsg = 'Failed to delete batch; no rows were deleted';
                $logAction .= 'Failed atomic batch deletion on page: ';
                error_log('Batch deletion failed (' . get_class($error) . ')');
            }
        } else {
            $failureMsg = 'CSRF token error';
            $logAction .= "$failureMsg on page: ";
        }
    }





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
