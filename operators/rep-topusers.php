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

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include_once('../common/includes/config_read.php');
    include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");


    $startdate = (array_key_exists('startdate', $_GET) && isset($_GET['startdate']) &&
                  preg_match(DATE_REGEX, $_GET['startdate'], $m) !== false &&
                  checkdate($m[2], $m[3], $m[1]))
               ? $_GET['startdate'] : "";

    $enddate = (array_key_exists('enddate', $_GET) && isset($_GET['enddate']) &&
                preg_match(DATE_REGEX, $_GET['enddate'], $m) !== false &&
                checkdate($m[2], $m[3], $m[1]))
             ? $_GET['enddate'] : "";

    // and in other cases we partially strip some character,
    // and leave validation/escaping to other functions used later in the script
    $username = (array_key_exists('username', $_GET) && !empty(str_replace("%", "", trim($_GET['username']))))
              ? str_replace("%", "", trim($_GET['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

    // the array $cols has multiple purposes:
    // - its keys (when non-numerical) can be used
    //   - for validating user input
    //   - for table ordering purpose
    // - its value can be used for table headings presentation
    $cols = array(
                    'username' => t('all','Username'),
                    'framedipaddress' => t('all','IPAddress'),
                    'acctstarttime' => t('all','StartTime'),
                    'acctstoptime' => t('all','StopTime'),
                    'Time' => t('all','TotalTime'),
                    'Upload' => t('all','Upload'),
                    'Download' => t('all','Download'),
                    'acctterminatecause' => t('all','Termination'),
                    'nasipaddress' => t('all','NASIPAddress'),
    );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);

    // validating user passed parameters

    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($cols)))
             ? $_GET['orderBy'] : array_keys($cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "asc";

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query for [order by: $orderBy] on page: ";


    // print HTML prologue
    $title = t('Intro','reptopusers.php');
    $help = t('helpPage','reptopusers') . " " . $orderBy;

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include('../common/includes/db_open.php');
    include('include/management/pages_common.php');

    // the partial query is built starting from user input
    // and for being passed to setupNumbering and setupLinks functions
    $partial_query_params = array();

    // creating $sql_WHERE for SQL query
    $sql_WHERE = array();
    $sql_WHERE[] = "AcctStopTime > '0000-00-00 00:00:01'";
    if (!empty($startdate)) {
        $partial_query_params[] = sprintf("startdate=%s", urlencode(htmlspecialchars($startdate, ENT_QUOTES, 'UTF-8')));
        $sql_WHERE[] = sprintf("AcctStartTime > '%s'", $dbSocket->escapeSimple($startdate));
    }

    if (!empty($enddate)) {
        $partial_query_params[] = sprintf("enddate=%s", urlencode(htmlspecialchars($enddate, ENT_QUOTES, 'UTF-8')));
        $sql_WHERE[] = sprintf("AcctStartTime < '%s'", $dbSocket->escapeSimple($enddate));
    }

    if (!empty($username)) {
        $partial_query_params[] = sprintf("username=%s", urlencode($username_enc));
        $sql_WHERE[] = sprintf("username LIKE '%s%%'", $dbSocket->escapeSimple($username));
    }

    // setup php session variables for exporting
    $_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADACCT'];
    $_SESSION['reportQuery'] = (count($sql_WHERE) > 0) ? " WHERE " . implode(" AND ", $sql_WHERE) : "";
    $_SESSION['reportType'] = "TopUsers";

    $sql = "SELECT DISTINCT(ra.username) AS username, ra.FramedIPAddress, ra.AcctStartTime, MAX(ra.AcctStopTime),
                   SUM(ra.AcctSessionTime) AS Time, SUM(ra.AcctInputOctets) AS Upload,
                   SUM(ra.AcctOutputOctets) AS Download, ra.AcctTerminateCause, ra.NASIPAddress
            FROM " . $configValues['CONFIG_DB_TBL_RADACCT'] . " AS ra";

    if (count($sql_WHERE) > 0) {
        $sql .= " WHERE " . implode(" AND ", $sql_WHERE);
    }

    $sql .= " GROUP BY username";


    $logDebugSQL = "$sql;\n";
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
        $res = $dbSocket->query($sql);
        $logDebugSQL = "$sql;\n";

        $per_page_numrows = $res->numRows();

        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = ((count($partial_query_params) > 0) ? "&" . implode("&", $partial_query_params)  : "");

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
        $descriptors['end'][] = array(
                                        'onclick' => "location.href='include/management/fileExport.php?reportFormat=csv'",
                                        'label' => 'CSV Export',
                                        'class' => 'btn-light',
                                     );
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
            $rowlen = count($row);

            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }


            list( $username, $framedIPAddress, $acctStartTime, $maxAcctStopTime, $time,
                  $upload, $download, $acctTerminateCause, $nasIPAddress ) = $row;

            $time = time2str($time);
            $upload = toxbyte($upload);
            $download = toxbyte($download);

            $table_row = array( $username, $framedIPAddress, $acctStartTime, $maxAcctStopTime, $time,
                                $upload, $download, $acctTerminateCause, $nasIPAddress );

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

    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }

    include('../common/includes/db_close.php');

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
