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

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // custom validation structures
    $valid_tables = array("check", "reply");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            
            $vendor = (array_key_exists('vendor', $_POST) && !empty(str_replace("%", "", trim($_POST['vendor']))))
                    ? str_replace("%", "", trim($_POST['vendor'])) : "";
            $vendor_enc = (!empty($vendor)) ? htmlspecialchars($vendor, ENT_QUOTES, 'UTF-8') : "";

            $attribute = (array_key_exists('attribute', $_POST) && !empty(str_replace("%", "", trim($_POST['attribute']))))
                       ? str_replace("%", "", trim($_POST['attribute'])) : "";
            $attribute_enc = (!empty($attribute)) ? htmlspecialchars($attribute, ENT_QUOTES, 'UTF-8') : "";
            
            $type = (array_key_exists('type', $_POST) && isset($_POST['type']) &&
                     in_array($_POST['type'], $valid_attributeTypes))
                  ? $_POST['type'] : "";

            $op = (array_key_exists('RecommendedOP', $_POST) && isset($_POST['RecommendedOP']) &&
                   in_array($_POST['RecommendedOP'], $valid_ops))
                ? $_POST['RecommendedOP'] : "";
            
            $table = (array_key_exists('RecommendedTable', $_POST) && isset($_POST['RecommendedTable']) &&
                      in_array($_POST['RecommendedTable'], $valid_tables))
                   ? $_POST['RecommendedTable'] : "";

            $helper = (array_key_exists('RecommendedHelper', $_POST) && isset($_POST['RecommendedHelper']) &&
                       in_array($_POST['RecommendedHelper'], $valid_recommendedHelpers))
                    ? $_POST['RecommendedHelper'] : "";

            $tooltip = (array_key_exists('RecommendedTooltip', $_POST) &&
                        !empty(str_replace("%", "", trim($_POST['RecommendedTooltip']))))
                     ? str_replace("%", "", trim($_POST['RecommendedTooltip'])) : "";

            if (empty($vendor) || empty($attribute)) {
                // vendor and attribute are required
                $failureMsg = "vendor and/or attribute are empty or invalid";
                $logAction .= "Failed adding new attribute [$attribute] (possible empty/invalid vendor and/or attribute) on page: ";
            } else {
                include('../common/includes/db_open.php');
                
                $sql = sprintf("SELECT DISTINCT(Vendor) FROM %s WHERE attribute='%s'",
                               $configValues['CONFIG_DB_TBL_DALODICTIONARY'], $dbSocket->escapeSimple($attribute));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";

                $vendors = array();
                while ($row = $res->fetchrow()) {
                    $vendors[] = $row[0];
                }
                
                if (count($vendors) > 0) {
                    // already present
                    $format = "An attribute with the same name is already present in another dictionary (attribute: %s, vendor(s): %s)";
                    $failureMsg = sprintf($format, $attribute_enc, htmlspecialchars(implode(", ", $vendors), ENT_QUOTES, 'UTF-8'));
                    $logAction .= sprintf("Failed to add an attribute [$format] on page: ", $attribute, implode(", ", $vendors));
                    
                } else {
                    
                    $sql = sprintf("INSERT INTO %s (id, Type, Attribute, Value, Format, Vendor, RecommendedOP,
                                                    RecommendedTable, RecommendedHelper, RecommendedTooltip)
                                            VALUES (0, '%s', '%s', '', '', '%s', '%s', '%s', '%s', '%s')",
                                   $configValues['CONFIG_DB_TBL_DALODICTIONARY'],
                                   $dbSocket->escapeSimple($type), $dbSocket->escapeSimple($attribute),
                                   $dbSocket->escapeSimple($vendor), $dbSocket->escapeSimple($op),
                                   $dbSocket->escapeSimple($table), $dbSocket->escapeSimple($helper),
                                   $dbSocket->escapeSimple($tooltip));
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                    
                    if (!DB::isError($res)) {
                        $format = "The new attribute has been inserted in the dictionary (attribute: %s, vendor: %s)";
                        $successMsg = sprintf($format, $attribute_enc, $vendor_enc)
                                    . sprintf(' [<a href="mng-rad-attributes-edit.php?vendor=%s&attribute=%s" title="Edit">%s</a>]',
                                              urlencode($vendor_enc), urlencode($attribute_enc));
                        $logAction .= sprintf("$format on page: ", $attribute, $vendor);
                    } else {
                        $format = "An error occurred when adding the new attribute to a dictionary (attribute: %s, vendor: %s)";
                        $failureMsg = sprintf($format, $attribute_enc, $vendor_enc);
                        $logAction .= sprintf("Failed to add an attribute [$format] on page: ", $attribute, $vendor);
                    }
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
                                        "datalist" => $valid_attributeTypes,
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
                                        "value" => (isset($tooltip) ? $tooltip : "")
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
