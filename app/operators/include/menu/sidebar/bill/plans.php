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

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/bill/plans.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}

global $planName;

include_once("include/management/populate_selectbox.php");
$menu_planNames = get_plans();

// define descriptors
$descriptors1 = array();

$descriptors1[] = array( 'type' => 'link', 'label' => t('button','NewPlan'), 'href' =>'bill-plans-new.php',
                         'icon' => 'plus-circle-fill', );

if (count($menu_planNames) > 0) {
    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','ListPlans'), 'href' => 'bill-plans-list.php',
                             'icon' => 'list', );

    $components = array();
    $components[] = array(
                            "id" => 'random',
                            "name" => "planName",
                            "type" => "select",
                            "selected_value" => ((isset($planName)) ? $planName : ""),
                            "required" => true,
                            "options" => $menu_planNames,
                            "caption" => t('all','PlanName'),
                            "tooltipText" => t('Tooltip','BillingPlanName'),
                          );

    $descriptors1[] = array( 'type' => 'form', 'title' => t('button','EditPlan'), 'action' => 'bill-plans-edit.php', 'method' => 'GET',
                             'icon' => 'pencil-square', 'form_components' => $components, );

    $descriptors1[] = array( 'type' => 'link', 'label' => t('button','RemovePlan'), 'href' => 'bill-plans-del.php',
                             'icon' => 'x-circle-fill', );
}

$sections = array();
$sections[] = array( 'title' => 'Plans Management', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'Management',
                'sections' => $sections,
             );
