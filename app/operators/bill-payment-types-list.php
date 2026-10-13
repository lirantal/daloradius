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
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logAction = "";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    $cols = array(
                    "selected",
                    "id" => t('all','ID'),
                    "paymentname" => t('all','PayTypeName'),
                    t('all','PayTypeNotes')
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
                  is_string($_GET['orderType']) && in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "asc";

    // print HTML prologue
    $title = t('Intro','paymenttypeslist.php');
    $help = t('helpPage','paymenttypeslist');
    
    print_html_prologue($title, $langCode);

    // start printing content
    print_title_and_help($title, $help);
    

    require_once('library/payment_types_pdo.php');
    include('include/management/pages_common.php');
    $numrows = 0; $rows = array(); $type_pdo = null;
    try {
        foreach (array('orderBy','orderType') as $field) { dalo_payment_scalar($_GET,$field); }
        $type_pdo = dalo_payment_open($configValues);
        $table = dalo_payment_table($type_pdo,$configValues,'CONFIG_DB_TBL_DALOPAYMENTTYPES');
        $numrows = (int)dalo_catalog_read_rows($type_pdo,"SELECT COUNT(id) FROM $table")[0][0];
        if ($numrows > 0) {
            include('include/management/pages_numbering.php');
            $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == 'yes' && $maxPage > 1;
            $sql = "SELECT id,value AS paymentName,notes FROM $table ORDER BY $orderBy $orderType LIMIT :offset,:limit";
            $rows = dalo_catalog_read_rows($type_pdo,$sql,array(':offset'=>(int)$offset,':limit'=>(int)$rowsPerPage));
            $logDebugSQL .= "$sql;\n";
        }
    } catch (Throwable $error) { dalo_payment_type_read_failure($error); $numrows = 0; $rows = array(); }
    finally { $type_pdo = null; }
    if ($numrows > 0) {
        $per_page_numrows = count($rows);

        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "bill-payment-types-del.php";
        
        // we prepare the "controls bar" (aka the table prologue bar)
        $params = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $rowsPerPage,
                            'page_num' => $pageNum,
                            'order_by' => $orderBy,
                            'order_type' => $orderType,
                        );

        $descriptors = array();
        $descriptors['start'] = array( 'common_controls' => 'paymentname[]', );
        $descriptors['center'] = array( 'draw' => $drawNumberLinks, 'params' => $params );
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
        foreach ($rows as $row) {
            $rawPaymentName = (string)$row[1];
            $rowlen = count($row);
        
            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars((string)$row[$i], ENT_QUOTES, 'UTF-8');
            }
            
            list($id, $paymentName, $notes) = $row;
            
            $tooltip = array(
                                'subject' => $id,
                                'actions' => array(),
                            );
            $tooltip['actions'][] = array( 'href' => sprintf('bill-payment-types-edit.php?paymentname=%s', urlencode($rawPaymentName), ), 'label' => t('Tooltip','EditPayType'), );
            $tooltip['actions'][] = array( 'href' => sprintf('bill-payment-types-del.php?paymentname=%s', urlencode($rawPaymentName), ), 'label' => t('Tooltip','RemovePayType'), );
            
            // create tooltip
            $tooltip = get_tooltip_list_str($tooltip);

            // create checkbox
            $d = array( 'name' => 'paymentname[]', 'value' => $rawPaymentName );
            $checkbox = get_checkbox_str($d);

            // build table row
            $table_row = array( $checkbox, $tooltip, $paymentName, $notes );

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
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printLinks($links, $drawNumberLinks);

    } else {
        if (!isset($failureMsg)) { $failureMsg = "Nothing to display"; }
        include_once("include/management/actionMessages.php");
    }
    


    include('include/config/logging.php');
    
    print_footer_and_html_epilogue();
?>
