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
    include_once("include/management/populate_selectbox.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";
    
    // load valid ippools
    $valid_ippools = get_ippools();
    

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $item = (array_key_exists('item', $_POST) && !empty(str_replace("%", "", trim($_POST['item']))))
              ? str_replace("%", "", trim($_POST['item'])) : "";
    } else {
        $item = (array_key_exists('item', $_REQUEST) && !empty(str_replace("%", "", trim($_REQUEST['item']))))
              ? str_replace("%", "", trim($_REQUEST['item'])) : "";
    }

    $exists = in_array($item, array_keys($valid_ippools));
    
    if (!$exists) {
        // we reset the rate if it does not exist
        $item = "";
        $internal_id = "";
    } else {
        $internal_id = intval(str_replace("ippool-", "", $item));
    }

    //feed the sidebar variables
    $selected_ippool = $item;


    include('../common/includes/db_open.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            if (empty($internal_id)) {
                // required
                $failureMsg = sprintf("Selected an empty/invalid ippool item");
                $logAction .= "$failureMsg on page: ";
            } else {
            
                $pool_name = (array_key_exists('pool_name', $_POST) && !empty(str_replace("%", "", trim($_POST['pool_name']))))
                           ? str_replace("%", "", trim($_POST['pool_name'])) : "";
                          
                $framedipaddress = (array_key_exists('framedipaddress', $_POST) && !empty(trim($_POST['framedipaddress'])) &&
                                    filter_var(trim($_POST['framedipaddress']), FILTER_VALIDATE_IP) !== false)
                                 ? trim($_POST['framedipaddress']) : "";
                
                if (empty($framedipaddress) || empty($pool_name)) {
                    // required
                    $failureMsg = sprintf("Empty/invalid %s and/or %s", t('all','PoolName'), t('all','IPAddress'));
                    $logAction .= "$failureMsg on page: ";
                } else {
                
                    $sql = sprintf("SELECT COUNT(id)
                                      FROM %s
                                     WHERE framedipaddress=? AND id<>?", $configValues['CONFIG_DB_TBL_RADIPPOOL']);
                    $prep = $dbSocket->prepare($sql);
                    $values = array( $framedipaddress, $internal_id );
                    $res = $dbSocket->execute($prep, $values);
                    $logDebugSQL .= "$sql;\n";

                    $exists = $res->fetchrow()[0] > 0;

                     if ($exists) {
                        // invalid
                        $failureMsg = sprintf("The chosen %s is already contained in a pool", t('all','IPAddress'));
                        $logAction .= "$failureMsg on page: ";
                    } else {
                        $sql = sprintf("UPDATE %s
                                           SET pool_name=?, framedipaddress=?
                                         WHERE id=?", $configValues['CONFIG_DB_TBL_RADIPPOOL']);
                        $prep = $dbSocket->prepare($sql);
                        $values = array( $pool_name, $framedipaddress, $internal_id );
                        $res = $dbSocket->execute($prep, $values);
                        $logDebugSQL .= "$sql;\n";

                        if (!DB::isError($res)) {
                            $successMsg = "Successfully updated ippool item";
                            $logAction .= "Successfully updated ippool item [$framedipaddress, $pool_name] on page: ";
                        } else {
                            $failureMsg = "Failed to update ippool item";
                            $logAction .= "Failed to inserted new ippool [$framedipaddress, $pool_name] on page: ";
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
        $failureMsg = sprintf("Selected an empty/invalid ippool element");
        $logAction .= "Failed updating ippool element (possible empty or invalid ippool element id) on page: ";
    } else {
        $sql = sprintf("SELECT pool_name, framedipaddress
                          FROM %s
                         WHERE id=?", $configValues['CONFIG_DB_TBL_RADIPPOOL']);
        $prep = $dbSocket->prepare($sql);
        $values = array( $internal_id );
        $res = $dbSocket->execute($prep, $values);
        $logDebugSQL .= "$sql;\n";

        list( $pool_name, $framedipaddress ) = $res->fetchrow();
    }

    include('../common/includes/db_close.php');
    

    // print HTML prologue
    $title = t('Intro','mngradippoolnew.php');
    $help = t('helpPage','mngradippoolnew');
    
    print_html_prologue($title, $langCode);

    


    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    
    
    if (!empty($internal_id)) {

        // descriptors 0
        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        'name' => 'pool_name',
                                        'caption' => t('all','PoolName'),
                                        'type' => 'text',
                                        'value' => $pool_name,
                                        'required' => true
                                     );
                                     
        $input_descriptors0[] = array(
                                        'name' => 'framedipaddress',
                                        'caption' => t('all','IPAddress'),
                                        'type' => 'text',
                                        'value' => $framedipaddress,
                                        'pattern' => trim(IP_REGEX, '/'),
                                        'required' => true
                                     );
                                     
        // descriptors 1
        $input_descriptors1 = array();

        $input_descriptors1[] = array(
                                        "name" => "item",
                                        "type" => "hidden",
                                        "value" => sprintf("ippool-%d", $internal_id),
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
                                        "title" => t('title','IPPoolInfo'),
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
