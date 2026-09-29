<?php
/* UNIT-037: opt-in mapping provider. The caller owns the PDO transaction. */
require_once __DIR__ . '/../../library/attributes_pdo.php';

function dalo_mapping_table($config, $key) {
    $keys = array('CONFIG_DB_TBL_RADCHECK', 'CONFIG_DB_TBL_RADGROUPCHECK',
        'CONFIG_DB_TBL_RADGROUPREPLY', 'CONFIG_DB_TBL_RADUSERGROUP',
        'CONFIG_DB_TBL_DALOBILLINGPLANS', 'CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES');
    $name = $config[$key] ?? null;
    if (!in_array($key, $keys, true) || !is_string($name) ||
        !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $name)) {
        throw new InvalidArgumentException('Invalid mapping table configuration');
    }
    return '`' . $name . '`';
}

function dalo_mapping_name($value, $limit) {
    if (!is_string($value)) { throw new InvalidArgumentException('Invalid mapping name'); }
    $value = trim($value);
    if ($value === '' || !preg_match('//u', $value) || strpos($value, "\0") !== false ||
        dalo_attribute_pdo_length($value) > $limit) {
        throw new InvalidArgumentException('Invalid mapping name');
    }
    return $value;
}

function dalo_mapping_groups($values, $limit) {
    if (!is_array($values) || count($values) > 5000) {
        throw new InvalidArgumentException('Invalid mapping selection');
    }
    $names = array();
    foreach ($values as $value) {
        if (is_string($value) && trim($value) === '') { continue; }
        $name = dalo_mapping_name($value, $limit);
        $names[$name] = $name;
    }
    return array_values($names);
}

/** Pure lookup may run without a transaction; writes use the locking form. */
function dalo_mapping_group_exists(PDO $pdo, $config, $name, $lock = false) {
    global $logDebugSQL;
    $name = dalo_mapping_name($name, 256);
    $tables = array(dalo_mapping_table($config, 'CONFIG_DB_TBL_RADGROUPCHECK'),
                    dalo_mapping_table($config, 'CONFIG_DB_TBL_RADGROUPREPLY'));
    if ($lock && !$pdo->inTransaction()) {
        throw new LogicException('Caller mapping transaction required');
    }
    foreach ($tables as $table) {
        $sql = "SELECT id FROM $table WHERE groupname=? ORDER BY id LIMIT 1" . ($lock ? ' FOR UPDATE' : '');
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($name));
        $exists = $stmt->fetchColumn() !== false;
        $stmt->closeCursor();
        $logDebugSQL .= "$sql;\n";
        if ($exists) { return true; }
    }
    return false;
}

/** Lock the parent before the group rows, using the same caller-owned handle. */
function dalo_mapping_prepare(PDO $pdo, $config, $subject, $groups, $plan = false) {
    global $logDebugSQL;
    if (!$pdo->inTransaction()) { throw new LogicException('Caller mapping transaction required'); }
    $parent = dalo_mapping_table($config, $plan ? 'CONFIG_DB_TBL_DALOBILLINGPLANS' : 'CONFIG_DB_TBL_RADCHECK');
    $target = dalo_mapping_table($config, $plan ? 'CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES' : 'CONFIG_DB_TBL_RADUSERGROUP');
    $sources = array(dalo_mapping_table($config, 'CONFIG_DB_TBL_RADGROUPCHECK'),
                     dalo_mapping_table($config, 'CONFIG_DB_TBL_RADGROUPREPLY'));
    dalo_attribute_pdo_require_innodb($pdo, array_merge(array($parent, $target), $sources));
    $column = $plan ? 'planName' : 'username';
    $sql = "SELECT id FROM $parent WHERE $column=? ORDER BY id FOR UPDATE";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($subject));
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $stmt->closeCursor();
    $logDebugSQL .= "$sql;\n";
    if (!$rows || ($plan && count($rows) !== 1)) {
        throw new DomainException('Missing or ambiguous mapping parent');
    }
    $ordered = $groups;
    sort($ordered, SORT_STRING);
    foreach ($ordered as $name) {
        if (!dalo_mapping_group_exists($pdo, $config, $name, true)) {
            throw new DomainException('Unknown mapping group');
        }
    }
    return $target;
}

