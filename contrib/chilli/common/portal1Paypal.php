<?php
/* daloRADIUS — GPL-2.0-or-later. R23 Portal1 pending PayPal registrations. */
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { http_response_code(404); exit; }
require_once __DIR__ . '/paypalPdo.php';

function dalo_portal1_paypal_table($config, $key) {
    $defaults = array('CONFIG_DB_TBL_DALOBILLINGPLANS' => 'billing_plans',
        'CONFIG_DB_TBL_DALOUSERINFO' => 'userinfo', 'CONFIG_DB_TBL_DALOBILLINGPAYPAL' => 'billing_paypal',
        'CONFIG_DB_TBL_RADCHECK' => 'radcheck', 'CONFIG_DB_TBL_RADUSERGROUP' => 'usergroup');
    if (!array_key_exists($key, $defaults)) { throw new InvalidArgumentException('Invalid registration table'); }
    $table = $config[$key] ?? $defaults[$key];
    if (!is_string($table) || preg_match('/\A[A-Za-z_][A-Za-z0-9_]{0,63}\z/D', $table) !== 1) {
        throw new InvalidArgumentException('Invalid registration table');
    }
    return '`' . $table . '`';
}

function dalo_portal1_paypal_rows(PDO $pdo, $sql, $params = array()) {
    $statement = $pdo->prepare($sql);
    if ($statement === false) { throw new RuntimeException('Registration prepare failed'); }
    try {
        if (!$statement->execute($params)) { throw new RuntimeException('Registration query failed'); }
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        if ($rows === false || $statement->errorCode() !== '00000') { throw new RuntimeException('Registration fetch failed'); }
        return $rows;
    } finally { $statement->closeCursor(); }
}

/** Fixed Standard fields matching the unchanged Portal1 IPN listener. */
function dalo_portal1_paypal_checkout($config) {
    $sandbox = $config['CONFIG_PAYPAL_SANDBOX'] ?? false;
    $business = $config['CONFIG_PAYPAL_RECEIVER_ID'] ?? '';
    try { dalo_paypal_text($business); }
    catch (InvalidArgumentException $error) { throw new RuntimeException('Checkout receiver unavailable'); }
    if ($business === '') {
        $business = $config['CONFIG_PAYPAL_RECEIVER_EMAIL'] ?? ($config['CONFIG_MERCHANT_BUSINESS_ID'] ?? '');
        try { dalo_paypal_text($business, 200, true); }
        catch (InvalidArgumentException $error) { throw new RuntimeException('Checkout receiver unavailable'); }
        if (filter_var($business, FILTER_VALIDATE_EMAIL) === false) { throw new RuntimeException('Checkout receiver unavailable'); }
    }
    $base = $config['CONFIG_PAYPAL_PORTAL_BASE_URL'] ?? '';
    if (!is_bool($sandbox) || !is_string($base) || strlen($base . '/success.php?txnId=' . str_repeat('0', 64)) > 255 ||
        preg_match('/[\x00-\x20\x7f\\\\]/', $base) || filter_var($base, FILTER_VALIDATE_URL) === false) {
        throw new RuntimeException('Checkout configuration unavailable');
    }
    $parts = parse_url($base);
    if (($parts['scheme'] ?? '') !== 'https' || empty($parts['host']) ||
        isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
        throw new RuntimeException('Checkout configuration unavailable');
    }
    return array('action' => $sandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr',
        'business' => $business, 'base' => rtrim($base, '/'));
}

function dalo_portal1_paypal_plans(PDO $pdo, $config) {
    $table = dalo_portal1_paypal_table($config, 'CONFIG_DB_TBL_DALOBILLINGPLANS');
    return dalo_portal1_paypal_rows($pdo, "SELECT planId,planName,planCost,planTax,planCurrency FROM $table WHERE planType='PayPal'");
}

