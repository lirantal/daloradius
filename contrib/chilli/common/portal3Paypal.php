<?php
/* daloRADIUS — GPL-2.0-or-later. Portal3 pending PayPal signup. */
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { http_response_code(404); exit; }
// Shared checked reads/capacities/inserts and merchant URLs, not Portal2 billing policy.
require_once __DIR__.'/portal2Paypal.php';

function dalo_portal3_paypal_table($config,$key) {
    if (!in_array($key,array('plans','info','orders','check','reply','mapping'),true)) {
        throw new InvalidArgumentException('Invalid registration source');
    }
    return $key==='orders' ? dalo_portal1_paypal_table($config,'CONFIG_DB_TBL_DALOBILLINGPAYPAL') :
        dalo_portal2_paypal_table($config,$key);
}

function dalo_portal3_paypal_plans(PDO $pdo,$config) {
    $table=dalo_portal3_paypal_table($config,'plans');
    return dalo_portal1_paypal_rows($pdo,"SELECT planId,planName,planCost,planTax,planCurrency FROM $table WHERE planType='PayPal'");
}

function dalo_portal3_paypal_checkout($config) {
    $checkout=dalo_portal2_paypal_checkout($config);
    $business=$config['CONFIG_MERCHANT_BUSINESS_ID'] ?? $checkout['business'];
    try {dalo_paypal_text($business,200,true);}
    catch (InvalidArgumentException $error) {throw new RuntimeException('Checkout receiver unavailable');}
    // Preserve Portal3's configured destination. It must identify the callback's receiver.
    $emailAlias=($config['CONFIG_PAYPAL_RECEIVER_ID'] ?? '')!=='' &&
        $business===($config['CONFIG_PAYPAL_RECEIVER_EMAIL'] ?? '');
    if ($business!==$checkout['business'] && !$emailAlias) {throw new RuntimeException('Checkout receiver mismatch');}
    $checkout['business']=$business;
    return $checkout;
}

function dalo_portal3_paypal_fields($registration) {
    $plan=$registration['plan'];$checkout=$registration['checkout'];
    return array('cmd'=>'_xclick','business'=>$checkout['business'],
        'return'=>$checkout['return'].'?txnId='.rawurlencode($registration['correlation']),
        'cancel_return'=>$checkout['cancel_return'],'notify_url'=>$checkout['notify_url'],
        'amount'=>$plan['planCost'],'tax'=>($plan['planTax'] ?? '')===''?'0':$plan['planTax'],
        'item_name'=>$plan['planName'],'item_number'=>$plan['planId'],'quantity'=>'1',
        'no_note'=>'1','currency_code'=>$plan['planCurrency'],'lc'=>'US',
        'on0'=>'Transaction ID','os0'=>$registration['correlation']);
}

