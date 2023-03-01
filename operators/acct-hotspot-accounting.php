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

	include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    $hotspot = array();
    if (isset($_GET['hotspot']) && !empty($_GET['hotspot'])) {
        $hotspot = (is_array($_GET['hotspot'])) ? $_GET['hotspot'] : array( $_GET['hotspot'] );
    }

    $tmp = array();
    foreach ($hotspot as $item) {
        $item = str_replace("%", "", trim($item));
        if (empty($item) && in_array($item, $tmp)) {
            continue;
        }

        $tmp[] = $item;
    }

    $hotspot = $tmp;

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
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js",
    );

    $title = t('Intro','accthotspot.php');
    $help = t('helpPage','accthotspotaccounting');

    print_html_prologue($title, $langCode, array(), $extra_js);

    if (!empty($hotspot_enc)) {
        $title .= " :: $hotspot_enc";
    }

    print_title_and_help($title, $help);

    include('../common/includes/db_open.php');
    include('include/management/pages_common.php');

    $tmp = array();
    if (count($hotspot) > 0) {
        foreach ($hotspot as $item) {
            $tmp[] = $dbSocket->escapeSimple($item);
        }
    }

    $sql_WHERE = (count($tmp) > 0)
               ? sprintf(" WHERE name IN ('%s')", implode("', '", $tmp))
               : "";

    // setup php session variables for exporting
    $_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADACCT'];
    $_SESSION['reportQuery'] = $sql_WHERE;
    $_SESSION['reportType'] = "accountingGeneric";


    $sql = sprintf("SELECT ra.RadAcctId, dhs.name AS hotspot, ra.username, ra.FramedIPAddress, ra.AcctStartTime,
                               ra.AcctStopTime, ra.AcctSessionTime, ra.AcctInputOctets, ra.AcctOutputOctets,
                               ra.AcctTerminateCause, ra.NASIPAddress
                          FROM %s AS ra LEFT JOIN %s AS dhs ON ra.calledstationid=dhs.mac",
                       $configValues['CONFIG_DB_TBL_RADACCT'], $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'])
         . $sql_WHERE;
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
        $tmp = array();
        if (count($hotspot) > 0) {
            foreach ($hotspot as $item) {
                $tmp[] = "hotspot[]=" . htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
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

            list($radAcctId, $hotspot, $username, $framedIPAddress, $acctStartTime, $acctStopTime,
                 $acctSessionTime, $acctInputOctets, $acctOutputOctets, $acctTerminateCause, $nasIPAddress) = $row;

            $acctSessionTime = time2str($acctSessionTime);
            $acctInputOctets = toxbyte($acctInputOctets);
            $acctOutputOctets = toxbyte($acctOutputOctets);

            $ajax_id = "divContainerHotspotInfo_" . $count;
            $param = sprintf('hotspot=%s', urlencode($hotspot));
            $onclick = "ajaxGeneric('library/ajax/hotspot_info.php','retHotspotGeneralStat','$ajax_id','$param')";
            $tooltip1 = array(
                                'subject' => $hotspot,
                                'onclick' => $onclick,
                                'ajax_id' => $ajax_id,
                                'actions' => array(),
                             );
            $tooltip1['actions'][] = array( 'href' => sprintf('mng-hs-edit.php?name=%s', urlencode($hotspot), ), 'label' => t('Tooltip','HotspotEdit'), );
            $tooltip1['actions'][] = array( 'href' => 'acct-hotspot-compare.php', 'label' => t('all','Compare'), );


            $ajax_id = "divContainerUserInfo_" . $count;
            $param = sprintf('username=%s', urlencode($username));
            $onclick = "ajaxGeneric('library/ajax/user_info.php','retBandwidthInfo','$ajax_id','$param')";
            $tooltip2 = array(
                                'subject' => $username,
                                'onclick' => $onclick,
                                'ajax_id' => $ajax_id,
                                'actions' => array(),
                             );
            $tooltip2['actions'][] = array( 'href' => sprintf('mng-edit.php?username=%s', urlencode($username), ), 'label' => t('Tooltip','UserEdit'), );

            $tooltip1 = get_tooltip_list_str($tooltip1);
            $tooltip2 = get_tooltip_list_str($tooltip2);

            // define table row
            $table_row = array( $radAcctId, $tooltip1, $tooltip2, $framedIPAddress, $acctStartTime, $acctStopTime,
                                $acctSessionTime, $acctInputOctets, $acctOutputOctets, $acctTerminateCause, $nasIPAddress);

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
