<?php
/* UNIT-013: POS account and dependent rows delete on one PDO connection. */
require_once __DIR__ . '/pos_provision.php';
require_once __DIR__ . '/invoice_create.php';

/** Return deleted [account rows, billing rows, invoices, items, payments, accounting rows]. */
function dalo_delete_pos_user(PDO $pdo, $config, $username, $deleteAccounting) {
    if (!is_string($username) || trim($username) === '' || strlen($username) > 128 ||
        !is_bool($deleteAccounting)) {
        throw new InvalidArgumentException('Invalid POS deletion request');
    }
    $check = dalo_pos_table($config, 'CONFIG_DB_TBL_RADCHECK');
    $reply = dalo_pos_table($config, 'CONFIG_DB_TBL_RADREPLY');
    $groups = dalo_pos_table($config, 'CONFIG_DB_TBL_RADUSERGROUP');
    $user = dalo_pos_table($config, 'CONFIG_DB_TBL_DALOUSERINFO');
    $billing = dalo_pos_table($config, 'CONFIG_DB_TBL_DALOUSERBILLINFO');
    $invoices = dalo_invoice_table($config, 'CONFIG_DB_TBL_DALOBILLINGINVOICE');
    $items = dalo_invoice_table($config, 'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS');
    $payments = dalo_invoice_table($config, 'CONFIG_DB_TBL_DALOPAYMENTS');
    $accounting = null;
    if ($deleteAccounting) {
        $accounting = dalo_pos_delete_table($config, 'CONFIG_DB_TBL_RADACCT');
    }
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start POS deletion');
    }
    try {
        $select = $pdo->prepare("SELECT id FROM $check WHERE username=:username ORDER BY id FOR UPDATE");
        $select->execute(array(':username' => $username));
        $accountIds = $select->fetchAll(PDO::FETCH_COLUMN);
        $select->closeCursor();
        if (!$accountIds) {
            throw new DomainException('POS user no longer exists');
        }
        $select = $pdo->prepare("SELECT id FROM $billing WHERE username=:username ORDER BY id FOR UPDATE");
        $select->execute(array(':username' => $username));
        $billingIds = $select->fetchAll(PDO::FETCH_COLUMN);
        $select->closeCursor();
        $invoiceIds = array();
        $selectInvoices = $pdo->prepare("SELECT id FROM $invoices WHERE user_id=:id ORDER BY id FOR UPDATE");
        foreach ($billingIds as $id) {
            $selectInvoices->execute(array(':id' => $id));
            foreach ($selectInvoices->fetchAll(PDO::FETCH_COLUMN) as $invoiceId) {
                $invoiceIds[(int) $invoiceId] = (int) $invoiceId;
            }
            $selectInvoices->closeCursor();
        }
        ksort($invoiceIds, SORT_NUMERIC);
        $counts = array(count($accountIds), count($billingIds), 0, 0, 0, 0);
        $deletePayments = $pdo->prepare("DELETE FROM $payments WHERE invoice_id=:id");
        $deleteItems = $pdo->prepare("DELETE FROM $items WHERE invoice_id=:id");
        $deleteInvoice = $pdo->prepare("DELETE FROM $invoices WHERE id=:id");
        foreach ($invoiceIds as $id) {
            $params = array(':id' => $id);
            $deletePayments->execute($params);
            $counts[4] += $deletePayments->rowCount();
            $deleteItems->execute($params);
            $counts[3] += $deleteItems->rowCount();
            $deleteInvoice->execute($params);
            $counts[2] += $deleteInvoice->rowCount();
        }
        $byUsername = array($groups, $reply, $user, $billing, $check);
        if ($accounting !== null) {
            array_unshift($byUsername, $accounting);
        }
        foreach ($byUsername as $table) {
            $delete = $pdo->prepare("DELETE FROM $table WHERE username=:username");
            $delete->execute(array(':username' => $username));
            if ($table === $accounting) {
                $counts[5] += $delete->rowCount();
            }
        }
        $pdo->commit();
        return $counts;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

/** Only accounting is outside the POS provisioning table whitelist. */
function dalo_pos_delete_table($config, $key) {
    if ($key !== 'CONFIG_DB_TBL_RADACCT' || !isset($config[$key]) ||
        !is_string($config[$key]) ||
        !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $config[$key])) {
        throw new InvalidArgumentException('Invalid POS accounting table');
    }
    return '`' . $config[$key] . '`';
}
