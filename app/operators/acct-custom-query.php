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
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    $sqlfields = (array_key_exists('sqlfields', $_GET) && !empty($_GET['sqlfields']) && is_array($_GET['sqlfields']) &&
                  array_intersect($_GET['sqlfields'], $acct_custom_query_options_all) == $_GET['sqlfields'])
               ? $_GET['sqlfields'] : $acct_custom_query_options_default;
    
    $cols = array();
    foreach ($sqlfields as $sqlfield) {
        $cols[$sqlfield] = $sqlfield;
    }
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);
    
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($acct_custom_query_options_all)))
             ? $_GET['orderBy'] : array_keys($acct_custom_query_options_all)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  preg_match(ORDER_TYPE_REGEX, $_GET['orderType']) !== false)
               ? strtolower($_GET['orderType']) : "asc";

    $startdate = (array_key_exists('startdate', $_GET) && isset($_GET['startdate']) &&
                  preg_match(DATE_REGEX, $_GET['startdate'], $m) !== false &&
                  checkdate($m[2], $m[3], $m[1]))
               ? $_GET['startdate'] : "";

    $enddate = (array_key_exists('enddate', $_GET) && isset($_GET['enddate']) &&
                preg_match(DATE_REGEX, $_GET['enddate'], $m) !== false &&
                checkdate($m[2], $m[3], $m[1]))
             ? $_GET['enddate'] : "";
    
    $valid_operators = array("equals" => "=", "contains" => "LIKE");
    $operator = (array_key_exists('operator', $_GET) && !empty($_GET['operator']) &&
                 in_array($_GET['operator'], array_keys($valid_operators)))
              ? $_GET['operator'] : "";
    
    $where_field = (array_key_exists('where_field', $_GET) && !empty($_GET['where_field']) &&
                    in_array($_GET['where_field'], $acct_custom_query_options_all))
                 ? $_GET['where_field'] : "";
    
    $where_value = (array_key_exists('where_value', $_GET) && !empty(str_replace("%", "", trim($_GET['where_value']))))
                 ? str_replace("%", "", trim($_GET['where_value'])) : "";

    $where_value_enc = (!empty($where_value)) ? htmlspecialchars($where_value, ENT_QUOTES, 'UTF-8') : "";
    
    //feed the sidebar variables
    $accounting_custom_startdate = $startdate;
    $accounting_custom_enddate = $enddate;
    $accounting_custom_value = $where_value_enc;


    // print HTML prologue
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js"
    );
    
    $title = t('Intro','acctcustomquery.php');
    $help = t('helpPage','acctcustomquery');
    
    print_html_prologue($title, $langCode, array(), $extra_js);
    
    print_title_and_help($title, $help);
    
    include('../common/includes/db_open.php');
    include('include/management/pages_common.php');
    
    // preparing the custom query
    
    $sql_WHERE = array();
    $partial_query_string_pieces = array();
    
    foreach ($sqlfields as $sqlfield) {
        $partial_query_string_pieces[] = sprintf("sqlfields[]=%s", $sqlfield);
    }
    
    if (!empty($startdate)) {
        $sql_WHERE[] = sprintf("AcctStartTime > '%s'", $dbSocket->escapeSimple($startdate));
        $partial_query_string_pieces[] = sprintf("startdate=%s", $startdate);
    }
    
    if (!empty($startdate)) {
        $sql_WHERE[] = sprintf("AcctStartTime < '%s'", $dbSocket->escapeSimple($enddate));
        $partial_query_string_pieces[] = sprintf("enddate=%s", $enddate);
    }
    
    if (!empty($where_value)) {
        // get the operator
        $op = $valid_operators[$operator];
        
        // if the op is LIKE then the SQL syntax uses % for pattern matching
        // and we sorround the $value with % as a wildcard
        $where_value = $dbSocket->escapeSimple($where_value);
        
        if ($op == "LIKE") {
            $where_value = "%" . $where_value . "%";
        }

        $sql_WHERE[] = sprintf("%s %s '%s'", $where_field, $op, $where_value);

        $partial_query_string_pieces[] = sprintf("where_field=%s", $where_field);
        $partial_query_string_pieces[] = sprintf("operator=%s", $operator);
        $partial_query_string_pieces[] = sprintf("where_value=%s", $where_value_enc);
    }

    // executing the custom query

    $sql = sprintf("SELECT %s FROM %s", implode(", ", $sqlfields), $configValues['CONFIG_DB_TBL_RADACCT']);
    
    if (count($sql_WHERE) > 0) {
        $sql .= " WHERE " . implode(" AND ", $sql_WHERE);
    }

    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    $numrows = $res->numRows();
    
    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */
        
        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                              // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
        
        $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        $per_page_numrows = $res->numRows();
        
        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = (count($partial_query_string_pieces) > 0)
                              ? "&" . implode("&", $partial_query_string_pieces) : "";


        $descriptors = array();

        $params = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $rowsPerPage,
                            'page_num' => $pageNum,
                            'order_by' => $orderBy,
                            'order_type' => $orderType,
                        );
        $descriptors['center'] = array( 'draw' => $drawNumberLinks, 'params' => $params );

        print_table_prologue($descriptors);

        // print table top
        print_table_top();

        // second line of table header
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);
        
        // closes table header, opens table body
        print_table_middle();

        // table content
        $count = 0;
        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            printf('<tr id="row-%d">', $count);
            foreach ($sqlfields as $field) {
                printf("<td>%s</td>", htmlspecialchars($row[$field], ENT_QUOTES, 'UTF-8'));
            }
            echo '</tr>';
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
        $descriptor = array(  'table_foot' => $table_foot );

        print_table_bottom($descriptor);

        // get and print "links"
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
        printLinks($links, $drawNumberLinks);

    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
        
    include('../common/includes/db_close.php');    

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
