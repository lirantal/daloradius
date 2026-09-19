# Operator LDAP authentication

This guide configures LDAP authentication for the **daloRADIUS operators
portal**. It covers Docker Compose, Debian, AlmaLinux, Microsoft Active
Directory, and generic LDAP/OpenLDAP deployments.

LDAP is an optional first-factor provider. It does not replace the local
operator database:

- local and LDAP operators may coexist;
- LDAP is disabled by default;
- existing operators remain local after the database migration;
- submitted operator LDAP passwords are never persisted by daloRADIUS; the
  separate service-account bind secret must still be stored securely in the
  PHP configuration or supplied through the process environment;
- ACLs, locations, TOTP, recovery codes, and operator sessions remain local to
  daloRADIUS;
- the login form selects one provider explicitly; authentication never falls
  back from LDAP to Local or from Local to LDAP;
- a local recovery administrator should remain enabled and tested.

## How authentication works

The implementation uses LDAP **search + bind**:

1. daloRADIUS connects to one configured LDAP URI.
2. It binds with the read-only service account.
3. It searches below the user base DN with the configured filter.
4. The submitted username is escaped before replacing `{username}`.
5. Exactly one result is required.
6. Optional group restrictions are evaluated.
7. daloRADIUS binds as the returned user DN with the submitted password.
8. The authenticated directory identity is matched to a manually provisioned
   local operator record.
9. Local ACL, location, MFA, recovery-code, and session processing continues.

A technical connection failure may try the next URI. Invalid credentials, an
ambiguous search, a group denial, or another authoritative directory response
does not trigger provider fallback or authentication against a second server.

## Prerequisites

- The PHP LDAP extension is installed in the web runtime.
- The web runtime can resolve and reach every LDAP hostname and port.
- The issuing CA chain is available to the web runtime when TLS verification is
  enabled.
- A least-privilege directory service account can read the user attributes and
  group membership required by the configured filter.
- The operator LDAP database migration has been applied.
- At least one tested local administrator remains available for recovery.

A CLI check is a useful preliminary test:

```bash
php -m | grep -i '^ldap$'
```

The CLI and web SAPIs can load different configuration. Also confirm that the
LDAP package is enabled for the PHP version used by Apache or PHP-FPM, restart
the web runtime, and perform an operator LDAP login test. Do not expose a public
`phpinfo()` page for this check.

## Database migration

Fresh installations already contain the operator identity columns. For an
existing database, run the idempotent migration from the repository root before
deploying the provider-aware operator pages:

```bash
mariadb -u <database-user> -p <configured-database-name> \
  < contrib/db/migrations/2026-09-operator-ldap.sql
```

Use the database name configured for that installation. The Docker examples
normally use `radius`, while the Debian and AlmaLinux installation guides use
`raddb`; do not copy one platform's database name to another deployment.

The migration:

- adds `operators.auth_source` with the default `local`;
- adds nullable `operators.external_id`;
- permits `NULL` in `operators.password` for LDAP identities;
- preserves existing usernames and password values;
- adds a uniqueness constraint so one LDAP external identity cannot map to
  multiple local ACL profiles;
- may be run more than once.

The official Docker web entrypoint applies this migration automatically.
Manual Debian and AlmaLinux installations must apply the normal project
migration sequence described by their installation guide.

## Configuration reference

Native installations configure LDAP in:

```text
app/common/includes/daloradius.conf.php
```

The canonical defaults are documented in:

```text
app/common/includes/daloradius.conf.php.sample
```

Docker deployments use the corresponding environment variables. URI and group
lists are JSON arrays in Docker and PHP arrays in the configuration file.

