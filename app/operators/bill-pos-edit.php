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


    require_once('library/catalog_reads_pdo.php');
    $catalog_pdo = null;
    require_once('../common/includes/pdo_connection.php');
    require_once('library/pos_update.php');


    $username_input = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['username'] ?? '') : ($_GET['username'] ?? '');
    $username = is_string($username_input) ? trim($username_input) : '';

    try {
        $source = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
        dalo_catalog_read_inputs($source, array('username'));
        $catalog_pdo = dalo_catalog_read_open($configValues);
        $table = dalo_read_table($catalog_pdo, $configValues, 'CONFIG_DB_TBL_RADCHECK');
        $exists = (int)dalo_catalog_read_rows($catalog_pdo, "SELECT COUNT(DISTINCT(username)) FROM $table WHERE username=:username", array(':username'=>$username))[0][0] > 0;
        if (!$exists) { $username = ''; }
        $table = dalo_read_table($catalog_pdo, $configValues, 'CONFIG_DB_TBL_DALOUSERINFO');
        $portal_password_is_set = (int)dalo_catalog_read_rows($catalog_pdo, "SELECT COUNT(id) FROM $table WHERE username=:username AND portalloginpassword IS NOT NULL AND portalloginpassword<>''", array(':username'=>$username))[0][0] === 1;
    } catch (Throwable $error) {
        dalo_catalog_read_failure($error); $username = ''; $portal_password_is_set = false;
    } finally { $catalog_pdo = null; }

    $username_enc = $username !== '' ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : '';
    $edit_username = $username_enc;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            // required later
            $current_datetime = date('Y-m-d H:i:s');
            $currBy = $operator;

            $planName = $_POST['planName'] ?? '';
            $planName = is_string($planName) ? trim($planName) : $planName;
            $oldplanName = $_POST['oldplanName'] ?? '';
            $oldplanName = is_string($oldplanName) ? trim($oldplanName) : $oldplanName;
            $profiles = (array_key_exists('profiles', $_POST) && isset($_POST['profiles'])) ? $_POST['profiles'] : array();
            $reassignplanprofiles_input = $_POST['reassignplanprofiles'] ?? '';
            $reassignplanprofiles = is_string($reassignplanprofiles_input) ? $reassignplanprofiles_input : '';

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
            $ui_PortalLoginPassword = (isset($_POST['portalLoginPassword']) &&
                                       dalo_portal_password_is_acceptable($_POST['portalLoginPassword']))
                                    ? trim($_POST['portalLoginPassword']) : "";

            $ui_hasPortalLoginPassword = $portal_password_is_set;
            $portal_password_available = $ui_hasPortalLoginPassword
                                      || dalo_portal_password_is_present($ui_PortalLoginPassword);
            $portal_access_valid = dalo_portal_access_is_valid($_POST, $ui_hasPortalLoginPassword);

            $ui_changeuserinfo = ($portal_password_available && isset($_POST['changeUserInfo']) && $_POST['changeUserInfo'] === '1')
                               ? '1' : '0';
            $ui_enableUserPortalLogin = ($portal_password_available && isset($_POST['enableUserPortalLogin']) && $_POST['enableUserPortalLogin'] === '1')
                                      ? '1' : '0';

            if (!$portal_access_valid) {
                $failureMsg = 'A portal password is required before portal access can be enabled.';
                $logAction .= "Failed updating user because portal access requires a password on page: ";
            }

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

            $bi_changeuserbillinfo = ($portal_password_available && isset($_POST['bi_changeuserbillinfo']) && $_POST['bi_changeuserbillinfo'] === '1')
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

            if (!empty($username) && $portal_access_valid) {
                $userinfo = array(
                    'firstname' => $firstname, 'lastname' => $lastname, 'email' => $email,
                    'department' => $department, 'company' => $company,
                    'workphone' => $workphone, 'homephone' => $homephone,
                    'mobilephone' => $mobilephone, 'address' => $address,
                    'city' => $city, 'state' => $state, 'country' => $country,
                    'zip' => $zip, 'notes' => $notes,
                    'changeuserinfo' => $ui_changeuserinfo,
                    'portalloginpassword' => $ui_PortalLoginPassword,
                    'enableportallogin' => $ui_enableUserPortalLogin,
                );
                $billinfo = array(
                    'contactperson' => $bi_contactperson, 'company' => $bi_company,
                    'email' => $bi_email, 'phone' => $bi_phone,
                    'address' => $bi_address, 'city' => $bi_city,
                    'state' => $bi_state, 'country' => $bi_country,
                    'zip' => $bi_zip, 'paymentmethod' => $bi_paymentmethod,
                    'cash' => $bi_cash, 'creditcardname' => $bi_creditcardname,
                    'creditcardnumber' => $bi_creditcardnumber,
                    'creditcardverification' => $bi_creditcardverification,
                    'creditcardtype' => $bi_creditcardtype,
                    'creditcardexp' => $bi_creditcardexp, 'notes' => $bi_notes,
                    'changeuserbillinfo' => $bi_changeuserbillinfo,
                    'lead' => $bi_lead, 'coupon' => $bi_coupon,
                    'ordertaker' => $bi_ordertaker, 'billstatus' => $bi_billstatus,
                    'nextinvoicedue' => $bi_nextinvoicedue, 'billdue' => $bi_billdue,
                    'postalinvoice' => $bi_postalinvoice, 'faxinvoice' => $bi_faxinvoice,
                    'emailinvoice' => $bi_emailinvoice,
                );
                try {
                    if (!is_string($planName) || !is_array($groups) ||
                        !is_string($ui_PortalLoginPassword) ||
                        !is_string($oldplanName) || !is_string($reassignplanprofiles_input)) {
                        throw new InvalidArgumentException('Invalid POS edit request');
                    }
                    $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                    dalo_pos_update($pdo, $configValues, $username, $planName,
                        (string)$reassignplanprofiles === '1', $groups, $userinfo,
                        $billinfo, $current_datetime, $currBy);
                    $successMsg = 'Updated user information';
                    $logAction .= 'Updated POS user on page: ';
                    $logDebugSQL .= 'POS user, billing and profile update (PDO transaction);\n';
                } catch (Throwable $error) {
                    $failureMsg = 'Failed to update user information';
                    $logAction .= 'Failed POS user update on page: ';
                    error_log('POS edit failed (' . get_class($error) . ')');
                }
            }

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }

    }

    if ($username === '') {
        if (!isset($failureMsg)) { $failureMsg = "You have specified an empty or invalid username"; }
        $inline_extra_js = "";
    } else {

        try {
            $catalog_pdo = dalo_catalog_read_open($configValues);
            /* an sql query to retrieve the password for the username to use in the quick link for the user test connectivity */
            $sql = sprintf("SELECT value FROM %s WHERE username=:username AND attribute LIKE '%%-Password' ORDER BY id DESC LIMIT 1",
                           dalo_read_table($catalog_pdo, $configValues, 'CONFIG_DB_TBL_RADCHECK'));
            $rows = dalo_catalog_read_rows($catalog_pdo, $sql, array(':username'=>$username));
            $logDebugSQL .= "$sql;\n";
            $user_password = $rows[0][0] ?? '';

            /* fill-in all the user info details */
            $sql = sprintf("SELECT firstname, lastname, email, department, company, workphone, homephone, mobilephone, address, city,
                                   state, country, zip, notes, changeuserinfo,
                                   (portalloginpassword IS NOT NULL AND portalloginpassword<>'') AS has_portal_password,
                                   enableportallogin, creationdate,
                                   creationby, updatedate, updateby
                              FROM %s WHERE username=:username", dalo_read_table($catalog_pdo, $configValues, 'CONFIG_DB_TBL_DALOUSERINFO'));
            $rows = dalo_catalog_read_rows($catalog_pdo, $sql, array(':username'=>$username));
            $logDebugSQL .= "$sql;\n";

            list(
                  $ui_firstname, $ui_lastname, $ui_email, $ui_department, $ui_company, $ui_workphone, $ui_homephone,
                  $ui_mobilephone, $ui_address, $ui_city, $ui_state, $ui_country, $ui_zip, $ui_notes, $ui_changeuserinfo,
                  $ui_hasPortalLoginPassword, $ui_enableUserPortalLogin, $ui_creationdate, $ui_creationby, $ui_updatedate,
                  $ui_updateby
                ) = $rows[0] ?? array_fill(0, 21, '');


            /* fill-in all the user bill info details */
            $sql = sprintf("SELECT id, planName, contactperson, company, email, phone, address, city, state, country, zip, paymentmethod,
                                   cash, creditcardname, creditcardnumber, creditcardverification, creditcardtype, creditcardexp,
                                   notes, changeuserbillinfo, `lead`, coupon, ordertaker, billstatus, lastbill, nextbill,
                                   nextinvoicedue, billdue, postalinvoice, faxinvoice, emailinvoice, creationdate, creationby,
                                   updatedate, updateby
                              FROM %s WHERE username=:username", dalo_read_table($catalog_pdo, $configValues, 'CONFIG_DB_TBL_DALOUSERBILLINFO'));
            $rows = dalo_catalog_read_rows($catalog_pdo, $sql, array(':username'=>$username));
            $logDebugSQL .= "$sql;\n";

            list(
                    $user_id, $bi_planname, $bi_contactperson, $bi_company, $bi_email, $bi_phone, $bi_address, $bi_city,
                    $bi_state, $bi_country, $bi_zip, $bi_paymentmethod, $bi_cash, $bi_creditcardname, $bi_creditcardnumber,
                    $bi_creditcardverification, $bi_creditcardtype, $bi_creditcardexp, $bi_notes, $bi_changeuserbillinfo,
                    $bi_lead, $bi_coupon, $bi_ordertaker, $bi_billstatus, $bi_lastbill, $bi_nextbill, $bi_nextinvoicedue,
                    $bi_billdue, $bi_postalinvoice, $bi_faxinvoice, $bi_emailinvoice, $bi_creationdate, $bi_creationby,
                    $bi_updatedate, $bi_updateby
                ) = $rows[0] ?? array_fill(0, 35, '');


        } catch (Throwable $error) {
            dalo_catalog_read_failure($error);
            $username = ''; $username_enc = ''; $inline_extra_js = '';
        } finally { $catalog_pdo = null; }

        // inline extra javascript
        $inline_extra_js = sprintf("var actionUsername = %s;\n", json_encode($username, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));

        $inline_extra_js .= '
function disableUser() {
    if (userActionPending) return false;
    if (confirm("You are about to disable this user account\nDo you want to continue?"))  {
        userAction("userDisable", [actionUsername]);
        return true;
    }
}

function enableUser() {
    if (userActionPending) return false;
    if (confirm("You are about to enable this user account\nDo you want to continue?"))  {
        userAction("userEnable", [actionUsername]);
        return true;
    }
}

function refillSessionTime() {
    if (userActionPending) return false;
    if (confirm("You are about to refill session time for this user account\nDo you want to continue?\n\nSuch action will also bill the user if set so in the plant the user is associated with!"))  {
        userAction("refillSessionTime", [actionUsername]);
        return true;
    }
}


function refillSessionTraffic() {
    if (userActionPending) return false;
    if (confirm("You are about to refill session traffic for this user account\nDo you want to continue?\n\nSuch action will also bill the user if set so in the plant the user is associated with!"))  {
        userAction("refillSessionTraffic", [actionUsername]);
        return true;
    }
}
' . "\n";
    }



    $hiddenPassword = (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) == "yes")
                    ? 'password' : 'text';

    // print HTML prologue
    $extra_css = array();

    $extra_js = array(
        "static/js/productive_funcs.js",
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
    if ($username !== '') {

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

        $dbSocket = null;
        ob_start();
        try {
            $dbSocket = dalo_catalog_read_open($configValues);
            include_once('include/management/groups.php');
            ob_end_flush();
        } catch (Throwable $error) {
            ob_end_clean();
            dalo_catalog_read_failure($error);
            include('include/management/actionMessages.php');
        } finally { $dbSocket = null; }


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
    userAction("checkDisabled", [actionUsername]);
};

EOF;
    }

    print_back_to_previous_page();

    include('include/config/logging.php');

    print_footer_and_html_epilogue($inline_extra_js);
?>