/** Own one checked transaction on the supplied connection; refuse borrowed transactions. */
function dalo_portal3_paypal_register(PDO $pdo, $config, $input) {
    if ($pdo->inTransaction()) { throw new LogicException('Registration requires an owned transaction'); }
    foreach (array('firstName', 'lastName', 'address', 'city', 'state', 'planId') as $field) {
        if (!array_key_exists($field, $input)) { throw new InvalidArgumentException('Missing registration field'); }
        dalo_paypal_text($input[$field], $field === 'planId' ? 128 : 200, true);
    }
    $checkout = dalo_portal3_paypal_checkout($config);
    $tables = array();
    foreach (array('plans','info','orders','check','reply','mapping') as $key) {
        $tables[$key] = dalo_portal3_paypal_table($config,$key);
    }
    if (count(array_unique($tables)) !== count($tables)) { throw new InvalidArgumentException('Ambiguous registration tables'); }
    $alphabet = $config['CONFIG_USER_ALLOWEDRANDOMCHARS'] ?? 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
    if (!is_string($alphabet) || preg_match('/^[A-Za-z0-9]{1,256}$/D', $alphabet) !== 1) {
        throw new InvalidArgumentException('Invalid PIN alphabet');
    }
    // Portal3 intentionally uses PASSWORD_LENGTH for its PIN, not USERNAME_LENGTH.
    $length = $config['CONFIG_PASSWORD_LENGTH'] ?? '8';
    if ((!is_string($length) && !is_int($length)) || !preg_match('/\A[1-9][0-9]?\z/D',(string)$length) || (int)$length>64) {
        throw new InvalidArgumentException('Invalid PIN length');
    }
    $locked = false;
    try {
        // Same schema-scoped name as the cooperating free-signup allocator.
        $database = dalo_portal1_paypal_rows($pdo, 'SELECT DATABASE() AS name')[0]['name'];
        $lockName = 'dalo-chilli-signup-' . substr(hash('sha256', $database), 0, 40);
        if ((int) dalo_portal1_paypal_rows($pdo, 'SELECT GET_LOCK(?,10) AS acquired', array($lockName))[0]['acquired'] !== 1) {
            throw new RuntimeException('Registration unavailable');
        }
        $locked = true;
        if (!$pdo->beginTransaction()) { throw new RuntimeException('Registration transaction unavailable'); }
        // Acquire table metadata locks before asserting transactional engines.
        foreach ($tables as $table) { dalo_portal1_paypal_rows($pdo, "SELECT 1 FROM $table LIMIT 0"); }
        foreach ($tables as $table) {
            $engine = dalo_portal1_paypal_rows($pdo, 'SELECT ENGINE FROM information_schema.TABLES
                WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', array(trim($table, '`')));
            if (strcasecmp($engine[0]['ENGINE'] ?? '', 'InnoDB') !== 0) { throw new RuntimeException('Registration requires InnoDB'); }
        }
        $rows = dalo_portal1_paypal_rows($pdo, "SELECT planId,planName,planCost,planTax,planCurrency FROM {$tables['plans']}
            WHERE planType='PayPal' AND planId=? LIMIT 2 FOR UPDATE", array($input['planId']));
        if (count($rows) !== 1 || $rows[0]['planId'] !== $input['planId']) {
            throw new InvalidArgumentException('Registration plan unavailable or ambiguous');
        }
        $plan = $rows[0];
        dalo_paypal_text($plan['planName'], 128, true);
        dalo_paypal_money($plan['planCost']);
        dalo_paypal_money(($plan['planTax'] ?? '') === '' ? '0' : $plan['planTax']);
        if (!in_array($plan['planCurrency'], array('USD','EUR','GBP','CAD','AUD','NZD','CHF','SGD','HKD'), true)) {
            throw new InvalidArgumentException('Registration currency unavailable');
        }
        $pin = null; $correlation = null;
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $candidate = '';
            for ($i = 0; $i < (int)$length; $i++) { $candidate .= $alphabet[random_int(0, strlen($alphabet)-1)]; }
            $token = bin2hex(random_bytes(32));
            $collision = false;
            foreach (array('info', 'orders', 'check', 'reply', 'mapping') as $source) {
                if (dalo_portal1_paypal_rows($pdo, "SELECT username FROM {$tables[$source]} WHERE username=? LIMIT 1 FOR UPDATE", array($candidate))) {
                    $collision = true;
                }
            }
            if (dalo_portal1_paypal_rows($pdo, "SELECT txnId FROM {$tables['orders']} WHERE txnId=? LIMIT 1 FOR UPDATE", array($token))) { $collision = true; }
            if (!$collision) { $pin = $candidate; $correlation = $token; break; }
        }
        if ($pin === null) { throw new RuntimeException('Registration identity unavailable'); }
        $info = array('username'=>$pin, 'firstname'=>$input['firstName'], 'lastname'=>$input['lastName'], 'creationby'=>'paypal-webinterface');
        $order = array('username'=>$pin, 'txnId'=>$correlation, 'planName'=>$plan['planName'], 'planId'=>$plan['planId']);
        dalo_portal1_paypal_capacity($pdo, $tables['info'], $info);
        dalo_portal1_paypal_capacity($pdo, $tables['orders'], $order);
        $date = date('Y-m-d H:i:s');
        // Preserve Portal3's historical projection: required address/city/state are not stored.
        $infoId = dalo_portal2_paypal_insert($pdo, $tables['info'], $info + array('creationdate'=>$date));
        $orderId = dalo_portal2_paypal_insert($pdo, $tables['orders'], $order);
        foreach (array(array('info',$infoId,$info + array('creationdate'=>$date)), array('orders',$orderId,$order)) as $check) {
            $stored = dalo_portal1_paypal_rows($pdo, "SELECT * FROM {$tables[$check[0]]} WHERE id=?", array($check[1]));
            if (count($stored) !== 1) { throw new RuntimeException('Registration verification failed'); }
            foreach ($check[2] as $field=>$value) {
                if (($stored[0][$field] ?? null) !== $value) { throw new RuntimeException('Registration verification failed'); }
            }
        }
        if (!$pdo->commit()) { throw new RuntimeException('Registration commit unavailable'); }
        return array('pin'=>$pin, 'correlation'=>$correlation, 'plan'=>$plan, 'checkout'=>$checkout);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) { try { $pdo->rollBack(); } catch (Throwable $ignored) {} }
        // Never chain driver errors or retain generated PINs/receipt tokens in an error.
        if ($error instanceof InvalidArgumentException) { throw new InvalidArgumentException('Invalid registration'); }
        throw new RuntimeException('Registration could not be completed');
    } finally {
        if ($locked) {
            try { dalo_portal1_paypal_rows($pdo, 'SELECT RELEASE_LOCK(?)', array($lockName)); }
            catch (Throwable $ignored) { /* Nonpersistent teardown releases; committed result stays successful. */ }
        }
    }
}

/** Single legacy PayPal row; never activate or change persisted payment status. */
function dalo_portal3_paypal_receipt(PDO $pdo,$config,$correlation) {
    return dalo_portal1_paypal_receipt($pdo,$config,$correlation);
}