| PHP configuration key | Docker environment variable | Type / default | Purpose |
| --- | --- | --- | --- |
| `CONFIG_OPERATOR_AUTH_LOCAL_ENABLED` | `DALORADIUS_OPERATOR_AUTH_LOCAL_ENABLED` | boolean / `true` | Enables the historical local-password provider. Keep it enabled for recovery. |
| `CONFIG_OPERATOR_AUTH_LDAP_ENABLED` | `DALORADIUS_OPERATOR_AUTH_LDAP_ENABLED` | boolean / `false` | Enables the LDAP provider and LDAP operator login option. |
| `CONFIG_OPERATOR_AUTH_DEFAULT` | `DALORADIUS_OPERATOR_AUTH_DEFAULT` | `local` or `ldap` / `local` | Controls the provider presented as the default when both are enabled. It does not enable fallback. |
| `CONFIG_OPERATOR_AUTH_LDAP_URI` | `DALORADIUS_LDAP_URI` | PHP array / JSON array / empty | Ordered LDAP server URIs. Use `ldap://` for `plain` or `starttls`, and `ldaps://` for `ldaps`. |
| `CONFIG_OPERATOR_AUTH_LDAP_SECURITY` | `DALORADIUS_LDAP_SECURITY` | `plain`, `starttls`, or `ldaps` / `starttls` | Selects the transport policy. The URI scheme must match. |
| `CONFIG_OPERATOR_AUTH_LDAP_TLS_VERIFY` | `DALORADIUS_LDAP_TLS_VERIFY` | boolean / `true` | Enables server-certificate validation. Set `false` only explicitly for isolated diagnostics. |
| `CONFIG_OPERATOR_AUTH_LDAP_TLS_CA_FILE` | `DALORADIUS_LDAP_TLS_CA_FILE` | path / empty | CA bundle or issuing CA file readable inside the PHP/web runtime. |
| `CONFIG_OPERATOR_AUTH_LDAP_BASE_DN` | `DALORADIUS_LDAP_BASE_DN` | DN / empty | General LDAP search base and fallback when no user-specific base is set. |
| `CONFIG_OPERATOR_AUTH_LDAP_USER_BASE_DN` | `DALORADIUS_LDAP_USER_BASE_DN` | DN / empty | Narrower base used for operator user searches. |
| `CONFIG_OPERATOR_AUTH_LDAP_BIND_DN` | `DALORADIUS_LDAP_BIND_DN` | DN / empty | DN of the least-privilege search account. |
| `CONFIG_OPERATOR_AUTH_LDAP_BIND_PASSWORD` | `DALORADIUS_LDAP_BIND_PASSWORD` | secret / empty | Service-account password. A non-empty environment value overrides the PHP-file value. |
| `CONFIG_OPERATOR_AUTH_LDAP_FILTER` | `DALORADIUS_LDAP_FILTER` | LDAP filter | User search filter. It should contain exactly one escaped `{username}` placeholder. |
| `CONFIG_OPERATOR_AUTH_LDAP_EXTERNAL_ID_ATTRIBUTE` | `DALORADIUS_LDAP_EXTERNAL_ID_ATTRIBUTE` | attribute / required when LDAP is enabled | Stable directory identifier stored in `operators.external_id`; normally `objectGUID` for AD or `entryUUID` for OpenLDAP. The supplied configuration samples use `uid`, but the Compose fallback is empty if the variable is omitted, so set it explicitly. |
| `CONFIG_OPERATOR_AUTH_LDAP_TIMEOUT` | `DALORADIUS_LDAP_TIMEOUT` | positive integer seconds / `5`, clamped to `1..30` | Request-wide LDAP deadline shared by sequential failover URIs; also bounds each server operation where supported. |
| `CONFIG_OPERATOR_AUTH_LDAP_ALLOWED_GROUPS` | `DALORADIUS_LDAP_ALLOWED_GROUPS` | PHP array / JSON array / empty | Optional allowed group DNs. Empty means no additional LDAP group restriction. |
| `CONFIG_OPERATOR_AUTH_LDAP_GROUP_ATTRIBUTE` | `DALORADIUS_LDAP_GROUP_ATTRIBUTE` | attribute / `memberOf` | DN-valued membership attribute read from the user entry. |
| `CONFIG_OPERATOR_AUTH_LDAP_GROUP_MATCHING_RULE` | `DALORADIUS_LDAP_GROUP_MATCHING_RULE` | OID / empty | Optional server-side matching rule. Use the AD recursive membership OID for nested AD groups. |

### PHP configuration template

