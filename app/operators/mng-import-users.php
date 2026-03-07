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
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);
 
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'functions.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'populate_selectbox.php' ]);

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
    $cleartextPasswordAllowed = (!isset($configValues['CONFIG_DB_PASSWORD_ENCRYPTION']) ||
        strtolower(trim($configValues['CONFIG_DB_PASSWORD_ENCRYPTION'])) === 'yes');
    if (!$cleartextPasswordAllowed) {
        $valid_passwordTypes = array_values(array_diff($valid_passwordTypes, array("Cleartext-Password")));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            $authType = $_POST['authType'] ?? array_keys($valid_authTypes)[0];
            $authType = in_array($authType, array_keys($valid_authTypes)) ? $authType : array_keys($valid_authTypes)[0];

            $planName = $_POST['planName'] ?? '';
            $planName = in_array($planName, $valid_planNames) ? $planName : '';

            $groups = $_POST['groups'] ?? [];

            $csvdata = $_POST['csvdata'] ?? '';
            $csvFormattedData = !empty($csvdata) ? explode("\n", $csvdata) : [];

            $simpleList = $_POST['simpleList'] ?? '';
            $simpleListData = !empty($simpleList) ? explode("\n", $simpleList) : [];

            $enableportallogin = $_POST['enableportallogin'] ?? 'no';
            $enableportallogin = ($enableportallogin === 'no') ? 0 : 1;

            $data = array();
            $passwordType = "";

            if (count($csvFormattedData) > 0) {

                $passwordType = (array_key_exists('passwordType', $_POST) && isset($_POST['passwordType']) &&
                                 in_array($_POST['passwordType'], $valid_passwordTypes))
                              ? $_POST['passwordType'] : $valid_passwordTypes[0];

                foreach ($csvFormattedData as $csvLine) {

                    $arr = str_getcsv($csvLine, ",");

                    // Support 5-20 fields:
                    // Required (5): username, password, email, firstname, lastname
                    // Optional (15): framedipaddress, expiration, department, company, mobilephone,
                    //                workphone, homephone, address, city, state, country, zip,
                    //                sessiontimeout, idletimeout, maxdailysession
                    if (count($arr) < 5 || count($arr) > 20) {
                        continue;
                    }

                    // Pad to 20 fields with empty strings
                    list($username, $password, $email, $firstname, $lastname, $framedipaddress, $expiration,
                         $department, $company, $mobilephone, $workphone, $homephone, $address, $city, $state,
                         $country, $zip, $sessiontimeout, $idletimeout, $maxdailysession) = array_pad($arr, 20, '');

                    $username = trim($username);
                    $password = trim($password);
                    $email = trim($email);
                    $firstname = trim($firstname);
                    $lastname = trim($lastname);
                    $framedipaddress = trim($framedipaddress);
                    $expiration = trim($expiration);
                    $department = trim($department);
                    $company = trim($company);
                    $mobilephone = trim($mobilephone);
                    $workphone = trim($workphone);
                    $homephone = trim($homephone);
                    $address = trim($address);
                    $city = trim($city);
                    $state = trim($state);
                    $country = trim($country);
                    $zip = trim($zip);
                    $sessiontimeout = trim($sessiontimeout);
                    $idletimeout = trim($idletimeout);
                    $maxdailysession = trim($maxdailysession);

                    // Validate IP address format if provided
                    if (!empty($framedipaddress) && preg_match(IP_REGEX, $framedipaddress) !== 1) {
                        continue; // Skip invalid IP
                    }

                    // Convert expiration date from Y-m-d to d M Y format (FreeRADIUS format)
                    if (!empty($expiration)) {
                        $expirationDate = DateTime::createFromFormat('Y-m-d', $expiration);
                        $errors = DateTime::getLastErrors();

                        // ensure strict parsing: no errors/warnings and no normalization
                        if ($expirationDate === false
                            || !empty($errors['warning_count'])
                            || !empty($errors['error_count'])
                            || $expirationDate->format('Y-m-d') !== $expiration) {
                            continue; // Skip invalid date
                        }

                        $expiration = $expirationDate->format('d M Y');
                    }

                    // Validate timeout fields (must be numeric if provided)
                    if (!empty($sessiontimeout) && !is_numeric($sessiontimeout)) {
                        continue;
                    }
                    if (!empty($idletimeout) && !is_numeric($idletimeout)) {
                        continue;
                    }
                    if (!empty($maxdailysession) && !is_numeric($maxdailysession)) {
                        continue;
                    }

                    if (preg_match(EMAIL_LIKE_USERNAME_REGEX, $username) === 1 &&
                        preg_match(SAFE_PASSWORD_REGEX, $password) === 1 &&
                        preg_match(FIRST_LAST_NAME_REGEX, $firstname) === 1 &&
                        preg_match(FIRST_LAST_NAME_REGEX, $lastname) === 1 &&
                        !array_key_exists($username, $data)) {
                        $data[$username] = array( $password, $email, $firstname, $lastname, $framedipaddress, $expiration,
                                                  $department, $company, $mobilephone, $workphone, $homephone,
                                                  $address, $city, $state, $country, $zip,
                                                  $sessiontimeout, $idletimeout, $maxdailysession );
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
                    
                    // Validate username as MAC address or PIN code
                    $isValidMacOrPin = (preg_match(MACADDR_REGEX, $username) === 1 || 
                                        preg_match(PINCODE_REGEX, $username) === 1 ||
                                        preg_match(IP_REGEX, $username) === 1);
                    
                    if ($isValidMacOrPin && !array_key_exists($username, $data)) {
                        $data[$username] = array( "Accept", $email, $firstname, $lastname );
                    }

                }

            }

            if (count($data) > 0 && !empty($passwordType)) {

                $current_datetime = date('Y-m-d H:i:s');
                $currBy = $_SESSION['operator_user'];

                include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);
                include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'attributes.php' ]);

                $counter = 0;
                foreach ($data as $subject => $arr) {
                    list( $value, $email, $firstname, $lastname, $framedipaddress, $expiration,
                          $department, $company, $mobilephone, $workphone, $homephone,
                          $address, $city, $state, $country, $zip,
                          $sessiontimeout, $idletimeout, $maxdailysession ) = $arr;

                    // skipping this user if it exists
                    if (user_exists($dbSocket, $subject)) {
                        continue;
                    }

                    // FORCE Auth-Type := Accept for Auth-Type passwordType (MAC/PIN)
                    if ($passwordType === 'Auth-Type') {
                        if (!insert_single_attribute($dbSocket, $subject, 'Auth-Type', ':=', 'Accept')) {
                            continue;
                        }
                    } else {
                        $hashed_value = hashPasswordAttribute($passwordType, $value);
                        if (!insert_single_attribute($dbSocket, $subject, $passwordType, ':=', $hashed_value)) {
                            continue;
                        }
                    }

                    // Insert Framed-IP-Address (radreply table)
                    if (!empty($framedipaddress)) {
                        insert_single_attribute($dbSocket, $subject, 'Framed-IP-Address', ':=', $framedipaddress, $configValues['CONFIG_DB_TBL_RADREPLY']);
                    }

                    // Insert Expiration (radcheck table)
                    if (!empty($expiration)) {
                        insert_single_attribute($dbSocket, $subject, 'Expiration', ':=', $expiration, $configValues['CONFIG_DB_TBL_RADCHECK']);
                    }

                    // Insert Session-Timeout (radreply table)
                    if (!empty($sessiontimeout)) {
                        insert_single_attribute($dbSocket, $subject, 'Session-Timeout', ':=', $sessiontimeout, $configValues['CONFIG_DB_TBL_RADREPLY']);
                    }

                    // Insert Idle-Timeout (radreply table)
                    if (!empty($idletimeout)) {
                        insert_single_attribute($dbSocket, $subject, 'Idle-Timeout', ':=', $idletimeout, $configValues['CONFIG_DB_TBL_RADREPLY']);
                    }

                    // Insert Max-Daily-Session (radcheck table)
                    if (!empty($maxdailysession)) {
                        insert_single_attribute($dbSocket, $subject, 'Max-Daily-Session', ':=', $maxdailysession, $configValues['CONFIG_DB_TBL_RADCHECK']);
                    }

                    // adding user info
                    $params = array(
                                        "creationdate" => $current_datetime,
                                        "creationby" => $currBy,
                                   );

                    if ($authType == 'userAuth') {
                        $params["portalloginpassword"] = $value;
                        $params["enableportallogin"] = $enableportallogin;
                        $params["changeuserinfo"] = $enableportallogin;
                    }

                    if (!empty($firstname) && preg_match(FIRST_LAST_NAME_REGEX, $firstname)) {
                        $params["firstname"] = $firstname;
                    }

                    if (!empty($lastname) && preg_match(FIRST_LAST_NAME_REGEX, $lastname)) {
                        $params["lastname"] = $lastname;
                    }

                    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $params["email"] = $email;
                    }

                    // Priority 1: Additional user info fields
                    if (!empty($department)) {
                        $params["department"] = $department;
                    }

                    if (!empty($company)) {
                        $params["company"] = $company;
                    }

                    if (!empty($mobilephone)) {
                        $params["mobilephone"] = $mobilephone;
                    }

                    if (!empty($workphone)) {
                        $params["workphone"] = $workphone;
                    }

                    if (!empty($homephone)) {
                        $params["homephone"] = $homephone;
                    }

                    // Priority 4: Location info fields
                    if (!empty($address)) {
                        $params["address"] = $address;
                    }

                    if (!empty($city)) {
                        $params["city"] = $city;
                    }

                    if (!empty($state)) {
                        $params["state"] = $state;
                    }

                    if (!empty($country)) {
                        $params["country"] = $country;
                    }

                    if (!empty($zip)) {
                        $params["zip"] = $zip;
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

                include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);

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

    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);

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
                                        "tooltipText" => 'Notice that for supported password-like attributes, ' .
                                                         'you can just specify a plaintext value. ' .
                                                         'The system will take care of correctly hashing it.',
                                    );

        if ($cleartextPasswordAllowed) {
            $input_descriptors1[] = array(
                                            "type" =>"select",
                                            "name" => "enableportallogin",
                                            "caption" => "Enable Portal Login",
                                            "options" => [ "yes", "no" ],
                                            "selected_value" => ((isset($failureMsg)) ? $enableportallogin : "no"),
                                            "tooltipText" => "If set to 'yes', " .
                                                             "allows the use of username and password for logging into the user portal.",
                                        );
        }

        $input_descriptors1[] = array(
                                        "caption" => t('all','CSVData'),
                                        "type" => "textarea",
                                        "name" => "csvdata",
                                        "tooltipText" => 'Paste a CSV-formatted data input of users.<br/><br/>' .
                                                         '<b>Required fields (5):</b> username,password,email,firstname,lastname<br/><br/>' .
                                                         '<b>Optional fields (15):</b><br/>' .
                                                         '• framedipaddress - Valid IPv4 address<br/>' .
                                                         '• expiration - Date in YYYY-MM-DD format<br/>' .
                                                         '• department - Department name<br/>' .
                                                         '• company - Company name<br/>' .
                                                         '• mobilephone - Mobile phone number<br/>' .
                                                         '• workphone - Work phone number<br/>' .
                                                         '• homephone - Home phone number<br/>' .
                                                         '• address - Street address<br/>' .
                                                         '• city - City name<br/>' .
                                                         '• state - State/Province<br/>' .
                                                         '• country - Country name<br/>' .
                                                         '• zip - Postal/ZIP code<br/>' .
                                                         '• sessiontimeout - Session timeout in seconds<br/>' .
                                                         '• idletimeout - Idle timeout in seconds<br/>' .
                                                         '• maxdailysession - Max daily session in seconds<br/><br/>' .
                                                         '<b>Example:</b> user001,pass123,user@example.com,John,Doe,192.168.1.100,2026-12-31,IT,ACME Corp,+1234567890,,,New York,NY,USA,10001,3600,600,86400<br/><br/>' .
                                                         'Note: Rows with more than 20 CSV fields will be skipped.',
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

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);

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
