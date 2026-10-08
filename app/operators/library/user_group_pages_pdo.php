<?php
/** R05: explicit PDO page operations, sharing mapping-provider parent locks. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/user_group_pages_pdo.php') !== false) {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../include/management/functions.php';
require_once __DIR__ . '/../include/management/groupMappingsPdo.php';
require_once __DIR__ . '/../../common/includes/pdo_connection.php';

function dalo_usergroup_table($config, $key) {
    if (!in_array($key, array('CONFIG_DB_TBL_RADUSERGROUP', 'CONFIG_DB_TBL_DALOUSERINFO',
                              'CONFIG_DB_TBL_RADCHECK'), true)) {
        throw new InvalidArgumentException('Invalid user mapping table');
    }
    $name = $config[$key] ?? null;
    if (!is_string($name) || !preg_match('/\A[A-Za-z_][A-Za-z0-9_]{0,63}\z/', $name)) {
        throw new InvalidArgumentException('Invalid user mapping table');
    }
    return '`' . $name . '`';
}

function dalo_usergroup_query(PDO $pdo, $sql, $values = array()) {
    global $logDebugSQL;
    try {
        $stmt = $pdo->prepare($sql);
        foreach (array_values($values) as $i => $value) {
            $stmt->bindValue($i + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        $logDebugSQL .= "$sql;\n";
        return $stmt;
    } catch (PDOException $error) {
        throw new RuntimeException('User mapping operation failed');
    }
}

/** Own only this page's transaction, never commit a borrowed caller transaction. */
function dalo_usergroup_transaction(PDO $pdo, $operation) {
    if ($pdo->inTransaction() || !$pdo->beginTransaction()) {
        throw new LogicException('User mapping transaction unavailable');
    }
    try {
        $result = $operation();
        if (!$pdo->commit()) { throw new RuntimeException('User mapping commit failed'); }
        return $result;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        throw $error;
    }
}

function dalo_usergroup_create(PDO $pdo, $config, $username, $group, $priority) {
    $username = dalo_mapping_name($username, 64);
    $group = dalo_mapping_name($group, 64);
    $priority = dalo_mapping_priority($group, $priority);
    return dalo_usergroup_transaction($pdo, function() use ($pdo, $config, $username, $group, $priority) {
        if (!dalo_mapping_insert_single($pdo, $config, $username, $group, $priority)) {
            throw new DomainException('Existing user mapping');
        }
        return true;
    });
}

function dalo_usergroup_edit(PDO $pdo, $config, $username, $current, $group, $priority) {
    $username = dalo_mapping_name($username, 64);
    $current = dalo_mapping_name($current, 64);
    $group = dalo_mapping_name($group, 64);
    $priority = dalo_mapping_priority($group, $priority);
    return dalo_usergroup_transaction($pdo, function() use ($pdo, $config, $username, $current, $group, $priority) {
        $table = dalo_mapping_prepare($pdo, $config, $username, array($group));
        $rows = dalo_usergroup_query($pdo, "SELECT username,groupname FROM $table WHERE username=? AND groupname=? ORDER BY id FOR UPDATE",
                                    array($username, $current))->fetchAll(PDO::FETCH_NUM);
        if (!$rows) { throw new DomainException('Stale user mapping'); }
        foreach ($rows as $row) {
            if ($row !== array($username, $current)) { throw new DomainException('Ambiguous user mapping'); }
        }
        if ($group !== $current && dalo_usergroup_query($pdo,
                "SELECT id FROM $table WHERE username=? AND groupname=? ORDER BY id FOR UPDATE",
                array($username, $group))->fetchColumn() !== false) {
            throw new DomainException('Existing destination mapping');
        }
        dalo_usergroup_query($pdo, "UPDATE $table SET groupname=?,priority=? WHERE username=? AND groupname=?",
                            array($group, $priority, $username, $current));
        // Legacy editing changes every duplicate row, rather than collapsing it.
        return true;
    });
}

/** Keep the legacy token for ordinary names; encode only ambiguous delimiter names. */
function dalo_usergroup_selection($username, $group) {
    if (strpos($username, '||') !== false || strpos($group, '||') !== false ||
        strpos($username, 'mapping:') === 0) {
        return 'mapping:' . rawurlencode($username) . '||' . rawurlencode($group);
    }
    return $username . '||' . $group;
}

