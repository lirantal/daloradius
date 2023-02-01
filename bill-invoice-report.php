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
    include_once('library/config_read.php');

    // init loggin variables
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";
    $logDebugSQL = "";


    //setting values for the order by and order type variables
    // and in other cases we partially strip some character,
    // and leave validation/escaping to other functions used later in the script
    $username = (array_key_exists('username', $_GET) && isset($_GET['username']))
              ? trim(str_replace("%", "", $_GET['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    
    // in other cases we just check that syntax is ok
    $date_regex = '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/';
    
    $startdate = (array_key_exists('startdate', $_GET) && isset($_GET['startdate']) &&
                  preg_match($date_regex, $_GET['startdate'], $m) !== false &&
                  checkdate($m[2], $m[3], $m[1]))
               ? $_GET['startdate'] : "";

    $enddate = (array_key_exists('enddate', $_GET) && isset($_GET['enddate']) &&
                preg_match($date_regex, $_GET['enddate'], $m) !== false &&
                checkdate($m[2], $m[3], $m[1]))
             ? $_GET['enddate'] : "";
    
    $invoice_status = (array_key_exists('invoice_status', $_GET) && isset($_GET['invoice_status']))
                    ? trim($_GET['invoice_status']) : "";
    
    // initialize the left-side menu vars
    $billinvoice_startdate = $startdate;
    $billinvoice_enddate = $enddate;
    $billinvoice_username = $username_enc;
    
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $extra_css = array();
    
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js",
    );
    
    $title = t('Intro','billinvoicereport.php');
    $help = t('helpPage','billinvoicereport');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);


    include("menu-bill-invoice.php");
    
    
    $cols = array(
                    "id" => t('all','Invoice'), 
                    "contactperson" => t('all','ClientName'),
                    "date" => t('all','Date'),
                    "totalbilled" => t('all','TotalBilled'),
                    "totalpayed" => t('all','TotalPayed'),
                    t('all','Balance'),
                    "status_id" => t('all','Status'),
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
               ? strtolower($_GET['orderType']) : "asc";
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    
    include('library/opendb.php');
    include('include/management/pages_common.php');

    
    $sql_WHERE = array();
    $partial_query_params = array();
    
    if (!empty($startdate)) {
        $sql_WHERE[] = sprintf("a.date >= '%s'", $dbSocket->escapeSimple($startdate));
        $partial_query_params[] = sprintf("startdate=%s", $startdate);
    }
    
    if (!empty($enddate)) {
        $sql_WHERE[] = sprintf("a.date <= '%s'", $dbSocket->escapeSimple($enddate));
        $partial_query_params[] = sprintf("enddate=%s", $enddate);
    }
    
    if (!empty($username)) {
        $sql_WHERE[] = sprintf("b.username LIKE '%s%%'", $dbSocket->escapeSimple($username));
        $partial_query_params[] = sprintf("username=%s", $username_enc);
    }
    
    if (!empty($invoice_status)) {
        $sql_WHERE[] = sprintf("a.status_id = '%s'", $dbSocket->escapeSimple($invoice_status));
        $partial_query_params[] = sprintf("invoice_status=%s", htmlspecialchars($invoice_status, ENT_QUOTES, 'UTF-8'));
    }
    
    $sql = sprintf("SELECT a.id, a.date, a.status_id, a.type_id, b.contactperson, b.username, c.value AS status,
                           COALESCE(e2.totalpayed, 0) as totalpayed, COALESCE(d2.totalbilled, 0) as totalbilled
                      FROM %s AS a INNER JOIN %s AS b ON a.user_id=b.id
                                   INNER JOIN %s AS c ON a.status_id=c.id
                                    LEFT JOIN (SELECT SUM(d.amount + d.tax_amount) AS totalbilled, invoice_id
                                                 FROM %s AS d GROUP BY d.invoice_id) AS d2 ON d2.invoice_id=a.id
                                    LEFT JOIN (SELECT SUM(e.amount) AS totalpayed, invoice_id
                                                 FROM %s AS e GROUP BY e.invoice_id) AS e2 ON e2.invoice_id=a.id",
                   $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'], $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                   $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS'], $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'],
                   $configValues['CONFIG_DB_TBL_DALOPAYMENTS']);
    if (count($sql_WHERE) > 0) {
        $sql .= " WHERE " . implode(" AND ", $sql_WHERE);
    }
    $sql .= " GROUP BY a.id";
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();        
    
    // setup php session variables for exporting
    $_SESSION['reportTable'] = '';
    $_SESSION['reportQuery'] = $sql;
    $_SESSION['reportType'] = "reportsInvoiceList";
    
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
        $partial_query_string = implode("&", $partial_query_params);
        
        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "bill-invoice-del.php";
?>

<form name="listall" method="POST" action="<?= $action ?>">

    <table border="0" class="table1">
        <thead>
            <tr style="background-color: white">
<?php
        // page numbers are shown only if there is more than one page
        if ($drawNumberLinks) {
            printf('<td style="text-align: left" colspan="%s">go to page: ', $half_colspan + ($colspan % 2));
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $partial_query_string);
            echo '</td>';
        }
?>
                <td style="text-align: right" colspan="<?= ($drawNumberLinks) ? $half_colspan : $colspan ?>">
                    <input class="button" type="button" value="CSV Export"
                        onclick="location.href='include/management/fileExport.php?reportFormat=csv'">
                </td>

            </tr>

            <tr>
                <th style="text-align: left" colspan="<?= $colspan ?>">
<?php
        printTableFormControls('invoice_id[]', $action);
?>
                </th>
            </tr>

            <tr>
<?php
        // second line of table header
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);
?>
            </tr>
            
        </thead>
<?php

        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            
            foreach ($row as $key => $value) {
                $row[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
            
            echo '<tr>';
            
            $content = sprintf('<a class="toolTip" href="bill-invoice-edit.php?invoice_id=%s">%s</a>',
                               urlencode($row['id']), t('Tooltip','InvoiceEdit'));
            $arr = array(
                            'content' => $content,
                            'onClick' => '',
                            'value' => '#'.$row['id'],
                            'divId' => '',
                        );
            $invoice_id = addToolTipBalloon($arr);

            $content = sprintf('<a class="toolTip" href="bill-pos-edit.php?username=%s">%s</a>',
                               urlencode($row['username']), t('Tooltip','UserEdit'));
            $arr = array(
                            'content' => $content,
                            'onClick' => '',
                            'value' => $row['contactperson'],
                            'divId' => '',
                        );
            $contactperson = addToolTipBalloon($arr);


            $balance = ($row['totalpayed'] - $row['totalbilled']);
            if ($balance < 0)
                $balance = '<span style="color: red">'.$balance.'</span>';
                
            echo '<td> '.$invoice_id.' </td>';
            echo '<td> '.$contactperson.' </td>';
            echo '<td> '.$row['date'].' </td>';
            echo '<td> '.$row['totalbilled'].' </td>';
            echo '<td> '.$row['totalpayed'].' </td>';
            echo '<td> '.$balance.' </td>';
            echo '<td> '.$row['status'].' </td>';
            
            echo '</tr>';
        
        }

?>
        </tbody>

<?php
        // tfoot
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links, $partial_query_string);
?>

    </table>
    
    <input type="hidden" name="csrf_token" value="<?= dalo_csrf_token() ?>">
    
</form>

<?php
    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
    
    include('library/closedb.php');    

    include('include/config/logging.php');
    
    print_footer_and_html_epilogue();
?>
