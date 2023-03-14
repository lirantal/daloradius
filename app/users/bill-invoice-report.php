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
    $login_user = $_SESSION['login_user'];

    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");

    $cols = array(
                    "id" => t('all','Invoice'),
                    "date" => t('all','Date'),
                    "totalbilled" => t('all','TotalBilled'),
                    "totalpayed" => t('all','TotalPayed'),
                    t('all','Balance'),
                    "status_id" => t('all','Status')
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
               ? strtolower($_GET['orderType']) : "asc";

    // in other cases we just check that syntax is ok
    $startdate = (array_key_exists('startdate', $_GET) && isset($_GET['startdate']) &&
                  preg_match(DATE_REGEX, $_GET['startdate'], $m) !== false &&
                  checkdate($m[2], $m[3], $m[1]))
               ? $_GET['startdate'] : "";

    $enddate = (array_key_exists('enddate', $_GET) && isset($_GET['enddate']) &&
                preg_match(DATE_REGEX, $_GET['enddate'], $m) !== false &&
                checkdate($m[2], $m[3], $m[1]))
             ? $_GET['enddate'] : "";

    $invoice_status = (array_key_exists('invoice_status', $_GET) && isset($_GET['invoice_status']))
                    ? trim($_GET['invoice_status']) : "";

    $username = $login_user;
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query for invoice report [username: $username] on page: ";
    $logDebugSQL = "";

    $title = t('Intro','billinvoicereport.php');
    $help = t('helpPage','billinvoicelist');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include('../common/includes/db_open.php');
    include('include/management/pages_common.php');


    $sql_WHERE = array();
    $partial_query_params = array();
    $sql_WHERE[] = sprintf("b.username = '%s'", $dbSocket->escapeSimple($username));
    $partial_query_params[] = sprintf("username=%s", $username_enc);

    if (!empty($startdate)) {
        $sql_WHERE[] = sprintf("a.date >= '%s'", $dbSocket->escapeSimple($startdate));
        $partial_query_params[] = sprintf("startdate=%s", $startdate);
    }

    if (!empty($enddate)) {
        $sql_WHERE[] = sprintf("a.date <= '%s'", $dbSocket->escapeSimple($enddate));
        $partial_query_params[] = sprintf("enddate=%s", $enddate);
    }

    if (!empty($invoice_status)) {
        $sql_WHERE[] = sprintf("a.status_id = '%s'", $dbSocket->escapeSimple($invoice_status));
        $partial_query_params[] = sprintf("invoice_status=%s", htmlspecialchars($invoice_status, ENT_QUOTES, 'UTF-8'));
    }

    $sql = sprintf("SELECT a.id, a.date, c.value AS status,
                           COALESCE(e2.totalpayed, 0) AS totalpayed, COALESCE(d2.totalbilled, 0) AS totalbilled
                      FROM %s AS a INNER JOIN %s AS b ON a.user_id=b.id
                                   INNER JOIN %s AS c ON a.status_id=c.id
                                    LEFT JOIN (SELECT SUM(d.amount + d.tax_amount) AS totalbilled, invoice_id
                                                 FROM %s AS d GROUP BY d.invoice_id) AS d2 ON d2.invoice_id=a.id
                                    LEFT JOIN (SELECT SUM(e.amount) AS totalpayed, invoice_id
                                                 FROM %s AS e GROUP BY e.invoice_id) AS e2 ON e2.invoice_id=a.id",
                   $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'], $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                   $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS'], $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'],
                   $configValues['CONFIG_DB_TBL_DALOPAYMENTS']);
    if (count($sql_WHERE) > 0) {
        $sql .= " WHERE " . implode(" AND ", $sql_WHERE);
    }
    $sql .= " GROUP BY a.id";
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

        // setup php session variables for exporting
        $_SESSION["export_items"] = array( "id", "date", "status", "totalpayed", "totalbilled" );
        $_SESSION["export_query"] = $sql;

        // we execute and log the actual query
        $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL = "$sql;\n";

        $per_page_numrows = $res->numRows();

        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = implode("&", $partial_query_params);

        // we prepare the "controls bar" (aka the table prologue bar)
        $descriptors = array();

        $params = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $rowsPerPage,
                            'page_num' => $pageNum,
                            'order_by' => $orderBy,
                            'order_type' => $orderType,
                            'partial_query_string' => $partial_query_string
                        );
        $descriptors['center'] = array( 'draw' => $drawNumberLinks, 'params' => $params );


        $descriptors['end'] = array();
        $descriptors['end'][] = array(
                                        'onclick' => "window.location.assign('include/management/fileExport.php')",
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

            list($id, $date, $status, $totalpayed, $totalbilled) = $row;
            $id = intval($id);

            // create balance
            $balance = $totalpayed - $totalbilled;
            $balance = sprintf('<span class="text-%s">%s <i class="bi bi-currency-exchange"></i></span>', (($balance < 0) ? "danger" : "success"), $balance);

            // create total payed and billed
            $totalpayed = sprintf('%s <i class="bi bi-currency-exchange"></i>', $totalpayed);

            // define table row
            $table_row = array( $id, $date, $totalbilled, $totalpayed, $balance, $status );

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
