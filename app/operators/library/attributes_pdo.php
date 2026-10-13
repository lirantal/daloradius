<?php
/* UNIT-023: opt-in PDO path for the shared attribute mutation provider. */

function dalo_attribute_pdo_identifier($config, $key) {
    $allowed = array('CONFIG_DB_TBL_RADCHECK', 'CONFIG_DB_TBL_RADREPLY',
                     'CONFIG_DB_TBL_RADGROUPCHECK', 'CONFIG_DB_TBL_RADGROUPREPLY');
    $name = $config[$key] ?? null;
    if (!in_array($key, $allowed, true) || !is_string($name) ||
        !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $name)) {
        throw new InvalidArgumentException('Invalid attribute table configuration');
    }
    return '`' . $name . '`';
}

function dalo_attribute_pdo_table($config, $table, $param) {
    if (!is_string($table) || !in_array($param, array('username', 'groupname'), true)) {
        throw new InvalidArgumentException('Invalid attribute table selection');
    }
    $keys = $param === 'groupname'
          ? array('CONFIG_DB_TBL_RADGROUPCHECK','CONFIG_DB_TBL_RADGROUPREPLY')
          : array('CONFIG_DB_TBL_RADCHECK','CONFIG_DB_TBL_RADREPLY');
    foreach ($keys as $key) {
        $name = $config[$key] ?? null;
        if ($table === $name || $table === '`' . $name . '`') {
            return dalo_attribute_pdo_identifier($config, $key);
        }
    }
    throw new InvalidArgumentException('Unknown attribute table');
}

function dalo_attribute_pdo_selector($config, $selector, $userOrGroup) {
    if (!is_string($selector)) {
        throw new InvalidArgumentException('Invalid attribute table selector');
    }
    $known = array('check' => 'check', 'reply' => 'reply',
                   'radcheck' => 'check', 'radreply' => 'reply',
                   'radgroupcheck' => 'check', 'radgroupreply' => 'reply');
    foreach (array('CONFIG_DB_TBL_RADCHECK' => 'check',
                   'CONFIG_DB_TBL_RADREPLY' => 'reply',
                   'CONFIG_DB_TBL_RADGROUPCHECK' => 'check',
                   'CONFIG_DB_TBL_RADGROUPREPLY' => 'reply') as $key => $kind) {
        if (isset($config[$key]) && is_string($config[$key])) {
            $known[$config[$key]] = $kind;
        }
    }
    if (!isset($known[$selector])) {
        throw new InvalidArgumentException('Unknown attribute table selector');
    }
    $prefix = $userOrGroup === 'group' ? 'CONFIG_DB_TBL_RADGROUP' : 'CONFIG_DB_TBL_RAD';
    return array($known[$selector], dalo_attribute_pdo_identifier($config,
                 $prefix . strtoupper($known[$selector])));
}

