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
    include("../common/includes/layout.php");

    $valid_importStrategies = array(
                                        "insert_or_update" => "insert new/update already-known attributes",
                                        "delete_then_insert" => "delete all already-known, then insert new attributes",
                                        "only_insert_new" => "only insert new attributes"
                                   );

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $importStrategy = (array_key_exists('importStrategy', $_POST) && isset($_POST['importStrategy']) &&
                           in_array($_POST['importStrategy'], array_keys($valid_importStrategies)))
                        ? $_POST['importStrategy'] : array_keys($valid_importStrategies)[0];

        $detectVendor = (array_key_exists('detectVendor', $_POST) && isset($_POST['detectVendor']));
        
        $vendor = (!$detectVendor && array_key_exists('vendor', $_POST) && !empty(str_replace("%", "", trim($_POST['vendor']))))
                ? str_replace("%", "", trim($_POST['vendor'])) : "";
        $vendor_enc = (!empty($vendor)) ? htmlspecialchars($vendor, ENT_QUOTES, 'UTF-8') : "";

        $dictionary = (array_key_exists('dictionary', $_POST) && !empty($_POST['dictionary']))
                    ? $_POST['dictionary'] : "";
        
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            
            if (empty($dictionary) || (!$detectVendor && empty($vendor))) {
                if (empty($dictionary)) {
                    // dictionary cannot be empty
                    $failureMsg = "dictionary cannot be empty";
                } else {
                    // vendor cannot be empty when auto detect is turned off
                    $failureMsg = "vendor cannot be empty when auto-detection is turned off";
                }
                $logAction .= "$failureMsg on page: ";
                
            } else {
                // we break the POST variable (continous string) into an array
                $myDictionary = explode("\n", $dictionary);
                
                $split_regex = '/\t+|\s+/';
                $this_attributes = array();
                $this_vendor = (!$detectVendor && !empty($vendor)) ? $vendor : "";

                foreach ($myDictionary as $line) {
                    $arr = preg_split($split_regex, trim($line));
                    $arrlen = count($arr);
                    
                    //~ we need at least two elements
                    //~ minimum arrlen == 2
                    //~ maximum unknown (because we could have comments)
                    if ($arrlen < 2) {
                        continue;
                    }
                    
                    if ($detectVendor && $arr[0] === "VENDOR") {
                        //~ VENDOR       TestVendor1    1    # this could be a comment
                        $this_vendor = $arr[1];
                        continue;
                    }
                    
                    if ($arr[0] === "ATTRIBUTE") {
                        //~ example: ATTRIBUTE    TestAttr2      2    string    # this could be a comment
                        $attr = $arr[1];
                        $type = ($arrlen >= 4) ? $arr[3] : null;
                            
                        if (!in_array($attr, $this_attributes)) {
                            $this_attributes[$attr] = $type;
                        } else {
                            if ($this_attributes[$attr] == null && $type != null) {
                                $this_attributes[$attr] = $type;
                            }
                        }

                        continue;
                    }
                }
                
                
                if (empty($this_vendor)) {
                    // cannot detect vendor
                    $failureMsg = "vendor cannot be auto-detected from dictionary";
                    $logAction .= "$failureMsg on page: ";
                } else {
                    include('../common/includes/db_open.php');
                    
                    $deleted = 0;
                    $updated = 0;
                    $inserted = 0;
                    
                    if ($importStrategy == "delete_then_insert") {
                        // delete all, attributes will be inserted later
                        $sql = sprintf("SELECT COUNT(id) FROM %s WHERE Vendor='%s'",
                                       $configValues['CONFIG_DB_TBL_DALODICTIONARY'],
                                       $dbSocket->escapeSimple($this_vendor));
                        $res = $dbSocket->query($sql);
                        $logDebugSQL .= "$sql;\n";
                        
                        $deleted = intval($res->fetchrow()[0]);
                        
                        
                        $sql = sprintf("DELETE FROM %s WHERE Vendor='%s'",
                                       $configValues['CONFIG_DB_TBL_DALODICTIONARY'],
                                       $dbSocket->escapeSimple($this_vendor));
                        $res = $dbSocket->query($sql);
                        $logDebugSQL .= "$sql;\n";
                    }
                    
                    foreach ($this_attributes as $this_attribute => $this_type) {
                        $this_type = ($this_type == null) ? "NULL" : sprintf("'%s'", $dbSocket->escapeSimple($this_type));
                        
                        $sql = sprintf("SELECT COUNT(id) FROM %s WHERE Vendor='%s' AND Attribute='%s'",
                                       $configValues['CONFIG_DB_TBL_DALODICTIONARY'],
                                       $dbSocket->escapeSimple($this_vendor), $dbSocket->escapeSimple($this_attribute));
                        $res = $dbSocket->query($sql);
                        $logDebugSQL .= "$sql;\n";
                        
                        $exists = $res->fetchrow()[0] > 0;
                        
                        if ($exists) {
                            // if it exists and the strategy is insert_or_update we update and then continue
                            if ($importStrategy == "insert_or_update") {
                                $sql = sprintf("UPDATE %s SET Type=%s WHERE Vendor='%s' AND Attribute='%s'",
                                               $configValues['CONFIG_DB_TBL_DALODICTIONARY'], $this_type,
                                               $dbSocket->escapeSimple($this_vendor),
                                               $dbSocket->escapeSimple($this_attribute));
                                $res = $dbSocket->query($sql);
                                $logDebugSQL .= "$sql;\n";
                                
                                $updated++;
                            }
                            
                            continue;
                        }
                        
                        // we are here:
                        // if the attribute does not exist hence needs to be inserted or 
                        // if it used to exist but it has been previously deleted
                        // because of the delete_then_insert strategy
                        $sql = sprintf("INSERT INTO %s (Id, Type, Vendor, Attribute)
                                                VALUES (0, %s, '%s', '%s')",
                                        $configValues['CONFIG_DB_TBL_DALODICTIONARY'],
                                        $this_type,
                                        $dbSocket->escapeSimple($this_vendor),
                                        $dbSocket->escapeSimple($this_attribute));
                        $res = $dbSocket->query($sql);
                        $logDebugSQL .= "$sql;\n";
                        
                        $inserted++;
                        
                    }
                    
                    include('../common/includes/db_close.php');
                    
                    $count = count($this_attributes);
                    $format = "processed: %d, deleted: %d, inserted: %d, updated: %d attributes for vendor %s";
                    $successMsg = sprintf($format, $count, $deleted, $inserted, $updated,
                                          htmlspecialchars($this_vendor, ENT_QUOTES, 'UTF-8'));
                    $logAction .= sprintf("$format on page: ", $count, $deleted, $inserted, $updated, $this_vendor);
                }

            }
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }
    

    // print HTML prologue
    $title = t('Intro','mngradattributesimport.php');
    $help = t('helpPage','mngradattributesimport');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    if (!isset($successMsg)) {
        
        $fieldset0_descriptor = array(
                                        "title" => t('title','VendorAttribute'),
                                     );

        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        "caption" => "Dictionary import strategy",
                                        "type" => "select",
                                        "name" => "importStrategy",
                                        "options" => $valid_importStrategies,
                                        "selected_value" => ((isset($failureMsg)) ? $importStrategy : ""),
                                     );
        
        $input_descriptors0[] = array(
                                        "name" => "vendor",
                                        "caption" => t('all','VendorName'),
                                        "type" => "text",
                                        "tooltipText" => t('Tooltip','vendorNameTooltip'),
                                        "value" => (isset($vendor) ? $vendor : ""),
                                        "disabled" => (isset($detectVendor) ? $detectVendor : true)
                                     );
                                     
        $input_descriptors0[] = array(
                                        "name" => "detectVendor",
                                        "caption" => "Auto-detect vendor from dictionary",
                                        "type" => "checkbox",
                                        "checked" => (isset($detectVendor) ? $detectVendor : true),
                                        "onclick" => "document.getElementById('vendor').disabled=document.getElementById('detectVendor').checked"
                                     );

        $input_descriptors0[] = array(
                                        "caption" => t('all','Dictionary'),
                                        "type" => "textarea",
                                        "name" => "dictionary",
                                        "content" => ((isset($failureMsg)) ? $dictionary : ""),
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
