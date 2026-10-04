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

    unset($_SESSION['reportExport'], $_SESSION['reportTable'], $_SESSION['reportQuery']);

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    $cols = array(
                    "bid" => t('all','ID'),
                    "batch_name" => t('all','BatchName'),
                    t('all','HotSpot'),
                    t('all','BatchStatus'),
                    t('all','TotalUsers'),
                    t('all','PlanName'),
                    t('all','PlanCost'),
                    t('all','BatchCost'),
                    "creationdate" => t('all','CreationDate'),
                    "creationby" => t('all','CreationBy')
                 );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);
                 
    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }
    
    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "desc";

    $_SESSION['reportType'] = "reportsBatchList";
    $_SESSION['reportExport'] = array(
        'source' => 'mng-batch-list',
        'type' => 'reportsBatchList',
        'filters' => array(),
    );


    // print HTML prologue    
    $title = t('Intro','mngbatchlist.php');
    $help = t('helpPage','mngbatchlist');
    
    print_html_prologue($title, $langCode);

    // start printing content
    print_title_and_help($title, $help);

    include('include/management/pages_common.php');
    
    //orig: used as method to get total rows - this is required for the pages_numbering.php page
    $reportPDO = null;
    $reportRows = array();
    $numrows = 0;
    try {
        $reportPDO = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        list($reportSQL, $reportBindings) = dalo_operator_query('mng-batch-list', array(), $configValues);
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
            $reportRows = dalo_operator_rows($reportPDO, $reportSQL, $reportBindings, 'mng-batch-list',
                $orderBy, $orderType, (int)$offset, (int)$rowsPerPage, false);
        } catch (Throwable $exception) { dalo_accounting_failure($exception); }
        $per_page_numrows = count($reportRows);


        
        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "mng-batch-del.php";
        
        // we prepare the "controls bar" (aka the table prologue bar)
        $additional_controls = array();
        $additional_controls[] = array(
                                'onclick' => sprintf("removeCheckbox('listall','%s')", $action),
                                'label' => 'Delete',
                                'class' => 'btn-danger',
                              );
        
        // we prepare the "controls bar" (aka the table prologue bar)
        $params = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $rowsPerPage,
                            'page_num' => $pageNum,
                            'order_by' => $orderBy,
                            'order_type' => $orderType,
                        );
        
        $descriptors = array();
        $descriptors['start'] = array( 'common_controls' => 'batch_id[]', 'additional_controls' => $additional_controls );
        $descriptors['center'] = array( 'draw' => $drawNumberLinks, 'params' => $params );
        $descriptors['end'] = array();
        if (!isset($failureMsg)) { $descriptors['end'][] = get_csv_export_control(); }
        print_table_prologue($descriptors);
        
        $form_descriptor = array( 'form' => array( 'action' => $action, 'method' => 'POST', 'name' => 'listall' ), );

        // print table top
        print_table_top($form_descriptor);

        // second line of table header
        printTableHead($cols, $orderBy, $orderType);

        // closes table header, opens table body
        print_table_middle();
        
        // table content
        $count = 0;
        foreach ($reportRows as $row) {

            // bh.id AS bid, bh.batch_name, bh.batch_description, bh.batch_status, COUNT(DISTINCT(ubi.id)) AS total_users,
            // ubi.planname, bp.plancost, bp.plancurrency, hs.name AS HotspotName, bh.creationdate, bh.creationby,
            // bh.updatedate, bh.updateby
            $rowlen = count($row);

            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars((string)($row[$i] ?? ''), ENT_QUOTES, 'UTF-8');
            }

            

            list($id, $this_batch_name, $this_batch_desc, $batch_status, $total_users, $plan_name, $plancost,
                 $plancurrency, $hotspot_name, $creationdate, $creationby, $updatedate, $updateby) = $row;

            $total_users = intval($total_users);
            $plancost = intval($plancost);

            $batch_cost = $total_users * $plancost;

            if (empty($this_batch_desc)) {
                $this_batch_desc = "(n/a)";
            }
            
            if (empty($plan_name)) {
                $plan_name = "(n/d)";
            }
            
            if (empty($hotspot_name)) {
                $hotspot_name = "(n/d)";
            }
            
            // tooltip stuff
            $tooltip = array(
                                'subject' => $this_batch_name,
                                'actions' => array(),
                                'content' => sprintf('<strong>%s</strong>:<br>%s', t('all','batchDescription'), $this_batch_desc),
                            );
            $tooltip['actions'][] = array( 'href' => sprintf('rep-batch-details.php?batch_name=%s', urlencode($this_batch_name), ), 'label' => t('Tooltip','BatchDetails'), );

            // create tooltip
            $tooltip = get_tooltip_list_str($tooltip);

            // create checkbox
            $d = array( 'name' => 'batch_id[]', 'value' => $id, 'label' => $id );
            $checkbox = get_checkbox_str($d);

            // build table row
            $table_row = array( $checkbox, $tooltip, $hotspot_name, $batch_status, $total_users,
                                $plan_name, $plancost, $batch_cost, $creationdate, $creationby );

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

        $descriptor = array(  'form' => $form_descriptor, 'table_foot' => $table_foot );
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
