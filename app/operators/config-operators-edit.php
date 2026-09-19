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
    $operator_id = $_SESSION['operator_id'];

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'operator_identity.php' ]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $operator_username = (array_key_exists('operator_username', $_POST) && !empty(trim(str_replace("%", "", $_POST['operator_username']))))
                           ? trim(str_replace("%", "", $_POST['operator_username'])) : "";
    } else {
        $operator_username = (array_key_exists('operator_username', $_REQUEST) && !empty(trim(str_replace("%", "", $_REQUEST['operator_username']))))
                           ? trim(str_replace("%", "", $_REQUEST['operator_username'])) : "";
    }
    $operator_username_enc = (!empty($operator_username)) ? htmlspecialchars($operator_username, ENT_QUOTES, 'UTF-8') : "";


    // Check for one unambiguous operator and retain its current identity source.
    $sql = sprintf("SELECT id, auth_source, external_id FROM %s WHERE username='%s'",
                   $configValues['CONFIG_DB_TBL_DALOOPERATORS'], $dbSocket->escapeSimple($operator_username));
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";

    $exists = (!DB::isError($res) && $res->numRows() == 1);
    if (!$exists) {
        $operator_username = "";
    } else {
        list($curr_operator_id, $current_auth_source, $current_external_id) = $res->fetchRow();
        $curr_operator_id = intval($curr_operator_id);
        $current_auth_source = operator_normalize_auth_source($current_auth_source);
        $current_auth_source = $current_auth_source === null ? 'local' : $current_auth_source;
    }

    //feed the sidebar variables
    $edit_operator_username = $operator_username_enc;

    // from now on we can assume $operator_username is valid

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            if (!empty($operator_username)) {
                $current_datetime = date('Y-m-d H:i:s');
                $currBy = $_SESSION['operator_user'];

                $operator_password = (array_key_exists('operator_password', $_POST) && is_string($_POST['operator_password']))
                                   ? trim($_POST['operator_password']) : "";
                $requested_auth_source = array_key_exists('auth_source', $_POST)
                                       ? operator_normalize_auth_source($_POST['auth_source'])
                                       : $current_auth_source;
                $confirm_source_change = array_key_exists('confirm_auth_source_change', $_POST)
                                      && $_POST['confirm_auth_source_change'] === '1';
                $operator_external_id = ($requested_auth_source === 'ldap')
                                      ? operator_normalize_external_id($_POST['external_id'] ?? null) : null;
                $identity = operator_prepare_update_identity(
                    $current_auth_source,
                    $requested_auth_source,
                    $operator_password,
                    $confirm_source_change
                );

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

                if (!$identity['ok']) {
                    $failureMsg = $identity['error'];
                    $logAction .= "Failed updating operator authentication identity on page: ";
                } else {
                    if ($identity['password_mode'] === 'clear') {
                        $password_clause = 'password=NULL';
                    } elseif ($identity['password_mode'] === 'replace') {
                        $password_clause = "password='" . $dbSocket->escapeSimple($identity['password_hash']) . "'";
                    } else {
                        $password_clause = 'password=password';
                    }
                    $external_id_sql = is_null($operator_external_id)
                                     ? 'NULL' : "'" . $dbSocket->escapeSimple($operator_external_id) . "'";

                    $sql = sprintf("UPDATE %s SET %s, auth_source='%s', external_id=%s, firstname='%s', lastname='%s', title='%s', department='%s',
                                                  company='%s', phone1='%s', phone2='%s', email1='%s', email2='%s', messenger1='%s',
                                                  messenger2='%s', notes='%s', updatedate='%s', updateby='%s' WHERE id=%d",
                                   $configValues['CONFIG_DB_TBL_DALOOPERATORS'], $password_clause,
                                   $dbSocket->escapeSimple($identity['auth_source']), $external_id_sql,
                                   $dbSocket->escapeSimple($firstname), $dbSocket->escapeSimple($lastname), $dbSocket->escapeSimple($title),
                                   $dbSocket->escapeSimple($department), $dbSocket->escapeSimple($company), $dbSocket->escapeSimple($phone1),
                                   $dbSocket->escapeSimple($phone2), $dbSocket->escapeSimple($email1), $dbSocket->escapeSimple($email2),
                                   $dbSocket->escapeSimple($messenger1), $dbSocket->escapeSimple($messenger2), $dbSocket->escapeSimple($notes),
                                   $current_datetime, $dbSocket->escapeSimple($currBy), $curr_operator_id);
                    $res = $dbSocket->query($sql);
                    // Never put a submitted password or derived hash in debug logs.
                    $logDebugSQL .= "UPDATE operator identity and profile WHERE id=$curr_operator_id;\n";

                    if (array_key_exists('reset_totp', $_POST) && $_POST['reset_totp'] === '1') {
                        $sql = sprintf("UPDATE %s SET totp_enabled=0, totp_secret=NULL, totp_last_counter=NULL, totp_confirmed_at=NULL, totp_recovery_codes=NULL, updatedate='%s', updateby='%s' WHERE id=%d",
                                       $configValues['CONFIG_DB_TBL_DALOOPERATORS'], $current_datetime,
                                       $dbSocket->escapeSimple($currBy), $curr_operator_id);
                        $dbSocket->query($sql);
                        $logDebugSQL .= "$sql;\n";
                    }

                    foreach ($_POST as $field => $access) {
                        if (!preg_match('/^ACL_/', $field)) {
                            continue;
                        }
                        $file = substr($field, 4);
                        $sql = sprintf("SELECT id FROM %s WHERE operator_id=%d AND file='%s'",
                                       $configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'],
                                       $curr_operator_id, $dbSocket->escapeSimple($file));
                        $acl_res = $dbSocket->query($sql);
                        $logDebugSQL .= "$sql;\n";
                        if ($acl_res->numRows() > 0) {
                            $sql = sprintf("UPDATE %s SET access='%s' WHERE file='%s' AND operator_id=%d",
                                           $configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'],
                                           $dbSocket->escapeSimple($access), $dbSocket->escapeSimple($file), $curr_operator_id);
                        } else {
                            $sql = sprintf("INSERT INTO %s (operator_id, file, access) VALUES (%d, '%s', '%s')",
                                           $configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'], $curr_operator_id,
                                           $dbSocket->escapeSimple($file), $dbSocket->escapeSimple($access));
                        }
                        $dbSocket->query($sql);
                        $logDebugSQL .= "$sql;\n";
                    }

                    $current_auth_source = $identity['auth_source'];
                    $current_external_id = $operator_external_id;
                    $successMsg = "Updated settings for: <b> $operator_username_enc </b>";
                    $logAction .= "Successfully updated settings for operator user [$operator_username] on page: ";
                }
            }

        } else {
            $operator_username = "";
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    if (empty($operator_username)) {
        $failureMsg = "the operator's username you have specified is empty or invalid";
        $logAction .= "Failed updating settings for operator [empty or invalid username] on page: ";
    } else {
        /* fill-in all the operator settings */

        $sql = sprintf("SELECT id, password, auth_source, external_id, firstname, lastname, title, department, company, phone1, phone2,
                               email1, email2, messenger1, messenger2, notes, lastlogin,
                               creationdate, creationby, updatedate, updateby, totp_enabled, totp_confirmed_at
                          FROM %s
                         WHERE username='%s'", $configValues['CONFIG_DB_TBL_DALOOPERATORS'],
                                               $dbSocket->escapeSimple($operator_username));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        list(
                $curr_operator_id, $operator_password, $operator_auth_source, $operator_external_id,
                $operator_firstname, $operator_lastname, $operator_title, $operator_department, $operator_company, $operator_phone1, $operator_phone2,
                $operator_email1, $operator_email2, $operator_messenger1, $operator_messenger2, $operator_notes,
                $operator_lastlogin, $operator_creationdate, $operator_creationby, $operator_updatedate, $operator_updateby,
                $operator_totp_enabled, $operator_totp_confirmed_at
            ) = $res->fetchRow();
        $operator_auth_source = operator_normalize_auth_source($operator_auth_source);
        $operator_auth_source = $operator_auth_source === null ? 'local' : $operator_auth_source;
        $current_auth_source = $operator_auth_source;
        $current_external_id = operator_normalize_external_id($operator_external_id);
    }

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);

    $hiddenPassword = (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) == "yes")
                    ? 'password' : 'text';

    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);

    // print HTML prologue
    $extra_css = array();

    $extra_js = array(
        "static/js/productive_funcs.js",
    );

    $title = t('Intro','configoperatorsedit.php');
    $help = t('helpPage','configoperatorsedit');

    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    if (!empty($operator_username_enc)) {
        $title .= " :: $operator_username_enc";
    }

    print_title_and_help($title, $help);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);

    if (!empty($operator_username)) {
        // set form component descriptors
        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        "type" => "hidden",
                                        "value" => $operator_username_enc,
                                        "name" => "operator_username"
                                     );

        $input_descriptors0[] = array(
                                        "id" => "operator_username_presentation",
                                        "name" => "operator_username_presentation",
                                        "caption" => t('all','Username'),
                                        "type" => "text",
                                        "value" => ((isset($operator_username)) ? $operator_username : ""),
                                        "disabled" => true,
                                     );

        $input_descriptors0[] = array(
                                        "id" => "operator_password",
                                        "name" => "operator_password",
                                        "caption" => t('all','Password'),
                                        "type" => $hiddenPassword,
                                        "value" => "",
                                        "random" => true,
                                        "disabled" => $operator_auth_source === 'ldap',
                                        "required" => false
                                     );

        $totp_status = (intval($operator_totp_enabled) === 1)
                     ? sprintf("Enabled%s", (!empty($operator_totp_confirmed_at) ? " since " . $operator_totp_confirmed_at : ""))
                     : "Disabled";

        $input_descriptors0[] = array(
                                        "id" => "operator_totp_status",
                                        "name" => "operator_totp_status",
                                        "caption" => t('sidebar','TwoFactorAuthentication'),
                                        "type" => "text",
                                        "value" => $totp_status,
                                        "disabled" => true,
                                     );

        if (intval($operator_totp_enabled) === 1) {
            $input_descriptors0[] = array(
                                            "id" => "reset_totp",
                                            "name" => "reset_totp",
                                            "caption" => "Reset two-factor authentication for this operator",
                                            "type" => "checkbox",
                                            "value" => "1",
                                         );
        }

        // set navbar stuff
        $navkeys = array( array( 'OperatorInfo', "Operator Info" ), 'ContactInfo', array( 'ACLSettings', "ACL Settings" ), );

        // print navbar controls
        print_tab_header($navkeys);

        open_form();

        // open tab wrapper
        open_tab_wrapper();

        // tab 0
        open_tab($navkeys, 0, true);

        $fieldset0_descriptor = array( "title" => "Account Settings" );

        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        close_tab($navkeys, 0);

        // tab 1
        open_tab($navkeys, 1);
        include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'operatorinfo.php' ]);
        close_tab($navkeys, 1);

        // tab 2
        open_tab($navkeys, 2);
        include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'operator_acls.php' ]);
        drawOperatorACLs($curr_operator_id);
        close_tab($navkeys, 2);

        // close tab wrapper
        close_tab_wrapper();

        $input_descriptors1 = array();

        $input_descriptors1[] = array(
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                        "name" => "csrf_token"
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

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    $inline_extra_js = <<<'JS'
function operatorAuthSourceChanged(select) {
    var password = document.getElementById('operator_password');
    if (!password) { return; }
    var isLdap = select && select.value === 'ldap';
    password.disabled = isLdap;
    if (isLdap) { password.value = ''; }
}
document.addEventListener('DOMContentLoaded', function () {
    operatorAuthSourceChanged(document.getElementById('auth_source'));
});
JS;
    print_footer_and_html_epilogue($inline_extra_js);
