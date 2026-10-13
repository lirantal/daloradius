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

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    require_once 'library/billing_rates_pdo.php';
    $ratename = $ratecost = $ratetypenum = $ratetypetime = '';
    foreach (array('ratename','ratecost','ratetypenum','ratetypetime') as $key) {
        if (isset($_POST[$key]) && is_string($_POST[$key])) { $$key = trim($_POST[$key]); }
    }
    $ratename_enc = htmlspecialchars($ratename, ENT_QUOTES, 'UTF-8');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) || !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
        } else {
            $pdo = null;
            try {
                $pdo = dalo_catalog_read_open($configValues);
                dalo_rate_mutate($pdo, $configValues, 'new', $_POST['ratename'] ?? '', $_POST, $operator);
                $successMsg = sprintf('Successfully inserted new rate (<strong>%s</strong>) <a href="bill-rates-edit.php?ratename=%s" title="Edit">%s</a>',
                    $ratename_enc, htmlspecialchars(urlencode($ratename), ENT_QUOTES, 'UTF-8'), $ratename_enc);
                $logAction .= 'Successfully inserted new rate on page: ';
            } catch (Throwable $error) {
                $failureMsg = 'Failed to insert rate; check the fields and current state before retrying';
                $logAction .= 'Rate create failed [' . get_class($error) . '] on page: ';
            } finally { $pdo = null; }
        }
    }

    // print HTML prologue
    $extra_css = array();

    $extra_js = array(
    );

    $title = t('Intro','billratesnew.php');
    $help = t('helpPage','billratesnew');

    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    if (!isset($successMsg)) {
        // descriptors 0
        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        'name' => 'ratename',
                                        'caption' => t('all','RateName'),
                                        'type' => 'text',
                                        'value' => $ratename,
                                     );

        $input_descriptors0[] = array(
                                        "name" => "ratetypenum",
                                        "caption" => t('all','RateType') . " (number)",
                                        "type" => "number",
                                        "value" => $ratetypenum,
                                        "min" => 1,
                                     );

        $options = $valid_timeUnits;
        array_unshift($options , '');
        $input_descriptors0[] = array(
                                        "type" =>"select",
                                        "name" => "ratetypetime",
                                        "caption" => t('all','RateType') . " (time unit)",
                                        "options" => $options,
                                        "selected_value" => $ratetypetime,
                                        "tooltipText" => t('Tooltip','rateTypeTooltip')
                                     );

        $input_descriptors0[] = array(
                                        "name" => "ratecost",
                                        "caption" => t('all','RateCost'),
                                        "type" => "number",
                                        "value" => $ratecost,
                                        "min" => 1,
                                        "tooltipText" => t('Tooltip','rateCostTooltip')
                                     );


        // descriptors 1
        $input_descriptors1 = array();

        $input_descriptors1[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );

        $input_descriptors1[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                      );

        open_form();

        // fieldset 0
        $fieldset0_descriptor = array(
                                        "title" => t('title','RateInfo'),
                                     );

        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_form();

    }

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
