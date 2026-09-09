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

        case 'batch-details':
            return notification_build_batch_details($configValues, $dbSocket, $params);
    }

    return false;
}

/**
 * Render a simple HTML table from a header list and a list of row arrays.
 */
function notification_html_table(array $headers, array $rows) {
    $html = '<table class="grid"><thead><tr>';
    foreach ($headers as $header) {
        $html .= '<th>' . notification_escape($header) . '</th>';
    }
    $html .= '</tr></thead><tbody>';

    foreach ($rows as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= '<td>' . notification_escape($cell) . '</td>';
        }
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';
    return $html;
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

/**
 * "batch-details" - a summary sheet for a pre-paid user batch and its hotspot.
 */
function notification_build_batch_details($configValues, $dbSocket, array $params) {
    $batch_name = isset($params['batch_name']) ? str_replace('%', '', trim((string) $params['batch_name'])) : '';
    if ($batch_name === '') {
        return false;
    }

    $escaped = $dbSocket->escapeSimple($batch_name);

    // batch overview (one row)
    $sql = sprintf("SELECT dbh.id AS batch_id, dbh.batch_name, dbh.batch_status,
                           COUNT(DISTINCT ubi.id) AS total_users,
                           COUNT(DISTINCT ra.username) AS active_users,
                           ubi.planName AS plan_name, dbp.planCost AS plan_cost,
                           dhs.name AS hotspot_name, dbh.creationdate, dbh.creationby
                      FROM %s AS dbh LEFT JOIN %s AS ubi ON dbh.id = ubi.batch_id
                                    LEFT JOIN %s AS dbp ON dbp.planName = ubi.planName
                                    LEFT JOIN %s AS dhs ON dbh.hotspot_id = dhs.id
                                    LEFT JOIN %s AS ra  ON ra.username = ubi.username
                     WHERE dbh.batch_name = '%s'
                     GROUP BY dbh.id",
                   $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'],
                   $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                   $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                   $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'],
                   $configValues['CONFIG_DB_TBL_RADACCT'],
                   $escaped);

    $res = $dbSocket->query($sql);
    $batch = (!DB::isError($res)) ? $res->fetchRow(DB_FETCHMODE_ASSOC) : null;
    if (!is_array($batch)) {
        return false;
    }

    $batch_id     = intval($batch['batch_id']);
    $plan_name    = trim((string) $batch['plan_name']);
    $hotspot_name = trim((string) $batch['hotspot_name']);
    $batch_cost   = intval($batch['active_users']) * floatval($batch['plan_cost']);

    $details_table = notification_html_table(
        array(t('all', 'BatchName'), t('all', 'BatchStatus'), t('all', 'TotalUsers'),
              t('all', 'ActiveUsers'), t('all', 'PlanName'), t('all', 'PlanCost'),
              t('all', 'BatchCost'), t('all', 'CreationDate'), t('all', 'CreationBy')),
        array(array($batch['batch_name'], $batch['batch_status'], $batch['total_users'],
                    $batch['active_users'], $plan_name, $batch['plan_cost'], $batch_cost,
                    $batch['creationdate'], $batch['creationby']))
    );

    // service plan detail
    $plan_table = '';
    if ($plan_name !== '') {
        $sql = sprintf("SELECT planName, planRecurringPeriod, planCost, planSetupCost, planTax, planCurrency
                          FROM %s WHERE planName = '%s' LIMIT 1",
                       $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'], $dbSocket->escapeSimple($plan_name));
        $res = $dbSocket->query($sql);
        $plan = (!DB::isError($res)) ? $res->fetchRow(DB_FETCHMODE_ASSOC) : null;
        if (is_array($plan)) {
            $rows = array();
            foreach ($plan as $key => $value) {
                $rows[] = array($key, $value);
            }
            $plan_table = notification_html_table(array(t('all', 'Attribute'), t('all', 'Value')), $rows);
        }
    }

    // business / hotspot contact
    $business = array('name' => '', 'owner' => '', 'address' => '', 'companyphone' => '',
                      'companyemail' => '', 'companywebsite' => '');
    if ($hotspot_name !== '') {
        $sql = sprintf("SELECT name, owner, address, companyphone, companyemail, companywebsite
                          FROM %s WHERE name = '%s' LIMIT 1",
                       $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'], $dbSocket->escapeSimple($hotspot_name));
        $res = $dbSocket->query($sql);
        $row = (!DB::isError($res)) ? $res->fetchRow(DB_FETCHMODE_ASSOC) : null;
        if (is_array($row)) {
            $business = array_merge($business, $row);
        }
    }

    // active users of this batch
    $sql = sprintf("SELECT ubi.username, MIN(ra.acctstarttime) AS acctstarttime
                      FROM %s AS ubi INNER JOIN %s AS ra ON ra.username = ubi.username
                     WHERE ubi.batch_id = %d
                     GROUP BY ubi.username
                     ORDER BY ubi.username ASC",
                   $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                   $configValues['CONFIG_DB_TBL_RADACCT'], $batch_id);
    $res = $dbSocket->query($sql);
    $active_rows = array();
    if (!DB::isError($res)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $active_rows[] = array($row['username'], $row['acctstarttime']);
        }
    }
    $active_users_table = notification_html_table(
        array(t('all', 'Username'), t('all', 'StartTime')), $active_rows);

    $template = implode(DIRECTORY_SEPARATOR,
                        array($configValues['OPERATORS_NOTIFICATIONS_TEMPLATES'], 'batch-details.html'));
    $html = notification_load_template($template);
    if ($html === false) {
        return false;
    }

    $html = notification_fill($html, array(
        '####__INVOICE_CREATION_DATE__####' => notification_escape(date('Y-m-d')),
        '####__BUSINESS_NAME__####'         => notification_escape($business['name']),
        '####__BUSINESS_OWNER_NAME__####'   => notification_escape($business['owner']),
        '####__BUSINESS_ADDRESS__####'      => notification_escape($business['address']),
        '####__BUSINESS_PHONE__####'        => notification_escape($business['companyphone']),
        '####__BUSINESS_EMAIL__####'        => notification_escape($business['companyemail']),
        '####__BUSINESS_WEB__####'          => notification_escape($business['companywebsite']),
        '####__SERVICE_PLAN_INFO__####'     => $plan_table,
        '####__BATCH_DETAILS__####'         => $details_table,
        '####__BATCH_ACTIVE_USERS__####'    => $active_users_table,
    ));

    return array(
        'html'            => $html,
        'filename'        => sprintf('daloradius-batch-%s-%s.pdf',
                                     notification_filename_slug($batch_name), date('Ymd')),
        'recipient_name'  => (string) ($business['name'] ?: $business['owner']),
        'recipient_email' => (string) $business['companyemail'],
        'subject'         => sprintf('Batch details - %s', $batch_name),
        'body'            => sprintf('Please find attached the details for batch "%s".',
                                     notification_escape($batch_name)),
    );
}
