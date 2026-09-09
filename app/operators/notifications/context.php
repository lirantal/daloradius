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

        case 'user-invoice':
            return notification_build_user_invoice($configValues, $dbSocket, $params);
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

/**
 * Format a monetary value coming from a VARCHAR column.
 */
function notification_money($value) {
    return number_format(floatval($value), 2, '.', '');
}

/**
 * Pick the invoice HTML template (and optional per-item template).
 *
 * The default is a plain "user_invoice.html"; CONFIG_INVOICE_TEMPLATE /
 * CONFIG_INVOICE_ITEM_TEMPLATE (optionally overridden per location) select the
 * richer, repeat-per-item layout.
 *
 * @return array [string $templatePath, string|null $itemTemplatePath]
 */
function notification_invoice_templates($configValues) {
    $base = $configValues['OPERATORS_NOTIFICATIONS_TEMPLATES'] . DIRECTORY_SEPARATOR;

    $template = $base . 'user_invoice.html';
    $item_template = null;

    if (!empty($configValues['CONFIG_INVOICE_TEMPLATE'])) {
        $template = $base . basename($configValues['CONFIG_INVOICE_TEMPLATE']);
        if (!empty($configValues['CONFIG_INVOICE_ITEM_TEMPLATE'])) {
            $item_template = $base . basename($configValues['CONFIG_INVOICE_ITEM_TEMPLATE']);
        }
    }

    $location_name = $_SESSION['location_name'] ?? 'default';
    if ($location_name !== 'default' && isset($configValues['CONFIG_LOCATIONS'][$location_name])) {
        $location = $configValues['CONFIG_LOCATIONS'][$location_name];
        if (!empty($location['CONFIG_INVOICE_TEMPLATE'])) {
            $template = $base . basename($location['CONFIG_INVOICE_TEMPLATE']);
            $item_template = (!empty($location['CONFIG_INVOICE_ITEM_TEMPLATE']))
                           ? $base . basename($location['CONFIG_INVOICE_ITEM_TEMPLATE']) : null;
        }
    }

    return array($template, $item_template);
}

/**
 * "user-invoice" - a billing invoice for a single customer.
 */
