<?php
/* Provider-aware login-flow tests. These extract only the pure helpers from
 * dologin.php, so no web request or database is needed. */
$failures = 0;
function check_login($label, $condition) {
    global $failures;
    if ($condition) { echo "ok   - $label\n"; } else { echo "FAIL - $label\n"; $failures++; }
}

if (!defined('DB_FETCHMODE_ASSOC')) {
    define('DB_FETCHMODE_ASSOC', 2);
}
class OperatorIdentityTestError {}
if (!class_exists('DB')) {
    class DB {
        public static function isError($value) {
            return $value instanceof OperatorIdentityTestError;
        }
    }
}
class OperatorIdentityTestResult {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function numRows() { return count($this->rows); }
    public function fetchRow($mode) { return array_shift($this->rows); }
    public function free() {}
}
class OperatorIdentityTestSocket {
    public $state;
    private $affectedRows = 0;
    public function __construct(&$state) { $this->state =& $state; }
    public function prepare($sql) { return $sql; }
    public function execute($sql, $params) {
        if (strpos($sql, 'UPDATE') !== false) {
            if (!empty($this->state->update_error)) {
                return new OperatorIdentityTestError();
            }
            if ($this->state->external_id === null) {
                $this->state->external_id = $params[0];
                $this->affectedRows = 1;
            } else {
                $this->affectedRows = 0;
            }
            return new OperatorIdentityTestResult(array());
        }
        if (!empty($this->state->select_error)) {
            return new OperatorIdentityTestError();
        }
        $rows = $this->state->external_id === null
            ? array()
            : array(array('external_id' => $this->state->external_id));
        return new OperatorIdentityTestResult($rows);
    }
    public function affectedRows() { return $this->affectedRows; }
    public function freePrepared($stmt) {}
}

function extract_login_function($source, $name) {
    $tokens = token_get_all($source);
    $code = '';
    $collecting = false;
    $braceDepth = 0;
    $seenName = false;
    foreach ($tokens as $token) {
        $text = is_array($token) ? $token[1] : $token;
        if (!$collecting) {
            if (is_array($token) && $token[0] === T_FUNCTION) {
                $collecting = true;
                $code = $text;
                $seenName = false;
            }
            continue;
        }
        $code .= $text;
        if (is_array($token) && $token[0] === T_STRING && !$seenName) {
            if ($token[1] === $name) { $seenName = true; }
            else { $collecting = false; $code = ''; }
        }
        if ($collecting && $text === '{') { $braceDepth++; }
        if ($collecting && $text === '}') {
            $braceDepth--;
            if ($braceDepth === 0) { return $code; }
        }
    }
    return '';
}

$sessionSource = file_get_contents(dirname(__DIR__) . '/app/operators/library/sessions.php');
$identityHelper = extract_login_function($sessionSource, 'dalo_operator_auth_external_id_matches');
check_login('session identity helper exists', $identityHelper !== '');
if ($identityHelper !== '') { eval($identityHelper); }

$source = file_get_contents(dirname(__DIR__) . '/app/operators/dologin.php');
$helpers = array(
    'dalo_operator_config_boolean', 'dalo_operator_auth_enabled',
    'dalo_operator_auth_default_source', 'dalo_operator_auth_select_source',
    'dalo_operator_ldap_provider_config', 'dalo_operator_ldap_link_external_id',
    'dalo_operator_auth_set_pending', 'dalo_operator_auth_set_authenticated',
);
foreach ($helpers as $helper) {
    $code = extract_login_function($source, $helper);
    check_login("login helper exists: $helper", $code !== '');
    if ($code !== '') { eval($code); }
}

$localOnly = array(
    'CONFIG_OPERATOR_AUTH_LOCAL_ENABLED' => true,
    'CONFIG_OPERATOR_AUTH_LDAP_ENABLED' => false,
    'CONFIG_OPERATOR_AUTH_DEFAULT' => 'local',
);
check_login('local-only missing provider remains local-compatible', dalo_operator_auth_select_source($localOnly, array()) === 'local');
check_login('local-only explicit LDAP is rejected', dalo_operator_auth_select_source($localOnly, array('operator_auth_source' => 'ldap')) === null);

