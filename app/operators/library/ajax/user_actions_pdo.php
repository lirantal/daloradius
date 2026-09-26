<?php
/* UNIT-024: PDO user actions; refill history and invoice share one transaction. */

function dalo_user_action_table($config, $key) {
    $keys = array('CONFIG_DB_TBL_RADUSERGROUP', 'CONFIG_DB_TBL_RADACCT',
                  'CONFIG_DB_TBL_RADCHECK', 'CONFIG_DB_TBL_DALOUSERINFO',
                  'CONFIG_DB_TBL_DALOUSERBILLINFO', 'CONFIG_DB_TBL_DALOBILLINGPLANS',
                  'CONFIG_DB_TBL_DALOBILLINGHISTORY', 'CONFIG_DB_TBL_DALOBILLINGINVOICE',
                  'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS');
    $table = $config[$key] ?? null;
    if (!in_array($key, $keys, true) || !is_string($table) ||
        !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $table)) {
        throw new InvalidArgumentException('Invalid user-action table configuration');
    }
    return '`' . $table . '`';
}

function dalo_user_action_innodb(PDO $pdo, $tables) {
    $names = array_values(array_unique(array_map(function ($table) {
        return trim($table, '`');
    }, $tables)));
    $slots = implode(',', array_fill(0, count($names), '?'));
    $stmt = $pdo->prepare("SELECT TABLE_NAME,ENGINE FROM INFORMATION_SCHEMA.TABLES
                            WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ($slots)");
    $stmt->execute($names);
    $engines = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($names as $name) {
        if (!isset($engines[$name]) || strcasecmp($engines[$name], 'InnoDB') !== 0) {
            throw new RuntimeException('User action requires transactional tables');
        }
    }
}

function dalo_user_action_names($submitted, $maxLength = 128) {
    if (!is_string($submitted) && !is_array($submitted)) {
        throw new InvalidArgumentException('Invalid username selection');
    }
    $input = is_array($submitted) ? $submitted : array($submitted);
    $inputLimit = (int) ini_get('max_input_vars');
    // Reject at PHP's parser limit: otherwise a truncated selection can succeed.
    if (!$input || count($input) > 1000 ||
        ($inputLimit > 0 && count($input) >= $inputLimit - 2)) {
        throw new InvalidArgumentException('Invalid username selection size');
    }
    $names = array();
    foreach ($input as $name) {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Invalid username');
        }
        $name = trim($name);
        if ($name === '' || strlen($name) > 512 || strpos($name, "\0") !== false ||
            ($characters = preg_match_all('/./us', $name)) === false || $characters > $maxLength) {
            throw new InvalidArgumentException('Invalid username');
        }
        $names[$name] = $name;
    }
    return array_values($names);
}

