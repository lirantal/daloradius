<?php
/*
 * Standalone checks for the operator PDF notification rendering layer.
 *
 * Run with:  php tests/notifications-render.test.php
 *
 * No database or SMTP is touched: only the pure template helpers in
 * app/operators/notifications/render.php and the shipped templates.
 */

$root = dirname(__DIR__);
$_SERVER['PHP_SELF'] = '/cli/tests';

require $root . '/app/operators/notifications/render.php';

$templates = $root . '/app/operators/notifications/templates';
$failures = 0;

function check($label, $condition) {
    global $failures;
    if ($condition) {
        printf("ok   - %s\n", $label);
    } else {
        printf("FAIL - %s\n", $label);
        $failures++;
    }
}

// notification_load_template
check('missing template returns false', notification_load_template($templates . '/nope.html') === false);
check('existing template loads', is_string(notification_load_template($templates . '/user-welcome.html')));

// welcome: every "####__X__####" wrapper must be consumed
$welcome = notification_fill(
    notification_load_template($templates . '/user-welcome.html'),
    array(
        '####__STYLE__####'                 => notification_stylesheet(),
        '####__INVOICE_CREATION_DATE__####' => '2026-09-09',
        '####__CUSTOMER_NAME__####'         => 'Mario Rossi',
        '####__CUSTOMER_ADDRESS__####'      => 'Via Roma 1',
        '####__CUSTOMER_PHONE__####'        => '+39 050 1',
        '####__CUSTOMER_EMAIL__####'        => 'mario@example.org',
    )
);
check('welcome: no #### markers left', strpos($welcome, '####') === false);
check('welcome: value substituted', strpos($welcome, 'Mario Rossi') !== false);

// batch-details: same, plus the __BUSINESS_WEB__ token that used to be missed
$batch = notification_fill(
    notification_load_template($templates . '/batch-details.html'),
    array(
        '####__STYLE__####'                 => notification_stylesheet(),
        '####__INVOICE_CREATION_DATE__####' => '2026-09-09',
        '####__BUSINESS_NAME__####'         => 'ACME',
        '####__BUSINESS_OWNER_NAME__####'   => 'Jane',
        '####__BUSINESS_ADDRESS__####'      => 'Road 1',
        '####__BUSINESS_PHONE__####'        => '1',
        '####__BUSINESS_EMAIL__####'        => 'a@b.c',
        '####__BUSINESS_WEB__####'          => 'https://acme.example',
        '####__SERVICE_PLAN_INFO__####'     => '<table></table>',
        '####__BATCH_DETAILS__####'         => '<table></table>',
        '####__BATCH_ACTIVE_USERS__####'    => '<table></table>',
    )
);
check('batch: no #### markers left', strpos($batch, '####') === false);
check('batch: __BUSINESS_WEB__ resolved', strpos($batch, 'https://acme.example') !== false);
check('shared stylesheet is injected', strpos($welcome, 'table.grid thead th') !== false);

// invoice: bracket tokens + repeated item template
$item = notification_fill(
    notification_load_template($templates . '/invoice_item_template.html'),
    array(
        '[InvoiceItemNumber]'      => '01',
        '[InvoiceItemPlan]'        => 'Gold',
        '[InvoiceItemNotes]'       => '-',
        '[InvoiceItemAmount]'      => '10.00',
        '[InvoiceItemTaxAmount]'   => '2.20',
        '[InvoiceItemTotalAmount]' => '12.20',
    )
);
$invoice = notification_fill(
    notification_load_template($templates . '/invoice_template.html'),
    array(
        '####__STYLE__####' => notification_stylesheet(),
        '[CustomerId]' => '5', '[CustomerName]' => 'ACME', '[CustomerContact]' => 'Jane',
        '[CustomerAddress]' => 'Road 1', '[CustomerAddress2]' => 'Town',
        '[CustomerPhone]' => '1', '[CustomerEmail]' => 'a@b.c',
        '[InvoiceNumber]' => '42', '[InvoiceDate]' => '2026-09-09', '[InvoiceStatus]' => 'OPEN',
        '[InvoiceTotalBilled]' => '12.20', '[InvoicePaid]' => '0.00', '[InvoiceDue]' => '-12.20',
        '[InvoiceNotes]' => 'n', '[InvoiceTotalAmount]' => '10.00', '[InvoiceTotalTax]' => '2.20',
        '[InvoiceItems]' => $item,
    )
);
check('invoice: no [Token] placeholders left',
      preg_match('/\[(Customer|Invoice)[A-Za-z]*\]/', $invoice) === 0);
check('invoice: item row rendered', substr_count($invoice, 'Gold') === 1);
check('invoice: decimals preserved', strpos($invoice, '12.20') !== false);

printf("\n%s\n", $failures === 0 ? 'ALL PASSED' : sprintf('%d FAILURE(S)', $failures));
exit($failures === 0 ? 0 : 1);
