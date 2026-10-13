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
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'functions.php' ]);

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    require_once __DIR__ . '/library/accounting_advanced_pdo.php';
    unset($_SESSION['reportExport'], $_SESSION['reportTable'], $_SESSION['reportQuery'], $_SESSION['reportType']);
    $accountingPDO = null;
    try {
        dalo_accounting_validate_request($_GET);
        foreach (array('where_value','where_field','where_operator') as $key) {
            if (isset($_GET[$key])) { dalo_accounting_scalar($_GET, $key); }
        }
        $accountingPDO = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        list($acct_custom_query_options_all, $acct_custom_query_options_default) = get_accounting_custom_query_options(
            $accountingPDO, $configValues['CONFIG_DB_TBL_RADACCT'],
            $acct_custom_query_options_all, $acct_custom_query_options_default);
        $sqlfields = dalo_advanced_columns($_GET['sqlfields'] ?? null,
            $acct_custom_query_options_all, $acct_custom_query_options_default);
    } catch (InvalidArgumentException $exception) {
        http_response_code(400);
        exit('Invalid accounting filters');
    } catch (Throwable $exception) {
        dalo_accounting_failure($exception);
        $sqlfields = $acct_custom_query_options_default;
    }
    if (isset($_REQUEST['page']) && !is_string($_REQUEST['page'])) { $_REQUEST['page'] = '1'; }

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    $cols = [];
    foreach ($sqlfields as $sqlfield) {
        $cols[$sqlfield] = $sqlfield;
    }
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);

    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], $acct_custom_query_options_all))
             ? $_GET['orderBy'] : $acct_custom_query_options_all[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array("asc", "desc")))
               ? strtolower($_GET['orderType']) : "asc";

    $date_default = date_range_default('current_month');

    $startdate = dalo_accounting_date($_GET, 'startdate', $date_default['start']);

    $enddate = dalo_accounting_date($_GET, 'enddate', $date_default['end']);

    $valid_operators = array("equals" => "=", "contains" => "LIKE");
    $where_operator = (array_key_exists('where_operator', $_GET) && !empty($_GET['where_operator']) &&
                       in_array($_GET['where_operator'], array_keys($valid_operators)))
                    ? $_GET['where_operator'] : "";

    $where_field = (array_key_exists('where_field', $_GET) && !empty($_GET['where_field']) &&
                    in_array($_GET['where_field'], $acct_custom_query_options_all))
                 ? $_GET['where_field'] : "";

    $where_value = dalo_accounting_scalar($_GET, 'where_value');

    $where_value_enc = ($where_value !== '') ? htmlspecialchars($where_value, ENT_QUOTES, 'UTF-8') : "";

    //feed the sidebar variables
    $accounting_custom_startdate = $startdate;
    $accounting_custom_enddate = $enddate;
    $accounting_custom_value = $where_value_enc;

    // print HTML prologue
    $extra_js = [ "static/js/request.js", "static/js/readonly_info.js", ];

    $title = t('Intro','acctcustomquery.php');
    $help = t('helpPage','acctcustomquery');

    print_html_prologue($title, $langCode, [], $extra_js);
    print_title_and_help($title, $help);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_common.php' ]);
    // preparing the custom query

    $partial_query_string_pieces = array();
    foreach ($sqlfields as $sqlfield) { $partial_query_string_pieces[] = 'sqlfields[]=' . rawurlencode($sqlfield); }
    foreach (array('startdate'=>$startdate,'enddate'=>$enddate,'where_field'=>$where_field,
                   'where_operator'=>$where_operator,'where_value'=>$where_value) as $key=>$value) {
        if ($value !== '') { $partial_query_string_pieces[] = $key . '=' . rawurlencode($value); }
    }
    $numrows = 0;
    $accountingRows = array();
    if (!isset($failureMsg)) {
        try {
            list($accountingSQL, $accountingBindings) = dalo_advanced_custom_query($configValues, $sqlfields,
                $acct_custom_query_options_all, $startdate, $enddate, $where_field, $where_operator, $where_value);
            $numrows = dalo_accounting_count($accountingPDO, $accountingSQL, $accountingBindings, $configValues);
        } catch (InvalidArgumentException $exception) {
            $failureMsg = 'Invalid accounting predicate';
        } catch (Throwable $exception) { dalo_accounting_failure($exception); }
    }

    if ($numrows > 0) {
        // when $numrows is set, $maxPage is calculated inside this include file
        // must be included after opendb because it needs to read
        // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_numbering.php' ]);

        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;

        try {
            $accountingRows = dalo_advanced_rows($accountingPDO, $accountingSQL, $accountingBindings,
                $orderBy, $orderType, $acct_custom_query_options_all, $offset, $rowsPerPage, true);
        } catch (Throwable $exception) { dalo_accounting_failure($exception); }
        $per_page_numrows = count($accountingRows);

        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = (count($partial_query_string_pieces) > 0)
                              ? "&" . implode("&", $partial_query_string_pieces) : "";

        $descriptors = [];

        $params = [
                    'num_rows' => $numrows,
                    'rows_per_page' => $rowsPerPage,
                    'page_num' => $pageNum,
                    'order_by' => $orderBy,
                    'order_type' => $orderType,
                    'partial_query_string' => $partial_query_string,
                  ];
        $descriptors['center'] = [ 'draw' => $drawNumberLinks, 'params' => $params ];

        print_table_prologue($descriptors);

        // print table top
        print_table_top(['class' => 'table-sm']);

        // second line of table header
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);

        // closes table header, opens table body
        print_table_middle();

        // table content
        $count = 0;
        foreach ($accountingRows as $row) {
            printf('<tr id="row-%d">', $count);
            foreach ($sqlfields as $field) {

                switch (strtolower($field)) {
                    case 'acctinputoctets':
                    case 'acctoutputoctets':
                        $value = toxbyte($row[$field]);
                        break;

                    case 'acctsessiontime':
                        $value = time2str($row[$field], true);
                        break;

                    case 'acctterminatecause':
                        $value = ($row[$field] == "0") ? 'Unknown' : htmlspecialchars((string)($row[$field] ?? ''), ENT_QUOTES, 'UTF-8');
                        break;

                    case 'username':
                        if (isset($row[$field]) && (string)$row[$field] !== '') {
                            $ajax_id = "divContainerUserInfo_" . $count;
                            $param = sprintf('username=%s', urlencode($row[$field]));
                            $onclick = "daloInfo.user('$ajax_id','$param')";

                            $value = [
                                        'subject' => htmlspecialchars((string)($row[$field] ?? ''), ENT_QUOTES, 'UTF-8'),
                                        'onclick' => $onclick,
                                        'ajax_id' => $ajax_id,
                                        'actions' => [],
                                     ];

                            $value['actions'][] = [ 'href' => sprintf('acct-username.php?username=%s', urlencode($row[$field]), ),
                                                    'label' => t('button','UserAccounting'), ];

                            if (dalo_accounting_exists($accountingPDO, (string)($row[$field] ?? ''), 'CONFIG_DB_TBL_RADCHECK')) {
                                $value['actions'][] = [ 'href' => sprintf('mng-edit.php?username=%s', urlencode($row[$field]), ),
                                                        'label' => t('Tooltip','UserEdit'), ];
                            }

                            $value = get_tooltip_list_str($value);
                        } else {
                            $value = "(n/a)";
                        }
                        break;

                    case 'realm':
                    case 'nasipaddress':
                    case 'nasporttype':
                    case 'calledstationid':
                    case 'callingstationid':
                    case 'servicetype':
                    case 'framedipaddress':
                    case 'framedipv6address':

                        if (isset($row[$field]) && (string)$row[$field] !== '') {
                            $filtered_query_string_pieces = [];

                            foreach ($partial_query_string_pieces as $query_piece) {
                                if (!preg_match('/^where_/', $query_piece)) {
                                    $filtered_query_string_pieces[] = $query_piece;
                                }
                            }

                            $filtered_query_string_pieces[] = sprintf("where_field=%s", $field);
                            $filtered_query_string_pieces[] =
                                sprintf("where_value=%s", urlencode((string)($row[$field] ?? '')));

                            $value = [
                                'subject' => htmlspecialchars((string)($row[$field] ?? ''), ENT_QUOTES, 'UTF-8'),
                                'actions' => []
                            ];

                            foreach (array_keys($valid_operators) as $valid_operator) {
                                $query_string_with_operator = $filtered_query_string_pieces;
                                $query_string_with_operator[] = sprintf("where_operator=%s", $valid_operator);
                                $query_string = (count($query_string_with_operator) > 0) ? "&" . implode("&", $query_string_with_operator) : "";
                                $href = get_link_href($query_string, $pageNum, $orderBy, $orderType);
                                $value['actions'][] = [ 'href' => $href, 'label' => $valid_operator ];
                            }

                            $value = get_tooltip_list_str($value);
                        } else {
                            $value = (isset($row[$field]) && (string)$row[$field] !== '') ? $row[$field] : "(n/a)";
                        }
                        break;


                    default:
                        $value = htmlspecialchars((string)($row[$field] ?? ''), ENT_QUOTES, 'UTF-8');
                        break;

                }

                printf("<td>%s</td>", $value);
            }
            echo '</tr>';
            $count++;
        }

        // close tbody,
        // print tfoot
        // and close table + form (if any)
        $table_foot = [
                        'num_rows' => $numrows,
                        'rows_per_page' => $per_page_numrows,
                        'colspan' => $colspan,
                        'multiple_pages' => $drawNumberLinks
                      ];
        print_table_bottom([ 'table_foot' => $table_foot ]);

        // get and print "links"
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
        printLinks($links, $drawNumberLinks);

    } else {
        $failureMsg = $failureMsg ?? "Nothing to display";
        include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);
    }

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    if (isset($failureMsg) && $numrows > 0) {
        include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);
    }
    unset($accountingPDO, $accountingRows);
    print_footer_and_html_epilogue();
