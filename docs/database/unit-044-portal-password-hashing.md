# UNIT-044 — PDO portal-password hashing maintenance

## Scope and preserved contract

`contrib/scripts/maintenance/hash-user-portal-passwords.php` now uses the UNIT-001 PDO connection instead of PEAR DB. It remains a **PHP CLI-only** maintenance command, not a web endpoint or a new password format. HTTP requests still return an empty 404 response before configuration is loaded.

The existing portal-password helpers, application marker, PHP `PASSWORD_DEFAULT`, login/self-service pages and RADIUS password attributes are unchanged. No schema SQL, live configuration, scheduler or deployment is changed. The existing schema upgrade in `contrib/db/migrations/2026-09-user-portal-password-hashing.sql` remains the prerequisite; this unit does not replace it or convert production credentials automatically.

Preserved behavior:

- `--help`, `--dry-run`, the default batch size 100 and range 1–1000; invalid sizes exit 2 before configuration, and help exits 0;
- keyset iteration by increasing positive `id`, bounded buffered batches and explicit integer bindings for the last ID and LIMIT;
- scanning all user-information rows, independent of whether portal access is enabled;
- empty strings and NULL count as empty; recognized application-marked hashes are skipped, not rehashed;
- unmarked PHP-hash-shaped strings and an invalid application marker remain legacy plaintext inputs;
- zero, Unicode, quotes, literal percent/plus and trailing spaces are not stripped from stored credentials;
- existing aggregate output fields `scanned`, `empty`, `already_hashed`, `migrated`/`would_migrate`, `conflicted` and `failed`;
- dry-run counts eligible nonempty/unmarked values without hashing or updating them. This can include a NUL-containing value that actual hashing refuses; `would_migrate` is not a guarantee of successful conversion;
- failed hashing/row SQL increments `failed` and scanning continues; a failed batch SELECT/fetch stops the scan after incrementing `failed`;
- failures yield status 1; conflicts alone retain status 0. No username, input, stored hash, SQL binding or driver exception is printed.

Bootstrap and connection failures use generic stderr messages with status 1. Missing required configuration/helper files are detected before inclusion. The PDO connection preserves UNIT-001's default/location selection and MySQL session settings. No PEAR connection, verbose error callback or `db_open.php`/`db_close.php` is used by this migrated script.

## Per-row compare-and-swap, not a batch transaction

Each successful UPDATE is an **independent autocommitted CAS** of the selected ID and previously read password. All values are bound. MySQL/MariaDB use the existing helper's `BINARY` guard, selected from the actual PDO driver rather than a raw configuration alias. PostgreSQL retains its ordinary exact string equality path.

`PDOStatement::rowCount()` equal to 1 increments `migrated`; zero affected rows increments `conflicted`. A reset differing only in case or trailing space must not match under a case-insensitive/padded MySQL collation. Deleting the selected row, replacing its credential with a marked hash or allowing another migrator to win also produces a conflict instead of overwriting the new state.

There is deliberately **no transaction wrapping a batch or the whole scan**. A later failed row does not roll back earlier successful conversions. Rerunning the command skips those already marked and retries remaining legacy values. This is distinct from UNIT-043's all-or-nothing accounting repair policy.

The guard protects the observed ID/value pair, not every possible account identity race. It cannot detect an ABA sequence that recreates the same ID and identical old value. Keep table replacement, ID reuse and schema/DDL changes out of the maintenance window. Concurrent normal password resets are handled by CAS rather than requiring a global write freeze.

## Safe preflight and intentional differences

Before scanning passwords, the configured table name must be an unqualified 1–64 ASCII letter/digit/underscore identifier and is driver-quoted. Qualified/punctuation-bearing identifiers are rejected. Metadata must describe:

- a base table, not a view;
- suitable varchar/text password storage with at least 255-character capacity, or appropriate unbounded text;
- a sole primary-key column named `id`.

Primary-key metadata is read without joining `TABLE_CONSTRAINTS`, which can hide constraints from a SELECT-only account. A native SELECT-only simulation is tested. No UPDATE grant is required for dry-run. Nonrepresentable/non-increasing IDs fail rather than trapping keyset iteration.

These are intentional safety checks, not changes to the schema. The old script on a narrowed `VARCHAR(64)` fixture **reports successful migrations while storing truncated, unverifiable hashes** under its permissive session SQL mode. The unmodified baseline reproduces this exact defect; the candidate refuses the same column before scanning/converting, with unchanged original values. It also refuses a missing primary ID and a view. These refusal cases are not claimed as baseline parity.

The preflight is not a database-wide lock against external DDL; do not alter schema concurrently. MySQL/MariaDB is the runtime-tested platform. Driver-specific PostgreSQL quoting/metadata/equality code is retained but PostgreSQL integration is not tested or claimed here.

## Verification

Run from the repository root:

```sh
PORTAL_HASH_BASELINE=1 PYTHONDONTWRITEBYTECODE=1 python3 tests/portal_password_hash_cli.py
PYTHONDONTWRITEBYTECODE=1 python3 tests/portal_password_hash_cli.py
php tests/portal-password.test.php
php tests/portal-password-storage.test.php
```

The integration test runs real PHP, PEAR/PDO and MariaDB on an isolated internal Docker network, with tmpfs database storage and a read-only PHP fixture mount. It imports the real repository `userinfo` DDL under a configured custom table name. The baseline entry point is taken unmodified from commit `892a018703fdfbcb3fecadb4e7a4721c391ec136`.

Fixture passwords/hashes are generated by PHP at runtime and kept only in process memory and the disposable tmpfs database. Verification compares exact preserved values or calls real `password_verify()` against converted values **inside the fixture**. Probes emit only booleans; the host checks exact aggregate CLI counters. No password/hash snapshots, dumps or SQL containing credential values are printed or retained.

A fixture-only synchronization hook in the copied hash helper pauses after the real script has read its batch and before native hashing. The production entry point remains unmodified and crypto is not mocked. A second native connection resets/deletes the selected row, or an independent migration process completes first; resuming the original process proves actual SQL CAS behavior and exact conflict counts. The hook is never added to application helpers.

Verified:

- baseline/candidate ordinary counters and cryptographic verification, bounded batches with sparse IDs, empty table, NULL/empty/zero/Unicode/quoted inputs, marked/unmarked/malformed hashes and NUL failures;
- exact dry-run invariance and idempotent rerun;
- concurrent case-only/trailing-space reset, marked replacement, deletion and competing migrator protection;
- a later-row SQL trigger failure leaves an earlier successful conversion in place, preserves the failed row and reports status 1;
- SELECT-only dry-run, denied UPDATE per-row failures and denied SELECT failure counters with no credential output;
- baseline short-column truncation characterized separately, candidate short-column/identifier/primary-key/view refusals before conversion;
- HTTP denial and help/invalid option handling before a configuration tripwire;
- unavailable database produces a generic connection failure without fixture connection values in stdout/stderr/server logs;
- temporary fixture directories, database, containers and network removed and their absence checked.

The existing helper test remains unchanged. The storage regression test has narrowly updated PEAR-specific migration assertions for PDO/bound keyset/CAS/preflight while retaining its legacy helper and UI/log-redaction tests. Setup documentation now states the schema prerequisites and per-row partial-success policy.

This validation exercises real native password hashing/verification and SQL mutations on synthetic data. It does **not** exercise a real production password, authenticated portal HTTP login, PostgreSQL server, real RADIUS authentication, live maintenance execution or deployment.

**Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.**
