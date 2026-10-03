# R02c — Invoice/group sidebars and realm/proxy catalogs

Implementation base: `39e4734ecd9ab7a17180d0d379b1255cd86f7c99` (R02b).
Differential baseline: `451a633c92bd5530c98bf357b80ee49d115c95f0` (R02a).
Branch: `refactor/pdo`.

## Scope

- RES-106: invoice sidebar's ID/username options use a private PDO read.
- RES-107: group sidebar's item options use private PDO reads, preserving keys,
  formatting, ordering and descriptors. The table argument must equal a configured
  check/reply table, then its identifier is validated; no arbitrary requested table.
- RES-171/172: realm/proxy list COUNT and page reads use private PDO, whitelisted
  scalar sort controls and bound integer LIMIT/OFFSET. All rows are fetched before
  the table is emitted. Existing location selection, default sorts, column sets,
  pagination and deletion/selection forms remain intact.

No business writes, locks, schema changes, generated FreeRADIUS config changes or
new snapshot guarantees. Count and page reads can still race as on the original
separate-query lists. Only the catalog's private handle is released; caller-owned
PDO/PEAR state is not closed or replaced. R02b's identifier helper is reused.

Intentional hardening: array-valued sort direction falls back instead of producing
a PHP TypeError; invalid page size/table or SQL failures render a generic failure
before the table body. Logs contain only exception classes and trusted templates,
not driver messages/bound values. NULL metadata is rendered safely as empty text.
Sidebar read failures use the R02a provider's redacted empty-option behavior.

### Characterized baseline defect

The original invoice sidebar includes common `db_close.php` at top level, where
it clears the caller's `$pdo` and rolls back its pending transaction. The native
wrapper retains a second reference only so it can observe/clean up this defect
without crashing on a NULL variable. The baseline explicitly must lose caller
ownership; the candidate explicitly must preserve it. This is **not parity** and
not an accepted arbitrary HTTP 500. The group sidebar's legacy close runs inside
its helper scope and does not lose this caller handle; both modes preserve it.

## Actual validation

`PYTHONDONTWRITEBYTECODE=1 python3 -u tests/operator_sidebars_catalogs_http.py`

Exit 0 on real PHP 8.4.24 and MariaDB 11.8.9/PDO MySQL. Baseline pins both list
pages, both sidebar files and the common selector file at R02a. Unchanged PEAR
branches in common readers remain equivalent; the new PDO count/column branches
are separately tested by R02b's fixture.

Checked:

- Exact invoice/group option values, keys and descriptor parity on custom tables,
  default and empty named backends; caller handle/transaction policy as above.
- Actual list-page HTTP output: six proxy/five realm sortable columns, asc/desc,
  three pages of seven fixture records, exact parsed cell and control parity,
  all seven identities covered once per traversal. Quote, percent, plus, HTML
  and Unicode labels participate in those comparisons.
- Actual POST deletion form action/method and selection field names/values;
  default, invalid and out-of-range navigation; empty named catalogs.
- Real auth/ACL refusal, array sort controls, invalid configured table/page size,
  missing-table failure and a deliberate **late SELECT failure after successful
  COUNT**, all before emitting a partly populated data table.
- Full configured-table checksum invariance before deliberate fixture NULL changes;
  candidate NULL metadata renders four empty cells on each row without warnings.
- An unconditional throwing `db_open.php` tripwire in the candidate: both complete
  list pages and real sidebar wrappers succeed without any PEAR open.
- SELECT-only domain grants, positively verified PHP stdout/stderr log channel,
  no fatal/parse/warning/uncaught error or generated connection material in logs,
  and actual container/network/temporary config/session teardown.

Initial fixture debugging corrected stock ACL-row copying and page aliases
(hyphens become underscores in the real permission gate). The earlier NULL-handle
fixture crash was traced to the exact original invoice-close defect, not hidden by
relaxing HTTP expectations. Temporary detailed diagnostics were removed and the
final native fixture rerun successfully.

Regression scripts on the combined candidate, all exit 0:
`group_attributes_http.py`, `group_mappings_http.py`, `invoice_create_http.py`,
`invoice_edit_http.py`, `realm_proxy_http.py`, `pos_update_http.py`,
`user_edit_http.py`, `operator_catalog_reads_http.py`, `operator_acl_reads_http.py`,
`selectbox_reads_http.py`, `operator_common_reads_http.py` (under `tests/`). These
cover real producers/mutations, existing rollback/CSRF policies and shared callers;
they do not convert every page in those families implicitly.
PHP syntax on six changed/new source files, Python AST and `git diff --check` pass.

R02a/b/c's planned slices are implemented. **R02b's reachable PEAR dispatch remains
necessary for callers scheduled in later lots**; unused old selector renderers
also require R28 reconciliation. Neither R02 progress nor historical inventory
counts establish current zero-PEAR coverage or release readiness. Keep `php-db`.

This is isolated native HTTP/database testing, not PostgreSQL/SQLite runtime,
external LDAP, FreeRADIUS parsing/reload, installation or live deployment.
No performance gain, push, PR, deployment, package removal, external cron change,
live configuration/service rebuild or NAS mutation. Preexisting
`tests/__pycache__/` is preserved outside the commit.

**Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.**
