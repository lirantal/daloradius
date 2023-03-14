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

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // declaring variables
        $username = (array_key_exists('username', $_POST) && !empty(str_replace("%", "", trim($_POST['username']))))
                  ? str_replace("%", "", trim($_POST['username'])) : "";
        $groupname = (array_key_exists('group', $_POST) && !empty(str_replace("%", "", trim($_POST['group']))))
                   ? str_replace("%", "", trim($_POST['group'])) : "";
        $groupname_enc = (!empty($groupname)) ? htmlspecialchars($groupname, ENT_QUOTES, 'UTF-8') : "";

        $current_groupname = (array_key_exists('current_group', $_POST) && !empty(str_replace("%", "", trim($_POST['current_group']))))
                      ? str_replace("%", "", trim($_POST['current_group'])) : "";

        $priority = (array_key_exists('priority', $_POST) && isset($_POST['priority']) &&
                     intval(trim($_POST['priority'])) >= 0) ? intval(trim($_POST['priority'])) : 0;
    } else {
        // declaring variables
        $username = (array_key_exists('username', $_REQUEST) && !empty(str_replace("%", "", trim($_REQUEST['username']))))
                  ? str_replace("%", "", trim($_REQUEST['username'])) : "";
        $current_groupname = (array_key_exists('current_group', $_REQUEST) && !empty(str_replace("%", "", trim($_REQUEST['current_group']))))
                      ? str_replace("%", "", trim($_REQUEST['current_group'])) : "";
    }

    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    $current_groupname_enc = (!empty($current_groupname)) ? htmlspecialchars($current_groupname, ENT_QUOTES, 'UTF-8') : "";

    // feed the sidebar
    $usernameList = $username_enc;

    include('../common/includes/db_open.php');

    $mapping_check_format = "SELECT COUNT(*) FROM %s WHERE username='%s' AND groupname='%s'";

    // check if the old mapping is already in place
    $sql = sprintf($mapping_check_format, $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                                          $dbSocket->escapeSimple($username),
                                          $dbSocket->escapeSimple($current_groupname));
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";

    $old_mapping_inplace = intval($res->fetchrow()[0]) > 0;

    if (!$old_mapping_inplace) {
        // if the mapping is not in place we reset user and group
        $username = "";
        $current_groupname = "";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            if (empty($username) || empty($groupname) || empty($current_groupname)) {
                // username and groupname are required
                $failureMsg = "Username and groupname are required.";
                $logAction .= "Failed updating user-group mapping (username and/or groupname missing or invalid): ";
            } else {
                // check if the new mapping is already in place
                $sql = sprintf($mapping_check_format, $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                                                      $dbSocket->escapeSimple($username),
                                                      $dbSocket->escapeSimple($groupname));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";

                $new_mapping_inplace = intval($res->fetchrow()[0]) > 0;

                if ($new_mapping_inplace) {
                    // error
                    $failureMsg = "The chosen user mapping ($username_enc - $groupname_enc) is already in place.";
                    $logAction .= "Failed updating user-group mapping [$username - $groupname already in place]: ";
                } else {
                    $sql = sprintf("UPDATE %s SET groupname='%s', priority=%s WHERE username='%s' AND groupname='%s'",
                                   $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($groupname),
                                   $dbSocket->escapeSimple($priority), $dbSocket->escapeSimple($username),
                                   $dbSocket->escapeSimple($current_groupname));
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";

                    if (!DB::isError($res)) {
                        $successMsg = "Updated user-group mapping [$username_enc, from $current_groupname_enc to $groupname_enc]";
                        $logAction .= "Updated user-group mapping [$username, from $current_groupname to $groupname]: ";

                        // reset variables
                        $current_groupname = $groupname;
                        $groupname = "";
                        $groupname_enc = "";

                    } else {
                        $failureMsg = "DB Error when updating the chosen user mapping ($username_enc, from $current_groupname_enc to $groupname_enc)";
                        $logAction .= "Failed updating user-group mapping [$username, from $current_groupname to $groupname, db error]: ";
                    }
                }
            }
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    if (empty($username) || empty($current_groupname)) {
        $failureMsg = "the user-group you have specified is empty or invalid";
        $logAction .= "Failed updating user-group [empty or invalid user-group] on page: ";
    } else {
        // retrieve mapping from database
        $sql = sprintf("SELECT username, groupname, priority FROM %s WHERE username='%s' AND groupname='%s'",
                       $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username),
                       $dbSocket->escapeSimple($current_groupname));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        list($this_username, $this_groupname, $this_priority) = $res->fetchRow();
    }

    include('../common/includes/db_close.php');


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

    if (!empty($username) && !empty($current_groupname)) {
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
                                        "tooltipText" => t('Tooltip','groupTooltip')
                                     );

        $input_descriptors0[] = array(
                                        "id" => "priority",
                                        "name" => "priority",
                                        "caption" => t('all','Priority'),
                                        "type" => "number",
                                        "min" => "0",
                                        "value" => $this_priority,
                                     );

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

    }

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
