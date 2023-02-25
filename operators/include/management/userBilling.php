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
 * Description:    returns user billing information (rates, plans, etc)
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/management/userBilling.php') !== false) {
    header("Location: ../../index.php");
    exit;
}


/*
 *********************************************************************************************************
 * userInvoiceAdd
 * general billing function to add invoices to the user based on the user_id
 *
 * $userId                    the userbillinfo user id or the username (autodetects)
 * $invoiceInfo            array holding the invoice information
 * $invoiceItems           array holding the invoice items information
 *
 *********************************************************************************************************
 */
function userInvoiceAdd($userId, $invoiceInfo = array(), $invoiceItems = array()) {
    global $logDebugSQL;

    include('../common/includes/db_open.php');

    $user_id = false;

    // if provided a numeric user id then this is the user_id that we need
    if (is_numeric($userId)) {
        // sanitize variable for sql statement
        $user_id = $dbSocket->escapeSimple($userId);
    } else {
        // otherwise this is the username and we need to look up the user id from the userbillinfo table
        $username = $dbSocket->escapeSimple($userId);
        $sql = sprintf("SELECT id FROM %s WHERE username='%s'", $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'], $username);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        $row = $res->fetchRow();
        $user_id = $row[0];
    }

    // if something is not right with the user id (set to null, false, whatever) we abort
    if (!$user_id) {
        return false;
    }

    $currDate = date('Y-m-d H:i:s');
    $currBy = $_SESSION['operator_user'];

    if (!is_array($invoiceInfo)) {
        $invoiceInfo = array();
    }

    // create default invoice information if nothing was provided
    $myinvoiceInfo['date'] = $currDate;
    $myinvoiceInfo['status_id'] = 1;             // defaults to invoice status of 'open'
    $myinvoiceInfo['type_id'] = 1;               // defaults to invoice type of 'Plans'
    $myinvoiceInfo['notes'] = 'provisioned new user from daloRADIUS platform';
    $invoiceInfo = array_merge($myinvoiceInfo, $invoiceInfo);


    $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'].
            " (id, user_id, date, status_id, type_id, notes, creationdate, creationby, updatedate, updateby) ".
            " VALUES (0, '".$user_id."', '".
            $dbSocket->escapeSimple($invoiceInfo['date'])."', '".
            $dbSocket->escapeSimple($invoiceInfo['status_id'])."', '".
            $dbSocket->escapeSimple($invoiceInfo['type_id'])."', '".
            $dbSocket->escapeSimple($invoiceInfo['notes'])."', ".
            " '$currDate', '$currBy', NULL, NULL)";
    $res = $dbSocket->query($sql);
    $logDebugSQL .= $sql . "\n";

    // if there hasn't been any errors with inserting the invoice record
    if (!PEAR::isError($res)) {

        // get the added invoice id from the database
        $invoice_id = $dbSocket->getOne( "SELECT LAST_INSERT_ID() FROM `".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE']."`" );

        if (!$invoice_id)
            return false;

        foreach($invoiceItems as $invoiceItem) {
            // set default information for the invoice items
            /*
            $myinvoiceItems['plan_id'] = '' ;
            $myinvoiceItems['amount'] = '' ;
            $myinvoiceItems['tax'] = '' ;
            $myinvoiceItems['notes'] = '' ;
            $invoiceItems = array_merge($myinvoiceItems, $invoiceItems);
            */

            // now add an invoice item
            $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'].
                " (id, invoice_id, plan_id, amount, tax_amount, notes, ".
                " creationdate, creationby, updatedate, updateby) ".
                " VALUES (0, '".$invoice_id."', '".
                $dbSocket->escapeSimple($invoiceItem['plan_id'])."', '".
                $dbSocket->escapeSimple($invoiceItem['amount'])."', '".
                $dbSocket->escapeSimple($invoiceItem['tax'])."', '".
                $dbSocket->escapeSimple($invoiceItem['notes'])."', ".
                " '$currDate', '$currBy', NULL, NULL)";

            $res = $dbSocket->query($sql);
            $logDebugSQL .= $sql . "\n";

        }

    }



    include('../common/includes/db_close.php');

    return true;

}


/*
 *********************************************************************************************************
 * userInvoicesStatus
 * $username            username to provide information of
 * $drawTable           if set to 1 (enabled) a toggled on/off table will be drawn
 *
 * returns user invoices status: total invoices, partial, completed, due invoices, due amount
 *
 *********************************************************************************************************
 */
function userInvoicesStatus($user_id, $drawTable) {

    include_once('include/management/pages_common.php');
    include('../common/includes/db_open.php');

    // sanitize variable for sql statement
    $user_id = intval($user_id);

    $sql = sprintf("SELECT COUNT(DISTINCT(a.id)) AS TotalInvoices, a.id, a.date, a.status_id, a.type_id, b.contactperson,
                           b.username, c.value AS status, COALESCE(SUM(e2.totalpayed), 0) AS totalpayed,
                           COALESCE(SUM(d2.totalbilled), 0) AS totalbilled, SUM(a.status_id=1) AS openInvoices
                      FROM %s AS a INNER JOIN %s AS b ON a.user_id=b.id
                                   INNER JOIN %s AS c ON a.status_id=c.id
                                    LEFT JOIN (SELECT SUM(d.amount + d.tax_amount) AS totalbilled, invoice_id
                                                 FROM %s AS d GROUP BY d.invoice_id) AS d2 ON d2.invoice_id=a.id
                                    LEFT JOIN (SELECT SUM(e.amount) as totalpayed, invoice_id
                                                 FROM %s AS e GROUP BY e.invoice_id) AS e2 ON e2.invoice_id=a.id
                     WHERE a.user_id=%d GROUP BY b.id", $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'],
                                                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                                        $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS'],
                                                        $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'],
                                                        $configValues['CONFIG_DB_TBL_DALOPAYMENTS'], $user_id);

    $res = $dbSocket->query($sql);
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

    $totalInvoices = $row['TotalInvoices'];
    $totalBilled = $row['totalbilled'];
    $totalPayed = $row['totalpayed'];
    $openInvoices = $row['openInvoices'];

    include('../common/includes/db_close.php');

    if ($drawTable == 1) {
        include_once("../common/includes/layout.php");

        $fieldset = array( 'title' => 'User Invoices' );
        open_fieldset($fieldset);

        $button_descriptors0 = array();
        $button_descriptors0[] = array(
                                            "label" => "New Invoice",
                                            "onclick" => sprintf("javascript:window.location='bill-invoice-new.php?user_id=%d'", $user_id),
                                            "class" => "btn-success",
                                      );

        $button_descriptors0[] = array(
                                            "label" => "Show Invoices",
                                            "onclick" => sprintf("javascript:window.location='bill-invoice-list.php?user_id=%d'", $user_id),
                                            "class" => "btn-primary",
                                      );

        $button_descriptors0[] = array(
                                            "label" => "Show Payments",
                                            "onclick" => sprintf("javascript:window.location='bill-payments-list.php?user_id=%d'", $user_id),
                                            "class" => "btn-secondary",
                                      );

        echo '<div class="d-flex flex-row-reverse">';
        print_additional_controls($button_descriptors0);
        echo "</div>";

        $input_descriptors0 = array();
        $input_descriptors0[] = array(
                                        "type" =>"number",
                                        "name" => "total_invoices",
                                        "caption" => "Total Invoices",
                                        "disabled" => true,
                                        "value" => $totalInvoices,
                                     );

        $input_descriptors0[] = array(
                                        "type" =>"number",
                                        "name" => "open_invoices",
                                        "caption" => "Open Invoices",
                                        "disabled" => true,
                                        "value" => $openInvoices,
                                     );

        $input_descriptors0[] = array(
                                        "type" =>"number",
                                        "name" => "total_billed",
                                        "caption" => "Total Billed",
                                        "disabled" => true,
                                        "value" => $totalBilled,
                                     );

        $input_descriptors0[] = array(
                                        "type" =>"number",
                                        "name" => "total_payed",
                                        "caption" => "Total Payed",
                                        "disabled" => true,
                                        "value" => $totalPayed,
                                     );

        $input_descriptors0[] = array(
                                        "type" =>"number",
                                        "name" => "balance",
                                        "caption" => "Balance",
                                        "disabled" => true,
                                        "value" => $totalPayed - $totalBilled,
                                     );

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();
    }
}


/*
 *********************************************************************************************************
 * userBillingRatesSummary
 * $username            username to provide information of
 * $startdate        starting date, first accounting session
 * $enddate        ending date, last accounting session
 * $ratename        the rate to use for calculations
 * $drawTable           if set to 1 (enabled) a toggled on/off table will be drawn
 *
 * returns user connection information: uploads, download, session time, total billed, etc...
 *
 *********************************************************************************************************
 */
function userBillingRatesSummary($username, $startdate, $enddate, $ratename, $drawTable) {

    include_once('include/management/pages_common.php');
    include('../common/includes/db_open.php');

    $username = $dbSocket->escapeSimple($username);            // sanitize variable for sql statement
    $startdate = $dbSocket->escapeSimple($startdate);
    $enddate = $dbSocket->escapeSimple($enddate);
    $ratename = $dbSocket->escapeSimple($ratename);

    // get rate type
    $sql = sprintf("SELECT rateType FROM %s WHERE rateName='%s'", $configValues['CONFIG_DB_TBL_DALOBILLINGRATES'], $ratename);
    $res = $dbSocket->query($sql);

    if ($res->numRows() == 0) {
        return;
    }

    $row = $res->fetchRow();
    list($ratetypenum, $ratetypetime) = explode("/",$row[0]);

    // we need to translate any kind of time into seconds,
    // so a minute is 60 seconds, an hour is 3600, and so on...
    switch ($ratetypetime) {
        case "second":
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
            // a month is 31 days
            $multiplicate = 187488000;
            break;
        default:
            $multiplicate = 0;
            break;
    }

    // then the rate cost would be the amount of seconds times the prefix multiplicator thus:
    $rateDivisor = $ratetypenum * $multiplicate;

    $sql = sprintf("SELECT DISTINCT(ra.username), ra.NASIPAddress, ra.AcctStartTime,
                           SUM(ra.AcctSessionTime) AS AcctSessionTime, dbr.rateCost,
                           SUM(ra.AcctInputOctets) AS AcctInputOctets,
                           SUM(ra.AcctOutputOctets) AS AcctOutputOctets
                      FROM %s AS ra, %s AS dbr
                     WHERE AcctStartTime >= '%s'
                       AND AcctStartTime <= '%s'
                       AND UserName = '%s'
                       AND dbr.rateName = '%s'
                     GROUP BY UserName", $configValues['CONFIG_DB_TBL_RADACCT'],
                                         $configValues['CONFIG_DB_TBL_DALOBILLINGRATES'],
                                         $startdate, $enddate, $username, $ratename);
    $res = $dbSocket->query($sql);
    $row = $res->fetchRow();

    $numrows = $res->numRows();

    list($username, $nasIPAddress, $acctStartTime, $sessionTime, $rateCost, $userUpload, $userDownload) = $row;

    $userUpload = toxbyte($userUpload);
    $userDownload = toxbyte($userDownload);
    $userOnlineTime = time2str($sessionTime);
    $sumBilled = ( $sessionTime/ $rateDivisor ) * $rateCost;

    include('../common/includes/db_close.php');

    if ($numrows == 0) {
        return;
    }

    if ($drawTable == 1) {
        $modal_id = "modal_" . rand();

        $table = array();
        $table['title'] = "Billing Summary";

        $table['rows'][] = array( "Username" , $username, );
        $table['rows'][] = array( "Billing for period of" , "$startdate until $enddate (inclusive)", );
        $table['rows'][] = array( "Online Time" , $userOnlineTime, );
        $table['rows'][] = array( "User Upload" , $userUpload, );
        $table['rows'][] = array( "User Download" , $userDownload, );
        $table['rows'][] = array( "Rate Name" , $ratename, );
        $table['rows'][] = array( "Total Billed" , $sumBilled , );

        echo <<<EOF
<button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#{$modal_id}">Show {$table['title']}</button>

<div class="modal fade" id="{$modal_id}" tabindex="-1" aria-labelledby="{$modal_id}_label" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="{$modal_id}_label">{$table['title']}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
EOF;
        print_simple_table($table);

        echo <<<EOF
            </div>
        </div>
    </div>
</div>

EOF;
    }
}


/*
 *********************************************************************************************************
 * userBillingPayPalSummary
 * $startdate        starting date, first accounting session
 * $enddate        ending date, last accounting session
 * $drawTable           if set to 1 (enabled) a toggled on/off table will be drawn
 *
 * returns user connection information: uploads, download, session time, total billed, etc...
 *
 *********************************************************************************************************
 */
function userBillingPayPalSummary($startdate, $enddate, $payer_email, $payment_address_status,
                                  $payer_status, $payment_status, $vendor_type, $drawTable) {
    global $logDebugSQL;

    include('../common/includes/db_open.php');

    $sql_WHERE = array();

    if (!empty($startdate)) {
        $sql_WHERE[] = sprintf("payment_date > '%s'", $dbSocket->escapeSimple($startdate));
    }

    if (!empty($startdate)) {
        $sql_WHERE[] = sprintf("payment_date < '%s'", $dbSocket->escapeSimple($enddate));
    }

    if (!empty($payer_email)) {
        $sql_WHERE[] = sprintf("payer_email LIKE '%s%%'", $dbSocket->escapeSimple($payer_email));
    }

    if (!empty($payment_status)) {
        $sql_WHERE[] = sprintf("payment_status='%s'", $dbSocket->escapeSimple($payment_status));
    }

    if (!empty($vendor_type)) {
        $sql_WHERE[] = sprintf("vendor_type='%s'", $dbSocket->escapeSimple($vendor_type));
    }

    if (!empty($payment_address_status)) {
        $sql_WHERE[] = sprintf("payment_address_status='%s'", $dbSocket->escapeSimple($payment_address_status));
    }

    if (!empty($payer_status)) {
        $sql_WHERE[] = sprintf("payer_status='%s'", $dbSocket->escapeSimple($payer_status));
    }

    $sql = sprintf("SELECT dbm.Username AS Username, business_email, dbp.planName, dbm.planId, SUM(payment_total) AS total,
                           SUM(payment_fee) AS fee, SUM(payment_tax) AS tax, payment_currency,
                           SUM(AcctSessionTime) AS AcctSessionTime, SUM(AcctInputOctets) AS AcctInputOctets,
                           SUM(AcctOutputOctets) AS AcctOutputOctets
                      FROM %s AS dbm LEFT JOIN %s AS ra ON dbm.Username = ra.Username
                                     LEFT JOIN %s AS dbp ON dbm.planId = dbp.id", $configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT'],
                                                                                  $configValues['CONFIG_DB_TBL_RADACCT'],
                                                                                  $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']);
    if (count($sql_WHERE) > 0) {
        $sql .= " WHERE " . implode(" AND ", $sql_WHERE);

    }

    $sql .= " GROUP BY Username";
    $logDebugSQL .= "$sql;\n";
    $res = $dbSocket->query($sql);

    if ($res->numRows() > 0 && $drawTable == 1) {

        include_once('include/management/pages_common.php');

        $row = $res->fetchRow();

        for ($i=0; $i < count($row); $i++) {
            $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
        }

        list( $username, $payer_email, $planName, $planId, $planTotalCost, $planTotalFee, $planTotalTax,
              $planCurrency, $sessionTime, $userUpload, $userDownload ) = $row;

        $grossGain = $planTotalCost - ($planTotalTax + $planTotalFee);

        $userUpload = toxbyte($userUpload);
        $userDownload = toxbyte($userDownload);
        $userOnlineTime = time2str($sessionTime);

        if ($drawTable == 1) {
            $modal_id = "modal_" . rand();

            $table = array();
            $table['title'] = "Billing Summary";

            $table['rows'] = array(
                                        array( "Username", "$username (email: $payer_email)" ),
                                        array( "Billing for period of", "$startdate until $enddate (inclusive)" ),
                                        array( "Online Time", $userOnlineTime ),
                                        array( "User Upload", $userUpload ),
                                        array( "User Download", $userDownload ),
                                        array( "Plan name", "$planName (planId: $planId)" ),
                                        array( "Total Plans Cost <br/> Total Transaction Fees <br/> Total Transaction Taxs",
                                               "$planTotalCost <br/> $planTotalFee <br/> $planTotalTax" ),
                                        array( "Gross Gain", "$grossGain $planCurrency" )
                                  );

            echo <<<EOF
    <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#{$modal_id}">Show {$table['title']}</button>

    <div class="modal fade" id="{$modal_id}" tabindex="-1" aria-labelledby="{$modal_id}_label" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="{$modal_id}_label">{$table['title']}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
EOF;
            print_simple_table($table);

            echo <<<EOF
                </div>
            </div>
        </div>
    </div>

EOF;
        }

    include('../common/includes/db_close.php');

    }
}
