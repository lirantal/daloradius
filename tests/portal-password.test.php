<?php
/*
 * Standalone tests for user portal password helpers.
 *
 * Run with: php tests/portal-password.test.php
 */

$root = dirname(__DIR__);
$_SERVER['PHP_SELF'] = '/cli/tests';
require $root . '/app/common/includes/portal_password.php';

$failures = 0;
function check($label, $condition) {
    global $failures;
    if ($condition) {
        printf("ok   - %s\n", $label);
    } else {
        printf("FAIL - %s\n", $label);
        $failures++;
    }
}

$password = 'portal-secret-753';
$hash1 = dalo_portal_password_hash($password);
$hash2 = dalo_portal_password_hash($password);

check('hash returns a string', is_string($hash1) && $hash1 !== '');
check('random salts produce different hashes', is_string($hash2) && $hash1 !== $hash2);
check('hash is recognized', dalo_portal_password_is_hash($hash1));
check('plaintext is not recognized as a hash', !dalo_portal_password_is_hash($password));

$verified = dalo_portal_password_verify($password, $hash1);
check('hashed password verifies', $verified['verified'] === true);
check('hashed password is not legacy', $verified['legacy'] === false);
check('current hash does not need rehashing', $verified['needs_rehash'] === false);
check('wrong hashed password is rejected',
      dalo_portal_password_verify('wrong', $hash1)['verified'] === false);

$old_parameters_hash = password_hash($password, PASSWORD_BCRYPT, array('cost' => 4));
check('old hash parameters are detected',
      dalo_portal_password_verify($password, $old_parameters_hash)['needs_rehash'] === true);

$legacy = dalo_portal_password_verify($password, $password);
check('legacy plaintext verifies exactly', $legacy['verified'] === true);
check('legacy plaintext is marked for migration',
      $legacy['legacy'] === true && $legacy['needs_rehash'] === true);
check('wrong legacy plaintext is rejected',
      dalo_portal_password_verify('wrong', $password)['verified'] === false);
check('empty values cannot authenticate',
      dalo_portal_password_verify('', '')['verified'] === false);
check('empty passwords are not hashed', dalo_portal_password_hash('') === false);

printf("\n%s\n", $failures === 0 ? 'ALL PASSED' : sprintf('%d FAILURE(S)', $failures));
exit($failures === 0 ? 0 : 1);
