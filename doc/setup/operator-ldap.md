# Operator LDAP authentication

This guide explains how to connect the **daloRADIUS operators portal** to
Active Directory or another LDAP directory.

LDAP authentication is optional and disabled by default. Local and LDAP
operators can coexist, and daloRADIUS never falls back automatically from one
provider to the other.

An LDAP operator still has a local daloRADIUS record for:

- ACLs and locations;
- TOTP MFA and recovery codes;
- sessions, login history, and auditing.

The operator password is checked by LDAP and is not stored by daloRADIUS. Keep
at least one tested local administrator account for recovery.

## Requirements

- PHP LDAP extension in the web runtime;
- network access from daloRADIUS to the LDAP servers;
- a read-only LDAP service account;
- the issuing CA certificate for StartTLS or LDAPS;
- the operator LDAP database migration on existing installations.

A CLI check is useful, although Apache or PHP-FPM may load a different PHP
configuration:

```bash
php -m | grep -i '^ldap$'
```

## Database migration

Fresh installations already include the required columns. For an existing
installation, run this command from the repository root:

```bash
mariadb -u DB_USER -p DB_NAME \
  < contrib/db/migrations/2026-09-operator-ldap.sql
```

Use the database name configured for the installation. Docker commonly uses
`radius`; the Debian and AlmaLinux guides use `raddb`.

The migration is idempotent and preserves existing local operators. The Docker
web container applies it automatically.

## Configuration reference

Native installations configure LDAP in:

```text
app/common/includes/daloradius.conf.php
```

Docker deployments use the corresponding environment variables.

| PHP key | Docker variable | Default / description |
| --- | --- | --- |
| `CONFIG_OPERATOR_AUTH_LOCAL_ENABLED` | `DALORADIUS_OPERATOR_AUTH_LOCAL_ENABLED` | `true`; enables local operator passwords. |
| `CONFIG_OPERATOR_AUTH_LDAP_ENABLED` | `DALORADIUS_OPERATOR_AUTH_LDAP_ENABLED` | `false`; enables LDAP authentication. |
| `CONFIG_OPERATOR_AUTH_DEFAULT` | `DALORADIUS_OPERATOR_AUTH_DEFAULT` | `local`; provider selected by default in the login form. |
| `CONFIG_OPERATOR_AUTH_LDAP_URI` | `DALORADIUS_LDAP_URI` | Ordered LDAP URI list. Docker uses a JSON array. |
| `CONFIG_OPERATOR_AUTH_LDAP_SECURITY` | `DALORADIUS_LDAP_SECURITY` | `plain`, `starttls`, or `ldaps`. |
| `CONFIG_OPERATOR_AUTH_LDAP_TLS_VERIFY` | `DALORADIUS_LDAP_TLS_VERIFY` | `true`; validates the LDAP server certificate. |
| `CONFIG_OPERATOR_AUTH_LDAP_TLS_CA_FILE` | `DALORADIUS_LDAP_TLS_CA_FILE` | CA file path visible to PHP. |
| `CONFIG_OPERATOR_AUTH_LDAP_BASE_DN` | `DALORADIUS_LDAP_BASE_DN` | General search base. |
| `CONFIG_OPERATOR_AUTH_LDAP_USER_BASE_DN` | `DALORADIUS_LDAP_USER_BASE_DN` | Optional narrower base for operator users. |
| `CONFIG_OPERATOR_AUTH_LDAP_BIND_DN` | `DALORADIUS_LDAP_BIND_DN` | Read-only service-account DN. |
| `CONFIG_OPERATOR_AUTH_LDAP_BIND_PASSWORD` | `DALORADIUS_LDAP_BIND_PASSWORD` | Service-account password. A non-empty environment value overrides the PHP value. |
| `CONFIG_OPERATOR_AUTH_LDAP_FILTER` | `DALORADIUS_LDAP_FILTER` | User filter containing `{username}`. |
| `CONFIG_OPERATOR_AUTH_LDAP_EXTERNAL_ID_ATTRIBUTE` | `DALORADIUS_LDAP_EXTERNAL_ID_ATTRIBUTE` | Stable identifier: normally `objectGUID` for AD or `entryUUID` for OpenLDAP. Set it explicitly when enabling LDAP. |
| `CONFIG_OPERATOR_AUTH_LDAP_TIMEOUT` | `DALORADIUS_LDAP_TIMEOUT` | `5`; request-wide timeout, clamped to 1–30 seconds. |
| `CONFIG_OPERATOR_AUTH_LDAP_ALLOWED_GROUPS` | `DALORADIUS_LDAP_ALLOWED_GROUPS` | Optional allowed group DN list. Docker uses a JSON array. |
| `CONFIG_OPERATOR_AUTH_LDAP_GROUP_ATTRIBUTE` | `DALORADIUS_LDAP_GROUP_ATTRIBUTE` | `memberOf`; user membership attribute. |
| `CONFIG_OPERATOR_AUTH_LDAP_GROUP_MATCHING_RULE` | `DALORADIUS_LDAP_GROUP_MATCHING_RULE` | Optional AD nested-group matching rule. |

