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
 * Description:    returns user billing information (rates, plans, etc)
 *
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

require_once dirname(__DIR__, 2) . '/library/shared_context_pdo.php';

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
 * $db_error_handler       optional error callback for JSON callers (uses the existing DB hook)
 *
 *********************************************************************************************************
 */
function userInvoiceAdd($userId, $invoiceInfo = array(), $invoiceItems = array(), $db_error_handler = null, ?PDO $pdo = null) {
    global $configValues;
    $owned = false;
    try {
        $pdo = dalo_shared_handle($configValues, $pdo);
        if ($pdo->inTransaction()) { throw new LogicException('Caller owns invoice transaction'); }
        if (!is_array($invoiceInfo) || !is_array($invoiceItems)) {
            throw new InvalidArgumentException('Invalid invoice request');
        }
        $now = date('Y-m-d H:i:s');
        $creator = dalo_shared_text($_SESSION['operator_user'] ?? '');
        $info = array_merge(array('date' => $now, 'status_id' => 1, 'type_id' => 1,
            'notes' => 'provisioned new user from daloRADIUS platform'), $invoiceInfo);
        $info['date'] = dalo_shared_text($info['date']);
        if (!preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}(?: [0-9]{2}:[0-9]{2}:[0-9]{2})?\z/', $info['date']) ||
            strtotime($info['date']) === false) {
            throw new InvalidArgumentException('Invalid invoice date');
        }
        $invoice_date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', strlen($info['date']) === 10 ? $info['date'] . ' 00:00:00' : $info['date']);
        if ($invoice_date === false || $invoice_date->format('Y-m-d H:i:s') !== (strlen($info['date']) === 10 ? $info['date'] . ' 00:00:00' : $info['date'])) {
            throw new InvalidArgumentException('Invalid invoice calendar date');
        }
        $info['date'] = $invoice_date->format('Y-m-d H:i:s');
        $info['status_id'] = dalo_shared_id($info['status_id']);
        $info['type_id'] = dalo_shared_id($info['type_id']);
        $info['notes'] = dalo_shared_text($info['notes']);
        $items = array();
        foreach ($invoiceItems as $item) {
            if (!is_array($item)) { throw new InvalidArgumentException('Invalid invoice item'); }
            $plan = dalo_shared_id($item['plan_id'] ?? null);
            $amount = dalo_shared_text($item['amount'] ?? null);
            $tax = dalo_shared_text($item['tax'] ?? null);
            foreach (array($amount, $tax) as $money) {
                if (!preg_match('/\A-?(?:[0-9]{1,8})(?:\.[0-9]{1,2})?\z/', $money)) {
                    throw new InvalidArgumentException('Invalid invoice amount');
                }
            }
            $items[] = array($plan, $amount, $tax, dalo_shared_text($item['notes'] ?? ''));
        }
        $bill = dalo_shared_table($pdo, $configValues, 'CONFIG_DB_TBL_DALOUSERBILLINFO');
        $invoices = dalo_shared_table($pdo, $configValues, 'CONFIG_DB_TBL_DALOBILLINGINVOICE');
        $lines = dalo_shared_table($pdo, $configValues, 'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS');
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') {
            throw new InvalidArgumentException('Unsupported invoice write driver');
        }
        foreach (array('CONFIG_DB_TBL_DALOBILLINGINVOICE', 'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS',
                       'CONFIG_DB_TBL_DALOUSERBILLINFO') as $key) {
            $engine = dalo_shared_rows($pdo, 'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:table',
                array(':table' => $configValues[$key]));
            if (count($engine) !== 1 || strtoupper($engine[0][0]) !== 'INNODB') {
                throw new RuntimeException('Invoice tables must be transactional');
            }
        }
        if (!$pdo->beginTransaction()) { throw new RuntimeException('Invoice transaction failed'); }
        $owned = true;
        $identity = dalo_shared_text($userId);
        if (ctype_digit($identity)) {
            $users = dalo_shared_rows($pdo, "SELECT id FROM $bill WHERE id=:id FOR UPDATE",
                array(':id' => dalo_shared_id($identity)));
        } else {
            $users = dalo_shared_rows($pdo, "SELECT id FROM $bill WHERE username=:username FOR UPDATE",
                array(':username' => $identity));
        }
        if (count($users) !== 1) { throw new InvalidArgumentException('Missing or ambiguous billing user'); }
        $user_id = (int) $users[0][0];
        dalo_shared_rows($pdo, "INSERT INTO $invoices
            (user_id,date,status_id,type_id,notes,creationdate,creationby,updatedate,updateby)
            VALUES (:user,:date,:status,:type,:notes,:created,:creator,NULL,NULL)",
            array(':user' => $user_id, ':date' => $info['date'], ':status' => $info['status_id'],
                ':type' => $info['type_id'], ':notes' => $info['notes'], ':created' => $now, ':creator' => $creator));
        $id = (int) $pdo->lastInsertId();
        if ($id < 1) { throw new RuntimeException('Missing inserted invoice identity'); }
        $stored = dalo_shared_rows($pdo, "SELECT notes,creationby,user_id,status_id,type_id,date,creationdate,updatedate,updateby FROM $invoices WHERE id=:id", array(':id' => $id));
        if (count($stored) !== 1 || $stored[0][0] !== $info['notes'] || $stored[0][1] !== $creator ||
            (int) $stored[0][2] !== $user_id || (int) $stored[0][3] !== $info['status_id'] || (int) $stored[0][4] !== $info['type_id'] || $stored[0][5] !== $info['date'] ||
            $stored[0][6] !== $now || $stored[0][7] !== null || $stored[0][8] !== null) {
            throw new RuntimeException('Invoice storage mismatch');
        }
        foreach ($items as $item) {
            dalo_shared_rows($pdo, "INSERT INTO $lines
                (invoice_id,plan_id,amount,tax_amount,notes,creationdate,creationby,updatedate,updateby)
                VALUES (:invoice,:plan,:amount,:tax,:notes,:created,:creator,NULL,NULL)",
                array(':invoice' => $id, ':plan' => $item[0], ':amount' => $item[1], ':tax' => $item[2],
                    ':notes' => $item[3], ':created' => $now, ':creator' => $creator));
            $line_id = (int) $pdo->lastInsertId();
            $stored = dalo_shared_rows($pdo, "SELECT notes,creationby,(amount=:amount),(tax_amount=:tax),plan_id,invoice_id,creationdate,updatedate,updateby FROM $lines WHERE id=:id",
                array(':id' => $line_id, ':amount' => $item[1], ':tax' => $item[2]));
            if (count($stored) !== 1 || $stored[0][0] !== $item[3] || $stored[0][1] !== $creator ||
                (int) $stored[0][2] !== 1 || (int) $stored[0][3] !== 1 ||
                (int) $stored[0][4] !== $item[0] || (int) $stored[0][5] !== $id || $stored[0][6] !== $now ||
                $stored[0][7] !== null || $stored[0][8] !== null) {
                throw new RuntimeException('Invoice item storage mismatch');
            }
        }
        if (!$pdo->commit()) { throw new RuntimeException('Invoice commit failed'); }
        $owned = false;
        return true;
    } catch (Throwable $error) {
        if ($owned && $pdo->inTransaction()) { $pdo->rollBack(); }
        error_log('User invoice creation failed: ' . get_class($error));
        if (is_callable($db_error_handler)) { $db_error_handler(new RuntimeException('Unable to create user invoice')); }
        return false;
    }
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
function userInvoicesStatus($user_id, $drawTable, ?PDO $pdo = null) {
    global $configValues;
    $buffer_level = ob_get_level();
    ob_start();
    try {
        $pdo = dalo_shared_handle($configValues, $pdo);

    include_once('include/management/pages_common.php');

    // sanitize variable for sql statement
    $user_id = dalo_shared_id($user_id);

    $daloTable0 = dalo_shared_table($pdo, $configValues, 'CONFIG_DB_TBL_DALOBILLINGINVOICE');
    $daloTable1 = dalo_shared_table($pdo, $configValues, 'CONFIG_DB_TBL_DALOUSERBILLINFO');
    $daloTable2 = dalo_shared_table($pdo, $configValues, 'CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS');
    $daloTable3 = dalo_shared_table($pdo, $configValues, 'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS');
    $daloTable4 = dalo_shared_table($pdo, $configValues, 'CONFIG_DB_TBL_DALOPAYMENTS');
    $sql = "SELECT COUNT(DISTINCT(a.id)) AS TotalInvoices, a.id, a.date, a.status_id, a.type_id, b.contactperson,
                           b.username, c.value AS status, COALESCE(SUM(e2.totalpayed), 0) AS totalpayed,
                           COALESCE(SUM(d2.totalbilled), 0) AS totalbilled, SUM(a.status_id=1) AS openInvoices
                      FROM {$daloTable0} AS a INNER JOIN {$daloTable1} AS b ON a.user_id=b.id
                                   INNER JOIN {$daloTable2} AS c ON a.status_id=c.id
                                    LEFT JOIN (SELECT SUM(d.amount + d.tax_amount) AS totalbilled, invoice_id
                                                 FROM {$daloTable3} AS d GROUP BY d.invoice_id) AS d2 ON d2.invoice_id=a.id
                                    LEFT JOIN (SELECT SUM(e.amount) as totalpayed, invoice_id
                                                 FROM {$daloTable4} AS e GROUP BY e.invoice_id) AS e2 ON e2.invoice_id=a.id
                     WHERE a.user_id=:value5 GROUP BY b.id";
    $query_params = array(':value5' => $user_id);

    $row = dalo_shared_rows($pdo, $sql, $query_params, PDO::FETCH_ASSOC)[0] ?? array();

    $totalInvoices = $row['TotalInvoices'] ?? null;
    $totalBilled = $row['totalbilled'] ?? null;
    $totalPayed = $row['totalpayed'] ?? null;
    $openInvoices = $row['openInvoices'] ?? null;


    if ($drawTable == 1) {
        include_once("../common/includes/layout.php");

        $fieldset = array( 'title' => 'User Invoices' );
        open_fieldset($fieldset);

        $button_descriptors0 = array();
        $button_descriptors0[] = array(
                                            "label" => t('button','NewInvoice'),
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
                                        "caption" => t('all','TotalInvoices'),
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
                                        "caption" => t('all','TotalBilled'),
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
                                        "caption" => t('all','Balance'),
                                        "disabled" => true,
                                        "value" => $totalPayed - $totalBilled,
                                     );

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();
    }
    } catch (Throwable $error) {
        while (ob_get_level() > $buffer_level) { ob_end_clean(); }
        error_log('Shared user report failed: ' . get_class($error));
        if ($drawTable == 1) { echo '<div class="failure">Unable to load user summary.</div>'; }
        return false;
    } finally {
        if (ob_get_level() > $buffer_level) { ob_end_flush(); }
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
function userBillingRatesSummary($username, $startdate, $enddate, $ratename, $drawTable, ?PDO $pdo = null) {
    global $configValues;
    $buffer_level = ob_get_level();
    ob_start();
    try {
        $pdo = dalo_shared_handle($configValues, $pdo);

    include_once('include/management/pages_common.php');

    $username = dalo_shared_text($username);
    $startdate = dalo_shared_text($startdate);
    $enddate = dalo_shared_text($enddate);
    $ratename = dalo_shared_text($ratename);

    // get rate type
    $daloTable0 = dalo_shared_table($pdo, $configValues, 'CONFIG_DB_TBL_DALOBILLINGRATES');
    $sql = "SELECT rateType FROM {$daloTable0} WHERE rateName=:value1";
    $query_params = array(':value1' => $ratename);
    $rows = dalo_shared_rows($pdo, $sql, $query_params);

    if (count($rows) == 0) {
        return;
    }

    $row = $rows[0] ?? array();
    if (!preg_match('/\A([1-9][0-9]*)\/(second|minute|hour|day|week|month)\z/', (string) $row[0], $rate_parts)) {
        throw new InvalidArgumentException('Invalid stored rate divisor');
    }
    list(, $ratetypenum, $ratetypetime) = $rate_parts;

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

    $daloTable0 = dalo_shared_table($pdo, $configValues, 'CONFIG_DB_TBL_RADACCT');
    $daloTable1 = dalo_shared_table($pdo, $configValues, 'CONFIG_DB_TBL_DALOBILLINGRATES');
    $sql = "SELECT DISTINCT(ra.username), ra.NASIPAddress, ra.AcctStartTime,
                           SUM(ra.AcctSessionTime) AS AcctSessionTime, dbr.rateCost,
                           SUM(ra.AcctInputOctets) AS AcctInputOctets,
                           SUM(ra.AcctOutputOctets) AS AcctOutputOctets
                      FROM {$daloTable0} AS ra, {$daloTable1} AS dbr
                     WHERE AcctStartTime >= :value2
                       AND AcctStartTime <= :value3
                       AND UserName = :value4
                       AND dbr.rateName = :value5
                     GROUP BY UserName";
    $query_params = array(':value2' => $startdate, ':value3' => $enddate, ':value4' => $username, ':value5' => $ratename);
    $rows = dalo_shared_rows($pdo, $sql, $query_params);
    $row = $rows[0] ?? array();

    $numrows = count($rows);

    if (!$row) { return; }
    list($username, $nasIPAddress, $acctStartTime, $sessionTime, $rateCost, $userUpload, $userDownload) = $row;

    $userUpload = toxbyte($userUpload);
    $userDownload = toxbyte($userDownload);
    $userOnlineTime = time2str($sessionTime);
    $sumBilled = ( $sessionTime/ $rateDivisor ) * $rateCost;


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
    } catch (Throwable $error) {
        while (ob_get_level() > $buffer_level) { ob_end_clean(); }
        error_log('Shared user report failed: ' . get_class($error));
        if ($drawTable == 1) { echo '<div class="failure">Unable to load user summary.</div>'; }
        return false;
    } finally {
        if (ob_get_level() > $buffer_level) { ob_end_flush(); }
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
                                  $payer_status, $payment_status, $vendor_type, $drawTable, ?PDO $pdo = null) {
    global $configValues;
    $buffer_level = ob_get_level();
    ob_start();
    try {
        $pdo = dalo_shared_handle($configValues, $pdo);



    $sql_WHERE = array();
    $filter_params = array();
    foreach (array('startdate' => $startdate, 'enddate' => $enddate, 'payer_email' => $payer_email,
                   'payment_status' => $payment_status, 'vendor_type' => $vendor_type,
                   'payment_address_status' => $payment_address_status, 'payer_status' => $payer_status) as $key => $value) {
        $value = dalo_shared_text($value);
        if ($value === '') { continue; }
        if ($key === 'startdate') {
            $sql_WHERE[] = 'payment_date >= :startdate';
        } elseif ($key === 'enddate') {
            $sql_WHERE[] = 'payment_date < (:enddate + INTERVAL 1 DAY)';
        } elseif ($key === 'payer_email') {
            // Keep the historical substring/wildcard search of this summary.
            $sql_WHERE[] = 'payer_email LIKE :payer_email';
            $value = '%' . $value . '%';
        } else {
            $sql_WHERE[] = $key . '=:' . $key;
        }
        $filter_params[':' . $key] = $value;
    }

    $daloTable0 = dalo_shared_table($pdo, $configValues, 'CONFIG_DB_TBL_DALOBILLINGMERCHANT');
    $daloTable1 = dalo_shared_table($pdo, $configValues, 'CONFIG_DB_TBL_RADACCT');
    $daloTable2 = dalo_shared_table($pdo, $configValues, 'CONFIG_DB_TBL_DALOBILLINGPLANS');
    $sql = "SELECT dbm.Username AS Username, business_email, dbp.planName, dbm.planId, CAST(SUM(payment_total) AS CHAR) AS total,
                           CAST(SUM(payment_fee) AS CHAR) AS fee, CAST(SUM(payment_tax) AS CHAR) AS tax, payment_currency,
                           SUM(AcctSessionTime) AS AcctSessionTime, SUM(AcctInputOctets) AS AcctInputOctets,
                           SUM(AcctOutputOctets) AS AcctOutputOctets
                      FROM {$daloTable0} AS dbm LEFT JOIN {$daloTable1} AS ra ON dbm.Username = ra.Username
                                     LEFT JOIN {$daloTable2} AS dbp ON dbm.planId = dbp.id";
    $query_params = array();
    if (count($sql_WHERE) > 0) {
        $sql .= " WHERE " . implode(" AND ", $sql_WHERE);

    }

    $sql .= " GROUP BY Username";
    $query_params = $filter_params;
    $rows = dalo_shared_rows($pdo, $sql, $query_params);

    if (count($rows) > 0 && $drawTable == 1) {

        include_once('include/management/pages_common.php');

        $row = $rows[0] ?? array();

        for ($i=0; $i < count($row); $i++) {
            $row[$i] = htmlspecialchars((string) ($row[$i] ?? ''), ENT_QUOTES, 'UTF-8');
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


    }
    } catch (Throwable $error) {
        while (ob_get_level() > $buffer_level) { ob_end_clean(); }
        error_log('Shared user report failed: ' . get_class($error));
        if ($drawTable == 1) { echo '<div class="failure">Unable to load user summary.</div>'; }
        return false;
    } finally {
        if (ob_get_level() > $buffer_level) { ob_end_flush(); }
    }
}
