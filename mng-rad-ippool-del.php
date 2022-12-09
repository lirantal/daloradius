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
    include_once('library/config_read.php');
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";


    include('library/opendb.php');

    // load valid_ids
    $sql = sprintf("SELECT id FROM %s", $configValues['CONFIG_DB_TBL_RADIPPOOL']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    $valid_ids = array();
    while ($row = $res->fetchrow()) {
        $valid_ids[] = intval($row[0]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            
            $arr = (!is_array($_POST['record_id'])) ? array( trim($_POST['record_id']) ) : $_POST['record_id'];
            
            
            if (count($arr) > 0) {
                
                $ids = array();
                
                // pre-validate
                foreach ($arr as $id) {
                    
                    $m = array();
                    if (preg_match("/^record-([0-9]+)$/", $id, $m) === false) {
                        continue;
                    }
                    
                    $id = intval($m[1]);
                    if (in_array($id, $valid_ids) && !in_array($id, $ids)) {
                        $ids[] = $id;
                    }
                }
                
                // execute delete
                if (count($ids) > 0) {
                    
                    $sql = sprintf("DELETE FROM %s WHERE id IN (%s)",
                                   $configValues['CONFIG_DB_TBL_RADIPPOOL'], implode(", ", $ids));
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                    
                    if (!DB::isError($res)) {
                        $successMsg = sprintf("Deleted %s IP pool record(s)", $res);
                        $logAction .= "$successMsg on page: ";
                    } else {
                        // DB Error
                        $successMsg = "Error when deleting IP pool record(s)";
                        $logAction .= "$successMsg on page: ";
                    }
                    
                } else {
                    // invalid
                    $failureMsg = "Empty or invalid IP pool element(s)";
                    $logAction .= sprintf("Failed deleting IP pool record(s) [%s] on page: ", $failureMsg);
                }
                
            } else {
                // invalid
                $failureMsg = "Empty or invalid IP pool element(s)";
                $logAction .= sprintf("Failed deleting IP pool record(s) [%s] on page: ", $failureMsg);
            }
            
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }


    $options = array();
    $options_format = "%s: [%s]";

    $sql = sprintf("SELECT id, pool_name, framedipaddress FROM %s ORDER BY pool_name, framedipaddress ASC",
                   $configValues['CONFIG_DB_TBL_RADIPPOOL']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    while ($row = $res->fetchrow()) {
        list($id, $pool_name, $framedipaddress) = $row;
        $key = "record-" . $id;
        $options[$key] = sprintf($options_format, $pool_name, $framedipaddress);
    }
    
    include('library/closedb.php');

    include_once("lang/main.php");
    include("library/layout.php");

    // print HTML prologue
    
    $title = t('Intro','mngradippooldel.php');
    $help = t('helpPage','mngradippooldel');
    
    print_html_prologue($title, $langCode);

    include ("menu-mng-rad-ippool.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    $input_descriptors1 = array();

    $caption = sprintf($options_format, t('all','PoolName'), t('all','IPAddress'));

    $input_descriptors1[] = array(
                                    'name' => 'record_id[]',
                                    'id' => 'record_id',
                                    'type' => 'select',
                                    'caption' => $caption,
                                    'options' => $options,
                                    'multiple' => true,
                                    'size' => 5,
                                 );

    $input_descriptors1[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );

    $input_descriptors1[] = array(
                                    'type' => 'submit',
                                    'name' => 'submit',
                                    'value' => t('buttons','apply')
                                 );
                                 
    $fieldset1_descriptor = array(
                                    "title" => t('title','IPPoolInfo'),
                                    "disabled" => (count($options) == 0)
                                 );

    open_form();
    
    open_fieldset($fieldset1_descriptor);

    foreach ($input_descriptors1 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
    
    close_fieldset();
    
    close_form();

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