function dalo_attribute_pdo_length($value) {
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

/** Pure input pass: never start writing until every posted attribute is valid. */
function dalo_attribute_pdo_parse($post, $skip, $config, $ops, $insertOnly, $userOrGroup) {
    if (!is_array($post) || !is_array($skip) || !is_array($ops) ||
        !is_bool($insertOnly) || !in_array($userOrGroup, array('user','group'), true)) {
        throw new InvalidArgumentException('Invalid attribute request');
    }
    $entries = array();
    $seen = array();
    foreach ($post as $element => $field) {
        if (in_array($element, $skip, true) || !is_array($field)) {
            continue;
        }
        if (array_keys($field) !== array(0, 1, 2, 3)) {
            throw new InvalidArgumentException('Malformed attribute controls');
        }
        foreach ($field as $value) {
            if (!is_string($value)) {
                throw new InvalidArgumentException('Malformed attribute value');
            }
        }
        list($idAndAttribute, $value, $op, $selector) = array_map('trim', $field);
        $id = 0;
        if (strpos($idAndAttribute, '__') !== false) {
            list($rawId, $attribute) = explode('__', $idAndAttribute, 2);
            if (!ctype_digit($rawId) || strlen($rawId) > 10) {
                throw new InvalidArgumentException('Invalid attribute ID');
            }
            $id = (int) $rawId;
            if ($id > 4294967295) {
                throw new InvalidArgumentException('Invalid attribute ID');
            }
            $attribute = trim($attribute);
        } else {
            $attribute = $idAndAttribute;
        }
        if ($attribute === '' && $value === '') {
            continue; // The empty row in the default form is not an attribute.
        }
        if ($attribute === '' || $value === '' ||
            dalo_attribute_pdo_length($attribute) > 64 ||
            dalo_attribute_pdo_length($value) > 253 ||
            !in_array($op, $ops, true)) {
            throw new InvalidArgumentException('Invalid attribute or operator');
        }
        list($kind, $table) = dalo_attribute_pdo_selector($config, $selector, $userOrGroup);
        if (is_passwordlike_attribute($attribute) &&
            !dalo_cleartext_password_allowed() &&
            in_array($attribute, dalo_cleartext_password_attributes(), true)) {
            throw new InvalidArgumentException('Cleartext password attribute is disabled');
        }
        $id = $insertOnly ? 0 : $id;
        if ($id !== 0) {
            $tag = $kind . ':' . $id;
            if (isset($seen[$tag])) {
                throw new InvalidArgumentException('Duplicate attribute ID');
            }
            $seen[$tag] = true;
        }
        $entries[] = array($id, $attribute, $value, $op, $kind, $table);
    }
    return $entries;
}

function dalo_attribute_pdo_require_innodb(PDO $pdo, $tables) {
    $names = array_values(array_unique(array_map(function ($table) {
        return trim($table, '`');
    }, $tables)));
    if (!$names) {
        return;
    }
    $marks = implode(',', array_fill(0, count($names), '?'));
    $stmt = $pdo->prepare("SELECT TABLE_NAME,ENGINE FROM INFORMATION_SCHEMA.TABLES
                            WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ($marks)");
    $stmt->execute($names);
    $engines = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($names as $name) {
        if (!isset($engines[$name]) || strcasecmp($engines[$name], 'InnoDB') !== 0) {
            throw new RuntimeException('Attribute writes require InnoDB');
        }
    }
}

function dalo_attribute_exists_pdo(PDO $pdo, $config, $tableName, $param,
                                   $subject, $attribute, $op, $value, $forUpdate = false) {
    global $logDebugSQL;
    if (!is_string($subject) || !is_string($attribute) ||
        !is_string($op) || !is_string($value)) {
        throw new InvalidArgumentException('Invalid attribute lookup');
    }
    $table = dalo_attribute_pdo_table($config, $tableName, $param);
    $lock = $forUpdate ? ' FOR UPDATE' : '';
    $stmt = $pdo->prepare("SELECT 1 FROM $table WHERE `$param`=:subject
                           AND `attribute`=:attribute AND `op`=:op AND `value`=:value
                           LIMIT 1$lock");
    $stmt->execute(array(':subject' => $subject, ':attribute' => $attribute,
                         ':op' => $op, ':value' => $value));
    $found = $stmt->fetchColumn() !== false;
    $stmt->closeCursor();
    $logDebugSQL .= "SELECT 1 FROM $table WHERE `$param`=? AND attribute=? AND op=? AND value=?;\n";
    return $found;
}

/** Caller owns begin/commit/rollback and any other dependent writes. */
function dalo_handle_attributes_pdo(PDO $pdo, $config, $post, $subject,
                                    $skip, $ops, $insertOnly = true, $userOrGroup = 'user') {
    global $logDebugSQL;
    if (!$pdo->inTransaction() || !is_string($subject) || $subject === '' ||
        dalo_attribute_pdo_length($subject) > 64) {
        throw new InvalidArgumentException('Caller transaction or subject is invalid');
    }
    $entries = dalo_attribute_pdo_parse($post, $skip, $config, $ops, $insertOnly, $userOrGroup);
    if (!$entries) {
        return 0;
    }
    $tables = array();
    foreach ($entries as $entry) {
        $tables[] = $entry[5];
    }
    dalo_attribute_pdo_require_innodb($pdo, $tables);
    $param = $userOrGroup === 'group' ? 'groupname' : 'username';
    $locked = array();
    $lockOrder = array();
    foreach ($entries as $entry) {
        if ($entry[0] !== 0) {
            $lockOrder[$entry[5] . ':' . sprintf('%010u', $entry[0])] = $entry;
        }
    }
    ksort($lockOrder, SORT_STRING);
    foreach ($lockOrder as $entry) {
        list($id, $attribute, $value, $op, $kind, $table) = $entry;
        $stmt = $pdo->prepare("SELECT `$param`,attribute,value,op FROM $table WHERE id=:id FOR UPDATE");
        $stmt->execute(array(':id' => $id));
        $old = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        if (!$old || $old[$param] !== $subject || $old['attribute'] !== $attribute) {
            throw new InvalidArgumentException('Stale or foreign attribute ID');
        }
        $locked[$kind . ':' . $id] = $old;
    }
    $written = 0;
    foreach ($entries as $entry) {
        list($id, $attribute, $value, $op, $kind, $table) = $entry;
        if (is_passwordlike_attribute($attribute)) {
            if ($id && $locked[$kind . ':' . $id]['value'] === $value) {
                if ($locked[$kind . ':' . $id]['op'] !== $op) {
                    $stmt = $pdo->prepare("UPDATE $table SET op=:op WHERE id=:id AND `$param`=:subject");
                    $stmt->execute(array(':op' => $op, ':id' => $id, ':subject' => $subject));
                    $written += $stmt->rowCount();
                    $logDebugSQL .= "UPDATE $table SET op=? WHERE id=? AND `$param`=?;\n";
                }
                continue;
            }
            $value = hashPasswordAttribute($attribute, $value);
            if (!is_string($value)) {
                throw new RuntimeException('Could not hash attribute');
            }
        }
        if (dalo_attribute_exists_pdo($pdo, $config, $table, $param,
                                      $subject, $attribute, $op, $value, true)) {
            continue;
        }
        if ($id === 0) {
            $stmt = $pdo->prepare("INSERT INTO $table (`$param`,attribute,op,value)
                                   VALUES (:subject,:attribute,:op,:value)");
            $stmt->execute(array(':subject' => $subject, ':attribute' => $attribute,
                                 ':op' => $op, ':value' => $value));
            $logDebugSQL .= "INSERT INTO $table (`$param`,attribute,op,value) VALUES (?,?,?,?);\n";
        } else {
            $stmt = $pdo->prepare("UPDATE $table SET value=:value,op=:op
                                   WHERE id=:id AND `$param`=:subject AND attribute=:attribute");
            $stmt->execute(array(':value' => $value, ':op' => $op, ':id' => $id,
                                 ':subject' => $subject, ':attribute' => $attribute));
            $logDebugSQL .= "UPDATE $table SET value=?,op=? WHERE id=? AND `$param`=? AND attribute=?;\n";
        }
        $written += $stmt->rowCount();
    }
    return $written;
}
