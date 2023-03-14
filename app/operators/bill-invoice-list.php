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
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    $cols = array(
                    "selected",
                    "id" => t('all','Invoice'),
                    "contactperson" => t('all','ClientName'),
                    "date" => t('all','Date'),
                    "totalbilled" => t('all','TotalBilled'),
                    "totalpayed" => t('all','TotalPayed'),
                    t('all','Balance'),
                    "status_id" => t('all','Status')
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

    $user_id = (array_key_exists('user_id', $_GET) && isset($_GET['user_id']) &&
            preg_match('/^[0-9]+$/', $_GET['user_id']) !== false)
         ? $_GET['user_id'] : "";
         
    $username = (array_key_exists('username', $_GET) && isset($_GET['username']))
              ? str_replace('%', '', $_GET['username']) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

    $invoice_status_id = (array_key_exists('invoice_status_id', $_GET) && isset($_GET['invoice_status_id']) &&
                          preg_match('/^[0-9]+$/', $_GET['invoice_status_id']) !== false)
                       ? $_GET['invoice_status_id'] : "";

    // feed the sidebar
    $edit_invoice_status_id = $invoice_status_id;
    $edit_invoiceUsername = $username_enc;    

    
    // print HTML prologue
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js"
    );
    
    $title = t('Intro','billinvoicelist.php');
    $help = t('helpPage','billinvoicelist');
    
    print_html_prologue($title, $langCode, array(), $extra_js);

    // start printing content
    print_title_and_help($title, $help);


    include('../common/includes/db_open.php');
    include('include/management/pages_common.php');

    // if provided username, we'll need to turn that into the userbillinfo user id
    if (!empty($username)) {
        $sql = sprintf("SELECT id FROM %s WHERE username='%s'",
                       $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                       $dbSocket->escapeSimple($username));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        $row = $res->fetchRow();
        $user_id = intval($row[0]);
    }
    
    
    $sql_WHERE = array();
    if (isset($user_id) && !empty($user_id)) {
        $sql_WHERE[] = sprintf("a.user_id = %s", $dbSocket->escapeSimple($user_id));
    }

    if (isset($edit_invoice_status_id) && !empty($edit_invoice_status_id)) {
        $sql_WHERE[] = sprintf("a.status_id = '%s'", $dbSocket->escapeSimple($edit_invoice_status_id));
    }
    
    $subquery1 = sprintf("SELECT SUM(d.amount + d.tax_amount) AS totalbilled, invoice_id
                            FROM %s AS d GROUP BY d.invoice_id", $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS']);
    
    $subquery2 = sprintf("SELECT SUM(e.amount) AS totalpayed, invoice_id
                            FROM %s AS e GROUP BY e.invoice_id", $configValues['CONFIG_DB_TBL_DALOPAYMENTS']);
    
    $sql = sprintf("SELECT a.id, a.date, a.status_id, a.type_id, b.contactperson, b.username, c.value AS status,
                           COALESCE(e2.totalpayed, 0) AS totalpayed, COALESCE(d2.totalbilled, 0) AS totalbilled
                      FROM %s AS a INNER JOIN %s AS b ON a.user_id=b.id
                                   INNER JOIN %s AS c ON a.status_id=c.id
                                    LEFT JOIN (%s) AS d2 ON d2.invoice_id=a.id
                                    LEFT JOIN (%s) AS e2 ON e2.invoice_id=a.id",
                   $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'], $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                   $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS'], $subquery1, $subquery2);
    
    if (count($sql_WHERE) > 0) {
        $sql .= " WHERE " . implode(" AND ", $sql_WHERE);
    }

    $sql .= " GROUP BY a.id";
    
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
        $logDebugSQL .= "$sql;\n";
        
        $per_page_numrows = $res->numRows();
        
        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "bill-invoice-del.php";
        
        // we prepare the "controls bar" (aka the table prologue bar)
        $params = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $rowsPerPage,
                            'page_num' => $pageNum,
                            'order_by' => $orderBy,
                            'order_type' => $orderType,
                        );
        
        $descriptors = array();
        $descriptors['start'] = array( 'common_controls' => 'invoice_id[]', );
        $descriptors['center'] = array( 'draw' => $drawNumberLinks, 'params' => $params );
        print_table_prologue($descriptors);
        
        $form_descriptor = array( 'form' => array( 'action' => $action, 'method' => 'POST', 'name' => 'listall' ), );
        
        // print table top
        print_table_top($form_descriptor);
        
        // second line of table header
        printTableHead($cols, $orderBy, $orderType);

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
            
            list($id, $date, $status_id, $type_id, $contactperson, $username, $status, $totalpayed, $totalbilled) = $row;
            $id = intval($id);
            
            // define tooltip1
            $tooltip1 = array(
                                'subject' => sprintf("#%d", intval($id)),
                                'actions' => array(),
                             );
            $tooltip1['actions'][] = array( 'href' => sprintf('bill-invoice-edit.php?invoice_id=%d', $id, ), 'label' => t('Tooltip','InvoiceEdit'), );
            
            // define tooltip1
            $tooltip2 = array(
                                'subject' => $username,
                                'actions' => array(),
                             );
            $tooltip2['actions'][] = array( 'href' => sprintf('bill-pos-edit.php?username=%s', $username, ), 'label' => t('Tooltip','UserEdit'), );
        
            // create balance
            $balance = $totalpayed - $totalbilled;
            $balance = sprintf('<span class="text-%s">%s <i class="bi bi-currency-exchange"></i></span>', (($balance < 0) ? "danger" : "success"), $balance);
            
            // create total payed and billed
            $totalpayed = sprintf('%s <i class="bi bi-currency-exchange"></i>', $totalpayed);
            $totalbilled = sprintf('%s <i class="bi bi-currency-exchange"></i>', $totalbilled);
            
            // create checkbox
            $d = array( 'name' => 'invoice_id[]', 'value' => $id );
            $checkbox1 = get_checkbox_str($d);
            
            // create tooltips
            $tooltip1 = get_tooltip_list_str($tooltip1);
            $tooltip2 = get_tooltip_list_str($tooltip2);
            
            // define table row
            $table_row = array( $checkbox1, $tooltip1, $tooltip2, $date, $totalbilled, $totalpayed, $balance, $status );
            
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
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printLinks($links, $drawNumberLinks);

    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
    
    include('../common/includes/db_close.php');
    
    include('include/config/logging.php');
    
    print_footer_and_html_epilogue();
?>
