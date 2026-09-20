<?php
/**
 * Real LDAP integration checks for LdapAuthProvider against the Samba AD fixture.
 *
 * Run inside the disposable fixture client, where php-ldap is installed:
 *   php tests/operator-ldap-provider-live.test.php
 */

require dirname(__DIR__) . '/app/operators/library/operator_auth.php';

$failures = 0;

function check_live($label, $condition)
{
    global $failures;
    if ($condition) {
        printf("ok   - %s\n", $label);
    } else {
        printf("FAIL - %s\n", $label);
        $failures++;
    }
}

function required_live_env($name)
{
    $value = getenv($name);
    if ($value === false || $value === '') {
        check_live("required fixture variable is present: $name", false);
        return '';
    }
    return $value;
}

$baseDn = required_live_env('BASE_DN');
$serviceBind = required_live_env('SERVICE_BIND');
$servicePassword = required_live_env('SERVICE_PASS');
$userPassword = required_live_env('USER_PASS');
$caFile = required_live_env('LDAP_CA_FILE');
$wrongCaFile = required_live_env('LDAP_WRONG_CA_FILE');
$ldapHost = required_live_env('LDAP_HOST');

function live_config($baseDn, $serviceBind, $servicePassword, $caFile, $uris, $security, $verify, $groups = array(), $matchingRule = '', $userFilter = null, $caOverride = null)
{
    return array(
        'CONFIG_OPERATOR_LDAP_URIS' => (array) $uris,
        'CONFIG_OPERATOR_LDAP_SECURITY' => $security,
        'CONFIG_OPERATOR_LDAP_TLS_VERIFY' => $verify,
        'CONFIG_OPERATOR_LDAP_CA_FILE' => $caOverride === null ? $caFile : $caOverride,
        'CONFIG_OPERATOR_LDAP_BASE_DN' => $baseDn,
        'CONFIG_OPERATOR_LDAP_BIND_DN' => $serviceBind,
        'CONFIG_OPERATOR_LDAP_BIND_PASSWORD' => $servicePassword,
        'CONFIG_OPERATOR_LDAP_USER_FILTER' => $userFilter === null
            ? '(&(objectClass=user)(sAMAccountName={username}))'
            : $userFilter,
        'CONFIG_OPERATOR_LDAP_EXTERNAL_ID_ATTRIBUTE' => 'objectGUID',
        'CONFIG_OPERATOR_LDAP_ALLOWED_GROUPS' => $groups,
        'CONFIG_OPERATOR_LDAP_GROUP_ATTRIBUTE' => 'memberOf',
        'CONFIG_OPERATOR_LDAP_GROUP_MATCHING_RULE' => $matchingRule,
        'CONFIG_OPERATOR_LDAP_NETWORK_TIMEOUT' => 2,
    );
}

function live_auth($config, $username, $password)
{
    return (new LdapAuthProvider($config))->authenticate($username, $password);
}

function run_wrong_ca_isolated()
{
    $environment = array('LDAP_WRONG_CA_ONLY' => '1');
    foreach (array('BASE_DN', 'SERVICE_BIND', 'SERVICE_PASS', 'USER_PASS', 'LDAP_CA_FILE', 'LDAP_WRONG_CA_FILE', 'LDAP_HOST') as $name) {
        $environment[$name] = (string) getenv($name);
    }
    $descriptors = array(
        0 => array('file', '/dev/null', 'r'),
        1 => array('file', '/dev/null', 'w'),
        2 => array('file', '/dev/null', 'w'),
    );
    $process = proc_open(array(PHP_BINARY, __FILE__), $descriptors, $pipes, dirname(__FILE__), $environment);
    if (!is_resource($process)) {
        return false;
    }
    return proc_close($process) === 0;
}

if (getenv('LDAP_WRONG_CA_ONLY') === '1') {
    $isolatedWrongCa = live_auth(
        live_config($baseDn, $serviceBind, $servicePassword, $caFile, "ldaps://$ldapHost", 'ldaps', true, array(), '', null, $wrongCaFile),
        'smoke-user',
        $userPassword
    );
    check_live('isolated LDAPS with the supplied untrusted CA fails verification', !$isolatedWrongCa->isAuthenticated());
    exit($failures === 0 ? 0 : 1);
}

$plainConfig = live_config($baseDn, $serviceBind, $servicePassword, $caFile, "ldap://$ldapHost", 'plain', true);
$plain = live_auth($plainConfig, 'smoke-user', $userPassword);
$plainIdentity = $plain->getIdentity();
check_live('plain LDAP authentication succeeds', $plain->isAuthenticated());
check_live('plain LDAP returns the expected provider identity',
    $plain->isAuthenticated()
    && $plainIdentity['source'] === 'ldap'
    && $plainIdentity['username'] === 'smoke-user'
    && is_string($plainIdentity['dn'])
    && $plainIdentity['dn'] !== '');
