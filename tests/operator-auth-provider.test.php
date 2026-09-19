<?php
require dirname(__DIR__) . '/app/operators/library/operator_auth.php';
$failures = 0;
function check_auth($label, $condition) {
    global $failures;
    if ($condition) { echo "ok   - $label\n"; } else { echo "FAIL - $label\n"; $failures++; }
}

$hash = password_hash('local-secret', PASSWORD_DEFAULT);
$rehash = array();
$local = new LocalAuthProvider(array('id' => 7, 'username' => 'alice', 'password' => $hash),
    function ($newHash, $username) use (&$rehash) { $rehash = array($newHash, $username); });
check_auth('local provider has a stable name', $local->getName() === 'local');
check_auth('local hashed password authenticates', $local->authenticate('alice', 'local-secret')->isAuthenticated());
check_auth('local wrong password is safe', $local->authenticate('alice', 'wrong')->getReason() === 'invalid_credentials');
check_auth('empty local password is rejected', $local->authenticate('alice', '')->getReason() === 'empty_password');
$wrongSource = new LocalAuthProvider(array('username' => 'alice', 'password' => $hash, 'auth_source' => 'ldap'));
check_auth('local provider rejects non-local row source', $wrongSource->authenticate('alice', 'local-secret')->getReason() === 'auth_source_mismatch');
$legacy = new LocalAuthProvider(array('username' => 'bob', 'password' => 'legacy-secret'),
    function ($newHash, $username) use (&$rehash) { $rehash = array($newHash, $username); });
$legacyResult = $legacy->authenticate('bob', 'legacy-secret');
check_auth('legacy local password authenticates', $legacyResult->isAuthenticated());
check_auth('legacy password requests rehash', $legacyResult->needsRehash() && password_verify('legacy-secret', $rehash[0]));

class FakeOperatorLdapAdapter implements OperatorLdapAdapter {
    public $uris = array(); public $filters = array(); public $bases = array(); public $attributes = array(); public $binds = array(); public $options = array(); public $searchLimits = array(); public $startTls = 0; public $codes = array(); public $searchEntries = array();
    public function connect($uri) { $this->uris[] = $uri; if (strpos($uri, 'down') !== false) { $this->codes['connect'] = 81; return false; } return $uri; }
    public function setOption($connection, $option, $value) { $this->options[] = array($connection, $option, $value); return true; }
    public function startTls($connection) { $this->startTls++; return true; }
    public function bind($connection, $dn, $password) { $this->binds[] = array($connection, $dn, $password); return $dn === 'cn=service' || $password === 'ldap-secret'; }
    public function search($connection, $base, $filter, array $attributes, $sizeLimit = 2, $timeLimit = 2) { $this->bases[] = $base; $this->filters[] = $filter; $this->attributes[] = $attributes; $this->searchLimits[] = array($sizeLimit, $timeLimit); return 'search'; }
    public function entries($connection, $search) { return $this->searchEntries; }
    public function escape($value, $ignore = '', $flags = 0) { return strtr($value, array('\\' => '\\5c', '*' => '\\2a', '(' => '\\28', ')' => '\\29', "\0" => '\\00')); }
    public function errorCode($connection) { return isset($this->codes[$connection]) ? $this->codes[$connection] : 49; }
    public function close($connection) { return true; }
}

