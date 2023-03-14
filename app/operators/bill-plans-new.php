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

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");
    include("include/management/functions.php");
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    $planName = (array_key_exists('planName', $_POST) && !empty(trim($_POST['planName']))) ? trim($_POST['planName']) : "";
    $planName_enc = (!empty($planName)) ? htmlspecialchars($planName, ENT_QUOTES, 'UTF-8') : "";
    
    $planId = (array_key_exists('planId', $_POST) && !empty(trim($_POST['planId']))) ? trim($_POST['planId']) : "";
    $planType = (array_key_exists('planType', $_POST) && !empty(trim($_POST['planType']))) ? trim($_POST['planType']) : "";
    
    $planTimeType = (array_key_exists('planTimeType', $_POST) && !empty(trim($_POST['planTimeType'])) &&
                     in_array(trim($_POST['planTimeType']), $valid_planTimeTypes))
                  ? trim($_POST['planTimeType']) : $valid_planTimeTypes[0];
    
    $planTimeBank = (array_key_exists('planTimeBank', $_POST) && !empty(trim($_POST['planTimeBank']))) ? trim($_POST['planTimeBank']) : "";
    $planTimeRefillCost = (array_key_exists('planTimeRefillCost', $_POST) && !empty(trim($_POST['planTimeRefillCost']))) ? trim($_POST['planTimeRefillCost']) : "";
    $planBandwidthUp = (array_key_exists('planBandwidthUp', $_POST) && !empty(trim($_POST['planBandwidthUp']))) ? trim($_POST['planBandwidthUp']) : "";
    $planBandwidthDown = (array_key_exists('planBandwidthDown', $_POST) && !empty(trim($_POST['planBandwidthDown']))) ? trim($_POST['planBandwidthDown']) : "";
    $planTrafficTotal = (array_key_exists('planTrafficTotal', $_POST) && !empty(trim($_POST['planTrafficTotal']))) ? trim($_POST['planTrafficTotal']) : "";
    $planTrafficDown = (array_key_exists('planTrafficDown', $_POST) && !empty(trim($_POST['planTrafficDown']))) ? trim($_POST['planTrafficDown']) : "";
    $planTrafficUp = (array_key_exists('planTrafficUp', $_POST) && !empty(trim($_POST['planTrafficUp']))) ? trim($_POST['planTrafficUp']) : "";
    $planTrafficRefillCost = (array_key_exists('planTrafficRefillCost', $_POST) && !empty(trim($_POST['planTrafficRefillCost']))) ? trim($_POST['planTrafficRefillCost']) : "";
    
    $planRecurring = (array_key_exists('planRecurring', $_POST) && !empty(trim($_POST['planRecurring'])) &&
                      in_array(strtolower(trim($_POST['planRecurring'])), array("yes", "no")))
                   ? strtolower(trim($_POST['planRecurring'])) : "yes";

    $planRecurringPeriod = (array_key_exists('planRecurringPeriod', $_POST) && !empty(trim($_POST['planRecurringPeriod'])) &&
                            in_array(trim($_POST['planRecurringPeriod']), $valid_planRecurringPeriods))
                         ? trim($_POST['planRecurringPeriod']) : $valid_planRecurringPeriods[0];

    $planRecurringBillingSchedule = (array_key_exists('planRecurringBillingSchedule', $_POST) &&
                                     !empty(trim($_POST['planRecurringBillingSchedule'])) &&
                                     in_array(trim($_POST['planRecurringBillingSchedule']), $valid_planRecurringBillingSchedules))
                                  ? trim($_POST['planRecurringBillingSchedule']) : $valid_planRecurringBillingSchedules[0];

    $planActive = (array_key_exists('planActive', $_POST) && !empty(trim($_POST['planActive'])) &&
                   in_array(strtolower(trim($_POST['planActive'])), array("yes", "no")))
                ? strtolower(trim($_POST['planActive'])) : "yes";

    $planCost = (array_key_exists('planCost', $_POST) && !empty(trim($_POST['planCost']))) ? trim($_POST['planCost']) : "";
    $planSetupCost = (array_key_exists('planSetupCost', $_POST) && !empty(trim($_POST['planSetupCost']))) ? trim($_POST['planSetupCost']) : "";
    $planTax = (array_key_exists('planTax', $_POST) && !empty(trim($_POST['planTax']))) ? trim($_POST['planTax']) : "";
    $planCurrency = (array_key_exists('planCurrency', $_POST) && !empty(trim($_POST['planCurrency']))) ? trim($_POST['planCurrency']) : "";
    $planGroup = (array_key_exists('planGroup', $_POST) && !empty(trim($_POST['planGroup']))) ? trim($_POST['planGroup']) : "";
    $groups = (array_key_exists('groups', $_POST) && isset($_POST['groups'])) ? $_POST['groups'] : array();


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
    
            if (empty($planName)) {                
                // required/invalid
                $failureMsg = sprintf("The required field '%s' is empty or invalid", t('all','PlanName'));
                $logAction .= "$failureMsg on page: ";
            } else {
            
                include('../common/includes/db_open.php');
            
                $sql = sprintf("SELECT COUNT(DISTINCT(planName)) FROM %s WHERE planName='%s'",
                               $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'], $dbSocket->escapeSimple($planName));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
            
                $exists = intval($res->fetchrow()[0]) > 0;
                
                if ($exists) {
                    // already exists                    
                    $failureMsg = sprintf("A plan with the chosen '%s' already exists in the database", t('all','PlanName'));
                    $logAction .= "$failureMsg on page: ";
                } else {
                    // required later
                    $currDate = date('Y-m-d H:i:s');
                    $currBy = $operator;
                    
                    $sql = sprintf("INSERT INTO %s (id, planName, planId, planType, planTimeBank, planTimeType,
                                                    planTimeRefillCost, planBandwidthUp, planBandwidthDown, planTrafficTotal,
                                                    planTrafficUp, planTrafficDown, planTrafficRefillCost, planRecurring,
                                                    planRecurringPeriod, planRecurringBillingSchedule, planCost,
                                                    planSetupCost, planTax, planCurrency, planGroup, planActive,
                                                    creationdate, creationby, updatedate, updateby)
                                            VALUES (0, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                                                    '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', NULL, NULL)",
                                   $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'], $dbSocket->escapeSimple($planName),
                                   $dbSocket->escapeSimple($planId), $dbSocket->escapeSimple($planType),
                                   $dbSocket->escapeSimple($planTimeBank), $dbSocket->escapeSimple($planTimeType),
                                   $dbSocket->escapeSimple($planTimeRefillCost), $dbSocket->escapeSimple($planBandwidthUp),
                                   $dbSocket->escapeSimple($planBandwidthDown), $dbSocket->escapeSimple($planTrafficTotal),
                                   $dbSocket->escapeSimple($planTrafficUp), $dbSocket->escapeSimple($planTrafficDown),
                                   $dbSocket->escapeSimple($planTrafficRefillCost), $dbSocket->escapeSimple($planRecurring),
                                   $dbSocket->escapeSimple($planRecurringPeriod), $dbSocket->escapeSimple($planRecurringBillingSchedule),
                                   $dbSocket->escapeSimple($planCost), $dbSocket->escapeSimple($planSetupCost),
                                   $dbSocket->escapeSimple($planTax), $dbSocket->escapeSimple($planCurrency),
                                   $dbSocket->escapeSimple($planGroup), $dbSocket->escapeSimple($planActive), $currDate, $currBy);
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";
                    
                    // add the profiles associated with this billing plan to the
                    // billing_plans_profiles table for later on
                    $groupsCount = insert_multiple_plan_group_mappings($dbSocket, $planName, $groups);
                    
                    if (!DB::isError($res)) {
                        $format = "A new %s named %s has been successfully added to database. %d %s have been associated to this %s";
                        $successMsg = sprintf($format . ' [<a href="bill-plans-edit.php?planName=%s" title="Edit">Edit</a>]', t('all','PlanName'),
                                              $planName_enc, $groupsCount, t('title','Profiles'), t('all','PlanName'), urlencode($planName_enc));
                        $logAction .= sprintf("$format on page: ", t('all','PlanName'), $groupsCount, t('title','Profiles'), t('all','PlanName'));
                    } else {
                        $failureMsg = "Failed to insert a new plan to database";
                        $logAction .= "$failureMsg on page: ";
                    }

                }
                
                include('../common/includes/db_close.php');
            }
    
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    
    // print HTML prologue
    $extra_css = array();
    
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/dynamic_attributes.js",
        "static/js/ajaxGeneric.js",
    );

    $title = t('Intro','billplansnew.php');
    $help = t('helpPage','billplansnew');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    if (!isset($successMsg)) {
    
        // descriptors 0
        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                    'name' => 'planName',
                                    'type' => 'text',
                                    'caption' => t('all','PlanName'),
                                    'value' => $planName,
                                    'required' => true,
                                    'tooltipText' => t('Tooltip','planNameTooltip'),
                                 );
                                 
        $input_descriptors0[] = array(
                                    'name' => 'planId',
                                    'type' => 'text',
                                    'caption' => t('all','PlanId'),
                                    'value' => $planId,
                                    'tooltipText' => t('Tooltip','planIdTooltip'),
                                 );
                                 
        $input_descriptors0[] = array(
                                    'name' => 'planType',
                                    'type' => 'select',
                                    'caption' => t('all','PlanType'),
                                    'options' => $valid_planTypes,
                                    'selected_value' => $planType,
                                    'tooltipText' => t('Tooltip','planTimeTypeTooltip'),
                                 );
                                 
        $input_descriptors0[] = array(
                                        "type" => "select",
                                        "options" => array( "yes", "no" ),
                                        "caption" => t('all','PlanRecurring'),
                                        "name" => "planRecurring",
                                        "selected_value" => $planRecurring,
                                        "tooltipText" => t('Tooltip','planRecurringTooltip'),
                                     );
                                     
        $input_descriptors0[] = array(
                                        "type" => "select",
                                        "options" => array( "yes", "no" ),
                                        "caption" => t('all','PlanActive'),
                                        "name" => "planRecurring",
                                        "selected_value" => $planActive,
                                     );

        $input_descriptors0[] = array(
                                    'name' => 'planRecurringPeriod',
                                    'type' => 'select',
                                    'caption' => t('all','PlanRecurringPeriod'),
                                    'options' => $valid_planRecurringPeriods,
                                    'selected_value' => $planRecurringPeriod,
                                    'tooltipText' => t('Tooltip','planRecurringPeriodTooltip'),
                                 );

        $input_descriptors0[] = array(
                                    'name' => 'planRecurringBillingSchedule',
                                    'type' => 'select',
                                    'caption' => t('all','planRecurringBillingSchedule'),
                                    'options' => $valid_planRecurringBillingSchedules,
                                    'selected_value' => $planRecurringBillingSchedule,
                                    'tooltipText' => t('Tooltip','planRecurringBillingScheduleTooltip'),
                                 );

        $input_descriptors0[] = array(
                                    'name' => 'planCost',
                                    'type' => 'text',
                                    'caption' => t('all','PlanCost'),
                                    'value' => $planCost,
                                    'tooltipText' => t('Tooltip','planCostTooltip'),
                                 );
                                 
        $input_descriptors0[] = array(
                                    'name' => 'planSetupCost',
                                    'type' => 'text',
                                    'caption' => t('all','PlanSetupCost'),
                                    'value' => $planSetupCost,
                                    'tooltipText' => t('Tooltip','planSetupCostTooltip'),
                                 );
                                 
        $input_descriptors0[] = array(
                                    'name' => 'planTax',
                                    'type' => 'text',
                                    'caption' => t('all','PlanTax'),
                                    'value' => $planTax,
                                    'tooltipText' => t('Tooltip','planTaxTooltip'),
                                 );

        $input_descriptors0[] = array(
                                        "type" => "select",
                                        "options" => $valid_planCurrencys,
                                        "caption" => t('all','PlanCurrency'),
                                        "name" => "planCurrency",
                                        "selected_value" => $planCurrency,
                                        'tooltipText' => t('Tooltip','planCurrencyTooltip'),
                                     );

        // descriptors 1
        $input_descriptors1 = array();
        
        $input_descriptors1[] = array(
                                        "type" => "select",
                                        "options" => $valid_planTimeTypes,
                                        "caption" => t('all','PlanTimeType'),
                                        "name" => "planTimeType",
                                        "selected_value" => $planTimeType,
                                        "tooltipText" => t('Tooltip','planTimeTypeTooltip'),
                                     );

        $input_descriptors1[] = array(
                                    'name' => 'planTimeBank',
                                    'type' => 'text',
                                    'caption' => t('all','PlanTimeBank'),
                                    'value' => $planTimeBank,
                                    'tooltipText' => t('Tooltip','planTimeBankTooltip'),
                                 );
                                 
        $input_descriptors1[] = array(
                                    'name' => 'planTimeRefillCost',
                                    'type' => 'text',
                                    'caption' => t('all','PlanTimeRefillCost'),
                                    'value' => $planTimeRefillCost,
                                    'tooltipText' => t('Tooltip','planTimeRefillCostTooltip'),
                                 );

        // descriptors 2
        $input_descriptors2 = array();
        
        $input_descriptors2[] = array(
                                    'name' => 'planBandwidthUp',
                                    'type' => 'text',
                                    'caption' => t('all','PlanBandwidthUp'),
                                    'value' => $planBandwidthUp,
                                    'tooltipText' => t('Tooltip','planBandwidthUpTooltip'),
                                 );
                                 
        $input_descriptors2[] = array(
                                    'name' => 'planBandwidthDown',
                                    'type' => 'text',
                                    'caption' => t('all','PlanBandwidthDown'),
                                    'value' => $planBandwidthDown,
                                    'tooltipText' => t('Tooltip','planBandwidthDownTooltip'),
                                 );
                                 
        $input_descriptors2[] = array(
                                    'name' => 'planTrafficTotal',
                                    'type' => 'text',
                                    'caption' => t('all','PlanTrafficTotal'),
                                    'value' => $planTrafficTotal,
                                    'tooltipText' => t('Tooltip','planTrafficTotalTooltip'),
                                 );
                                 
        $input_descriptors2[] = array(
                                    'name' => 'planTrafficUp',
                                    'type' => 'text',
                                    'caption' => t('all','PlanTrafficUp'),
                                    'value' => $planTrafficUp,
                                    'tooltipText' => t('Tooltip','planTrafficUpTooltip'),
                                 );
                                 
        $input_descriptors2[] = array(
                                    'name' => 'planTrafficDown',
                                    'type' => 'text',
                                    'caption' => t('all','PlanTrafficDown'),
                                    'value' => $planTrafficDown,
                                    'tooltipText' => t('Tooltip','planTrafficDownTooltip'),
                                 );
                                 
        $input_descriptors2[] = array(
                                    'name' => 'planTrafficRefillCost',
                                    'type' => 'text',
                                    'caption' => t('all','PlanTrafficRefillCost'),
                                    'value' => $planTrafficRefillCost,
                                    'tooltipText' => t('Tooltip','planTrafficRefillCostTooltip'),
                                 );
        
        // descriptors 3
        $input_descriptors3 = array();
        
        include_once('include/management/populate_selectbox.php');
        $options = get_groups();
        array_unshift($options, '');
        $input_descriptors3[] = array(
                                        "type" =>"select",
                                        "name" => "groups[]",
                                        "id" => "groups",
                                        "caption" => t('all','Profile'),
                                        "options" => $options,
                                        "multiple" => true,
                                        "size" => 5,
                                        "selected_value" => ((isset($failureMsg)) ? $groups : ""),
                                        "tooltipText" => t('Tooltip','groupTooltip')
                                     );
        
        // set navbar stuff
        $navkeys = array( 'PlanInfo', 'TimeSettings', 'BandwidthSettings', 'Profiles', );

        // print navbar controls
        print_tab_header($navkeys);
        
        open_form();
    
        // open tab wrapper
        open_tab_wrapper();
    
        // tab 0
        open_tab($navkeys, 0, true);
    
        $fieldset0_descriptor = array(
                                    "title" => t('title','PlanInfo')
                                 );
    
        // fieldset 0
        open_fieldset($fieldset0_descriptor);
        
        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        close_tab($navkeys, 0);
        
        // tab 1
        open_tab($navkeys, 1);
        
        $fieldset1_descriptor = array(
                                    "title" => t('title','TimeSettings')
                                 );
        
        // fieldset 1
        open_fieldset($fieldset1_descriptor);
        
        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        close_tab($navkeys, 1);
        
        // tab 2
        open_tab($navkeys, 2);
        
        $fieldset2_descriptor = array(
                                    "title" => t('title','PlanInfo')
                                 );
        
        // fieldset 2
        open_fieldset($fieldset2_descriptor);
        
        foreach ($input_descriptors2 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        close_tab($navkeys, 2);
        
        // tab 3
        open_tab($navkeys, 3);
        
        $fieldset3_descriptor = array(
                                    "title" => t('title','Profiles')
                                 );
        
        // fieldset 3
        open_fieldset($fieldset3_descriptor);
        
        foreach ($input_descriptors3 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        close_tab($navkeys, 3);
        
        // close tab wrapper
        close_tab_wrapper();
        
        // descriptors 4
        $input_descriptors4 = array();
        
        $input_descriptors4[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );
        
        $input_descriptors4[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                      );
        
        foreach ($input_descriptors4 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_form();
    
    }
    
    print_back_to_previous_page();
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
    
?>