check_live('plain LDAP canonicalizes objectGUID',
    $plain->isAuthenticated()
    && isset($plainIdentity['external_id'])
    && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $plainIdentity['external_id']) === 1);

$wrongPassword = live_auth($plainConfig, 'smoke-user', 'definitely-wrong-password');
check_live('wrong password is rejected',
    !$wrongPassword->isAuthenticated() && $wrongPassword->getReason() === 'invalid_credentials');

$missingUser = live_auth($plainConfig, 'does-not-exist', $userPassword);
check_live('missing user is rejected',
    !$missingUser->isAuthenticated() && $missingUser->getReason() === 'invalid_credentials');

$escaped = live_auth($plainConfig, 'smoke*)(|(objectClass=*))', $userPassword);
check_live('LDAP injection characters are escaped',
    !$escaped->isAuthenticated() && $escaped->getReason() === 'invalid_credentials');

$duplicateConfig = live_config(
    $baseDn,
    $serviceBind,
    $servicePassword,
    $caFile,
    "ldap://$ldapHost",
    'plain',
    true,
    array(),
    '',
    '(|(sAMAccountName={username})(objectClass=user))'
);
$duplicate = live_auth($duplicateConfig, 'duplicate', $userPassword);
check_live('multiple LDAP results are rejected',
    !$duplicate->isAuthenticated() && $duplicate->getReason() === 'ldap_ambiguous');

$smokeGroup = "CN=Smoke Group,CN=Users,$baseDn";
$parentGroup = "CN=Parent Group,CN=Users,$baseDn";
$directGroup = live_config(
    $baseDn,
    $serviceBind,
    $servicePassword,
    $caFile,
    "ldap://$ldapHost",
    'plain',
    true,
    array($smokeGroup)
);
$direct = live_auth($directGroup, 'smoke-user', $userPassword);
check_live('direct allowed group authenticates', $direct->isAuthenticated());

$deniedGroup = live_config(
    $baseDn,
    $serviceBind,
    $servicePassword,
    $caFile,
    "ldap://$ldapHost",
    'plain',
    true,
    array("CN=Denied Group,CN=Users,$baseDn")
);
$denied = live_auth($deniedGroup, 'smoke-user', $userPassword);
check_live('denied group is rejected',
    !$denied->isAuthenticated() && $denied->getReason() === 'group_not_allowed');

$nestedGroup = live_config(
    $baseDn,
    $serviceBind,
    $servicePassword,
    $caFile,
    "ldap://$ldapHost",
    'plain',
    true,
    array($parentGroup),
    '1.2.840.113556.1.4.1941'
);
$nested = live_auth($nestedGroup, 'smoke-user', $userPassword);
check_live('AD nested group matching rule authenticates', $nested->isAuthenticated());

$startTlsVerified = live_auth(
    live_config($baseDn, $serviceBind, $servicePassword, $caFile, "ldap://$ldapHost", 'starttls', true),
    'smoke-user',
    $userPassword
);
check_live('StartTLS verify=true with CA authenticates', $startTlsVerified->isAuthenticated());

$startTlsUnverified = live_auth(
    live_config($baseDn, $serviceBind, $servicePassword, $caFile, "ldap://$ldapHost", 'starttls', false),
    'smoke-user',
    $userPassword
);
check_live('StartTLS verify=false authenticates', $startTlsUnverified->isAuthenticated());

$ldapsVerified = live_auth(
    live_config($baseDn, $serviceBind, $servicePassword, $caFile, "ldaps://$ldapHost", 'ldaps', true),
    'smoke-user',
    $userPassword
);
check_live('LDAPS verify=true with CA authenticates', $ldapsVerified->isAuthenticated());

$ldapsBadCa = live_auth(
    live_config($baseDn, $serviceBind, $servicePassword, $caFile, preg_replace('/^ldap:/', 'ldaps:', required_live_env('LDAP_URI')), 'ldaps', true),
    'smoke-user',
    $userPassword
);
check_live('LDAPS certificate hostname mismatch fails verification', !$ldapsBadCa->isAuthenticated());

check_live('LDAPS with the supplied untrusted CA fails verification', run_wrong_ca_isolated());

$ldapsUnverified = live_auth(
    live_config($baseDn, $serviceBind, $servicePassword, $caFile, "ldaps://$ldapHost", 'ldaps', false),
    'smoke-user',
    $userPassword
);
check_live('LDAPS verify=false authenticates', $ldapsUnverified->isAuthenticated());

$failover = live_auth(
    live_config($baseDn, $serviceBind, $servicePassword, $caFile, array('ldap://127.0.0.1:1', "ldap://$ldapHost"), 'plain', true),
    'smoke-user',
    $userPassword
);
check_live('technical first URI failure fails over to the second URI', $failover->isAuthenticated());

printf("\n%s\n", $failures === 0 ? 'ALL PASSED' : sprintf('%d FAILURE(S)', $failures));
exit($failures === 0 ? 0 : 1);
