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

    
    require_once __DIR__ . '/library/group_profiles_pdo.php';
    $pdo = null;
    $options = array();
    $options_format = "%s: [%s %s %s]";
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_string($_POST['csrf_token'] ?? null) || !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
        } else {
            try {
                $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                $count = dalo_group_delete($pdo, $configValues, 'CONFIG_DB_TBL_RADGROUPREPLY', $_POST['record_id'] ?? null);
                $successMsg = sprintf('Deleted %d groupreply record(s)', $count);
                $logAction = 'Successfully deleted group attributes on page: ';
            } catch (Throwable $error) {
                $failureMsg = 'Unable to delete group attributes: invalid or stale selection or database operation failed';
                $logAction = 'Group attribute deletion failed on page: ';
            } finally { $pdo = null; }
        }
    }
    try {
        $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        $table = dalo_group_table($configValues, 'CONFIG_DB_TBL_RADGROUPREPLY');
        $rows = dalo_group_query($pdo, "SELECT id,groupname,attribute,op,value FROM $table ORDER BY groupname,attribute DESC")->fetchAll(PDO::FETCH_NUM);
        foreach ($rows as $row) {
            list($id,$groupname,$attribute,$op,$value) = $row;
            $options['record-' . $id] = sprintf($options_format, $groupname,$attribute,$op,$value);
        }
    } catch (Throwable $error) {
        $failureMsg = 'Unable to load group attributes; please retry';
    } finally { $pdo = null; }

    include_once("lang/main.php");
    
    include("../common/includes/layout.php");

    // print HTML prologue
    $title = t('Intro','mngradgroupreplydel.php');
    $help = t('helpPage','mngradgroupreplydel');
    
    print_html_prologue($title, $langCode);

    


    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');

    $input_descriptors1 = array();

    $caption = sprintf($options_format, t('all','Groupname'), t('all','Attribute'), "op", t('all','Value'));
    $input_descriptors1[] = array(
                                    'name' => 'record_id[]',
                                    'id' => 'record_id',
                                    'type' => 'select',
                                    'caption' => $caption,
                                    'options' => $options,
                                    'multiple' => true,
                                    'size' => 5,
                                 );

    $input_descriptors1[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );

    $input_descriptors1[] = array(
                                    'type' => 'submit',
                                    'name' => 'submit',
                                    'value' => t('buttons','apply')
                                 );
                                 
    $fieldset1_descriptor = array(
                                    "title" => t('title','GroupInfo'),
                                    "disabled" => (count($options) == 0)
                                 );

    open_form();
    
    open_fieldset($fieldset1_descriptor);

    foreach ($input_descriptors1 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
    
    close_fieldset();
    
    close_form();

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
