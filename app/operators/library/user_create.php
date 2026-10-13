<?php
/* R03: caller-owned create/import transaction; legacy dispatch remains opt-in. */
require_once __DIR__ . '/attributes_pdo.php';
require_once __DIR__ . '/../../common/includes/pdo_connection.php';

function dalo_create_table($config, $key) {
    $keys = array('CONFIG_DB_TBL_RADCHECK','CONFIG_DB_TBL_RADREPLY',
        'CONFIG_DB_TBL_DALOUSERINFO','CONFIG_DB_TBL_DALOUSERBILLINFO','CONFIG_DB_TBL_RADUSERGROUP');
    $name = $config[$key] ?? null;
    if (!in_array($key, $keys, true) || !is_string($name) ||
        !preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,63}$/D', $name)) {
        throw new InvalidArgumentException('Invalid creation table');
    }
    return '`' . $name . '`';
}

function dalo_create_request($post) {
    foreach ($post as $key => $value) {
        if ($key === 'groups') {
            if (!is_array($value)) { throw new InvalidArgumentException('Invalid groups'); }
            foreach ($value as $group) {
                if (!is_string($group)) { throw new InvalidArgumentException('Invalid group'); }
            }
        } elseif (is_array($value)) {
            if (array_keys($value) !== array(0,1,2,3)) {
                throw new InvalidArgumentException('Invalid attribute control');
            }
            foreach ($value as $part) {
                if (!is_string($part)) { throw new InvalidArgumentException('Invalid attribute field'); }
            }
        } elseif (!is_string($value)) {
            throw new InvalidArgumentException('Invalid request field');
        }
        // Ordinary form controls may never masquerade as attribute arrays.
        if (is_array($value) && $key !== 'groups' &&
            !preg_match('/^(?:dictValues[0-9]+|editValues[0-9]+|injected_attribute)/', $key)) {
            throw new InvalidArgumentException('Invalid scalar control');
        }
    }
}

function dalo_create_begin($config) {
    $pdo = dalo_pdo_connect($config, $_SESSION['location_name'] ?? 'default');
    $tables = array();
    foreach (array('CONFIG_DB_TBL_RADCHECK','CONFIG_DB_TBL_RADREPLY',
        'CONFIG_DB_TBL_DALOUSERINFO','CONFIG_DB_TBL_DALOUSERBILLINFO','CONFIG_DB_TBL_RADUSERGROUP') as $key) {
        $tables[] = dalo_create_table($config, $key);
    }
    dalo_attribute_pdo_require_innodb($pdo, $tables);
    // Schema/table-wide lock respects all database username collations.
    $lock = 'dalo-create-' . substr(hash('sha256', $pdo->query('SELECT DATABASE()')->fetchColumn() . $tables[0]), 0, 48);
    $stmt = $pdo->prepare('SELECT GET_LOCK(?,10)');
    $stmt->execute(array($lock));
    if ((int)$stmt->fetchColumn() !== 1) { throw new RuntimeException('Creation is busy'); }
    $stmt->closeCursor();
    if (!$pdo->beginTransaction()) { throw new RuntimeException('Could not begin creation'); }
    return $pdo;
}

function dalo_create_name($name) {
    if (!is_string($name) || trim($name) === '' || strpos($name, "\0") !== false ||
        !preg_match('//u', $name) || dalo_attribute_pdo_length($name) > 64) {
        throw new InvalidArgumentException('Invalid account name');
    }
    return trim($name);
}

