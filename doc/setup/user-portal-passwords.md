# User portal password storage and upgrade

User portal credentials are independent from FreeRADIUS authentication credentials. The **Allow cleartext password attributes in db** setting only controls whether cleartext RADIUS attributes such as `Cleartext-Password` may be stored in `radcheck`; it does not control the user portal password.

The user portal password is always stored as a one-way hash produced by PHP's `password_hash()` with `PASSWORD_DEFAULT` and is verified with `password_verify()`. Stored values carry the application marker `$dalo$portal$v1$`, which distinguishes them from legacy plaintext and identifies the verification format independently of PHP's algorithm marker. Operators can replace a portal password, but the stored value is never displayed or pre-filled.

The `$dalo$portal$v1$` namespace is reserved for daloRADIUS-generated values. Before enabling this feature on an existing database, administrators should check for legacy plaintext values that already begin with this marker; a marker followed by a syntactically valid PHP password hash is treated as a stored hash.

## Upgrading an existing installation

Back up the database before upgrading. Apply the schema migration before deploying the updated application:

```sh
mariadb -u raduser -p raddb \
  < contrib/db/migrations/2026-09-user-portal-password-hashing.sql
```

For Docker Compose, run from the directory containing `docker-compose.yml`:

```sh
docker compose exec -T radius-mysql sh -lc \
  'mariadb -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' \
  < contrib/db/migrations/2026-09-user-portal-password-hashing.sql
```

The migration widens `userinfo.portalloginpassword` to `VARCHAR(255)`. It is idempotent and does not transform existing values.

After deploying the updated application, inspect how many legacy values remain:

```sh
php contrib/scripts/maintenance/hash-user-portal-passwords.php --dry-run
```

Then convert them:

```sh
php contrib/scripts/maintenance/hash-user-portal-passwords.php
```

The command processes records in bounded batches, skips empty and already-marked values, and does not print usernames, passwords, hashes, or database debug queries containing them. It is safe to run again. Unmarked values, including strings that happen to look like PHP password hashes, are treated as legacy plaintext. Use `--batch-size=N` to select a batch size between 1 and 1000.

Legacy plaintext values also migrate automatically after the next successful portal login. New or changed portal passwords are always hashed.

## Operator behavior

On an existing user's edit page, the portal password field is intentionally empty:

- leave it empty to preserve the current credential;
- enter a value to replace the credential with a new hash;
- disabling portal login does not reveal or delete the existing hash;
- portal login cannot be enabled until a credential exists.

## Rollback

The schema widening is backward compatible, but older daloRADIUS versions compare portal passwords directly in SQL and cannot authenticate records that have already been hashed. After converting values, rolling back the application requires restoring the pre-upgrade database backup or resetting affected portal passwords.
