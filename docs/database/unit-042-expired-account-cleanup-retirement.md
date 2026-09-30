# UNIT-042 — retire unsafe legacy expired-account cleanup

## Disposition and scope

The inventory allows an **audit/deprecate or migrate** disposition for `contrib/scripts/maintenance/cleanExpiredAccounts.php`. The user explicitly selected **deprecation**, after native MariaDB probes reproduced unsafe expiration selections. This is an intentional breaking change, **not a PDO cleanup implementation**.

The historical entry point is retained as a fail-closed tombstone:

- PHP CLI prints `Legacy expired-account cleanup is retired. No accounts were deleted.` to stderr, leaves stdout empty and exits with status **1** so cron/wrappers cannot interpret retirement as successful cleanup.
- HTTP requests return **410 Gone** with the same fixed plain-text message, `Cache-Control: no-store` and `X-Content-Type-Options: nosniff`. HEAD has no response body. There is no redirect or session cookie.
- The script loads no configuration or PEAR/PDO driver, opens no database connection, processes no request fields or command-line flags, and performs no selection or deletion. `--force`, `--execute`, `--dry-run` and `--help` do not bypass retirement or implement new modes.
- The legacy connection, selection and deletion functions are removed. No repository caller of these maintenance helpers was found; their generic names are not retained as callable database APIs.
- No schema, migration SQL, shared database helper, other maintenance script, cron job, service configuration or deployment is changed.

## Native legacy evidence

The pinned baseline is commit `c86140442bc475e4b3de3404b8e61ff70ba9eb89`. Its selectors are unsafe independently of the database client:

1. **Time-To-Finish:** the HAVING condition compares an accounting start's Unix epoch to the current Unix epoch, rather than comparing elapsed time with the plan's bank. In the fixture, an account started 20 minutes ago with a 3,600-second bank is selected and actually deleted before the bank is exhausted.
2. **Due login:** old accounting rows are filtered before grouping by username. An account with a 100-day-old record and a login yesterday is still selected and deleted by the 90-day cleanup. The recent record does not protect it.
3. **Write sequence:** the legacy script deletes `radacct`, `userbillinfo`, `userinfo`, `radcheck` and `radreply` separately, without a transaction or a checked materialization step. Retrying or adding PDO does not itself fix the deletion policy.

`tests/expired_accounts_retirement.py` executes the **unmodified** baseline script against isolated MariaDB twice: through PHP CLI and through a PHP HTTP server. Both delete the two wrongly selected fixture accounts, alongside legitimately selected fixture accounts. Ordinary retained accounts, including a quoted/Unicode/percent/plus username, remain. The untouched `radusergroup` and `billing_history` fixtures also remain, demonstrating the old limited deletion surface rather than a complete account-removal policy.

To exercise that full baseline flow, the test supplies the historical `library/config_read.php` **only inside the disposable fixture**, referencing ephemeral container environment. It does not repair or modify the baseline PHP source or add a compatibility loader to the repository. The current checkout does not contain that historical loader. A separate baseline run without it is asserted to print a database connection error and exit **0**, with no database changes. That startup failure is characterized separately; it is not presented as a working baseline, behavioral parity or evidence that every historical deployment is harmless.

The fixture HTTP reachability demonstrates what happens **if this contrib script is exposed under a web root**. It does not establish that the deployed lab or every installation publicly exposes this path. The tests use minimal tables matching the fields touched by this script, not a full RADIUS installation or production-schema certification.

## Usage audit and operator action

A read-only audit found no references to this script/helpers in other PHP, shell, Markdown or Python files in the inspected repository. On the lab VM, readable system cron directories and `/etc/systemd/system` contained no matching jobs; the cron CLI was not installed. The inspected live checkout had no other matching references and lacked the legacy config loader. This is a scoped lab audit, **not proof that no other installation, external scheduler or custom wrapper uses the script**.

Before deploying:

- Inventory external cron jobs, timers, wrappers and web links, and remove/disable this retired task explicitly. This change does not automatically edit any scheduler. Its nonzero CLI exit can trigger existing failure notifications.
- Withdraw public links/exposure to this script if present. Do not treat HTTP 410 as a successful cleanup response.
- Existing accounts, sessions, RADIUS attributes, user profiles and billing records are unchanged by retirement. Expired accounts are no longer purged by this entry point; use a separately reviewed operator procedure if cleanup is needed.
- Do not restore the legacy selectors or merely exchange PEAR calls for PDO. A replacement must separately define reliable expiration/renewal semantics and retention, protect recently used/active accounts, preview the exact affected accounts, require explicit destructive authorization, validate all participating transactional tables and make dependent writes atomic on one PDO connection.
- Historical incorrect deletions are not automatically repaired. Any recovery requires operator review and authoritative backups/account records.

**Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.**

## Verification

Run from the repository root with Docker and the local test images available:

```sh
EXPIRED_ACCOUNTS_BASELINE=1 PYTHONDONTWRITEBYTECODE=1 python3 tests/expired_accounts_retirement.py
PYTHONDONTWRITEBYTECODE=1 python3 tests/expired_accounts_retirement.py
```

The tests run real PHP CLI/HTTP and MariaDB on a disposable internal Docker network. Database storage is tmpfs and the fixture is mounted read-only to PHP. Only synthetic accounts with no authentication passwords/hashes or real personal data are seeded; no production configuration is copied. Fixture connection settings are supplied through disposable container environment rather than persisted as configuration values.

Candidate assertions:

- CLI status 1, fixed stderr and empty stdout from several working directories, regardless of misleading command-line flags;
- HTTP 410, exact body/headers and no cookies/redirects for GET, POST, HEAD, PUT, DELETE and OPTIONS;
- empty, array-typed, quoted/Unicode, oversized and JSON inputs cannot bypass retirement or be reflected;
- concurrent requests also refuse cleanup;
- exact complete fixture row state remains unchanged in all seven tables: the five historical deletion targets, plus group mappings and billing history;
- legacy/modern configuration and PEAR include tripwires are never executed;
- retirement still works after removing those files and stopping the fixture database;
- candidate PHP logs contain no warning/fatal/driver errors or submitted body marker;
- fixture trees, containers, database and internal network are removed and their absence is checked.

No production script execution, real account deletion, live RADIUS authentication, production database access or deployment is claimed. This unit removes an unsafe legacy mutation surface; it does not provide replacement expiration functionality or convert unrelated PEAR callers.
