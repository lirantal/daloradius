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
    include_once("include/management/functions.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";


    include('../common/includes/db_open.php');


    function addPlanProfile($dbSocket, $username, $planName, $oldplanName) {

        global $logDebugSQL;
        global $configValues;

        $sql = sprintf("DELETE FROM %s WHERE UserName='%s'",
                       $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        // search to see if the plan is associated with any profiles
        $sql = sprintf("SELECT profile_name FROM %s WHERE plan_name='%s'",
                       $configValues['CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES'], $dbSocket->escapeSimple($planName));

        // $res is an array of all profiles associated with this plan
        $cols = $dbSocket->getCol($sql);

        // if the profile list for this plan isn't empty, we associate it with the user
        if (count($cols) > 0) {

            // if profiles are associated with this plan, loop through each and add a usergroup entry for each
            foreach($cols as $profile_name) {
                $sql = sprintf("INSERT INTO %s (username, groupname, priority) VALUES ('%s','%s','0')",
                               $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                               $dbSocket->escapeSimple($username),
                               $dbSocket->escapeSimple($profile_name));
                $res = $dbSocket->query($sql);
            }
        }
    }

    function addUserProfiles($dbSocket, $username, $planName, $oldplanName, $groups, $groups_priority, $newgroups) {

        global $logDebugSQL;
        global $configValues;

        // update usergroup mapping (existing)
        if (is_array($groups) && count($groups) > 0) {

            $sql = sprintf("DELETE FROM %s WHERE username='%s'",
                           $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username));
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";


            $insert_group_format = "INSERT INTO %s (username, groupname, priority) VALUES ('%s', '%s', %s)";

            foreach ($groups as $i => $group) {
                $group = trim($group);

                if (empty($group)) {
                    continue;
                }

                $priority = (!empty($groups_priority[$i])) ? $groups_priority[$i] : "0";

                $sql = sprintf($insert_group_format,
                               $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                               $dbSocket->escapeSimple($username),
                               $dbSocket->escapeSimple($group),
                               $dbSocket->escapeSimple($priority));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
            }

        }

        // insert usergroup mapping (new groups)
        if (is_array($newgroups) && count($newgroups) > 0) {
            foreach ($newgroups as $newgroup) {
                $newgroup = trim($newgroup);

                if (empty($newgroup)) {
                    continue;
                }

                $sql = sprintf($insert_group_format,
                               $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                               $dbSocket->escapeSimple($username),
                               $dbSocket->escapeSimple($group), 0);
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
            }
        }

    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = (array_key_exists('username', $_POST) && !empty(str_replace("%", "", trim($_POST['username']))))
                  ? str_replace("%", "", trim($_POST['username'])) : "";
    } else {
        $username = (array_key_exists('username', $_REQUEST) && !empty(str_replace("%", "", trim($_REQUEST['username']))))
                  ? str_replace("%", "", trim($_REQUEST['username'])) : "";
    }

    // check if this user exists
    $exists = user_exists($dbSocket, $username);

    if (!$exists) {
        // we reset the username if it does not exist
        $username = "";
    }

    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

    //feed the sidebar variables
    $edit_username = $username_enc;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            // required later
            $currDate = date('Y-m-d H:i:s');
            $currBy = $operator;

            $planName = (array_key_exists('planName', $_POST) && isset($_POST['planName'])) ? trim($_POST['planName']) : "";
            $oldplanName = (array_key_exists('oldplanName', $_POST) && isset($_POST['oldplanName'])) ? trim($_POST['oldplanName']) : "";
            $profiles = (array_key_exists('profiles', $_POST) && isset($_POST['profiles'])) ? $_POST['profiles'] : array();
            isset($_POST['reassignplanprofiles']) ? $reassignplanprofiles = $_POST['reassignplanprofiles'] : $reassignplanprofiles = "";

            isset($_POST['password']) ? $password = $_POST['password'] : $password = "";
            isset($_POST['passwordType']) ? $passwordtype = $_POST['passwordType'] : $passwordtype = "";

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
            $ui_enableUserPortalLogin = (!empty($ui_PortalLoginPassword) &&  isset($_POST['enableUserPortalLogin']) && $_POST['enableUserPortalLogin'] === '1')
                                      ? '1' : '0';

            $groups = (isset($_POST['groups']) && is_array($_POST['groups'])) ? $_POST['groups'] : array();

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

            // this is forced to 0 (disabled) if user portal login password is empty
            $bi_changeuserbillinfo = (!empty($ui_PortalLoginPassword) && isset($_POST['bi_changeuserbillinfo']) && $_POST['bi_changeuserbillinfo'] === '1')
                                   ? '1' : '0';

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

                if ($reassignplanprofiles == 1) {
                    // if the user chose to re-assign profiles from the change of plan then we proceed with removing
                    // all profiles associated with the user and re-assigning them based on the plan's profiles associations
                    addPlanProfile($dbSocket, $username, $planName, $oldplanName);
                } else {
                    // otherwise, we remove all profiles and assign profiles as configured in the profiles tab by the user
                    if (delete_user_group_mappings($dbSocket, $username)) {
                        if (count($groups) > 0) {
                            foreach ($groups as $group) {
                                list($groupname, $priority) = $group;
                                insert_single_user_group_mapping($dbSocket, $username, $groupname, $priority);
                            }
                        }
                    }
                }

            }

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }

    }

    if (empty($username)) {
        $failureMsg = "You have specified an empty or invalid username";
        $inline_extra_js = "";
    } else {

        /* an sql query to retrieve the password for the username to use in the quick link for the user test connectivity */
        $sql = sprintf("SELECT value FROM %s WHERE username='%s' AND attribute LIKE '%%-Password' ORDER BY id DESC LIMIT 1",
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
        $sql = sprintf("SELECT id, planName, contactperson, company, email, phone, address, city, state, country, zip, paymentmethod,
                               cash, creditcardname, creditcardnumber, creditcardverification, creditcardtype, creditcardexp,
                               notes, changeuserbillinfo, `lead`, coupon, ordertaker, billstatus, lastbill, nextbill,
                               nextinvoicedue, billdue, postalinvoice, faxinvoice, emailinvoice, creationdate, creationby,
                               updatedate, updateby
                          FROM %s WHERE username='%s'", $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                                        $dbSocket->escapeSimple($username));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        list(
                $user_id, $bi_planname, $bi_contactperson, $bi_company, $bi_email, $bi_phone, $bi_address, $bi_city,
                $bi_state, $bi_country, $bi_zip, $bi_paymentmethod, $bi_cash, $bi_creditcardname, $bi_creditcardnumber,
                $bi_creditcardverification, $bi_creditcardtype, $bi_creditcardexp, $bi_notes, $bi_changeuserbillinfo,
                $bi_lead, $bi_coupon, $bi_ordertaker, $bi_billstatus, $bi_lastbill, $bi_nextbill, $bi_nextinvoicedue,
                $bi_billdue, $bi_postalinvoice, $bi_faxinvoice, $bi_emailinvoice, $bi_creationdate, $bi_creationby,
                $bi_updatedate, $bi_updateby
            ) = $res->fetchRow();


        // inline extra javascript
        $inline_extra_js = sprintf("var strUsername = 'username=%s';\n", $username_enc);

        $inline_extra_js .= '
function disableUser() {
    if (confirm("You are about to disable this user account\nDo you want to continue?"))  {
        ajaxGeneric("library/ajax/user_actions.php", "userDisable=true", "returnMessages", strUsername);
        return true;
    }
}

function enableUser() {
    if (confirm("You are about to enable this user account\nDo you want to continue?"))  {
        ajaxGeneric("library/ajax/user_actions.php", "userEnable=true", "returnMessages", strUsername);
        return true;
    }
}

function refillSessionTime() {
    if (confirm("You are about to refill session time for this user account\nDo you want to continue?\n\nSuch action will also bill the user if set so in the plant the user is associated with!"))  {
        ajaxGeneric("library/ajax/user_actions.php", "refillSessionTime=true", "returnMessages", strUsername);
        return true;
    }
}


function refillSessionTraffic() {
    if (confirm("You are about to refill session traffic for this user account\nDo you want to continue?\n\nSuch action will also bill the user if set so in the plant the user is associated with!"))  {
        ajaxGeneric("library/ajax/user_actions.php", "refillSessionTraffic=true", "returnMessages", strUsername);
        return true;
    }
}
' . "\n";
    }

    include('../common/includes/db_close.php');

    $hiddenPassword = (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) == "yes")
                    ? 'password' : 'text';

    // print HTML prologue
    $extra_css = array();

    $extra_js = array(
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js",
        "static/js/productive_funcs.js",
        "static/js/dynamic_attributes.js",
        "static/js/pages_common.js",
    );



    $title = t('Intro','billposedit.php');
    $help = t('helpPage','billposedit');

    print_html_prologue($title, $langCode, $extra_css, $extra_js, "", $inline_extra_js);

    if (!empty($username_enc)) {
        $title .= " :: $username_enc";
    }

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    $inline_extra_js = "";
    if (!empty($username)) {

        // ajax return div
        echo '<div id="returnMessages"></div>';
        include_once('include/management/populate_selectbox.php');

        // set navbar stuff
        $navkeys = array( 'AccountInfo', 'UserInfo', 'BillingInfo', 'Profiles', 'Invoices', array( 'OtherInfo', "Other Info" ) );

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
                                    "type" => "hidden",
                                    "value" => $username_enc,
                                    "name" => "username"
                                 );

        $input_descriptors0[] = array(
                                        "name" => "username_presentation",
                                        "caption" => t('all','Username'),
                                        "type" => "text",
                                        "value" => ((isset($username)) ? $username : ""),
                                        "disabled" => true,
                                        "tooltipText" => t('Tooltip','usernameTooltip')
                                      );

        $input_descriptors0[] = array(
                                        "id" => "password",
                                        "name" => "password",
                                        "caption" => t('all','Password'),
                                        "type" => $hiddenPassword,
                                        "value" => ((isset($user_password)) ? $user_password : ""),
                                        "disabled" => true,
                                        "tooltipText" => t('Tooltip','passwordTooltip')
                                     );

        $input_descriptors0[] = array( 'name' => 'oldplanName', 'type' => 'hidden',
                                                 'value' => ((isset($bi_planname)) ? $bi_planname : "") );

        $options = get_active_plans();
        array_unshift($options, '');
        $input_descriptors0[] = array(
                                         'type' => 'select',
                                         'name' => 'planName',
                                         'caption' => t('all','PlanName'),
                                         'tooltipText' => t('Tooltip','planNameTooltip'),
                                         'options' => $options,
                                         'selected_value' => ((isset($bi_planname)) ? $bi_planname : "")
                                     );

        $input_descriptors0[] = array(
                                        'type' => 'checkbox',
                                        'name' => 'reassignplanprofiles',
                                        'caption' => t('button','ReAssignPlanProfiles'),
                                        'value' => ((isset($reassignplanprofiles)) ? $reassignplanprofiles : ""),
                                        'tooltipText' => t('Tooltip','reassignplanprofiles')
                                     );

        foreach ($input_descriptors0 as $descr) {
            print_form_component($descr);
        }

        // buttons
        $button_descriptors0 = array();

        $button_descriptors0[] = array(
                                        'type' => 'button',
                                        'value' => 'Refill Session Time',
                                        'onclick' => 'javascript:refillSessionTime()',
                                        'name' => 'refillSessionTime-button'
                                      );

        $button_descriptors0[] = array(
                                        'type' => 'button',
                                        'value' => 'Refill Session Traffic',
                                        'onclick' => 'javascript:refillSessionTraffic()',
                                        'name' => 'refillSessionTraffic-button'
                                      );

        $button_descriptors0[] = array(
                                        'type' => 'button',
                                        'value' => 'Enable User',
                                        'onclick' => 'javascript:enableUser()',
                                        'name' => 'enableUser-button'
                                      );

        $button_descriptors0[] = array(
                                        'type' => 'button',
                                        'value' => 'Disable User',
                                        'onclick' => 'javascript:disableUser()',
                                        'name' => 'disableUser-button'
                                      );

        // custom actions
        echo <<<EOF
    <div class="dropdown dropup">
        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            Actions
        </button>

        <ul class="dropdown-menu">
