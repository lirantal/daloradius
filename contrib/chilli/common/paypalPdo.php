<?php
/* daloRADIUS — GPL-2.0-or-later. Verified, atomic and idempotent PayPal IPN. */
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { http_response_code(404); exit; }
require_once __DIR__ . '/database.php';

function dalo_paypal_text($value, $limit = 200, $required = false) {
    if (!is_string($value) || strpos($value, "\0") !== false || preg_match('//u', $value) !== 1 ||
        preg_match_all('/./us', $value) > $limit || ($required && $value === '')) {
        throw new InvalidArgumentException('Invalid payment field');
    }
    return $value;
}

/** Parse the actual verified body, not PHP's truncated/mangled POST projection. */
function dalo_paypal_parse($raw) {
    if (!is_string($raw) || $raw === '' || strlen($raw) > 65536) {
        throw new InvalidArgumentException('Invalid notification');
    }
    $fields = array();
    foreach (explode('&', $raw) as $part) {
        if ($part === '' || preg_match('/%(?![0-9a-f]{2})/i', $part)) {
            throw new InvalidArgumentException('Invalid notification');
        }
        $pair = explode('=', $part, 2); $name = urldecode($pair[0]);
        $value = urldecode($pair[1] ?? '');
        if (!preg_match('/^[A-Za-z0-9_]{1,64}$/D', $name) || $name === 'cmd' || array_key_exists($name, $fields) || strlen($value) > 4096) {
            throw new InvalidArgumentException('Ambiguous notification');
        }
        $fields[$name] = $value;
        if (count($fields) > 200) { throw new InvalidArgumentException('Oversized notification'); }
    }
    return $fields;
}

/** Exact VERIFIED body, trusted TLS peer/host, no redirect or POST-selected URL. */
function dalo_paypal_verify($config, $raw, $sandbox) {
    if (!function_exists('curl_init')) { throw new RuntimeException('Payment verification unavailable'); }
    $url = $sandbox ? 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr' : 'https://ipnpb.paypal.com/cgi-bin/webscr';
    $curl = curl_init($url);
    try {
        $response = '';
        $options = array(
            CURLOPT_POST => true, CURLOPT_POSTFIELDS => 'cmd=_notify-validate&' . $raw,
            CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded', 'Connection: close'),
            CURLOPT_USERAGENT => 'daloRADIUS-IPN', CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false, CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_TIMEOUT => 30,
            CURLOPT_WRITEFUNCTION => static function ($handle, $chunk) use (&$response) {
                if (strlen($response) + strlen($chunk) > 1024) { return 0; }
                $response .= $chunk; return strlen($chunk);
            },
        );
        if (isset($config['CONFIG_PAYPAL_CA_FILE'])) {
            $ca = $config['CONFIG_PAYPAL_CA_FILE'];
            if (!is_string($ca) || !is_file($ca) || !is_readable($ca)) {
                throw new RuntimeException('Payment trust configuration unavailable');
            }
            $options[CURLOPT_CAINFO] = $ca;
        }
        curl_setopt_array($curl, $options);
        if (curl_exec($curl) === false || curl_getinfo($curl, CURLINFO_RESPONSE_CODE) !== 200) {
            throw new RuntimeException('Payment verification unavailable');
        }
        if (trim($response) === 'INVALID') { return false; }
        if (trim($response) !== 'VERIFIED') { throw new RuntimeException('Payment verification unavailable'); }
        return true;
    } finally { curl_close($curl); }
}

function dalo_paypal_table($config, $key, $default) {
    $table = $config[$key] ?? $default;
    if (!is_string($table) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $table)) {
        throw new InvalidArgumentException('Invalid payment table');
    }
    return '`' . $table . '`';
}

function dalo_paypal_money($value) {
    if (!is_string($value) || !preg_match('/^[0-9]{1,7}(?:\.[0-9]{1,2})?$/D', $value)) {
        throw new InvalidArgumentException('Invalid payment amount');
    }
    $parts = explode('.', $value, 2);
    return (int) $parts[0] * 100 + (int) str_pad($parts[1] ?? '', 2, '0');
}

function dalo_paypal_date($value) {
    dalo_paypal_text($value, 200, true);
    $stamp = strtotime($value);
    if ($stamp === false) { throw new InvalidArgumentException('Invalid payment date'); }
    return gmdate('Y-m-d H:i:s', $stamp);
}

