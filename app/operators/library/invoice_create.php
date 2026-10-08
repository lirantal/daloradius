<?php
/* UNIT-005: one PDO transaction for invoice creation and all its items. */

function dalo_invoice_table($config, $key) {
    if (!in_array($key, array('CONFIG_DB_TBL_DALOBILLINGINVOICE',
                              'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS',
                              'CONFIG_DB_TBL_DALOBILLINGPLANS',
                              'CONFIG_DB_TBL_DALOPAYMENTS'), true) ||
        !isset($config[$key]) || !is_string($config[$key]) ||
        !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $config[$key])) {
        throw new InvalidArgumentException('Invalid invoice table configuration');
    }
    return '`' . $config[$key] . '`';
}

/** Collect submitted rows before any write; blank default rows are ignored. */
function dalo_invoice_items_from_post($post) {
    $items = array();
    foreach ($post as $key => $value) {
        if (strncmp((string) $key, 'item', 4) !== 0) {
            continue;
        }
        if (!preg_match('/^item[0-9]+$/D', (string) $key) || !is_array($value) ||
            array_keys($value) !== array('plan', 'amount', 'tax', 'notes')) {
            throw new InvalidArgumentException('Invalid invoice item');
        }
        foreach ($value as $field) {
            if (!is_string($field)) {
                throw new InvalidArgumentException('Invalid invoice item field');
            }
        }
        $amount = trim($value['amount']);
        $tax = trim($value['tax']);
        $notes = trim($value['notes']);
        if ($amount === '' && $tax === '' && $notes === '') {
            continue;
        }
        $plan = trim($value['plan']);
        if ($amount === '' || !ctype_digit($plan) || (int) $plan < 1 ||
            !preg_match('/^(?:0|[0-9]{1,8})(?:\.[0-9]{1,2})?$/D', $amount) ||
            ($tax !== '' && !preg_match('/^(?:0|[0-9]{1,8})(?:\.[0-9]{1,2})?$/D', $tax))) {
            throw new InvalidArgumentException('Invalid invoice item amount, tax or plan');
        }
        $items[] = array('plan' => (int) $plan, 'amount' => $amount,
                         'tax' => ($tax === '' ? '0' : $tax), 'notes' => $notes);
    }
    return $items;
}

/** Lock selected active plans for the lifetime of an invoice write transaction. */
function dalo_lock_invoice_item_plans(PDO $pdo, $plansTable, $items) {
    $checkPlan = $pdo->prepare("SELECT id FROM $plansTable
        WHERE id = :plan AND planActive = 'yes' FOR UPDATE");
    $checked = array();
    foreach ($items as $item) {
        $plan = $item['plan'];
        if (!isset($checked[$plan])) {
            $checkPlan->execute(array(':plan' => $plan));
            if ($checkPlan->fetchColumn() === false) {
                throw new InvalidArgumentException('Invalid or inactive invoice item plan');
            }
            $checkPlan->closeCursor();
            $checked[$plan] = true;
        }
    }
}

/** Return [new id, item count]; never mix PEAR statements into this transaction. */
function dalo_create_invoice(PDO $pdo, $config, $invoice, $items) {
    $table = dalo_invoice_table($config, 'CONFIG_DB_TBL_DALOBILLINGINVOICE');
    $itemsTable = dalo_invoice_table($config, 'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS');
    $plansTable = dalo_invoice_table($config, 'CONFIG_DB_TBL_DALOBILLINGPLANS');
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start invoice transaction');
    }
    try {
        // The UI only lists active plans. Lock each selected plan until commit
        // so a concurrent deactivation/deletion cannot create a dangling item.
        dalo_lock_invoice_item_plans($pdo, $plansTable, $items);
        $insert = $pdo->prepare("INSERT INTO $table
            (user_id, date, status_id, type_id, notes, creationdate, creationby, updatedate, updateby)
            VALUES (:user_id, :date, :status_id, :type_id, :notes, :created, :creator, NULL, NULL)");
        $insert->execute(array(':user_id' => $invoice['user_id'], ':date' => $invoice['date'],
            ':status_id' => $invoice['status_id'], ':type_id' => $invoice['type_id'],
            ':notes' => $invoice['notes'], ':created' => $invoice['created'],
            ':creator' => $invoice['creator']));
        $id = (int) $pdo->lastInsertId();
        if ($id < 1) {
            throw new RuntimeException('Invoice insert returned no ID');
        }
        $insertItem = $pdo->prepare("INSERT INTO $itemsTable
            (invoice_id, plan_id, amount, tax_amount, notes, creationdate, creationby)
            VALUES (:invoice_id, :plan, :amount, :tax, :notes, :created, :creator)");
        foreach ($items as $item) {
            $insertItem->execute(array(':invoice_id' => $id, ':plan' => $item['plan'],
                ':amount' => $item['amount'], ':tax' => $item['tax'], ':notes' => $item['notes'],
                ':created' => $invoice['created'], ':creator' => $invoice['creator']));
        }
        $pdo->commit();
        return array($id, count($items));
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}
