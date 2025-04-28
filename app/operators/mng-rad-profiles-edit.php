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

    include implode(DIRECTORY_SEPARATOR, [ __DIR__, 'library', 'checklogin.php' ]);
    $operator = $_SESSION['operator_user'];

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);


    // process the profile name here for presentation purpose
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $profile_name = (array_key_exists('profile_name', $_POST) && !empty(str_replace("%", "", trim($_POST['profile_name']))))
                      ? str_replace("%", "", trim($_POST['profile_name'])) : "";
    } else {
        $profile_name = (array_key_exists('profile_name', $_REQUEST) && !empty(str_replace("%", "", trim($_REQUEST['profile_name']))))
                      ? str_replace("%", "", trim($_REQUEST['profile_name'])) : "";
    }


    // we check if the profile name is valid
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'populate_selectbox.php' ]);
    $groups = array_keys(get_groups());

    $exists = in_array($profile_name, $groups);

    if (!$exists) {
        // we empty the profile name if it does not exist
        $profile_name = "";
    }

    $profile_name_enc = (!empty($profile_name)) ? htmlspecialchars($profile_name, ENT_QUOTES, 'UTF-8') : "";


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {

            if (empty($profile_name)) {
                $failureMsg = "You have specified an empty or invalid profile name";
                $logAction .= "Failed updating profile (possible empty or invalid profile name) on page: ";
            } else {
                include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);
                include("library/attributes.php");
                $skipList = array( "profile_name", "submit", "csrf_token" );
                $count = handleAttributes($dbSocket, $profile_name, $skipList, false, 'group');
                include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);

                $successMsg = "Updated attributes for: <b> $profile_name_enc </b>";
                $logAction .= "Successfully updates attributes for profile [$profile_name] on page:";
            }

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    function print_edit_form($dbSocket, $profile_name, $table_name, $no_attributes_message) {
        global $configValues, $logDebugSQL;

        $sql = sprintf("SELECT rad.attribute, rad.op, rad.value, dd.type, dd.recommendedTooltip, rad.id
                      FROM %s AS rad LEFT JOIN %s AS dd ON rad.attribute = dd.attribute AND dd.value IS NULL
                     WHERE rad.groupname='%s'", $table_name,
                                                $configValues['CONFIG_DB_TBL_DALODICTIONARY'],
                                                $dbSocket->escapeSimple($profile_name));

        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        $numrows = $res->numRows();

        echo '<div class="container">';

        if ($numrows > 0) {

            while ($row = $res->fetchRow()) {

                foreach ($row as $i => $v) {
                    $row[$i] = htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
                }

                $id = intval($row[5]);
                $id__attribute = sprintf('%d__%s', $id, $row[0]);
                $name = sprintf('editValues%s[]', $editCounter);

                $type = ((preg_match("/-Password$/", $row[0]) === 1) && (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) == "yes"))
                      ? "password" : "text";

                $onclick = sprintf("location.href='mng-rad-profiles-del.php?profile_name=%s&id=%d&tablename=%s'",
                                   urlencode(htmlspecialchars($profile_name, ENT_QUOTES, 'UTF-8')), $id, $table_name);

                $descriptor = array( 'onclick' => $onclick, 'attribute' => $row[0], 'select_name' => $name, 'selected_option' => $row[1],
                                     'id__attribute' => $id__attribute, 'type' => $type, 'value' => $row[2], 'name' => $name,
                                     'attr_type' => $row[3], 'attr_desc' => $row[4], 'table' => $table_name);

                print_edit_attribute($descriptor);

                // we increment the counter for the html elements of the edit attributes
                $editCounter++;
            }

            echo '<small class="mt-4 d-block">'
               . 'Notice that for supported password-like attributes, you can just specify a plaintext value. '
               . 'The system will take care of correctly hashing it.'
               . '</small>';

        } else {
            $msg = htmlspecialchars($no_attributes_message, ENT_QUOTES, 'UTF-8');

            echo <<<HTML
            <div class="alert alert-info d-flex align-items-center" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <div>{$msg}</div>
            </div>
            HTML;
        }

        echo '</div><!-- .container -->';
    }

    // print HTML prologue
    $extra_css = array();

    $extra_js = array(
        "static/js/ajax.js",
        "static/js/dynamic_attributes.js",
        "static/js/ajaxGeneric.js",
    );

    $title = t('Intro','mngradprofilesedit.php');
    $help = t('helpPage','mngradprofilesedit');

    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    if (!empty($profile_name_enc)) {
        $title .= " :: $profile_name_enc";
    }

    print_title_and_help($title, $help);


    if (empty($profile_name)) {
        $failureMsg = "You have specified an empty or invalid profile name";
        $logAction .= "Failed updating profile (possible empty or invalid profile name) on page: ";
    }

    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);

    if (!empty($profile_name)) {

        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );
        $input_descriptors0[] = array(
                                        "name" => "profile_name",
                                        "type" => "hidden",
                                        "value" => $profile_name,
                                     );

        $input_descriptors0[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                     );


        // set navbar stuff
        $navkeys = array( 'RADIUSCheck', 'RADIUSReply', 'Attributes' );

        // print navbar controls
        print_tab_header($navkeys);


        include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);
        include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_common.php' ]);

        $fieldset0_descriptor = array(
                                        "title" => t('title','RADIUSCheck'),
                                     );

        open_form();

        // open tab wrapper
        open_tab_wrapper();

        // tab 0
        open_tab($navkeys, 0, true);

        open_fieldset($fieldset0_descriptor);

        print_edit_form($dbSocket, $profile_name, $configValues['CONFIG_DB_TBL_RADGROUPCHECK'], t('messages','noCheckAttributesForGroup'));

        close_fieldset();

        close_tab($navkeys, 0);


        // tab 1
        open_tab($navkeys, 1);

        $fieldset1_descriptor = array(
                                        "title" => t('title','RADIUSReply'),
                                     );

        open_fieldset($fieldset1_descriptor);

        print_edit_form($dbSocket, $profile_name, $configValues['CONFIG_DB_TBL_RADGROUPREPLY'], t('messages','noReplyAttributesForGroup'));

        close_fieldset();

        close_tab($navkeys, 1);

        include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);

        // tab 2
        open_tab($navkeys, 2);

        include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'attributes.php' ]);

        close_tab($navkeys, 2);

        // close tab wrapper
        close_tab_wrapper();

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_form();

    }

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    print_footer_and_html_epilogue();
