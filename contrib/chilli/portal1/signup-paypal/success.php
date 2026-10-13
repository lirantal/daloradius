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
 * Authors:     Liran Tal <liran@lirantal.com>
 *
 *********************************************************************************************************
 */

require_once __DIR__ . '/library/config_read.php';
require_once dirname(__DIR__, 2) . '/common/portal1Paypal.php';
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');
$successMsg = $configValues['CONFIG_PAYPAL_SUCCESS_MSG_PRE'] ?? 'Waiting for payment confirmation';
$refresh = true; $pdo = null;
try {
    if (array_key_exists('txnId', $_GET)) {
        dalo_paypal_text($_GET['txnId'], 200, true);
        $pdo = dalo_chilli_pdo_open($configValues);
        $receipt = dalo_portal1_paypal_receipt($pdo, $configValues, $_GET['txnId']);
        if ($receipt !== null && $receipt['payment_status'] === 'Completed') {
            $successMsg = 'Your user PIN is: <b>' . htmlspecialchars((string) $receipt['username'], ENT_QUOTES, 'UTF-8') .
                '</b> <br/>' . ($configValues['CONFIG_PAYPAL_SUCCESS_MSG_POST'] ?? 'Payment confirmed');
            $refresh = false;
        }
    }
} catch (InvalidArgumentException $error) {
    http_response_code(400); $refresh = false; $successMsg = 'Payment receipt unavailable';
} catch (Throwable $error) {
    http_response_code(503); $refresh = false; $successMsg = 'Payment receipt unavailable';
} finally { if ($pdo instanceof PDO) { dalo_chilli_database_close($pdo); } }
?>
<html><head><meta charset="UTF-8">
<?php if ($refresh): ?><meta http-equiv="refresh" content="5"><?php endif; ?>
</head><body>
<?php
// Administrator-configured message markup is retained; the DB PIN is escaped.
echo $configValues['CONFIG_PAYPAL_SUCCESS_MSG_HEADER'] ?? '';
echo $successMsg;
?>
</body></html>
