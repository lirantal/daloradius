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

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    // we import validation facilities
    include_once("library/validation.php");

    // TODO validate user input
    $username = (array_key_exists('username', $_POST) && isset($_POST['username']))
              ? trim(str_replace("%", "", $_POST['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

    isset($_POST['authType']) ? $authType = $_POST['authType'] : $authType = "";

    $password = (array_key_exists('password', $_POST) && isset($_POST['password'])) ? $_POST['password'] : "";
    $passwordType = (array_key_exists('passwordType', $_POST) && isset($_POST['passwordType']) &&
                     in_array($_POST['passwordType'], $valid_passwordTypes)) ? $_POST['passwordType'] : "";
    
    $macaddress = (array_key_exists('macaddress', $_POST) && isset($_POST['macaddress']) &&
                   filter_var(trim($_POST['macaddress']), FILTER_VALIDATE_MAC)) ? trim($_POST['macaddress']) : "";
                   
    $pincode = (array_key_exists('pincode', $_POST) && isset($_POST['pincode'])) ? trim($_POST['pincode']) : "";

    // this can be used for all authTypes
    $groups = (array_key_exists('groups', $_POST) && isset($_POST['groups'])) ? $_POST['groups'] : array();

    // billing info variables
    $bi_contactperson = (array_key_exists('bi_contactperson', $_POST) && isset($_POST['bi_contactperson'])) ? $_POST['bi_contactperson'] : "";
    $bi_company = (array_key_exists('bi_company', $_POST) && isset($_POST['bi_company'])) ? $_POST['bi_company'] : "";
    $bi_email = (array_key_exists('bi_email', $_POST) && isset($_POST['bi_email'])) ? $_POST['bi_email'] : "";
    $bi_phone = (array_key_exists('bi_phone', $_POST) && isset($_POST['bi_phone'])) ? $_POST['bi_phone'] : "";
    $bi_address = (array_key_exists('bi_address', $_POST) && isset($_POST['bi_address'])) ? $_POST['bi_address'] : "";
    $bi_city = (array_key_exists('bi_city', $_POST) && isset($_POST['bi_city'])) ? $_POST['bi_city'] : "";
    $bi_state = (array_key_exists('bi_state', $_POST) && isset($_POST['bi_state'])) ? $_POST['bi_state'] : "";
    $bi_country = (array_key_exists('bi_country', $_POST) && isset($_POST['bi_country'])) ? $_POST['bi_country'] : "";
    $bi_zip = (array_key_exists('bi_zip', $_POST) && isset($_POST['bi_zip'])) ? $_POST['bi_zip'] : "";
    $bi_paymentmethod = (array_key_exists('bi_paymentmethod', $_POST) && isset($_POST['bi_paymentmethod'])) ? $_POST['bi_paymentmethod'] : "";
    $bi_cash = (array_key_exists('bi_cash', $_POST) && isset($_POST['bi_cash'])) ? $_POST['bi_cash'] : "";
    $bi_creditcardname = (array_key_exists('bi_creditcardname', $_POST) && isset($_POST['bi_creditcardname'])) ? $_POST['bi_creditcardname'] : "";
    $bi_creditcardnumber = (array_key_exists('bi_creditcardnumber', $_POST) && isset($_POST['bi_creditcardnumber'])) ? $_POST['bi_creditcardnumber'] : "";
    $bi_creditcardverification = (array_key_exists('bi_creditcardverification', $_POST) && isset($_POST['bi_creditcardverification'])) ? $_POST['bi_creditcardverification'] : "";
    $bi_creditcardtype = (array_key_exists('bi_creditcardtype', $_POST) && isset($_POST['bi_creditcardtype'])) ? $_POST['bi_creditcardtype'] : "";
    $bi_creditcardexp = (array_key_exists('bi_creditcardexp', $_POST) && isset($_POST['bi_creditcardexp'])) ? $_POST['bi_creditcardexp'] : "";
    $bi_notes = (array_key_exists('bi_notes', $_POST) && isset($_POST['bi_notes'])) ? $_POST['bi_notes'] : "";
    
    isset($_POST['bi_lead']) ? $bi_lead = $_POST['bi_lead'] : $bi_lead = "";
    isset($_POST['bi_coupon']) ? $bi_coupon = $_POST['bi_coupon'] : $bi_coupon = "";
    isset($_POST['bi_ordertaker']) ? $bi_ordertaker = $_POST['bi_ordertaker'] : $bi_ordertaker = "";
    isset($_POST['bi_billstatus']) ? $bi_billstatus = $_POST['bi_billstatus'] : $bi_billstatus = "";
    isset($_POST['bi_lastbill']) ? $bi_lastbill = $_POST['bi_lastbill'] : $bi_lastbill = "";
    isset($_POST['bi_nextbill']) ? $bi_nextbill = $_POST['bi_nextbill'] : $bi_nextbill = "";
    isset($_POST['bi_nextinvoicedue']) ? $bi_nextinvoicedue = $_POST['bi_nextinvoicedue'] : $bi_nextinvoicedue = "";
    isset($_POST['bi_billdue']) ? $bi_billdue = $_POST['bi_billdue'] : $bi_billdue = "";
    isset($_POST['bi_postalinvoice']) ? $bi_postalinvoice = $_POST['bi_postalinvoice'] : $bi_postalinvoice = "";
    isset($_POST['bi_faxinvoice']) ? $bi_faxinvoice = $_POST['bi_faxinvoice'] : $bi_faxinvoice = "";
    isset($_POST['bi_emailinvoice']) ? $bi_emailinvoice = $_POST['bi_emailinvoice'] : $bi_emailinvoice = "";
    
    $bi_changeuserbillinfo = (array_key_exists('changeUserBillInfo', $_POST) && isset($_POST['changeUserBillInfo'])) ? $_POST['changeUserBillInfo'] : "0";

    $firstname = (array_key_exists('firstname', $_POST) && isset($_POST['firstname'])) ? $_POST['firstname'] : "";
    $lastname = (array_key_exists('lastname', $_POST) && isset($_POST['lastname'])) ? $_POST['lastname'] : "";
    $email = (array_key_exists('email', $_POST) && isset($_POST['email'])) ? $_POST['email'] : "";
    $department = (array_key_exists('department', $_POST) && isset($_POST['department'])) ? $_POST['department'] : "";
    $company = (array_key_exists('company', $_POST) && isset($_POST['company'])) ? $_POST['company'] : "";
    $workphone = (array_key_exists('workphone', $_POST) && isset($_POST['workphone'])) ? $_POST['workphone'] : "";
    $homephone = (array_key_exists('homephone', $_POST) && isset($_POST['homephone'])) ? $_POST['homephone'] : "";
    $mobilephone = (array_key_exists('mobilephone', $_POST) && isset($_POST['mobilephone'])) ? $_POST['mobilephone'] : "";
    $address = (array_key_exists('address', $_POST) && isset($_POST['address'])) ? $_POST['address'] : "";
    $city = (array_key_exists('city', $_POST) && isset($_POST['city'])) ? $_POST['city'] : "";
    $state = (array_key_exists('state', $_POST) && isset($_POST['state'])) ? $_POST['state'] : "";
    $country = (array_key_exists('country', $_POST) && isset($_POST['country'])) ? $_POST['country'] : "";
    $zip = (array_key_exists('zip', $_POST) && isset($_POST['zip'])) ? $_POST['zip'] : "";
    $notes = (array_key_exists('notes', $_POST) && isset($_POST['notes'])) ? $_POST['notes'] : "";
    $ui_changeuserinfo = (array_key_exists('changeuserinfo', $_POST) && isset($_POST['changeuserinfo'])) ? $_POST['changeuserinfo'] : "0";
    $ui_enableUserPortalLogin = (array_key_exists('enableUserPortalLogin', $_POST) && isset($_POST['enableUserPortalLogin'])) ? $_POST['enableUserPortalLogin'] : "0";
    $ui_PortalLoginPassword = (array_key_exists('portalLoginPassword', $_POST) && isset($_POST['portalLoginPassword'])) ? $_POST['portalLoginPassword'] : "";
    
    isset($_POST['dictAttributes']) ? $dictAttributes = $_POST['dictAttributes'] : $dictAttributes = "";        


    function addGroups($dbSocket, $username, $groups) {

        global $logDebugSQL;
        global $configValues;

        // insert usergroup mapping
        // check if any group should be added
        if (count($groups) > 0) {
            foreach ($groups as $group) {
                $group = trim($group);
                
                if (empty($group)) {
                    continue;
                }

                $sql = sprintf("INSERT INTO %s (username, groupname, priority) VALUES ('%s', '%s', 0)",
                               $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                               $dbSocket->escapeSimple($username),
                               $dbSocket->escapeSimple($group));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
            }
        }
    }


    function addUserInfo($dbSocket, $username) {

        global $firstname;
        global $lastname;
        global $email;
        global $department;
        global $company;
        global $workphone;
        global $homephone;
        global $mobilephone;
        global $address;
        global $city;
        global $state;
        global $country;
        global $zip;
        global $notes;
        global $ui_changeuserinfo;
        global $ui_PortalLoginPassword;
        global $ui_enableUserPortalLogin;
        
        global $logDebugSQL;
        global $configValues;

        $currDate = date('Y-m-d H:i:s');
        $currBy = $_SESSION['operator_user'];

        // insert userinfo
        $sql = sprintf("SELECT COUNT(DISTINCT(username)) FROM %s WHERE username='%s'",
                       $configValues['CONFIG_DB_TBL_DALOUSERINFO'], $dbSocket->escapeSimple($username));
        $res = $dbSocket->query($sql);
        $userinfoExist = intval($res->fetchrow()[0]) > 0;
        $logDebugSQL .= "$sql;\n";
        
        // if there were no records for this user present in the userinfo table
        if (!$userinfoExist) {
            // insert user information table
            $sql = sprintf("INSERT INTO %s (id, username, firstname, lastname, email, department, company,
                                            workphone, homephone,  mobilephone, address, city, state, country,
                                            zip, notes, changeuserinfo, portalloginpassword, enableportallogin,
                                            creationdate, creationby, updatedate, updateby) 
                                   VALUES (0, '%s',  '%s',  '%s',  '%s',  '%s',  '%s',  '%s',  '%s',  '%s', 
                                           '%s',  '%s',  '%s',  '%s',  '%s',  '%s',  '%s',  '%s',  '%s', '%s',
                                           '%s', NULL, NULL)", $configValues['CONFIG_DB_TBL_DALOUSERINFO'],
                                                               $dbSocket->escapeSimple($username), $dbSocket->escapeSimple($firstname),
                                                               $dbSocket->escapeSimple($lastname), $dbSocket->escapeSimple($email),
                                                               $dbSocket->escapeSimple($department), $dbSocket->escapeSimple($company),
                                                               $dbSocket->escapeSimple($workphone), $dbSocket->escapeSimple($homephone),
                                                               $dbSocket->escapeSimple($mobilephone), $dbSocket->escapeSimple($address),
                                                               $dbSocket->escapeSimple($city), $dbSocket->escapeSimple($state),
                                                               $dbSocket->escapeSimple($country), $dbSocket->escapeSimple($zip),
                                                               $dbSocket->escapeSimple($notes), $dbSocket->escapeSimple($ui_changeuserinfo),
                                                               $dbSocket->escapeSimple($ui_PortalLoginPassword),
                                                               $dbSocket->escapeSimple($ui_enableUserPortalLogin),
                                                               $dbSocket->escapeSimple($currDate), $dbSocket->escapeSimple($currBy));
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";
            return true;
        }
        
        return false;

    }



    function addUserBillInfo($dbSocket, $username) {

        global $bi_contactperson;
        global $bi_company;
        global $bi_email;
        global $bi_phone;
        global $bi_address;
        global $bi_city;
        global $bi_state;
        global $bi_country;
        global $bi_zip;
        global $bi_paymentmethod;
        global $bi_cash;
        global $bi_creditcardname;
        global $bi_creditcardnumber;
        global $bi_creditcardexp;
        global $bi_creditcardverification;
        global $bi_creditcardtype;
        global $bi_notes;
        global $bi_lead;
        global $bi_coupon;
        global $bi_ordertaker;
        global $bi_billstatus;
        global $bi_lastbill;
        global $bi_nextbill;
        global $bi_nextinvoicedue;
        global $bi_billdue;
        global $bi_postalinvoice;
        global $bi_faxinvoice;
        global $bi_emailinvoice;
        global $bi_changeuserbillinfo;
        global $logDebugSQL;
        global $configValues;

        $currDate = date('Y-m-d H:i:s');
        $currBy = $_SESSION['operator_user'];

        // insert user billing info
        $sql = sprintf("SELECT COUNT(DISTINCT(username)) FROM %s WHERE username='%s'",
                       $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'], $dbSocket->escapeSimple($username));
        $res = $dbSocket->query($sql);
        $userbillinfoExits = $res->fetchrow()[0];
        $logDebugSQL .= "$sql;\n";
        
        if (!$userbillinfoExits) {
            // insert user billing information table
            $sql = sprintf("INSERT INTO %s (id, username, contactperson, company, email, phone, address,
                                            city, state, country, zip, paymentmethod, cash, creditcardname,
                                            creditcardnumber, creditcardverification, creditcardtype,
                                            creditcardexp, notes, changeuserbillinfo, creationdate,
                                            creationby, updatedate, updateby)
                                   VALUES (0, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                                           '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                                           NULL, NULL)", $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                                         $dbSocket->escapeSimple($username), $dbSocket->escapeSimple($bi_contactperson),
                                                         $dbSocket->escapeSimple($bi_company), $dbSocket->escapeSimple($bi_email),
                                                         $dbSocket->escapeSimple($bi_phone), $dbSocket->escapeSimple($bi_address),
                                                         $dbSocket->escapeSimple($bi_city), $dbSocket->escapeSimple($bi_state),
                                                         $dbSocket->escapeSimple($bi_country), $dbSocket->escapeSimple($bi_zip),
                                                         $dbSocket->escapeSimple($bi_paymentmethod), $dbSocket->escapeSimple($bi_cash),
                                                         $dbSocket->escapeSimple($bi_creditcardname),
                                                         $dbSocket->escapeSimple($bi_creditcardnumber), 
                                                         $dbSocket->escapeSimple($bi_creditcardverification),
                                                         $dbSocket->escapeSimple($bi_creditcardtype),
                                                         $dbSocket->escapeSimple($bi_creditcardexp), $dbSocket->escapeSimple($bi_notes),
                                                         $dbSocket->escapeSimple($bi_changeuserbillinfo), $currDate, $currBy);
            
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";
            return true;
        }
        
        return false;

    }


    function addAttributes($dbSocket, $username) {
        
        global $logDebugSQL;
        global $configValues;

        $skipList = array( "authType", "username", "password", "passwordType", "groups",
                           "macaddress", "pincode", "submit", "firstname", "lastname", "email",
                           "department", "company", "workphone", "homephone", "mobilephone", "address", "city",
                           "state", "country", "zip", "notes", "bi_contactperson", "bi_company", "bi_email", "bi_phone",
                           "bi_address", "bi_city", "bi_state", "bi_country", "bi_zip", "bi_paymentmethod", "bi_cash",
                           "bi_creditcardname", "bi_creditcardnumber", "bi_creditcardverification", "bi_creditcardtype",
                           "bi_creditcardexp", "bi_notes", "bi_lead", "bi_coupon", "bi_ordertaker", "bi_billstatus",
                           "bi_lastbill", "bi_nextbill", "bi_nextinvoicedue", "bi_billdue", "bi_postalinvoice", "bi_faxinvoice",
                           "bi_emailinvoice", "changeUserBillInfo", "changeUserInfo", "copycontact", "portalLoginPassword",
                           "enableUserPortalLogin"
                         );

        $result = 0;

        foreach ($_POST as $element => $field) {

            // we skip several attributes (contained in the $skipList array)
            // which we do not wish to process (ie: do any sql related stuff in the db)
            if (in_array($element, $skipList)) {
                continue;
            }
            
            // we need $field to be exactly an array with 4 fields:
            // $attribute, $value, $op, $table
            if (!is_array($field) || count($field) != 4) {
                continue;
            }
            
            // we trim all array values
            foreach ($field as $i => $v) {
                $field[$i] = trim($v);
            }
            
            list($attribute, $value, $op, $table) = $field;
            
            // value and attribute are required
            if (empty($value) || empty($attribute)) {
                    continue;
            }

            // we only accept valid ops
            if (!in_array($op, $valid_ops)) {
                continue;
            }

            // $table value can be only '(rad)reply' or '(rad)check'
            $table = strtolower($table);
            if (in_array($table, array('reply', 'radreply'))) {
                $table = $configValues['CONFIG_DB_TBL_RADREPLY'];
            } else if (in_array($table, array('check', 'radcheck'))) {
                $table = $configValues['CONFIG_DB_TBL_RADCHECK'];
            } else {
                continue;
            }

            // if all checks are passed, we insert the new attribute
            $sql = sprintf("INSERT INTO %s (id, username, attribute, op, value) VALUES (0, '%s', '%s', '%s', '%s')",
                           $table, $dbSocket->escapeSimple($username), $dbSocket->escapeSimple($attribute),
                           $dbSocket->escapeSimple($op), $dbSocket->escapeSimple($value));
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";

            $result++;

        } // foreach

        return ($result > 0);
    }

    if (isset($_POST['submit'])) {
        include('library/opendb.php');

        // we will have a $username_to_check, only
        // if required arguments have been supplied
        // according to the chosen $authType
        $username_to_check = "";
        
        if ($authType == "userAuth") {
            // we can add a new record to the check table
            // only if $username and $password are not empty
            if (!empty($username) && !empty($password)) {
                $username_to_check = $username;
            } else {
                $failureMsg = "Username and/or password are invalid";
                
            }
        } else if ($authType == "macAuth") {
            if (!empty($macaddress)) {
                $username_to_check = $macaddress;
            } else {
                $failureMsg = "MAC address is invalid";
            }
        } else if ($authType == "pincodeAuth") {
            if (!empty($pincode)) {
                $username_to_check = $pincode;
            } else {
                $failureMsg = "PIN code is invalid";
            }
        } else {
            // authentication method is invalid
            $failureMsg = "Unknown authentication method";
        }

        if (empty($username_to_check)) {
            // failure message has been set above
            $logAction .= "Failed adding a new user ($failureMsg) on page: ";
            
        } else {
            // we can proceed and check if username/mac address/pincode is already present in the radcheck table
            $sql = sprintf("SELECT COUNT(DISTINCT(username)) FROM %s WHERE username='%s'",
                           $configValues['CONFIG_DB_TBL_RADCHECK'], $dbSocket->escapeSimple($username_to_check));
            $res = $dbSocket->query($sql);
            $exists = (intval($res->fetchrow()[0]) > 0);
            $logDebugSQL .= "$sql;\n";

            // we proceed only if username/mac address/pincode is not present
            if ($exists) {
                // user exists
                $failureMsg = "record already found in database: <b> $username_to_check </b>";
                $logAction .= "Failed adding new user already existing in database [$username_to_check] on page: ";
            } else {
                if ($authType == "userAuth") {
                    // we need to perform the secure method escapeSimple on $dbPassword early because as seen below
                    // we manipulate the string and manually add to it the '' which screw up the query if added in $sql
                    $password = $dbSocket->escapeSimple($password);

                    switch (strtolower($configValues['CONFIG_DB_PASSWORD_ENCRYPTION'])) {
                        case "crypt":
                            $dbPassword = sprintf("ENCRYPT('%s', 'SALT_DALORADIUS')", $password);
                            break;
                        
                        case "md5":
                            $dbPassword = sprintf("MD5('%s')", $password);
                            break;
                            
                        default:
                        case "cleartext":
                            $dbPassword = sprintf("'%s'", $password);
                    }
                    
                    // at this stage $dbPassword contains the password string encapsulated by '' and either uses
                    // a function to encrypt it like ENCRYPT or it doesn't, it's based on the configuration
                    // but here we provide another stage, for Crypt-Password and MD5-Password it's obvious
                    // that the password need be encrypted so even if this option is not in the configuration
                    // we enforce it.

                    // we first check if the password attribute is to be encrypted at all
                    if (preg_match("/crypt/i", $passwordType)) {
                        // if we don't find the encrypt function even though we identified
                        // a Crypt-Password attribute
                        if (!(preg_match("/encrypt/i",$dbPassword))) {
                            $dbPassword = "ENCRYPT('$password', 'SALT_DALORADIUS')";
                        }
                
                        // we now perform the same check but for an MD5-Password attribute
                    } else if (preg_match("/md5/i", $passwordType)) {
                        // if we don't find the md5 function even though we identified
                        // a MD5-Password attribute
                        if (!(preg_match("/md5/i",$dbPassword))) {
                            $dbPassword = "MD5('$password')";
                        }
                    }

                    // insert username/password
                    $sql = sprintf("INSERT INTO %s (id, username, attribute, op, value) VALUES (0, '%s', '%s', ':=', %s)",
                                   $configValues['CONFIG_DB_TBL_RADCHECK'], $dbSocket->escapeSimple($username),
                                   $dbSocket->escapeSimple($passwordType), $dbPassword);
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                    
                    addGroups($dbSocket, $username, $groups);
                    addUserInfo($dbSocket, $username);
                    addUserBillInfo($dbSocket, $username);
                    addAttributes($dbSocket, $username);

                    $successMsg = sprintf("Added to database new user: <b>%s</b>", $username_enc);
                    $logAction .= "Successfully added new user [$username] on page: ";
                } else if ($authType == "macAuth") {
                    // insert macaddress as username
                    $sql = sprintf("INSERT INTO %s (id, username, attribute, op, value) VALUES (0, '%s', 'Auth-Type', ':=', 'Accept')",
                                   $configValues['CONFIG_DB_TBL_RADCHECK'], $dbSocket->escapeSimple($macaddress));
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";

                    addGroups($dbSocket, $macaddress, $groups);
                    addUserInfo($dbSocket, $macaddress);
                    addUserBillInfo($dbSocket, $username);
                    addAttributes($dbSocket, $macaddress);

                    $successMsg = "Added to database new mac auth user: <b> $macaddress </b>";
                    $logAction .= "Successfully added new mac auth user [$macaddress] on page: ";
                    
               } else if ($authType == "pincodeAuth") {
                   // insert pincode as username
                   $sql = sprintf("INSERT INTO %s (id, username, attribute, op, value) VALUES (0, '%s', 'Auth-Type', ':=', 'Accept')",
                                  $configValues['CONFIG_DB_TBL_RADCHECK'], $dbSocket->escapeSimple($pincode));
                   $res = $dbSocket->query($sql);
                   $logDebugSQL .= "$sql;\n";

                   addGroups($dbSocket, $pincode, $groups);
                   addUserInfo($dbSocket, $pincode);
                   addUserBillInfo($dbSocket, $username);
                   addAttributes($dbSocket, $pincode);

                   $successMsg = "Added to database new pincode: <b> $pincode </b>";
                   $logAction .= "Successfully added new pincode [$pincode] on page: ";
               }
               
               // TODO delete values
            }
        }
        
        include('library/closedb.php');
    }

    include_once('library/config_read.php');
    
    $hiddenPassword = (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) == "yes")
                    ? 'password' : 'text';
    
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    // print HTML prologue
    $extra_css = array(
        // css tabs stuff
        "css/tabs.css"
    );
    
    $extra_js = array(
        "library/javascript/ajax.js",
        "library/javascript/dynamic_attributes.js",
        "library/javascript/ajaxGeneric.js",
        "library/javascript/productive_funcs.js",
        // js tabs stuff
        "library/javascript/tabs.js"
    );
    
    $title = t('Intro','mngnew.php');
    $help = t('helpPage','mngnew');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);
    
    include("menu-mng-users.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    include_once('include/management/populate_selectbox.php');
    
    $input_descriptors1 = array();
    
    $select_descriptor = array(
                                    "type" =>"select",
                                    "name" => "authType",
                                    "caption" => "Authentication Type",
                                    "options" => $valid_authTypes,
                                    "onchange" => "switchAuthType()",
                                    "selected_value" => ((isset($failureMsg)) ? $authType : "")
                                 );
    
    $input_descriptors1[] = array(
                                    "id" => "username",
                                    "name" => "username",
                                    "caption" => t('all','Username'),
                                    "type" => "text",
                                    "random" => true,
                                    "value" => ((isset($failureMsg)) ? $username : ""),
                                    "tooltipText" => t('Tooltip','usernameTooltip')
                                 );
                                
    $input_descriptors1[] = array(
                                    "id" => "password",
                                    "name" => "password",
                                    "caption" => t('all','Password'),
                                    "type" => $hiddenPassword,
                                    "random" => true,
                                    "tooltipText" => t('Tooltip','passwordTooltip')
                                 );
    $input_descriptors1[] = array(
                                    "name" => "passwordType",
                                    "caption" => t('all','PasswordType'),
                                    "options" => $valid_passwordTypes,
                                    "type" => "select",
                                    "selected_value" => ((isset($failureMsg)) ? $passwordType : "")
                                );
                                
    $input_descriptors2 = array();
    $input_descriptors2[] = array(
                                    "name" => "macaddress",
                                    "caption" => t('all','MACAddress'),
                                    "type" => "text",
                                    "value" => ((isset($failureMsg)) ? $macaddress : ""),
                                    "tooltipText" => t('Tooltip','macaddressTooltip')
                                 );
                                 
    $input_descriptors3 = array();
    $input_descriptors3[] = array(
                                    "name" => "pincode",
                                    "caption" => t('all','PINCode'),
                                    "type" => "text",
                                    "value" => ((isset($failureMsg)) ? $pincode : ""),
                                    "tooltipText" => t('Tooltip','pincodeTooltip')
                                 );
                                 
    $button_descriptor = array(
                                'type' => 'submit',
                                'name' => 'submit',
                                'value' => t('buttons','apply')
                              );

    // draw navbar
    $navbuttons = array(
                            'AccountInfo-tab' => t('title','AccountInfo'),
                            'UserInfo-tab' => t('title','UserInfo'),
                            'BillingInfo-tab' => t('title','BillingInfo'),
                            'Attributes-tab' => t('title','Attributes'),
                       );

    print_tab_navbuttons($navbuttons);
?>
     
<form name="newuser" method="POST">
    
    <div id="AccountInfo-tab" class="tabcontent" title="<?= t('title','AccountInfo') ?>" style="display: block">
    
        <fieldset>
            <h302>Common parameters</h302>
            <ul>
            
<?php
                print_form_component($select_descriptor);
?>
                <li class="fieldset">
                    <label for='group' class='form'><?= t('all','Group')?></label>
<?php
                    populate_groups("Select Groups","groups[]");
                    $onclick = "javascript:ajaxGeneric('include/management/dynamic_groups.php','getGroups','divContainerGroups',"
                             . "genericCounter('divCounter')+'&elemName=groups[]')";
?>

                    <a class='tablenovisit' href='#' onclick="<?= $onclick ?>">Add</a>
                    <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('group')" />
                    <div id='divContainerGroups'></div>


                    <div id='groupTooltip' style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0'><?= t('Tooltip','groupTooltip') ?>
                    </div>
                </li>

            </ul>
        </fieldset>
    
        <fieldset id="userAuth-fieldset">

            <h302>Username/password info</h302>
            
            <ul>
<?php
                foreach ($input_descriptors1 as $input_descriptor) {
                    print_form_component($input_descriptor);
                }
?>

            </ul>
        </fieldset>


        <fieldset id="macAuth-fieldset">

            <h302>MAC Address info</h302>
            
            <ul>

<?php
                foreach ($input_descriptors2 as $input_descriptor) {
                    print_form_component($input_descriptor);
                }   
?>

            </ul>

        </fieldset>

        <fieldset id="pincodeAuth-fieldset">

            <h302>PIN code info</h302>

            <ul>

<?php
                foreach ($input_descriptors3 as $input_descriptor) {
                    print_form_component($input_descriptor);
                }   
?>

            </ul>

        </fieldset>

<?php
        print_form_component($button_descriptor);
?>
    </div>

    <div id="UserInfo-tab" class="tabcontent" title="<?= t('title','UserInfo') ?>">
<?php
        $customApplyButton = "<input type='submit' name='submit' value=".t('buttons','apply')." class='button' />";
        include_once('include/management/userinfo.php');
?>
    </div><!-- .tabbertab -->

    <div id="BillingInfo-tab" class="tabcontent" title="<?= t('title','BillingInfo') ?>">
<?php
        $customApplyButton = "<input type='submit' name='submit' value=".t('buttons','apply')." class='button' />";
        include_once('include/management/userbillinfo.php');
?>
    </div><!-- .tabbertab -->

    <div id="Attributes-tab" class="tabcontent" title="<?= t('title','Attributes') ?>">
<?php
    include_once('include/management/attributes.php');
?>
    </div><!-- .tabbertab -->
</form>


        </div><!-- #contentnorightbar -->
        
        <div id="footer">
<?php
    include('include/config/logging.php');
    include('page-footer.php');
?>
        </div><!-- #footer -->
    </div>
</div>

<script>
    function switchAuthType() {
        var switcher = document.getElementById("authType");
        
        for (var i=0; i<switcher.length; i++) {
            var fieldset_id = switcher[i].value + "-fieldset";
            document.getElementById(fieldset_id).disabled = (switcher.value != switcher[i].value);
        }
    }

    switchAuthType();
</script>

</body>
</html>
