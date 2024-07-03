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
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
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

    // validate this parameter before including menu
    $onlyactive = (array_key_exists('only-active', $_GET) && isset($_GET['only-active']));
    
    // validate this parameter before including menu
    $nasipaddress = (array_key_exists('nasipaddress', $_GET) && isset($_GET['nasipaddress']) &&
                  preg_match(LOOSE_IP_REGEX, $_GET['nasipaddress'], $m) !== false) ? $_GET['nasipaddress'] : "";
    $nasipaddress_enc = (!empty($nasipaddress)) ? htmlspecialchars($nasipaddress, ENT_QUOTES, 'UTF-8') : "";

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

    // validating user passed parameters

    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($cols)))
             ? $_GET['orderBy'] : array_keys($cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  preg_match(ORDER_TYPE_REGEX, $_GET['orderType']) !== false)
               ? strtolower($_GET['orderType']) : "desc";

    // init logging variables
    $log = "visited page: ";
    $logQuery = sprintf("performed query for %s on page: ",
                        ((!empty($nasipaddress)) ? "NAS IP address [$nasipaddress]" : "all NAS IP addresses"));
    $logDebugSQL = "";
    
    // print HTML prologue
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js",
    );
    
    $title = t('Intro','acctnasipaddress.php');
    $help = t('helpPage','acctnasipaddress');
    
    print_html_prologue($title, $langCode, array(), $extra_js);

    print_title_and_help($title, $help);

    // we can only use the $dbSocket after we have included '../common/includes/db_open.php' which initialzes the connection and the $dbSocket object
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_common.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);

    $sql_WHERE = (!empty($nasipaddress))
               ? sprintf(" WHERE NASIPAddress LIKE '%%%s%%'", $dbSocket->escapeSimple($nasipaddress))
               : "";

    // setup php session variables for exporting
    $_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADACCT'];
    $_SESSION['reportQuery'] = $sql_WHERE;
    $_SESSION['reportType'] = "accountingGeneric";

    $sql = "SELECT ra.RadAcctId, dh.name as hotspot, ra.UserName, ra.FramedIPAddress, ra.AcctStartTime, ra.AcctStopTime,
                   ra.AcctSessionTime, ra.AcctInputOctets, ra.AcctOutputOctets,
                   CASE WHEN ra.AcctTerminateCause = '0' THEN 'Unknown' ELSE ra.AcctTerminateCause END AS AcctTerminateCause,
                   ra.NASIPAddress
              FROM %s AS ra LEFT JOIN %s AS dh ON ra.calledstationid=dh.mac";
    
    $sql = sprintf($sql, $configValues['CONFIG_DB_TBL_RADACCT'], $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']) . $sql_WHERE;
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();
    
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
        $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL = "$sql;\n";
        
        $per_page_numrows = $res->numRows();
        
        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = (!empty($nasipaddress_enc) ? "&nasipaddress=$nasipaddress_enc" : "");

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
        print_table_top(['class' => 'table-sm']);

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
            
            list($radAcctId, $hotspot, $username, $framedIPAddress, $acctStartTime, $acctStopTime,
                 $acctSessionTime, $acctInputOctets, $acctOutputOctets, $acctTerminateCause, $nasIPAddress) = $row;
                
            $acctSessionTime = time2str($acctSessionTime, true);
            $acctInputOctets = toxbyte($acctInputOctets);
            $acctOutputOctets = toxbyte($acctOutputOctets);
            
            if (hotspots_exists($dbSocket, $hotspot)) {
                $ajax_id = "divContainerHotspotInfo_" . $count;
                $param = sprintf('hotspot=%s', urlencode($hotspot));
                $onclick = "ajaxGeneric('library/ajax/hotspot_info.php','retHotspotGeneralStat','$ajax_id','$param')";

                $tooltip1 = [
                                'subject' => $hotspot,
                                'onclick' => $onclick,
                                'ajax_id' => $ajax_id,
                                'actions' => array(),
                            ];
                $tooltip1['actions'][] = [ 'href' => sprintf('mng-hs-edit.php?name=%s', urlencode($hotspot), ),
                                           'label' => t('Tooltip','HotspotEdit'), ];
                $tooltip1['actions'][] = [ 'href' => 'acct-hotspot-compare.php',
                                           'label' => t('all','Compare'), ];
                
                $tooltip1 = get_tooltip_list_str($tooltip1);
            } else {
                $tooltip1 = (!empty($hotspot)) ? $hotspot : "(n/a)";
            }

            if (!empty($username)) {
                $ajax_id = "divContainerUserInfo_" . $count;
                $param = sprintf('username=%s', urlencode($username));
                $onclick = "ajaxGeneric('library/ajax/user_info.php','retBandwidthInfo','$ajax_id','$param')";
            
                $tooltip2 = [
                                'subject' => $username,
                                'onclick' => $onclick,
                                'ajax_id' => $ajax_id,
                                'actions' => array(),
                            ];
                if (user_exists($dbSocket, $username, 'CONFIG_DB_TBL_RADACCT')) {
                    $tooltip2['actions'][] = [ 'href' => sprintf('acct-username.php?username=%s', urlencode($username), ),
                                               'label' => t('button','UserAccounting'), ];
                }
                if (user_exists($dbSocket, $username, 'CONFIG_DB_TBL_RADCHECK')) {
                    $tooltip2['actions'][] = [ 'href' => sprintf('mng-edit.php?username=%s', urlencode($username), ),
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
                $tooltip4['actions'][] = [  'href' => sprintf('acct-nasipaddress.php?ipaddress=%s', urlencode($nasIPAddress), ),
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
        $failureMsg = "Nothing to display";
        include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);
    }
    
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    
    print_footer_and_html_epilogue();

