<?php
/** R12: custom reports and scoped accounting purge, explicit PDO only. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/accounting_advanced_pdo.php') !== false) {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/accounting_pages_pdo.php';

function dalo_advanced_columns($values, $allowed, $defaults) {
    if ($values === null || $values === array()) { return $defaults; }
    if (!is_array($values) || count($values) > 256) {
        throw new InvalidArgumentException('Invalid accounting columns');
    }
    $columns = array();
    foreach ($values as $value) {
        if (!is_string($value) || !in_array($value, $allowed, true) ||
            !preg_match('/\A[A-Za-z0-9_]{1,64}\z/', $value)) {
            throw new InvalidArgumentException('Invalid accounting column');
        }
        if (!in_array($value, $columns, true)) { $columns[] = $value; }
    }
    return $columns;
}

function dalo_advanced_custom_query($config, $columns, $allowed, $start, $end, $field, $operator, $value) {
    $columns = dalo_advanced_columns($columns, $allowed, array());
    if (!$columns) { throw new InvalidArgumentException('Missing accounting columns'); }
    $bindings = array(); $where = array();
    // Preserve this report's strict midnight bounds, unlike date/plan reports.
    if ($start !== '') { $where[] = 'acctstarttime > :custom_start'; $bindings[':custom_start'] = $start; }
    if ($end !== '') { $where[] = 'acctstarttime < :custom_end'; $bindings[':custom_end'] = $end; }
    if ($value !== '') {
        if (!in_array($field, $allowed, true) || !preg_match('/\A[A-Za-z0-9_]{1,64}\z/', $field) ||
            !in_array($operator, array('equals','contains'), true)) {
            throw new InvalidArgumentException('Invalid accounting predicate');
        }
        $where[] = '`' . $field . '` ' . ($operator === 'equals' ? '=' : 'LIKE') . ' :custom_value';
        $bindings[':custom_value'] = $operator === 'contains' ? '%' . $value . '%' : $value;
    }
    $projection = array_map(static function ($column) { return '`' . $column . '` AS `' . $column . '`'; }, $columns);
    $sql = 'SELECT ' . implode(', ', $projection) . ' FROM ' . dalo_export_table($config, 'CONFIG_DB_TBL_RADACCT');
    if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
    return array($sql, $bindings);
}

function dalo_advanced_rows(PDO $pdo, $sql, $bindings, $order, $direction, $allowed, $offset, $limit, $associative = false) {
    if (!is_string($order) || !in_array($order, $allowed, true) || !preg_match('/\A[A-Za-z0-9_]{1,64}\z/', $order) ||
        !in_array($direction, array('asc','desc'), true) || !is_numeric($offset) || $offset < 0 ||
        !is_numeric($limit) || $limit < 1 || $limit > 10000) {
        throw new InvalidArgumentException('Invalid accounting pagination');
    }
    $bindings[':advanced_offset'] = (int)$offset;
    $bindings[':advanced_limit'] = (int)$limit;
    return dalo_accounting_execute($pdo, $sql . ' ORDER BY `' . $order . '` ' . $direction .
        ' LIMIT :advanced_offset, :advanced_limit', $bindings)->fetchAll($associative ? PDO::FETCH_ASSOC : PDO::FETCH_NUM);
}

function dalo_advanced_purge(PDO $pdo, $config, $username, $start, $end) {
    if ($pdo->inTransaction()) { throw new LogicException('Purge requires an owned transaction'); }
    foreach (array($username,$start,$end) as $value) {
        if (!is_string($value) || $value === '' || strlen($value) > 4096 || strpos($value,"\0") !== false) {
            throw new InvalidArgumentException('Invalid purge fields');
        }
    }
    foreach (array($start,$end) as $date) { dalo_export_accounting_date_filter(array('date'=>$date), 'date'); }
    if ($start > $end) { throw new InvalidArgumentException('Invalid purge interval'); }
    $table = dalo_export_table($config, 'CONFIG_DB_TBL_RADACCT');
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') {
        throw new RuntimeException('Unsupported accounting purge engine');
    }
    if (!$pdo->beginTransaction()) { throw new RuntimeException('Unable to start accounting purge'); }
    try {
        // Hold target metadata before inspecting the engine: concurrent DDL must
        // not replace InnoDB after preflight and before the DELETE.
        dalo_accounting_execute($pdo, "SELECT radacctid FROM $table WHERE 1=0 FOR UPDATE")->closeCursor();
        $engine = dalo_accounting_execute($pdo,
            'SELECT ENGINE FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:purge_table',
            array(':purge_table'=>trim($table,'`')))->fetchColumn();
        if (strcasecmp((string)$engine, 'InnoDB') !== 0) { throw new RuntimeException('Transactional accounting table required'); }
        // Revalidate the exact visible identity on the same write connection.
        $exists = dalo_accounting_execute($pdo, "SELECT radacctid FROM $table WHERE username=:purge_lookup AND BINARY username=:purge_identity LIMIT 1 FOR UPDATE",
            array(':purge_lookup'=>$username, ':purge_identity'=>$username))->fetchColumn();
        if ($exists === false) { throw new InvalidArgumentException('Stale accounting identity'); }
        // Strict start midnight, inclusive whole end day: historical policy.
        $stmt = dalo_accounting_execute($pdo,
            "DELETE FROM $table WHERE username=:purge_username AND AcctStartTime > :purge_start
                                  AND AcctStartTime < DATE_ADD(:purge_end, INTERVAL 1 DAY)",
            array(':purge_username'=>$username, ':purge_start'=>$start, ':purge_end'=>$end));
        $count = $stmt->rowCount();
        if (!$pdo->commit()) { throw new RuntimeException('Unable to commit accounting purge'); }
        return $count;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        throw $exception;
    }
}
