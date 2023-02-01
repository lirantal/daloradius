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
    include_once("lang/main.php");
    include("library/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    $cols = array(
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

    include("include/menu/sidebar.php");

    
    // start printing content
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);


    include('library/opendb.php');
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
?>

<form name="listall" method="POST" action="<?= $action ?>">
    <table border="0" class="table1">
        <thead>
            
<?php
        // page numbers are shown only if there is more than one page
        if ($drawNumberLinks) {
            echo '<tr style="background-color: white">';
            printf('<td style="text-align: left" colspan="%s">go to page: ', $colspan);
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);
            echo '</td>' . '</tr>';
        }
?>
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
        printTableHead($cols, $orderBy, $orderType);
?>           
            </tr>
        </thead>

        <tbody>

<?php

        $count = 1;
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        
            // escape row elements
            foreach ($row as $key => $value) {
                $row[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        
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

?>
            <tr>
                <td>
                    <input type="checkbox" name="invoice_id[]" value="<?= $row['id'] ?>" id="<?= "checkbox-$count" ?>">
                    <label for="<?= "checkbox-$count" ?>"><?= $invoice_id ?></label>
                </td>
                <td><?= $contactperson ?></td>
                <td><?= $row['date'] ?></td>
                <td class="money"><?= $row['totalbilled'] ?></td>
                <td class="money"><?= $row['totalpayed'] ?></td>
                <td class="money"><?= ($balance >= 0) ? $balance : sprintf('<span style="color: red">%s</font>', $balance) ?></td>
                <td><?= $row['status'] ?></td>
            </tr>
<?php
            $count++;
        }
?>
        </tbody>

<?php
        // tfoot
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links);
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
