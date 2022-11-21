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

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('library/config_read.php');

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";
    
    // we import validation facilities
    include_once("library/validation.php");
    
    include_once("include/management/functions.php");
    
    // required later
    $currDate = date('Y-m-d H:i:s');
    $currBy = $operator;
    
    include('library/opendb.php');
    
    // updates old plan profile with a new one
    // or simply add a new plan profile
    function addPlanProfile($dbSocket, $username, $planName, $oldplanName) {
        global $logDebugSQL;
        global $configValues;
        
        if ($planName == $oldplanName) {
            return;
        }

        $sql = sprintf("SELECT planGroup FROM %s WHERE planName='%s'",
                        $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                        $dbSocket->escapeSimple($oldplanName));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        $oldplanGroup = $res->fetchRow()[0];
        
        if (!empty($oldplanGroup)) {
            $sql = sprintf("DELETE FROM %s WHERE username='%s' AND groupname='%s'",
                           $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username),
                           $dbSocket->escapeSimple($oldplanGroup));
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";
        }

        $sql = sprintf("SELECT planGroup FROM %s WHERE planName='%s'",
                        $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                        $dbSocket->escapeSimple($planName));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        $planGroup = $res->fetchRow()[0];

        if (!empty($planGroup)) {

            $sql = sprintf("INSERT INTO %s (username, groupname, priority) VALUES ('%s', '%s', 0)",
                           $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username),
                           $dbSocket->escapeSimple($planGroup));
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // TODO validate user input
        $username = (array_key_exists('username', $_POST) && isset($_POST['username']))
                  ? trim(str_replace("%", "", $_POST['username'])) : "";
        $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

        $groups = (array_key_exists('groups', $_POST) && isset($_POST['groups'])) ? $_POST['groups'] : array();
        $newgroups = (array_key_exists('newgroups', $_POST) && isset($_POST['newgroups'])) ? $_POST['newgroups'] : array();
        $groups_priority = (array_key_exists('groups_priority', $_POST) && isset($_POST['groups_priority'])) ? $_POST['groups_priority'] : array();
        
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
    
        $bi_lead = (array_key_exists('bi_lead', $_POST) && isset($_POST['bi_lead'])) ? $_POST['bi_lead'] : "";
        $bi_coupon = (array_key_exists('bi_coupon', $_POST) && isset($_POST['bi_coupon'])) ? $_POST['bi_coupon'] : "";
        $bi_ordertaker = (array_key_exists('bi_ordertaker', $_POST) && isset($_POST['bi_ordertaker'])) ? $_POST['bi_ordertaker'] : "";
        $bi_billstatus = (array_key_exists('bi_billstatus', $_POST) && isset($_POST['bi_billstatus'])) ? $_POST['bi_billstatus'] : "";
        $bi_lastbill = (array_key_exists('bi_lastbill', $_POST) && isset($_POST['bi_lastbill'])) ? $_POST['bi_lastbill'] : "";
        $bi_nextbill = (array_key_exists('bi_nextbill', $_POST) && isset($_POST['bi_nextbill'])) ? $_POST['bi_nextbill'] : "";
        $bi_nextinvoicedue = (array_key_exists('bi_nextinvoicedue', $_POST) && isset($_POST['bi_nextinvoicedue'])) ? $_POST['bi_nextinvoicedue'] : "";
        $bi_billdue = (array_key_exists('bi_billdue', $_POST) && isset($_POST['bi_billdue'])) ? $_POST['bi_billdue'] : "";
        $bi_postalinvoice = (array_key_exists('bi_postalinvoice', $_POST) && isset($_POST['bi_postalinvoice'])) ? $_POST['bi_postalinvoice'] : "";
        $bi_faxinvoice = (array_key_exists('bi_faxinvoice', $_POST) && isset($_POST['bi_faxinvoice'])) ? $_POST['bi_faxinvoice'] : "";
        $bi_emailinvoice = (array_key_exists('bi_emailinvoice', $_POST) && isset($_POST['bi_emailinvoice'])) ? $_POST['bi_emailinvoice'] : "";

        $bi_changeuserbillinfo = (array_key_exists('changeUserBillInfo', $_POST) && isset($_POST['changeUserBillInfo'])) ? $_POST['changeUserBillInfo'] : "0";

        $planName = (array_key_exists('planName', $_POST) && isset($_POST['planName'])) ? trim($_POST['planName']) : "";
        $oldplanName = (array_key_exists('oldplanName', $_POST) && isset($_POST['oldplanName'])) ? trim($_POST['oldplanName']) : "";

        // fix up errors with droping the Plan name
        if (empty($planName)) {
            $planName = $oldplanName;
        }

        if (!empty($username)) {
            
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
            } else {
               // update user information table
               $sql = sprintf("UPDATE %s SET firstname='%s', lastname='%s', email='%s', department='%s', company='%s', workphone='%s',
                                             homephone='%s', mobilephone='%s', address='%s', city='%s', state='%s', country='%s',
                                             zip='%s', notes='%s', changeuserinfo='%s', portalloginpassword='%s', enableportallogin='%s',
                                             updatedate='%s', updateby='%s'
                                WHERE username='%s'", $configValues['CONFIG_DB_TBL_DALOUSERINFO'], $dbSocket->escapeSimple($firstname),
                                                      $dbSocket->escapeSimple($lastname), $dbSocket->escapeSimple($email),
                                                      $dbSocket->escapeSimple($department), $dbSocket->escapeSimple($company),
                                                      $dbSocket->escapeSimple($workphone), $dbSocket->escapeSimple($homephone),
                                                      $dbSocket->escapeSimple($mobilephone), $dbSocket->escapeSimple($address),
                                                      $dbSocket->escapeSimple($city), $dbSocket->escapeSimple($state),
                                                      $dbSocket->escapeSimple($country), $dbSocket->escapeSimple($zip),
                                                      $dbSocket->escapeSimple($notes), $dbSocket->escapeSimple($ui_changeuserinfo),
                                                      $dbSocket->escapeSimple($ui_PortalLoginPassword),
                                                      $dbSocket->escapeSimple($ui_enableUserPortalLogin),
                                                      $dbSocket->escapeSimple($currDate), $dbSocket->escapeSimple($currBy),
                                                      $dbSocket->escapeSimple($username));
            }

            // execute the insert/update onto userinfo
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";
            
            
            /* perform user billing info table instructions */
            $sql = sprintf("SELECT COUNT(DISTINCT(username)) FROM %s WHERE username='%s'",
                           $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'], $dbSocket->escapeSimple($username));
            $res = $dbSocket->query($sql);
            $userbillinfoExist = $res->fetchrow()[0];
            $logDebugSQL .= "$sql;\n";


            // if there were no records for this user present in the userbillinfo table
            if (!$userbillinfoExist) {
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
            } else {
                // update user information table
                $sql = sprintf("UPDATE %s SET `contactperson`='%s', `planname`='%s', `company`='%s', `email`='%s', `phone`='%s',
                                              `paymentmethod`='%s', `cash`='%s', `creditcardname`='%s', `creditcardnumber`='%s',
                                              `creditcardverification`='%s', `creditcardtype`='%s', `creditcardexp`='%s', `address`='%s',
                                              `city`='%s', `state`='%s', `country`='%s', `zip`='%s', `notes`='%s', `changeuserbillinfo`='%s',
                                              `lead`='%s', `coupon`='%s', `ordertaker`='%s', `billstatus`='%s', `nextinvoicedue`='%s',
                                              `billdue`='%s', `postalinvoice`='%s', `faxinvoice`='%s', `emailinvoice`='%s', `updatedate`='%s',
                                              `updateby`='%s'
                                        WHERE `username`='%s'", $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'], $dbSocket->escapeSimple($bi_contactperson),
                                                                $dbSocket->escapeSimple($planName), $dbSocket->escapeSimple($bi_company),
                                                                $dbSocket->escapeSimple($bi_email), $dbSocket->escapeSimple($bi_phone),
                                                                $dbSocket->escapeSimple($bi_paymentmethod), $dbSocket->escapeSimple($bi_cash),
                                                                $dbSocket->escapeSimple($bi_creditcardname), $dbSocket->escapeSimple($bi_creditcardnumber),
                                                                $dbSocket->escapeSimple($bi_creditcardverification), $dbSocket->escapeSimple($bi_creditcardtype),
                                                                $dbSocket->escapeSimple($bi_creditcardexp), $dbSocket->escapeSimple($bi_address),
                                                                $dbSocket->escapeSimple($bi_city), $dbSocket->escapeSimple($bi_state),
                                                                $dbSocket->escapeSimple($bi_country), $dbSocket->escapeSimple($bi_zip),
                                                                $dbSocket->escapeSimple($bi_notes), $dbSocket->escapeSimple($bi_changeuserbillinfo),
                                                                $dbSocket->escapeSimple($bi_lead), $dbSocket->escapeSimple($bi_coupon),
                                                                $dbSocket->escapeSimple($bi_ordertaker), $dbSocket->escapeSimple($bi_billstatus),
                                                                $dbSocket->escapeSimple($bi_nextinvoicedue), $dbSocket->escapeSimple($bi_billdue),
                                                                $dbSocket->escapeSimple($bi_postalinvoice), $dbSocket->escapeSimple($bi_faxinvoice),
                                                                $dbSocket->escapeSimple($bi_emailinvoice), $currDate, $currBy,
                                                                $dbSocket->escapeSimple($username));
            }

            // execute the insert/update onto userbillinfo
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";


            // add new user group mappings (implicitly) using priority 0
            $user_groups = get_user_group_mappings($dbSocket, $username);
            if (count($newgroups) > 0) {
                foreach ($newgroups as $groupname) {
                    $groupname = trim($groupname);
                    
                    if (empty($groupname) || in_array($groupname, $user_groups)) {
                        continue;
                    }
                    
                    insert_single_user_group_mapping($dbSocket, $username, $groupname);
                }
            }

            // $groups and $groups_priority are used to update existing user group mappings
            if (count($groups) == count($groups_priority)) {
                // we need that these two arrays contain the exact same number of elements
                
                $updated_user_group_mappings = array();
                
                for ($i = 0; $i < count($groups); $i++) {
                    $groupname = trim($groups[$i]);
                    
                    // if the groupname is empty or it is not contained
                    // in the user group mappings we skip
                    if (empty($groupname) || !in_array($groupname, $user_groups)) {
                        continue;
                    }
                    
                    $priority = (empty($groups_priority[$i])) ? 0 : intval($groups_priority[$i]);
                    
                    // if the groupname appears two times, we "reset" the priority to the default values
                    $updated_user_group_mappings[$groupname] = (array_key_exists($groupname, $updated_user_group_mappings))
                                                             ? 0 : $priority;
                }
                
                // we now can proceed and update existing user group mappings
                foreach ($updated_user_group_mappings as $groupname => $priority) {
                    update_user_group_mapping_priority($dbSocket, $username, $groupname, $priority);
                }
            }
                        
            
            

            addPlanProfile($dbSocket, $username, $planName, $oldplanName);


            // dealing with attributes
            include("library/attributes.php");

            $skipList = array( "username", "submit", "oldgroups", "groups", "planName", "oldplanName", "groups_priority",
                               "copycontact", "firstname", "lastname", "email", "department", "company", "workphone",
                               "homephone", "mobilephone", "address", "city", "state", "country", "zip", "notes",
                               "changeUserInfo", "bi_contactperson", "bi_company", "bi_email", "bi_phone", "bi_address",
                               "bi_city", "bi_state", "bi_country", "bi_zip", "bi_paymentmethod", "bi_cash", "bi_creditcardname",
                               "bi_creditcardnumber", "bi_creditcardverification", "bi_creditcardtype", "bi_creditcardexp",
                               "bi_notes", "changeUserBillInfo", "bi_lead", "bi_coupon", "bi_ordertaker", "bi_billstatus",
                               "bi_lastbill", "bi_nextbill", "bi_nextinvoicedue", "bi_billdue", "bi_postalinvoice", "bi_faxinvoice",
                               "bi_emailinvoice", "bi_planname", "newgroups", "portalLoginPassword", "enableUserPortalLogin"
                             );

            
            handleAttributes($dbSocket, $username, $skipList, false);

            $successMsg = sprintf("Successfully updated user <strong>%s</strong>", $username_enc);
            $logAction .= sprintf("Successfully updated user %s on page: ", $username);
        
        } else { // if username != ""
            $failureMsg = "no user was entered, please specify a username to edit";
            $logAction .= "Failed updating attributes for user [$username] on page: ";
        }
    } else {
        $username = (array_key_exists('username', $_REQUEST) && isset($_REQUEST['username']))
                  ? trim(str_replace("%", "", $_REQUEST['username'])) : "";
        $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    }

    //feed the sidebar variables
    $edit_username = $username_enc;

    if (empty($username)) {
        $failureMsg = "no user was entered, please specify a username to edit";
    } else {

        /* an sql query to retrieve the password for the username to use in the quick link for the user test connectivity */
        $sql = sprintf("SELECT value FROM %s WHERE username='%s' AND attribute LIKE '%%-Password' ORDER BY id DESC",
                       $configValues['CONFIG_DB_TBL_RADCHECK'], $dbSocket->escapeSimple($username));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        $user_password = $res->fetchRow()[0];

        /* fill-in all the user info details */
        $sql = sprintf("SELECT firstname, lastname, email, department, company, workphone, homephone, mobilephone, address, city,
                               state, country, zip, notes, changeuserinfo, portalloginpassword, enableportallogin, creationdate,
                               creationby, updatedate, updateby
                          FROM %s WHERE username='%s'", $configValues['CONFIG_DB_TBL_DALOUSERINFO'],
                                                        $dbSocket->escapeSimple($username));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        list( 
              $ui_firstname, $ui_lastname, $ui_email, $ui_department, $ui_company, $ui_workphone, $ui_homephone,
              $ui_mobilephone, $ui_address, $ui_city, $ui_state, $ui_country, $ui_zip, $ui_notes, $ui_changeuserinfo,
              $ui_PortalLoginPassword, $ui_enableUserPortalLogin, $ui_creationdate, $ui_creationby, $ui_updatedate,
              $ui_updateby
            ) = $res->fetchRow();

        /* fill-in all the user bill info details */
        $sql = sprintf("SELECT planName, contactperson, company, email, phone, address, city, state, country, zip, paymentmethod,
                               cash, creditcardname, creditcardnumber, creditcardverification, creditcardtype, creditcardexp,
                               notes, changeuserbillinfo, `lead`, coupon, ordertaker, billstatus, lastbill, nextbill,
                               nextinvoicedue, billdue, postalinvoice, faxinvoice, emailinvoice, creationdate, creationby,
                               updatedate, updateby
                          FROM %s WHERE username='%s'", $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                                        $dbSocket->escapeSimple($username));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        list(
                $bi_planname, $bi_contactperson, $bi_company, $bi_email, $bi_phone, $bi_address, $bi_city, $bi_state,
                $bi_country, $bi_zip, $bi_paymentmethod, $bi_cash, $bi_creditcardname, $bi_creditcardnumber,
                $bi_creditcardverification, $bi_creditcardtype, $bi_creditcardexp, $bi_notes, $bi_changeuserbillinfo,
                $bi_lead, $bi_coupon, $bi_ordertaker, $bi_billstatus, $bi_lastbill, $bi_nextbill, $bi_nextinvoicedue,
                $bi_billdue, $bi_postalinvoice, $bi_faxinvoice, $bi_emailinvoice, $bi_creationdate, $bi_creationby,
                $bi_updatedate, $bi_updateby
            ) = $res->fetchRow();
    }

    include('library/closedb.php');

    $hiddenPassword = (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) == "yes")
                    ? 'password' : 'text';
    
    include_once("lang/main.php");
    
    include("library/layout.php");

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
    
    // inline extra javascript
    $inline_extra_js = sprintf("var strUsername = 'username=%s';\n", $username_enc);
    
    $inline_extra_js .= '
