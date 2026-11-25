<?php
/** CLI runner for the bundled MariaDB schema migrations (PDO). */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

const DALO_MIGRATE_EXIT_OK = 0;
const DALO_MIGRATE_EXIT_ERROR = 1;
const DALO_MIGRATE_EXIT_USAGE = 2;
const DALO_MIGRATE_EXIT_PENDING = 10;

$root = dirname(__DIR__, 3);
$configFile = $root . '/app/common/includes/daloradius.conf.php';
$migrationsDir = $root . '/contrib/db/migrations';
$locationName = 'default';
$mode = 'preview'; // preview | apply | baseline-fresh | check-pending
$lockTimeout = 30;
$verbose = false;
$help = false;
$colorMode = 'auto';

$output = function ($message, $tone = 'info', $stream = null) use (&$colorMode) {
    $stream = $stream ?? STDOUT;
    $noColor = getenv('NO_COLOR');
    $enabled = $colorMode === 'always' || ($colorMode === 'auto'
        && ($noColor === false || $noColor === '') && getenv('TERM') !== 'dumb'
        && function_exists('stream_isatty') && stream_isatty($stream));
    $colors = array(
        'title' => '1;36',
        'success' => '32',
        'warning' => '33',
        'error' => '31',
        'info' => '36',
        'muted' => '90',
    );
    fwrite($stream, $enabled ? "\033[" . $colors[$tone] . 'm' . $message . "\033[0m\n" : $message . "\n");
};

$setMode = function ($newMode) use (&$mode, $output) {
    if ($mode !== 'preview' && $mode !== $newMode) {
        $output('Only one of --apply, --baseline-fresh or --check-pending may be used.', 'error', STDERR);
        exit(DALO_MIGRATE_EXIT_USAGE);
    }
    $mode = $newMode;
};

foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--apply') {
        $setMode('apply');
    } elseif ($arg === '--baseline-fresh') {
        $setMode('baseline-fresh');
    } elseif ($arg === '--check-pending') {
        $setMode('check-pending');
    } elseif (strpos($arg, '--config=') === 0) {
        $configFile = substr($arg, 9);
    } elseif (strpos($arg, '--location=') === 0) {
        $locationName = substr($arg, 11);
        if ($locationName === '') {
            $output('Location name must not be empty.', 'error', STDERR);
            exit(DALO_MIGRATE_EXIT_USAGE);
        }
    } elseif (strpos($arg, '--migrations-dir=') === 0) {
        $migrationsDir = substr($arg, 17);
    } elseif (strpos($arg, '--lock-timeout=') === 0) {
        $rawTimeout = substr($arg, 15);
        if (!ctype_digit($rawTimeout) || (int) $rawTimeout > 300) {
            $output('Invalid lock timeout. Use an integer between 0 and 300.', 'error', STDERR);
            exit(DALO_MIGRATE_EXIT_USAGE);
        }
        $lockTimeout = (int) $rawTimeout;
    } elseif ($arg === '--verbose' || $arg === '-v') {
        $verbose = true;
    } elseif ($arg === '--no-color') {
        $colorMode = 'never';
    } elseif (strpos($arg, '--color=') === 0) {
        $colorMode = substr($arg, 8);
        if (!in_array($colorMode, array('auto', 'always', 'never'), true)) {
            $colorMode = 'never';
            $output('Invalid color mode. Use auto, always or never.', 'error', STDERR);
            exit(DALO_MIGRATE_EXIT_USAGE);
        }
    } elseif ($arg === '--help' || $arg === '-h') {
        $help = true;
    } else {
        $output('Unknown argument. Use --help.', 'error', STDERR);
        exit(DALO_MIGRATE_EXIT_USAGE);
    }
}

