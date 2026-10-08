<?php
/* UNIT-006: update invoice header and replace items in one PDO transaction. */
require_once __DIR__ . '/invoice_create.php';

function dalo_replace_invoice_items(PDO $pdo, $config, $invoiceId, $changes, $items,
                                    $updated, $operator) {
    $invoiceTable = dalo_invoice_table($config, 'CONFIG_DB_TBL_DALOBILLINGINVOICE');
    $itemsTable = dalo_invoice_table($config, 'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS');
    $plansTable = dalo_invoice_table($config, 'CONFIG_DB_TBL_DALOBILLINGPLANS');
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start invoice edit transaction');
    }
    try {
        // Recheck the invoice on the write connection; PEAR's earlier display
        // lookup cannot prevent concurrent deletion or bind this transaction.
        $check = $pdo->prepare("SELECT id FROM $invoiceTable WHERE id = :id FOR UPDATE");
        $check->execute(array(':id' => $invoiceId));
        if ($check->fetchColumn() === false) {
            throw new RuntimeException('Invoice no longer exists');
        }
        $check->closeCursor();
        dalo_lock_invoice_item_plans($pdo, $plansTable, $items);

        $columns = array('user_id' => 'user_id', 'type_id' => 'type_id',
                         'status_id' => 'status_id', 'date' => 'date',
                         'notes' => 'notes');
        $set = array('updatedate = :updated', 'updateby = :operator');
        $params = array(':updated' => $updated, ':operator' => $operator,
                        ':invoice_id' => $invoiceId);
        foreach ($columns as $key => $column) {
            if (array_key_exists($key, $changes)) {
                $set[] = "`$column` = :$key";
                $params[':' . $key] = $changes[$key];
            }
        }
        $update = $pdo->prepare("UPDATE $invoiceTable SET " . implode(', ', $set) .
                                ' WHERE id = :invoice_id');
        $update->execute($params);

        $delete = $pdo->prepare("DELETE FROM $itemsTable WHERE invoice_id = :invoice_id");
        $delete->execute(array(':invoice_id' => $invoiceId));
        $insert = $pdo->prepare("INSERT INTO $itemsTable
            (invoice_id, plan_id, amount, tax_amount, notes, creationdate, creationby)
            VALUES (:invoice_id, :plan, :amount, :tax, :notes, :created, :creator)");
        foreach ($items as $item) {
            $insert->execute(array(':invoice_id' => $invoiceId, ':plan' => $item['plan'],
                ':amount' => $item['amount'], ':tax' => $item['tax'], ':notes' => $item['notes'],
                ':created' => $updated, ':creator' => $operator));
        }
        $pdo->commit();
        return count($items);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}
