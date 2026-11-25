<?php
/**
 * Idempotent performance indexes for daloRADIUS (PDO migration).
 *
 * Equivalent to contrib/db/update-performance-indexes.sql without requiring
 * mysql client DELIMITER or stored procedures. Checks both exact index name
 * and functionally equivalent column order before creating each index.
 *
 * If the target FreeRADIUS tables (radacct, radpostauth, radcheck) do not
 * exist yet (for example during fresh Docker init before the FreeRADIUS
 * container seeds its schema), returns false so the runner defers recording
 * this migration until a subsequent run once those tables exist.
 */

$indexes = array(
    array(
        'table' => 'radacct',
        'name' => 'idx_radacct_username_time',
        'columns' => 'username,acctstarttime',
        'sql' => 'CREATE INDEX `idx_radacct_username_time` ON `radacct` (`username`, `acctstarttime`)',
    ),
    array(
        'table' => 'radpostauth',
        'name' => 'idx_radpostauth_authdate',
        'columns' => 'authdate',
        'sql' => 'CREATE INDEX `idx_radpostauth_authdate` ON `radpostauth` (`authdate`)',
    ),
    array(
        'table' => 'radacct',
        'name' => 'idx_radacct_status_start',
        'columns' => 'acctstoptime,acctstarttime',
        'sql' => 'CREATE INDEX `idx_radacct_status_start` ON `radacct` (`acctstoptime`, `acctstarttime`)',
    ),
    array(
        'table' => 'radacct',
        'name' => 'idx_radacct_top_users',
        'columns' => 'acctstarttime,username,acctsessiontime,acctinputoctets,acctoutputoctets',
        'sql' => 'CREATE INDEX `idx_radacct_top_users` ON `radacct` (`acctstarttime`, `username`, `acctsessiontime`, `acctinputoctets`, `acctoutputoctets`)',
    ),
    array(
        'table' => 'radcheck',
        'name' => 'idx_radcheck_username_attr',
        'columns' => 'username,attribute',
        'sql' => 'CREATE INDEX `idx_radcheck_username_attr` ON `radcheck` (`username`, `attribute`)',
    ),
);

$allTargetTablesExist = static function (PDO $pdo, array $indexes): bool {
    $tables = array_values(array_unique(array_column($indexes, 'table')));
    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_TYPE = 'BASE TABLE'
           AND TABLE_NAME = ?"
    );
    foreach ($tables as $table) {
        $stmt->execute(array($table));
        $exists = (int) $stmt->fetchColumn() > 0;
        $stmt->closeCursor();
        if (!$exists) {
            return false;
        }
    }
    return true;
};

$indexExists = static function (PDO $pdo, array $index): bool {
    $nameDefStmt = $pdo->prepare(
        "SELECT GROUP_CONCAT(
                    CASE
                        WHEN SUB_PART IS NULL THEN COLUMN_NAME
                        ELSE CONCAT(COLUMN_NAME, '(', SUB_PART, ')')
                    END
                    ORDER BY SEQ_IN_INDEX
                    SEPARATOR ','
                ) AS indexed_columns
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?
           AND INDEX_NAME = ?
         GROUP BY INDEX_NAME"
    );
    $nameDefStmt->execute(array($index['table'], $index['name']));
    $existingCols = $nameDefStmt->fetchColumn();
    $nameDefStmt->closeCursor();
    if ($existingCols !== false) {
        if ((string) $existingCols !== $index['columns']) {
            throw new RuntimeException(
                'Conflicting index definition for ' . $index['table'] . '.' . $index['name']
                . ': expected (' . $index['columns'] . '), found (' . (string) $existingCols . ').'
            );
        }
        return true;
    }

    $equivStmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM (
             SELECT INDEX_NAME,
                    GROUP_CONCAT(
                        CASE
                            WHEN SUB_PART IS NULL THEN COLUMN_NAME
                            ELSE CONCAT(COLUMN_NAME, '(', SUB_PART, ')')
                        END
                        ORDER BY SEQ_IN_INDEX
                        SEPARATOR ','
                    ) AS indexed_columns
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
             GROUP BY INDEX_NAME
         ) existing_indexes
         WHERE existing_indexes.indexed_columns = ?"
    );
    $equivStmt->execute(array($index['table'], $index['columns']));
    $hasEquiv = (int) $equivStmt->fetchColumn() > 0;
    $equivStmt->closeCursor();
    return $hasEquiv;
};

return array(
    'is_applied' => static function (PDO $pdo) use ($indexes, $allTargetTablesExist, $indexExists): bool {
        if (!$allTargetTablesExist($pdo, $indexes)) {
            return false;
        }
        foreach ($indexes as $index) {
            if (!$indexExists($pdo, $index)) {
                return false;
            }
        }
        return true;
    },
    'apply' => static function (PDO $pdo) use ($indexes, $allTargetTablesExist, $indexExists): bool {
        if (!$allTargetTablesExist($pdo, $indexes)) {
            return false;
        }
        foreach ($indexes as $index) {
            if (!$indexExists($pdo, $index)) {
                $pdo->exec($index['sql']);
            }
        }
        return true;
    },
);
