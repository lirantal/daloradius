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

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include_once('../common/includes/config_read.php');
    include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";


    require_once __DIR__ . '/library/hotspot_pages_pdo.php';
    $name=$name_enc=$edit_hotspotname='';$exists=false;
    try {
        $is_post=($_SERVER['REQUEST_METHOD'] ?? '')==='POST';
        $name=dalo_hotspot_name(($is_post ? $_POST : $_GET)['name'] ?? null);
        $pdo=dalo_pdo_connect($configValues,$_SESSION['location_name'] ?? 'default');
        $table=dalo_hotspot_table($configValues);
        if ($is_post) {
            if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) || !dalo_check_csrf_token($_POST['csrf_token'])) { $failureMsg='CSRF token error'; }
            else {
                $fields=dalo_hotspot_fields($_POST);
                if (!dalo_hotspot_save($pdo,$configValues,$fields,$operator,true)) { $failureMsg='The MAC/IP address you have inserted is already used by another hotspot'; }
                else { $successMsg='Updated hotspot: <strong>' . htmlspecialchars($name,ENT_QUOTES,'UTF-8') . '</strong>'; $logAction.='Successfully updated hotspot on page: '; }
            }
        }
        $row=dalo_hotspot_read($pdo,$table,$name);
        if (!$row) { throw new RuntimeException('Unknown hotspot'); }
        $exists=true;
        foreach (dalo_hotspot_map() as $column=>$control) { $$control=$row[$column] ?? ''; }
        foreach (array('id','creationdate','creationby','updatedate','updateby') as $key) { $$key=$row[$key] ?? ''; }
        $name_enc=htmlspecialchars($name,ENT_QUOTES,'UTF-8');
        $edit_hotspotname=$name_enc;
    } catch (Throwable $e) {
        $name=$name_enc=$edit_hotspotname='';
        $failureMsg=isset($successMsg) ? 'Hotspot saved; unable to reload form' : 'Unable to load or update hotspot';
    }

    // print HTML prologue
    $title = t('Intro','mnghsedit.php');
    $help = t('helpPage','mnghsedit');

    print_html_prologue($title, $langCode);

    if ($name_enc!=='') {
        $title .= " :: $name_enc";
    }

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    if ($exists) {

        // set form component descriptors
        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        "name" => "name_presentation",
                                        "caption" => t('all','HotSpotName'),
                                        "type" => "text",
                                        "value" => ((isset($name)) ? $name : ""),
                                        "tooltipText" => t('Tooltip','hotspotNameTooltip'),
                                        "disabled" => true
                                     );

        $input_descriptors0[] = array(
                                        "name" => "macaddress",
                                        "caption" => t('all','MACAddress'),
                                        "type" => "text",
                                        "value" => ((isset($macaddress)) ? $macaddress : ""),
                                        "tooltipText" => t('Tooltip','hotspotMacaddressTooltip')
                                     );

        $input_descriptors0[] = array(
                                        "name" => "geocode",
                                        "caption" => t('all','Geocode'),
                                        "type" => "text",
                                        "value" => ((isset($geocode)) ? $geocode : ""),
                                        "tooltipText" => t('Tooltip','geocodeTooltip')
                                     );

        $input_descriptors1 = array();
        $input_descriptors1[] = array(
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                        "name" => "csrf_token"
                                     );

        $input_descriptors1[] = array(
                                        "type" => "hidden",
                                        "value" => $name,
                                        "name" => "name"
                                     );

        $input_descriptors1[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                  );

        // set navbar stuff
        $navkeys = array( 'HotspotInfo', 'ContactInfo', );

        // print navbar controls
        print_tab_header($navkeys);

        // open form
        open_form();

        // open tab wrapper
        open_tab_wrapper();

        // open first tab (shown)
        open_tab($navkeys, 0, true);

        // open a fieldset
        $fieldset0_descriptor = array(
                                        "title" => t('title','HotspotInfo'),
                                     );

        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        close_tab($navkeys, 0);

        // open second tab
        open_tab($navkeys, 1);
        include_once('include/management/contactinfo.php');
        close_tab($navkeys, 1);

        // close tab wrapper
        close_tab_wrapper();

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_form();

    }

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
