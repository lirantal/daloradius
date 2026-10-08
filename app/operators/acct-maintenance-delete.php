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
 
    include_once('../common/includes/config_read.php');
    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');
    
    // init logging variables
    $logAction = "";
    $logDebugSQL = "";
    $log = "visited page: ";

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");
    
    
    require_once __DIR__ . '/library/accounting_advanced_pdo.php';
    unset($_SESSION['reportExport'], $_SESSION['reportTable'], $_SESSION['reportQuery'], $_SESSION['reportType']);
    $username = $startdate = $enddate = '';
    $mindate = $maxdate = '';
    $valid_usernames = array();
    $accountingPDO = null;
    try {
        $accountingPDO = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        $table = dalo_export_table($configValues, 'CONFIG_DB_TBL_RADACCT');
        list($mindate, $maxdate) = dalo_accounting_execute($accountingPDO,
            "SELECT DATE(MIN(acctstarttime)), DATE(MAX(acctstarttime)) FROM $table")->fetch(PDO::FETCH_NUM);
        $valid_usernames = dalo_accounting_execute($accountingPDO,
            "SELECT DISTINCT(username) FROM $table ORDER BY username ASC")->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $exception) { dalo_accounting_failure($exception); }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($failureMsg)) {
        if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) ||
            !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
        } else {
            try {
                $username = dalo_accounting_scalar($_POST, 'username');
                $startdate = dalo_accounting_date($_POST, 'startdate');
                $enddate = dalo_accounting_date($_POST, 'enddate');
                $required_fields = array();
                if ($username === '' || !in_array($username, $valid_usernames, true)) { $required_fields[] = t('all','Username'); }
                if ($startdate === '') { $required_fields[] = t('all','StartingDate'); }
                if ($enddate === '') { $required_fields[] = t('all','EndingDate'); }
                if ($startdate !== '' && $enddate !== '' && $startdate > $enddate) {
                    $required_fields[] = t('all','StartingDate'); $required_fields[] = t('all','EndingDate');
                }
                if ($required_fields) {
                    $failureMsg = sprintf('Empty or invalid required field(s) [%s]', implode(', ', array_unique($required_fields)));
                } else {
                    dalo_advanced_purge($accountingPDO, $configValues, $username, $startdate, $enddate);
                    $successMsg = sprintf('Deleted accounting records for user %s [period: %s - %s]',
                        htmlspecialchars($username, ENT_QUOTES, 'UTF-8'), $startdate, $enddate);
                }
            } catch (Throwable $exception) {
                $failureMsg = 'Unable to delete accounting records';
                error_log('Accounting purge failed (' . get_class($exception) . ')');
            }
        }
        $logAction .= ($failureMsg ?? $successMsg ?? '') . ' on page: ';
    }
    unset($accountingPDO);

    // print HTML prologue
    $title = t('Intro','acctmaintenancedelete.php');
    $help = t('helpPage','acctmaintenancedelete');
    
    print_html_prologue($title, $langCode);

    
    

    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    $options = $valid_usernames;
    array_unshift($options , '');
    
    $input_descriptors0 = array();

    $input_descriptors0[] = array(
                                    'name' => 'username',
                                    'type' => 'select',
                                    'caption' => t('all','Username'),
                                    'options' => $options,
                                    'selected_value' => $username
                                 );

    $input_descriptors0[] = array(
                                    'name' => 'startdate',
                                    'caption' => t('all','StartingDate'),
                                    'type' => 'date',
                                    'value' => $startdate,
                                    'min' => $mindate,
                                    'max' => $maxdate,
                                 );
                                 
    $input_descriptors0[] = array(
                                    'name' => 'enddate',
                                    'caption' => t('all','EndingDate'),
                                    'type' => 'date',
                                    'value' => $enddate,
                                    'min' => $mindate,
                                    'max' => $maxdate,
                                 );
                                 
    $input_descriptors0[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );
    
    $input_descriptors0[] = array(
                                    "type" => "submit",
                                    "name" => "submit",
                                    "value" => t('buttons','apply')
                                  );
    
    $fieldset0_descriptor = array(
                                    "title" => t('title','DeleteRecords'),
                                    "disabled" => (count($valid_usernames) == 0)
                                 );
                                 
    open_form();
    
    open_fieldset($fieldset0_descriptor);

    foreach ($input_descriptors0 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
    
    close_fieldset();
    
    close_form();

    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
    
?>
