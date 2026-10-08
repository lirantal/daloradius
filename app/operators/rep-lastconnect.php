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

    unset($_SESSION['reportExport']);

    // setting table-related parameters first
    switch($configValues['FREERADIUS_VERSION']) {
    case '1':
        $tableSetting['postauth']['user'] = 'user';
        $tableSetting['postauth']['date'] = 'date';
        break;

    case '2':
    case '3':
    default:
        $tableSetting['postauth']['user'] = 'username';
        $tableSetting['postauth']['date'] = 'authdate';
        break;
    }

    // in other cases we just check that syntax is ok
    $date_default = date_range_default('last_7_days');

    $startdate = dalo_accounting_date($_GET, 'startdate', $date_default['start']);

    $enddate = dalo_accounting_date($_GET, 'enddate', $date_default['end']);

    $radiusReply = (array_key_exists('radiusReply', $_GET) && !empty(trim($_GET['radiusReply'])) &&
                    in_array(trim($_GET['radiusReply']), $valid_radiusReplys))
                 ? trim($_GET['radiusReply']) : $valid_radiusReplys[0];

    // and in other cases we partially strip some character,
    // and leave validation/escaping to other functions used later in the script
    $username = dalo_accounting_scalar($_GET, 'username');
    $username_enc = ($username !== '') ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

    $hiddenPassword = (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) == "yes");

    // the array $cols has multiple purposes:
    // - its keys (when non-numerical) can be used
    //   - for validating user input
    //   - for table ordering purpose
    // - its value can be used for table headings presentation
    $cols = array(
                   $tableSetting['postauth']['user'] => t('all','Username'),
                   "fullname" => t('all','Name'),
                 );

    if (!$hiddenPassword) {
        $cols["pass"] = t('all','Password');
    }

    if ($radiusReply == 'Any') {
        $cols["reply"] = t('all','RADIUSReply');
    } else {
        $cols[] = t('all','RADIUSReply');
    }

    $date_label = $tableSetting['postauth']['date'];
    $cols[$date_label] = t('all','StartTime');

    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);

    // validating user passed parameters

    $starttime_index = count($cols) - 1;
    $default_orderBy = array_keys($cols)[$starttime_index];
    $default_orderType = "desc";

    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($cols)))
             ? $_GET['orderBy'] : $default_orderBy;

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array("asc", "desc")))
               ? strtolower($_GET['orderType']) : $default_orderType;

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logDebugSQL = "";

    // print HTML prologue
    $title = t('Intro','replastconnect.php');
    $help = t('helpPage','replastconnect');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_common.php' ]);

    // The selected-backend provider binds the validated scalar filters.
    $_SESSION['reportType'] = 'reportsLastConnectionAttempts';
    $_SESSION['reportExport'] = array('source'=>'rep-lastconnect','type'=>'reportsLastConnectionAttempts','filters'=>array('username'=>$username,'startdate'=>$startdate,'enddate'=>$enddate,'radiusReply'=>$radiusReply));
    $reportPDO = null;
    $reportRows = array();
    $numrows = 0;
    try {
        $reportPDO = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        list($reportSQL, $reportBindings) = dalo_operator_query('rep-lastconnect', array('username'=>$username,'startdate'=>$startdate,'enddate'=>$enddate,'radiusReply'=>$radiusReply), $configValues);
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

        try {
            $reportRows = dalo_operator_rows($reportPDO, $reportSQL, $reportBindings, 'rep-lastconnect',
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
        if ($username_enc !== '') {
            $partial_query_params[] = sprintf("username=%s", urlencode($username));
        }
        if (!empty($radiusReply)) {
            $partial_query_params[] = sprintf("radiusReply=%s", $radiusReply);
        }

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
        foreach ($reportRows as $row) {
            $rowlen = count($row);

            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars(trim((string)($row[$i] ?? '')), ENT_QUOTES, 'UTF-8');
            }

            // The table that is being produced is in the format of:
            // +-------------+-------------+---------------+-----------+-----------+
            // | fullname    | user        | pass (opt.)   | reply     | date      |
            // +-------------+-------------+---------------+-----------+-----------+

            list($fullname, $user, $pass, $reply, $datetime) = $row;

            // fullname
            $fullname = (!empty($fullname) ? $fullname : t('all','NotAvailable'));

            // datetime
            $datetime = !empty($datetime) ? date('Y-m-d H:i:s', strtotime($datetime)) : t('all','NotAvailable');

            // reply
            $is_rejected = $reply === 'Access-Reject';

            $badge_class = $is_rejected ? 'text-bg-danger' : 'text-bg-success';
            $icon = $is_rejected ? 'x-circle-fill' : 'check-circle-fill';

            $reply = sprintf(
                '<span class="badge %s"><i class="bi bi-%s me-1"></i>%s</span>',
                $badge_class, $icon, $reply
            );

            $table_row = array( $user, $fullname );
            if (!$hiddenPassword) {
                $table_row[] = $pass;
            }

            $table_row[] = $reply;
            $table_row[] = $datetime;

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
