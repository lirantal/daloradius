# Operator LDAP authentication

This guide configures LDAP authentication for the daloRADIUS operators portal. It
covers Microsoft Active Directory (AD) and OpenLDAP. LDAP support requires the
PHP LDAP extension (`php-ldap` on Debian/AlmaLinux and in the official Docker
image).

LDAP is disabled and local authentication is the default in the sample
configuration:

```php
$configValues['CONFIG_OPERATOR_AUTH_LOCAL_ENABLED'] = true;
$configValues['CONFIG_OPERATOR_AUTH_LDAP_ENABLED'] = false;
$configValues['CONFIG_OPERATOR_AUTH_DEFAULT'] = 'local';
```

Do not disable local authentication until an LDAP login and a local recovery
login have both been tested from a separate session.

## Configuration keys

The complete LDAP configuration is in
`app/common/includes/daloradius.conf.php.sample`:

```php
$configValues['CONFIG_OPERATOR_AUTH_LDAP_URI'] = array(
    'ldaps://ldap.example.org:636',
);
$configValues['CONFIG_OPERATOR_AUTH_LDAP_SECURITY'] = 'ldaps';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_TLS_VERIFY'] = true;
$configValues['CONFIG_OPERATOR_AUTH_LDAP_TLS_CA_FILE'] = '/etc/ssl/certs/ca-certificates.crt';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_BASE_DN'] = 'dc=example,dc=org';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_USER_BASE_DN'] = 'ou=People,dc=example,dc=org';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_BIND_DN'] = 'cn=daloradius,ou=Service Accounts,dc=example,dc=org';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_BIND_PASSWORD'] = '';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_FILTER'] = '(&(objectClass=person)(uid={username}))';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_EXTERNAL_ID_ATTRIBUTE'] = 'uid';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_TIMEOUT'] = 5;
$configValues['CONFIG_OPERATOR_AUTH_LDAP_ALLOWED_GROUPS'] = array(
    'cn=daloradius-operators,ou=Groups,dc=example,dc=org',
);
$configValues['CONFIG_OPERATOR_AUTH_LDAP_GROUP_ATTRIBUTE'] = 'memberOf';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_GROUP_MATCHING_RULE'] = '';
```

The implementation must treat the following values as configuration types, not
as display strings: the three enable/default values are booleans/string, URI
and allowed-group values are arrays, TLS verification is a boolean, and timeout
is an integer. `{username}` is replaced with the submitted operator name by the
LDAP authentication flow. The URI scheme must match the security mode:
`plain` and `starttls` require `ldap://`; `ldaps` requires `ldaps://`. A mismatch
is rejected before any bind, and certificate failure never downgrades security.

### Bind-password handling

`DALORADIUS_LDAP_BIND_PASSWORD` overrides
`CONFIG_OPERATOR_AUTH_LDAP_BIND_PASSWORD` at runtime. Prefer the environment
variable or a secret manager; leave the PHP-file value empty. Never commit,
print, paste into a ticket, or include a bind password in a debug log. Protect
`.env` with `chmod 600` and remember that `docker compose config` renders
interpolated environment values, so do not redirect its output to a shared log.

A bind account should have the minimum directory-read permission required for
user and group searches. It must not be an administrator account.

## Docker environment

Copy `.env.example` to `.env`. Scalar values use the `DALORADIUS_*` names shown
below. URI and group lists are JSON arrays; JSON avoids unsafe comma splitting
when a DN or URI contains punctuation:

```dotenv
DALORADIUS_OPERATOR_AUTH_LOCAL_ENABLED=true
DALORADIUS_OPERATOR_AUTH_LDAP_ENABLED=true
DALORADIUS_OPERATOR_AUTH_DEFAULT=ldap
DALORADIUS_LDAP_URI=["ldaps://ad-1.example.org:636","ldaps://ad-2.example.org:636"]
DALORADIUS_LDAP_SECURITY=ldaps
DALORADIUS_LDAP_TLS_VERIFY=true
DALORADIUS_LDAP_TLS_CA_FILE=/etc/ssl/certs/ca-certificates.crt
DALORADIUS_LDAP_BASE_DN=DC=example,DC=org
DALORADIUS_LDAP_USER_BASE_DN=OU=Users,DC=example,DC=org
DALORADIUS_LDAP_BIND_DN=CN=daloradius,OU=Service Accounts,DC=example,DC=org
DALORADIUS_LDAP_BIND_PASSWORD=store-this-outside-source-control
DALORADIUS_LDAP_FILTER=(&(objectClass=user)(sAMAccountName={username}))
DALORADIUS_LDAP_EXTERNAL_ID_ATTRIBUTE=objectGUID
DALORADIUS_LDAP_TIMEOUT=5
DALORADIUS_LDAP_GROUP_ATTRIBUTE=memberOf
DALORADIUS_LDAP_GROUP_MATCHING_RULE=1.2.840.113556.1.4.1941
DALORADIUS_LDAP_ALLOWED_GROUPS=["CN=daloRADIUS Operators,OU=Groups,DC=example,DC=org"]
```

