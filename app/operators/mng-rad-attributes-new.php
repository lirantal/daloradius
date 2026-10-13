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

    include_once('../common/includes/config_read.php');
    include('library/check_operator_perm.php');

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");

    require_once __DIR__ . '/library/dictionary_pages_pdo.php';
    $valid_tables = array('check', 'reply');
    $vendor = $attribute = $type = $op = $table = $helper = $tooltip = '';
    $valid_csrf = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token']);
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        if (!$valid_csrf) { $failureMsg = 'CSRF token error'; }
        else {
            try {
                $fields = dalo_dictionary_fields($_POST, $valid_attributeTypes, $valid_ops, $valid_recommendedHelpers);
                $vendor=$fields['vendor']; $attribute=$fields['attribute']; $type=$fields['type'];
                $op=$fields['recommendedOP']; $table=$fields['recommendedTable'];
                $helper=$fields['recommendedHelper']; $tooltip=$fields['recommendedTooltip'];
                $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                if (dalo_dictionary_create($pdo, $configValues, $fields)) {
                    $url = 'mng-rad-attributes-edit.php?' . http_build_query(array('vendor'=>$vendor,'attribute'=>$attribute), '', '&', PHP_QUERY_RFC3986);
                    $vendor_enc=htmlspecialchars($vendor, ENT_QUOTES, 'UTF-8');
                    $attribute_enc=htmlspecialchars($attribute, ENT_QUOTES, 'UTF-8');
                    $successMsg = sprintf('The new attribute has been inserted in the dictionary (attribute: %s, vendor: %s) [<a href="%s" title="Edit">%s</a>]',
                                          $attribute_enc, $vendor_enc, htmlspecialchars($url, ENT_QUOTES, 'UTF-8'), $attribute_enc);
                    $logAction .= 'Added dictionary attribute on page: ';
                } else { $failureMsg = 'An attribute with the same name is already present in the dictionary'; }
                $logDebugSQL = 'dictionary existence check; INSERT INTO configured dictionary (bound values);';
            } catch (Throwable $e) { $failureMsg = 'Could not add dictionary attribute'; }
        }
    }

    // print HTML prologue
    $title = t('Intro','mngradattributesnew.php');
    $help = t('helpPage','mngradattributesnew');

    print_html_prologue($title, $langCode);




    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    if (!isset($successMsg)) {

        $fieldset0_descriptor = array(
                                        "title" => t('title','VendorAttribute'),
                                     );


        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        "name" => "vendor",
                                        "caption" => t('all','VendorName'),
                                        "type" => "text",
                                        "tooltipText" => t('Tooltip','vendorNameTooltip'),
                                        "value" => (isset($vendor) ? $vendor : "")
                                     );

        $input_descriptors0[] = array(
                                        "name" => "attribute",
                                        "caption" => t('all','Attribute'),
                                        "type" => "text",
                                        "tooltipText" => t('Tooltip','attributeTooltip'),
                                        "value" => (isset($attribute) ? $attribute : "")
                                     );

        $input_descriptors0[] = array(
                                        "name" => "type",
                                        "caption" => t('all','Type'),
                                        "type" => "text",
                                        "datalist" => $datalist_attributeTypes,
                                        "value" => ((isset($type)) ? $type : ""),
                                        "tooltipText" => t('Tooltip','typeTooltip'),
                                     );

        $input_descriptors0[] = array(
                                        "name" => "RecommendedOP",
                                        "caption" => t('all','RecommendedOP'),
                                        "type" => "text",
                                        "datalist" => $valid_ops,
                                        "value" => ((isset($op)) ? $op : ""),
                                        "tooltipText" => t('Tooltip','RecommendedOPTooltip'),
                                     );

        $input_descriptors0[] = array(
                                        "name" => "RecommendedTable",
                                        "caption" => t('all','RecommendedTable'),
                                        "type" => "text",
                                        "datalist" => $valid_tables,
                                        "value" => ((isset($table)) ? $table : ""),
                                        "tooltipText" => t('Tooltip','RecommendedTableTooltip'),
                                     );

        $input_descriptors0[] = array(
                                        "name" => "RecommendedHelper",
                                        "caption" => t('all','RecommendedHelper'),
                                        "type" => "text",
                                        "datalist" => $valid_recommendedHelpers,
                                        "value" => ((isset($helper)) ? $helper : ""),
                                        "tooltipText" => t('Tooltip','RecommendedHelperTooltip'),
                                     );

        $input_descriptors0[] = array(
                                        "name" => "RecommendedTooltip",
                                        "caption" => t('all','RecommendedTooltip'),
                                        "type" => "textarea",
                                        "tooltipText" => t('Tooltip','RecommendedTooltipTooltip'),
                                        "content" => (isset($tooltip) ? $tooltip : "")
                                     );

        $input_descriptors0[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );

        $input_descriptors0[] = array(
                                        'type' => 'submit',
                                        'name' => 'submit',
                                        'value' => t('buttons','apply')
                                     );

        open_form();

        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        close_form();
    }

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
