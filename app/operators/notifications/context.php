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
 * Description:    Builds the rendered HTML (and delivery metadata) for each type
 *                 of operator PDF notification.
 *
 * Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/notifications/context.php') !== false) {
    http_response_code(404);
    exit;
}

/**
 * Build a notification document.
 *
 * @param string $type         one of the supported notification types
 * @param array  $configValues
 * @param object $dbSocket      open PEAR DB connection
 * @param array  $params        request parameters (GET merged over session)
 *
 * @return array|false On success an array with keys:
 *                     html, filename, recipient_name, recipient_email, subject, body.
 *                     false when the notification cannot be built.
 */
function notification_build($type, $configValues, $dbSocket, array $params) {
    switch ($type) {
        case 'user-welcome':
            return notification_build_user_welcome($configValues, $dbSocket, $params);
    }

    return false;
}

/**
 * Sanitise a string so it can be used inside a download filename.
 */
function notification_filename_slug($value) {
    $value = preg_replace('/[^A-Za-z0-9._-]+/', '_', (string) $value);
    return trim($value, '_') ?: 'daloradius';
}

/**
 * "user-welcome" - a welcome letter for a freshly created user.
 */
function notification_build_user_welcome($configValues, $dbSocket, array $params) {
    $username = isset($params['username']) ? str_replace('%', '', trim((string) $params['username'])) : '';
    if ($username === '') {
        return false;
    }

    $sql = sprintf("SELECT firstname, lastname, email, address, city, state, zip,
                           mobilephone, workphone, homephone
                      FROM %s WHERE username = '%s' LIMIT 1",
                   $configValues['CONFIG_DB_TBL_DALOUSERINFO'],
                   $dbSocket->escapeSimple($username));

    $res = $dbSocket->query($sql);
    $row = (!DB::isError($res)) ? $res->fetchRow(DB_FETCHMODE_ASSOC) : null;
    if (!is_array($row)) {
        $row = array();
    }

    $field = function ($key) use ($row) {
        return isset($row[$key]) ? trim((string) $row[$key]) : '';
    };

    $name  = trim($field('firstname') . ' ' . $field('lastname'));
    $name  = ($name !== '') ? $name : $username;
    $email = $field('email');
    $phone = $field('mobilephone') ?: $field('workphone') ?: $field('homephone');

    $address = implode(', ', array_filter(array(
        $field('address'), $field('city'), $field('state'), $field('zip'),
    )));

    $template = implode(DIRECTORY_SEPARATOR,
                        array($configValues['OPERATORS_NOTIFICATIONS_TEMPLATES'], 'user-welcome.html'));
    $html = notification_load_template($template);
    if ($html === false) {
        return false;
    }

    $html = notification_fill($html, array(
        '####__INVOICE_CREATION_DATE__####' => notification_escape(date('Y-m-d')),
        '####__CUSTOMER_NAME__####'         => notification_escape($name),
        '####__CUSTOMER_ADDRESS__####'      => notification_escape($address !== '' ? $address : '(n/a)'),
        '####__CUSTOMER_PHONE__####'        => notification_escape($phone !== '' ? $phone : '(n/a)'),
        '####__CUSTOMER_EMAIL__####'        => notification_escape($email !== '' ? $email : '(n/a)'),
    ));

    return array(
        'html'            => $html,
        'filename'        => sprintf('daloradius-welcome-%s-%s.pdf',
                                     notification_filename_slug($username), date('Ymd')),
        'recipient_name'  => $name,
        'recipient_email' => $email,
        'subject'         => 'Welcome notification',
        'body'            => 'Dear customer,<br><br>please find attached your welcome notification.',
    );
}
