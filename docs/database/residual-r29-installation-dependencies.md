# R29 — Remove PEAR DB from installations

## Scope and decisions

Base: `a16c68f378df02ed8d5a9d26668873fdc01ae640`, branch `refactor/pdo`.
Remove PEAR DB from Docker/classic dependency lists and installation instructions;
retain PEAR Mail/Mime and native PDO MySQL. No application logic, database schema,
live service, scheduler, or existing-data deployment was changed.

Lot 5 (a new CI gate) is explicitly waived. The standalone smoke test is not wired
into CI. Lot 6 (global final-source validation and preserved-data upgrades) remains
separate; this report does not claim release-wide acceptance.

## Changes

- Dockerfile: remove `php-db`, verify PDO MySQL during build, explicitly install
  `php-net-smtp` with the retained `php-mail` and `php-mail-mime` packages.
- Debian installer: remove `php-db`, retain mail components and add explicit CLI/
  SMTP dependencies; fail before continuing if PDO MySQL is unavailable.
- Debian/AlmaLinux guides and README files: document PDO requirements, driver
  verification and retained mail dependencies. AlmaLinux retains `php-pdo` and
  `php-mysqlnd` and installs Mail/Mime with `--alldeps`, never PEAR DB.
- Existing password-storage PHP test: remove its unused `DB.php` include and
  obsolete `PEAR_ERROR_RETURN` definition; retain every assertion.
- Add a standalone native Docker/Apache/MariaDB/MIME-SMTP dependency smoke test.
- Upgrade and rollback notes: `doc/setup/pdo-database-upgrade.md`.

## Checked results

| Verification | Level | Result |
| --- | --- | --- |
| Docker build with PDO MySQL guard | Real image build from tracked source plus reviewed overlays, no live configuration | PASS |
| Debian installer's dependency function | Real package installation in a fresh Debian container; not the complete installer | PASS |
| AlmaLinux guide PHP/PEAR dependency commands | Real package installation in a fresh AlmaLinux 10 container; not a full web/database install | PASS |
| Missing-driver and package-install refusal branches | Synthetic shell injections into the actual dependency function | PASS |
| PEAR DB package/entrypoint absent and PDO available | Native rebuilt image/Apache checks | PASS |
| Both `mysql` and `mysqli` labels: read, transaction, rollback | Native PHP/PDO and disposable MariaDB | PASS |
| Nonempty accounting endpoint and redacted SQL error | Actual Apache operator endpoint, seeded session/ACL, disposable SQL | PASS |
| MIME notification accepted by an internal SMTP receiver | Actual PEAR Mail/Mime/Net_SMTP, no external delivery | PASS |
| PHP log capture and absence of unexpected diagnostics | Positive marker plus Apache file/container log inspection | PASS |
| PDO connection, portal password, portal password storage, notification rendering PHP tests | Actual PHP in the PDO-only image; focused tests, not password-login or external-mail flows | PASS |
| Shell syntax, Python AST, whitespace, unchanged app/contrib/CI diff | Static | PASS |
| Disposable containers/network/context cleanup | Native resource inventory | PASS |

Sanitized checked results and source/image hashes are stored outside the repository
in the lab validation directory; no connection settings, sessions or credentials
are included in them.

## Defects found during verification

The initial rebuilt image lacked Net_SMTP: Debian `php-mail` only recommends
`php-net-smtp`, which `--no-install-recommends` omits. Sending a real fixture message
failed in `Mail/smtp.php`. Explicit installation fixes this; the SMTP test was
replayed successfully. AlmaLinux `--alldeps` likewise retains the SMTP transport
and dependencies. PEAR DB was not reintroduced.

The password-storage test still loaded unused `DB.php`, producing warnings despite
passing its assertions. Removing the stale include/constant makes the unchanged
assertions pass without PEAR DB and without diagnostics.

The Apache harness required a www-data-owned seeded session and actual Apache
file-log inspection; a root CLI session file and Docker stderr alone were not
adequate. Those were fixture corrections, not application/session changes.

## Limits

No full classic installation, preserved-data upgrade, browser password login,
external SMTP mailbox/TLS/authentication, payment-provider exchange, FreeRADIUS/NAS
hardware or production deployment is certified by R29. AlmaLinux's PEAR dependency
installation emits an upstream optional Auth_SASL deprecation warning; SMTP
authentication is not claimed tested. Historical PEAR baselines stay separate.

The installer stops requesting PEAR DB but does not purge shared system packages
on existing machines. Docker upgrades require rebuilding/replacing the web image,
not just updating source. No database volume is deleted. A rollback to PEAR-based
code requires the matching older runtime/packages, not just a source rollback.

Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.
