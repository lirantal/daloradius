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
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
session_cache_limiter('');
session_start();
if (!isset($_SESSION['portal1_paypal_csrf']) || !is_string($_SESSION['portal1_paypal_csrf'])) {
    $_SESSION['portal1_paypal_csrf'] = bin2hex(random_bytes(32));
}
function portal1_paypal_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
$plans = array(); $registered = null; $failureMsg = ''; $pdo = null;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
try {
    if (!in_array($method, array('GET', 'POST'), true)) { http_response_code(405); throw new DomainException('Unsupported method'); }
    if ($method === 'POST') {
        $csrf = $_POST['csrf_token'] ?? null;
        if (!is_string($_POST['submit'] ?? null) || !is_string($csrf) || !hash_equals($_SESSION['portal1_paypal_csrf'], $csrf)) {
            http_response_code(403); throw new DomainException('Invalid registration request');
        }
        // A successful/rejected submitted form cannot be replayed with its old token.
        $_SESSION['portal1_paypal_csrf'] = bin2hex(random_bytes(32));
    }
    $pdo = dalo_chilli_pdo_open($configValues);
    $plans = dalo_portal1_paypal_plans($pdo, $configValues);
    if ($method === 'POST') { $registered = dalo_portal1_paypal_register($pdo, $configValues, $_POST); }
} catch (DomainException $error) { $failureMsg = 'Registration request rejected'; }
catch (InvalidArgumentException $error) { http_response_code(400); $failureMsg = 'Missing or invalid registration fields'; }
catch (Throwable $error) { http_response_code(503); $failureMsg = 'Registration could not be completed'; }
finally { if ($pdo instanceof PDO) { dalo_chilli_database_close($pdo); } }
?>
<!doctype html>
<html><head><meta charset="UTF-8"><title>Online Registration</title></head><body>
<form name="newuser" action="index.php" method="post">
<input type="hidden" name="csrf_token" value="<?= portal1_paypal_html($_SESSION['portal1_paypal_csrf']) ?>">
Select your plan:<br>
<select id="planId" name="planId">
<?php foreach ($plans as $plan): ?>
<option value="<?= portal1_paypal_html($plan['planId']) ?>"><?= portal1_paypal_html($plan['planName']) ?> - Cost <?= portal1_paypal_html($plan['planCost']) ?> <?= portal1_paypal_html($plan['planCurrency']) ?> </option>
<?php endforeach; ?>
</select><br><br><b>Personal Details:</b><br><br>
<?php foreach (array('firstName'=>'First Name','lastName'=>'Last Name','address'=>'Address','city'=>'City','state'=>'State') as $field=>$label): ?>
<?= $label ?><br>
<input name="<?= $field ?>" value="<?= portal1_paypal_html(is_string($_POST[$field] ?? null) ? $_POST[$field] : '') ?>"><br>
<?php endforeach; ?>
<br><input type="submit" value="submit" name="submit">
</form>
<?php if ($failureMsg !== ''): ?><p role="alert"><?= portal1_paypal_html($failureMsg) ?></p><?php endif; ?>
<?php if ($registered !== null):
    $plan = $registered['plan']; $checkout = $registered['checkout'];
    $fields = array('cmd'=>'_xclick', 'business'=>$checkout['business'],
        'return'=>$checkout['base'].'/success.php?txnId='.rawurlencode($registered['correlation']),
        'cancel_return'=>$checkout['base'].'/index.php', 'notify_url'=>$checkout['base'].'/paypal-ipn.php',
        'amount'=>$plan['planCost'], 'item_name'=>$plan['planName'], 'quantity'=>'1',
        'tax'=>($plan['planTax'] ?? '') === '' ? '0' : $plan['planTax'], 'item_number'=>$plan['planId'],
        'no_note'=>'1', 'currency_code'=>$plan['planCurrency'], 'lc'=>'US',
        'on0'=>'Transaction ID', 'os0'=>$registered['correlation']);
?>
<p>Thank you... this is your user PIN: <b><?= portal1_paypal_html($registered['pin']) ?></b></p>
<p>Please write it down, you will need to enter it at the login page.</p>
<p>Your account has been created but it will only be active after you complete your payment through PayPal.</p>
<form action="<?= portal1_paypal_html($checkout['action']) ?>" method="post">
<?php foreach ($fields as $name=>$value): ?>
<input type="hidden" name="<?= $name ?>" value="<?= portal1_paypal_html($value) ?>">
<?php endforeach; ?>
<button type="submit">Buy Now</button>
</form>
<?php endif; ?>
</body></html>
