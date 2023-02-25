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

    include('../common/includes/config_read.php');
    include('library/check_operator_perm.php');
    
    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");
    include_once("include/management/functions.php");
    include_once('include/management/populate_selectbox.php');

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    // custom valid authTypes
    $valid_authTypes = array(
                                "userAuth" => "Based on username and password",
                                "otherAuth" => "Based on MAC addr/PIN code"
                            );

    $valid_groups = get_groups();
    $valid_planNames = get_plans();

    // if cleartext passwords are not allowed, 
    // we remove Cleartext-Password from the $valid_passwordTypes array
    if (isset($configValues['CONFIG_DB_PASSWORD_ENCRYPTION']) &&
        strtolower(trim($configValues['CONFIG_DB_PASSWORD_ENCRYPTION'])) !== 'yes') {
        $valid_passwordTypes = array_values(array_diff($valid_passwordTypes, array("Cleartext-Password")));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            $authType = (array_key_exists('authType', $_POST) && isset($_POST['authType']) &&
                         in_array($_POST['authType'], array_keys($valid_authTypes))) ? $_POST['authType'] : array_keys($valid_authTypes)[0];

            $planName = (array_key_exists('planName', $_POST) && !empty($_POST['planName']) &&
                         in_array($_POST['planName'], $valid_planNames))
                      ? $_POST['planName'] : "";

            $groups = (array_key_exists('groups', $_POST) && isset($_POST['groups'])) ? $_POST['groups'] : array();

            $csvdata = (array_key_exists('csvdata', $_POST) && isset($_POST['csvdata']))
                     ? $_POST['csvdata'] : "";

            $csvFormattedData = (!empty($csvdata)) ? explode("\n", $csvdata) : array();

            $simpleList = (array_key_exists('simpleList', $_POST) && isset($_POST['simpleList']))
                        ? $_POST['simpleList'] : "";

            $simpleListData = (!empty($simpleList)) ? explode("\n", $simpleList) : array();

            $data = array();
            $passwordType = "";

            if (count($csvFormattedData) > 0) {

                $passwordType = (array_key_exists('passwordType', $_POST) && isset($_POST['passwordType']) &&
                                 in_array($_POST['passwordType'], $valid_passwordTypes))
                              ? $_POST['passwordType'] : $valid_passwordTypes[0];

                foreach ($csvFormattedData as $csvLine) {

                    $arr = explode(",", $csvLine);

                    if (count($arr) != 5) {
                        continue;
                    }

                    list($username, $password, $email, $firstname, $lastname) = $arr;
                    $username = trim($username);
                    $password = trim($password);
                    $email = trim($email);
                    $firstname = trim($firstname);
                    $lastname = trim($lastname);

                    if (strpos("%", $username) === false &&
                        preg_match(ALL_PRINTABLE_CHARS_REGEX, $username) &&
                        preg_match(ALL_PRINTABLE_CHARS_REGEX, $password) &&
                        !array_key_exists($username, $data)) {
                        $data[$username] = array( $password, $email, $firstname, $lastname );
                    }
                }


            } else if (count($simpleListData) > 0) {

                $passwordType = "Auth-Type";
                
                foreach ($simpleListData as $simpleLine) {
                    $arr = explode(",", $simpleLine);

                    if (count($arr) != 4) {
                        continue;
                    }
                    
                    list($username, $email, $firstname, $lastname) = $arr;
                    $username = trim($username);
                    $email = trim($email);
                    $firstname = trim($firstname);
                    $lastname = trim($lastname);
                    
                    if (strpos("%", $username) === false &&
                        preg_match(ALL_PRINTABLE_CHARS_REGEX, $username) &&
                        !array_key_exists($username, $data)) {
                        $data[$username] = array( "Accept", $email, $firstname, $lastname );
                    }

                }

            }

            if (count($data) > 0 && !empty($passwordType)) {

                $currDate = date('Y-m-d H:i:s');
                $currBy = $_SESSION['operator_user'];

                include('../common/includes/db_open.php');
                include("library/attributes.php");

                $counter = 0;
                foreach ($data as $subject => $arr) {
                    list( $value, $email, $firstname, $lastname ) = $arr;
                    
                    $value = hashPasswordAttribute($passwordType, $value);

                    // skipping this user if it exists or insert fails
                    if (
                            user_exists($dbSocket, $subject) ||
                            !insert_single_attribute($dbSocket, $subject, $passwordType, ":=", $value)
                       ) {
                        continue;
                    }

                    // adding user info
                    $params = array(
                                        "creationdate" => $currDate,
                                        "creationby" => $currBy,
                                   );

                    if (!empty($firstname) && preg_match(ALL_PRINTABLE_CHARS_REGEX, $firstname)) {
                        $params["firstname"] = $firstname;
                    }
                    
                    if (!empty($lastname) && preg_match(ALL_PRINTABLE_CHARS_REGEX, $lastname)) {
                        $params["lastname"] = $lastname;
                    }
                    
                    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $params["email"] = $email;
                    }

                    $addedUserInfo = add_user_info($dbSocket, $subject, $params);

                    $groupsCount = insert_multiple_user_group_mappings($dbSocket, $subject, $groups);

                    // adding billing info
                    if (!empty($planName)) {
                        $params["planName"] = $planName;

                        $addedBillingInfo = add_user_billing_info($dbSocket, $subject, $params);
                    }

                    $counter++;
                }

                include('../common/includes/db_close.php');

                if ($counter > 0) {
                    $successMsg = "Successfully imported a total of <b>$counter</b> users to database";
                    $logAction .= "Successfully imported a total of <b>$counter</b> users to database on page: ";
                } else {
                    $failureMsg = "No users have been imported to database";
                    $logAction .= "No users have been imported to database on page: ";
                }

            } else {
                // invalid data
                $failureMsg = "Empty or invalid data provided";
                $logAction .= "Empty or invalid data provided on page: ";
            }

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }


    // print HTML prologue
    $title = t('Intro','mngimportusers.php');
    $help = t('helpPage','mngimportusers');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    if (!isset($successMsg)) {

        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        "type" =>"select",
                                        "name" => "authType",
                                        "caption" => "Authentication Type",
                                        "options" => $valid_authTypes,
                                        "onchange" => "switchAuthType()",
                                        "selected_value" => ((isset($failureMsg)) ? $authType : "")
                                     );

        $options = $valid_groups;
        array_unshift($options, '');
        $input_descriptors0[] = array(
                                        "type" =>"select",
                                        "name" => "groups[]",
                                        "id" => "groups",
                                        "caption" => t('all','Group'),
                                        "options" => $options,
                                        "multiple" => true,
                                        "size" => 5,
                                        "selected_value" => ((isset($failureMsg)) ? $groups : ""),
                                        "tooltipText" => t('Tooltip','groupTooltip')
                                     );

        $options = $valid_planNames;
        array_unshift($options, '');
        $input_descriptors0[] = array(
                                        "type" =>"select",
                                        "name" => "planName",
                                        "caption" => t('all','PlanName'),
                                        "options" => $options,
                                        "selected_value" => ((isset($failureMsg)) ? $planName : ""),
                                        "tooltipText" => t('Tooltip','planTooltip')
                                     );

        open_form();

        // open a fieldset
        $fieldset0_descriptor = array(
                                        "title" => t('title','ImportUsers'),
                                     );

        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();


        // open a fieldset
        $fieldset1_descriptor = array(
                                        "title" => "Username/password info",
                                        "id" => "userAuth-fieldset",
                                     );

        open_fieldset($fieldset1_descriptor);

        $input_descriptors1[] = array(
                                        "name" => "passwordType",
                                        "caption" => t('all','PasswordType'),
                                        "options" => $valid_passwordTypes,
                                        "type" => "select",
                                        "selected_value" => ((isset($failureMsg)) ? $passwordType : ""),
                                        "tooltipText" => 'Notice that for supported password-like attributes, you can just specify a plaintext value. ' .
                                                         'The system will take care of correctly hashing it.',
                                    );

        $input_descriptors1[] = array(
                                        "caption" => t('all','CSVData'),
                                        "type" => "textarea",
                                        "name" => "csvdata",
                                        "tooltipText" => 'Paste a CSV-formatted data input of users, expected format is: user,password,email,firstname,lastname. ' .
                                                         'Note: any CSV fields beyond the first 5 are ignored.',
                                        "content" => ((isset($failureMsg)) ? $csvdata : ""),
                                     );

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        // open a fieldset
        $fieldset2_descriptor = array(
                                        "title" => "MAC addr/PIN code info",
                                        "id" => "otherAuth-fieldset",
                                     );

        open_fieldset($fieldset2_descriptor);

        $input_descriptors2[] = array(
                                        "caption" => "Simple List",
                                        "type" => "textarea",
                                        "name" => "simpleList",
                                        "tooltipText" => 'Paste a CSV-formatted data input of MAC addresses or PIN codes. The expected format is: MAC address/PIN code,email,firstname,lastname. ' .
                                                         'Note: any CSV fields beyond the first 4 are ignored.',
                                        "content" => ((isset($failureMsg)) ? $simpleList : ""),
                                     );

        foreach ($input_descriptors2 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        $input_descriptors3 = array();
        
        $input_descriptors3[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );

        $input_descriptors3[] = array(
                                        'type' => 'submit',
                                        'name' => 'submit',
                                        'value' => t('buttons','apply')
                                     );

        foreach ($input_descriptors3 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_form();

    }

    include('include/config/logging.php');

    $inline_extra_js = <<<EOF
function switchAuthType() {
    var switcher = document.getElementById("authType");

    for (var i=0; i<switcher.length; i++) {
        var fieldset_id = switcher[i].value + "-fieldset",
            disabled = switcher.value != switcher[i].value,
            fieldset = document.getElementById(fieldset_id);

        fieldset.disabled = disabled;
        fieldset.style.display = (disabled) ? "none" : "block";
    }
}

window.addEventListener("load", function() { switchAuthType(); });
EOF;

    print_footer_and_html_epilogue($inline_extra_js);
?>
