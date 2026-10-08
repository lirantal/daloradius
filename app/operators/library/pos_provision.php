<?php
/* UNIT-011: POS provisioning is one caller-owned PDO transaction. */
require_once __DIR__ . '/plan_create.php';

function dalo_pos_table($config, $key) {
    $allowed = array('CONFIG_DB_TBL_RADCHECK', 'CONFIG_DB_TBL_RADREPLY',
        'CONFIG_DB_TBL_RADUSERGROUP', 'CONFIG_DB_TBL_DALOUSERINFO',
        'CONFIG_DB_TBL_DALOUSERBILLINFO', 'CONFIG_DB_TBL_DALOBILLINGPLANS',
        'CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES', 'CONFIG_DB_TBL_DALOBILLINGINVOICE',
        'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS');
    if (!in_array($key, $allowed, true) || !isset($config[$key]) ||
        !is_string($config[$key]) ||
        !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $config[$key])) {
        throw new InvalidArgumentException('Invalid POS table configuration');
    }
    return '`' . $config[$key] . '`';
}

/** Keep the legacy four-slot attribute form, but never let a request name a table. */
function dalo_pos_attributes_from_post($post, $skip, $validOps) {
    $attributes = array();
    foreach ($post as $key => $field) {
        if (in_array($key, $skip, true) || !is_array($field)) {
            continue;
        }
        if (count($field) !== 4 || array_keys($field) !== array(0, 1, 2, 3)) {
            throw new InvalidArgumentException('Invalid RADIUS attribute');
        }
        foreach ($field as $value) {
            if (!is_string($value)) {
                throw new InvalidArgumentException('Invalid RADIUS attribute field');
            }
        }
        list($attribute, $value, $op, $table) = array_map('trim', $field);
        if (strpos($attribute, '__') !== false) {
            $attribute = trim(explode('__', $attribute, 2)[1]);
        }
        if ($attribute === '' || $value === '' || !in_array($op, $validOps, true)) {
            continue;
        }
        if (is_passwordlike_attribute($attribute)) {
            if (!dalo_cleartext_password_allowed() &&
                in_array($attribute, dalo_cleartext_password_attributes(), true)) {
                continue;
            }
            $value = hashPasswordAttribute($attribute, $value);
            if (!is_string($value)) {
                throw new InvalidArgumentException('Invalid RADIUS password');
            }
        }
        $attributes[] = array('table' => stripos($table, 'reply') !== false
                                       ? 'CONFIG_DB_TBL_RADREPLY' : 'CONFIG_DB_TBL_RADCHECK',
                              'attribute' => $attribute, 'op' => $op, 'value' => $value);
    }
    return $attributes;
}

/** Insert a fixed set of columns with bound values; no request-provided identifiers. */
function dalo_pos_insert(PDO $pdo, $table, $data, $allowedColumns) {
    $columns = array();
    $slots = array();
    $params = array();
    foreach ($data as $column => $value) {
        if (!in_array($column, $allowedColumns, true) || !is_string($value)) {
            throw new InvalidArgumentException('Invalid POS field');
        }
        $columns[] = '`' . $column . '`';
        $slots[] = ':' . $column;
        $params[':' . $column] = $value;
    }
    $statement = $pdo->prepare("INSERT INTO $table (" . implode(', ', $columns) .
                               ') VALUES (' . implode(', ', $slots) . ')');
    $statement->execute($params);
}

