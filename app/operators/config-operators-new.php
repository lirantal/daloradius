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
    $operator_id = $_SESSION['operator_id'];

    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include_once("include/management/operator_identity.php");
    require_once("library/operator_create.php");
    include("../common/includes/layout.php");
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    $operator_auth_source = 'local';
    $operator_external_id = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $operator_username = '';
        $operator_username_enc = '';
        if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) ||
            !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
            $logAction .= 'Failed adding operator (invalid CSRF) on page: ';
        } else {
            try {
                $profile = dalo_operator_create_fields($_POST);
                $operator_username = $profile['username'];
                $operator_username_enc = htmlspecialchars($operator_username, ENT_QUOTES, 'UTF-8');
                $operator_auth_source = $profile['auth_source'];
                $operator_external_id = $profile['external_id'];
                $identity = operator_prepare_create_identity($operator_auth_source,
                                                              $profile['password'], $operator_external_id);
                unset($profile['password']);
                if (!$identity['ok']) {
                    throw new InvalidArgumentException($identity['error']);
                }
                require_once('../common/includes/pdo_connection.php');
                $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                dalo_operator_create($pdo, $configValues, $profile, $identity, $operator);
                $successMsg = sprintf('Successfully added new operator (<strong>%s</strong>) '
                    . '<a href="config-operators-edit.php?operator_username=%s" title="Edit">%s</a>',
                    $operator_username_enc, htmlspecialchars(rawurlencode($operator_username), ENT_QUOTES, 'UTF-8'),
                    $operator_username_enc);
                $logAction .= 'Successfully added new operator on page: ';
                $logDebugSQL .= "INSERT operator identity and ACLs (bound values omitted);\n";
            } catch (DomainException $error) {
                $failureMsg = sprintf('operator already exists in database: <b>%s</b>', $operator_username_enc);
                $logAction .= 'Failed adding operator (duplicate) on page: ';
            } catch (InvalidArgumentException $error) {
                $failureMsg = $error->getMessage(); // Only fixed validation strings; never user data.
                $logAction .= 'Failed adding operator (validation) on page: ';
            } catch (Throwable $error) {
                // SQL driver errors can include bound values and hashes.
                $failureMsg = 'Failed to add this operator identity to the database';
                $logAction .= 'Failed adding operator (database) on page: ';
            }
        }
    } // if form was submitted
    
    $hiddenPassword = (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) == "yes")
                    ? 'password' : 'text';
    

    // print HTML prologue
    $extra_css = array();
    
    $extra_js = array(
        "static/js/productive_funcs.js",
    );
    
    $title = t('Intro','configoperatorsnew.php');
    $help = t('helpPage','configoperatorsnew');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);
    
    include_once('include/management/actionMessages.php');
    
    if (!isset($successMsg)) {
    
        // set form component descriptors
        $input_descriptors0 = array();
        
        $input_descriptors0[] = array(
                                        "id" => "operator_username",
                                        "name" => "operator_username",
                                        "caption" => t('all','Username'),
                                        "type" => "text",
                                        "value" => ((isset($operator_username)) ? $operator_username : ""),
                                        "random" => true
                                     );
                                    
        $input_descriptors0[] = array(
                                        "id" => "operator_password",
                                        "name" => "operator_password",
                                        "caption" => t('all','Password'),
                                        "type" => $hiddenPassword,
                                        "value" => "",
                                        "random" => true,
                                        "disabled" => $operator_auth_source === 'ldap',
                                        "required" => $operator_auth_source === 'local'
                                     );
        
        // set navbar stuff
        $navkeys = array( array( 'OperatorInfo', "Operator Info" ), 'ContactInfo', array( 'ACLSettings', "ACL Settings" ) );

        // print navbar controls
        print_tab_header($navkeys);
        
        open_form();
    
        // open tab wrapper
        open_tab_wrapper();
    
        // tab 0
        open_tab($navkeys, 0, true);
    
        $fieldset0_descriptor = array(
                                        "title" => "Operator Info"
                                     );

        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        close_tab($navkeys, 0);

        // tab 1
        open_tab($navkeys, 1);

        include_once('include/management/operatorinfo.php');

        close_tab($navkeys, 1);

        // tab 2
        open_tab($navkeys, 2);

        include_once('include/management/operator_acls.php');
        drawOperatorACLs($operator_id);

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

    $inline_extra_js = <<<'JS'
function operatorAuthSourceChanged(select) {
    var password = document.getElementById('operator_password');
    if (!password) { return; }
    var isLdap = select && select.value === 'ldap';
    password.disabled = isLdap;
    password.required = !isLdap;
    if (isLdap) { password.value = ''; }
}
document.addEventListener('DOMContentLoaded', function () {
    operatorAuthSourceChanged(document.getElementById('auth_source'));
});
JS;
    print_footer_and_html_epilogue($inline_extra_js);
?>