if ($help) {
    $output(
        "Usage: php migrate-db.php [--apply | --baseline-fresh | --check-pending]\n"
        . "                         [--config=/path/to/daloradius.conf.php]\n"
        . "                         [--location=NAME]\n"
        . "                         [--migrations-dir=/path/to/migrations]\n"
        . "                         [--lock-timeout=SECONDS]\n"
        . "                         [--verbose|-v]\n"
        . "                         [--color=auto|always|never] [--no-color]\n"
        . "Default: read-only preview. Back up your database before --apply.\n"
        . "--check-pending exits 0 when up to date, 10 when pending migrations exist.\n"
        . "--baseline-fresh records bundled migrations on a newly seeded schema without running SQL."
    );
    exit(DALO_MIGRATE_EXIT_OK);
}

/**
 * Split a MariaDB SQL migration script into individual executable statements.
 * Respects single/double-quoted strings, backtick identifiers, and SQL comments.
 */
function dalo_split_sql_statements($sql) {
    $statements = array();
    $buffer = '';
    $length = strlen($sql);
    $inSingle = false;
    $inDouble = false;
    $inBacktick = false;
    $inLineComment = false;
    $inBlockComment = false;

    for ($i = 0; $i < $length; $i++) {
        $ch = $sql[$i];
        $next = ($i + 1 < $length) ? $sql[$i + 1] : '';

        if ($inLineComment) {
            if ($ch === "\n") {
                $inLineComment = false;
                $buffer .= $ch;
            }
            continue;
        }

        if ($inBlockComment) {
            if ($ch === '*' && $next === '/') {
                $inBlockComment = false;
                $buffer .= ' ';
                $i++;
            }
            continue;
        }

        if ($inSingle) {
            $buffer .= $ch;
            if ($ch === '\\' && $next !== '') {
                $buffer .= $next;
                $i++;
            } elseif ($ch === "'") {
                if ($next === "'") {
                    $buffer .= $next;
                    $i++;
                } else {
                    $inSingle = false;
                }
            }
            continue;
        }

        if ($inDouble) {
            $buffer .= $ch;
            if ($ch === '\\' && $next !== '') {
                $buffer .= $next;
                $i++;
            } elseif ($ch === '"') {
                if ($next === '"') {
                    $buffer .= $next;
                    $i++;
                } else {
                    $inDouble = false;
                }
            }
            continue;
        }

        if ($inBacktick) {
            $buffer .= $ch;
            if ($ch === '`') {
                if ($next === '`') {
                    $buffer .= $next;
                    $i++;
                } else {
                    $inBacktick = false;
                }
            }
            continue;
        }

        // Start of SQL comments outside quotes/identifiers.
        if ($ch === '-' && $next === '-' && ($i + 2 >= $length || ctype_space($sql[$i + 2]))) {
            $inLineComment = true;
            $i++;
            continue;
        }
        if ($ch === '#') {
            $inLineComment = true;
            continue;
        }
        if ($ch === '/' && $next === '*' && ($i + 2 >= $length || $sql[$i + 2] !== '!')) {
            $inBlockComment = true;
            $i++;
            continue;
        }

        if ($ch === "'") {
            $inSingle = true;
            $buffer .= $ch;
            continue;
        }
        if ($ch === '"') {
            $inDouble = true;
            $buffer .= $ch;
            continue;
        }
        if ($ch === '`') {
            $inBacktick = true;
            $buffer .= $ch;
            continue;
        }

        if ($ch === ';') {
            $trimmed = trim($buffer);
            if ($trimmed !== '') {
                $statements[] = $trimmed;
            }
            $buffer = '';
            continue;
        }

        $buffer .= $ch;
    }

    if ($inSingle || $inDouble || $inBacktick || $inBlockComment) {
        throw new RuntimeException('Unterminated SQL string, identifier or comment in migration.');
    }

    $trimmed = trim($buffer);
    if ($trimmed !== '') {
        $statements[] = $trimmed;
    }

    foreach ($statements as $stmt) {
        if (preg_match('/\A(?:DELIMITER|SOURCE|USE)\b/i', $stmt)) {
            throw new RuntimeException('Unsupported mysql client command (DELIMITER, SOURCE or USE) in migration.');
        }
    }

    return $statements;
}

/**
 * Load a PHP migration file in an isolated local variable scope.
 * Returns normalized array('apply' => callable, 'is_applied' => callable|null).
 */
