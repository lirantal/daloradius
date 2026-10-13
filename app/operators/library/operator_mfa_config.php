<?php
/** R01b: operator-owned MFA changes, committed before publishing recovery codes. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/operator_mfa_config.php') !== false) {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/operator_create.php';
require_once __DIR__ . '/operator_acl_read.php';
require_once __DIR__ . '/totp.php';

function dalo_operator_mfa_row(PDO $pdo, $config, $id, $operator, $lock = false) {
    $id = dalo_operator_acl_id($id);
    if (!is_string($operator) || $operator === '') {
        throw new InvalidArgumentException('Invalid operator identity');
    }
    $table = dalo_operator_create_table($config, 'CONFIG_DB_TBL_DALOOPERATORS');
    $stmt = $pdo->prepare("SELECT id,username,totp_enabled,totp_confirmed_at FROM $table
                           WHERE id=:id AND username=:operator" . ($lock ? ' FOR UPDATE' : ''));
    $stmt->execute(array(':id' => $id, ':operator' => $operator));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || $row['username'] !== $operator || $stmt->fetch(PDO::FETCH_ASSOC)) {
        throw new DomainException('Operator changed; reload and retry');
    }
    return $row;
}

function dalo_operator_mfa_capacity(PDO $pdo, $table, $values) {
    $stmt = $pdo->prepare('SELECT COLUMN_NAME,CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:table');
    $stmt->execute(array(':table' => trim($table, '`')));
    $lengths = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($values as $column => $value) {
        if (!isset($lengths[$column]) || strlen($value) > (int) $lengths[$column]) {
            throw new RuntimeException('Operator MFA storage is insufficient');
        }
    }
}

function dalo_operator_mfa_apply(PDO $pdo, $config, $id, $operator, $action, $secret = '', $code = '') {
    global $logDebugSQL;
    if (!in_array($action, array('confirm_enable', 'disable', 'regenerate_recovery'), true) || $pdo->inTransaction()) {
        throw new InvalidArgumentException('Invalid MFA operation');
    }
    if ($action === 'confirm_enable' && (!is_string($secret) || $secret === '' ||
        !is_string($code) || !dalo_totp_verify($secret, $code))) {
        throw new DomainException('Invalid verification code');
    }
    $table = dalo_operator_create_table($config, 'CONFIG_DB_TBL_DALOOPERATORS');
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not begin MFA change');
    }
    try {
        // Lock before engine preflight so concurrent table DDL cannot invalidate rollback.
        $current = dalo_operator_mfa_row($pdo, $config, $id, $operator, true);
        dalo_operator_create_innodb($pdo, array($table));
        if ($action === 'confirm_enable' && (int) $current['totp_enabled'] === 1) {
            throw new DomainException('Two-factor authentication is already enabled');
        }
        if ($action === 'regenerate_recovery' && (int) $current['totp_enabled'] !== 1) {
            throw new DomainException('Two-factor authentication is not enabled');
        }
        $codes = array();
        $args = array(':id' => (int) $current['id']);
        if ($action === 'disable') {
            $sql = "UPDATE $table SET totp_enabled=0,totp_secret=NULL,totp_last_counter=NULL,
                         totp_confirmed_at=NULL,totp_recovery_codes=NULL WHERE id=:id";
        } else {
            $codes = dalo_totp_generate_recovery_codes();
            $hashes = dalo_totp_hash_recovery_codes($codes);
            $values = array('totp_recovery_codes' => $hashes);
            $args[':recovery'] = $hashes;
            if ($action === 'confirm_enable') {
                $values['totp_secret'] = $secret;
                $args[':secret'] = $secret;
                $args[':confirmed'] = date('Y-m-d H:i:s');
                $sql = "UPDATE $table SET totp_enabled=1,totp_secret=:secret,totp_last_counter=NULL,
                             totp_confirmed_at=:confirmed,totp_recovery_codes=:recovery WHERE id=:id";
            } else {
                $sql = "UPDATE $table SET totp_recovery_codes=:recovery WHERE id=:id AND totp_enabled=1";
            }
            dalo_operator_mfa_capacity($pdo, $table, $values);
        }
        $stmt = $pdo->prepare($sql);
        if (!$stmt->execute($args)) {
            throw new RuntimeException('MFA change failed');
        }
        // Only the template can reach debug/on-page/file logging, never factor values/hashes.
        $logDebugSQL .= $sql . ";\n";
        $row = dalo_operator_mfa_row($pdo, $config, $id, $operator);
        if (!$pdo->commit()) {
            throw new RuntimeException('MFA change did not commit');
        }
        return array('row' => $row, 'codes' => $codes);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}
