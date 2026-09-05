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
 * Description:    returns user status (active, expired, disabled)
 *                 as well as performs different user operations
 *                 (e.g. disable user, enable user, etc.) via ajax
 *
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

include_once('../checklogin.php');
include_once('../../../common/includes/config_read.php');
include_once('../../../common/includes/mail.php');
// name of the group of disabled users
$disabled_groupname = 'daloRADIUS-Disabled-Users';

// Keep errors JSON, including failures in ACL checks and billing helpers.
function user_actions_response($success, $message, $status = 200, $level = null, $disabled = null) {
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'level' => $level ?? ($success ? 'success' : 'danger'),
        'disabled' => $disabled,
    ], JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

$db_error_handler = function ($error) {
    user_actions_response(false, 'The action could not be completed. Some changes may already have been applied; check the user and billing records before trying again.', 500);
};

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET' && $method !== 'POST') {
    header('Allow: GET, POST');
    user_actions_response(false, 'Method not allowed.', 405);
}
$input = ($method === 'POST') ? $_POST : $_GET;
$action = $input['action'] ?? null;
$actions = ['userEnable', 'userDisable', 'checkDisabled', 'refillSessionTime', 'refillSessionTraffic', 'userMail'];
if (!is_string($action) || !in_array($action, $actions, true)) {
    user_actions_response(false, 'Missing or unknown action.', 400);
}
if ($action !== 'checkDisabled') {
    if ($method !== 'POST') {
        header('Allow: POST');
        user_actions_response(false, 'This action requires POST.', 405);
    }
    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || !isset($_SESSION['csrf_token']) || !dalo_check_csrf_token($token)) {
        user_actions_response(false, 'Invalid CSRF token. Reload the page before trying again.', 403);
    }
}

$tmp_usernames = $input['username'] ?? [];
$tmp_usernames = is_array($tmp_usernames) ? $tmp_usernames : [$tmp_usernames];
$usernames = [];
foreach ($tmp_usernames as $value) {
    if (!is_string($value)) {
        user_actions_response(false, 'Invalid username.', 400);
    }
    $value = trim($value);
    if ($value !== '' && !in_array($value, $usernames, true)) {
        $usernames[] = $value;
    }
}
if (!$usernames) {
    user_actions_response(false, 'No users selected.', 400);
}

