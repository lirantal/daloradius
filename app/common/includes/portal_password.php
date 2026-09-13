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

function dalo_portal_password_dummy_hash($cost = null) {
    $hashes = array(
        4 => '$2y$04$wKn.PJGHzScowkrGI3yvtu8.iWCrxxZInv.jD2PhBRrATeNBSE71.',
        5 => '$2y$05$8.O93ZMdao3pLJfCBfYBbu5x2uXeFO4W0237vlL71BckAbI.1MF.a',
        6 => '$2y$06$.2qte4QakrXDsdc7H67rau7ewqLBeAH8Of6Go.9WEqS1GWUmQuAaW',
        7 => '$2y$07$flULOx3vUxRieVI0CjwhtuwblBeEOAeAs.5GUL3eZa28Ns5c.2rna',
        8 => '$2y$08$5pgqjiVL23GhqA./urdlKOjVZfpji1K4F1Lt7Cv3OuUNoklUG3H1a',
        9 => '$2y$09$4C1fF8MMbq92U2QRfFBuqe2swF5ZhoGuAnYkwS96xdWNCwZ6q0Q4q',
        10 => '$2y$10$4aT2V3qZVCaRopSEc6N1hOis.JbEgXFH1jqDKdyDT97HiDTL38yHa',
        11 => '$2y$11$dsGDA4GqJrTT2Gz07yiLGurF1sX/6rXx9Q2h0tYHmKscaxz1CMMFS',
        12 => '$2y$12$.WwOwD73/JLZtMK9h8iYc.Y.6D11CyECU.se7juSCCM3kicqOiPcy',
    );
    if ($cost === null) {
        $cost = defined('PASSWORD_BCRYPT_DEFAULT_COST') ? PASSWORD_BCRYPT_DEFAULT_COST : 10;
    }

    return array_key_exists($cost, $hashes) ? $hashes[$cost] : $hashes[12];
}

function dalo_portal_password_dummy_verify($password) {
    if (!is_string($password) || strpos($password, "\0") !== false) {
        return false;
    }

    try {
        password_verify($password, dalo_portal_password_dummy_hash());
    } catch (Throwable $exception) {
        // Keep all authentication failures generic.
    }

    return false;
}

function dalo_portal_password_pad_bcrypt_timing($password, $hash_info) {
    if (!is_array($hash_info) || !isset($hash_info['algoName']) || $hash_info['algoName'] !== 'bcrypt' ||
        !isset($hash_info['options']['cost'])) {
        return;
    }

    $current_cost = defined('PASSWORD_BCRYPT_DEFAULT_COST') ? PASSWORD_BCRYPT_DEFAULT_COST : 10;
    $stored_cost = intval($hash_info['options']['cost']);
    // bcrypt work doubles for every cost increment. The sum of one verify at
    // each lower cost pads an older bcrypt hash to current default cost.
    for ($cost = max(4, $stored_cost); $cost < $current_cost; $cost++) {
        if ($cost <= 11) {
            password_verify($password, dalo_portal_password_dummy_hash($cost));
        }
    }
}

function dalo_portal_password_match_condition($db_engine) {
    return in_array($db_engine, array('mysql', 'mysqli'), true)
         ? 'BINARY portalloginpassword=BINARY ?'
         : 'portalloginpassword=?';
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
            $hash_info = password_get_info($hash);
            $result['verified'] = password_verify($password, $hash);
            dalo_portal_password_pad_bcrypt_timing($password, $hash_info);
        } catch (Throwable $exception) {
            return $result;
        }
        $result['needs_rehash'] = $result['verified']
                                && password_needs_rehash($hash, PASSWORD_DEFAULT);
        return $result;
    }

    // Legacy plaintext verification is cheap. Perform one fixed-cost password
    // verification as well so legacy and missing-user failures are not cheaper
    // than failures against versioned hashes.
    dalo_portal_password_dummy_verify($password);
    $result['verified'] = hash_equals($stored_password, $password);
    $result['legacy'] = $result['verified'];
    $result['needs_rehash'] = $result['verified'];

    return $result;
}

function dalo_portal_db_sensitive_call($dbSocket, $callback) {
    $dbSocket->pushErrorHandling(PEAR_ERROR_RETURN);

    try {
        return $callback();
    } finally {
        $dbSocket->popErrorHandling();
    }
}
