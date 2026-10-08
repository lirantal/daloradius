<?php
/* R04: shared profile/group reads and single-handle atomic mutations. */
require_once __DIR__ . '/attributes_pdo.php';
require_once __DIR__ . '/attributes.php';
require_once __DIR__ . '/../../common/includes/pdo_connection.php';

function dalo_group_table($config, $key) {
    $allowed = array('CONFIG_DB_TBL_RADGROUPCHECK','CONFIG_DB_TBL_RADGROUPREPLY',
                     'CONFIG_DB_TBL_RADUSERGROUP','CONFIG_DB_TBL_DALODICTIONARY');
    $name = $config[$key] ?? null;
    if (!in_array($key, $allowed, true) || !is_string($name) ||
        !preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,63}$/D', $name)) {
        throw new InvalidArgumentException('Invalid group table configuration');
    }
    return '`' . $name . '`';
}

function dalo_group_query(PDO $pdo, $sql, $params = array()) {
    global $logDebugSQL;
    try {
        $stmt = $pdo->prepare($sql);
        foreach (array_values($params) as $i => $value) {
            $stmt->bindValue($i + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        $logDebugSQL .= "$sql;\n";
        return $stmt;
    } catch (PDOException $error) {
        throw new RuntimeException('Group database operation failed');
    }
}

function dalo_profile_write(PDO $pdo, $config, $name, $post, $ops, $create) {
    if (!is_string($name) || trim($name) === '' || !preg_match('//u', $name) ||
        strpos($name, "\0") !== false || dalo_attribute_pdo_length($name) > 64 || $pdo->inTransaction()) {
        throw new InvalidArgumentException('Invalid profile or transaction');
    }
    $name = trim($name);
    $skip = array('profile','profile_name','submit','csrf_token');
    $entries = dalo_attribute_pdo_parse($post, $skip, $config, $ops, $create, 'group');
    if ($create && !$entries) { throw new InvalidArgumentException('Empty profile attributes'); }
    $tables = array();
    foreach (array('CONFIG_DB_TBL_RADGROUPCHECK','CONFIG_DB_TBL_RADGROUPREPLY','CONFIG_DB_TBL_RADUSERGROUP') as $key) {
        $tables[] = dalo_group_table($config, $key);
    }
    dalo_attribute_pdo_require_innodb($pdo, $tables);
    $lock = 'dalo-profile-' . substr(hash('sha256', $pdo->query('SELECT DATABASE()')->fetchColumn() . implode(',', $tables)), 0, 48);
    $stmt = dalo_group_query($pdo, 'SELECT GET_LOCK(?,10)', array($lock));
    if ((int)$stmt->fetchColumn() !== 1) { throw new RuntimeException('Profile is busy'); }
    $stmt->closeCursor();
    try {
        if (!$pdo->beginTransaction()) { throw new RuntimeException('Profile transaction unavailable'); }
        $found = false;
        foreach ($tables as $table) {
            $rows = dalo_group_query($pdo, "SELECT groupname FROM $table WHERE groupname=? ORDER BY id FOR UPDATE", array($name))->fetchAll(PDO::FETCH_COLUMN);
            foreach ($rows as $stored) {
                if (!$create && $stored !== $name) { throw new DomainException('Ambiguous profile'); }
            }
            $found = $found || count($rows) > 0;
        }
        if ($create ? $found : !$found) { throw new DomainException('Existing or stale profile'); }
        $count = dalo_handle_attributes_pdo($pdo, $config, $post, $name, $skip, $ops, $create, 'group');
        if (!$pdo->commit()) { throw new RuntimeException('Profile commit failed'); }
        return $count;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        throw $error;
    } finally {
        // Release best-effort; teardown of the nonpersistent handle is the fallback.
        // An unlock error after commit must not turn committed state into failure.
        try { dalo_group_query($pdo, 'SELECT RELEASE_LOCK(?)', array($lock))->closeCursor(); }
        catch (Throwable $ignored) { error_log('Profile lock release failed'); }
    }
}

function dalo_group_delete(PDO $pdo, $config, $key, $input) {
    if (!in_array($key, array('CONFIG_DB_TBL_RADGROUPCHECK','CONFIG_DB_TBL_RADGROUPREPLY'), true)) {
        throw new InvalidArgumentException('Invalid group attribute family');
    }
    $values = is_string($input) ? array($input) : $input;
    if (!is_array($values) || !$values || count($values) > 5000 || $pdo->inTransaction()) {
        throw new InvalidArgumentException('Invalid attribute selection');
    }
    $ids = array();
    foreach ($values as $value) {
        if (!is_string($value) || !preg_match('/^record-([1-9][0-9]{0,9})$/D', $value, $match) ||
            (float)$match[1] > 4294967295) { throw new InvalidArgumentException('Invalid attribute ID'); }
        $ids[(int)$match[1]] = (int)$match[1];
    }
    sort($ids, SORT_NUMERIC);
    $table = dalo_group_table($config, $key);
    dalo_attribute_pdo_require_innodb($pdo, array($table));
    if (!$pdo->beginTransaction()) { throw new RuntimeException('Delete transaction unavailable'); }
    try {
        foreach ($ids as $id) {
            if (dalo_group_query($pdo, "SELECT id FROM $table WHERE id=? FOR UPDATE", array($id))->fetchColumn() === false) {
                throw new DomainException('Stale group attribute');
            }
        }
        $count = 0;
        foreach ($ids as $id) {
            $count += dalo_group_query($pdo, "DELETE FROM $table WHERE id=?", array($id))->rowCount();
        }
        if (!$pdo->commit()) { throw new RuntimeException('Delete commit failed'); }
        return $count;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        throw $error;
    }
}
