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

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");
    include_once("include/management/populate_selectbox.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    require_once __DIR__ . '/library/huntgroup_pages_pdo.php';
    $arr=$valid_huntgroups=array();
    $is_post=($_SERVER['REQUEST_METHOD'] ?? '')==='POST';
    if ($is_post) {
        if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) || !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg='CSRF token error';
        } else {
            try {
                $limit=(int)ini_get('max_input_vars');
                if ($limit>0 && count($_POST,COUNT_RECURSIVE)>=$limit) { throw new InvalidArgumentException('Truncated Huntgroup selection'); }
                $ids=dalo_hunt_selection($_POST['item'] ?? null);
                $arr=array_map(function ($id) { return 'huntgroup-' . $id; },$ids);
                $pdo=dalo_pdo_connect($configValues,$_SESSION['location_name'] ?? 'default');
                $deleted=dalo_hunt_delete($pdo,$configValues,$ids);
                $successMsg=sprintf('Deleted %d huntgroup(s)',$deleted);
                $logAction.='Deleted Huntgroup selection on page: ';
            } catch (Throwable $e) { $failureMsg='Unable to delete Huntgroup selection'; }
        }
    } elseif (isset($_GET['item'])) {
        try { $ids=dalo_hunt_selection($_GET['item']); $arr=array_map(function ($id) { return 'huntgroup-' . $id; },$ids); }
        catch (Throwable $e) { $failureMsg='Invalid Huntgroup selection'; }
    }
    // Independent PDO selector, after the completed write transaction.
    try {
        $options_pdo=dalo_pdo_connect($configValues,$_SESSION['location_name'] ?? 'default');
        $valid_huntgroups=dalo_hunt_options($options_pdo,$configValues);
        $arr=array_values(array_intersect($arr,array_keys($valid_huntgroups)));
    }
    catch (Throwable $e) { $failureMsg='Unable to load Huntgroup options'; }

    // print HTML prologue
    $title = t('Intro','mngradhuntdel.php');
    $help = t('helpPage','mngradhuntdel');

    print_html_prologue($title, $langCode);

    


    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    if (!isset($successMsg)) {

        $options = $valid_huntgroups;

        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        'name' => 'item[]',
                                        'id' => 'item',
                                        'type' => 'select',
                                        'caption' => sprintf("%s:%s (%s)", t('all','HgIPHost'), t('all','HgPortId'), t('all','HgGroupName')),
                                        'options' => $options,
                                        'multiple' => true,
                                        'selected_value' => ((count($arr) > 0) ? $arr : ""),
                                        'size' => 5,
                                     );

        $fieldset0_descriptor = array(
                                        "title" => t('title','HGInfo'),
                                        "disabled" => (count($options) == 0)
                                     );

        open_form();

        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        $input_descriptors1 = array();
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

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_form();

    }

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
