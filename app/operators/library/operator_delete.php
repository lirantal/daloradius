<?php
/** UNIT-027: remove operators and their ACLs in one PDO transaction. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/operator_delete.php') !== false) {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/operator_create.php';

function dalo_operator_delete_options(PDO $pdo, $config) {
    $operators = dalo_operator_create_table($config, 'CONFIG_DB_TBL_DALOOPERATORS');
    $rows = $pdo->query("SELECT id,username FROM $operators ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    $names = array();
    foreach ($rows as $row) {
        // Preserve the legacy datalist's one entry per displayed username.
        if (!in_array($row['username'], $names, true)) {
            $names[] = $row['username'];
        }
    }
    return $names;
}

/** Validate the entire scalar/flat-array selection before touching the database. */
function dalo_operator_delete_selection($raw) {
    $values = is_string($raw) ? array($raw) : $raw;
    if (!is_array($values) || !$values) {
        throw new InvalidArgumentException('Empty or invalid operator selection');
    }
    $inputLimit = (int) ini_get('max_input_vars');
    if ($inputLimit > 0 && count($values) >= $inputLimit - 16) {
        throw new InvalidArgumentException('Operator selection exceeds request limit');
    }
    $names = array();
    foreach ($values as $value) {
        if (!is_string($value)) {
            throw new InvalidArgumentException('Invalid operator selection');
        }
        $name = trim($value);
        $length = preg_match_all('/./us', $name);
        if ($name === '' || $length === false || $length > 32 || strpos($name, "\0") !== false) {
            throw new InvalidArgumentException('Invalid operator selection');
        }
        if (!in_array($name, $names, true)) {
            $names[] = $name;
        }
    }
    return $names;
}

/** No parent or ACL deletion survives an invalid later target or failed statement. */
function dalo_operator_delete(PDO $pdo, $config, $names) {
    $operators = dalo_operator_create_table($config, 'CONFIG_DB_TBL_DALOOPERATORS');
    $acls = dalo_operator_create_table($config, 'CONFIG_DB_TBL_DALOOPERATORS_ACL');
    dalo_operator_create_innodb($pdo, array($operators, $acls));
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not begin operator deletion');
    }
    try {
        $lookup = $pdo->prepare("SELECT id,username FROM $operators WHERE username=? ORDER BY id FOR UPDATE");
        $locked = array();
        $lockNames = $names;
        sort($lockNames, SORT_STRING);
        foreach ($lockNames as $name) {
            $lookup->execute(array($name));
            // Database collations may be case-insensitive. Match exactly as the legacy
            // PHP selection did, but refuse ambiguous identical usernames.
            $matches = array_values(array_filter($lookup->fetchAll(PDO::FETCH_ASSOC),
                function ($row) use ($name) { return $row['username'] === $name; }));
            if (count($matches) !== 1) {
                throw new DomainException('Operator selection changed or is ambiguous; reload and retry');
            }
            $locked[] = $matches[0];
        }
        // Lock existing ACL rows before deleting; cooperating edits lock the parent first.
        $ids = array_column($locked, 'id');
        $slots = implode(',', array_fill(0, count($ids), '?'));
        $aclLock = $pdo->prepare("SELECT id FROM $acls WHERE operator_id IN ($slots) ORDER BY id FOR UPDATE");
        $aclLock->execute($ids);
        $aclLock->fetchAll(PDO::FETCH_COLUMN);
        $deleteAcl = $pdo->prepare("DELETE FROM $acls WHERE operator_id=?");
        $deleteOperator = $pdo->prepare("DELETE FROM $operators WHERE id=? AND username=?");
        foreach ($locked as $row) {
            $deleteAcl->execute(array($row['id']));
            $deleteOperator->execute(array($row['id'], $row['username']));
            if ($deleteOperator->rowCount() !== 1) {
                throw new RuntimeException('Operator disappeared during deletion');
            }
        }
        if (!$pdo->commit()) {
            throw new RuntimeException('Operator deletion did not commit');
        }
        // Return submitted order for the visible result; sorting above is only for locks.
        return $names;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}
