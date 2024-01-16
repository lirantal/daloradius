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
 * Authors:    Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

include __DIR__ . '/../../library/checklogin.php';
include __DIR__ . '/../../../common/includes/config_read.php';
include __DIR__ . '/../../lang/main.php';

// Retrieve redirect target
$redirect = ($_SESSION['PREV_LIST_PAGE'] ?? "") ? trim($_SESSION['PREV_LIST_PAGE']) : "../../index.php";

// Check if there is a notification array in the session variable
if (!isset($_SESSION['notification']) || !is_array($_SESSION['notification'])) {
    header("Location: $redirect");
    exit;
}

// Params contain the notification parameters
$params = $_SESSION['notification'];

// Validate notification type
$allowed_types = ["user-welcome", "user-invoice", "batch-details"];
if (!isset($params['type']) || !in_array($params['type'], $allowed_types)) {
    header("Location: $redirect");
    exit;
}

$type = $params['type'];

// Setup template path
$template = sprintf("%s/%s.html", rtrim($configValues['CONFIG_PATH_DALO_TEMPLATES_DIR'], "/"), $type);
if (!file_exists($template)) {
    header("Location: $redirect");
    exit;
}

// Validate notification action
$allowed_actions = ["preview", "download", "email"];
$action = (isset($_GET['action']) && in_array($_GET['action'], $allowed_actions)) ? $_GET['action'] : "preview";