$fake = new FakeOperatorLdapAdapter();
$fake->searchEntries = array(array('dn' => 'cn=Alice,dc=example,dc=org', 'uid' => array('Alice'), 'memberOf' => array('cn=operators,dc=example,dc=org')));
$ldap = new LdapAuthProvider(array(
    'CONFIG_OPERATOR_LDAP_URIS' => array('ldap://down.example', 'ldap://directory.example'),
    'CONFIG_OPERATOR_LDAP_SECURITY' => 'starttls',
    'CONFIG_OPERATOR_LDAP_CA_FILE' => '/etc/ldap/operator-ca.pem',
    'CONFIG_OPERATOR_LDAP_BASE_DN' => 'dc=example,dc=org',
    'CONFIG_OPERATOR_LDAP_USER_BASE_DN' => 'ou=operators,dc=example,dc=org',
    'CONFIG_OPERATOR_LDAP_BIND_DN' => 'cn=service',
    'CONFIG_OPERATOR_LDAP_BIND_PASSWORD' => 'service-secret',
    'CONFIG_OPERATOR_LDAP_USER_FILTER' => '(&(objectClass=person)(uid={username}))',
    'CONFIG_OPERATOR_LDAP_ALLOWED_GROUPS' => array('cn=operators,dc=example,dc=org'),
), $fake);
$result = $ldap->authenticate('Alice*)(uid=*)', 'ldap-secret');
check_auth('LDAP technical failover reaches second URI', $result->isAuthenticated() && count($fake->uris) === 2);
check_auth('LDAP provider has a stable name', $ldap->getName() === 'ldap');
check_auth('LDAP exact security key enables startTLS', $fake->startTls === 1);
$hasGlobalCa = false;
$caOption = defined('LDAP_OPT_X_TLS_CACERTFILE') ? LDAP_OPT_X_TLS_CACERTFILE : 24578;
foreach ($fake->options as $option) {
    if ($option[0] === null && $option[1] === $caOption && $option[2] === '/etc/ldap/operator-ca.pem') {
        $hasGlobalCa = true;
    }
}
check_auth('LDAP exact CA key is set before connect', $hasGlobalCa);
check_auth('LDAP user base DN takes precedence', $fake->bases[0] === 'ou=operators,dc=example,dc=org');
check_auth('LDAP username is filter escaped', strpos($fake->filters[0], 'Alice\\2a\\29\\28uid=\\2a\\29') !== false);
check_auth('LDAP service bind precedes user bind', count($fake->binds) === 2 && $fake->binds[1][1] === 'cn=Alice,dc=example,dc=org');
$hasNetworkTimeout = false;
$hasTimeLimit = false;
foreach ($fake->options as $option) {
    if ($option[0] === null && $option[1] === 20485 && $option[2] === 5) {
        $hasNetworkTimeout = true;
    }
    if ($option[0] === null && $option[1] === 4 && $option[2] === 5) {
        $hasTimeLimit = true;
    }
}
check_auth('LDAP network timeout is applied as a bounded integer', $hasNetworkTimeout);
check_auth('LDAP time limit option is applied when available', $hasTimeLimit);
check_auth('LDAP search is bounded to two results and two seconds', $fake->searchLimits[0] === array(2, 2));

function option_has_value(array $options, $option, $value)
{
    foreach ($options as $setting) {
        if ($setting[1] === $option && $setting[2] === $value) {
            return true;
        }
    }
    return false;
}

$minimumTimeoutFake = new FakeOperatorLdapAdapter();
$minimumTimeoutFake->searchEntries = array(array('dn' => 'cn=Minimum', 'uid' => array('minimum')));
$minimumTimeoutProvider = new LdapAuthProvider(array(
    'CONFIG_OPERATOR_LDAP_URI' => 'ldap://directory',
    'CONFIG_OPERATOR_LDAP_NETWORK_TIMEOUT' => 0,
    'CONFIG_OPERATOR_LDAP_BASE_DN' => 'dc=x',
    'CONFIG_OPERATOR_LDAP_BIND_DN' => 'cn=service',
), $minimumTimeoutFake);
check_auth('non-positive LDAP timeout clamps to one second',
    $minimumTimeoutProvider->authenticate('minimum', 'ldap-secret')->isAuthenticated()
    && option_has_value($minimumTimeoutFake->options, 20485, 1)
    && option_has_value($minimumTimeoutFake->options, 4, 1));

$maximumTimeoutFake = new FakeOperatorLdapAdapter();
$maximumTimeoutFake->searchEntries = array(array('dn' => 'cn=Maximum', 'uid' => array('maximum')));
$maximumTimeoutProvider = new LdapAuthProvider(array(
    'CONFIG_OPERATOR_LDAP_URI' => 'ldap://directory',
    'CONFIG_OPERATOR_LDAP_NETWORK_TIMEOUT' => '999999999999999999999',
    'CONFIG_OPERATOR_LDAP_BASE_DN' => 'dc=x',
    'CONFIG_OPERATOR_LDAP_BIND_DN' => 'cn=service',
), $maximumTimeoutFake);
check_auth('oversized LDAP timeout clamps to thirty seconds',
    $maximumTimeoutProvider->authenticate('maximum', 'ldap-secret')->isAuthenticated()
    && option_has_value($maximumTimeoutFake->options, 20485, 30)
    && option_has_value($maximumTimeoutFake->options, 4, 30));

