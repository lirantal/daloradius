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
 
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    $valid_tablenames = [
                            $configValues['CONFIG_DB_TBL_RADCHECK'],
                            $configValues['CONFIG_DB_TBL_RADREPLY'],
                            $configValues['CONFIG_DB_TBL_RADGROUPREPLY'],
                            $configValues['CONFIG_DB_TBL_RADGROUPCHECK']
                        ];

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
                
            $id__attribute = (array_key_exists('attribute', $_POST) && !empty(trim($_POST['attribute'])) &&
                              preg_match("/__/", trim($_POST['attribute'])) !== false) ? trim($_POST['attribute']) : "";
            
            $tablename = (!empty($id__attribute) && array_key_exists('tablename', $_POST) &&
                          !empty(trim($_POST['tablename'])) && in_array(trim($_POST['tablename']), $valid_tablenames))
                       ? trim($_POST['tablename']) : "";

            $delradacct = (array_key_exists('delradacct', $_POST) && strtolower(trim($_POST['delradacct'])) == 'yes');


            // validate values
            $usernames = [];
            
            if (array_key_exists('username', $_POST) && !empty($_POST['username'])) {
                
                $tmp = (!is_array($_POST['username'])) ? [ $_POST['username'] ] : $_POST['username'];
                foreach ($tmp as $value) {
                    
                    $value = urldecode($value);
                    $value = trim(str_replace("%", "", $value));
                    
                    if (!in_array($value, $usernames)) {
                        $usernames[] = $value;
                    }
                }
                
                if (count($usernames) > 0) {
                
                    if (!empty($id__attribute) && !empty($tablename)) {
                        
                        $sql = sprintf("SELECT COUNT(`id`) FROM %s WHERE username='%s'",
                                       $configValues['CONFIG_DB_TBL_RADCHECK'], $dbSocket->escapeSimple($usernames[0]));
                        $res = $dbSocket->query($sql);
                        $logDebugSQL .= "$sql;\n";
                        
                        $check_attr_count = intval($res->fetchrow()[0]);
                        
                        $sql = sprintf("SELECT COUNT(`id`) FROM %s WHERE username='%s' AND attribute='Auth-Type' OR attribute LIKE '%%-Password'",
                                       $configValues['CONFIG_DB_TBL_RADCHECK'], $dbSocket->escapeSimple($usernames[0]));
                        $res = $dbSocket->query($sql);
                        $logDebugSQL .= "$sql;\n";
                        
                        $check_auth_attr_count = intval($res->fetchrow()[0]);
                        
                        list($columnId, $attribute) = explode("__", $id__attribute);
                        $attribute = trim($attribute);
                        $columnId = intval(trim($columnId));
                        $is_last_auth_attr = $check_auth_attr_count == 1 && ($attribute == 'Auth-Type' || preg_match("/-Password$/", $attribute) === 1);
                        
                        if ($tablename == $configValues['CONFIG_DB_TBL_RADCHECK'] && ($check_attr_count == 1 || $is_last_auth_attr)) {
                            // if operator wants to remove the last check attribute
                            // or the last "password-like" check attribute
                            // they should delete all user related info stored in the db
                            
                            $format = "Cannot delete the last check (password like?) attribute for the selected user (%s)";
                            $failureMsg = sprintf($format,
                                                  htmlspecialchars($usernames[0], ENT_QUOTES, 'UTF-8'));
                            $logAction = sprintf("$format on page: ", $username[0]);
                        } else {
                        
                            $sql = sprintf("DELETE FROM %s WHERE username='%s' AND attribute='%s' AND id=%s",
                                           $dbSocket->escapeSimple($tablename), $dbSocket->escapeSimple($usernames[0]),
                                           $dbSocket->escapeSimple($attribute), $dbSocket->escapeSimple($columnId));
                            $res = $dbSocket->query($sql);
                            $logDebugSQL .= "$sql;\n";
                            
                            $format = "Deleted attribute %s for user %s";
                            $successMsg = sprintf($format, htmlspecialchars($attribute, ENT_QUOTES, 'UTF-8'),
                                                           htmlspecialchars($usernames[0], ENT_QUOTES, 'UTF-8'));
                            $logAction = sprintf("$format on page: ", $attribute, $usernames[0]);
                        }
                    } else {
                        $dbusers = [];
                        
                        foreach ($usernames as $u) {
                            if (!empty($dbSocket->escapeSimple($u))) {
                                $dbusers[] = $dbSocket->escapeSimple($u);
                            }
                        }
                        
                        $dbusersLen = count($dbusers);
                        if ($dbusersLen > 0) {
                            // setting table-related parameters first                
                            switch($configValues['FREERADIUS_VERSION']) {
                                case '1' :
                                    $tableSetting['postauth']['user'] = 'user';
                                    $tableSetting['postauth']['date'] = 'date';
                                    break;
                                case '2' :
                                    // down
                                case '3' :
                                    // down
                                default  :
                                    $tableSetting['postauth']['user'] = 'username';
                                    $tableSetting['postauth']['date'] = 'authdate';
                                    break;
                            }
                            
                            $sql_format = "DELETE FROM %s WHERE %s IN ('" . implode("', '", $dbusers) . "')";
                            
                            $sql = sprintf($sql_format, $configValues['CONFIG_DB_TBL_RADPOSTAUTH'],
                                                        $tableSetting['postauth']['user']);
                            $res = $dbSocket->query($sql);
                            $logDebugSQL .= "$sql;\n";
                            
                            $tables = [
                                        $configValues['CONFIG_DB_TBL_RADCHECK'],
                                        $configValues['CONFIG_DB_TBL_RADREPLY'],
                                        $configValues['CONFIG_DB_TBL_DALOUSERINFO'],
                                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                        $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                                      ];

                            if ($delradacct) {
                                $tables[] = $configValues['CONFIG_DB_TBL_RADACCT'];
                            }
                            
                            foreach ($tables as $table) {
                                $sql = sprintf($sql_format, $table, 'username');
                                $res = $dbSocket->query($sql);
                                $logDebugSQL .= "$sql;\n";
                            }
                            
                            $format = "%d user(s) have been deleted";
                            $successMsg = sprintf($format, $dbusersLen);
                            $logAction = sprintf("$format on page: ", $dbusersLen);
                            
                        } else {
                            $failureMsg = "You have provided an empty or invalid username list";
                            $logAction = "Provided an empty or invalid username list (user(s) deletion) on page: ";
                        }
                    }
                } else {
                    $failureMsg = "You have provided an empty or invalid username list";
                    $logAction = "Provided an empty or invalid username list (user(s) deletion) on page: ";
                }
            } else if (array_key_exists('clearSessionsUsers', $_POST) && !empty($_POST['clearSessionsUsers'])) {
                
                $username__starttimes = [];
                
                $tmp = (!is_array($_POST['clearSessionsUsers'])) ? [ $_POST['clearSessionsUsers'] ] : $_POST['clearSessionsUsers'];
                foreach ($tmp as $value) {
                    
                    $value = trim(str_replace("%", "", $value));
                    
                    if (!in_array($value, $username__starttimes)) {
                        $username__starttimes[] = $value;
                    }
                }
                
                
                $userstimesLen = count($username__starttimes);
                if ($userstimesLen > 0) {
                    
                    foreach ($username__starttimes as $username__starttime) {
                        list($username, $datetime) = explode('||', $username__starttime);
                        $sql = sprintf("DELETE FROM %s
                                         WHERE username='%s' AND AcctStartTime='%s'
                                           AND (AcctStopTime='0000-00-00 00:00:00' OR AcctStopTime IS NULL)",
                                       $configValues['CONFIG_DB_TBL_RADACCT'], $dbSocket->escapeSimple($username),
                                       $dbSocket->escapeSimple($datetime));
                        $res = $dbSocket->query($sql);
                        $logDebugSQL .= "$sql;\n";
                    }
                    
                    $format = "%d user' session(s) have been cleaned";
                    $successMsg = sprintf($format, $userstimesLen);
                    $logAction = sprintf("$format on page: ", $userstimesLen);
                    
                } else {
                    $failureMsg = "You have provided an empty or invalid username list";
                    $logAction = "Provided an empty or invalid username list (session cleaning) on page: ";
                }
            }
        } else {
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);

    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);

    // print HTML prologue
    $title = t('Intro','mngdel.php');
    $help = t('helpPage','mngdel');
    
    print_html_prologue($title, $langCode);
    
    if (!empty($username) && !is_array($username)) {
        $title .= " :: " . htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    }

    print_title_and_help($title, $help);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);
    
    $sql = sprintf("SELECT DISTINCT(`username`) FROM %s", $configValues['CONFIG_DB_TBL_RADCHECK']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    $options = [];
    while ($row = $res->fetchrow()) {
        $options[] = $row[0];
    }
    
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
    
    $input_descriptors1 = [];

    $input_descriptors1[] = array(
                                'name' => 'username[]',
                                'id' => 'username',
                                'type' => 'select',
                                'caption' => t('all','Username'),
                                'options' => $options,
                                'multiple' => true,
                                'size' => 5
                             );

    $input_descriptors1[] = array(
                                'name' => 'delradacct',
                                'type' => 'select',
                                'caption' => t('all','RemoveRadacctRecords'),
                                'options' => array("", "yes", "no"),
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
                                    "title" => t('title','AccountRemoval'),
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

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    print_footer_and_html_epilogue();
