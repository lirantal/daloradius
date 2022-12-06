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

    $username = "";
    $password = "";
    $maxallsession = "";
    $expiration = "";
    $sessiontimeout = "";
    $idletimeout = "";
    $ui_changeuserinfo = "0";
    $bi_changeuserbillinfo = "0";
    
    if (isset($_POST['submit'])) {
        // required later
        $currDate = date('Y-m-d H:i:s');
        $currBy = $operator;
    
        // TODO validate user input
        $username = (array_key_exists('username', $_POST) && isset($_POST['username']))
                  ? trim(str_replace("%", "", $_POST['username'])) : "";
        $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

        // search:  \$([A-Za-z0-9_]+)\s+=\s+\$_POST\[\'([A-Za-z0-9_]+)\'\];
        // replace: $\1 = (array_key_exists('\2', $_POST) && isset($_POST['\2'])) ? $_POST['\2'] : "";

        $password = (array_key_exists('password', $_POST) && isset($_POST['password'])) ? trim($_POST['password']) : "";
        $passwordType = (array_key_exists('passwordType', $_POST) && isset($_POST['passwordType']) &&
                         in_array($_POST['passwordType'], $valid_passwordTypes)) ? $_POST['passwordType'] : "";
        $groups = (array_key_exists('groups', $_POST) && isset($_POST['groups'])) ? $_POST['groups'] : array();
        $maxallsession = (array_key_exists('maxallsession', $_POST) && isset($_POST['maxallsession'])) ? $_POST['maxallsession'] : "";
        $expiration = (array_key_exists('expiration', $_POST) && isset($_POST['expiration'])) ? $_POST['expiration'] : "";
        $sessiontimeout = (array_key_exists('sessiontimeout', $_POST) && isset($_POST['sessiontimeout'])) ? $_POST['sessiontimeout'] : "";
        $idletimeout = (array_key_exists('idletimeout', $_POST) && isset($_POST['idletimeout'])) ? $_POST['idletimeout'] : "";
        $simultaneoususe = (array_key_exists('simultaneoususe', $_POST) && isset($_POST['simultaneoususe'])) ? $_POST['simultaneoususe'] : "";
        $framedipaddress = (array_key_exists('framedipaddress', $_POST) && isset($_POST['framedipaddress'])) ? $_POST['framedipaddress'] : "";

        // search:  isset\(\$_POST\[\'([A-Za-z0-9_]+)\'\]\)\s+\?\s+\$([A-Za-z0-9_]+).*
        // replace: $\2 = (array_key_exists('\1', $_POST) && isset($_POST['\1'])) ? $_POST['\1'] : "";

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
        $bi_changeuserbillinfo = (array_key_exists('changeUserBillInfo', $_POST) && isset($_POST['changeUserBillInfo'])) ? $_POST['changeUserBillInfo'] : "0";
        
        include('library/opendb.php');
        
        // check if username is already present in the radcheck table
        $sql = sprintf("SELECT COUNT(DISTINCT(username)) FROM %s WHERE username='%s'",
                       $configValues['CONFIG_DB_TBL_RADCHECK'], $dbSocket->escapeSimple($username));
        $res = $dbSocket->query($sql);
        $userExists = (intval($res->fetchrow()[0]) > 0);
        $logDebugSQL .= "$sql;\n";

        if ($userExists) {
            $failureMsg = "user already exist in database: <b> $username_enc </b>";
            $logAction .= "Failed adding new user already existing in database [$username] on page: ";
        } else {
            
            // username and password are required
            if (empty($username) || empty($password)) {
                $failureMsg = "username and/or password are empty";
                $logAction .= "Failed adding (possible empty user/pass) new user [$username] on page: ";
            } else {
            
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

                // left piece of the query which is the same for all common check attributes
                $sql0 = sprintf("INSERT INTO %s (id, username, attribute, op, value) VALUES ",
                                $configValues['CONFIG_DB_TBL_RADCHECK']);

                $sql_piece_format = "(0, '%s', '%s', ':=', %s)";
                $sql_pieces = array();

                // insert username/password
                $sql_pieces[] = sprintf($sql_piece_format, $dbSocket->escapeSimple($username),
                                                           $dbSocket->escapeSimple($passwordType),
                                                           $dbPassword);
                
                if ($maxallsession) {
                    $sql_pieces[] = sprintf($sql_piece_format, $dbSocket->escapeSimple($username),
                                                               'Max-All-Session',
                                                               $dbSocket->escapeSimple($maxallsession));
                }

                if ($expiration) {
                    $sql_pieces[] = sprintf($sql_piece_format, $dbSocket->escapeSimple($username),
                                                               'Expiration',
                                                               $dbSocket->escapeSimple($expiration));
                }

                if ($sessiontimeout) {
                    $sql_pieces[] = sprintf($sql_piece_format, $dbSocket->escapeSimple($username),
                                                               'Session-Timeout',
                                                               $dbSocket->escapeSimple($sessiontimeout));
                }

                if ($idletimeout) {
                    $sql_pieces[] = sprintf($sql_piece_format, $dbSocket->escapeSimple($username),
                                                               'Idle-Timeout',
                                                               $dbSocket->escapeSimple($idletimeout));
                }

                if ($simultaneoususe) {
                    $sql_pieces[] = sprintf($sql_piece_format, $dbSocket->escapeSimple($username),
                                                               'Simultaneous-Use',
                                                               $dbSocket->escapeSimple($simultaneoususe));
                }
                
                if ($framedipaddress) {
                    $sql_pieces[] = sprintf($sql_piece_format, $dbSocket->escapeSimple($username),
                                                               'Framed-IP-Address',
                                                               $dbSocket->escapeSimple($framedipaddress));
                }

                if (count($sql_pieces) > 0) {
                    $sql = $sql0 . implode(", ", $sql_pieces);
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                }
                
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
                }
                
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
                }
                
                $successMsg = "Added to database new user: <b> $username_enc </b>";
                $logAction .= "Successfully added new user [$username] on page: ";
                
            
            } // if (empty($username) || empty($password)) {
        
        } // if ($userExists) {

        include('library/closedb.php');
    }

    include_once('library/config_read.php');
    
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
        "library/javascript/ajaxGeneric.js",
        "library/javascript/productive_funcs.js",
        // js tabs stuff
        "library/javascript/tabs.js"
    );
    
    $title = t('Intro','mngnewquick.php');
    $help = t('helpPage','mngnewquick');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    include("menu-mng-users.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    $input_descriptors = array();
    
    $input_descriptors[] = array(
                                    "id" => "username",
                                    "name" => "username",
                                    "caption" => t('all','Username'),
                                    "type" => "text",
                                    "value" => "",
                                    "random" => true,
                                    "tooltipText" => t('Tooltip','usernameTooltip')
                                 );
                                
    $input_descriptors[] = array(
                                    "id" => "password",
                                    "name" => "password",
                                    "caption" => t('all','Password'),
                                    "type" => $hiddenPassword,
                                    "value" => "",
                                    "random" => true,
                                    "tooltipText" => t('Tooltip','passwordTooltip')
                                 );
    $input_descriptors[] = array(
                                    "name" => "passwordType",
                                    "caption" => t('all','PasswordType'),
                                    "options" => $valid_passwordTypes,
                                    "type" => "select"
                                );

    // draw navbar
    $navbuttons = array(
                          'AccountInfo-tab' => t('title','AccountInfo'),
                          'UserInfo-tab' => t('title','UserInfo'),
                          'BillingInfo-tab' => t('title','BillingInfo')
                       );

    print_tab_navbuttons($navbuttons);

?>

<form name="newuser" method="POST">
    <div id="AccountInfo-tab" class="tabcontent" title="<?= t('title','AccountInfo') ?>" style="display: block">
        <fieldset>
            <h302> <?= t('title','AccountInfo') ?> </h302>
            <ul>

<?php
                    foreach ($input_descriptors as $input_descriptor) {
                        print_form_component($input_descriptor);
                    }
?>

                <li class="fieldset">
                    <label for="group" class="form"><?= t('all','Group')?></label>
<?php   
                    include_once('include/management/populate_selectbox.php');
                    populate_groups("Select Groups","groups[]");
                    
                    $onclick = "javascript:ajaxGeneric('include/management/dynamic_groups.php','getGroups','divContainerGroups',"
                             . "genericCounter('divCounter')+'&elemName=groups[]');";
?>

                    <a class="tablenovisit" href="#" onclick="<?= $onclick ?>">Add</a>
                    <img src="images/icons/comment.png" alt="Tip" border="0" onclick="javascript:toggleShowDiv('group')">
                    <div id="divContainerGroups"></div>


                    <div id="groupTooltip"  style='display:none;visibility:visible' class="ToolTip">
                        <img src="images/icons/comment.png" alt="Tip" border="0">
                        <?= t('Tooltip','groupTooltip') ?>
                    </div>
                </li>

            </ul>
        </fieldset>

        <fieldset>

            <h302><?= t('title','Attributes') ?></h302>

            <ul>

<?php
    $input_descriptors = array();

    $input_descriptors[] = array(
                                    "name" => "simultaneoususe",
                                    "caption" => t('all','SimultaneousUse'),
                                    "type" => "number",
                                );
    
    $input_descriptors[] = array(
                                    "name" => "framedipaddress",
                                    "caption" => t('all','FramedIPAddress'),
                                    "type" => "text",
                                    "pattern" => "^(((2(5[0-5]|[0-4][0-9]))|1[0-9]{2}|[1-9]?[0-9])\.){3}((2(5[0-5]|[0-4][0-9]))|1[0-9]{2}|[1-9]?[0-9])$"
                                );
    
    $input_descriptors[] = array(
                                    "id" => "expiration",
                                    "name" => "expiration",
                                    "caption" => t('all','Expiration'),
                                    "type" => "date",
                                    "min" => "2022-01-01",
                                    "max" => "2030-01-01"
                                    
                                );
    
    foreach ($input_descriptors as $input_descriptor) {
        print_form_component($input_descriptor);
    }
                    
    $time_values = array(
                            "0" => "calculate time",
                            "1" => "seconds",
                            "60" => "minutes",
                            "3600" => "hours",
                            "86400" => "days",
                            "604800" => "weeks",
                            "2592000" => "months (30 days)",
                        );
                        
    $select_descriptors = array();
    $select_descriptors[] = array(
                                    "id" => "sessiontimeout",
                                    "name" => "sessiontimeout",
                                    "caption" => t('all','SessionTimeout'),
                                    "type" => "number",
                                    "options" => $time_values
                                 );
                                 
    $select_descriptors[] = array(
                                    "id" => "idletimeout",
                                    "name" => "idletimeout",
                                    "caption" => t('all','IdleTimeout'),
                                    "type" => "number",
                                    "options" => $time_values
                                 );
                                 
    $select_descriptors[] = array(
                                "id" => "maxallsession",
                                "name" => "idletimeout",
                                "caption" => t('all','MaxAllSession'),
                                "type" => "number",
                                "options" => $time_values
                             );
                             
    
    foreach ($select_descriptors as $select_descriptor) {
        print_calculated_select($select_descriptor);
    }

    $button_descriptor = array(
                            'type' => 'submit',
                            'name' => 'submit',
                            'value' => t('buttons','apply'),
                            'onclick' => 'javascript:small_window(document.newuser.username.value,
                                                                  document.newuser.password.value,
                                                                  document.newuser.maxallsession.value)'
                          );


?>
            <ul>
        </fieldset>

<?php
            print_form_component($button_descriptor);
?>

    </div><!-- #AccountInfo-tab -->

    <div id="UserInfo-tab" class="tabcontent" title="<?= t('title','UserInfo') ?>">
<?php
            $customApplyButton = sprintf('<input type="submit" name="submit" value="%s" ', t('buttons','apply'))
                               . 'onclick="javascript:small_window(document.newuser.username.value, '
                               . 'document.newuser.password.value, document.newuser.maxallsession.value);" '
                               . 'class="button">';
            include_once('include/management/userinfo.php');
?>

        </div><!-- #UserInfo-tab -->

        <div id="BillingInfo-tab" class="tabcontent" title="<?= t('title','BillingInfo') ?>">
<?php
            $customApplyButton = sprintf('<input type="submit" name="submit" value="%s" class="button">', t('buttons','apply'));
            include_once('include/management/userbillinfo.php');
?>

        </div><!-- #BillingInfo-tab -->
        
    
</form>

<?php
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
