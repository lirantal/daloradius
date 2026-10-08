<?php
/* R06: dictionary pages use native PDO; shared PEAR consumers remain independent. */
require_once dirname(__DIR__, 2) . '/common/includes/pdo_connection.php';
require_once __DIR__ . '/dictionary_import.php';

function dalo_dictionary_text($value, $limit, $required = false) {
    if (!is_string($value) || strpos($value, "\0") !== false || !preg_match('//u', $value) ||
        (function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value)) > $limit ||
        ($required && $value === '')) {
        throw new InvalidArgumentException('Invalid dictionary input');
    }
    return $value;
}

function dalo_dictionary_request_field($input, $name, $alias = null) {
    $value = $input[$name] ?? ($alias === null ? '' : ($input[$alias] ?? ''));
    if (!is_string($value) || ($alias !== null && isset($input[$name], $input[$alias]) &&
        $input[$name] !== $input[$alias])) {
        throw new InvalidArgumentException('Invalid dictionary field');
    }
    return trim($value);
}

function dalo_dictionary_fields($input, $types, $ops, $helpers) {
    $r = array();
    foreach (array('vendor'=>32, 'attribute'=>64, 'type'=>30, 'value'=>64, 'format'=>20,
                  'recommendedOP'=>32, 'recommendedTable'=>32, 'recommendedHelper'=>32,
                  'recommendedTooltip'=>512) as $key=>$limit) {
        $alias = strpos($key, 'recommended') === 0 ? ucfirst($key) : null;
        $r[$key] = dalo_dictionary_text(dalo_dictionary_request_field($input, $key, $alias),
                                      $limit, in_array($key, array('vendor','attribute'), true));
    }
    $baseType = strtolower(preg_split('/[\s#]+/', $r['type'], 2)[0]);
    if (!in_array($baseType, $types, true)) { $r['type'] = ''; }
    if (!in_array($r['recommendedOP'], $ops, true)) { $r['recommendedOP'] = ''; }
    if (!in_array($r['recommendedTable'], array('check','reply'), true)) { $r['recommendedTable'] = ''; }
    if (!in_array($r['recommendedHelper'], $helpers, true)) { $r['recommendedHelper'] = ''; }
    return $r;
}

