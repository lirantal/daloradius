<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 * Description:    Open-session maintenance: bounded previews and optimistic, per-row writes.
 *                 No NAS disconnection or inactivity detection is performed.
 * 
 * Authors:    Kevin Lous
 *
 *********************************************************************************************************
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

function dalo_maintenance_where($filter) {
    $scope = $filter['scope'] === 'username'
           ? 'username = :filter_value' : 'acctstarttime < :filter_value';
    $value = $filter['scope'] === 'username' ? $filter['value'] : $filter['value'] . ' 00:00:00';
    return ['(acctstoptime IS NULL OR acctstoptime=\'0000-00-00 00:00:00\') AND ' . $scope,
            [':filter_value' => $value]];
}

function dalo_maintenance_query($pdo, $sql, $parameters, &$logDebugSQL = null, $throwOnError = true) {
    if ($logDebugSQL !== null) {
        // Log only SQL templates, never the bound username or snapshot values.
        $logDebugSQL .= "$sql;\n";
    }
    try {
        $statement = $pdo->prepare($sql);
        foreach ($parameters as $key => $value) {
            $statement->bindValue($key, $value,
                                  $key === ':limit' ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $statement->execute();
        return $statement;
    } catch (PDOException $exception) {
        if ($throwOnError) {
            // The driver error may contain statement values or database details.
            throw new RuntimeException('Accounting query failed');
        }
        return false;
    }
}

function dalo_maintenance_preview($pdo, $table, $filter, &$logDebugSQL = null) {
    $filter = dalo_maintenance_filter($filter);
    $table = dalo_maintenance_table($table);
    [$where, $parameters] = dalo_maintenance_where($filter);
    $result = dalo_maintenance_query($pdo, "SELECT COUNT(*) FROM $table WHERE $where",
                                     $parameters, $logDebugSQL);
    $total = (int)$result->fetchColumn();
    $result = dalo_maintenance_query($pdo, "SELECT * FROM $table WHERE $where ORDER BY radacctid LIMIT :limit",
                                     $parameters + [':limit' => DALO_MAINTENANCE_LIMIT], $logDebugSQL);
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    return ['filter' => $filter, 'rows' => $rows, 'total' => $total,
            'created' => time(), 'token' => bin2hex(random_bytes(32))];
}

function dalo_maintenance_apply($pdo, $table, $preview, &$logDebugSQL = null) {
    $filter = dalo_maintenance_filter($preview['filter']);
    if (!isset($preview['created'], $preview['rows']) || !is_array($preview['rows']) ||
        time() - $preview['created'] > DALO_MAINTENANCE_TTL ||
        count($preview['rows']) > DALO_MAINTENANCE_LIMIT) {
        throw new InvalidArgumentException('Expired or invalid preview');
    }
    $table = dalo_maintenance_table($table);
    $affected = 0;
    $failed = 0;
    // Only columns actually present in the accounting table may be used as
    // identifiers. A schema change after preview invalidates the snapshot.
    $schema = dalo_maintenance_query($pdo, "SHOW COLUMNS FROM $table", [], $logDebugSQL, false);
    if ($schema === false) {
        return ['affected' => 0, 'failed' => count($preview['rows']), 'skipped' => 0];
    }
    $columns = array_column($schema->fetchAll(PDO::FETCH_ASSOC), 'Field');
    [$where, $filterParameters] = dalo_maintenance_where($filter);
    // Reject the entire confirmation before touching any row, even if only a
    // later snapshot has been altered or the table's schema has changed.
    foreach ($preview['rows'] as $row) {
        if (!is_array($row) || !isset($row['radacctid']) ||
            count($row) !== count($columns) || array_diff(array_keys($row), $columns)) {
            throw new InvalidArgumentException('Invalid snapshot');
        }
        foreach ($row as $value) {
            if ($value !== null && !is_scalar($value)) {
                throw new InvalidArgumentException('Invalid snapshot');
            }
        }
    }
    foreach ($preview['rows'] as $row) {
        // Compare every column, not only counters: Interim/Stop changes must skip
        // the row. This predicate and the write are atomic, even without InnoDB.
        $predicates = [$where];
        $parameters = $filterParameters;
        $index = 0;
        foreach ($row as $column => $value) {
            $column = dalo_maintenance_table($column);
            if ($value === null) {
                $predicates[] = "$column IS NULL";
            } else {
                $key = ':snapshot_' . $index++;
                $predicates[] = "BINARY $column <=> BINARY $key";
                $parameters[$key] = $value;
            }
        }
        $sql = $filter['action'] === 'close'
             ? "UPDATE $table SET acctstoptime=NOW(), acctterminatecause='Admin-Reset'"
             : "DELETE FROM $table";
        $result = dalo_maintenance_query($pdo, $sql . ' WHERE ' . implode(' AND ', $predicates),
                                         $parameters, $logDebugSQL, false);
        if ($result === false) {
            $failed++;
        } else {
            // rowCount() is valid for these UPDATE/DELETE statements, not SELECT.
            $affected += $result->rowCount();
        }
    }
    return ['affected' => $affected, 'failed' => $failed,
            'skipped' => count($preview['rows']) - $affected - $failed];
}
