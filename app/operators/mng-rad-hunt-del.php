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

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");
    include_once("include/management/populate_selectbox.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    // load valid huntgroups
    $valid_huntgroups = get_huntgroups();


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $item = (array_key_exists('item', $_POST) && !empty($_POST['item'])) ? $_POST['item'] : "";
    } else {
        $item = (array_key_exists('item', $_REQUEST) && !empty($_REQUEST['item'])) ? $_REQUEST['item'] : "";
    }

    $arr = array();
    $tmp = (!is_array($item)) ? array( $item ) : $item;
    foreach ($tmp as $tmp_item) {
        if (!in_array($tmp_item, array_keys($valid_huntgroups))) {
            continue;
        }

        $arr[] = $tmp_item;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            if (count($arr) == 0) {
                // invalid
                $failureMsg = "Empty or invalid huntgroup(s)";
                $logAction .= sprintf("Failed deleting huntgroup(s) [%s] on page: ", $failureMsg);
            } else {
                include('../common/includes/db_open.php');

                $deleted = 0;
                foreach ($arr as $arr_item) {

                    $internal_id = intval(str_replace("huntgroup-", "", $arr_item));

                    $sql = sprintf("DELETE FROM %s WHERE id=?", $configValues['CONFIG_DB_TBL_RADHG']);
                    $prep = $dbSocket->prepare($sql);
                    $values = array( $internal_id, );
                    $res = $dbSocket->execute($prep, $values);
                    $logDebugSQL .= "$sql;\n";

                    if (!DB::isError($res)) {
                        $deleted++;
                    }
                }

                if ($deleted > 0) {
                    $successMsg = sprintf("Deleted %s huntgroup(s)", $deleted);
                    $logAction .= "$successMsg on page: ";
                } else {
                    // invalid
                    $failureMsg = "Empty or invalid huntgroup(s)";
                    $logAction .= sprintf("Failed deleting huntgroup(s) [%s] on page: ", $failureMsg);
                }

                include('../common/includes/db_close.php');
            }

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }


    // print HTML prologue
    $title = t('Intro','mngradhuntdel.php');
    $help = t('helpPage','mngradhuntdel');

    print_html_prologue($title, $langCode);

    


    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    if (!isset($successMsg)) {

        $options = $valid_huntgroups;

        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        'name' => 'item[]',
                                        'id' => 'item',
                                        'type' => 'select',
                                        'caption' => sprintf("%s:%s (%s)", t('all','HgIPHost'), t('all','HgPortId'), t('all','HgGroupName')),
                                        'options' => $options,
                                        'multiple' => true,
                                        'selected_value' => ((count($arr) > 0) ? $arr : ""),
                                        'size' => 5,
                                     );

        $fieldset0_descriptor = array(
                                        "title" => t('title','HGInfo'),
                                        "disabled" => (count($options) == 0)
                                     );

        open_form();

        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        $input_descriptors1 = array();
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

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_form();

    }

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
