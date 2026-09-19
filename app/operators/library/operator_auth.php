<?php
/**
 * Operator authentication providers.
 *
 * This file deliberately has no framework or LDAP-extension dependency.  The
 * LDAP provider receives an adapter so authentication can be tested without
 * loading the native extension.
 */

interface OperatorAuthProvider
{
    public function getName();
    public function authenticate($username, $password);
}

final class OperatorAuthResult
{
    private $authenticated;
    private $identity;
    private $reason;
    private $retryable;
    private $rehash;

    private function __construct($authenticated, array $identity, $reason, $retryable, $rehash)
    {
        $this->authenticated = (bool) $authenticated;
        $this->identity = $identity;
        $this->reason = (string) $reason;
        $this->retryable = (bool) $retryable;
        $this->rehash = (bool) $rehash;
    }

    public static function success(array $identity = array(), $rehash = false)
    {
        return new self(true, $identity, 'authenticated', false, $rehash);
    }

    public static function failure($reason, $retryable = false)
    {
        return new self(false, array(), $reason, $retryable, false);
    }

    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    public function authenticated()
    {
        return $this->authenticated;
    }

    public function isSuccess()
    {
        return $this->authenticated;
    }

    public function getIdentity()
    {
        return $this->identity;
    }

    public function identity()
    {
        return $this->identity;
    }

    public function getReason()
    {
        return $this->reason;
    }

    public function reason()
    {
        return $this->reason;
    }

    public function getFailureReason()
    {
        return $this->authenticated ? null : $this->reason;
    }

    public function isRetryable()
    {
        return $this->retryable;
    }

    public function needsRehash()
    {
        return $this->rehash;
    }
}

final class OperatorAuthenticationManager
{
    private $provider;

    public function __construct(OperatorAuthProvider $provider)
    {
        $this->provider = $provider;
    }

    public function authenticate($username, $password)
    {
        $result = $this->provider->authenticate($username, $password);
        if (!$result instanceof OperatorAuthResult) {
            throw new UnexpectedValueException('Operator auth provider returned an invalid result');
        }
        return $result;
    }

    public function login($username, $password)
    {
        return $this->authenticate($username, $password);
    }
}

final class LocalAuthProvider implements OperatorAuthProvider
{
    private $row;
    private $rehashCallback;

    /** @param array $row One already-fetched operator row. */
    public function __construct(array $row, $rehashCallback = null)
    {
        $this->row = $row;
        $this->rehashCallback = $rehashCallback;
    }

    public function getName()
    {
        return 'local';
    }

    public function authenticate($username, $password)
    {
        if (array_key_exists('auth_source', $this->row) && $this->row['auth_source'] !== 'local') {
            return OperatorAuthResult::failure('auth_source_mismatch');
        }
        if (!is_string($password) || $password === '') {
            return OperatorAuthResult::failure('empty_password');
        }
        if (!isset($this->row['username']) || !is_string($this->row['username'])
            || !hash_equals($this->row['username'], (string) $username)) {
            return OperatorAuthResult::failure('invalid_credentials');
        }
        if (!array_key_exists('password', $this->row) || !is_string($this->row['password'])
            || $this->row['password'] === '') {
            return OperatorAuthResult::failure('invalid_credentials');
        }

        $stored = $this->row['password'];
        $verified = password_verify($password, $stored);
        $legacy = !$verified && hash_equals($stored, $password);
        if (!$verified && !$legacy) {
            return OperatorAuthResult::failure('invalid_credentials');
        }

        $rehash = $legacy || ($verified && password_needs_rehash($stored, PASSWORD_DEFAULT));
        if ($rehash && is_callable($this->rehashCallback)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            call_user_func($this->rehashCallback, $newHash, $username, $this->row);
        }

        $identity = array(
            'username' => $this->row['username'],
            'source' => 'local',
        );
        foreach (array('id', 'operator_id', 'group') as $key) {
            if (array_key_exists($key, $this->row)) {
                $identity[$key] = $this->row[$key];
            }
        }
        return OperatorAuthResult::success($identity, $rehash);
    }
}

interface OperatorLdapAdapter
{
    public function connect($uri);
    public function setOption($connection, $option, $value);
    public function startTls($connection);
    public function bind($connection, $dn, $password);
    public function search($connection, $baseDn, $filter, array $attributes);
    public function entries($connection, $searchResult);
    public function escape($value, $ignore = '', $flags = 0);
    public function errorCode($connection);
    public function close($connection);
}

