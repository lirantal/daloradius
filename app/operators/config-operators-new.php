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
        
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
        
            $operator_username = (array_key_exists('operator_username', $_POST) && isset($_POST['operator_username']))
                               ? trim(str_replace("%", "", $_POST['operator_username'])) : "";
            $operator_username_enc = (!empty($operator_username)) ? htmlspecialchars($operator_username, ENT_QUOTES, 'UTF-8') : "";
            $operator_password = (array_key_exists('operator_password', $_POST) && isset($_POST['operator_password'])) ? trim($_POST['operator_password']) : "";

            $firstname = (array_key_exists('firstname', $_POST) && isset($_POST['firstname'])) ? trim($_POST['firstname']) : "";
            $lastname = (array_key_exists('lastname', $_POST) && isset($_POST['lastname'])) ? trim($_POST['lastname']) : "";
            $title = (array_key_exists('title', $_POST) && isset($_POST['title'])) ? trim($_POST['title']) : "";
            $department = (array_key_exists('department', $_POST) && isset($_POST['department'])) ? trim($_POST['department']) : "";
            $company = (array_key_exists('company', $_POST) && isset($_POST['company'])) ? trim($_POST['company']) : "";
            $phone1 = (array_key_exists('phone1', $_POST) && isset($_POST['phone1'])) ? trim($_POST['phone1']) : "";
            $phone2 = (array_key_exists('phone2', $_POST) && isset($_POST['phone2'])) ? trim($_POST['phone2']) : "";
            $email1 = (array_key_exists('email1', $_POST) && isset($_POST['email1'])) ? trim($_POST['email1']) : "";
            $email2 = (array_key_exists('email2', $_POST) && isset($_POST['email2'])) ? trim($_POST['email2']) : "";
            $messenger1 = (array_key_exists('messenger1', $_POST) && isset($_POST['messenger1'])) ? trim($_POST['messenger1']) : "";
            $messenger2 = (array_key_exists('messenger2', $_POST) && isset($_POST['messenger2'])) ? trim($_POST['messenger2']) : "";
            $notes = (array_key_exists('notes', $_POST) && isset($_POST['notes'])) ? trim($_POST['notes']) : "";

            include('../common/includes/db_open.php');

            if (empty($operator_username) || empty($operator_password)) {
                // if statement returns false which means that the user has left an empty field for
                // either the username or password, or both

                $failureMsg = "username or password are empty";
                $logAction .= "Failed adding (possible empty user/pass) new operator on page: ";
            } else {
                $sql = sprintf("SELECT COUNT(DISTINCT(username)) FROM %s WHERE username='%s'",
                               $configValues['CONFIG_DB_TBL_DALOOPERATORS'], $dbSocket->escapeSimple($operator_username));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                
                $exists = ($res->fetchrow()[0] == 1);
                
                if ($exists) {
                    // if statement returns false which means there is at least one operator
                    // in the database with the same username

                    $failureMsg = sprintf("operator already exists in database: <b>%s</b>", $operator_username_enc);
                    $logAction .= "Failed adding new operator user already existing in database [$operator_username] on page: ";
                } else {
                    $currDate = date('Y-m-d H:i:s');
                    $currBy = $_SESSION['operator_user'];

                    // insert username and password of operator into the database
                    $sql = sprintf("INSERT INTO %s (id, username, password, firstname, lastname, title, department, company,
                                                    phone1, phone2, email1, email2, messenger1, messenger2, notes, creationdate,
                                                    creationby, updatedate, updateby)
                                            VALUES (0, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                                                    '%s', '%s', '%s', '%s', NULL, NULL)", $configValues['CONFIG_DB_TBL_DALOOPERATORS'],
                                   $operator_username, $operator_password, $firstname, $lastname, $title, $department, $company,
                                   $phone1, $phone2, $email1, $email2, $messenger1, $messenger2, $notes, $currDate, $currBy);
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";

                    // lets make sure we've inserted the new operator successfully and grab his operator_id
                    $sql = sprintf("SELECT id FROM %s WHERE username='%s'", $configValues['CONFIG_DB_TBL_DALOOPERATORS'],
                                                                            $dbSocket->escapeSimple($operator_username));
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                    
                    $numrows = $res->numRows();
                    
                    if ($numrows == 1) {
                        
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $new_operator_id = intval($row['id']);

                        // left piece of the query which is the same for all common values to insert
                        $sql0 = sprintf("INSERT INTO %s (operator_id, file, access) VALUES ",
                                        $configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL']);

                        $sql_piece_format = sprintf("(%s", $new_operator_id) . ", '%s', '%s')";
                        $sql_pieces = array();

                        // insert operators acl for this operator
                        foreach ($_POST as $field => $access) {
                            
                            if (preg_match('/^ACL_/', $field) === false) {
                                continue;
                            }
                            
                            $file = substr($field, 4);
                            $sql_pieces[] = sprintf($sql_piece_format, $dbSocket->escapeSimple($file),
                                                                       $dbSocket->escapeSimple($access));
                        } // foreach
                        
                        if (count($sql_pieces) > 0) {
                            $sql = $sql0 . implode(", ", $sql_pieces);
                            $res = $dbSocket->query($sql);
                            $logDebugSQL .= "$sql;\n";
                            
                            if (!DB::isError($res)) {
                                $successMsg = sprintf('Successfully added new operator (<strong>%s</strong>) '
                                                    . '<a href="config-operators-edit.php?operator_username=%s" title="Edit">%s</a>',
                                                      $operator_username_enc, $operator_username_enc, urlencode($operator_username_enc));
                                $logAction .= "Successfully added new operator [$operator_username] on page: ";
                            } else {
                                // it seems that operator could not be added
                                $f = "Failed to add this new operator [%s] to database";
                                $failureMsg = sprintf($f, $operator_username_enc);
                                $logAction .= sprintf($f, $operator_username);
                            }

                        }
                    
                    } else { //if numrows()
                        // it seems that operator could not be added
                        $f = "Failed to add this new operator [%s] to database";
                        $failureMsg = sprintf($f, $operator_username_enc);
                        $logAction .= sprintf($f, $operator_username);
                    }
                }
                
            }
            
            include('../common/includes/db_close.php');
            
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    } // if form was submitted
    
    $hiddenPassword = (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) == "yes")
                    ? 'password' : 'text';
    

    // print HTML prologue
    $extra_css = array();
    
    $extra_js = array(
        "static/js/productive_funcs.js",
    );
    
    $title = t('Intro','configoperatorsnew.php');
    $help = t('helpPage','configoperatorsnew');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);
    
    include_once('include/management/actionMessages.php');
    
    if (!isset($successMsg)) {
    
        // set form component descriptors
        $input_descriptors0 = array();
        
        $input_descriptors0[] = array(
                                        "id" => "operator_username",
                                        "name" => "operator_username",
                                        "caption" => t('all','Username'),
                                        "type" => "text",
                                        "value" => ((isset($operator_username)) ? $operator_username : ""),
                                        "random" => true
                                     );
                                    
        $input_descriptors0[] = array(
                                        "id" => "operator_password",
                                        "name" => "operator_password",
                                        "caption" => t('all','Password'),
                                        "type" => $hiddenPassword,
                                        "value" => ((isset($operator_password)) ? $operator_password : ""),
                                        "random" => true
                                     );
        
        // set navbar stuff
        $navkeys = array( array( 'OperatorInfo', "Operator Info" ), 'ContactInfo', array( 'ACLSettings', "ACL Settings" ) );

        // print navbar controls
        print_tab_header($navkeys);
        
        open_form();
    
        // open tab wrapper
        open_tab_wrapper();
    
        // tab 0
        open_tab($navkeys, 0, true);
    
        $fieldset0_descriptor = array(
                                        "title" => "Operator Info"
                                     );

        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        close_tab($navkeys, 0);

        // tab 1
        open_tab($navkeys, 1);

        include_once('include/management/operatorinfo.php');

        close_tab($navkeys, 1);

        // tab 2
        open_tab($navkeys, 2);

        include_once('include/management/operator_acls.php');
        drawOperatorACLs();

        close_tab($navkeys, 2);

        // close tab wrapper
        close_tab_wrapper();

        $input_descriptors1 = array();

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

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_form();
    }

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
