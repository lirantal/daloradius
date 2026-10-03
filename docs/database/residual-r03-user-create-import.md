# R03 — User creation/import and associated PDO providers

## Scope and baseline

Baseline: `a4f2c6ef2973c9ea037f5720ae402870dfef4acb` on `refactor/pdo`.

- R03a: `mng-new.php`, shared `user_create.php`, PDO dispatch for information preparation/add/update and single-attribute inserts.
- R03b: `mng-new-quick.php`.
- R03c: `mng-import-users.php`, CSV and simple MAC/PIN lists.
- R03d: username autocomplete, accounting user-info JSON, remaining page-local existence/group reads in `mng-edit.php`.

The existing PDO-aware `dalo_portal_db_sensitive_call()` already met the handle/error-redaction contract and was not rewritten.
The shared groups renderer adds PDO dispatch while keeping its PEAR path. The dictionary autocomplete renderer reuses the independent R02 selector, with its historical nonempty/non-NULL filter: it no longer opens/closes the caller's connection. This is a prerequisite overlap with one R06 renderer, **not completion of R06**.

## Transaction and connection contract

Every standard/quick create and whole accepted import batch uses one nonpersistent PDO handle and transaction. Information, attributes and group helpers borrow it; they never begin/commit/roll back or reconnect. All creation-written tables require InnoDB before mutation. Selected session locations and configured table names are honored. Values are bound; table/column identifiers are allowlisted and schema lengths are checked before information writes. SQL logs contain templates, not passwords, portal hashes or payment values.

Because the account name has no database-wide unique constraint across tables, a schema/authentication-table advisory lock serializes cooperating R03 creators (including case-insensitive collisions). It is released by nonpersistent PDO teardown after commit/rollback; a failed post-commit lock release cannot turn a successful commit into a failure. Existing authentication/info/billing collisions are rechecked under the transaction. Import skips already-existing accounts, preserving its policy. External writers not using this lock can still race; this is not a universal uniqueness guarantee.

Commit happens before presenting success or exporting generated credentials. Any exception rolls back earlier account rows and earlier import users, clears newly generated results, and preserves unrelated prior notification state. No schema upgrade SQL is needed; this lot does not alter live engines or data.

## Characterized differences from PEAR

- A malformed nonblank CSV/simple-list row rejects the batch rather than silently importing a valid subset; blank trailing lines remain allowed. Duplicate rows inside one submitted batch are rejected, whereas pre-existing database accounts remain skipped.
- Legacy import passed raw configured table names into a helper accepting configuration keys only. Optional RADIUS attributes could silently disappear. PDO accepts validated keys/configured names and persists those attributes; ordinary A/B projections exclude this characterized difference and assert repaired reply attributes separately.
- Literal `%` in standard/quick identities is preserved rather than stripped. Zero-valued passwords and attribute values are not treated as empty.
- Quick-form missing billing variables receive explicit historical empty defaults. Early malformed/CSRF failure rendering has initialized selection defaults and produces no PHP warnings.
- The edit page uses its PDO display handle for the groups renderer and respects the selected location for existing PDO operations; its already-migrated mutation provider is not rewritten.

## Native validation

`PYTHONDONTWRITEBYTECODE=1 python3 -u tests/user_create_import_http.py` executes real PHP HTTP endpoints and MariaDB schemas in disposable Docker containers, not mocked database responses.

Covered: pinned PEAR/PDO standard/quick/import state parity; authentication, ACL and CSRF gates; dynamic `dictValuesN[]` controls; empty rows and literal zero; MAC/PIN modes including numeric import keys; optional CSV check/reply attributes; duplicate/collision refusal; malformed later attribute/CSV rejection; errors in later billing writes and a later imported user with exact physical noncredential row-state rollback (including IDs and all non-sensitive columns); non-InnoDB refusal; independent concurrent sessions on separate PHP workers creating one account; portal access/password gating; selected locations for all create/read paths; configured table names; AJAX contracts, array parameters and clean JSON SQL failures; edit/group display; PDO add/update/preparation/attribute helpers preserving a caller-owned prior write and rollback; real retained PEAR helper dispatch; generated-password export offered only after commit and withheld after rollback. Candidate PHP logs contain no warnings/fatals or fixture credential values/hashes. Credential-bearing columns are not persisted in snapshots.

Focused native regressions passed: `user_edit_http.py`, `group_attributes_http.py`, `group_mappings_http.py`, `operator_common_reads_http.py`, `pos_provision_http.py`, `batch_create_http.py`, and `portal_password_hash_cli.py`. PHP syntax and `git diff --check` are part of final verification. These are isolated local runtime tests, not live RADIUS/hardware or deployment tests.

## Remaining boundary

PEAR DB stays installed. `mng-edit.php` still renders the separate legacy user-report family scheduled under R20; attribute-information AJAX belongs to R06. Information-helper PEAR branches still serve legacy callers. Completing R03 does not establish whole-application PDO coverage or authorize dependency removal.

No push, PR, deployment, live configuration change or live data mutation is included. The pre-existing untracked `tests/__pycache__/` is preserved. Disposable fixture files, databases, containers and networks are removed.