function disableUser() {
    if (confirm("You are about to disable this user account\nDo you want to continue?"))  {
        ajaxGeneric("include/management/userOperations.php", "userDisable=true", "returnMessages", strUsername);
        return true;
    }
}

function enableUser() {
    if (confirm("You are about to enable this user account\nDo you want to continue?"))  {
        ajaxGeneric("include/management/userOperations.php", "userEnable=true", "returnMessages", strUsername);
        return true;
    }
}

window.onload = function(){
    ajaxGeneric("include/management/userOperations.php", "checkDisabled=true", "returnMessages", strUsername);
};' . "\n";
    
    $title = t('Intro','mngedit.php');
    $help = t('helpPage','mngedit');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js, "", $inline_extra_js);

    if (!empty($username_enc)) {
        $title .= " :: $username_enc";
    }

    include("menu-mng-users.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    // ajax return div
    echo '<div id="returnMessages"></div>';

    include_once('include/management/actionMessages.php');
    include('include/management/populate_selectbox.php');
    
    if (!empty($username)) {
        $input_descriptors1 = array();
        
        $input_descriptors1[] = array(
                                        "type" => "hidden",
                                        "value" => $username_enc,
                                        "name" => "username"
                                     );
        
        $input_descriptors1[] = array(
                                        "name" => "username_presentation",
                                        "caption" => t('all','Username'),
                                        "type" => "text",
                                        "value" => ((isset($username)) ? $username : ""),
                                        "disabled" => true,
                                        "tooltipText" => t('Tooltip','usernameTooltip')
                                      );
                                    
        $input_descriptors1[] = array(
                                        "id" => "password",
                                        "name" => "password",
                                        "caption" => t('all','Password'),
                                        "type" => $hiddenPassword,
                                        "value" => ((isset($user_password)) ? $user_password : ""),
                                        "disabled" => true,
                                        "tooltipText" => t('Tooltip','passwordTooltip')
                                     );
        
        $input_descriptors1[] = array( 'name' => 'oldplanName', 'type' => 'hidden',
                                                 'value' => ((isset($bi_planname)) ? $bi_planname : "") );
                        
        $options = get_active_plans();
        array_unshift($options, '');
        $input_descriptors1[] = array(
                                         'type' => 'select',
                                         'name' => 'planName',
                                         'caption' => t('all','PlanName'),
                                         'tooltipText' => t('Tooltip','planNameTooltip'),
                                         'options' => $options,
                                         'selected_value' => ((isset($bi_planname)) ? $bi_planname : "")
                                     );

        $buttons = array();
        $buttons[] = array(
                            'type' => 'button',
                            'value' => 'Enable User',
                            'onclick' => 'javascript:enableUser()',
                            'name' => 'enableUser-button'
                          );
                          
        $buttons[] = array(
                            'type' => 'button',
                            'value' => 'Disable User',
                            'onclick' => 'javascript:disableUser()',
                            'name' => 'disableUser-button'
                          );

        // draw navbar
        $navbuttons = array(
                                'AccountInfo-tab' => t('title','AccountInfo'),
                                'RADIUSCheck-tab' => t('title','RADIUSCheck'),
                                'UserInfo-tab' => t('title','UserInfo'),
                                'BillingInfo-tab' => t('title','BillingInfo'),
                                'Attributes-tab' => t('title','Attributes'),
                                'Groups-tab' => t('title','Groups'),
                                'OtherInfo-tab' => "Other Info"
                           );
        print_tab_navbuttons($navbuttons);

?>

<form method="POST">
    <div id="AccountInfo-tab" class="tabcontent active" title="<?= t('title','AccountInfo') ?>" style="display: block">
        <fieldset>
            <h302><?= t('title','AccountInfo'); ?></h302>
            <ul>
<?php
                    
                foreach ($input_descriptors1 as $descr) {
                    print_form_component($descr);
                }                    
?>
            </ul>
        </fieldset>
        
        <fieldset>
            <h302>Actions</h302>
<?php
            include('include/management/buttons.php');
                
            foreach ($buttons as $button_desc) {
                print_input_field($button_desc);
            }
?>
            
        </fieldset>

    </div><!-- #AccountInfo-tab -->

    <div id="RADIUSCheck-tab" class="tabcontent" title="<?= t('title','RADIUSCheck') ?>">
        <fieldset>
            <h302> <?= t('title','RADIUSCheck'); ?> </h302>
            <ul>
<?php

    $hashing_algorithm_notice = '<small style="font-size: 10px; color: black">'
                              . 'Notice that for supported password-like attributes, you can just specify a plaintext value. '
                              . 'The system will take care of correctly hashing it.'
                              . '</small>';

    include('library/opendb.php');

    include_once('include/management/pages_common.php');

    $sql = sprintf("SELECT rc.attribute, rc.op, rc.value, dd.type, dd.recommendedTooltip, rc.id
                      FROM %s AS rc LEFT JOIN %s AS dd ON rc.attribute = dd.attribute AND dd.value IS NULL
                     WHERE rc.username='%s'", $configValues['CONFIG_DB_TBL_RADCHECK'],
                                              $configValues['CONFIG_DB_TBL_DALODICTIONARY'],
                                              $dbSocket->escapeSimple($username));
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";

    if ($res->numRows() == 0) {
        echo '<div style="text-align: center">'
           . t('messages','noCheckAttributesForUser')
           . '</div>';
    } else {
        
        echo '<ul>';
        
        $editCounter = 0;
        while ($row = $res->fetchRow()) {
            
            foreach ($row as $i => $v) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }

            $id__attribute = sprintf('%s__%s', $row[5], $row[0]);
            $name = sprintf('editValues%s[]', $editCounter);
            $type = (preg_match("/-Password$/", $row[0])) ? $hiddenPassword : "text";

            echo '<li>';
            printf('<a class="tablenovisit" href="mng-del.php?username=%s&attribute=%s&tablename=radcheck">',
                   urlencode($username_enc), urlencode($id__attribute));
            echo '<img src="images/icons/delete.png" border="0" alt="Remove"></a>';
            
            printf('<label for="attribute" class="attributes">%s</label>', $row[0]);

            printf('<input type="hidden" name="%s" value="%s">', $name, $id__attribute);            
            printf('<input type="%s" value="%s" name="%s">', $type, $row[2], $name);
            
            printf('<select name="%s" class="form">', $name);
            printf('<option value="%s">%s</option>', $row[1], $row[1]);
            drawOptions();
            echo '</select>';

            printf('<input type="hidden" name="%s" value="radcheck">', $name);


            if (!empty($row[3]) || !empty($row[4])) {
                $divId = sprintf("%s-Tooltip-%d-check", $row[0], $editCounter);
                $onclick = sprintf("toggleShowDiv('%s')", $divId);
                printf('<img src="images/icons/comment.png" alt="Tip" border="0" onClick="%s">', $onclick);
                printf('<div id="%s" style="display:none;visibility:visible" class="ToolTip2">', $divId);
                
                if (!empty($row[3])) {
                    echo '<br>';
                    printf('<i><b>Type:</b> %s</i>', $row[3]);
                }
                
                if (!empty($row[4])) {
                    echo '<br>';
                    printf('<i><b>Tooltip Description:</b> %s</i>', $row[4]);
                }
                echo '</div>';
            }
            
            echo '</li>';
            
            // we increment the counter for the html elements of the edit attributes
            $editCounter++;
        }
        
        echo '</ul>';
    }
    
    echo $hashing_algorithm_notice;

?>
            <br/><br/>
            <hr><br/>

            <br/>
            <input type='submit' name='submit' value='<?= t('buttons','apply')?>' class='button' />
            <br/>

            </ul>

        </fieldset>
    </div>

    <div id="RADIUSReply-tab" class="tabcontent" title='<?= t('title','RADIUSReply')?>' >

        <fieldset>

            <h302> <?= t('title','RADIUSReply'); ?> </h302>
            <br/>

            <ul>

<?php

    $sql = sprintf("SELECT rc.attribute, rc.op, rc.value, dd.type, dd.recommendedTooltip, rc.id
                      FROM %s AS rc LEFT JOIN %s AS dd ON rc.attribute = dd.attribute AND dd.value IS NULL
                     WHERE rc.username='%s'", $configValues['CONFIG_DB_TBL_RADREPLY'],
                                              $configValues['CONFIG_DB_TBL_DALODICTIONARY'],
                                              $dbSocket->escapeSimple($username));
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";

    if ($res->numRows() == 0) {
        echo '<div style="text-align: center">'
           . t('messages','noReplyAttributesForUser')
           . '</div>';
    } else {
        
        echo '<ul>';
        $editCounter = 0;
        while ($row = $res->fetchRow()) {
            
            foreach ($row as $i => $v) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }

            $id__attribute = sprintf('%s__%s', $row[5], $row[0]);
            $name = sprintf('editValues%s[]', $editCounter);
            $type = (preg_match("/-Password$/", $row[0])) ? $hiddenPassword : "text";
    
            echo '<li>';
            printf('<a class="tablenovisit" href="mng-del.php?username=%s&attribute=%s&tablename=radreply">',
                   urlencode($username_enc), urlencode($id__attribute));
            echo '<img src="images/icons/delete.png" border="0" alt="Remove"></a>';

            printf('<label for="attribute" class="attributes">%s</label>', $row[0]);

            printf('<input type="hidden" name="%s" value="%s">', $name, $id__attribute);            
            printf('<input type="%s" value="%s" name="%s">', $type, $row[2], $name);
            
            printf('<select name="%s" class="form">', $name);
            printf('<option value="%s">%s</option>', $row[1], $row[1]);
            drawOptions();
            echo '</select>';

            printf('<input type="hidden" name="%s" value="radreply">', $name);

            if (!empty($row[3]) || !empty($row[4])) {
                $divId = sprintf("%s-Tooltip-%d-reply", $row[0], $editCounter);
                $onclick = sprintf("toggleShowDiv('%s')", $divId);
                printf('<img src="images/icons/comment.png" alt="Tip" border="0" onClick="%s">', $onclick);
                printf('<div id="%s" style="display:none;visibility:visible" class="ToolTip2">', $divId);
                
                if (!empty($row[3])) {
                    echo '<br>';
                    printf('<i><b>Type:</b> %s</i>', $row[3]);
                }
                
                if (!empty($row[4])) {
                    echo '<br>';
                    printf('<i><b>Tooltip Description:</b> %s</i>', $row[4]);
                }
                echo '</div>';
            }
            
            echo '</li>';
            
            // we increment the counter for the html elements of the edit attributes
            $editCounter++;
        }
        echo '</ul>';
    }

    echo $hashing_algorithm_notice;

    include('library/closedb.php');

