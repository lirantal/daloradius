<?php
/** R17: local payment CRUD, invoice-first locks and strict selected-backend reads. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/payments_pdo.php') !== false) {
    http_response_code(404); exit;
}
require_once __DIR__ . '/../../common/includes/pdo_connection.php';
require_once __DIR__ . '/catalog_reads_pdo.php';

function dalo_payment_open($config) {
    return dalo_pdo_connect($config, $_SESSION['location_name'] ?? 'default');
}
function dalo_payment_table(PDO $pdo, $config, $key) {
    if (!in_array($key, array('CONFIG_DB_TBL_DALOPAYMENTS', 'CONFIG_DB_TBL_DALOPAYMENTTYPES',
        'CONFIG_DB_TBL_DALOBILLINGINVOICE', 'CONFIG_DB_TBL_DALOUSERBILLINFO'), true)) {
        throw new InvalidArgumentException('Invalid payment table');
    }
    return dalo_read_table($pdo, $config, $key);
}
function dalo_payment_id($value) {
    if (!is_string($value) || !preg_match('/\A[1-9][0-9]{0,9}\z/', $value) ||
        (int)$value > 2147483647) { throw new InvalidArgumentException('Invalid payment or invoice ID'); }
    return (int)$value;
}
function dalo_payment_scalar($input, $key, $default = '') {
    $value = $input[$key] ?? $default;
    if (!is_string($value)) { throw new InvalidArgumentException('Invalid payment input'); }
    return trim($value);
}
/** Exact DECIMAL(10,2), no floating-point conversion or permissive SQL rounding. */
function dalo_payment_amount($value) {
    if (!preg_match('/\A(-?)([0-9]+)(?:\.([0-9]{1,2}))?\z/', $value, $m)) {
        throw new InvalidArgumentException('Invalid payment amount');
    }
    $whole = ltrim($m[2], '0'); $whole = $whole === '' ? '0' : $whole;
    if (strlen($whole) > 8) { throw new InvalidArgumentException('Payment amount out of range'); }
    $fraction = str_pad($m[3] ?? '', 2, '0');
    $zero = $whole === '0' && $fraction === '00';
    return ($zero ? '' : $m[1]) . $whole . '.' . $fraction;
}
function dalo_payment_date($value) {
    if (!preg_match('/\A([0-9]{4})-([0-9]{2})-([0-9]{2})\z/', $value, $m) ||
        (int)$m[1] < 1000 || !checkdate((int)$m[2], (int)$m[3], (int)$m[1])) {
        throw new InvalidArgumentException('Invalid payment date');
    }
    return $value;
}
function dalo_payment_fields($post, $create) {
    $values = array();
    $invoice = dalo_payment_scalar($post, 'payment_invoice_id');
    if ($invoice !== '') { $values['invoice_id'] = dalo_payment_id($invoice); }
    elseif ($create) { throw new InvalidArgumentException('Missing invoice'); }
    $type = dalo_payment_scalar($post, 'payment_type_id');
    if ($type !== '') {
        if (strpos($type, 'paymentType-') !== 0) { throw new InvalidArgumentException('Invalid payment type'); }
        $values['type_id'] = dalo_payment_id(substr($type, 12));
    } elseif ($create) { $values['type_id'] = 0; } // Historical optional blank type.
    $amount = dalo_payment_scalar($post, 'payment_amount');
    if ($amount !== '') {
        $amount = dalo_payment_amount($amount);
        if ($create && $amount === '0.00') { throw new InvalidArgumentException('Missing amount'); }
        // Preserve legacy edit skip policy for zero and negative amounts.
        if ($create || ($amount !== '0.00' && $amount[0] !== '-')) { $values['amount'] = $amount; }
    } elseif ($create) { throw new InvalidArgumentException('Missing amount'); }
    $date = dalo_payment_scalar($post, 'payment_date');
    if ($date !== '') { $values['date'] = dalo_payment_date($date); }
    elseif ($create) { $values['date'] = date('Y-m-d'); }
    $notes = dalo_payment_scalar($post, 'payment_notes');
    if ((function_exists('mb_strlen') ? mb_strlen($notes, 'UTF-8') : strlen($notes)) > 128) {
        throw new InvalidArgumentException('Payment notes too long');
    }
    if ($create || $notes !== '') { $values['notes'] = $notes; }
    return $values;
}
function dalo_payment_ids($input) {
    $values = is_array($input) ? $input : array($input);
    $limit = (int)ini_get('max_input_vars');
    if (!$values || count($values) > 500 || ($limit > 0 && count($_POST, COUNT_RECURSIVE) >= $limit)) {
        throw new InvalidArgumentException('Invalid payment selection size');
    }
    $ids = array();
    foreach ($values as $value) { $id = dalo_payment_id($value); $ids[$id] = $id; }
    sort($ids, SORT_NUMERIC); return array_values($ids);
}
function dalo_payment_types(PDO $pdo, $config) {
    $table = dalo_payment_table($pdo, $config, 'CONFIG_DB_TBL_DALOPAYMENTTYPES');
    $options = array();
    foreach (dalo_catalog_read_rows($pdo, "SELECT id, value FROM $table") as $row) {
        $options['paymentType-' . $row[0]] = (string)$row[1];
    }
    return $options;
}
function dalo_payment_read_failure(Throwable $error) {
    global $failureMsg, $logAction;
    $failureMsg = 'Unable to read payment data';
    $logAction .= 'Payment read failed [' . get_class($error) . '] on page: ';
}
/** Acquire metadata locks inside the owned transaction before engine inspection. */
function dalo_payment_begin(PDO $pdo, $config, $keys) {
    if ($pdo->inTransaction() || !$pdo->beginTransaction()) {
        throw new RuntimeException('Payment transaction unavailable');
    }
    foreach ($keys as $key) {
        $table = dalo_payment_table($pdo, $config, $key);
        $pdo->query("SELECT id FROM $table WHERE 1=0")->closeCursor();
        $stmt = $pdo->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
        $stmt->execute(array($config[$key]));
        if (strtoupper((string)$stmt->fetchColumn()) !== 'INNODB') {
            throw new RuntimeException('Payment operation requires transactional tables');
        }
        $stmt->closeCursor();
    }
}
/** Lock invoices before payments, matching invoice/POS/batch deletion order. */
function dalo_payment_lock_invoices(PDO $pdo, $table, $ids, $required) {
    $ids = array_values(array_unique($ids)); sort($ids, SORT_NUMERIC);
    $stmt = $pdo->prepare("SELECT id FROM $table WHERE id=? FOR UPDATE");
    foreach ($ids as $id) {
        $stmt->execute(array($id)); $exists = $stmt->fetchColumn() !== false; $stmt->closeCursor();
        if (!$exists && $required) { throw new DomainException('Invoice no longer exists'); }
    }
}
/** Own one transaction; rediscover then recheck every child after parent locks. */
function dalo_payment_mutate(PDO $pdo, $config, $mode, $ids, $values, $operator) {
    if (!in_array($mode, array('new','edit','del'), true) || !is_string($operator) ||
        (function_exists('mb_strlen') ? mb_strlen($operator, 'UTF-8') : strlen($operator)) > 128) {
        throw new InvalidArgumentException('Invalid payment operation');
    }
    if (!is_array($ids) || !is_array($values) || count($ids) > 500 ||
        ($mode === 'new' && $ids) || ($mode === 'edit' && count($ids) !== 1) ||
        ($mode === 'del' && (!$ids || $values))) {
        throw new InvalidArgumentException('Invalid payment operation shape');
    }
    foreach ($ids as $id) {
        if (!is_int($id) || $id < 1 || $id > 2147483647) { throw new InvalidArgumentException('Invalid payment ID'); }
    }
    $ids = array_values(array_unique($ids)); sort($ids, SORT_NUMERIC);
    $columns = array('invoice_id','type_id','amount','date','notes');
    if (($mode === 'new' && count($values) !== count($columns)) || array_diff(array_keys($values), $columns)) {
        throw new InvalidArgumentException('Invalid payment fields');
    }
    foreach ($values as $column=>$value) {
        if (in_array($column, array('invoice_id','type_id'), true)) {
            if (!is_int($value) || $value > 2147483647 || $value < ($column === 'type_id' && $mode === 'new' ? 0 : 1)) {
                throw new InvalidArgumentException('Invalid payment relation');
            }
        } elseif (!is_string($value)) { throw new InvalidArgumentException('Invalid payment value'); }
        elseif ($column === 'amount' && (dalo_payment_amount($value) !== $value || $value === '0.00' || ($mode === 'edit' && $value[0] === '-'))) {
            throw new InvalidArgumentException('Invalid normalized payment amount');
        } elseif ($column === 'date') { dalo_payment_date($value); }
        elseif ($column === 'notes' && (function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value)) > 128) {
            throw new InvalidArgumentException('Payment notes too long');
        }
    }
    $payment = dalo_payment_table($pdo, $config, 'CONFIG_DB_TBL_DALOPAYMENTS');
    $invoice = dalo_payment_table($pdo, $config, 'CONFIG_DB_TBL_DALOBILLINGINVOICE');
    $type = dalo_payment_table($pdo, $config, 'CONFIG_DB_TBL_DALOPAYMENTTYPES');
    $owns = false;
    try {
        if ($pdo->inTransaction()) { throw new RuntimeException('Caller transaction must remain untouched'); }
        $owns = true;
        $keys = array('CONFIG_DB_TBL_DALOPAYMENTS','CONFIG_DB_TBL_DALOBILLINGINVOICE');
        if ($mode !== 'del') { $keys[] = 'CONFIG_DB_TBL_DALOPAYMENTTYPES'; }
        dalo_payment_begin($pdo, $config, $keys);
        $old = array(); $parents = array();
        if ($mode !== 'new') {
            $discover = $pdo->prepare("SELECT invoice_id FROM $payment WHERE id=?");
            foreach ($ids as $id) {
                $discover->execute(array($id)); $parent = $discover->fetchColumn(); $discover->closeCursor();
                if ($parent === false) { throw new DomainException('Payment no longer exists'); }
                $old[$id] = (int)$parent; $parents[] = (int)$parent;
            }
        }
        if (isset($values['invoice_id'])) { $parents[] = $values['invoice_id']; }
        dalo_payment_lock_invoices($pdo, $invoice, $parents, $mode !== 'del');
        if ($mode !== 'new') {
            $lock = $pdo->prepare("SELECT invoice_id FROM $payment WHERE id=? FOR UPDATE");
            foreach ($ids as $id) {
                $lock->execute(array($id)); $parent = $lock->fetchColumn(); $lock->closeCursor();
                if ($parent === false || (int)$parent !== $old[$id]) {
                    throw new DomainException('Payment changed during selection');
                }
            }
        }
        if (!empty($values['type_id'])) {
            $stmt = $pdo->prepare("SELECT id FROM $type WHERE id=? FOR UPDATE");
            $stmt->execute(array($values['type_id'])); $exists = $stmt->fetchColumn() !== false; $stmt->closeCursor();
            if (!$exists) { throw new DomainException('Payment type no longer exists'); }
        }
        $now = date('Y-m-d H:i:s');
        if ($mode === 'new') {
            $stmt = $pdo->prepare("INSERT INTO $payment (invoice_id,amount,date,type_id,notes,creationdate,creationby,updatedate,updateby)
                VALUES (:invoice_id,:amount,:date,:type_id,:notes,:created,:creator,NULL,NULL)");
            $stmt->execute(array(':invoice_id'=>$values['invoice_id'], ':amount'=>$values['amount'], ':date'=>$values['date'],
                ':type_id'=>$values['type_id'], ':notes'=>$values['notes'], ':created'=>$now, ':creator'=>$operator));
            $result = (int)$pdo->lastInsertId();
            if ($result < 1) { throw new RuntimeException('No payment insert ID'); }
        } elseif ($mode === 'edit') {
            $set = array('updatedate=:updated','updateby=:operator');
            $bind = array(':updated'=>$now,':operator'=>$operator,':id'=>$ids[0]);
            foreach ($values as $column=>$value) {
                if (!in_array($column,array('invoice_id','type_id','amount','date','notes'),true)) { throw new InvalidArgumentException('Invalid payment field'); }
                $set[] = "$column=:$column"; $bind[':'.$column]=$value;
            }
            $stmt = $pdo->prepare("UPDATE $payment SET " . implode(',',$set) . ' WHERE id=:id');
            $stmt->execute($bind); $result = $ids[0]; // Unchanged submission is still a successful edit.
        } else {
            $stmt = $pdo->prepare("DELETE FROM $payment WHERE id=?"); $result = 0;
            foreach ($ids as $id) { $stmt->execute(array($id)); $result += $stmt->rowCount(); }
        }
        if (!$pdo->commit()) { throw new RuntimeException('Payment commit failed'); }
        return $result;
    } catch (Throwable $error) {
        if ($owns && $pdo->inTransaction()) { $pdo->rollBack(); }
        throw $error;
    }
}
function dalo_payment_list_query(PDO $pdo, $config, $filters) {
    $payment = dalo_payment_table($pdo, $config, 'CONFIG_DB_TBL_DALOPAYMENTS');
    $type = dalo_payment_table($pdo, $config, 'CONFIG_DB_TBL_DALOPAYMENTTYPES');
    $invoice = dalo_payment_table($pdo, $config, 'CONFIG_DB_TBL_DALOBILLINGINVOICE');
    $bill = dalo_payment_table($pdo, $config, 'CONFIG_DB_TBL_DALOUSERBILLINFO');
    $sql = "SELECT p.id,p.invoice_id,p.amount,p.date,pt.value,p.notes FROM $payment AS p LEFT JOIN $type AS pt ON p.type_id=pt.id";
    $where = array(); $bind = array(); $user = $filters['user_id'] ?? '';
    if (($filters['username'] ?? '') !== '') {
        $rows = dalo_catalog_read_rows($pdo,"SELECT id FROM $bill WHERE username=:username",array(':username'=>$filters['username']));
        $user = $rows ? (string)$rows[0][0] : '0';
    }
    if ($user !== '') {
        $sql .= " JOIN $invoice AS bi ON bi.id=p.invoice_id";
        $where[] = 'bi.user_id=:user_id'; $bind[':user_id']=$user;
    }
    if (($filters['invoice_id'] ?? '') !== '') {
        $where[] = 'p.invoice_id=:invoice_id'; $bind[':invoice_id']=$filters['invoice_id'];
    }
    if ($where) { $sql .= ' WHERE ' . implode(' AND ',$where); }
    return array($sql,$bind);
}
