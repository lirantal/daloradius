# R02b — Borrowed-handle operator reads

Base: `451a633c92bd5530c98bf357b80ee49d115c95f0`, branch `refactor/pdo`.

## Scope

RES-059/061/062/063/065/073/074: `count_sql`, `get_numrows`,
`get_table_column_names`, `get_user_group_mappings`, `hotspots_exists`,
`user_exists`, `user_portal_password_is_set` now dispatch on the caller's handle.
The new PDO branches borrow that exact handle: no connection factory, commit,
rollback or close. Existing PEAR branches remain byte-identical, verified by
extracting/removing only the seven inserted dispatch blocks. All unrelated
function bodies are unchanged. This is provider readiness, **not conversion of
all accounting/user/import pages that still pass PEAR**.

`read_helpers_pdo.php` supplies narrow internal identifier validation and prepared
read execution. User/hotspot names are bound, trim/no-trim policies and DISTINCT
projections preserved; debug SQL contains templates, not values. Configured table
keys must match the DB-table prefix and exist; quoted identifiers are limited to
64 ASCII alphanumeric/underscore characters. This intentionally rejects keys that
the legacy `preg_match(...) !== false` predicate accidentally accepted.

COUNT stays explicit, not SELECT rowCount. `count_sql` returns an integer;
`get_numrows` keeps the scalar fetch API, although native PDO can return an integer
where PEAR returned a numeric string. Current callers use numeric comparisons.
Column discovery keeps filtering/fallback behavior, using MySQL SHOW COLUMNS or
PostgreSQL information_schema. Mapping errors return an empty list, portal-presence
errors false, metadata failures fallback; count/existence SQL failures propagate a
fixed RuntimeException without the original driver exception. Presence still
requires exactly one matching non-NULL/nonempty portal field, not merely COUNT>0.

The string SQL count APIs remain trusted application code, not request SQL or a
security parser. Other legacy builders may still require PEAR-specific escaping;
adding count dispatch is not permission to convert their dependent writes by
changing a handle silently.

## Executed native validation

`PYTHONDONTWRITEBYTECODE=1 python3 -u tests/operator_common_reads_http.py`

Exit 0 on PHP 8.4.24 and MariaDB 11.8.9. All seven readers compare the pinned PEAR
implementation with both current PEAR compatibility and current PDO on equivalent
custom-table fixtures, default and named backends. Numeric-count comparison is
normalized deliberately; no blanket strict PHP scalar-type parity is claimed.
Tests cover quoted/percent/plus names, trim policy, group DISTINCT/order, columns,
portal NULL/empty/set and duplicate-row rejection, invalid table keys, metadata
fallback and later SQL errors.

A real caller INSERT precedes every read on the same open transaction; the reader
must still see it, preserve the handle/transaction, and leave it invisible to an
independent connection. Caller rollback must remove it. Domain tables have SELECT
only; INSERT is granted solely on the disposable ownership-probe table. Table
checksums remain unchanged after controlled fixture changes are restored.
The wrapper uses native generated sessions and the real auth/ACL gate, not primary
login. PHP stdout/stderr capture is verified by a positive harmless marker, then
checked for fatal/parse/warning/uncaught errors and generated connection material.

Regression suites on the combined R02b/R02c candidate, all exit 0:
`group_attributes_http.py`, `group_mappings_http.py`, `invoice_create_http.py`,
`invoice_edit_http.py`, `realm_proxy_http.py`, `pos_update_http.py`,
`user_edit_http.py`, `operator_catalog_reads_http.py`, `operator_acl_reads_http.py`,
`selectbox_reads_http.py` (all under `tests/`). R02c's new suite also passes.
PHP syntax, Python AST and `git diff --check` pass. No production test hooks remain.

Fixtures use tmpfs SQL/internal networking, exclude live config, generate connection
material at runtime and remove configs/sessions/containers/network at teardown.
This is isolated HTTP/PHP/MariaDB execution, not PostgreSQL runtime, installation,
FreeRADIUS protocol, external-directory or live deployment validation. No performance
claim, push, PR, package removal, live configuration/service, cron or NAS change.
Preexisting `tests/__pycache__/` is excluded. R02c is a separate commit slice.

**Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.**
