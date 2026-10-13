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

    require_once implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'pdo_connection.php' ]);
    require_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'operator_edit.php' ]);

    // GET and POST select one unambiguous operator by bound username; never read its password.
    $request = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
    $operator_username = isset($request['operator_username']) && is_string($request['operator_username'])
                       ? trim($request['operator_username']) : '';
    if ($operator_username === '' || preg_match_all('/./us', $operator_username) > 32) {
        $operator_username = '';
    }
    $operatorRow = null;
    if ($operator_username !== '') {
        try {
            $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
            $operatorRow = dalo_operator_edit_row($pdo, $configValues, $operator_username);
        } catch (Throwable $error) {
            $failureMsg = 'Unable to load this operator';
            $logAction .= 'Failed loading operator on page: ';
        }
    }
    if (!$operatorRow) {
        $operator_username = '';
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $operatorRow) {
        if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) ||
            !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
            $logAction .= 'CSRF token error on page: ';
            $operator_username = '';
        } else {
            try {
                $input = dalo_operator_edit_input($_POST);
                $operatorRow = dalo_operator_edit_apply($pdo, $configValues, $operator_username,
                                                        $input, $operator);
                $escapedUsername = htmlspecialchars($operator_username, ENT_QUOTES, 'UTF-8');
                $successMsg = "Updated settings for: <b> $escapedUsername </b>";
                $logAction .= 'Successfully updated operator settings on page: ';
                // SQL parameters, identity values and hashes are never logged.
                $logDebugSQL .= 'Updated operator profile and ACLs using PDO;';
            } catch (InvalidArgumentException $error) {
                $failureMsg = $error->getMessage();
                $logAction .= 'Rejected operator edit on page: ';
            } catch (DomainException $error) {
                $failureMsg = $error->getMessage();
                $logAction .= 'Stale operator edit on page: ';
            } catch (Throwable $error) {
                $failureMsg = 'Failed to update this operator; no changes saved';
                $logAction .= 'Failed updating operator on page: ';
            }
        }
    }
    if ($operator_username === '') {
        if (!isset($failureMsg)) {
            $failureMsg = "the operator's username you have specified is empty or invalid";
            $logAction .= 'Invalid operator username on page: ';
        }
    } else {
        $curr_operator_id = (int) $operatorRow['id'];
        $operator_auth_source = operator_normalize_auth_source($operatorRow['auth_source']) ?? 'local';
        $current_auth_source = $operator_auth_source;
        $current_external_id = operator_normalize_external_id($operatorRow['external_id']);
        foreach (array('firstname','lastname','title','department','company','phone1','phone2',
                       'email1','email2','messenger1','messenger2','notes', 'lastlogin',
                       'creationdate','creationby','updatedate','updateby','totp_enabled',
                       'totp_confirmed_at') as $field) {
            ${'operator_' . $field} = $operatorRow[$field];
        }
    }
    $operator_username_enc = htmlspecialchars($operator_username, ENT_QUOTES, 'UTF-8');
    $edit_operator_username = $operator_username_enc;

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
                                        "value" => $operator_username,
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
                                        "value" => (string) $curr_operator_id,
                                        "name" => "identity_operator_id"
                                     );
        $input_descriptors1[] = array(
                                        "type" => "hidden",
                                        "value" => $current_auth_source,
                                        "name" => "identity_auth_source"
                                     );
        $input_descriptors1[] = array(
                                        "type" => "hidden",
                                        "value" => $current_external_id ?? "",
                                        "name" => "identity_external_id"
                                     );
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