function dalo_paypal_one(PDO $pdo, $sql, $params) {
    $s = $pdo->prepare($sql); $s->execute($params); $rows = $s->fetchAll(PDO::FETCH_ASSOC);
    if (count($rows) !== 1) { throw new InvalidArgumentException('Payment identity unavailable or ambiguous'); }
    return $rows[0];
}

function dalo_paypal_insert(PDO $pdo, $table, $data) {
    // Columns only come from the fixed maps below, never from a notification.
    $s = $pdo->prepare('INSERT INTO ' . $table . ' (`' . implode('`,`', array_keys($data)) . '`) VALUES (' .
        implode(',', array_fill(0, count($data), '?')) . ')');
    $s->execute(array_values($data));
}

function dalo_paypal_update(PDO $pdo, $table, $id, $data) {
    $columns = array(); foreach ($data as $column => $value) { $columns[] = '`' . $column . '`=?'; }
    $s = $pdo->prepare('UPDATE ' . $table . ' SET ' . implode(',', $columns) . ' WHERE id=?');
    $values = array_values($data); $values[] = $id; $s->execute($values);
}

function dalo_paypal_auth(PDO $pdo, $tables, $username, $value) {
    $s = $pdo->prepare('SELECT id FROM ' . $tables['check'] . " WHERE username=? AND attribute='Auth-Type' ORDER BY id FOR UPDATE");
    $s->execute(array($username)); $ids = $s->fetchAll(PDO::FETCH_COLUMN);
    if (count($ids) === 1) {
        $s = $pdo->prepare('UPDATE ' . $tables['check'] . " SET op=':=',value=? WHERE id=?");
        $s->execute(array($value, $ids[0]));
    } else {
        $s = $pdo->prepare('DELETE FROM ' . $tables['check'] . " WHERE username=? AND attribute='Auth-Type'");
        $s->execute(array($username));
        dalo_paypal_insert($pdo, $tables['check'], array('username'=>$username,'attribute'=>'Auth-Type','op'=>':=','value'=>$value));
    }
}

function dalo_paypal_enable(PDO $pdo, $tables, $username, $plan, $modern) {
    $groups = array();
    if ($modern) {
        $s = $pdo->prepare('SELECT profile_name FROM ' . $tables['profiles'] . ' WHERE plan_name=? ORDER BY profile_name FOR UPDATE');
        $s->execute(array($plan['planName'])); $groups = $s->fetchAll(PDO::FETCH_COLUMN);
    } elseif (($plan['planGroup'] ?? '') !== '') { $groups[] = $plan['planGroup']; }
    foreach ($groups as $group) { dalo_paypal_text($group, 64, true); }
    $groups = array_values(array_unique($groups)); sort($groups, SORT_STRING);
    foreach ($groups as $group) {
        $defined = false;
        foreach (array('groupcheck','groupreply') as $source) {
            $s = $pdo->prepare('SELECT groupname FROM ' . $tables[$source] . ' WHERE groupname=? FOR UPDATE');
            $s->execute(array($group)); if ($s->fetchColumn() !== false) { $defined = true; } $s->closeCursor();
        }
        if (!$defined) { throw new InvalidArgumentException('Payment profile unavailable'); }
    }
    dalo_paypal_auth($pdo, $tables, $username, 'Accept');
    foreach ($groups as $group) {
        $s = $pdo->prepare('SELECT username FROM ' . $tables['mapping'] . ' WHERE username=? AND groupname=? FOR UPDATE');
        $s->execute(array($username, $group)); $exists = $s->fetchColumn() !== false; $s->closeCursor();
        if (!$exists) { dalo_paypal_insert($pdo, $tables['mapping'], array('username'=>$username,'groupname'=>$group,'priority'=>0)); }
    }
    if (!$groups) {
        $attribute = null;
        if (($plan['planTimeType'] ?? '') === 'Time-To-Finish') { $attribute = 'Access-Period'; }
        elseif (($plan['planTimeType'] ?? '') === 'Accumulative') {
            $periods = array('Never'=>'Max-All-Session','Monthly'=>'Max-Monthly-Session','Weekly'=>'Max-Weekly-Session',
                'Daily'=>'Max-Daily-Session','Yearly'=>'Max-Yearly-Session','Quaterly'=>'Max-Quaterly-Session');
            if ($modern || ($plan['planRecurring'] ?? '') === 'Yes') { $attribute = $periods[$plan['planRecurringPeriod'] ?? ''] ?? null; }
        }
        if ($attribute !== null) {
            $bank = dalo_paypal_text($plan['planTimeBank'] ?? '', 128, true);
            $s = $pdo->prepare('SELECT id FROM ' . $tables['check'] . ' WHERE username=? AND attribute=? FOR UPDATE');
            $s->execute(array($username, $attribute)); $ids = $s->fetchAll(PDO::FETCH_COLUMN);
            if (!$ids) { dalo_paypal_insert($pdo, $tables['check'], array('username'=>$username,'attribute'=>$attribute,'op'=>':=','value'=>$bank)); }
            else {
                $s = $pdo->prepare('UPDATE ' . $tables['check'] . " SET op=':=',value=? WHERE username=? AND attribute=?");
                $s->execute(array($bank, $username, $attribute));
            }
        }
    }
}

