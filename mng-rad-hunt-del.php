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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
        
            $arr = array();
        
            if (array_key_exists('nashost', $_POST) && !empty($_POST['nashost'])) {
                $arr = (!is_array($_POST['nashost'])) ? array( $_POST['nashost'] ) : $_POST['nashost'];
            } else if (array_key_exists('nasipaddress', $_POST) && !empty(trim($_POST['nasipaddress']))) {
             
                if (array_key_exists('nasportid', $_POST) && !empty(trim($_POST['nasportid']))) {
                    $arr = array( trim($_POST['nasipaddress']) . "||" . trim($_POST['nasportid']) );
                } else {
                    $arr = array( trim($_POST['nasipaddress']) );
                }
            }

            if (count($arr) > 0) {

                $deleted = 0;
                foreach ($arr as $arr_elem) {
                    
                    $sql_WHERE = array();
                    
                    if (preg_match('/^[a-zA-Z0-9_-.]+\|\|/[0-9]+$', $arr_elem) !== false) {
                        $tmp = explode("||", $arr_elem);
                        if (count($tmp) != 2) {
                            continue;
                        }
                        
                        list($addr, $port) = $tmp;
                        $addr = trim($addr);
                        $port = trim($port);
                        
                        if (empty($addr) || empty($port)) {
                            continue;
                        }
                        
                        $sql_WHERE[] = sprintf("nasipaddress='%s'", $dbSocket->escapeSimple($addr));
                        $sql_WHERE[] = sprintf("nasportid='%s'", $dbSocket->escapeSimple($port));
                    } else if (preg_match('/^[a-zA-Z0-9_-.]+$', $arr_elem) !== false) {
                        $sql_WHERE[] = sprintf("nasipaddress='%s'", $dbSocket->escapeSimple($addr));
                    } else {
                        continue;
                    }
                    
                    $sql = sprintf("DELETE FROM %s", $configValues['CONFIG_DB_TBL_RADHG'])
                         . " WHERE " . implode(" AND ", $sql_WHERE);
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                    
                    if (!DB::isError($res)) {
                        $deleted++;
                    }
                }
                
                if ($deleted > 0) {                
                    $successMsg = sprintf("Deleted %s huntgroup(s)", $deleted);
                    $logAction .= "$successMsg on page: ";
                } else {
                    // invalid
                    $failureMsg = "Empty or invalid huntgroup(s)";
                    $logAction .= sprintf("Failed deleting huntgroup(s) [%s] on page: ", $failureMsg);
                }

            } else {
                // invalid
                $failureMsg = "Empty or invalid huntgroup(s)";
                $logAction .= sprintf("Failed deleting huntgroup(s) [%s] on page: ", $failureMsg);
            }
        
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    include_once("lang/main.php");
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','mngradhuntdel.php');
    $help = t('helpPage','mngradhuntdel');
    
    print_html_prologue($title, $langCode);

    include("menu-mng-rad-hunt.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    
    if (!isset($successMsg)) {
    
        $sql = sprintf("SELECT nasipaddress, nasportid FROM %s", $configValues['CONFIG_DB_TBL_RADHG']);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        $options = array();
        while ($row = $res->fetchrow()) {
            $tmp = $row[0] . "||" . $row[1];
            
            if (in_array($tmp, array_keys($options))) {
                continue;
            }
            
            $options[$tmp] = sprintf("%s:%s", $row[0], $row[1]);
        }
    
    
        $input_descriptors1 = array();

        $input_descriptors1[] = array(
                                        'name' => 'nashost[]',
                                        'id' => 'nashost',
                                        'type' => 'select',
                                        'caption' => t('all','HgIPHost') . ":" . t('all','HgPortId'),
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
                                        "title" => t('title','HGInfo'),
                                        "disabled" => (count($options) == 0)
                                     );

        open_form();
        
        open_fieldset($fieldset1_descriptor);

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        close_form();
    }

    include('library/closedb.php');

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
