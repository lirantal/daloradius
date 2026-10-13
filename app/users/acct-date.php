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
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include("library/checklogin.php");
    $login_user = $_SESSION['login_user'];
    // A zero-row page must not leave another report exportable.
    unset($_SESSION['userReportExport'], $_SESSION['export_query'],
          $_SESSION['export_items'], $_SESSION['export_title']);
    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");
    
    $username = $login_user;
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

    // validate this parameter before including menu
    $startdate = (array_key_exists('startdate', $_GET) && is_string($_GET['startdate']) &&
                  trim($_GET['startdate']) !== '' &&
                  preg_match(DATE_REGEX, trim($_GET['startdate']), $m) === 1 &&
                  checkdate((int) $m[2], (int) $m[3], (int) $m[1]))
               ? trim($_GET['startdate']) : "";

    $enddate = (array_key_exists('enddate', $_GET) && is_string($_GET['enddate']) &&
                trim($_GET['enddate']) !== '' &&
                preg_match(DATE_REGEX, trim($_GET['enddate']), $m) === 1 &&
                checkdate((int) $m[2], (int) $m[3], (int) $m[1]))
             ? trim($_GET['enddate']) : "";
    
    $cols = array(
                    "radacctid" => t('all','ID'),
                    "hotspot" => t('all','HotSpot'),
                    "nasipaddress" => t('all','NASIPAddress'),
                    "framedipaddress" => t('all','IPAddress'),
                    "acctstarttime" => t('all','StartTime'),
                    "acctstoptime" => t('all','StopTime'),
                    "acctsessiontime" => t('all','TotalTime'),
                    "acctinputoctets" => t('all','Upload'),
                    "acctoutputoctets" => t('all','Download'),
                    "acctterminatecause" => t('all','Termination'),
                 );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);
    
    $orderBy = (array_key_exists('orderBy', $_GET) && is_string($_GET['orderBy']) && !empty($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($cols)))
             ? $_GET['orderBy'] : array_keys($cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && is_string($_GET['orderType']) && !empty($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array("asc", "desc")))
               ? strtolower($_GET['orderType']) : "asc";
    
    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query for user [$username] and start date [$startdate] and end date [$enddate] on page: ";
    $logDebugSQL = "";
	
	$title = t('Intro','acctdate.php');
    $help = t('helpPage','acctdate');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    require_once __DIR__ . '/library/portal_pages_pdo.php';
    include('include/management/pages_common.php');
    $portalPdo = null;
    try {
    $portalPdo = dalo_portal_handle($configValues);
    list($sql, $portalParams) = dalo_portal_report_query('acct-date', $login_user, $configValues, array('startdate' => $startdate, 'enddate' => $enddate));
    $numrows = dalo_portal_report_count($portalPdo, $sql, $portalParams, 'acct-date');
    $partial_query_params = array();
    if ($startdate !== '') { $partial_query_params[] = 'startdate=' . rawurlencode($startdate); }
    if ($enddate !== '') { $partial_query_params[] = 'enddate=' . rawurlencode($enddate); }
    $partial_query_params[] = 'username=' . rawurlencode($login_user);

    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */
            
        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                              // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
        
        // The exporter builds its own user-scoped, unpaginated PDO query.
        $portalRows = dalo_portal_report_rows($portalPdo, $sql, $portalParams, 'acct-date',
            $orderBy, $orderType, $offset, $rowsPerPage);
        $per_page_numrows = count($portalRows);
        $_SESSION['userReportExport'] = array(
            'source' => 'acct-date',
            'filters' => array('startdate' => $startdate, 'enddate' => $enddate),
        );


        $partial_query_string = (count($partial_query_params) > 0)
                              ? ("&" . implode("&", $partial_query_params)) : "";
                              
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
        $descriptors['end'][] = get_csv_export_control();
        print_table_prologue($descriptors);

        // print table top
        print_table_top();

        // second line of table header
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);

        // closes table header, opens table body
        print_table_middle();

        // table content
        $count = 0;
        
        foreach ($portalRows as $row) {
            $rowlen = count($row);

            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars((string) ($row[$i] ?? ''), ENT_QUOTES, 'UTF-8');
            }
            
            list($radAcctId, $hotspot, $nasIPAddress, $framedIPAddress, $acctStartTime, $acctStopTime,
                 $acctSessionTime, $acctInputOctets, $acctOutputOctets, $acctTerminateCause) = $row;
            
            $acctSessionTime = intval($acctSessionTime);
            if ($acctSessionTime === 0) {
                $acctSessionTime = "(n/d)";
            } else {
                $acctSessionTime = time2str($acctSessionTime);
            }
            
            $acctInputOctets = toxbyte($acctInputOctets);
            $acctOutputOctets = toxbyte($acctOutputOctets);
            
            $hotspot = (!empty($hotspot)) ? $hotspot : "(n/a)";
            
            // define table row
            $table_row = array( $radAcctId, $hotspot, $nasIPAddress, $framedIPAddress, $acctStartTime,
                                $acctStopTime, $acctSessionTime, $acctInputOctets, $acctOutputOctets, $acctTerminateCause);

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
    }

    include_once("include/management/actionMessages.php");

    } catch (Throwable $exception) {
        unset($_SESSION['userReportExport']);
        $failureMsg = 'Report unavailable';
        include('include/management/actionMessages.php');
    } finally { $portalPdo = null; }

    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
