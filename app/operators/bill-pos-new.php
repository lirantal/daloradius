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
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'functions.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_common.php' ]);
    require_once implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'pdo_connection.php' ]);
    require_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'attributes.php' ]);
    require_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'pos_provision.php' ]);

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    // Validate scalar form fields before the legacy display-value normalization.
    $invalidRequest = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach (array('address', 'bi_address', 'bi_billdue', 'bi_billstatus', 'bi_cash', 'bi_changeuserbillinfo', 'bi_city', 'bi_company', 'bi_contactperson', 'bi_country', 'bi_coupon', 'bi_creditcardexp', 'bi_creditcardname', 'bi_creditcardnumber', 'bi_creditcardtype', 'bi_creditcardverification', 'bi_email', 'bi_emailinvoice', 'bi_faxinvoice', 'bi_lastbill', 'bi_lead', 'bi_nextbill', 'bi_nextinvoicedue', 'bi_notes', 'bi_ordertaker', 'bi_paymentmethod', 'bi_phone', 'bi_postalinvoice', 'bi_state', 'bi_zip', 'changeUserInfo', 'city', 'company', 'country', 'department', 'email', 'enableUserPortalLogin', 'firstname', 'homephone', 'lastname', 'mobilephone', 'notes', 'password', 'passwordType', 'planName', 'portalLoginPassword', 'state', 'username', 'workphone', 'zip') as $field) {
            if (isset($_POST[$field]) && !is_string($_POST[$field])) {
                $invalidRequest = true;
                $_POST[$field] = '';
            }
        }
        if (isset($_POST['profiles']) && !is_array($_POST['profiles'])) {
            $invalidRequest = true;
            $_POST['profiles'] = array();
        }
    }

    $valid_passwordTypes = dalo_filter_password_types($valid_passwordTypes);

    $username = (array_key_exists('username', $_POST) && isset($_POST['username']))
              ? trim(str_replace("%", "", $_POST['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

    $password = (array_key_exists('password', $_POST) && isset($_POST['password'])) ? trim($_POST['password']) : "";
    $passwordType = (array_key_exists('passwordType', $_POST) && isset($_POST['passwordType']) &&
                     in_array($_POST['passwordType'], $valid_passwordTypes)) ? $_POST['passwordType'] : "";
    $profiles = (array_key_exists('profiles', $_POST) && isset($_POST['profiles'])) ? $_POST['profiles'] : array();

    $planName = (array_key_exists('planName', $_POST) && isset($_POST['planName']))
              ? trim($_POST['planName']) : "";

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
    $ui_PortalLoginPassword = (isset($_POST['portalLoginPassword']) &&
                               dalo_portal_password_is_acceptable($_POST['portalLoginPassword']))
                            ? trim($_POST['portalLoginPassword']) : "";

    $portal_access_valid = dalo_portal_access_is_valid($_POST);

    // these are forced to 0 (disabled) if user portal login password is empty
    $ui_changeuserinfo = (dalo_portal_password_is_present($ui_PortalLoginPassword) && isset($_POST['changeUserInfo']) && $_POST['changeUserInfo'] === '1')
                       ? '1' : '0';
    $ui_enableUserPortalLogin = (dalo_portal_password_is_present($ui_PortalLoginPassword) && isset($_POST['enableUserPortalLogin']) && $_POST['enableUserPortalLogin'] === '1')
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
    $bi_changeuserbillinfo = (dalo_portal_password_is_present($ui_PortalLoginPassword) && isset($_POST['bi_changeuserbillinfo']) && $_POST['bi_changeuserbillinfo'] === '1')
                           ? '1' : '0';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) &&
            dalo_check_csrf_token($_POST['csrf_token'])) {
            if ($invalidRequest || !$portal_access_valid || $username === '' ||
                $password === '' || $passwordType === '') {
                $failureMsg = 'Username, password, password type or submitted field is invalid';
                $logAction .= 'Failed adding POS user with invalid input on page: ';
            } else {
                $current_datetime = date('Y-m-d H:i:s');
                $currBy = $operator;
                $_POST['injected_attribute'] = array($passwordType, $password, ':=', 'check');
                    $skipList = array(
                                        "username", "password", "passwordType", "profiles", "planName",
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
                                        "creationdate" => $current_datetime,
                                        "creationby" => $currBy,
                                   );

                    $billing = array(
                        'contactperson' => $bi_contactperson,
                        'company' => $bi_company,
                        'email' => $bi_email,
                        'phone' => $bi_phone,
                        'address' => $bi_address,
                        'city' => $bi_city,
                        'state' => $bi_state,
                        'country' => $bi_country,
                        'zip' => $bi_zip,
                        'paymentmethod' => $bi_paymentmethod,
                        'cash' => $bi_cash,
                        'creditcardname' => $bi_creditcardname,
                        'creditcardnumber' => $bi_creditcardnumber,
                        'creditcardverification' => $bi_creditcardverification,
                        'creditcardtype' => $bi_creditcardtype,
                        'creditcardexp' => $bi_creditcardexp,
                        'notes' => $bi_notes,
                        'lead' => $bi_lead,
                        'coupon' => $bi_coupon,
                        'ordertaker' => $bi_ordertaker,
                        'billstatus' => $bi_billstatus,
                        'lastbill' => $bi_lastbill,
                        'nextbill' => $bi_nextbill,
                        'nextinvoicedue' => $bi_nextinvoicedue,
                        'billdue' => $bi_billdue,
                        'postalinvoice' => $bi_postalinvoice,
                        'faxinvoice' => $bi_faxinvoice,
                        'emailinvoice' => $bi_emailinvoice,
                        'changeuserbillinfo' => $bi_changeuserbillinfo,
                    );
                try {
                    $manualProfiles = dalo_plan_profiles_from_post($profiles);
                    $attributes = dalo_pos_attributes_from_post($_POST, $skipList, $valid_ops);
                    $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                    list($attributesCount, $groupsCount, $userbillinfo_id, $invoice_id) =
                        dalo_pos_provision($pdo, $configValues, $username, $planName, $manualProfiles,
                                           $attributes, $params, $billing, $current_datetime, $currBy);
                    $addedUserInfo = 'stored';
                    $successMsg = sprintf(
                        'Inserted new user <strong>%s</strong>: <a href="bill-pos-edit.php?username=%s" title="Edit">%s</a>',
                        $username_enc,
                        $username_enc,
                        urlencode($username_enc)
                    );

                    $successMsg .= '<ul style="color: black">'
                                 . sprintf("<li><strong>attributes count</strong>: %d</li>", $attributesCount)
                                 . sprintf("<li><strong>groups count</strong>: %d</li>", $groupsCount)
                                 . sprintf("<li><strong>user info</strong>: %s</li>", $addedUserInfo)
                                 . '</ul>'

                                 . "<strong>Welcome notification</strong>: "
                                 . '<a target="_blank" href="include/common/notifications.php?action=preview">Preview</a>';

                    if (strtolower($configValues['CONFIG_MAIL_ENABLED']) == "yes") {
                        $successMsg .= ' or <a href="include/common/notifications.php?action=email">Send</a>';
                    }

                    $_SESSION['notification'] = array( 'username' => $username, 'type' => 'user-welcome' );

                    $logAction .= sprintf("Successfully inserted new user [%s] on page: ", $username);
                } catch (DomainException $error) {
                    $failureMsg = 'User already exists in database';
                    $logAction .= 'Failed adding existing POS user on page: ';
                } catch (InvalidArgumentException $error) {
                    $failureMsg = 'Invalid user, plan, profile or attribute';
                    $logAction .= 'Failed adding POS user with invalid selection on page: ';
                } catch (Throwable $error) {
                    $failureMsg = 'Failed to provision user and billing records';
                    $logAction .= 'Failed adding POS user on page: ';
                }
            }
        } else {
            $failureMsg = 'CSRF token error';
            $logAction .= "$failureMsg on page: ";
        }
    }

    $hiddenPassword = (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) == "yes")
                    ? 'password' : 'text';


    // print HTML prologue
    $extra_css = array();

    $extra_js = array(
        "static/js/productive_funcs.js",
    );

    $title = t('Intro','billposnew.php');
    $help = t('helpPage','billposnew');

    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    print_title_and_help($title, $help);

    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);

    if (!isset($successMsg)) {

        include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'populate_selectbox.php' ]);

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
                                        "selected_value" => ((isset($failureMsg)) ? $profiles : ""),
                                        "tooltipText" => t('Tooltip','groupTooltip')
                                     );

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        close_tab($navkeys, 0);

        // open 1-st tab
        open_tab($navkeys, 1);

        include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'userinfo.php' ]);

        close_tab($navkeys, 1);

        // open 2-nd tab
        open_tab($navkeys, 2);

        include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'userbillinfo.php' ]);

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

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    print_footer_and_html_epilogue();

?>