?>
            <br/><br/>
            <hr><br/>

            <br/>
            <input type='submit' name='submit' value='<?= t('buttons','apply')?>' class='button' />
            <br/>

            </ul>

        </fieldset>
    </div>

    <div id="UserInfo-tab" class="tabcontent">
<?php
        $customApplyButton = "<input type='submit' name='submit' value=".t('buttons','apply')." class='button' />";
        include_once('include/management/userinfo.php');
?>
     </div>

    <div id="BillingInfo-tab" class="tabcontent">
<?php
        $customApplyButton = "<input type='submit' name='submit' value=".t('buttons','apply')." class='button' />";
        include_once('include/management/userbillinfo.php');
?>
    </div>

    <div id="Attributes-tab" class="tabcontent">
<?php
        include_once('include/management/attributes.php');
        echo $hashing_algorithm_notice;
?>
    </div>

    <div id="Groups-tab" class="tabcontent">
<?php
        include('library/opendb.php');
        include_once('include/management/groups.php');
        
        $selected_options = get_user_group_mappings($dbSocket, $username);
        
        include_once('include/management/populate_selectbox.php');
        $options = get_groups();
        array_unshift($options, '');
        
        include('library/closedb.php');
        
        $input_descriptors2 = array();
        
        $input_descriptors2[] = array(
                                        "type" =>"select",
                                        "name" => "newgroups[]",
                                        "id" => "groups",
                                        "caption" => t('all','Group'),
                                        "options" => $options,
                                        "multiple" => true,
                                        "size" => 5,
                                        "selected_value" => $selected_options,
                                        "tooltipText" => t('Tooltip','groupTooltip')
                                     );
        $input_descriptors2[] = array(
                                        'type' => 'submit',
                                        'name' => 'submit',
                                        'value' => t('buttons','apply')
                                     );
?>

        <br/>
        <h301> Assign New Groups </h301>
        <br/>
        
        <ul>

<?php
                foreach ($input_descriptors2 as $input_descriptor) {
                    print_form_component($input_descriptor);
                }
?>

        </ul>

        </fieldset>
        <br/>

     </div>

</form>

    <div id="OtherInfo-tab" class="tabcontent" title="Other Info">
<?php
        include_once('include/management/userReports.php');
        userPlanInformation($username, 1);
        userSubscriptionAnalysis($username, 1);                 // userSubscriptionAnalysis with argument set to 1 for drawing the table
        userConnectionStatus($username, 1);                     // userConnectionStatus (same as above)
?>
    </div><!-- #OtherInfo-tab -->

<?php
    }
    
    if (array_key_exists('PREV_LIST_PAGE', $_SESSION) && !empty(trim($_SESSION['PREV_LIST_PAGE']))) {
        echo '<div style="float: right; text-align: right; margin: 0; font-size: small">';
        printf('<a href="%s" title="Back to Previous Page">Back to Previous Page</a>', trim($_SESSION['PREV_LIST_PAGE']));
        echo '</div>';
        
        unset($_SESSION['PREV_LIST_PAGE']);
    }

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
