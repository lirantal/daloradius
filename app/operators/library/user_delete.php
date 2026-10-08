<?php
/* UNIT-019: operator user, attribute and session removals on caller-owned PDO. */
require_once __DIR__ . '/pos_delete.php';

function dalo_user_delete_table($config, $key) {
    if ($key === 'CONFIG_DB_TBL_RADPOSTAUTH') {
        if (!isset($config[$key]) || !is_string($config[$key]) ||
            !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $config[$key])) {
            throw new InvalidArgumentException('Invalid postauth table');
        }
        return '`' . $config[$key] . '`';
    }
    if ($key === 'CONFIG_DB_TBL_RADACCT') {
        return dalo_pos_delete_table($config, $key);
    }
    if (in_array($key, array('CONFIG_DB_TBL_DALOBILLINGINVOICE',
                            'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS',
                            'CONFIG_DB_TBL_DALOPAYMENTS'), true)) {
        return dalo_invoice_table($config, $key);
    }
    return dalo_pos_table($config, $key);
}

/** Refuse to claim transactional deletion for an absent/non-InnoDB table. */
function dalo_user_delete_require_innodb(PDO $pdo, $tables) {
    $names = array_values(array_unique(array_map(function ($table) {
        return trim($table, '`');
    }, $tables)));
    $slots = implode(',', array_fill(0, count($names), '?'));
    $stmt = $pdo->prepare("SELECT TABLE_NAME, ENGINE FROM INFORMATION_SCHEMA.TABLES
                            WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ($slots)");
    $stmt->execute($names);
    $engines = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($names as $name) {
        if (!isset($engines[$name]) || strcasecmp($engines[$name], 'InnoDB') !== 0) {
            throw new RuntimeException('Deletion requires transactional tables');
        }
    }
}

/** A scalar or flat array from the operator's selection, preserving literal percent/plus. */
function dalo_user_delete_selection($submitted, $max = 1000) {
    if (!is_string($submitted) && !is_array($submitted)) {
        throw new InvalidArgumentException('Invalid selection');
    }
    $input = is_array($submitted) ? array_values($submitted) : array($submitted);
    if (!$input || count($input) > $max) {
        throw new InvalidArgumentException('Invalid selection size');
    }
    $users = array();
    foreach ($input as $raw) {
        if (!is_string($raw) || $raw === '' || $raw !== trim($raw) || strlen($raw) > 128 ||
            strpos($raw, "\0") !== false) {
            throw new InvalidArgumentException('Invalid selected username');
        }
        $users[$raw] = $raw;
    }
    $users = array_values($users);
    sort($users, SORT_STRING);
    return $users;
}

function dalo_user_delete_where($column, $count) {
    $slots = implode(',', array_fill(0, $count, '?'));
    return "$column IN ($slots) AND BINARY $column IN ($slots)";
}

function dalo_user_delete_bind_selection($users) {
    return array_merge($users, $users);
}

/** Delete all selected accounts and dependencies or leave every row unchanged. */
function dalo_user_delete_accounts(PDO $pdo, $config, $users, $deleteAccounting) {
    if (!is_array($users) || !$users || !is_bool($deleteAccounting)) {
        throw new InvalidArgumentException('Invalid account removal');
    }
    $keys = array('CONFIG_DB_TBL_RADPOSTAUTH', 'CONFIG_DB_TBL_RADCHECK',
        'CONFIG_DB_TBL_RADREPLY', 'CONFIG_DB_TBL_DALOUSERINFO',
        'CONFIG_DB_TBL_DALOUSERBILLINFO', 'CONFIG_DB_TBL_RADUSERGROUP',
        'CONFIG_DB_TBL_DALOBILLINGINVOICE', 'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS',
        'CONFIG_DB_TBL_DALOPAYMENTS');
    if ($deleteAccounting) {
        $keys[] = 'CONFIG_DB_TBL_RADACCT';
    }
    $tables = array();
    foreach ($keys as $key) {
        $tables[$key] = dalo_user_delete_table($config, $key);
    }
    dalo_user_delete_require_innodb($pdo, array_values($tables));
    $where = dalo_user_delete_where('username', count($users));
    $values = dalo_user_delete_bind_selection($users);
    $check = $tables['CONFIG_DB_TBL_RADCHECK'];
    $billing = $tables['CONFIG_DB_TBL_DALOUSERBILLINFO'];
    $invoices = $tables['CONFIG_DB_TBL_DALOBILLINGINVOICE'];
    $items = $tables['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'];
    $payments = $tables['CONFIG_DB_TBL_DALOPAYMENTS'];
    $postauth = $tables['CONFIG_DB_TBL_RADPOSTAUTH'];
    $postauthKey = (isset($config['FREERADIUS_VERSION']) && $config['FREERADIUS_VERSION'] === '1')
                 ? '`user`' : '`username`';
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start account deletion');
    }
    try {
        $select = $pdo->prepare("SELECT id,username FROM $check WHERE $where ORDER BY username,id FOR UPDATE");
        $select->execute($values);
        $found = array();
        foreach ($select->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $found[$row['username']] = true;
        }
        $select->closeCursor();
        $actual = array_keys($found);
        sort($actual, SORT_STRING);
        if ($actual !== $users) {
            throw new DomainException('A selected user is missing');
        }
        $select = $pdo->prepare("SELECT id FROM $billing WHERE $where ORDER BY id FOR UPDATE");
        $select->execute($values);
        $billingIds = $select->fetchAll(PDO::FETCH_COLUMN);
        $select->closeCursor();
        $invoiceIds = array();
        $select = $pdo->prepare("SELECT id FROM $invoices WHERE user_id=:id ORDER BY id FOR UPDATE");
        foreach ($billingIds as $id) {
            $select->execute(array(':id' => $id));
            foreach ($select->fetchAll(PDO::FETCH_COLUMN) as $invoiceId) {
                $invoiceIds[(int) $invoiceId] = (int) $invoiceId;
            }
            $select->closeCursor();
        }
        ksort($invoiceIds, SORT_NUMERIC);
        $deletePayments = $pdo->prepare("DELETE FROM $payments WHERE invoice_id=:id");
        $deleteItems = $pdo->prepare("DELETE FROM $items WHERE invoice_id=:id");
        $deleteInvoice = $pdo->prepare("DELETE FROM $invoices WHERE id=:id");
        foreach ($invoiceIds as $id) {
            $params = array(':id' => $id);
            $deletePayments->execute($params);
            $deleteItems->execute($params);
            $deleteInvoice->execute($params);
        }
        $postWhere = dalo_user_delete_where($postauthKey, count($users));
        $delete = $pdo->prepare("DELETE FROM $postauth WHERE $postWhere");
        $delete->execute($values);
        $byUsername = array('CONFIG_DB_TBL_RADUSERGROUP','CONFIG_DB_TBL_RADREPLY',
            'CONFIG_DB_TBL_DALOUSERINFO','CONFIG_DB_TBL_DALOUSERBILLINFO',
            'CONFIG_DB_TBL_RADCHECK');
        if ($deleteAccounting) {
            array_unshift($byUsername, 'CONFIG_DB_TBL_RADACCT');
        }
        foreach ($byUsername as $key) {
            $delete = $pdo->prepare("DELETE FROM {$tables[$key]} WHERE $where");
            $delete->execute($values);
        }
        if (!$pdo->commit()) {
            throw new RuntimeException('Could not commit account deletion');
        }
        return count($users);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

/** Delete one owned attribute, retaining at least one check/auth attribute. */
function dalo_user_delete_attribute(PDO $pdo, $config, $username, $idAttribute, $tableKind) {
    $users = dalo_user_delete_selection($username, 1);
    if (count($users) !== 1 || !is_string($idAttribute) ||
        !preg_match('/^([1-9][0-9]{0,9})__(.+)$/D', $idAttribute, $parts) ||
        !in_array($tableKind, array('radcheck','radreply'), true)) {
        throw new InvalidArgumentException('Invalid attribute removal');
    }
    $id = (int) $parts[1];
    $attribute = $parts[2];
    if (strlen($attribute) > 128) {
        throw new InvalidArgumentException('Invalid attribute');
    }
    $check = dalo_user_delete_table($config, 'CONFIG_DB_TBL_RADCHECK');
    $table = dalo_user_delete_table($config,
        $tableKind === 'radcheck' ? 'CONFIG_DB_TBL_RADCHECK' : 'CONFIG_DB_TBL_RADREPLY');
    dalo_user_delete_require_innodb($pdo, array($check, $table));
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start attribute deletion');
    }
    try {
        $select = $pdo->prepare("SELECT id,attribute FROM $check WHERE username=:username ORDER BY id FOR UPDATE");
        $select->execute(array(':username' => $users[0]));
        $checks = $select->fetchAll(PDO::FETCH_ASSOC);
        $select->closeCursor();
        if (!$checks) {
            throw new DomainException('User no longer exists');
        }
        $select = $pdo->prepare("SELECT attribute FROM $table WHERE id=:id AND username=:username FOR UPDATE");
        $select->execute(array(':id' => $id, ':username' => $users[0]));
        $stored = $select->fetchColumn();
        $select->closeCursor();
        if ($stored === false || $stored !== $attribute) {
            throw new DomainException('Attribute no longer belongs to this user');
        }
        if ($tableKind === 'radcheck') {
            $auth = 0;
            foreach ($checks as $row) {
                if ($row['attribute'] === 'Auth-Type' || preg_match('/-Password$/D', $row['attribute'])) {
                    $auth++;
                }
            }
            if (count($checks) <= 1 ||
                ($auth <= 1 && ($attribute === 'Auth-Type' || preg_match('/-Password$/D', $attribute)))) {
                throw new DomainException('Cannot delete the last check (password-like) attribute');
            }
        }
        $delete = $pdo->prepare("DELETE FROM $table WHERE id=:id AND username=:username AND attribute=:attribute");
        $delete->execute(array(':id' => $id, ':username' => $users[0], ':attribute' => $attribute));
        if ($delete->rowCount() !== 1 || !$pdo->commit()) {
            throw new RuntimeException('Attribute deletion failed');
        }
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

/** Parse the report's username||starttime selections before the first delete. */
function dalo_user_delete_sessions_selection($submitted) {
    if (!is_string($submitted) && !is_array($submitted)) {
        throw new InvalidArgumentException('Invalid session selection');
    }
    $input = is_array($submitted) ? array_values($submitted) : array($submitted);
    if (!$input || count($input) > 1000) {
        throw new InvalidArgumentException('Invalid session selection');
    }
    $selected = array();
    foreach ($input as $value) {
        if (!is_string($value) || strlen($value) > 256) {
            throw new InvalidArgumentException('Invalid session entry');
        }
        $split = strrpos($value, '||');
        if ($split === false) {
            throw new InvalidArgumentException('Invalid session entry');
        }
        $username = substr($value, 0, $split);
        $datetime = substr($value, $split + 2);
        dalo_user_delete_selection($username, 1);
        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/D', $datetime)) {
            throw new InvalidArgumentException('Invalid session start time');
        }
        $selected[$username . "\0" . $datetime] = array($username, $datetime);
    }
    ksort($selected, SORT_STRING);
    return array_values($selected);
}

function dalo_user_delete_sessions(PDO $pdo, $config, $selection) {
    if (!is_array($selection) || !$selection) {
        throw new InvalidArgumentException('Invalid session selection');
    }
    $accounting = dalo_user_delete_table($config, 'CONFIG_DB_TBL_RADACCT');
    dalo_user_delete_require_innodb($pdo, array($accounting));
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start session cleanup');
    }
    try {
        $ids = array();
        $select = $pdo->prepare("SELECT radacctid FROM $accounting WHERE username=:username AND
            AcctStartTime=:started AND (AcctStopTime='0000-00-00 00:00:00' OR AcctStopTime IS NULL)
            ORDER BY radacctid FOR UPDATE");
        foreach ($selection as $entry) {
            $select->execute(array(':username' => $entry[0], ':started' => $entry[1]));
            $found = $select->fetchAll(PDO::FETCH_COLUMN);
            $select->closeCursor();
            if (!$found) {
                throw new DomainException('A selected open session no longer exists');
            }
            foreach ($found as $id) {
                $ids[(int) $id] = (int) $id;
            }
        }
        ksort($ids, SORT_NUMERIC);
        $delete = $pdo->prepare("DELETE FROM $accounting WHERE radacctid=:id");
        foreach ($ids as $id) {
            $delete->execute(array(':id' => $id));
        }
        if (!$pdo->commit()) {
            throw new RuntimeException('Could not commit session cleanup');
        }
        return count($ids);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}
