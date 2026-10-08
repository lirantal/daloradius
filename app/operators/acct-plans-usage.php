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
 * Authors:	Liran Tal <liran@lirantal.com>
 *
 *********************************************************************************************************
 */

    include_once('../common/includes/config_read.php');
    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("include/management/functions.php");
    include("../common/includes/layout.php");

    require_once __DIR__ . '/library/accounting_advanced_pdo.php';
    unset($_SESSION['reportExport'], $_SESSION['reportTable'], $_SESSION['reportQuery'], $_SESSION['reportType']);
    try {
        dalo_accounting_validate_request($_GET);
        if (isset($_GET['planname'])) { dalo_accounting_scalar($_GET, 'planname'); }
    } catch (Throwable $exception) {
        http_response_code(400);
        exit('Invalid accounting filters');
    }
    if (isset($_REQUEST['page']) && !is_string($_REQUEST['page'])) { $_REQUEST['page'] = '1'; }
    $username = dalo_accounting_scalar($_GET, 'username');
	$username_enc = ($username !== '') ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    $planname = dalo_accounting_scalar($_GET, 'planname');
    $planname_enc = ($planname !== '') ? htmlspecialchars($planname, ENT_QUOTES, 'UTF-8') : "";
    
	// we validate starting and ending dates
    $date_default = date_range_default('month_to_date');

    $startdate = dalo_accounting_date($_GET, 'startdate', $date_default['start']);

    $enddate = dalo_accounting_date($_GET, 'enddate', $date_default['end']);

    $cols = array(
                    "username" => t('all','Username'),
                    "planname" => t('all','PlanName'),
                    "sessiontime" => t('all','UsedTime'),
                    "plantimebank" => t('all','TotalTime'),
                    t('all','TotalTraffic')
                 );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);
    
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($cols)))
             ? $_GET['orderBy'] : array_keys($cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array("asc", "desc")))
               ? strtolower($_GET['orderType']) : "asc";

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query";
    if ($username !== '') {
        $logQuery .= " for user $username";
    }
    
    if ($planname !== '') {
        $logQuery .= "for plan $planname";
    }
    
    if (!empty($startdate)) {
         $logQuery .= " from $startdate";
    }
    if (!empty($enddate)) {
         $logQuery .= " to $enddate";
    }
    $logQuery .= "on page: ";
    $logDebugSQL = "";

    // print HTML prologue
    $extra_css = array();
    
    $extra_js = array(
        "static/js/request.js",
        "static/js/readonly_info.js",
        "static/js/pages_common.js",
    );
    
    $title = t('Intro','acctplans.php');
    $help = t('helpPage','acctplans');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);
    
    print_title_and_help($title, $help);


    include_once('include/management/pages_common.php');

	
    unset($_SESSION['reportExport'], $_SESSION['reportTable'], $_SESSION['reportQuery']);
    $_SESSION['reportType'] = "reportsPlansUsage";
    $_SESSION['reportExport'] = array(
        'source' => 'acct-plans-usage',
        'type' => 'reportsPlansUsage',
        'filters' => array(
            'username' => $username,
            'planname' => $planname,
            'startdate' => $startdate,
            'enddate' => $enddate,
        ),
    );
    unset($_SESSION['reportTable'], $_SESSION['reportQuery']);

    $partial_query_params = array();
    foreach (array('username'=>$username,'planname'=>$planname,'startdate'=>$startdate,'enddate'=>$enddate) as $key=>$value) {
        if ($value !== '') { $partial_query_params[] = $key . '=' . rawurlencode($value); }
    }
    $userExists = false;
    $accountingPDO = null;
    $accountingRows = array();
    $numrows = 0;
    try {
        $accountingPDO = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        list($accountingSQL, $accountingBindings) = dalo_export_accounting_query(
            'acct-plans-usage', 'reportsPlansUsage', $_SESSION['reportExport']['filters'], $configValues);
        $accountingSQL = substr($accountingSQL, 0, -strlen(' ORDER BY username ASC'));
        $numrows = dalo_accounting_count($accountingPDO, $accountingSQL, $accountingBindings, $configValues);
        if ($username !== '') { $userExists = dalo_accounting_exists($accountingPDO, $username, 'CONFIG_DB_TBL_DALOUSERBILLINFO'); }
    } catch (Throwable $exception) { dalo_accounting_failure($exception); }

    if ($numrows > 0) {
        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                              // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
        
        try {
            $accountingRows = dalo_advanced_rows($accountingPDO, $accountingSQL, $accountingBindings,
                $orderBy, $orderType, array('username','planname','sessiontime','plantimebank'), $offset, $rowsPerPage);
        } catch (Throwable $exception) { dalo_accounting_failure($exception); }
        $per_page_numrows = count($accountingRows);

        $partial_query_string = (count($partial_query_params) > 0)
                              ? ("&" . implode("&", $partial_query_params)) : "";
                              
        $descriptors = array();

        $params = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $rowsPerPage,
                            'page_num' => $pageNum,
                            'order_by' => $orderBy,
                            'order_type' => $orderType,
                            'partial_query_string' => $partial_query_string,
                        );
        $descriptors['center'] = array( 'draw' => $drawNumberLinks, 'params' => $params );

        $descriptors['end'] = array();
        if (!isset($failureMsg)) { $descriptors['end'][] = get_csv_export_control(); }
        print_table_prologue($descriptors);

        // print table top
        print_table_top();

        // second line of table header
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);

        // closes table header, opens table body
        print_table_middle();

        // table content
        $count = 0;
        foreach ($accountingRows as $row) {
            $rowlen = count($row);

            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars((string)($row[$i] ?? ''), ENT_QUOTES, 'UTF-8');
            }
            
            list( $this_username, $this_planname, $this_sessiontime,
                  $this_upload, $this_download, $this_plantimebank, $this_plantimetype ) = $row;
            
            $this_sessiontime = time2str($this_sessiontime);
            $this_plantimebank = time2str($this_plantimebank);
        
            $this_traffic = toxbyte((is_numeric($this_upload) ? $this_upload : 0) +
                                    (is_numeric($this_download) ? $this_download : 0));
            
            $ajax_id = "divContainerUserInfo_" . $count;
            $param = sprintf('username=%s', urlencode($username));
            $onclick = "daloInfo.user('$ajax_id','$param')";
            $tooltip = array(
                                'subject' => $username,
                                'onclick' => $onclick,
                                'ajax_id' => $ajax_id,
                                'actions' => array(),
                            );
            $tooltip['actions'][] = array( 'href' => sprintf('bill-pos-edit.php?username=%s', urlencode($this_username), ), 'label' => t('Tooltip','UserEdit'), );
            
            $tooltip = get_tooltip_list_str($tooltip);
            
            // define table row
            $table_row = array( $this_username, $this_planname, $this_sessiontime, $this_plantimebank, $this_traffic );
            
            // print table row
            print_table_row($table_row);

            $count++;

        }

        // close tbody,
        // print tfoot
        // and close table + form (if any)
        $table_foot = array(
                                'num_rows' => $numrows,
                                'rows_per_page' => $per_page_numrows,
                                'colspan' => $colspan,
                                'multiple_pages' => $drawNumberLinks
                           );
        $descriptor = array( 'table_foot' => $table_foot );

        print_table_bottom($descriptor);

        // get and print "links"
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
        printLinks($links, $drawNumberLinks);
        
        if ($userExists) {
            
            echo '<div class="accordion m-2" id="accordion-parent">';
            include_once('include/management/userReports.php');
            userPlanInformation($username, 1);
            userSubscriptionAnalysis($username, 1);                 // userSubscriptionAnalysis with argument set to 1 for drawing the table
            userConnectionStatus($username, 1);                     // userConnectionStatus (same as above)
            echo '</div>';
        }
    } else {
        $failureMsg = $failureMsg ?? "Nothing to display";
    }

    include_once("include/management/actionMessages.php");


	include('include/config/logging.php');
    
    $inline_extra_js = ($userExists)
                     ? "window.onload = function() { setupAccordion() };" : "";
    
    if (empty($numrows) || isset($failureMsg)) {
        unset($_SESSION['reportExport'], $_SESSION['reportTable'], $_SESSION['reportQuery'], $_SESSION['reportType']);
    }
    unset($accountingPDO, $accountingRows);
    print_footer_and_html_epilogue($inline_extra_js);
?>
