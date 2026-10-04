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
 *  Description:   This extension provides an HTML output of tickets information
 *
 * Authors:	       Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', '..', '..', 'common', 'includes', 'config_read.php' ]);   
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);
    $operator = $_SESSION['operator_user'];

    $redirect = (array_key_exists('PREV_LIST_PAGE', $_SESSION) && !empty(trim($_SESSION['PREV_LIST_PAGE'])))
              ? trim($_SESSION['PREV_LIST_PAGE'])
              : "../../index.php";

    $ticketInformation = "<strong>Information</strong>:<br>to use this card, please connect your device to the nearest ssid."
                       . "Open your web browser and enter each needed field.";
    $ticketLogoFile = "../../static/images/daloradius_small.png";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && !is_string($_POST['csrf_token'])) {
        http_response_code(400); exit('Invalid ticket request.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && dalo_check_csrf_token()) {
        
        if (array_key_exists('accounts', $_POST) && !empty($_POST['accounts']) && is_array($_POST['accounts']) &&
            array_key_exists('type', $_POST) && $_POST['type'] == "batch") {

            foreach (array('batch_name', 'ticketInformation', 'plan') as $field) {
                if (isset($_POST[$field]) && !is_string($_POST[$field])) {
                    http_response_code(400); exit('Invalid ticket request.');
                }
            }
            // The real batch producer sends one heading row, followed by pairs.
            foreach ($_POST['accounts'] as $account) {
                if (!is_array($account) || array_keys($account) !== array(0, 1) ||
                    !is_string($account[0]) || !is_string($account[1])) {
                    http_response_code(400); exit('Invalid ticket request.');
                }
            }

            $batch_name = (array_key_exists('batch_name', $_POST) && !empty(trim($_POST['batch_name'])))
                        ? htmlspecialchars(trim($_POST['batch_name']), ENT_QUOTES, 'UTF-8') : "";

            $accounts = $_POST['accounts'];

            $ticketInformation = trim($_POST['ticketInformation'] ?? '');
            if (!empty($ticketInformation)) {
                $ticketInformation = "<strong>Information</strong>:<br>" . htmlspecialchars($ticketInformation, ENT_QUOTES, 'UTF-8');
                $ticketInformation = str_replace("\n", "<br>", $ticketInformation);
            }


            $plan = (array_key_exists('plan', $_POST) && !empty(trim($_POST['plan'])))
                    ? trim($_POST['plan']) : "";

            $ticketCost = "";
            $ticketTime = "";

            require_once $configValues['OPERATORS_LIBRARY'] . '/shared_context_pdo.php';
            require_once $configValues['OPERATORS_LIBRARY'] . '/operator_acl_read.php';
            $ticket_pdo = null;
            try {
                $ticket_pdo = dalo_shared_handle($configValues);
                if (!dalo_operator_acl_allowed($ticket_pdo, $configValues, $_SESSION['operator_id'] ?? null, 'mng_batch_add')) {
                    http_response_code(403);
                    exit;
                }
                if ($plan !== '') {
                    include_once $configValues['OPERATORS_INCLUDE_MANAGEMENT'] . '/pages_common.php';
                    $plans_table = dalo_shared_table($ticket_pdo, $configValues, 'CONFIG_DB_TBL_DALOBILLINGPLANS');
                    $rows = dalo_shared_rows($ticket_pdo,
                        "SELECT plancost, plantimebank, plancurrency FROM $plans_table WHERE planname=:plan",
                        array(':plan' => $plan));
                    if (!$rows) { throw new InvalidArgumentException('Unknown ticket plan'); }
                    list($ticketCost, $ticketTime, $ticketCurrency) = $rows[0];
                    $ticketCost = "$ticketCost $ticketCurrency";
                    $ticketTime = time2str($ticketTime);
                }
            } catch (InvalidArgumentException $error) {
                http_response_code(400);
                exit('Invalid ticket request.');
            } catch (Throwable $error) {
                error_log('Ticket context failed: ' . get_class($error));
                http_response_code(503);
                exit('Unable to load ticket data.');
            } finally {
                $ticket_pdo = null;
            }

            $card_body_height = 10;
            $card_foot_height = 30;
            if (!empty($ticketCost) || !empty($ticketTime)) {
                $card_foot_height -= 5;
                $card_body_height += 5;
            }                

            $title = (!empty($batch_name)) ? $batch_name : "user cards";

            echo <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>{$title}</title>

    <style>

@page {
size: 21cm 29.7cm;
margin: 0;
}

body {
font-family: Tahoma;
padding: 1cm;
}

.container:first-child .card {
border-top: 1px dotted gray;
}

.card {
height: 54mm;
width: 44mm;
margin: 0;
border-right: 1px dotted gray;
border-bottom: 1px dotted gray;
}

.card:last-child {
border-right: 1px dotted gray;
}

.card:first-child {
border-left: 1px dotted gray;
}

.card-head {
height: 12mm;
width: 100%;
margin: 0;
position: relative;
}

.card-head img {
position: absolute;
margin: auto;
top: 0;
left: 0;
right: 0;
bottom: 0;
}

.card-body {
height: {$card_body_height}mm;
width: 42mm;
margin: 0;
padding: 1mm;
}

.card-body table {
border: 0;
margin: 0;
width: 100%;
text-align: center;
font-size: 8pt;
padding: 0;
}

.card-body table tr {
border: 0;
margin: 0;
width: 100%;
height: 5mm;
padding: 0;
}

.card-body table th {
text-align: right;
}

.card-body table td {
text-align: left;
}

.card-foot {
height: {$card_foot_height}mm;
margin: 0;
}

.card-foot p {
margin: 0;
width: 42mm;
padding: 1mm;
font-size: 8pt;
font-weight: normal;
text-align: justify;
}

.container {
text-align: center;
padding: 0;
margin: 0;
}

.container > div {
display: inline-block;
vertical-align: middle;
padding: 0;
margin: 0;
}

    </style>

</head>

<body>
    <div class="container">
EOF;

        // remove first element
        array_shift($accounts);

        foreach ($accounts as $i => $account) {
            list($username, $password) = $account;

            if ($i != 0 && $i % 4 == 0) {
                echo '</div><div class="container">';
            }

            $trs = [ "User" => $username, "Pass" => $password ];

            if (!empty($ticketTime)) {
                $trs["Validity"] = $ticketTime;
            }

            if (!empty($ticketCost)) {
                $trs["Price"] = $ticketCost;
            }

            $table = "";
            foreach ($trs as $label => $value) {
                $table .= sprintf('<tr><th>%s:</th><td>%s</td></tr>', $label, htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }

            echo <<<EOF
            <div class="card">
                <div class="card-head">
                    <img src="{$ticketLogoFile}">
                </div><!-- .card-head -->
                <div class="card-body">
                    <table>
                        {$table}
                    </table>
                </div><!-- .card-body -->
                <div class="card-foot">
                    <p>{$ticketInformation}</p>
                </div><!-- .card-foot -->
            </div>
EOF;

            $i++;
        }

        echo <<<EOF
        </div><!-- .container -->
    </body>
</html>
EOF;
            exit;
        }
    }

    header("Location: $redirect");
