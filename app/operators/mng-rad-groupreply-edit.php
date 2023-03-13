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

    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $item = (array_key_exists('item', $_POST) && !empty(str_replace("%", "", trim($_POST['item']))))
              ? str_replace("%", "", trim($_POST['item'])) : "";
    } else {
        $item = (array_key_exists('item', $_REQUEST) && !empty(str_replace("%", "", trim($_REQUEST['item']))))
              ? str_replace("%", "", trim($_REQUEST['item'])) : "";
    }
    
    $item_prefix = "groupreply-";
    $item_table = $configValues['CONFIG_DB_TBL_RADGROUPREPLY'];
    
    include('../common/includes/db_open.php');
    
    // get valid attributes
    $valid_attributes = array();
    $sql = sprintf("SELECT DISTINCT(attribute)
                      FROM %s
                     WHERE RecommendedTable IS NULL OR RecommendedTable='' OR RecommendedTable=?
                     ORDER BY attribute ASC",
                    $configValues['CONFIG_DB_TBL_DALODICTIONARY']);
    $prep = $dbSocket->prepare($sql);
    $values = array( $item_table, );
    $res = $dbSocket->execute($prep, $values);
    $logDebugSQL .= "$sql;\n";
    
    while ($row = $res->fetchrow()) {
        $valid_attributes[] = $row[0];
    }
    
    // check if item is valid
    if (!empty($item)) {
        $internal_id = intval(str_replace($item_prefix, "", $item));
        $sql = sprintf("SELECT COUNT(id) FROM %s WHERE id=?", $item_table);
        $prep = $dbSocket->prepare($sql);
        $values = array( $internal_id, );
        $res = $dbSocket->execute($prep, $values);
        $logDebugSQL .= "$sql;\n";

        $exists = $res->fetchrow()[0] > 0;
        
    } else {
        $item = "";
        $internal_id = "";
        $exists = false;
    }
    
    
    //feed the sidebar variables
    $selected_groupreply_item = $item;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            if (empty($internal_id)) {
                // required
                $failureMsg = sprintf("Selected an empty/invalid %s item", $item_table);
                $logAction .= "$failureMsg on page: ";
            } else {
                $sql_SET = array();
                $required_fields = array();
                
                $op = (array_key_exists('op', $_POST) && isset($_POST['op']) && in_array($_POST['op'], $valid_ops))
                    ? $_POST['op'] : "";
                
                if (!empty($op)) {
                    $sql_SET[] = sprintf("op='%s'", $op);
                } else {
                    $required_fields['op'] = t('all','Operator');
                }
                
                $groupname = (array_key_exists('groupname', $_POST) && !empty(str_replace("%", "", trim($_POST['groupname']))))
                           ? str_replace("%", "", trim($_POST['groupname'])) : "";
                if (!empty($groupname)) {
                    $sql_SET[] = sprintf("groupname='%s'", $dbSocket->escapeSimple($groupname));
                } else {
                    $required_fields['groupname'] = t('all','Groupname');
                }
                
                $attribute = (array_key_exists('attribute', $_POST) && !empty(str_replace("%", "", trim($_POST['attribute']))))
                           ? str_replace("%", "", trim($_POST['attribute'])) : "";
                if (!empty($attribute)) {
                    $sql_SET[] = sprintf("attribute='%s'", $dbSocket->escapeSimple($attribute));
                } else {
                    $required_fields['attribute'] = t('all','Attribute');
                }
                
                $value = (array_key_exists('value', $_POST) && !empty(str_replace("%", "", trim($_POST['value']))))
                       ? str_replace("%", "", trim($_POST['value'])) : "";
                if (!empty($value)) {
                    $sql_SET[] = sprintf("value='%s'", $dbSocket->escapeSimple($value));
                } else {
                    $required_fields['value'] = t('all','Value');
                }
                
                if (count($required_fields) > 0) {
                    // required/invalid
                    $failureMsg = sprintf("Empty or invalid required field(s) [%s]", implode(", ", array_values($required_fields)));
                    $logAction .= "$failureMsg on page: ";
                } else {
                
                    $sql = sprintf("SELECT COUNT(id) FROM %s WHERE groupname=? AND attribute=? AND value=? AND id<>?",
                                   $item_table);
                    $prep = $dbSocket->prepare($sql);
                    $values = array( $groupname, $attribute, $value, $internal_id );
                    $res = $dbSocket->execute($prep, $values);
                    $logDebugSQL .= "$sql;\n";

                    $exists = $res->fetchrow()[0] > 0;
                    
                    if ($exists) {
                        // already exists
                        $failureMsg = sprintf("Failed to update %s item, duplicate entry", $item_table);
                        $logAction .= sprintf("Failed to update %s item, duplicate entry [%s %s %s (%s)] on page: ",
                                              $item_table, $attribute, $op, $value, $groupname);
                    } else {
                        $sql = sprintf("UPDATE %s SET ", $item_table)
                             . implode(", ", $sql_SET)
                             . sprintf(" WHERE id=%d", $internal_id);
                        $res = $dbSocket->query($sql);
                        $logDebugSQL .= "$sql;\n";
                        
                        if (!DB::isError($res)) {
                            $successMsg = sprintf("Successfully updated %s item", $item_table);
                            $logAction .= sprintf("Successfully updated %s item [%s %s %s (%s)] on page: ",
                                                  $item_table, $attribute, $op, $value, $groupname);
                        } else {
                            $failureMsg = sprintf("Failed to update %s item", $item_table);
                            $logAction .= sprintf("Failed to update %s item [%s %s %s (%s)] on page: ",
                                                  $item_table, $attribute, $op, $value, $groupname);
                        }
                    }
                }
            }
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }
    
    
    if (empty($internal_id)) {
        $failureMsg = sprintf("Selected an empty/invalid %s item", $item_table);
        $logAction .= sprintf("Failed updating (possible empty/invalid %s item) on page: ", $item_table);
    } else {
        $sql = sprintf("SELECT groupname, attribute, op, value FROM %s WHERE id=?", $item_table);
        $prep = $dbSocket->prepare($sql);
        $values = array( $internal_id );
        $res = $dbSocket->execute($prep, $values);
        $logDebugSQL .= "$sql;\n";

        list( $groupname, $attribute, $op, $value ) = $res->fetchrow();
    }
    
    include('../common/includes/db_close.php');

    
    // print HTML prologue
    $title = t('Intro','mngradgroupreplyedit.php');
    $help = t('helpPage','mngradgroupreplyedit');
    
    print_html_prologue($title, $langCode);

    if (!empty($groupname)) {
        $title .= sprintf(" %s", htmlspecialchars($groupname, ENT_QUOTES, 'UTF-8'));
    }

    
    

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    
    if (!empty($internal_id)) {
        
        $input_descriptors0 = array();
        $input_descriptors0[] = array(
                                        'name' => 'groupname',
                                        'caption' => t('all','Groupname'),
                                        'type' => 'text',
                                        'value' => $groupname,
                                        'required' => true
                                     );
                                     
        $input_descriptors0[] = array(
                                        'name' => 'attribute',
                                        'caption' => t('all','Attribute'),
                                        'type' => 'text',
                                        'value' => $attribute,
                                        'required' => true,
                                        'datalist' => $valid_attributes
                                     );
                                     
        $options = $valid_ops;
        $input_descriptors0[] = array(
                                        "name" => "op",
                                        "caption" => t('all','Operator'),
                                        "type" => "select",
                                        "options" => $options,
                                        "selected_value" => $op
                                     );
                                     
        $input_descriptors0[] = array(
                                        'name' => 'value',
                                        'caption' => t('all','Value'),
                                        'type' => 'text',
                                        'value' => $value,
                                        'required' => true,
                                     );
        
        // descriptors 1
        $input_descriptors1 = array();

        $input_descriptors1[] = array(
                                        "name" => "item",
                                        "type" => "hidden",
                                        "value" => sprintf("%s%d", $item_prefix, $internal_id),
                                     );

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
                                        "title" => t('title','GroupInfo'),
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
