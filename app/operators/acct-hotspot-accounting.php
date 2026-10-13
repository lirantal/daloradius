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
    require_once __DIR__ . '/library/accounting_pages_pdo.php';
    unset($_SESSION['reportExport'], $_SESSION['reportTable'], $_SESSION['reportQuery'], $_SESSION['reportType']);
    try {
        dalo_accounting_validate_request($_GET);
    } catch (Throwable $exception) {
        http_response_code(400);
        exit('Invalid accounting filters');
    }
    if (isset($_REQUEST['page']) && !is_string($_REQUEST['page'])) { $_REQUEST['page'] = '1'; }

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'functions.php' ]);

    $hotspot = dalo_accounting_hotspots($_GET);

    $hotspot_enc = "";
    if (count($hotspot) > 0) {

        $hotspot_enc = htmlspecialchars($hotspot[0], ENT_QUOTES, 'UTF-8');

        if (count($hotspot) > 1) {
            $hotspot_enc .= ", &hellip;";
        }
    }

    $cols = array(
                    "radacctid" => t('all','ID'),
                    "name" => t('all','HotSpot'),
                    "username" => t('all','Username'),
                    "framedipaddress" => t('all','IPAddress'),
                    "acctstarttime" => t('all','StartTime'),
                    "acctstoptime" => t('all','StopTime'),
                    "acctsessiontime" => t('all','TotalTime'),
                    "acctinputoctets" => t('all','Upload'),
                    "acctoutputoctets" => t('all','Download'),
                    "acctterminatecause" => t('all','Termination'),
                    "nasipaddress" => t('all','NASIPAddress'),
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

    // init logging variables
    $log = "visited page: ";
    if (!empty($hotspot)) {
        $logQuery = sprintf("performed accounting query for %d hotspot(s) on page: ", count($hotspot));
    } else {
        $logQuery = "performed accounting query on page: ";
    }
    $logDebugSQL = "";


    // print HTML prologue
    $extra_js = array(
        "static/js/request.js",
        "static/js/readonly_info.js",
    );

    $title = t('Intro','accthotspot.php');
    $help = t('helpPage','accthotspotaccounting');

    print_html_prologue($title, $langCode, array(), $extra_js);

    if (!empty($hotspot_enc)) {
        $title .= " :: $hotspot_enc";
    }

    print_title_and_help($title, $help);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_common.php' ]);

    unset($_SESSION['reportExport'], $_SESSION['reportTable'], $_SESSION['reportQuery']);
    $_SESSION['reportType'] = "accountingGeneric";
    $_SESSION['reportExport'] = array(
        'source' => 'acct-hotspot-accounting',
        'type' => 'accountingGeneric',
        'filters' => array('hotspot' => $hotspot),
    );
    unset($_SESSION['reportTable'], $_SESSION['reportQuery']);

    $accountingPDO = null;
    $accountingRows = array();
    $numrows = 0;
    try {
        $accountingPDO = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        list($accountingSQL, $accountingBindings) = dalo_accounting_query('acct-hotspot-accounting', array('hotspot'=>$hotspot), $configValues);
        $numrows = dalo_accounting_count($accountingPDO, $accountingSQL, $accountingBindings, $configValues);
    } catch (Throwable $exception) {
        dalo_accounting_failure($exception);
    }

    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */
        
        // when $numrows is set, $maxPage is calculated inside this include file
        // must be included after opendb because it needs to read
        // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_numbering.php' ]);
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
        
        /* END */

        // we execute and log the actual query

        try {
            $accountingRows = dalo_accounting_rows($accountingPDO, $accountingSQL, $accountingBindings,
                'acct-hotspot-accounting', $orderBy, $orderType, $offset, $rowsPerPage);
        } catch (Throwable $exception) {
            dalo_accounting_failure($exception);
        }
        $per_page_numrows = count($accountingRows);

        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $tmp = array();
        if (count($hotspot) > 0) {
            foreach ($hotspot as $item) {
                $tmp[] = "hotspot[]=" . rawurlencode($item);
            }
        }

        $partial_query_string = (count($tmp) > 0) ? "&" . implode("&", $tmp) : "";

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
        print_table_top([ 'class' => 'table-sm' ]);

        // second line of table header
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);

        // closes table header, opens table body
        print_table_middle();

        // table content
        $count = 0;
        foreach ($accountingRows as $row) {
            $rawUsername = (string)($row[2] ?? '');
            $rawHotspot = (string)($row[1] ?? '');
            $rowlen = count($row);

            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars((string)($row[$i] ?? ''), ENT_QUOTES, 'UTF-8');
            }

            list($radAcctId, $hotspot, $username, $framedIPAddress, $acctStartTime, $acctStopTime,
                 $acctSessionTime, $acctInputOctets, $acctOutputOctets, $acctTerminateCause, $nasIPAddress) = $row;

            $acctSessionTime = time2str($acctSessionTime, true);
            $acctInputOctets = toxbyte($acctInputOctets);
            $acctOutputOctets = toxbyte($acctOutputOctets);

            if (dalo_accounting_exists($accountingPDO, $rawHotspot, 'CONFIG_DB_TBL_DALOHOTSPOTS')) {
                $ajax_id = "divContainerHotspotInfo_" . $count;
                $param = sprintf('hotspot=%s', urlencode($rawHotspot));
                $onclick = "daloInfo.hotspot('$ajax_id','$param')";

                $tooltip1 = [
                                'subject' => $hotspot,
                                'onclick' => $onclick,
                                'ajax_id' => $ajax_id,
                                'actions' => array(),
                            ];
                $tooltip1['actions'][] = [ 'href' => sprintf('mng-hs-edit.php?name=%s', urlencode($rawHotspot), ),
                                           'label' => t('Tooltip','HotspotEdit'), ];
                $tooltip1['actions'][] = [ 'href' => 'acct-hotspot-compare.php',
                                           'label' => t('all','Compare'), ];
                
                $tooltip1 = get_tooltip_list_str($tooltip1);
            } else {
                $tooltip1 = (!empty($hotspot)) ? $hotspot : "(n/a)";
            }

            if ($username !== '') {
                $ajax_id = "divContainerUserInfo_" . $count;
                $param = sprintf('username=%s', urlencode($rawUsername));
                $onclick = "daloInfo.user('$ajax_id','$param')";
            
                $tooltip2 = [
                                'subject' => $username,
                                'onclick' => $onclick,
                                'ajax_id' => $ajax_id,
                                'actions' => array(),
                            ];
                if (dalo_accounting_exists($accountingPDO, $rawUsername, 'CONFIG_DB_TBL_RADACCT')) {
                    $tooltip2['actions'][] = [ 'href' => sprintf('acct-username.php?username=%s', urlencode($rawUsername), ),
                                               'label' => t('button','UserAccounting'), ];
                }
                if (dalo_accounting_exists($accountingPDO, $rawUsername, 'CONFIG_DB_TBL_RADCHECK')) {
                    $tooltip2['actions'][] = [ 'href' => sprintf('mng-edit.php?username=%s', urlencode($rawUsername), ),
                                               'label' => t('Tooltip','UserEdit'), ];
                }
                
                $tooltip2 = get_tooltip_list_str($tooltip2);
            } else {
                $tooltip2 = "(n/a)";
            }

            if (preg_match(LOOSE_IP_REGEX, $framedIPAddress, $m) !== false) {
                $tooltip3 = [
                    'subject' => $framedIPAddress,
                    'actions' => array(),
                ];
                $tooltip3['actions'][] = [  'href' => sprintf('acct-ipaddress.php?ipaddress=%s', urlencode($framedIPAddress), ),
                                            'label' => t('button','IPAccounting'), ];
                
                $tooltip3 = get_tooltip_list_str($tooltip3);
            } else {
                $tooltip3 = (!empty($framedIPAddress)) ? $framedIPAddress : "(n/a)";
            }

            if (preg_match(LOOSE_IP_REGEX, $nasIPAddress, $m) !== false) {
                $tooltip4 = [
                    'subject' => $nasIPAddress,
                    'actions' => array(),
                ];
                $tooltip4['actions'][] = [  'href' => sprintf('acct-nasipaddress.php?nasipaddress=%s', urlencode($nasIPAddress), ),
                                            'label' => t('button','NASIPAccounting'), ];
                
                $tooltip4 = get_tooltip_list_str($tooltip4);
            } else {
                $tooltip4 = (!empty($nasIPAddress)) ? $nasIPAddress : "(n/a)";
            }

            // define table row
            $table_row = array( $radAcctId, $tooltip1, $tooltip2, $tooltip3, $acctStartTime, $acctStopTime,
                                $acctSessionTime, $acctInputOctets, $acctOutputOctets, $acctTerminateCause, $tooltip4);

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
        include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);
    }

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);

    if (isset($failureMsg) && $numrows > 0) {
        include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);
    }
    if (empty($numrows) || isset($failureMsg)) {
        unset($_SESSION['reportExport'], $_SESSION['reportTable'], $_SESSION['reportQuery'], $_SESSION['reportType']);
    }
    unset($accountingRows, $accountingPDO);
    print_footer_and_html_epilogue();
