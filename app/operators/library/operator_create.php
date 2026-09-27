<?php
/** UNIT-025: validate operator creation and persist identity plus ACLs atomically. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/operator_create.php') !== false) {
    http_response_code(404);
    exit;
}

function dalo_operator_create_table($config, $key) {
    if (!in_array($key, array('CONFIG_DB_TBL_DALOOPERATORS',
                             'CONFIG_DB_TBL_DALOOPERATORS_ACL',
                             'CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES'), true)) {
        throw new InvalidArgumentException('Invalid operator table');
    }
    $name = $config[$key] ?? null;
    if (!is_string($name) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $name)) {
        throw new InvalidArgumentException('Invalid operator table');
    }
    return '`' . $name . '`';
}

function dalo_operator_create_innodb(PDO $pdo, $tables) {
    $names = array_values(array_unique(array_map(function ($name) {
        return trim($name, '`');
    }, $tables)));
    $slots = implode(',', array_fill(0, count($names), '?'));
    $stmt = $pdo->prepare("SELECT TABLE_NAME,ENGINE FROM INFORMATION_SCHEMA.TABLES
                           WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ($slots)");
    $stmt->execute($names);
    $engines = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($names as $name) {
        if (!isset($engines[$name]) || strcasecmp($engines[$name], 'InnoDB') !== 0) {
            throw new RuntimeException('Operator creation requires InnoDB tables');
        }
    }
}

function dalo_operator_create_text($post, $key, $maximum) {
    if (!isset($post[$key])) {
        return '';
    }
    if (!is_string($post[$key])) {
        throw new InvalidArgumentException('Invalid operator field');
    }
    $value = trim($post[$key]);
    $characters = preg_match_all('/./us', $value);
    if ($characters === false || $characters > $maximum || strpos($value, "\0") !== false) {
        throw new InvalidArgumentException('Invalid operator field length or encoding');
    }
    return $value;
}

/** Parse every submitted value before opening a database transaction. */
function dalo_operator_create_fields(array $post) {
    $username = trim(str_replace('%', '', dalo_operator_create_text($post, 'operator_username', 128)));
    if ($username === '' || preg_match_all('/./us', $username) > 32) {
        throw new InvalidArgumentException('username is empty or too long');
    }
    $profile = array('username' => $username);
    foreach (array('firstname' => 32, 'lastname' => 32, 'title' => 32,
                   'department' => 32, 'company' => 32, 'phone1' => 32,
                   'phone2' => 32, 'email1' => 32, 'email2' => 32,
                   'messenger1' => 32, 'messenger2' => 32, 'notes' => 128) as $key => $maximum) {
        $profile[$key] = dalo_operator_create_text($post, $key, $maximum);
    }
    if (isset($post['operator_password']) && !is_string($post['operator_password'])) {
        throw new InvalidArgumentException('Invalid operator password field');
    }
    if (isset($post['external_id']) && !is_string($post['external_id'])) {
        throw new InvalidArgumentException('Invalid external identity field');
    }
    if (isset($post['auth_source']) && !is_string($post['auth_source'])) {
        throw new InvalidArgumentException('Invalid authentication source');
    }
    $profile['auth_source'] = operator_auth_source_from_post($post);
    $profile['external_id'] = operator_normalize_external_id($post['external_id'] ?? null);
    $profile['password'] = isset($post['operator_password']) ? trim($post['operator_password']) : '';
    $acls = array();
    foreach ($post as $field => $access) {
        if (strncmp($field, 'ACL_', 4) !== 0) {
            continue;
        }
        $file = substr($field, 4);
        if ($file === '' || !is_string($access) ||
            !in_array($access, array('0', '1'), true)) {
            throw new InvalidArgumentException('Invalid operator ACL');
        }
        $acls[$file] = $access;
    }
    if (!$acls) {
        throw new InvalidArgumentException('At least one operator ACL is required');
    }
    ksort($acls, SORT_STRING);
    $profile['acls'] = $acls;
    return $profile;
}