function dalo_mapping_priority($group, $priority) {
    if (!is_int($priority) && !is_string($priority)) {
        throw new InvalidArgumentException('Invalid mapping priority');
    }
    $raw = trim((string)$priority);
    if (!preg_match('/^-?[0-9]+$/D', $raw) || strlen(ltrim($raw, '-')) > 10 ||
        (float)$raw < -2147483648 || (float)$raw > 2147483647) {
        throw new InvalidArgumentException('Invalid mapping priority');
    }
    return normalize_user_group_priority($group, (int)$raw);
}

/** PDO batches are all-or-nothing; propagate any error for caller rollback. */
function dalo_mapping_insert_many(PDO $pdo, $config, $subject, $input, $plan = false) {
    global $logDebugSQL;
    if (!$pdo->inTransaction()) { throw new LogicException('Caller mapping transaction required'); }
    $subject = dalo_mapping_name($subject, $plan ? 128 : 64);
    $groups = dalo_mapping_groups($input, $plan ? 256 : 64);
    if (!$groups) { return false; }
    $table = dalo_mapping_prepare($pdo, $config, $subject, $groups, $plan);
    $sql = $plan ? "INSERT INTO $table (plan_name,profile_name) VALUES (?,?)"
                 : "INSERT INTO $table (username,groupname,priority) VALUES (?,?,?)";
    $stmt = $pdo->prepare($sql);
    $count = 0;
    foreach ($groups as $name) {
        $values = $plan ? array($subject, $name)
                       : array($subject, $name, dalo_mapping_priority($name, 0));
        $stmt->execute($values);
        $count += $stmt->rowCount();
        $logDebugSQL .= "$sql;\n";
    }
    return $count;
}

/** Parent locking serializes cooperating single inserts and priority updates. */
function dalo_mapping_insert_single(PDO $pdo, $config, $username, $group, $priority) {
    global $logDebugSQL;
    $username = dalo_mapping_name($username, 64);
    $group = dalo_mapping_name($group, 64);
    $priority = dalo_mapping_priority($group, $priority);
    $table = dalo_mapping_prepare($pdo, $config, $username, array($group));
    $sql = "SELECT id FROM $table WHERE username=? AND groupname=? ORDER BY id FOR UPDATE";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($username, $group));
    $exists = $stmt->fetchColumn() !== false;
    $stmt->closeCursor();
    $logDebugSQL .= "$sql;\n";
    if ($exists) { return false; }
    $sql = "INSERT INTO $table (username,groupname,priority) VALUES (?,?,?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($username, $group, $priority));
    $logDebugSQL .= "$sql;\n";
    return $stmt->rowCount() === 1;
}

/** Preserve legacy duplicate-collapse behavior within the caller's transaction. */
function dalo_mapping_update_priority(PDO $pdo, $config, $username, $group, $priority) {
    global $logDebugSQL;
    $username = dalo_mapping_name($username, 64);
    $group = dalo_mapping_name($group, 64);
    $priority = dalo_mapping_priority($group, $priority);
    $table = dalo_mapping_prepare($pdo, $config, $username, array($group));
    $sql = "SELECT priority FROM $table WHERE username=? AND groupname=? ORDER BY id FOR UPDATE";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($username, $group));
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $stmt->closeCursor();
    $logDebugSQL .= "$sql;\n";
    if (!$rows) { return false; }
    if (count($rows) > 1) {
        $sql = "DELETE FROM $table WHERE username=? AND groupname=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($username, $group));
        $logDebugSQL .= "$sql;\n";
        $sql = "INSERT INTO $table (username,groupname,priority) VALUES (?,?,?)";
        $values = array($username, $group, $priority);
    } else {
        $sql = "UPDATE $table SET priority=? WHERE username=? AND groupname=?";
        $values = array($priority, $username, $group);
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);
    $logDebugSQL .= "$sql;\n";
    return true; // An unchanged update is successful after the locked existence check.
}
