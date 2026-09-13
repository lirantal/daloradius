<?php
/* Open-session maintenance: bounded previews and optimistic, per-row writes.
 * GPL-2.0-or-later. No NAS disconnection or inactivity detection is performed.
 */
const DALO_MAINTENANCE_LIMIT = 100;
const DALO_MAINTENANCE_TTL = 600;

function dalo_maintenance_filter($input) {
    $action = $input['action'] ?? null;
    $scope = $input['scope'] ?? null;
    $value = $input['value'] ?? null;
    if (!in_array($action, ['close', 'delete'], true) ||
        !in_array($scope, ['username', 'date'], true) || !is_string($value)) {
        throw new InvalidArgumentException('Invalid filter');
    }
    if ($scope === 'username') {
        if ($value === '' || strlen($value) > 253 || preg_match('/[\x00-\x1f\x7f]/', $value)) {
            throw new InvalidArgumentException('Invalid username');
        }
    } elseif (preg_match('/\A([0-9]{4})-([0-9]{2})-([0-9]{2})\z/', $value, $m) !== 1 ||
              !checkdate((int)$m[2], (int)$m[3], (int)$m[1])) {
        throw new InvalidArgumentException('Invalid date');
    }
    return ['action' => $action, 'scope' => $scope, 'value' => $value];
}

function dalo_maintenance_table($table) {
    if (!is_string($table) || preg_match('/\A[a-zA-Z0-9_]+\z/', $table) !== 1) {
        throw new InvalidArgumentException('Invalid table');
    }
    return '`' . $table . '`';
}

function dalo_maintenance_where($db, $filter) {
    $value = $db->escapeSimple($filter['value']);
    $scope = $filter['scope'] === 'username'
           ? "username='$value'" : "acctstarttime < '$value 00:00:00'";
    return "(acctstoptime IS NULL OR acctstoptime='0000-00-00 00:00:00') AND $scope";
}

function dalo_maintenance_query($db, $sql, &$logDebugSQL = null, $throwOnError = true) {
    if ($logDebugSQL !== null) {
        $logDebugSQL .= "$sql;\n";
    }
    $result = $db->query($sql);
    if ($throwOnError && DB::isError($result)) {
        throw new RuntimeException('Accounting query failed');
    }
    return $result;
}

function dalo_maintenance_preview($db, $table, $filter, &$logDebugSQL = null) {
    $filter = dalo_maintenance_filter($filter);
    $table = dalo_maintenance_table($table);
    $where = dalo_maintenance_where($db, $filter);
    $result = dalo_maintenance_query($db, "SELECT COUNT(*) FROM $table WHERE $where", $logDebugSQL);
    $total = (int)$result->fetchRow()[0];
    $result = dalo_maintenance_query($db, "SELECT * FROM $table WHERE $where ORDER BY radacctid LIMIT " . DALO_MAINTENANCE_LIMIT, $logDebugSQL);
    $rows = [];
    while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
        $rows[] = $row;
    }
    return ['filter' => $filter, 'rows' => $rows, 'total' => $total,
            'created' => time(), 'token' => bin2hex(random_bytes(32))];
}

function dalo_maintenance_apply($db, $table, $preview, &$logDebugSQL = null) {
    $filter = dalo_maintenance_filter($preview['filter']);
    if (!isset($preview['created'], $preview['rows']) ||
        time() - $preview['created'] > DALO_MAINTENANCE_TTL ||
        count($preview['rows']) > DALO_MAINTENANCE_LIMIT) {
        throw new InvalidArgumentException('Expired or invalid preview');
    }
    $table = dalo_maintenance_table($table);
    $affected = 0;
    $failed = 0;
    foreach ($preview['rows'] as $row) {
        // Compare every column, not only counters: Interim/Stop changes must skip
        // the row. This predicate and the write are atomic, even without InnoDB.
        $predicates = [dalo_maintenance_where($db, $filter)];
        foreach ($row as $column => $value) {
            $column = dalo_maintenance_table($column);
            $predicates[] = $value === null ? "$column IS NULL"
                : "BINARY $column <=> BINARY '" . $db->escapeSimple($value) . "'";
        }
        if (!isset($row['radacctid'])) {
            throw new InvalidArgumentException('Invalid snapshot');
        }
        $sql = $filter['action'] === 'close'
             ? "UPDATE $table SET acctstoptime=NOW(), acctterminatecause='Admin-Reset'"
             : "DELETE FROM $table";
        $result = dalo_maintenance_query($db, $sql . ' WHERE ' . implode(' AND ', $predicates), $logDebugSQL, false);
        if (DB::isError($result)) {
            $failed++;
        } else {
            $affected += (int)$db->affectedRows();
        }
    }
    return ['affected' => $affected, 'failed' => $failed,
            'skipped' => count($preview['rows']) - $affected - $failed];
}