```php
$configValues['CONFIG_OPERATOR_AUTH_LOCAL_ENABLED'] = true;
$configValues['CONFIG_OPERATOR_AUTH_LDAP_ENABLED'] = true;
$configValues['CONFIG_OPERATOR_AUTH_DEFAULT'] = 'ldap';

$configValues['CONFIG_OPERATOR_AUTH_LDAP_URI'] = array(
    'ldaps://dc01.example.org:636',
    'ldaps://dc02.example.org:636',
);
$configValues['CONFIG_OPERATOR_AUTH_LDAP_SECURITY'] = 'ldaps';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_TLS_VERIFY'] = true;
$configValues['CONFIG_OPERATOR_AUTH_LDAP_TLS_CA_FILE'] =
    '/etc/ssl/certs/corporate-ldap-ca.crt';

$configValues['CONFIG_OPERATOR_AUTH_LDAP_BASE_DN'] =
    'DC=example,DC=org';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_USER_BASE_DN'] =
    'OU=Users,DC=example,DC=org';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_BIND_DN'] =
    'CN=svc-daloradius,OU=Service Accounts,DC=example,DC=org';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_BIND_PASSWORD'] = '';

$configValues['CONFIG_OPERATOR_AUTH_LDAP_FILTER'] =
    '(&(objectClass=user)(sAMAccountName={username}))';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_EXTERNAL_ID_ATTRIBUTE'] =
    'objectGUID';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_TIMEOUT'] = 5;

$configValues['CONFIG_OPERATOR_AUTH_LDAP_ALLOWED_GROUPS'] = array(
    'CN=daloRADIUS Operators,OU=Groups,DC=example,DC=org',
);
$configValues['CONFIG_OPERATOR_AUTH_LDAP_GROUP_ATTRIBUTE'] = 'memberOf';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_GROUP_MATCHING_RULE'] =
    '1.2.840.113556.1.4.1941';
```

Validate PHP syntax after editing:

```bash
php -l app/common/includes/daloradius.conf.php
```

## Transport modes

The configured security mode and every URI scheme must agree. A mismatch is
rejected before a service or user bind.

| Security mode | URI format | Behavior |
| --- | --- | --- |
| `plain` | `ldap://ldap.example.org:389` | Unencrypted LDAP. Credentials cross the LDAP network in cleartext. Use only on a deliberately isolated test or legacy network. |
| `starttls` | `ldap://ldap.example.org:389` | Connects using LDAP and requires a successful StartTLS upgrade before binding. |
| `ldaps` | `ldaps://ldap.example.org:636` | TLS is established when the connection opens. This is the recommended production mode when supported. |

`CONFIG_OPERATOR_AUTH_LDAP_TLS_VERIFY=true` is recommended for StartTLS and
LDAPS. A certificate failure never causes an automatic retry without
verification.

`TLS_VERIFY=false` is intentionally supported for isolated tests, private PKI
migration, and legacy environments, but it removes server-identity protection
against man-in-the-middle attacks. Use it only as an explicit temporary choice.

For verified TLS:

- use a hostname in the URI that appears in the certificate SAN;
- install or mount the complete issuing CA chain;
- ensure the CA file is readable by Apache/PHP;
- verify system time;
- use the same TLS policy for every failover URI.

## Docker Compose

The official image contains `php-ldap`. Copy the example environment file and
protect the resulting file:

```bash
cp .env.example .env
chmod 600 .env
```

Example Active Directory configuration:

```dotenv
DALORADIUS_OPERATOR_AUTH_LOCAL_ENABLED=true
DALORADIUS_OPERATOR_AUTH_LDAP_ENABLED=true
DALORADIUS_OPERATOR_AUTH_DEFAULT=ldap

DALORADIUS_LDAP_URI=["ldaps://dc01.example.org:636","ldaps://dc02.example.org:636"]
DALORADIUS_LDAP_SECURITY=ldaps
DALORADIUS_LDAP_TLS_VERIFY=true
DALORADIUS_LDAP_TLS_CA_FILE=/etc/ssl/certs/corporate-ldap-ca.crt

DALORADIUS_LDAP_BASE_DN=DC=example,DC=org
DALORADIUS_LDAP_USER_BASE_DN=OU=Users,DC=example,DC=org
DALORADIUS_LDAP_BIND_DN=CN=svc-daloradius,OU=Service Accounts,DC=example,DC=org
DALORADIUS_LDAP_BIND_PASSWORD=CHANGE_ME_LDAP_BIND_PASSWORD
DALORADIUS_LDAP_FILTER=(&(objectClass=user)(sAMAccountName={username}))
DALORADIUS_LDAP_EXTERNAL_ID_ATTRIBUTE=objectGUID
DALORADIUS_LDAP_TIMEOUT=5

DALORADIUS_LDAP_ALLOWED_GROUPS=["CN=daloRADIUS Operators,OU=Groups,DC=example,DC=org"]
DALORADIUS_LDAP_GROUP_ATTRIBUTE=memberOf
DALORADIUS_LDAP_GROUP_MATCHING_RULE=1.2.840.113556.1.4.1941
```

