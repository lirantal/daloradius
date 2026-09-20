<?php
require dirname(__DIR__) . '/app/operators/include/management/operator_identity.php';

$failures = 0;
function check_identity($label, $condition) {
    global $failures;
    if ($condition) { echo "ok   - $label\n"; } else { echo "FAIL - $label\n"; $failures++; }
}

check_identity('missing auth_source defaults to local', operator_auth_source_from_post(array()) === 'local');
check_identity('posted LDAP source is normalized', operator_auth_source_from_post(array('auth_source' => ' LDAP ')) === 'ldap');
check_identity('malformed posted source is rejected', operator_auth_source_from_post(array('auth_source' => 'radius')) === null);
check_identity('array-shaped posted source is rejected', operator_auth_source_from_post(array('auth_source' => array('ldap'))) === null);
check_identity('empty external ID becomes NULL', operator_normalize_external_id('  ') === null);
check_identity('255-character external ID is accepted', operator_validate_external_id(str_repeat('x', 255))['ok'] === true);
check_identity('255-character LDAP creation is accepted',
    operator_prepare_create_identity('ldap', '', str_repeat('x', 255))['ok'] === true);
check_identity('256-character external ID is rejected', operator_validate_external_id(str_repeat('x', 256))['ok'] === false);
check_identity('identity snapshot matches unchanged source and external ID',
    operator_identity_state_matches('ldap', 'directory-id-42', 'ldap', 'directory-id-42'));
check_identity('identity snapshot rejects a concurrent external ID conversion',
    !operator_identity_state_matches('ldap', 'directory-id-new', 'ldap', 'directory-id-42'));

$local = operator_prepare_create_identity('local', 'local-secret', 'directory-id');
check_identity('local creation succeeds with a password', $local['ok'] === true);
check_identity('local password is hashed', $local['password_hash'] !== null && password_verify('local-secret', $local['password_hash']));
check_identity('local external ID is always NULL', $local['external_id'] === null);

$localEmpty = operator_prepare_create_identity('local', '  ', 'ignored');
check_identity('local creation requires a password', !$localEmpty['ok'] && $localEmpty['error'] === 'local operators require a password');

$ldap = operator_prepare_create_identity(' LDAP ', '', '  directory-id-42  ');
check_identity('LDAP creation succeeds without a password', $ldap['ok'] === true);
check_identity('LDAP source is normalized', $ldap['auth_source'] === 'ldap');
check_identity('LDAP password is NULL', $ldap['password_hash'] === null);
check_identity('LDAP external ID is retained and trimmed', $ldap['external_id'] === 'directory-id-42');
$ldapTooLong = operator_prepare_create_identity('ldap', '', str_repeat('x', 256));
check_identity('LDAP creation rejects a 256-character external ID',
    !$ldapTooLong['ok'] && $ldapTooLong['error'] === 'external ID must not exceed 255 characters');

$ldapWithPassword = operator_prepare_create_identity('ldap', 'forged-local-password', 'id');
check_identity('LDAP ignores a posted password', $ldapWithPassword['ok'] === true && $ldapWithPassword['password_hash'] === null);

$invalid = operator_prepare_create_identity(operator_auth_source_from_post(array('auth_source' => 'radius')), 'secret', 'id');
check_identity('malformed source cannot create an identity', !$invalid['ok'] && $invalid['error'] === 'invalid authentication source');

$ldapEdit = operator_prepare_update_identity('ldap', 'ldap', 'forged-password', false, 'directory-id-42');
check_identity('LDAP edit clears any local password', $ldapEdit['ok'] === true && $ldapEdit['password_mode'] === 'clear');
$ldapEditBoundary = operator_prepare_update_identity('ldap', 'ldap', '', false, str_repeat('x', 255));
check_identity('255-character LDAP edit is accepted', $ldapEditBoundary['ok'] === true);
$ldapEditTooLong = operator_prepare_update_identity('ldap', 'ldap', '', false, str_repeat('x', 256));
check_identity('LDAP edit rejects a 256-character external ID',
    !$ldapEditTooLong['ok'] && $ldapEditTooLong['error'] === 'external ID must not exceed 255 characters');
$localEdit = operator_prepare_update_identity('local', 'local', '', false);
check_identity('local edit preserves password when blank', $localEdit['ok'] === true && $localEdit['password_mode'] === 'preserve');
$unconfirmed = operator_prepare_update_identity('local', 'ldap', '', false);
check_identity('source conversion requires confirmation', !$unconfirmed['ok']);
$toLdap = operator_prepare_update_identity('local', 'ldap', 'ignored', true);
check_identity('confirmed local-to-LDAP conversion clears password', $toLdap['ok'] === true && $toLdap['password_mode'] === 'clear');
$toLocalEmpty = operator_prepare_update_identity('ldap', 'local', '', true);
check_identity('LDAP-to-local conversion requires a password', !$toLocalEmpty['ok']);
$toLocal = operator_prepare_update_identity('ldap', 'local', 'new-local-secret', true);
check_identity('confirmed LDAP-to-local conversion hashes password', $toLocal['ok'] === true && $toLocal['password_mode'] === 'replace' && password_verify('new-local-secret', $toLocal['password_hash']));

printf("\n%s", $failures === 0 ? 'ALL PASSED' : "$failures FAILURE(S)");
exit($failures === 0 ? 0 : 1);