/** Native adapter kept small so all extension calls remain injectable. */
class NativeLdapAdapter implements OperatorLdapAdapter
{
    private function available($name)
    {
        if (!function_exists($name)) {
            throw new RuntimeException('LDAP extension is unavailable');
        }
    }

    public function connect($uri)
    {
        $this->available('ldap_connect');
        return @ldap_connect($uri);
    }

    public function setOption($connection, $option, $value)
    {
        $this->available('ldap_set_option');
        return ldap_set_option($connection, $option, $value);
    }

    public function startTls($connection)
    {
        $this->available('ldap_start_tls');
        return @ldap_start_tls($connection);
    }

    public function bind($connection, $dn, $password)
    {
        $this->available('ldap_bind');
        return @ldap_bind($connection, $dn, $password);
    }

    public function search($connection, $baseDn, $filter, array $attributes)
    {
        $this->available('ldap_search');
        return @ldap_search($connection, $baseDn, $filter, $attributes);
    }

    public function entries($connection, $searchResult)
    {
        $this->available('ldap_get_entries');
        return ldap_get_entries($connection, $searchResult);
    }

    public function escape($value, $ignore = '', $flags = 0)
    {
        $this->available('ldap_escape');
        return ldap_escape($value, $ignore, $flags);
    }

    public function errorCode($connection)
    {
        return function_exists('ldap_errno') ? ldap_errno($connection) : 0;
    }

    public function close($connection)
    {
        if (function_exists('ldap_unbind')) {
            return ldap_unbind($connection);
        }
        return true;
    }
}

class OperatorNativeLdapAdapter extends NativeLdapAdapter
{
}

final class LdapAuthProvider implements OperatorAuthProvider
{
    private $config;
    private $adapter;

    public function __construct(array $config = array(), $adapter = null)
    {
        /* Accept adapter-first too; it is convenient for small isolated tests. */
        if (is_object($config) && (is_array($adapter) || $adapter === null)) {
            $tmp = $config;
            $config = is_array($adapter) ? $adapter : array();
            $adapter = $tmp;
        }
        $this->config = $config;
        $this->adapter = $adapter ?: new NativeLdapAdapter();
    }

    public function getName()
    {
        return 'ldap';
    }

    public function authenticate($username, $password)
    {
        if (!is_string($password) || $password === '') {
            return OperatorAuthResult::failure('empty_password');
        }
        if (!is_string($username) || $username === '') {
            return OperatorAuthResult::failure('invalid_credentials');
        }

        if ($this->externalIdAttribute() === null) {
            return OperatorAuthResult::failure('ldap_configuration_invalid');
        }

        $uris = $this->uris();
        if (!$uris) {
            return OperatorAuthResult::failure('ldap_unavailable', true);
        }
        $lastTechnical = false;
        foreach ($uris as $uri) {
            $connection = null;
            $searchCompleted = false;
            try {
                $security = $this->securityMode($uri);
                if ($security === null || !$this->configureTlsOptions(null)) {
                    $lastTechnical = true;
                    continue;
                }
                $connection = $this->adapter->connect($uri);
                if (!$connection) {
                    $lastTechnical = true;
                    continue;
                }
                if (!$this->configureTls($connection, $uri)) {
                    $lastTechnical = true;
                    $this->safeClose($connection);
                    continue;
                }
                if (!$this->bindService($connection)) {
                    $code = $this->errorCode($connection);
                    $this->safeClose($connection);
                    if ($this->isTechnicalCode($code)) {
                        $lastTechnical = true;
                        continue;
                    }
                    return OperatorAuthResult::failure('service_auth_failed');
                }

                $filter = $this->userFilter($username);
                $attributes = $this->requestedAttributes();
                $search = $this->adapter->search($connection, $this->userBaseDn(), $filter, $attributes);
                if (!$search) {
                    $code = $this->errorCode($connection);
                    $this->safeClose($connection);
                    if ($this->isTechnicalCode($code)) {
                        $lastTechnical = true;
                        continue;
                    }
                    return OperatorAuthResult::failure('ldap_search_failed');
                }
                /* A successful search is a decision point: never fail over after it. */
                $searchCompleted = true;
                $entries = $this->entries($connection, $search);
                if (!is_array($entries)) {
                    $this->safeClose($connection);
                    return OperatorAuthResult::failure('ldap_search_failed');
                }
                if (count($entries) !== 1) {
                    $this->safeClose($connection);
                    return OperatorAuthResult::failure(count($entries) === 0 ? 'invalid_credentials' : 'ldap_ambiguous');
                }
                $entry = reset($entries);
                if (!is_array($entry)) {
                    $this->safeClose($connection);
                    return OperatorAuthResult::failure('ldap_search_failed');
                }
                if (!$this->allowedGroup($entry)) {
                    $this->safeClose($connection);
                    return OperatorAuthResult::failure('group_not_allowed');
                }
                $dn = $this->entryValue($entry, 'dn');
                if (!is_string($dn) || $dn === '') {
                    $this->safeClose($connection);
                    return OperatorAuthResult::failure('ldap_search_failed');
                }
                if (!$this->adapter->bind($connection, $dn, $password)) {
                    $code = $this->errorCode($connection);
                    $this->safeClose($connection);
                    if ($this->isTechnicalCode($code)) {
                        $lastTechnical = true;
                        continue;
                    }
                    return OperatorAuthResult::failure('invalid_credentials');
                }

                $identity = array(
                    'username' => (string) $username,
                    'source' => 'ldap',
                    'dn' => $dn,
                );
                $externalAttribute = $this->externalIdAttribute();
                $external = $this->entryValue($entry, $externalAttribute);
                $externalId = $this->canonicalExternalId($external, $externalAttribute);
                if ($external === null || $externalId === null || $externalId === '') {
                    $this->safeClose($connection);
                    return OperatorAuthResult::failure('ldap_external_id_missing');
                }
                $identity['external_id'] = $externalId;
                $this->safeClose($connection);
                return OperatorAuthResult::success($identity);
            } catch (Throwable $exception) {
                if ($connection !== null) {
                    $this->safeClose($connection);
                }
                /* After a completed search the selected directory is
                 * authoritative; never authenticate against another URI. */
                if ($searchCompleted) {
                    return OperatorAuthResult::failure('ldap_authentication_failed');
                }
                $lastTechnical = true;
                continue;
            }
        }
        return OperatorAuthResult::failure('ldap_unavailable', $lastTechnical);
    }

