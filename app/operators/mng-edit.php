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
    include_once('../common/includes/config_read.php');

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");
    include_once("include/management/functions.php");


    include('../common/includes/db_open.php');

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

    // from now on we can assume that $username is valid
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            // required later
            $currDate = date('Y-m-d H:i:s');
            $currBy = $operator;

            // TODO validate user input
            $groups = (isset($_POST['groups']) && is_array($_POST['groups'])) ? $_POST['groups'] : array();

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

            // this is forced to 0 (disabled) if user portal login password is empty
            $bi_changeuserbillinfo = (!empty($ui_PortalLoginPassword) && isset($_POST['bi_changeuserbillinfo']) && $_POST['bi_changeuserbillinfo'] === '1')
                                   ? '1' : '0';

            $planName = (array_key_exists('planName', $_POST) && isset($_POST['planName'])) ? trim($_POST['planName']) : "";
            $oldplanName = (array_key_exists('oldplanName', $_POST) && isset($_POST['oldplanName'])) ? trim($_POST['oldplanName']) : "";

            // fix up errors with droping the Plan name
            if (empty($planName)) {
                $planName = $oldplanName;
            }

            if (!empty($username)) {

                // dealing with attributes
                include("library/attributes.php");

                $skipList = array( "username", "submit", "groups", "planName", "oldplanName",
                                   "copycontact", "firstname", "lastname", "email", "department", "company", "workphone",
                                   "homephone", "mobilephone", "address", "city", "state", "country", "zip", "notes",
                                   "changeUserInfo", "bi_contactperson", "bi_company", "bi_email", "bi_phone", "bi_address",
                                   "bi_city", "bi_state", "bi_country", "bi_zip", "bi_paymentmethod", "bi_cash", "bi_creditcardname",
                                   "bi_creditcardnumber", "bi_creditcardverification", "bi_creditcardtype", "bi_creditcardexp",
                                   "bi_notes", "bi_changeuserbillinfo", "bi_lead", "bi_coupon", "bi_ordertaker", "bi_billstatus",
                                   "bi_lastbill", "bi_nextbill", "bi_nextinvoicedue", "bi_billdue", "bi_postalinvoice", "bi_faxinvoice",
                                   "bi_emailinvoice", "bi_planname", "portalLoginPassword", "enableUserPortalLogin",
                                   "csrf_token", "submit"
                                 );


                handleAttributes($dbSocket, $username, $skipList, false);

                // insert or update user info
                $userinfoExist = user_exists($dbSocket, $username, 'CONFIG_DB_TBL_DALOUSERINFO');

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
                               );

                if ($userinfoExist) {
                    $params["updatedate"] = $currDate;
                    $params["updateby"] = $currBy;
                    $addedUserInfo = (update_user_info($dbSocket, $username, $params)) ? "stored" : "nothing to store";
                } else {
                    $params["creationdate"] = $currDate;
                    $params["creationby"] = $currBy;
                    $addedUserInfo = (add_user_info($dbSocket, $username, $params)) ? "updated" : "nothing to update";
                }


                // insert or update billing info
                $billinfoExist = user_exists($dbSocket, $username, 'CONFIG_DB_TBL_DALOUSERBILLINFO');

                $params = array(
                                    "contactperson" => $bi_contactperson,
                                    "company" => $bi_company,
                                    "email" => $bi_email,
                                    "phone" => $bi_phone,
                                    "address" => $bi_address,
                                    "city" => $bi_city,
                                    "state" => $bi_state,
                                    "country" => $bi_country,
                                    "zip" => $bi_zip,
                                    "postalinvoice" => $bi_postalinvoice,
                                    "faxinvoice" => $bi_faxinvoice,
                                    "emailinvoice" => $bi_emailinvoice,

                                    "paymentmethod" => $bi_paymentmethod,
                                    "cash" => $bi_cash,
                                    "creditcardname" => $bi_creditcardname,
                                    "creditcardnumber" => $bi_creditcardnumber,
                                    "creditcardverification" => $bi_creditcardverification,
                                    "creditcardtype" => $bi_creditcardtype,
                                    "creditcardexp" => $bi_creditcardexp,

                                    "lead" => $bi_lead,
                                    "coupon" => $bi_coupon,
                                    "ordertaker" => $bi_ordertaker,

                                    "notes" => $bi_notes,
                                    "changeuserbillinfo" => $bi_changeuserbillinfo,

                                    //~ "billstatus" => $bi_billstatus,
                                    //~ "lastbill" => $bi_lastbill,
                                    //~ "nextbill" => $bi_nextbill,
                                    "billdue" => $bi_billdue,
                                    "nextinvoicedue" => $bi_nextinvoicedue,

                                    "creationdate" => $currDate,
                                    "creationby" => $currBy,
                               );

                if ($billinfoExist) {
                    $params["planName"] = $planName;
                    $params["updatedate"] = $currDate;
                    $params["updateby"] = $currBy;
                    $addedBillinfo = (update_user_billing_info($dbSocket, $username, $params)) ? "stored" : "nothing to store";
                } else {
                    $params["creationdate"] = $currDate;
                    $params["creationby"] = $currBy;
                    $addedBillinfo = (add_user_billing_info($dbSocket, $username, $params)) ? "updated" : "nothing to update";
                }

                // update group mappings
                if (delete_user_group_mappings($dbSocket, $username)) {
                    if (count($groups) > 0) {
                        foreach ($groups as $group) {
                            list($groupname, $priority) = $group;
                            insert_single_user_group_mapping($dbSocket, $username, $groupname, $priority);
                        }
                    }
                }

                addPlanProfile($dbSocket, $username, $planName, $oldplanName);

                $successMsg = sprintf("Successfully updated user <strong>%s</strong>", $username_enc);
                $logAction .= sprintf("Successfully updated user %s on page: ", $username);

            } else { // if username != ""
                $failureMsg = "You have specified an empty or invalid username";
                $logAction .= "empty or invalid username on page: ";
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
}' . "\n";
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
        "static/js/pages_common.js",
        "static/js/dynamic_attributes.js",
    );

    $title = t('Intro','mngedit.php');
    $help = t('helpPage','mngedit');

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

        // we have more than one form in this page so we can reuse many times the same csrf_token value
        $csrf_token = dalo_csrf_token();

        $submit_descriptor = array(
                                    "type" => "submit",
                                    "name" => "submit",
                                    "value" => t('buttons','apply')
                                  );

        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        "type" => "hidden",
                                        "value" => $csrf_token,
                                        "name" => "csrf_token"
                                     );

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

        // set navbar stuff
        $navkeys = array(
                          'AccountInfo', 'RADIUSCheck', 'RADIUSReply', 'UserInfo', 'BillingInfo',
                          'Groups', 'Attributes', array( 'OtherInfo', "Other Info" )
                        );

        // print navbar controls
        print_tab_header($navkeys);

        // open form
        open_form();

        // open tab wrapper
        open_tab_wrapper();

        // open first tab (shown)
        open_tab($navkeys, 0, true);

        // open a fieldset
        $fieldset0_descriptor = array(
                                        "title" => t('title','AccountInfo'),
                                     );

        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        // open a fieldset
        $fieldset0_descriptor = array(
                                        "title" => "Actions",
                                     );

        open_fieldset($fieldset0_descriptor);

        include('include/management/buttons.php');

        $button_descriptors1[] = array(
                                        'type' => 'button',
                                        'value' => 'Enable User',
                                        'onclick' => 'javascript:enableUser()',
                                        'name' => 'enableUser-button'
                                      );

        $button_descriptors1[] = array(
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

        foreach ($button_descriptors1 as $desc) {
            printf('<li><a href="#" class="dropdown-item" name="%s" onclick="%s">%s</a></li>', $desc['name'], $desc['onclick'], $desc['value']);
        }


        echo <<<EOF
        </ul>
    </div>
EOF;

        close_fieldset();

        close_tab($navkeys, 0);


        // open 1-st tab (not shown)
        open_tab($navkeys, 1);

        // open 1-st fieldset
        $fieldset1_descriptor = array(
                                        "title" => t('title','RADIUSCheck'),
                                     );
        open_fieldset($fieldset1_descriptor);

        $hashing_algorithm_notice = '<small class="mt-4 d-block">'
                                  . 'Notice that for supported password-like attributes, you can just specify a plaintext value. '
                                  . 'The system will take care of correctly hashing it.'
                                  . '</small>';

        include('../common/includes/db_open.php');

        include_once('include/management/pages_common.php');

        $sql = sprintf("SELECT rad.attribute, rad.op, rad.value, dd.type, dd.recommendedTooltip, rad.id
                          FROM %s AS rad LEFT JOIN %s AS dd ON rad.attribute = dd.attribute AND dd.value IS NULL
                         WHERE rad.username='%s' ORDER BY rad.id ASC", $configValues['CONFIG_DB_TBL_RADCHECK'],
                                                                       $configValues['CONFIG_DB_TBL_DALODICTIONARY'],
                                                                       $dbSocket->escapeSimple($username));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        echo '<div class="container">';

        if ($res->numRows() == 0) {
            printf('<div class="alert alert-info" role="alert">%s</div>', t('messages','noCheckAttributesForUser'));
        } else {
            while ($row = $res->fetchRow()) {

                foreach ($row as $i => $v) {
                    $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
                }

                $id = $row[5];
                $id__attribute = sprintf('%s__%s', $id, $row[0]);
                $name = sprintf('editValues%s[]', $id);
                $type = (preg_match("/-Password$/", $row[0])) ? $hiddenPassword : "text";
                $onclick = sprintf("document.getElementById('form-%d-radcheck').submit()", $id);

                $descriptor = array( 'onclick' => $onclick, 'attribute' => $row[0], 'select_name' => $name, 'selected_option' => $row[1],
                                     'id__attribute' => $id__attribute, 'type' => $type, 'value' => $row[2], 'name' => $name,
                                     'attr_type' => $row[3], 'attr_desc' => $row[4], 'table' => 'radcheck');

                print_edit_attribute($descriptor);
            }

            echo $hashing_algorithm_notice;
        }

        echo '</div><!-- .container -->';

        close_fieldset();

        close_tab($navkeys, 1);

        // open 2-nd tab (not shown)
        open_tab($navkeys, 2);

        // open 2-nd fieldset
        $fieldset1_descriptor = array(
                                        "title" => t('title','RADIUSReply'),
                                     );
        open_fieldset($fieldset1_descriptor);

        $sql = sprintf("SELECT rad.attribute, rad.op, rad.value, dd.type, dd.recommendedTooltip, rad.id
                          FROM %s AS rad LEFT JOIN %s AS dd ON rad.attribute = dd.attribute AND dd.value IS NULL
                         WHERE rad.username='%s' ORDER BY rad.id ASC", $configValues['CONFIG_DB_TBL_RADREPLY'],
                                                                       $configValues['CONFIG_DB_TBL_DALODICTIONARY'],
                                                                       $dbSocket->escapeSimple($username));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";


        echo '<div class="container">';
        if ($res->numRows() == 0) {
            printf('<div class="alert alert-info" role="alert">%s</div>', t('messages','noReplyAttributesForUser'));
        } else {
            while ($row = $res->fetchRow()) {

                foreach ($row as $i => $v) {
                    $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
                }

                $id = $row[5];
                $id__attribute = sprintf('%s__%s', $id, $row[0]);
                $name = sprintf('editValues%s[]', $id);
                $type = (preg_match("/-Password$/", $row[0])) ? $hiddenPassword : "text";
                $onclick = sprintf("document.getElementById('form-%d-radreply').submit()", $id);

                $descriptor = array( 'onclick' => $onclick, 'attribute' => $row[0], 'select_name' => $name, 'selected_option' => $row[1],
                                     'id__attribute' => $id__attribute, 'type' => $type, 'value' => $row[2], 'name' => $name,
                                     'attr_type' => $row[3], 'attr_desc' => $row[4], 'table' => 'radreply');

                print_edit_attribute($descriptor);
            }


            echo $hashing_algorithm_notice;
        }

        echo '</div><!-- .container -->';

        close_fieldset();

        close_tab($navkeys, 2);

        // open 3-rd tab (not shown)
        open_tab($navkeys, 3);
        include_once('include/management/userinfo.php');
        close_tab($navkeys, 3);


        // open 4-th tab (not shown)
        open_tab($navkeys, 4);
        include_once('include/management/userbillinfo.php');
        close_tab($navkeys, 4);

        // open 5-th tab (not shown)
        open_tab($navkeys, 5);

        include('../common/includes/db_open.php');
        include_once('include/management/groups.php');
        include('../common/includes/db_close.php');

        close_tab($navkeys, 5);

        open_tab($navkeys, 6);

        include_once('include/management/attributes.php');

        close_tab($navkeys, 6);

        open_tab($navkeys, 7);

        // accordion
        echo '<div class="accordion m-2" id="accordion-parent">';
        include_once('include/management/userReports.php');
        userPlanInformation($username, 1);
        userSubscriptionAnalysis($username, 1);                 // userSubscriptionAnalysis with argument set to 1 for drawing the table
        userConnectionStatus($username, 1);                     // userConnectionStatus (same as above)
        echo '</div>';

        close_tab($navkeys, 7);

        // close tab wrapper
        close_tab_wrapper();

        print_form_component($submit_descriptor);

        close_form();

        // print forms
        include('../common/includes/db_open.php');

        $tables = array(
                            'radcheck' => $configValues['CONFIG_DB_TBL_RADCHECK'],
                            'radreply' => $configValues['CONFIG_DB_TBL_RADREPLY']
                       );

        foreach ($tables as $table_value => $table) {

            $sql = sprintf("SELECT id, attribute, value FROM %s WHERE username='%s' ORDER BY id ASC",
                           $table, $dbSocket->escapeSimple($username));
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";

            if ($res->numRows() > 0) {

                while ($row = $res->fetchrow()) {
                    list($id, $attribute, $value) = $row;
                    $id = intval($id);

                    $formId = sprintf("form-%d-%s", $id, $table_value);
                    $id__attribute = sprintf("%d__%s", $id, htmlspecialchars($attribute, ENT_QUOTES, 'UTF-8'));

                    printf('<form id="%s" style="display: none" method="POST" action="mng-del.php">', $formId);
                    printf('<input type="hidden" name="username" value="%s">', $username_enc);
                    printf('<input type="hidden" name="attribute" value="%s">', $id__attribute);
                    printf('<input type="hidden" name="csrf_token" value="%s">', $csrf_token);
                    printf('<input type="hidden" name="tablename" value="%s">', $table_value);
                    echo '</form>';
                }
            }
        }

        include('../common/includes/db_close.php');

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
