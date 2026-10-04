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
 *             Filippo Maria Del Prete <filippo.delprete@gmail.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */
 
    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');
    require_once('../common/includes/pdo_connection.php');
    require_once('library/plan_delete.php');
    
    // init logging variables
    $logAction = "";
    $logDebugSQL = "";
    $log = "visited page: ";

    require_once('library/catalog_reads_pdo.php');
    $valid_planNames = array(); $catalog_pdo = null;
    try {
        $catalog_pdo = dalo_catalog_read_open($configValues);
        $valid_planNames = dalo_catalog_read_options($catalog_pdo, $configValues, 'CONFIG_DB_TBL_DALOBILLINGPLANS', 'planName', true);
    } catch (Throwable $error) {
        dalo_catalog_read_failure($error);
    } finally { $catalog_pdo = null; }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) &&
            dalo_check_csrf_token($_POST['csrf_token'])) {
            $planName = array();
            try {
                $planName = dalo_plan_names_from_post($_POST['planName'] ?? array());
                $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                list($deletedPlans, $deletedMappings) = dalo_delete_billing_plans($pdo, $configValues, $planName);
                $successMsg = sprintf('Deleted %d plan(s) and %d profile association(s)',
                                      $deletedPlans, $deletedMappings);
                $logAction .= "$successMsg on page: ";
            } catch (InvalidArgumentException $error) {
                $failureMsg = 'Empty or invalid plan name(s)';
                $logAction .= sprintf('Failed deleting plan(s) [%s] on page: ', $failureMsg);
            } catch (DomainException $error) {
                $failureMsg = 'Selected plan no longer exists';
                $logAction .= sprintf('Failed deleting plan(s) [%s] on page: ', $failureMsg);
            } catch (Throwable $error) {
                $failureMsg = 'Failed to delete plan(s) and profile associations';
                $logAction .= sprintf('Failed deleting plan(s) [%s] on page: ', $failureMsg);
            }
        } else {
            $failureMsg = 'CSRF token error';
            $logAction .= "$failureMsg on page: ";
        }
    } else {
        // Preserve the exact plan name shown on the list, including percent signs.
        $planName = isset($_GET['planName']) && is_string($_GET['planName'])
                  ? trim($_GET['planName']) : '';
        if ($planName === '' || !in_array($planName, $valid_planNames, true)) {
            $planName = '';
        }
    }



    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // print HTML prologue
    $title = t('Intro','billplansdel.php');
    $help = t('helpPage','billplansdel');
    
    print_html_prologue($title, $langCode);

    
    
    if (!empty($planName) && !is_array($planName)) {
        $title .= " :: " . htmlspecialchars($planName, ENT_QUOTES, 'UTF-8');
    }
    

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    if (!isset($successMsg)) {

        $input_descriptors1 = array();

        $input_descriptors1[] = array(
                                    'name' => 'planName[]',
                                    'id' => 'planName',
                                    'type' => 'select',
                                    'caption' => t('all','PlanName'),
                                    'options' => $valid_planNames,
                                    'multiple' => true,
                                    'size' => 5,
                                    'selected_value' => $planName
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
                                        "title" => t('title','PlanRemoval'),
                                        "disabled" => (count($valid_planNames) == 0)
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