$rejectFake = new FakeOperatorLdapAdapter();
$rejectFake->searchEntries = $fake->searchEntries;
$reject = new LdapAuthProvider(array('CONFIG_OPERATOR_LDAP_URIS' => array('ldap://one', 'ldap://two'), 'CONFIG_OPERATOR_LDAP_BASE_DN' => 'dc=x', 'CONFIG_OPERATOR_LDAP_BIND_DN' => 'cn=service'), $rejectFake);
$rejectResult = $reject->authenticate('Alice', 'wrong');
check_auth('user rejection does not fail over', !$rejectResult->isAuthenticated() && count($rejectFake->uris) === 1);

$nestedFake = new FakeOperatorLdapAdapter();
$nestedFake->searchEntries = array(array('dn' => 'cn=Nested', 'uid' => array('nested')));
$nestedProvider = new LdapAuthProvider(array(
    'CONFIG_OPERATOR_LDAP_URI' => 'ldap://directory',
    'CONFIG_OPERATOR_LDAP_BASE_DN' => 'dc=x',
    'CONFIG_OPERATOR_LDAP_BIND_DN' => 'cn=service',
    'CONFIG_OPERATOR_LDAP_ALLOWED_GROUPS' => array('cn=parent,dc=x'),
    'CONFIG_OPERATOR_LDAP_GROUP_ATTRIBUTE' => 'memberOf',
    'CONFIG_OPERATOR_LDAP_GROUP_MATCHING_RULE' => '1.2.840.113556.1.4.1941',
), $nestedFake);
$nestedResult = $nestedProvider->authenticate('nested', 'ldap-secret');
$nestedAttributes = $nestedFake->attributes ? $nestedFake->attributes[0] : array();
check_auth('server-side nested-group matching authenticates without memberOf data',
    $nestedResult->isAuthenticated() && !in_array('memberOf', $nestedAttributes, true));

class SlowFirstUriAdapter extends FakeOperatorLdapAdapter {
    public function connect($uri) {
        if (strpos($uri, 'slow') !== false) {
            usleep(1100000);
        }
        return parent::connect($uri);
    }
}
$deadlineFake = new SlowFirstUriAdapter();
$deadlineProvider = new LdapAuthProvider(array(
    'CONFIG_OPERATOR_LDAP_URIS' => array('ldap://slow-down', 'ldap://directory'),
    'CONFIG_OPERATOR_LDAP_NETWORK_TIMEOUT' => 1,
    'CONFIG_OPERATOR_LDAP_BASE_DN' => 'dc=x',
    'CONFIG_OPERATOR_LDAP_BIND_DN' => 'cn=service',
), $deadlineFake);
$deadlineResult = $deadlineProvider->authenticate('deadline', 'ldap-secret');
check_auth('LDAP failover observes one request-wide deadline',
    !$deadlineResult->isAuthenticated() && $deadlineResult->isRetryable()
    && count($deadlineFake->uris) === 1);

$empty = $ldap->authenticate('Alice', '');
check_auth('LDAP empty password does not connect', $empty->getReason() === 'empty_password' && count($fake->uris) === 2);

$guidFake = new FakeOperatorLdapAdapter();
$guidFake->searchEntries = array(array('dn' => 'cn=Guid', 'objectGUID' => hex2bin('00112233445566778899aabbccddeeff')));
$guidProvider = new LdapAuthProvider(array('CONFIG_OPERATOR_LDAP_URI' => 'ldaps://directory', 'CONFIG_OPERATOR_LDAP_SECURITY' => 'ldaps', 'CONFIG_OPERATOR_LDAP_BASE_DN' => 'dc=x', 'CONFIG_OPERATOR_LDAP_BIND_DN' => 'cn=service', 'CONFIG_OPERATOR_LDAP_EXTERNAL_ID_ATTRIBUTE' => 'objectGUID'), $guidFake);
$guidResult = $guidProvider->authenticate('guid', 'ldap-secret');
check_auth('binary objectGUID is canonicalized', $guidResult->getIdentity()['external_id'] === '33221100-5544-7766-8899-aabbccddeeff');

