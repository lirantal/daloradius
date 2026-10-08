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
    $login_user = $_SESSION['login_user'];

    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");

    require_once __DIR__ . '/library/portal_pages_pdo.php';
    $invoice_id = '';
    try { $invoice_id = dalo_portal_id($_REQUEST['invoice_id'] ?? ''); }
    catch (InvalidArgumentException $exception) { /* Render the historical invalid-id notice. */ }

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
        $logAction = "invalid or empty invoice id on page: ";
    } else {

        $portalPdo = null;
        try {
        $portalPdo = dalo_portal_handle($configValues);
        $portalInvoice = dalo_portal_invoice($portalPdo, $configValues, $login_user, $invoice_id);
        $portalPdo = null;
        if ($portalInvoice['customer']) {
            if ($portalInvoice['header']) {
                $row = $portalInvoice['header'];
                $invoice_date = $row['date']; $invoice_status_id = $row['status_id'];
                $invoice_type_id = $row['type_id']; $user_id = $row['user_id'];
                $invoice_notes = $row['notes']; $contactperson = $row['contactperson'];
                $username = $row['username']; $city = $row['city']; $state = $row['state'];
                $type = $row['type']; $status = $row['status'];
                $totalpayed = $row['totalpayed']; $totalbilled = $row['totalbilled'];

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

                $numrows = count($portalInvoice['items']);

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

                    foreach ($portalInvoice['items'] as $item) {
                        $row = array($item['planName'], $item['tax_amount'], $item['amount'], $item['notes']);
                        // print table row
                        print_table_row($row);
                    }

                    echo '</tbody>'
                       . '</table>';
                } else {
                    $failureMsg = "this invoice has no items";
                }

                close_fieldset();

                close_tab($navkeys, 1);

                // close tab wrapper
                close_tab_wrapper();

            } else {
                // no details to show
                $failureMsg = "this invoice has no details";
            }


        } else {
            // missing user id
            $failureMsg = "problems finding your user id";
        }

        } catch (Throwable $exception) {
            $failureMsg = 'Invoice unavailable';
        } finally { $portalPdo = null; }

    }

    include_once("include/management/actionMessages.php");

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
