<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
 *********************************************************************************************************
 * Performs provider-aware operator authentication and starts the common MFA flow.
 */

include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);
include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'sessions.php' ]);
include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'totp.php' ]);
include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'operator_auth.php' ]);

function dalo_operator_config_boolean(array $config, $key, $default = false)
{
    if (!array_key_exists($key, $config)) {
        return (bool) $default;
    }
    $value = $config[$key];
    if (is_string($value)) {
        return !in_array(strtolower(trim($value)), array('', '0', 'false', 'no', 'off'), true);
    }
    return (bool) $value;
}

function dalo_operator_auth_enabled(array $config, $source)
{
    if ($source === 'local') {
        return dalo_operator_config_boolean($config, 'CONFIG_OPERATOR_AUTH_LOCAL_ENABLED', true);
    }
    if ($source === 'ldap') {
        return dalo_operator_config_boolean($config, 'CONFIG_OPERATOR_AUTH_LDAP_ENABLED', false);
    }
    return false;
}

function dalo_operator_auth_row_source(array $row)
{
    return array_key_exists('auth_source', $row) && is_string($row['auth_source'])
        ? $row['auth_source'] : 'local';
}

/* A missing source is backward-compatible only when one provider is enabled.
 * With both providers enabled the browser must submit its explicit choice. */
function dalo_operator_auth_select_source(array $config, array $post)
{
    $local = dalo_operator_auth_enabled($config, 'local');
    $ldap = dalo_operator_auth_enabled($config, 'ldap');
    $count = ($local ? 1 : 0) + ($ldap ? 1 : 0);
    if ($count !== 1 && $count !== 2) {
        return null;
    }

    $hasSource = array_key_exists('operator_auth_source', $post)
        && is_string($post['operator_auth_source'])
        && trim($post['operator_auth_source']) !== '';
    if (!$hasSource) {
        return $count === 1 ? ($local ? 'local' : 'ldap') : null;
    }

    $source = strtolower(trim($post['operator_auth_source']));
    if (!in_array($source, array('local', 'ldap'), true) || !dalo_operator_auth_enabled($config, $source)) {
        return null;
    }
    return $source;
}

function dalo_operator_ldap_provider_config(array $config)
{
    $map = array(
        'CONFIG_OPERATOR_AUTH_LDAP_URI' => 'CONFIG_OPERATOR_LDAP_URI',
        'CONFIG_OPERATOR_AUTH_LDAP_SECURITY' => 'CONFIG_OPERATOR_LDAP_SECURITY',
        'CONFIG_OPERATOR_AUTH_LDAP_TLS_VERIFY' => 'CONFIG_OPERATOR_LDAP_TLS_VERIFY',
        'CONFIG_OPERATOR_AUTH_LDAP_TLS_CA_FILE' => 'CONFIG_OPERATOR_LDAP_CA_FILE',
        'CONFIG_OPERATOR_AUTH_LDAP_BASE_DN' => 'CONFIG_OPERATOR_LDAP_BASE_DN',
        'CONFIG_OPERATOR_AUTH_LDAP_USER_BASE_DN' => 'CONFIG_OPERATOR_LDAP_USER_BASE_DN',
        'CONFIG_OPERATOR_AUTH_LDAP_BIND_DN' => 'CONFIG_OPERATOR_LDAP_BIND_DN',
        'CONFIG_OPERATOR_AUTH_LDAP_FILTER' => 'CONFIG_OPERATOR_LDAP_USER_FILTER',
        'CONFIG_OPERATOR_AUTH_LDAP_EXTERNAL_ID_ATTRIBUTE' => 'CONFIG_OPERATOR_LDAP_EXTERNAL_ID_ATTRIBUTE',
        'CONFIG_OPERATOR_AUTH_LDAP_TIMEOUT' => 'CONFIG_OPERATOR_LDAP_NETWORK_TIMEOUT',
        'CONFIG_OPERATOR_AUTH_LDAP_ALLOWED_GROUPS' => 'CONFIG_OPERATOR_LDAP_ALLOWED_GROUPS',
        'CONFIG_OPERATOR_AUTH_LDAP_GROUP_ATTRIBUTE' => 'CONFIG_OPERATOR_LDAP_GROUP_ATTRIBUTE',
        'CONFIG_OPERATOR_AUTH_LDAP_GROUP_MATCHING_RULE' => 'CONFIG_OPERATOR_LDAP_GROUP_MATCHING_RULE',
    );
    $providerConfig = array();
    foreach ($map as $configKey => $providerKey) {
        if (array_key_exists($configKey, $config)) {
            $providerConfig[$providerKey] = $config[$configKey];
        }
    }

    $bindPassword = getenv('DALORADIUS_LDAP_BIND_PASSWORD');
    if ($bindPassword !== false && $bindPassword !== '') {
        $providerConfig['CONFIG_OPERATOR_LDAP_BIND_PASSWORD'] = $bindPassword;
    } elseif (array_key_exists('CONFIG_OPERATOR_AUTH_LDAP_BIND_PASSWORD', $config)) {
        $providerConfig['CONFIG_OPERATOR_LDAP_BIND_PASSWORD'] = $config['CONFIG_OPERATOR_AUTH_LDAP_BIND_PASSWORD'];
    }
    return $providerConfig;
}

