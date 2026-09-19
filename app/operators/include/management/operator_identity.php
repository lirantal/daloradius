<?php
/**
 * Validation and persistence decisions for operator identities.
 *
 * Keep these decisions independent from the page and database layers so the
 * password/source rules can be tested without bootstrapping the application.
 */

function operator_auth_source_options()
{
    return array(
        'local' => 'Local',
        'ldap' => 'LDAP',
    );
}

function operator_normalize_auth_source($source)
{
    if (!is_string($source)) {
        return null;
    }

    $source = strtolower(trim($source));
    return array_key_exists($source, operator_auth_source_options()) ? $source : null;
}

function operator_auth_source_from_post(array $post)
{
    return array_key_exists('auth_source', $post)
        ? operator_normalize_auth_source($post['auth_source'])
        : 'local';
}

function operator_auth_source_label($source)
{
    $source = operator_normalize_auth_source($source);
    return $source === null ? 'Unknown' : operator_auth_source_options()[$source];
}

function operator_normalize_external_id($externalId)
{
    if (!is_string($externalId)) {
        return null;
    }

    $externalId = trim($externalId);
    return $externalId === '' ? null : $externalId;
}

function operator_identity_error($message)
{
    return array(
        'ok' => false,
        'error' => $message,
    );
}

function operator_prepare_create_identity($authSource, $password, $externalId)
{
    $authSource = operator_normalize_auth_source($authSource);
    if ($authSource === null) {
        return operator_identity_error('invalid authentication source');
    }

    $passwordHash = null;
    if ($authSource === 'local') {
        if (!is_string($password) || trim($password) === '') {
            return operator_identity_error('local operators require a password');
        }
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            return operator_identity_error('unable to hash operator password');
        }
    }

    return array(
        'ok' => true,
        'auth_source' => $authSource,
        'external_id' => $authSource === 'ldap' ? operator_normalize_external_id($externalId) : null,
        'password_hash' => $passwordHash,
    );
}

function operator_prepare_update_identity($currentSource, $requestedSource, $password, $confirmed)
{
    $currentSource = operator_normalize_auth_source($currentSource);
    $requestedSource = operator_normalize_auth_source($requestedSource);

    if ($currentSource === null || $requestedSource === null) {
        return operator_identity_error('invalid authentication source');
    }

    $sourceChanged = $currentSource !== $requestedSource;
    if ($sourceChanged && $confirmed !== true) {
        return operator_identity_error('authentication source conversion requires confirmation');
    }

    if ($requestedSource === 'ldap') {
        // LDAP accounts never retain a local password, including on a
        // same-source edit where a forged password field was submitted.
        return array(
            'ok' => true,
            'auth_source' => 'ldap',
            'password_mode' => 'clear',
        );
    }

    if (!is_string($password) || trim($password) === '') {
        if ($sourceChanged) {
            return operator_identity_error('LDAP to Local conversion requires a new password');
        }

        return array(
            'ok' => true,
            'auth_source' => 'local',
            'password_mode' => 'preserve',
        );
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    if ($passwordHash === false) {
        return operator_identity_error('unable to hash operator password');
    }

    return array(
        'ok' => true,
        'auth_source' => 'local',
        'password_mode' => 'replace',
        'password_hash' => $passwordHash,
    );
}
