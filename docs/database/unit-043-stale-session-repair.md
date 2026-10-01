# UNIT-043 — PDO stale-session repair

## Scope and atomicity decision

`contrib/scripts/maintenance/fix-stale-sessions.php` is migrated from PEAR DB to the opt-in PDO connection from UNIT-001. Both existing UPDATE statements use **one PDO connection and one checked InnoDB transaction**. The selected policy is **all-or-nothing**: a failure in either update rolls back the repair, rather than committing the stop-time changes while failing to reconstruct missing start times.

The existing cron producer in `app/operators/config-crontab.php` invokes `/usr/bin/php` on this exact script. Its path, flags, scheduling configuration and generated command remain unchanged. No other tracked repository caller was found. The script is now **CLI-only**; HTTP access returns a fixed **403** before loading configuration or touching a database. This intentionally removes the prior unauthenticated web mutation surface if the contrib script is exposed by an installation. It does not prove that the lab or any other production deployment exposes that path.

No schema/migration SQL, other maintenance scripts, live cron jobs, shared connection helpers or deployment settings are changed. UNIT-044/045 remain outside this unit.

## Preserved behavior

- Configuration is read from the normal `app/common/includes/config_read.php` loader using script-relative paths, independent of the current working directory.
- The PDO connection uses the existing default/location settings policy, charset and MySQL session SQL mode from UNIT-001. No PEAR `db_open.php`/`db_close.php` connection is opened by this script.
- Interval/grace conversion follows the legacy `intval()` rules: nonpositive/missing interval defaults to 60; nonpositive/missing grace or grace exceeding the interval becomes `intdiv(interval, 2)`. The threshold remains their sum, including a possible zero grace when interval is 1. The configuration UI's acceptance of zero grace does not change the script's historical fallback behavior.
- The first UPDATE sets stop time to database `NOW()` and cause to `Stale-Session` only when the elapsed time since start plus recorded session duration is **strictly greater** than the threshold, and the old stop time is NULL or the zero date.
- The second UPDATE remains in its original position and uses the original expression: `DATE_ADD(NOW(), INTERVAL (acctsessiontime + threshold) SECOND)` for a missing/zero start and positive session duration. This produces a **future start time**, not a reconstructed past start. It is preserved deliberately, not silently fixed as part of the database-client migration. Reviewing that accounting policy requires separate scope.
- Closed sessions, threshold-equal sessions and relevant NULL/zero-counter cases retain their original selection behavior. The expressions preserve SQL NULL behavior rather than substituting new application-side date arithmetic.
- Success remains silent with CLI status 0. Failure now emits only `Stale-session repair failed.` on stderr and exits 1, without SQL, connection identifiers, secrets, exception chains or driver messages. Commit/rollback errors are not represented as success or as proof of an unchanged database.

## Validation and failure boundary

- The configured accounting identifier is restricted to 1–64 ASCII letters/digits/underscores and backtick-quoted. Qualified names, punctuation and overlong identifiers are rejected before connection/writes. Ordinary configured custom table names are supported.
- Nonscalar interval/grace configuration and integer overflow in the threshold sum are refused. Normal legacy scalar conversions are retained.
- The existing query dialect requires MySQL/MariaDB; another PDO driver is refused before mutations. PostgreSQL repair is not implemented or claimed.
- The script checks required bootstrap/configuration files exist and are readable before loading them.
- Inside the transaction, an executed zero-row target SELECT obtains the target metadata lock before checking `information_schema.TABLES`. Only an InnoDB base table is accepted. MyISAM and views fail before either UPDATE. The metadata-lock/preflight ordering avoids knowingly relying on rollback for a nontransactional table and prevents target DDL from switching its engine during the repair transaction.
- Each UPDATE is prepared with the threshold bound as `PDO::PARAM_INT`; execution and commit are checked. Statements are released after successful commit. On failure, an active transaction is rolled back if possible; an uncertain rollback/commit still reports failure generically.

This is an accounting-table maintenance transaction, not a command that disconnects a NAS session. InnoDB row locking/rechecked UPDATE predicates protect against the tested writer arriving before repair obtains the row lock. They do not establish that a real NAS has stopped, or prevent an independent later accounting update after repair commits. Configure interval plus grace above the actual accounting interim interval as before. Large repairs may hold accounting-row locks longer than the former autocommitted statements; no performance or production lock-duration claim is made.

## Verification

From the repository root:

```sh
STALE_SESSIONS_BASELINE=1 PYTHONDONTWRITEBYTECODE=1 python3 tests/stale_sessions_cli.py
PYTHONDONTWRITEBYTECODE=1 python3 tests/stale_sessions_cli.py
```

The test uses its shared fixture command/readiness helpers from `tests/expired_accounts_retirement.py`. It runs real PHP CLI/HTTP and MariaDB on a disposable internal Docker network, with tmpfs DB storage and a read-only PHP fixture mount. The baseline entry point is taken **unmodified** from commit `1707f4a2ff742de36465764e2e8ffc5ae504a5d5`. Both variants use the repository's real configuration loader and required shared includes; only the synthetic configuration is generated.

For deterministic comparison, a nonprivileged disposable database account gets SELECT/UPDATE on the fixture schema, and MariaDB's fixture-only `init_connect` freezes each application connection's clock. An independently executed pair of native legacy SQL expressions supplies golden complete accounting-row state. The unmodified PEAR entry point and the PDO candidate are each checked against it, without persisted snapshots or normalizing away stop/start timestamp differences. This clock control is not added to application code or live DB settings.

Verified cases:

- normal and empty tables, defaults and custom interval/grace values, legacy scalar conversions, grace fallback and strict threshold boundaries;
- NULL/zero start/stop dates and counters, closed/future sessions, a configured custom table, quoted/Unicode/percent/plus fixture usernames and execution from different working directories;
- legacy second-UPDATE trigger failure leaves the first update committed and exits 0: explicitly characterized as the baseline defect, not accepted as candidate parity;
- candidate errors in either UPDATE leave the complete accounting-row state unchanged, including rollback of earlier successful first-UPDATE changes;
- two independent concurrent CLI repair processes converge to the expected state;
- a second SQL connection holds a row lock while recording fresh accounting duration; the repair waits and its UPDATE rechecks the committed duration rather than closing that now-fresh session;
- a separate native PDO preflight probe executes the same zero-row SELECT in a transaction and holds it open; a concurrent engine-changing ALTER is observed waiting for the metadata lock until the probe rolls back. This is a targeted real locking check, not a claim that a production DDL operation was performed;
- malformed/overlong identifiers, malformed/overflow thresholds, MyISAM and a view fail without row changes;
- GET/POST/HEAD/PUT are refused before a configuration tripwire can execute, with unchanged rows;
- missing configuration and unavailable DB yield the generic nonzero CLI failure, without fixture connection values appearing in output/server logs;
- fixture directories, containers, DB and network are removed and absence is checked.

The tables are minimal typed accounting fixtures matching the fields consumed by this script, not full production-schema certification. No production configuration or database is copied/accessed; no real RADIUS/NAS exchange, live scheduling, PostgreSQL integration, deployment or performance test is claimed. Fixture connection material is generated at runtime, supplied only through ephemeral container environment and removed with the disposable resources; it is never persisted as config values or logged.

**Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.**