function notification_build_user_invoice($configValues, $dbSocket, array $params) {
    $invoice_id = isset($params['invoice_id']) ? intval($params['invoice_id']) : 0;
    if ($invoice_id <= 0) {
        return false;
    }

    $sql = sprintf("SELECT a.id, a.date, a.user_id, a.notes,
                           b.contactperson, b.company, b.city, b.state, b.country, b.zip, b.address,
                           b.email, b.emailinvoice, b.phone,
                           f.value AS type, c.value AS status,
                           COALESCE(e2.totalpayed, 0) AS totalpayed,
                           COALESCE(d2.totalbilled, 0) AS totalbilled
                      FROM %s AS a INNER JOIN %s AS b ON a.user_id = b.id
                                   INNER JOIN %s AS c ON a.status_id = c.id
                                   INNER JOIN %s AS f ON a.type_id = f.id
                                   LEFT JOIN (
                                       SELECT invoice_id, SUM(amount + tax_amount) AS totalbilled
                                         FROM %s GROUP BY invoice_id
                                   ) AS d2 ON d2.invoice_id = a.id
                                   LEFT JOIN (
                                       SELECT invoice_id, SUM(amount) AS totalpayed
                                         FROM %s GROUP BY invoice_id
                                   ) AS e2 ON e2.invoice_id = a.id
                     WHERE a.id = %d
                     GROUP BY a.id",
                   $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'],
                   $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                   $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS'],
                   $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICETYPE'],
                   $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'],
                   $configValues['CONFIG_DB_TBL_DALOPAYMENTS'],
                   $invoice_id);

    $res = $dbSocket->query($sql);
    $invoice = (!DB::isError($res)) ? $res->fetchRow(DB_FETCHMODE_ASSOC) : null;
    if (!is_array($invoice)) {
        return false;
    }

    $get = function ($key) use ($invoice) {
        return isset($invoice[$key]) ? trim((string) $invoice[$key]) : '';
    };

    $customer_email = $get('email') !== '' ? $get('email') : $get('emailinvoice');
    $balance = floatval($invoice['totalpayed']) - floatval($invoice['totalbilled']);

    // legacy "####__X__####" detail block
    $details = array(
        array(t('all', 'ClientName'),   $get('contactperson')),
        array(t('all', 'Invoice'),      $invoice_id),
        array(t('all', 'Date'),         $get('date')),
        array(t('all', 'TotalBilled'),  notification_money($invoice['totalbilled'])),
        array(t('all', 'TotalPayed'),   notification_money($invoice['totalpayed'])),
        array(t('all', 'Balance'),      notification_money($balance)),
        array(t('all', 'Status'),       $get('status')),
        array(t('ContactInfo', 'Notes'), $get('notes')),
    );
    $invoice_details = '';
    foreach ($details as $detail) {
        $invoice_details .= sprintf('<b>%s</b>: %s<br>',
                                    notification_escape($detail[0]), notification_escape($detail[1]));
    }

    // invoice line items
    $sql = sprintf("SELECT i.amount, i.tax_amount, i.notes, p.planName
                      FROM %s AS i LEFT JOIN %s AS p ON i.plan_id = p.id
                     WHERE i.invoice_id = %d ORDER BY i.id ASC",
                   $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'],
                   $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'], $invoice_id);
    $res = $dbSocket->query($sql);

    $items = array();
    $total_amount = 0.0;
    $total_tax = 0.0;
    $number = 1;
    if (!DB::isError($res)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $amount = floatval($row['amount']);
            $tax = floatval($row['tax_amount']);
            $items[] = array(
                'number'      => sprintf('%02d', $number++),
                'plan'        => (string) $row['planName'],
                'notes'       => (string) $row['notes'],
                'amount'      => notification_money($amount),
                'tax_amount'  => notification_money($tax),
                'total_amount' => notification_money($amount + $tax),
            );
            $total_amount += $amount;
            $total_tax += $tax;
        }
    }

    $items_table = '<table class="grid"><thead><tr>'
                 . '<th>' . notification_escape(t('title', 'Plan')) . '</th>'
                 . '<th>' . notification_escape(t('all', 'Tax')) . '</th>'
                 . '<th>' . notification_escape(t('all', 'Amount')) . '</th>'
                 . '<th>' . notification_escape(t('ContactInfo', 'Notes')) . '</th>'
                 . '</tr></thead><tbody>';
    foreach ($items as $item) {
        $items_table .= '<tr><td>' . notification_escape($item['plan']) . '</td>'
                      . '<td>' . notification_escape($item['tax_amount']) . '</td>'
                      . '<td>' . notification_escape($item['amount']) . '</td>'
                      . '<td>' . notification_escape($item['notes']) . '</td></tr>';
    }
    $items_table .= '</tbody></table>';

    list($template_path, $item_template_path) = notification_invoice_templates($configValues);
    $html = notification_load_template($template_path);
    if ($html === false) {
        return false;
    }

    $customer_name = ($get('company') !== '') ? $get('company') : $get('contactperson');
    $address2 = trim(implode(' ', array_filter(array($get('zip'), $get('city'), $get('state'), $get('country')))));

    $replacements = array(
        // legacy tokens
        '####__INVOICE_CREATION_DATE__####' => notification_escape(date('Y-m-d')),
        '####__CUSTOMER_NAME__####'         => notification_escape($get('contactperson')),
        '####__CUSTOMER_ADDRESS__####'      => notification_escape(trim($get('address') . ' ' . $get('city') . ' ' . $get('state'))),
        '####__CUSTOMER_PHONE__####'        => notification_escape($get('phone')),
        '####__CUSTOMER_EMAIL__####'        => notification_escape($customer_email),
        '####__INVOICE_DETAILS__####'       => $invoice_details,
        '####__INVOICE_ITEMS__####'         => $items_table,
        // bracket tokens
        '[CustomerId]'        => notification_escape($get('user_id')),
        '[CustomerName]'      => notification_escape($customer_name),
        '[CustomerAddress]'   => notification_escape($get('address')),
        '[CustomerAddress2]'  => notification_escape($address2),
        '[CustomerPhone]'     => notification_escape($get('phone')),
        '[CustomerEmail]'     => notification_escape($get('email')),
        '[CustomerContact]'   => notification_escape($get('contactperson')),
        '[InvoiceNumber]'     => notification_escape($invoice_id),
        '[InvoiceDate]'       => notification_escape($get('date') !== '' ? date('Y-m-d', strtotime($get('date'))) : ''),
        '[InvoiceStatus]'     => notification_escape(strtoupper($get('status'))),
        '[InvoiceTotalBilled]' => notification_escape(notification_money($invoice['totalbilled'])),
        '[InvoicePaid]'       => notification_escape(notification_money($invoice['totalpayed'])),
        '[InvoiceDue]'        => notification_escape(notification_money($balance)),
        '[InvoiceNotes]'      => notification_escape($get('notes')),
        '[InvoiceTotalAmount]' => notification_escape(notification_money($total_amount)),
        '[InvoiceTotalTax]'   => notification_escape(notification_money($total_tax)),
    );

    // repeat the per-item template for [InvoiceItems]
    if ($item_template_path !== null) {
        $item_html = notification_load_template($item_template_path);
        $rendered_items = '';
        if ($item_html !== false) {
            foreach ($items as $item) {
                $rendered_items .= notification_fill($item_html, array(
                    '[InvoiceItemNumber]'      => notification_escape($item['number']),
                    '[InvoiceItemPlan]'        => notification_escape($item['plan']),
                    '[InvoiceItemNotes]'       => notification_escape($item['notes']),
                    '[InvoiceItemAmount]'      => notification_escape($item['amount']),
                    '[InvoiceItemTaxAmount]'   => notification_escape($item['tax_amount']),
                    '[InvoiceItemTotalAmount]' => notification_escape($item['total_amount']),
                ));
            }
        }
        $replacements['[InvoiceItems]'] = $rendered_items;
    }

    $html = notification_fill($html, $replacements);

    return array(
        'html'            => $html,
        'filename'        => sprintf('daloradius-invoice-%d-%s.pdf', $invoice_id, date('Ymd')),
        'recipient_name'  => $customer_name,
        'recipient_email' => $customer_email,
        'subject'         => sprintf('Invoice #%d', $invoice_id),
        'body'            => sprintf('Dear customer,<br><br>please find attached invoice #%d.', $invoice_id),
    );
}