/** Return [attribute count, manual group count, billing id or null, invoice id or null]. */
function dalo_pos_provision(PDO $pdo, $config, $username, $planName, $manualProfiles,
                            $attributes, $userInfo, $billing, $created, $operator) {
    if (!is_string($username) || $username === '' || strlen($username) > 128 ||
        !is_string($planName) || strlen($planName) > 128) {
        throw new InvalidArgumentException('Invalid POS user or plan');
    }
    $checkTable = dalo_pos_table($config, 'CONFIG_DB_TBL_RADCHECK');
    $replyTable = dalo_pos_table($config, 'CONFIG_DB_TBL_RADREPLY');
    $groupsTable = dalo_pos_table($config, 'CONFIG_DB_TBL_RADUSERGROUP');
    $userTable = dalo_pos_table($config, 'CONFIG_DB_TBL_DALOUSERINFO');
    $billingTable = dalo_pos_table($config, 'CONFIG_DB_TBL_DALOUSERBILLINFO');
    $plansTable = dalo_pos_table($config, 'CONFIG_DB_TBL_DALOBILLINGPLANS');
    $mappingTable = dalo_pos_table($config, 'CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES');
    $invoiceTable = dalo_pos_table($config, 'CONFIG_DB_TBL_DALOBILLINGINVOICE');
    $itemsTable = dalo_pos_table($config, 'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS');
    $userFields = array('firstname','lastname','email','department','company','workphone',
        'homephone','mobilephone','address','city','state','country','zip','notes',
        'changeuserinfo','enableportallogin','portalloginpassword');
    $billingFields = array('contactperson','company','email','phone','address','city','state',
        'country','zip','paymentmethod','cash','creditcardname','creditcardnumber',
        'creditcardverification','creditcardtype','creditcardexp','notes','changeuserbillinfo',
        'lead','coupon','ordertaker','billstatus','lastbill','nextbill','nextinvoicedue',
        'billdue','postalinvoice','faxinvoice','emailinvoice');
    foreach ($userFields as $field) {
        if (!isset($userInfo[$field]) || !is_string($userInfo[$field])) {
            throw new InvalidArgumentException('Invalid POS user info');
        }
    }
    foreach ($billingFields as $field) {
        if (!isset($billing[$field]) || !is_string($billing[$field])) {
            throw new InvalidArgumentException('Invalid POS billing info');
        }
    }
    if (!is_array($manualProfiles) || !is_array($attributes)) {
        throw new InvalidArgumentException('Invalid POS profiles or attributes');
    }
    if ($userInfo['portalloginpassword'] !== '') {
        $userInfo['portalloginpassword'] = dalo_portal_password_hash($userInfo['portalloginpassword']);
        if (!is_string($userInfo['portalloginpassword'])) {
            throw new RuntimeException('Could not hash portal password');
        }
    } else {
        unset($userInfo['portalloginpassword']);
    }
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start POS provisioning');
    }
    try {
        // Recheck on the write connection. The legacy PEAR read is not a lock.
        foreach (array($checkTable, $userTable, $billingTable) as $table) {
            $check = $pdo->prepare("SELECT id FROM $table WHERE username = :username LIMIT 1 FOR UPDATE");
            $check->execute(array(':username' => $username));
            if ($check->fetchColumn() !== false) {
                throw new DomainException('User already exists');
            }
            $check->closeCursor();
        }
        $plan = null;
        if ($planName !== '') {
            $check = $pdo->prepare("SELECT id, planCost, planSetupCost, planTax,
                planRecurring, planRecurringPeriod, planRecurringBillingSchedule
                FROM $plansTable WHERE planName = :name AND planActive = 'yes' LIMIT 1 FOR UPDATE");
            $check->execute(array(':name' => $planName));
            $plan = $check->fetch(PDO::FETCH_ASSOC);
            $check->closeCursor();
            if (!$plan) {
                throw new InvalidArgumentException('Invalid or inactive plan');
            }
        }
        dalo_validate_plan_profiles($pdo, $config, $manualProfiles);
        $attributeCount = 0;
        foreach ($attributes as $attribute) {
            $table = $attribute['table'] === 'CONFIG_DB_TBL_RADREPLY' ? $replyTable : $checkTable;
            $insert = $pdo->prepare("INSERT INTO $table (username, attribute, op, value)
                                     VALUES (:username, :attribute, :op, :value)");
            $insert->execute(array(':username' => $username, ':attribute' => $attribute['attribute'],
                                   ':op' => $attribute['op'], ':value' => $attribute['value']));
            $attributeCount++;
        }
        if ($attributeCount === 0) {
            throw new InvalidArgumentException('No RADIUS check attribute');
        }
        $manualInsert = $pdo->prepare("INSERT INTO $groupsTable (username, groupname, priority)
                                       VALUES (:username, :groupname, :priority)");
        foreach ($manualProfiles as $group) {
            $manualInsert->execute(array(':username' => $username, ':groupname' => $group,
                                         ':priority' => normalize_user_group_priority($group, 0)));
        }
        if ($plan) {
            $mapped = $pdo->prepare("SELECT profile_name FROM $mappingTable WHERE plan_name = :name FOR UPDATE");
            $mapped->execute(array(':name' => $planName));
            $planProfiles = $mapped->fetchAll(PDO::FETCH_COLUMN);
            $mapped->closeCursor();
            foreach ($planProfiles as $group) {
                // Preserve the plan mapping priority even for the reserved group.
                $manualInsert->execute(array(':username' => $username, ':groupname' => $group,
                                             ':priority' => 0));
            }
        }
        $userInfo['username'] = $username;
        $userInfo['creationdate'] = $created;
        $userInfo['creationby'] = $operator;
        dalo_pos_insert($pdo, $userTable, $userInfo,
                        array_merge($userFields, array('username', 'creationdate', 'creationby')));
        $billing['username'] = $username;
        $billing['planName'] = $planName;
        $billing['creationdate'] = $created;
        $billing['creationby'] = $operator;
        $billing['nextbill'] = $billing['nextbill'] === '' ? '0000-00-00' : $billing['nextbill'];
        if ($plan && $plan['planRecurring'] === 'Yes' && $billing['nextbill'] === '0000-00-00') {
            $billing['nextbill'] = getNextBillingDate($plan['planRecurringBillingSchedule'], $plan['planRecurringPeriod']);
        }
        dalo_pos_insert($pdo, $billingTable, $billing,
                        array_merge($billingFields, array('username', 'planName', 'creationdate', 'creationby')));
        $billingId = (int) $pdo->lastInsertId();
        if ($billingId < 1) {
            throw new RuntimeException('No billing ID returned');
        }
        $invoiceId = null;
        if ($plan) {
            $planId = (int) $plan['id'];
            $cost = is_numeric($plan['planCost']) ? (float) $plan['planCost'] : 0.0;
            $tax = is_numeric($plan['planTax']) ? (float) $plan['planTax'] : 0.0;
            $items = array(array('amount' => $cost, 'tax' => $cost * ($tax / 100),
                                 'notes' => 'charge for plan service'));
            if ($plan['planSetupCost'] !== null && $plan['planSetupCost'] !== '') {
                $setup = is_numeric($plan['planSetupCost']) ? (float) $plan['planSetupCost'] : 0.0;
                $items[] = array('amount' => $setup, 'tax' => $setup * ($tax / 100),
                                 'notes' => 'charge for plan setup fee (one time)');
            }
            dalo_pos_insert($pdo, $invoiceTable, array('user_id' => (string) $billingId,
                'date' => $created, 'status_id' => '1', 'type_id' => '1',
                'notes' => 'provisioned new user from daloRADIUS platform',
                'creationdate' => $created, 'creationby' => $operator),
                array('user_id', 'date', 'status_id', 'type_id', 'notes', 'creationdate', 'creationby'));
            $invoiceId = (int) $pdo->lastInsertId();
            if ($invoiceId < 1) {
                throw new RuntimeException('No invoice ID returned');
            }
            foreach ($items as $item) {
                dalo_pos_insert($pdo, $itemsTable, array('invoice_id' => (string) $invoiceId,
                    'plan_id' => (string) $planId, 'amount' => (string) $item['amount'],
                    'tax_amount' => (string) $item['tax'], 'notes' => $item['notes'],
                    'creationdate' => $created, 'creationby' => $operator),
                    array('invoice_id', 'plan_id', 'amount', 'tax_amount', 'notes', 'creationdate', 'creationby'));
            }
        }
        $pdo->commit();
        return array($attributeCount, count($manualProfiles), $billingId, $invoiceId);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}