function dalo_paypal_bill(PDO $pdo, $tables, $username, $plan, $event) {
    $rows = $pdo->prepare('SELECT id FROM ' . $tables['billinfo'] . ' WHERE username=? ORDER BY id FOR UPDATE');
    $rows->execute(array($username)); $ids = $rows->fetchAll(PDO::FETCH_COLUMN);
    if (!$ids) { throw new InvalidArgumentException('Payment billing account unavailable'); }
    $next = '0000-00-00';
    if (($plan['planRecurring'] ?? '') === 'Yes') {
        $periods = array('Daily'=>'+1 day','Weekly'=>'+1 week','Monthly'=>'+1 month','Quarterly'=>'+3 months','Quaterly'=>'+3 months',
            'Year'=>'+1 year','Yearly'=>'+1 year');
        $period = $periods[$plan['planRecurringPeriod'] ?? ''] ?? null;
        if ($period === null) { throw new InvalidArgumentException('Payment billing period unavailable'); }
        $next = (new DateTimeImmutable($event['date'],new DateTimeZone('UTC')))->modify($period)->format('Y-m-d');
    }
    $s = $pdo->prepare('UPDATE ' . $tables['billinfo'] . ' SET lastbill=?,nextbill=? WHERE username=? AND (lastbill<=? OR lastbill IS NULL)');
    $s->execute(array(substr($event['date'],0,10), $next, $username, substr($event['date'],0,10)));
    dalo_paypal_insert($pdo, $tables['history'], array('username'=>$username,'planId'=>$plan['id'],'billAmount'=>$event['gross'],
        'billPerformer'=>'PayPal Provision','billReason'=>$event['type'],'paymentmethod'=>'PayPal',
        'creationdate'=>$event['date'],'creationby'=>'PayPal Provision'));
}

