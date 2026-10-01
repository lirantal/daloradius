# UNIT-045 — PDO read-only maintenance monitors

## Scope

Migrate the two read-only query/iteration blocks in:

- `contrib/scripts/maintenance/monitor/node-status-monitor.php`;
- `contrib/scripts/maintenance/monitor/user-traffic-monitor.php`.

Both now use the UNIT-001 PDO connection, bound data values and explicit fetch modes. They do not use PEAR DB, SELECT `rowCount()`, `db_open.php` or `db_close.php`. There is **no write transaction, schema change or accounting update**. SMTP notification is an external side effect, not a database write.

The only adjacent application change is correcting the two monitor filenames in `app/operators/config-crontab.php` to `maintenance/monitor/...`, retaining their existing intervals. This changes the source of future generated cron entries, not an installed system crontab. The cron configuration UI's authorization, rendering, saving and other jobs are unchanged.

## Connection, inputs and error policy

The correct repository bootstrap is `dirname(__DIR__, 4) . '/app/common/includes'`. Required configuration/provider files are checked before loading. Default/location selection, charset and MySQL session settings come from UNIT-001. The queries retain MySQL/MariaDB's historical `UNIX_TIMESTAMP`, zero-date and unsigned-cast semantics; non-MySQL PDO drivers fail explicitly rather than pretending these queries are portable.

Table identifiers must be unqualified 1–64 character ASCII letter/digit/underscore names and are backtick-quoted. Node delay, hard/soft limits and every excluded username are bound. Threshold values are scalar, integer-coerced and clamped to at least 1. Saved configuration is used for normal cron CLI execution; trusted CLI includes can supply the historical `$_POST` overrides. Defaults remain node delay 15 seconds, hard traffic 1073741824 bytes and soft half the hard limit.

There is an important coexistence detail: the old `db_open.php` **reloads configuration** after the old monitors' preliminary threshold assignments. Consequently ordinary legacy SQL uses saved threshold values; the migration does not claim that honoring saved settings is a new repair. Tests supply equivalent saved settings to PEAR and PDO and separately exercise POST-free invocations and nondefault saved settings.

The tracked producer invokes `/usr/bin/php`. Both scripts are now **CLI-only**: HTTP returns an empty 403 before configuration, SQL or SMTP. A fixture demonstrates that the unmodified baseline with its loader issue worked around can send a node email through unauthenticated HTTP. This is not a claim about production web-server exposure.

SMTP disabled and invalid recipients preserve their historical informational text and status 0, without attempting a database connection. No-match runs emit nothing and exit 0. SQL/bootstrap/provider failures emit only a fixed monitor-specific stderr message and exit 1. SMTP failures retain the SUCCESS/FAILURE labels but replace raw helper exceptions with `Email delivery failed` and yield status 1, including a failed hard delivery followed by a successful soft delivery. Successful deliveries retain `Email sent successfully`.

The shared `mail.php`, PHPMailer implementation, legacy database provider and configuration files are unchanged. No driver exception, SQL binding, SMTP exception message or connection value is printed by the migrated monitors. A `finally` block releases statements/results/connection before the final status is selected; failure paths do not call `exit()` inside the catch and bypass that cleanup.

## Preserved selection and notification contracts

### Nodes

Keep `UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(time) > delay`, the selected column order and the original email subject/header. PDO fetches numeric rows to match the old default PEAR row shape. The existing separator-free `implode($row)` formatting is deliberately retained. Exact delay-boundary, future and zero dates retain the observed baseline behavior; NULL selected fields still format as empty strings.

### Traffic

Keep active accounting selection `(acctstoptime = '0000-00-00 00:00:00' OR acctstoptime IS NULL)` and the unsigned sum of input/output octets. Hard uses `>=`, soft uses `>`; NULL counters and terminated sessions do not qualify. Soft excludes usernames already present in the hard result, including their other sessions, under the existing database collation semantics.

**The historical soft-only policy is not changed:** the soft query/email occurs only when the hard query matched at least one row. These are sequential autocommit reads on one PDO handle, not a repeatable-read snapshot. No lock or cross-resource transaction is introduced. A previously accepted hard email cannot be undone by a later SQL or SMTP failure, and repeated scheduled executions can send repeated alerts as before.

## Demonstrated repairs, not blanket baseline parity