$both = array(
    'CONFIG_OPERATOR_AUTH_LOCAL_ENABLED' => true,
    'CONFIG_OPERATOR_AUTH_LDAP_ENABLED' => true,
    'CONFIG_OPERATOR_AUTH_DEFAULT' => 'ldap',
);
check_login('configured default is LDAP when both are enabled', dalo_operator_auth_default_source($both) === 'ldap');
check_login('both enabled requires explicit source POST', dalo_operator_auth_select_source($both, array()) === null);
check_login('explicit LDAP stays LDAP with no local fallback', dalo_operator_auth_select_source($both, array('operator_auth_source' => 'ldap')) === 'ldap');
check_login('explicit local stays local', dalo_operator_auth_select_source($both, array('operator_auth_source' => 'local')) === 'local');
check_login('unknown provider is rejected', dalo_operator_auth_select_source($both, array('operator_auth_source' => 'radius')) === null);

putenv('DALORADIUS_LDAP_BIND_PASSWORD=env-test-secret');
$ldapConfig = array(
    'CONFIG_OPERATOR_AUTH_LDAP_URI' => array('ldap://directory'),
    'CONFIG_OPERATOR_AUTH_LDAP_FILTER' => '(&(uid={username}))',
    'CONFIG_OPERATOR_AUTH_LDAP_BIND_PASSWORD' => 'file-secret',
    'CONFIG_OPERATOR_AUTH_LDAP_GROUP_ATTRIBUTE' => 'memberOf',
    'CONFIG_OPERATOR_AUTH_LDAP_GROUP_MATCHING_RULE' => '1.2.840.113556.1.4.1941',
);
$providerConfig = dalo_operator_ldap_provider_config($ldapConfig);
check_login('LDAP config maps the configured URI', $providerConfig['CONFIG_OPERATOR_LDAP_URI'] === array('ldap://directory'));
check_login('LDAP environment bind password overrides file config', $providerConfig['CONFIG_OPERATOR_LDAP_BIND_PASSWORD'] === 'env-test-secret');
check_login('LDAP group configuration reaches the provider',
    $providerConfig['CONFIG_OPERATOR_LDAP_GROUP_ATTRIBUTE'] === 'memberOf'
    && $providerConfig['CONFIG_OPERATOR_LDAP_GROUP_MATCHING_RULE'] === '1.2.840.113556.1.4.1941');
putenv('DALORADIUS_LDAP_BIND_PASSWORD=');
$providerConfig = dalo_operator_ldap_provider_config($ldapConfig);
check_login('empty LDAP bind environment value preserves file config',
    $providerConfig['CONFIG_OPERATOR_LDAP_BIND_PASSWORD'] === 'file-secret');
putenv('DALORADIUS_LDAP_BIND_PASSWORD');

$session = array('operator_pass' => 'must-not-survive');
dalo_operator_auth_set_pending($session, 7, 'alice', 'ldap', 'directory-id-42');
check_login('pending session carries provider', $session['operator_2fa_auth_source'] === 'ldap');
check_login('pending session carries operator identity', $session['operator_2fa_id'] === 7 && $session['operator_2fa_user'] === 'alice');
check_login('LDAP pending session carries external identity', $session['operator_2fa_external_id'] === 'directory-id-42');
check_login('pending session does not carry password', !array_key_exists('operator_pass', $session));

$sharedIdentity = (object) array('external_id' => null);
$firstLink = dalo_operator_ldap_link_external_id(new OperatorIdentityTestSocket($sharedIdentity), 'operators', 7, 'directory-id-42');
$secondLink = dalo_operator_ldap_link_external_id(new OperatorIdentityTestSocket($sharedIdentity), 'operators', 7, 'directory-id-42');
check_login('same-identity concurrent link is accepted', $firstLink && $secondLink);
$differentIdentity = (object) array('external_id' => null);
$winnerLink = dalo_operator_ldap_link_external_id(new OperatorIdentityTestSocket($differentIdentity), 'operators', 7, 'directory-id-winner');
$loserLink = dalo_operator_ldap_link_external_id(new OperatorIdentityTestSocket($differentIdentity), 'operators', 7, 'directory-id-other');
check_login('different-identity concurrent link is rejected', $winnerLink && !$loserLink);
$errorState = (object) array('external_id' => null, 'update_error' => true);
check_login('identity link database update errors fail closed', !dalo_operator_ldap_link_external_id(new OperatorIdentityTestSocket($errorState), 'operators', 7, 'directory-id-42'));

check_login('unchanged LDAP identity permits MFA continuity', dalo_operator_auth_external_id_matches('directory-id-42', $session['operator_2fa_external_id']));
check_login('changed LDAP identity rejects MFA continuity', !dalo_operator_auth_external_id_matches('directory-id-changed', $session['operator_2fa_external_id']));