The Compose entrypoint validates both JSON arrays before writing the generated
configuration. Invalid arrays stop initialization rather than being partially
applied. The entrypoint does not echo the bind password. Changing the bind
password environment value on an existing container requires recreating or
restarting the web container so the new process environment is used.

For a standalone install, set the environment variable in the Apache/PHP
service environment and restart that service. Do not put the secret in a shell
command line captured by process inspection.

## Choosing the LDAP transport

Set `CONFIG_OPERATOR_AUTH_LDAP_SECURITY` (or `DALORADIUS_LDAP_SECURITY`) to one
of the supported modes. Use `TLS_VERIFY=true` in production for every TLS mode.

| Mode | URI example | Notes |
| --- | --- | --- |
| Plain LDAP | `ldap://ldap.example.org:389` | No encryption. Use only on a protected test network. Never send credentials over an untrusted network. |
| StartTLS | `ldap://ldap.example.org:389` | Starts plain and upgrades the connection with StartTLS. Require certificate verification and reject downgrade/failure. |
| LDAPS | `ldaps://ldap.example.org:636` | TLS from connection start. Require certificate verification and a CA file containing the issuing CA chain. |

`TLS_VERIFY=false` is a diagnostic escape hatch only. It makes a man-in-the-
middle attack possible and must not be used in production. If it is used for a
short test, record the change, test only on an isolated network, restore
`true`, and restart the web service.

Do not mix URI schemes and security settings accidentally. For example, use
`ldap://...` with `starttls` and `ldaps://...` with `ldaps`. Confirm that the
certificate name matches the hostname in the URI and that the container or host
has the required CA bundle.

## Active Directory example

A typical AD configuration uses the logon name for lookup and `objectGUID` as a
stable external identifier:

```php
$configValues['CONFIG_OPERATOR_AUTH_LDAP_URI'] = array(
    'ldaps://dc01.example.org:636',
    'ldaps://dc02.example.org:636',
);
$configValues['CONFIG_OPERATOR_AUTH_LDAP_SECURITY'] = 'ldaps';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_TLS_VERIFY'] = true;
$configValues['CONFIG_OPERATOR_AUTH_LDAP_TLS_CA_FILE'] = '/etc/ssl/certs/ca-certificates.crt';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_BASE_DN'] = 'DC=example,DC=org';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_USER_BASE_DN'] = 'OU=Users,DC=example,DC=org';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_FILTER'] = '(&(objectClass=user)(sAMAccountName={username}))';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_EXTERNAL_ID_ATTRIBUTE'] = 'objectGUID';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_ALLOWED_GROUPS'] = array(
    'CN=daloRADIUS Operators,OU=Groups,DC=example,DC=org',
);
```

For nested AD membership, set:

```php
$configValues['CONFIG_OPERATOR_AUTH_LDAP_GROUP_ATTRIBUTE'] = 'memberOf';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_GROUP_MATCHING_RULE'] = '1.2.840.113556.1.4.1941';
```

The matching rule is inserted only with the configured allowed group DNs. Leave
it empty for direct membership checks.

AD deployments may instead use `userPrincipalName` when operators enter an
email-style login. Make the filter match the chosen login format and test
case-folding behavior with the actual directory. Do not use an unrestricted
filter such as `(objectClass=*)`.

For nested AD groups, use a directory-supported matching-rule filter or an
explicit group-membership strategy. A common AD matching rule is:

```text
(memberOf:1.2.840.113556.1.4.1941:=CN=daloRADIUS Operators,OU=Groups,DC=example,DC=org)
```

Combine it with the user filter only after confirming the directory supports
that OID. If nested matching is unavailable or expensive, provision operators
into a direct group and list that group in `LDAP_ALLOWED_GROUPS`.

## OpenLDAP example

OpenLDAP commonly uses `uid` and `inetOrgPerson`:
When group restrictions are enabled, the user entry must expose the configured group attribute (commonly `memberOf` via the memberof overlay).


```php
$configValues['CONFIG_OPERATOR_AUTH_LDAP_URI'] = array(
    'ldap://ldap-1.example.org:389',
    'ldap://ldap-2.example.org:389',
);
$configValues['CONFIG_OPERATOR_AUTH_LDAP_SECURITY'] = 'starttls';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_TLS_VERIFY'] = true;
$configValues['CONFIG_OPERATOR_AUTH_LDAP_TLS_CA_FILE'] = '/etc/ssl/certs/ca-certificates.crt';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_BASE_DN'] = 'dc=example,dc=org';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_USER_BASE_DN'] = 'ou=People,dc=example,dc=org';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_FILTER'] = '(&(objectClass=inetOrgPerson)(uid={username}))';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_EXTERNAL_ID_ATTRIBUTE'] = 'entryUUID';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_ALLOWED_GROUPS'] = array(
    'cn=daloradius-operators,ou=Groups,dc=example,dc=org',
);
```