/** Verification finishes before SQL. All subsequent mutations use this handle. */
function dalo_paypal_process($config, $fields, $variant, $sandbox) {
    $modern = $variant === 2;
    $correlation = dalo_paypal_text($fields['option_selection1'] ?? '', 200, true);
    $type = dalo_paypal_text($fields['txn_type'] ?? 'web_accept', 40, true);
    $supported = array('web_accept','subscr_signup','subscr_payment','subscr_cancel','subscr_eot','subscr_failed','subscr_modify','recurring_payment');
    if (!in_array($type, $supported, true) || (!$modern && $type !== 'web_accept')) { return 'ignored'; }
    $payment = in_array($type, array('web_accept','subscr_payment','recurring_payment'), true);
    $status = $payment ? dalo_paypal_text($fields['payment_status'] ?? '', 32, true) : '';
    if ($payment && !in_array($status, array('Completed','Pending','Denied','Failed'), true)) { return 'ignored'; }
    $providerId = dalo_paypal_text($payment ? ($fields['txn_id'] ?? '') : ($fields['subscr_id'] ?? ''), 200, true);
    $configuredEmail = $config['CONFIG_PAYPAL_RECEIVER_EMAIL'] ?? ($config['CONFIG_MERCHANT_BUSINESS_ID'] ?? '');
    $configuredId = $config['CONFIG_PAYPAL_RECEIVER_ID'] ?? '';
    dalo_paypal_text($configuredEmail); dalo_paypal_text($configuredId);
    if ($configuredId !== '') {
        if (!hash_equals($configuredId, $fields['receiver_id'] ?? '')) { throw new InvalidArgumentException('Payment receiver mismatch'); }
        $receiver = $configuredId;
    } elseif ($configuredEmail !== '' && strpos($configuredEmail, '@') !== false) {
        $receiver = strtolower($configuredEmail);
        if (!hash_equals($receiver, strtolower(dalo_paypal_text($fields['receiver_email'] ?? '', 200, true)))) {
            throw new InvalidArgumentException('Payment receiver mismatch');
        }
    } else { throw new RuntimeException('Payment receiver must be configured'); }
    if (($fields['test_ipn'] ?? '0') !== ($sandbox ? '1' : '0')) { throw new InvalidArgumentException('Payment environment mismatch'); }
    $rawDate = $fields['payment_date'] ?? (in_array($type,array('subscr_signup','subscr_cancel','subscr_modify'),true) ? ($fields['subscr_date'] ?? '') : '');
    $eventDate = $rawDate !== '' ? dalo_paypal_date($rawDate) : gmdate('Y-m-d H:i:s');
    if ($payment && $rawDate === '') { throw new InvalidArgumentException('Payment date unavailable'); }
    $scope = ($sandbox ? 'sandbox:' : 'live:') . $receiver;
    $subscriptionId=dalo_paypal_text($fields['subscr_id'] ?? ($fields['recurring_payment_id'] ?? ''),200);
    $subscriptionKey=$subscriptionId!=='' ? hash('sha256',json_encode(array($scope,$subscriptionId),JSON_THROW_ON_ERROR)) : null;
    if ($type !== 'web_accept' && $subscriptionKey === null) { throw new InvalidArgumentException('Subscription identity unavailable'); }
    $eventKey = hash('sha256', json_encode(array($scope,$providerId,$type,$status,$type==='subscr_modify'?$rawDate:''),JSON_THROW_ON_ERROR));
    $paymentKey = $payment && $status === 'Completed' ? hash('sha256', json_encode(array($scope,$providerId))) : null;
    $tables = array(
        'events'=>dalo_paypal_table($config,'CONFIG_DB_TBL_CHILLI_PAYPAL_EVENTS','chilli_paypal_events'),
        'orders'=>dalo_paypal_table($config,$modern?'CONFIG_DB_TBL_DALOBILLINGMERCHANT':'CONFIG_DB_TBL_DALOBILLINGPAYPAL',$modern?'billing_merchant':'billing_paypal'),
        'plans'=>dalo_paypal_table($config,'CONFIG_DB_TBL_DALOBILLINGPLANS','billing_plans'),
        'check'=>dalo_paypal_table($config,'CONFIG_DB_TBL_RADCHECK','radcheck'),
        'mapping'=>dalo_paypal_table($config,'CONFIG_DB_TBL_RADUSERGROUP','radusergroup'),
        'groupcheck'=>dalo_paypal_table($config,'CONFIG_DB_TBL_RADGROUPCHECK','radgroupcheck'),
        'groupreply'=>dalo_paypal_table($config,'CONFIG_DB_TBL_RADGROUPREPLY','radgroupreply'),
    );
    if ($modern) {
        $tables['profiles']=dalo_paypal_table($config,'CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES','billing_plans_profiles');
        $tables['billinfo']=dalo_paypal_table($config,'CONFIG_DB_TBL_DALOUSERBILLINFO','userbillinfo');
        $tables['history']=dalo_paypal_table($config,'CONFIG_DB_TBL_DALOBILLINGHISTORY','billing_history');
    }
    if (count($tables) !== count(array_unique($tables))) { throw new InvalidArgumentException('Ambiguous payment tables'); }
    $pdo = null; $locked = false;
    try {
        $pdo = dalo_chilli_pdo_open($config);
        $lockName = 'dalo-paypal-' . substr(hash('sha256', $pdo->query('SELECT DATABASE()')->fetchColumn()), 0, 50);
        $s = $pdo->prepare('SELECT GET_LOCK(?,10)'); $s->execute(array($lockName));
        if ((int) $s->fetchColumn() !== 1) { throw new RuntimeException('Payment unavailable'); } $locked = true;
        $s = $pdo->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
        foreach ($tables as $table) {
            $s->execute(array(trim($table,'`')));
            if (strcasecmp((string)$s->fetchColumn(), 'InnoDB') !== 0) { throw new RuntimeException('Payment requires transactional tables'); }
        }
        if (!$pdo->beginTransaction()) { throw new RuntimeException('Payment transaction unavailable'); }
        $s = $pdo->prepare('SELECT * FROM ' . $tables['orders'] . ' WHERE txnId=? ORDER BY id FOR UPDATE');
        $s->execute(array($correlation)); $orders = $s->fetchAll(PDO::FETCH_ASSOC);
        if (!$orders || (!$modern && count($orders) !== 1)) { throw new InvalidArgumentException('Payment order unavailable or ambiguous'); }
        $order = $orders[0];
        if ($order['txnId'] !== $correlation) { throw new InvalidArgumentException('Payment order mismatch'); }
        foreach ($orders as $existing) {
            if ($existing['txnId'] !== $correlation || $existing['username'] !== $order['username'] || (string)$existing['planId'] !== (string)$order['planId'] ||
                ($modern && $existing['vendor_type'] !== 'PayPal')) { throw new InvalidArgumentException('Ambiguous payment order'); }
        }
        if ($modern && count(array_filter($orders, static function($r){return $r['txn_type']==='';})) > 1) {
            throw new InvalidArgumentException('Ambiguous payment registration');
        }
        $username = dalo_paypal_text($variant === 1 && ($order['pin'] ?? '') !== '' && $order['pin'] !== null ? $order['pin'] : $order['username'], 64, true);
        if (isset($fields['option_selection2']) && $fields['option_selection2'] !== '' && $fields['option_selection2'] !== $username) {
            throw new InvalidArgumentException('Payment account mismatch');
        }
        $plan = dalo_paypal_one($pdo, 'SELECT * FROM ' . $tables['plans'] . ' WHERE ' . ($modern?'id':'planId') . '=? LIMIT 2 FOR UPDATE', array($order['planId']));
        if (isset($fields['item_number']) && $fields['item_number'] !== (string)$order['planId']) { throw new InvalidArgumentException('Payment plan mismatch'); }
        if (isset($fields['item_name']) && $fields['item_name'] !== ($plan['planName'] ?? '')) { throw new InvalidArgumentException('Payment plan mismatch'); }
        $gross = $payment ? dalo_paypal_text($fields['mc_gross'] ?? '', 200, true) : '';
        $currency = $payment ? dalo_paypal_text($fields['mc_currency'] ?? '', 3, true) : '';
        if ($payment) {
            // These examples support the following two-decimal currencies only.
            if (!in_array($currency,array('USD','EUR','GBP','CAD','AUD','NZD','CHF','SGD','HKD'),true) || $currency !== $plan['planCurrency'] ||
                ($fields['quantity'] ?? '1') !== '1') { throw new InvalidArgumentException('Payment currency or quantity mismatch'); }
            $cost = dalo_paypal_money($plan['planCost'] ?? ''); $tax = dalo_paypal_money(($plan['planTax'] ?? '') === '' ? '0' : $plan['planTax']);
            $expected = $cost + ($modern ? intdiv($cost * $tax + 5000,10000) : $tax);
            if (dalo_paypal_money($gross) !== $expected) { throw new InvalidArgumentException('Payment amount mismatch'); }
        }
        if ($type !== 'web_accept' && ($plan['planRecurring'] ?? '') !== 'Yes') { throw new InvalidArgumentException('Payment is not a subscription'); }
        $core = array($scope,$correlation,(string)$order['planId'],$username,$providerId,$type,$status,$gross,$currency,$rawDate,$subscriptionId);
        $fingerprint = hash('sha256', json_encode($core, JSON_THROW_ON_ERROR));
        $orderHash = hash('sha256', json_encode(array($scope,$correlation), JSON_THROW_ON_ERROR));
        $s=$pdo->prepare('SELECT event_key FROM '.$tables['events'].' WHERE order_hash=? FOR UPDATE');$s->execute(array($orderHash));
        if ($s->fetchColumn()===false && (($order['payment_status'] ?? '') !== '' || ($modern && $order['txn_type'] !== ''))) {
            // Old callbacks could persist INVALID/partial notifications. Never
            // infer an authenticated event or fulfill an already-used order twice.
            throw new RuntimeException('Legacy payment requires reconciliation');
        }
        $s = $pdo->prepare('SELECT event_hash FROM ' . $tables['events'] . ' WHERE event_key=? FOR UPDATE');
        $s->execute(array($eventKey)); $prior = $s->fetchColumn();
        if ($prior !== false) {
            if (!hash_equals($prior,$fingerprint)) { throw new InvalidArgumentException('Conflicting payment replay'); }
            $pdo->rollBack(); return 'duplicate';
        }
        if ($paymentKey !== null) {
            $s=$pdo->prepare('SELECT event_key FROM '.$tables['events'].' WHERE payment_key=? FOR UPDATE');$s->execute(array($paymentKey));
            if ($s->fetchColumn() !== false) { throw new InvalidArgumentException('Payment already assigned'); }
        }
        // Legacy one-shot orders cannot consume a second distinct completed payment.
        if ($paymentKey !== null && ($plan['planRecurring'] ?? '') !== 'Yes') {
            $s=$pdo->prepare('SELECT event_key FROM '.$tables['events'].' WHERE order_hash=? AND payment_key IS NOT NULL FOR UPDATE');$s->execute(array($orderHash));
            if ($s->fetchColumn() !== false) { throw new InvalidArgumentException('Payment order already fulfilled'); }
        }
        if ($subscriptionKey!==null) {
            $s=$pdo->prepare('SELECT DISTINCT subscription_key FROM '.$tables['events'].' WHERE order_hash=? AND subscription_key IS NOT NULL FOR UPDATE');
            $s->execute(array($orderHash));
            foreach ($s->fetchAll(PDO::FETCH_COLUMN) as $enrollment) {
                if (!hash_equals($enrollment,$subscriptionKey)) { throw new InvalidArgumentException('Order already enrolled'); }
            }
            $s=$pdo->prepare('SELECT order_hash FROM '.$tables['events'].' WHERE subscription_key=? FOR UPDATE');$s->execute(array($subscriptionKey));
            foreach ($s->fetchAll(PDO::FETCH_COLUMN) as $boundOrder) {
                if (!hash_equals($boundOrder,$orderHash)) { throw new InvalidArgumentException('Subscription already assigned'); }
            }
            if (in_array($type,array('subscr_cancel','subscr_eot'),true)) {
                $s=$pdo->prepare('SELECT event_key FROM '.$tables['events'].' WHERE order_hash=? AND subscription_key=? AND (event_type=\'subscr_signup\' OR payment_key IS NOT NULL) FOR UPDATE');
                $s->execute(array($orderHash,$subscriptionKey));
                if ($s->fetchColumn()===false) { throw new InvalidArgumentException('Subscription enrollment unavailable'); }
            }
        }
        dalo_paypal_insert($pdo,$tables['events'],array('event_key'=>$eventKey,'payment_key'=>$paymentKey,'subscription_key'=>$subscriptionKey,'event_hash'=>$fingerprint,
            'order_hash'=>$orderHash,'event_type'=>$type,'event_status'=>$status,'event_date'=>$eventDate));
        $s=$pdo->prepare('SELECT MAX(event_date) FROM '.$tables['events'].' WHERE order_hash=? AND event_key<>? AND (payment_key IS NOT NULL OR event_type IN (\'subscr_cancel\',\'subscr_eot\'))');
        $s->execute(array($orderHash,$eventKey));$latest=$s->fetchColumn();
        $stale=$latest!==null && $latest!==false && $eventDate<$latest;
        $s=$pdo->prepare('SELECT event_key FROM '.$tables['events'].' WHERE order_hash=? AND event_key<>? AND event_type IN (\'subscr_cancel\',\'subscr_eot\') FOR UPDATE');
        $s->execute(array($orderHash,$eventKey));$terminated=$s->fetchColumn()!==false;
        $writeProjection=!($payment && $status!=='Completed' && $latest!==null && $latest!==false);
        $map = $modern ? array('quantity'=>'quantity','receiver_email'=>'business_email','business'=>'business_id','tax'=>'payment_tax',
            'mc_gross'=>'payment_cost','mc_fee'=>'payment_fee','mc_handling'=>'payment_total','mc_currency'=>'payment_currency',
            'first_name'=>'first_name','last_name'=>'last_name','payer_email'=>'payer_email','address_name'=>'payer_address_name',
            'address_street'=>'payer_address_street','address_country'=>'payer_address_country','address_country_code'=>'payer_address_country_code',
            'address_city'=>'payer_address_city','address_state'=>'payer_address_state','address_zip'=>'payer_address_zip','payment_status'=>'payment_status',
            'payment_address_status'=>'payment_address_status','payer_status'=>'payer_status','pending_reason'=>'pending_reason','reason_code'=>'reason_code',
            'receipt_ID'=>'receipt_ID','payment_type'=>'payment_type') : array_combine(
            array('quantity','receiver_email','business','tax','mc_gross','mc_fee','mc_currency','first_name','last_name','payer_email','address_name',
                'address_street','address_country','address_country_code','address_city','address_state','address_zip','payment_status','payment_address_status','payer_status'),
            array('quantity','receiver_email','business','tax','mc_gross','mc_fee','mc_currency','first_name','last_name','payer_email','address_name',
                'address_street','address_country','address_country_code','address_city','address_state','address_zip','payment_status','payment_address_status','payer_status'));
        $data = array(); foreach ($map as $source=>$column) {
            $value = $fields[$source] ?? '';
            if (preg_match('//u',$value) !== 1) {
                $charset=$fields['charset'] ?? '';
                if (!in_array(strtolower($charset),array('windows-1252','iso-8859-1'),true)) { throw new InvalidArgumentException('Unsupported payment encoding'); }
                $value=iconv($charset,'UTF-8',$value);
            }
            $data[$column] = dalo_paypal_text($value);
        }
        $data['payment_date']=$eventDate;
        if ($modern && $writeProjection) {
            $data['txn_type']=$type; $data['txn_id']=$providerId; $data['vendor_type']='PayPal';
            if (($plan['planRecurring'] ?? '') === 'Yes' && $order['txn_type'] !== '') {
                $data['username']=$username;$data['txnId']=$correlation;$data['planId']=$order['planId'];
                dalo_paypal_insert($pdo,$tables['orders'],$data);
            } else { dalo_paypal_update($pdo,$tables['orders'],$order['id'],$data); }
        } elseif (!$modern && $writeProjection) {
            $data['planName']=dalo_paypal_text($plan['planName'] ?? '',128,true);
            dalo_paypal_update($pdo,$tables['orders'],$order['id'],$data);
        }
        // Pending or older events must not undo completed account provisioning.
        $event=array('date'=>$eventDate,'gross'=>$gross,'type'=>$type);
        if ($payment && $status==='Completed') {
            if (!$stale && !$terminated) { dalo_paypal_enable($pdo,$tables,$username,$plan,$modern); }
            if ($modern) { dalo_paypal_bill($pdo,$tables,$username,$plan,$event); }
        } elseif ($modern && in_array($type,array('subscr_cancel','subscr_eot'),true)) {
            dalo_paypal_auth($pdo,$tables,$username,'Reject');
        }
        // A subscription signup is not evidence of a completed payment.
        if (!$pdo->commit()) { throw new RuntimeException('Payment commit unavailable'); }
        return 'processed';
    } catch (Throwable $error) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) { try { $pdo->rollBack(); } catch (Throwable $ignored) {} }
        if ($error instanceof InvalidArgumentException) { throw new InvalidArgumentException('Notification rejected'); }
        throw new RuntimeException('Notification processing unavailable');
    } finally {
        if ($pdo instanceof PDO) {
            if ($locked) { try {$s=$pdo->prepare('SELECT RELEASE_LOCK(?)');$s->execute(array($lockName));} catch(Throwable $ignored){} }
            dalo_chilli_database_close($pdo);
        }
    }
}

function dalo_paypal_endpoint($config, $variant, $defaultSandbox) {
    header('Content-Type: text/plain; charset=UTF-8'); header('Cache-Control: no-store');
    try {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); echo 'POST required'; return; }
        $raw=file_get_contents('php://input',false,null,0,65537);$fields=dalo_paypal_parse($raw);
        $sandbox=$config['CONFIG_PAYPAL_SANDBOX'] ?? $defaultSandbox;
        if (!is_bool($sandbox)) { throw new RuntimeException('Invalid payment environment configuration'); }
        if (!dalo_paypal_verify($config,$raw,$sandbox)) { http_response_code(400);echo 'Notification rejected';return; }
        $result=dalo_paypal_process($config,$fields,$variant,$sandbox);
        echo $result;
    } catch (InvalidArgumentException $error) { http_response_code(400);echo 'Notification rejected'; }
    catch (Throwable $error) { http_response_code(503);echo 'Notification processing unavailable'; }
}
