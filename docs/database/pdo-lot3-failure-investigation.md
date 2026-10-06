# Lot 3 failure investigation and targeted validation

Worktree: `/home/kevin/worktrees/daloradius/pdo-unit-001`, branch `refactor/pdo`.
Starting HEAD: `cb766c2315e1d2cefe7bf077eeeef16f01814b2d`.
Existing uncommitted lot-3 changes and `tests/__pycache__/` were preserved.

## Actual endpoint defect: nonempty user accounting returned HTTP 500

R03 commit `527cebeeae351cbd074283e192419a3509e0a869` removed the
`pages_common.php` include from `app/operators/library/ajax/user_info.php` while
converting the query to PDO. `json_info.php` still formats a nonzero total with
`dalo_info_bytes()`, which calls `toxbyte()` from that missing include. NULL/zero
aggregates bypass the formatter and therefore concealed this defect in the previous
native AJAX comparison.

Reproduced in disposable PHP HTTP execution before the correction: HTTP 500 with
the generic JSON error, and a temporary fixture-only diagnostic identifying
`Error` at `json_info.php:34`. Git history confirms the lost include. No raw driver
exception or connection values were logged; diagnostic instrumentation was never
written to production source.

Fix: one explicit `require_once` of the existing `pages_common.php`, resolved from
`__DIR__`, before running the user accounting query. PDO SQL, parameters, ACL and
error-response behavior are unchanged.

Regression: `tests/user_create_import_http.py` now inserts nonzero accounting rows
for the selected and an unrelated account into both disposable baseline/candidate
MariaDB databases, and checks exact `1 KB`/`2 KB` JSON responses for the selected
account. The pinned historical PEAR endpoint and current PDO endpoint both pass.
The existing empty-result and actual missing-table HTTP 500 assertions remain.

## Historical group fixture failures

The persisted final results for `group_mappings_http.py` (candidate and explicit
baseline) and `user_group_pages_http.py` were already successful. The earlier fatal
errors came from fixtures mixing retired PEAR handles and current PDO-only helpers:

- Basename-only replacement did not match the old page's complete helper include
  path, so the pinned PEAR page loaded the current helper instead.
- Legacy connection includes still pointed into the candidate tree after the
  production bootstrap files were removed.
- A compatibility-widget assertion still expected a candidate PDO-only include to
  accept the deliberately unsupported legacy handle.

Existing fixture corrections pin the historical helper/bootstrap in disposable
historical trees, replace the exact include paths and assert explicit rejection of
a non-PDO candidate handle. No PEAR branch or fallback is restored in production.
All three suites were rerun in this investigation and passed with empty stderr and
positive clean-PHP-log assertions where supplied by the suites.

## Synthetic read-only contract fixture

`tests/readonly_info_http.py` previously used PEAR doubles despite the production
ACL/endpoints having migrated to PDO. It now copies the required native reader
providers and uses typed synthetic PDO/PDOStatement doubles. The separate old
bootstrap characterization remains historical only.

The dictionary route's ACL connection failure deliberately returns fixed HTTP 503
text because that route does not register the JSON database callback; the other
two routes use their generic HTTP 500 JSON callback. The fixture now asserts these
exact existing contracts instead of changing the production ACL gate.

A 5-second server startup window also expired while other Docker fixtures ran.
Replace it with a bounded 30-second readiness deadline, without changing HTTP
assertions or accepting a failed request.

## Checked results after correction

| Suite | Result | Level |
|---|---|---|
| `pdo_only_helpers_audit.py` | exit 0 | PHP token parsing, native bootstrap/reflection, PDO-only contracts |
| `group_mappings_http.py` | exit 0, empty stderr | disposable native PHP/HTTP/MariaDB candidate; rollback and concurrency |
| `group_mappings_http.py`, explicit baseline | exit 0, empty stderr | disposable historical baseline; partial-write behavior characterized |
| `user_group_pages_http.py` | exit 0, empty stderr | native differential pages, ownership, rollback, concurrency and gates |
| `user_create_import_http.py` | exit 0, empty stderr | native differential create/import plus nonempty accounting regression |
| `readonly_info_http.py` | exit 0, empty stderr | 36 contract scenarios, including a historical CLI probe; real PHP/session/ACL, simulated database |

The native regression prints `PASS native nonempty accounting AJAX: exact byte
formatting, username filtering and pinned PEAR/PDO parity`. The selected native
matrix completes with `failures=[]`.

## Boundaries

Only application source/test/documentation in the worktree was changed. No live
lab database, Compose service, schema, installation dependency, scheduler or
external service was modified. Temporary test resources are disposable and cleaned
up by the fixtures. No commit, push, PR or deployment was performed. These checks
do not certify a fresh installation without PEAR DB, FreeRADIUS/NAS hardware,
external payment providers or production upgrade behavior.

Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.