1. Both original entry points traverse one parent directory too few when loading configuration. On an ordinary fixture tree they report `SMTP Server not configured`, exit 0 and emit missing-loader warnings instead of working. The candidate uses the actual repository root. Differential success testing installs a **fixture-only transparent compatibility loader** under `contrib/app/common/includes`; it does not modify the baseline entry points or legacy helpers.
2. The old cron producer emits `monitor/...` beneath `CONTRIB_SCRIPTS`, although the tracked files are in `maintenance/monitor/...`. Only those two source literals are corrected. Tests check these source paths statically; actual UI cron installation is not exercised.
3. The baseline interpolates hard-result usernames into the soft `NOT IN` list. A quote-bearing synthetic hard username produces a real SQL failure after the hard email has already been accepted. The candidate binds all names and successfully sends the appropriate soft alert for the same fixture.
4. The old soft notification builds `$body2` but sends `$body1`. The baseline SMTP fixture receives two copies of the hard body. The candidate sends the actual soft body and threshold. Normal node and hard-body parity are tested separately from this intentional soft-body change.

The node description's unrelated stale-session text is corrected; its query and mail layout are not redesigned. No broader changes to alert policy, HTML formatting, per-user aggregation, scheduling activation or global SMTP exception handling are included.

## Validation

From the repository root, with Docker available:

```sh
READONLY_MONITORS_BASELINE=1 PYTHONDONTWRITEBYTECODE=1 python3 tests/readonly_monitors_cli.py
PYTHONDONTWRITEBYTECODE=1 python3 tests/readonly_monitors_cli.py
```

This runs real PHP, PEAR/PDO, MariaDB and the repository's unchanged `send_email()`/PHPMailer SMTP implementation. The fixture imports the actual `node` DDL and the repository's adapted FreeRADIUS 3 `radacct` DDL under configured custom table names. It does not claim to test the Docker installer's separate stock FreeRADIUS schema import path.

A generated database account has **SELECT-only** grants. The database is tmpfs-backed on a disposable internal Docker network. A frozen clock is applied to ordinary fixture connections through MariaDB `init_connect`. A threaded SMTP receiver binds only the new internal bridge address, accepts only `admin@example.test` recipients and keeps decoded messages in memory. No SMTP endpoint is published on the LAN address and no external delivery occurs. Connection material is generated in memory/passed through disposable container environment, never put in fixture configuration literals, snapshots or output.

Body/subject/recipient/content-type assertions use actual received SMTP messages. Decoded line endings are normalized; variable RFC Date/Message-ID headers are not compared. Full controlled node/accounting table state is compared before/after; it contains only synthetic metadata and no seeded authentication credentials.

Verified runtime scenarios:

- exact baseline startup-loader failure, then working unmodified PEAR entry points with the compatibility loader;
- node mail layout, column order, NULL fields, strict delay equality, future/zero dates and alternate working directory;
- node/traffic empty results, disabled SMTP and invalid recipient early returns;
- hard equality, just-below hard, exact soft equality, soft-only silence, terminated sessions, NULL octets and same-name session exclusion;
- normal hard-body parity and the separately characterized/corrected soft-body defect;
- quote/Unicode/literal percent/plus names and real native bound `NOT IN` behavior;
- POST-free saved/nondefault delay and limit settings;
- malformed table identifiers and array-typed thresholds, rejected before notification;
- denied SELECT on each target table while the account can still connect through privileges on the other table;
- native SMTP DATA rejection, redacted failure output and failed hard delivery followed by successful soft delivery retaining status 1;
- fixture SMTP acceptance of the hard alert followed by a second-connection table rename: the later soft SELECT fails, no soft alert is sent and the already accepted hard alert remains; restoring the fixture table yields unchanged exact row state;
- baseline fixture HTTP notification without authentication, candidate GET/POST/HEAD/PUT 403 before a configuration tripwire;
- thrown configuration loader, missing configuration file and stopped database generic failures; disabled SMTP/invalid recipient still return informationally with the database stopped;
- fixture server logs do not contain generated connection material or the configuration-tripwire exception;
- tmpfs database, PHP fixture, internal network and SMTP listener/thread removed and cleanup verified.

Static checks cover PHP/Python syntax, whitespace and the corrected cron producer paths. No live cron, production database, actual mail account, SMTP AUTH/STARTTLS/TLS, external mailbox, PostgreSQL server or authenticated configuration UI flow is tested or modified. These scripts have no new dry-run/help options: an ordinary CLI invocation can send alerts when deployed/configured.

**Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.**