function dalo_operator_auth_safe_reason($reason)
{
    $reason = preg_replace('/[^A-Za-z0-9_.-]/', '_', (string) $reason);
    return substr($reason === '' ? 'unknown' : $reason, 0, 80);
}

/* Link an LDAP identity without allowing a concurrent different identity to
 * take over the operator row. A zero-row conditional update is resolved by a
 * fresh read, so a concurrent link of the same identity is accepted while a
 * different identity fails closed. */
function dalo_operator_auth_table(array $config)
{
    $name = $config['CONFIG_DB_TBL_DALOOPERATORS'] ?? null;
    if (!is_string($name) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $name)) {
        throw new InvalidArgumentException('Invalid operator table');
    }
    return '`' . $name . '`';
}

function dalo_operator_auth_innodb(PDO $pdo, $table)
{
    $check = $pdo->prepare('SELECT ENGINE FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
    $check->execute(array(trim($table, '`')));
    if (strcasecmp((string) $check->fetchColumn(), 'InnoDB') !== 0) {
        throw new RuntimeException('Operator authentication requires a transactional table');
    }
}

function dalo_operator_ldap_link_external_id(PDO $pdo, $operatorsTable, $operatorId, $externalId)
{
    // A successful concurrent link of the same identity is harmless, while a
    // different winner must fail closed. The caller validates the table name.
    $update = $pdo->prepare("UPDATE $operatorsTable SET external_id=? WHERE id=? AND auth_source='ldap' AND external_id IS NULL");
    if (!$update->execute(array($externalId, (int) $operatorId))) {
        return false;
    }
    if ($update->rowCount() === 1) {
        return true;
    }
    $select = $pdo->prepare("SELECT external_id FROM $operatorsTable WHERE id=? AND auth_source='ldap'");
    if (!$select->execute(array((int) $operatorId))) {
        return false;
    }
    $linked = $select->fetchAll(PDO::FETCH_ASSOC);
    return count($linked) === 1
        && dalo_operator_auth_external_id_matches($linked[0]['external_id'], $externalId);
}

/** A successful password check must not overwrite a concurrent reset or source change. */
function dalo_operator_auth_rehash(PDO $pdo, $table, array $row, $username, $newHash)
{
    $sourceClause = array_key_exists('auth_source', $row) ? " AND auth_source='local'" : '';
    $update = $pdo->prepare("UPDATE $table SET `password`=? WHERE id=? AND username=? AND `password`=?$sourceClause");
    if (!$update->execute(array($newHash, (int) $row['id'], $username, $row['password']))) {
        return false;
    }
    return $update->rowCount() === 1;
}

/** Recheck the authenticated identity under lock before opening a session. */
function dalo_operator_auth_finalize(PDO $pdo, $table, array $row, $source, $externalId)
{
    if (!$pdo->beginTransaction()) {
        return false;
    }
    try {
        $select = $pdo->prepare("SELECT * FROM $table WHERE id=? AND username=? FOR UPDATE");
        if (!$select->execute(array((int) $row['id'], $row['username']))) {
            throw new RuntimeException('Operator identity lookup failed');
        }
        $locked = $select->fetchAll(PDO::FETCH_ASSOC);
        if (count($locked) !== 1 || !hash_equals($source, dalo_operator_auth_row_source($locked[0]))) {
            $pdo->rollBack();
            return false;
        }
        $current = $locked[0];
        if ($source === 'local') {
            $valid = isset($current['password'], $row['password'])
                && hash_equals((string) $row['password'], (string) $current['password']);
        } else {
            $valid = dalo_operator_auth_external_id_matches($current['external_id'] ?? null, $externalId);
        }
        if (!$valid) {
            $pdo->rollBack();
            return false;
        }
        $mfa = !empty($current['totp_enabled']) && !empty($current['totp_secret']);
        if (!$mfa) {
            $update = $pdo->prepare("UPDATE $table SET lastlogin=? WHERE id=? AND username=?");
            if (!$update->execute(array(date('Y-m-d H:i:s'), (int) $row['id'], $row['username']))) {
                throw new RuntimeException('Operator login timestamp update failed');
            }
            // A repeated login in the same second can affect zero changed rows.
            // The locked identity above proves existence; execute must succeed.
        }
        if (!$pdo->commit()) {
            throw new RuntimeException('Operator authentication commit failed');
        }
        return array('mfa' => $mfa);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

function dalo_operator_auth_set_pending(array &$session, $operatorId, $operatorUser, $source, $externalId = null)
{
    $session['operator_2fa_pending'] = true;
    $session['operator_2fa_id'] = (int) $operatorId;
    $session['operator_2fa_user'] = (string) $operatorUser;
    $session['operator_2fa_auth_source'] = (string) $source;
    $session['operator_2fa_attempts'] = 0;
    if ($source === 'ldap') {
        $session['operator_2fa_external_id'] = (string) $externalId;
    } else {
        unset($session['operator_2fa_external_id']);
    }
    unset($session['operator_pass'], $session['operator_password'], $session['operator_2fa_password']);
}

function dalo_operator_auth_set_authenticated(array &$session, $operatorId, $operatorUser, $source)
{
    $session['daloradius_logged_in'] = true;
    $session['operator_user'] = (string) $operatorUser;
    $session['operator_id'] = (int) $operatorId;
    $session['operator_auth_source'] = (string) $source;
    unset($session['operator_2fa_external_id']);
    unset($session['operator_pass'], $session['operator_password'], $session['operator_2fa_password']);
}

/* Tests can load these pure helpers without executing the request handler. */
if (defined('DALORADIUS_OPERATOR_LOGIN_TEST_ONLY')) {
    return;
}

require_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'pdo_connection.php' ]);
dalo_session_start();

unset(
    $_SESSION['operator_2fa_pending'],
    $_SESSION['operator_2fa_id'],
    $_SESSION['operator_2fa_user'],
    $_SESSION['operator_2fa_auth_source'],
    $_SESSION['operator_2fa_external_id'],
    $_SESSION['operator_2fa_attempts'],
    $_SESSION['operator_auth_source'],
    $_SESSION['location_name']
);
$_SESSION['daloradius_logged_in'] = false;

$locationName = isset($_POST['location']) && is_string($_POST['location']) ? $_POST['location'] : 'default';
if (!isset($configValues['CONFIG_LOCATIONS']) || !is_array($configValues['CONFIG_LOCATIONS'])
    || !$configValues['CONFIG_LOCATIONS'] || !array_key_exists($locationName, $configValues['CONFIG_LOCATIONS'])) {
    $locationName = 'default';
}

$authenticationFailure = 'invalid_credentials';
$authenticated = false;
$needsMfa = false;
$authSource = null;
$operator = null;
$pdo = null;
$externalId = null;
$row = null;

if (isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])
    && dalo_check_csrf_token($_POST['csrf_token'])
    && isset($_POST['operator_user'], $_POST['operator_pass'])
    && is_string($_POST['operator_user']) && is_string($_POST['operator_pass'])) {
    $authSource = dalo_operator_auth_select_source($configValues, $_POST);
    if ($authSource !== null) {
        $operator = $_POST['operator_user'];
        $operatorPass = $_POST['operator_pass'];
        try {
            $table = dalo_operator_auth_table($configValues);
            $pdo = dalo_pdo_connect($configValues, $locationName);
            dalo_operator_auth_innodb($pdo, $table);
            /* Fetch at most two rows: duplicate usernames are never an identity. */
            $select = $pdo->prepare("SELECT * FROM $table WHERE username=? LIMIT 2");
            $select->execute(array($operator));
            $matches = $select->fetchAll(PDO::FETCH_ASSOC);
            if (count($matches) === 1) {
                $row = $matches[0];
                $rowSource = dalo_operator_auth_row_source($row);
                if (hash_equals($authSource, $rowSource)) {
                    $rehashAttempted = false;
                    $rehashSucceeded = false;
                    $rehashCallback = function ($newHash, $username) use (
                        $pdo, $table, &$row, &$rehashAttempted, &$rehashSucceeded
                    ) {
                        $rehashAttempted = true;
                        $rehashSucceeded = dalo_operator_auth_rehash($pdo, $table, $row, $username, $newHash);
                        if ($rehashSucceeded) {
                            $row['password'] = $newHash;
                        }
                        return $rehashSucceeded;
                    };
                    $provider = $authSource === 'local'
                        ? new LocalAuthProvider($row, $rehashCallback)
                        : new LdapAuthProvider(dalo_operator_ldap_provider_config($configValues));
                    $result = (new OperatorAuthenticationManager($provider))->authenticate($operator, $operatorPass);
                    if ($result->isAuthenticated()) {
                        if ($rehashAttempted && !$rehashSucceeded) {
                            // LocalAuthProvider treats the callback as best effort;
                            // a concurrent password reset must fail closed here.
                            $authenticationFailure = 'operator_password_changed';
                        } elseif ($authSource === 'ldap') {
                            $identity = $result->getIdentity();
                            $externalId = isset($identity['external_id']) && is_scalar($identity['external_id'])
                                ? (string) $identity['external_id'] : '';
                            if ($externalId === '') {
                                $authenticationFailure = 'ldap_external_id_missing';
                            } elseif (!array_key_exists('external_id', $row)) {
                                $authenticationFailure = 'operator_identity_missing';
                            } elseif ($row['external_id'] !== null
                                && !dalo_operator_auth_external_id_matches((string) $row['external_id'], $externalId)) {
                                $authenticationFailure = 'external_id_mismatch';
                            } elseif ($row['external_id'] === null
                                && !dalo_operator_ldap_link_external_id($pdo, $table, $row['id'], $externalId)) {
                                $authenticationFailure = 'operator_identity_update_failed';
                            }
                        }
                        if ($authenticationFailure === 'invalid_credentials') {
                            $finalized = dalo_operator_auth_finalize($pdo, $table, $row, $authSource, $externalId);
                            if ($finalized !== false) {
                                $authenticated = true;
                                $needsMfa = $finalized['mfa'];
                            } else {
                                $authenticationFailure = 'operator_identity_changed';
                            }
                        }
                    } else {
                        $authenticationFailure = $result->getReason();
                    }
                } else {
                    $authenticationFailure = 'auth_source_mismatch';
                }
            }
        } catch (Throwable $exception) {
            $authenticationFailure = 'provider_exception_' . get_class($exception);
            error_log('Operator authentication failure: source=' . dalo_operator_auth_safe_reason($authSource)
                . ' reason=' . dalo_operator_auth_safe_reason($authenticationFailure));
        }
        unset($operatorPass);
    } else {
        $authenticationFailure = 'provider_not_enabled';
    }
}

if ($authenticated) {
    session_regenerate_id(true);
    $_SESSION['location_name'] = $locationName;
    $operatorId = (int) $row['id'];
    if ($needsMfa) {
        dalo_operator_auth_set_pending($_SESSION, $operatorId, $operator, $authSource,
            $authSource === 'ldap' ? $externalId : null);
        $pdo = null;
        header('Location: login-otp.php');
        exit;
    }
    dalo_operator_auth_set_authenticated($_SESSION, $operatorId, $operator, $authSource);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
}

if ($_SESSION['daloradius_logged_in'] !== true) {
    $_SESSION['operator_login_error'] = true;
    error_log('Operator authentication failure: source=' . dalo_operator_auth_safe_reason($authSource ?: 'none')
        . ' reason=' . dalo_operator_auth_safe_reason($authenticationFailure));
}
$pdo = null;
header('Location: ' . ($_SESSION['daloradius_logged_in'] === true ? 'index.php' : 'login.php'));
?>
