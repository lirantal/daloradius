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
    $logAction = "";
    $logDebugSQL = "";
    $log = "visited page: ";


    $success = false;
    $count_involved_users = 0;
    $count_involved_groups = 0;

    include('../common/includes/db_open.php');


    function check_usergroup_mapping($dbSocket, $username, $group) {
        global $configValues, $logDebugSQL;
    
        $sql = sprintf("SELECT COUNT(*) FROM %s WHERE username='%s' AND groupname='%s'",
                       $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $username, $group);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
                            
        return ($res->fetchrow()[0] > 0);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
    
            $usergroup_mappings = array();
        
            if (array_key_exists('usergroup', $_POST) && !empty($_POST['usergroup'])) {
                $usergroup = (!is_array($_POST['usergroup'])) ? array( trim($_POST['usergroup']) ) : $_POST['usergroup'];
                
                foreach ($usergroup as $item) {
                    if(strpos($item, "||") === false) {
                        continue;
                    }
                    
                    $arr = explode("||", $item);
                    
                    if (count($arr) != 2) {
                        continue;
                    }
                    
                    list($u, $g) = $arr;
                    
                    $u = $dbSocket->escapeSimple(trim(str_replace("%", "", $u)));
                    $g = $dbSocket->escapeSimple(trim(str_replace("%", "", $g)));
                    
                    if (empty($u) || empty($g)) {
                        continue;
                    }
                    
                    if (array_key_exists($u, $usergroup_mappings) && in_array($g, $usergroup_mappings[$u])) {
                        continue;
                    }
                    
                    if (!check_usergroup_mapping($dbSocket, $u, $g)) {
                        continue;
                    }
                    
                    $usergroup_mappings[$u][] = $g;
                }

            } else {
                $username_is_set = array_key_exists('username', $_POST) && !empty($_POST['username']);
                $groupname_is_set = array_key_exists('group', $_POST) && !empty($_POST['group']);
            
                if ($username_is_set) {
                    $u = $dbSocket->escapeSimple(trim(str_replace("%", "", $_POST['username'])));
                    if (!empty($u)) {
                        
                        if (!$groupname_is_set) {
                            // if user is set but groupname not we want to delete all groups
                            if (check_usergroup_mapping($dbSocket, $u, $g)) {
                                $usergroup_mappings[$u] = array();
                            
                                while ($row = $res->fetchrow()) {
                                    $usergroup_mappings[$u][] = $dbSocket->escapeSimple($row[0]);
                                }
                            }
                            
                        } else {
                            $g = $dbSocket->escapeSimple(trim(str_replace("%", "", $_POST['group'])));
                            
                            if (!empty($g)) {
                                $sql = sprintf("SELECT COUNT(*) FROM %s WHERE username='%s' AND groupname='%s'",
                                               $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $u, $g);
                                $res = $dbSocket->query($sql);
                                $logDebugSQL .= "$sql;\n";
                                
                                if ($res->fetchrow()[0] > 0) {
                                    $usergroup_mappings[$u] = array( $g );
                                }
                            }
                        }
                    }
                }
            }
       
            if (count($usergroup_mappings) > 0) {
                foreach ($usergroup_mappings as $username => $groups) {
                    $sql = sprintf("DELETE FROM %s WHERE username='%s' AND groupname IN ('%s')",
                                   $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username),
                                   implode("', '", $groups));
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                    
                    if ($res > 0) {
                        $count_involved_users++;
                        $count_involved_groups += $res;
                    }
                }
            }
            
            $success = $count_involved_users > 0 && $count_involved_groups > 0;
            
            // present results
            if ($success) {
                $successMsg = sprintf("Deleted %s group mapping(s) for a total of %s user(s)", $count_involved_groups, $count_involved_users);
                $logAction .= sprintf("%s on page: ", $successMsg);
            } else {
                $failureMsg = "Cannot remove the specified group mapping(s)";
                $logAction .= sprintf("%s on page: ", $failureMsg);
            }
            
        } else {
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
        
    } else {
        
        $username = (array_key_exists('username', $_REQUEST) && !empty($_REQUEST['username']))
                  ? trim(str_replace("%", "", $_REQUEST['username'])) : "";
                  
        $groupname = (array_key_exists('group', $_REQUEST) && !empty($_REQUEST['group']))
                   ? trim(str_replace("%", "", $_REQUEST['group'])) : "";
        
        if (!empty($username) && !empty($groupname)) {
            $valid = check_usergroup_mapping($dbSocket, $dbSocket->escapeSimple($username), $dbSocket->escapeSimple($groupname));
        
            if (!$valid) {
                $username = "";
                $groupname = "";
            }
        }
        
    }

    include('../common/includes/db_close.php');

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // print HTML prologue
    
    $title = t('Intro','mngradusergroupdel.php');
    $help = t('helpPage','mngradusergroupdel');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);

    if ($_SERVER['REQUEST_METHOD'] != 'GET') {
        include_once('include/management/actionMessages.php');
    }

    if (!$success) {
        include_once('include/management/populate_selectbox.php');

        $input_descriptors1[] = array(
                                        "name" => "username",
                                        "caption" => t('all','Username'),
                                        "type" => "text",
                                        "value" => $username,
                                        "tooltipText" => t('Tooltip','usernameTooltip'),
                                     );

        $options = get_groups();
        $input_descriptors1[] = array(
                                        "id" => "group",
                                        "name" => "group",
                                        "caption" => t('all','Groupname'),
                                        "type" => "select",
                                        "options" => $options,
                                        "selected_value" => $groupname,
                                        "tooltipText" => t('Tooltip','groupTooltip')
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
                                        "title" => t('title','GroupInfo'),
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

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