/** Check real configured string capacities before inserts under permissive SQL mode. */
function dalo_portal1_paypal_capacity(PDO $pdo, $table, $values) {
    $rows = dalo_portal1_paypal_rows($pdo, 'SELECT COLUMN_NAME,CHARACTER_MAXIMUM_LENGTH,CHARACTER_OCTET_LENGTH
        FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', array(trim($table, '`')));
    $limits = array_column($rows, null, 'COLUMN_NAME');
    foreach ($values as $column => $value) {
        dalo_paypal_text($value, 200);
        if (!isset($limits[$column]['CHARACTER_MAXIMUM_LENGTH'], $limits[$column]['CHARACTER_OCTET_LENGTH']) ||
            preg_match_all('/./us', $value) > (int) $limits[$column]['CHARACTER_MAXIMUM_LENGTH'] ||
            strlen($value) > (int) $limits[$column]['CHARACTER_OCTET_LENGTH']) {
            throw new InvalidArgumentException('Registration exceeds storage capacity');
        }
    }
}

/** Own one checked transaction on the supplied connection; refuse borrowed transactions. */
function dalo_portal1_paypal_register(PDO $pdo, $config, $input) {
    if ($pdo->inTransaction()) { throw new LogicException('Registration requires an owned transaction'); }
    foreach (array('firstName', 'lastName', 'address', 'city', 'state', 'planId') as $field) {
        if (!array_key_exists($field, $input)) { throw new InvalidArgumentException('Missing registration field'); }
        dalo_paypal_text($input[$field], $field === 'planId' ? 128 : 200, $field === 'planId');
    }
    $checkout = dalo_portal1_paypal_checkout($config);
    $tables = array();
    foreach (array('plans' => 'CONFIG_DB_TBL_DALOBILLINGPLANS', 'info' => 'CONFIG_DB_TBL_DALOUSERINFO',
        'orders' => 'CONFIG_DB_TBL_DALOBILLINGPAYPAL', 'check' => 'CONFIG_DB_TBL_RADCHECK',
        'mapping' => 'CONFIG_DB_TBL_RADUSERGROUP') as $name => $key) {
        $tables[$name] = dalo_portal1_paypal_table($config, $key);
    }
    if (count(array_unique($tables)) !== count($tables)) { throw new InvalidArgumentException('Ambiguous registration tables'); }
    $alphabet = $config['CONFIG_USER_ALLOWEDRANDOMCHARS'] ?? 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
    if (!is_string($alphabet) || preg_match('/^[A-Za-z0-9]{1,256}$/D', $alphabet) !== 1) {
        throw new InvalidArgumentException('Invalid PIN alphabet');
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
            for ($i = 0; $i < 8; $i++) { $candidate .= $alphabet[random_int(0, strlen($alphabet)-1)]; }
            $token = bin2hex(random_bytes(32));
            $collision = false;
            foreach (array('info', 'orders', 'check', 'mapping') as $source) {
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
        // Preserve Portal1's historical projection: address/city/state are not stored.
        dalo_paypal_insert($pdo, $tables['info'], $info + array('creationdate'=>$date));
        $infoId = $pdo->lastInsertId();
        dalo_paypal_insert($pdo, $tables['orders'], $order);
        $orderId = $pdo->lastInsertId();
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

/** Historical bearer receipt lookup: this read never grants access or marks payment. */
function dalo_portal1_paypal_receipt(PDO $pdo, $config, $correlation) {
    dalo_paypal_text($correlation, 200, true);
    $table = dalo_portal1_paypal_table($config, 'CONFIG_DB_TBL_DALOBILLINGPAYPAL');
    $rows = dalo_portal1_paypal_rows($pdo, "SELECT txnId,username,payment_status FROM $table WHERE txnId=? LIMIT 2", array($correlation));
    if (count($rows) > 1 || ($rows && $rows[0]['txnId'] !== $correlation)) {
        throw new InvalidArgumentException('Receipt unavailable or ambiguous');
    }
    return $rows[0] ?? null;
}
