<?php
/*
 * Shared helpers for user portal passwords.
 *
 * Portal passwords are independent from FreeRADIUS password attributes.
 */

if (strpos($_SERVER['PHP_SELF'] ?? '', '/common/includes/portal_password.php') !== false) {
    http_response_code(404);
    exit;
}

function dalo_portal_password_prefix() {
    return '$dalo$portal$v1$';
}

function dalo_portal_password_is_present($password) {
    return is_string($password) && trim($password) !== '';
}

function dalo_portal_password_is_acceptable($password) {
    return dalo_portal_password_is_present($password) && strpos($password, "\0") === false;
}

function dalo_portal_access_requested($values) {
    foreach (array('enableUserPortalLogin', 'changeUserInfo', 'bi_changeuserbillinfo') as $field) {
        if (isset($values[$field]) && $values[$field] === '1') {
            return true;
        }
    }

    return false;
}

function dalo_portal_access_is_valid($values, $has_existing_password = false) {
    $password = isset($values['portalLoginPassword']) ? $values['portalLoginPassword'] : '';
    if (dalo_portal_password_is_present($password) && !dalo_portal_password_is_acceptable($password)) {
        return false;
    }

    return !dalo_portal_access_requested($values)
        || $has_existing_password
        || dalo_portal_password_is_acceptable($password);
}

function dalo_portal_password_is_hash($stored_password) {
    $prefix = dalo_portal_password_prefix();
    if (!is_string($stored_password) || substr($stored_password, 0, strlen($prefix)) !== $prefix) {
        return false;
    }

    $hash = substr($stored_password, strlen($prefix));
    $info = password_get_info($hash);
    return isset($info['algoName']) && $info['algoName'] !== 'unknown';
}

function dalo_portal_password_hash($password) {
    if (!is_string($password) || $password === '' || strpos($password, "\0") !== false) {
        return false;
    }

    try {
        $hash = password_hash($password, PASSWORD_DEFAULT);
    } catch (Throwable $exception) {
        return false;
    }

    return is_string($hash) ? dalo_portal_password_prefix() . $hash : false;
}

function dalo_portal_password_verify($password, $stored_password) {
    $result = array(
        'verified' => false,
        'legacy' => false,
        'needs_rehash' => false,
    );

    if (!is_string($password) || !is_string($stored_password) || $stored_password === '' ||
        strpos($password, "\0") !== false) {
        return $result;
    }

    if (dalo_portal_password_is_hash($stored_password)) {
        $hash = substr($stored_password, strlen(dalo_portal_password_prefix()));
        try {
            $result['verified'] = password_verify($password, $hash);
        } catch (Throwable $exception) {
            return $result;
        }
        $result['needs_rehash'] = $result['verified']
                                && password_needs_rehash($hash, PASSWORD_DEFAULT);
        return $result;
    }

    $result['verified'] = hash_equals($stored_password, $password);
    $result['legacy'] = $result['verified'];
    $result['needs_rehash'] = $result['verified'];

    return $result;
}

function dalo_portal_db_sensitive_call($dbSocket, $callback, $error_handler = null) {
    $dbSocket->setErrorHandling(PEAR_ERROR_RETURN);

    try {
        return $callback();
    } finally {
        if (is_callable($error_handler)) {
            $dbSocket->setErrorHandling(PEAR_ERROR_CALLBACK, $error_handler);
        }
    }
}