The URI and allowed-group values must be valid single-line JSON arrays. Do not
use a comma-separated string because DNs themselves contain commas.

### Mounting a private CA

A host path in `.env` is not automatically visible inside a container. Add a
read-only CA mount to the `radius-web` service:

```yaml
services:
  radius-web:
    volumes:
      - ./data/daloradius:/data
      - radius_logs:/var/log/freeradius
      - ./certs/corporate-ldap-ca.crt:/etc/ssl/certs/corporate-ldap-ca.crt:ro
```

Then point `DALORADIUS_LDAP_TLS_CA_FILE` to the **container path**:

```dotenv
DALORADIUS_LDAP_TLS_CA_FILE=/etc/ssl/certs/corporate-ldap-ca.crt
```

### Applying and verifying Docker configuration

Validate the configuration without printing it to shared logs because rendered
Compose output may include the bind password:

```bash
docker compose config --quiet
docker compose up -d --build radius-web
docker compose ps
```

The web entrypoint refreshes operator LDAP configuration from the container
environment at each start. After changing `.env`, recreate `radius-web` so that
Docker supplies the new environment; `docker compose restart` alone retains the
old container environment:

```bash
docker compose up -d --force-recreate radius-web
```

The bind password is consumed from the environment at runtime and is not
written to the generated PHP configuration. The current Compose file does not
implement Docker `secrets:` or a `_FILE` variable. Values supplied through
`.env` become container environment variables and are visible to principals
with Docker inspection privileges. Protect `.env`, limit Docker access, and use
an external deployment/secret mechanism to populate the environment when
required by local policy.

Verify the module inside the actual web container:

```bash
docker compose exec -T radius-web \
  php -r 'exit(extension_loaded("ldap") ? 0 : 1);'
```

Do not print the full container environment, `.env`, rendered Compose
configuration, or `docker inspect` output while a bind password is present.

## Debian installation

Install the LDAP extension and common diagnostic clients:

```bash
sudo apt update
sudo apt install php-ldap ldap-utils ca-certificates
sudo systemctl restart apache2
```

If several PHP versions are installed, ensure the LDAP package matches the
version used by Apache or PHP-FPM. Confirm from the web runtime or its loaded
module configuration, not only from a different CLI version.

### Installing a private CA on Debian

```bash
sudo install -m 0644 corporate-ldap-ca.crt \
  /usr/local/share/ca-certificates/corporate-ldap-ca.crt
sudo update-ca-certificates
sudo systemctl restart apache2
```

Use either the installed CA file or the system bundle in
`CONFIG_OPERATOR_AUTH_LDAP_TLS_CA_FILE`. The explicit CA path must be readable
by the web process.

### Supplying the bind password to Apache on Debian

Keep non-secret settings in `daloradius.conf.php`. Store the bind password in a
root-readable environment file, for example:

```text
/etc/daloradius/operator-ldap.env
```

Example file content:

```dotenv
DALORADIUS_LDAP_BIND_PASSWORD=replace-with-the-service-account-secret
```

Protect it:

```bash
sudo chown root:root /etc/daloradius/operator-ldap.env
sudo chmod 600 /etc/daloradius/operator-ldap.env
```

Add a systemd drop-in with `sudo systemctl edit apache2`:

```ini
[Service]
EnvironmentFile=/etc/daloradius/operator-ldap.env
```

Then apply it:

```bash
sudo systemctl daemon-reload
sudo systemctl restart apache2
```

For PHP-FPM, place the variable in the PHP-FPM service or pool environment and
restart the relevant `php*-fpm` service. Confirm that the pool does not clear the
required variable before PHP executes.

## AlmaLinux installation

Install the extension and diagnostic tools:

```bash
sudo dnf install php-ldap openldap-clients ca-certificates
sudo systemctl restart httpd
```

### Installing a private CA on AlmaLinux

```bash
sudo install -m 0644 corporate-ldap-ca.crt \
  /etc/pki/ca-trust/source/anchors/corporate-ldap-ca.crt
sudo update-ca-trust
sudo systemctl restart httpd
```

### Supplying the bind password to Apache on AlmaLinux

