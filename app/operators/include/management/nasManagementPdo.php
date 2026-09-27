<?php
/* Shared PDO operations for NAS create/edit/delete. The lock helpers remain PEAR-compatible. */
require_once __DIR__ . '/nasImportExport.php';

function nas_management_table($config) {
    $table = $config['CONFIG_DB_TBL_RADNAS'] ?? null;
    if (!is_string($table) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $table)) {
        throw new InvalidArgumentException('Invalid NAS table');
    }
    return '`' . $table . '`';
}

function nas_management_connect($config, $location, $write = false) {
    require_once $config['COMMON_INCLUDES'] . '/pdo_connection.php';
    $table = nas_management_table($config);
    $pdo = dalo_pdo_connect($config, $location);
    if ($write) {
        $stmt = $pdo->prepare('SELECT ENGINE FROM INFORMATION_SCHEMA.TABLES '
                             . 'WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
        $stmt->execute(array($config['CONFIG_DB_TBL_RADNAS']));
        if (strcasecmp((string)$stmt->fetchColumn(), 'InnoDB') !== 0) {
            throw new RuntimeException('NAS writes require InnoDB');
        }
    }
    return array($pdo, $table);
}

function nas_management_string($post, $field, $required = false) {
    $value = $post[$field] ?? '';
    if (!is_string($value)) {
        throw new InvalidArgumentException('Invalid NAS field');
    }
    $value = trim($value);
    if (!nas_backup_is_valid_utf8($value)) {
        throw new InvalidArgumentException('Invalid NAS encoding');
    }
    if ($required && $value === '') {
        throw new InvalidArgumentException('Missing NAS field');
    }
    return $value;
}

function nas_management_fields($post, $validTypes, $legacyType = null) {
    $fields = array();
    foreach (array('nasname' => 128, 'secret' => 60, 'shortname' => 32,
                   'server' => 64, 'community' => 50, 'description' => 200) as $field => $limit) {
        $fields[$field] = nas_management_string($post, $field, $field === 'nasname' || $field === 'secret');
        if (nas_backup_string_length($fields[$field]) > $limit) {
            throw new InvalidArgumentException('NAS field exceeds column length');
        }
    }
    $typeField = array_key_exists('nastype', $post) ? 'nastype' : 'type';
    $type = nas_management_string($post, $typeField);
    $fields['type'] = in_array($type, $validTypes, true) || ($legacyType !== null && $type === $legacyType)
        ? $type : 'other';
    if (nas_backup_string_length($fields['type']) > 30) {
        throw new InvalidArgumentException('NAS type exceeds column length');
    }
    $rawPorts = nas_management_string($post, 'ports');
    if ($rawPorts !== '' && (!ctype_digit($rawPorts) || (int)$rawPorts > 99999)) {
        throw new InvalidArgumentException('Invalid NAS ports');
    }
    $fields['ports'] = $rawPorts === '' ? 0 : (int)$rawPorts;
    return $fields;
}

function nas_management_lock(PDO $pdo, $config) {
    $lock = nas_backup_acquire_lock($pdo, $config['CONFIG_DB_TBL_RADNAS'], 30);
    if (!$lock['acquired']) {
        throw new RuntimeException($lock['error'] ? 'NAS lock unavailable' : 'NAS lock busy');
    }
    return $lock;
}

function nas_management_finish($pdo, $lock) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        try { $pdo->rollBack(); }
        catch (Throwable $error) { error_log('NAS rollback: ' . get_class($error)); }
    }
    if ($pdo instanceof PDO && $lock !== null && $lock['acquired'] &&
        !nas_backup_release_lock($pdo, $lock['name'])) {
        error_log('NAS advisory lock release could not be confirmed');
    }
}

function nas_management_row_matches($row, $fields) {
    if (!is_array($row) || (int)$row['ports'] !== $fields['ports']) { return false; }
    foreach (array('nasname','shortname','type','secret','server','community','description') as $field) {
        if (!is_string($row[$field] ?? null) || $row[$field] !== $fields[$field]) { return false; }
    }
    return true;
}

function nas_management_find(PDO $pdo, $table, $name, $forUpdate = false) {
    $sql = "SELECT id,nasname,shortname,type,ports,secret,server,community,description "
         . "FROM $table WHERE HEX(nasname)=? ORDER BY id LIMIT 2";
    if ($forUpdate) { $sql .= ' FOR UPDATE'; }
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(strtoupper(bin2hex($name))));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($rows) > 1) { throw new RuntimeException('Ambiguous NAS name'); }
    return $rows[0] ?? null;
}