function dalo_user_action_disabled(PDO $pdo, $config, $name, $disabledGroup) {
    $table = dalo_user_action_table($config, 'CONFIG_DB_TBL_RADUSERGROUP');
    $stmt = $pdo->prepare("SELECT 1 FROM $table WHERE username=:username
                           AND groupname=:groupname LIMIT 1");
    $stmt->execute(array(':username' => $name, ':groupname' => $disabledGroup));
    return $stmt->fetchColumn() !== false;
}

/** Enable/disable the entire selection, or none of it on failure. */
function dalo_user_action_toggle(PDO $pdo, $config, $names, $disabledGroup, $disable) {
    $table = dalo_user_action_table($config, 'CONFIG_DB_TBL_RADUSERGROUP');
    dalo_user_action_innodb($pdo, array($table));
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Cannot begin user action');
    }
    try {
        $sorted = $names;
        sort($sorted, SORT_STRING);
        $existing = $pdo->prepare("SELECT id FROM $table WHERE username=:username
                                   AND groupname=:groupname ORDER BY id FOR UPDATE");
        $remove = $pdo->prepare("DELETE FROM $table WHERE username=:username
                                 AND groupname=:groupname");
        $insert = $pdo->prepare("INSERT INTO $table (username,groupname,priority)
                                 VALUES (:username,:groupname,-1)");
        $missing = array();
        foreach ($sorted as $name) {
            $values = array(':username' => $name, ':groupname' => $disabledGroup);
            $existing->execute($values);
            $found = $existing->fetchColumn() !== false;
            $existing->closeCursor();
            if (!$found) {
                $missing[$name] = true;
            }
        }
        if ($disable) {
            foreach ($sorted as $name) {
                if (isset($missing[$name])) {
                    $insert->execute(array(':username' => $name, ':groupname' => $disabledGroup));
                }
            }
        } else {
            foreach ($sorted as $name) {
                $remove->execute(array(':username' => $name, ':groupname' => $disabledGroup));
            }
        }
        if (!$pdo->commit()) {
            throw new RuntimeException('User action was not committed');
        }
        return array_values(array_filter($names, function ($name) use ($missing) {
            return isset($missing[$name]);
        }));
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

/** Persist a refill's history and optional invoice on the same PDO transaction. */
function dalo_user_action_refill(PDO $pdo, $config, $names, $action, $operator) {
    if (!in_array($action, array('refillSessionTime','refillSessionTraffic'), true) ||
        !is_string($operator) || $operator === '') {
        throw new InvalidArgumentException('Invalid refill action');
    }
    $keys = array('CONFIG_DB_TBL_RADACCT','CONFIG_DB_TBL_DALOUSERBILLINFO',
                  'CONFIG_DB_TBL_DALOBILLINGPLANS','CONFIG_DB_TBL_DALOBILLINGHISTORY',
                  'CONFIG_DB_TBL_DALOBILLINGINVOICE','CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS');
    $tables = array();
    foreach ($keys as $key) {
        $tables[$key] = dalo_user_action_table($config, $key);
    }
    dalo_user_action_innodb($pdo, array_values($tables));
    $acct = $tables['CONFIG_DB_TBL_RADACCT'];
    $billing = $tables['CONFIG_DB_TBL_DALOUSERBILLINFO'];
    $plans = $tables['CONFIG_DB_TBL_DALOBILLINGPLANS'];
    $history = $tables['CONFIG_DB_TBL_DALOBILLINGHISTORY'];
    $invoice = $tables['CONFIG_DB_TBL_DALOBILLINGINVOICE'];
    $items = $tables['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'];
    $isTime = $action === 'refillSessionTime';
    $costField = $isTime ? 'planTimeRefillCost' : 'planTrafficRefillCost';
    $billAction = $isTime ? 'Refill Session Time' : 'Refill Session Traffic';
    $itemNotes = $isTime ? 'refill user session time' : 'refill user session traffic';
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Cannot begin refill');
    }
    try {
        $sorted = $names;
        sort($sorted, SORT_STRING);
        $billRows = array();
        $select = $pdo->prepare("SELECT ubi.id,ubi.username,bp.id AS PlanID,
                 bp.`$costField` AS cost,bp.planTax,ubi.paymentmethod,ubi.cash,
                 ubi.creditcardname,ubi.creditcardnumber,ubi.creditcardverification,
                 ubi.creditcardtype,ubi.creditcardexp
                 FROM $billing AS ubi JOIN $plans AS bp ON ubi.planName=bp.planName
                 WHERE ubi.username=:username ORDER BY ubi.id LIMIT 1 FOR UPDATE");
        foreach ($sorted as $name) {
            $select->execute(array(':username' => $name));
            $row = $select->fetch(PDO::FETCH_ASSOC);
            $select->closeCursor();
            if ($row) {
                $cost = $row['cost'];
                $tax = $row['planTax'];
                if (($cost !== null && $cost !== '' && !is_numeric($cost)) ||
                    ($tax !== null && $tax !== '' && !is_numeric($tax))) {
                    throw new InvalidArgumentException('Invalid refill amount or tax');
                }
                $billRows[$name] = $row;
            }
        }
        $slots = implode(',', array_fill(0, count($names), '?'));
        $set = $isTime ? 'AcctSessionTime=0' : 'AcctInputOctets=0,AcctOutputOctets=0';
        $reset = $pdo->prepare("UPDATE $acct SET $set WHERE Username IN ($slots)");
        $reset->execute($names);
        $now = date('Y-m-d H:i:s');
        $writeHistory = $pdo->prepare("INSERT INTO $history
            (username,planId,billAmount,billAction,billPerformer,billReason,
             paymentmethod,cash,creditcardname,creditcardnumber,
             creditcardverification,creditcardtype,creditcardexp,creationdate,creationby)
            VALUES (:username,:plan,:amount,:action,:performer,:reason,
                    :paymentmethod,:cash,:creditcardname,:creditcardnumber,
                    :creditcardverification,:creditcardtype,:creditcardexp,:created,:creator)");
        $writeInvoice = $pdo->prepare("INSERT INTO $invoice
            (user_id,date,status_id,type_id,notes,creationdate,creationby,updatedate,updateby)
            VALUES (:user_id,:date,1,1,:notes,:created,:creator,NULL,NULL)");
        $writeItem = $pdo->prepare("INSERT INTO $items
            (invoice_id,plan_id,amount,tax_amount,notes,creationdate,creationby,updatedate,updateby)
            VALUES (:invoice_id,:plan_id,:amount,:tax,:notes,:created,:creator,NULL,NULL)");
        $historyCount = 0;
        $invoiceCount = 0;
        foreach ($names as $name) {
            if (!isset($billRows[$name])) {
                continue;
            }
            $row = $billRows[$name];
            $amount = (string) ($row['cost'] ?? '');
            $values = array(':username' => $row['username'], ':plan' => $row['PlanID'],
                ':amount' => $amount, ':action' => $billAction,
                ':performer' => 'daloRADIUS Web Interface', ':reason' => $billAction,
                ':created' => $now, ':creator' => $operator);
            foreach (array('paymentmethod','cash','creditcardname','creditcardnumber',
                           'creditcardverification','creditcardtype','creditcardexp') as $field) {
                // Legacy interpolation wrote an empty string for a NULL payment field.
                $values[':' . $field] = (string) ($row[$field] ?? '');
            }
            $writeHistory->execute($values);
            $historyCount++;
            if ((float) $row['cost'] <= 0) {
                continue; // Free refill: history only, as before.
            }
            $writeInvoice->execute(array(':user_id' => $row['id'], ':date' => $now,
                ':notes' => 'refill user account', ':created' => $now, ':creator' => $operator));
            $invoiceId = (int) $pdo->lastInsertId();
            if ($invoiceId < 1) {
                throw new RuntimeException('No invoice ID returned');
            }
            $writeItem->execute(array(':invoice_id' => $invoiceId, ':plan_id' => $row['PlanID'],
                ':amount' => $amount, ':tax' => (string) ((float) $amount * ((float) $row['planTax'] / 100)),
                ':notes' => $itemNotes, ':created' => $now, ':creator' => $operator));
            $invoiceCount++;
        }
        if (!$pdo->commit()) {
            throw new RuntimeException('Refill was not committed');
        }
        return array('history' => $historyCount, 'invoices' => $invoiceCount);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

/** Read-only recipient lookup; SMTP side effects cannot be rolled back. */
function dalo_user_action_mail_rows(PDO $pdo, $config, $names) {
    $check = dalo_user_action_table($config, 'CONFIG_DB_TBL_RADCHECK');
    $info = dalo_user_action_table($config, 'CONFIG_DB_TBL_DALOUSERINFO');
    $slots = implode(',', array_fill(0, count($names), '?'));
    $stmt = $pdo->prepare("SELECT credentials.username,credentials.value,person.email,
                          person.firstname,person.lastname FROM $check AS credentials
                          JOIN $info AS person ON credentials.username=person.username
                          WHERE credentials.username IN ($slots)");
    $stmt->execute($names);
    return $stmt->fetchAll(PDO::FETCH_NUM);
}
