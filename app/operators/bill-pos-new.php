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
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");
    include_once("include/management/functions.php");
    include('include/management/pages_common.php');
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    // if cleartext passwords are not allowed, 
    // we remove Cleartext-Password from the $valid_passwordTypes array
    if (isset($configValues['CONFIG_DB_PASSWORD_ENCRYPTION']) &&
        strtolower(trim($configValues['CONFIG_DB_PASSWORD_ENCRYPTION'])) !== 'yes') {
        $valid_passwordTypes = array_values(array_diff($valid_passwordTypes, array("Cleartext-Password")));
    }
    
    $username = (array_key_exists('username', $_POST) && isset($_POST['username']))
              ? trim(str_replace("%", "", $_POST['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

    $password = (array_key_exists('password', $_POST) && isset($_POST['password'])) ? trim($_POST['password']) : "";
    $passwordType = (array_key_exists('passwordType', $_POST) && isset($_POST['passwordType']) &&
                     in_array($_POST['passwordType'], $valid_passwordTypes)) ? $_POST['passwordType'] : "";
    $profiles = (array_key_exists('profiles', $_POST) && isset($_POST['profiles'])) ? $_POST['profiles'] : array();

    $planName = (array_key_exists('planName', $_POST) && isset($_POST['planName']))
              ? trim(str_replace("%", "", $_POST['planName'])) : "";
    
    $notificationWelcome = (
                                array_key_exists('notificationWelcome', $_POST) &&
                                !empty(strtolower(trim($_POST['notificationWelcome']))) &&
                                in_array(strtolower(trim($_POST['notificationWelcome'])), array("yes", "no"))
                            ) ? strtolower(trim($_POST['notificationWelcome'])) : "yes";
                
    
    // user info variables
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
    
    // first we check user portal login password
    $ui_PortalLoginPassword = (isset($_POST['portalLoginPassword']) && !empty(trim($_POST['portalLoginPassword'])))
                            ? trim($_POST['portalLoginPassword']) : "";
    
    // these are forced to 0 (disabled) if user portal login password is empty
    $ui_changeuserinfo = (!empty($ui_PortalLoginPassword) && isset($_POST['changeUserInfo']) && $_POST['changeUserInfo'] === '1')
                       ? '1' : '0';
    $ui_enableUserPortalLogin = (!empty($ui_PortalLoginPassword) && isset($_POST['enableUserPortalLogin']) && $_POST['enableUserPortalLogin'] === '1')
                              ? '1' : '0';
    
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
    
    $bi_postalinvoice = (array_key_exists('bi_postalinvoice', $_POST) && isset($_POST['bi_postalinvoice'])) ? $_POST['bi_postalinvoice'] : "";
    $bi_faxinvoice = (array_key_exists('bi_faxinvoice', $_POST) && isset($_POST['bi_faxinvoice'])) ? $_POST['bi_faxinvoice'] : "";
    $bi_emailinvoice = (array_key_exists('bi_emailinvoice', $_POST) && isset($_POST['bi_emailinvoice'])) ? $_POST['bi_emailinvoice'] : "";
    
    $bi_paymentmethod = (array_key_exists('bi_paymentmethod', $_POST) && isset($_POST['bi_paymentmethod'])) ? $_POST['bi_paymentmethod'] : "";
    $bi_cash = (array_key_exists('bi_cash', $_POST) && isset($_POST['bi_cash'])) ? $_POST['bi_cash'] : "";
    $bi_creditcardname = (array_key_exists('bi_creditcardname', $_POST) && isset($_POST['bi_creditcardname'])) ? $_POST['bi_creditcardname'] : "";
    $bi_creditcardnumber = (array_key_exists('bi_creditcardnumber', $_POST) && isset($_POST['bi_creditcardnumber'])) ? $_POST['bi_creditcardnumber'] : "";
    $bi_creditcardverification = (array_key_exists('bi_creditcardverification', $_POST) && isset($_POST['bi_creditcardverification'])) ? $_POST['bi_creditcardverification'] : "";
    $bi_creditcardtype = (array_key_exists('bi_creditcardtype', $_POST) && isset($_POST['bi_creditcardtype'])) ? $_POST['bi_creditcardtype'] : "";
    $bi_creditcardexp = (array_key_exists('bi_creditcardexp', $_POST) && isset($_POST['bi_creditcardexp'])) ? $_POST['bi_creditcardexp'] : "";
    
    $bi_lead = (array_key_exists('bi_lead', $_POST) && isset($_POST['bi_lead'])) ? $_POST['bi_lead'] : "";
    $bi_coupon = (array_key_exists('bi_coupon', $_POST) && isset($_POST['bi_coupon'])) ? $_POST['bi_coupon'] : "";
    $bi_ordertaker = (array_key_exists('bi_ordertaker', $_POST) && isset($_POST['bi_ordertaker'])) ? $_POST['bi_ordertaker'] : "";
    
    $bi_notes = (array_key_exists('bi_notes', $_POST) && isset($_POST['bi_notes'])) ? $_POST['bi_notes'] : "";
    $bi_billstatus = (array_key_exists('bi_billstatus', $_POST) && isset($_POST['bi_billstatus'])) ? $_POST['bi_billstatus'] : "";
    $bi_lastbill = (array_key_exists('bi_lastbill', $_POST) && isset($_POST['bi_lastbill'])) ? $_POST['bi_lastbill'] : "";
    $bi_nextbill = (array_key_exists('bi_nextbill', $_POST) && isset($_POST['bi_nextbill'])) ? $_POST['bi_nextbill'] : "";
    $bi_nextinvoicedue = (array_key_exists('bi_nextinvoicedue', $_POST) && isset($_POST['bi_nextinvoicedue'])) ? $_POST['bi_nextinvoicedue'] : "";
    $bi_billdue = (array_key_exists('bi_billdue', $_POST) && isset($_POST['bi_billdue'])) ? $_POST['bi_billdue'] : "";

    // this is forced to 0 (disabled) if user portal login password is empty
    $bi_changeuserbillinfo = (!empty($ui_PortalLoginPassword) && isset($_POST['bi_changeuserbillinfo']) && $_POST['bi_changeuserbillinfo'] === '1')
                           ? '1' : '0';
    
    function addPlanProfile($dbSocket, $username, $planName) {

        global $logDebugSQL;
        global $configValues;

        // search to see if the plan is associated with any profiles
        $sql = "SELECT profile_name FROM ".
                $configValues['CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES'].
                " WHERE plan_name='$planName'";
        $res = $dbSocket->getCol($sql);
        // $res is an array of all profiles associated with this plan
        
        // if the profile list for this plan isn't empty, we associate it with the user
        if (count($res) != 0) {
    
            // if profiles are associated with this plan, loop through each and add a usergroup entry for each
            foreach($res as $profile_name) {
                $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." (UserName,GroupName,priority) ".
                    " VALUES ('".$dbSocket->escapeSimple($username)."','$profile_name','0')";
                $res = $dbSocket->query($sql);
            }
            
            return true;
        
        }
        
        return false;

    }
    
 
    function addUserBillInfo($dbSocket, $username) {

        global $planName;
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

        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
                        " WHERE username='".$dbSocket->escapeSimple($username)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        // if there were no records for this user present in the userbillinfo table
        if ($res->numRows() == 0) {
            
            // calculate the nextbill and other related billing information
            $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
                            " WHERE planName='".$dbSocket->escapeSimple($planName)."' LIMIT 1";
            $res = $dbSocket->query($sql);
            $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
            $logDebugSQL .= $sql . "\n";
                            
            $planRecurring = $row['planRecurring'];
            $planRecurringPeriod = $row['planRecurringPeriod'];
            $planRecurringBillingSchedule = $row['planRecurringBillingSchedule'];
            
            
            // initialize next bill date string (Y-m-d style)
            $nextBillDate = "0000-00-00";
            
            // get next billing date
            if ($planRecurring == "Yes") {
                $nextBillDate = getNextBillingDate($planRecurringBillingSchedule, $planRecurringPeriod);
            }

        
            // if $bi_nextbill was not set to anything (empty)
            if (empty($bi_nextbill))
                $bi_nextbill = $nextBillDate;
                    
            
            
            // insert user billing information table
            $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
                    " (id, planname, username, contactperson, company, email, phone, ".
                    " address, city, state, country, zip, ".
                    " paymentmethod, cash, creditcardname, creditcardnumber, creditcardverification, creditcardtype, creditcardexp, ".
                    " notes, changeuserbillinfo, ".
                    " `lead`, coupon, ordertaker, billstatus, lastbill, nextbill, nextinvoicedue, billdue, postalinvoice, faxinvoice, emailinvoice, ".
                    " creationdate, creationby, updatedate, updateby) ".
                    " VALUES (0, '".$dbSocket->escapeSimple($planName)."', 
                    '".$dbSocket->escapeSimple($username)."', '".$dbSocket->escapeSimple($bi_contactperson)."', '".
                    $dbSocket->escapeSimple($bi_company)."', '".$dbSocket->escapeSimple($bi_email)."', '".
                    $dbSocket->escapeSimple($bi_phone)."', '".$dbSocket->escapeSimple($bi_address)."', '".
                    $dbSocket->escapeSimple($bi_city)."', '".$dbSocket->escapeSimple($bi_state)."', '".
                    $dbSocket->escapeSimple($bi_country)."', '".
                    $dbSocket->escapeSimple($bi_zip)."', '".$dbSocket->escapeSimple($bi_paymentmethod)."', '".
                    $dbSocket->escapeSimple($bi_cash)."', '".$dbSocket->escapeSimple($bi_creditcardname)."', '".
                    $dbSocket->escapeSimple($bi_creditcardnumber)."', '".$dbSocket->escapeSimple($bi_creditcardverification)."', '".
                    $dbSocket->escapeSimple($bi_creditcardtype)."', '".$dbSocket->escapeSimple($bi_creditcardexp)."', '".
                    $dbSocket->escapeSimple($bi_notes)."', '".
                    $dbSocket->escapeSimple($bi_changeuserbillinfo)."', '".
                    $dbSocket->escapeSimple($bi_lead)."', '".$dbSocket->escapeSimple($bi_coupon)."', '".
                    $dbSocket->escapeSimple($bi_ordertaker)."', '".$dbSocket->escapeSimple($bi_billstatus)."', '".
                    $dbSocket->escapeSimple($bi_lastbill)."', '".$dbSocket->escapeSimple($bi_nextbill)."', '".
                    $dbSocket->escapeSimple($bi_nextinvoicedue)."', '".$dbSocket->escapeSimple($bi_billdue)."', '".
                    $dbSocket->escapeSimple($bi_postalinvoice)."', '".$dbSocket->escapeSimple($bi_faxinvoice)."', '".
                    $dbSocket->escapeSimple($bi_emailinvoice).
                                    "', '$currDate', '$currBy', NULL, NULL)";
            $res = $dbSocket->query($sql);
            $logDebugSQL .= $sql . "\n";
            
            $user_id = $dbSocket->getOne( "SELECT LAST_INSERT_ID() FROM `".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO']."`" );
            return $user_id;
            
        } //FIXME:
          //if the user already exist in userinfo then we should somehow alert the user
          //that this has happened and the administrator/operator will take care of it

    }
    
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
        
            // required later
            $currDate = date('Y-m-d H:i:s');
            $currBy = $operator;
        
            include('../common/includes/db_open.php');

            // check if username is already present in the radcheck table
            $userExists = user_exists($dbSocket, $username);

            if ($userExists) {
                $failureMsg = "user already exist in database: <b> $username </b>";
                $logAction .= "Failed adding new user already existing in database [$username] on page: ";
            } else {
                
                // username and password are required
                if (empty($username) || empty($password)) {
                    $failureMsg = "username or password are empty";
                    $logAction .= "Failed adding (possible empty user/pass) new user [$username] on page: ";
                } else {
                    
                    // we "inject" the prepared password/auth attribute in the $_POST array.
                    // handleAttributes() - called later - will take care of it.
                    $_POST['injected_attribute'] = array( $passwordType, $password, ':=', 'check' );

                    include("library/attributes.php");

                    $skipList = array(
                                        "username", "password", "passwordType", "profiles", "planName", "notificationWelcome",
                                        "macaddress", "pincode", "submit", "firstname", "lastname", "email",
                                        "department", "company", "workphone", "homephone", "mobilephone", "address", "city",
                                        "state", "country", "zip", "notes", "bi_contactperson", "bi_company", "bi_email", "bi_phone",
                                        "bi_address", "bi_city", "bi_state", "bi_country", "bi_zip", "bi_paymentmethod", "bi_cash",
                                        "bi_creditcardname", "bi_creditcardnumber", "bi_creditcardverification", "bi_creditcardtype",
                                        "bi_creditcardexp", "bi_notes", "bi_lead", "bi_coupon", "bi_ordertaker", "bi_billstatus",
                                        "bi_lastbill", "bi_nextbill", "bi_nextinvoicedue", "bi_billdue", "bi_postalinvoice", "bi_faxinvoice",
                                        "bi_emailinvoice", "bi_changeuserbillinfo", "changeUserInfo", "copycontact", "portalLoginPassword",
                                        "enableUserPortalLogin", "csrf_token", "submit"
                                     );

                    $attributesCount = handleAttributes($dbSocket, $username, $skipList);
                    
                    $groupsCount = insert_multiple_user_group_mappings($dbSocket, $username, $profiles);
                    
                    // adding user info
                    $params = array(
                                        "firstname" => $firstname,
                                        "lastname" => $lastname,
                                        "email" => $email,
                                        "department" => $department,
                                        "company" => $company,
                                        "workphone" => $workphone,
                                        "homephone" => $homephone,
                                        "mobilephone" => $mobilephone,
                                        "address" => $address,
                                        "city" => $city,
                                        "state" => $state,
                                        "country" => $country,
                                        "zip" => $zip,
                                        "notes" => $notes,
                                        "changeuserinfo" => $ui_changeuserinfo,
                                        "enableportallogin" => $ui_enableUserPortalLogin,
                                        "portalloginpassword" => $ui_PortalLoginPassword,
                                        "creationdate" => $currDate,
                                        "creationby" => $currBy,
                                   );
                    
                    $addedUserInfo = (add_user_info($dbSocket, $username, $params)) ? "stored" : "nothing to store";

                    addPlanProfile($dbSocket, $username, $planName);
                    $userbillinfo_id = addUserBillInfo($dbSocket, $username);

                    // create any invoices if required (meaning, if a plan was chosen)
                    if ($planName) {
                        include_once("include/management/userBilling.php");
                        
                        // get plan information
                        $sql = "SELECT id, planCost, planSetupCost, planTax FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
                            " WHERE planName='".$dbSocket->escapeSimple($planName)."' LIMIT 1";
                        $res = $dbSocket->query($sql);
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

                        // calculate tax (planTax is the numerical percentage amount) 
                        $calcTax = (float) ($row['planCost'] * (float)($row['planTax'] / 100) );
                        $invoiceItems[0]['plan_id'] = $row['id'];
                        $invoiceItems[0]['amount'] = $row['planCost'];
                        $invoiceItems[0]['tax'] = $calcTax;
                        $invoiceItems[0]['notes'] = 'charge for plan service';
                        
                        if (isset($row['planSetupCost']) && ($row['planSetupCost'] != '') ) {
                            $calcTax = (float) ($row['planSetupCost'] * (float)($row['planTax'] / 100) );
                            $invoiceItems[1]['plan_id'] = $row['id'];
                            $invoiceItems[1]['amount'] = $row['planSetupCost'];
                            $invoiceItems[1]['tax'] = $calcTax;
                            $invoiceItems[1]['notes'] = 'charge for plan setup fee (one time)';
                        }
                                            
                        userInvoiceAdd($userbillinfo_id, array(), $invoiceItems);
                        
                    }
                    
                    $successMsg = sprintf('Inserted new user <strong>%s</strong>: ', $username_enc)
                                . sprintf('<a href="bill-pos-edit.php?username=%s" title="Edit">%s</a>',
                                          $username_enc, urlencode($username_enc))
                                . '<ul style="color: black">'
                                . sprintf("<li><strong>attributes count</strong>: %d</li>", $attributesCount)
                                . sprintf("<li><strong>groups count</strong>: %d</li>", $groupsCount)
                                . sprintf("<li><strong>user info</strong>: %s</li>", $addedUserInfo)
                                . "</ul>";
                    
                    $logAction .= sprintf("Successfully inserted new user [%s] on page: ", $username);
                }
            }
        
            include('../common/includes/db_close.php');

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    include_once('../common/includes/config_read.php');
    
    $hiddenPassword = (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) == "yes")
                    ? 'password' : 'text';
    

    // print HTML prologue
    $extra_css = array();
    
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js",
        "static/js/productive_funcs.js",
        "static/js/dynamic_attributes.js",
    );
    
    $title = t('Intro','billposnew.php');
    $help = t('helpPage','billposnew');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    
    if (!isset($successMsg)) {
    
        include_once('include/management/populate_selectbox.php');
        
        // set navbar stuff
        $navkeys = array( 'AccountInfo', 'UserInfo', 'BillingInfo' );

        // print navbar controls
        print_tab_header($navkeys);
        
        open_form();
        
        // open tab wrapper
        open_tab_wrapper();
        
        // open 0-th tab (shown)
        open_tab($navkeys, 0, true);
        
        // open 0-th fieldset
        $fieldset0_descriptor = array(
                                        "title" => t('title','AccountInfo'),
                                     );

        open_fieldset($fieldset0_descriptor);
        
        $input_descriptors0 = array();
        
        $input_descriptors0[] = array(
                                        "name" => "username",
                                        "caption" => t('all','Username'),
                                        "type" => "text",
                                        "value" => ((isset($failureMsg)) ? $username : ""),
                                        "random" => true,
                                        "tooltipText" => t('Tooltip','usernameTooltip')
                                     );
                                    
        $input_descriptors0[] = array(
                                        "name" => "password",
                                        "caption" => t('all','Password'),
                                        "type" => $hiddenPassword,
                                        "value" => "",
                                        "random" => true,
                                        "tooltipText" => t('Tooltip','passwordTooltip')
                                    );
        
        $input_descriptors0[] = array(
                                        "name" => "passwordType",
                                        "caption" => t('all','PasswordType'),
                                        "options" => $valid_passwordTypes,
                                        "type" => "select",
                                        "selected_value" => ((isset($failureMsg)) ? $passwordType : ""),
                                    );
        
        $options = get_active_plans();
        array_unshift($options, '');
        $input_descriptors0[] = array(
                                        "name" => "planName",
                                        "caption" => t('all','PlanName'),
                                        "type" => "select",
                                        "tooltipText" => t('Tooltip','planNameTooltip'),
                                        "options" => $options,
                                        "selected_value" => ((isset($failureMsg)) ? $planName : ""),
                                    );
        
        $options = get_groups();
        array_unshift($options, '');
        $input_descriptors0[] = array(
                                        "type" =>"select",
                                        "name" => "profiles[]",
                                        "id" => "profiles",
                                        "caption" => t('all','Profile'),
                                        "options" => $options,
                                        "multiple" => true,
                                        "size" => 5,
                                        "selected_value" => ((isset($failureMsg)) ? $groups : ""),
                                        "tooltipText" => t('Tooltip','groupTooltip')
                                     );
        
        $input_descriptors0[] = array(
                                        "name" => "notificationWelcome",
                                        "caption" => t('all','SendWelcomeNotification'),
                                        "type" => "select",
                                        "options" => array( "yes", "no" ),
                                        "selected_value" => ((isset($failureMsg)) ? $notificationWelcome : "yes"),
                                    );
        
        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        close_tab($navkeys, 0);
        
        // open 1-st tab
        open_tab($navkeys, 1);
        
        //~ $customApplyButton = sprintf('<input type="submit" name="submit" value="%s" class="button">', t('buttons','apply'));
        include_once('include/management/userinfo.php');
        
        close_tab($navkeys, 1);
        
        // open 2-nd tab
        open_tab($navkeys, 2);
        
        //~ $customApplyButton = sprintf('<input type="submit" name="submit" value="%s" class="button">', t('buttons','apply'));
        include_once('include/management/userbillinfo.php');
        
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
