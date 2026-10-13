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

    include_once('../common/includes/config_read.php');
    include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // init logging variables
    $logAction = "";
    $logDebugSQL = "";
    $log = "visited page: ";

    require_once __DIR__ . '/library/hotspot_pages_pdo.php';
    $field_name='name';$valid_values=$values=array();$success=false;
    if (($_SERVER['REQUEST_METHOD'] ?? '')==='POST') {
        if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) || !dalo_check_csrf_token($_POST['csrf_token'])) { $failureMsg='CSRF token error'; }
        else {
            try {
                $limit=(int)ini_get('max_input_vars');
                if ($limit>0 && count($_POST,COUNT_RECURSIVE)>=$limit) { throw new InvalidArgumentException('Truncated hotspot selection'); }
                $values=dalo_hotspot_selection($_POST['name'] ?? null);
                $pdo=dalo_pdo_connect($configValues,$_SESSION['location_name'] ?? 'default');
                $deleted=dalo_hotspot_delete($pdo,$configValues,$values);
                $escaped=array_map(function ($v) { return htmlspecialchars($v,ENT_QUOTES,'UTF-8'); },$deleted);
                $successMsg='Deleted hotspot(s): <strong>' . implode(', ',$escaped) . '</strong>';$success=true;
                $logAction.='Successfully deleted hotspots on page: ';
            } catch (Throwable $e) { $failureMsg='Unable to delete hotspot selection'; }
        }
    } elseif (isset($_GET['name'])) {
        try { $values=dalo_hotspot_selection($_GET['name']); }
        catch (Throwable $e) { $failureMsg='Invalid hotspot selection'; }
    }
    try {
        $options_pdo=dalo_pdo_connect($configValues,$_SESSION['location_name'] ?? 'default');
        $valid_values=dalo_hotspot_options($options_pdo,$configValues);
    } catch (Throwable $e) { $failureMsg='Unable to load hotspot options'; }

    // print HTML prologue
    $title = t('Intro','mnghsdel.php');
    $help = t('helpPage','mnghsdel');

    print_html_prologue($title, $langCode);

     print_title_and_help($title, $help);

    if (isset($failureMsg) || isset($successMsg)) {
        include_once('include/management/actionMessages.php');
    }

    if (!$success) {
        $options = $valid_values;

        $input_descriptors1 = array();

        $input_descriptors1[] = array(
                                        'name' => $field_name . "[]",
                                        'id' => $field_name,
                                        'type' => 'select',
                                        'caption' => t('all','HotSpotName'),
                                        'options' => $options,
                                        'multiple' => true,
                                        'selected_value' => $values,
                                        'size' => 5
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
                                        "title" => t('title','HotspotRemoval'),
                                        "disabled" => (count($options) == 0)
                                     );

        open_form();

        open_fieldset($fieldset1_descriptor);

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        close_form();

    }

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
