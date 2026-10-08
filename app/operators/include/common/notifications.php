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
 * Description:    Single entry point for the operator PDF notifications. It takes a
 *                 notification "type" plus an "action" (preview | download | email)
 *                 and streams / mails the resulting PDF.
 *
 * Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', '..', '..', 'common', 'includes', 'config_read.php' ]);
include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);
$operator = $_SESSION['operator_user'];

include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);

$redirect = (!empty(trim($_SESSION['PREV_LIST_PAGE'] ?? "")))
          ? trim($_SESSION['PREV_LIST_PAGE']) : "../../index.php";

// supported notification types mapped to the ACL entries that guard them:
// access to any one of the listed owning pages is enough
$notification_types = array(
    'user-welcome'  => array('mng_new', 'bill_pos_new'),
    'user-invoice'  => array('bill_invoice_edit'),
    'batch-details' => array('rep_batch_details'),
);

$session_params = (isset($_SESSION['notification']) && is_array($_SESSION['notification']))
                ? $_SESSION['notification'] : array();

// the type may come from the query string or from the session payload
$type = $_GET['type'] ?? ($session_params['type'] ?? '');
if (!is_string($type)) { http_response_code(400); exit('Invalid notification type.'); }
if (!array_key_exists($type, $notification_types)) {
    header("Location: $redirect");
    exit;
}

// preview streams an inline PDF, download forces an attachment, email mails it
$allowed_actions = array('preview', 'download', 'email');
$action = $_GET['action'] ?? $_GET['destination'] ?? 'preview';
if (!is_string($action)) { http_response_code(400); exit('Invalid notification action.'); }
$action = strtolower($action);
if (!in_array($action, $allowed_actions, true)) {
    $action = 'preview';
}

include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_NOTIFICATIONS'], 'render.php' ]);
include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_NOTIFICATIONS'], 'context.php' ]);

// query-string parameters win over the session payload
$params = array_merge($session_params, $_GET);

require_once $configValues['OPERATORS_LIBRARY'] . '/operator_acl_read.php';
$notification_pdo = null;
try {
    $notification_pdo = dalo_shared_handle($configValues);
    $operator_id = $_SESSION['operator_id'] ?? null;
    $has_access = false;
    foreach ($notification_types[$type] as $acl_file) {
        if (dalo_operator_acl_allowed($notification_pdo, $configValues, $operator_id, $acl_file)) {
            $has_access = true;
            break;
        }
    }
    if (!$has_access) { http_response_code(403); exit; }
    $notification = notification_build($type, $configValues, $notification_pdo, $params);
} catch (InvalidArgumentException $error) {
    http_response_code(400);
    exit('Invalid notification request.');
} catch (Throwable $error) {
    error_log('Notification context failed: ' . get_class($error));
    http_response_code(503);
    exit('Unable to load notification data.');
} finally {
    $notification_pdo = null;
}

if (!is_array($notification) || empty($notification['html'])) {
    header("Location: $redirect");
    exit;
}

include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'pdf.php' ]);

$pdf = create_pdf($notification['html'], $configValues['OPERATORS_NOTIFICATIONS_TEMPLATES'], 'portrait');
$filename = $notification['filename'] ?? sprintf('daloradius-%s-%s.pdf', $type, date('Ymd'));

$back_link = sprintf(' <a href="%s">Go back</a>.', notification_escape($redirect));

switch ($action) {

    case 'download':
        header('Content-Type: application/pdf');
        header(sprintf('Content-Disposition: attachment; filename="%s"; size=%d', $filename, strlen($pdf)));
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('X-Content-Type-Options: nosniff');
        print $pdf;
        break;

    case 'email':
        if (strtolower($configValues['CONFIG_MAIL_ENABLED'] ?? 'no') !== 'yes') {
            print 'E-mail delivery is disabled in the configuration.' . $back_link;
            break;
        }

        if (empty($notification['recipient_email'])) {
            print 'No recipient e-mail address is available for this notification.' . $back_link;
            break;
        }

        include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'mail.php' ]);

        list($success, $message) = send_email(
            $configValues,
            $notification['recipient_email'],
            $notification['recipient_name'] ?? '',
            $notification['subject'] ?? 'daloRADIUS notification',
            $notification['body'] ?? '',
            array('content' => $pdf, 'filename' => $filename, 'type' => 'application/pdf')
        );

        printf('%s%s', notification_escape($message), $back_link);
        break;

    case 'preview':
    default:
        header('Content-Type: application/pdf');
        header(sprintf('Content-Disposition: inline; filename="%s"', $filename));
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('X-Content-Type-Options: nosniff');
        print $pdf;
        break;
}
