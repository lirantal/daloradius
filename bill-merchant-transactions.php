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
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');
	include_once('library/config_read.php');
    
    include_once("lang/main.php");
    include("library/validation.php");
    include("library/layout.php");

    // init loggin variables
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    $sqlfields = (array_key_exists('sqlfields', $_GET) && !empty($_GET['sqlfields']) && is_array($_GET['sqlfields']) &&
                  array_intersect($_GET['sqlfields'], array_keys($bill_merchant_transactions_options_all)) == $_GET['sqlfields'])
               ? $_GET['sqlfields'] : $bill_merchant_transactions_options_default;
    
    $cols = array();
    foreach ($sqlfields as $sqlfield) {
        $cols[$sqlfield] = $bill_merchant_transactions_options_all[$sqlfield];
    }
    $colspan = count($cols);
    $half_colspan = intdiv($colspan, 2);
    
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($bill_merchant_transactions_options_all)))
             ? $_GET['orderBy'] : array_keys($bill_merchant_transactions_options_all)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  preg_match(ORDER_TYPE_REGEX, $_GET['orderType']) !== false)
               ? strtolower($_GET['orderType']) : "asc";
    
    $startdate = (array_key_exists('startdate', $_GET) && !empty($_GET['startdate']) &&
                  preg_match(DATE_REGEX, $_GET['startdate'], $m) !== false &&
                  checkdate($m[2], $m[3], $m[1]))
               ? $_GET['startdate'] : "";

    $enddate = (array_key_exists('enddate', $_GET) && !empty($_GET['enddate']) &&
                preg_match(DATE_REGEX, $_GET['enddate'], $m) !== false &&
                checkdate($m[2], $m[3], $m[1]))
             ? $_GET['enddate'] : "";
             
    $vendor_type = (array_key_exists('vendor_type', $_GET) && isset($_GET['vendor_type']) &&
                    in_array($_GET['vendor_type'], array_slice($valid_vendorTypes, 1))) // avoid inserting "Any" in the SQL query
                 ? $_GET['vendor_type'] : "";

    $payer_email = (array_key_exists('payer_email', $_GET) && !empty(str_replace("%", "", trim($_GET['payer_email']))))
                 ? str_replace("%", "", trim($_GET['payer_email'])) : "";
    $payer_email_enc = (!empty($payer_email)) ? htmlspecialchars($payer_email, ENT_QUOTES, 'UTF-8') : "";

	$payment_status = (array_key_exists('payment_status', $_GET) && isset($_GET['payment_status']) &&
                       in_array($_GET['payment_status'], array_slice($valid_paymentStatus, 1))) // avoid inserting "Any" in the SQL query
                    ? $_GET['payment_status'] : "";
    
    // FIX THIS: they aren't passed
    $payment_address_status = (array_key_exists('payment_address_status', $_GET) &&
                               !empty(str_replace("%", "", trim($_GET['payment_address_status']))))
                            ? str_replace("%", "", trim($_GET['payment_address_status'])) : "";
    $payer_status = (array_key_exists('payer_status', $_GET) && !empty(str_replace("%", "", trim($_GET['payer_status']))))
                  ? str_replace("%", "", trim($_GET['payer_status'])) : "";
	
    //feed the sidebar variables
	$billing_date_startdate = $startdate;
	$billing_date_enddate = $enddate;
	$billing_paypal_payeremail = $payer_email_enc;
	$billing_paypal_paymentstatus = $payment_status;
	$billing_paypal_vendor_type = $vendor_type;

    //~ $billing_paypal_paymentaddressstatus = $payment_address_status;
	//~ $billing_paypal_payerstatus = $payer_status;
	
    // print HTML prologue
    $title = t('Intro','billpaypaltransactions.php');
    $help = t('helpPage','billpaypaltransactions');
    
    print_html_prologue($title, $langCode);
    
	include("menu-bill-merchant.php");
	
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    // draw the billing rates summary table
    include_once('include/management/userBilling.php');
    userBillingPayPalSummary($startdate, $enddate, $payer_email, $payment_address_status, $payer_status, $payment_status, $vendor_type, 1);
									                         

    include('library/opendb.php');
    include_once('include/management/pages_common.php');
    
    // preparing the custom query
    
    $sql_WHERE = array();
    $partial_query_string_pieces = array();
    
    foreach ($sqlfields as $sqlfield) {
        $partial_query_string_pieces[] = sprintf("sqlfields[]=%s", $sqlfield);
    }
    
    if (!empty($startdate)) {
        $sql_WHERE[] = sprintf("payment_date > '%s'", $dbSocket->escapeSimple($startdate));
        $partial_query_string_pieces[] = sprintf("startdate=%s", $startdate);
    }
    
    if (!empty($startdate)) {
        $sql_WHERE[] = sprintf("payment_date < '%s'", $dbSocket->escapeSimple($enddate));
        $partial_query_string_pieces[] = sprintf("enddate=%s", $enddate);
    }
    
    if (!empty($payer_email)) {
        $sql_WHERE[] = sprintf("payer_email LIKE '%s%%'", $dbSocket->escapeSimple($payer_email));
        $partial_query_string_pieces[] = sprintf("payer_email=%s", $payer_email);
    }
    
    if (!empty($payment_status)) {
        $sql_WHERE[] = sprintf("payment_status='%s'", $dbSocket->escapeSimple($payment_status));
        $partial_query_string_pieces[] = sprintf("payment_status=%s", $payment_status);
    }
    
    if (!empty($vendor_type)) {
        $sql_WHERE[] = sprintf("vendor_type='%s'", $dbSocket->escapeSimple($vendor_type));
        $partial_query_string_pieces[] = sprintf("vendor_type=%s", $vendor_type);
    }
    
    // executing the custom query

    $sql = sprintf("SELECT %s FROM %s", implode(", ", $sqlfields), $configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT']);
    
    if (count($sql_WHERE) > 0) {
        $sql .= " WHERE " . implode(" AND ", $sql_WHERE);
    }

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
        
        $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        $per_page_numrows = $res->numRows();
        
        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = (count($partial_query_string_pieces) > 0)
                              ? "&" . implode("&", $partial_query_string_pieces) : "";

        echo '<table border="0" class="table1">'
           . '<thead>';

        // page numbers are shown only if there is more than one page
        if ($drawNumberLinks) {
            echo '<tr style="background-color: white">';
            printf('<td style="text-align: left" colspan="%s">go to page: ', $colspan);
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $partial_query_string);
            echo '</td>' . '</tr>';
        }

        // second line of table header
        echo "<tr>";
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);
        echo "</tr>";

            
        echo '</thead>'
           . '<tbody>';
        
        // inserting the values of each field from the database to the table
        $count = 0;
        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            printf('<tr id="row-%d">', $count);
            foreach ($sqlfields as $field) {
                printf("<td>%s</td>", htmlspecialchars($row[$field], ENT_QUOTES, 'UTF-8'));
            }
            echo '</tr>';
            $count++;
        }

        echo '</tbody>';
        
        // tfoot
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
        printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links);

        echo '</table>';

    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
        
    include('library/closedb.php');    

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
