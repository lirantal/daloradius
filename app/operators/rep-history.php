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
    require_once __DIR__ . '/library/operator_reports_pdo.php';
    unset($_SESSION['reportExport'], $_SESSION['reportTable'], $_SESSION['reportQuery'], $_SESSION['reportType']);
    try {
        dalo_accounting_validate_request($_GET);
        foreach (array('batch_name','radiusReply') as $key) {
            if (isset($_GET[$key])) { dalo_accounting_scalar($_GET, $key); }
        }
    } catch (Throwable $exception) {
        http_response_code(400); exit('Invalid operator report filters');
    }
    if (isset($_REQUEST['page']) && !is_string($_REQUEST['page'])) { $_REQUEST['page'] = '1'; }

    include_once('../common/includes/config_read.php');

    // This page has no CSV export builder. Do not reuse a prior page's report.
    unset($_SESSION['reportExport'], $_SESSION['reportTable'], $_SESSION['reportQuery'],
          $_SESSION['reportType'], $_SESSION['reportParams']);

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");
    
    // these three variable can be used for validation an presentation purpose
    $cols = array(
                    "section" => t('all','Section'), 
                    "item" => t('all','Item'), 
                    "creationdate" => t('all','CreationDate'), 
                    "creationby" => t('all','CreationBy'), 
                    "updatedate" => t('all','UpdateDate'), 
                    "updateby" => t('all','UpdateBy')
                 );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);

    // validating user passed parameters

    $default_orderBy = array_keys($cols)[2];
    $default_orderType = "desc";

    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($cols)))
             ? $_GET['orderBy'] : $default_orderBy;

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : $default_orderType;
    
    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logDebugSQL = "";
    
    // print HTML prologue
    $title = t('Intro','rephistory.php');
    $help = t('helpPage','rephistory');
    
    print_html_prologue($title, $langCode);
    
    print_title_and_help($title, $help);

    include('include/management/pages_common.php');

    // we use this convenient way to build our SQL query
    $reportPDO = null;
    $reportRows = array();
    $numrows = 0;
    try {
        $reportPDO = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        list($reportSQL, $reportBindings) = dalo_operator_query('rep-history', array(), $configValues);
        $numrows = dalo_accounting_count($reportPDO, $reportSQL, $reportBindings, $configValues);
    } catch (Throwable $exception) { dalo_accounting_failure($exception); }

    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */
        
        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php');    // must follow configuration initialization because it needs to read
                                                              // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
        
        /* END */

        // we execute and log the actual query
        try {
            $reportRows = dalo_operator_rows($reportPDO, $reportSQL, $reportBindings, 'rep-history',
                $orderBy, $orderType, (int)$offset, (int)$rowsPerPage, false);
        } catch (Throwable $exception) { dalo_accounting_failure($exception); }
        $per_page_numrows = count($reportRows);


        
        $descriptors = array();

        $params = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $rowsPerPage,
                            'page_num' => $pageNum,
                            'order_by' => $orderBy,
                            'order_type' => $orderType,
                        );
        $descriptors['center'] = array( 'draw' => $drawNumberLinks, 'params' => $params );


        $descriptors['end'] = array();
        print_table_prologue($descriptors);

        // print table top
        print_table_top();
        
        // second line of table header
        printTableHead($cols, $orderBy, $orderType);

        // closes table header, opens table body
        print_table_middle();

        // table content
        $count = 0;
        foreach ($reportRows as $row) {
            $rowlen = count($row);

            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = (!empty($row[$i])) ? htmlspecialchars((string)($row[$i] ?? ''), ENT_QUOTES, 'UTF-8') : "(n/a)";
            }
            
            // print table row
            print_table_row($row);
            
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
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printLinks($links, $drawNumberLinks);

    } else {
        $failureMsg = $failureMsg ?? "Nothing to display";
        include_once("include/management/actionMessages.php");
    }

        
    if (isset($failureMsg)) {
        include_once $configValues['OPERATORS_INCLUDE_MANAGEMENT'] . '/actionMessages.php';
    }

    include('include/config/logging.php');
    if (isset($failureMsg) || empty($numrows)) {
        unset($_SESSION['reportExport'], $_SESSION['reportTable'], $_SESSION['reportQuery'], $_SESSION['reportType']);
    }
    unset($reportPDO, $reportRows);
    print_footer_and_html_epilogue();
?>
