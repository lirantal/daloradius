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

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");
    include("include/management/functions.php");

    require_once __DIR__ . '/library/user_group_pages_pdo.php';
    $username = is_string($_POST['username'] ?? null) ? trim($_POST['username']) : '';
    $groupname = is_string($_POST['group'] ?? null) ? trim($_POST['group']) : '';
    $username_enc = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $groupname_enc = htmlspecialchars($groupname, ENT_QUOTES, 'UTF-8');
    $priority = normalize_user_group_priority($groupname, is_scalar($_POST['priority'] ?? null) ? $_POST['priority'] : 0);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_string($_POST['csrf_token'] ?? null) || !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
        } else {
            $pdo = null;
            try {
                $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                dalo_usergroup_create($pdo, $configValues, $_POST['username'] ?? null,
                                     $_POST['group'] ?? null, $_POST['priority'] ?? 0);
                $successMsg = sprintf('Added new user-group mapping (%s &isin; %s) '
                    . '[<a href="mng-rad-usergroup-edit.php?username=%s&current_group=%s">Edit</a>]',
                    $username_enc, $groupname_enc, urlencode($username), urlencode($groupname));
                $logAction = 'Added user-group mapping on page: ';
            } catch (Throwable $error) {
                $failureMsg = 'Unable to create user-group mapping: invalid input, existing mapping or database operation failed';
                $logAction = 'User-group creation failed on page: ';
            } finally { $pdo = null; }
        }
    }

    $title = t('Intro','mngradusergroupnew.php');
    $help = t('helpPage','mngradusergroupnew');
    
    print_html_prologue($title, $langCode);
    
    


    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    include_once('include/management/populate_selectbox.php');

    $input_descriptors0 = array();
    
    $options = get_users();
    array_unshift($options , '');
    
    $input_descriptors0[] = array(
                                    "id" => "username",
                                    "name" => "username",
                                    "caption" => t('all','Username'),
                                    "type" => "text",
                                    "value" => ((isset($failureMsg)) ? $username : ""),
                                    "tooltipText" => t('Tooltip','usernameTooltip'),
                                    "datalist" => $options,
                                 );

    $options = get_groups();
    array_unshift($options , '');
    $input_descriptors0[] = array(
                                    "id" => "group",
                                    "name" => "group",
                                    "caption" => t('all','Groupname'),
                                    "type" => "select",
                                    "options" => $options,
                                    "selected_value" => ((isset($failureMsg)) ? $groupname : ""),
                                    "tooltipText" => t('Tooltip','groupTooltip'),
                                    "onchange" => "setDisabledUsersGroupPriority()"
                                 );
                                 
    $priority_descriptor = array(
                                    "id" => "priority",
                                    "name" => "priority",
                                    "caption" => t('all','Priority'),
                                    "type" => "number",
                                    "min" => "-1",
                                    "value" => ((isset($failureMsg)) ? $priority : "0"),
                                 );

    if (isset($failureMsg) && is_disabled_users_group($groupname)) {
        $priority_descriptor["title"] = "Reserved disabled-users group; priority is locked to -1 so it is evaluated before normal groups.";
    }

    $input_descriptors0[] = $priority_descriptor;

    $input_descriptors1 = array();

    $input_descriptors1[] = array(
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                        "name" => "csrf_token"
                                     );

    $input_descriptors1[] = array(
                                    'type' => 'submit',
                                    'name' => 'submit',
                                    'value' => t('buttons','apply')
                                 );

    $fieldset0_descriptor = array( "title" => t('title','GroupInfo') );
        
    open_form();
    
    open_fieldset($fieldset0_descriptor);
    
    foreach ($input_descriptors0 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
    
    close_fieldset();
    
    foreach ($input_descriptors1 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
    
    close_form();


    $disabled_users_group_js = json_encode(DALO_DISABLED_USERS_GROUP);
    $disabled_users_group_priority_js = json_encode((string)DALO_DISABLED_USERS_GROUP_PRIORITY);

    echo <<<EOF
<script>
var disabledUsersGroupName = {$disabled_users_group_js};
var disabledUsersGroupPriority = {$disabled_users_group_priority_js};

function setDisabledUsersGroupPriority() {
    var group = document.getElementById('group');
    var priority = document.getElementById('priority');

    if (!group || !priority) {
        return;
    }

    if (group.value === disabledUsersGroupName) {
        priority.value = disabledUsersGroupPriority;
        priority.min = disabledUsersGroupPriority;
        priority.readOnly = true;
        priority.classList.add('bg-body-secondary', 'text-muted');
        priority.title = 'Reserved disabled-users group; priority is locked to -1 so it is evaluated before normal groups.';
    } else {
        priority.min = '0';
        priority.readOnly = false;
        priority.classList.remove('bg-body-secondary', 'text-muted');
        if (parseInt(priority.value, 10) < 0) {
            priority.value = '0';
        }
        priority.title = '';
    }
}

document.addEventListener('DOMContentLoaded', setDisabledUsersGroupPriority);
</script>
EOF;

    print_back_to_previous_page();

    include('include/config/logging.php');

    print_footer_and_html_epilogue();
?>
