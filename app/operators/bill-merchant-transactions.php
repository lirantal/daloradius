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

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');
	include_once('../common/includes/config_read.php');
    
    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");

    require_once 'library/billing_rates_pdo.php';
    // init loggin variables
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    $input_error = false;
    $sqlfields = $bill_merchant_transactions_options_default;
    if (isset($_GET['sqlfields'])) {
        if (is_array($_GET['sqlfields']) && $_GET['sqlfields'] && count($_GET['sqlfields']) <= count($bill_merchant_transactions_options_all) &&
            count(array_filter($_GET['sqlfields'], 'is_string')) === count($_GET['sqlfields']) &&
            !array_diff($_GET['sqlfields'], array_keys($bill_merchant_transactions_options_all))) { $sqlfields = array_values(array_unique($_GET['sqlfields'])); }
        else { $input_error = true; }
    }
    $cols = array();
    foreach ($sqlfields as $field) { $cols[$field] = $bill_merchant_transactions_options_all[$field]; }
    $colspan = count($cols); $half_colspan = intval($colspan / 2);
    $orderBy = isset($_GET['orderBy']) && is_string($_GET['orderBy']) && array_key_exists($_GET['orderBy'], $bill_merchant_transactions_options_all) ? $_GET['orderBy'] : 'id';
    $orderType = isset($_GET['orderType']) && is_string($_GET['orderType']) && in_array(strtolower($_GET['orderType']), array('asc','desc'), true) ? strtolower($_GET['orderType']) : 'asc';
    $startdate = $enddate = $payer_email = $payment_status = $vendor_type = $payment_address_status = $payer_status = '';
    try {
        dalo_catalog_read_inputs($_GET, array('payer_email','payment_status','vendor_type','payment_address_status','payer_status'));
        $defaults = date_range_default('previous_month');
        $startdate = dalo_billing_date($_GET, 'startdate', $defaults['start']);
        $enddate = dalo_billing_date($_GET, 'enddate', $defaults['end']);
        $payer_email = trim($_GET['payer_email'] ?? '');
        $payer_email_enc = htmlspecialchars($payer_email, ENT_QUOTES, 'UTF-8');
        $vendor_type = isset($_GET['vendor_type']) && in_array($_GET['vendor_type'], array_slice($valid_vendorTypes, 1), true) ? $_GET['vendor_type'] : '';
        $payment_status = isset($_GET['payment_status']) && in_array($_GET['payment_status'], array_slice($valid_paymentStatus, 1), true) ? $_GET['payment_status'] : '';
        // Historical summary-only filters; the local table never used these controls.
        $payment_address_status = trim($_GET['payment_address_status'] ?? '');
        $payer_status = trim($_GET['payer_status'] ?? '');
    } catch (Throwable $error) { $input_error = true; }

    $billing_paypal_vendor_type = $vendor_type;
    $billing_paypal_payeremail = $payer_email;
    $billing_paypal_paymentstatus = $payment_status;

    // print HTML prologue
    $title = t('Intro','billpaypaltransactions.php');
    $help = t('helpPage','billpaypaltransactions');
    
    print_html_prologue($title, $langCode);
    
	print_title_and_help($title, $help);

    include_once('include/management/pages_common.php');
    $numrows = 0; $billing_rows = array(); $pdo = null;
    try {
        if ($input_error) { throw new InvalidArgumentException('Invalid billing report input'); }
        $pdo = dalo_catalog_read_open($configValues);
        $table = dalo_read_table($pdo, $configValues, 'CONFIG_DB_TBL_DALOBILLINGMERCHANT');
        $where = array(); $bind = array();
        $partial_query_string_pieces = array();
        foreach ($sqlfields as $field) { $partial_query_string_pieces[] = 'sqlfields[]=' . urlencode($field); }
        if ($startdate !== '') { $where[] = 'payment_date >= :startdate'; $bind[':startdate'] = $startdate; $partial_query_string_pieces[] = 'startdate=' . urlencode($startdate); }
        if ($enddate !== '') { $where[] = 'payment_date < (:enddate + INTERVAL 1 DAY)'; $bind[':enddate'] = $enddate; $partial_query_string_pieces[] = 'enddate=' . urlencode($enddate); }
        if ($payer_email !== '') { $where[] = 'payer_email LIKE :payer_email'; $bind[':payer_email'] = dalo_billing_like($payer_email); $partial_query_string_pieces[] = 'payer_email=' . urlencode($payer_email); }
        if ($payment_status !== '') { $where[] = 'payment_status=:payment_status'; $bind[':payment_status'] = $payment_status; $partial_query_string_pieces[] = 'payment_status=' . urlencode($payment_status); }
        if ($vendor_type !== '') { $where[] = 'vendor_type=:vendor_type'; $bind[':vendor_type'] = $vendor_type; $partial_query_string_pieces[] = 'vendor_type=' . urlencode($vendor_type); }
        $sql_where = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $numrows = (int)dalo_catalog_read_rows($pdo, "SELECT COUNT(*) FROM $table$sql_where", $bind)[0][0];
        if ($numrows > 0) {
            include('include/management/pages_numbering.php');
            $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == 'yes' && $maxPage > 1;
            $sql = 'SELECT ' . implode(',', $sqlfields) . " FROM $table$sql_where ORDER BY $orderBy $orderType LIMIT :offset,:limit";
            $billing_rows = dalo_catalog_read_rows($pdo, $sql, array_merge($bind, array(':offset'=>(int)$offset, ':limit'=>(int)$rowsPerPage)));
        }
    } catch (Throwable $error) { $numrows = 0; dalo_rate_failure($error); }
    finally { $pdo = null; }
    // Independent legacy summary remains in R20; no dependent mutation crosses clients.
    if (!isset($failureMsg)) {
        include_once('include/management/userBilling.php');
        userBillingPayPalSummary($startdate, $enddate, $payer_email, $payment_address_status, $payer_status, $payment_status, $vendor_type, 1);
    }
    if ($numrows > 0) {
        $per_page_numrows = count($billing_rows);
        $partial_query_string = $partial_query_string_pieces ? '&' . implode('&', $partial_query_string_pieces) : '';
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

        // table content
        $count = 0;
        foreach ($billing_rows as $row) {
            printf('<tr id="row-%d">', $count);
            foreach ($sqlfields as $index=>$field) {
                printf("<td>%s</td>", htmlspecialchars($row[$index] ?? '', ENT_QUOTES, 'UTF-8'));
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
        $descriptor = array( 'table_foot' => $table_foot );

        print_table_bottom($descriptor);

        // get and print "links"
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
        printLinks($links, $drawNumberLinks);

    } else {
        $failureMsg = $failureMsg ?? "Nothing to display";
        include_once("include/management/actionMessages.php");
    }

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
