<?php
/* UNIT-007: delete invoices and their children on one PDO transaction. */
require_once __DIR__ . '/invoice_create.php';

/** Accept one or several canonical, positive integer invoice IDs; reject all bad input. */
function dalo_invoice_ids_from_post($input) {
    $values = is_array($input) ? $input : array($input);
    if (count($values) === 0) {
        throw new InvalidArgumentException('No invoice selected');
    }
    $ids = array();
    foreach ($values as $value) {
        if (!is_string($value) || !ctype_digit($value) ||
            (int) $value < 1 || (int) $value > 2147483647 ||
            (string) (int) $value !== $value) {
            throw new InvalidArgumentException('Invalid invoice ID');
        }
        $ids[(int) $value] = (int) $value;
    }
    $ids = array_values($ids);
    sort($ids, SORT_NUMERIC); // Consistent lock order for overlapping batch requests.
    return $ids;
}

/** Return [deleted parents, items, payments]; rollback on any failure. */
function dalo_delete_invoices(PDO $pdo, $config, $ids) {
    $invoiceTable = dalo_invoice_table($config, 'CONFIG_DB_TBL_DALOBILLINGINVOICE');
    $itemsTable = dalo_invoice_table($config, 'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS');
    $paymentsTable = dalo_invoice_table($config, 'CONFIG_DB_TBL_DALOPAYMENTS');
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start invoice deletion');
    }
    try {
        $select = $pdo->prepare("SELECT id FROM $invoiceTable WHERE id = :id FOR UPDATE");
        $existing = array();
        foreach ($ids as $id) {
            $select->execute(array(':id' => $id));
            if ($select->fetchColumn() !== false) {
                $existing[] = $id;
            }
            $select->closeCursor();
        }
        $deletePayments = $pdo->prepare("DELETE FROM $paymentsTable WHERE invoice_id = :id");
        $deleteItems = $pdo->prepare("DELETE FROM $itemsTable WHERE invoice_id = :id");
        $deleteInvoice = $pdo->prepare("DELETE FROM $invoiceTable WHERE id = :id");
        $counts = array(0, 0, 0);
        foreach ($existing as $id) {
            $params = array(':id' => $id);
            $deletePayments->execute($params);
            $counts[2] += $deletePayments->rowCount();
            $deleteItems->execute($params);
            $counts[1] += $deleteItems->rowCount();
            $deleteInvoice->execute($params);
            $counts[0] += $deleteInvoice->rowCount();
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
