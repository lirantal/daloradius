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

    include("../../library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    $invoice_id = (array_key_exists('invoice_id', $_GET) && intval(trim($_GET['invoice_id'])) > 0)
                ? intval(trim($_GET['invoice_id'])) : "";

    $destination = (array_key_exists('destination', $_GET) && !empty(trim($_GET['destination'])) &&
                    in_array(strtolower(trim($_GET['destination'])), array( "download", "email", "preview" )))
                 ? strtolower($_GET['destination']) : "preview";
                 
    if (empty($invoice_id)) {
        die("you should provide a valid invoice id");
    }

    require_once("../../notifications/processNotificationUserInvoice.php");
    require_once("../../../common/includes/config_read.php");
    
    $customerInfo = @getInvoiceDetails($invoice_id);
    
    if ($customerInfo === false) {
        die("error when loading invoice");
    }
    
    $document = @createNotification($customerInfo, $destination == "preview");
        
    if ($destination == "download") {
        $filename = sprintf('notification_invoice_%s.pdf', date("Ymd"));
        $size = strlen($document);
        
        header("Content-type: application/pdf");
        header(sprintf("Content-Disposition: attachment; filename=%s; size=%d", $filename, $size));
            
        print $document;

    } else if ($destination == "email") {
        $smtpInfo['host'] = $configValues['CONFIG_MAIL_SMTPADDR'];
        $smtpInfo['port'] = $configValues['CONFIG_MAIL_SMTPPORT'];
        $smtpInfo['auth'] = $configValues['CONFIG_MAIL_SMTPAUTH'];
        
        $from = $configValues['CONFIG_MAIL_SMTPFROM'];
        
        @emailNotification($document, $customerInfo, $smtpInfo, $from);
        $redirect = (array_key_exists('PREV_LIST_PAGE', $_SESSION) && !empty(trim($_SESSION['PREV_LIST_PAGE'])))
                  ? trim($_SESSION['PREV_LIST_PAGE']) : "/bill-invoice.php";
        header("Location: " . $redirect);
            
    } else /*if ($destination == "preview")*/ {
        print $document;
        //~ $result = file_put_contents(dirname(__FILE__).'/../../notifications/templates/invoice_preview.html', $htmlDocument);
        //~ header('Location: ../../notifications/templates/invoice_preview.html');
    }
    
    
    
    function getInvoiceDetails($invoice_id = NULL) {
        global $configValues;
        
        if ($invoice_id == NULL || empty($invoice_id)) {
            exit;
        }
        
        include_once('../../../common/includes/db_open.php');
        include_once("../../lang/main.php");
            
        $tableTags = 'style="width: 580px"';
        $tableTrTags = 'style="background-color: #ECE5B6"';
        
        // get invoice details
        $sql = sprintf("SELECT a.id, a.date, a.status_id, a.type_id, a.user_id, a.notes, b.contactperson, b.username,
                               b.company, b.city, b.state, b.country, b.zip, b.address, b.email, b.emailinvoice, b.phone,
                               f.value AS type, c.value AS status, COALESCE(e2.totalpayed, 0) AS totalpayed,
                               COALESCE(d2.totalbilled, 0) AS totalbilled
                          FROM %s AS a INNER JOIN %s AS b ON a.user_id=b.id
                                       INNER JOIN %s AS c ON a.status_id=c.id
                                       INNER JOIN %s AS f ON a.type_id=f.id
                                       LEFT JOIN (
                                                    SELECT SUM(d.amount + d.tax_amount) AS totalbilled, invoice_id,
                                                           amount, tax_amount, notes, plan_id
                                                      FROM %s AS d
                                                     GROUP BY d.invoice_id
                                                 ) AS d2 ON d2.invoice_id=a.id
                                       LEFT JOIN %s AS bp2 ON bp2.id=d2.plan_id
                                       LEFT JOIN (
                                                    SELECT SUM(e.amount) AS totalpayed, invoice_id
                                                      FROM %s AS e
                                                     GROUP BY e.invoice_id
                                                 ) AS e2 ON e2.invoice_id=a.id
                         WHERE a.id=%d
                         GROUP BY a.id", $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'],
                                         $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                         $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS'],
                                         $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICETYPE'],
                                         $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'],
                                         $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                                         $configValues['CONFIG_DB_TBL_DALOPAYMENTS'],
                                         $dbSocket->escapeSimple($invoice_id));
        $res = $dbSocket->query($sql);    
        $invoiceDetails = $res->fetchRow(DB_FETCHMODE_ASSOC);

        $customer_email = (empty($invoiceDetails['email'])) ? $invoiceDetails['emailinvoice'] : $invoiceDetails['email'];
            
        // populate user contact informatin
        $customerInfo['customer_name'] = $invoiceDetails['contactperson'];
        $customerInfo['customer_address'] = $invoiceDetails['address'] . " " . $invoiceDetails['city'] . " " . $invoiceDetails['state'];
        $customerInfo['customer_email'] = $customer_email;
        $customerInfo['customer_phone'] = $invoiceDetails['phone'];
        
        // populate user invoice details
        $balance = floatval($invoiceDetails['totalpayed'] - $invoiceDetails['totalbilled']);
        
        $details = array(
                            array( t('all','ClientName'), $invoiceDetails['contactperson'] ),
                            array( t('all','Invoice'), $invoice_id ), 
                            array( t('all','Date'), $invoiceDetails['date'] ), 
                            array( t('all','TotalBilled'), $invoiceDetails['totalbilled'] ), 
                            array( t('all','TotalPayed'), $invoiceDetails['totalpayed'] ), 
                            array( t('all','Balance'), $balance ), 
                            array( t('all','Status'), $invoiceDetails['status'] ), 
                            array( t('ContactInfo','Notes'), $invoiceDetails['notes'] ), 
                        );
        
        $invoice_details = "";
        foreach ($details as $detail) {
            list( $caption, $data ) = $detail;
            $invoice_details .= sprintf("<b>%s</b>; %s<br>", $caption, $data);
        }
        $invoice_details .= "<br><br>";

        $customerInfo['invoice_details'] = $invoice_details;
        
        // populate customer data - NEW STYLE
        $customerInfo['customerId'] = $invoiceDetails['user_id'];
        $customerInfo['customerName'] = (isset($invoiceDetails['company']) ? $invoiceDetails['company'] : $invoiceDetails['contactperson']);
        $customerInfo['customerAddress'] = $invoiceDetails['address'];
        $customerInfo['customerAddress2'] = $invoiceDetails['zip'] . ' '. $invoiceDetails['city'] . ' ' .
                                            $invoiceDetails['state'] . ' ' . $invoiceDetails['country'];
        $customerInfo['customerEmail'] = $invoiceDetails['email'];
        $customerInfo['customerPhone'] = $invoiceDetails['phone'];
        $customerInfo['customerContact'] = $invoiceDetails['contactperson'];
        
        $customerInfo['invoiceNumber'] = $invoice_id;
        $customerInfo['invoiceDate'] = date('Y-m-d', strtotime($invoiceDetails['date']));
        $customerInfo['invoiceStatus'] = strtoupper($invoiceDetails['status']);
        $customerInfo['invoiceTotalBilled'] = $invoiceDetails['totalbilled'];
        $customerInfo['invoicePaid'] = $invoiceDetails['totalpayed'];
        $customerInfo['invoiceDue'] = $balance;
        $customerInfo['invoiceNotes'] = $invoiceDetails['notes'];

        $ths = array(
                        t('title','Plan'),
                        t('all','Tax'),
                        t('all','Amount'),
                        t('ContactInfo','Notes'),
                    );
        // populate user invoice items
        $invoice_items = "<table $tableTags><tr $tableTrTags>";
        foreach ($ths as $th) {
            $invoice_items .= sprintf("<th>%s</th>", $th);
        }
        $invoice_items .= "</tr>";
        
        // get all invoice items
        $sql = sprintf("SELECT a.id, a.plan_id, a.amount, a.tax_amount, a.notes, b.planName
                          FROM %s a LEFT JOIN %s b ON a.plan_id=b.id
                         WHERE a.invoice_id=%d
                         ORDER BY a.id ASC", $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'],
                                             $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                                             $dbSocket->escapeSimple($invoice_id));
        $res = $dbSocket->query($sql);
        
        // initialize invoice items - NEW STYLE
        $invoiceItems = array();
        $invoiceItemsNumber = 1;
        $invoiceItemsTotalAmount = 0;
        $invoiceItemsTotalTax = 0;
        
        while ($row = $res->fetchRow()) {
            foreach ($row as $i => $value) {
                $row[$i] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }

            list($id, $plan_id, $amount, $tax_amount, $notes, $planName) = $row;

            $tds = array( $planName, $amount, $tax_amount, $notes );
            
            $invoice_items .= "<tr>";
            foreach ($tds as $td) {
                $invoice_items .= sprintf("<td>%s</td>", $td);
            }
            $invoice_items .= "</tr>";

            // populate invoice items - NEW STYLE
            $invoiceItem = array();
            
            $invoiceItem['invoiceItemNumber'] = sprintf('%02d', $invoiceItemsNumber);
            $invoiceItem['invoiceItemPlan'] = $planName;
            $invoiceItem['invoiceItemNotes'] = $notes;
            $invoiceItem['invoiceItemAmount'] = $amount;
            $invoiceItem['invoiceItemTaxAmount'] = $tax_amount;
            $invoiceItem['invoiceItemTotalAmount'] = intval($amount) + intval($tax_amount);
            
            $invoiceItems[] = $invoiceItem;
            $invoiceItemsTotalAmount += intval($amount);
            $invoiceItemsTotalTax += intval($tax_amount);
            
            ++$invoiceItemsNumber;
        }

        $invoice_items .= "</table>";
        
        $customerInfo['invoice_items'] = $invoice_items;
        
        // populate invoice items - NEW STYLE
        $customerInfo['invoiceItems'] = $invoiceItems;
        $customerInfo['invoiceTotalAmount'] = $invoiceItemsTotalAmount;
        $customerInfo['invoiceTotalTax'] = $invoiceItemsTotalTax;
        
        include_once('../../../common/includes/db_close.php');

        return $customerInfo;
    }
    
?>