dalo_operator_auth_set_authenticated($session, 7, 'alice', 'ldap');
check_login('final session carries provider', $session['operator_auth_source'] === 'ldap');
check_login('final session preserves ACL identity', $session['operator_id'] === 7 && $session['operator_user'] === 'alice');
check_login('final session is authenticated', $session['daloradius_logged_in'] === true);

require_once dirname(__DIR__) . '/app/operators/library/totp.php';
$totpSecret = dalo_totp_generate_secret();
$totpCode = dalo_totp_new()->getCode($totpSecret);
$totpCounter = dalo_totp_verify_once($totpSecret, $totpCode, null);
check_login('LDAP pending session accepts a valid common TOTP factor',
    $session['operator_auth_source'] === 'ldap' && $totpCounter !== null);
check_login('common TOTP rejects an incorrect factor',
    dalo_totp_verify_once($totpSecret, '000000', null) === null);
$recoveryCodes = dalo_totp_generate_recovery_codes(2);
$recoveryHashes = dalo_totp_hash_recovery_codes($recoveryCodes);
list($recoveryOk, $remainingRecoveryHashes) = dalo_totp_verify_recovery_code($recoveryHashes, $recoveryCodes[0]);
list($reusedRecoveryOk) = dalo_totp_verify_recovery_code($remainingRecoveryHashes, $recoveryCodes[0]);
check_login('common recovery code is accepted once for provider sessions',
    $recoveryOk && !$reusedRecoveryOk);
$localMfaSession = array();
dalo_operator_auth_set_pending($localMfaSession, 8, 'local-admin', 'local');
check_login('local provider enters the same MFA pending flow',
    $localMfaSession['operator_2fa_auth_source'] === 'local');
check_login('local MFA compatibility keeps external identity absent',
    !array_key_exists('operator_2fa_external_id', $localMfaSession));

$loginPage = file_get_contents(dirname(__DIR__) . '/app/operators/login.php');
$otpPage = file_get_contents(dirname(__DIR__) . '/app/operators/login-otp.php');
$flow = file_get_contents(dirname(__DIR__) . '/app/operators/dologin.php');
check_login('login page exposes explicit selection only for both providers', strpos($loginPage, 'name="operator_auth_source"') !== false && strpos($loginPage, '$showAuthSource') !== false);
check_login('MFA finalizes operator auth source', strpos($otpPage, "\$_SESSION['operator_auth_source']") !== false);
check_login('MFA queries and verifies external identity continuity',
    strpos($otpPage, 'external_id') !== false
    && strpos($otpPage, 'dalo_operator_auth_external_id_matches') !== false);
check_login('MFA locks the identity row before provider verification and state update',
    strpos($otpPage, 'autoCommit(false)') !== false
    && strpos($otpPage, 'FOR UPDATE') !== false
    && strpos($otpPage, 'dalo_operator_auth_external_id_matches') !== false);
$otpUpdate = strpos($otpPage, '$updateResult = $dbSocket->query($sql);');
$otpIdentityCheck = strpos($otpPage, '$externalIdMatches =');
$otpCommit = strpos($otpPage, '$commit = $dbSocket->commit();');
$otpSession = strpos($otpPage, "session_regenerate_id(true);");
check_login('MFA checks provider identity before updating one-time state',
    $otpIdentityCheck !== false && $otpUpdate !== false && $otpIdentityCheck < $otpUpdate);
check_login('MFA checks state-update results and commits before creating a session',
    $otpUpdate !== false
    && strpos($otpPage, '$affectedRows =', $otpUpdate) !== false
    && $otpCommit !== false
    && $otpSession !== false
    && $otpCommit < $otpSession);
check_login('MFA rolls back failed or invalid verification attempts',
    strpos($otpPage, '$rollback = $dbSocket->rollback();') !== false);
check_login('primary authentication gates MFA', strpos($flow, 'if ($authenticated)') !== false && strpos($flow, "dalo_operator_auth_set_pending") !== false);
check_login('pre-migration operator rows default to local auth', strpos($flow, "? \$row['auth_source'] : 'local'") !== false);
check_login('pre-provider pending MFA sessions default to local auth', strpos($otpPage, "operator_2fa_auth_source'] = 'local'") !== false);
check_login('LDAP flow never updates password', strpos($flow, "'UPDATE %s SET `password`=?") !== false && strpos($flow, '$authSource === \'local\'') !== false);

printf("\n%s\n", $failures === 0 ? 'ALL PASSED' : "$failures FAILURE(S)");
exit($failures === 0 ? 0 : 1);