function dalo_create_info(PDO $pdo, $config, $username, $params, $allowed, $skip, $key, $update) {
    global $logDebugSQL;
    if (!$pdo->inTransaction()) { throw new LogicException('Caller transaction required'); }
    $username = dalo_create_name($username);
    $table = dalo_create_table($config, $key);
    if (!in_array($key, array('CONFIG_DB_TBL_DALOUSERINFO','CONFIG_DB_TBL_DALOUSERBILLINFO'), true)) {
        throw new InvalidArgumentException('Invalid information table');
    }
    $check = $pdo->prepare("SELECT id FROM $table WHERE username=? LIMIT 1 FOR UPDATE");
    $check->execute(array($username));
    $exists = $check->fetchColumn() !== false;
    $check->closeCursor();
    if ($exists !== $update) { return false; }
    $fields = array(); $values = array();
    foreach ($params as $field => $value) {
        if (!in_array($field, $allowed, true) || in_array($field, $skip, true)) { continue; }
        if ((!is_string($value) && !is_int($value)) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $field)) {
            throw new InvalidArgumentException('Invalid information field');
        }
        $fields[] = '`' . $field . '`'; $values[] = trim((string)$value);
    }
    if (!$fields) { return null; }
    // Match actual configured schema lengths even with permissive application SQL mode.
    $meta = $pdo->prepare('SELECT COLUMN_NAME,CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
    $meta->execute(array(trim($table, '`'))); $lengths = $meta->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($fields as $i => $field) {
        $name = trim($field, '`');
        if (!array_key_exists($name, $lengths) || ($lengths[$name] !== null && dalo_attribute_pdo_length($values[$i]) > (int)$lengths[$name])) {
            throw new InvalidArgumentException('Information field exceeds schema');
        }
    }
    if ($update) {
        $sql = "UPDATE $table SET " . implode(',', array_map(function($field) { return "$field=?"; }, $fields)) . ' WHERE username=?';
        $values[] = $username;
    } else {
        $sql = "INSERT INTO $table (username," . implode(',', $fields) . ') VALUES (' . implode(',',array_fill(0,count($fields)+1,'?')) . ')';
        array_unshift($values, $username);
    }
    $stmt = $pdo->prepare($sql); $stmt->execute($values);
    $logDebugSQL .= "$sql;\n";
    return true;
}

function dalo_create_attribute(PDO $pdo, $config, $subject, $attribute, $op, $value, $key) {
    global $logDebugSQL;
    if (!$pdo->inTransaction()) { throw new LogicException('Caller transaction required'); }
    $subject = dalo_create_name($subject);
    // Legacy callers supply either the config key or its configured value.
    foreach (array('CONFIG_DB_TBL_RADCHECK','CONFIG_DB_TBL_RADREPLY') as $candidate) {
        if ($key === ($config[$candidate] ?? null)) { $key = $candidate; break; }
    }
    if (!in_array($key, array('CONFIG_DB_TBL_RADCHECK','CONFIG_DB_TBL_RADREPLY'), true) ||
        !is_string($attribute) || !is_string($op) || !is_string($value) ||
        $attribute === '' || dalo_attribute_pdo_length($attribute)>64 || strlen($op)>2 ||
        dalo_attribute_pdo_length($value)>253) { throw new InvalidArgumentException('Invalid attribute'); }
    $table = dalo_create_table($config, $key);
    $sql = "INSERT INTO $table (username,attribute,op,value) VALUES (?,?,?,?)";
    $stmt = $pdo->prepare($sql); $stmt->execute(array($subject,$attribute,$op,$value));
    $logDebugSQL .= "$sql;\n";
    return $stmt->rowCount() === 1;
}

function dalo_create_collision(PDO $pdo, $config, $name) {
    $name = dalo_create_name($name);
    foreach (array('CONFIG_DB_TBL_RADCHECK','CONFIG_DB_TBL_DALOUSERINFO','CONFIG_DB_TBL_DALOUSERBILLINFO') as $key) {
        $table = dalo_create_table($config,$key);
        $stmt = $pdo->prepare("SELECT id FROM $table WHERE username=? LIMIT 1 FOR UPDATE");
        $stmt->execute(array($name)); $found = $stmt->fetchColumn() !== false; $stmt->closeCursor();
        if ($found) { return true; }
    }
    return false;
}
