# PDO-only database installation and upgrade

Database access uses native PDO. The Docker, Debian installer and Debian/AlmaLinux
installation guides target MariaDB through `pdo_mysql`. Existing `mysql` and
`mysqli` configuration labels both select PDO MySQL; they no longer select PEAR
DB. Other database engines are not certified end-to-end by these installation
paths.

## Dependencies

- Debian/Docker: retain `php-mysql`, which supplies `pdo_mysql`, and PHP CLI for
  maintenance commands. The Docker build and Debian installer check that PDO and
  the MySQL driver are available.
- AlmaLinux: install `php-pdo` and `php-mysqlnd` as documented in the installation
  guide, then verify the available PDO driver.
- Do not install `php-db` or the PEAR `DB` package for this application revision.
- Keep PEAR Mail and Mail_Mime for notifications. Debian/Docker explicitly install
  `php-net-smtp` because `--no-install-recommends` otherwise omits the SMTP
  transport. The AlmaLinux guide uses `pear install --alldeps Mail Mail_Mime` to
  retain Net_SMTP and its dependencies. Do not remove all PEAR packages.

Check the CLI driver without printing connection settings:

```bash
php -r 'exit(class_exists("PDO") && in_array("mysql", PDO::getAvailableDrivers(), true) ? 0 : 1);'
```

A nonzero result means the required driver is unavailable. Verify the extension in
the Apache/FPM SAPI as well when that SAPI uses a different PHP version or module
configuration. The installer CLI check alone does not prove the web SAPI works.

## Existing installations

1. Preserve application configuration and data using the administrator's normal
   secure backup process; do not put them in source control or a test report.
2. Update the complete application source together, including retained Chilli
   examples' shared PDO provider dependencies. Custom code must use PDO handles;
   the retired common `db_open.php`, `db_close.php` and DB callback are not aliases
   for the new provider. See the lot-3 compatibility documentation.
3. Install/enable the PDO MySQL extension and retain the mail dependencies above.
4. Docker: rebuild the web image and replace only the web container as appropriate
   to the deployment. Updating source alone does not remove packages from an older
   image. Do not delete database files or volumes.
5. Classic installs: the new package list stops requesting PEAR DB; it does not
   forcibly uninstall shared system packages from an existing machine. Only after
   verifying that no other application or historical test runtime needs it may an
   administrator remove `php-db` or uninstall the PEAR DB package. Never remove
   `php-pear`, Mail or Mail_Mime indiscriminately.
6. Recheck operator/user routes, scheduled maintenance and notification delivery.
   Apply relevant existing SQL feature migrations normally; removing the database
   client dependency itself introduces no schema/data migration.

Rolling back to application code that still uses PEAR DB also requires its matching
older image or packages. A source-only rollback onto this PDO-only runtime is not
sufficient. Keep historical PEAR regression baselines in a separate legacy test
runtime, not in the new production image.

## Isolated verification

From a clean source checkout/export, with no live configuration or secrets in the
build context:

```bash
docker build -t daloradius-pdo-r29:local .
python3 tests/installation_pdo_dependencies.py --image daloradius-pdo-r29:local
```

The standalone test uses disposable MariaDB data in tmpfs and an internal Docker
network without published ports. It checks PEAR DB package/entrypoint absence,
native PDO reads and rollback for both configuration labels, the actual Apache
accounting endpoint with nonempty totals and a redacted SQL error, and PEAR
Mail/Mime delivery to an isolated SMTP receiver. Its operator session is seeded;
this is not a password-login test. Configuration/session values are confined to
the disposable container and removed with it. No external mailbox, SMTP TLS/auth,
FreeRADIUS/NAS hardware or live database is involved.

No CI gate is introduced: lot 5 was explicitly waived. The global lot-6 fresh
installations, preserved-data upgrade and cross-domain regression remain separate
release acceptance work.

Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.