function get_batch_details_context($configValues, $dbSocket, $batch_name) {
    $context = array();

    if ($batch_name == NULL || empty(trim($batch_name))) {
        return $context;
    }

    $tableTags = 'style="width: 580px"';
    $tableTrTags = 'style="background-color: #ECE5B6"';

    $ths = array(
                    t('all','BatchName'),
                    t('all','HotSpot'),
                    t('all','BatchStatus'),
                    t('all','TotalUsers'),
                    t('all','ActiveUsers'),
                    t('all','PlanName'),
                    t('all','PlanCost'),
                    t('all','BatchCost'),
                    t('all','CreationDate'),
                    t('all','CreationBy'),
                );

    // start filling in batch details
    $batch_details = "<table $tableTags><tr $tableTrTags>";

    foreach ($ths as $th) {
        $batch_details .= sprintf("<th>%s</th>", $th);
    }

    $batch_details .= "</tr>";

    $sql = sprintf("SELECT dbh.id AS batch_id, dbh.batch_name, dbh.batch_description, dbh.batch_status,
                           COUNT(DISTINCT(ubi.id)) AS total_users, COUNT(DISTINCT(ra.username)) AS active_users,
                           ubi.planname, dbp.plancost, dbp.plancurrency, dhs.name AS hotspot_name,
                           dbh.creationdate, dbh.creationby, dbh.updatedate, dbh.updateby
                      FROM %s AS dbh LEFT JOIN %s AS ubi ON dbh.id=ubi.batch_id
                                    LEFT JOIN %s AS dbp ON dbp.planname=ubi.planname
                                    LEFT JOIN %s AS dhs ON dbh.hotspot_id=dhs.id
                                    LEFT JOIN %s AS ra ON ra.username=ubi.username
                     WHERE dbh.batch_name='%s'
                     GROUP BY dbh.batch_name", $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'],
                                               $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                               $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                                               $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'],
                                               $configValues['CONFIG_DB_TBL_RADACCT'],
                                               $dbSocket->escapeSimple($batch_name));
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();

    if ($numrows <= 0) {
        return $context;
    }

    $active_users_per = 0;
    $total_users = 0;
    $active_users = 0;
    $batch_cost = 0;

    $hotspot_name = "";
    $batch_id = "";
    $plan_name = "";

    while($row = $res->fetchRow()) {

        foreach ($row as $i => $value) {
            $row[$i] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        list(
                $batch_id, $batch_name, $batch_description, $batch_status, $total_users, $active_users, $plan_name,
                $plancost, $plancurrency, $hotspot_name, $creationdate, $creationby, $updatedate, $updateby
            ) = $row;


        $batch_cost = (intval($active_users) * intval($plancost));

        $tds = array(
                        $batch_name,
                        $hotspot_name,
                        $batch_status,
                        $total_users,
                        $active_users,
                        $plan_name,
                        $plancost,
                        $batch_cost,
                        $creationdate,
                        $creationby
                    );

        $batch_details .= "<tr>";
        foreach ($tds as $td) {
            $batch_details .= sprintf("<td>%s</td>", $td);
        }
        $batch_details .= "</tr>";

    }

    $batch_details .= "</table>";

    $context['__BATCH_DETAILS__'] = $batch_details;

    // filling in plan info
    if (!empty($plan_name)) {

        $sql = sprintf("SELECT planId, planName, planRecurringPeriod, planCost, planSetupCost, planTax, planCurrency
                          FROM %s WHERE planName='%s'", $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                                                        $dbSocket->escapeSimple($plan_name));
        $res = $dbSocket->query($sql);
        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

        echo $sql;
        echo $row;
        exit;

        $service_plan_info = "<table $tableTags>";

        foreach ($row as $rowName => $rowValue) {
            $rowName = htmlspecialchars($rowName, ENT_QUOTES, 'UTF-8');
            $rowValue = htmlspecialchars($rowValue, ENT_QUOTES, 'UTF-8');

            $service_plan_info .= "<tr $tableTrTags>"
                                . sprintf("<th>%s</th>", $rowName)
                                . sprintf("<td>%s</td>", $rowValue)
                                . "</tr>";
        }

        $service_plan_info .= "</table>";
        $context['__SERVICE_PLAN_INFO__'] = $service_plan_info;
    }

    // filling in business info
    if (!empty($hotspot_name)) {
        $sql = sprintf("SELECT id, name, owner, address, companyphone, companyemail, companywebsite
                          FROM %s WHERE name='%s'", $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'],
                                                    $dbSocket->escapeSimple($hotspot_name));
        $res = $dbSocket->query($sql);
        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

        $context['__BUSINESS_NAME__'] = $row['name'];
        $context['__BUSINESS_OWNER_NAME__'] = $row['owner'];
        $context['__BUSINESS_ADDRESS__'] = $row['address'];
        $context['__BUSINESS_PHONE__'] = $row['companyphone'];
        $context['__BUSINESS_EMAIL__'] = $row['companyemail'];
        $context['__BUSINESS_WEBSITE__'] = $row['companywebsite'];
    }

    // active users details
    $sql = sprintf("SELECT ubi.id, ubi.username, ra.acctstarttime, dbh.batch_name
                      FROM %s AS ubi, %s AS ra, %s AS dbh
                     WHERE ubi.batch_id=dbh.id
                       AND ubi.batch_id='%s'
                       AND ubi.username=ra.username
                     GROUP BY ubi.username
                     ORDER BY id, ra.radacctid ASC", $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                                     $configValues['CONFIG_DB_TBL_RADACCT'],
                                                     $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'],
                                                     $dbSocket->escapeSimple($batch_id));
    $res = $dbSocket->query($sql);

    $ths = array(
                    t('all','BatchName'),
                    t('all','Username'),
                    t('all','StartTime'),
                );

    $batch_active_users = "<table $tableTags><tr $tableTrTags>";
    foreach ($ths as $th) {
        $batch_active_users .= sprintf("<th>%s</th>", $th);
    }
    $batch_active_users .= "</tr>";

    $active_users_per = 0;
    $total_users = 0;
    $active_users = 0;
    $batch_cost = 0;
    while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        foreach ($row as $i => $value) {
            $row[$i] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        list($id, $username, $acctstarttime, $batch_name) = $row;

        $tds = array( $username, $acctstarttime, $batch_name );

        $batch_active_users .= "<tr>";
        foreach ($tds as $td) {
            $batch_active_users .= sprintf("<td>%s</td>", $td);
        }
        $batch_active_users .= "</tr>";
    }

    $batch_active_users .= "</table>";
    $context['__BATCH_ACTIVE_USERS__'] = $batch_active_users;

    return $context;
}


function get_user_welcome_context($configValues, $dbSocket, $username) {
    $context = array();

    // Get user info
    $sql = sprintf("SELECT firstname, lastname, email, department, company, workphone, homephone, mobilephone, address, city,
                           state, country, zip, notes, changeuserinfo, portalloginpassword, enableportallogin, creationdate,
                           creationby, updatedate, updateby
                      FROM %s WHERE username='%s'", $configValues['CONFIG_DB_TBL_DALOUSERINFO'],
                                                    $dbSocket->escapeSimple($username));
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();

    if ($numrows <= 0) {
        return $context;
    }

    // Fetch user info
    list($ui_firstname, $ui_lastname, $ui_email, $ui_department, $ui_company, $ui_workphone, $ui_homephone,
        $ui_mobilephone, $ui_address, $ui_city, $ui_state, $ui_country, $ui_zip, $ui_notes, $ui_changeuserinfo,
        $ui_PortalLoginPassword, $ui_enableUserPortalLogin, $ui_creationdate, $ui_creationby, $ui_updatedate,
        $ui_updateby) = $res->fetchRow();

    // Get billing info
    $sql = sprintf("SELECT id, planName, contactperson, company, email, phone, address, city, state, country, zip, paymentmethod,
                           cash, creditcardname, creditcardnumber, creditcardverification, creditcardtype, creditcardexp,
                           notes, changeuserbillinfo, `lead`, coupon, ordertaker, billstatus, lastbill, nextbill,
                           nextinvoicedue, billdue, postalinvoice, faxinvoice, emailinvoice, creationdate, creationby,
                           updatedate, updateby
                      FROM %s WHERE username='%s'", $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                                    $dbSocket->escapeSimple($username));
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();

    if ($numrows <= 0) {
        return $context;
    }

    // Fetch billing info
    list($user_id, $bi_planname, $bi_contactperson, $bi_company, $bi_email, $bi_phone, $bi_address, $bi_city,
        $bi_state, $bi_country, $bi_zip, $bi_paymentmethod, $bi_cash, $bi_creditcardname, $bi_creditcardnumber,
        $bi_creditcardverification, $bi_creditcardtype, $bi_creditcardexp, $bi_notes, $bi_changeuserbillinfo,
        $bi_lead, $bi_coupon, $bi_ordertaker, $bi_billstatus, $bi_lastbill, $bi_nextbill, $bi_nextinvoicedue,
        $bi_billdue, $bi_postalinvoice, $bi_faxinvoice, $bi_emailinvoice, $bi_creationdate, $bi_creationby,
        $bi_updatedate, $bi_updateby) = $res->fetchRow();

    // Initialize email
    $invoice_email = trim($ui_email) ?? trim($bi_emailinvoice) ?? trim($bi_email) ?? "";

    // Initialize phone
    $invoice_phone = trim($ui_mobilephone) ?? trim($ui_workphone) ?? trim($ui_homephone) ?? trim($bi_phone) ?? "(n/a)";

    // Initialize address
    $invoice_address = $ui_address ?? "";
    $invoice_address .= isset($ui_city) ? ", $ui_city" : "";
    $invoice_address .= isset($ui_state) ? "<br>$ui_state" : "";
    $invoice_address .= isset($ui_zip) ? " $ui_zip" : "";
    $invoice_address = $invoice_address ?: "(n/a)";

    // Update the context
    $context = array(
                        '__INVOICE_CREATION_DATE__' => date("Y-m-d"),
                        '__CUSTOMER_NAME__'         => sprintf("%s %s", $ui_firstname, $ui_lastname),
                        '__CUSTOMER_ADDRESS__'      => $invoice_address,
                        '__CUSTOMER_PHONE__'        => $invoice_phone,
                        '__CUSTOMER_EMAIL__'        => $invoice_email,
                        '__PLAN__'                  => $bi_planname,
                    );

    return $context;
}


// Create a context to pass to our PDF creator
$context = array();

include __DIR__ . '/../../../common/includes/db_open.php';

switch ($type) {
    case "user-welcome":
        $username = ($params['username'] ?? "") ? str_replace('%', '', $params['username']) : "";
        if (empty($username)) {
            header("Location: $redirect");
            exit;
        }

        $filename = sprintf('%s-%s-%s.pdf', date("Ymd"), $username, $type);
        $context = get_user_welcome_context($configValues, $dbSocket, $username);
        break;

    case "batch-details":
        $batch_name = ($params['batch_name'] ?? "") ? str_replace('%', '', $params['batch_name']) : "";
        if (empty($batch_name)) {
            header("Location: $redirect");
            exit;
        }

        $filename = sprintf('%s-%s-%s.pdf', date("Ymd"), $batch_name, $type);
        $context = get_batch_details_context($configValues, $dbSocket, $batch_name);
        break;
}

include __DIR__ . '/../../../common/includes/db_close.php';

// Get template contents
$template_contents = file_get_contents($template);

// Fill template contents with correct values
foreach ($context as $key => $value) {
    $template_contents = str_replace($key, $value, $template_contents);
}

// Fix for DOMPDF error: https://stackoverflow.com/questions/37521775/dompdf-error-no-block-level-parent-found-not-good
$html = str_replace("\n", "", $template_contents);

// Include the dompdf class
include __DIR__ . '/../../../common/library/dompdf/dompdf_config.inc.php';

// Instantiate the PDF document
$dompdf = new DOMPDF();
$dompdf->set_base_path(rtrim($configValues['CONFIG_PATH_DALO_TEMPLATES_DIR'], "/"));
$dompdf->load_html($html);
$dompdf->render();
$pdf_contents = $dompdf->output();
$size = strlen($pdf_contents);

switch ($action) {

    case "preview":
    case "download":
        header("Content-type: application/pdf");
        header(sprintf("Content-Disposition: attachment; filename=%s; size=%d", $filename, $size));
        print $pdf_contents;
        break;

    case "email":
        if (strtolower($configValues['CONFIG_MAIL_ENABLED']) != "yes") {
            header("Location: $redirect");
            break;
        }



        break;
}
