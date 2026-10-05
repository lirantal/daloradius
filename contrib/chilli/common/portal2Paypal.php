<?php
/* daloRADIUS — GPL-2.0-or-later. Portal2 pending PayPal registrations. */
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { http_response_code(404); exit; }
// Reuse checked, read-only SQL/capacity primitives; Portal1 policy stays unchanged.
require_once __DIR__ . '/portal1Paypal.php';

function dalo_portal2_paypal_table($config, $key) {
    $defaults = array('plans'=>'billing_plans','info'=>'userinfo','billinfo'=>'userbillinfo',
        'orders'=>'billing_merchant','check'=>'radcheck','reply'=>'radreply','mapping'=>'radusergroup');
    $keys = array('plans'=>'CONFIG_DB_TBL_DALOBILLINGPLANS','info'=>'CONFIG_DB_TBL_DALOUSERINFO',
        'billinfo'=>'CONFIG_DB_TBL_DALOUSERBILLINFO','orders'=>'CONFIG_DB_TBL_DALOBILLINGMERCHANT',
        'check'=>'CONFIG_DB_TBL_RADCHECK','reply'=>'CONFIG_DB_TBL_RADREPLY','mapping'=>'CONFIG_DB_TBL_RADUSERGROUP');
    if (!isset($keys[$key])) { throw new InvalidArgumentException('Invalid registration source'); }
    $table = $config[$keys[$key]] ?? $defaults[$key];
    if (!is_string($table) || preg_match('/\A[A-Za-z_][A-Za-z0-9_]{0,63}\z/D', $table) !== 1) {
        throw new InvalidArgumentException('Invalid registration table');
    }
    return '`'.$table.'`';
}

function dalo_portal2_paypal_id($value) {
    if (!is_string($value) || preg_match('/\A(?:0|[1-9][0-9]{0,9})\z/D', $value) !== 1 ||
        strlen($value) > 10 || (strlen($value) === 10 && strcmp($value, '2147483647') > 0)) {
        throw new InvalidArgumentException('Invalid registration plan');
    }
    return $value;
}

function dalo_portal2_paypal_checkout($config) {
    $sandbox = $config['CONFIG_PAYPAL_SANDBOX'] ?? true; // matches this listener's default
    $root = $config['CONFIG_MERCHANT_IPN_URL_ROOT'] ?? ($config['CONFIG_PAYPAL_PORTAL_BASE_URL'] ?? '');
    $translated = $config;
    $translated['CONFIG_PAYPAL_PORTAL_BASE_URL'] = $root;
    $translated['CONFIG_PAYPAL_SANDBOX'] = $sandbox;
    $checkout = dalo_portal1_paypal_checkout($translated);
    if (isset($config['CONFIG_MERCHANT_WEB_PAYMENT']) && $config['CONFIG_MERCHANT_WEB_PAYMENT'] !== $checkout['action']) {
        throw new RuntimeException('Checkout environment mismatch');
    }
    foreach (array('return'=>array('CONFIG_MERCHANT_IPN_URL_RELATIVE_SUCCESS','success.php'),
        'cancel_return'=>array('CONFIG_MERCHANT_IPN_URL_RELATIVE_FAILURE','index.php'),
        'notify_url'=>array('CONFIG_MERCHANT_IPN_URL_RELATIVE_DIR','paypal-ipn.php')) as $name=>$setting) {
        $relative = $config[$setting[0]] ?? $setting[1];
        if (!is_string($relative) || !preg_match('/\A[A-Za-z0-9_\/-]+\.php\z/D', $relative) ||
            strpos($relative,'..') !== false || $relative[0] === '/') {
            throw new RuntimeException('Checkout destination unavailable');
        }
        $url = $checkout['base'].'/'.$relative;
        if (strlen($url . ($name === 'return' ? '?txnId='.str_repeat('0',64) : '')) > 255) {
            throw new RuntimeException('Checkout destination unavailable');
        }
        $checkout[$name] = $url;
    }
    return $checkout;
}

