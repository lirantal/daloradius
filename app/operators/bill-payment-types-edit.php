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
 *             Filippo Maria Del Prete <filippo.delprete@gmail.com>
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

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";


    include('../common/includes/db_open.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $paymentname = (array_key_exists('paymentname', $_POST) && !empty(str_replace("%", "", trim($_POST['paymentname']))))
                     ? str_replace("%", "", trim($_POST['paymentname'])) : "";
    } else {
        $paymentname = (array_key_exists('paymentname', $_REQUEST) && !empty(str_replace("%", "", trim($_REQUEST['paymentname']))))
                     ? str_replace("%", "", trim($_REQUEST['paymentname'])) : "";
    }


    // check if this payment name exists
    $sql = sprintf("SELECT COUNT(id) FROM %s WHERE value='%s'", $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'],
                                                              $dbSocket->escapeSimple($paymentname));
    $res = $dbSocket->query($sql);

    $exists = intval($res->fetchrow()[0]) == 1;

    if (!$exists) {
        // we reset the payment name if it does not exist
        $paymentname = "";
    }

    $paymentname_enc = (!empty($paymentname)) ? htmlspecialchars($paymentname, ENT_QUOTES, 'UTF-8') : "";

    //feed the sidebar variables
    $edit_paymentname = $paymentname_enc;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            if (empty($paymentname)) {
                // required
                $failureMsg = "invalid or empty payment type, please specify a valid payment type to edit.";
                $logAction .= "invalid or empty payment type on page: ";
            } else {
                $sql_SET = array();

                // required later
                $currDate = date('Y-m-d H:i:s');
                $currBy = $operator;

                $sql_SET[] = sprintf("updatedate='%s'", $currDate);
                $sql_SET[] = sprintf("updateby='%s'", $currBy);

                $paymentnotes = (array_key_exists('paymentnotes', $_POST) && !empty(trim($_POST['paymentnotes'])))
                              ? trim($_POST['paymentnotes']) : "";
                if (!empty($paymentnotes)) {
                    $sql_SET[] = sprintf("notes='%s'", $dbSocket->escapeSimple($paymentnotes));
                }

                $sql = sprintf("UPDATE %s SET ", $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'])
                     . implode(", ", $sql_SET)
                     . sprintf(" WHERE value='%s'", $dbSocket->escapeSimple($paymentname));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";

                if (!DB::isError($res)) {
                    $successMsg = "Successfully updated payment type (<strong>$paymentname_enc</strong>)";
                    $logAction .= "Successfully updated payment type [$paymentname] on page: ";
                } else {
                    $failureMsg = "Failed to updated payment type (<strong>$paymentname_enc</strong>)";
                    $logAction .= "Failed to updated payment type [$paymentname] on page: ";
                }
            }

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }


    if (empty($paymentname)) {
        $failureMsg = "invalid or empty payment type entered, please specify a valid payment type to edit.";
        $logAction .= "$failureMsg on page: ";
    } else {

        $sql = sprintf("SELECT id, notes, creationdate, creationby, updatedate, updateby FROM %s WHERE value='%s'",
                       $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'], $dbSocket->escapeSimple($paymentname));



        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        $row = $res->fetchrow();

        list( $id, $notes, $creationdate, $creationby, $updatedate, $updateby ) = $row;

    }

    include('../common/includes/db_close.php');


    // print HTML prologue
    $extra_css = array();

    $extra_js = array(
        "static/js/ajax.js",
        "static/js/dynamic_attributes.js",
        "static/js/ajaxGeneric.js",
    );

    $title = t('Intro','paymenttypesedit.php');
    $help = t('helpPage','paymenttypesedit');

    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    if (!empty($paymentname)) {
        $title .= ":: $paymentname_enc";
    }

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    if (!empty($paymentname)) {
        // descriptors 0
        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        'name' => 'paymentname-presentation',
                                        'caption' => t('all','PayTypeName'),
                                        'type' => 'text',
                                        'disabled' => true,
                                        'value' => $paymentname,
                                        'tooltipText' => t('Tooltip','paymentTypeTooltip'),
                                     );

        $input_descriptors0[] = array(
                                        "name" => "paymentnotes",
                                        "caption" => t('all','PayTypeNotes'),
                                        "type" => "textarea",
                                        "content" => $paymentnotes,
                                        'tooltipText' => t('Tooltip','paymentTypeNotesTooltip'),
                                     );

        $input_descriptors1 = array();

        $input_descriptors1[] = array( 'name' => 'creationdate', 'caption' => t('all','CreationDate'), 'type' => 'datetime-local',
                                       'disabled' => true, 'value' => ((isset($creationdate)) ? $creationdate : '') );
        $input_descriptors1[] = array( 'name' => 'creationby', 'caption' => t('all','CreationBy'), 'type' => 'text',
                                       'disabled' => true, 'value' => ((isset($creationby)) ? $creationby : '') );
        $input_descriptors1[] = array( 'name' => 'updatedate', 'caption' => t('all','UpdateDate'), 'type' => 'datetime-local',
                                       'disabled' => true, 'value' => ((isset($updatedate)) ? $updatedate : '') );
        $input_descriptors1[] = array( 'name' => 'updateby', 'caption' => t('all','UpdateBy'), 'type' => 'text',
                                       'disabled' => true, 'value' => ((isset($updateby)) ? $updateby : '') );

        $input_descriptors2 = array();

        $input_descriptors2[] = array(
                                        "name" => "paymentname",
                                        "type" => "hidden",
                                        "value" => $paymentname,
                                     );

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


        open_form();

        // fieldset 0
        $fieldset0_descriptor = array(
                                        "title" => t('title','PayTypeInfo'),
                                     );

        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        // fieldset 1
        $fieldset1_descriptor = array(
                                        "title" => "Other Information",
                                     );

        open_fieldset($fieldset1_descriptor);

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        foreach ($input_descriptors2 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_form();
    }

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
