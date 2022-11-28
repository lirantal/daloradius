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

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logDebugSQL = "";

    include('library/check_operator_perm.php');
    include_once('library/config_read.php');

    $invoice_id = (array_key_exists('invoice_id', $_GET) && isset($_GET['invoice_id']) &&
                   preg_match('/^[0-9]+$/', $_GET['invoice_id']) !== false)
                ? $_GET['invoice_id'] : "";
    
    $user_id = (array_key_exists('user_id', $_GET) && isset($_GET['user_id']) &&
                preg_match('/^[0-9]+$/', $_GET['user_id']) !== false)
             ? $_GET['user_id'] : "";

    $username = (array_key_exists('username', $_GET) && isset($_GET['username']))
              ? str_replace('%', '', $_GET['username']) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    
    $edit_username = $username_enc;
    $edit_invoice_id = $invoice_id;

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue    
    $title = t('Intro','paymentslist.php');
    $help = t('helpPage','paymentslist');
    
    print_html_prologue($title, $langCode);
    
    include("menu-bill-payments.php");
    
    $cols = array(
                    "id" => t('all','ID'),
                    "invoice_id" => t('all','PaymentInvoiceID'),
                    t('all','PaymentAmount'),
                    t('all','PaymentDate'),
                    t('all','PaymentType'),
                    t('all','PaymentNotes')
                 );
    $colspan = count($cols);
    $half_colspan = intdiv($colspan, 2);
                 
    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }
    
    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "desc";

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
    $sql_JOIN = "";
    
    // if invoice_id then we need to lookup specific invoices
    if (!empty($invoice_id)) {
        $sql_WHERE[] = sprintf("p.invoice_id = %s", $dbSocket->escapeSimple($invoice_id));
    }
    
    // if we did get a user id let's make the sql query specific to payments by this user 
    if (isset($user_id) && !empty($user_id)) {
        $sql_JOIN = sprintf("JOIN %s AS bi ON bi.id=p.invoice_id", $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE']);
        $sql_WHERE[] = sprintf("bi.user_id = %s", $dbSocket->escapeSimple($user_id));
    }
    
    
    $sql = sprintf("SELECT p.id, p.invoice_id, p.amount, p.date, pt.value, p.notes
                      FROM %s AS p %s LEFT JOIN %s AS pt ON p.type_id=pt.id", $configValues['CONFIG_DB_TBL_DALOPAYMENTS'],
                                                                              $sql_JOIN,
                                                                              $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES']);
    if (count($sql_WHERE) > 0) {
        $sql .= " WHERE " . implode(" AND ", $sql_WHERE);
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
        
        /* END */
        
        // we execute and log the actual query
        $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        $per_page_numrows = $res->numRows();
        
        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "bill-payments-del.php";
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
        printTableFormControls('payment_id[]', $action);
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
        while ($row = $res->fetchRow()) {
            $rowlen = count($row);
        
            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }
        
            list($payment_id, $invoice_id, $amount, $date, $value, $notes) = $row;
        
            $onclick = 'javascript:return false;';
            
            $li_style = 'margin: 7px auto';
            $tooltipText1 = '<ul style="list-style-type: none">'
                          . sprintf('<li style="%s"><a class="toolTip" href="bill-payments-edit.php?payment_id=%s">%s</a></li>',
                                    $li_style, urlencode($payment_id), t('Tooltip','EditPayment'))
                          . sprintf('<li style="%s"><a class="toolTip" href="bill-payments-del.php?payment_id=%s">%s</a></li>',
                                    $li_style, urlencode($payment_id), t('Tooltip','RemovePayment'))
                          . '</ul>';
            
            $tooltipText2 = sprintf('<a class="toolTip" href="bill-invoice-edit.php?invoice_id=%s">%s</a>',
                                    urlencode($invoice_id), t('Tooltip','InvoiceEdit'));
?>
            <tr>
                <td>
                    <input type="checkbox" name="payment_id[]" value="<?= $payment_id ?>" id="<?= "checkbox-$count" ?>">
                    <label for="<?= "checkbox-$count" ?>">
                        <a class="tablenovisit" href="#" onclick="<?= $onclick ?>" tooltipText='<?= $tooltipText1 ?>'>
                            <?= $payment_id ?>
                        </a>
                    </label>
                </td>
                
                <td>
                    <a class="tablenovisit" href="#" onclick="<?= $onclick ?>" tooltipText='<?= $tooltipText2 ?>'>
                        <?= $invoice_id ?>
                    </a>
                </td>
                
                <td class="money"><?= $amount ?></td>
                <td><?= $date ?></td>
                <td><?= $value ?></td>
                <td><?= $notes ?></td>
                
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

</form>

<?php
    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
    
    include('library/closedb.php');
    include('include/config/logging.php');
    
    $inline_extra_js = "
var tooltipObj = new DHTMLgoodies_formTooltip();
tooltipObj.setTooltipPosition('right');
tooltipObj.setPageBgColor('#EEEEEE');
tooltipObj.setTooltipCornerSize(15);
tooltipObj.initFormFieldTooltip()";
    
    print_footer_and_html_epilogue($inline_extra_js);
?>
