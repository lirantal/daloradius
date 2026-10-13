<?php
/** UNIT-026: update an operator, MFA state and ACLs on one PDO transaction. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/operator_edit.php') !== false) {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/operator_create.php';
require_once __DIR__ . '/../include/management/operator_identity.php';

function dalo_operator_edit_row(PDO $pdo, $config, $username) {
    $table = dalo_operator_create_table($config, 'CONFIG_DB_TBL_DALOOPERATORS');
    $stmt = $pdo->prepare("SELECT id,username,auth_source,external_id,firstname,lastname,title,
                    department,company,phone1,phone2,email1,email2,messenger1,messenger2,
                    notes,lastlogin,creationdate,creationby,updatedate,updateby,
                    totp_enabled,totp_confirmed_at FROM $table WHERE username=:username LIMIT 2");
    $stmt->execute(array(':username' => $username));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return count($rows) === 1 ? $rows[0] : null;
}

/** Reject nested/malformed controls before locking or writing anything. */
function dalo_operator_edit_input($post) {
    if (!is_array($post) || !isset($post['identity_operator_id']) ||
        !is_string($post['identity_operator_id']) ||
        !ctype_digit($post['identity_operator_id']) ||
        (int) $post['identity_operator_id'] < 1 ||
        !isset($post['identity_auth_source']) || !is_string($post['identity_auth_source']) ||
        !array_key_exists('identity_external_id', $post) ||
        !is_string($post['identity_external_id'])) {
        throw new InvalidArgumentException('Invalid operator identity snapshot; reload and retry');
    }
    foreach (array('operator_password','auth_source','external_id',
                   'confirm_auth_source_change','reset_totp') as $field) {
        if (isset($post[$field]) && !is_string($post[$field])) {
            throw new InvalidArgumentException('Invalid operator control');
        }
    }
    if ((isset($post['confirm_auth_source_change']) && $post['confirm_auth_source_change'] !== '1') ||
        (isset($post['reset_totp']) && $post['reset_totp'] !== '1')) {
        throw new InvalidArgumentException('Invalid operator control');
    }
    $fields = array();
    foreach (array('firstname' => 32, 'lastname' => 32, 'title' => 32,
                   'department' => 32, 'company' => 32, 'phone1' => 32,
                   'phone2' => 32, 'email1' => 32, 'email2' => 32,
                   'messenger1' => 32, 'messenger2' => 32, 'notes' => 128) as $field => $limit) {
        $fields[$field] = dalo_operator_create_text($post, $field, $limit);
    }
    $acls = array();
    foreach ($post as $field => $access) {
        if (strncmp($field, 'ACL_', 4) !== 0) {
            continue;
        }
        $file = substr($field, 4);
        if ($file === '' || !is_string($access) || !in_array($access, array('0','1'), true)) {
            throw new InvalidArgumentException('Invalid operator ACL');
        }
        $acls[$file] = $access;
    }
    ksort($acls, SORT_STRING);
    $requestedSource = null;
    if (isset($post['auth_source'])) {
        $requestedSource = operator_normalize_auth_source($post['auth_source']);
        if ($requestedSource === null) {
            throw new InvalidArgumentException('Invalid authentication source');
        }
    }
    return array(
        'id' => (int) $post['identity_operator_id'],
        'expected_source' => $post['identity_auth_source'],
        'expected_external' => $post['identity_external_id'],
        'source' => $requestedSource,
        'external' => operator_normalize_external_id($post['external_id'] ?? null),
        'password' => isset($post['operator_password']) ? trim($post['operator_password']) : '',
        'confirm' => isset($post['confirm_auth_source_change']),
        'reset_totp' => isset($post['reset_totp']),
        'fields' => $fields,
        'acls' => $acls,
    );
}