Create `/etc/daloradius/operator-ldap.env` as described for Debian, owned by
`root:root` with mode `0600`. Add the equivalent drop-in with
`sudo systemctl edit httpd`:

```ini
[Service]
EnvironmentFile=/etc/daloradius/operator-ldap.env
```

Apply it:

```bash
sudo systemctl daemon-reload
sudo systemctl restart httpd
```

On SELinux-enforcing systems, permit the web service to initiate the LDAP/TLS
network connection:

```bash
sudo setsebool -P httpd_can_network_connect 1
```

Keep the CA under the standard trust-store path so it receives an appropriate
SELinux context. If PHP-FPM is used, configure and restart its service/pool as
well.

## Active Directory configuration

A typical AD setup uses:

- `sAMAccountName` for short login names;
- `userPrincipalName` when operators enter `user@example.org`;
- `objectGUID` as the stable external identifier;
- `memberOf` for direct group membership;
- matching rule OID `1.2.840.113556.1.4.1941` for recursive group membership.

Example filter for short names:

```text
(&(objectClass=user)(sAMAccountName={username}))
```

Example filter for email-style UPN login:

```text
(&(objectClass=user)(userPrincipalName={username}))
```

Do not place a raw username directly in a custom filter. The implementation
escapes the submitted value before replacing `{username}`. Filters without a
usable placeholder are harder to reason about and should be avoided.

For direct membership, leave the matching rule empty. For nested groups:

```php
$configValues['CONFIG_OPERATOR_AUTH_LDAP_GROUP_ATTRIBUTE'] = 'memberOf';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_GROUP_MATCHING_RULE'] =
    '1.2.840.113556.1.4.1941';
```

The allowed-group values are full group DNs, not display names.

## Generic LDAP and OpenLDAP configuration

A common OpenLDAP configuration is:

```php
$configValues['CONFIG_OPERATOR_AUTH_LDAP_URI'] = array(
    'ldap://ldap01.example.org:389',
    'ldap://ldap02.example.org:389',
);
$configValues['CONFIG_OPERATOR_AUTH_LDAP_SECURITY'] = 'starttls';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_TLS_VERIFY'] = true;
$configValues['CONFIG_OPERATOR_AUTH_LDAP_TLS_CA_FILE'] =
    '/etc/ssl/certs/corporate-ldap-ca.crt';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_BASE_DN'] =
    'dc=example,dc=org';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_USER_BASE_DN'] =
    'ou=People,dc=example,dc=org';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_BIND_DN'] =
    'uid=svc-daloradius,ou=Services,dc=example,dc=org';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_FILTER'] =
    '(&(objectClass=inetOrgPerson)(uid={username}))';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_EXTERNAL_ID_ATTRIBUTE'] =
    'entryUUID';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_ALLOWED_GROUPS'] = array(
    'cn=daloradius-operators,ou=Groups,dc=example,dc=org',
);
$configValues['CONFIG_OPERATOR_AUTH_LDAP_GROUP_ATTRIBUTE'] = 'memberOf';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_GROUP_MATCHING_RULE'] = '';
```

Group enforcement reads the configured DN-valued membership attribute from the
**user entry**. For OpenLDAP, enable a `memberOf` overlay or expose another
suitable user attribute. This first implementation does not search
`groupOfNames` or `posixGroup` entries directly using `member` or `memberUid`.
If the directory does not expose a suitable user membership attribute, leave
the allowed-group list empty and rely on manual provisioning plus local ACLs
until an appropriate directory strategy is available.

## Multiple LDAP servers

List URIs in priority order:

```php
$configValues['CONFIG_OPERATOR_AUTH_LDAP_URI'] = array(
    'ldaps://dc01.example.org:636',
    'ldaps://dc02.example.org:636',
    'ldaps://dc03.example.org:636',
);
```

All URIs share the same base DN, filter, service account, group policy, TLS
verification setting, and CA file. Keep them in the same identity domain.

Failover occurs for technical connection, TLS, bind transport, and directory
availability errors. It does not turn invalid credentials or a valid directory
denial into a success on another server, and it never changes the configured
TLS level.

## Provisioning an LDAP operator

LDAP authentication proves identity; daloRADIUS remains the authorization
source. Provision each LDAP operator manually:

