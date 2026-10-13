<?php
/** R01a: read-only operator permission queries on an explicit PDO handle. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/operator_acl_read.php') !== false) {
    http_response_code(404);
    exit;
}

function dalo_operator_acl_table(PDO $pdo, $config, $key) {
    if (!in_array($key, array('CONFIG_DB_TBL_DALOOPERATORS_ACL',
                             'CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES'), true)) {
        throw new InvalidArgumentException('Invalid operator permission table');
    }
    $name = $config[$key] ?? null;
    if (!is_string($name) || !preg_match('/\A[A-Za-z_][A-Za-z0-9_]{0,63}\z/', $name)) {
        throw new InvalidArgumentException('Invalid operator permission table');
    }
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if (!in_array($driver, array('mysql', 'pgsql'), true)) {
        throw new InvalidArgumentException('Unsupported operator permission driver');
    }
    $quote = $driver === 'mysql' ? '`' : '"';
    return $quote . $name . $quote;
}

function dalo_operator_acl_id($id) {
    if ((!is_int($id) && !is_string($id)) ||
        !preg_match('/\A[0-9]+\z/', (string) $id) ||
        filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) === false) {
        throw new InvalidArgumentException('Invalid operator permission identity');
    }
    return (int) $id;
}

function dalo_operator_acl_allowed(PDO $pdo, $config, $id, $file) {
    $id = dalo_operator_acl_id($id);
    if (!is_string($file) || $file === '' || strpos($file, "\0") !== false) {
        throw new InvalidArgumentException('Invalid operator permission page');
    }
    $table = dalo_operator_acl_table($pdo, $config, 'CONFIG_DB_TBL_DALOOPERATORS_ACL');
    $stmt = $pdo->prepare("SELECT access FROM $table WHERE operator_id=:id AND file=:file");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':file', $file, PDO::PARAM_STR);
    if (!$stmt->execute()) {
        throw new RuntimeException('Operator permission lookup failed');
    }
    // Preserve the historical first-row/intval policy; this is not an ACL redesign.
    return intval($stmt->fetchColumn()) === 1;
}

function dalo_operator_acl_rows(PDO $pdo, $config, $id = '') {
    $files = dalo_operator_acl_table($pdo, $config, 'CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES');
    $acl = dalo_operator_acl_table($pdo, $config, 'CONFIG_DB_TBL_DALOOPERATORS_ACL');
    $sql = "SELECT DISTINCT(opf.file), opf.category, opf.section, opa.access
              FROM $files AS opf LEFT JOIN $acl AS opa ON opf.file=opa.file";
    // Keep both historical joins, including no-id creation-form and filtered edit semantics.
    $filtered = !in_array($id, array('', null, 0, '0'), true);
    if ($filtered) {
        $id = dalo_operator_acl_id($id);
        $sql .= ' WHERE opa.operator_id=:id';
    }
    $sql .= ' ORDER BY opf.category, opf.section ASC';
    $stmt = $pdo->prepare($sql);
    if ($filtered) {
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    }
    if (!$stmt->execute()) {
        throw new RuntimeException('Operator permission list failed');
    }
    return $stmt->fetchAll(PDO::FETCH_NUM);
}