function dalo_operator_edit_apply(PDO $pdo, $config, $username, $input, $operator) {
    $table = dalo_operator_create_table($config, 'CONFIG_DB_TBL_DALOOPERATORS');
    $aclTable = dalo_operator_create_table($config, 'CONFIG_DB_TBL_DALOOPERATORS_ACL');
    $filesTable = dalo_operator_create_table($config, 'CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES');
    dalo_operator_create_innodb($pdo, array($table, $aclTable, $filesTable));
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not begin operator edit');
    }
    try {
        $lock = $pdo->prepare("SELECT id,username,auth_source,external_id FROM $table
                               WHERE id=:id AND username=:username FOR UPDATE");
        $lock->execute(array(':id' => $input['id'], ':username' => $username));
        $current = $lock->fetch(PDO::FETCH_ASSOC);
        if (!$current || $lock->fetch(PDO::FETCH_ASSOC)) {
            throw new DomainException('Operator changed; reload and retry');
        }
        $source = operator_normalize_auth_source($current['auth_source']) ?? 'local';
        if (!operator_identity_state_matches($source, $current['external_id'],
                                             $input['expected_source'], $input['expected_external'])) {
            throw new DomainException('Operator identity changed; reload and retry');
        }
        $requested = $input['source'] === null ? $source : $input['source'];
        $identity = operator_prepare_update_identity($source, $requested, $input['password'],
                                                      $input['confirm'], $input['external']);
        if (!$identity['ok']) {
            throw new InvalidArgumentException($identity['error']);
        }
        if (isset($identity['password_hash']) && strlen($identity['password_hash']) > 95) {
            throw new InvalidArgumentException('Invalid operator password hash length');
        }
        if ($input['acls']) {
            $catalog = $pdo->query("SELECT file FROM $filesTable ORDER BY id FOR UPDATE");
            $available = array_fill_keys($catalog->fetchAll(PDO::FETCH_COLUMN), true);
            foreach ($input['acls'] as $file => $access) {
                if (!isset($available[$file])) {
                    throw new InvalidArgumentException('Unknown operator ACL');
                }
            }
        }
        $readAcls = $pdo->prepare("SELECT file FROM $aclTable WHERE operator_id=:id ORDER BY id FOR UPDATE");
        $readAcls->execute(array(':id' => $input['id']));
        $existing = array_fill_keys($readAcls->fetchAll(PDO::FETCH_COLUMN), true);
        $now = date('Y-m-d H:i:s');
        $parts = array();
        $values = array();
        foreach ($input['fields'] as $field => $value) {
            $parts[] = "`$field`=?";
            $values[] = $value;
        }
        $parts[] = '`auth_source`=?';
        $values[] = $identity['auth_source'];
        $parts[] = '`external_id`=?';
        $values[] = $identity['external_id'];
        if ($identity['password_mode'] !== 'preserve') {
            $parts[] = '`password`=?';
            $values[] = $identity['password_mode'] === 'clear' ? null : $identity['password_hash'];
        }
        $parts[] = '`updatedate`=?';
        $values[] = $now;
        $parts[] = '`updateby`=?';
        $values[] = $operator;
        $values[] = $input['id'];
        $update = $pdo->prepare("UPDATE $table SET " . implode(',', $parts) . ' WHERE id=?');
        $update->execute($values);
        // An idempotent edit can report zero changed rows. The row was locked above.
        if ($input['reset_totp']) {
            $reset = $pdo->prepare("UPDATE $table SET totp_enabled=0,totp_secret=NULL,
                         totp_last_counter=NULL,totp_confirmed_at=NULL,totp_recovery_codes=NULL,
                         updatedate=:updated,updateby=:operator WHERE id=:id");
            $reset->execute(array(':updated' => $now, ':operator' => $operator, ':id' => $input['id']));
        }
        $changeAcl = $pdo->prepare("UPDATE $aclTable SET access=:access
                                   WHERE operator_id=:id AND file=:file");
        $addAcl = $pdo->prepare("INSERT INTO $aclTable (operator_id,file,access)
                                VALUES (:id,:file,:access)");
        foreach ($input['acls'] as $file => $access) {
            $args = array(':id' => $input['id'], ':file' => $file, ':access' => $access);
            if (isset($existing[$file])) {
                $changeAcl->execute($args);
            } else {
                $addAcl->execute($args);
            }
        }
        $result = $pdo->prepare("SELECT id,username,auth_source,external_id,firstname,lastname,title,
                     department,company,phone1,phone2,email1,email2,messenger1,messenger2,
                     notes,lastlogin,creationdate,creationby,updatedate,updateby,
                     totp_enabled,totp_confirmed_at FROM $table WHERE id=:id");
        $result->execute(array(':id' => $input['id']));
        $updated = $result->fetch(PDO::FETCH_ASSOC);
        if (!$updated) {
            throw new RuntimeException('Operator disappeared during edit');
        }
        if (!$pdo->commit()) {
            throw new RuntimeException('Operator edit did not commit');
        }
        return $updated;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}