function dalo_usergroup_parse_selection($value) {
    if (!is_string($value)) { throw new InvalidArgumentException('Invalid user mapping selection'); }
    $encoded = strpos($value, 'mapping:') === 0;
    $parts = explode('||', $encoded ? substr($value, 8) : $value);
    if (count($parts) !== 2) { throw new InvalidArgumentException('Ambiguous user mapping selection'); }
    if ($encoded) {
        foreach ($parts as &$part) {
            $decoded = rawurldecode($part);
            if (rawurlencode($decoded) !== $part) { throw new InvalidArgumentException('Invalid mapping encoding'); }
            $part = $decoded;
        }
        unset($part);
    }
    return array(dalo_mapping_name($parts[0], 64), dalo_mapping_name($parts[1], 64));
}

function dalo_usergroup_delete(PDO $pdo, $config, $post) {
    $input_limit = (int)ini_get('max_input_vars');
    if ($input_limit > 0 && count($post, COUNT_RECURSIVE) >= $input_limit) {
        throw new InvalidArgumentException('Too many mapping controls');
    }
    $pairs = array();
    $all_user = null;
    if (array_key_exists('usergroup', $post)) {
        $input = is_string($post['usergroup']) ? array($post['usergroup']) : $post['usergroup'];
        if (!is_array($input) || !$input || count($input) > 5000) {
            throw new InvalidArgumentException('Invalid mapping selection');
        }
        foreach ($input as $value) {
            $pair = dalo_usergroup_parse_selection($value);
            $pairs[json_encode($pair)] = $pair;
        }
        ksort($pairs, SORT_STRING);
    } else {
        $username = dalo_mapping_name($post['username'] ?? null, 64);
        $group = $post['group'] ?? '';
        if (!is_string($group)) { throw new InvalidArgumentException('Invalid group selection'); }
        if (trim($group) === '') { $all_user = $username; }
        else { $pairs[] = array($username, dalo_mapping_name($group, 64)); }
    }
    $table = dalo_usergroup_table($config, 'CONFIG_DB_TBL_RADUSERGROUP');
    $parent = dalo_usergroup_table($config, 'CONFIG_DB_TBL_RADCHECK');
    dalo_attribute_pdo_require_innodb($pdo, array($parent, $table));
    return dalo_usergroup_transaction($pdo, function() use ($pdo, $table, $parent, $pairs, $all_user) {
        $users = $all_user !== null ? array($all_user) : array_unique(array_column($pairs, 0));
        sort($users, SORT_STRING);
        foreach ($users as $username) {
            // Lock an existing parent, but allow cleanup of an orphaned mapping.
            dalo_usergroup_query($pdo, "SELECT id FROM $parent WHERE username=? ORDER BY id FOR UPDATE",
                                array($username))->fetchAll(PDO::FETCH_COLUMN);
        }
        $locked = array();
        if ($all_user !== null) {
            $locked = dalo_usergroup_query($pdo, "SELECT id,username,groupname FROM $table WHERE username=? ORDER BY id FOR UPDATE",
                                          array($all_user))->fetchAll(PDO::FETCH_NUM);
            if (!$locked) { throw new DomainException('Stale user mappings'); }
            foreach ($locked as $row) {
                if ($row[1] !== $all_user) { throw new DomainException('Ambiguous user mappings'); }
            }
        } else {
            foreach ($pairs as $pair) {
                $rows = dalo_usergroup_query($pdo, "SELECT id,username,groupname FROM $table WHERE username=? AND groupname=? ORDER BY id FOR UPDATE",
                                            $pair)->fetchAll(PDO::FETCH_NUM);
                if (!$rows) { throw new DomainException('Stale user mapping'); }
                foreach ($rows as $row) {
                    if (array_slice($row, 1) !== $pair) { throw new DomainException('Ambiguous user mapping'); }
                    $locked[$row[0]] = $row;
                }
            }
        }
        // All selections have been verified before the first write.
        $count = 0;
        foreach ($locked as $row) {
            $count += dalo_usergroup_query($pdo, "DELETE FROM $table WHERE id=?", array((int)$row[0]))->rowCount();
        }
        return array($count, count($users));
    });
}
