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
    include_once("include/management/functions.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    require_once __DIR__ . '/library/user_group_pages_pdo.php';
    $input = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
    $username = is_string($input['username'] ?? null) ? trim($input['username']) : '';
    $current_groupname = is_string($input['current_group'] ?? null) ? trim($input['current_group']) : '';
    $groupname = is_string($input['group'] ?? null) ? trim($input['group']) : '';
    $this_username = $this_groupname = '';
    $this_priority = 0;
    $pdo = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_string($_POST['csrf_token'] ?? null) || !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
        } else {
            try {
                $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                dalo_usergroup_edit($pdo, $configValues, $_POST['username'] ?? null,
                                   $_POST['current_group'] ?? null, $_POST['group'] ?? null,
                                   $_POST['priority'] ?? 0);
                $successMsg = 'Updated user-group mapping';
                $logAction = 'Updated user-group mapping on page: ';
                $current_groupname = $groupname;
            } catch (Throwable $error) {
                $failureMsg = 'Unable to update user-group mapping: invalid or stale selection or database operation failed';
                $logAction = 'User-group update failed on page: ';
            } finally { $pdo = null; }
        }
    }
    try {
        $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        $table = dalo_usergroup_table($configValues, 'CONFIG_DB_TBL_RADUSERGROUP');
        $row = dalo_usergroup_query($pdo, "SELECT username,groupname,priority FROM $table WHERE username=? AND groupname=? ORDER BY id LIMIT 1",
                                   array($username, $current_groupname))->fetch(PDO::FETCH_NUM);
        if ($row) { list($this_username, $this_groupname, $this_priority) = $row; }
        else {
            $username = $current_groupname = '';
            if (!isset($failureMsg)) { $failureMsg = 'The user-group mapping is empty or invalid'; }
        }
    } catch (Throwable $error) {
        $username = $current_groupname = '';
        if (!isset($failureMsg)) { $failureMsg = 'Unable to load user-group mapping'; }
    } finally { $pdo = null; }
    $username_enc = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $current_groupname_enc = htmlspecialchars($current_groupname, ENT_QUOTES, 'UTF-8');
    $usernameList = $username_enc;

    include_once("lang/main.php");

    include("../common/includes/layout.php");

    // print HTML prologue
    $extra_css = array();

    $extra_js = array(
        "static/js/productive_funcs.js",
    );

    $title = t('Intro','mngradusergroupedit');
    $help = t('helpPage','mngradusergroupedit');

    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    if (!empty($username_enc)) {
        $title .= " $username_enc";
    }

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    if ($username !== '' && $current_groupname !== '') {
        include_once('include/management/populate_selectbox.php');

        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        "name" => "username-presentation",
                                        "caption" => t('all','Username'),
                                        "type" => "text",
                                        "value" => $this_username,
                                        "tooltipText" => t('Tooltip','usernameTooltip'),
                                        "disabled" => true,
                                     );

        $input_descriptors0[] = array(
                                        "name" => "username",
                                        "type" => "hidden",
                                        "value" => $this_username,
                                     );

        $input_descriptors0[] = array(
                                        "name" => "groupname-presentation",
                                        "caption" => (t('all','Groupname') . " (current)"),
                                        "type" => "text",
                                        "value" => $this_groupname,
                                        "disabled" => true,
                                     );

        $input_descriptors0[] = array(
                                        "name" => "current_group",
                                        "type" => "hidden",
                                        "value" => $this_groupname,
                                     );

        $options = get_groups();
        $input_descriptors0[] = array(
                                        "id" => "group",
                                        "name" => "group",
                                        "caption" => (t('all','Groupname') . " (new)"),
                                        "type" => "select",
                                        "options" => $options,
                                        "selected_value" => $this_groupname,
                                        "tooltipText" => t('Tooltip','groupTooltip'),
                                        "onchange" => "setDisabledUsersGroupPriority()"
                                     );

        $priority_descriptor = array(
                                        "id" => "priority",
                                        "name" => "priority",
                                        "caption" => t('all','Priority'),
                                        "type" => "number",
                                        "min" => "-1",
                                        "value" => normalize_user_group_priority($this_groupname, $this_priority),
                                     );

        if (is_disabled_users_group($this_groupname)) {
            $priority_descriptor["title"] = "Reserved disabled-users group; priority is locked to -1 so it is evaluated before normal groups.";
        }

        $input_descriptors0[] = $priority_descriptor;

        $input_descriptors0[] = array(
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                        "name" => "csrf_token"
                                     );

        $input_descriptors0[] = array(
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

    }

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
