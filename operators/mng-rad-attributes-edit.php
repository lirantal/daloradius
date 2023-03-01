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

    function attribute_vendor_exist($dbSocket, $attribute, $vendor) {
        global $configValues, $logDebugSQL;
        
        $sql = sprintf("SELECT COUNT(DISTINCT(id)) FROM %s WHERE attribute='%s' AND vendor='%s'",
                               $configValues['CONFIG_DB_TBL_DALODICTIONARY'],
                               $dbSocket->escapeSimple($attribute),
                               $dbSocket->escapeSimple($vendor));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        return $res->fetchrow()[0] > 0;
    }

    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            $vendor = (array_key_exists('vendor', $_POST) && !empty(str_replace("%", "", trim($_POST['vendor']))))
                    ? str_replace("%", "", trim($_POST['vendor'])) : "";
            $vendor_enc = (!empty($vendor)) ? htmlspecialchars($vendor, ENT_QUOTES, 'UTF-8') : "";

            $attribute = (array_key_exists('attribute', $_POST) && !empty(str_replace("%", "", trim($_POST['attribute']))))
                       ? str_replace("%", "", trim($_POST['attribute'])) : "";
            $attribute_enc = (!empty($attribute)) ? htmlspecialchars($attribute, ENT_QUOTES, 'UTF-8') : "";

            $type = (array_key_exists('type', $_POST) && !empty(trim($_POST['type'])) &&
                     in_array(trim($_POST['type']), $valid_attributeTypes))
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
                $logAction .= "Failed updating attribute [$attribute] (possible empty/invalid vendor and/or attribute) on page: ";
            } else {
                
                include('../common/includes/db_open.php');
                
                $exists = attribute_vendor_exist($dbSocket, $attribute, $vendor);
                
                if (!$exists) {
                    // vendor and/or attribute invalid
                    $failureMsg = "vendor and/or attribute are invalid";
                    $logAction .= "Failed updating attribute [$attribute] (possible invalid vendor and/or attribute) on page: ";
                } else {
                    
                    $sql = sprintf("UPDATE %s
                                       SET Type='%s', RecommendedOP='%s', RecommendedTable='%s',
                                           RecommendedTooltip='%s', RecommendedHelper='%s'
                                     WHERE Vendor='%s' AND Attribute='%s'",
                                   $configValues['CONFIG_DB_TBL_DALODICTIONARY'], $dbSocket->escapeSimple($type),
                                   $dbSocket->escapeSimple($op), $dbSocket->escapeSimple($table),
                                   $dbSocket->escapeSimple($tooltip), $dbSocket->escapeSimple($helper), 
                                   $dbSocket->escapeSimple($vendor), $dbSocket->escapeSimple($attribute));
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                    
                    if (!DB::isError($res)) {
                        $format = "Attribute information has been updated in the dictionary (attribute: %s, vendor: %s)";
                        $successMsg = sprintf($format, $attribute_enc, $vendor_enc);
                        $logAction .= sprintf("$format on page: ", $attribute, $vendor);
                    } else {
                        $format = "An error occurred when updating attribute information in the dictionary (attribute: %s, vendor: %s)";
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
    } else {
        // !POST
        
        $vendor = (array_key_exists('vendor', $_REQUEST) && !empty(str_replace("%", "", trim($_REQUEST['vendor']))))
                ? str_replace("%", "", trim($_REQUEST['vendor'])) : "";
        $vendor_enc = (!empty($vendor)) ? htmlspecialchars($vendor, ENT_QUOTES, 'UTF-8') : "";

        $attribute = (array_key_exists('attribute', $_REQUEST) && !empty(str_replace("%", "", trim($_REQUEST['attribute']))))
                   ? str_replace("%", "", trim($_REQUEST['attribute'])) : "";
        $attribute_enc = (!empty($attribute)) ? htmlspecialchars($attribute, ENT_QUOTES, 'UTF-8') : "";
    }


    // print HTML prologue
    $title = t('Intro','mngradattributesedit.php');
    $help = t('helpPage','mngradattributesedit');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);

    include('../common/includes/db_open.php');
    
    $exists = attribute_vendor_exist($dbSocket, $attribute, $vendor);
                    
    if (!$exists) {
        // vendor and/or attribute invalid
        $failureMsg = "vendor and/or attribute are invalid";
        $logAction .= "Failed updating attribute [$attribute] (possible invalid vendor and/or attribute) on page: ";
        
    } else {

        $sql = sprintf("SELECT Type, Value, Format, RecommendedOP, RecommendedTable, RecommendedHelper, RecommendedTooltip
                          FROM %s WHERE attribute='%s' AND vendor='%s' LIMIT 1",
                       $configValues['CONFIG_DB_TBL_DALODICTIONARY'],
                       $dbSocket->escapeSimple($attribute),
                       $dbSocket->escapeSimple($vendor));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        list($this_Type, $this_Value, $this_Format, $this_OP, $this_Table, $this_Helper, $this_Tooltip) = $res->fetchrow();

    }
    
    include('../common/includes/db_close.php');

    include_once('include/management/actionMessages.php');


    if (!isset($successMsg) && !empty($vendor) && !empty($attribute)) {
        
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
                                        "datalist" => $valid_attributeTypes,
                                        "value" => ((isset($this_Type)) ? $this_Type : ""),
                                        "tooltipText" => t('Tooltip','typeTooltip'),
                                     );
        
        $input_descriptors0[] = array(
                                        "name" => "RecommendedOP",
                                        "caption" => t('all','RecommendedOP'),
                                        "type" => "text",
                                        "datalist" => $valid_ops,
                                        "value" => ((isset($this_OP)) ? $this_OP : ""),
                                        "tooltipText" => t('Tooltip','RecommendedOPTooltip'),
                                     );
        
        $input_descriptors0[] = array(
                                        "name" => "RecommendedTable",
                                        "caption" => t('all','RecommendedTable'),
                                        "type" => "text",
                                        "datalist" => $valid_tables,
                                        "value" => ((isset($this_Table)) ? $this_Table : ""),
                                        "tooltipText" => t('Tooltip','RecommendedTableTooltip'),
                                     );
        
        $input_descriptors0[] = array(
                                        "name" => "RecommendedHelper",
                                        "caption" => t('all','RecommendedHelper'),
                                        "type" => "text",
                                        "datalist" => $valid_recommendedHelpers,
                                        "value" => ((isset($this_Helper)) ? $this_Helper : ""),
                                        "tooltipText" => t('Tooltip','RecommendedHelperTooltip'),
                                     );
        
        $input_descriptors0[] = array(
                                        "name" => "RecommendedTooltip",
                                        "caption" => t('all','RecommendedTooltip'),
                                        "type" => "textarea",
                                        "tooltipText" => t('Tooltip','RecommendedTooltipTooltip'),
                                        "value" => (isset($this_Tooltip) ? $this_Tooltip : "")
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

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
