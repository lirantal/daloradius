# Applying bundled database migrations

For application upgrades, see the [Debian/manual/Docker upgrade guide](../../../doc/setup/upgrade.md).
The Debian upgrader, fresh installer and Docker entrypoint all use this runner.
Backups and recovery are administrator-managed; neither the runner nor the
Debian upgrade helper creates backups or performs automatic rollbacks.

From the root of the **updated** daloRADIUS checkout:

```sh
php contrib/scripts/maintenance/migrate-db.php
# Stop application writes and make/verify a database backup before continuing.
php contrib/scripts/maintenance/migrate-db.php --apply
```

The default command is a read-only preview. `--apply` executes pending `*.sql`
and `*.php` migration files from this directory in filename order. It uses the
default database from `app/common/includes/daloradius.conf.php`; another trusted
PHP configuration can be selected with `--config=/absolute/path/to/daloradius.conf.php`,
and a named `$configValues['CONFIG_LOCATIONS']` entry can be targeted with
`--location=NAME`. Configuration files are executable PHP: never use an
untrusted file. No password is passed on the command line. Run as an account
allowed to read the application configuration.

Requirements: PHP CLI with PDO MySQL (`pdo_mysql`), MariaDB, standard table
names and database privileges required by the bundled migrations
(SELECT/INSERT/UPDATE/DELETE/CREATE/ALTER and indexes). Before writing, the
runner checks that the required standard application tables and key columns
exist; a missing table/column is refused without creating history or starting
DDL. This does not predict all later SQL failures. These migrations are
**not MySQL/PostgreSQL-compatible**. Keep this script,
`app/common/includes/pdo_connection.php` and the migrations directory from the
same checkout. The upgrader can select a staged target directory with
`--migrations-dir=/absolute/path/to/migrations`; only trusted repository
migrations may be selected. Existing records must still match the selected
files and checksums.

The runner creates `daloradius_schema_migrations` in the selected database,
records each filename and normalized SHA-256 checksum (CRLF/CR normalized to LF
so Linux and Windows checkouts/images produce identical hashes), and skips
successful migrations on later runs. A database-scoped advisory lock
(`GET_LOCK`, default timeout 30 seconds, configurable with
`--lock-timeout=SECONDS`) prevents two runners executing at once. It does not
block application writers or manual SQL: use a maintenance window. Dry runs do
not create a history table or validate future DDL privileges.

## Modes and options

- Default (no mode flag): read-only preview. Exits `0` on success.
- `--check-pending`: read-only check for scripts and monitoring. Exits `0` if
  all bundled migrations are recorded, `10` if pending migrations exist, or `1`
  on error.
- `--apply`: executes pending migrations and records them.
- `--baseline-fresh`: **for fresh installations only**, immediately after
  loading `contrib/db/mariadb-daloradius.sql`. Refuses if
  `daloradius_schema_migrations` already exists; creates the ledger and records
  all bundled migrations as `applied` without replaying historical SQL over a
  newly seeded schema. PHP migrations that expose an `is_applied` check whose
  prerequisites are not yet present (for example FreeRADIUS tables before the
  FreeRADIUS container initializes them on a fresh Docker deployment) are
  reported as `DEFERRED` instead of being recorded prematurely, so the next
  `--apply` runs them once the tables exist.
- `--location=NAME`: select a named database location from `CONFIG_LOCATIONS`.
  When running against `default` while `CONFIG_LOCATIONS` entries are present,
  the runner prints a reminder listing the configured location names.
- `--lock-timeout=SECONDS`: advisory lock wait timeout (`0`..`300`, default `30`).
- `--verbose` / `-v`: include the SQL driver error message on database failure
  (never prints credentials).

Existing installations without history are processed once using the bundled
idempotent SQL. The old operator-password widening migration is considered
satisfied if `operators.password` already has capacity 95 or greater (or is a
TEXT type), preserving LDAP NULL passwords. Likewise, the LDAP migration
preserves already-wider `operators.password` columns (such as `VARCHAR(255)` or
`TEXT`) instead of shrinking them to `VARCHAR(95)`. Once the ledger exists and
all bundled migrations are recorded, `--apply` performs no `CREATE` or write
statements. Existing password values are not hashed by this runner. Portal
password conversion remains a separate maintenance task.

## Terminal output

Colors are automatic on interactive terminals: green for successful migrations,
yellow for pending work/warnings, cyan for already-satisfied or baselined
entries, gray for recorded migrations that are skipped, and red for errors.
Text labels remain meaningful without colors. The header identifies the mode
and selected database; the final summary counts each status.

Pipes and redirected output stay plain. Automatic colors also respect a
nonempty `NO_COLOR` environment variable and `TERM=dumb`. Use `--no-color` or
`--color=never` to disable colors explicitly. `--color=always` forces ANSI
colors, even in a pipe or with `NO_COLOR`; `--color=auto` is the default.
stdout and stderr are checked independently.

## Existing installations upgraded manually

The runner can be introduced after **all or some of the currently bundled SQL
migrations were applied manually**. It cannot recover their execution history:
without its own history rows, the preview reports `PENDING`, meaning *not
recorded*, not proof that a schema change is missing.

On the first `--apply`, unrecorded migrations are processed once. Apart from
the operator-password widening check described above, their SQL is executed
again using its idempotent guards, then recorded. `APPLIED` means the migration
was executed successfully, not necessarily that it changed a row or column.
`SATISFIED` means the special widening requirement was already met and recorded
without running its old NOT NULL statement. Later runs report `SKIP` for all
successfully recorded migrations.

This does not guarantee compatibility with arbitrarily customized schemas,
altered historical SQL or future non-idempotent migrations: keep a verified
backup and use a maintenance window.

## Failure and recovery

**MariaDB DDL commits implicitly: this is not an atomic upgrade or a rollback
tool.** Each migration is marked `running` before execution and `applied` only
after all its statements succeed. A failure or interruption can leave partially
applied SQL. The next invocation refuses to proceed if history is unfinished,
a tracked checksum changed, or a tracked file disappeared.

Do not delete history and rerun blindly. Inspect the named migration file,
statement index, current schema/data and the history row using an administrator
connection. Restore the verified backup or complete/repair the interrupted SQL
manually. Only after verifying that the entire file's intended changes are
present should an administrator set its history `status` to `applied` and
`applied_at` appropriately. The runner intentionally has no force/retry flag.
By default it reports the SQLSTATE, MariaDB error code and statement number;
pass `--verbose` to also display the driver error message.

**Security note:** Like `--config`, `--migrations-dir` is **executable input** —
the runner does not enforce repository provenance on the selected path. Any
`*.php` file in that directory is executed via PHP `require`, and any `*.sql`
file is executed against the configured database. Only point `--migrations-dir`
at a trusted, maintainer-controlled directory (never user-writable or untrusted
paths). New migrations must be reviewed for safe ordering and idempotence. SQL
files using mysql client commands such as `DELIMITER`, `SOURCE` or `USE` are
rejected before execution. When conditional schema inspection or complex logic
is needed, a `.php` migration file can return either a `function (PDO $pdo)`
callable or an array `['apply' => callable, 'is_applied' => callable|null]`.
Returning `false` from `apply` (or `is_applied` during `--baseline-fresh`)
marks the migration as `DEFERRED` without recording it in
`daloradius_schema_migrations`, allowing a subsequent `--apply` to execute it
once external prerequisite tables exist.