function dalo_portal2_paypal_plans(PDO $pdo, $config) {
    $table = dalo_portal2_paypal_table($config, 'plans');
    return dalo_portal1_paypal_rows($pdo, "SELECT id AS planId,planName,planCost,planTax,planCurrency FROM $table WHERE planType='PayPal'");
}

/** Match the unchanged callback's exact percentage-tax arithmetic. */
function dalo_portal2_paypal_price($plan) {
    dalo_paypal_text($plan['planName'],128,true);
    $cost = dalo_paypal_money($plan['planCost']);
    $percent = dalo_paypal_money(($plan['planTax'] ?? '') === '' ? '0' : $plan['planTax']);
    $tax = intdiv($cost*$percent+5000,10000);
    if ($cost+$tax > 999999999 || !in_array($plan['planCurrency'],array('USD','EUR','GBP','CAD','AUD','NZD','CHF','SGD','HKD'),true)) {
        throw new InvalidArgumentException('Unsupported checkout amount');
    }
    if (!in_array($plan['planRecurring'],array('No','Yes'),true)) { throw new InvalidArgumentException('Unsupported checkout type'); }
    $periods = array('Daily'=>'D','Weekly'=>'W','Monthly'=>'M','Yearly'=>'Y');
    if ($plan['planRecurring'] === 'Yes' && !isset($periods[$plan['planRecurringPeriod']])) {
        throw new InvalidArgumentException('Unsupported checkout period');
    }
    $format = static function($cents) { return intdiv($cents,100).'.'.str_pad((string)($cents%100),2,'0',STR_PAD_LEFT); };
    return array('amount'=>$format($cost),'tax'=>$format($tax),
        'period'=>$periods[$plan['planRecurringPeriod']] ?? 'M');
}

function dalo_portal2_paypal_fields($registration) {
    $plan=$registration['plan'];$price=$registration['price'];$checkout=$registration['checkout'];
    $fields=array('business'=>$checkout['business'],'return'=>$checkout['return'].'?txnId='.rawurlencode($registration['correlation']),
        'cancel_return'=>$checkout['cancel_return'],'notify_url'=>$checkout['notify_url'],
        'item_name'=>$plan['planName'],'item_number'=>(string)$plan['planId'],'quantity'=>'1',
        'tax'=>$price['tax'],'currency_code'=>$plan['planCurrency'],'no_note'=>'1','no_shipping'=>'1','lc'=>'US',
        'on0'=>'Transaction ID','os0'=>$registration['correlation'],'on1'=>'Username','os1'=>$registration['pin']);
    if ($plan['planRecurring'] === 'No') { $fields['cmd']='_xclick';$fields['amount']=$price['amount']; }
    else { $fields += array('cmd'=>'_xclick-subscriptions','a3'=>$price['amount'],'p3'=>'1',
        't3'=>$price['period'],'src'=>'1','sra'=>'1','custom'=>$registration['pin']); }
    return $fields;
}

/** Fixed internal column maps only, with explicit silent-mode failure checks. */
function dalo_portal2_paypal_insert(PDO $pdo, $table, $data) {
    $statement=$pdo->prepare('INSERT INTO '.$table.' (`'.implode('`,`',array_keys($data)).'`) VALUES ('.
        implode(',',array_fill(0,count($data),'?')).')');
    if ($statement===false || !$statement->execute(array_values($data))) { throw new RuntimeException('Registration insert failed'); }
    $id=$pdo->lastInsertId();$statement->closeCursor();return $id;
}