$missingIdFake = new FakeOperatorLdapAdapter();
$missingIdFake->searchEntries = array(array('dn' => 'cn=MissingId'));
$missingIdProvider = new LdapAuthProvider(array('CONFIG_OPERATOR_LDAP_URI' => 'ldap://directory', 'CONFIG_OPERATOR_LDAP_SECURITY' => 'plain', 'CONFIG_OPERATOR_LDAP_BASE_DN' => 'dc=x', 'CONFIG_OPERATOR_LDAP_BIND_DN' => 'cn=service'), $missingIdFake);
$missingIdResult = $missingIdProvider->authenticate('missing', 'ldap-secret');
check_auth('LDAP missing external ID fails authentication', !$missingIdResult->isAuthenticated() && $missingIdResult->getReason() === 'ldap_external_id_missing');
$invalidConfig = new LdapAuthProvider(array('CONFIG_OPERATOR_LDAP_URI' => 'ldap://directory', 'CONFIG_OPERATOR_LDAP_EXTERNAL_ID_ATTRIBUTE' => ''), new FakeOperatorLdapAdapter());
check_auth('LDAP empty external ID attribute fails configuration', $invalidConfig->authenticate('missing', 'ldap-secret')->getReason() === 'ldap_configuration_invalid');

class TechnicalUserBindAdapter extends FakeOperatorLdapAdapter {
    public function bind($connection, $dn, $password) {
        $this->binds[] = array($connection, $dn, $password);
        if ($dn === 'cn=service') { return true; }
        if ($connection === 'ldap://one') {
            $this->codes[$connection] = 81;
            return false;
        }
        return $password === 'ldap-secret';
    }
}
$technicalBindFake = new TechnicalUserBindAdapter();
$technicalBindFake->searchEntries = $fake->searchEntries;
$technicalBindProvider = new LdapAuthProvider(array(
    'CONFIG_OPERATOR_LDAP_URIS' => array('ldap://one', 'ldap://two'),
    'CONFIG_OPERATOR_LDAP_SECURITY' => 'plain',
    'CONFIG_OPERATOR_LDAP_BASE_DN' => 'dc=x',
    'CONFIG_OPERATOR_LDAP_BIND_DN' => 'cn=service',
), $technicalBindFake);
check_auth('technical user-bind failure reaches second URI',
    $technicalBindProvider->authenticate('Alice', 'ldap-secret')->isAuthenticated()
    && count($technicalBindFake->uris) === 2);

$mismatchFake = new FakeOperatorLdapAdapter();
$mismatchProvider = new LdapAuthProvider(array(
    'CONFIG_OPERATOR_LDAP_URI' => 'ldap://directory',
    'CONFIG_OPERATOR_LDAP_SECURITY' => 'ldaps',
    'CONFIG_OPERATOR_LDAP_BASE_DN' => 'dc=x',
), $mismatchFake);
check_auth('LDAPS mode rejects a cleartext ldap URI before connecting',
    !$mismatchProvider->authenticate('Alice', 'ldap-secret')->isAuthenticated()
    && count($mismatchFake->uris) === 0);

$stringFalseFake = new FakeOperatorLdapAdapter();
$stringFalseFake->searchEntries = $fake->searchEntries;
$stringFalseProvider = new LdapAuthProvider(array(
    'CONFIG_OPERATOR_LDAP_URI' => 'ldaps://directory',
    'CONFIG_OPERATOR_LDAP_SECURITY' => 'ldaps',
    'CONFIG_OPERATOR_LDAP_TLS_VERIFY' => 'false',
    'CONFIG_OPERATOR_LDAP_BASE_DN' => 'dc=x',
    'CONFIG_OPERATOR_LDAP_BIND_DN' => 'cn=service',
), $stringFalseFake);
$stringFalseProvider->authenticate('Alice', 'ldap-secret');
$usesNever = false;
foreach ($stringFalseFake->options as $option) {
    if ($option[1] === 24582 && $option[2] === 0) { $usesNever = true; }
}
check_auth('string false explicitly disables certificate verification', $usesNever);

$manager = new OperatorAuthenticationManager($local);
check_auth('manager delegates to its explicit provider', $manager->authenticate('alice', 'local-secret')->isAuthenticated());
check_auth('manager has no implicit fallback', $manager->authenticate('not-alice', 'local-secret')->getReason() === 'invalid_credentials');

printf("\n%s\n", $failures === 0 ? 'ALL PASSED' : "$failures FAILURE(S)");
exit($failures === 0 ? 0 : 1);
