<?php
/*
 * Hash legacy plaintext user portal passwords using PDO and per-row CAS.
 *
 * Usage:
 *   php hash-user-portal-passwords.php --dry-run
 *   php hash-user-portal-passwords.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

$options = getopt('', array('dry-run', 'batch-size::', 'help'));
if (isset($options['help'])) {
    fwrite(STDOUT, "Usage: php hash-user-portal-passwords.php [--dry-run] [--batch-size=N]\n");
    exit(0);
}

$dry_run = isset($options['dry-run']);
$batch_size = isset($options['batch-size']) ? intval($options['batch-size']) : 100;
if ($batch_size < 1 || $batch_size > 1000) {
    fwrite(STDERR, "batch-size must be between 1 and 1000\n");
    exit(2);
}

$includes = dirname(__DIR__, 3) . '/app/common/includes';
try {
    foreach (array('config_read.php', 'daloradius.conf.php', 'portal_password.php', 'pdo_connection.php') as $file) {
        if (!is_file($includes . '/' . $file) || !is_readable($includes . '/' . $file)) {
            throw new RuntimeException('Migration configuration is unavailable');
        }
    }
    require_once $includes . '/config_read.php';
    require_once $includes . '/portal_password.php';
    require_once $includes . '/pdo_connection.php';
} catch (Throwable $error) {
    fwrite(STDERR, "Unable to initialize password migration.\n");
    exit(1);
}

try {
    $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
} catch (Throwable $error) {
    // Never expose a PDO exception, SQL template, binding or connection value.
    fwrite(STDERR, "Unable to connect to the database.\n");
    exit(1);
}

$last_id = 0;
$counts = array(
    'scanned' => 0,
    'empty' => 0,
    'already_hashed' => 0,
    'migrated' => 0,
    'conflicted' => 0,
    'failed' => 0,
);

$migration_ready = false;
try {
    $table = $configValues['CONFIG_DB_TBL_DALOUSERINFO'] ?? null;
    if (!is_string($table) || strlen($table) > 64 || !preg_match('/\A[A-Za-z0-9_]+\z/D', $table)) {
        throw new InvalidArgumentException('Invalid user information table');
    }
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if (!in_array($driver, array('mysql', 'pgsql'), true)) {
        throw new RuntimeException('Unsupported migration driver');
    }
    $quote = $driver === 'mysql' ? '`' : '"';
    $quoted_table = $quote . $table . $quote;
    $schema = $driver === 'mysql' ? 'DATABASE()' : 'current_schema()';

    // The existing schema migration must precede password conversion. Without
    // this preflight, permissive SQL mode can silently truncate a stored hash.
    $metadata = $pdo->prepare("SELECT t.TABLE_TYPE AS kind, c.DATA_TYPE AS type,
                                      c.CHARACTER_MAXIMUM_LENGTH AS capacity
                                FROM information_schema.TABLES t
                                JOIN information_schema.COLUMNS c
                                  ON c.TABLE_SCHEMA=t.TABLE_SCHEMA AND c.TABLE_NAME=t.TABLE_NAME
                               WHERE t.TABLE_SCHEMA=$schema AND t.TABLE_NAME=?
                                 AND c.COLUMN_NAME='portalloginpassword'");
    $metadata->execute(array($table));
    $column = $metadata->fetch();
    $metadata->closeCursor();
    if (!$column || $column['kind'] !== 'BASE TABLE' ||
        !in_array(strtolower($column['type']), array('varchar', 'character varying', 'text', 'tinytext', 'mediumtext', 'longtext'), true) ||
        ($column['capacity'] !== null && (int) $column['capacity'] < 255)) {
        throw new RuntimeException('Password storage requires the existing schema upgrade');
    }
    // TABLE_CONSTRAINTS can hide rows from SELECT-only accounts. Read the
    // visible key columns directly so --dry-run does not require write grants.
    $primary_condition = $driver === 'mysql'
        ? "k.CONSTRAINT_NAME='PRIMARY'"
        : "k.CONSTRAINT_NAME=(SELECT conname FROM pg_constraint WHERE conrelid=to_regclass(?) AND contype='p')";
    $metadata = $pdo->prepare("SELECT k.COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE k
                               WHERE k.TABLE_SCHEMA=$schema AND k.TABLE_NAME=? AND $primary_condition
                               ORDER BY k.ORDINAL_POSITION");
    $primary_parameters = array($table);
    if ($driver === 'pgsql') {
        $primary_parameters[] = $quoted_table;
    }
    $metadata->execute($primary_parameters);
    if ($metadata->fetchAll(PDO::FETCH_COLUMN) !== array('id')) {
        throw new RuntimeException('User information requires a unique primary ID');
    }
    $metadata->closeCursor();
    $metadata = null;
    $match_condition = dalo_portal_password_match_condition($driver);
    $migration_ready = true;
} catch (Throwable $error) {
    $counts['failed']++;
}

// Autocommit is intentional: each bytewise conditional UPDATE stands alone.
// Earlier successful rows remain migrated if a later row fails or conflicts.
while ($migration_ready) {
    try {
        $select = $pdo->prepare("SELECT id, portalloginpassword FROM $quoted_table
                                 WHERE id > ? ORDER BY id ASC LIMIT ?");
        $select->bindValue(1, $last_id, PDO::PARAM_INT);
        $select->bindValue(2, $batch_size, PDO::PARAM_INT);
        $select->execute();
        $rows = $select->fetchAll(PDO::FETCH_ASSOC);
        $select->closeCursor();
        $select = null;
    } catch (Throwable $error) {
        $counts['failed']++;
        break;
    }
    if (count($rows) === 0) {
        break;
    }

    foreach ($rows as $row) {
        $id = filter_var($row['id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
        if ($id === false || $id <= $last_id) {
            // Prevent a nonrepresentable ID from trapping the keyset scan.
            $counts['failed']++;
            break 2;
        }
        $stored_password = (string) $row['portalloginpassword'];
        $last_id = $id;
        $counts['scanned']++;

        if ($stored_password === '') {
            $counts['empty']++;
            continue;
        }
        if (dalo_portal_password_is_hash($stored_password)) {
            $counts['already_hashed']++;
            continue;
        }
        if ($dry_run) {
            $counts['migrated']++;
            continue;
        }
        $hash = dalo_portal_password_hash($stored_password);
        if ($hash === false) {
            $counts['failed']++;
            continue;
        }

        try {
            $update = $pdo->prepare("UPDATE $quoted_table SET portalloginpassword=? WHERE id=? AND $match_condition");
            $update->execute(array($hash, $id, $stored_password));
            $affected_rows = $update->rowCount();
            $update->closeCursor();
            $update = null;
            if ($affected_rows === 1) {
                $counts['migrated']++;
            } else {
                $counts['conflicted']++;
            }
        } catch (Throwable $error) {
            $counts['failed']++;
        }
    }
}

// Release buffered credential batches/statements; no value is ever logged.
$rows = $row = $stored_password = $hash = $select = $update = $metadata = null;
$pdo = null;
fwrite(STDOUT, sprintf(
    "%s: scanned=%d empty=%d already_hashed=%d %s=%d conflicted=%d failed=%d\n",
    $dry_run ? 'DRY RUN' : 'COMPLETE',
    $counts['scanned'],
    $counts['empty'],
    $counts['already_hashed'],
    $dry_run ? 'would_migrate' : 'migrated',
    $counts['migrated'],
    $counts['conflicted'],
    $counts['failed']
));
exit($counts['failed'] === 0 ? 0 : 1);