1. Sign in with a local administrator.
2. Open **Config > Operators > New Operator**.
3. Set **Username** to the exact login value matched by the LDAP filter.
4. Set **Authentication Source** to **LDAP**.
5. Leave the local password unused; LDAP passwords are not stored.
6. Optionally leave **External ID** empty for the first login. daloRADIUS records
   the stable LDAP identifier after successful authentication.
7. Assign the required local operator ACLs.
8. Save the operator.
9. Test in a separate private browser session and explicitly choose **LDAP**.

Do not create a Local and an LDAP operator with the same username. Changing an
existing operator between Local and LDAP requires explicit confirmation in the
edit form. Conversion to LDAP clears local password material; conversion to
Local requires a new local password.

After LDAP first-factor authentication, existing TOTP and recovery-code logic
runs unchanged. Enroll and test MFA separately for the LDAP operator.

## Safe rollout

1. Apply the database migration.
2. Install `php-ldap` and the required CA chain.
3. Keep Local enabled, LDAP disabled, and the default set to Local.
4. Configure one directory URI and a least-privilege service account.
5. Provision a dedicated LDAP test operator with minimal ACLs.
6. Enable LDAP while keeping Local enabled.
7. Test successful LDAP login, wrong password, denied group, unavailable LDAP,
   MFA, and local break-glass login in separate sessions.
8. Add secondary LDAP URIs and verify technical failover.
9. Change the displayed default to LDAP only after repeated successful tests.
10. Keep a documented and monitored local recovery account.

The operator login currently has no built-in IP-and-username rate limiter.
Apply an appropriate limit at the reverse proxy or WAF and monitor directory
lockout/audit events.

## Troubleshooting

The browser deliberately displays a generic authentication failure. Use server
logs to distinguish causes, but never log passwords, bind secrets, TOTP values,
or recovery codes.

### LDAP extension missing

Install the LDAP module for the PHP runtime actually serving daloRADIUS, restart
Apache/PHP-FPM, and confirm the module is loaded. LDAP being disabled must not
require the extension.

### Directory unavailable

Check DNS and TCP reachability from the web container or web host, not only from
your workstation. Verify timeout, URI order, routing, firewall policy, and that
the security mode matches every URI scheme.

Local login remains independent and should still work when LDAP is down.

### TLS or certificate validation failure

Check:

- URI hostname against certificate SAN;
- port and security mode;
- CA chain and CA-file path;
- CA permissions inside the container or web host;
- system clock;
- SELinux denials on AlmaLinux.

Do not solve a production certificate problem by leaving TLS verification
disabled.

### Service bind fails

Confirm the full bind DN, the non-empty runtime secret, account status, and
least-privilege read permissions. In Docker, an empty
`DALORADIUS_LDAP_BIND_PASSWORD` does not override a configured PHP-file value.
Recreate the web container after changing its environment.

### Search returns no user or several users

Check the user base DN, object class, login attribute, and filter. The search
must return exactly one user. Test `uid`, `sAMAccountName`, or
`userPrincipalName` according to the directory. Special characters in the
submitted username are escaped by the provider.

### Valid directory user is denied

Check all of the following:

- a local operator with the exact username exists;
- its authentication source is LDAP;
- the allowed group DN is exact;
- the configured membership attribute exists on the user entry;
- the AD matching rule is enabled only when nested groups are intended;
- the stable external ID matches the one previously linked to the operator;
- local ACLs permit the requested daloRADIUS page;
- local TOTP or recovery-code verification succeeds when enabled.

### Docker settings do not change

Validate JSON arrays, then recreate `radius-web` with
`docker compose up -d --force-recreate radius-web`. A plain
`docker compose restart` reuses the environment captured when the container was
created. Check the effective generated configuration without printing the bind
password or the complete container environment.

### LDAP works but daloRADIUS reports missing RADIUS tables

Operator LDAP authentication and the normal FreeRADIUS schema are separate. A
complete installation still requires the standard FreeRADIUS SQL schema before
the daloRADIUS schema. Errors such as `Table 'radius.radcheck' doesn't exist`
mean the deployment database initialization is incomplete, not that LDAP bind
failed. Follow the platform installation guide and import schemas in the
required order.

## Recovery

If LDAP is unavailable, explicitly choose the Local provider and use the tested
local recovery operator. Do not implement an automatic LDAP-to-Local fallback.
After recovery, restore directory connectivity, review authentication logs,
and rotate any credential that may have been exposed during troubleshooting.
