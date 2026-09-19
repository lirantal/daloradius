<?php
/* Provider-aware login-flow tests. These extract only the pure helpers from
 * dologin.php, so no web request or database is needed. */
$failures = 0;
function check_login($label, $condition) {
    global $failures;
    if ($condition) { echo "ok   - $label\n"; } else { echo "FAIL - $label\n"; $failures++; }
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

$source = file_get_contents(dirname(__DIR__) . '/app/operators/dologin.php');
$helpers = array(
    'dalo_operator_config_boolean', 'dalo_operator_auth_enabled',
    'dalo_operator_auth_default_source', 'dalo_operator_auth_select_source',
    'dalo_operator_ldap_provider_config', 'dalo_operator_auth_set_pending',
    'dalo_operator_auth_set_authenticated',
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
);
$providerConfig = dalo_operator_ldap_provider_config($ldapConfig);
check_login('LDAP config maps the configured URI', $providerConfig['CONFIG_OPERATOR_LDAP_URI'] === array('ldap://directory'));
check_login('LDAP environment bind password overrides file config', $providerConfig['CONFIG_OPERATOR_LDAP_BIND_PASSWORD'] === 'env-test-secret');
putenv('DALORADIUS_LDAP_BIND_PASSWORD');

$session = array('operator_pass' => 'must-not-survive');
dalo_operator_auth_set_pending($session, 7, 'alice', 'ldap');
check_login('pending session carries provider', $session['operator_2fa_auth_source'] === 'ldap');
check_login('pending session carries operator identity', $session['operator_2fa_id'] === 7 && $session['operator_2fa_user'] === 'alice');
check_login('pending session does not carry password', !array_key_exists('operator_pass', $session));

dalo_operator_auth_set_authenticated($session, 7, 'alice', 'ldap');
check_login('final session carries provider', $session['operator_auth_source'] === 'ldap');
check_login('final session preserves ACL identity', $session['operator_id'] === 7 && $session['operator_user'] === 'alice');
check_login('final session is authenticated', $session['daloradius_logged_in'] === true);

$loginPage = file_get_contents(dirname(__DIR__) . '/app/operators/login.php');
$otpPage = file_get_contents(dirname(__DIR__) . '/app/operators/login-otp.php');
$flow = file_get_contents(dirname(__DIR__) . '/app/operators/dologin.php');
check_login('login page exposes explicit selection only for both providers', strpos($loginPage, 'name="operator_auth_source"') !== false && strpos($loginPage, '$showAuthSource') !== false);
check_login('MFA finalizes operator auth source', strpos($otpPage, "\$_SESSION['operator_auth_source']") !== false);
check_login('primary authentication gates MFA', strpos($flow, 'if ($authenticated)') !== false && strpos($flow, "dalo_operator_auth_set_pending") !== false);
check_login('LDAP flow never updates password', strpos($flow, "'UPDATE %s SET `password`=?") !== false && strpos($flow, '$authSource === \'local\'') !== false);

printf("\n%s\n", $failures === 0 ? 'ALL PASSED' : "$failures FAILURE(S)");
exit($failures === 0 ? 0 : 1);