EOF;

        foreach ($button_descriptors0 as $desc) {
            printf('<li><a href="#" class="dropdown-item" name="%s" onclick="%s">%s</a></li>', $desc['name'], $desc['onclick'], $desc['value']);
        }


        echo <<<EOF
        </ul>
    </div>
EOF;

        close_fieldset();

        close_tab($navkeys, 0);

        // open 1-st tab
        open_tab($navkeys, 1);

        include_once('include/management/userinfo.php');

        close_tab($navkeys, 1);

        // open 2-nd tab
        open_tab($navkeys, 2);

        include_once('include/management/userbillinfo.php');

        close_tab($navkeys, 2);

        // open 3-rd tab
        open_tab($navkeys, 3);

        $groupTerminology = "Profile";
        $groupTerminologyPriority = "ProfilePriority";

        include('../common/includes/db_open.php');
        include_once('include/management/groups.php');
        include('../common/includes/db_close.php');

        close_tab($navkeys, 3);

        // open 4-th tab
        open_tab($navkeys, 4);

        if ($user_id) {
            include_once('include/management/userBilling.php');
            userInvoicesStatus($user_id, 1);
        }

        close_tab($navkeys, 4);

        // open 5-th tab
        open_tab($navkeys, 5);

        echo '<div class="accordion m-2" id="accordion-parent">';
        include_once('include/management/userReports.php');
        userPlanInformation($username, 1);
        userSubscriptionAnalysis($username, 1);                 // userSubscriptionAnalysis with argument set to 1 for drawing the table
        userConnectionStatus($username, 1);                     // userConnectionStatus (same as above)
        echo '</div>';

        close_tab($navkeys, 5);

        // close tab wrapper
        close_tab_wrapper();

        $input_descriptors2 = array();

        $input_descriptors2[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );

        $input_descriptors2[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                      );

        foreach ($input_descriptors2 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_form();

        $inline_extra_js = <<<EOF

window.onload = function() {
    setupAccordion();
    ajaxGeneric("library/ajax/user_actions.php", "checkDisabled=true", "returnMessages", strUsername);
};

EOF;
    }

    print_back_to_previous_page();

    include('include/config/logging.php');

    print_footer_and_html_epilogue($inline_extra_js);
?>
