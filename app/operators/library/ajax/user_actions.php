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
    user_actions_response(false, 'The action could not be completed. Check user and billing records before trying again.', 500);
};

require_once(__DIR__ . '/user_actions_pdo.php');
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET' && $method !== 'POST') {
    header('Allow: GET, POST');
    user_actions_response(false, 'Method not allowed.', 405);
}
$input = ($method === 'POST') ? $_POST : $_GET;
$action = $input['action'] ?? null;
$actions = array('userEnable', 'userDisable', 'checkDisabled',
                 'refillSessionTime', 'refillSessionTraffic', 'userMail');
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
try {
    $maxUsernameLength = in_array($action, array('refillSessionTime', 'refillSessionTraffic'), true) ? 128 : 64;
    $usernames = dalo_user_action_names($input['username'] ?? array(), $maxUsernameLength);
} catch (InvalidArgumentException $error) {
    user_actions_response(false, 'Invalid or empty username selection.', 400);
}

try {
    $operator_perm_file = ($action === 'checkDisabled') ? 'mng_search' : 'mng_edit';
    $operator_perm_deny_http_status = 403;
    include_once('../check_operator_perm.php'); // Independent PDO authorization read.
    $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
    $label = count($usernames) > 1 ? 'users' : 'user';
    $namesLabel = implode(', ', $usernames);

    switch ($action) {
        case 'userEnable':
            dalo_user_action_toggle($pdo, $configValues, $usernames, $disabled_groupname, false);
            user_actions_response(true, sprintf('Enabled %s %s.', $label, $namesLabel));
            break;

        case 'userDisable':
            $new = dalo_user_action_toggle($pdo, $configValues, $usernames, $disabled_groupname, true);
            if (!$new) {
                user_actions_response(false, sprintf('%s %s already disabled.', $label, $namesLabel), 200);
            }
            user_actions_response(true, sprintf('Disabled %s %s.', $label, implode(', ', $new)));
            break;

        case 'checkDisabled':
            $disabled = dalo_user_action_disabled($pdo, $configValues, $usernames[0], $disabled_groupname);
            $message = $disabled
                ? sprintf('Please note that user %s is currently disabled. To enable this user, remove it from the %s profile.',
                          $usernames[0], $disabled_groupname)
                : '';
            user_actions_response(true, $message, 200, $disabled ? 'danger' : 'success', $disabled);
            break;

        case 'refillSessionTime':
        case 'refillSessionTraffic':
            dalo_user_action_refill($pdo, $configValues, $usernames, $action, $_SESSION['operator_user']);
            user_actions_response(true, sprintf('Session %s for %s %s has been successfully refilled (and billed).',
                                  $action === 'refillSessionTime' ? 'time' : 'traffic',
                                  $label, $namesLabel));
            break;

        case 'userMail':
            $recipients = dalo_user_action_mail_rows($pdo, $configValues, $usernames);
            $sent = 0;
            $failed = 0;
            foreach ($recipients as $recipient) {
                list($username, $password, $email, $firstname, $lastname) = $recipient;
                $body = sprintf(
                    '<b>VPN credential</b><br>Hello, %s %s!<br>Your login is: %s<br>Your password is: %s<br>VPN Server is: %s<br><br>Best regards, Admin',
                    $firstname, $lastname, $username, $password,
                    $configValues['CONFIG_USER_VPN_SERVER']);
                // External SMTP is not transactional; report aggregate partial failures.
                list($success) = send_email($configValues, $email, $username,
                                            'VPN Credentials', $body, array());
                $success ? $sent++ : $failed++;
            }
            if ($sent === 0 && $failed === 0) {
                user_actions_response(false, 'No email recipients found.', 200);
            }
            user_actions_response($sent > 0 && $failed === 0,
                                  sprintf('Emails sent: %d. Failed: %d.', $sent, $failed), 200);
            break;
    }
} catch (Throwable $error) {
    // Driver details (which can contain bound data) must never reach a response or log.
    user_actions_response(false, 'The action could not be completed. Check user and billing records before trying again.', 500);
}
