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

function dalo_portal_password_is_hash($stored_password) {
    if (!is_string($stored_password) || $stored_password === '') {
        return false;
    }

    $info = password_get_info($stored_password);
    return isset($info['algoName']) && $info['algoName'] !== 'unknown';
}

function dalo_portal_password_hash($password) {
    if (!is_string($password) || $password === '') {
        return false;
    }

    return password_hash($password, PASSWORD_DEFAULT);
}

function dalo_portal_password_verify($password, $stored_password) {
    $result = array(
        'verified' => false,
        'legacy' => false,
        'needs_rehash' => false,
    );

    if (!is_string($password) || !is_string($stored_password) || $stored_password === '') {
        return $result;
    }

    if (dalo_portal_password_is_hash($stored_password)) {
        $result['verified'] = password_verify($password, $stored_password);
        $result['needs_rehash'] = $result['verified']
                                && password_needs_rehash($stored_password, PASSWORD_DEFAULT);
        return $result;
    }

    $result['verified'] = hash_equals($stored_password, $password);
    $result['legacy'] = $result['verified'];
    $result['needs_rehash'] = $result['verified'];

    return $result;
}
