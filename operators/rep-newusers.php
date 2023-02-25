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
 *             Filippo Maria Del Prete <filippo.delprete@gmail.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include_once('../common/includes/config_read.php');
    include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // we validate starting and ending dates
    $startdate = (array_key_exists('startdate', $_GET) && !empty(trim($_GET['startdate'])) &&
                  preg_match(DATE_REGEX, trim($_GET['startdate']), $m) !== false &&
                  checkdate($m[2], $m[3], $m[1]))
               ? trim($_GET['startdate']) : "";

    $enddate = (array_key_exists('enddate', $_GET) && !empty(trim($_GET['enddate'])) &&
                preg_match(DATE_REGEX, trim($_GET['enddate']), $m) !== false &&
                checkdate($m[2], $m[3], $m[1]))
             ? trim($_GET['enddate']) : "";

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query for new user(s)";
    if (!empty($startdate)) {
         $logQuery .= " from $startdate";
    }
    if (!empty($enddate)) {
         $logQuery .= " to $enddate";
    }
    $logQuery .= "on page: ";


    // print HTML prologue
    $title = t('Intro','repnewusers.php');
    $help = t('helpPage','repnewusers');
    
    print_html_prologue($title, $langCode);
    
    
    
    // the array $cols has multiple purposes:
    // - its keys (when non-numerical) can be used
    //   - for validating user input
    //   - for table ordering purpose
    // - its value can be used for table headings presentation
    $cols = array(
                    "month" => t('all','Month'),
                    "users" => t('all','Users')
                 );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);
                 
    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }

    // validating user passed parameters

    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "desc";



    print_title_and_help($title, $help);

    include('../common/includes/db_open.php');
    include('include/management/pages_common.php');
    
    $sql_WHERE_pieces = array();
    if (!empty($enddate)) {
        $sql_WHERE_pieces[] = sprintf("CreationDate <= '%s'", $dbSocket->escapeSimple($enddate));
    }
    
    if (!empty($startdate)) {
        $sql_WHERE_pieces[] = sprintf("CreationDate >= '%s'", $dbSocket->escapeSimple($startdate));
    }

    $sql_WHERE = (count($sql_WHERE_pieces) > 0) ? " WHERE " . implode(" AND ", $sql_WHERE_pieces) : "";

    // month is used as a "shadow" parameter for non-lexicographic ordering purpose
    // period and users are used for presentation purpose
    $sql = sprintf("SELECT CONCAT(MONTHNAME(CreationDate), ' ', YEAR(CreationDate)) AS period, COUNT(*) As users,
                           CAST(CONCAT(YEAR(CreationDate), '-', MONTH(CreationDate), '-01') AS DATE) AS month
                      FROM %s", $configValues['CONFIG_DB_TBL_DALOUSERINFO'])
         . $sql_WHERE . " GROUP BY month";                                                
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();

    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */
        
        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                              // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
        
        /* END */
                     
        // we execute and log the actual query
        $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $logDebugSQL = "$sql;\n";
        $res = $dbSocket->query($sql);
        
        $per_page_numrows = $res->numRows();
        
        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_params = array();
        if (!empty($startdate)) {
            $partial_query_params[] = sprintf("startdate=%s", $startdate);
        }
        if (!empty($enddate)) {
            $partial_query_params[] = sprintf("enddate=%s", $enddate);
        }
        
        $partial_query_string = ((count($partial_query_params) > 0) ? "&" . implode("&", $partial_query_params)  : "");
        
        
        // set navbar stuff
        $navkeys = array( array( 'stats', t('all','Statistics') ), array( 'graphs', t('menu','Graphs') ), );

        // print navbar controls
        print_tab_header($navkeys);
        
        // open tab wrapper
        open_tab_wrapper();
        
        // tab 0
        open_tab($navkeys, 0, true);
        
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
        while ($row = $res->fetchRow()) {
            
            // last field is used only for ordering purpose
            $rowlen = count($row) - 1;
            
            // print table row
            printf('<tr id="row-%d">', $count);
            
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
                printf("<td>%s</td>", $row[$i]);
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
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printLinks($links, $drawNumberLinks);

        close_tab($navkeys, 0);
        
        $img_format = '<div class="my-3 text-center"><img src="%s" alt="%s"></div>';
        
        // tab 1
        open_tab($navkeys, 1);
        
        $src = sprintf("library/graphs/new_users.php?startdate=%s&enddate=%s", $startdate, $enddate);
        $alt = "monthly number of new users";
        printf($img_format, $src, $alt);
        
        close_tab($navkeys, 1);
        
        // close tab wrapper
        close_tab_wrapper();
        
    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
    
    include('../common/includes/db_close.php');

    include('include/config/logging.php');
    
    print_footer_and_html_epilogue();
?>
