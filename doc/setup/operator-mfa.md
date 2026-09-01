# Operator two-factor authentication recovery

This guide explains how to reset two-factor authentication (2FA/MFA) for a daloRADIUS operator account from the server command line. Use it when an operator has lost access to their authenticator app or recovery codes and cannot complete login.

Resetting MFA does **not** change the operator password and does **not** delete the operator account. It only clears the TOTP secret, last accepted counter, confirmation timestamp, and recovery codes. The operator can then log in with their existing password and enroll MFA again from the operators interface.

## Before you start

1. Log in to the server that hosts the daloRADIUS database.
2. Identify the operator username to reset.
3. Identify the daloRADIUS database name, user, and operators table name.

For standard installations these values are configured in:

```text
app/common/includes/daloradius.conf.php
```

The relevant settings are:

```php
$configValues['CONFIG_DB_USER'] = 'raduser';
$configValues['CONFIG_DB_PASS'] = 'radpass';
$configValues['CONFIG_DB_NAME'] = 'raddb';
$configValues['CONFIG_DB_TBL_DALOOPERATORS'] = 'operators';
```

If your installation uses different values, replace the example database name and table name in the commands below.

## Upgrading an existing installation

Fresh installations already include the operator MFA schema in `contrib/db/mariadb-daloradius.sql`. Existing installations must apply the database migration before operators enable MFA or use the MFA recovery page.

Back up the database first, then run the migration script from the daloRADIUS source tree.

For a standard MariaDB/MySQL installation:

```bash
mariadb -u raduser -p raddb < contrib/db/migrations/2026-06-operator-totp-mfa.sql
```

For a Docker Compose installation, run this from the directory that contains `docker-compose.yml`:

```bash
docker compose exec -T radius-mysql sh -lc 'mariadb -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' \
  < contrib/db/migrations/2026-06-operator-totp-mfa.sql
```

The migration is idempotent: it adds the missing MFA columns and registers the MFA page in the operators ACL metadata if they are not already present.

## Standard MariaDB/MySQL installation

In the examples below, replace `administrator` with the operator username you want to unlock.

Check that the operator exists and confirm their current MFA state:

```bash
mariadb -u raduser -p raddb <<'SQL'
SELECT id, username, totp_enabled, totp_confirmed_at
FROM operators
WHERE username = 'administrator';
SQL
```

Reset MFA for that operator:

```bash
mariadb -u raduser -p raddb <<'SQL'
UPDATE operators
   SET totp_enabled = 0,
       totp_secret = NULL,
       totp_last_counter = NULL,
       totp_confirmed_at = NULL,
       totp_recovery_codes = NULL
 WHERE username = 'administrator';
SQL
```

Verify the reset:

```bash
mariadb -u raduser -p raddb <<'SQL'
SELECT id, username, totp_enabled, totp_confirmed_at
FROM operators
WHERE username = 'administrator';
SQL
```

The `totp_enabled` value should now be `0`.

> **Note:** If the operator username contains a single quote (`'`), escape it for SQL by replacing it with two single quotes (`''`) before running the commands.

## Docker Compose installation

Run these commands from the directory that contains `docker-compose.yml`. Replace `administrator` with the operator username you want to unlock.

Check that the operator exists:

```bash
docker compose exec -T radius-mysql sh -lc 'mariadb -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' <<'SQL'
SELECT id, username, totp_enabled, totp_confirmed_at
FROM operators
WHERE username = 'administrator';
SQL
```

Reset MFA for that operator:

```bash
docker compose exec -T radius-mysql sh -lc 'mariadb -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' <<'SQL'
UPDATE operators
   SET totp_enabled = 0,
       totp_secret = NULL,
       totp_last_counter = NULL,
       totp_confirmed_at = NULL,
       totp_recovery_codes = NULL
 WHERE username = 'administrator';
SQL
```

Verify the reset:

```bash
docker compose exec -T radius-mysql sh -lc 'mariadb -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' <<'SQL'
SELECT id, username, totp_enabled, totp_confirmed_at
FROM operators
WHERE username = 'administrator';
SQL
```

The operator can now log in with their existing password. Ask them to re-enable two-factor authentication and store their new recovery codes.
