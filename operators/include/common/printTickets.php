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
 *  Description:   This extension provides an HTML output of tickets information
 *
 * Authors:	       Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include('../../library/checklogin.php');

    $redirect = (array_key_exists('PREV_LIST_PAGE', $_SESSION) && !empty(trim($_SESSION['PREV_LIST_PAGE'])))
              ? trim($_SESSION['PREV_LIST_PAGE'])
              : "../../index.php";


    function printTicketsHTMLTable($accounts, $ticketCost, $ticketTime) {

    $output = "";

    global $ticketInformation;
    global $ticketLogoFile;

    // the $accounts array contain the username,password|| first element as it's originally
    // used to be a for CSV table header
    array_shift($accounts);

    // we align 3 tables for each row (each line)
    // for each 4th entry of a new ticket table we put it in a new row of it's own
    $trCounter = 0;
    foreach ($accounts as $account) {

        list($user, $pass) = $account;

        if ($trCounter > 2)
            $trCounter = 0;

        if ($trCounter == 2)
            $trTextEnd = "</tr>";
        else
            $trTextEnd = "";

        if ($trCounter == 0)
            $trTextBeg = "<tr>";
        else
            $trTextBeg = "";

        $output .= "
            $trTextBeg
                <td>
                    <table border='0' cellpadding='1' cellspacing='1' height='140' width='211'>
                        <tbody>
                        <tr align='center'>
                            <td colspan='2'>
                                <img src='$ticketLogoFile' alt='Logo' />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Login</b>:
                            </td>
                            <td>
                                <font size='2'>
                                $user
                                </font>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Password</b>:
                            </td>
                            <td>
                                <font size='2'>
                                $pass
                                </font>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Validity</b>:
                            </td>
                            <td>
                                <font size='2'>
                                $ticketTime
                                </font>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Price</b>:
                            </td>
                            <td>
                                <font size='2'>
                                $ticketCost
                                </font>
                            </td>
                        </tr>
                        <tr>
                            <td colspan='2' valign='top'>
                                <font size='1'>
                                $ticketInformation
                                </font>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </td>
            $trTextEnd
        ";

        $trCounter++;
    }

    print "
         <style type='text/css'>
            @page { size:landscape; margin-top:20cm; margin-right:0cm; margin-left:0cm; margin-bottom: 0px; marks:cross;}
            td, tr, th { border: 1px dotted black }
        </style>
        <html><body>
            <table style='maring-top: 15px; margin-left: auto; margin-right: auto;'
                    cellspacing='15'>
                <tbody>
                            $output
                </tbody>
            </table>
        </body></html>
    ";

}

    $ticketInformation = "<strong>Information</strong>:<br>to use this card, please connect your device to the nearest ssid."
                       . "Open your web browser and enter each needed field.";
    $ticketLogoFile = "../../static/images/daloradius_small.png";


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
    
            if (array_key_exists('accounts', $_POST) && !empty($_POST['accounts']) && is_array($_POST['accounts']) &&
                array_key_exists('type', $_POST) && $_POST['type'] == "batch") {
                
                $batch_name = (array_key_exists('batch_name', $_POST) && !empty(trim($_POST['batch_name'])))
                            ? htmlspecialchars(trim($_POST['batch_name']), ENT_QUOTES, 'UTF-8') : "";
                
                $accounts = $_POST['accounts'];
                
                if (array_key_exists('ticketInformation', $_POST) && !empty(trim($_POST['ticketInformation']))) {
                    $ticketInformation = "<strong>Information</strong>:<br>" . htmlspecialchars(trim($_POST['ticketInformation']), ENT_QUOTES, 'UTF-8');
                    $ticketInformation = str_replace("\n", "<br>", $ticketInformation);
                }
                
                $plan = (array_key_exists('plan', $_POST) && !empty(trim($_POST['plan'])))
                      ? trim($_POST['plan']) : "";
                
                $ticketCost = "";
                $ticketTime = "";
                
                if (!empty($plan)) {
                    include_once('../../../common/includes/db_open.php');
                    include_once('../management/pages_common.php');

                    $sql = sprintf("SELECT dbp.planCost AS planCost, dbp.planTimeBank AS planTimeBank, dbp.planCurrency AS planCurrency
                                      FROM %s AS dbp WHERE dbp.planName='%s'",
                                   $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'], $dbSocket->escapeSimple($plan));
                    $res = $dbSocket->query($sql);
                    list($ticketCost, $ticketTime, $ticketCurrency) = $res->fetchRow();

                    $ticketCost = "$ticketCost $ticketCurrency";
                    $ticketTime = time2str($ticketTime);

                    include_once('../../../common/includes/db_close.php');
                }
                
                $card_body_height = 10;
                $card_foot_height = 30;
                if (!empty($ticketCost)) {
                    $card_foot_height -= 5;
                    $card_body_height += 5;
                }
                if (!empty($ticketTime)) {
                    $card_foot_height -= 5;
                    $card_body_height += 5;
                }
            
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title><?= (!empty($batch_name)) ? $batch_name : "user cards" ?></title>
        
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
    height: <?= $card_body_height ?>mm;
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
    height: <?= $card_foot_height ?>mm;
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

<?php
            // remove first element
            array_shift($accounts);
            
            echo '<div class="container">';
            
            foreach ($accounts as $i => $account) {
                list($username, $password) = $account;
                
                if ($i != 0 && $i % 4 == 0) {
                    echo '</div><div class="container">';
                }
                
                $trs = array(
                                "User" => $username,
                                "Pass" => $password
                            );
                
                if (!empty($ticketTime)) {
                    $trs["Validity"] = $ticketTime;
                }
                
                if (!empty($ticketCost)) {
                    $trs["Price"] = $ticketCost;
                }
                
                echo '<div class="card">';
                printf('<div class="card-head"><img src="%s"></div>', $ticketLogoFile); 
                echo '<div class="card-body">'
                   . '<table>';

                foreach ($trs as $label => $value) {
                    printf('<tr><th>%s:</th><td>%s</td></tr>', $label, htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
                }

                echo '</table>'
                   . '</div>'
                   . '<div class="card-foot">';
                printf('<p>%s</p>', $ticketInformation);
                echo '</div>'
                   . '</div>';
                
                $i++;
            }
            
            echo '</div>';
?>

    </body>
</html>
<?php
                exit;
            }
        }
    }
    
    header("Location: $redirect");

?>
