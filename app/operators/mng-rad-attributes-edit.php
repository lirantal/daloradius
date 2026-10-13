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

    include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);
    $operator = $_SESSION['operator_user'];

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    require_once __DIR__ . '/library/dictionary_pages_pdo.php';
    $exists = false;
    $valid_tables = array('check', 'reply');
    $vendor = $attribute = $type = $recommendedOP = $table = $recommendedHelper = $recommendedTooltip = '';
    $is_post = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    $valid_csrf = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token']);
    try {
        $source = $is_post ? $_POST : $_GET;
        $vendor = dalo_dictionary_text(dalo_dictionary_request_field($source, 'vendor'), 32, true);
        $attribute = dalo_dictionary_text(dalo_dictionary_request_field($source, 'attribute'), 64, true);
        $vendor_enc=htmlspecialchars($vendor, ENT_QUOTES, 'UTF-8');
        $attribute_enc=htmlspecialchars($attribute, ENT_QUOTES, 'UTF-8');
        $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        $dictionaryTable = dalo_dictionary_table($configValues);
        if ($is_post) {
            if (!$valid_csrf) { $failureMsg = 'CSRF token error'; }
            else {
                $fields = dalo_dictionary_fields($_POST, $valid_attributeTypes, $valid_ops, $valid_recommendedHelpers);
                if (dalo_dictionary_edit($pdo, $configValues, $fields)) {
                    $successMsg = sprintf('Attribute information has been updated in the dictionary (attribute: %s, vendor: %s)', $attribute_enc, $vendor_enc);
                    $logAction .= 'Updated dictionary metadata on page: ';
                    $logDebugSQL = 'UPDATE configured dictionary SET metadata (bound values);';
                } else { $failureMsg = 'Dictionary attribute no longer exists'; }
            }
        }
        $rows = dalo_dictionary_read($pdo, $dictionaryTable, $vendor, $attribute);
        $exists = count($rows)>0;
        if ($exists) {
            $row = $rows[0];
            $type=$row['Type']; $recommendedOP=$row['RecommendedOP']; $table=$row['RecommendedTable'];
            $recommendedHelper=$row['RecommendedHelper']; $recommendedTooltip=$row['RecommendedTooltip'];
        } elseif (!isset($failureMsg)) { $failureMsg = 'Dictionary attribute not found'; }
    } catch (Throwable $e) { $failureMsg = 'Could not load or update dictionary attribute'; }

    // print HTML prologue
    $title = t('Intro','mngradattributesedit.php');
    $help = t('helpPage','mngradattributesedit');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);


    if (!isset($successMsg) && $exists && $vendor !== '' && $attribute !== '') {

        $fieldset0_descriptor = array(
                                        "title" => t('title','VendorAttribute'),
                                     );


        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        "name" => "vendor",
                                        "type" => "hidden",
                                        "value" => (isset($vendor) ? $vendor : ""),
                                     );

        $input_descriptors0[] = array(
                                        "name" => "attribute",
                                        "type" => "hidden",
                                        "value" => (isset($attribute) ? $attribute : ""),
                                     );

        $input_descriptors0[] = array(
                                        "name" => "vendor_presentation",
                                        "caption" => t('all','VendorName'),
                                        "type" => "text",
                                        "tooltipText" => t('Tooltip','vendorNameTooltip'),
                                        "value" => (isset($vendor) ? $vendor : ""),
                                        "disabled" => true
                                     );

        $input_descriptors0[] = array(
                                        "name" => "attribute_presentation",
                                        "caption" => t('all','Attribute'),
                                        "type" => "text",
                                        "tooltipText" => t('Tooltip','attributeTooltip'),
                                        "value" => (isset($attribute) ? $attribute : ""),
                                        "disabled" => true
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
                                        "name" => "recommendedOP",
                                        "caption" => t('all','RecommendedOP'),
                                        "type" => "text",
                                        "datalist" => $valid_ops,
                                        "value" => ((isset($recommendedOP)) ? $recommendedOP : ""),
                                        "tooltipText" => t('Tooltip','RecommendedOPTooltip'),
                                     );

        $input_descriptors0[] = array(
                                        "name" => "recommendedTable",
                                        "caption" => t('all','RecommendedTable'),
                                        "type" => "text",
                                        "datalist" => $valid_tables,
                                        "value" => ((isset($table)) ? $table : ""),
                                        "tooltipText" => t('Tooltip','RecommendedTableTooltip'),
                                     );

        $input_descriptors0[] = array(
                                        "name" => "recommendedHelper",
                                        "caption" => t('all','RecommendedHelper'),
                                        "type" => "text",
                                        "datalist" => $valid_recommendedHelpers,
                                        "value" => ((isset($recommendedHelper)) ? $recommendedHelper : ""),
                                        "tooltipText" => t('Tooltip','RecommendedHelperTooltip'),
                                     );

        $input_descriptors0[] = array(
                                        "name" => "recommendedTooltip",
                                        "caption" => t('all','RecommendedTooltip'),
                                        "type" => "textarea",
                                        "tooltipText" => t('Tooltip','RecommendedTooltipTooltip'),
                                        "content" => (isset($recommendedTooltip) ? $recommendedTooltip : "")
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

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    print_footer_and_html_epilogue();
