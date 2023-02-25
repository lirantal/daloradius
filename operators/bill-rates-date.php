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

    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");
    
    //setting values for the order by and order type variables
    // and in other cases we partially strip some character,
    // and leave validation/escaping to other functions used later in the script
    $ratename = (array_key_exists('ratename', $_GET) && isset($_GET['ratename']))
              ? trim(str_replace("%", "", $_GET['ratename'])) : "";
    $ratename_enc = (!empty($ratename)) ? htmlspecialchars($ratename, ENT_QUOTES, 'UTF-8') : "";

    $username = (array_key_exists('username', $_GET) && isset($_GET['username']))
              ? trim(str_replace("%", "", $_GET['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    
    // in other cases we just check that syntax is ok
    $startdate = (array_key_exists('startdate', $_GET) && isset($_GET['startdate']) &&
                  preg_match(DATE_REGEX, $_GET['startdate'], $m) !== false &&
                  checkdate($m[2], $m[3], $m[1]))
               ? $_GET['startdate'] : "";

    $enddate = (array_key_exists('enddate', $_GET) && isset($_GET['enddate']) &&
                preg_match(DATE_REGEX, $_GET['enddate'], $m) !== false &&
                checkdate($m[2], $m[3], $m[1]))
             ? $_GET['enddate'] : "";

    $cols = array(
                    "username" => t('all','Username'),
                    "nasipaddress" => t('all','NASIPAddress'),
                    "acctstarttime" => t('all','LastLoginTime'),
                    "acctsessiontime" => t('all','TotalTime'),
                    t('all','Billed')
                 );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);
    
    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }
    
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  preg_match(ORDER_TYPE_REGEX, $_GET['orderType']) !== false)
               ? strtolower($_GET['orderType']) : "asc";

    // init loggin variables
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";
    $logDebugSQL = "";

    
    // print HTML prologue
    $extra_css = array();
    
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js",
    );
    
    $title = t('Intro','billratesdate.php');
    $help = t('helpPage','billratesdate');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    print_title_and_help($title, $help);

    if (!empty($ratename)) {
        
        include('../common/includes/db_open.php');
        
        $sql_WHERE = array();
        $partial_query_params = array();

        if (!empty($startdate)) {
            $sql_WHERE[] = sprintf("AcctStartTime > '%s'", $dbSocket->escapeSimple($startdate));
            $partial_query_params[] = sprintf("startdate=%s", $startdate);
        }

        if (!empty($enddate)) {
            $sql_WHERE[] = sprintf("AcctStartTime < '%s'", $dbSocket->escapeSimple($enddate));
            $partial_query_params[] = sprintf("enddate=%s", $enddate);
        }

        if (!empty($username)) {
            $sql_WHERE[] = sprintf("username='%s'", $dbSocket->escapeSimple($username));
            $partial_query_params[] = sprintf("username=%s", urlencode($username_enc));
        }
        
        $sql_WHERE[] = sprintf("ratename='%s'", $dbSocket->escapeSimple($ratename));
        $partial_query_params[] = sprintf("ratename=%s", urlencode($ratename_enc));
        

        include 'include/management/pages_common.php';
        include 'include/management/pages_numbering.php';        // must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

        // we can only use the $dbSocket after we have included '../common/includes/db_open.php' which initialzes the connection and the $dbSocket object
        $username = $dbSocket->escapeSimple($username);
        $startdate = $dbSocket->escapeSimple($startdate);
        $enddate = $dbSocket->escapeSimple($enddate);
        $ratename = $dbSocket->escapeSimple($ratename);

        include_once('include/management/userBilling.php');
        userBillingRatesSummary($username, $startdate, $enddate, $ratename, 1);                // draw the billing rates summary table


        // get rate type
        $sql = sprintf("SELECT rateType FROM %s WHERE rateName='%s'", $configValues['CONFIG_DB_TBL_DALOBILLINGRATES'], $ratename);
        $res = $dbSocket->query($sql);

        if ($res->numRows() == 0) {
            $failureMsg = "Rate was not found in database, check again please";
        } else {

            $row = $res->fetchRow();
            list($ratetypenum, $ratetypetime) = explode("/", $row[0]);

            switch ($ratetypetime) {                  // we need to translate any kind of time into seconds,
                                                      // so a minute is 60 seconds, an hour is 3600,
                case "second":                        // and so on...
                    $multiplicate = 1;
                    break;
                case "minute":
                    $multiplicate = 60;
                    break;
                case "hour":
                    $multiplicate = 3600;
                    break;
                case "day":
                    $multiplicate = 86400;
                    break;
                case "week":
                    $multiplicate = 604800;
                    break;
                case "month":
                    $multiplicate = 187488000;        // a month is 31 days
                    break;
                default:
                    $multiplicate = 0;
                    break;
            }

            // then the rate cost would be the amount of seconds times the prefix multiplicator thus:
            $rateDivisor = $ratetypenum * $multiplicate;
        }

        $sql = sprintf("SELECT DISTINCT(ra.username), ra.NASIPAddress, ra.AcctStartTime, ra.AcctSessionTime, dbr.rateCost
                          FROM %s AS ra, %s AS dbr WHERE dbr.rateName='%s' ",
                       $configValues['CONFIG_DB_TBL_RADACCT'], $configValues['CONFIG_DB_TBL_DALOBILLINGRATES'], $ratename);

        if (count($sql_WHERE) > 0) {
            $sql .= " AND " . implode(" AND ", $sql_WHERE);
        }
        $res = $dbSocket->query($sql);
        $numrows = $res->numRows();

        if ($numrows > 0) {
            /* START - Related to pages_numbering.php */
            
            // when $numrows is set, $maxPage is calculated inside this include file
            include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                                  // the CONFIG_IFACE_TABLES_LISTING variable from the config file
            
            // here we decide if page numbers should be shown
            $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
            
            $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";

            $per_page_numrows = $res->numRows();

            $partial_query_string = (count($partial_query_params) > 0)
                                  ? ("&" . implode("&", $partial_query_params)) : "";
                                  
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

            print_table_prologue($descriptors);
            
            // print table top
            print_table_top();
            
            // second line of table header
            printTableHead($cols, $orderBy, $orderType, $partial_query_string);

            // closes table header, opens table body
            print_table_middle();

            $sumBilled = 0;
            $sumSession = 0;

            while($row = $res->fetchRow()) {
                foreach ($row as $i => $value) {
                    $row[$i] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }

                list($username, $nasIPAddress, $acctStartTime, $sessionTime, $rateCost) = $row;

                $sessionTime = $row[3];
                $rateCost = $row[4];
                $billed = ($sessionTime / $rateDivisor) * $rateCost;
                $sumBilled += $billed;
                $sumSession += $sessionTime;

                $sessionTime = time2str($sessionTime);
                $billed = number_format($billed, 2);

                $table_row = array($username, $nasIPAddress, $acctStartTime, $sessionTime, $billed);

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
            $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
            printLinks($links, $drawNumberLinks);
    
        } else {
            $failureMsg = "No entries retrieved";
        }
        
        include('../common/includes/db_close.php');
        
    } else {
        $failureMsg = "Rate name is required";
        
    }
    
    include_once("include/management/actionMessages.php");

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