Group enforcement reads the configured membership attribute from the user
entry. For OpenLDAP, expose `memberOf` (or another DN-valued user attribute) and
set `CONFIG_OPERATOR_AUTH_LDAP_GROUP_ATTRIBUTE` accordingly. Direct searches of
`groupOfNames`/`posixGroup` entries using `member` or `memberUid` are not part of
this first implementation. Test the chosen overlay and nested-group behavior
explicitly because OpenLDAP schema choices differ between installations.

## Multiple LDAP servers

Put servers in priority order in the URI array. Use the same TLS policy and CA
trust for every server. The authentication implementation should try the next
URI only for a connection/availability failure, not after an invalid password.
Otherwise a mistyped password can create unnecessary load and confusing audit
trails. Keep all servers in the same identity domain or document differences in
base DN, schema, and group membership before enabling failover.

The existing operator login has no built-in IP-and-username rate limiter. LDAP
failures therefore can contribute to directory lockout thresholds. Apply a
suitable limit at the reverse proxy/WAF and monitor directory audit logs. The
provider selection and authentication manager are centralized so a native
`IP + username` limiter can be added later without changing LDAP bind logic.

## Operator provisioning and MFA

LDAP authentication does not by itself grant operator authorization. The
application must map a successfully authenticated external identity to a
local operator record and enforce `LDAP_ALLOWED_GROUPS`. Plan whether first
login is:

1. **Manual provisioning (recommended initially):** create the local operator
   record and its ACLs in daloRADIUS, then permit LDAP login only for that
   external identity/group.
2. **Controlled just-in-time provisioning:** create a restricted local record
   from approved LDAP attributes and apply a safe default ACL. Review and
   tighten the record before granting additional permissions.

Do not auto-provision every directory user or copy arbitrary LDAP attributes
into HTML, SQL, or logs. Keep local ACLs authoritative and audit provisioning
changes.

Keep MFA enabled for operators after LDAP is working. LDAP password validation
and local TOTP/MFA are separate controls; LDAP does not replace MFA. Follow
[`operator-mfa.md`](operator-mfa.md) to reset a lost local TOTP enrollment. Test
MFA after an LDAP login and keep recovery codes offline.

## Rollout and local recovery

For an existing database, apply the idempotent migration before deploying the
provider-aware pages:

```bash
mariadb -u root -p radius < contrib/db/migrations/2026-09-operator-ldap.sql
```

The official Docker entrypoint applies it automatically. It preserves existing
passwords, marks existing operators as `local`, and may be run more than once.

1. Install `php-ldap` and deploy the CA file.
2. Leave `LOCAL_ENABLED=true`, `LDAP_ENABLED=false`, and `DEFAULT=local`.
3. Configure one test URI and a test directory account.
4. Validate certificate verification, search scope, filter, external ID, and
   group authorization without changing the production operator.
5. Provision a second operator with least-privilege ACLs and test MFA.
6. Enable LDAP while retaining local authentication and keep a local
   break-glass account with a tested password and MFA recovery procedure.
7. Change the default to `ldap` only after two independent LDAP logins work.

If LDAP is unavailable, use the local break-glass operator. If all local
operators are inaccessible, follow the normal operator password/MFA recovery
procedure from the host console or database backup process; do not delete ACLs
or disable authentication globally as an emergency shortcut. After recovery,
restore the intended configuration and rotate any credentials that may have
been exposed during troubleshooting.

## Troubleshooting

### `Class "LDAP\\\\..." not found` or missing LDAP functions

Install `php-ldap`, restart Apache/PHP, and confirm the module in the same PHP
runtime used by the web server. The CLI `php -m` output can differ from the
Apache module set.

### TLS handshake or certificate errors

Check the URI hostname, port, system clock, CA file permissions, and the full
issuer chain. Use `openssl s_client` only for diagnostics and never treat a
successful TCP connection as proof that certificate verification is correct.
Do not solve certificate errors by leaving `TLS_VERIFY=false`.

### Search returns no user

Check base DN versus user base DN, the filter syntax, escaped usernames, bind
account read permission, and whether the directory uses `uid`,
`sAMAccountName`, or `userPrincipalName`. Search with a least-privilege account
using a directory client; do not paste passwords into command history.

### User authenticates but is denied

Check the allowed group DN spelling/case, group membership attribute, nested
membership behavior, and the local operator mapping/ACL. Confirm that the
external ID attribute is present and stable. Group authorization should fail
closed when it cannot be evaluated.

### Failover behaves unexpectedly

Confirm all URIs are valid JSON, reachable from the web container, and covered
by the same CA trust. Review application logs for server names and error codes,
never passwords or bind credentials. A password failure must not be treated as
an availability failure.

### Docker changes do not take effect

Inspect only non-secret variable names and values. Recreate the web service
when changing its environment, and remember that `/data` preserves the
configuration lock file. Never print the full environment or `docker compose
config` output when it contains a bind password.
