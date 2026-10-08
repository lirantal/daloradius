<?php
/* UNIT-021: duplicate a profile's check/reply attributes on one PDO transaction. */
require_once __DIR__ . '/plan_create.php';

function dalo_profile_duplicate_tables($config) {
    $tables = array();
    foreach (array('CONFIG_DB_TBL_RADGROUPCHECK', 'CONFIG_DB_TBL_RADGROUPREPLY',
                   'CONFIG_DB_TBL_RADUSERGROUP') as $key) {
        $tables[$key] = dalo_plan_table($config, $key);
    }
    return $tables;
}

function dalo_profile_duplicate_name($value) {
    if (!is_string($value) || strpos($value, "\0") !== false) {
        throw new InvalidArgumentException('Invalid profile name');
    }
    $name = trim($value);
    if ($name === '' || (function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name)) > 64) {
        throw new InvalidArgumentException('Invalid profile name');
    }
    return $name;
}

/** Same group sources as the operator's selector, but all reads use PDO. */
function dalo_profile_duplicate_list(PDO $pdo, $tables) {
    $check = $tables['CONFIG_DB_TBL_RADGROUPCHECK'];
    $reply = $tables['CONFIG_DB_TBL_RADGROUPREPLY'];
    $users = $tables['CONFIG_DB_TBL_RADUSERGROUP'];
    return $pdo->query("SELECT groupname FROM $check UNION SELECT groupname FROM $reply
                        UNION SELECT groupname FROM $users ORDER BY groupname")
               ->fetchAll(PDO::FETCH_COLUMN);
}

function dalo_profile_duplicate_require_innodb(PDO $pdo, $tables) {
    $names = array_map(function ($key) use ($tables) {
        return trim($tables[$key], '`');
    }, array('CONFIG_DB_TBL_RADGROUPCHECK', 'CONFIG_DB_TBL_RADGROUPREPLY'));
    $stmt = $pdo->prepare('SELECT TABLE_NAME, ENGINE FROM INFORMATION_SCHEMA.TABLES
                           WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN (?, ?)');
    $stmt->execute($names);
    $engines = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($names as $name) {
        if (!isset($engines[$name]) || strcasecmp($engines[$name], 'InnoDB') !== 0) {
            throw new RuntimeException('Profile duplication requires transactional tables');
        }
    }
}

/** Check/reply are copied, never the source profile's user or plan memberships. */
function dalo_profile_duplicate(PDO $pdo, $config, $source, $target) {
    $source = dalo_profile_duplicate_name($source);
    $target = dalo_profile_duplicate_name($target);
    if ($source === $target) {
        throw new InvalidArgumentException('Source and target profiles must differ');
    }
    $tables = dalo_profile_duplicate_tables($config);
    dalo_profile_duplicate_require_innodb($pdo, $tables);
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start profile duplication');
    }
    try {
        // Lock the source rows to prevent deletion or modification during the copy.
        $sourceCount = 0;
        foreach (array('CONFIG_DB_TBL_RADGROUPCHECK', 'CONFIG_DB_TBL_RADGROUPREPLY') as $key) {
            $table = $tables[$key];
            $stmt = $pdo->prepare("SELECT id FROM $table WHERE groupname=:source
                                   AND BINARY groupname=:exact ORDER BY id FOR UPDATE");
            $stmt->execute(array(':source' => $source, ':exact' => $source));
            $sourceCount += count($stmt->fetchAll(PDO::FETCH_COLUMN));
        }
        if ($sourceCount === 0) {
            // A radusergroup-only selector entry has no attributes to duplicate.
            throw new DomainException('Source profile has no attributes');
        }
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SELECT id FROM $table WHERE groupname=:target
                                   ORDER BY id LIMIT 1 FOR UPDATE");
            $stmt->execute(array(':target' => $target));
            if ($stmt->fetchColumn() !== false) {
                throw new DomainException('Target profile already exists');
            }
            $stmt->closeCursor();
        }
        $copied = 0;
        foreach (array('CONFIG_DB_TBL_RADGROUPCHECK', 'CONFIG_DB_TBL_RADGROUPREPLY') as $key) {
            $table = $tables[$key];
            $stmt = $pdo->prepare("INSERT INTO $table (groupname, attribute, op, value)
                                   SELECT :target, attribute, op, value FROM $table
                                   WHERE groupname=:source AND BINARY groupname=:exact");
            $stmt->execute(array(':target' => $target, ':source' => $source,
                                 ':exact' => $source));
            $copied += $stmt->rowCount();
        }
        if ($copied !== $sourceCount) {
            throw new RuntimeException('Profile changed while duplicating');
        }
        $pdo->commit();
        return $copied;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}