/** On failure, no operator row or ACL row survives this transaction. */
function dalo_operator_create(PDO $pdo, $config, $profile, $identity, $operator) {
    $operators = dalo_operator_create_table($config, 'CONFIG_DB_TBL_DALOOPERATORS');
    $acl = dalo_operator_create_table($config, 'CONFIG_DB_TBL_DALOOPERATORS_ACL');
    $aclFiles = dalo_operator_create_table($config, 'CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES');
    dalo_operator_create_innodb($pdo, array($operators, $acl, $aclFiles));
    if (!$identity['ok'] ||
        ($identity['password_hash'] !== null && strlen($identity['password_hash']) > 95)) {
        throw new InvalidArgumentException('Invalid operator identity');
    }
    // Unlike a SELECT-and-insert alone, cooperating create requests cannot race
    // against one another on a schema without a unique username constraint.
    $lockName = 'dalo-op-create-' . substr(hash('sha256', (string) $pdo->query('SELECT DATABASE()')->fetchColumn()), 0, 40);
    $lock = $pdo->prepare('SELECT GET_LOCK(:name, 5)');
    $lock->execute(array(':name' => $lockName));
    if ((int) $lock->fetchColumn() !== 1) {
        throw new RuntimeException('Operator creation is busy');
    }
    try {
        if (!$pdo->beginTransaction()) {
            throw new RuntimeException('Could not begin operator creation');
        }
        try {
            $exists = $pdo->prepare("SELECT id FROM $operators WHERE username=:username FOR UPDATE");
            $exists->execute(array(':username' => $profile['username']));
            if ($exists->fetchColumn() !== false) {
                throw new DomainException('Operator already exists');
            }
            $files = $pdo->query("SELECT file FROM $aclFiles ORDER BY id FOR UPDATE");
            $allowed = array_fill_keys($files->fetchAll(PDO::FETCH_COLUMN), true);
            foreach ($profile['acls'] as $file => $access) {
                if (!isset($allowed[$file])) {
                    throw new InvalidArgumentException('Unknown operator ACL');
                }
            }
            $now = date('Y-m-d H:i:s');
            $columns = array('username','password','auth_source','external_id',
                'firstname','lastname','title','department','company','phone1','phone2',
                'email1','email2','messenger1','messenger2','notes','creationdate','creationby');
            $names = implode(',', array_map(function ($column) { return '`' . $column . '`'; }, $columns));
            $slots = implode(',', array_fill(0, count($columns), '?'));
            $record = $profile;
            $record['password'] = $identity['password_hash'];
            $record['auth_source'] = $identity['auth_source'];
            $record['external_id'] = $identity['external_id'];
            $record['creationdate'] = $now;
            $record['creationby'] = $operator;
            $values = array();
            foreach ($columns as $column) {
                $values[] = $record[$column];
            }
            $insert = $pdo->prepare("INSERT INTO $operators ($names) VALUES ($slots)");
            $insert->execute($values);
            $id = (int) $pdo->lastInsertId();
            if ($id < 1) {
                throw new RuntimeException('No operator ID returned');
            }
            $insertAcl = $pdo->prepare("INSERT INTO $acl (operator_id,file,access) VALUES (?,?,?)");
            foreach ($profile['acls'] as $file => $access) {
                $insertAcl->execute(array($id, $file, $access));
            }
            if (!$pdo->commit()) {
                throw new RuntimeException('Operator creation was not committed');
            }
            return $id;
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $error;
        }
    } finally {
        try {
            $release = $pdo->prepare('SELECT RELEASE_LOCK(:name)');
            $release->execute(array(':name' => $lockName));
        } catch (Throwable $ignored) {
            // A cleanup failure after commit must not report a committed account as failed.
            // The non-persistent connection releases its lock on disconnect.
        }
    }
}