function dalo_dictionary_read(PDO $pdo, $table, $vendor, $attribute, $lock = false) {
    $stmt = $pdo->prepare("SELECT id, Type, Value, Format, RecommendedOP, RecommendedTable,
                                 RecommendedHelper, RecommendedTooltip FROM $table
                          WHERE Vendor=:vendor AND Attribute=:attribute ORDER BY id" .
                          ($lock ? ' FOR UPDATE' : ''));
    $stmt->execute(array(':vendor'=>$vendor, ':attribute'=>$attribute));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/** Own the write transaction, never commit/rollback a caller's earlier work. */
function dalo_dictionary_mutate(PDO $pdo, $config, $operation) {
    if ($pdo->inTransaction()) { throw new LogicException('Dictionary page owns its transaction'); }
    $table = dalo_dictionary_table($config);
    dalo_dictionary_require_innodb($pdo, $table);
    // The schema has no universal vendor/attribute uniqueness. Serialize page writers.
    $schema = $pdo->query('SELECT DATABASE()')->fetchColumn();
    $name = 'dalo_dict_' . hash('sha256', $schema . ':' . $table);
    $name = substr($name, 0, 64);
    $lock = $pdo->prepare('SELECT GET_LOCK(:name, 10)');
    $lock->execute(array(':name'=>$name));
    if ((int)$lock->fetchColumn() !== 1) { throw new RuntimeException('Dictionary is busy'); }
    try {
        $pdo->beginTransaction();
        try { $result = $operation($pdo, $table); $pdo->commit(); return $result; }
        catch (Throwable $e) { if ($pdo->inTransaction()) { $pdo->rollBack(); } throw $e; }
    } finally {
        try { $release = $pdo->prepare('SELECT RELEASE_LOCK(:name)'); $release->execute(array(':name'=>$name)); }
        catch (Throwable $ignored) { /* Nonpersistent connection teardown releases it too. */ }
    }
}

function dalo_dictionary_create(PDO $pdo, $config, $fields) {
    return dalo_dictionary_mutate($pdo, $config, function ($pdo, $table) use ($fields) {
        // Preserve the creation page's global attribute-name collision policy.
        $exists = $pdo->prepare("SELECT id FROM $table WHERE Attribute=:attribute ORDER BY id FOR UPDATE");
        $exists->execute(array(':attribute'=>$fields['attribute']));
        if ($exists->fetchColumn() !== false) { return false; }
        $stmt = $pdo->prepare("INSERT INTO $table (Type, Attribute, Value, Format, Vendor,
                              RecommendedOP, RecommendedTable, RecommendedHelper, RecommendedTooltip)
                              VALUES (:type, :attribute, '', '', :vendor, :recommendedOP,
                                      :recommendedTable, :recommendedHelper, :recommendedTooltip)");
        $values = array();
        foreach ($fields as $key=>$value) { if (!in_array($key, array('value','format'), true)) { $values[':'.$key]=$value; } }
        $stmt->execute($values);
        return true;
    });
}

function dalo_dictionary_edit(PDO $pdo, $config, $fields) {
    return dalo_dictionary_mutate($pdo, $config, function ($pdo, $table) use ($fields) {
        if (!dalo_dictionary_read($pdo, $table, $fields['vendor'], $fields['attribute'], true)) { return false; }
        $stmt = $pdo->prepare("UPDATE $table SET Type=:type,
                              RecommendedOP=:recommendedOP, RecommendedTable=:recommendedTable,
                              RecommendedHelper=:recommendedHelper, RecommendedTooltip=:recommendedTooltip
                              WHERE Vendor=:vendor AND Attribute=:attribute");
        $values = array(); foreach ($fields as $key=>$value) { if (!in_array($key, array('value','format'), true)) { $values[':'.$key]=$value; } }
        $stmt->execute($values);
        return true; // An unchanged UPDATE is still a successful existing edit.
    });
}

function dalo_dictionary_selection_token($vendor, $attribute) {
    if (strpos($vendor, '__') !== false || strpos($attribute, '__') !== false ||
        strpos($vendor, 'dict:') === 0) {
        return 'dict:' . rawurlencode(json_encode(array($vendor, $attribute), JSON_THROW_ON_ERROR));
    }
    return urlencode($vendor) . '__' . urlencode($attribute);
}

function dalo_dictionary_selection($input) {
    if (!is_array($input) || !$input) { throw new InvalidArgumentException('Missing dictionary selection'); }
    $result = array();
    foreach ($input as $token) {
        if (!is_string($token) || strlen($token)>1600 || preg_match('/%(?![a-f0-9]{2})/i', $token)) {
            throw new InvalidArgumentException('Invalid dictionary selection');
        }
        if (strpos($token, 'dict:') === 0) {
            $pair = json_decode(rawurldecode(substr($token, 5)), true, 4, JSON_THROW_ON_ERROR);
            if (!is_array($pair) || array_keys($pair) !== array(0,1) ||
                !is_string($pair[0]) || !is_string($pair[1]) ||
                dalo_dictionary_selection_token($pair[0], $pair[1]) !== $token) {
                throw new InvalidArgumentException('Ambiguous dictionary selection');
            }
        } else {
            $pair = explode('__', $token);
            if (count($pair)!==2) { throw new InvalidArgumentException('Ambiguous dictionary selection'); }
            $pair = array_map('urldecode', $pair);
        }
        dalo_dictionary_text($pair[0], 32, true); dalo_dictionary_text($pair[1], 64, true);
        $result[json_encode($pair, JSON_THROW_ON_ERROR)] = $pair;
    }
    return array_values($result);
}

function dalo_dictionary_delete(PDO $pdo, $config, $pairs) {
    return dalo_dictionary_mutate($pdo, $config, function ($pdo, $table) use ($pairs) {
        foreach ($pairs as $pair) {
            if (!dalo_dictionary_read($pdo, $table, $pair[0], $pair[1], true)) {
                throw new RuntimeException('Stale dictionary selection');
            }
        }
        $delete = $pdo->prepare("DELETE FROM $table WHERE Vendor=:vendor AND Attribute=:attribute");
        $count = 0;
        foreach ($pairs as $pair) { $delete->execute(array(':vendor'=>$pair[0], ':attribute'=>$pair[1])); $count += $delete->rowCount(); }
        return $count;
    });
}
