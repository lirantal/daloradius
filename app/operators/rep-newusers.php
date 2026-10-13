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
 *             Filippo Maria Del Prete <filippo.delprete@gmail.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);
    $operator = $_SESSION['operator_user'];
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);
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

    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);

    // we validate starting and ending dates
    $date_default = date_range_default('year_to_date');

    $startdate = dalo_accounting_date($_GET, 'startdate', $date_default['start']);

    $enddate = dalo_accounting_date($_GET, 'enddate', $date_default['end']);

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

    print_html_prologue($title, $langCode, array(), array("static/js/chart.umd.min.js", "static/js/daloradius-charts.js"));


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

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_common.php' ]);

    $reportPDO = null;
    $reportRows = array();
    $numrows = 0;
    try {
        $reportPDO = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        list($reportSQL, $reportBindings) = dalo_operator_query('rep-newusers', array('startdate'=>$startdate,'enddate'=>$enddate), $configValues);
        $numrows = dalo_accounting_count($reportPDO, $reportSQL, $reportBindings, $configValues);
    } catch (Throwable $exception) { dalo_accounting_failure($exception); }

    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */

        // when $numrows is set, $maxPage is calculated inside this include file
        // must follow configuration initialization because it needs to read
        // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_numbering.php' ]);

        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;

        /* END */

        // we execute and log the actual query
        try {
            $reportRows = dalo_operator_rows($reportPDO, $reportSQL, $reportBindings, 'rep-newusers',
                $orderBy, $orderType, (int)$offset, (int)$rowsPerPage, false);
        } catch (Throwable $exception) { dalo_accounting_failure($exception); }
        $per_page_numrows = count($reportRows);



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
        foreach ($reportRows as $row) {

            // last field is used only for ordering purpose
            $rowlen = count($row) - 1;

            // print table row
            printf('<tr id="row-%d">', $count);

            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars((string)($row[$i] ?? ''), ENT_QUOTES, 'UTF-8');
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

        $img_format = '<div class="my-3 text-center" style="height:384px"><canvas data-chart-source="%s" aria-label="%s" role="img"></canvas></div>';

        // tab 1
        open_tab($navkeys, 1);

        $src = sprintf("library/graphs/new_users.php?startdate=%s&enddate=%s", $startdate, $enddate);
        $alt = "monthly number of new users";
        printf($img_format, $src, $alt);

        close_tab($navkeys, 1);

        // close tab wrapper
        close_tab_wrapper();

    } else {
        $failureMsg = $failureMsg ?? "Nothing to display";
        include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);
    }


    if (isset($failureMsg)) {
        include_once $configValues['OPERATORS_INCLUDE_MANAGEMENT'] . '/actionMessages.php';
    }

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    if (isset($failureMsg) || empty($numrows)) {
        unset($_SESSION['reportExport'], $_SESSION['reportTable'], $_SESSION['reportQuery'], $_SESSION['reportType']);
    }
    unset($reportPDO, $reportRows);
    print_footer_and_html_epilogue();