    private function value($key, $default = null)
    {
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }
        /* Permit the same names in a lower-level config adapter without making
         * the public names ambiguous. */
        $short = strtolower(substr($key, strlen('CONFIG_OPERATOR_')));
        foreach (array($short, strtolower($key)) as $candidate) {
            if (array_key_exists($candidate, $this->config)) {
                return $this->config[$candidate];
            }
        }
        return $default;
    }

    private function uris()
    {
        $value = $this->value('CONFIG_OPERATOR_LDAP_URIS', null);
        if ($value === null) {
            $value = $this->value('CONFIG_OPERATOR_LDAP_URI', array());
        }
        if (is_string($value)) {
            $value = preg_split('/[\r\n,]+/', $value, -1, PREG_SPLIT_NO_EMPTY);
        }
        if (!is_array($value)) {
            return array();
        }
        $result = array();
        foreach ($value as $uri) {
            if (is_string($uri) && $uri !== '') {
                $result[] = $uri;
            }
        }
        return $result;
    }

    private function securityMode($uri = '')
    {
        $mode = $this->value('CONFIG_OPERATOR_LDAP_SECURITY', null);
        if ($mode === null) {
            $mode = $this->value('CONFIG_OPERATOR_LDAP_MODE', null);
        }
        if ($mode === null) {
            $mode = 'plain';
        }
        $mode = strtolower(trim((string) $mode));
        if (!in_array($mode, array('plain', 'starttls', 'ldaps'), true)) {
            return null;
        }

        /* A configured security mode must agree with the URI scheme. Merely
         * setting certificate options does not upgrade ldap:// to LDAPS. */
        $scheme = strtolower((string) parse_url((string) $uri, PHP_URL_SCHEME));
        if (($mode === 'ldaps' && $scheme !== 'ldaps')
            || ($mode !== 'ldaps' && $scheme !== 'ldap')) {
            return null;
        }
        return $mode;
    }

    private function booleanValue($key, $default)
    {
        $value = $this->value($key, $default);
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value)) {
            return $value !== 0;
        }
        if (is_string($value)) {
            $parsed = filter_var(trim($value), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            return $parsed === null ? (bool) $default : $parsed;
        }
        return (bool) $value;
    }

    private function caFile()
    {
        return $this->value('CONFIG_OPERATOR_LDAP_CA_FILE',
            $this->value('CONFIG_OPERATOR_LDAP_TLS_CA_CERT',
                $this->value('CONFIG_OPERATOR_LDAP_CA_CERT', '')));
    }

    private function configureTlsOptions($connection)
    {
        $verify = $this->booleanValue('CONFIG_OPERATOR_LDAP_TLS_VERIFY', true);
        $requireCert = $verify
            ? $this->ldapConstant('LDAP_OPT_X509_DEMAND', 2)
            : $this->ldapConstant('LDAP_OPT_X509_NEVER', 0);
        if (!$this->adapter->setOption($connection,
            $this->ldapConstant('LDAP_OPT_X_TLS_REQUIRE_CERT', 24582), $requireCert)) {
            return false;
        }
        $ca = $this->caFile();
        if ($ca !== '' && !$this->adapter->setOption($connection,
            $this->ldapConstant('LDAP_OPT_X_TLS_CACERTFILE', 24579), $ca)) {
            return false;
        }
        $timeout = $this->value('CONFIG_OPERATOR_LDAP_NETWORK_TIMEOUT', null);
        if ($timeout !== null && !$this->adapter->setOption($connection,
            $this->ldapConstant('LDAP_OPT_NETWORK_TIMEOUT', 20485), (int) $timeout)) {
            return false;
        }
        return true;
    }

    private function configureTls($connection, $uri)
    {
        $mode = $this->securityMode($uri);
        if ($mode === null || !$this->configureTlsOptions($connection)) {
            return false;
        }
        if ($mode === 'starttls' && !$this->adapter->startTls($connection)) {
            return false;
        }
        return true;
    }

    private function ldapConstant($name, $fallback)
    {
        return defined($name) ? constant($name) : $fallback;
    }

    private function bindService($connection)
    {
        $dn = $this->value('CONFIG_OPERATOR_LDAP_BIND_DN', $this->value('CONFIG_OPERATOR_LDAP_SERVICE_DN', ''));
        $password = $this->value('CONFIG_OPERATOR_LDAP_BIND_PASSWORD', $this->value('CONFIG_OPERATOR_LDAP_SERVICE_PASSWORD', ''));
        return $this->adapter->bind($connection, $dn, $password);
    }

    private function userFilter($username)
    {
        $escaped = $this->escapeFilter($username);
        $template = $this->value('CONFIG_OPERATOR_LDAP_USER_FILTER', '(&(objectClass=person)(uid={username}))');
        if (strpos($template, '{username}') !== false) {
            $filter = str_replace('{username}', $escaped, $template);
        } elseif (strpos($template, '%s') !== false) {
            $filter = sprintf($template, $escaped);
        } else {
            $attribute = $this->value('CONFIG_OPERATOR_LDAP_USERNAME_ATTRIBUTE', 'uid');
            $filter = '(&' . $template . '(' . $attribute . '=' . $escaped . '))';
        }
        $groups = $this->allowedGroups();
        $rule = (string) $this->value('CONFIG_OPERATOR_LDAP_GROUP_MATCHING_RULE', '');
        if ($groups && $rule !== '') {
            $groupAttribute = $this->value('CONFIG_OPERATOR_LDAP_GROUP_ATTRIBUTE', 'memberOf');
            $parts = array();
            foreach ($groups as $group) {
                $parts[] = '(' . $groupAttribute . ':' . $rule . ':=' . $this->escapeFilter($group) . ')';
            }
            $filter = '(&' . $filter . '(|' . implode('', $parts) . '))';
        }
        return $filter;
    }

    private function externalIdAttribute()
    {
        $attribute = $this->value('CONFIG_OPERATOR_LDAP_EXTERNAL_ID_ATTRIBUTE', 'uid');
        $attribute = is_string($attribute) ? trim($attribute) : '';
        return $attribute === '' ? null : $attribute;
    }

    private function userBaseDn()
    {
        return $this->value('CONFIG_OPERATOR_LDAP_USER_BASE_DN',
            $this->value('CONFIG_OPERATOR_LDAP_BASE_DN', ''));
    }

    private function requestedAttributes()
    {
        $attrs = array('dn');
        $external = $this->externalIdAttribute();
        if ($external !== '') {
            $attrs[] = $external;
        }
        $group = $this->value('CONFIG_OPERATOR_LDAP_GROUP_ATTRIBUTE', 'memberOf');
        if ($group !== '') {
            $attrs[] = $group;
        }
        return array_values(array_unique($attrs));
    }

    private function allowedGroups()
    {
        $groups = $this->value('CONFIG_OPERATOR_LDAP_ALLOWED_GROUPS', array());
        if (is_string($groups)) {
            $groups = preg_split('/[\r\n;]+/', $groups, -1, PREG_SPLIT_NO_EMPTY);
        }
        return is_array($groups) ? array_values(array_filter($groups, 'is_string')) : array();
    }

    private function allowedGroup(array $entry)
    {
        $allowed = $this->allowedGroups();
        if (!$allowed) {
            return true;
        }
        /* With the AD matching rule, the server has already enforced nested
         * membership in the search filter.  Do not require memberOf in attrs. */
        if ((string) $this->value('CONFIG_OPERATOR_LDAP_GROUP_MATCHING_RULE', '') !== '') {
            return true;
        }
        $attribute = $this->value('CONFIG_OPERATOR_LDAP_GROUP_ATTRIBUTE', 'memberOf');
        $values = $this->entryValues($entry, $attribute);
        foreach ($values as $value) {
            foreach ($allowed as $group) {
                if (strcasecmp(trim((string) $value), trim($group)) === 0) {
                    return true;
                }
            }
        }
        return false;
    }

    private function entries($connection, $search)
    {
        if (is_array($search) && !$this->looksLikeNativeEntries($search)) {
            return array($search);
        }
        if (method_exists($this->adapter, 'entries')) {
            $data = $this->adapter->entries($connection, $search);
        } elseif (method_exists($this->adapter, 'getEntries')) {
            $data = $this->adapter->getEntries($connection, $search);
        } else {
            return null;
        }
        if (!is_array($data)) {
            return null;
        }
        if (isset($data['count'])) {
            $count = (int) $data['count'];
            $out = array();
            for ($i = 0; $i < $count; $i++) {
                if (isset($data[$i]) && is_array($data[$i])) {
                    $out[] = $data[$i];
                }
            }
            return $out;
        }
        return array_values($data);
    }

    private function looksLikeNativeEntries(array $data)
    {
        return array_key_exists('count', $data) || (isset($data[0]) && is_array($data[0]));
    }

    private function entryValues(array $entry, $attribute)
    {
        foreach ($entry as $key => $value) {
            if (is_string($key) && strtolower($key) === strtolower($attribute)) {
                if (is_array($value)) {
                    if (isset($value['count'])) {
                        $out = array();
                        for ($i = 0; $i < (int) $value['count']; $i++) {
                            if (array_key_exists($i, $value)) {
                                $out[] = $value[$i];
                            }
                        }
                        return $out;
                    }
                    return array_values($value);
                }
                return array($value);
            }
        }
        return array();
    }

    private function entryValue(array $entry, $attribute)
    {
        $values = $this->entryValues($entry, $attribute);
        return $values ? reset($values) : null;
    }

    private function canonicalExternalId($value, $attribute)
    {
        if (strcasecmp($attribute, 'objectGUID') !== 0 && strcasecmp($attribute, 'objectguid') !== 0) {
            return is_scalar($value) ? (string) $value : null;
        }
        if (!is_string($value)) {
            return null;
        }
        if (strlen($value) === 16) {
            $hex = unpack('H*', $value);
            $hex = $hex[1];
            return strtolower(substr($hex, 6, 2) . substr($hex, 4, 2) . substr($hex, 2, 2) . substr($hex, 0, 2)
                . '-' . substr($hex, 10, 2) . substr($hex, 8, 2)
                . '-' . substr($hex, 14, 2) . substr($hex, 12, 2)
                . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12));
        }
        return strtolower(trim($value, '{}'));
    }

    private function escapeFilter($value)
    {
        if (method_exists($this->adapter, 'escape')) {
            $flags = defined('LDAP_ESCAPE_FILTER') ? LDAP_ESCAPE_FILTER : 1;
            return $this->adapter->escape($value, '', $flags);
        }
        return strtr($value, array('\\' => '\\5c', '*' => '\\2a', '(' => '\\28', ')' => '\\29', "\0" => '\\00'));
    }

    private function errorCode($connection)
    {
        try {
            return method_exists($this->adapter, 'errorCode') ? $this->adapter->errorCode($connection) : null;
        } catch (Throwable $exception) {
            return null;
        }
    }

    private function isTechnicalCode($code)
    {
        if ($code === null || $code === '') {
            return false;
        }
        return in_array((int) $code, array(-1, 0, 1, 51, 52, 53, 81, 82, 85, 86, 91, 110, 112, 113, 114, 115, 116, 118), true);
    }

    private function safeClose($connection)
    {
        try {
            $this->adapter->close($connection);
        } catch (Throwable $exception) {
            /* Closing is best effort and must not disclose LDAP errors. */
        }
    }
}
