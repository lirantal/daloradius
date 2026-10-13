<?php
/* UNIT-016: consistent InnoDB read snapshot with atomic file publication. */

function dalo_backup_tables_from_post($post, $allowed, $config) {
    $tables = array();
    foreach ($allowed as $key) {
        if (!isset($post[$key]) || $post[$key] === 'no' || $post[$key] === '') {
            continue;
        }
        if ($post[$key] !== 'yes' || !isset($config[$key]) ||
            !is_string($config[$key]) ||
            !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $config[$key])) {
            throw new InvalidArgumentException('Invalid backup table selection');
        }
        $tables[] = $config[$key];
    }
    if (!$tables) {
        throw new InvalidArgumentException('Select at least one backup table');
    }
    // Some aliases may resolve to the same physical table: export it only once.
    return array_values(array_unique($tables));
}

function dalo_backup_write($fh, $data) {
    $length = strlen($data);
    for ($offset = 0; $offset < $length;) {
        $written = fwrite($fh, substr($data, $offset));
        if ($written === false || $written === 0) {
            throw new RuntimeException('Could not write backup snapshot');
        }
        $offset += $written;
    }
}

function dalo_backup_literal(PDO $pdo, $value, $meta) {
    if ($value === null) {
        return 'NULL';
    }
    $type = strtoupper((string) ($meta['native_type'] ?? ''));
    if (in_array($type, array('BLOB','TINY_BLOB','MEDIUM_BLOB','LONG_BLOB','BIT','GEOMETRY'), true)) {
        return '0x' . bin2hex((string) $value);
    }
    $quoted = $pdo->quote((string) $value);
    if ($quoted === false) {
        throw new RuntimeException('Could not encode backup value');
    }
    return $quoted;
}

/** Return published file path and table names; never publish partial output. */
function dalo_create_backup_snapshot(PDO $pdo, $tables, $directory, $afterTable = null) {
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') {
        // The existing restore page consumes MySQL-specific backtick INSERT syntax.
        throw new RuntimeException('Backup export requires MySQL/MariaDB');
    }
    if (!is_array($tables) || !$tables || !is_string($directory) ||
        !is_dir($directory) || !is_writable($directory)) {
        throw new InvalidArgumentException('Invalid backup destination');
    }
    foreach ($tables as $table) {
        if (!is_string($table) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $table)) {
            throw new InvalidArgumentException('Invalid backup table');
        }
    }
    $temp = tempnam($directory, '.backup-staging-');
    if ($temp === false) {
        throw new RuntimeException('Could not stage backup file');
    }
    $fh = null;
    try {
        $fh = fopen($temp, 'wb');
        if ($fh === false || !chmod($temp, 0600)) {
            throw new RuntimeException('Could not secure backup file');
        }
        $pdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        if (!$pdo->beginTransaction()) {
            throw new RuntimeException('Could not start backup snapshot');
        }
        // Never buffer a whole table in PHP (the restore format still uses
        // one INSERT statement per nonempty table).
        $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        // MySQL's first consistent (non-locking) SELECT establishes one MVCC
        // snapshot; subsequent selected tables read from the same transaction.
        $exported = array();
        foreach ($tables as $table) {
            $engine = $pdo->prepare('SELECT ENGINE FROM information_schema.TABLES '
                . 'WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:table');
            $engine->execute(array(':table' => $table));
            $value = $engine->fetchColumn();
            $engine->closeCursor();
            if (strtoupper((string) $value) !== 'INNODB') {
                throw new RuntimeException('Backup requires an InnoDB table');
            }
            $quotedTable = '`' . $table . '`';
            $metaQuery = $pdo->query("SELECT * FROM $quotedTable LIMIT 0");
            $columns = array();
            $metas = array();
            for ($i = 0; $i < $metaQuery->columnCount(); $i++) {
                $meta = $metaQuery->getColumnMeta($i);
                $column = $meta['name'] ?? null;
                if (!is_string($column) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $column)) {
                    throw new RuntimeException('Invalid backup column');
                }
                $columns[] = '`' . $column . '`';
                $metas[] = $meta;
            }
            $metaQuery->closeCursor();
            if (!$columns) {
                throw new RuntimeException('Backup table has no columns');
            }
            $rows = $pdo->query("SELECT * FROM $quotedTable");
            $prefix = 'INSERT INTO ' . $quotedTable . ' (' . implode(', ', $columns) . ') VALUES ';
            $first = true;
            while (($row = $rows->fetch(PDO::FETCH_NUM)) !== false) {
                if ($first) {
                    dalo_backup_write($fh, $prefix);
                    $first = false;
                } else {
                    dalo_backup_write($fh, ', ');
                }
                $values = array();
                foreach ($row as $i => $value) {
                    $values[] = dalo_backup_literal($pdo, $value, $metas[$i]);
                }
                dalo_backup_write($fh, '(' . implode(', ', $values) . ')');
            }
            $rows->closeCursor();
            if (!$first) {
                dalo_backup_write($fh, ";\n\n\n");
                $exported[] = $table;
            }
            if ($afterTable !== null) {
                $afterTable($table);
            }
        }
        $pdo->commit();
        if (!fflush($fh) || (function_exists('fsync') && !fsync($fh))) {
            throw new RuntimeException('Could not flush backup file');
        }
        if (!fclose($fh)) {
            $fh = null;
            throw new RuntimeException('Could not close backup file');
        }
        $fh = null;
        if (filesize($temp) === false || filesize($temp) < 1) {
            throw new RuntimeException('Backup produced no rows');
        }
        // Same-directory hard link is an atomic no-overwrite publication.
        // The temporary filename is ignored by the backup manager.
        $final = $directory . '/backup-' . date('Ymd-His') . '-'
               . bin2hex(random_bytes(6)) . '.sql';
        if (!link($temp, $final)) {
            throw new RuntimeException('Could not publish backup file');
        }
        unlink($temp);
        return array($final, $exported);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if (is_resource($fh)) {
            fclose($fh);
        }
        if (is_file($temp)) {
            unlink($temp);
        }
        throw $error;
    }
}
