<?php
/* UNIT-020: profile removals, attributes and dependent mappings on PDO. */
require_once __DIR__ . '/plan_create.php';

function dalo_profile_delete_tables($config) {
    $keys = array('CONFIG_DB_TBL_RADGROUPCHECK', 'CONFIG_DB_TBL_RADGROUPREPLY',
                  'CONFIG_DB_TBL_RADUSERGROUP', 'CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES');
    $tables = array();
    foreach ($keys as $key) {
        $tables[$key] = dalo_plan_table($config, $key);
    }
    return $tables;
}

function dalo_profile_delete_require_innodb(PDO $pdo, $tables) {
    $names = array_values(array_unique(array_map(function ($table) {
        return trim($table, '`');
    }, array_values($tables))));
    $slots = implode(',', array_fill(0, count($names), '?'));
    $stmt = $pdo->prepare("SELECT TABLE_NAME, ENGINE FROM INFORMATION_SCHEMA.TABLES
                            WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ($slots)");
    $stmt->execute($names);
    $engines = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($names as $name) {
        if (!isset($engines[$name]) || strcasecmp($engines[$name], 'InnoDB') !== 0) {
            throw new RuntimeException('Profile deletion requires transactional tables');
        }
    }
}

/** A scalar or ordinary multi-select, validated before the first write. */
function dalo_profile_delete_names($submitted) {
    if (!is_string($submitted) && !is_array($submitted)) {
        throw new InvalidArgumentException('Invalid profile selection');
    }
    $values = is_array($submitted) ? $submitted : array($submitted);
    if (!$values || count($values) > 1000) {
        throw new InvalidArgumentException('Invalid profile selection size');
    }
    $names = array();
    foreach ($values as $name) {
        if (!is_string($name) || $name === '' || $name !== trim($name) ||
            strlen($name) > 256 || strpos($name, "\0") !== false) {
            throw new InvalidArgumentException('Invalid profile name');
        }
        $names[$name] = $name;
    }
    $names = array_values($names);
    sort($names, SORT_STRING);
    return $names;
}

/** Select the same three group sources as the legacy option list. */
function dalo_profile_delete_list(PDO $pdo, $tables) {
    $check = $tables['CONFIG_DB_TBL_RADGROUPCHECK'];
    $reply = $tables['CONFIG_DB_TBL_RADGROUPREPLY'];
    $users = $tables['CONFIG_DB_TBL_RADUSERGROUP'];
    return $pdo->query("SELECT groupname FROM $check UNION SELECT groupname FROM $reply
                        UNION SELECT groupname FROM $users ORDER BY groupname")
               ->fetchAll(PDO::FETCH_COLUMN);
}

/** Lock every selected group before any deletion, including mapping-only groups. */
function dalo_profile_delete_lock(PDO $pdo, $tables, $names) {
    $groups = array();
    foreach (array('CONFIG_DB_TBL_RADGROUPCHECK','CONFIG_DB_TBL_RADGROUPREPLY',
                   'CONFIG_DB_TBL_RADUSERGROUP') as $key) {
        $table = $tables[$key];
        $groups[$key] = $pdo->prepare("SELECT id FROM $table WHERE groupname=:name
                                      AND BINARY groupname=:exact ORDER BY id FOR UPDATE");
    }
    foreach ($names as $name) {
        $found = false;
        foreach ($groups as $stmt) {
            $stmt->execute(array(':name' => $name, ':exact' => $name));
            if ($stmt->fetchColumn() !== false) {
                $found = true;
            }
            $stmt->closeCursor();
        }
        if (!$found) {
            throw new DomainException('A selected profile no longer exists');
        }
    }
}

function dalo_profile_delete_by_name(PDO $pdo, $table, $column, $name) {
    $stmt = $pdo->prepare("DELETE FROM $table WHERE $column=:name AND BINARY $column=:exact");
    $stmt->execute(array(':name' => $name, ':exact' => $name));
}

/** Mapping-only or complete deletion of every selected profile, all or nothing. */
function dalo_profile_delete_groups(PDO $pdo, $config, $names, $mappingsOnly) {
    if (!is_array($names) || !$names || !is_bool($mappingsOnly)) {
        throw new InvalidArgumentException('Invalid profile deletion');
    }
    $tables = dalo_profile_delete_tables($config);
    dalo_profile_delete_require_innodb($pdo, $tables);
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start profile deletion');
    }
    try {
        dalo_profile_delete_lock($pdo, $tables, $names);
        $mapping = $tables['CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES'];
        $userGroup = $tables['CONFIG_DB_TBL_RADUSERGROUP'];
        $check = $tables['CONFIG_DB_TBL_RADGROUPCHECK'];
        $reply = $tables['CONFIG_DB_TBL_RADGROUPREPLY'];
        if (!$mappingsOnly) {
            $lock = $pdo->prepare("SELECT id FROM $mapping WHERE profile_name=:name
                                   AND BINARY profile_name=:exact ORDER BY id FOR UPDATE");
            foreach ($names as $name) {
                $lock->execute(array(':name' => $name, ':exact' => $name));
                $lock->fetchAll(PDO::FETCH_COLUMN);
                $lock->closeCursor();
            }
        }
        foreach ($names as $name) {
            if (!$mappingsOnly) {
                dalo_profile_delete_by_name($pdo, $mapping, 'profile_name', $name);
            }
            dalo_profile_delete_by_name($pdo, $userGroup, 'groupname', $name);
            if (!$mappingsOnly) {
                dalo_profile_delete_by_name($pdo, $check, 'groupname', $name);
                dalo_profile_delete_by_name($pdo, $reply, 'groupname', $name);
            }
        }
        if (!$pdo->commit()) {
            throw new RuntimeException('Could not commit profile deletion');
        }
        return count($names);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

/** Parse existing check/reply attribute controls, splitting from the right. */
function dalo_profile_delete_items($submitted, $config) {
    if (!is_string($submitted) && !is_array($submitted)) {
        throw new InvalidArgumentException('Invalid profile attribute selection');
    }
    $values = is_array($submitted) ? $submitted : array($submitted);
    if (!$values || count($values) > 1000) {
        throw new InvalidArgumentException('Invalid profile attribute selection');
    }
    $tables = dalo_profile_delete_tables($config);
    $allowed = array($config['CONFIG_DB_TBL_RADGROUPCHECK'] => 'CONFIG_DB_TBL_RADGROUPCHECK',
                     $config['CONFIG_DB_TBL_RADGROUPREPLY'] => 'CONFIG_DB_TBL_RADGROUPREPLY');
    if (count($allowed) !== 2) {
        throw new InvalidArgumentException('Ambiguous profile table configuration');
    }
    $items = array();
    foreach ($values as $value) {
        if (!is_string($value) ||
            !preg_match('/^(.+)__([1-9][0-9]{0,9})__([A-Za-z_][A-Za-z0-9_]*)$/D', $value, $parts) ||
            !isset($allowed[$parts[3]])) {
            throw new InvalidArgumentException('Invalid profile attribute');
        }
        $name = dalo_profile_delete_names($parts[1])[0];
        $id = (int) $parts[2];
        $key = $allowed[$parts[3]];
        $token = $key . ':' . $id;
        if (isset($items[$token]) && $items[$token][0] !== $name) {
            throw new InvalidArgumentException('Conflicting profile attribute selection');
        }
        $items[$token] = array($name, $id, $key);
    }
    $items = array_values($items);
    usort($items, function ($left, $right) {
        return strcmp($left[0], $right[0]) ?: strcmp($left[2], $right[2]) ?: ($left[1] <=> $right[1]);
    });
    return $items;
}

/** Delete selected attributes, then orphan group/plan mappings if a profile became empty. */
function dalo_profile_delete_attributes(PDO $pdo, $config, $items) {
    if (!is_array($items) || !$items) {
        throw new InvalidArgumentException('Invalid profile attributes');
    }
    $tables = dalo_profile_delete_tables($config);
    dalo_profile_delete_require_innodb($pdo, $tables);
    $check = $tables['CONFIG_DB_TBL_RADGROUPCHECK'];
    $reply = $tables['CONFIG_DB_TBL_RADGROUPREPLY'];
    $users = $tables['CONFIG_DB_TBL_RADUSERGROUP'];
    $mapping = $tables['CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES'];
    $names = array_values(array_unique(array_column($items, 0)));
    sort($names, SORT_STRING);
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start profile attribute deletion');
    }
    try {
        dalo_profile_delete_lock($pdo, $tables, $names);
        $locked = array();
        foreach (array('CONFIG_DB_TBL_RADGROUPCHECK','CONFIG_DB_TBL_RADGROUPREPLY') as $key) {
            $table = $tables[$key];
            $locked[$key] = $pdo->prepare("SELECT groupname FROM $table WHERE id=:id FOR UPDATE");
        }
        // Reject a stale ID, wrong table or wrong owner before the first DELETE.
        foreach ($items as $item) {
            list($name, $id, $key) = $item;
            $stmt = $locked[$key];
            $stmt->execute(array(':id' => $id));
            $owner = $stmt->fetchColumn();
            $stmt->closeCursor();
            if ($owner === false || $owner !== $name) {
                throw new DomainException('A selected profile attribute no longer matches');
            }
        }
        foreach ($names as $name) {
            $lock = $pdo->prepare("SELECT id FROM $mapping WHERE profile_name=:name
                                   AND BINARY profile_name=:exact ORDER BY id FOR UPDATE");
            $lock->execute(array(':name' => $name, ':exact' => $name));
            $lock->fetchAll(PDO::FETCH_COLUMN);
            $lock->closeCursor();
        }
        foreach ($items as $item) {
            list($name, $id, $key) = $item;
            $table = $tables[$key];
            $delete = $pdo->prepare("DELETE FROM $table WHERE id=:id AND groupname=:name
                                     AND BINARY groupname=:exact");
            $delete->execute(array(':id' => $id, ':name' => $name, ':exact' => $name));
            if ($delete->rowCount() !== 1) {
                throw new RuntimeException('Profile attribute deletion failed');
            }
        }
        $countCheck = $pdo->prepare("SELECT COUNT(*) FROM $check WHERE groupname=:name AND BINARY groupname=:exact");
        $countReply = $pdo->prepare("SELECT COUNT(*) FROM $reply WHERE groupname=:name AND BINARY groupname=:exact");
        foreach ($names as $name) {
            $params = array(':name' => $name, ':exact' => $name);
            $countCheck->execute($params);
            $remaining = (int) $countCheck->fetchColumn();
            $countCheck->closeCursor();
            $countReply->execute($params);
            $remaining += (int) $countReply->fetchColumn();
            $countReply->closeCursor();
            if ($remaining === 0) {
                dalo_profile_delete_by_name($pdo, $users, 'groupname', $name);
                dalo_profile_delete_by_name($pdo, $mapping, 'profile_name', $name);
            }
        }
        if (!$pdo->commit()) {
            throw new RuntimeException('Could not commit profile attribute deletion');
        }
        return count($names);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}
