<?php
/* UNIT-015: delete complete batches and their users on one PDO transaction. */
require_once __DIR__ . '/batch_create.php';
require_once __DIR__ . '/invoice_create.php';

function dalo_batch_delete_ids($input) {
    if ($input === null || $input === '') {
        return array();
    }
    $values = is_array($input) ? $input : array($input);
    $ids = array();
    foreach ($values as $value) {
        if (!is_string($value) || !ctype_digit($value) || (int) $value < 1 ||
            (int) $value > 2147483647 || (string) (int) $value !== $value) {
            throw new InvalidArgumentException('Invalid batch ID');
        }
        $ids[(int) $value] = (int) $value;
    }
    $ids = array_values($ids);
    sort($ids, SORT_NUMERIC);
    return $ids;
}

function dalo_batch_delete_radius_table($config, $key) {
    if (!in_array($key, array('CONFIG_DB_TBL_RADACCT',
                              'CONFIG_DB_TBL_RADPOSTAUTH'), true) ||
        !isset($config[$key]) || !is_string($config[$key]) ||
        !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $config[$key])) {
        throw new InvalidArgumentException('Invalid batch RADIUS table');
    }
    return '`' . $config[$key] . '`';
}

/** Return [deleted batch rows, distinct usernames]; rollback every batch on failure. */
function dalo_delete_user_batches(PDO $pdo, $config, $ids, $name) {
    if (!is_array($ids) || !is_string($name) || strlen($name) > 64) {
        throw new InvalidArgumentException('Invalid batch selection');
    }
    $batchTable = dalo_batch_table($config, 'CONFIG_DB_TBL_DALOBATCHHISTORY');
    $billingTable = dalo_pos_table($config, 'CONFIG_DB_TBL_DALOUSERBILLINFO');
    $userTable = dalo_pos_table($config, 'CONFIG_DB_TBL_DALOUSERINFO');
    $checkTable = dalo_pos_table($config, 'CONFIG_DB_TBL_RADCHECK');
    $replyTable = dalo_pos_table($config, 'CONFIG_DB_TBL_RADREPLY');
    $groupsTable = dalo_pos_table($config, 'CONFIG_DB_TBL_RADUSERGROUP');
    $acctTable = dalo_batch_delete_radius_table($config, 'CONFIG_DB_TBL_RADACCT');
    $postauthTable = dalo_batch_delete_radius_table($config, 'CONFIG_DB_TBL_RADPOSTAUTH');
    $postauthUser = isset($config['FREERADIUS_VERSION']) &&
                    (string) $config['FREERADIUS_VERSION'] === '1' ? '`user`' : '`username`';
    $invoiceTable = dalo_invoice_table($config, 'CONFIG_DB_TBL_DALOBILLINGINVOICE');
    $itemsTable = dalo_invoice_table($config, 'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS');
    $paymentsTable = dalo_invoice_table($config, 'CONFIG_DB_TBL_DALOPAYMENTS');
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start batch deletion');
    }
    try {
        $selected = array_fill_keys($ids, true);
        if ($name !== '') {
            $byName = $pdo->prepare("SELECT id FROM $batchTable WHERE batch_name=:name FOR UPDATE");
            $byName->execute(array(':name' => $name));
            $matches = $byName->fetchAll(PDO::FETCH_COLUMN);
            $byName->closeCursor();
            if (count($matches) !== 1) {
                throw new InvalidArgumentException('Missing or ambiguous batch name');
            }
            $selected[(int) $matches[0]] = true;
        }
        $ids = array_keys($selected);
        sort($ids, SORT_NUMERIC);
        if (!$ids) {
            throw new InvalidArgumentException('No batches selected');
        }
        $selectBatch = $pdo->prepare("SELECT id FROM $batchTable WHERE id=:id FOR UPDATE");
        $selectBilling = $pdo->prepare("SELECT id,username FROM $billingTable WHERE batch_id=:id ORDER BY id FOR UPDATE");
        $billingIds = array();
        $usernames = array();
        foreach ($ids as $id) {
            $selectBatch->execute(array(':id' => $id));
            $found = $selectBatch->fetchColumn() !== false;
            $selectBatch->closeCursor();
            if (!$found) {
                throw new InvalidArgumentException('Batch no longer exists');
            }
            $selectBilling->execute(array(':id' => $id));
            foreach ($selectBilling->fetchAll(PDO::FETCH_ASSOC) as $bill) {
                if (!is_string($bill['username']) || $bill['username'] === '') {
                    throw new InvalidArgumentException('Invalid batch username');
                }
                $billingIds[(int) $bill['id']] = (int) $bill['id'];
                $usernames[$bill['username']] = $bill['username'];
            }
            $selectBilling->closeCursor();
        }
        // Do not delete a username shared with an unselected batch or billing record.
        $checkBilling = $pdo->prepare("SELECT id FROM $billingTable WHERE username=:username FOR UPDATE");
        foreach ($usernames as $username) {
            $checkBilling->execute(array(':username' => $username));
            foreach ($checkBilling->fetchAll(PDO::FETCH_COLUMN) as $billId) {
                if (!isset($billingIds[(int) $billId])) {
                    throw new DomainException('Username belongs to another billing record');
                }
            }
            $checkBilling->closeCursor();
        }
        $invoiceIds = array();
        $selectInvoices = $pdo->prepare("SELECT id FROM $invoiceTable WHERE user_id=:id ORDER BY id FOR UPDATE");
        ksort($billingIds, SORT_NUMERIC);
        foreach ($billingIds as $id) {
            $selectInvoices->execute(array(':id' => $id));
            foreach ($selectInvoices->fetchAll(PDO::FETCH_COLUMN) as $invoiceId) {
                $invoiceIds[(int) $invoiceId] = (int) $invoiceId;
            }
            $selectInvoices->closeCursor();
        }
        ksort($invoiceIds, SORT_NUMERIC);
        foreach (array($paymentsTable, $itemsTable, $invoiceTable) as $table) {
            $delete = $pdo->prepare("DELETE FROM $table WHERE " .
                ($table === $invoiceTable ? 'id' : 'invoice_id') . '=:id');
            foreach ($invoiceIds as $id) {
                $delete->execute(array(':id' => $id));
            }
        }
        ksort($usernames, SORT_STRING);
        foreach (array(array($postauthTable, $postauthUser),
                       array($acctTable, '`username`'),
                       array($groupsTable, '`username`'),
                       array($replyTable, '`username`'),
                       array($userTable, '`username`'),
                       array($billingTable, '`username`'),
                       array($checkTable, '`username`')) as $target) {
            $delete = $pdo->prepare("DELETE FROM {$target[0]} WHERE {$target[1]}=:username");
            foreach ($usernames as $username) {
                $delete->execute(array(':username' => $username));
            }
        }
        $deleteBatch = $pdo->prepare("DELETE FROM $batchTable WHERE id=:id");
        foreach ($ids as $id) {
            $deleteBatch->execute(array(':id' => $id));
            if ($deleteBatch->rowCount() !== 1) {
                throw new RuntimeException('Batch changed during deletion');
            }
        }
        $pdo->commit();
        return array(count($ids), count($usernames));
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}
