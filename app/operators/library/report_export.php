<?php
/* Fixed, parameterized builders for operator CSV exports (UNIT-003). */

function dalo_export_table($configValues, $key) {
    if (!isset($configValues[$key]) || !is_string($configValues[$key]) ||
        preg_match('/\A[A-Za-z0-9_]+\z/D', $configValues[$key]) !== 1) {
        throw new InvalidArgumentException('Invalid export table configuration');
    }
    return '`' . $configValues[$key] . '`';
}

function dalo_export_descriptor($session, $get) {
    $type = $get['reportType'] ?? $session['reportType'] ?? null;
    if (!is_string($type)) {
        throw new InvalidArgumentException('Invalid export type');
    }
    // The group export is a direct, parameterized link from profile management.
    if ($type === 'usernameListByGroup') {
        $group = $get['groupname'] ?? null;
        if (!is_string($group) || trim($group) === '') {
            throw new InvalidArgumentException('Invalid group');
        }
        return ['source' => 'mng-rad-profiles-list', 'type' => $type,
                'filters' => ['groupname' => trim($group)]];
    }
    $descriptor = $session['reportExport'] ?? null;
    if (!is_array($descriptor) || !isset($descriptor['source'], $descriptor['type'], $descriptor['filters']) ||
        !is_string($descriptor['source']) || !is_string($descriptor['type']) ||
        !is_array($descriptor['filters'])) {
        throw new InvalidArgumentException('Missing export descriptor');
    }
    if ($type === 'reportsBatchTotalUsers') {
        if ($descriptor['source'] !== 'rep-batch-details' ||
            $descriptor['type'] !== 'reportsBatchActiveUsers') {
            throw new InvalidArgumentException('Invalid batch export descriptor');
        }
        $descriptor['type'] = $type;
    } elseif ($type !== $descriptor['type']) {
        throw new InvalidArgumentException('Mismatched export type');
    }
    return $descriptor;
}

function dalo_export_query($descriptor, $configValues) {
    $source = $descriptor['source'];
    $type = $descriptor['type'];
    $filters = $descriptor['filters'];
    if (!is_string($source) || !is_string($type) || !is_array($filters)) {
        throw new InvalidArgumentException('Invalid export descriptor');
    }
    $families = [
        'acct-all' => 'accounting', 'acct-date' => 'accounting',
        'acct-hotspot-accounting' => 'accounting', 'acct-ipaddress' => 'accounting',
        'acct-nasipaddress' => 'accounting', 'acct-username' => 'accounting',
        'acct-plans-usage' => 'accounting',
        'mng-list-all' => 'users', 'mng-search' => 'users',
        'mng-rad-profiles-list' => 'users',
        'rep-online' => 'reports', 'rep-lastconnect' => 'reports',
        'rep-topusers' => 'reports',
        'rep-batch-list' => 'batch', 'mng-batch-list' => 'batch',
        'rep-batch-details' => 'batch', 'bill-invoice-report' => 'batch',
    ];
    if (!isset($families[$source])) {
        throw new InvalidArgumentException('Invalid export source');
    }
    $family = $families[$source];
    require_once __DIR__ . '/report_export_' . $family . '.php';
    $builder = 'dalo_export_' . $family . '_query';
    [$sql, $bindings] = $builder($source, $type, $filters, $configValues);
    if (!is_string($sql) || !is_array($bindings) ||
        !preg_match('/\A\s*SELECT\s/iu', $sql)) {
        throw new InvalidArgumentException('Invalid export query');
    }
    return [$sql, $bindings];
}

function dalo_export_fetch(PDO $pdo, $descriptor, $configValues) {
    [$sql, $bindings] = dalo_export_query($descriptor, $configValues);
    $statement = $pdo->prepare($sql);
    foreach ($bindings as $name => $value) {
        if (!is_string($name) || !preg_match('/\A:[A-Za-z_][A-Za-z0-9_]*\z/D', $name) ||
            (!is_scalar($value) && $value !== null)) {
            throw new InvalidArgumentException('Invalid export binding');
        }
        $statement->bindValue($name, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $statement->execute();
    return $statement;
}
