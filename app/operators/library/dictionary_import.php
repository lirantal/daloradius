<?php
/* UNIT-022: validated, transactional import of one vendor's dictionary. */

function dalo_dictionary_table($config) {
    $name = $config['CONFIG_DB_TBL_DALODICTIONARY'] ?? null;
    if (!is_string($name) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $name)) {
        throw new InvalidArgumentException('Invalid dictionary table configuration');
    }
    return '`' . $name . '`';
}

function dalo_dictionary_import_text($input, $autoDetect, $manualVendor) {
    if (!is_string($input) || trim($input) === '' || !is_bool($autoDetect) ||
        !is_string($manualVendor)) {
        throw new InvalidArgumentException('Invalid dictionary import');
    }
    $vendor = $autoDetect ? '' : trim($manualVendor);
    $attributes = array();
    foreach (explode("\n", $input) as $line) {
        $parts = preg_split('/\s+/', trim($line));
        if (count($parts) < 2) {
            continue;
        }
        if ($autoDetect && $parts[0] === 'VENDOR') {
            if ($vendor !== '' && $vendor !== $parts[1]) {
                throw new InvalidArgumentException('Dictionary contains multiple vendors');
            }
            $vendor = $parts[1];
        } elseif ($parts[0] === 'ATTRIBUTE') {
            $attribute = $parts[1];
            $type = count($parts) >= 4 ? $parts[3] : null;
            if ($attribute === '' || strlen($attribute) > 256 ||
                ($type !== null && strlen($type) > 120)) {
                throw new InvalidArgumentException('Invalid dictionary attribute');
            }
            // As in the legacy import, the last entry for a repeated name wins.
            $attributes[$attribute] = $type;
        }
    }
    if ($vendor === '' ||
        (function_exists('mb_strlen') ? mb_strlen($vendor, 'UTF-8') : strlen($vendor)) > 32 ||
        strpos($vendor, "\0") !== false || !$attributes) {
        throw new InvalidArgumentException('Missing or invalid vendor attributes');
    }
    foreach ($attributes as $attribute => $type) {
        if ((function_exists('mb_strlen') ? mb_strlen($attribute, 'UTF-8') : strlen($attribute)) > 64 ||
            ($type !== null &&
             (function_exists('mb_strlen') ? mb_strlen($type, 'UTF-8') : strlen($type)) > 30)) {
            throw new InvalidArgumentException('Dictionary value exceeds schema limits');
        }
    }
    return array($vendor, $attributes);
}

function dalo_dictionary_require_innodb(PDO $pdo, $table) {
    $stmt = $pdo->prepare('SELECT ENGINE FROM INFORMATION_SCHEMA.TABLES
                           WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:name');
    $stmt->execute(array(':name' => trim($table, '`')));
    $engine = $stmt->fetchColumn();
    if ($engine === false || strcasecmp($engine, 'InnoDB') !== 0) {
        throw new RuntimeException('Dictionary import requires an InnoDB table');
    }
}

/** All existence checks and mutations run on the same caller-owned PDO handle. */
function dalo_dictionary_import(PDO $pdo, $config, $strategy, $vendor, $attributes) {
    if (!is_string($strategy) ||
        !in_array($strategy, array('insert_or_update','delete_then_insert','only_insert_new'), true) ||
        !is_string($vendor) || $vendor === '' || !is_array($attributes) || !$attributes) {
        throw new InvalidArgumentException('Invalid dictionary import selection');
    }
    $table = dalo_dictionary_table($config);
    dalo_dictionary_require_innodb($pdo, $table);
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start dictionary import');
    }
    try {
        $lock = $pdo->prepare("SELECT id FROM $table WHERE Vendor=:vendor ORDER BY id FOR UPDATE");
        $lock->execute(array(':vendor' => $vendor));
        $lock->fetchAll(PDO::FETCH_COLUMN);
        $deleted = 0;
        $updated = 0;
        $inserted = 0;
        if ($strategy === 'delete_then_insert') {
            $delete = $pdo->prepare("DELETE FROM $table WHERE Vendor=:vendor");
            $delete->execute(array(':vendor' => $vendor));
            $deleted = $delete->rowCount();
        }
        $exists = $pdo->prepare("SELECT id FROM $table WHERE Vendor=:vendor
                                 AND Attribute=:attribute ORDER BY id FOR UPDATE");
        $update = $pdo->prepare("UPDATE $table SET Type=:type WHERE Vendor=:vendor
                                 AND Attribute=:attribute");
        $insert = $pdo->prepare("INSERT INTO $table (Type, Vendor, Attribute)
                                 VALUES (:type, :vendor, :attribute)");
        foreach ($attributes as $attribute => $type) {
            $values = array(':vendor' => $vendor, ':attribute' => $attribute);
            $exists->execute($values);
            $found = $exists->fetchColumn() !== false;
            $exists->closeCursor();
            if ($found) {
                if ($strategy === 'insert_or_update') {
                    $update->execute($values + array(':type' => $type));
                    $updated += $update->rowCount();
                }
                continue;
            }
            $insert->execute($values + array(':type' => $type));
            $inserted += $insert->rowCount();
        }
        if (!$pdo->commit()) {
            throw new RuntimeException('Dictionary import was not committed');
        }
        return array('processed' => count($attributes), 'deleted' => $deleted,
                     'inserted' => $inserted, 'updated' => $updated);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}
