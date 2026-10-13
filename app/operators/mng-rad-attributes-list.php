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

    include_once('../common/includes/config_read.php');
    include('library/check_operator_perm.php');
    require_once __DIR__ . '/library/dictionary_pages_pdo.php';
    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    $cols = array(
                    "selected",
                    "id" => t('all','ID'),
                    "vendor" => t('all','VendorName'),
                    "attribute" => t('all','VendorAttribute')
                 );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);
                 
    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }
    
    // whenever possible we use a whitelist approach
    $orderBy = isset($_GET['orderBy']) && is_string($_GET['orderBy']) && in_array($_GET['orderBy'], array_keys($param_cols), true)
             ? $_GET['orderBy'] : array_keys($param_cols)[0];
    $orderType = isset($_GET['orderType']) && is_string($_GET['orderType']) && in_array(strtolower($_GET['orderType']), array('desc','asc'), true)
               ? strtolower($_GET['orderType']) : 'asc';

    // print HTML prologue
    $extra_js = array(
        "static/js/request.js",
        "static/js/readonly_info.js"
    );
    
    $title = t('Intro','mngradattributeslist.php');
    $help = t('helpPage','mngradattributeslist');
    
    print_html_prologue($title, $langCode, array(), $extra_js);

    // start printing content
    print_title_and_help($title, $help);


    include('include/management/pages_common.php');
    $vendor = '';
    $numrows = 0;
    $sql_WHERE = "(Type <> '' OR Type IS NOT NULL)";
    $bindings = array();
    try {
        $vendor = dalo_dictionary_text($_GET['vendor'] ?? '', 256);
        // Keep legacy percent removal and LIKE underscore behavior in search filters.
        $vendor = str_replace('%', '', $vendor);
        if ($vendor !== '') { $sql_WHERE .= ' AND Vendor LIKE :filter'; $bindings[':filter'] = '%' . $vendor . '%'; }
        $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        $dictionaryTable = dalo_dictionary_table($configValues);
        $stmt = $pdo->prepare("SELECT COUNT(id) FROM $dictionaryTable WHERE $sql_WHERE");
        $stmt->execute($bindings);
        $numrows = (int)$stmt->fetchColumn();
    } catch (Throwable $e) { $failureMsg = 'Could not load dictionary list'; }

    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */
        
        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                              // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
        
        /* END */
                     
        $rows = array();
        try {
            $sql = "SELECT id, Vendor, Attribute FROM $dictionaryTable WHERE $sql_WHERE ORDER BY $orderBy $orderType LIMIT :offset, :limit";
            $stmt = $pdo->prepare($sql);
            foreach ($bindings as $key=>$value) { $stmt->bindValue($key, $value, PDO::PARAM_STR); }
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$rowsPerPage, PDO::PARAM_INT);
            $stmt->execute(); $rows = $stmt->fetchAll(PDO::FETCH_NUM);
            $logDebugSQL = $sql . ";\n";
        } catch (Throwable $e) { $failureMsg = 'Could not load dictionary list'; }
        $per_page_numrows = count($rows);

        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = ($vendor !== '')
                              ? "&vendor=" . urlencode($vendor) : "";
                              
        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "mng-rad-attributes-del.php";
        
        // we prepare the "controls bar" (aka the table prologue bar)
        $params = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $rowsPerPage,
                            'page_num' => $pageNum,
                            'order_by' => $orderBy,
                            'order_type' => $orderType,
                        );

        $descriptors = array();
        $descriptors['start'] = array( 'common_controls' => 'vendor__attribute[]', );
        $descriptors['center'] = array( 'draw' => $drawNumberLinks, 'params' => $params );
        print_table_prologue($descriptors);

        $form_descriptor = array( 'form' => array( 'action' => $action, 'method' => 'POST', 'name' => 'listall' ), );
        
        // print table top
        print_table_top($form_descriptor);

        // second line of table header
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);
        
        // closes table header, opens table body
        print_table_middle();

        // table content
        $count = 0;
        foreach ($rows as $row) {
            list($raw_id, $raw_vendor, $raw_attribute) = $row;
            $this_id = htmlspecialchars((string)$raw_id, ENT_QUOTES, 'UTF-8');
            $this_vendor = htmlspecialchars((string)$raw_vendor, ENT_QUOTES, 'UTF-8');
            $this_attribute = htmlspecialchars((string)$raw_attribute, ENT_QUOTES, 'UTF-8');

            // define tooltip
            $ajax_id = "divContainerAttributeInfo_" . $count;
            $param = sprintf('attribute=%s', rawurlencode((string)$raw_attribute));
            $onclick = "daloInfo.attribute('$ajax_id','$param')";
            $tooltip = array(
                                'subject' => $this_id,
                                'onclick' => $onclick,
                                'ajax_id' => $ajax_id,
                                'actions' => array(),
                            );
            $tooltip['actions'][] = array( 'href' => sprintf('mng-rad-attributes-edit.php?vendor=%s&attribute=%s', rawurlencode((string)$raw_vendor), rawurlencode((string)$raw_attribute), ), 'label' => t('Tooltip','AttributeEdit'), );
            
            // create tooltip
            $tooltip = get_tooltip_list_str($tooltip);
            
            // create checkbox
            $d = array( 'name' => 'vendor__attribute[]',
                        'value' => dalo_dictionary_selection_token((string)$raw_vendor, (string)$raw_attribute));
            $checkbox = get_checkbox_str($d);
            
            // build table row
            $table_row = array( $checkbox, $tooltip, $this_vendor, $this_attribute );

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
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
        printLinks($links, $drawNumberLinks);
        
    } else {
        $failureMsg = $failureMsg ?? "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
    
    if (isset($failureMsg)) { include_once 'include/management/actionMessages.php'; }

    include('include/config/logging.php');
    
    print_footer_and_html_epilogue();
?>
