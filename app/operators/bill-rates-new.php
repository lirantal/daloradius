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

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    include('../common/includes/db_open.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            // required later
            $currDate = date('Y-m-d H:i:s');
            $currBy = $operator;

            $required_fields = array();

            $ratename = (array_key_exists('ratename', $_POST) && !empty(trim($_POST['ratename'])))
                      ? trim($_POST['ratename']) : "";
            if (empty($ratename)) {
                $required_fields['ratename'] = t('all','RateName');
            } else {
                $ratename_enc = htmlspecialchars($ratename, ENT_QUOTES, 'UTF-8');
            }

            $ratecost = (array_key_exists('ratecost', $_POST) && intval(trim($_POST['ratecost'])) > 0)
                      ? intval(trim($_POST['ratecost'])) : "";
            if (empty($ratecost)) {
                $required_fields['ratecost'] = t('all','RateCost');
            }

            $ratetypenum = (array_key_exists('ratetypenum', $_POST) && intval(trim($_POST['ratetypenum'])) > 0)
                      ? intval(trim($_POST['ratetypenum'])) : "";
            if (empty($ratetypenum)) {
                $required_fields['ratetypenum'] = t('all','RateType') . " (number)";
            }

            $ratetypetime = (array_key_exists('ratetypetime', $_POST) && !empty(trim($_POST['ratetypetime'])) &&
                             in_array(trim($_POST['ratetypetime']), $valid_timeUnits))
                          ? trim($_POST['ratetypetime']) : "";
            if (empty($ratetypetime)) {
                $required_fields['ratetypetime'] = t('all','RateType') . " (time unit)";
            }

            if (count($required_fields) > 0) {
                // required/invalid
                $failureMsg = sprintf("Empty or invalid required field(s) [%s]", implode(", ", array_values($required_fields)));
                $logAction .= "$failureMsg on page: ";
            } else {

                // check if this rate exists
                $sql = sprintf("SELECT COUNT(id) FROM %s WHERE rateName='%s'", $configValues['CONFIG_DB_TBL_DALOBILLINGRATES'],
                                                                               $dbSocket->escapeSimple($ratename));
                $res = $dbSocket->query($sql);

                $exists = intval($res->fetchrow()[0]) == 1;

                if ($exists) {
                    // invalid
                    $failureMsg = sprintf("You have provided an invalid rate name");
                    $logAction .= "$failureMsg on page: ";
                } else {

                    $ratetype = sprintf("%d/%s", $ratetypenum, $ratetypetime);

                    $sql = sprintf("INSERT INTO %s (id, ratename, ratetype, ratecost, creationdate, creationby, updatedate, updateby)
                                            VALUES (0, '%s', '%s', %d, '%s', '%s', NULL, NULL)",
                                   $configValues['CONFIG_DB_TBL_DALOBILLINGRATES'], $dbSocket->escapeSimple($ratename),
                                   $dbSocket->escapeSimple($ratetype), $ratecost, $currDate, $currBy);
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";

                    if (!DB::isError($res)) {
                        $successMsg = sprintf('Successfully inserted new rate (<strong>%s</strong>) '
                                            . '<a href="bill-rates-edit.php?ratename=%s" title="Edit">%s</a>',
                                              $ratename_enc, $ratename_enc, urlencode($ratename_enc));
                        $logAction .= "Successfully inserted new rate [$ratename] on page: ";
                    } else {
                        $failureMsg = "Failed to inserted new rate (<strong>$ratename_enc</strong>)";
                        $logAction .= "Failed to inserted new rate [$ratename] on page: ";
                    }
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
    $extra_css = array();

    $extra_js = array(
        "static/js/ajax.js",
        "static/js/dynamic_attributes.js",
        "static/js/ajaxGeneric.js",
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
