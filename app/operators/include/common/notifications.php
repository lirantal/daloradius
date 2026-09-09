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

// supported notification types mapped to the ACL entry that guards them
$notification_types = array(
    'user-welcome' => 'mng_new',
);

$session_params = (isset($_SESSION['notification']) && is_array($_SESSION['notification']))
                ? $_SESSION['notification'] : array();

// the type may come from the query string or from the session payload
$type = (string) ($_GET['type'] ?? ($session_params['type'] ?? ''));
if (!array_key_exists($type, $notification_types)) {
    header("Location: $redirect");
    exit;
}

// preview streams an inline PDF, download forces an attachment, email mails it
$allowed_actions = array('preview', 'download', 'email');
$action = strtolower((string) ($_GET['action'] ?? $_GET['destination'] ?? 'preview'));
if (!in_array($action, $allowed_actions, true)) {
    $action = 'preview';
}

// this helper endpoint reuses the permission of the page that owns the feature
$operator_perm_file = $notification_types[$type];
$operator_perm_deny_http_status = 403;
include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);

include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_NOTIFICATIONS'], 'render.php' ]);
include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_NOTIFICATIONS'], 'context.php' ]);

// query-string parameters win over the session payload
$params = array_merge($session_params, $_GET);

include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);
$notification = notification_build($type, $configValues, $dbSocket, $params);
include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);

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
