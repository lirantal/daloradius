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

check('hash returns a versioned string',
      is_string($hash1) && substr($hash1, 0, strlen(dalo_portal_password_prefix())) === dalo_portal_password_prefix());
check('random salts produce different hashes', is_string($hash2) && $hash1 !== $hash2);
check('versioned hash is recognized', dalo_portal_password_is_hash($hash1));
check('plaintext is not recognized as a hash', !dalo_portal_password_is_hash($password));

$verified = dalo_portal_password_verify($password, $hash1);
check('hashed password verifies', $verified['verified'] === true);
check('hashed password is not legacy', $verified['legacy'] === false);
check('current hash does not need rehashing', $verified['needs_rehash'] === false);
check('wrong hashed password is rejected',
      dalo_portal_password_verify('wrong', $hash1)['verified'] === false);
check('stored marked value cannot be replayed as the password',
      dalo_portal_password_verify($hash1, $hash1)['verified'] === false);
check('dummy verification never authenticates',
      dalo_portal_password_dummy_verify('any-password') === false);
$dummy_info = password_get_info(dalo_portal_password_dummy_hash());
$expected_dummy_cost = defined('PASSWORD_BCRYPT_DEFAULT_COST') ? PASSWORD_BCRYPT_DEFAULT_COST : 10;
check('dummy hash follows the runtime bcrypt default cost',
      isset($dummy_info['options']['cost'])
      && intval($dummy_info['options']['cost']) === min(12, max(10, $expected_dummy_cost)));
check('credential guards are bytewise on MySQL and portable elsewhere',
      dalo_portal_password_match_condition('mysqli') === 'BINARY portalloginpassword=BINARY ?'
      && dalo_portal_password_match_condition('pgsql') === 'portalloginpassword=?');

$old_parameters_hash = dalo_portal_password_prefix()
                     . password_hash($password, PASSWORD_BCRYPT, array('cost' => 4));
check('old hash parameters are detected',
      dalo_portal_password_verify($password, $old_parameters_hash)['needs_rehash'] === true);

$legacy = dalo_portal_password_verify($password, $password);
check('legacy plaintext verifies exactly', $legacy['verified'] === true);
check('legacy plaintext is marked for migration',
      $legacy['legacy'] === true && $legacy['needs_rehash'] === true);
check('wrong legacy plaintext is rejected',
      dalo_portal_password_verify('wrong', $password)['verified'] === false);

$hash_shaped_legacy = password_hash('unrelated-value', PASSWORD_BCRYPT);
$hash_shaped_result = dalo_portal_password_verify($hash_shaped_legacy, $hash_shaped_legacy);
check('unmarked hash-shaped plaintext remains a legacy credential',
      $hash_shaped_result['verified'] === true
      && $hash_shaped_result['legacy'] === true
      && !dalo_portal_password_is_hash($hash_shaped_legacy));

$zero_hash = dalo_portal_password_hash('0');
check('password zero is accepted and verifies',
      is_string($zero_hash) && dalo_portal_password_verify('0', $zero_hash)['verified'] === true);
check('NUL password is rejected without an exception', dalo_portal_password_hash("a\0b") === false);
check('leading and trailing NUL passwords are rejected before trimming',
      dalo_portal_password_hash("\0ab") === false
      && dalo_portal_password_hash("ab\0") === false
      && !dalo_portal_password_is_acceptable("\0ab")
      && !dalo_portal_password_is_acceptable("ab\0"));
check('NUL password does not authenticate against a versioned hash',
      dalo_portal_password_verify("a\0b", $hash1)['verified'] === false);
check('NUL password does not authenticate as a legacy credential',
      dalo_portal_password_verify("ab\0", "ab\0")['verified'] === false);
check('empty values cannot authenticate',
      dalo_portal_password_verify('', '')['verified'] === false);
check('empty passwords are not hashed', dalo_portal_password_hash('') === false);

check('portal access without a credential is rejected',
      dalo_portal_access_is_valid(array('enableUserPortalLogin' => '1')) === false);
check('an existing credential allows portal access to be retained',
      dalo_portal_access_is_valid(array('changeUserInfo' => '1'), true) === true);
check('password zero satisfies portal access validation',
      dalo_portal_access_is_valid(array(
          'enableUserPortalLogin' => '1',
          'portalLoginPassword' => '0',
      )) === true);
check('a NUL password fails before portal writes',
      dalo_portal_access_is_valid(array(
          'enableUserPortalLogin' => '1',
          'portalLoginPassword' => "a\0b",
      )) === false);
check('edge NUL passwords fail before portal writes',
      dalo_portal_access_is_valid(array(
          'enableUserPortalLogin' => '1',
          'portalLoginPassword' => "\0ab",
      )) === false
      && dalo_portal_access_is_valid(array(
          'enableUserPortalLogin' => '1',
          'portalLoginPassword' => "ab\0",
      )) === false);

if (!defined('PEAR_ERROR_RETURN')) {
    define('PEAR_ERROR_RETURN', 1);
}
if (!defined('PEAR_ERROR_CALLBACK')) {
    define('PEAR_ERROR_CALLBACK', 16);
}

class PortalPasswordFakeDb {
    public $mode = PEAR_ERROR_CALLBACK;
    public $option = null;
    private $error_stack = array();

    public function pushErrorHandling($mode, $option = null) {
        $this->error_stack[] = array($this->mode, $this->option);
        $this->mode = $mode;
        $this->option = $option;
    }

    public function popErrorHandling() {
        list($this->mode, $this->option) = array_pop($this->error_stack);
    }

    public function failWithDebugInfo($secret) {
        if ($this->mode === PEAR_ERROR_CALLBACK) {
            print "debug SQL contains $secret";
        }
        return false;
    }
}

$fake_db = new PortalPasswordFakeDb();
$restored_handler = function() {};
$fake_db->option = $restored_handler;
$secret = 'portal-db-error-secret';
ob_start();
$result = dalo_portal_db_sensitive_call(
    $fake_db,
    function() use ($fake_db, $secret) {
        return $fake_db->failWithDebugInfo($secret);
    }
);
$output = ob_get_clean();
check('sensitive DB errors cannot emit interpolated credentials',
      $result === false && strpos($output, $secret) === false);
check('sensitive DB calls restore the application error callback',
      $fake_db->mode === PEAR_ERROR_CALLBACK && $fake_db->option === $restored_handler);

$exception_restored = false;
try {
    dalo_portal_db_sensitive_call($fake_db, function() {
        throw new RuntimeException('expected test exception');
    });
} catch (RuntimeException $exception) {
    $exception_restored = $fake_db->mode === PEAR_ERROR_CALLBACK
                       && $fake_db->option === $restored_handler;
}
check('sensitive DB calls restore error handling after exceptions', $exception_restored);

printf("\n%s\n", $failures === 0 ? 'ALL PASSED' : sprintf('%d FAILURE(S)', $failures));
exit($failures === 0 ? 0 : 1);
