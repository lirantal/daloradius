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

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include_once('../common/includes/config_read.php');
    include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logDebugSQL = "";

    $cols = array(
                    "hotspot" => t('all','HotSpot'),
                    "uniqueusers" => t('all','UniqueUsers'),
                    "totalhits" => t('all','TotalHits'),
                    "avgsessiontime" => t('all','AverageTime'),
                    "totaltime" => t('all','TotalTime'),
                    "sumInputOctets" => "Total Uploads",
                    "sumOutputOctets" => "Total Downloads"
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


    // print HTML prologue
    $extra_css = array();

    $extra_js = array(
        "static/js/ajax.js",
        "static/js/dynamic_attributes.js",
    );

    $title = t('Intro','accthotspotcompare.php');
    $help = t('helpPage','accthotspotcompare');

    print_html_prologue($title, $langCode, $extra_css, $extra_js);




    print_title_and_help($title, $help);

    include('../common/includes/db_open.php');
    include('include/management/pages_common.php');


    $sql = sprintf("SELECT hs.name AS hotspot, COUNT(DISTINCT(UserName)) AS uniqueusers, COUNT(radacctid) AS totalhits,
                           AVG(AcctSessionTime) AS avgsessiontime, SUM(AcctSessionTime) AS totaltime,
                           AVG(AcctInputOctets) AS avgInputOctets, SUM(AcctInputOctets) AS sumInputOctets,
                           AVG(AcctOutputOctets) AS avgOutputOctets, SUM(AcctOutputOctets) AS sumOutputOctets
                      FROM %s AS ra JOIN %s AS hs ON ra.calledstationid=hs.mac
                     GROUP BY hotspot", $configValues['CONFIG_DB_TBL_RADACCT'],
                                        $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";

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


        // set navbar stuff
        $navkeys = array(
                                array( 'AccountInfo', "Account Info" ),
                                array( 'UniqueUsers', "Unique Users Chart" ),
                                array( 'LoginHits', "Login Hits Chart" ),
                                array( 'TotalSessionTime', "Total Sess. Time Chart" ),
                                array( 'AvgSessionTime', "Average Sess. Time Chart" ),
                            );

        // print navbar controls
        print_tab_header($navkeys);

        // open tab wrapper
        open_tab_wrapper();

        // tab 0
        open_tab($navkeys, 0, true);

        // print table top
        print_table_top();

        // second line of table header
        printTableHead($cols, $orderBy, $orderType);

        // closes table header, opens table body
        print_table_middle();

        while ($row = $res->fetchRow()) {

            $rowlen = count($row);

            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }

            list($hotspot, $uniqueusers, $totalhits, $avgsessiontime, $totaltime,
                 $avgInputOctets, $sumInputOctets, $avgOutputOctets, $sumOutputOctets) = $row;

            $avgsessiontime = time2str($avgsessiontime);
            $totaltime = time2str($totaltime);

            $sumInputOctets = toxbyte($sumInputOctets);
            $sumOutputOctets = toxbyte($sumOutputOctets);

            // build table row
            $table_row = array( $hotspot, $uniqueusers, $totalhits, $avgsessiontime, $totaltime, $sumInputOctets, $sumOutputOctets );

            // print table row
            print_table_row($table_row);

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
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printLinks($links, $drawNumberLinks);

        // tab 0
        close_tab($navkeys, 0);

        $categories = array( "unique_users", "login_hits", "total_session_time", "avg_session_time", );
        $img_format = '<div class="my-3 text-center"><img src="%s" alt="%s"></div>';

        foreach ($categories as $i => $category) {

            // tab $i+1
            open_tab($navkeys, $i+1);

            $src = sprintf("library/graphs/hotspot_details.php?category=%s", $category);
            $alt = sprintf("hotspot details (category: %s)", str_replace("_", " ", $category));
            printf($img_format, $src, $alt);

            close_tab($navkeys, $i+1);

        }

        // open tab wrapper
        close_tab_wrapper();

    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }

    include('../common/includes/db_close.php');

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