## Transport security

The security mode must match the URI scheme:

| Mode | URI | Usage |
| --- | --- | --- |
| `plain` | `ldap://server:389` | Unencrypted; use only on an isolated legacy or test network. |
| `starttls` | `ldap://server:389` | Requires a successful TLS upgrade before binding. |
| `ldaps` | `ldaps://server:636` | TLS from connection start; recommended when available. |

Keep certificate verification enabled in production. The URI hostname must
match the certificate, and the CA file must be readable by the web runtime.

## Active Directory example

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

Use `userPrincipalName` instead of `sAMAccountName` when operators should sign
in as `user@example.org`:

```text
(&(objectClass=user)(userPrincipalName={username}))
```

Leave `CONFIG_OPERATOR_AUTH_LDAP_GROUP_MATCHING_RULE` empty if only direct AD
group membership should be accepted.

## OpenLDAP example

```php
$configValues['CONFIG_OPERATOR_AUTH_LOCAL_ENABLED'] = true;
$configValues['CONFIG_OPERATOR_AUTH_LDAP_ENABLED'] = true;
$configValues['CONFIG_OPERATOR_AUTH_DEFAULT'] = 'ldap';

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
$configValues['CONFIG_OPERATOR_AUTH_LDAP_BIND_PASSWORD'] = '';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_FILTER'] =
    '(&(objectClass=inetOrgPerson)(uid={username}))';
$configValues['CONFIG_OPERATOR_AUTH_LDAP_EXTERNAL_ID_ATTRIBUTE'] =
    'entryUUID';
```

Group restrictions require a DN-valued membership attribute on the user entry,
such as `memberOf`. This implementation does not search `posixGroup` or
`groupOfNames` entries directly through `memberUid` or `member`.

## Docker Compose

Copy the example environment file and restrict access to it:

```bash
cp .env.example .env
chmod 600 .env
```

Example Active Directory settings:

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

URI and group lists must be valid single-line JSON arrays.

### Mounting a private CA

A host path is not automatically visible inside the container. Add a read-only
mount to `radius-web`:

```yaml
services:
  radius-web:
    volumes:
      - ./certs/corporate-ldap-ca.crt:/etc/ssl/certs/corporate-ldap-ca.crt:ro
```

The path in `DALORADIUS_LDAP_TLS_CA_FILE` must be the container path.

Apply changes by recreating the web container:

```bash
docker compose config --quiet
docker compose up -d --build --force-recreate radius-web
```

A simple `docker compose restart` does not reload values changed in `.env`.
The current Compose file passes the bind password as an environment variable;
it does not implement Docker `secrets:` or a `_FILE` variable. Protect `.env`
and restrict Docker access.

Verify the LDAP extension in the web container:

```bash
docker compose exec -T radius-web \
  php -r 'exit(extension_loaded("ldap") ? 0 : 1);'
```

## Debian

Install the LDAP extension and CA tools:

```bash
sudo apt update
sudo apt install php-ldap ldap-utils ca-certificates
```

For a private CA:

