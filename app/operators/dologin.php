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

function dalo_operator_auth_default_source(array $config)
{
    $default = strtolower(trim((string) ($config['CONFIG_OPERATOR_AUTH_DEFAULT'] ?? 'local')));
    if (in_array($default, array('local', 'ldap'), true) && dalo_operator_auth_enabled($config, $default)) {
        return $default;
    }
    return null;
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
    );
    $providerConfig = array();
    foreach ($map as $configKey => $providerKey) {
        if (array_key_exists($configKey, $config)) {
            $providerConfig[$providerKey] = $config[$configKey];
        }
    }

    $bindPassword = getenv('DALORADIUS_LDAP_BIND_PASSWORD');
    if ($bindPassword !== false) {
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

function dalo_operator_auth_set_pending(array &$session, $operatorId, $operatorUser, $source)
{
    $session['operator_2fa_pending'] = true;
    $session['operator_2fa_id'] = (int) $operatorId;
    $session['operator_2fa_user'] = (string) $operatorUser;
    $session['operator_2fa_auth_source'] = (string) $source;
    $session['operator_2fa_attempts'] = 0;
    unset($session['operator_pass'], $session['operator_password'], $session['operator_2fa_password']);
}

function dalo_operator_auth_set_authenticated(array &$session, $operatorId, $operatorUser, $source)
{
    $session['daloradius_logged_in'] = true;
    $session['operator_user'] = (string) $operatorUser;
    $session['operator_id'] = (int) $operatorId;
    $session['operator_auth_source'] = (string) $source;
    unset($session['operator_pass'], $session['operator_password'], $session['operator_2fa_password']);
}

/* Tests can load these pure helpers without executing the request handler. */
if (defined('DALORADIUS_OPERATOR_LOGIN_TEST_ONLY')) {
    return;
}

dalo_session_start();

unset(
    $_SESSION['operator_2fa_pending'],
    $_SESSION['operator_2fa_id'],
    $_SESSION['operator_2fa_user'],
    $_SESSION['operator_2fa_auth_source'],
    $_SESSION['operator_2fa_attempts'],
    $_SESSION['operator_auth_source']
);

$location_name = isset($_POST['location']) ? $_POST['location'] : 'default';
$_SESSION['location_name'] = (array_key_exists('CONFIG_LOCATIONS', $configValues)
    && is_array($configValues['CONFIG_LOCATIONS'])
    && count($configValues['CONFIG_LOCATIONS']) > 0
    && array_key_exists($location_name, $configValues['CONFIG_LOCATIONS']))
    ? $location_name : 'default';
$_SESSION['daloradius_logged_in'] = false;

$authenticationFailure = 'invalid_credentials';
$authenticated = false;
$authSource = null;
$operator = null;
$operatorPass = null;
$row = null;
$databaseOpen = false;

if (isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])
    && isset($_POST['operator_user']) && isset($_POST['operator_pass'])) {
    $authSource = dalo_operator_auth_select_source($configValues, $_POST);
    if ($authSource !== null) {
        include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);
        $databaseOpen = true;
        $operator = $_POST['operator_user'];
        $operatorPass = $_POST['operator_pass'];

        try {
            /* Deliberately fetch by username once, then require the stored
             * provider to match the explicit provider choice before auth. */
            $sql = sprintf('SELECT * FROM %s WHERE `username`=?',
                           $configValues['CONFIG_DB_TBL_DALOOPERATORS']);
            $stmt = $dbSocket->prepare($sql);
            $res = $dbSocket->execute($stmt, array($operator));
            $dbSocket->freePrepared($stmt);

            if (!DB::isError($res) && $res->numRows() === 1) {
                $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                $res->free();
                $rowSource = array_key_exists('auth_source', $row) ? $row['auth_source'] : null;

                if (is_string($rowSource) && hash_equals($authSource, $rowSource)) {
                    $rehashCallback = function ($newHash, $username) use ($dbSocket, $configValues) {
                        $rehashSql = sprintf('UPDATE %s SET `password`=? WHERE `username`=?',
                                             $configValues['CONFIG_DB_TBL_DALOOPERATORS']);
                        $rehashStmt = $dbSocket->prepare($rehashSql);
                        $rehashResult = $dbSocket->execute($rehashStmt, array($newHash, $username));
                        $dbSocket->freePrepared($rehashStmt);
                        return !DB::isError($rehashResult);
                    };
                    $provider = $authSource === 'local'
                        ? new LocalAuthProvider($row, $rehashCallback)
                        : new LdapAuthProvider(dalo_operator_ldap_provider_config($configValues));
                    $manager = new OperatorAuthenticationManager($provider);
                    $result = $manager->authenticate($operator, $operatorPass);

                    if ($result->isAuthenticated()) {
                        if ($authSource === 'ldap') {
                            $identity = $result->getIdentity();
                            $externalId = isset($identity['external_id']) && is_scalar($identity['external_id'])
                                ? (string) $identity['external_id'] : '';
                            if ($externalId === '') {
                                $authenticationFailure = 'ldap_external_id_missing';
                            } elseif (!array_key_exists('external_id', $row)) {
                                $authenticationFailure = 'operator_identity_missing';
                            } elseif ($row['external_id'] !== null
                                && !hash_equals((string) $row['external_id'], $externalId)) {
                                $authenticationFailure = 'external_id_mismatch';
                            } else {
                                /* Bind identity first; only then fill a null
                                 * external id. LDAP never writes a password. */
                                if ($row['external_id'] === null) {
                                    $externalSql = sprintf(
                                        'UPDATE %s SET external_id=? WHERE id=? AND external_id IS NULL',
                                        $configValues['CONFIG_DB_TBL_DALOOPERATORS']
                                    );
                                    $externalStmt = $dbSocket->prepare($externalSql);
                                    $externalResult = $dbSocket->execute($externalStmt, array($externalId, intval($row['id'])));
                                    $dbSocket->freePrepared($externalStmt);
                                    if (DB::isError($externalResult)) {
                                        $authenticationFailure = 'operator_identity_update_failed';
                                    }
                                }
                            }
                        }
                        if ($authenticationFailure === 'invalid_credentials') {
                            $authenticated = true;
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

    } else {
        $authenticationFailure = 'provider_not_enabled';
    }
}

if ($authenticated) {
    session_regenerate_id(true);
    $operatorId = intval($row['id']);
    $totpEnabled = array_key_exists('totp_enabled', $row) && intval($row['totp_enabled']) === 1;
    $totpSecret = array_key_exists('totp_secret', $row) && !empty($row['totp_secret']) ? $row['totp_secret'] : '';

    if ($totpEnabled && $totpSecret !== '') {
        dalo_operator_auth_set_pending($_SESSION, $operatorId, $operator, $authSource);
        include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
        $databaseOpen = false;
        header('Location: login-otp.php');
        exit;
    }

    dalo_operator_auth_set_authenticated($_SESSION, $operatorId, $operator, $authSource);
    $now = date('Y-m-d H:i:s');
    $sql = sprintf('UPDATE %s SET lastlogin=? WHERE username=?', $configValues['CONFIG_DB_TBL_DALOOPERATORS']);
    $stmt = $dbSocket->prepare($sql);
    $res = $dbSocket->execute($stmt, array($now, $operator));
    $dbSocket->freePrepared($stmt);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
    $databaseOpen = false;
}

if ($_SESSION['daloradius_logged_in'] !== true) {
    $_SESSION['operator_login_error'] = true;
    error_log('Operator authentication failure: source=' . dalo_operator_auth_safe_reason($authSource ?: 'none')
        . ' reason=' . dalo_operator_auth_safe_reason($authenticationFailure));
}

if ($databaseOpen) {
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
}

$header_location = $_SESSION['daloradius_logged_in'] === true ? 'index.php' : 'login.php';
header("Location: $header_location");
?>
