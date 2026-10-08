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


    require_once('library/payment_types_pdo.php');
    $paymentname = ''; $paymentname_enc = ''; $edit_paymentname = ''; $paymentnotes = ''; $type_pdo = null;
    try {
        $input = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
        $paymentname = dalo_payment_type_text(dalo_payment_scalar($input,'paymentname'),32,true);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) || !dalo_check_csrf_token($_POST['csrf_token'])) {
                $failureMsg = 'CSRF token error';
            } else {
                $paymentnotes = dalo_payment_type_text(dalo_payment_scalar($_POST,'paymentnotes'),128);
                $type_pdo = dalo_payment_open($configValues);
                dalo_payment_type_mutate($type_pdo,$configValues,'edit',$paymentname,$paymentnotes,$operator);
                $paymentname_enc = htmlspecialchars($paymentname, ENT_QUOTES, 'UTF-8');
                $successMsg = "Successfully updated payment type (<strong>$paymentname_enc</strong>)";
                $logAction .= 'Successful payment type mutation on page: ';
                $logDebugSQL .= 'Payment type mutation (PDO transaction, bound values);\n';
            }
        }
    } catch (Throwable $error) {
        $failureMsg = 'Failed to update payment type; verify its state before retrying';
        $logAction .= 'Payment type mutation failed [' . get_class($error) . '] on page: ';
    } finally { $type_pdo = null; }
    if ($paymentname !== '') {
        try {
            $type_pdo = dalo_payment_open($configValues);
            list($id,$paymentname,$paymentnotes,$creationdate,$creationby,$updatedate,$updateby) = dalo_payment_type_read($type_pdo,$configValues,$paymentname);
        } catch (DomainException $error) {
            if (!isset($failureMsg)) { $failureMsg = 'invalid or empty payment type entered, please specify a valid payment type to edit.'; }
            $paymentname = '';
        } catch (Throwable $error) { dalo_payment_type_read_failure($error); $paymentname = ''; }
        finally { $type_pdo = null; }
    }
    $paymentname_enc = $paymentname !== '' ? htmlspecialchars($paymentname, ENT_QUOTES, 'UTF-8') : '';
    $edit_paymentname = $paymentname;

    // print HTML prologue
    $extra_css = array();

    $extra_js = array(
    );

    $title = t('Intro','paymenttypesedit.php');
    $help = t('helpPage','paymenttypesedit');

    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    if ($paymentname !== '') {
        $title .= ":: $paymentname_enc";
    }

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    if ($paymentname !== '') {
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
