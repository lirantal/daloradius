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

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");
    
    require_once 'library/billing_rates_pdo.php';
    $input_error = false; $ratename = $username = '';
    try {
        dalo_catalog_read_inputs($_GET, array('ratename','username'));
        $ratename = trim($_GET['ratename'] ?? ''); $username = trim($_GET['username'] ?? '');
        $date_default = date_range_default('current_month');
        $startdate = dalo_billing_date($_GET, 'startdate', $date_default['start']);
        $enddate = dalo_billing_date($_GET, 'enddate', $date_default['end']);
    } catch (Throwable $error) { $input_error = true; }
    $ratename_enc = htmlspecialchars($ratename, ENT_QUOTES, 'UTF-8');
    $username_enc = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

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
    
    $orderBy = (array_key_exists('orderBy', $_GET) && is_string($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && is_string($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array("asc", "desc")))
               ? strtolower($_GET['orderType']) : "asc";

    // init loggin variables
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";
    $logDebugSQL = "";

    
    // print HTML prologue
    $extra_css = array();
    
    $extra_js = array(
    );
    
    $title = t('Intro','billratesdate.php');
    $help = t('helpPage','billratesdate');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    print_title_and_help($title, $help);

    $numrows = 0; $billing_rows = array(); $pdo = null;
    if ($ratename !== '' && !$input_error) {
        try {
            $pdo = dalo_catalog_read_open($configValues);
            $rate = dalo_rate_read($pdo, $configValues, dalo_rate_text($ratename, true));
            $rateDivisor = dalo_rate_divisor($rate[2]);
            $acct = dalo_read_table($pdo, $configValues, 'CONFIG_DB_TBL_RADACCT');
            $rates = dalo_read_table($pdo, $configValues, 'CONFIG_DB_TBL_DALOBILLINGRATES');
            $sql = "SELECT DISTINCT ra.username,ra.NASIPAddress,ra.AcctStartTime,ra.AcctSessionTime,dbr.rateCost FROM $acct AS ra CROSS JOIN $rates AS dbr WHERE dbr.rateName=:ratename AND ra.AcctStartTime>:startdate AND ra.AcctStartTime<:enddate";
            $bind = array(':ratename'=>$ratename, ':startdate'=>$startdate, ':enddate'=>$enddate);
            if ($username !== '') { $sql .= ' AND ra.username=:username'; $bind[':username']=$username; }
            $partial_query_string = '&' . http_build_query(array('startdate'=>$startdate,'enddate'=>$enddate,'username'=>$username,'ratename'=>$ratename));
            include('include/management/pages_common.php');
            $numrows = (int)dalo_catalog_read_rows($pdo, "SELECT COUNT(*) FROM ($sql) AS rate_count", $bind)[0][0];
            if ($numrows > 0) {
                include('include/management/pages_numbering.php');
                $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == 'yes' && $maxPage > 1;
                $billing_rows = dalo_catalog_read_rows($pdo, "$sql ORDER BY $orderBy $orderType LIMIT :offset,:limit",
                    array_merge($bind, array(':offset'=>(int)$offset, ':limit'=>(int)$rowsPerPage)));
            }
        } catch (DomainException $error) { $numrows = 0; $failureMsg = 'Rate was not found or has an invalid stored type'; }
        catch (Throwable $error) { $numrows = 0; dalo_rate_failure($error); }
        finally { $pdo = null; }
        if (!isset($failureMsg)) {
            // Independent R20 summary retains its own inclusive date policy.
            include_once('include/management/userBilling.php');
            userBillingRatesSummary($username, $startdate, $enddate, $ratename, 1);
        }
        if ($numrows > 0) {
            $per_page_numrows = count($billing_rows);
            $params = array('num_rows'=>$numrows,'rows_per_page'=>$rowsPerPage,'page_num'=>$pageNum,
                'order_by'=>$orderBy,'order_type'=>$orderType,'partial_query_string'=>$partial_query_string);
            print_table_prologue(array('center'=>array('draw'=>$drawNumberLinks,'params'=>$params)));
            print_table_top();
            printTableHead($cols, $orderBy, $orderType, $partial_query_string);
            print_table_middle();

            $sumBilled = 0;
            $sumSession = 0;

            foreach ($billing_rows as $row) {
                foreach ($row as $i => $value) {
                    $row[$i] = htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
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
            $failureMsg = $failureMsg ?? "No entries retrieved";
        }
        

        
    } else {
        $failureMsg = $input_error ? "Invalid billing report input" : "Rate name is required";
        
    }
    
    include_once("include/management/actionMessages.php");

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