function dalo_load_php_migration($path, $name) {
    $loaded = require $path;
    if (is_callable($loaded)) {
        return array('apply' => $loaded, 'is_applied' => null);
    }
    if (is_array($loaded) && isset($loaded['apply']) && is_callable($loaded['apply'])) {
        $isApplied = null;
        if (array_key_exists('is_applied', $loaded) && $loaded['is_applied'] !== null) {
            if (!is_callable($loaded['is_applied'])) {
                throw new RuntimeException('PHP migration is_applied entry must be callable: ' . $name);
            }
            $isApplied = $loaded['is_applied'];
        }
        return array('apply' => $loaded['apply'], 'is_applied' => $isApplied);
    }
    throw new RuntimeException('PHP migration must return a callable or an array with an apply callable: ' . $name);
}

/** Inspect the operators.password column definition for safe non-shrinking migrations. */
function dalo_operators_password_column(PDO $pdo) {
    $stmt = $pdo->query(
        "SELECT LOWER(DATA_TYPE) AS data_type,
                CHARACTER_MAXIMUM_LENGTH AS max_len,
                UPPER(IS_NULLABLE) AS is_nullable
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = 'operators'
           AND COLUMN_NAME = 'password'"
    );
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return is_array($row) ? $row : null;
}

$pdo = null;
$lockName = null;
$writesStarted = false;
$current = 'preflight';
$statementIndex = null;
$exitCode = DALO_MIGRATE_EXIT_OK;

