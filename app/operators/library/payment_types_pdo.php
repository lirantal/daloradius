<?php
/** R18: payment-type CRUD; preserve payments and refuse deleting referenced types. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/payment_types_pdo.php') !== false) {
    http_response_code(404); exit;
}
require_once __DIR__ . '/payments_pdo.php';

function dalo_payment_type_text($value, $limit, $required = false) {
    if (!is_string($value)) { throw new InvalidArgumentException('Invalid payment type input'); }
    $value = trim($value);
    if (($required && $value === '') || (function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value)) > $limit) {
        throw new InvalidArgumentException('Invalid payment type length');
    }
    return $value;
}
function dalo_payment_type_names($input) {
    $values = is_array($input) ? $input : array($input);
    $limit = (int)ini_get('max_input_vars');
    if (!$values || count($values) > 500 || ($limit > 0 && count($_POST, COUNT_RECURSIVE) >= $limit)) {
        throw new InvalidArgumentException('Invalid payment type selection');
    }
    $names = array();
    foreach ($values as $value) {
        $name = dalo_payment_type_text($value, 32, true);
        if (!in_array($name, $names, true)) { $names[] = $name; }
    }
    sort($names, SORT_STRING); return $names;
}
function dalo_payment_type_read(PDO $pdo, $config, $name) {
    $table = dalo_payment_table($pdo, $config, 'CONFIG_DB_TBL_DALOPAYMENTTYPES');
    $rows = dalo_catalog_read_rows($pdo, "SELECT id,value,notes,creationdate,creationby,updatedate,updateby FROM $table WHERE value=:name", array(':name'=>$name));
    if (count($rows) !== 1 || (string)$rows[0][1] !== $name) { throw new DomainException('Missing or ambiguous payment type'); }
    return $rows[0];
}
function dalo_payment_type_read_failure(Throwable $error) {
    global $failureMsg, $logAction;
    $failureMsg = 'Unable to read payment type data';
    $logAction .= 'Payment type read failed [' . get_class($error) . '] on page: ';
}
function dalo_payment_type_lock_name(PDO $pdo, $config) {
    return 'dalo-type:' . substr(hash('sha256', $pdo->query('SELECT DATABASE()')->fetchColumn() . '\0' .
        $config['CONFIG_DB_TBL_DALOPAYMENTTYPES']), 0, 40);
}
/** Serialize name writes, lock type parents, check references with a current nonlocking read. */
function dalo_payment_type_mutate(PDO $pdo, $config, $mode, $input, $notes, $operator) {
    if (!in_array($mode, array('new','edit','del'), true) || $pdo->inTransaction()) {
        throw new RuntimeException('Payment type transaction unavailable');
    }
    $names = dalo_payment_type_names($input);
    if ($mode !== 'del' && count($names) !== 1) { throw new InvalidArgumentException('One payment type required'); }
    $notes = dalo_payment_type_text($notes, 128);
    $operator = dalo_payment_type_text($operator, 128, true);
    $table = dalo_payment_table($pdo, $config, 'CONFIG_DB_TBL_DALOPAYMENTTYPES');
    $lockName = dalo_payment_type_lock_name($pdo, $config); $locked = false; $owns = false;
    try {
        $lock = $pdo->prepare('SELECT GET_LOCK(?,10)'); $lock->execute(array($lockName));
        $locked = (int)$lock->fetchColumn() === 1; $lock->closeCursor();
        if (!$locked) { throw new RuntimeException('Payment type lock unavailable'); }
        // A locking child read would invert R17's payment -> type order. A current
        // READ COMMITTED reference read, protected by the type lock, avoids that cycle.
        $pdo->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
        $owns = true;
        $keys = array('CONFIG_DB_TBL_DALOPAYMENTTYPES');
        if ($mode === 'del') { $keys[] = 'CONFIG_DB_TBL_DALOPAYMENTS'; }
        dalo_payment_begin($pdo, $config, $keys);
        $select = $pdo->prepare("SELECT id,value FROM $table WHERE value=?"); $ids = array();
        foreach ($names as $name) {
            $select->execute(array($name)); $rows = $select->fetchAll(PDO::FETCH_NUM); $select->closeCursor();
            if ($mode === 'new') {
                if ($rows) { throw new DomainException('Payment type already exists'); }
            } else {
                if (count($rows) !== 1 || (string)$rows[0][1] !== $name) { throw new DomainException('Missing or ambiguous payment type'); }
                $ids[(int)$rows[0][0]] = $name;
            }
        }
        ksort($ids, SORT_NUMERIC);
        $check = $pdo->prepare("SELECT value FROM $table WHERE id=? FOR UPDATE");
        foreach ($ids as $id=>$name) {
            $check->execute(array($id)); $value = $check->fetchColumn(); $check->closeCursor();
            if ($value === false || (string)$value !== $name) { throw new DomainException('Payment type changed'); }
        }
        if ($mode === 'del') {
            $payments = dalo_payment_table($pdo, $config, 'CONFIG_DB_TBL_DALOPAYMENTS');
            $reference = $pdo->prepare("SELECT id FROM $payments WHERE type_id=? LIMIT 1");
            foreach ($ids as $id=>$name) {
                $reference->execute(array($id)); $used = $reference->fetchColumn() !== false; $reference->closeCursor();
                if ($used) { throw new DomainException('Referenced payment type cannot be deleted'); }
            }
            $delete = $pdo->prepare("DELETE FROM $table WHERE id=?"); $result = 0;
            foreach ($ids as $id=>$name) { $delete->execute(array($id)); $result += $delete->rowCount(); }
        } elseif ($mode === 'new') {
            $insert = $pdo->prepare("INSERT INTO $table (value,notes,creationdate,creationby,updatedate,updateby) VALUES (?,?,?, ?,NULL,NULL)");
            $insert->execute(array($names[0],$notes,date('Y-m-d H:i:s'),$operator));
            $result = (int)$pdo->lastInsertId();
            if ($result < 1) { throw new RuntimeException('Missing payment type insert ID'); }
        } else {
            $id = array_key_first($ids);
            $params = array(date('Y-m-d H:i:s'),$operator); $set = 'updatedate=?,updateby=?';
            if ($notes !== '') { $set .= ',notes=?'; $params[] = $notes; }
            $params[] = $id;
            $update = $pdo->prepare("UPDATE $table SET $set WHERE id=?"); $update->execute($params); $result = $id;
        }
        if ($mode !== 'del') {
            $verify = $pdo->prepare("SELECT value,notes,creationby,updateby FROM $table WHERE id=?");
            $verify->execute(array($result)); $stored = $verify->fetch(PDO::FETCH_NUM); $verify->closeCursor();
            if (!$stored || (string)$stored[0] !== $names[0] ||
                (($mode === 'new' || $notes !== '') && (string)$stored[1] !== $notes) ||
                (string)$stored[$mode === 'new' ? 2 : 3] !== $operator) {
                throw new RuntimeException('Payment type storage mismatch');
            }
        }
        if (!$pdo->commit()) { throw new RuntimeException('Payment type commit failed'); }
        return $result;
    } catch (Throwable $error) {
        if ($owns && $pdo->inTransaction()) { $pdo->rollBack(); }
        throw $error;
    } finally {
        if ($locked) {
            // Release failure must not turn a committed mutation into a false failure.
            try { $release = $pdo->prepare('SELECT RELEASE_LOCK(?)'); $release->execute(array($lockName)); $release->closeCursor(); }
            catch (Throwable $ignored) { /* The nonpersistent page handle is disposed by its caller. */ }
        }
    }
}