function dalo_portal2_paypal_register(PDO $pdo, $config, $input) {
    if ($pdo->inTransaction()) { throw new LogicException('Registration requires an owned transaction'); }
    foreach (array('firstName','lastName','address','city','state','planId') as $field) {
        dalo_paypal_text($input[$field] ?? null,$field==='planId'?10:200,true);
    }
    $id=dalo_portal2_paypal_id($input['planId']);$checkout=dalo_portal2_paypal_checkout($config);
    $length=$config['CONFIG_USERNAME_LENGTH'] ?? '8';
    if ((!is_string($length) && !is_int($length)) || !preg_match('/\A[1-9][0-9]?\z/D',(string)$length) || (int)$length>64) {
        throw new InvalidArgumentException('Invalid PIN length');
    }
    $alphabet=$config['CONFIG_USER_ALLOWEDRANDOMCHARS'] ?? 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
    if (!is_string($alphabet) || !preg_match('/\A[A-Za-z0-9]{1,256}\z/D',$alphabet)) { throw new InvalidArgumentException('Invalid PIN alphabet'); }
    $tables=array();foreach (array('plans','info','billinfo','orders','check','reply','mapping') as $key) {$tables[$key]=dalo_portal2_paypal_table($config,$key);}
    if (count(array_unique($tables))!==count($tables)) {throw new InvalidArgumentException('Ambiguous registration tables');}
    $locked=false;
    try {
        $database=dalo_portal1_paypal_rows($pdo,'SELECT DATABASE() AS name')[0]['name'];
        $lockName='dalo-chilli-signup-'.substr(hash('sha256',$database),0,40);
        if ((int)dalo_portal1_paypal_rows($pdo,'SELECT GET_LOCK(?,10) AS acquired',array($lockName))[0]['acquired']!==1) {
            throw new RuntimeException('Registration unavailable');
        }
        $locked=true;if (!$pdo->beginTransaction()) {throw new RuntimeException('Registration unavailable');}
        foreach ($tables as $table) {dalo_portal1_paypal_rows($pdo,"SELECT 1 FROM $table LIMIT 0");}
        foreach ($tables as $table) {
            $engine=dalo_portal1_paypal_rows($pdo,'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',array(trim($table,'`')));
            if (strcasecmp($engine[0]['ENGINE'] ?? '','InnoDB')!==0) {throw new RuntimeException('Registration requires InnoDB');}
        }
        $rows=dalo_portal1_paypal_rows($pdo,"SELECT id AS planId,planName,planCost,planTax,planCurrency,planRecurring,planRecurringPeriod FROM {$tables['plans']}
            WHERE planType='PayPal' AND id=? LIMIT 2 FOR UPDATE",array($id));
        if (count($rows)!==1 || (string)$rows[0]['planId']!==$id) {throw new InvalidArgumentException('Registration plan unavailable');}
        $plan=$rows[0];$price=dalo_portal2_paypal_price($plan);$pin=null;$correlation=null;
        for ($attempt=0;$attempt<20;$attempt++) {
            $candidate='';for ($i=0;$i<(int)$length;$i++) {$candidate.=$alphabet[random_int(0,strlen($alphabet)-1)];}
            $token=bin2hex(random_bytes(32));$collision=false;
            foreach (array('info','billinfo','orders','check','reply','mapping') as $key) {
                if (dalo_portal1_paypal_rows($pdo,"SELECT username FROM {$tables[$key]} WHERE username=? LIMIT 1 FOR UPDATE",array($candidate))) {$collision=true;}
            }
            if (dalo_portal1_paypal_rows($pdo,"SELECT txnId FROM {$tables['orders']} WHERE txnId=? LIMIT 1 FOR UPDATE",array($token))) {$collision=true;}
            if (!$collision) {$pin=$candidate;$correlation=$token;break;}
        }
        if ($pin===null) {throw new RuntimeException('Registration identity unavailable');}
        $date=date('Y-m-d H:i:s');$creator='paypal-webinterface';
        $info=array('username'=>$pin,'firstname'=>$input['firstName'],'lastname'=>$input['lastName'],
            'address'=>$input['address'],'city'=>$input['city'],'state'=>$input['state'],'creationby'=>$creator);
        $bill=array('username'=>$pin,'planName'=>$plan['planName'],'contactperson'=>$input['firstName'].' '.$input['lastName'],
            'address'=>$input['address'],'city'=>$input['city'],'state'=>$input['state'],'creationby'=>$creator);
        $order=array('username'=>$pin,'txnId'=>$correlation,'vendor_type'=>'PayPal');
        foreach (array('info'=>$info,'billinfo'=>$bill,'orders'=>$order) as $key=>$values) {dalo_portal1_paypal_capacity($pdo,$tables[$key],$values);}
        $data=array('info'=>$info+array('creationdate'=>$date),'billinfo'=>$bill+array('creationdate'=>$date),
            'orders'=>$order+array('planId'=>$id,'payment_date'=>$date));
        foreach ($data as $key=>$values) {
            $insertId=dalo_portal2_paypal_insert($pdo,$tables[$key],$values);
            $stored=dalo_portal1_paypal_rows($pdo,"SELECT * FROM {$tables[$key]} WHERE id=?",array($insertId));
            if (count($stored)!==1) {throw new RuntimeException('Registration verification failed');}
            foreach ($values as $field=>$value) {
                if ((string)($stored[0][$field] ?? '')!==$value) {throw new RuntimeException('Registration verification failed');}
            }
        }
        if (!$pdo->commit()) {throw new RuntimeException('Registration commit unavailable');}
        return array('pin'=>$pin,'correlation'=>$correlation,'plan'=>$plan,'price'=>$price,'checkout'=>$checkout);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {try {$pdo->rollBack();} catch (Throwable $ignored) {}}
        if ($error instanceof InvalidArgumentException) {throw new InvalidArgumentException('Invalid registration');}
        throw new RuntimeException('Registration could not be completed');
    } finally {
        if ($locked) {try {dalo_portal1_paypal_rows($pdo,'SELECT RELEASE_LOCK(?)',array($lockName));} catch (Throwable $ignored) {}}
    }
}

/** Resume only when called with the correlation kept by the same browser session. */
function dalo_portal2_paypal_resume(PDO $pdo,$config,$correlation) {
    dalo_paypal_text($correlation,200,true);$orders=dalo_portal2_paypal_table($config,'orders');
    $rows=dalo_portal1_paypal_rows($pdo,"SELECT txnId,username,planId,vendor_type,payment_status,txn_type FROM $orders WHERE txnId=? ORDER BY id LIMIT 2",array($correlation));
    if (count($rows)!==1 || $rows[0]['txnId']!==$correlation || $rows[0]['vendor_type']!=='PayPal' ||
        $rows[0]['payment_status']!=='' || $rows[0]['txn_type']!=='') {return null;}
    dalo_paypal_text($rows[0]['username'],64,true);$id=dalo_portal2_paypal_id((string)$rows[0]['planId']);
    foreach (array('info','billinfo') as $key) {
        $table=dalo_portal2_paypal_table($config,$key);
        $account=dalo_portal1_paypal_rows($pdo,"SELECT username FROM $table WHERE username=? LIMIT 2",array($rows[0]['username']));
        if (count($account)!==1 || $account[0]['username']!==$rows[0]['username']) {
            throw new RuntimeException('Pending registration unavailable');
        }
    }
    $plans=dalo_portal2_paypal_table($config,'plans');
    $plan=dalo_portal1_paypal_rows($pdo,"SELECT id AS planId,planName,planCost,planTax,planCurrency,planRecurring,planRecurringPeriod FROM $plans WHERE id=? AND planType='PayPal' LIMIT 2",array($id));
    if (count($plan)!==1) {throw new InvalidArgumentException('Checkout plan unavailable');}
    return array('pin'=>$rows[0]['username'],'correlation'=>$correlation,'plan'=>$plan[0],
        'price'=>dalo_portal2_paypal_price($plan[0]),'checkout'=>dalo_portal2_paypal_checkout($config));
}

/** A receipt proves a recorded payment, not current subscription authorization. */
function dalo_portal2_paypal_receipt(PDO $pdo,$config,$correlation) {
    dalo_paypal_text($correlation,200,true);$table=dalo_portal2_paypal_table($config,'orders');
    $rows=dalo_portal1_paypal_rows($pdo,"SELECT txnId,username,planId,vendor_type,payment_status FROM $table WHERE txnId=? AND payment_status!='' ORDER BY id",array($correlation));
    $receipt=$rows[0] ?? null;
    foreach ($rows as $row) {
        if ($row['txnId']!==$correlation || $row['username']!==$rows[0]['username'] ||
            (string)$row['planId']!==(string)$rows[0]['planId'] || $row['vendor_type']!=='PayPal') {
            throw new InvalidArgumentException('Receipt unavailable or ambiguous');
        }
        if ($row['payment_status']==='Completed') {$receipt=$row;}
    }
    return $receipt;
}