try {
    switch ($action) {
        case 'userDisable':
        case 'userEnable':
        case 'userMail':
        case 'refillSessionTime':
        case 'refillSessionTraffic':
            $operator_perm_file = 'mng_edit';
            break;

        default:
            $operator_perm_file = 'mng_search';
            break;
    }

    $operator_perm_deny_http_status = 403;
    include_once('../check_operator_perm.php');

    include('../../../common/includes/db_open.php');
    include_once('../../include/management/pages_common.php');

    $raw_usernames = $usernames;
    $usernames = array_map([$dbSocket, 'escapeSimple'], $raw_usernames);
    $username_list = "'" . implode("', '", $usernames) . "'";
    $username_list_enc = implode(', ', $raw_usernames);
    $label = count($usernames) > 1 ? 'users' : 'user';
    $class = 'success';
    $message = '';
    $disabled = null;

    switch ($action) {

        case 'userEnable':
            // delete from radusergroup
            $sql = sprintf("DELETE FROM %s WHERE username IN (%s) AND groupname='%s'",
                           $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $username_list, $disabled_groupname);
            $res = $dbSocket->query($sql);
            // return message
            if (DB::isError($res)) {
                $class = "danger";
                $message = sprintf('Failed to enable %s %s.', $label, $username_list_enc);
            } else {
                $class = "success";
                $message = sprintf('Enabled %s %s.', $label, $username_list_enc);
            }
            break;

        case 'userDisable':
            // get the list of users already disabled
            $sql = sprintf("SELECT DISTINCT(username)
                              FROM %s
                             WHERE username IN (%s)
                               AND groupname='%s'",
                           $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $username_list, $disabled_groupname);
            $res = $dbSocket->query($sql);

            $already_disabled = array();
            while ($row = $res->fetchRow()) {
                $already_disabled[] = $row[0];
            }
            // no need to disable already disabled users
            $to_disable = array();
            foreach ($usernames as $i => $username) {
                if (in_array($raw_usernames[$i], $already_disabled, true)) {
                    continue;
                }
                $to_disable[] = $username;
            }
            if (count($to_disable) > 0) {
                // this left piece of the query is the same for all
                $sql0 = sprintf("INSERT INTO %s (username, groupname, priority) VALUES ",
                                $configValues['CONFIG_DB_TBL_RADUSERGROUP']);
                $sql_piece_format = "('%s', '%s', -1)";
                $sql_pieces = array();
                foreach ($to_disable as $username) {
                    $sql_pieces[] = sprintf($sql_piece_format, $username, $disabled_groupname);
                }
                // actually execute the query for disabling users
                $sql = $sql0 . implode(", ", $sql_pieces);
                $res = $dbSocket->query($sql);
                $to_disable_list_enc = implode(', ', array_diff($raw_usernames, $already_disabled));
                if (DB::isError($res)) {
                    $class = "danger";
                    $message = sprintf('Failed to disable %s %s.', $label, $to_disable_list_enc);
                } else {
                    $class = "success";
                    $message = sprintf('Disabled %s %s.', $label, $to_disable_list_enc);
                }
            } else {
                $already_disabled_enc = implode(", ", $already_disabled);
                $already_disabled_label = count($already_disabled) > 1 ? 'users' : 'user';
                $class = "danger";
                $message = sprintf('%s %s already disabled.', $already_disabled_label, $already_disabled_enc);
            }
            break;
//============================
        case 'userMail':
            // Prepare the SQL query to retrieve user mail information
            $sql = sprintf(
                "SELECT radcheck.username, value, email, firstname, lastname
        FROM %s
        JOIN %s
        ON radcheck.username = userinfo.username
        WHERE radcheck.username IN (%s);",
                $configValues['CONFIG_DB_TBL_RADCHECK'], // Table containing user credentials
                $configValues['CONFIG_DB_TBL_DALOUSERINFO'], // Table containing user info
                $username_list // List of usernames to filter the results
            );

            // Execute the SQL query
            $res = $dbSocket->query($sql);

            $sent = 0;
            $failed = 0;
            // Iterate through the results
            while ($row = $res->fetchRow()) {
                // Get the recipient's email address and username
                $recipient_email_address = $row[2]; // Email of the user
                $recipient_name = $row[0]; // Username of the user

                // Set the subject and body of the email
                $subject = 'VPN Credentials'; // Subject of the email
                $body = sprintf(
                    '<b>VPN credential</b><br>Hello, %s %s!<br>Your login is: %s<br>Your password is: %s<br>VPN Server is: %s<br><br>Best regards, Admin',
                    $row[3], // First name of the user
                    $row[4], // Last name of the user
                    $row[0], // Username
                    $row[1],  // Password
                    $configValues['CONFIG_USER_VPN_SERVER']  // VPN Server name/IP
                );

                // Prepare an empty array for email attachments, if any
                $attachment = array();

                // Send the email and capture the success status and message
                list($success, $status) = send_email($configValues, $recipient_email_address, $recipient_name, $subject, $body, $attachment);

                $success ? $sent++ : $failed++;
            }
            $class = ($sent > 0 && $failed === 0) ? 'success' : 'danger';
            $message = sprintf('Emails sent: %d. Failed: %d.', $sent, $failed);
            if ($sent === 0 && $failed === 0) {
                $message = 'No email recipients found.';
            }
            break; // End of the case
//=======================
        case 'checkDisabled':
            $username = $usernames[0];
            $sql = sprintf("SELECT username FROM %s WHERE username='%s' AND groupname='%s'",
                           $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                           $username, $disabled_groupname);
            $res = $dbSocket->query($sql);
            $numrows = $res->numRows();
            $disabled = $numrows > 0;
            if ($disabled) {
                $class = "danger";
                $message = sprintf('Please note that user %s is currently disabled.',
                                   $raw_usernames[0])
                         . ' '
                         . sprintf('To enable this user, remove it from the %s profile.', $disabled_groupname);
            }
            break;

        case 'refillSessionTime':
            // we update the sessiontime value to be 0 - this will only work though
            // for accumulative type accounts. For TTF accounts we need to completely
            // delete the record.
            // to handle this - as a work-around I've modified the accessperiod sql
            // counter definition in radiusd.conf to check for records with AcctSessionTime>=1
            $sql = sprintf("UPDATE %s SET AcctSessionTime=0 WHERE Username IN (%s)",
                           $configValues['CONFIG_DB_TBL_RADACCT'], $username_list);

            $res = $dbSocket->query($sql);

            $isErr = DB::isError($res);

            if (!$isErr) {

                // take care of recording the billing action in billing_history table
                foreach ($usernames as $username) {
                    $sql = sprintf("SELECT ubi.id, ubi.username, ubi.planName, bp.id as PlanID, bp.planTimeRefillCost,
                                           bp.planTax, ubi.paymentmethod, ubi.cash, ubi.creditcardname, ubi.creditcardnumber,
                                           ubi.creditcardverification, ubi.creditcardtype, ubi.creditcardexp
                                      FROM %s AS ubi, %s AS bp
                                     WHERE ubi.planname = bp.planname AND ubi.username = '%s'",
                                   $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                   $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'], $username);
                    $res = $dbSocket->query($sql);
                    $numrows = $res->numRows();

                    if ($numrows == 0) {
                        continue;
                    }

                    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

                    $id = $row['id'];
                    $refillCost = $row['planTimeRefillCost'];

                    $current_datetime = date('Y-m-d H:i:s');
                    $currBy = $_SESSION['operator_user'];

                    $sql = sprintf("INSERT INTO %s (id, username, planId, billAmount, billAction, billPerformer, billReason,
                                                    paymentmethod, cash, creditcardname, creditcardnumber, creditcardverification,
                                                    creditcardtype, creditcardexp, creationdate, creationby)
                                          VALUES (0, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                                                  '%s', '%s',  '%s')",
                                   $configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY'], $username, $row['PlanID'], $row['planTimeRefillCost'],
                                   'Refill Session Time', 'daloRADIUS Web Interface', 'Refill Session Time', $row['paymentmethod'],
                                   $row['cash'], $row['creditcardname'], $row['creditcardnumber'], $row['creditcardverification'],
                                   $row['creditcardtype'], $row['creditcardexp'], $current_datetime, $currBy);
                    $res = $dbSocket->query($sql);


                    // if the refill cost is anything beyond the amount 0, we create an invoice for it.
                    if ($refillCost > 0 && !empty($id)) {

                        // if the user id indeed set in the userbillinfo table
                        include_once('../../include/management/userBilling.php');

                        $invoiceInfo['notes'] = 'refill user account';

                        // calculate tax (planTax is the numerical percentage amount)
                        $planTax = floatval($row['planTax'] / 100);

                        $invoiceItems[0]['plan_id'] = $row['PlanID'];
                        $invoiceItems[0]['amount'] = $row['planTimeRefillCost'];
                        $invoiceItems[0]['tax'] = floatval($row['planTimeRefillCost'] * $planTax);
                        $invoiceItems[0]['notes'] = 'refill user session time';

                        if (!userInvoiceAdd($id, $invoiceInfo, $invoiceItems, $db_error_handler)) {
                            $db_error_handler(null);
                        }
                    }
                }
            }

            // return message
            if ($isErr) {
                $class = "danger";
                $message = sprintf('Cannot refill session time for %s %s', $label, $username_list_enc);
            } else {
                $class = "success";
                $message = sprintf('Session time for %s %s has been successfully refilled (and billed).',
                                   $label, $username_list_enc);
            }

            break;

         case 'refillSessionTraffic':
            $sql = sprintf("UPDATE %s SET AcctInputOctets=0, AcctOutputOctets=0 WHERE Username IN (%s)",
                           $configValues['CONFIG_DB_TBL_RADACCT'], $username_list);

            $res = $dbSocket->query($sql);

            $isErr = DB::isError($res);

            if (!$isErr) {

                // take care of recording the billing action in billing_history table
                foreach ($usernames as $username) {

                    $sql = sprintf("SELECT ubi.id, ubi.username, ubi.planName, bp.id as PlanID, bp.planTax,
                                           bp.planTrafficRefillCost, ubi.paymentmethod, ubi.cash, ubi.creditcardname,
                                           ubi.creditcardnumber, ubi.creditcardverification, ubi.creditcardtype, ubi.creditcardexp
                                      FROM %s AS ubi, %s AS bp
                                     WHERE ubi.planname = bp.planname AND ubi.username = '%s'",
                                   $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                   $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'], $username);
                    $res = $dbSocket->query($sql);
                    $numrows = $res->numRows();

                    if ($numrows == 0) {
                        continue;
                    }

                    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

                    $id = $row['id'];
                    $refillCost = $row['planTrafficRefillCost'];

                    $current_datetime = date('Y-m-d H:i:s');
                    $currBy = $_SESSION['operator_user'];

                    $sql = sprintf("INSERT INTO %s (id, username, planId, billAmount, billAction, billPerformer, billReason,
                                                    paymentmethod, cash, creditcardname, creditcardnumber, creditcardverification,
                                                    creditcardtype, creditcardexp, creationdate, creationby)
                                          VALUES (0, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                                                  '%s', '%s',  '%s')",
                                   $configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY'], $username, $row['PlanID'], $row['planTrafficRefillCost'],
                                   'Refill Session Traffic', 'daloRADIUS Web Interface', 'Refill Session Traffic', $row['paymentmethod'],
                                   $row['cash'], $row['creditcardname'], $row['creditcardnumber'], $row['creditcardverification'],
                                   $row['creditcardtype'], $row['creditcardexp'], $current_datetime, $currBy);
                    $res = $dbSocket->query($sql);

                    // if the refill cost is anything beyond the amount 0, we create an invoice for it.
                    if ($refillCost > 0 && !empty($id)) {
                        // if the user id indeed set in the userbillinfo table
                        include_once('../../include/management/userBilling.php');

                        $invoiceInfo['notes'] = 'refill user account';

                        // calculate tax (planTax is the numerical percentage amount)
                        $planTax = floatval($row['planTax'] / 100);
                        $invoiceItems[0]['plan_id'] = $row['PlanID'];
                        $invoiceItems[0]['amount'] = $row['planTrafficRefillCost'];
                        $invoiceItems[0]['tax'] = floatval($row['planTrafficRefillCost'] * $planTax);
                        $invoiceItems[0]['notes'] = 'refill user session traffic';

                        if (!userInvoiceAdd($id, $invoiceInfo, $invoiceItems, $db_error_handler)) {
                            $db_error_handler(null);
                        }

                    }
                }
            }

            // return message
            if ($isErr) {
                $class = "danger";
                $message = sprintf('Cannot refill session traffic for %s %s', $label, $username_list_enc);
            } else {
                $class = "success";
                $message = sprintf('Session traffic for %s %s has been successfully refilled (and billed).',
                                   $label, $username_list_enc);
            }

            break;

    }

    include('../../../common/includes/db_close.php');

    user_actions_response($action === 'checkDisabled' || $class === 'success', $message, 200, $class, $disabled);
} catch (Throwable $error) {
    $db_error_handler($error);
}
