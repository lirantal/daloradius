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

    include ("library/checklogin.php");
    $login_user = $_SESSION['login_user'];

    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");

    $invoice_id = (array_key_exists('invoice_id', $_REQUEST) && intval(trim($_REQUEST['invoice_id'])) > 0)
                    ? intval(trim($_REQUEST['invoice_id'])) : "";

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query for invoice_id [$invoice_id] on page: ";
    $logDebugSQL = "";

    $title = t('Intro','billinvoiceedit.php');
    $help = t('helpPage','billinvoicesedit');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    // invoice details
    if (empty($invoice_id)) {
        $failureMsg = "invalid or empty invoice id, please specify a valid invoice id.";
        $logAction .= "invalid or empty invoice id on page: ";
    } else {

        include('../common/includes/db_open.php');

        $sql = sprintf("SELECT id FROM %s WHERE username = '%s'",
                       $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'], $dbSocket->escapeSimple($login_user));
        $res = $dbSocket->query($sql);

        $numrows = $res->numRows();

        if ($numrows > 0) {
            $user_id = $res->fetchrow()[0];

            // get invoice details
            $sql = sprintf("SELECT a.id, a.date, a.status_id, a.type_id, a.user_id, a.notes, b.contactperson, b.username,
                                   b.city, b.state, f.value AS type, c.value AS status, COALESCE(e2.totalpayed, 0) AS totalpayed,
                                   COALESCE(d2.totalbilled, 0) AS totalbilled
                              FROM %s AS a INNER JOIN %s AS b ON a.user_id = b.id
                                           INNER JOIN %s AS c ON a.status_id = c.id
                                           INNER JOIN %s AS f ON a.type_id = f.id
                                            LEFT JOIN (SELECT SUM(d.amount + d.tax_amount) AS totalbilled, invoice_id, amount,
                                                              tax_amount, notes, plan_id
                                                         FROM %s AS d GROUP BY d.invoice_id) AS d2 ON d2.invoice_id = a.id
                                            LEFT JOIN %s AS bp2 ON bp2.id = d2.plan_id
                                            LEFT JOIN (SELECT SUM(e.amount) as totalpayed, invoice_id
                                                         FROM %s AS e GROUP BY e.invoice_id) AS e2 ON e2.invoice_id = a.id
                             WHERE a.id=%d AND a.user_id=%d
                             GROUP BY a.id",
                           $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'], $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                           $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS'], $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICETYPE'],
                           $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'], $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                           $configValues['CONFIG_DB_TBL_DALOPAYMENTS'], $invoice_id, $user_id);

            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";

            $numrows = $res->numRows();

            if ($numrows > 0) {

                $row = $res->fetchRow();

                list(
                        $invoice_id, $invoice_date, $invoice_status_id, $invoice_type_id, $user_id,
                        $invoice_notes, $contactperson, $username, $city, $state, $type, $status,
                        $totalpayed, $totalbilled
                    ) = $row;

                // print customer info
                printf('<div><strong>Customer</strong>: %s',
                       htmlspecialchars($username, ENT_QUOTES, 'UTF-8'));

                if (!empty($contactperson)) {
                     printf(' (%s)', htmlspecialchars($contactperson, ENT_QUOTES, 'UTF-8'));
                }

                $arr = array();

                if (!empty($city)) {
                    $arr[] = htmlspecialchars($city, ENT_QUOTES, 'UTF-8');
                }

                if (!empty($state)) {
                    $arr[] = htmlspecialchars($state, ENT_QUOTES, 'UTF-8');
                }

                if (count($arr) > 0) {
                    echo "<br>" . implode(", ", $arr);
                }

                echo '</div>';

                // set navbar stuff
                $navkeys = array( 'Invoice', 'Items', );

                // print navbar controls
                print_tab_header($navkeys);

                // descriptors 0
                $input_descriptors0 = array();
                
                $onclick = "window.location.href='include/common/notificationsUserInvoice.php?destination=%s&invoice_id=%d'";
                $button_descriptors1 = array();
                $input_descriptors0[] = array(
                                                "type" => "button",
                                                "name" => "DownloadInvoice",
                                                "value" => "Download Invoice",
                                                "onclick" => sprintf($onclick, 'download', $invoice_id),
                                              );
                
                $input_descriptors0[] = array(
                                                "name" => "totalbilled",
                                                "caption" => t('all','TotalBilled'),
                                                "type" => "number",
                                                "value" => $totalbilled,
                                                "min" => 0,
                                                "step" => ".01",
                                                "disabled" => true,
                                             );

                $input_descriptors0[] = array(
                                                "name" => "totalpayed",
                                                "caption" => t('all','TotalPayed'),
                                                "type" => "number",
                                                "value" => $totalpayed,
                                                "min" => 0,
                                                "step" => ".01",
                                                "disabled" => true,
                                             );

                $balance = floatval($totalpayed - $totalbilled);
                $input_descriptors0[] = array(
                                                "name" => "balance",
                                                "caption" => t('all','Balance'),
                                                "type" => "number",
                                                "value" => $balance,
                                                "min" => "0",
                                                "step" => ".01",
                                                "disabled" => true,
                                             );

                $input_descriptors0[] = array(
                                                "name" => "invoice_status",
                                                "caption" => t('all','InvoiceStatus'),
                                                "type" => "text",
                                                "value" => $status,
                                                "disabled" => true,
                                             );

                $input_descriptors0[] = array(
                                                "name" => "invoice_type",
                                                "caption" => t('all','InvoiceType'),
                                                "type" => "text",
                                                "value" => $type,
                                                "disabled" => true,
                                             );

                $input_descriptors0[] = array(
                                                "name" => "invoice_date",
                                                "caption" => t('all','PaymentDate'),
                                                "type" => "date",
                                                "value" => $invoice_date,
                                                "min" => date("1970-m-01"),
                                                "disabled" => true,
                                             );

                // open tab wrapper
                open_tab_wrapper();

                // tab 0
                open_tab($navkeys, 0, true);

                $fieldset0_descriptor = array( "title" => t('title','Invoice') );

                open_fieldset($fieldset0_descriptor);

                foreach ($input_descriptors0 as $input_descriptor) {
                    print_form_component($input_descriptor);
                }

                close_fieldset();

                close_tab($navkeys, 0);

                // tab 1
                open_tab($navkeys, 1);

                $fieldset1_descriptor = array( "title" => t('title','Items') );

                open_fieldset($fieldset1_descriptor);

                $sql = sprintf("SELECT b.planName, a.tax_amount, a.amount, a.notes
                                  FROM %s a LEFT JOIN %s b ON a.plan_id=b.id
                                 WHERE a.invoice_id=%d
                                 ORDER BY a.id ASC", $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'],
                                                     $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                                                     $invoice_id);
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";

                $numrows = $res->numRows();

                if ($numrows > 0) {

                    echo '<table class="table table-striped table-hover my-2">'
                       . '<tbody id="container">'
                       . '<tr>';

                    $headers = array(
                                        "Plan",
                                        "Tax",
                                        t('all','Amount'),
                                        t('ContactInfo','Notes'),
                                   );

                    foreach ($headers as $header) {
                        printf("<th>%s</th>", $header);
                    }

                    echo '</tr>' . "\n";

                    while($row = $res->fetchRow()) {
                        // print table row
                        print_table_row($row);
                    }

                    echo '</tbody>'
                       . '</table>';
                } else {
                    $failureMsg = "this invoice has no items";
                    //~ $logAction .= "invalid or empty invoice id on page: ";
                }

                close_fieldset();

                close_tab($navkeys, 1);

                // close tab wrapper
                close_tab_wrapper();

            } else {
                // no details to show
                $failureMsg = "this invoice has no details";
                //~ $logAction .= "invalid or empty invoice id on page: ";
            }


        } else {
            // missing user id
            $failureMsg = "problems finding your user id";
        }

        include('../common/includes/db_close.php');

    }

    include_once("include/management/actionMessages.php");

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
