<?php
/** R01b: message reads and caller-owned transactional writes. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/common/includes/messages_pdo.php') !== false) {
    http_response_code(404);
    exit;
}

function dalo_messages_table($config) {
    $name = $config['CONFIG_DB_TBL_DALOMESSAGES'] ?? null;
    if (!is_string($name) || !preg_match('/\A[A-Za-z_][A-Za-z0-9_]{0,63}\z/', $name)) {
        throw new InvalidArgumentException('Invalid message table');
    }
    return '`' . $name . '`';
}

function dalo_messages_input($post, $types, $purifier) {
    $updates = array();
    foreach ($types as $type) {
        $flag = $post[$type . '_message_changed'] ?? 'no';
        if (!is_string($flag) || !in_array($flag, array('no', 'yes'), true)) {
            throw new InvalidArgumentException('Invalid message control');
        }
        if ($flag === 'yes') {
            $content = $post[$type . '_message'] ?? null;
            if (!is_string($content)) {
                throw new InvalidArgumentException('Invalid message content');
            }
            $updates[$type] = $purifier->purify($content);
        }
    }
    return $updates;
}

function dalo_messages_read(PDO $pdo, $config, $type, $purifier) {
    global $logDebugSQL;
    $table = dalo_messages_table($config);
    $sql = "SELECT content,modified_on,modified_by,created_on,created_by FROM $table
                 WHERE `type`=:type ORDER BY id ASC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':type' => $type));
    $logDebugSQL .= $sql . ";\n";
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$data) {
        $data = array('content' => '', 'modified_on' => null, 'modified_by' => null,
                      'created_on' => '', 'created_by' => '');
    }
    $data['content'] = $purifier->purify((string) $data['content']);
    return $data;
}

function dalo_messages_lock(PDO $pdo, $config, $updates, $operator) {
    if (!$pdo->inTransaction() || !is_string($operator) || $operator === '') {
        throw new InvalidArgumentException('Invalid message transaction or actor');
    }
    $table = dalo_messages_table($config);
    $slots = implode(',', array_fill(0, count($updates), '?'));
    $stmt = $pdo->prepare("SELECT id,`type` FROM $table WHERE `type` IN ($slots) ORDER BY id FOR UPDATE");
    $stmt->execute(array_keys($updates));
    $found = array_fill_keys($stmt->fetchAll(PDO::FETCH_COLUMN, 1), true);
    foreach ($updates as $type => $value) {
        if (!isset($found[$type])) {
            throw new DomainException('Selected message no longer exists; reload and retry');
        }
    }
    $engine = $pdo->prepare('SELECT ENGINE FROM INFORMATION_SCHEMA.TABLES
                             WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:table');
    $engine->execute(array(':table' => trim($table, '`')));
    if (strcasecmp((string) $engine->fetchColumn(), 'InnoDB') !== 0) {
        throw new RuntimeException('Message updates require InnoDB');
    }
    // Validate actual storage before a permissive SQL mode can truncate content/actor.
    $meta = $pdo->prepare('SELECT COLUMN_NAME,CHARACTER_MAXIMUM_LENGTH,CHARACTER_OCTET_LENGTH
                            FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:table');
    $meta->execute(array(':table' => trim($table, '`')));
    $columns = array();
    foreach ($meta->fetchAll(PDO::FETCH_ASSOC) as $column) {
        $columns[$column['COLUMN_NAME']] = $column;
    }
    foreach (array('content' => array_values($updates), 'modified_by' => array($operator)) as $name => $values) {
        if (!isset($columns[$name]['CHARACTER_MAXIMUM_LENGTH'], $columns[$name]['CHARACTER_OCTET_LENGTH'])) {
            throw new RuntimeException('Invalid message storage');
        }
        foreach ($values as $value) {
            $characters = preg_match_all('/./us', $value);
            if ($characters === false || $characters > (int) $columns[$name]['CHARACTER_MAXIMUM_LENGTH'] ||
                strlen($value) > (int) $columns[$name]['CHARACTER_OCTET_LENGTH']) {
                throw new InvalidArgumentException('Message value exceeds storage capacity');
            }
        }
    }
}

function dalo_messages_update(PDO $pdo, $config, $type, $content, $operator) {
    global $logDebugSQL;
    if (!$pdo->inTransaction()) {
        throw new LogicException('Message write requires caller transaction');
    }
    $table = dalo_messages_table($config);
    $sql = "UPDATE $table SET content=:content,modified_on=NOW(),modified_by=:operator WHERE `type`=:type";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute(array(':content' => $content, ':operator' => $operator, ':type' => $type));
    $logDebugSQL .= $sql . ";\n";
    return $ok;
}