```bash
sudo install -m 0644 corporate-ldap-ca.crt \
  /usr/local/share/ca-certificates/corporate-ldap-ca.crt
sudo update-ca-certificates
```

Put the non-secret configuration in `daloradius.conf.php`. Store the bind
password in `/etc/daloradius/operator-ldap.env`:

```dotenv
DALORADIUS_LDAP_BIND_PASSWORD=CHANGE_ME_LDAP_BIND_PASSWORD
```

Protect the file:

```bash
sudo chown root:root /etc/daloradius/operator-ldap.env
sudo chmod 600 /etc/daloradius/operator-ldap.env
```

Add an Apache systemd drop-in with `sudo systemctl edit apache2`:

```ini
[Service]
EnvironmentFile=/etc/daloradius/operator-ldap.env
```

Then restart Apache:

```bash
sudo systemctl daemon-reload
sudo systemctl restart apache2
```

If PHP-FPM is used, supply the variable through its service or pool environment
and restart the matching `php*-fpm` service.

## AlmaLinux

Install the LDAP extension and CA tools:

```bash
sudo dnf install php-ldap openldap-clients ca-certificates
```

For a private CA:

```bash
sudo install -m 0644 corporate-ldap-ca.crt \
  /etc/pki/ca-trust/source/anchors/corporate-ldap-ca.crt
sudo update-ca-trust
```

Create `/etc/daloradius/operator-ldap.env` as in the Debian section, owned by
`root:root` with mode `0600`. Add an HTTPD systemd drop-in with
`sudo systemctl edit httpd`:

```ini
[Service]
EnvironmentFile=/etc/daloradius/operator-ldap.env
```

Allow the web service to contact LDAP when SELinux is enforcing, then restart
HTTPD:

```bash
sudo setsebool -P httpd_can_network_connect 1
sudo systemctl daemon-reload
sudo systemctl restart httpd
```

Configure and restart PHP-FPM as well when the installation uses it.

## Creating an LDAP operator

LDAP validates the password, but daloRADIUS still controls authorization.
Create one local operator record for each LDAP operator:

1. Sign in with a local administrator.
2. Open **Config > Operators > New Operator**.
3. Enter the exact username expected by the LDAP filter.
4. Select **LDAP** as the authentication source.
5. Leave the local password unused.
6. Leave **External ID** empty for first-login linking, or enter the known stable
   directory ID.
7. Assign the required ACLs and location.
8. Save, then test in a separate private browser session and choose **LDAP**.

Do not create Local and LDAP operators with the same username. TOTP MFA and
recovery codes can be enabled for LDAP operators in the same way as for local
operators.

## Verification and troubleshooting

Test all of the following before production use:

- successful LDAP login;
- wrong password and unauthorized group rejection;
- local recovery login while LDAP is unavailable;
- TOTP and recovery codes when MFA is enabled;
- secondary LDAP server failover;
- certificate rejection with an untrusted CA.

Common checks:

- **LDAP option missing:** install `php-ldap` for the PHP version used by the web
  server and restart Apache/PHP-FPM.
- **TLS failure:** check URI hostname, certificate SAN, CA path, permissions, and
  system time. Do not disable certificate verification in production.
- **No user found:** check the user base DN, filter, and login attribute. The
  search must return exactly one entry.
- **Group denied:** use full group DNs and confirm `memberOf` is present on the
  user entry. Use the AD matching-rule OID only for nested AD groups.
- **LDAP user denied after a valid bind:** confirm that a daloRADIUS operator
  with the same username exists and uses the LDAP authentication source.
- **Docker settings unchanged:** recreate `radius-web`; do not use only
  `docker compose restart` after changing `.env`.
- **Missing `radcheck` or other RADIUS tables:** initialize the normal
  FreeRADIUS SQL schema. This is separate from LDAP operator authentication.

The browser intentionally displays a generic authentication error. Inspect
server logs for the internal reason, but never log passwords, bind secrets,
TOTP values, or recovery codes.

Apply login rate limiting at the reverse proxy or WAF and retain a tested local
administrator account for recovery.