try {
    $pdoHelper = $root . '/app/common/includes/pdo_connection.php';
    if (!is_file($pdoHelper) || !is_readable($pdoHelper)) {
        throw new RuntimeException('Shared PDO connection helper is unavailable.');
    }
    require_once $pdoHelper;

    if (!class_exists('PDO') || !in_array('mysql', PDO::getAvailableDrivers(), true)) {
        throw new RuntimeException('PHP PDO with the mysql driver (pdo_mysql) is required.');
    }
    if (!is_file($configFile) || !is_readable($configFile)) {
        throw new RuntimeException('A readable trusted configuration file is required.');
    }

    $configValues = array();
    require $configFile;
    if (!isset($configValues) || !is_array($configValues)) {
        throw new RuntimeException('Invalid configuration file.');
    }
    if (!isset($configValues['CONFIG_DB_PORT']) || $configValues['CONFIG_DB_PORT'] === '') {
        $configValues['CONFIG_DB_PORT'] = '3306';
    }
    if ($locationName !== 'default'
        && isset($configValues['CONFIG_LOCATIONS'][$locationName])
        && is_array($configValues['CONFIG_LOCATIONS'][$locationName])
        && (!isset($configValues['CONFIG_LOCATIONS'][$locationName]['Port'])
            || $configValues['CONFIG_LOCATIONS'][$locationName]['Port'] === '')) {
        $configValues['CONFIG_LOCATIONS'][$locationName]['Port'] = '3306';
    }

    $settings = dalo_pdo_settings($configValues, $locationName);
    if (!in_array(strtolower((string) $settings['engine']), array('mysql', 'mysqli'), true)) {
        throw new RuntimeException('Only MariaDB configurations are supported.');
    }

    $standardTables = array(
        'CONFIG_DB_TBL_DALOOPERATORS' => 'operators',
        'CONFIG_DB_TBL_DALOOPERATORS_ACL' => 'operators_acl',
        'CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES' => 'operators_acl_files',
        'CONFIG_DB_TBL_DALOUSERINFO' => 'userinfo',
        'CONFIG_DB_TBL_RADACCT' => 'radacct',
        'CONFIG_DB_TBL_RADPOSTAUTH' => 'radpostauth',
        'CONFIG_DB_TBL_RADCHECK' => 'radcheck',
    );
    foreach ($standardTables as $key => $table) {
        if (($configValues[$key] ?? $table) !== $table) {
            throw new RuntimeException('Bundled migrations require the standard table names.');
        }
    }

    $pdo = dalo_pdo_connect($configValues, $locationName);

    $version = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
    if (stripos($version, 'MariaDB') === false) {
        throw new RuntimeException('The bundled SQL is MariaDB-specific; MySQL is not supported.');
    }

    // Reject a wrong/incomplete installation before history creation or DDL.
    // This does not make subsequent DDL transactional or predict every error.
    $requiredColumns = array(
        'operators' => array('id', 'username', 'password'),
        'operators_acl' => array('operator_id', 'file', 'access'),
        'operators_acl_files' => array('file', 'category', 'section'),
        'userinfo' => array('portalloginpassword'),
    );
    $metadata = $pdo->query("SELECT c.TABLE_NAME, c.COLUMN_NAME
        FROM information_schema.COLUMNS c
        JOIN information_schema.TABLES t
          ON t.TABLE_SCHEMA = c.TABLE_SCHEMA AND t.TABLE_NAME = c.TABLE_NAME
        WHERE c.TABLE_SCHEMA = DATABASE()
          AND t.TABLE_TYPE = 'BASE TABLE'
          AND c.TABLE_NAME IN ('operators', 'operators_acl', 'operators_acl_files', 'userinfo')");
    $present = array();
    while ($column = $metadata->fetch(PDO::FETCH_ASSOC)) {
        $present[$column['TABLE_NAME']][] = $column['COLUMN_NAME'];
    }
    $metadata->closeCursor();
    foreach ($requiredColumns as $table => $columns) {
        if (array_diff($columns, $present[$table] ?? array())) {
            throw new RuntimeException('Required daloRADIUS table or columns missing: ' . $table . '. No migrations started.');
        }
    }

    if (!is_dir($migrationsDir) || !is_readable($migrationsDir)) {
        throw new RuntimeException('Migration directory is unavailable.');
    }
    $sqlFiles = glob(rtrim($migrationsDir, '/\\') . '/*.sql') ?: array();
    $phpFiles = glob(rtrim($migrationsDir, '/\\') . '/*.php') ?: array();
    $files = array_merge($sqlFiles, $phpFiles);
    sort($files, SORT_STRING);
    if (!$files) {
        throw new RuntimeException('No bundled migrations found.');
    }

    // Read and normalize all migration inputs before any write.
    $scripts = array();
    foreach ($files as $file) {
        $base = basename($file);
        if (!preg_match('/\A[A-Za-z0-9._-]+\.(?:sql|php)\z/D', $base)) {
            throw new RuntimeException('Invalid migration filename: ' . $base);
        }
        $raw = file_get_contents($file);
        if ($raw === false || trim($raw) === '') {
            throw new RuntimeException('Unreadable or empty migration: ' . $base);
        }
        // Normalize CRLF/CR to LF so checksums are identical across Linux and Windows checkouts/builds.
        $normalized = str_replace(array("\r\n", "\r"), "\n", $raw);
        $ext = substr($base, -4);
        $statements = null;
        if ($ext === '.sql') {
            $current = $base;
            $statements = dalo_split_sql_statements($normalized);
            if (!$statements) {
                throw new RuntimeException('Migration contains no executable SQL statements: ' . $base);
            }
            $current = 'preflight';
        }
        $scripts[$base] = array(
            'type' => $ext === '.php' ? 'php' : 'sql',
            'path' => $file,
            'statements' => $statements,
            'checksum' => hash('sha256', $normalized),
        );
    }

    $isWriteMode = ($mode === 'apply' || $mode === 'baseline-fresh');
    if ($isWriteMode) {
        $dbNameHash = (string) $pdo->query('SELECT LEFT(SHA2(DATABASE(), 256), 40)')->fetchColumn();
        $candidateLock = 'dalo-migrate:' . $dbNameHash;
        $lockStmt = $pdo->prepare('SELECT GET_LOCK(?, ?)');
        $lockStmt->bindValue(1, $candidateLock, PDO::PARAM_STR);
        $lockStmt->bindValue(2, $lockTimeout, PDO::PARAM_INT);
        $lockStmt->execute();
        $acquired = (int) $lockStmt->fetchColumn() === 1;
        $lockStmt->closeCursor();
        if (!$acquired) {
            throw new RuntimeException('Another migration runner holds the database lock (waited ' . $lockTimeout . 's).');
        }
        $lockName = $candidateLock;
    }

    $exists = (int) $pdo->query(
        "SELECT COUNT(*) FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'daloradius_schema_migrations'"
    )->fetchColumn() === 1;

    if ($mode === 'baseline-fresh' && $exists) {
        throw new RuntimeException('Refusing --baseline-fresh: daloradius_schema_migrations already exists. Use --apply for upgrades.');
    }

    $history = array();
    if ($exists) {
        $colsStmt = $pdo->query(
            "SELECT COLUMN_NAME FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'daloradius_schema_migrations'"
        );
        $columns = $colsStmt->fetchAll(PDO::FETCH_COLUMN);
        $colsStmt->closeCursor();
        foreach (array('filename', 'checksum', 'status', 'applied_at') as $requiredCol) {
            if (!in_array($requiredCol, $columns, true)) {
                throw new RuntimeException('Unknown daloradius_schema_migrations table layout.');
            }
        }

        $rowsStmt = $pdo->query('SELECT filename, checksum, status FROM daloradius_schema_migrations');
        while ($row = $rowsStmt->fetch(PDO::FETCH_ASSOC)) {
            $name = $row['filename'];
            if (!preg_match('/\A[A-Za-z0-9._-]+\.(?:sql|php)\z/D', $name) || isset($history[$name])) {
                throw new RuntimeException('Invalid or duplicate migration history identity.');
            }
            $history[$name] = $row;
        }
        $rowsStmt->closeCursor();
    }

    // Validate the complete history before starting any pending migration.
    foreach ($history as $name => $row) {
        if ($row['status'] !== 'applied') {
            throw new RuntimeException('An earlier migration run was interrupted (' . $name . ': ' . $row['status'] . '). Inspect the history and database before continuing.');
        }
        if (!isset($scripts[$name])) {
            throw new RuntimeException('Recorded migration file is missing from the migrations directory: ' . $name);
        }
        if (!hash_equals($row['checksum'], $scripts[$name]['checksum'])) {
            throw new RuntimeException('Migration checksum differs from recorded history: ' . $name . '. Inspect before continuing.');
        }
    }

    $modeLabels = array(
        'preview' => 'PREVIEW (read-only)',
        'check-pending' => 'CHECK-PENDING (read-only)',
        'apply' => 'APPLY',
        'baseline-fresh' => 'BASELINE-FRESH (record without running SQL)',
    );
    $output('daloRADIUS database migrations', 'title');
    $output('Mode: ' . $modeLabels[$mode]);
    $output('Database: ' . json_encode($settings['database'])
        . ' on ' . json_encode($settings['host'])
        . ($locationName !== 'default' ? ' (location ' . json_encode($locationName) . ')' : ''));

    if ($locationName === 'default'
        && isset($configValues['CONFIG_LOCATIONS'])
        && is_array($configValues['CONFIG_LOCATIONS'])
        && count($configValues['CONFIG_LOCATIONS']) > 0) {
        $locNames = array_map(static function ($k) {
            return (string) $k;
        }, array_keys($configValues['CONFIG_LOCATIONS']));
        $output(
            'Note: ' . count($locNames) . ' named CONFIG_LOCATIONS configured ('
            . implode(', ', $locNames)
            . '). Migrate separate databases with --location=NAME.',
            'info'
        );
    }

    if (!$history && $mode !== 'baseline-fresh') {
        $output('No recorded history: SQL may have been applied manually. Pending means unrecorded, not necessarily missing.', 'warning');
    }

    $hasPending = false;
    foreach ($scripts as $name => $unused) {
        if (!isset($history[$name])) {
            $hasPending = true;
            break;
        }
    }

    // Avoid CREATE/DDL writes when the ledger already exists and nothing is pending,
    // so an up-to-date --apply run succeeds even without CREATE privileges.
    $needsWrite = $isWriteMode && ($mode === 'baseline-fresh' || $hasPending);
    if ($needsWrite) {
        $writesStarted = true;
        if (!$exists) {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS daloradius_schema_migrations (
                    filename VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
                    checksum CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                    status VARCHAR(16) NOT NULL,
                    applied_at DATETIME NULL
                ) ENGINE=InnoDB"
            );
        }
        // Use strict validation without NO_ZERO_DATE/NO_ZERO_IN_DATE, because legacy
        // daloRADIUS tables still carry DEFAULT '0000-00-00 00:00:00' columns.
        $pdo->exec("SET SESSION sql_mode = 'STRICT_ALL_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    }

    $counts = array('applied' => 0, 'satisfied' => 0, 'baselined' => 0, 'deferred' => 0, 'skipped' => 0, 'pending' => 0);

    foreach ($scripts as $name => $script) {
        $current = $name;
        $statementIndex = null;

        if (isset($history[$name])) {
            $counts['skipped']++;
            $output("SKIP $name (already recorded)", 'muted');
            continue;
        }

        if ($mode === 'preview' || $mode === 'check-pending') {
            $counts['pending']++;
            $output("PENDING $name (not recorded; would run)", 'warning');
            continue;
        }

        if ($mode === 'baseline-fresh') {
            if ($script['type'] === 'php') {
                $phpMigration = dalo_load_php_migration($script['path'], $name);
                if ($phpMigration['is_applied'] !== null && !$phpMigration['is_applied']($pdo)) {
                    $counts['deferred']++;
                    $output("DEFERRED $name (prerequisites not yet present; will run on next --apply)", 'warning');
                    continue;
                }
            }
            $stmt = $pdo->prepare(
                "INSERT INTO daloradius_schema_migrations (filename, checksum, status, applied_at)
                 VALUES (?, ?, 'applied', NOW())"
            );
            $stmt->execute(array($name, $script['checksum']));
            $counts['baselined']++;
            $output("BASELINED $name (recorded for fresh schema)", 'info');
            continue;
        }

        $insert = $pdo->prepare(
            "INSERT INTO daloradius_schema_migrations (filename, checksum, status)
             VALUES (?, ?, 'running')"
        );
        $insert->execute(array($name, $script['checksum']));

        // LDAP permits NULL passwords. Do not reapply the older NOT NULL
        // change if its widening requirement is already satisfied.
        $satisfied = false;
        $deferred = false;
        if ($name === '2025-03-operator-password-hashing.sql') {
            $col = dalo_operators_password_column($pdo);
            if ($col !== null) {
                $colType = $col['data_type'];
                $colLen = (int) ($col['max_len'] ?? 0);
                $satisfied = ($colType === 'varchar' && $colLen >= 95)
                    || in_array($colType, array('tinytext', 'text', 'mediumtext', 'longtext'), true);
            }
        }

        if (!$satisfied) {
            if ($script['type'] === 'php') {
                $phpMigration = dalo_load_php_migration($script['path'], $name);
                $result = $phpMigration['apply']($pdo);
                if ($result === false) {
                    $deferred = true;
                }
            } else {
                foreach ($script['statements'] as $idx => $sqlStatement) {
                    $statementIndex = $idx + 1;
                    // Never shrink an already-wider operators.password column (e.g. VARCHAR(255)
                    // or TEXT) down to VARCHAR(95) when applying the LDAP nullability migration.
                    if ($name === '2026-09-operator-ldap.sql'
                        && preg_match('/\AALTER\s+TABLE\s+`?operators`?\s+MODIFY\s+(?:COLUMN\s+)?`?password`?\s+VARCHAR\s*\(\s*95\s*\)\s+NULL\z/i', $sqlStatement)) {
                        $col = dalo_operators_password_column($pdo);
                        if ($col !== null) {
                            $colType = $col['data_type'];
                            $colLen = (int) ($col['max_len'] ?? 0);
                            $isNullable = $col['is_nullable'] === 'YES';
                            if ($colType === 'varchar' && $colLen > 95) {
                                if (!$isNullable) {
                                    $pdo->exec('ALTER TABLE operators MODIFY COLUMN password VARCHAR(' . $colLen . ') NULL');
                                }
                                continue;
                            }
                            if (in_array($colType, array('tinytext', 'text', 'mediumtext', 'longtext'), true)) {
                                if (!$isNullable) {
                                    $pdo->exec('ALTER TABLE operators MODIFY COLUMN password ' . strtoupper($colType) . ' NULL');
                                }
                                continue;
                            }
                            if ($colType === 'varchar' && $colLen === 95 && $isNullable) {
                                continue;
                            }
                        }
                    }
                    $pdo->exec($sqlStatement);
                }
                $statementIndex = null;
            }
        }

        if ($deferred) {
            $delete = $pdo->prepare(
                "DELETE FROM daloradius_schema_migrations
                 WHERE filename = ? AND status = 'running'"
            );
            $delete->execute(array($name));
            $counts['deferred']++;
            $output("DEFERRED $name (prerequisites not yet present; not recorded)", 'warning');
            continue;
        }

        $update = $pdo->prepare(
            "UPDATE daloradius_schema_migrations
             SET status = 'applied', applied_at = NOW()
             WHERE filename = ?"
        );
        $update->execute(array($name));

        $counts[$satisfied ? 'satisfied' : 'applied']++;
        $output(
            ($satisfied ? 'SATISFIED ' : 'APPLIED ') . $name
                . ($satisfied ? ' (requirement already met; recorded)' : ' (executed and recorded)'),
            $satisfied ? 'info' : 'success'
        );
    }

    $output(sprintf(
        'Summary: %d applied, %d already satisfied, %d baselined, %d deferred, %d skipped, %d pending.',
        $counts['applied'],
        $counts['satisfied'],
        $counts['baselined'],
        $counts['deferred'],
        $counts['skipped'],
        $counts['pending']
    ), 'title');

    if ($mode === 'apply' || $mode === 'baseline-fresh') {
        $output($mode === 'baseline-fresh' ? 'Fresh schema baseline complete.' : 'Migration run complete.', 'success');
        $exitCode = DALO_MIGRATE_EXIT_OK;
    } elseif ($mode === 'check-pending') {
        if ($counts['pending'] > 0) {
            $output('Pending migrations detected (' . $counts['pending'] . ').', 'warning');
            $exitCode = DALO_MIGRATE_EXIT_PENDING;
        } else {
            $output('Schema is up to date.', 'success');
            $exitCode = DALO_MIGRATE_EXIT_OK;
        }
    } else {
        $output('Preview only: no database writes. Back up, then use --apply.', 'warning');
        $exitCode = DALO_MIGRATE_EXIT_OK;
    }
} catch (Throwable $error) {
    $locationInfo = $current . ($statementIndex !== null ? ' (statement #' . $statementIndex . ')' : '');
    if ($error instanceof PDOException) {
        $sqlState = isset($error->errorInfo[0]) ? (string) $error->errorInfo[0] : (string) $error->getCode();
        $driverCode = isset($error->errorInfo[1]) ? (string) $error->errorInfo[1] : 'unknown';
        $message = 'Database operation failed (SQLSTATE ' . $sqlState . ', code ' . $driverCode . ').';
        if ($verbose && isset($error->errorInfo[2]) && is_string($error->errorInfo[2])) {
            $message .= ' ' . $error->errorInfo[2];
        } elseif (!$verbose) {
            $message .= ' Re-run with --verbose for driver details.';
        }
    } else {
        $message = $error->getMessage();
    }
    $output("Stopped at $locationInfo: $message", 'error', STDERR);
    $output(
        $writesStarted
            ? 'DDL may already be committed. Do not retry an interrupted migration without inspection.'
            : 'No migration writes were started.',
        'warning',
        STDERR
    );
    $exitCode = DALO_MIGRATE_EXIT_ERROR;
} finally {
    if ($pdo !== null && $lockName !== null) {
        try {
            $release = $pdo->prepare('SELECT RELEASE_LOCK(?)');
            $release->execute(array($lockName));
            $release->closeCursor();
        } catch (Throwable $ignored) {
            // Connection close below also releases the session advisory lock.
        }
    }
    $pdo = null;
}

exit($exitCode);
